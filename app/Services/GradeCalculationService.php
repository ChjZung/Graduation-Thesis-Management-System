<?php

namespace App\Services;

use App\Models\PhieuChamDiem;
use App\Models\ThanhVienHoiDong;
use App\Models\KetQuaSinhVien;
use App\Models\HocKy;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\GiangVien;
use App\Models\LichSuCongBoKetQua;
use App\Models\TaiKhoan;
use App\Services\ThongBaoService;
use Illuminate\Support\Facades\DB;

class GradeCalculationService
{
    /**
     * Kiểm tra chi tiết tiến độ chấm điểm của từng Hội đồng và từng Đề tài.
     * Xác định: số phiếu đã hoàn thành, số phiếu nháp, số phiếu còn thiếu, cảnh báo độ lệch điểm.
     */
    public function kiemTraTienDoChamHoiDong(string $maHoiDong): array
    {
        $hoiDong = HoiDong::with([
            'thanhViens.giangVien',
            'hoSoBaoVes.nhom.thanhViens.sinhVien',
            'hoSoBaoVes.nhom.deTai.giangVien',
        ])->find($maHoiDong);

        if (!$hoiDong) {
            return [
                'exists' => false,
                'message' => 'Hội đồng không tồn tại',
            ];
        }

        $allThanhViens = $hoiDong->thanhViens;
        $totalMembers = $allThanhViens->count();
        $thanhVienMap = $allThanhViens->keyBy('MaGV');

        $danhSachHoSo = [];
        $hoiDongDuPhieu = true;
        $tongSoDeTai = $hoiDong->hoSoBaoVes->count();
        $soDeTaiDuPhieu = 0;

        foreach ($hoiDong->hoSoBaoVes as $hoSo) {
            $nhom = $hoSo->nhom;
            $sinhViens = $nhom?->thanhViens->pluck('sinhVien')->filter() ?? collect();

            // Lấy tất cả phiếu chấm của hồ sơ này
            $phieuChams = PhieuChamDiem::with('giangVien')
                ->where('MaHoiDong', $maHoiDong)
                ->where('MaHoSo', $hoSo->MaHoSo)
                ->get();

            // Phân loại giảng viên: đã nộp hoàn thành, lưu nháp, chưa chấm
            $gvHoanThanh = [];
            $gvLuuNhap = [];
            $gvChuaCham = [];

            foreach ($allThanhViens as $tv) {
                $gv = $tv->giangVien;
                $phieusCuaGV = $phieuChams->where('MaGV', $tv->MaGV);

                if ($phieusCuaGV->isEmpty()) {
                    $gvChuaCham[] = [
                        'MaGV' => $tv->MaGV,
                        'HoTen' => $gv?->HoTen ?? $tv->MaGV,
                        'VaiTro' => $tv->VaiTro ?? 'Thành viên Hội đồng',
                    ];
                } else {
                    $tatCaHoanThanh = $phieusCuaGV->every(fn($p) => $p->TrangThai === 'Đã hoàn thành');
                    if ($tatCaHoanThanh) {
                        $gvHoanThanh[] = [
                            'MaGV' => $tv->MaGV,
                            'HoTen' => $gv?->HoTen ?? $tv->MaGV,
                            'VaiTro' => $tv->VaiTro ?? 'Thành viên Hội đồng',
                            'NgayXacNhan' => $phieusCuaGV->max('NgayXacNhan'),
                        ];
                    } else {
                        $gvLuuNhap[] = [
                            'MaGV' => $tv->MaGV,
                            'HoTen' => $gv?->HoTen ?? $tv->MaGV,
                            'VaiTro' => $tv->VaiTro ?? 'Thành viên Hội đồng',
                        ];
                    }
                }
            }

            $countHoanThanh = count($gvHoanThanh);
            $duPhieu = ($totalMembers > 0 && $countHoanThanh === $totalMembers);

            if ($duPhieu) {
                $soDeTaiDuPhieu++;
            } else {
                $hoiDongDuPhieu = false;
            }

            // Điểm theo từng sinh viên và kiểm tra độ lệch điểm giữa các giám khảo
            $chiTietSinhVien = [];
            foreach ($sinhViens as $sv) {
                $diemCacGiamKhao = [];
                $validScores = [];

                foreach ($allThanhViens as $tv) {
                    $p = $phieuChams->where('MaGV', $tv->MaGV)->where('MaSV', $sv->MaSV)->first();
                    $diem = $p ? (float)$p->Diem : null;
                    $isFinal = $p && $p->TrangThai === 'Đã hoàn thành';

                    $diemCacGiamKhao[$tv->MaGV] = [
                        'MaGV' => $tv->MaGV,
                        'TenGV' => $tv->giangVien?->HoTen ?? $tv->MaGV,
                        'VaiTro' => $tv->VaiTro ?? 'Thành viên',
                        'Diem' => $diem,
                        'TrangThai' => $p?->TrangThai ?? 'Chưa chấm',
                        'ChiTietDiem' => $p?->ChiTietDiem ?? [],
                        'NhanXet' => $p?->NhanXet ?? '',
                    ];

                    if ($isFinal && $diem !== null) {
                        $validScores[] = $diem;
                    }
                }

                $diemTB = !empty($validScores) ? round(array_sum($validScores) / count($validScores), 2) : 0.0;
                $minScore = !empty($validScores) ? min($validScores) : 0.0;
                $maxScore = !empty($validScores) ? max($validScores) : 0.0;
                $diff = !empty($validScores) ? round($maxScore - $minScore, 2) : 0.0;
                $canhBaoLech = ($diff >= 1.5 && count($validScores) >= 2);

                $kq = KetQuaSinhVien::where('MaSV', $sv->MaSV)->where('MaHoSo', $hoSo->MaHoSo)->first();

                $chiTietSinhVien[] = [
                    'sinhVien' => $sv,
                    'diemCacGiamKhao' => $diemCacGiamKhao,
                    'validScoresCount' => count($validScores),
                    'diemTB' => $diemTB,
                    'diemChinhThuc' => $kq?->DiemChinhThuc,
                    'trangThaiKetQua' => $kq?->TrangThai ?? ($duPhieu ? 'Đã đủ phiếu hợp lệ' : 'Chưa đủ phiếu'),
                    'minScore' => $minScore,
                    'maxScore' => $maxScore,
                    'diff' => $diff,
                    'canhBaoLech' => $canhBaoLech,
                    'xepLoai' => KetQuaSinhVien::xepLoai($kq?->DiemChinhThuc ?? $diemTB),
                ];
            }

            $danhSachHoSo[] = [
                'hoSo' => $hoSo,
                'nhom' => $nhom,
                'deTai' => $nhom?->deTai,
                'totalMembers' => $totalMembers,
                'countHoanThanh' => $countHoanThanh,
                'duPhieu' => $duPhieu,
                'gvHoanThanh' => $gvHoanThanh,
                'gvLuuNhap' => $gvLuuNhap,
                'gvChuaCham' => $gvChuaCham,
                'chiTietSinhVien' => $chiTietSinhVien,
                'loaiKhoaLuan' => $hoSo->xacDinhLoaiKhoaLuan(),
            ];
        }

        return [
            'exists' => true,
            'hoiDong' => $hoiDong,
            'totalMembers' => $totalMembers,
            'tongSoDeTai' => $tongSoDeTai,
            'soDeTaiDuPhieu' => $soDeTaiDuPhieu,
            'hoiDongDuPhieu' => $hoiDongDuPhieu && ($tongSoDeTai > 0),
            'danhSachHoSo' => $danhSachHoSo,
        ];
    }

