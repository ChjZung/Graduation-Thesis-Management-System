<?php

namespace App\Services;

class AiRequirementCrossMatcher
{
    private static array $stopWords = [
        'va', 'la', 'cua', 'cac', 'nhung', 'mot', 'duoc', 'cho', 'voi', 'trong',
        'theo', 've', 'co', 'da', 'de', 'khi', 'nay', 'bao', 'cao', 'he', 'thong',
        'noi', 'dung', 'yeu', 'cau', 'sinh', 'vien', 'giang', 'vien', 'thuc', 'hien',
        'de', 'tai', 'phan', 'muc', 'va/hoac', 'hoac', 'bang', 'can', 'trinh', 'bay',
        'danh', 'gom', 'thiet', 'ke', 'xay', 'dung'
    ];

    private static array $conceptClusters = [
        'database' => ['csdl', 'database', 'co_so_du_lieu', 'mysql', 'sql', 'bang', 'table', 'erd', 'thuc_the', 'quan_he', 'khoa'],
        'ui' => ['giao_dien', 'ui', 'ux', 'man_hinh', 'form', 'nguoi_dung', 'view', 'frontend', 'layout'],
        'sequence' => ['tuan_tu', 'sequence', 'so_do_tuan_tu', 'luong', 'tuong_tac', 'so_do'],
        'usecase' => ['use_case', 'usecase', 'chuc_nang', 'dac_ta', 'tac_nhan', 'actor', 'nghiep_vu'],
        'class_diagram' => ['lop', 'class', 'class_diagram', 'so_do_lop', 'thiet_ke_lop'],
        'survey' => ['khao_sat', 'hien_trang', 'quy_trinh', 'nghiep_vu', 'thuc_trang', 'tong_quan', 'gioi_thieu', 'ly_do'],
        'testing' => ['kiem_thu', 'test', 'danh_gia', 'ket_qua', 'thu_nghiem', 'trien_khai', 'ap_dung', 'thuc_te'],
        'topic_mgmt' => ['de_tai', 'de_xuat', 'dang_ky', 'quan_ly_de_tai', 'danh_sach_de_tai'],
        'progress_defense' => ['tien_do', 'theo_doi', 'ho_so', 'bao_ve', 'hoi_dong', 'cham_diem', 'khoa_luan'],
        'architecture' => ['kien_truc', 'cong_nghe', 'mo_hinh', 'mvc', 'client_server', 'laravel', 'framework', 'api'],
    ];

    public static function removeDiacritics(string $str): string
    {
        $str = preg_replace('/[đĐ]/u', 'd', $str);
        $normalized = \Normalizer::normalize($str, \Normalizer::FORM_D);
        $clean = preg_replace('/\p{M}/u', '', $normalized);
        return mb_strtolower($clean, 'UTF-8');
    }

    public static function tokenize(string $text): array
    {
        $clean = self::removeDiacritics($text);
        preg_match_all('/[a-z0-9_]+/u', $clean, $matches);
        $stopLookup = array_flip(self::$stopWords);
        $tokens = [];
        foreach ($matches[0] as $w) {
            if (mb_strlen($w) > 1 && !isset($stopLookup[$w])) {
                $tokens[$w] = $w;
            }
        }
        return $tokens;
    }

