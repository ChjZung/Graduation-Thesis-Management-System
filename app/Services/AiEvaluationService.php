<?php

namespace App\Services;

class AiEvaluationService
{
    public const GROUPS = ['completed_work', 'difficulties', 'next_steps'];

    public function evaluate(array $prediction, ?array $truth): array
    {
        if ($truth === null) return ['status'=>'not_evaluated', 'precision'=>null, 'recall'=>null, 'f1'=>null];
        foreach (self::GROUPS as $group) {
            if (!array_key_exists($group, $truth) || !is_array($truth[$group])) {
                throw new \InvalidArgumentException('Đáp án chuẩn phải có đủ ba nhóm, nhóm không có nội dung dùng [].');
            }
        }
        $tp = $fp = $fn = 0;
        $groups = [];
        foreach (self::GROUPS as $group) {
            $actual = $this->quotes($prediction[$group] ?? []);
            $expected = $this->quotes($truth[$group]);
            $groupTp = count(array_intersect($actual, $expected));
            $groups[$group] = ['tp'=>$groupTp,'fp'=>count(array_diff($actual,$expected)),'fn'=>count(array_diff($expected,$actual))];
            $tp += $groupTp; $fp += $groups[$group]['fp']; $fn += $groups[$group]['fn'];
        }
        $precision = ($tp + $fp) ? $tp / ($tp + $fp) : null;
        $recall = ($tp + $fn) ? $tp / ($tp + $fn) : null;
        $f1 = (2 * $tp + $fp + $fn) ? 2 * $tp / (2 * $tp + $fp + $fn) : null;
        return compact('tp','fp','fn','precision','recall','f1','groups') + [
            'status'=>'evaluated', 'method'=>'micro_exact_evidence_by_group',
            'note'=>'Khớp nguyên văn bằng chứng theo từng nhóm sau chuẩn hóa chữ hoa và khoảng trắng; không phải điểm đánh giá ngữ nghĩa tổng quát.',
        ];
    }

    private function quotes(array $items): array
    {
        $quotes = [];
        foreach ($items as $item) {
            $quote = is_array($item) ? ($item['evidence'] ?? '') : $item;
            if (!is_string($quote) || trim($quote) === '') throw new \InvalidArgumentException('Mỗi mục đánh giá phải có câu bằng chứng.');
            $quotes[] = mb_strtolower(preg_replace('/\s+/u','',trim($quote)));
        }
        return array_values(array_unique($quotes));
    }
}