    /**
     * Tổng hợp kết quả cho tất cả SV trong Hội đồng sau khi có điểm mới.
     * Quy tắc: Chỉ tổng hợp điểm từ các phiếu đã xác nhận hoàn thành ('Đã hoàn thành').
     * Nếu chưa đủ phiếu: Ghi nhận trạng thái 'Chưa đủ phiếu' và không xác lập điểm chính thức.
     */
    public function tongHopKetQuaHoiDong(string $maHoiDong): array
    {
        $tienDo = $this->kiemTraTienDoChamHoiDong($maHoiDong);
        if (!$tienDo['exists']) {
            return $tienDo;
        }

        $hocKy = HocKy::where('TrangThai', true)->first() ?? HocKy::latest()->first();
        $maHocKy = $hocKy ? $hocKy->MaHocKy : 'HK_DEFAULT';

        foreach ($tienDo['danhSachHoSo'] as $item) {
            $hoSo = $item['hoSo'];
            $duPhieu = $item['duPhieu'];

            // Cập nhật trạng thái kết quả của hồ sơ bảo vệ
            if ($hoSo->TrangThaiKetQua !== 'DaCongBo' && $hoSo->TrangThaiKetQua !== 'DaHoanThanh') {
                if ($duPhieu) {
                    $hoSo->TrangThaiKetQua = 'DaDuPhieu';
                } else {
                    $hoSo->TrangThaiKetQua = 'ChuaDuPhieu';
                }
                $hoSo->save();
            }

            if ($item['nhom']) {
                foreach ($item['nhom']->thanhViens as $tv) {
                    $this->tinhVaLuuDiemSinhVien($tv->MaSV, $maHoiDong, $hoSo, $maHocKy, $duPhieu);
                }
            }
        }

        return $tienDo;
    }

    /**
     * Tính và lưu điểm cho 1 Sinh viên.
     * - Chỉ tính trung bình từ các phiếu 'Đã hoàn thành'.
     * - Capping điểm tối đa 10.0 và làm tròn 2 chữ số thập phân.
     * - Giữ nguyên điểm chính thức nếu đã xác nhận hoặc đã công bố.
     */
    public function tinhVaLuuDiemSinhVien(
        string $maSV,
        string $maHoiDong,
        $hoSo,
        string $maHocKy,
        bool $duPhieu = true
    ): ?KetQuaSinhVien {
        // Chỉ lấy các phiếu đã xác nhận hoàn thành của Hội đồng này
        $validDiems = PhieuChamDiem::where('MaHoiDong', $maHoiDong)
            ->where('MaSV', $maSV)
            ->where('TrangThai', 'Đã hoàn thành')
            ->get();

        $existing = KetQuaSinhVien::where('MaSV', $maSV)
            ->where('MaHocKy', $maHocKy)
            ->first();

        // Nếu chưa có phiếu nào hoàn thành
        if ($validDiems->isEmpty()) {
            if ($existing && $existing->TrangThai !== 'Đã công bố') {
                $existing->TrangThai = 'Chưa đủ phiếu';
                $existing->DiemHoiDongTB = null;
                $existing->save();
            }
            return $existing;
        }

        // Tính điểm trung bình sơ bộ từ các phiếu hợp lệ
        $diemHoiDongTB = round((float)$validDiems->avg('Diem'), 2);
        if ($diemHoiDongTB > 10.0) $diemHoiDongTB = 10.0;

        // Điểm chính thức: Nếu đã được xác nhận trước đó thì giữ nguyên, ngược lại dùng điểm TB sơ bộ
        $diemChinhThuc = $existing?->DiemChinhThuc;
        $diemTongKet = $diemChinhThuc ?? $diemHoiDongTB;

        $loaiKhoaLuan = $hoSo->LoaiKhoaLuan ?? ($validDiems->first()->LoaiKhoaLuan ?? 'KLCN');

        $maKetQua = $existing ? $existing->MaKetQua : ('KQ' . str_pad(KetQuaSinhVien::count() + 1, 4, '0', STR_PAD_LEFT));

        // Trạng thái kết quả
        $trangThai = 'Chưa đủ phiếu';
        if ($existing && in_array($existing->TrangThai, ['Đã công bố', 'Đã xác nhận'])) {
            $trangThai = $existing->TrangThai;
        } elseif ($duPhieu) {
            $trangThai = 'Đã đủ phiếu hợp lệ';
        }

        return KetQuaSinhVien::updateOrCreate(
            ['MaSV' => $maSV, 'MaHocKy' => $maHocKy],
            [
                'MaKetQua'      => $maKetQua,
                'DiemPhanBien'  => null,
                'DiemHoiDongTB' => $diemHoiDongTB,
                'DiemTongKet'   => $diemTongKet,
                'DiemChinhThuc' => $diemChinhThuc,
                'KetQua'        => KetQuaSinhVien::xepLoai($diemTongKet),
                'TrangThai'     => $trangThai,
                'LoaiKhoaLuan'  => $loaiKhoaLuan,
                'MaHoSo'        => $hoSo->MaHoSo ?? null,
                'NgayCham'      => now()->toDateString(),
            ]
        );
    }

    /**
     * Trưởng khoa / Quản lý xác nhận kết quả bảo vệ của Nhóm / Hồ sơ.
     * Cố định điểm sơ bộ thành Điểm chính thức và ghi vết kiểm toán (Audit Trail).
     */
    public function xacNhanKetQua(
        string $maHoSo,
        string $nguoiXacNhan,
        ?array $dieuChinhDiem = null,
        ?string $ghiChu = null
    ): void {
        $hoSo = HoSoBaoVe::with('nhom.thanhViens')->findOrFail($maHoSo);

        DB::transaction(function () use ($hoSo, $nguoiXacNhan, $dieuChinhDiem, $ghiChu) {
            foreach ($hoSo->nhom->thanhViens as $tv) {
                $kq = KetQuaSinhVien::where('MaSV', $tv->MaSV)
                    ->where('MaHoSo', $hoSo->MaHoSo)
                    ->first();

                if (!$kq) continue;

                $diemGoc = $kq->DiemHoiDongTB ?? 0.0;
                $diemMoi = isset($dieuChinhDiem[$tv->MaSV]) ? floatval($dieuChinhDiem[$tv->MaSV]) : $diemGoc;
                if ($diemMoi > 10.0) $diemMoi = 10.0;
                if ($diemMoi < 0.0) $diemMoi = 0.0;

                $lichSu = $kq->LichSuDieuChinh ?? [];
                if ($diemMoi != $diemGoc || !empty($ghiChu)) {
                    $lichSu[] = [
                        'nguoi_thuc_hien' => $nguoiXacNhan,
                        'thoi_gian'       => now()->toDateTimeString(),
                        'diem_cu'         => $diemGoc,
                        'diem_moi'        => $diemMoi,
                        'ly_do'           => $ghiChu ?? 'Xác nhận và điều chỉnh theo quyết định của Hội đồng / Trưởng khoa',
                    ];
                }

                $kq->DiemChinhThuc = $diemMoi;
                $kq->DiemTongKet = $diemMoi;
                $kq->KetQua = KetQuaSinhVien::xepLoai($diemMoi);
                $kq->TrangThai = 'Đã xác nhận';
                $kq->NguoiXacNhan = $nguoiXacNhan;
                $kq->NgayXacNhan = now();
                $kq->GhiChuDieuChinh = $ghiChu;
                $kq->LichSuDieuChinh = $lichSu;
                $kq->save();
            }

            $hoSo->TrangThaiKetQua = 'ChoCongBo';
            $hoSo->save();
        });
    }

