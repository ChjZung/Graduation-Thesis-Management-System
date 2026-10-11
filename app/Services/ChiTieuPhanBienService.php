<?php

namespace App\Services;

use App\Models\ChiTieuPhanBien;
use App\Models\PhanCongPhanBien;
use App\Models\GiangVien;
use App\Helpers\IdGenerator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ChiTieuPhanBienService
{
    const DEFAULT_LIMIT = 5;

    /**
     * Lấy hạn mức phản biện tối đa của giảng viên (mặc định 5 nếu chưa tùy chỉnh).
     */
    public static function getChiTieuTong(string $maGV, ?string $maHocKy = null, bool $lockForUpdate = false): int
    {
        $query = ChiTieuPhanBien::where('MaGV', $maGV);

        if ($maHocKy) {
            $query->where(function ($q) use ($maHocKy) {
                $q->where('MaHocKy', $maHocKy)->orWhereNull('MaHocKy');
            })->orderByRaw('CASE WHEN MaHocKy = ? THEN 0 ELSE 1 END', [$maHocKy]);
        }

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $row = $query->first();

        return $row !== null ? (int)$row->SoDeTaiToiDa : self::DEFAULT_LIMIT;
    }

    /**
     * Tính số đề tài đã được phân công phản biện của giảng viên trong học kỳ.
     */
    public static function getChiTieuDaDung(string $maGV, ?string $maHocKy = null, ?string $excludeMaDeTai = null, bool $lockForUpdate = false): int
    {
        $query = PhanCongPhanBien::where('MaGV', $maGV)
            ->where('VaiTro', 'Phản biện đề cương');

        if ($excludeMaDeTai) {
            $query->where('MaDeTai', '!=', $excludeMaDeTai);
        }

        if ($maHocKy) {
            $query->whereHas('deTai', function ($dq) use ($maHocKy) {
                $dq->where('MaHocKy', $maHocKy);
            });
        }

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->count();
    }

    /**
     * Lấy thống kê chi tiết chỉ tiêu phản biện của giảng viên.
     *
     * @return array{
     *     tong: int,
     *     da_dung: int,
     *     con_lai: int,
     *     is_full: bool,
     *     label: string
     * }
     */
    public static function getChiTieuStats(string $maGV, ?string $maHocKy = null, ?string $excludeMaDeTai = null, bool $lockForUpdate = false): array
    {
        $tong = self::getChiTieuTong($maGV, $maHocKy, $lockForUpdate);
        $daDung = self::getChiTieuDaDung($maGV, $maHocKy, $excludeMaDeTai, $lockForUpdate);
        $conLai = max(0, $tong - $daDung);

        return [
            'tong'    => $tong,
            'da_dung' => $daDung,
            'con_lai' => $conLai,
            'is_full' => $daDung >= $tong,
            'label'   => "{$daDung}/{$tong}",
        ];
    }

    /**
     * Lấy thống kê chỉ tiêu phản biện hàng loạt cho danh sách giảng viên (tránh N+1).
     *
     * @param array<string> $gvIds
     * @return array<string, array{tong: int, da_dung: int, con_lai: int, is_full: bool, label: string}>
     */
    public static function getChiTieuStatsBatch(array $gvIds, ?string $maHocKy = null): array
    {
        if (empty($gvIds)) {
            return [];
        }

        // 1. Chỉ tiêu tùy chỉnh từ bảng chitieuphanbien
        $customLimits = ChiTieuPhanBien::whereIn('MaGV', $gvIds)
            ->when($maHocKy, fn($q) => $q->where(fn($sub) => $sub->where('MaHocKy', $maHocKy)->orWhereNull('MaHocKy')))
            ->get()
            ->keyBy('MaGV');

        // 2. Số lượng đề tài đã phân công phản biện
        $pcQuery = PhanCongPhanBien::whereIn('PhanCongPhanBien.MaGV', $gvIds)
            ->where('PhanCongPhanBien.VaiTro', 'Phản biện đề cương');

        if ($maHocKy) {
            $pcQuery->join('DeTai', 'PhanCongPhanBien.MaDeTai', '=', 'DeTai.MaDeTai')
                    ->where('DeTai.MaHocKy', $maHocKy);
        }

        $usedCounts = $pcQuery->groupBy('PhanCongPhanBien.MaGV')
            ->select('PhanCongPhanBien.MaGV', DB::raw('count(*) as aggregate'))
            ->pluck('aggregate', 'PhanCongPhanBien.MaGV')
            ->toArray();

        $result = [];
        foreach ($gvIds as $maGV) {
            $tong = isset($customLimits[$maGV]) ? (int)$customLimits[$maGV]->SoDeTaiToiDa : self::DEFAULT_LIMIT;
            $daDung = $usedCounts[$maGV] ?? 0;
            $conLai = max(0, $tong - $daDung);

            $result[$maGV] = [
                'tong'    => $tong,
                'da_dung' => $daDung,
                'con_lai' => $conLai,
                'is_full' => $daDung >= $tong,
                'label'   => "{$daDung}/{$tong}",
            ];
        }

        return $result;
    }

    /**
     * Tùy chỉnh chỉ tiêu phản biện cho 1 giảng viên (phải là số nguyên >= 0).
     */
    public static function updateChiTieu(string $maGV, int $soLuong, ?string $maHocKy = null, ?string $ghiChu = null): ChiTieuPhanBien
    {
        if ($soLuong < 0) {
            throw new InvalidArgumentException("Hạn mức phản biện tối đa phải là số nguyên >= 0.");
        }

        $record = ChiTieuPhanBien::where('MaGV', $maGV)
            ->when($maHocKy, fn($q) => $q->where('MaHocKy', $maHocKy), fn($q) => $q->whereNull('MaHocKy'))
            ->first();

        if ($record) {
            $record->update([
                'SoDeTaiToiDa' => $soLuong,
                'GhiChu'       => $ghiChu,
            ]);
            return $record;
        }

        $maCT = 'CTPB_' . substr($maGV, -6) . '_' . ($maHocKy ?: 'ALL');

        return ChiTieuPhanBien::create([
            'MaChiTieuPB'  => $maCT,
            'MaGV'         => $maGV,
            'MaHocKy'      => $maHocKy,
            'SoDeTaiToiDa' => $soLuong,
            'GhiChu'       => $ghiChu,
        ]);
    }

    /**
     * Khôi phục chỉ tiêu phản biện về mặc định 5.
     */
    public static function resetChiTieu(string $maGV, ?string $maHocKy = null): void
    {
        ChiTieuPhanBien::where('MaGV', $maGV)
            ->when($maHocKy, fn($q) => $q->where('MaHocKy', $maHocKy), fn($q) => $q->whereNull('MaHocKy'))
            ->delete();
    }
}
