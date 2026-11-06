<?php

namespace App\Services;

use App\Models\HoiDong;
use App\Models\PhieuChamDiem;
use App\Models\KetQuaSinhVien;
use App\Models\HoSoBaoVe;
use App\Models\HocKy;
use Illuminate\Support\Collection;

class CloCalculationService
{
    public static function auditSchoolScores(array $examiners, bool $engineer): array
    {
        $judges = array_map(fn ($key) => $examiners[$key] ?? [], ['GV1', 'GV2', 'GV3']);
        $criteria = [];
        $clo = [];
        $judgeTotals = [0, 0, 0];
        foreach (CloSchoolWorkbook::criteria($engineer) as [$label, $code, $plo, $max, $items]) {
            $scores = [];
            foreach ($judges as $i => $judge) {
                $scores[$i] = !empty($judge['isFinal']) ? CloSchoolWorkbook::score($judge['chiTietDiem'] ?? [], $items) : null;
                $judgeTotals[$i] = $judgeTotals[$i] === null || $scores[$i] === null ? null : $judgeTotals[$i] + $scores[$i];
            }
            $average = in_array(null, $scores, true) ? null : array_sum($scores) / 3;
            $weight = $max / 10;
            $normalized = $average === null ? null : $average * 10 / $max;
            $weighted = $normalized === null ? null : $normalized * $weight;
            $group = 'CLO'.(int) $code;
            $clo[$group] ??= ['weight' => 0, 'weighted' => 0, 'plo' => $plo];
            $clo[$group]['weight'] += $weight;
            $subtotal = $clo[$group]['weighted'];
            $clo[$group]['weighted'] = $weighted === null || $subtotal === null ? null : $subtotal + $weighted;
            $criteria[] = compact('label', 'code', 'max', 'scores', 'average', 'weight', 'normalized', 'weighted');
        }
        ksort($clo);
        foreach ($clo as &$group) {
            $group['score'] = $group['weighted'] === null ? null : $group['weighted'] / $group['weight'];
        }
        unset($group);
        if ($engineer) {
            foreach ($judgeTotals as $i => &$total) {
                $bonus = $judges[$i]['chiTietDiem']['tc14'] ?? null;
                if ($total !== null) {
                    $total = min(10, $total + (is_numeric($bonus) && $bonus >= 0 && $bonus <= 1 ? (float) $bonus : 0));
                }
            }
            unset($total);
        }
        $complete = count($examiners) === 3 && !in_array(null, $judgeTotals, true);
        $final = $complete ? array_sum($judgeTotals) / 3 : null;
        $passed = $complete ? min(array_column($clo, 'score')) >= self::PASSING_THRESHOLD : null;

        return compact('criteria', 'clo', 'judgeTotals', 'complete', 'final', 'passed');
    }

    /**
     * Ngưỡng đạt chuẩn đầu ra CLO theo quy định Khoa CNTT - HUIT (thang điểm 10.0)
     */
    public const PASSING_THRESHOLD = 4.00;

    /**
     * Thứ tự vai trò chuẩn 3 Giảng viên trong Hội đồng bảo vệ
     */
    public const ROLE_ORDER = [
        'Chủ tịch' => 1,
        'Thư ký' => 2,
        'Phản biện' => 3,
        'Ủy viên' => 3,
    ];

    /**
     * Trích xuất và gom nhóm CLO động từ Rubric (Không hardcode các tiêu chí)
     */
    public function getCloGroupsFromRubric(array $rubric): array
    {
        $cloGroups = [];
        foreach ($rubric as $crit) {
            // Điểm khuyến khích không nằm trong chuẩn đầu ra bắt buộc 100%
            if (!empty($crit['is_bonus'])) {
                continue;
            }
            $baseClo = preg_replace('/(\.[0-9]+)$/', '', $crit['clo']);
            if (!isset($cloGroups[$baseClo])) {
                $cloGroups[$baseClo] = [
                    'clo' => $baseClo,
                    'name' => "Chuẩn đầu ra {$baseClo}",
                    'criteria' => [],
                    'max_sum' => 0.0,
                    'plo' => $this->getPloMapping($baseClo),
                ];
            }
            $cloGroups[$baseClo]['criteria'][] = [
                'id' => $crit['id'],
                'stt' => $crit['stt'],
                'noi_dung' => strtok($crit['noi_dung'], "\n"),
                'clo' => $crit['clo'],
                'max' => (float)$crit['max'],
            ];
            $cloGroups[$baseClo]['max_sum'] += (float)$crit['max'];
        }
        ksort($cloGroups);
        return $cloGroups;
    }

