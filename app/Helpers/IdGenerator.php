<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class IdGenerator
{
    /**
     * Sinh mã Khoa: K01, K02...
     */
    public static function nextKhoa(): string
    {
        return DB::transaction(function () {
            $max = DB::table('Khoa')
                ->where('MaKhoa', 'LIKE', 'K%')
                ->max(DB::raw("CAST(SUBSTRING(MaKhoa, 2) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'K' . str_pad($next, 2, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Sinh mã Bộ môn: BM01, BM02...
     */
    public static function nextBoMon(): string
    {
        return DB::transaction(function () {
            $max = DB::table('BoMon')
                ->where('MaBoMon', 'LIKE', 'BM%')
                ->max(DB::raw("CAST(SUBSTRING(MaBoMon, 3) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'BM' . str_pad($next, 2, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Sinh mã Ngành: NG01, NG02...
     */
    public static function nextNganh(): string
    {
        return DB::transaction(function () {
            $max = DB::table('Nganh')
                ->where('MaNganh', 'LIKE', 'NG%')
                ->max(DB::raw("CAST(SUBSTRING(MaNganh, 3) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'NG' . str_pad($next, 2, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Sinh mã Lớp: L01, L02...
     */
    public static function nextLop(): string
    {
        return DB::transaction(function () {
            $max = DB::table('Lop')
                ->where('MaLop', 'LIKE', 'L%')
                ->max(DB::raw("CAST(SUBSTRING(MaLop, 2) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'L' . str_pad($next, 2, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Sinh mã Học kỳ: HK01, HK02...
     */
    public static function nextHocKy(): string
    {
        return DB::transaction(function () {
            $max = DB::table('HocKy')
                ->where('MaHocKy', 'LIKE', 'HK%')
                ->max(DB::raw("CAST(SUBSTRING(MaHocKy, 3) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'HK' . str_pad($next, 2, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Sinh mã Giảng viên: GV01, GV02...
     */
    public static function nextGiangVien(): string
    {
        return DB::transaction(function () {
            $max = DB::table('GiangVien')
                ->where('MaGV', 'LIKE', 'GV%')
                ->max(DB::raw("CAST(SUBSTRING(MaGV, 3) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'GV' . str_pad($next, 2, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Sinh mã Sinh viên: SV01, SV02...
     */
    public static function nextSinhVien(): string
    {
        return DB::transaction(function () {
            $max = DB::table('SinhVien')
                ->where('MaSV', 'LIKE', 'SV%')
                ->max(DB::raw("CAST(SUBSTRING(MaSV, 3) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'SV' . str_pad($next, 2, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Sinh mã Báo cáo: BC001, BC002, ...
     */
    public static function nextBaoCao(): string
    {
        return DB::transaction(function () {
            $max = DB::table('BaoCaoTienDo')
                ->where('MaBaoCao', 'LIKE', 'BC%')
                ->max(DB::raw("CAST(SUBSTRING(MaBaoCao, 3) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'BC' . str_pad($next, 3, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Sinh mã Tóm tắt: TT001, TT002, ...
     */
    public static function nextTomTat(): string
    {
        return DB::transaction(function () {
            $max = DB::table('TomTatBaoCao')
                ->where('MaTomTat', 'LIKE', 'TT%')
                ->max(DB::raw("CAST(SUBSTRING(MaTomTat, 3) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'TT' . str_pad($next, 3, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Sinh mã Nhóm: N01, N02, ...
     */
    public static function nextNhom(): string
    {
        return DB::transaction(function () {
            $max = DB::table('Nhom')
                ->where('MaNhom', 'LIKE', 'N%')
                ->max(DB::raw("CAST(SUBSTRING(MaNhom, 2) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'N' . str_pad($next, 2, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Sinh mã Nhận xét: NX001, NX002, ...
     */
    public static function nextNhanXet(): string
    {
        return DB::transaction(function () {
            $max = DB::table('nhan_xets')
                ->where('MaNhanXet', 'LIKE', 'NX%')
                ->max(DB::raw("CAST(SUBSTRING(MaNhanXet, 3) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'NX' . str_pad($next, 3, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Sinh mã Hội đồng: HD01, HD02, ...
     */
    public static function nextHoiDong(): string
    {
        return DB::transaction(function () {
            $max = DB::table('HoiDong')
                ->where('MaHoiDong', 'LIKE', 'HD%')
                ->max(DB::raw("CAST(SUBSTRING(MaHoiDong, 3) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'HD' . str_pad($next, 2, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Sinh mã Thông báo: TB01, TB02, ...
     */
    public static function nextThongBao(): string
    {
        return DB::transaction(function () {
            $max = DB::table('ThongBao')
                ->where('MaThongBao', 'LIKE', 'TB%')
                ->max(DB::raw("CAST(SUBSTRING(MaThongBao, 3) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'TB' . str_pad($next, 2, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Sinh mã Hồ sơ bảo vệ: HS01, HS02, ...
     */
    public static function nextHoSoBaoVe(): string
    {
        return DB::transaction(function () {
            $max = DB::table('HoSoBaoVe')
                ->where('MaHoSo', 'LIKE', 'HS%')
                ->max(DB::raw("CAST(SUBSTRING(MaHoSo, 3) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'HS' . str_pad($next, 2, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Sinh mã Tệp hồ sơ bảo vệ: TEP01, TEP02, ...
     */
    public static function nextTepHoSoBaoVe(): string
    {
        return DB::transaction(function () {
            $max = DB::table('TepHoSoBaoVe')
                ->where('MaTep', 'LIKE', 'TEP%')
                ->max(DB::raw("CAST(SUBSTRING(MaTep, 4) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'TEP' . str_pad($next, 2, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Sinh mã Phân công phản biện: PC01, PC02, ...
     */
    public static function nextPhanCong(): string
    {
        return DB::transaction(function () {
            $max = DB::table('PhanCongPhanBien')
                ->where('MaPhanCong', 'LIKE', 'PC%')
                ->max(DB::raw("CAST(SUBSTRING(MaPhanCong, 3) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'PC' . str_pad($next, 2, '0', STR_PAD_LEFT);
        });
    }
}
