<?php

namespace App\Services;

use App\Models\ThongBao;
use App\Models\NguoiNhanThongBao;
use App\Models\ThanhVienNhom;
use App\Models\GiangVien;
use App\Models\TaiKhoan;
use Illuminate\Support\Str;

class ThongBaoService
{
    /**
     * Gửi thông báo đến một tài khoản cụ thể
     */
    public static function guiDen(string $maTK, string $tieuDe, string $noiDung, string $loai = 'Hệ thống', ?string $duongDan = null): void
    {
        $maTB = 'TB_' . Str::upper(Str::random(7));
        while (ThongBao::where('MaThongBao', $maTB)->exists()) {
            $maTB = 'TB_' . Str::upper(Str::random(7));
        }

        // Tạo bản ghi cha trong thong_baos
        ThongBao::create([
            'MaThongBao'   => $maTB,
            'TieuDe'       => $tieuDe,
            'NoiDung'      => $noiDung,
            'LoaiThongBao' => $loai,
            'DoiTuongNhan' => 'Cá nhân',
            'NgayTao'      => now(),
            'TrangThai'    => 'Đã tạo',
        ]);

        // Tạo bản ghi người nhận
        NguoiNhanThongBao::create([
            'MaThongBao' => $maTB,
            'MaTK'       => $maTK,
            'TieuDe'     => $tieuDe,
            'NoiDung'    => $noiDung,
            'Loai'       => $loai,
            'DuongDan'   => $duongDan,
            'DaDoc'      => false,
            'NgayDoc'    => null,
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

    /**
     * Gửi thông báo deadline có chống gửi trùng lặp cùng 1 tiêu đề cho 1 tài khoản trong ngày
     */
    public static function guiThongBaoDeadline(string $maTK, string $tieuDe, string $noiDung, string $loai = 'Báo cáo', ?string $duongDan = null): bool
    {
        $today = date('Y-m-d');
        $alreadySent = NguoiNhanThongBao::where('MaTK', $maTK)
            ->where('TieuDe', $tieuDe)
            ->whereDate('created_at', $today)
            ->exists();

        if ($alreadySent) {
            return false;
        }

        self::guiDen($maTK, $tieuDe, $noiDung, $loai, $duongDan);
        return true;
    }

    /**
     * Gửi thông báo đính kèm file văn bản gốc kế hoạch cho Sinh viên / Giảng viên
     */
    public static function guiThongBaoKemFileGoc(string $tieuDe, string $noiDung, string $fileUrl, string $loai = 'Kế hoạch'): int
    {
        $maTB = 'TB_' . Str::upper(Str::random(7));
        while (ThongBao::where('MaThongBao', $maTB)->exists()) {
            $maTB = 'TB_' . Str::upper(Str::random(7));
        }

        // Tạo bản ghi cha
        ThongBao::create([
            'MaThongBao'   => $maTB,
            'TieuDe'       => $tieuDe,
            'NoiDung'      => $noiDung,
            'LoaiThongBao' => $loai,
            'DoiTuongNhan' => 'Toàn thể',
            'FileDinhKem'  => $fileUrl,
            'NgayTao'      => now(),
            'TrangThai'    => 'Đã phát hành',
        ]);

        // Phân phối tới tất cả Sinh viên và Giảng viên
        $taiKhoans = TaiKhoan::whereHas('vaiTro', function($q) {
            $q->whereIn('TenVaiTro', ['Sinh viên', 'Giảng viên']);
        })->get();

        $count = 0;
        foreach ($taiKhoans as $tk) {
            NguoiNhanThongBao::create([
                'MaThongBao' => $maTB,
                'MaTK'       => $tk->MaTK,
                'TieuDe'     => $tieuDe,
                'NoiDung'    => $noiDung,
                'Loai'       => $loai,
                'DuongDan'   => asset($fileUrl),
                'DaDoc'      => false,
                'NgayDoc'    => null,
            ]);
            $count++;
        }

        return $count;
    }
}