    public static function parseRequirementsText(string $rawText): array
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $rawText))));
        $numbered = [];

        foreach ($lines as $line) {
            if (preg_match('/^(?:Yêu cầu\s*)?(\d+)[.)]\s*(.+)$/ui', $line, $m)) {
                $numbered[] = [(string)$m[1], trim($m[2]), $line];
            } elseif (preg_match('/^[-•*+]\s*(.+)$/u', $line, $m)) {
                $clean = trim($m[1]);
                if (mb_strlen($clean) > 5) {
                    $numbered[] = [(string)(count($numbered) + 1), $clean, $line];
                }
            }
        }

        $items = [];
        if ($numbered) {
            foreach ($numbered as $idx => [$num, $body, $full]) {
                $items[] = [
                    'id' => sprintf('req_%03d', $idx + 1),
                    'num' => $num,
                    'text' => $body,
                    'quote' => $full,
                ];
            }
        } else {
            $count = 1;
            foreach ($lines as $line) {
                if (mb_strlen($line) > 10 && !preg_match('/^(giai đoạn|đợt nộp|mô tả đợt)/ui', $line)) {
                    $items[] = [
                        'id' => sprintf('req_%03d', $count),
                        'num' => (string)$count,
                        'text' => $line,
                        'quote' => $line,
                    ];
                    $count++;
                }
            }
        }
        return $items;
    }

    public static function flattenChapters(array $chapters, array $parentTitles = [], array $parentRefs = []): array
    {
        $flat = [];
        foreach ($chapters as $ch) {
            $title = $ch['title'] ?? '';
            $currentTitles = $title ? array_merge($parentTitles, [$title]) : $parentTitles;
            $fullTitle = implode(' > ', $currentTitles);

            $summaryText = trim($ch['summary'] ?? '');
            if (!$summaryText && !empty($ch['items'])) {
                $parts = [];
                foreach ($ch['items'] as $it) {
                    if (is_array($it) && !empty($it['content'])) $parts[] = $it['content'];
                }
                $summaryText = implode(' ', $parts);
            }

            $refs = [];
            foreach ($ch['items'] ?? [] as $it) {
                if (is_array($it) && !empty($it['source_references'])) {
                    foreach ($it['source_references'] as $r) $refs[] = $r;
                }
            }

            $inheritedRefs = [];

            $flat[] = [
                'id' => $ch['id'] ?? '',
                'title' => $title,
                'full_title' => $fullTitle,
                'start_page' => $ch['start_page'] ?? null,
                'end_page' => $ch['end_page'] ?? null,
                'summary' => $summaryText,
                'observations' => $ch['items'] ?? [],
                'source_references' => $refs,
                'inherited_references' => $inheritedRefs,
                'level' => $ch['level'] ?? 1,
            ];

            $children = $ch['sections'] ?? $ch['children'] ?? [];
            if ($children) {
                $flat = array_merge($flat, self::flattenChapters($children, $currentTitles, $inheritedRefs));
            }
        }
        return $flat;
    }

    public static function findMatchingCaptions(array $reqTokens, string $reqText, array $captionIndex): array
    {
        $assets = $captionIndex['assets'] ?? [];
        if (!is_array($assets)) return [];

        $reqClean = self::removeDiacritics($reqText);
        $matched = [];

        foreach ($assets as $ast) {
            $rawText = $ast['display_text'] ?? $ast['raw_text'] ?? '';
            $secTitle = $ast['section_title'] ?? '';
            if (!AiRequirementRelevance::relevant($reqText,$rawText)) continue;
            $combClean = self::removeDiacritics($rawText);
            $capTokens = self::tokenize($rawText);
            $overlap = array_intersect_key($reqTokens, $capTokens);

            // Match known diagram phrases even when PDF text joins words.
            $hasPhrase = (bool) array_intersect(AiRequirementRelevance::families($reqText),AiRequirementRelevance::families($rawText));
            if (str_contains($reqClean, 'erd') && (str_contains($combClean, 'erd') || str_contains($combClean, 'quan he co so du lieu'))) {
                $hasPhrase = true;
            } elseif (str_contains($reqClean, 'so do lop') && (str_contains($combClean, 'so do lop') || str_contains($combClean, 'class'))) {
                $hasPhrase = true;
            } elseif (str_contains($reqClean, 'tuan tu') && str_contains($combClean, 'tuan tu')) {
                foreach (['ke hoach', 'sinh vien', 'de tai', 'tien do', 'ho so', 'hoi dong', 'cham diem', 'dang ky', 'de xuat'] as $term) {
                    if (str_contains($reqClean, $term) && str_contains($combClean, $term)) {
                        $hasPhrase = true;
                        break;
                    }
                }
                if (count($overlap) >= 2) $hasPhrase = true;
            }

            if ($hasPhrase || count($overlap) >= 3) {
                $matched[] = [
                    'source_id' => $ast['asset_id'] ?? ('p' . ($ast['page'] ?? 1) . '_caption'),
                    'page' => $ast['page'] ?? 1,
                    'end_page' => $ast['page'] ?? 1,
                    'quote' => trim($rawText),
                    'start' => ($ast['source_offsets'][0][0] ?? 0),
                    'end' => ($ast['source_offsets'][0][1] ?? 0),
                    'quote_verified' => true,
                    'semantic_support' => 'NEEDS_VISUAL_REVIEW',
                    'source_type' => 'CAPTION_REFERENCE',
                    'document_sha256' => $ast['document_sha256'] ?? '',
                ];
            }
        }

        $seen = [];
        $unique = [];
        foreach ($matched as $m) {
            $k = $m['page'] . ':' . $m['quote'];
            if (!isset($seen[$k])) {
                $seen[$k] = true;
                $unique[] = $m;
            }
        }
        return $unique;
    }

    public static function calculateMatchScore(array $reqTokens, string $reqText, array $candidate, array $matchedCaptions = []): float
    {
        $title = $candidate['title'] ?? '';
        $fullTitle = $candidate['full_title'] ?? $title;
        $summary = $candidate['summary'] ?? '';

        $titleTokens = self::tokenize($title . ' ' . $fullTitle);
        $summaryTokens = self::tokenize($summary);

        $titleOverlap = array_intersect_key($reqTokens, $titleTokens);
        $score = count($titleOverlap) * 7.0;

        $summaryOverlap = array_intersect_key($reqTokens, $summaryTokens);
        $score += count($summaryOverlap) * 1.5;

        foreach (self::$conceptClusters as $clusterWords) {
            $clusterLookup = array_flip($clusterWords);
            $reqHas = (bool) array_intersect_key($reqTokens, $clusterLookup);
            $candHas = (bool) (array_intersect_key($titleTokens, $clusterLookup) || array_intersect_key($summaryTokens, $clusterLookup));
            if ($reqHas && $candHas) {
                $score += 8.0;
            }
        }

        $reqClean = self::removeDiacritics($reqText);
        $fullTitleClean = self::removeDiacritics($fullTitle);
        $keyPhrases = [
            'co so du lieu', 'so do lop', 'tuan tu', 'use case', 'khao sat',
            'giao dien', 'de xuat', 'dang ky de tai', 'hoi dong', 'bao ve',
            'erd', 'quan he', 'bang du lieu', 'cham diem', 'ho so', 'tien do'
        ];
        foreach ($keyPhrases as $ph) {
            if (str_contains($reqClean, $ph) && str_contains($fullTitleClean, $ph)) {
                $score += 12.0;
            }
        }

        if ($matchedCaptions && !empty($candidate['start_page']) && !empty($candidate['end_page'])) {
            foreach ($matchedCaptions as $cap) {
                if ($candidate['start_page'] <= $cap['page'] && $cap['page'] <= $candidate['end_page']) {
                    $score += 15.0;
                    break;
                }
            }
        }

        return $score;
    }

    public static function crossMatch(array $data, array $context): array
    {
        $reqText = '';
        foreach (['submission_requirements', 'stage_requirements', 'round_description'] as $field) {
            $val = trim((string)($context[$field] ?? ''));
            if ($val) $reqText .= $val . "\n";
        }
        if (!$reqText && !empty($data['requirements_document']['text'])) {
            $reqText = trim((string)$data['requirements_document']['text']);
        }

        $reqItems = self::parseRequirementsText($reqText);
        if (!$reqItems) return [];

        $chapters = $data['chapters'] ?? [];
        $flatSections = self::flattenChapters($chapters);
        $completedItems = [];
        $captionIndex = $data['caption_index'] ?? [];

        $allCandidates = $flatSections;
        foreach ($completedItems as $cIdx => $cw) {
            if (is_array($cw) && !empty($cw['content'])) {
                $startP = $cw['source_references'][0]['page'] ?? null;
                $endP = end($cw['source_references'])['end_page'] ?? $startP;
                $allCandidates[] = [
                    'id' => 'CW_' . ($cIdx + 1),
                    'title' => mb_substr($cw['content'], 0, 70),
                    'full_title' => mb_substr($cw['content'], 0, 70),
                    'start_page' => $startP,
                    'end_page' => $endP,
                    'summary' => $cw['content'],
                    'source_references' => $cw['source_references'] ?? [],
                    'inherited_references' => $cw['source_references'] ?? [],
                    'level' => 99,
                ];
            }
        }

        $resultTeacher = [];

        foreach ($reqItems as $req) {
            $reqTokens = self::tokenize($req['text']);
            $matchedCaptions = self::findMatchingCaptions($reqTokens, $req['text'], $captionIndex);

            $scored = [];
            foreach ($allCandidates as $cand) {
                $cand=AiRequirementRelevance::selectCandidate($req['text'],$cand);
                $localCaps=array_filter($matchedCaptions,fn($c)=>!empty($cand['start_page']) && !empty($cand['end_page']) && $cand['start_page']<=$c['page'] && $c['page']<=$cand['end_page'] && AiRequirementRelevance::relevant($req['text'],$cand['title']??''));
                if(!$cand['source_references'] && !$localCaps)continue;
                if(!AiRequirementRelevance::relevant($req['text'],($cand['title']??'').' '.($cand['summary']??'')))continue;
                $sc = self::calculateMatchScore($reqTokens, $req['text'], $cand, $matchedCaptions);
                if ($sc >= 10.0) {
                    $scored[] = [$sc, $cand];
                }
            }

            usort($scored, fn($a, $b) => $b[0] <=> $a[0]);

            if ($scored) {
                $cand = $scored[0][1];
                // Keep independently relevant captions even when prose is on another page.
                $titleToShow = $cand['full_title'] ?? $cand['title'];

                $pagesStr = '';
                if (!empty($cand['start_page'])) {
                    $pagesStr = (!empty($cand['end_page']) && $cand['end_page'] != $cand['start_page'])
                        ? " (Trang {$cand['start_page']}–{$cand['end_page']})"
                        : " (Trang {$cand['start_page']})";
                }

                $seenRefs = [];
                $cleanRefs = [];
                $candRefs = $cand['source_references'] ?: $cand['inherited_references'] ?: [];
                foreach ($candRefs as $r) {
                    $k = ($r['page'] ?? '') . ':' . mb_substr($r['quote'] ?? '', 0, 50);
                    if (!isset($seenRefs[$k])) {
                        $seenRefs[$k] = true;
                        $cleanRefs[] = $r;
                    }
                }

                if (!$cleanRefs && $matchedCaptions) {
                    foreach ($matchedCaptions as $mc) {
                        $cleanRefs[] = [
                            'source_id' => $mc['source_id'],
                            'page' => $mc['page'],
                            'end_page' => $mc['end_page'],
                            'quote' => $mc['quote'],
                            'quote_verified' => true,
                            'semantic_support' => 'NEEDS_VISUAL_REVIEW', 'source_type' => 'CAPTION_REFERENCE',
                        ];
                    }
                }

                $summarySentence = trim($cand['summary'] ?? '');
                if (!$summarySentence) {
                    if ($matchedCaptions) {
                        $capTitles = implode('; ', array_column(array_slice($matchedCaptions, 0, 2), 'quote'));
                        $summarySentence = "Nhận diện tên hình/bảng liên quan: {$capTitles}. Chỉ đối chiếu tên; giảng viên cần kiểm chứng nội dung trong PDF gốc.";
                    } else {
                        $summarySentence = "Nội dung được trình bày tại mục {$titleToShow}.";
                    }
                }

                if (mb_strlen($summarySentence) > 300) {
                    $summarySentence = mb_substr($summarySentence, 0, 297) . '...';
                }

                $explanation = "Báo cáo có nội dung liên quan tại {$titleToShow}{$pagesStr}: {$summarySentence}";

                $point = [
                    'title' => "{$titleToShow}{$pagesStr}",
                    'relation' => 'related_text',
                    'explanation' => $explanation,
                    'source_references' => array_slice($cleanRefs, 0, 12),
                    'check_status' => count(array_filter($cleanRefs,fn($r)=>($r['source_type']??'')!=='CAPTION_REFERENCE')) ? 'PARTIAL' : 'NEEDS_VISUAL_REVIEW',
                    'manual_references' => $matchedCaptions,
                ];

                $reportMatch = [
                    'content' => $summarySentence,
                    'source_references' => array_slice($cleanRefs, 0, 12),
                ];

                $resultTeacher[] = [
                    'requirement_id' => $req['id'],
                    'text' => $req['quote'],
                    'evidence_quote' => $req['quote'],
                    'report_relation' => 'related_text',
                    'report_explanation' => $explanation,
                    'report_points' => [$point],
                    'report_matches' => [$reportMatch],
                ];
            } elseif ($matchedCaptions) {
                $capPages = implode(', ', array_map(fn($c) => "Trang {$c['page']}", $matchedCaptions));
                $capNames = implode('; ', array_column(array_slice($matchedCaptions, 0, 2), 'quote'));
                $explanation = "Nhận diện tên hình/bảng liên quan tại {$capPages}: {$capNames}. Chưa xác minh nội dung bên trong; giảng viên cần kiểm chứng PDF gốc.";
                $point = [
                    'title' => $req['text'],
                    'relation' => 'related_text',
                    'explanation' => $explanation,
                    'source_references' => [
                        [
                            'source_id' => $matchedCaptions[0]['source_id'],
                            'page' => $matchedCaptions[0]['page'],
                            'end_page' => $matchedCaptions[0]['end_page'],
                            'quote' => $matchedCaptions[0]['quote'],
                            'quote_verified' => true,
                            'semantic_support' => 'NEEDS_VISUAL_REVIEW', 'source_type' => 'CAPTION_REFERENCE',
                        ]
                    ],
                    'check_status' => 'NEEDS_VISUAL_REVIEW',
                    'manual_references' => $matchedCaptions,
                ];
                $resultTeacher[] = [
                    'requirement_id' => $req['id'],
                    'text' => $req['quote'],
                    'evidence_quote' => $req['quote'],
                    'report_relation' => 'related_text',
                    'report_explanation' => $explanation,
                    'report_points' => [$point],
                    'report_matches' => [],
                ];
            } else {
                $point = [
                    'title' => $req['text'],
                    'relation' => 'not_found',
                    'explanation' => 'Hệ thống chưa tìm thấy mục tương ứng trong các chương báo cáo đã tóm tắt. Giảng viên vui lòng đối chiếu trực tiếp file PDF.',
                    'source_references' => [],
                    'check_status' => 'NOT_FOUND',
                    'manual_references' => [],
                ];
                $resultTeacher[] = [
                    'requirement_id' => $req['id'],
                    'text' => $req['quote'],
                    'evidence_quote' => $req['quote'],
                    'report_relation' => 'not_found',
                    'report_explanation' => 'Hệ thống chưa tìm thấy mục tương ứng trong các chương báo cáo đã tóm tắt. Giảng viên vui lòng đối chiếu trực tiếp file PDF.',
                    'report_points' => [$point],
                    'report_matches' => [],
                ];
            }
        }

        return $resultTeacher;
    }
}
