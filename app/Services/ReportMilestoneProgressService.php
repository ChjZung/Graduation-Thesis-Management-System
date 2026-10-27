<?php

namespace App\Services;

use App\Models\DeTai;
use App\Models\KhoiBaoCaoTienDo;
use App\Models\Nhom;
use App\Models\PhieuDangKy;

/** Progress of lecturer-configured report milestones, independent of the six-step thesis workflow. */
class ReportMilestoneProgressService
{
    public function forGroup(Nhom $group, ?DeTai $topic = null): array
    {
        if (!$topic) {
            $registration = PhieuDangKy::with('deTai')->where('MaNhom', $group->MaNhom)
                ->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])
                ->when($group->MaHocKy, fn ($q) => $q->whereHas('deTai', fn ($t) => $t->where('MaHocKy', $group->MaHocKy)))
                ->orderByDesc('created_at')->first();
            $topic = $registration?->deTai;
        }
        $semester = (string) ($group->MaHocKy ?? $topic?->MaHocKy ?? '');
        if (!$topic || ($group->MaHocKy && $topic->MaHocKy && (string) $group->MaHocKy !== (string) $topic->MaHocKy)) {
            return $this->calculate([], ['group_id'=>$group->MaNhom, 'semester_id'=>$semester]);
        }
        $owners = PhieuDangKy::where('MaDeTai', $topic->MaDeTai)->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])
            ->whereHas('nhom', fn ($q) => $q->where('MaHocKy', $semester))->distinct()->pluck('MaNhom')->all();
        $stages = $topic->giaiDoanThucHiens()->with('dotNops')->orderBy('ThuTu')->orderBy('MaGiaiDoan')->get();
        $versions = KhoiBaoCaoTienDo::where('MaDeTai', $topic->MaDeTai)->whereNotNull('MaDotNop')
            ->orderByDesc('LanNop')->orderByDesc('NgayNop')->orderByDesc('MaKhoi')->get();
        $owned = $this->ownedVersions($versions, $group, $semester, $owners);
        $configuration = [];
        foreach ($stages as $stage) {
            $rounds = [];
            foreach ($stage->dotNops as $round) {
                $version = $owned->first(fn ($v) => (string) $v->MaDotNop === (string) $round->MaDotNop
                    && (string) $v->MaGiaiDoan === (string) $stage->MaGiaiDoan);
                $rounds[] = ['id'=>$round->MaDotNop, 'title'=>$round->TenDotNop, 'status'=>$version?->TrangThai ?? 'Chưa nộp',
                    'confirmed'=>$version?->TrangThai === 'Đạt' && filled($version?->NguoiNhanXet) && filled($version?->NgayNhanXet),
                    'overdue'=>$round->HanNop && $round->HanNop->isPast() && $version?->TrangThai !== 'Đạt',
                    'version_id'=>$version?->MaKhoi, 'version'=>$version?->LanNop];
            }
            $configuration[] = ['id'=>$stage->MaGiaiDoan, 'title'=>$stage->TenGiaiDoan, 'rounds'=>$rounds];
        }
        return $this->calculate($configuration, ['group_id'=>$group->MaNhom, 'topic_id'=>$topic->MaDeTai,
            'semester_id'=>$semester, 'legacy_ownership_ambiguous'=>count($owners) > 1]);
    }

    public function versionsForRound(\App\Models\DotNopBaoCao $round, Nhom $group): \Illuminate\Support\Collection
    {
        $semester = (string) ($group->MaHocKy ?? $round->deTai?->MaHocKy ?? '');
        $owners = PhieuDangKy::where('MaDeTai', $round->MaDeTai)->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])
            ->whereHas('nhom', fn ($q) => $q->where('MaHocKy', $semester))->distinct()->pluck('MaNhom')->all();
        return $this->ownedVersions($round->khoiBaoCaos, $group, $semester, $owners)->values();
    }

    private function ownedVersions($versions, Nhom $group, string $semester, array $owners)
    {
        return $versions->filter(function ($version) use ($group, $semester, $owners) {
            $json = $version->TomTatJson ?? [];
            if (is_string($json)) $json = json_decode($json, true) ?: [];
            $identity = $json['_submission_identity'] ?? $json['source_identity'] ?? null;
            if (is_array($identity) && !empty($identity['group_id'])) {
                return (string) $identity['group_id'] === (string) $group->MaNhom
                    && (string) ($identity['semester_id'] ?? '') === $semester;
            }
            // Old schema has no group FK on reports. Never attribute ambiguous legacy records.
            return count($owners) === 1 && (string) $owners[0] === (string) $group->MaNhom;
        });
    }

    /** Pure calculation: input contains one latest version per round, never one row per upload. */
    public function calculate(array $stages, array $scope = []): array
    {
        $count = count($stages);
        $total = 0.0;
        $roundCount = $done = 0;
        $incompleteConfiguration = false;
        foreach ($stages as &$stage) {
            $rounds = $stage['rounds'] ?? [];
            $stage['weight'] = $count ? 100.0 / $count : 0.0;
            $stage['earned'] = 0.0;
            $incompleteConfiguration = $incompleteConfiguration || !$rounds;
            foreach ($rounds as &$round) {
                $round['weight'] = $stage['weight'] / count($rounds);
                $round['completed'] = ($round['confirmed'] ?? false) === true && ($round['status'] ?? '') === 'Đạt';
                $round['earned'] = $round['completed'] ? $round['weight'] : 0.0;
                $stage['earned'] += $round['earned'];
                $total += $round['earned'];
                $round['cumulative'] = min(100.0, $total);
                $roundCount++;
                $done += $round['completed'] ? 1 : 0;
            }
            unset($round);
            $stage['rounds'] = $rounds;
        }
        unset($stage);
        return ['label'=>'Tiến độ hoàn thành các mốc báo cáo', 'percentage'=>min(100.0, $total),
            'stage_count'=>$count, 'round_count'=>$roundCount, 'completed_rounds'=>$done, 'stages'=>$stages,
            'configuration_incomplete'=>$incompleteConfiguration, 'scope'=>$scope];
    }
}
