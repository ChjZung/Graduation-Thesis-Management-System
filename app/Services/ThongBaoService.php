<?php

namespace App\Services;

use App\Models\NguoiNhanThongBao;
use App\Models\ThanhVienNhom;
use App\Models\GiangVien;
use App\Models\TaiKhoan;

class ThongBaoService
{
    /**
     * Gửi thông báo đến một tài khoản cụ thể
     */
    public static function guiDen(string $maTK, string $tieuDe, string $noiDung, string $loai = 'Hệ thống', ?string $duongDan = null): void
    {
        NguoiNhanThongBao::create([
            'MaTK'      => $maTK,
            'TieuDe'    => $tieuDe,
            'NoiDung'   => $noiDung,
            'Loai'      => $loai,
            'DuongDan'  => $duongDan,
            'DaDoc'     => false,
            'NgayGui'   => now(),
        ]);
    }

    /**
     * Gửi thông báo đến tất cả thành viên trong một nhóm
     */
    public static function guiDenNhom(string $maNhom, string $tieuDe, string $noiDung, string $loai = 'Hệ thống', ?string $duongDan = null): void
    {
        $thanhViens = ThanhVienNhom::where('MaNhom', $maNhom)
            ->where('TrangThai', 'da_tham_gia')
            ->with('sinhVien.taiKhoan')
            ->get();

        foreach ($thanhViens as $tv) {
            $maTK = $tv->sinhVien->taiKhoan->MaTK ?? null;
            if ($maTK) {
                self::guiDen($maTK, $tieuDe, $noiDung, $loai, $duongDan);
            }
        }
    }

    /**
     * Gửi thông báo đến GVHD của một nhóm (thông qua DangKyDeTai)
     */
    public static function guiDenGVHD(string $maNhom, string $tieuDe, string $noiDung, string $loai = 'Hệ thống', ?string $duongDan = null): void
    {
        $dk = \App\Models\DangKyDeTai::where('MaNhom', $maNhom)
            ->where('TrangThai', 'Đã duyệt')
            ->first();

        if (!$dk) return;

        $gv = GiangVien::find($dk->MaGVHuongDan);
        if (!$gv) return;

        $tk = TaiKhoan::find($gv->MaTK);
        if ($tk) {
            self::guiDen($tk->MaTK, $tieuDe, $noiDung, $loai, $duongDan);
        }
    }

    /**
     * Gửi thông báo đến tất cả Sinh viên
     */
    public static function guiDenTatCaSV(string $tieuDe, string $noiDung, string $loai = 'Hệ thống'): void
    {
        $maTKs = \App\Models\SinhVien::with('taiKhoan')->get()
            ->pluck('taiKhoan.MaTK')->filter();

        foreach ($maTKs as $maTK) {
            self::guiDen($maTK, $tieuDe, $noiDung, $loai);
        }
    }
}