    /**
     * Kiểm tra điều kiện công bố cho 1 Đề tài (Hồ sơ bảo vệ).
     * Bắt buộc: Hội đồng phải có thành viên và 100% thành viên đã nộp phiếu 'Đã hoàn thành' cho mọi SV trong nhóm.
     */
    public function kiemTraDieuKienCongBoDeTai(string $maHoSo): array
    {
        $hoSo = HoSoBaoVe::with([
            'nhom.thanhViens.sinhVien',
            'nhom.deTai.giangVien',
            'hoiDong.thanhViens.giangVien',
        ])->find($maHoSo);

        if (!$hoSo) {
            return [
                'du_dieu_kien' => false,
                'ly_do'        => 'Không tìm thấy hồ sơ bảo vệ ' . $maHoSo,
                'ma_ho_so'     => $maHoSo,
            ];
        }

        if (!$hoSo->MaHoiDong || !$hoSo->hoiDong) {
            return [
                'du_dieu_kien' => false,
                'ly_do'        => 'Đề tài chưa được phân công vào Hội đồng bảo vệ nào.',
                'ma_ho_so'     => $maHoSo,
                'ten_de_tai'   => $hoSo->nhom?->deTai?->TenDeTai ?? 'Chưa xác định',
                'ten_nhom'     => $hoSo->nhom?->TenNhom ?? $hoSo->MaNhom,
            ];
        }

        $allMembers = $hoSo->hoiDong->thanhViens;
        $totalMembers = $allMembers->count();

        if ($totalMembers === 0) {
            return [
                'du_dieu_kien' => false,
                'ly_do'        => "Hội đồng {$hoSo->hoiDong->TenHoiDong} chưa có thành viên giám khảo nào.",
                'ma_ho_so'     => $maHoSo,
                'ten_de_tai'   => $hoSo->nhom?->deTai?->TenDeTai ?? 'Chưa xác định',
                'ten_nhom'     => $hoSo->nhom?->TenNhom ?? $hoSo->MaNhom,
            ];
        }

        $phieuChams = PhieuChamDiem::where('MaHoiDong', $hoSo->MaHoiDong)
            ->where('MaHoSo', $hoSo->MaHoSo)
            ->get();

        $gvHoanThanh = [];
        $gvChuaHoanThanh = [];

        foreach ($allMembers as $tv) {
            $phieusCuaGV = $phieuChams->where('MaGV', $tv->MaGV);
            $gv = $tv->giangVien;
            $hoTen = $gv?->HoTen ?? $tv->MaGV;
            $vaiTro = $tv->VaiTro ?? 'Thành viên';

            if ($phieusCuaGV->isEmpty()) {
                $gvChuaHoanThanh[] = [
                    'MaGV'      => $tv->MaGV,
                    'HoTen'     => $hoTen,
                    'VaiTro'    => $vaiTro,
                    'TrangThai' => 'Chưa chấm',
                ];
            } else {
                $tatCaXong = $phieusCuaGV->every(fn($p) => $p->TrangThai === 'Đã hoàn thành');
                if ($tatCaXong) {
                    $gvHoanThanh[] = [
                        'MaGV'   => $tv->MaGV,
                        'HoTen'  => $hoTen,
                        'VaiTro' => $vaiTro,
                    ];
                } else {
                    $gvChuaHoanThanh[] = [
                        'MaGV'      => $tv->MaGV,
                        'HoTen'     => $hoTen,
                        'VaiTro'    => $vaiTro,
                        'TrangThai' => 'Lưu nháp / Chưa ký',
                    ];
                }
            }
        }

        $duDieuKien = ($totalMembers > 0 && count($gvHoanThanh) === $totalMembers);
        $daCongBo = in_array($hoSo->TrangThaiKetQua, ['DaCongBo', 'CoYeuCauChinhSua', 'DaHoanThanh']);

        $lyDo = '';
        if (!$duDieuKien) {
            $danhSachGv = implode(', ', array_map(fn($g) => "{$g['HoTen']} ({$g['TrangThai']})", $gvChuaHoanThanh));
            $lyDo = "Còn " . count($gvChuaHoanThanh) . " giám khảo chưa hoàn thành nộp phiếu: {$danhSachGv}.";
        }

        $sinhViens = [];
        if ($hoSo->nhom) {
            foreach ($hoSo->nhom->thanhViens as $tvn) {
                if ($tvn->sinhVien) {
                    $kq = KetQuaSinhVien::where('MaSV', $tvn->MaSV)->where('MaHoSo', $hoSo->MaHoSo)->first();
                    $sinhViens[] = [
                        'MaSV'          => $tvn->MaSV,
                        'HoTen'         => $tvn->sinhVien->HoTen,
                        'DiemTB'        => $kq?->DiemHoiDongTB,
                        'DiemChinhThuc' => $kq?->DiemChinhThuc ?? $kq?->DiemTongKet,
                        'TrangThai'     => $kq?->TrangThai ?? 'Chưa chấm',
                    ];
                }
            }
        }

        return [
            'du_dieu_kien'       => $duDieuKien,
            'da_cong_bo'         => $daCongBo,
            'ly_do'              => $lyDo,
            'ma_ho_so'           => $hoSo->MaHoSo,
            'ten_de_tai'         => $hoSo->nhom?->deTai?->TenDeTai ?? 'Chưa xác định',
            'ten_nhom'           => $hoSo->nhom?->TenNhom ?? $hoSo->MaNhom,
            'ma_hoi_dong'        => $hoSo->MaHoiDong,
            'ten_hoi_dong'       => $hoSo->hoiDong->TenHoiDong,
            'tong_gv'            => $totalMembers,
            'so_gv_hoan_thanh'   => count($gvHoanThanh),
            'gv_hoan_thanh'      => $gvHoanThanh,
            'gv_chua_hoan_thanh' => $gvChuaHoanThanh,
            'sinh_viens'         => $sinhViens,
        ];
    }

    /**
     * Kiểm tra điều kiện công bố cho toàn bộ 1 Hội đồng.
     */
    public function kiemTraDieuKienCongBoHoiDong(string $maHoiDong): array
    {
        $hoiDong = HoiDong::with(['hoSoBaoVes'])->find($maHoiDong);
        if (!$hoiDong) {
            return [
                'du_dieu_kien' => false,
                'ly_do'        => 'Không tìm thấy Hội đồng bảo vệ ' . $maHoiDong,
                'ma_hoi_dong'  => $maHoiDong,
            ];
        }

        $danhSach = [];
        $tongDeTai = $hoiDong->hoSoBaoVes->count();
        $deTaiDu = 0;
        $deTaiDaCongBo = 0;
        $deTaiChuaDu = [];

        foreach ($hoiDong->hoSoBaoVes as $hoSo) {
            $chk = $this->kiemTraDieuKienCongBoDeTai($hoSo->MaHoSo);
            $danhSach[] = $chk;
            if ($chk['du_dieu_kien']) {
                $deTaiDu++;
            } else {
                $deTaiChuaDu[] = $chk;
            }
            if ($chk['da_cong_bo']) {
                $deTaiDaCongBo++;
            }
        }

        $duDieuKien = ($tongDeTai > 0 && $deTaiDu === $tongDeTai);
        $lyDo = '';
        if ($tongDeTai === 0) {
            $lyDo = "Hội đồng {$hoiDong->TenHoiDong} chưa có nhóm đề tài nào.";
        } elseif (!$duDieuKien) {
            $lyDo = "Hội đồng có {$deTaiDu}/{$tongDeTai} đề tài đủ điều kiện. Còn " . count($deTaiChuaDu) . " đề tài chưa đủ 100% phiếu chấm hợp lệ.";
        }

        return [
            'du_dieu_kien'       => $duDieuKien,
            'ly_do'              => $lyDo,
            'ma_hoi_dong'        => $maHoiDong,
            'ten_hoi_dong'       => $hoiDong->TenHoiDong,
            'tong_de_tai'        => $tongDeTai,
            'de_tai_du'          => $deTaiDu,
            'so_de_tai_chua_du'  => count($deTaiChuaDu),
            'de_tai_chua_du'     => $deTaiChuaDu,
            'de_tai_da_cong_bo'  => $deTaiDaCongBo,
            'danh_sach_de_tai'   => $danhSach,
            'da_cong_bo_toan_bo' => ($tongDeTai > 0 && $deTaiDaCongBo === $tongDeTai),
        ];
    }

