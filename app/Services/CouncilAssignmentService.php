<?php

namespace App\Services;

use App\Models\GiangVien;
use App\Models\HoiDong;
use App\Models\ThanhVienHoiDong;
use App\Models\HoSoBaoVe;
use App\Models\HocKy;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CouncilAssignmentService
{
    /**
     * Thống kê tổng hợp trước khi phân công hội đồng
     */
    public function thongKeTruocPhanCong(?string $maHocKy = null, ?string $maKhoa = null): array
    {
        // 1. Giảng viên đủ điều kiện
        $gvQuery = GiangVien::with(['boMon.khoa', 'taiKhoan'])
            ->where(function($q) {
                $q->whereNull('TrangThai')
                  ->orWhere('TrangThai', 'Đang công tác')
                  ->orWhere('TrangThai', 'Hoạt động');
            });

        if ($maKhoa) {
            $gvQuery->whereHas('boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa));
        }

        $allGiangViens = $gvQuery->get();
        $tongGVEligible = $allGiangViens->count();

        // 2. Hội đồng trong phạm vi
        $hdQuery = HoiDong::with(['thanhViens.giangVien', 'hoSoBaoVes']);
        if ($maHocKy) {
            $hdQuery->where('MaHocKy', $maHocKy);
        }
        if ($maKhoa) {
            $hdQuery->where('MaKhoa', $maKhoa);
        }
        $hoiDongs = $hdQuery->get();
        $tongHoiDong = $hoiDongs->count();

        // Đếm trạng thái hội đồng
        $now = now();
        $hdSapDienRa = 0;
        $hdDangDienRa = 0;
        $hdDaHoanThanh = 0;

        foreach ($hoiDongs as $hd) {
            $tt = trim($hd->TrangThai ?? '');
            if ($tt === 'Đã kết thúc' || ($hd->ThoiGianKetThuc && $hd->ThoiGianKetThuc->isPast())) {
                $hdDaHoanThanh++;
            } elseif ($tt === 'Đang diễn ra' || $tt === 'Đang hoạt động' || ($hd->ThoiGianBatDau && $hd->ThoiGianBatDau->isPast() && $hd->ThoiGianKetThuc && $hd->ThoiGianKetThuc->isFuture())) {
                $hdDangDienRa++;
            } else {
                $hdSapDienRa++;
            }
        }

        // 3. Số lượt phân công và số lượng GV thực tế tham gia
        $thanhVienRecords = ThanhVienHoiDong::whereIn('MaHoiDong', $hoiDongs->pluck('MaHoiDong'))->get();
        $distinctGVsAssigned = $thanhVienRecords->pluck('MaGV')->unique();
        $soLuongGVPhanCong = $distinctGVsAssigned->count();

        // Thống kê theo chức danh
        $theoChucDanh = [
            'ChuTich' => 0,
            'ThuKy' => 0,
            'PhanBien' => 0,
            'UyVien' => 0,
        ];

        foreach ($thanhVienRecords as $tv) {
            $vt = trim($tv->VaiTro ?? '');
            if (mb_stripos($vt, 'chủ tịch') !== false) {
                $theoChucDanh['ChuTich']++;
            } elseif (mb_stripos($vt, 'thư ký') !== false) {
                $theoChucDanh['ThuKy']++;
            } elseif (mb_stripos($vt, 'phản biện') !== false) {
                $theoChucDanh['PhanBien']++;
            } else {
                $theoChucDanh['UyVien']++;
            }
        }

        // 4. Số lượng hội đồng theo từng giảng viên (1 hội đồng = 1 lượt)
        $gvWorkload = [];
        foreach ($allGiangViens as $gv) {
            $count = $thanhVienRecords->where('MaGV', $gv->MaGV)->unique('MaHoiDong')->count();
            $gvWorkload[$gv->MaGV] = [
                'giangVien' => $gv,
                'soHoiDong' => $count,
            ];
        }

        $workloads = array_column($gvWorkload, 'soHoiDong');
        $averageLoad = count($workloads) > 0 ? round(array_sum($workloads) / count($workloads), 2) : 0;

        // Giảng viên chưa tham gia hội đồng nào (0 hội đồng)
        $gvChuaThamGia = array_filter($gvWorkload, fn($item) => $item['soHoiDong'] === 0);

        // Giảng viên tham gia nhiều hơn mức trung bình
        $gvVuotTrungBinh = array_filter($gvWorkload, fn($item) => $item['soHoiDong'] > $averageLoad);

        return [
            'tong_giang_vien_du_dieu_kien' => $tongGVEligible,
            'tong_hoi_dong'                => $tongHoiDong,
            'so_gv_da_phan_cong'          => $soLuongGVPhanCong,
            'so_gv_chua_phan_cong'        => max(0, $tongGVEligible - $soLuongGVPhanCong),
            'hoi_dong_sap_dien_ra'        => $hdSapDienRa,
            'hoi_dong_dang_dien_ra'       => $hdDangDienRa,
            'hoi_dong_da_hoan_thanh'      => $hdDaHoanThanh,
            'theo_chuc_danh'              => $theoChucDanh,
            'trung_binh_hoi_dong_moi_gv'   => $averageLoad,
            'danh_sach_0_hoi_dong'        => array_values($gvChuaThamGia),
            'danh_sach_vuot_trung_binh'    => array_values($gvVuotTrungBinh),
        ];
    }

    /**
     * Thống kê chi tiết hội đồng cho 1 giảng viên cụ thể
     * Quy tắc đếm: 1 hội đồng = 1 lượt
     */
    public function thongKeGiangVienHoiDong(string $maGV, ?string $maHocKy = null): array
    {
        $query = ThanhVienHoiDong::with('hoiDong')
            ->where('MaGV', $maGV)
            ->whereHas('hoiDong');

        if ($maHocKy) {
            $query->whereHas('hoiDong', fn($q) => $q->where('MaHocKy', $maHocKy));
        }

        $records = $query->get()->unique('MaHoiDong');
        $now = now();

        $tongSo = $records->count();
        $daHoanThanh = 0;
        $sapDienRa = 0;
        $dangDienRa = 0;

        $theoVaiTro = [
            'Chủ tịch' => 0,
            'Thư ký' => 0,
            'Phản biện' => 0,
            'Ủy viên' => 0,
        ];

        foreach ($records as $r) {
            $hd = $r->hoiDong;
            $tt = trim($hd->TrangThai ?? '');

            if ($tt === 'Đã kết thúc' || ($hd->ThoiGianKetThuc && Carbon::parse($hd->ThoiGianKetThuc)->isPast())) {
                $daHoanThanh++;
            } elseif ($tt === 'Đang diễn ra' || $tt === 'Đang hoạt động' || ($hd->ThoiGianBatDau && Carbon::parse($hd->ThoiGianBatDau)->isPast() && $hd->ThoiGianKetThuc && Carbon::parse($hd->ThoiGianKetThuc)->isFuture())) {
                $dangDienRa++;
            } else {
                $sapDienRa++;
            }

            $vt = trim($r->VaiTro ?? '');
            if (mb_stripos($vt, 'chủ tịch') !== false) {
                $theoVaiTro['Chủ tịch']++;
            } elseif (mb_stripos($vt, 'thư ký') !== false) {
                $theoVaiTro['Thư ký']++;
            } elseif (mb_stripos($vt, 'phản biện') !== false) {
                $theoVaiTro['Phản biện']++;
            } else {
                $theoVaiTro['Ủy viên']++;
            }
        }

        return [
            'tong_so_hoidong' => $tongSo,
            'da_hoan_thanh'   => $daHoanThanh,
            'sap_dien_ra'     => $sapDienRa,
            'dang_dien_ra'    => $dangDienRa,
            'theo_vai_tro'    => $theoVaiTro,
            'danh_sach'       => $records,
        ];
    }

    /**
     * Thuật toán phân công thông minh gợi ý thành viên Hội đồng
     * - Loại trừ GVHD của các đề tài đã chọn
     * - Loại trừ giảng viên bị trùng lịch bảo vệ cùng thời điểm
     * - Ưu tiên giảng viên có chuyên môn cùng bộ môn/ngành
     * - Cân bằng tải công việc: ưu tiên giảng viên có số lần tham gia ít hơn
     * - Phân bổ đúng vai trò: Chủ tịch (PGS/TS), Thư ký (cẩn trọng), Phản biện (chuyên môn sâu)
     */
    public function deXuatThanhVienHoiDong(
        $thoiGianBatDau,
        $thoiGianKetThuc,
        array $maHoSos,
        ?string $maHocKy = null,
        ?string $maKhoa = null
    ): array {
        $startTime = Carbon::parse($thoiGianBatDau);
        $endTime = Carbon::parse($thoiGianKetThuc);

        // 1. Xác định GVHD của các đề tài đã chọn (Bắt buộc LOẠI TRỪ)
        $hoSos = HoSoBaoVe::with(['nhom.deTai.giangVien', 'nhom.truongNhom', 'nhom.thanhVienNhoms.sinhVien.lop'])
            ->whereIn('MaHoSo', $maHoSos)
            ->get();

        $gvhdMaGVs = [];
        $topicBoMons = [];
        foreach ($hoSos as $hs) {
            $gvhd = $hs->nhom?->deTai?->giangVien;
            if ($gvhd) {
                $gvhdMaGVs[] = $gvhd->MaGV;
                if ($gvhd->MaBoMon) {
                    $topicBoMons[] = $gvhd->MaBoMon;
                }
            }
        }
        $gvhdMaGVs = array_unique($gvhdMaGVs);
        $topicBoMons = array_unique($topicBoMons);

        // 2. Xác định giảng viên bị TRÙNG LỊCH BẢO VỆ
        // Hội đồng khác đang diễn ra giao thoa thời gian với [startTime, endTime]
        $conflictingCouncils = HoiDong::where(function($q) use ($startTime, $endTime) {
            $q->whereBetween('ThoiGianBatDau', [$startTime, $endTime])
              ->orWhereBetween('ThoiGianKetThuc', [$startTime, $endTime])
              ->orWhere(function($sub) use ($startTime, $endTime) {
                  $sub->where('ThoiGianBatDau', '<=', $startTime)
                      ->where('ThoiGianKetThuc', '>=', $endTime);
              });
        })->pluck('MaHoiDong');

        $conflictingLecturerMaGVs = ThanhVienHoiDong::whereIn('MaHoiDong', $conflictingCouncils)
            ->pluck('MaGV')
            ->unique()
            ->toArray();

        // 3. Lấy ứng viên giảng viên thuộc Khoa
        $gvQuery = GiangVien::with(['boMon.khoa', 'thanhVienHoiDongs.hoiDong'])
            ->where(function($q) {
                $q->whereNull('TrangThai')
                  ->orWhere('TrangThai', 'Đang công tác')
                  ->orWhere('TrangThai', 'Hoạt động');
            });

        if ($maKhoa) {
            $gvQuery->whereHas('boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa));
        }

        $allCandidates = $gvQuery->get();

        // Lọc bỏ GVHD và GV bị trùng lịch
        $eligiblePool = $allCandidates->reject(function($gv) use ($gvhdMaGVs, $conflictingLecturerMaGVs) {
            return in_array($gv->MaGV, $gvhdMaGVs) || in_array($gv->MaGV, $conflictingLecturerMaGVs);
        });

        // Tính điểm ưu tiên và số hội đồng đã tham gia
        $scoredPool = $eligiblePool->map(function($gv) use ($topicBoMons, $maHocKy) {
            $soHoiDong = $gv->thanhVienHoiDongs
                ->filter(fn($tv) => !$maHocKy || ($tv->hoiDong?->MaHocKy === $maHocKy))
                ->unique('MaHoiDong')
                ->count();

            $hocVi = mb_strtolower($gv->HocVi ?? '');
            $hocHam = mb_strtolower($gv->HocHam ?? '');
            $isTienSiOrHigher = str_contains($hocVi, 'tiến sĩ') || str_contains($hocVi, 'ts') || str_contains($hocHam, 'pgs') || str_contains($hocHam, 'gs');
            $isSameBoMon = in_array($gv->MaBoMon, $topicBoMons);

            return [
                'giangVien'         => $gv,
                'soHoiDong'         => $soHoiDong,
                'isTienSiOrHigher'  => $isTienSiOrHigher,
                'isSameBoMon'       => $isSameBoMon,
            ];
        });

        $assigned = [];
        $warnings = [];

        // 4. Chọn CHỦ TỊCH HỘI ĐỒNG:
        // Ưu tiên: Tiến sĩ / PGS / GS + cùng chuyên môn hoặc thuộc khoa + số hội đồng ít hơn
        $chuTichCandidates = $scoredPool->filter(fn($item) => $item['isTienSiOrHigher'])
            ->sortBy(fn($item) => ($item['isSameBoMon'] ? 0 : 10) + $item['soHoiDong']);

        $chosenChuTich = $chuTichCandidates->first();
        if (!$chosenChuTich) {
            // Dự phòng: Chọn người có số hội đồng ít nhất
            $chosenChuTich = $scoredPool->sortBy(fn($item) => $item['soHoiDong'])->first();
            $warnings[] = "Khoa tạm thời hết ứng viên có học vị Tiến sĩ khả dụng; hệ thống đề xuất Thạc sĩ có tải công tác thấp nhất làm Chủ tịch.";
        }

        if ($chosenChuTich) {
            $assigned[] = [
                'VaiTro'     => 'Chủ tịch',
                'MaGV'       => $chosenChuTich['giangVien']->MaGV,
                'HoTen'      => $chosenChuTich['giangVien']->HoTen,
                'HocVi'      => $chosenChuTich['giangVien']->HocHam ?? ($chosenChuTich['giangVien']->HocVi ?? 'Giảng viên'),
                'TenBoMon'   => $chosenChuTich['giangVien']->boMon?->TenBoMon ?? 'Bộ môn',
                'SoHoiDong'  => $chosenChuTich['soHoiDong'],
                'LyDo'       => "Học hàm/học vị cao (" . ($chosenChuTich['giangVien']->HocVi ?? 'TS') . "), tải hiện tại: {$chosenChuTich['soHoiDong']} HĐ.",
            ];
            $scoredPool = $scoredPool->reject(fn($item) => $item['giangVien']->MaGV === $chosenChuTich['giangVien']->MaGV);
        }

        // 5. Chọn THƯ KÝ HỘI ĐỒNG:
        // Trách nhiệm: lập phiếu chấm từ đề cương, ghi chép biên bản.
        // Ưu tiên: Giảng viên có số hội đồng ít nhất trong nhóm khả dụng.
        $thuKyCandidates = $scoredPool->sortBy(fn($item) => ($item['isSameBoMon'] ? 0 : 5) + $item['soHoiDong']);
        $chosenThuKy = $thuKyCandidates->first();

        if ($chosenThuKy) {
            $assigned[] = [
                'VaiTro'     => 'Thư ký',
                'MaGV'       => $chosenThuKy['giangVien']->MaGV,
                'HoTen'      => $chosenThuKy['giangVien']->HoTen,
                'HocVi'      => $chosenThuKy['giangVien']->HocHam ?? ($chosenThuKy['giangVien']->HocVi ?? 'Giảng viên'),
                'TenBoMon'   => $chosenThuKy['giangVien']->boMon?->TenBoMon ?? 'Bộ môn',
                'SoHoiDong'  => $chosenThuKy['soHoiDong'],
                'LyDo'       => "Phụ trách chuẩn bị phiếu chấm & biên bản theo quy trình mới, tải hiện tại: {$chosenThuKy['soHoiDong']} HĐ.",
            ];
            $scoredPool = $scoredPool->reject(fn($item) => $item['giangVien']->MaGV === $chosenThuKy['giangVien']->MaGV);
        }

        // 6. Chọn PHẢN BIỆN HỘI ĐỒNG:
        // Ưu tiên: Cùng bộ môn / chuyên môn sâu với đề tài + tải thấp
        $phanBienCandidates = $scoredPool->sortBy(fn($item) => ($item['isSameBoMon'] ? 0 : 20) + $item['soHoiDong']);
        $chosenPhanBien = $phanBienCandidates->first();

        if ($chosenPhanBien) {
            $assigned[] = [
                'VaiTro'     => 'Phản biện',
                'MaGV'       => $chosenPhanBien['giangVien']->MaGV,
                'HoTen'      => $chosenPhanBien['giangVien']->HoTen,
                'HocVi'      => $chosenPhanBien['giangVien']->HocHam ?? ($chosenPhanBien['giangVien']->HocVi ?? 'Giảng viên'),
                'TenBoMon'   => $chosenPhanBien['giangVien']->boMon?->TenBoMon ?? 'Bộ môn',
                'SoHoiDong'  => $chosenPhanBien['soHoiDong'],
                'LyDo'       => "Chuyên môn phù hợp (" . ($chosenPhanBien['giangVien']->boMon?->TenBoMon ?? 'Khoa') . "), tải hiện tại: {$chosenPhanBien['soHoiDong']} HĐ.",
            ];
            $scoredPool = $scoredPool->reject(fn($item) => $item['giangVien']->MaGV === $chosenPhanBien['giangVien']->MaGV);
        }

        return [
            'success'      => count($assigned) >= 3,
            'thanh_viens'  => $assigned,
            'canh_bao'     => $warnings,
            'da_loai_gvhd' => count($gvhdMaGVs),
            'da_loai_trung_lich' => count($conflictingLecturerMaGVs),
        ];
    }
}
