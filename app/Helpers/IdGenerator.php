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
            $max = DB::table('khoas')
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
            $max = DB::table('bo_mons')
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
            $max = DB::table('nganhs')
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
            $max = DB::table('lops')
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
            $max = DB::table('hoc_kies')
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
            $max = DB::table('giang_viens')
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
            $max = DB::table('sinh_viens')
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
            $max = DB::table('bao_cao_tien_dos')
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
            $max = DB::table('tom_tat_bao_caos')
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
            $max = DB::table('nhoms')
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
            $max = DB::table('hoi_dongs')
                ->where('MaHoiDong', 'LIKE', 'HD%')
                ->max(DB::raw("CAST(SUBSTRING(MaHoiDong, 2) AS UNSIGNED)"));
            $next = ($max ?? 0) + 1;
            return 'HD' . str_pad($next, 2, '0', STR_PAD_LEFT);
        });
    }
}