    /**
     * Lấy danh sách 3 giảng viên sắp xếp chuẩn của Hội đồng: GV1 (Chủ tịch), GV2 (Thư ký), GV3 (Phản biện / Ủy viên)
     */
    public function getSortedExaminers(HoiDong $hoiDong): Collection
    {
        return $hoiDong->thanhViens->sortBy(function ($tv) {
            return self::ROLE_ORDER[$tv->VaiTro] ?? 4;
        })->values();
    }

    /**
     * Kiểm tra điều kiện Hội đồng đã có đủ 3 phiếu chấm hợp lệ (Đã hoàn thành) từ 3 giảng viên
     */
    public function kiemTraDu3PhieuCham(HoiDong $hoiDong, ?string $maHoSo = null): array
    {
        $hoiDong->loadMissing(['thanhViens.giangVien', 'hoSoBaoVes.nhom.thanhViens']);

        $sortedMembers = $this->getSortedExaminers($hoiDong);
        $totalMembers = $sortedMembers->count();

        // Lấy danh sách hồ sơ cần kiểm tra
        $hoSos = $maHoSo 
            ? $hoiDong->hoSoBaoVes->where('MaHoSo', $maHoSo)
            : $hoiDong->hoSoBaoVes;

        if ($hoSos->isEmpty()) {
            return [
                'du_3_phieu' => false,
                'total_members' => $totalMembers,
                'so_phieu_hoan_thanh' => 0,
                'danh_sach_thieu' => 'Hội đồng chưa có đề tài / hồ sơ bảo vệ',
                'message' => 'Hội đồng chưa có đề tài bảo vệ.',
            ];
        }

        $allStudentsCompleted = true;
        $missingExaminers = [];
        $totalCompletedSheets = 0;
        $totalExpectedSheets = 0;

        foreach ($hoSos as $hoSo) {
            $sinhViens = $hoSo->nhom?->thanhViens ?? collect();
            foreach ($sinhViens as $tvn) {
                $maSV = $tvn->MaSV;
                foreach ($sortedMembers as $idx => $tv) {
                    $totalExpectedSheets++;
                    $phieu = PhieuChamDiem::where('MaHoiDong', $hoiDong->MaHoiDong)
                        ->where('MaHoSo', $hoSo->MaHoSo)
                        ->where('MaGV', $tv->MaGV)
                        ->where('MaSV', $maSV)
                        ->where('TrangThai', 'Đã hoàn thành')
                        ->first();

                    if ($phieu) {
                        $totalCompletedSheets++;
                    } else {
                        $allStudentsCompleted = false;
                        $roleName = $tv->VaiTro ?? ('Giám khảo ' . ($idx + 1));
                        $gvName = $tv->giangVien?->HoTen ?? $tv->MaGV;
                        $key = "{$roleName} ({$gvName})";
                        if (!in_array($key, $missingExaminers)) {
                            $missingExaminers[] = $key;
                        }
                    }
                }
            }
        }

        $du3Phieu = $allStudentsCompleted && ($totalExpectedSheets > 0) && ($totalCompletedSheets === $totalExpectedSheets);

        return [
            'du_3_phieu'           => $du3Phieu,
            'total_members'        => $totalMembers,
            'total_expected'       => $totalExpectedSheets,
            'so_phieu_hoan_thanh'  => $totalCompletedSheets,
            'missing_examiners'    => $missingExaminers,
            'danh_sach_thieu'      => implode(', ', $missingExaminers),
            'message'              => $du3Phieu 
                ? 'Đã có đủ 3 phiếu chấm điểm hợp lệ từ cả 3 giảng viên Hội đồng.'
                : 'Chưa đủ 3 phiếu chấm điểm hợp lệ từ Hội đồng. Giảng viên còn thiếu: ' . implode(', ', $missingExaminers),
        ];
    }

