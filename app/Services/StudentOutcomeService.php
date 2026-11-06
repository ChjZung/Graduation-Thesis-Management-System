<?php
namespace App\Services;

use App\Models\{HoSoBaoVe, KetQuaSinhVien, PhieuChamDiem};

class StudentOutcomeService
{
    public function evaluate(?KetQuaSinhVien $result, HoSoBaoVe $dossier, ?\Illuminate\Support\Collection $ballots = null): array
    {
        $score = $result?->DiemChinhThuc ?? $result?->DiemTongKet ?? $result?->DiemHoiDongTB;
        $pending = ['passed' => null, 'label' => 'Chưa đủ dữ liệu CLO', 'failed_clos' => [], 'clo_scores' => []];
        $kind = $dossier->xacDinhLoaiKhoaLuan();
        if (!$result || $score === null || !$dossier->hoiDong || !$kind) return $pending;
        $rubric = $kind === 'KLKS' ? PhieuChamDiem::getRubricKLKS($dossier->TieuChiChucNang) : PhieuChamDiem::getRubricKLCN($dossier->TieuChiChucNang);
        // Read current ballots for this dossier; never persist or trust an older CLO snapshot.
        $ballots ??= PhieuChamDiem::where('MaHoSo', $dossier->MaHoSo)->where('MaHoiDong', $dossier->MaHoiDong)
            ->where('MaSV', $result->MaSV)->get();
        $clo = app(CloCalculationService::class)->calculateStudentClo($dossier->hoiDong, $result->MaSV, $kind, $rubric, $ballots);
        if (!$clo['is_official'] || !$clo['total_clo_count']) return $pending;
        $failed = collect($clo['cloScores'])->filter(fn($item) => !$item['passed'])->keys()->all();
        $passed = (float)$score >= CloCalculationService::PASSING_THRESHOLD && $clo['all_clo_passed'];
        return ['passed' => $passed, 'label' => $passed ? 'Đạt' : 'Không đạt', 'failed_clos' => $failed, 'clo_scores' => array_map(fn ($item) => $item['score'], $clo['cloScores'])];
    }
}
