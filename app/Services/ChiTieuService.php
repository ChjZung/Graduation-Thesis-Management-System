<?php

namespace App\Services;

use App\Models\ChiTieuHuongDan;
use App\Models\PhieuDangKy;
use App\Models\GiangVien;
use Illuminate\Support\Facades\DB;

class ChiTieuService
{
    /**
     * Lấy hạn mức chỉ tiêu (tổng số nhóm tối đa) của giảng viên trong học kỳ
     * Lấy từ cấu hình của giáo vụ trong bảng ChiTieuHuongDan (cột SoNhomToiDa), mặc định 5 nếu chưa thiết lập.
     */
    public static function getChiTieuTong(string $maGV, ?string $maHocKy = null, bool $lockForUpdate = false): int
    {
        $query = ChiTieuHuongDan::where('MaGV', $maGV);
        if ($maHocKy) {
            $query->where('MaHocKy', $maHocKy);
        }

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $chiTieu = $query->value('SoNhomToiDa');

        return $chiTieu !== null ? (int)$chiTieu : 5;
    }

    /**
     * Tính số chỉ tiêu đã dùng:
     * Dựa trên số nhóm sinh viên đăng ký thành công (TrangThai = 'Đã duyệt'),
     * KHÔNG tính đề tài chỉ mới được duyệt, bị từ chối hay bản nháp.
     */
    public static function getChiTieuDaDung(string $maGV, ?string $maHocKy = null, bool $lockForUpdate = false): int
    {
        $query = PhieuDangKy::where('TrangThai', 'Đã duyệt')
            ->whereHas('deTai', function ($dq) use ($maGV, $maHocKy) {
                $dq->where('MaGV', $maGV);
                if ($maHocKy) {
                    $dq->where('MaHocKy', $maHocKy);
                }
            });

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->count();
    }

    /**
     * Lấy thống kê chi tiết chỉ tiêu của giảng viên trong học kỳ
     *
     * @return array{
     *     tong: int,
     *     da_dung: int,
     *     con_lai: int,
     *     tinh_trang: 'con_chi_tieu'|'het_chi_tieu'|'vuot_chi_tieu',
     *     is_full: bool,
     *     label: string
     * }
     */
    public static function getChiTieuStats(string $maGV, ?string $maHocKy = null, bool $lockForUpdate = false): array
    {
        $tong = self::getChiTieuTong($maGV, $maHocKy, $lockForUpdate);
        $daDung = self::getChiTieuDaDung($maGV, $maHocKy, $lockForUpdate);
        $conLai = max(0, $tong - $daDung);

        $tinhTrang = 'con_chi_tieu';
        if ($daDung === $tong) {
            $tinhTrang = 'het_chi_tieu';
        } elseif ($daDung > $tong) {
            $tinhTrang = 'vuot_chi_tieu';
        }

        return [
            'tong'       => $tong,
            'da_dung'    => $daDung,
            'con_lai'    => $conLai,
            'tinh_trang' => $tinhTrang,
            'is_full'    => $daDung >= $tong,
            'label'      => "{$daDung}/{$tong}",
        ];
    }

    /**
     * Lấy thống kê chỉ tiêu hàng loạt cho danh sách giảng viên (tối ưu tránh N+1)
     *
     * @param array<string> $gvIds
     * @return array<string, array{
     *     tong: int,
     *     da_dung: int,
     *     con_lai: int,
     *     tinh_trang: 'con_chi_tieu'|'het_chi_tieu'|'vuot_chi_tieu',
     *     is_full: bool,
     *     label: string
     * }>
     */
    public static function getChiTieuStatsBatch(array $gvIds, ?string $maHocKy = null): array
    {
        if (empty($gvIds)) {
            return [];
        }

        // 1. Chỉ tiêu tối đa từ cấu hình giáo vụ
        $chiTieuQuery = ChiTieuHuongDan::whereIn('MaGV', $gvIds);
        if ($maHocKy) {
            $chiTieuQuery->where('MaHocKy', $maHocKy);
        }
        $chiTieuMap = $chiTieuQuery->pluck('SoNhomToiDa', 'MaGV')->toArray();

        // 2. Số nhóm đã duyệt từ PhieuDangKy (TrangThai = 'Đã duyệt')
        $pdkQuery = PhieuDangKy::where('PhieuDangKy.TrangThai', 'Đã duyệt')
            ->join('DeTai', 'PhieuDangKy.MaDeTai', '=', 'DeTai.MaDeTai')
            ->whereIn('DeTai.MaGV', $gvIds);

        if ($maHocKy) {
            $pdkQuery->where('DeTai.MaHocKy', $maHocKy);
        }

        $daDungMap = $pdkQuery->groupBy('DeTai.MaGV')
            ->select('DeTai.MaGV', DB::raw('count(*) as count'))
            ->pluck('count', 'MaGV')
            ->toArray();

        $result = [];
        foreach ($gvIds as $maGV) {
            $tong = isset($chiTieuMap[$maGV]) ? (int)$chiTieuMap[$maGV] : 5;
            $daDung = isset($daDungMap[$maGV]) ? (int)$daDungMap[$maGV] : 0;
            $conLai = max(0, $tong - $daDung);

            $tinhTrang = 'con_chi_tieu';
            if ($daDung === $tong) {
                $tinhTrang = 'het_chi_tieu';
            } elseif ($daDung > $tong) {
                $tinhTrang = 'vuot_chi_tieu';
            }

            $result[$maGV] = [
                'tong'       => $tong,
                'da_dung'    => $daDung,
                'con_lai'    => $conLai,
                'tinh_trang' => $tinhTrang,
                'is_full'    => $daDung >= $tong,
                'label'      => "{$daDung}/{$tong}",
            ];
        }

        return $result;
    }
}

