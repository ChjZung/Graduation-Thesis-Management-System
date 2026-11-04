<?php
namespace Tests\Feature;

use App\Models\{HoiDong, HoSoBaoVe, HocKy, Nhom, DeTai, SinhVien, TaiKhoan, GiangVien, ThanhVienNhom, ThanhVienHoiDong, PhieuChamDiem, KetQuaSinhVien};
use App\Services\{CloRoundReportService, CloReportExportService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CloRoundReportTest extends TestCase
{
    use DatabaseTransactions;
    private array $scope;
    private $advisor;
    private array $councils = [];

    protected function setUp(): void
    {
        parent::setUp();
        $semester = HocKy::firstOrFail()->replicate();
        $semester->MaHocKy = 'HK_CLO_'.bin2hex(random_bytes(3)); $semester->save();
        $teachers = GiangVien::limit(3)->get();
        $this->advisor = GiangVien::whereNotIn('MaGV', $teachers->pluck('MaGV'))->whereHas('taiKhoan')->firstOrFail();
        $topic = DeTai::whereNotNull('MaNganh')->firstOrFail();
        $this->scope = ['MaHocKy' => $semester->MaHocKy, 'MaNganh' => $topic->MaNganh, 'DotBaoVe' => 'Đợt CLO kiểm thử', 'loai' => 'KLCN', 'format' => 'xlsx'];
        foreach (SinhVien::limit(2)->get() as $i => $student) {
            $student->update(['MaNganh' => $this->scope['MaNganh']]);
            $hd = HoiDong::firstOrFail()->replicate();
            $hd->forceFill(['MaHoiDong' => 'HD_CLO_'.bin2hex(random_bytes(3)), 'MaHocKy' => $semester->MaHocKy, 'DotBaoVe' => $this->scope['DotBaoVe']])->save();
            $this->councils[] = $hd;
            $dt = $topic->replicate(); $dt->forceFill(['MaDeTai' => 'DT_CLO_'.bin2hex(random_bytes(3)), 'MaGV' => $i === 0 ? $this->advisor->MaGV : GiangVien::whereNotIn('MaGV', $teachers->pluck('MaGV')->push($this->advisor->MaGV))->firstOrFail()->MaGV, 'MaHocKy' => $semester->MaHocKy])->save();
            $group = Nhom::firstOrFail()->replicate(); $group->forceFill(['MaNhom' => 'N_CLO_'.bin2hex(random_bytes(3)), 'MaHocKy' => $semester->MaHocKy])->save();
            $registration = \App\Models\PhieuDangKy::firstOrFail()->replicate(); $registration->forceFill(['MaDangKy' => 'PD_CLO_'.bin2hex(random_bytes(3)), 'MaNhom' => $group->MaNhom, 'MaDeTai' => $dt->MaDeTai])->save();
            ThanhVienNhom::create(['MaNhom' => $group->MaNhom, 'MaSV' => $student->MaSV, 'VaiTro' => 'Trưởng nhóm']);
            $hs = HoSoBaoVe::firstOrFail()->replicate(); $hs->forceFill(['MaHoSo' => 'HS_CLO_'.bin2hex(random_bytes(3)), 'MaNhom' => $group->MaNhom, 'MaHoiDong' => $hd->MaHoiDong, 'LoaiKhoaLuan' => 'KLCN'])->save();
            foreach ($teachers as $j => $teacher) {
                ThanhVienHoiDong::create(['MaHoiDong' => $hd->MaHoiDong, 'MaGV' => $teacher->MaGV, 'VaiTro' => ['Chủ tịch','Thư ký','Phản biện'][$j]]);
                $scores = []; foreach (PhieuChamDiem::getRubricKLCN() as $criterion) $scores[$criterion['id']] = empty($criterion['is_bonus']) ? $criterion['max'] * ($i === 0 ? 0.8 : 0.2) : 0;
                PhieuChamDiem::create(['MaPhieuChamDiem' => 'PC_CLO_'.bin2hex(random_bytes(4)), 'MaHoiDong' => $hd->MaHoiDong, 'MaHoSo' => $hs->MaHoSo, 'MaSV' => $student->MaSV, 'MaGV' => $teacher->MaGV, 'MaDeTai' => $dt->MaDeTai, 'MaHocKy' => $semester->MaHocKy, 'LoaiKhoaLuan' => 'KLCN', 'TrangThai' => 'Đã hoàn thành', 'Diem' => $i === 0 ? 8 : 2, 'ChiTietDiem' => $scores, 'NgayCham' => now()]);
            }
        }
        $this->loginRole('Admin');
    }

    private function loginRole(string $role): void
    {
        $user = TaiKhoan::whereHas('vaiTro', fn($q) => $q->whereIn('TenVaiTro', $role === 'Admin' ? ['Admin','Giáo vụ'] : [$role]))->firstOrFail();
        $user->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]); $this->actingAs($user);
    }

    public function test_all_report_previews_render_with_string_group_keys(): void
    {
        foreach (range(1, 10) as $number) {
            $template = sprintf('mau_%02d', $number);
            $data = app(\App\Services\ThongKeService::class)->thongKeTongHop(['MaHocKy' => $this->scope['MaHocKy'], 'mau_bao_cao' => $template]);
            $data['canhBaoChuaHoanTat'] = 'CANH_BAO_KHONG_HIEN_THI';
            $html = view('thongke_baocao.partials.preview_content', ['data' => $data, 'mauBaoCao' => $template, 'config' => []])->render();
            $this->assertStringContainsString('a4-preview-paper', $html, $template);
            $this->assertStringNotContainsString('CANH_BAO_KHONG_HIEN_THI', $html);
            if ($number === 9 || $number === 10) {
                $groups = $number === 9 ? $data['statsTheoBoMon'] : $data['statsTheoHocPhan'];
                $this->assertNotEmpty($groups);
                $this->assertIsString(array_key_first($groups));
                $this->assertStringContainsString('<td>1</td>', $html);
                $pdfTemplate = $number === 9 ? 'mau_09_bo_mon' : 'mau_10_hoc_phan';
                $this->assertNotEmpty(view('thongke_baocao.pdf.'.$pdfTemplate, $data)->render());
            }
        }
    }

    public function test_admin_and_dean_share_fixed_previews_for_all_ten_templates(): void
    {
        foreach (['Admin' => 'admin', 'Trưởng khoa' => 'truongkhoa'] as $role => $prefix) {
            $this->loginRole($role);
            $this->get(route($prefix.'.thongke_baocao.index', ['MaHocKy' => $this->scope['MaHocKy']]))
                ->assertOk()->assertSee('modalHeaderMauTitle', false);
            foreach (range(1, 10) as $number) {
                $response = $this->getJson(route($prefix.'.thongke_baocao.preview_report', [
                    'MaHocKy' => $this->scope['MaHocKy'], 'mau_bao_cao' => sprintf('mau_%02d', $number),
                ]))->assertOk()->assertJsonPath('status', 'success');
                $this->assertStringContainsString('a4-preview-paper', $response->json('html'));
                $this->assertStringNotContainsString('chưa tính vào tỷ lệ', $response->json('html'));
            }
        }
    }

    public function test_student_outcome_requires_all_official_clos_and_keeps_score_letter(): void
    {
        $dossier = $this->councils[0]->hoSoBaoVes()->firstOrFail();
        $student = $dossier->nhom->thanhViens->first()->sinhVien;
        $result = KetQuaSinhVien::create(['MaKetQua' => 'KQ_CLO_'.bin2hex(random_bytes(4)),
            'MaSV' => $student->MaSV, 'MaHoSo' => $dossier->MaHoSo, 'MaHocKy' => $this->scope['MaHocKy'],
            'DiemTongKet' => 5.75, 'DiemChinhThuc' => 5.75, 'TrangThai' => 'Đã công bố']);
        $service = app(\App\Services\StudentOutcomeService::class);
        $this->assertTrue($service->evaluate($result, $dossier)['passed']);
        $groups = app(\App\Services\CloCalculationService::class)->getCloGroupsFromRubric(PhieuChamDiem::getRubricKLCN($dossier->TieuChiChucNang));
        $failedCode = array_key_first($groups);
        $criteria = array_column($groups[$failedCode]['criteria'], 'id');
        foreach (PhieuChamDiem::where('MaHoSo', $dossier->MaHoSo)->get() as $ballot) {
            $scores = $ballot->ChiTietDiem;
            foreach ($criteria as $id) $scores[$id] = 0;
            $ballot->update(['ChiTietDiem' => $scores]);
        }
        $before = $result->refresh()->getAttributes();
        $outcome = $service->evaluate($result, $dossier);
        $this->assertFalse($outcome['passed']);
        $this->assertContains($failedCode, $outcome['failed_clos']);
        $this->assertSame('C', $result->diem_chu);
        $this->assertSame($before, $result->fresh()->getAttributes());
        $statistics = app(\App\Services\ThongKeService::class)->thongKeTongHop(['MaHocKy' => $this->scope['MaHocKy'], 'mau_bao_cao' => 'mau_03']);
        $statStudent = collect($statistics['danhSachTatCaSV'])->firstWhere('maSV', $student->MaSV);
        $this->assertNotNull($statStudent);
        $this->assertFalse($statStudent['isDat']);
        $this->assertTrue($statStudent['isDuPhieu']);
        $this->assertSame('C', $statStudent['diemChu']);
        $this->assertEquals(5.75, $statStudent['diemTongKet']);
        $this->assertContains($failedCode, $statStudent['failedClos']);
        $this->assertSame(0, $statistics['kpi']['tongSVDat']);
        $this->assertSame(2, $statistics['kpi']['tongSVChuaDat']);
        foreach (['mau_03', 'mau_04', 'mau_06'] as $template) {
            $data = app(\App\Services\ThongKeService::class)->thongKeTongHop(['MaHocKy' => $this->scope['MaHocKy'], 'mau_bao_cao' => $template]);
            $html = view('thongke_baocao.partials.preview_content', ['data' => $data, 'mauBaoCao' => $template, 'config' => []])->render();
            if ($template === 'mau_03') $this->assertStringNotContainsString($student->MaSV, $html);
            else {
                $this->assertStringContainsString($student->MaSV, $html);
                $this->assertStringContainsString('Chưa đạt', $html);
            }
        }
        // Select this test group as the student's current active membership.
        ThanhVienNhom::where('MaSV', $student->MaSV)->update(['TrangThai' => 'da_roi']);
        ThanhVienNhom::where('MaSV', $student->MaSV)->where('MaNhom', $dossier->MaNhom)->update(['TrangThai' => 'da_tham_gia']);
        $dossier->update(['TrangThaiKetQua' => 'DaCongBo']);
        $user = $student->taiKhoan;
        $user->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->actingAs($user)->get(route('sinhvien.ketqua.index'))->assertOk()
            ->assertViewHas('outcomes', fn($rows) => $rows[$student->MaSV]['passed'] === false)
            ->assertSee('CLO chưa đạt:')->assertSee('Không đạt')->assertSee('5.75');
        PhieuChamDiem::where('MaHoSo', $dossier->MaHoSo)->firstOrFail()->update(['TrangThai' => 'Nháp']);
        $this->assertNull($service->evaluate($result, $dossier)['passed']);
    }

    public function test_round_is_optional_but_semester_and_major_are_required_and_student_is_blocked(): void
    {
        $this->get(route('clo.reports.index'))->assertRedirect(route('admin.thongke_baocao.index', ['clo_export' => 1]));
        $this->get(route('clo.reports.index', ['popup' => 1]))->assertOk()->assertSee('Đợt bảo vệ')->assertSee('data-clo-round-form', false)->assertDontSee('<nav', false);
        $this->get(route('clo.reports.export', array_diff_key($this->scope, ['DotBaoVe' => 1])))->assertOk();
        $this->getJson(route('clo.reports.export', array_diff_key($this->scope, ['MaHocKy' => 1, 'MaNganh' => 1])))->assertStatus(422)->assertJsonValidationErrors(['MaHocKy', 'MaNganh']);
        $this->loginRole('Sinh viên'); $this->get(route('clo.reports.index'))->assertForbidden();
        $this->get(route('clo.reports.export', $this->scope))->assertForbidden();
    }

    public function test_round_collects_all_councils_and_formulas_recalculate_without_changing_grades(): void
    {
        $before = KetQuaSinhVien::all()->map->getAttributes()->all();
        $report = app(CloRoundReportService::class)->prepare(auth()->user(), $this->scope['MaHocKy'], $this->scope['DotBaoVe'], $this->scope['MaNganh'], 'KLCN');
        $this->assertCount(2, $report['students']);
        $book = app(CloReportExportService::class)->exportRound($report);
        $this->assertSame(5, $book->getSheetCount());
        $sheet = $book->getSheet(0); $title = null;
        foreach ($sheet->getRowIterator() as $row) if ($sheet->getCell('C'.$row->getRowIndex())->getValue() === 'THỐNG KÊ CLO ĐÁNH GIÁ CLĐT') $title = $row->getRowIndex();
        $this->assertNotNull($title);
        $this->assertEquals(.4, $sheet->getCell('G'.($title+2))->getValue());
        $this->assertEquals(4, $sheet->getCell('G'.($title+3))->getCalculatedValue());
        $this->assertEquals(1, $sheet->getCell('G'.($title+4))->getCalculatedValue());
        $this->assertEquals(.5, $sheet->getCell('G'.($title+5))->getCalculatedValue());
        $this->assertSame('ĐẠT', $sheet->getCell('G'.($title+6))->getCalculatedValue());
        $sheet->setCellValue('G12', 0); $sheet->setCellValue('G13', 0);
        \PhpOffice\PhpSpreadsheet\Calculation\Calculation::getInstance($book)->clearCalculationCache();
        $this->assertSame('KHÔNG ĐẠT', $sheet->getCell('G'.($title+6))->getCalculatedValue());
        $this->assertSame($before, KetQuaSinhVien::all()->map->getAttributes()->all());
    }

    public function test_supervisor_exports_whole_round_and_missing_scores_cannot_be_bypassed(): void
    {
        $user = $this->advisor->taiKhoan; $user->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]); $this->actingAs($user);
        $this->get(route('clo.reports.index'))->assertRedirect();
        $this->get(route('clo.reports.index', ['popup' => 1]))->assertOk();
        $prepared = app(CloRoundReportService::class)->prepare($user, $this->scope['MaHocKy'], $this->scope['DotBaoVe'], $this->scope['MaNganh'], 'KLCN');
        $this->assertCount(2, $prepared['students']);
        $response = $this->get(route('clo.reports.export', $this->scope))->assertOk();
        $bytes = $response->streamedContent(); $this->assertStringStartsWith('PK', $bytes);
        file_put_contents('C:/Users/hochidung/Documents/Codex/2026-10-25/b-n-v-o-c-laragon/work/clo_round_export.xlsx', $bytes);
        $pdf = $this->get(route('clo.reports.export', array_merge($this->scope, ['format' => 'pdf'])))->assertOk()->assertHeader('Content-Type','application/pdf');
        file_put_contents('C:/Users/hochidung/Documents/Codex/2026-10-25/b-n-v-o-c-laragon/work/clo_round_export.pdf', $pdf->getContent());
        PhieuChamDiem::where('MaHoiDong', $this->councils[1]->MaHoiDong)->firstOrFail()->update(['TrangThai' => 'Nháp']);
        $this->get(route('clo.reports.export', $this->scope + ['force' => 1]))->assertRedirect()->assertSessionHasErrors('report');
    }

    public function test_exact_round_empty_scope_and_unauthorized_major(): void
    {
        $this->councils[1]->update(['DotBaoVe' => $this->scope['DotBaoVe'].' khác']);
        $service = app(CloRoundReportService::class);
        $report = $service->prepare(auth()->user(), $this->scope['MaHocKy'], $this->scope['DotBaoVe'], $this->scope['MaNganh'], 'KLCN');
        $this->assertCount(1, $report['students']);
        $this->get(route('clo.reports.export', array_merge($this->scope, ['DotBaoVe' => 'Đợt trống'])))->assertRedirect()->assertSessionHasErrors('report');
        $this->loginRole('Trưởng khoa');
        $this->get(route('clo.reports.index'))->assertRedirect();
        $this->get(route('clo.reports.index', ['popup' => 1]))->assertOk();
        $allowed = $service->allowedMajors(auth()->user())->pluck('MaNganh');
        $outside = \App\Models\Nganh::whereNotIn('MaNganh', $allowed)->first();
        if ($outside) $this->get(route('clo.reports.export', array_merge($this->scope, ['MaNganh' => $outside->MaNganh])))->assertForbidden();
    }

    public function test_major_and_degree_filters_do_not_mix_students(): void
    {
        $other = \App\Models\Nganh::where('MaNganh', '!=', $this->scope['MaNganh'])->firstOrFail();
        $this->councils[1]->hoSoBaoVes->first()->nhom->deTai->update(['MaNganh' => $other->MaNganh]);
        $report = app(CloRoundReportService::class)->prepare(auth()->user(), $this->scope['MaHocKy'], $this->scope['DotBaoVe'], $this->scope['MaNganh'], 'KLCN');
        $this->assertCount(2, $report['students'], 'Topic major must not override student majors');
        $student = $this->councils[1]->hoSoBaoVes->first()->nhom->thanhViens->first()->sinhVien;
        $student->update(['MaNganh' => $other->MaNganh]);
        $report = app(CloRoundReportService::class)->prepare(auth()->user(), $this->scope['MaHocKy'], $this->scope['DotBaoVe'], $this->scope['MaNganh'], 'KLCN');
        $this->assertCount(1, $report['students']);
        $this->get(route('clo.reports.export', array_merge($this->scope, ['loai' => 'KLKS'])))->assertRedirect()->assertSessionHasErrors('report');
    }

    public function test_student_major_takes_priority_over_class_topic_and_council_name(): void
    {
        $other = \App\Models\Nganh::where('MaNganh', '!=', $this->scope['MaNganh'])->firstOrFail();
        $class = \App\Models\Lop::firstOrFail()->replicate();
        $class->forceFill(['MaLop' => 'L_CLO_'.bin2hex(random_bytes(3)), 'MaNganh' => $other->MaNganh])->save();
        $student = $this->councils[0]->hoSoBaoVes->first()->nhom->thanhViens->first()->sinhVien;
        $student->update(['MaLop' => $class->MaLop]);
        $this->councils[0]->update(['TenHoiDong' => 'Hội đồng Ngành Khoa học Dữ liệu', 'MaNganh' => $other->MaNganh]);
        $this->councils[0]->hoSoBaoVes->first()->nhom->deTai->update(['MaNganh' => $other->MaNganh]);
        $service = app(CloRoundReportService::class);
        $report = $service->prepare(auth()->user(), $this->scope['MaHocKy'], $this->scope['DotBaoVe'], $this->scope['MaNganh'], 'KLCN');
        $this->assertCount(2, $report['students']);
        $this->get(route('clo.reports.index', ['popup' => 1]))->assertOk()->assertViewHas('options', fn($rows) => $rows->contains(fn($row) => $row['semester'] === $this->scope['MaHocKy'] && $row['major'] === $this->scope['MaNganh']));
        $student->update(['MaNganh' => null]);
        $this->assertSame($other->MaNganh, CloRoundReportService::studentMajor($student->fresh('lop')));
        $report = $service->prepare(auth()->user(), $this->scope['MaHocKy'], $this->scope['DotBaoVe'], $this->scope['MaNganh'], 'KLCN');
        $this->assertCount(1, $report['students']);
    }

    public function test_mixed_major_group_excludes_other_students_before_checking_scores(): void
    {
        $hs1 = $this->councils[0]->hoSoBaoVes->first(); $hs2 = $this->councils[1]->hoSoBaoVes->first();
        $other = \App\Models\Nganh::where('MaNganh', '!=', $this->scope['MaNganh'])->firstOrFail();
        $member = $hs2->nhom->thanhViens->first(); $member->sinhVien->update(['MaNganh' => $other->MaNganh]);
        $member->update(['MaNhom' => $hs1->MaNhom]);
        $report = app(CloRoundReportService::class)->prepare(auth()->user(), $this->scope['MaHocKy'], $this->scope['DotBaoVe'], $this->scope['MaNganh'], 'KLCN');
        $this->assertCount(1, $report['students']);
        $this->assertNotSame($member->MaSV, $report['students'][0]['maSV']);
        $this->assertCount(1, $report['councils'][0]['students']);
        $this->get(route('clo.reports.export', $this->scope))->assertOk();
    }

    public function test_examiner_can_export_even_when_not_the_topic_supervisor(): void
    {
        $member = $this->councils[0]->thanhViens()->whereHas('giangVien.taiKhoan.vaiTro', fn($q) => $q->where('TenVaiTro', 'Giảng viên'))->with('giangVien.taiKhoan')->firstOrFail();
        $user = $member->giangVien->taiKhoan;
        $user->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->actingAs($user);
        $this->assertNotSame($this->advisor->MaGV, $member->MaGV);
        $this->get(route('clo.reports.index', ['popup' => 1]))->assertOk()->assertViewHas('majors', fn($rows) => $rows->contains('MaNganh', $this->scope['MaNganh']));
        $prepared = app(CloRoundReportService::class)->prepare($user, $this->scope['MaHocKy'], $this->scope['DotBaoVe'], $this->scope['MaNganh'], 'KLCN');
        $this->assertCount(2, $prepared['students']);
        $this->get(route('clo.reports.export', $this->scope))->assertOk();
        $this->getJson(route('clo.reports.export', array_merge($this->scope, ['DotBaoVe' => 'Đợt không được phân công'])))->assertForbidden();
        PhieuChamDiem::where('MaHoiDong', $this->councils[1]->MaHoiDong)->firstOrFail()->update(['TrangThai' => 'Nháp']);
        $this->getJson(route('clo.reports.export', $this->scope))->assertStatus(422)->assertJsonValidationErrors('report');
    }

    public function test_three_scope_levels_and_selected_council_ignore_other_incomplete_councils(): void
    {
        $service = app(CloRoundReportService::class);
        $this->councils[1]->update(['DotBaoVe' => 'Đợt khác']);
        $all = $service->prepare(auth()->user(), $this->scope['MaHocKy'], null, $this->scope['MaNganh'], 'KLCN');
        $this->assertCount(2, $all['students']); $this->assertSame('major', $all['scope']['type']);
        $round = $service->prepare(auth()->user(), $this->scope['MaHocKy'], $this->scope['DotBaoVe'], $this->scope['MaNganh'], 'KLCN');
        $this->assertCount(1, $round['students']); $this->assertSame('round', $round['scope']['type']);
        PhieuChamDiem::where('MaHoiDong', $this->councils[1]->MaHoiDong)->update(['TrangThai' => 'Nháp']);
        $one = $service->prepare(auth()->user(), $this->scope['MaHocKy'], null, $this->scope['MaNganh'], 'KLCN', $this->councils[0]->MaHoiDong);
        $this->assertCount(1, $one['students']); $this->assertCount(1, $one['councils']); $this->assertSame('council', $one['scope']['type']);
        $book = app(CloReportExportService::class)->exportRound($one);
        $this->assertSame(3, $book->getSheetCount());
        $this->assertStringContainsString($this->councils[0]->MaHoiDong, $book->getSheet(0)->getCell('I7')->getValue());
        $scope = array_diff_key($this->scope, ['DotBaoVe' => 1]) + ['MaHoiDong' => $this->councils[0]->MaHoiDong];
        $this->get(route('clo.reports.export', $scope))->assertOk();
        $this->get(route('clo.reports.export', array_merge($scope, ['format' => 'pdf'])))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->getJson(route('clo.reports.export', array_merge($scope, ['DotBaoVe' => 'Đợt khác'])))->assertStatus(422)->assertJsonValidationErrors('MaHoiDong');
        $this->getJson(route('clo.reports.export', array_diff_key($this->scope, ['DotBaoVe' => 1])))->assertStatus(422)->assertJsonValidationErrors('report');
    }

    public function test_examiner_only_selects_own_councils_and_server_rejects_tampered_scope(): void
    {
        $member = $this->councils[0]->thanhViens()->whereHas('giangVien.taiKhoan.vaiTro', fn($q) => $q->where('TenVaiTro', 'Giảng viên'))->with('giangVien.taiKhoan')->firstOrFail();
        $user = $member->giangVien->taiKhoan;
        $user->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]); $this->actingAs($user);
        ThanhVienHoiDong::where('MaHoiDong', $this->councils[1]->MaHoiDong)->where('MaGV', $member->MaGV)->delete();
        $this->get(route('clo.reports.index', ['popup' => 1]))->assertOk()->assertViewHas('options', function($rows) {
            return $rows->contains('council', $this->councils[0]->MaHoiDong) && !$rows->contains('council', $this->councils[1]->MaHoiDong);
        });
        $this->get(route('clo.reports.export', $this->scope + ['MaHoiDong' => $this->councils[0]->MaHoiDong]))->assertOk();
        $this->getJson(route('clo.reports.export', $this->scope + ['MaHoiDong' => $this->councils[1]->MaHoiDong]))->assertForbidden();
        $this->getJson(route('clo.reports.export', array_merge($this->scope, ['MaHoiDong' => $this->councils[0]->MaHoiDong, 'MaHocKy' => HocKy::where('MaHocKy', '!=', $this->scope['MaHocKy'])->firstOrFail()->MaHocKy])))->assertStatus(422)->assertJsonValidationErrors('MaHoiDong');
    }
}