    /**
     * Tính điểm CLO chi tiết cho 1 Sinh viên theo đúng nguyên tắc:
     * 1. Mỗi giảng viên chấm điểm độc lập -> Tính điểm từng CLO của GV1, GV2, GV3 (thang 10).
     * 2. Điểm CLO trung bình = (Điểm CLO GV1 + Điểm CLO GV2 + Điểm CLO GV3) / 3.
     * 3. Điểm CLO trung bình của 3 giảng viên là dữ liệu chính thức duy nhất.
     */
    public function calculateStudentClo(
        HoiDong $hoiDong,
        string $maSV,
        ?string $loaiKhoaLuan = null,
        ?array $rubric = null,
        ?Collection $phieuChams = null
    ): array {
        // Lấy hoặc xác định loại khóa luận và rubric
        if (!$loaiKhoaLuan) {
            $hoSo = $hoiDong->hoSoBaoVes->first();
            $loaiKhoaLuan = $hoSo?->xacDinhLoaiKhoaLuan() ?? 'KLCN';
        }

        if (!$rubric) {
            $customChucNang = $hoiDong->hoSoBaoVes->first()?->TieuChiChucNang;
            $rubric = ($loaiKhoaLuan === 'KLKS') 
                ? PhieuChamDiem::getRubricKLKS($customChucNang) 
                : PhieuChamDiem::getRubricKLCN($customChucNang);
        }

        $cloGroups = $this->getCloGroupsFromRubric($rubric);
        $sortedMembers = $this->getSortedExaminers($hoiDong);

        if (!$phieuChams) {
            $phieuChams = PhieuChamDiem::where('MaHoiDong', $hoiDong->MaHoiDong)
                ->where('MaSV', $maSV)
                ->get();
        } else {
            $phieuChams = $phieuChams->where('MaSV', $maSV);
        }

        // Lấy phiếu của từng giám khảo theo thứ tự 3 thành viên
        $examinerData = [];
        $completedCount = 0;
        foreach ($sortedMembers as $idx => $tv) {
            $gvLabel = 'GV' . ($idx + 1);
            $p = $phieuChams->firstWhere('MaGV', $tv->MaGV);
            $isFinal = ($p && $p->TrangThai === 'Đã hoàn thành');
            if ($isFinal) {
                $completedCount++;
            }

            $rawChiTiet = $p ? (is_array($p->ChiTietDiem) ? $p->ChiTietDiem : json_decode($p->ChiTietDiem, true) ?? []) : [];

            $examinerData[$gvLabel] = [
                'label'       => $gvLabel,
                'maGV'        => $tv->MaGV,
                'hoTen'       => $tv->giangVien?->HoTen ?? $tv->MaGV,
                'vaiTro'      => $tv->VaiTro ?? 'Thành viên',
                'trangThai'   => $p?->TrangThai ?? 'Chưa chấm',
                'isFinal'     => $isFinal,
                'diemTong'    => $p ? (float)$p->Diem : null,
                'chiTietDiem' => $rawChiTiet,
                'cloScores'   => [],
            ];
        }

        $isOfficial = ($completedCount === 3);

        // BƯỚC 0: TÍNH ĐIỂM TRUNG BÌNH VÀ CÔNG THỨC CHO TỪNG TIÊU CHÍ RUBRIC
        $critAverages = [];
        $critFormulas = [];
        $examinersList = [];
        foreach ($examinerData as $ex) {
            $examinersList[] = [
                'maGV'        => $ex['maGV'],
                'hoTen'       => $ex['hoTen'],
                'vaiTro'      => $ex['vaiTro'],
                'trangThai'   => $ex['trangThai'],
                'isFinal'     => $ex['isFinal'],
                'diem'        => $ex['diemTong'],
                'chiTietDiem' => $ex['chiTietDiem'],
            ];
        }

        foreach ($rubric as $crit) {
            $cId = $crit['id'];
            $critScores = [];
            $critParts = [];
            foreach (['GV1', 'GV2', 'GV3'] as $gvLabel) {
                $ex = $examinerData[$gvLabel] ?? null;
                if ($ex && isset($ex['chiTietDiem'][$cId]) && is_numeric($ex['chiTietDiem'][$cId])) {
                    $val = (float)$ex['chiTietDiem'][$cId];
                    if ($ex['isFinal'] || !$isOfficial) {
                        $critScores[] = $val;
                        $critParts[] = number_format($val, 2);
                    }
                }
            }
            $critCnt = count($critScores);
            $critAvg = ($critCnt > 0) ? round(array_sum($critScores) / $critCnt, 2) : 0.0;
            $critAverages[$cId] = $critAvg;
            $critFormulas[$cId] = ($critCnt > 0)
                ? "(" . implode(' + ', $critParts) . ") / {$critCnt} = " . number_format($critAvg, 2)
                : "Chưa có điểm giám khảo";
        }

        // BƯỚC 1: TÍNH ĐIỂM CLO ĐỘC LẬP CHO TỪNG GIẢNG VIÊN (GV1, GV2, GV3)
        foreach ($examinerData as $gvLabel => &$ex) {
            $chiTiet = $ex['chiTietDiem'];
            foreach ($cloGroups as $code => $g) {
                if (empty($ex['trangThai']) || $ex['trangThai'] === 'Chưa chấm') {
                    $ex['cloScores'][$code] = null;
                    continue;
                }
                $sum = 0.0;
                foreach ($g['criteria'] as $c) {
                    $cId = $c['id'];
                    if (isset($chiTiet[$cId]) && is_numeric($chiTiet[$cId])) {
                        $sum += (float)$chiTiet[$cId];
                    }
                }
                // Công thức quy đổi thang 10 cho từng giảng viên: (Tổng điểm GV chấm / Tổng điểm tối đa CLO) * 10
                $score10 = ($g['max_sum'] > 0) ? round(($sum / $g['max_sum']) * 10, 2) : 0.0;
                $ex['cloScores'][$code] = [
                    'raw_sum' => round($sum, 2),
                    'max_sum' => $g['max_sum'],
                    'score'   => $score10,
                ];
            }
        }
        unset($ex);

        $audit = self::auditSchoolScores($examinerData, $loaiKhoaLuan === 'KLKS');
        $isOfficial = $audit['complete'];
        $allPassed = $audit['passed'] === true;
        $passedCount = 0;
        $cloScores = [];
        $totalCloCount = count($audit['clo']);
        foreach ($audit['clo'] as $code => $group) {
            $value = $group['score'];
            $pass = $value !== null && $value >= self::PASSING_THRESHOLD;
            $passedCount += (int) $pass;
            $gvScores = [];
            foreach (['GV1', 'GV2', 'GV3'] as $label) {
                $examiner = $examinerData[$label] ?? [];
                $gvScores[$label] = [
                    'hoTen' => $examiner['hoTen'] ?? $label,
                    'vaiTro' => $examiner['vaiTro'] ?? '',
                    'isFinal' => $examiner['isFinal'] ?? false,
                    'score' => $examiner['cloScores'][$code]['score'] ?? null,
                ];
            }
            $cloScores[$code] = [
                'clo' => $code,
                'name' => $cloGroups[$code]['name'],
                'plo' => $group['plo'],
                'max_sum' => $group['weight'] * 10,
                'gv1' => $gvScores['GV1']['score'],
                'gv2' => $gvScores['GV2']['score'],
                'gv3' => $gvScores['GV3']['score'],
                'gvScores' => $gvScores,
                'score' => $value,
                'passed' => $pass,
                'status' => $value === null ? 'Chưa đủ dữ liệu' : ($pass ? 'Đạt' : 'Chưa đạt'),
                'formula' => $value === null ? 'Chưa đủ dữ liệu'
                    : sprintf('%.6g / %.6g = %.2f', $group['weighted'], $group['weight'], $value),
            ];
        }

        $passRate = ($totalCloCount > 0) ? round(($passedCount / $totalCloCount) * 100, 1) : 0.0;

        // Cấu trúc dữ liệu tóm tắt để lưu vào DB (DiemCLO & LichSuDieuChinh)
        $summaryForDb = [
            'maSV'                 => $maSV,
            'loaiKhoaLuan'         => $loaiKhoaLuan,
            'is_official'          => $isOfficial,
            'completed_examiners'  => $completedCount,
            'total_examiners'      => 3,
            'cloScores'            => $cloScores,
            'all_clo_passed'       => $allPassed && $isOfficial,
            'passed_clo_count'     => $passedCount,
            'total_clo_count'      => $totalCloCount,
            'clo_pass_rate'        => $passRate,
            'calculated_at'        => now()->toDateTimeString(),
        ];

        return [
            'maSV'                 => $maSV,
            'loaiKhoaLuan'         => $loaiKhoaLuan,
            'is_official'          => $isOfficial,
            'completed_examiners'  => $completedCount,
            'examinerData'         => $examinerData,
            'audit'                => $audit,
            'examiners'            => $examinersList,
            'critAverages'         => $critAverages,
            'critFormulas'         => $critFormulas,
            'cloGroups'            => $cloGroups,
            'cloScores'            => $cloScores,
            'all_clo_passed'       => $allPassed,
            'passed_clo_count'     => $passedCount,
            'total_clo_count'      => $totalCloCount,
            'clo_pass_rate'        => $passRate,
            'summary_for_db'       => $summaryForDb,
        ];
    }

