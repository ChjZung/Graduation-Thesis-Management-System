<?php

namespace App\Services;

use App\Models\PhieuChamDiem;
use App\Models\ThanhVienHoiDong;
use App\Models\KetQuaSinhVien;
use App\Models\HocKy;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\GiangVien;
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
     * Trưởng khoa Công bố kết quả chính thức của toàn Hội đồng hoặc từng Đề tài.
     * Cập nhật trạng thái 'Đã công bố', đóng băng dữ liệu và phát thông báo.
     */
    public function congBoKetQua(string $maHoiDong, string $nguoiCongBo): void
    {
        $hoiDong = HoiDong::with([
            'hoSoBaoVes.nhom.thanhViens.sinhVien',
            'hoSoBaoVes.nhom.deTai.giangVien',
        ])->findOrFail($maHoiDong);

        DB::transaction(function () use ($hoiDong, $nguoiCongBo) {
            foreach ($hoiDong->hoSoBaoVes as $hoSo) {
                // Khóa điểm chính thức cho tất cả sinh viên
                foreach ($hoSo->nhom->thanhViens as $tv) {
                    $kq = KetQuaSinhVien::where('MaSV', $tv->MaSV)
                        ->where('MaHoSo', $hoSo->MaHoSo)
                        ->first();

                    if ($kq) {
                        $finalScore = $kq->DiemChinhThuc ?? $kq->DiemHoiDongTB ?? 0.0;
                        $kq->DiemChinhThuc = $finalScore;
                        $kq->DiemTongKet = $finalScore;
                        $kq->KetQua = KetQuaSinhVien::xepLoai($finalScore);
                        $kq->TrangThai = 'Đã công bố';
                        $kq->NguoiCongBo = $nguoiCongBo;
                        $kq->NgayCongBo = now();
                        $kq->save();
                    }
                }

                // Cập nhật trạng thái hồ sơ
                if ($hoSo->TrangThaiKetQua !== 'DaHoanThanh') {
                    $hoSo->TrangThaiKetQua = !empty($hoSo->YeuCauChinhSuaSauBaoVe)
                        ? 'CoYeuCauChinhSua'
                        : 'DaCongBo';
                    $hoSo->save();
                }

                // Gửi thông báo đến từng sinh viên của nhóm
                ThongBaoService::guiDenNhom(
                    $hoSo->MaNhom,
                    'Công bố kết quả bảo vệ Khóa luận tốt nghiệp',
                    "Trưởng khoa đã công bố chính thức kết quả bảo vệ Khóa luận tốt nghiệp cho Hội đồng {$hoiDong->TenHoiDong}. Sinh viên có thể vào xem bảng điểm và nhận xét chi tiết.",
                    'Kết quả'
                );

                // Gửi thông báo đến Giảng viên hướng dẫn
                if ($hoSo->nhom?->deTai?->MaGV) {
                    ThongBaoService::gui(
                        $hoSo->nhom->deTai->MaGV,
                        'Công bố kết quả bảo vệ Khóa luận nhóm hướng dẫn',
                        "Kết quả bảo vệ Khóa luận của nhóm {$hoSo->nhom->TenNhom} (Đề tài: {$hoSo->nhom->deTai->TenDeTai}) đã được công bố chính thức bởi Trưởng khoa.",
                        'Kết quả'
                    );
                }
            }
        });
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
