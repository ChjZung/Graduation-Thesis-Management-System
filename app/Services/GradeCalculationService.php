<?php

namespace App\Services;

use App\Models\ChiTietDiemHoiDong;
use App\Models\ThanhVienHoiDong;
use App\Models\KetQuaSinhVien;
use App\Models\HocKy;

class GradeCalculationService
{
    // Trọng số điểm (từ config để dễ thay đổi sau)
    const WEIGHT_GVHD   = 0.30;
    const WEIGHT_PB     = 0.30;
    const WEIGHT_HD     = 0.40;

    /**
     * Tổng hợp kết quả cho tất cả SV trong Hội đồng sau khi có điểm mới.
     */
    public function tongHopKetQuaHoiDong(string $maHoiDong): void
    {
        $hoiDong = \App\Models\HoiDong::with([
            'hoSoBaoVes.nhom.thanhViens' => fn($q) => $q->where('TrangThai', 'da_tham_gia'),
        ])->find($maHoiDong);

        if (!$hoiDong) return;

        $hocKy = HocKy::where('TrangThai', true)->first();
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
     */
    private function tinhVaLuuDiemSinhVien(
        string $maSV,
        string $maHoiDong,
        $hoSo,
        string $maHocKy
    ): void {
        // Điểm trung bình từ tất cả GV trong HĐ cho SV này
        $allDiems = ChiTietDiemHoiDong::where('MaHoiDong', $maHoiDong)
            ->where('MaSV', $maSV)->get();

        if ($allDiems->isEmpty()) return;

        $diemHoiDongTB = round($allDiems->avg('Diem'), 2);

        // BƯỚC 6 FIX: Điểm GVHD lấy từ ChamDiem — 0 nếu chưa chấm
        // (không hardcode default 8)
        $diemGVHD = \App\Models\ChamDiem::where('MaSV', $maSV)->value('DiemHuongDan') ?? 0;

        // Điểm GV Phản biện trong HĐ
        $maGVPB = ThanhVienHoiDong::where('MaHoiDong', $maHoiDong)
            ->where('VaiTro', 'Phản biện')->value('MaGV');

        $diemPB = $maGVPB
            ? (ChiTietDiemHoiDong::where('MaHoiDong', $maHoiDong)
                ->where('MaGV', $maGVPB)->where('MaSV', $maSV)->value('Diem') ?? 0)
            : 0;

        // Tính điểm tổng kết theo công thức đã cấu hình trong constants
        $diemTongKet = self::tinhTongKet($diemGVHD, $diemPB, $diemHoiDongTB);

        KetQuaSinhVien::updateOrCreate(
            ['MaSV' => $maSV, 'MaHocKy' => $maHocKy],
            [
                'MaKetQua'      => 'KQ' . str_pad(KetQuaSinhVien::count() + 1, 3, '0', STR_PAD_LEFT),
                'DiemHuongDan'  => $diemGVHD,
                'DiemPhanBien'  => $diemPB,
                'DiemHoiDongTB' => $diemHoiDongTB,
                'DiemTongKet'   => $diemTongKet,
                'KetQua'        => KetQuaSinhVien::xepLoai($diemTongKet),
                'NgayCham'      => now()->toDateString(),
            ]
        );
    }

    /**
     * Công thức tính điểm tổng kết.
     */
    public static function tinhTongKet(float $diemGVHD, float $diemPB, float $diemHDTB): float
    {
        return round(
            ($diemGVHD * self::WEIGHT_GVHD) +
            ($diemPB   * self::WEIGHT_PB) +
            ($diemHDTB * self::WEIGHT_HD),
            2
        );
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
            $diem >= 6.0 => 'Trung bình',
            default      => 'Không đạt',
        };
    }
}