    /**
     * Lưu kết quả điểm CLO trung bình vào cơ sở dữ liệu (KetQuaSinhVien)
     * Đảm bảo tính nhất quán dữ liệu giữa phiếu chấm, màn hình tổng hợp và file xuất báo cáo.
     */
    public function saveStudentCloToDatabase(
        string $maSV,
        string $maHoiDong,
        string $maHocKy,
        array $cloResult
    ): ?KetQuaSinhVien {
        $kq = KetQuaSinhVien::where('MaSV', $maSV)
            ->where('MaHocKy', $maHocKy)
            ->first();

        if (!$kq) {
            return null;
        }

        $summary = $cloResult['summary_for_db'] ?? [];

        // Lưu vào cột DiemCLO chuyên biệt
        $kq->DiemCLO = $summary;

        // Đồng thời đồng bộ vào LichSuDieuChinh['diem_clo'] để tương thích ngược 100%
        $lichSu = $kq->LichSuDieuChinh ?? [];
        $lichSu['diem_clo'] = $summary;
        $kq->LichSuDieuChinh = $lichSu;

        $kq->save();
        return $kq;
    }

    /**
     * Tính toán và tổng hợp dữ liệu CLO toàn bộ sinh viên trong Hội đồng phục vụ Báo cáo & Hiển thị
     */
    public function calculateCouncilClo(HoiDong $hoiDong, ?string $loaiKhoaLuan = null, bool $persist = true): array
    {
        $hoiDong->loadMissing([
            'thanhViens.giangVien',
            'hoSoBaoVes.nhom.thanhViens.sinhVien.lop.nganh',
            'hoSoBaoVes.nhom.thanhViens.sinhVien.nganh',
            'hoSoBaoVes.nhom.deTai.giangVien',
            'hoSoBaoVes.nhom.deTai.hocPhan',
            'hocKy',
        ]);

        if (!$loaiKhoaLuan) {
            $firstHoSo = $hoiDong->hoSoBaoVes->first();
            $loaiKhoaLuan = $firstHoSo?->xacDinhLoaiKhoaLuan() ?? 'KLCN';
        }

        $customChucNang = $hoiDong->hoSoBaoVes->first()?->TieuChiChucNang;
        $rubric = ($loaiKhoaLuan === 'KLKS') 
            ? PhieuChamDiem::getRubricKLKS($customChucNang) 
            : PhieuChamDiem::getRubricKLCN($customChucNang);

        $cloGroups = $this->getCloGroupsFromRubric($rubric);
        $sortedMembers = $this->getSortedExaminers($hoiDong);
        $phieuQuery = PhieuChamDiem::where('MaHoiDong', $hoiDong->MaHoiDong);
        if (!$persist) $phieuQuery->where('TrangThai', 'Đã hoàn thành');
        $allPhieuChams = $phieuQuery->get();

        $checkDuPhieu = $this->kiemTraDu3PhieuCham($hoiDong);

        $students = [];
        $stt = 1;
        $totalStudents = 0;
        $passedStudents = 0;

        foreach ($hoiDong->hoSoBaoVes as $hoSo) {
            $nhom = $hoSo->nhom;
            $deTai = $nhom?->deTai;
            $thanhViens = $nhom?->thanhViens ?? collect();

            foreach ($thanhViens as $tvn) {
                $sv = $tvn->sinhVien;
                if (!$sv) continue;

                $totalStudents++;
                $cloResult = $this->calculateStudentClo($hoiDong, $sv->MaSV, $loaiKhoaLuan, $rubric, $allPhieuChams);

                if ($cloResult['all_clo_passed']) {
                    $passedStudents++;
                }

                // Tách họ và tên chuẩn tiếng Việt
                $hoTenParts = preg_split('/\s+/', trim($sv->HoTen ?? ''));
                $ten = !empty($hoTenParts) ? array_pop($hoTenParts) : '';
                $ho = implode(' ', $hoTenParts);

                $finalScore = $cloResult['audit']['final'];

                // Lưu kết quả CLO vào DB nếu đã có dữ liệu hợp lệ
                $hocKy = $hoiDong->hocKy ?? HocKy::where('TrangThai', true)->first();
                $maHocKy = $hocKy?->MaHocKy ?? 'HK_DEF';
                if ($persist) $this->saveStudentCloToDatabase($sv->MaSV, $hoiDong->MaHoiDong, $maHocKy, $cloResult);

                $students[] = [
                    'stt'                 => $stt++,
                    'maSV'                => $sv->MaSV,
                    'ho'                  => $ho,
                    'ten'                 => $ten,
                    'hoTen'               => $sv->HoTen,
                    'lop'                 => $sv->lop?->TenLop ?? 'N/A',
                    'maNganh'             => CloRoundReportService::studentMajor($sv),
                    'nganh'               => $sv->nganh?->TenNganh ?? $sv->lop?->nganh?->TenNganh ?? 'Chưa xác định ngành',
                    'tenDeTai'            => $deTai?->TenDeTai ?? 'Chưa cập nhật',
                    'maHoSo'              => $hoSo->MaHoSo,
                    'is_official'         => $cloResult['is_official'],
                    'completed_examiners' => $cloResult['completed_examiners'],
                    'examinerData'        => $cloResult['examinerData'],
                    'audit'               => $cloResult['audit'],
                    'examiners'           => $cloResult['examiners'],
                    'critAverages'        => $cloResult['critAverages'],
                    'critFormulas'        => $cloResult['critFormulas'],
                    'cloScores'           => $cloResult['cloScores'],
                    'allPassed'           => $cloResult['all_clo_passed'],
                    'allCloPassed'        => $cloResult['all_clo_passed'],
                    'all_clo_passed'      => $cloResult['all_clo_passed'],
                    'status_label'        => !$cloResult['is_official'] ? 'Chưa đủ dữ liệu' : ($cloResult['all_clo_passed'] ? 'Đạt' : 'Chưa đạt'),
                    'clo_pass_rate'       => $cloResult['clo_pass_rate'],
                    'finalScore'          => $finalScore,
                    'summary_for_db'      => $cloResult['summary_for_db'],
                ];
            }
        }

        $overallPassRate = ($totalStudents > 0) ? round(($passedStudents / $totalStudents) * 100, 1) : 0.0;

        return [
            'hoiDong'                 => $hoiDong,
            'loaiKhoaLuan'            => $loaiKhoaLuan,
            'rubric'                  => $rubric,
            'cloGroups'               => $cloGroups,
            'sortedMembers'           => $sortedMembers,
            'checkDuPhieu'            => $checkDuPhieu,
            'has_enough_3_examiners'  => $checkDuPhieu['du_3_phieu'],
            'total_students'          => $totalStudents,
            'passed_students'         => $passedStudents,
            'overall_pass_rate'       => $overallPassRate,
            'students'                => $students,
        ];
    }

    /**
     * Ánh xạ chuẩn đầu ra CTĐT (PLO) từ chuẩn đầu ra học phần (CLO)
     */
    public function getPloMapping(string $baseClo): string
    {
        return match ($baseClo) {
            'CLO1' => 'PLO1',
            'CLO2' => 'PLO3',
            'CLO3' => 'PLO4',
            'CLO4' => 'PLO6',
            'CLO5' => 'PLO10',
            'CLO6' => 'PLO12',
            default => 'PLO1',
        };
    }
}
