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
     * Thống kê số lượng và vai trò Hội đồng của từng giảng viên trong một Học kỳ cụ thể.
     * Quy tắc:
     * - Chỉ đếm các hội đồng thuộc MaHocKy đã chỉ định (không cộng dồn kỳ cũ).
     * - Mỗi hội đồng chỉ tính 1 lần / giảng viên (COUNT DISTINCT HoiDong.MaHoiDong).
     * - Loại bỏ hội đồng đã hủy (TrangThai = 'Đã hủy').
     * - Loại trừ hội đồng đang sửa/xét ($excludeHoiDongId).
     * - Tối ưu: Chỉ dùng đúng 2 truy vấn gom nhóm GROUP BY, không N+1.
     */
    public function getFacultyCouncilStatsBySemester(string $maHocKy, ?string $excludeHoiDongId = null): array
    {
        $hocKy = HocKy::find($maHocKy);
        $tenHocKy = $hocKy ? $hocKy->TenDayDu : $maHocKy;

        // Query 1: Tổng số hội đồng distinct theo từng MaGV
        $counts = ThanhVienHoiDong::query()
            ->join('HoiDong', 'ThanhVienHoiDong.MaHoiDong', '=', 'HoiDong.MaHoiDong')
            ->where('HoiDong.MaHocKy', $maHocKy)
            ->where('HoiDong.TrangThai', '!=', 'Đã hủy')
            ->when($excludeHoiDongId, fn($q) => $q->where('HoiDong.MaHoiDong', '!=', $excludeHoiDongId))
            ->select('ThanhVienHoiDong.MaGV', DB::raw('COUNT(DISTINCT HoiDong.MaHoiDong) as so_hoi_dong'))
            ->groupBy('ThanhVienHoiDong.MaGV')
            ->pluck('so_hoi_dong', 'MaGV')
            ->toArray();

        // Query 2: Gom nhóm số lần theo vai trò của từng MaGV
        $roleRows = ThanhVienHoiDong::query()
            ->join('HoiDong', 'ThanhVienHoiDong.MaHoiDong', '=', 'HoiDong.MaHoiDong')
            ->where('HoiDong.MaHocKy', $maHocKy)
            ->where('HoiDong.TrangThai', '!=', 'Đã hủy')
            ->when($excludeHoiDongId, fn($q) => $q->where('HoiDong.MaHoiDong', '!=', $excludeHoiDongId))
            ->select('ThanhVienHoiDong.MaGV', 'ThanhVienHoiDong.VaiTro', DB::raw('COUNT(DISTINCT HoiDong.MaHoiDong) as so_lan'))
            ->groupBy('ThanhVienHoiDong.MaGV', 'ThanhVienHoiDong.VaiTro')
            ->get();

        $rolesByFaculty = [];
        foreach ($roleRows as $row) {
            $rolesByFaculty[$row->MaGV][$row->VaiTro] = (int)$row->so_lan;
        }

        // Lấy toàn bộ MaGV đang công tác để đảm bảo mọi GV đều có key
        $allMaGVs = GiangVien::pluck('MaGV');
        $result = [];

        foreach ($allMaGVs as $maGV) {
            $total = (int)($counts[$maGV] ?? 0);
            $facultyRoles = $rolesByFaculty[$maGV] ?? [];

            // Chuẩn hóa tên vai trò cho hiển thị ngắn gọn
            $roleParts = [];
            foreach ($facultyRoles as $vt => $sl) {
                $shortRole = match($vt) {
                    'Chủ tịch' => 'Chủ tịch',
                    'Ủy viên phản biện', 'Phản biện' => 'Phản biện',
                    'Ủy viên thư ký', 'Thư ký' => 'Thư ký',
                    default => 'Ủy viên',
                };
                $roleParts[] = "{$sl} {$shortRole}";
            }
            $roleText = !empty($roleParts) ? implode(', ', $roleParts) : '';

            $tooltip = $total > 0
                ? "Đã tham gia {$total} hội đồng trong {$tenHocKy}" . ($roleText ? " ({$roleText})" : '')
                : "Chưa tham gia hội đồng nào trong {$tenHocKy}";

            $result[$maGV] = [
                'total'    => $total,
                'roles'    => $facultyRoles,
                'roleText' => $roleText,
                'tooltip'  => $tooltip,
            ];
        }

        return $result;
    }

    /**
     * Thống kê số lượng hội đồng của từng giảng viên theo TẤT CẢ các học kỳ trong hệ thống.
     * Trả về mảng map [MaHocKy => ['tenHocKy' => ..., 'faculties' => [...]]]
     * phục vụ chuyển đổi học kỳ linh hoạt ở frontend JS.
     */
    public function getAllSemestersFacultyCouncilStats(?string $excludeHoiDongId = null): array
    {
        $hocKys = HocKy::all();
        $allStats = [];

        foreach ($hocKys as $hk) {
            $allStats[$hk->MaHocKy] = [
                'tenHocKy'  => $hk->TenDayDu,
                'faculties' => $this->getFacultyCouncilStatsBySemester($hk->MaHocKy, $excludeHoiDongId),
            ];
        }

        return $allStats;
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
}
