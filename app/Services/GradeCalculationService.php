<?php

namespace App\Services;

use App\Models\PhieuChamDiem;
use App\Models\ThanhVienHoiDong;
use App\Models\KetQuaSinhVien;
use App\Models\HocKy;
use App\Models\HoiDong;

class GradeCalculationService
{
    /**
     * Tổng hợp kết quả cho tất cả SV trong Hội đồng sau khi có điểm mới.
     * QUY CHẾ KHOA CNTT - HUIT: Điểm khóa luận tốt nghiệp 100% do Hội đồng chấm.
     */
    public function tongHopKetQuaHoiDong(string $maHoiDong): void
    {
        $hoiDong = HoiDong::with([
            'hoSoBaoVes.nhom.thanhViens',
        ])->find($maHoiDong);

        if (!$hoiDong) return;

        $hocKy = HocKy::where('TrangThai', true)->first() ?? HocKy::latest()->first();
        if (!$hocKy) return;

        foreach ($hoiDong->hoSoBaoVes as $hoSo) {
            if (!$hoSo->nhom) continue;

            foreach ($hoSo->nhom->thanhViens as $tv) {
                $this->tinhVaLuuDiemSinhVien($tv->MaSV, $maHoiDong, $hoSo, $hocKy->MaHocKy);
            }
        }
    }

    /**
     * Tính và lưu điểm tổng kết cho 1 Sinh viên.
     * Điểm tổng kết = Điểm trung bình các thành viên Hội đồng chấm cho sinh viên.
     */
    public function tinhVaLuuDiemSinhVien(
        string $maSV,
        string $maHoiDong,
        $hoSo,
        string $maHocKy
    ): void {
        // Lấy tất cả điểm phiếu chấm từ các thành viên trong Hội đồng cho SV này
        $allDiems = PhieuChamDiem::where('MaHoiDong', $maHoiDong)
            ->where('MaSV', $maSV)
            ->get();

        if ($allDiems->isEmpty()) return;

        $diemHoiDongTB = round((float)$allDiems->avg('Diem'), 2);

        // Điểm khóa luận 100% từ Hội đồng chấm bảo vệ
        $diemTongKet = $diemHoiDongTB;

        // Nhận diện loại khóa luận (KLCN hoặc KLKS)
        $loaiKhoaLuan = $allDiems->first()->LoaiKhoaLuan ?? 'KLCN';

        $existing = KetQuaSinhVien::where('MaSV', $maSV)->where('MaHocKy', $maHocKy)->first();
        $maKetQua = $existing ? $existing->MaKetQua : ('KQ' . str_pad(KetQuaSinhVien::count() + 1, 4, '0', STR_PAD_LEFT));

        KetQuaSinhVien::updateOrCreate(
            ['MaSV' => $maSV, 'MaHocKy' => $maHocKy],
            [
                'MaKetQua'      => $maKetQua,
                'DiemPhanBien'  => null,
                'DiemHoiDongTB' => $diemHoiDongTB,
                'DiemTongKet'   => $diemTongKet,
                'KetQua'        => KetQuaSinhVien::xepLoai($diemTongKet),
                'LoaiKhoaLuan'  => $loaiKhoaLuan,
                'MaHoSo'        => $hoSo->MaHoSo ?? null,
                'NgayCham'      => now()->toDateString(),
            ]
        );
    }

    /**
     * Công thức tính điểm tổng kết khóa luận (100% Hội đồng chấm).
     */
    public static function tinhTongKet(float $diemHDTB): float
    {
        return round($diemHDTB, 2);
    }

    /**
     * Xếp loại học lực theo điểm tổng kết.
     */
    public static function xepLoai(float $diem): string
    {
        return match(true) {
            $diem >= 9.0 => 'Xuất sắc',
            $diem >= 8.0 => 'Giỏi',
            $diem >= 7.0 => 'Khá',
            $diem >= 5.5 => 'Trung bình',
            default      => 'Không đạt',
        };
    }
}
