<?php

namespace Tests\Feature;

use App\Models\GiangVien;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\HocKy;
use App\Models\KetQuaSinhVien;
use App\Models\LichSuChinhSuaDiem;
use App\Models\Nhom;
use App\Models\PhieuChamDiem;
use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\ThanhVienHoiDong;
use App\Models\ThanhVienNhom;
use App\Services\CloCalculationService;
use App\Services\GradeCalculationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ScoreEditingPolicyAndAuditLogTest extends TestCase
{
    use DatabaseTransactions;

    protected CloCalculationService $cloService;
    protected GradeCalculationService $gradeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertStringStartsWith('ai_stability_', \Illuminate\Support\Facades\DB::connection()->getDatabaseName());
        $this->cloService = app(CloCalculationService::class);
        $this->gradeService = app(GradeCalculationService::class);
    }

    public function test_result_confirmation_cannot_be_repeated_or_overwrite_published_results(): void
    {
        [$council, $teachers, $student, $dossier, $semester] = $this->createMockData();
        $result = KetQuaSinhVien::create([
            'MaKetQua' => 'KQ_ONCE_'.bin2hex(random_bytes(4)), 'MaSV' => $student->MaSV,
            'MaHoSo' => $dossier->MaHoSo, 'MaHocKy' => $semester->MaHocKy,
            'DiemHoiDongTB' => 8, 'DiemTongKet' => 8, 'TrangThai' => 'Đã đủ phiếu hợp lệ',
        ]);
        $this->assertTrue($this->gradeService->xacNhanKetQua($dossier->MaHoSo, 'Trưởng khoa', [$student->MaSV => 8.5], 'Chốt điểm'));
        $snapshot = $result->fresh()->getAttributes();
        $this->assertTrue($result->fresh()->daXacNhanKetQua());
        $this->assertFalse($this->gradeService->xacNhanKetQua($dossier->MaHoSo, 'Người bấm lại', [$student->MaSV => 2], 'Ghi đè'));
        $this->assertSame($snapshot, $result->fresh()->getAttributes());
        $user = TaiKhoan::whereHas('vaiTro', fn($q) => $q->where('TenVaiTro', 'Trưởng khoa'))->firstOrFail();
        $user->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->actingAs($user)->post(route('truongkhoa.ketqua.xacnhan'), [
            'MaHoSo' => $dossier->MaHoSo, 'dieu_chinh_diem' => [$student->MaSV => 1], 'ghi_chu' => 'Bấm lặp qua HTTP',
        ])->assertRedirect()->assertSessionHas('info');
        $this->assertSame($snapshot, $result->fresh()->getAttributes());
        $page = $this->get(route('truongkhoa.ketqua.hoidong', $council->MaHoiDong));
        $page->assertOk()->assertSee('Đã Xác Nhận Kết Quả')->assertDontSee('data-bs-target="#modalXacNhan_'.$dossier->MaHoSo.'"', false)
            ->assertDontSee('Công Bố Đề Tài');
        $dossier->update(['TrangThaiKetQua' => 'DaCongBo']);
        $result->refresh()->update(['TrangThai' => 'Đã công bố']);
        $snapshot = $result->fresh()->getAttributes();
        $this->assertFalse($this->gradeService->xacNhanKetQua($dossier->MaHoSo, 'Bấm lại sau công bố'));
        $this->assertSame('DaCongBo', $dossier->fresh()->TrangThaiKetQua);
        $this->assertSame($snapshot, $result->fresh()->getAttributes());
    }

    public function test_all_three_members_can_save_confirmed_ballots_without_losing_signature(): void
    {
        [$council,$teachers,$student,$dossier,$semester,$topic]=$this->createMockData();
        $council->update(['TrangThaiCongBo'=>'Đã công bố', 'ThoiGianKetThucSuaDiem'=>now()->subDay()]);
        $dossier->update(['TrangThaiChuanBiPhieu'=>'DaChuanBi','TieuChiChucNang'=>'+ Chức năng 1: Quản lý\n* Yêu cầu 1: Đăng nhập']);
        $scores=collect(PhieuChamDiem::getRubricKLCN())->mapWithKeys(fn($c)=>[$c['id']=>round($c['max']*.8,2)])->all();
        foreach($teachers as $teacher){
            $ballot=PhieuChamDiem::create(['MaPhieuChamDiem'=>'PCD_'.bin2hex(random_bytes(5)),
                'MaHoiDong'=>$council->MaHoiDong,'MaHoSo'=>$dossier->MaHoSo,'MaDeTai'=>$topic,'MaHocKy'=>$semester->MaHocKy,
                'MaGV'=>$teacher->MaGV,'MaSV'=>$student->MaSV,'LoaiKhoaLuan'=>'KLCN','TrangThai'=>'Đã hoàn thành',
                'Diem'=>8,'ChiTietDiem'=>$scores,'ChuKy'=>'retained-signature','NgayKy'=>now()->subHour(),'NgayXacNhan'=>now()->subHour()]);
            $confirmedAt=$ballot->NgayXacNhan->toDateTimeString();
            $this->actingAs($teacher->taiKhoan)->post(route('giangvien.chamdiem.store'),[
                'MaHoiDong'=>$council->MaHoiDong,'MaHoSo'=>$dossier->MaHoSo,'LoaiKhoaLuan'=>'KLCN','action_type'=>'luu_nhap',
                'diems'=>[$student->MaSV=>['ChiTietDiem'=>$scores]],'chu_ky'=>'',
            ])->assertSessionHas('success');
            $ballot->refresh();
            $this->assertSame('Đã hoàn thành',$ballot->TrangThai);
            $this->assertSame('retained-signature',$ballot->ChuKy);
            $this->assertSame($confirmedAt,$ballot->NgayXacNhan->toDateTimeString());
            $this->get(route('giangvien.hoidong.index',['tab'=>'chamdiem','hd'=>$council->MaHoiDong,'hoso'=>$dossier->MaHoSo]))
                ->assertOk()->assertSee('Chỉnh sửa phiếu chấm điểm')->assertSee('Lưu phiếu chấm điểm')
                ->assertSee('data-score-edit',false)->assertSee('data-score-save',false);
        }
    }

    public function test_secretary_configuration_locks_after_a_saved_draft_only_for_that_dossier(): void
    {
        [$council,$teachers,$student,$dossier,$semester,$topic]=$this->createMockData();
        $payload=['MaHoiDong'=>$council->MaHoiDong,'MaHoSo'=>$dossier->MaHoSo,'TieuChiChucNang'=>'+ Chức năng 1: Đăng nhập\n* Yêu cầu 1: Xác thực'];
        $this->actingAs($teachers[1]->taiKhoan)->postJson(route('giangvien.chamdiem.capNhatChucNang'),$payload)->assertOk();
        $criteria=$dossier->fresh()->TieuChiChucNang;
        PhieuChamDiem::create(['MaPhieuChamDiem'=>'PCD_'.bin2hex(random_bytes(5)),
            'MaHoiDong'=>$council->MaHoiDong,'MaHoSo'=>$dossier->MaHoSo,'MaDeTai'=>$topic,'MaHocKy'=>$semester->MaHocKy,
            'MaGV'=>$teachers[0]->MaGV,'MaSV'=>$student->MaSV,'LoaiKhoaLuan'=>'KLCN','TrangThai'=>'Lưu nháp','Diem'=>0,'ChiTietDiem'=>[]]);
        $payload['TieuChiChucNang']='Must not change';
        $this->postJson(route('giangvien.chamdiem.capNhatChucNang'),$payload)->assertStatus(409);
        $this->postJson(route('giangvien.chamdiem.chuanBiPhieu'),$payload)->assertStatus(409);
        $this->assertSame($criteria,$dossier->fresh()->TieuChiChucNang);
        $reason='';$this->assertTrue($council->canGrade($teachers[0],$dossier,$reason),$reason);
        $other=$dossier->replicate();$other->MaHoSo='HS_SCOPE_'.bin2hex(random_bytes(4));$other->save();
        $this->assertFalse($other->gradingHasStarted());
        $payload['MaHoSo']=$other->MaHoSo;
        $this->postJson(route('giangvien.chamdiem.capNhatChucNang'),$payload)->assertOk();
        $payload['MaHoiDong']=HoiDong::where('MaHoiDong','!=',$council->MaHoiDong)->value('MaHoiDong');
        $this->postJson(route('giangvien.chamdiem.chuanBiPhieu'),$payload)->assertStatus(403);
    }

    public function test_published_result_record_blocks_draft_writes_even_with_fake_unlock_token(): void
    {
        [$council,$teachers,$student,$dossier,$semester,$topic]=$this->createMockData();
        KetQuaSinhVien::create(['MaKetQua'=>'KQ_TEST_'.bin2hex(random_bytes(4)), 'MaSV'=>$student->MaSV,
            'MaHoSo'=>$dossier->MaHoSo,'MaHocKy'=>$semester->MaHocKy,'TrangThai'=>'Đã công bố','DiemTongKet'=>8,'DiemChinhThuc'=>8]);
        $this->actingAs($teachers[0]->taiKhoan)->postJson(route('giangvien.chamdiem.store'),[
            'MaHoiDong'=>$council->MaHoiDong,'MaHoSo'=>$dossier->MaHoSo,'LoaiKhoaLuan'=>'KLCN',
            'action_type'=>'luu_nhap','unlock_token'=>'forged','diems'=>[$student->MaSV=>['ChiTietDiem'=>[]]],
        ])->assertForbidden();
        $this->assertSame(0,PhieuChamDiem::where('MaHoSo',$dossier->MaHoSo)->count());
    }

    public function test_edit_permission_never_opens_before_council_start(): void
    {
        [$council,$teachers,$student,$dossier]=$this->createMockData();
        $council->update(['NgayBaoVe'=>now()->addDays(2),'ThoiGianBatDau'=>now()->addDays(2),'ThoiGianKetThuc'=>now()->addDays(2)->addHour(),
            'ThoiGianKetThucSuaDiem'=>now()->addDays(5),'TrangThaiMoKhoaSuaDiem'=>true]);
        $reason='';$this->assertFalse($council->canEditScore($teachers[0],$dossier,$reason));
        $this->assertStringContainsString('chưa đến',$reason);
    }

    public function test_saved_draft_remains_editable_after_deadline_but_new_ballot_does_not(): void
    {
        [$council,$teachers,$student,$dossier,$semester,$topic]=$this->createMockData();
        $council->update(['NgayBaoVe'=>now()->subDays(10),'ThoiGianBatDau'=>now()->subDays(10)->setTime(8,0),
            'ThoiGianKetThuc'=>now()->subDays(10)->setTime(17,0),'ThoiGianKetThucSuaDiem'=>now()->subDay()]);
        PhieuChamDiem::create(['MaPhieuChamDiem'=>'PCD_'.bin2hex(random_bytes(5)),
            'MaHoiDong'=>$council->MaHoiDong,'MaHoSo'=>$dossier->MaHoSo,'MaDeTai'=>$topic,'MaHocKy'=>$semester->MaHocKy,
            'MaGV'=>$teachers[0]->MaGV,'MaSV'=>$student->MaSV,'LoaiKhoaLuan'=>'KLCN','TrangThai'=>'Lưu nháp','Diem'=>8,'ChiTietDiem'=>[]]);
        $reason='';$this->assertTrue($council->canGrade($teachers[0],$dossier,$reason),$reason);
        $this->assertFalse($council->canGrade($teachers[1],$dossier,$reason));
    }

    public function test_recalculation_preserves_a_published_result_snapshot(): void
    {
        [$council,$teachers,$student,$dossier,$semester]=$this->createMockData();
        $result=KetQuaSinhVien::create(['MaKetQua'=>'KQ_TEST_'.bin2hex(random_bytes(4)), 'MaSV'=>$student->MaSV,
            'MaHoSo'=>$dossier->MaHoSo,'MaHocKy'=>$semester->MaHocKy,'TrangThai'=>'Đã công bố','DiemTongKet'=>8,'DiemChinhThuc'=>8]);
        $before=$result->refresh()->getAttributes();
        $this->gradeService->tongHopKetQuaHoiDong($council->MaHoiDong);
        $this->assertSame($before,$result->fresh()->getAttributes());
    }

    public function test_admin_report_filters_and_pdf_export_use_the_same_completed_result_data(): void
    {
        [$council,$teachers,$student,$dossier,$semester,$topic]=$this->createMockData();
        $service=app(\App\Services\ThongKeService::class);
        $filters=['MaHocKy'=>$semester->MaHocKy,'MaHoiDong'=>$council->MaHoiDong,'mau_bao_cao'=>'mau_01','ket_qua'=>'ChuaXong'];
        $scope=['role'=>'admin','maKhoa'=>null];
        $this->assertCount(1,$service->thongKeTongHop($filters,$scope)['danhSachTatCaSV']);
        foreach($teachers as $teacher){
            PhieuChamDiem::create(['MaPhieuChamDiem'=>'PCD_'.bin2hex(random_bytes(5)),
                'MaHoiDong'=>$council->MaHoiDong,'MaHoSo'=>$dossier->MaHoSo,'MaDeTai'=>$topic,'MaHocKy'=>$semester->MaHocKy,
                'MaGV'=>$teacher->MaGV,'MaSV'=>$student->MaSV,'LoaiKhoaLuan'=>'KLCN','TrangThai'=>'Đã hoàn thành','Diem'=>8,'ChiTietDiem'=>[]]);
        }
        $this->gradeService->tongHopKetQuaHoiDong($council->MaHoiDong);
        $this->assertCount(0,$service->thongKeTongHop($filters,$scope)['danhSachTatCaSV']);
        $filters['ket_qua']='Dat';
        $this->assertCount(1,$service->thongKeTongHop($filters,$scope)['danhSachTatCaSV']);
        $adminRole=\App\Models\VaiTro::firstOrCreate(['TenVaiTro'=>'Admin'],['MaVaiTro'=>'VT_RPT_AD']);
        $admin=$teachers[0]->taiKhoan->replicate();
        $admin->MaTK='TK_RPT_'.bin2hex(random_bytes(4));$admin->TenDangNhap=$admin->MaTK;
        $admin->MaGV=null;$admin->MaVaiTro=$adminRole->MaVaiTro;$admin->save();
        $response=$this->actingAs($admin)->get(route('admin.thongke_baocao.index',$filters));
        $response->assertOk();
        $this->assertCount(1,$response->viewData('data')['danhSachTatCaSV']);
        $this->getJson(route('admin.thongke_baocao.preview_report',$filters))->assertOk()->assertJsonPath('soSV',1);
        $pdf=$this->get(route('admin.thongke_baocao.export_pdf',$filters));
        $pdf->assertOk();
        $this->assertStringStartsWith('%PDF',$pdf->getContent());
    }

    protected function createMockData(): array
    {
        $hocKy = HocKy::where('TrangThai', true)->first() ?? HocKy::first();

        $maHd = 'HD_AUDIT_' . mt_rand(1000, 9999);
        $sysNow = HoiDong::getSystemNow();
        $hoiDong = HoiDong::create([
            'MaHoiDong'  => $maHd,
            'TenHoiDong' => "Hội Đồng Kiểm Toán {$maHd}",
            'MaHocKy'    => $hocKy->MaHocKy,
            'TrangThai'  => 'Đang chấm điểm',
            'NgayBaoVe'  => $sysNow->toDateString(),
            'ThoiGianBatDau'         => $sysNow->copy()->subHours(2),
            'ThoiGianKetThuc'        => $sysNow->copy()->addHours(6),
            'ThoiGianBatDauSuaDiem'  => $sysNow->copy()->subDays(1),
            'ThoiGianKetThucSuaDiem' => $sysNow->copy()->addDays(2),
        ]);

        $gvs = GiangVien::take(3)->get();
        if ($gvs->count() < 3) {
            $this->markTestSkipped('Cần tối thiểu 3 giảng viên để chạy test.');
        }

        $roles = ['Chủ tịch', 'Thư ký', 'Phản biện'];
        foreach ($gvs as $idx => $gv) {
            ThanhVienHoiDong::create([
                'MaHoiDong' => $maHd,
                'MaGV'      => $gv->MaGV,
                'VaiTro'    => $roles[$idx],
            ]);
        }

        $sv = SinhVien::first();
        if (!$sv) {
            $this->markTestSkipped('Cần có sinh viên để chạy test.');
        }

        $deTai = \App\Models\DeTai::first();
        $maDeTai = $deTai?->MaDeTai ?? 'DT01';

        $maNhom = 'NHOM_AUDIT_' . mt_rand(1000, 9999);
        $nhom = Nhom::create([
            'MaNhom'  => $maNhom,
            'TenNhom' => "Nhóm Kiểm Toán {$maNhom}",
            'MaHocKy' => $hocKy->MaHocKy,
            'MaDeTai' => $maDeTai,
        ]);

        ThanhVienNhom::create([
            'MaNhom' => $maNhom,
            'MaSV'   => $sv->MaSV,
            'VaiTro' => 'Trưởng nhóm',
        ]);

        $maHoSo = 'HS_AUDIT_' . mt_rand(1000, 9999);
        $hoSo = HoSoBaoVe::create([
            'MaHoSo'          => $maHoSo,
            'MaNhom'          => $maNhom,
            'MaDeTai'         => $maDeTai,
            'MaHoiDong'       => $maHd,
            'TrangThai'       => 'Đã duyệt bảo vệ',
            'TrangThaiKetQua' => 'ChuaDuPhieu',
            'LoaiKhoaLuan'    => 'KLCN',
        ]);

        return [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai];
    }

    /**
     * Yêu cầu 1: Giảng viên chỉ sửa phiếu của mình & KHÔNG được sửa nhận xét (nhận xét cũ được bảo toàn)
     */
    public function test_lecturer_can_edit_scores_but_comments_remain_strictly_unchanged(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockData();
        $gv1 = $gvs[0];

        $rubric = PhieuChamDiem::getRubricKLCN();
        $initialChiTiet = [];
        foreach ($rubric as $crit) {
            $initialChiTiet[$crit['id']] = round((float)$crit['max'] * 0.70, 2);
        }

        // Tạo phiếu ban đầu đã xác nhận với nhận xét ban đầu
        $originalComment = 'Nhận xét ban đầu: Sinh viên hoàn thành tốt mục tiêu đề tài.';
        $phieu1 = PhieuChamDiem::create([
            'MaPhieuChamDiem' => 'PCD_' . mt_rand(100000, 999999),
            'MaHoiDong'       => $hoiDong->MaHoiDong,
            'MaHoSo'          => $hoSo->MaHoSo,
            'MaDeTai'         => $maDeTai,
            'MaHocKy'         => $hocKy->MaHocKy,
            'MaGV'            => $gv1->MaGV,
            'MaSV'            => $sv->MaSV,
            'LoaiKhoaLuan'    => 'KLCN',
            'Diem'            => 7.0,
            'ChiTietDiem'     => $initialChiTiet,
            'NhanXet'         => $originalComment,
            'TrangThai'       => 'Đã hoàn thành',
            'NgayCham'        => now(),
            'NgayXacNhan'     => now(),
        ]);

        // Giảng viên đăng nhập và gửi request sửa điểm với điểm mới và nhận xét giả mạo mới
        $newChiTiet = $initialChiTiet;
        $firstCritId = $rubric[0]['id'];
        $newChiTiet[$firstCritId] = round((float)$rubric[0]['max'] * 0.95, 2); // Tăng điểm tiêu chí đầu tiên

        $userGv1 = $gv1->taiKhoan ?? TaiKhoan::create([
            'MaTK'        => 'TK_' . $gv1->MaGV,
            'TenDangNhap' => 'gv_test_' . $gv1->MaGV,
            'MatKhau'     => bcrypt('secret'),
            'MaVaiTro'    => 'VT02',
            'MaGV'        => $gv1->MaGV,
            'TrangThai'   => 1,
        ]);

        $response = $this->actingAs($userGv1)->post(route('giangvien.chamdiem.store'), [
            'MaHoiDong'    => $hoiDong->MaHoiDong,
            'MaHoSo'       => $hoSo->MaHoSo,
            'LoaiKhoaLuan' => 'KLCN',
            'action_type'  => 'xac_nhan_hoan_thanh',
            'diems'        => [
                $sv->MaSV => [
                    'MaSV'        => $sv->MaSV,
                    'ChiTietDiem' => $newChiTiet,
                    'NhanXet'     => 'CỐ TÌNH SỬA ĐỔI NHẬN XÉT TRÁI QUY CHẾ',
                ],
            ],
        ]);

        $response->assertRedirect();

        // Kiểm tra trong CSDL:
        $updatedPhieu = PhieuChamDiem::where('MaHoiDong', $hoiDong->MaHoiDong)
            ->where('MaGV', $gv1->MaGV)
            ->where('MaSV', $sv->MaSV)
            ->first();

        // 1. Nhận xét phải được giữ nguyên nhận xét ban đầu!
        $this->assertEquals($originalComment, $updatedPhieu->NhanXet, 'Nhận xét ban đầu phải được giữ nguyên không thay đổi khi sửa điểm.');

        // 2. Điểm tiêu chí phải được cập nhật giá trị mới
        $this->assertEquals($newChiTiet[$firstCritId], $updatedPhieu->ChiTietDiem[$firstCritId], 'Điểm tiêu chí phải được cập nhật mới.');

        // 3. Phải ghi nhận vết thay đổi trong LichSuChinhSuaDiem
        $log = LichSuChinhSuaDiem::where('MaPhieuChamDiem', $phieu1->MaPhieuChamDiem)
            ->where('MaSV', $sv->MaSV)
            ->where('MaGV', $gv1->MaGV)
            ->first();

        $this->assertNotNull($log, 'Hệ thống phải tự động ghi nhận lịch sử chỉnh sửa điểm.');
        $this->assertEquals($initialChiTiet[$firstCritId], $log->DiemCu, 'Điểm cũ trong lịch sử phải chính xác.');
        $this->assertEquals($newChiTiet[$firstCritId], $log->DiemMoi, 'Điểm mới trong lịch sử phải chính xác.');
    }

    /**
     * Yêu cầu 2: Hết thời hạn chỉnh sửa -> Tự động khóa và hiển thị thông báo
     */
    public function test_confirmed_ballot_can_be_saved_after_edit_window_before_publication(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockData();
        $gv1 = $gvs[0];

        // Đặt thời hạn chỉnh sửa đã kết thúc 2 tiếng trước
        $sysNow = HoiDong::getSystemNow();
        $hoiDong->update([
            'ThoiGianBatDauSuaDiem'  => $sysNow->copy()->subDays(2),
            'ThoiGianKetThucSuaDiem' => $sysNow->copy()->subHours(2),
        ]);

        PhieuChamDiem::create([
            'MaPhieuChamDiem' => 'PCD_' . mt_rand(100000, 999999),
            'MaHoiDong'       => $hoiDong->MaHoiDong,
            'MaHoSo'          => $hoSo->MaHoSo,
            'MaDeTai'         => $maDeTai,
            'MaHocKy'         => $hocKy->MaHocKy,
            'MaGV'            => $gv1->MaGV,
            'MaSV'            => $sv->MaSV,
            'LoaiKhoaLuan'    => 'KLCN',
            'Diem'            => 8.0,
            'ChiTietDiem'     => [],
            'NhanXet'         => 'Test',
            'TrangThai'       => 'Đã hoàn thành',
        ]);

        $userGv1 = $gv1->taiKhoan ?? TaiKhoan::create([
            'MaTK'        => 'TK_' . $gv1->MaGV,
            'TenDangNhap' => 'gv_test_' . $gv1->MaGV,
            'MatKhau'     => bcrypt('secret'),
            'MaVaiTro'    => 'VT02',
            'MaGV'        => $gv1->MaGV,
            'TrangThai'   => 1,
        ]);

        $response = $this->actingAs($userGv1)->post(route('giangvien.chamdiem.store'), [
            'MaHoiDong'    => $hoiDong->MaHoiDong,
            'MaHoSo'       => $hoSo->MaHoSo,
            'LoaiKhoaLuan' => 'KLCN',
            'action_type'  => 'luu_nhap',
            'diems'        => [
                $sv->MaSV => [
                    'MaSV'        => $sv->MaSV,
                    'ChiTietDiem' => collect(PhieuChamDiem::getRubricKLCN())->mapWithKeys(fn ($c) => [$c['id'] => round($c['max'] * .8, 2)])->all(),
                ],
            ],
        ]);

        $response->assertSessionHas('success');
        $this->assertSame('Đã hoàn thành', PhieuChamDiem::where('MaHoiDong', $hoiDong->MaHoiDong)->where('MaGV', $gv1->MaGV)->firstOrFail()->TrangThai);
    }

    /**
     * Yêu cầu 3: Khi quản trị viên công bố điểm chính thức -> Tự động khóa quyền sửa điểm của cả 3 giảng viên
     */
    public function test_cannot_edit_when_scores_are_officially_published(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockData();
        $gv1 = $gvs[0];

        // Đã công bố kết quả chính thức
        $hoSo->update(['TrangThaiKetQua' => 'DaCongBo']);
        $hoiDong->update(['TrangThaiCongBo' => 'Đã công bố']);

        PhieuChamDiem::create([
            'MaPhieuChamDiem' => 'PCD_' . mt_rand(100000, 999999),
            'MaHoiDong'       => $hoiDong->MaHoiDong,
            'MaHoSo'          => $hoSo->MaHoSo,
            'MaDeTai'         => $maDeTai,
            'MaHocKy'         => $hocKy->MaHocKy,
            'MaGV'            => $gv1->MaGV,
            'MaSV'            => $sv->MaSV,
            'LoaiKhoaLuan'    => 'KLCN',
            'Diem'            => 8.0,
            'ChiTietDiem'     => [],
            'NhanXet'         => 'Test',
            'TrangThai'       => 'Đã hoàn thành',
        ]);

        $userGv1 = $gv1->taiKhoan ?? TaiKhoan::create([
            'MaTK'        => 'TK_' . $gv1->MaGV,
            'TenDangNhap' => 'gv_test_' . $gv1->MaGV,
            'MatKhau'     => bcrypt('secret'),
            'MaVaiTro'    => 'VT02',
            'MaGV'        => $gv1->MaGV,
            'TrangThai'   => 1,
        ]);

        $response = $this->actingAs($userGv1)->post(route('giangvien.chamdiem.store'), [
            'MaHoiDong'    => $hoiDong->MaHoiDong,
            'MaHoSo'       => $hoSo->MaHoSo,
            'LoaiKhoaLuan' => 'KLCN',
            'action_type'  => 'xac_nhan_hoan_thanh',
            'diems'        => [
                $sv->MaSV => [
                    'MaSV'        => $sv->MaSV,
                    'ChiTietDiem' => [],
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        $errorMessage = session('error');
        $this->assertStringContainsString('công bố', $errorMessage);
    }

    /**
     * Yêu cầu 3 & 5: Quản trị viên mở khóa khi có sai sót có lý do bắt buộc -> Giảng viên sửa được điểm
     */
    public function test_admin_can_unlock_with_reason_and_gv_can_edit(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockData();
        $gv1 = $gvs[0];

        // Khóa do đã hết hạn
        $sysNow = HoiDong::getSystemNow();
        $hoiDong->update([
            'ThoiGianBatDauSuaDiem'  => $sysNow->copy()->subDays(3),
            'ThoiGianKetThucSuaDiem' => $sysNow->copy()->subDay(),
        ]);

        $rubric = PhieuChamDiem::getRubricKLCN();
        $initialChiTiet = [];
        foreach ($rubric as $crit) {
            $initialChiTiet[$crit['id']] = round((float)$crit['max'] * 0.70, 2);
        }

        $phieu1 = PhieuChamDiem::create([
            'MaPhieuChamDiem' => 'PCD_' . mt_rand(100000, 999999),
            'MaHoiDong'       => $hoiDong->MaHoiDong,
            'MaHoSo'          => $hoSo->MaHoSo,
            'MaDeTai'         => $maDeTai,
            'MaHocKy'         => $hocKy->MaHocKy,
            'MaGV'            => $gv1->MaGV,
            'MaSV'            => $sv->MaSV,
            'LoaiKhoaLuan'    => 'KLCN',
            'Diem'            => 7.0,
            'ChiTietDiem'     => $initialChiTiet,
            'NhanXet'         => 'Nhận xét cố định',
            'TrangThai'       => 'Đã hoàn thành',
        ]);

        // Tạo tài khoản Trưởng khoa / Quản trị viên
        $truongKhoaTk = TaiKhoan::create([
            'MaTK'        => 'TK_TRUONGKHOA_' . mt_rand(100, 999),
            'TenDangNhap' => 'TK_CNTT_truongkhoa',
            'MatKhau'     => bcrypt('secret'),
            'MaVaiTro'    => 'VT05', // Trưởng khoa
            'TrangThai'   => 1,
        ]);

        // Trưởng khoa mở khóa với lý do
        $reasonUnlock = 'Xử lý sai sót: Giám khảo chấm nhầm điểm tiêu chí 3 theo biên bản họp khoa';
        $resUnlock = $this->actingAs($truongKhoaTk)->post(route('truongkhoa.ketqua.mokhoaphieu'), [
            'MaHoiDong'   => $hoiDong->MaHoiDong,
            'ly_do'       => $reasonUnlock,
            'han_sua_moi' => $sysNow->copy()->addDay()->toDateTimeString(),
        ]);
        $resUnlock->assertRedirect();

        $hoiDong->refresh();
        $this->assertTrue($hoiDong->TrangThaiMoKhoaSuaDiem, 'Cờ mở khóa phải được bật.');
        $this->assertEquals($reasonUnlock, $hoiDong->LyDoMoKhoaSuaDiem, 'Lý do mở khóa phải được lưu trữ.');

        // Giảng viên bây giờ có thể sửa điểm thành công
        $userGv1 = $gv1->taiKhoan ?? TaiKhoan::create([
            'MaTK'        => 'TK_' . $gv1->MaGV,
            'TenDangNhap' => 'gv_test_' . $gv1->MaGV,
            'MatKhau'     => bcrypt('secret'),
            'MaVaiTro'    => 'VT02',
            'MaGV'        => $gv1->MaGV,
            'TrangThai'   => 1,
        ]);

        $newChiTiet = $initialChiTiet;
        $firstCritId = $rubric[0]['id'];
        $newChiTiet[$firstCritId] = round((float)$rubric[0]['max'] * 0.90, 2);

        $response = $this->actingAs($userGv1)->post(route('giangvien.chamdiem.store'), [
            'MaHoiDong'    => $hoiDong->MaHoiDong,
            'MaHoSo'       => $hoSo->MaHoSo,
            'LoaiKhoaLuan' => 'KLCN',
            'action_type'  => 'xac_nhan_hoan_thanh',
            'diems'        => [
                $sv->MaSV => [
                    'MaSV'        => $sv->MaSV,
                    'ChiTietDiem' => $newChiTiet,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Yêu cầu 5: Lịch sử chỉnh sửa không cho phép xóa (Immutable Audit Log)
     */
    public function test_score_edit_audit_log_cannot_be_deleted(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockData();

        $log = LichSuChinhSuaDiem::create([
            'MaPhieuChamDiem' => 'PCD_TEST_DELETE',
            'MaHoiDong'       => $hoiDong->MaHoiDong,
            'MaHoSo'          => $hoSo->MaHoSo,
            'MaSV'            => $sv->MaSV,
            'MaGV'            => $gvs[0]->MaGV,
            'NguoiThucHien'   => $gvs[0]->HoTen,
            'MaCLO'           => 'CLO1',
            'TenTieuChi'      => 'Tiêu chí 1',
            'DiemCu'          => 7.0,
            'DiemMoi'         => 8.5,
            'ThoiGian'        => now(),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Không được phép xóa lịch sử chỉnh sửa điểm');

        $log->delete();
    }

    /**
     * Yêu cầu 4: Đồng bộ tính điểm trung bình CLO của 3 giảng viên sau khi chỉnh sửa
     */
    public function test_three_examiners_average_clo_is_recalculated_after_score_edit(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockData();

        $rubric = PhieuChamDiem::getRubricKLCN();

        // 3 Giảng viên ban đầu chấm: GV1: 7.0, GV2: 8.0, GV3: 9.0
        // Điểm TB ban đầu = (7.0 + 8.0 + 9.0) / 3 = 8.0
        $ratios = [0.70, 0.80, 0.90];
        foreach ($gvs as $idx => $gv) {
            $chiTiet = [];
            foreach ($rubric as $crit) {
                $chiTiet[$crit['id']] = round((float)$crit['max'] * $ratios[$idx], 2);
            }
            PhieuChamDiem::create([
                'MaPhieuChamDiem' => 'PCD_' . $idx . '_' . mt_rand(1000, 9999),
                'MaHoiDong'       => $hoiDong->MaHoiDong,
                'MaHoSo'          => $hoSo->MaHoSo,
                'MaDeTai'         => $maDeTai,
                'MaHocKy'         => $hocKy->MaHocKy,
                'MaGV'            => $gv->MaGV,
                'MaSV'            => $sv->MaSV,
                'LoaiKhoaLuan'    => 'KLCN',
                'Diem'            => $ratios[$idx] * 10,
                'ChiTietDiem'     => $chiTiet,
                'NhanXet'         => "Nhận xét của GV {$gv->MaGV}",
                'TrangThai'       => 'Đã hoàn thành',
            ]);
        }

        // Tính điểm ban đầu
        $this->gradeService->tongHopKetQuaHoiDong($hoiDong->MaHoiDong);
        $kqBanDau = KetQuaSinhVien::where('MaSV', $sv->MaSV)->first();
        $this->assertNotNull($kqBanDau, 'KetQuaSinhVien phải tồn tại sau khi tổng hợp điểm');
        $this->assertEquals(8.0, round($kqBanDau->DiemHoiDongTB, 2));

        // GV1 chỉnh sửa điểm từ 7.0 lên 10.0 (1.00 ratio trên mọi tiêu chí)
        // Điểm TB mới kỳ vọng = (10.0 + 8.0 + 9.0) / 3 = 9.00
        $gv1 = $gvs[0];
        $newChiTiet = [];
        foreach ($rubric as $crit) {
            $newChiTiet[$crit['id']] = (float)$crit['max'];
        }

        $userGv1 = $gv1->taiKhoan ?? TaiKhoan::create([
            'MaTK'        => 'TK_' . $gv1->MaGV,
            'TenDangNhap' => 'gv_test_' . $gv1->MaGV,
            'MatKhau'     => bcrypt('secret'),
            'MaVaiTro'    => 'VT02',
            'MaGV'        => $gv1->MaGV,
            'TrangThai'   => 1,
        ]);

        $res = $this->actingAs($userGv1)->post(route('giangvien.chamdiem.store'), [
            'MaHoiDong'    => $hoiDong->MaHoiDong,
            'MaHoSo'       => $hoSo->MaHoSo,
            'LoaiKhoaLuan' => 'KLCN',
            'action_type'  => 'xac_nhan_hoan_thanh',
            'diems'        => [
                $sv->MaSV => [
                    'MaSV'        => $sv->MaSV,
                    'ChiTietDiem' => $newChiTiet,
                ],
            ],
        ]);

        $res->assertRedirect();

        // Kiểm tra KetQuaSinhVien sau khi sửa
        $kqMoi = KetQuaSinhVien::where('MaSV', $sv->MaSV)->first();
        $this->assertEquals(9.0, round($kqMoi->DiemHoiDongTB, 2), 'Điểm trung bình Hội đồng phải được tự động tính lại sau khi giảng viên sửa điểm.');

        // Kiểm tra DiemCLO được đồng bộ
        $cloData = $kqMoi->DiemCLO;
        $this->assertNotEmpty($cloData, 'Dữ liệu CLO phải được tự động đồng bộ.');
        $this->assertArrayHasKey('cloScores', $cloData);
        $this->assertEquals(3, $cloData['completed_examiners']);
        $this->assertTrue($cloData['is_official']);
    }

    /**
     * Nghiệp vụ: Hội đồng đã công bố quyết định thành lập (TrangThaiCongBo = 'Đã công bố')
     * nhưng Trưởng khoa CHƯA xác nhận công bố điểm (TrangThaiKetQua = 'ChuaDuPhieu' / 'DuPhieu')
     * -> Giảng viên VẪN ĐƯỢC PHÉP CHỈNH SỬA ĐIỂM trong thời hạn cho phép.
     */
    public function test_lecturer_can_edit_scores_when_council_is_announced_but_grades_are_not_yet_published(): void
    {
        [$hoiDong, $gvs, $sv, $hoSo, $hocKy, $maDeTai] = $this->createMockData();
        $gv1 = $gvs[0];

        // Hội đồng đã công bố quyết định thành lập nhưng CHƯA công bố điểm
        $hoiDong->update(['TrangThaiCongBo' => 'Đã công bố']);
        $hoSo->update(['TrangThaiKetQua' => 'DuPhieu']);

        PhieuChamDiem::create([
            'MaPhieuChamDiem' => 'PCD_' . mt_rand(100000, 999999),
            'MaHoiDong'       => $hoiDong->MaHoiDong,
            'MaHoSo'          => $hoSo->MaHoSo,
            'MaDeTai'         => $maDeTai,
            'MaHocKy'         => $hocKy->MaHocKy,
            'MaGV'            => $gv1->MaGV,
            'MaSV'            => $sv->MaSV,
            'LoaiKhoaLuan'    => 'KLCN',
            'Diem'            => 7.5,
            'ChiTietDiem'     => [],
            'NhanXet'         => 'Nhận xét ban đầu',
            'TrangThai'       => 'Đã hoàn thành',
        ]);

        $reason = '';
        $canEdit = $hoiDong->canEditScore($gv1, $hoSo, $reason);

        $this->assertTrue($canEdit, "Giảng viên phải được phép sửa điểm khi Trưởng khoa chưa xác nhận công bố kết quả điểm (Lý do bị từ chối: {$reason}).");
    }
}