    /**
     * Kiểm tra điều kiện công bố cho toàn bộ 1 Học kỳ / Đợt (Toàn khoa).
     */
    public function kiemTraDieuKienCongBoToanDot(string $maHocKy, ?string $maKhoa = null): array
    {
        $hoSoQuery = HoSoBaoVe::whereNotNull('MaHoiDong')
            ->whereHas('hoiDong', fn($q) => $q->where('MaHocKy', $maHocKy))
            ->with(['nhom.thanhViens.sinhVien', 'nhom.deTai.giangVien', 'hoiDong']);

        if ($maKhoa) {
            $hoSoQuery->where(function ($q) use ($maKhoa) {
                $q->whereHas('hoiDong', fn($hd) => $hd->where('MaKhoa', $maKhoa))
                  ->orWhereHas('nhom.deTai.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa));
            });
        }

        $hoSos = $hoSoQuery->get();
        $totalDeTai = $hoSos->count();
        $dsDu = [];
        $dsChuaDu = [];
        $dsDaCongBo = [];
        $totalSV = 0;

        foreach ($hoSos as $hoSo) {
            $chk = $this->kiemTraDieuKienCongBoDeTai($hoSo->MaHoSo);
            $totalSV += count($chk['sinh_viens'] ?? []);
            if ($chk['da_cong_bo']) {
                $dsDaCongBo[] = $chk;
            }
            if ($chk['du_dieu_kien']) {
                $dsDu[] = $chk;
            } else {
                $dsChuaDu[] = $chk;
            }
        }

        return [
            'ma_hoc_ky'            => $maHocKy,
            'tong_de_tai'          => $totalDeTai,
            'tong_sinh_vien'       => $totalSV,
            'so_de_tai_du'         => count($dsDu),
            'so_de_tai_chua_du'    => count($dsChuaDu),
            'so_de_tai_da_cong_bo' => count($dsDaCongBo),
            'de_tai_du'            => $dsDu,
            'de_tai_chua_du'       => $dsChuaDu,
            'de_tai_da_cong_bo'    => $dsDaCongBo,
        ];
    }

    /**
     * Mức 1: Công bố kết quả chính thức cho 1 Đề tài (Hồ sơ bảo vệ).
     */
    public function congBoDeTai(string $maHoSo, string $nguoiCongBo, ?string $maTK = null): array
    {
        $chk = $this->kiemTraDieuKienCongBoDeTai($maHoSo);
        if (!$chk['du_dieu_kien']) {
            return [
                'success' => false,
                'message' => 'Không thể công bố đề tài: ' . $chk['ly_do'],
            ];
        }

        $hoSo = HoSoBaoVe::with([
            'nhom.thanhViens.sinhVien',
            'nhom.deTai.giangVien',
            'hoiDong',
        ])->findOrFail($maHoSo);

        $snapshot = [];

        DB::transaction(function () use ($hoSo, $nguoiCongBo, $maTK, &$snapshot) {
            $maHocKy = $hoSo->hoiDong?->MaHocKy ?? (HocKy::where('TrangThai', true)->value('MaHocKy') ?? 'HK_CURRENT');

            // Cập nhật điểm cho từng sinh viên
            foreach ($hoSo->nhom->thanhViens as $tv) {
                $this->tinhVaLuuDiemSinhVien($tv->MaSV, $hoSo->MaHoiDong, $hoSo, $maHocKy, true);

                $kq = KetQuaSinhVien::where('MaSV', $tv->MaSV)->where('MaHoSo', $hoSo->MaHoSo)->first();
                if ($kq) {
                    $finalScore = $kq->DiemChinhThuc ?? $kq->DiemHoiDongTB ?? 0.0;
                    $kq->DiemChinhThuc = $finalScore;
                    $kq->DiemTongKet = $finalScore;
                    $kq->KetQua = KetQuaSinhVien::xepLoai($finalScore);
                    $kq->TrangThai = 'Đã công bố';
                    $kq->NguoiCongBo = $nguoiCongBo;
                    $kq->NgayCongBo = now();
                    $kq->save();

                    $snapshot[] = [
                        'MaSV'          => $tv->MaSV,
                        'HoTen'         => $tv->sinhVien?->HoTen ?? 'N/A',
                        'DiemHoiDongTB' => $kq->DiemHoiDongTB,
                        'DiemChinhThuc' => $kq->DiemChinhThuc,
                        'KetQua'        => $kq->KetQua,
                    ];
                }
            }

            // Cập nhật trạng thái Hồ sơ
            if ($hoSo->TrangThaiKetQua !== 'DaHoanThanh') {
                $hoSo->TrangThaiKetQua = !empty($hoSo->YeuCauChinhSuaSauBaoVe)
                    ? 'CoYeuCauChinhSua'
                    : 'DaCongBo';
                $hoSo->save();
            }

            // Ghi Audit Log
            LichSuCongBoKetQua::create([
                'LoaiHanhDong'    => 'CONG_BO',
                'PhamVi'          => 'DE_TAI',
                'MaHocKy'         => $maHocKy,
                'MaHoiDong'       => $hoSo->MaHoiDong,
                'MaHoSo'          => $hoSo->MaHoSo,
                'SoLuongDeTai'    => 1,
                'SoLuongSinhVien' => count($snapshot),
                'NguoiThucHien'   => $nguoiCongBo,
                'MaTK'            => $maTK,
                'LyDo'            => 'Trưởng khoa duyệt công bố điểm chính thức đề tài sau khi hoàn thành 100% phiếu chấm hợp lệ.',
                'DuLieuSnapshot'  => [
                    'de_tai'     => $hoSo->nhom?->deTai?->TenDeTai ?? 'N/A',
                    'nhom'       => $hoSo->nhom?->TenNhom ?? 'N/A',
                    'hoi_dong'   => $hoSo->hoiDong?->TenHoiDong ?? 'N/A',
                    'sinh_viens' => $snapshot,
                ],
            ]);

            // Thông báo đến Nhóm Sinh viên
            ThongBaoService::guiDenNhom(
                $hoSo->MaNhom,
                'Công bố kết quả bảo vệ Khóa luận tốt nghiệp',
                "Trưởng khoa đã công bố chính thức kết quả bảo vệ Khóa luận tốt nghiệp cho đề tài '{$hoSo->nhom?->deTai?->TenDeTai}'. Sinh viên có thể vào xem bảng điểm và nhận xét chi tiết.",
                'Kết quả'
            );

            // Thông báo đến GVHD
            if ($hoSo->nhom?->deTai?->MaGV) {
                ThongBaoService::gui(
                    $hoSo->nhom->deTai->MaGV,
                    'Công bố kết quả bảo vệ Khóa luận nhóm hướng dẫn',
                    "Kết quả bảo vệ Khóa luận của nhóm {$hoSo->nhom?->TenNhom} (Đề tài: {$hoSo->nhom?->deTai?->TenDeTai}) đã được Trưởng khoa công bố chính thức.",
                    'Kết quả'
                );
            }

            // Thông báo đến các thành viên Hội đồng
            foreach ($hoSo->hoiDong?->thanhViens ?? [] as $tvHd) {
                ThongBaoService::gui(
                    $tvHd->MaGV,
                    'Kết quả bảo vệ Khóa luận đã được công bố',
                    "Kết quả bảo vệ của đề tài '{$hoSo->nhom?->deTai?->TenDeTai}' thuộc Hội đồng {$hoSo->hoiDong?->TenHoiDong} đã được Trưởng khoa công bố chính thức.",
                    'Kết quả'
                );
            }

            // Thông báo cho Giáo vụ / Admin
            $this->notifyAdmins(
                'Công bố kết quả bảo vệ Khóa luận',
                "Trưởng khoa đã công bố chính thức kết quả cho đề tài '{$hoSo->nhom?->deTai?->TenDeTai}' (Hồ sơ: {$hoSo->MaHoSo})."
            );
        });

        return [
            'success' => true,
            'message' => "Đã công bố chính thức kết quả cho đề tài '{$hoSo->nhom?->deTai?->TenDeTai}' thành công!",
        ];
    }

    /**
     * Mức 2: Công bố kết quả chính thức cho 1 Hội đồng (các đề tài đủ điều kiện).
     */
    public function congBoHoiDong(string $maHoiDong, string $nguoiCongBo, ?string $maTK = null): array
    {
        $chkHd = $this->kiemTraDieuKienCongBoHoiDong($maHoiDong);
        if ($chkHd['de_tai_du'] === 0) {
            return [
                'success' => false,
                'message' => "Không thể công bố Hội đồng: Chưa có đề tài nào đủ 100% phiếu chấm hợp lệ! ({$chkHd['ly_do']})",
            ];
        }

        $hoiDong = HoiDong::with(['hoSoBaoVes'])->findOrFail($maHoiDong);
        $publishedCount = 0;
        $totalSV = 0;
        $allSnapshots = [];

        DB::transaction(function () use ($hoiDong, $maHoiDong, $nguoiCongBo, $maTK, &$publishedCount, &$totalSV, &$allSnapshots) {
            $maHocKy = $hoiDong->MaHocKy ?? (HocKy::where('TrangThai', true)->value('MaHocKy') ?? 'HK_CURRENT');

            foreach ($hoiDong->hoSoBaoVes as $hoSo) {
                $chkDt = $this->kiemTraDieuKienCongBoDeTai($hoSo->MaHoSo);
                if (!$chkDt['du_dieu_kien']) {
                    continue; // Bỏ qua đề tài chưa đủ phiếu
                }

                $hoSo->loadMissing(['nhom.thanhViens.sinhVien', 'nhom.deTai.giangVien']);
                $subSnap = [];

                foreach ($hoSo->nhom->thanhViens as $tv) {
                    $this->tinhVaLuuDiemSinhVien($tv->MaSV, $hoSo->MaHoiDong ?? $maHoiDong, $hoSo, $maHocKy, true);

                    $kq = KetQuaSinhVien::where('MaSV', $tv->MaSV)->where('MaHoSo', $hoSo->MaHoSo)->first();
                    if ($kq) {
                        $finalScore = $kq->DiemChinhThuc ?? $kq->DiemHoiDongTB ?? 0.0;
                        $kq->DiemChinhThuc = $finalScore;
                        $kq->DiemTongKet = $finalScore;
                        $kq->KetQua = KetQuaSinhVien::xepLoai($finalScore);
                        $kq->TrangThai = 'Đã công bố';
                        $kq->NguoiCongBo = $nguoiCongBo;
                        $kq->NgayCongBo = now();
                        $kq->save();

                        $subSnap[] = [
                            'MaSV'          => $tv->MaSV,
                            'HoTen'         => $tv->sinhVien?->HoTen ?? 'N/A',
                            'DiemHoiDongTB' => $kq->DiemHoiDongTB,
                            'DiemChinhThuc' => $kq->DiemChinhThuc,
                            'KetQua'        => $kq->KetQua,
                        ];
                        $totalSV++;
                    }
                }

                if ($hoSo->TrangThaiKetQua !== 'DaHoanThanh') {
                    $hoSo->TrangThaiKetQua = !empty($hoSo->YeuCauChinhSuaSauBaoVe)
                        ? 'CoYeuCauChinhSua'
                        : 'DaCongBo';
                    $hoSo->save();
                }

                $publishedCount++;
                $allSnapshots[] = [
                    'ma_ho_so'   => $hoSo->MaHoSo,
                    'ten_de_tai' => $hoSo->nhom?->deTai?->TenDeTai ?? 'N/A',
                    'sinh_viens' => $subSnap,
                ];

                // Thông báo sinh viên nhóm
                ThongBaoService::guiDenNhom(
                    $hoSo->MaNhom,
                    'Công bố kết quả bảo vệ Khóa luận tốt nghiệp',
                    "Trưởng khoa đã công bố chính thức kết quả bảo vệ Khóa luận tốt nghiệp cho Hội đồng {$hoiDong->TenHoiDong}. Sinh viên có thể vào xem bảng điểm và nhận xét chi tiết.",
                    'Kết quả'
                );

                // Thông báo GVHD
                if ($hoSo->nhom?->deTai?->MaGV) {
                    ThongBaoService::gui(
                        $hoSo->nhom->deTai->MaGV,
                        'Công bố kết quả bảo vệ Khóa luận nhóm hướng dẫn',
                        "Kết quả bảo vệ Khóa luận của nhóm {$hoSo->nhom?->TenNhom} (Đề tài: {$hoSo->nhom?->deTai?->TenDeTai}) đã được Trưởng khoa công bố chính thức.",
                        'Kết quả'
                    );
                }
            }

            // Ghi Audit Log cho toàn Hội đồng
            LichSuCongBoKetQua::create([
                'LoaiHanhDong'    => 'CONG_BO',
                'PhamVi'          => 'HOI_DONG',
                'MaHocKy'         => $maHocKy,
                'MaHoiDong'       => $maHoiDong,
                'SoLuongDeTai'    => $publishedCount,
                'SoLuongSinhVien' => $totalSV,
                'NguoiThucHien'   => $nguoiCongBo,
                'MaTK'            => $maTK,
                'LyDo'            => "Trưởng khoa công bố kết quả bảo vệ cho {$publishedCount} đề tài đủ điều kiện thuộc Hội đồng {$hoiDong->TenHoiDong}.",
                'DuLieuSnapshot'  => [
                    'hoi_dong' => $hoiDong->TenHoiDong,
                    'de_tais'  => $allSnapshots,
                ],
            ]);

            // Thông báo toàn bộ giám khảo Hội đồng
            foreach ($hoiDong->thanhViens ?? [] as $tvHd) {
                ThongBaoService::gui(
                    $tvHd->MaGV,
                    'Công bố kết quả Hội đồng bảo vệ Khóa luận',
                    "Trưởng khoa đã công bố chính thức kết quả bảo vệ của {$publishedCount} đề tài thuộc Hội đồng {$hoiDong->TenHoiDong}.",
                    'Kết quả'
                );
            }

            // Thông báo Giáo vụ / Admin
            $this->notifyAdmins(
                'Công bố kết quả Hội đồng bảo vệ',
                "Trưởng khoa đã công bố kết quả bảo vệ cho {$publishedCount} đề tài thuộc Hội đồng {$hoiDong->TenHoiDong}."
            );
        });

        return [
            'success'        => true,
            'message'        => "Đã công bố chính thức kết quả cho {$publishedCount} đề tài thuộc Hội đồng {$hoiDong->TenHoiDong}!",
            'publishedCount' => $publishedCount,
        ];
    }

    /**
     * Mức 3: Công bố kết quả chính thức cho Toàn Bộ Đợt / Học kỳ.
     */
    public function congBoToanDot(string $maHocKy, string $nguoiCongBo, ?string $maTK = null, ?string $maKhoa = null): array
    {
        $chk = $this->kiemTraDieuKienCongBoToanDot($maHocKy, $maKhoa);
        if ($chk['so_de_tai_du'] === 0) {
            return [
                'success' => false,
                'message' => 'Không thể công bố toàn đợt: Chưa có đề tài nào đủ 100% phiếu chấm hợp lệ để công bố.',
            ];
        }

        $totalDeTaiPublished = 0;
        $totalSVPublished = 0;
        $allSnapshots = [];

        DB::transaction(function () use ($chk, $maHocKy, $nguoiCongBo, $maTK, &$totalDeTaiPublished, &$totalSVPublished, &$allSnapshots) {
            foreach ($chk['de_tai_du'] as $dtInfo) {
                $hoSo = HoSoBaoVe::with(['nhom.thanhViens.sinhVien', 'nhom.deTai.giangVien', 'hoiDong'])->find($dtInfo['ma_ho_so']);
                if (!$hoSo) continue;

                $subSnap = [];
                foreach ($hoSo->nhom->thanhViens as $tv) {
                    $this->tinhVaLuuDiemSinhVien($tv->MaSV, $hoSo->MaHoiDong, $hoSo, $maHocKy, true);

                    $kq = KetQuaSinhVien::where('MaSV', $tv->MaSV)->where('MaHoSo', $hoSo->MaHoSo)->first();
                    if ($kq) {
                        $finalScore = $kq->DiemChinhThuc ?? $kq->DiemHoiDongTB ?? 0.0;
                        $kq->DiemChinhThuc = $finalScore;
                        $kq->DiemTongKet = $finalScore;
                        $kq->KetQua = KetQuaSinhVien::xepLoai($finalScore);
                        $kq->TrangThai = 'Đã công bố';
                        $kq->NguoiCongBo = $nguoiCongBo;
                        $kq->NgayCongBo = now();
                        $kq->save();

                        $subSnap[] = [
                            'MaSV'          => $tv->MaSV,
                            'HoTen'         => $tv->sinhVien?->HoTen ?? 'N/A',
                            'DiemHoiDongTB' => $kq->DiemHoiDongTB,
                            'DiemChinhThuc' => $kq->DiemChinhThuc,
                            'KetQua'        => $kq->KetQua,
                        ];
                        $totalSVPublished++;
                    }
                }

                if ($hoSo->TrangThaiKetQua !== 'DaHoanThanh') {
                    $hoSo->TrangThaiKetQua = !empty($hoSo->YeuCauChinhSuaSauBaoVe)
                        ? 'CoYeuCauChinhSua'
                        : 'DaCongBo';
                    $hoSo->save();
                }

                $totalDeTaiPublished++;
                $allSnapshots[] = [
                    'ma_ho_so'   => $hoSo->MaHoSo,
                    'ten_de_tai' => $hoSo->nhom?->deTai?->TenDeTai ?? 'N/A',
                    'sinh_viens' => $subSnap,
                ];

                // Thông báo sinh viên nhóm
                ThongBaoService::guiDenNhom(
                    $hoSo->MaNhom,
                    'Công bố kết quả bảo vệ Khóa luận tốt nghiệp',
                    "Trưởng khoa đã công bố chính thức kết quả bảo vệ Khóa luận tốt nghiệp đợt học kỳ {$maHocKy}. Sinh viên có thể vào xem bảng điểm và nhận xét chi tiết.",
                    'Kết quả'
                );

                // Thông báo GVHD
                if ($hoSo->nhom?->deTai?->MaGV) {
                    ThongBaoService::gui(
                        $hoSo->nhom->deTai->MaGV,
                        'Công bố kết quả bảo vệ Khóa luận toàn đợt',
                        "Kết quả bảo vệ Khóa luận của nhóm {$hoSo->nhom?->TenNhom} đã được Trưởng khoa công bố chính thức.",
                        'Kết quả'
                    );
                }
            }

            // Ghi Audit Log toàn đợt
            LichSuCongBoKetQua::create([
                'LoaiHanhDong'    => 'CONG_BO',
                'PhamVi'          => 'TOAN_DOT',
                'MaHocKy'         => $maHocKy,
                'SoLuongDeTai'    => $totalDeTaiPublished,
                'SoLuongSinhVien' => $totalSVPublished,
                'NguoiThucHien'   => $nguoiCongBo,
                'MaTK'            => $maTK,
                'LyDo'            => "Trưởng khoa công bố chính thức toàn đợt cho {$totalDeTaiPublished} đề tài đủ điều kiện trong học kỳ {$maHocKy}.",
                'DuLieuSnapshot'  => [
                    'ma_hoc_ky' => $maHocKy,
                    'de_tais'   => $allSnapshots,
                ],
            ]);

            // Thông báo Giáo vụ / Admin
            $this->notifyAdmins(
                'Công bố kết quả Khóa luận toàn đợt',
                "Trưởng khoa đã công bố chính thức kết quả bảo vệ toàn đợt cho {$totalDeTaiPublished} đề tài ({$totalSVPublished} sinh viên) trong học kỳ {$maHocKy}."
            );
        });

        return [
            'success'             => true,
            'message'             => "Đã công bố chính thức toàn đợt thành công cho {$totalDeTaiPublished} đề tài ({$totalSVPublished} sinh viên)!",
            'totalDeTaiPublished' => $totalDeTaiPublished,
            'totalSVPublished'    => $totalSVPublished,
            'soDeTaiChuaDu'       => $chk['so_de_tai_chua_du'],
        ];
    }

    /**
     * Thu hồi công bố kết quả cho 1 Đề tài (Hồ sơ bảo vệ).
     * Bắt buộc có lý do thu hồi, chuyển trạng thái về chưa công bố, lưu audit log.
     */
    public function thuHoiCongBoDeTai(string $maHoSo, string $nguoiThuHoi, string $lyDo, ?string $maTK = null): array
    {
        if (empty(trim($lyDo))) {
            return [
                'success' => false,
                'message' => 'Vui lòng cung cấp lý do thu hồi quyết định công bố kết quả.',
            ];
        }

        $hoSo = HoSoBaoVe::with([
            'nhom.thanhViens.sinhVien',
            'nhom.deTai.giangVien',
            'hoiDong',
        ])->findOrFail($maHoSo);

        $snapshot = [];

        DB::transaction(function () use ($hoSo, $nguoiThuHoi, $lyDo, $maTK, &$snapshot) {
            $maHocKy = $hoSo->hoiDong?->MaHocKy ?? 'HK_CURRENT';

            foreach ($hoSo->nhom->thanhViens as $tv) {
                $kq = KetQuaSinhVien::where('MaSV', $tv->MaSV)->where('MaHoSo', $hoSo->MaHoSo)->first();
                if ($kq) {
                    $snapshot[] = [
                        'MaSV'          => $tv->MaSV,
                        'HoTen'         => $tv->sinhVien?->HoTen ?? 'N/A',
                        'DiemChinhThuc' => $kq->DiemChinhThuc,
                        'TrangThaiCu'   => $kq->TrangThai,
                    ];

                    $kq->TrangThai = 'Đã đủ phiếu hợp lệ';
                    $kq->NgayCongBo = null;
                    $kq->NguoiCongBo = null;
                    $kq->save();
                }
            }

            // Chuyển trạng thái hồ sơ về Chờ công bố
            $hoSo->TrangThaiKetQua = 'DaDuPhieu';
            $hoSo->save();

            // Ghi Audit Log Thu hồi
            LichSuCongBoKetQua::create([
                'LoaiHanhDong'    => 'THU_HOI',
                'PhamVi'          => 'DE_TAI',
                'MaHocKy'         => $maHocKy,
                'MaHoiDong'       => $hoSo->MaHoiDong,
                'MaHoSo'          => $hoSo->MaHoSo,
                'SoLuongDeTai'    => 1,
                'SoLuongSinhVien' => count($snapshot),
                'NguoiThucHien'   => $nguoiThuHoi,
                'MaTK'            => $maTK,
                'LyDo'            => $lyDo,
                'DuLieuSnapshot'  => [
                    'de_tai'     => $hoSo->nhom?->deTai?->TenDeTai ?? 'N/A',
                    'nhom'       => $hoSo->nhom?->TenNhom ?? 'N/A',
                    'sinh_viens' => $snapshot,
                ],
            ]);

            // Thông báo đến Nhóm Sinh viên
            ThongBaoService::guiDenNhom(
                $hoSo->MaNhom,
                'Thông báo thu hồi quyết định công bố kết quả Khóa luận',
                "Trưởng khoa đã THU HỒI quyết định công bố kết quả bảo vệ đề tài '{$hoSo->nhom?->deTai?->TenDeTai}' để tiến hành rà soát, kiểm tra lại. Lý do: {$lyDo}. Kết quả tạm thời được chuyển về trạng thái 'Chưa công bố'.",
                'Cảnh báo'
            );

            // Thông báo đến GVHD
            if ($hoSo->nhom?->deTai?->MaGV) {
                ThongBaoService::gui(
                    $hoSo->nhom->deTai->MaGV,
                    'Thông báo thu hồi công bố kết quả đề tài hướng dẫn',
                    "Trưởng khoa đã thu hồi quyết định công bố kết quả của nhóm {$hoSo->nhom?->TenNhom} (Đề tài: {$hoSo->nhom?->deTai?->TenDeTai}) để thẩm định lại. Lý do: {$lyDo}.",
                    'Cảnh báo'
                );
            }

            // Thông báo Giáo vụ / Admin
            $this->notifyAdmins(
                'Thu hồi quyết định công bố kết quả',
                "Trưởng khoa đã thu hồi công bố đề tài '{$hoSo->nhom?->deTai?->TenDeTai}' (Hồ sơ: {$hoSo->MaHoSo}). Lý do: {$lyDo}."
            );
        });

        return [
            'success' => true,
            'message' => "Đã thu hồi quyết định công bố kết quả cho đề tài '{$hoSo->nhom?->deTai?->TenDeTai}' thành công!",
        ];
    }

    /**
     * Thu hồi công bố kết quả cho toàn bộ 1 Hội đồng.
     */
    public function thuHoiCongBoHoiDong(string $maHoiDong, string $nguoiThuHoi, string $lyDo, ?string $maTK = null): array
    {
        if (empty(trim($lyDo))) {
            return [
                'success' => false,
                'message' => 'Vui lòng cung cấp lý do thu hồi quyết định công bố.',
            ];
        }

        $hoiDong = HoiDong::with(['hoSoBaoVes.nhom.thanhViens.sinhVien', 'hoSoBaoVes.nhom.deTai.giangVien'])->findOrFail($maHoiDong);
        $revertedCount = 0;
        $totalSV = 0;
        $snapshots = [];

        DB::transaction(function () use ($hoiDong, $nguoiThuHoi, $lyDo, $maTK, &$revertedCount, &$totalSV, &$snapshots) {
            $maHocKy = $hoiDong->MaHocKy ?? 'HK_CURRENT';

            foreach ($hoiDong->hoSoBaoVes as $hoSo) {
                if (!in_array($hoSo->TrangThaiKetQua, ['DaCongBo', 'CoYeuCauChinhSua'])) {
                    continue;
                }

                $subSnap = [];
                foreach ($hoSo->nhom->thanhViens as $tv) {
                    $kq = KetQuaSinhVien::where('MaSV', $tv->MaSV)->where('MaHoSo', $hoSo->MaHoSo)->first();
                    if ($kq && $kq->TrangThai === 'Đã công bố') {
                        $kq->TrangThai = 'Đã đủ phiếu hợp lệ';
                        $kq->NgayCongBo = null;
                        $kq->NguoiCongBo = null;
                        $kq->save();

                        $subSnap[] = ['MaSV' => $tv->MaSV, 'HoTen' => $tv->sinhVien?->HoTen];
                        $totalSV++;
                    }
                }

                $hoSo->TrangThaiKetQua = 'DaDuPhieu';
                $hoSo->save();
                $revertedCount++;

                $snapshots[] = [
                    'ma_ho_so'   => $hoSo->MaHoSo,
                    'ten_de_tai' => $hoSo->nhom?->deTai?->TenDeTai ?? 'N/A',
                    'sinh_viens' => $subSnap,
                ];

                ThongBaoService::guiDenNhom(
                    $hoSo->MaNhom,
                    'Thông báo thu hồi quyết định công bố kết quả',
                    "Trưởng khoa đã THU HỒI quyết định công bố kết quả bảo vệ của Hội đồng {$hoiDong->TenHoiDong} để rà soát. Lý do: {$lyDo}.",
                    'Cảnh báo'
                );
            }

            LichSuCongBoKetQua::create([
                'LoaiHanhDong'    => 'THU_HOI',
                'PhamVi'          => 'HOI_DONG',
                'MaHocKy'         => $maHocKy,
                'MaHoiDong'       => $hoiDong->MaHoiDong,
                'SoLuongDeTai'    => $revertedCount,
                'SoLuongSinhVien' => $totalSV,
                'NguoiThucHien'   => $nguoiThuHoi,
                'MaTK'            => $maTK,
                'LyDo'            => $lyDo,
                'DuLieuSnapshot'  => [
                    'hoi_dong' => $hoiDong->TenHoiDong,
                    'de_tais'  => $snapshots,
                ],
            ]);

            $this->notifyAdmins(
                'Thu hồi công bố kết quả Hội đồng',
                "Trưởng khoa đã thu hồi công bố kết quả {$revertedCount} đề tài thuộc Hội đồng {$hoiDong->TenHoiDong}. Lý do: {$lyDo}."
            );
        });

        return [
            'success'       => true,
            'message'       => "Đã thu hồi quyết định công bố cho {$revertedCount} đề tài thuộc Hội đồng {$hoiDong->TenHoiDong}!",
            'revertedCount' => $revertedCount,
        ];
    }

    /**
     * Thu hồi công bố kết quả Toàn Bộ Đợt / Học kỳ.
     */
    public function thuHoiCongBoToanDot(string $maHocKy, string $nguoiThuHoi, string $lyDo, ?string $maTK = null, ?string $maKhoa = null): array
    {
        if (empty(trim($lyDo))) {
            return [
                'success' => false,
                'message' => 'Vui lòng cung cấp lý do thu hồi quyết định công bố.',
            ];
        }

        $hoSoQuery = HoSoBaoVe::whereNotNull('MaHoiDong')
            ->whereIn('TrangThaiKetQua', ['DaCongBo', 'CoYeuCauChinhSua'])
            ->whereHas('hoiDong', fn($q) => $q->where('MaHocKy', $maHocKy))
            ->with(['nhom.thanhViens.sinhVien', 'nhom.deTai.giangVien', 'hoiDong']);

        if ($maKhoa) {
            $hoSoQuery->where(function ($q) use ($maKhoa) {
                $q->whereHas('hoiDong', fn($hd) => $hd->where('MaKhoa', $maKhoa))
                  ->orWhereHas('nhom.deTai.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa));
            });
        }

        $hoSos = $hoSoQuery->get();
        $revertedCount = 0;
        $totalSV = 0;
        $snapshots = [];

        DB::transaction(function () use ($hoSos, $maHocKy, $nguoiThuHoi, $lyDo, $maTK, &$revertedCount, &$totalSV, &$snapshots) {
            foreach ($hoSos as $hoSo) {
                $subSnap = [];
                foreach ($hoSo->nhom->thanhViens as $tv) {
                    $kq = KetQuaSinhVien::where('MaSV', $tv->MaSV)->where('MaHoSo', $hoSo->MaHoSo)->first();
                    if ($kq && $kq->TrangThai === 'Đã công bố') {
                        $kq->TrangThai = 'Đã đủ phiếu hợp lệ';
                        $kq->NgayCongBo = null;
                        $kq->NguoiCongBo = null;
                        $kq->save();

                        $subSnap[] = ['MaSV' => $tv->MaSV, 'HoTen' => $tv->sinhVien?->HoTen];
                        $totalSV++;
                    }
                }

                $hoSo->TrangThaiKetQua = 'DaDuPhieu';
                $hoSo->save();
                $revertedCount++;

                $snapshots[] = [
                    'ma_ho_so'   => $hoSo->MaHoSo,
                    'ten_de_tai' => $hoSo->nhom?->deTai?->TenDeTai ?? 'N/A',
                    'sinh_viens' => $subSnap,
                ];

                ThongBaoService::guiDenNhom(
                    $hoSo->MaNhom,
                    'Thông báo thu hồi công bố kết quả toàn đợt',
                    "Trưởng khoa đã THU HỒI quyết định công bố kết quả bảo vệ toàn đợt học kỳ {$maHocKy} để kiểm tra, chuẩn hóa dữ liệu. Lý do: {$lyDo}.",
                    'Cảnh báo'
                );
            }

            LichSuCongBoKetQua::create([
                'LoaiHanhDong'    => 'THU_HOI',
                'PhamVi'          => 'TOAN_DOT',
                'MaHocKy'         => $maHocKy,
                'SoLuongDeTai'    => $revertedCount,
                'SoLuongSinhVien' => $totalSV,
                'NguoiThucHien'   => $nguoiThuHoi,
                'MaTK'            => $maTK,
                'LyDo'            => $lyDo,
                'DuLieuSnapshot'  => [
                    'ma_hoc_ky' => $maHocKy,
                    'de_tais'   => $snapshots,
                ],
            ]);

            $this->notifyAdmins(
                'Thu hồi công bố kết quả toàn đợt',
                "Trưởng khoa đã thu hồi công bố kết quả toàn đợt cho {$revertedCount} đề tài trong học kỳ {$maHocKy}. Lý do: {$lyDo}."
            );
        });

        return [
            'success'       => true,
            'message'       => "Đã thu hồi quyết định công bố toàn đợt cho {$revertedCount} đề tài ({$totalSV} sinh viên) thành công!",
            'revertedCount' => $revertedCount,
            'totalSV'       => $totalSV,
        ];
    }

    /**
     * Lấy chi tiết điểm Rubric của 1 đề tài cho Bảng 3 (Modal / API).
     */
    public function getChiTietDiemRubric(string $maHoSo): array
    {
        $hoSo = HoSoBaoVe::with([
            'nhom.thanhViens.sinhVien.lop',
            'nhom.deTai.giangVien.boMon',
            'hoiDong.thanhViens.giangVien.boMon',
        ])->findOrFail($maHoSo);

        $hoiDong = $hoSo->hoiDong;
        $loaiKL = $hoSo->xacDinhLoaiKhoaLuan();
        $rubric = ($loaiKL === 'KLKS') ? PhieuChamDiem::getRubricKLKS() : PhieuChamDiem::getRubricKLCN();

        $allMembers = $hoiDong?->thanhViens ?? collect();
        $roleOrder = ['Chủ tịch' => 1, 'Thư ký' => 2, 'Phản biện' => 3, 'Ủy viên' => 4];
        $sortedMembers = $allMembers->sortBy(fn($tv) => $roleOrder[$tv->VaiTro] ?? 5);

        $phieus = PhieuChamDiem::with(['giangVien'])
            ->where('MaHoSo', $maHoSo)
            ->where('MaHoiDong', $hoSo->MaHoiDong)
            ->get();

        $chiTietSinhVien = [];
        foreach ($hoSo->nhom?->thanhViens ?? [] as $tvn) {
            $sv = $tvn->sinhVien;
            if (!$sv) continue;

            $kq = KetQuaSinhVien::where('MaSV', $sv->MaSV)->where('MaHoSo', $maHoSo)->first();
            $diemGiamKhaos = [];
            $validScores = [];

            foreach ($sortedMembers as $tv) {
                $p = $phieus->where('MaGV', $tv->MaGV)->where('MaSV', $sv->MaSV)->first();
                $diem = $p ? (float)$p->Diem : null;
                $isCompleted = ($p && $p->TrangThai === 'Đã hoàn thành');

                if ($isCompleted && $diem !== null) {
                    $validScores[] = $diem;
                }

                $diemGiamKhaos[] = [
                    'MaGV'        => $tv->MaGV,
                    'HoTen'       => $tv->giangVien?->HoTen ?? $tv->MaGV,
                    'VaiTro'      => $tv->VaiTro ?? 'Thành viên',
                    'Diem'        => $diem,
                    'TrangThai'   => $p?->TrangThai ?? 'Chưa chấm',
                    'ChiTietDiem' => $p?->ChiTietDiem ?? [],
                    'NhanXet'     => $p?->NhanXet ?? '',
                    'ChuKy'       => $p?->ChuKy ?? null,
                ];
            }

            $diemTB = !empty($validScores) ? round(array_sum($validScores) / count($validScores), 2) : 0.0;
            $diemChinhThuc = $kq?->DiemChinhThuc ?? $kq?->DiemTongKet ?? ($kq?->TrangThai === 'Đã công bố' ? $diemTB : null);

            $chiTietSinhVien[] = [
                'sinhVien'       => $sv,
                'diemGiamKhaos'  => $diemGiamKhaos,
                'diemTB'         => $diemTB,
                'diemChinhThuc'  => $diemChinhThuc,
                'xepLoai'        => KetQuaSinhVien::xepLoai($diemChinhThuc ?? $diemTB),
                'trangThai'      => $kq?->TrangThai ?? 'Chưa có điểm',
                'nhanXetChung'   => $kq?->NhanXetChung ?? '',
            ];
        }

        return [
            'hoSo'            => $hoSo,
            'hoiDong'         => $hoiDong,
            'loaiKL'          => $loaiKL,
            'rubric'          => $rubric,
            'sortedMembers'   => $sortedMembers,
            'chiTietSinhVien' => $chiTietSinhVien,
        ];
    }

    /**
     * Gửi thông báo đến tất cả tài khoản Quản trị / Giáo vụ (VT01).
     */
    public function notifyAdmins(string $tieuDe, string $noiDung, string $loai = 'Kết quả'): void
    {
        try {
            $admins = TaiKhoan::where('MaVaiTro', 'VT01')->get();
            foreach ($admins as $adm) {
                ThongBaoService::guiDen($adm->MaTK, $tieuDe, $noiDung, $loai);
            }
        } catch (\Throwable $e) {
            // Không làm gián đoạn transaction nếu lỗi gửi thông báo
        }
    }

    /**
     * Trưởng khoa Công bố kết quả chính thức của toàn Hội đồng.
     * (Giữ alias cho tương thích ngược).
     */
    public function congBoKetQua(string $maHoiDong, string $nguoiCongBo): void
    {
        $this->congBoHoiDong($maHoiDong, $nguoiCongBo);
    }

    /**
     * Gửi thông báo nhắc nhở các Giảng viên chưa hoàn thành phiếu chấm điểm.
     */
    public function guiNhacNhoChamDiem(string $maHoiDong, ?string $maHoSo = null): int
    {
        $tienDo = $this->kiemTraTienDoChamHoiDong($maHoiDong);
        if (!$tienDo['exists']) return 0;

        $sentCount = 0;
        $hoiDong = $tienDo['hoiDong'];

        foreach ($tienDo['danhSachHoSo'] as $item) {
            if ($maHoSo && $item['hoSo']->MaHoSo !== $maHoSo) {
                continue;
            }

            $uncompletedJudges = array_merge($item['gvChuaCham'], $item['gvLuuNhap']);
            foreach ($uncompletedJudges as $judge) {
                ThongBaoService::gui(
                    $judge['MaGV'],
                    'Nhắc nhở hoàn thành Phiếu chấm điểm Khóa luận',
                    "Kính gửi Thầy/Cô, xin vui lòng kiểm tra và hoàn tất phiếu chấm điểm cho đề tài '{$item['deTai']->TenDeTai}' thuộc Hội đồng {$hoiDong->TenHoiDong}.",
                    'Nhắc nhở'
                );
                $sentCount++;
            }
        }

        return $sentCount;
    }
}
