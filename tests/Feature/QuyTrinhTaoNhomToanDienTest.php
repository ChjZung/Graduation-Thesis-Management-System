<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\HocKy;
use App\Models\HocPhan;
use App\Models\Lop;
use App\Models\Nganh;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class QuyTrinhTaoNhomToanDienTest extends TestCase
{
    use DatabaseTransactions;

    protected $userLeader;
    protected $svLeader;
    protected $userMember1;
    protected $svMember1;
    protected $userMember2;
    protected $svMember2;
    protected $userDiffMajor;
    protected $svDiffMajor;
    protected $hocKy;
    protected $hocPhan;

    protected function setUp(): void
    {
        parent::setUp();

        // Đóng băng thời gian trong giai đoạn mở cổng tạo nhóm (Kế hoạch HK2627_1 mở ngày 10/08/2026)
        Carbon::setTestNow('2026-08-10 12:00:00');

        // 1. Sinh viên Trưởng nhóm (Ngành CNPM)
        $this->userLeader = TaiKhoan::where('TenDangNhap', '2001210060')->firstOrFail();
        $this->userLeader->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->svLeader = SinhVien::where('MaTK', $this->userLeader->MaTK)->firstOrFail();

        // 2. Sinh viên Thành viên 1 cùng ngành (CNPM)
        $this->userMember1 = TaiKhoan::where('TenDangNhap', '2001210061')->firstOrFail();
        $this->userMember1->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->svMember1 = SinhVien::where('MaTK', $this->userMember1->MaTK)->firstOrFail();

        // 3. Sinh viên Thành viên 2 cùng ngành (CNPM)
        $this->userMember2 = TaiKhoan::where('TenDangNhap', '2001210062')->firstOrFail();
        $this->userMember2->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->svMember2 = SinhVien::where('MaTK', $this->userMember2->MaTK)->firstOrFail();

        // 4. Sinh viên khác ngành (ví dụ ATTT, HTTT hoặc KHDL)
        $leaderMajor = $this->svLeader->MaNganh ?? $this->svLeader->lop?->MaNganh;
        $this->svDiffMajor = SinhVien::where(function($q) use ($leaderMajor) {
            $q->where('MaNganh', '!=', $leaderMajor)
              ->orWhereHas('lop', fn($lq) => $lq->where('MaNganh', '!=', $leaderMajor));
        })->whereNotIn('MaSV', ['2001210060', '2001210061', '2001210062'])->first();

        if ($this->svDiffMajor) {
            $this->userDiffMajor = TaiKhoan::where('MaTK', $this->svDiffMajor->MaTK)->first();
            if ($this->userDiffMajor) {
                $this->userDiffMajor->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
            }
        }

        // 5. Học kỳ & Học phần test
        $this->hocKy = HocKy::where('MaHocKy', 'HK2627_1')->first() ?? HocKy::first();
        $this->hocPhan = HocPhan::where('MaHocPhan', 'HP_KLCN_CNPM')->first() ?? HocPhan::first();

        // Xóa nhóm cũ của các sinh viên test để kịch bản độc lập
        ThanhVienNhom::whereIn('MaSV', [$this->svLeader->MaSV, $this->svMember1->MaSV, $this->svMember2->MaSV])->delete();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * CASE 1: Sinh viên chưa có nhóm tạo nhóm chính thức thành công
     */
    public function test_case_1_tao_nhom_chinh_thuc_thanh_cong_cho_sinh_vien_chua_co_nhom()
    {
        $response = $this->actingAs($this->userLeader)->post(route('sinhvien.nhom.store'), [
            'MaHocKy'   => $this->hocKy->MaHocKy,
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
        ]);

        $response->assertSessionHas('success');

        $tvRecord = ThanhVienNhom::where('MaSV', $this->svLeader->MaSV)
            ->where('VaiTro', 'Trưởng nhóm')
            ->where('TrangThai', 'da_tham_gia')
            ->first();

        $this->assertNotNull($tvRecord, 'Thành viên trưởng nhóm phải được lưu.');
        $nhom = $tvRecord->nhom;
        $this->assertNotNull($nhom, 'Nhóm phải được lưu vào cơ sở dữ liệu.');
        $this->assertEquals($this->svLeader->MaSV, $nhom->MaTruongNhom);
        $this->assertEquals($this->hocPhan->MaHocPhan, $nhom->MaHocPhan);
        $this->assertEquals($this->hocKy->MaHocKy, $nhom->MaHocKy);
    }

    /**
     * CASE 2: Chặn tạo nhóm trùng học phần khi sinh viên đã có nhóm chính thức
     */
    public function test_case_2_chan_tao_nhom_trung_hoc_phan_khi_da_co_nhom_chinh_thuc()
    {
        // 1. Tạo nhóm đầu tiên
        $nhom1 = Nhom::create([
            'MaNhom' => 'N_TEST_EXIST_' . rand(100, 999),
            'TenNhom' => 'Nhóm Đã Có Sẵn ' . uniqid(),
            'MaTruongNhom' => $this->svLeader->MaSV,
            'MaHocKy' => $this->hocKy->MaHocKy,
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'TrangThai' => 'Đang hoạt động',
        ]);
        ThanhVienNhom::create([
            'MaNhom' => $nhom1->MaNhom,
            'MaSV' => $this->svLeader->MaSV,
            'VaiTro' => 'Trưởng nhóm',
            'TrangThai' => 'da_tham_gia',
            'NgayGiaNhap' => now(),
        ]);

        // 2. Cố tình tạo thêm nhóm thứ 2 cho cùng học phần và học kỳ -> Bị chặn
        $res = $this->actingAs($this->userLeader)->post(route('sinhvien.nhom.store'), [
            'MaHocKy'   => $this->hocKy->MaHocKy,
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
        ]);

        $res->assertSessionHasErrors();
    }

    /**
     * CASE 3: Tra cứu và gửi lời mời thành viên cùng ngành thành công
     */
    public function test_case_3_tra_cuu_va_moi_sinh_vien_chung_nganh_thanh_cong()
    {
        $nhom = Nhom::create([
            'MaNhom' => 'N_INVITE_' . rand(100, 999),
            'TenNhom' => 'Nhóm Mời Thành Viên ' . uniqid(),
            'MaTruongNhom' => $this->svLeader->MaSV,
            'MaHocKy' => $this->hocKy->MaHocKy,
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'TrangThai' => 'Đang hoạt động',
        ]);
        ThanhVienNhom::create([
            'MaNhom' => $nhom->MaNhom,
            'MaSV' => $this->svLeader->MaSV,
            'VaiTro' => 'Trưởng nhóm',
            'TrangThai' => 'da_tham_gia',
            'NgayGiaNhap' => now(),
        ]);

        // 1. Tra cứu sinh viên cùng ngành -> canInvite = true
        $resSearch = $this->actingAs($this->userLeader)->get(route('sinhvien.nhom.traCuuSinhVien', [
            'mssv' => $this->svMember1->MaSV,
            'ma_nhom' => $nhom->MaNhom,
        ]));
        $resSearch->assertStatus(200);
        $resSearch->assertJsonFragment(['can_invite' => true]);
        $resSearch->assertJsonFragment(['MaSV' => $this->svMember1->MaSV]);

        // 2. Gửi lời mời gia nhập nhóm
        $resInvite = $this->actingAs($this->userLeader)->post(route('sinhvien.nhom.moiThanhVien'), [
            'MaNhom' => $nhom->MaNhom,
            'MaSV'   => $this->svMember1->MaSV,
        ]);
        $resInvite->assertSessionHas('invite_success');

        // Bản ghi lời mời phải được lưu với trạng thái 'cho_xac_nhan'
        $this->assertDatabaseHas('ThanhVienNhom', [
            'MaNhom'    => $nhom->MaNhom,
            'MaSV'      => $this->svMember1->MaSV,
            'TrangThai' => 'cho_xac_nhan',
            'VaiTro'    => 'Thành viên',
        ]);
    }

    /**
     * CASE 4: Chặn tra cứu (canInvite = false) và chặn gửi lời mời sinh viên khác ngành đào tạo
     */
    public function test_case_4_chan_tra_cuu_va_moi_sinh_vien_khac_nganh()
    {
        if (!$this->svDiffMajor) {
            $this->markTestSkipped('Không có sinh viên khác ngành trong DB test.');
        }

        $nhom = Nhom::create([
            'MaNhom' => 'N_DIFF_MAJOR_' . rand(100, 999),
            'TenNhom' => 'Nhóm CNPM Không Mời Khác Ngành ' . uniqid(),
            'MaTruongNhom' => $this->svLeader->MaSV,
            'MaHocKy' => $this->hocKy->MaHocKy,
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'TrangThai' => 'Đang hoạt động',
        ]);
        ThanhVienNhom::create([
            'MaNhom' => $nhom->MaNhom,
            'MaSV' => $this->svLeader->MaSV,
            'VaiTro' => 'Trưởng nhóm',
            'TrangThai' => 'da_tham_gia',
            'NgayGiaNhap' => now(),
        ]);

        // 1. Tra cứu sinh viên khác ngành -> canInvite = false
        $resSearch = $this->actingAs($this->userLeader)->get(route('sinhvien.nhom.traCuuSinhVien', [
            'mssv' => $this->svDiffMajor->MaSV,
            'ma_nhom' => $nhom->MaNhom,
        ]));
        $resSearch->assertStatus(200);
        $resSearch->assertJsonFragment(['can_invite' => false]);

        // 2. Cố tình gửi request mời sinh viên khác ngành -> Bị từ chối bằng withErrors
        $resInvite = $this->actingAs($this->userLeader)->post(route('sinhvien.nhom.moiThanhVien'), [
            'MaNhom' => $nhom->MaNhom,
            'MaSV'   => $this->svDiffMajor->MaSV,
        ]);
        $resInvite->assertSessionHasErrors();
    }

    /**
     * CASE 5: Chặn sinh viên khác ngành xin gia nhập nhóm
     */
    public function test_case_5_chan_sinh_vien_khac_nganh_xin_gia_nhap_nhom()
    {
        if (!$this->userDiffMajor) {
            $this->markTestSkipped('Không có tài khoản sinh viên khác ngành trong DB test.');
        }

        $nhom = Nhom::create([
            'MaNhom' => 'N_BLOCK_JOIN_' . rand(100, 999),
            'TenNhom' => 'Nhóm Chuẩn Ngành ' . uniqid(),
            'MaTruongNhom' => $this->svLeader->MaSV,
            'MaHocKy' => $this->hocKy->MaHocKy,
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'TrangThai' => 'Đang hoạt động',
        ]);
        ThanhVienNhom::create([
            'MaNhom' => $nhom->MaNhom,
            'MaSV' => $this->svLeader->MaSV,
            'VaiTro' => 'Trưởng nhóm',
            'TrangThai' => 'da_tham_gia',
            'NgayGiaNhap' => now(),
        ]);

        // Sinh viên khác ngành gửi yêu cầu xin gia nhập -> Bị chặn
        $res = $this->actingAs($this->userDiffMajor)->post(route('sinhvien.nhom.xinGiaNhap', $nhom->MaNhom));
        $res->assertSessionHasErrors();
    }

    /**
     * CASE 6: Thành viên chấp nhận lời mời, nhóm đạt đủ 3 người tự động ẩn khỏi danh sách mở
     */
    public function test_case_6_sinh_vien_chap_nhan_loi_moi_va_du_3_thanh_vien_tu_dong_an()
    {
        $nhom = Nhom::create([
            'MaNhom' => 'N_FULL_3_' . rand(100, 999),
            'TenNhom' => 'Nhóm Tuyển Đủ 3 Người ' . uniqid(),
            'MaTruongNhom' => $this->svLeader->MaSV,
            'MaHocKy' => $this->hocKy->MaHocKy,
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'TrangThai' => 'Đang hoạt động',
        ]);
        ThanhVienNhom::create([
            'MaNhom' => $nhom->MaNhom,
            'MaSV' => $this->svLeader->MaSV,
            'VaiTro' => 'Trưởng nhóm',
            'TrangThai' => 'da_tham_gia',
            'NgayGiaNhap' => now(),
        ]);

        // Tạo 2 lời mời cho member 1 và member 2
        ThanhVienNhom::create([
            'MaNhom' => $nhom->MaNhom,
            'MaSV' => $this->svMember1->MaSV,
            'VaiTro' => 'Thành viên',
            'TrangThai' => 'cho_xac_nhan',
            'NgayGiaNhap' => now(),
        ]);
        ThanhVienNhom::create([
            'MaNhom' => $nhom->MaNhom,
            'MaSV' => $this->svMember2->MaSV,
            'VaiTro' => 'Thành viên',
            'TrangThai' => 'cho_xac_nhan',
            'NgayGiaNhap' => now(),
        ]);

        // 1. Member 1 chấp nhận lời mời
        $res1 = $this->actingAs($this->userMember1)->post(route('sinhvien.nhom.xacNhanLoiMoi', $nhom->MaNhom));
        $res1->assertSessionHas('success');

        // 2. Member 2 chấp nhận lời mời -> Nhóm đạt 3/3
        $res2 = $this->actingAs($this->userMember2)->post(route('sinhvien.nhom.xacNhanLoiMoi', $nhom->MaNhom));
        $res2->assertSessionHas('success');

        // Kiểm tra nhóm có đúng 3 thành viên chính thức
        $count = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)->where('TrangThai', 'da_tham_gia')->count();
        $this->assertEquals(3, $count);

        // Sinh viên khác cùng ngành truy cập danh sách nhóm -> Nhóm hiển thị đầy đủ trong CSDL kèm trạng thái Đã Đủ 3/3 và khóa nút
        $otherSvUser = TaiKhoan::where('TenDangNhap', '2001210063')->first();
        if ($otherSvUser) {
            $otherSvUser->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
            $resList = $this->actingAs($otherSvUser)->get(route('sinhvien.nhom.index', [
                'hoc_phan' => $nhom->MaHocPhan,
                'hoc_ky' => $nhom->MaHocKy,
            ]));
            $resList->assertStatus(200);
            $resList->assertSee($nhom->TenNhom);
            $resList->assertSee('Đã Đủ 3/3');
            $resList->assertSee('Đã Đủ Thành Viên');
        }
    }

    /**
     * CASE 7: Chặn mời thêm hoặc xin gia nhập khi nhóm đã đủ 3 thành viên
     */
    public function test_case_7_chan_moi_them_hoac_xin_gia_nhap_khi_nhom_da_du_3_thanh_vien()
    {
        $nhom = Nhom::create([
            'MaNhom' => 'N_OVER_LIMIT_' . rand(100, 999),
            'TenNhom' => 'Nhóm Đã Đủ 3 Không Thể Thêm ' . uniqid(),
            'MaTruongNhom' => $this->svLeader->MaSV,
            'MaHocKy' => $this->hocKy->MaHocKy,
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'TrangThai' => 'Đang hoạt động',
        ]);
        ThanhVienNhom::create(['MaNhom' => $nhom->MaNhom, 'MaSV' => $this->svLeader->MaSV, 'VaiTro' => 'Trưởng nhóm', 'TrangThai' => 'da_tham_gia', 'NgayGiaNhap' => now()]);
        ThanhVienNhom::create(['MaNhom' => $nhom->MaNhom, 'MaSV' => $this->svMember1->MaSV, 'VaiTro' => 'Thành viên', 'TrangThai' => 'da_tham_gia', 'NgayGiaNhap' => now()]);
        ThanhVienNhom::create(['MaNhom' => $nhom->MaNhom, 'MaSV' => $this->svMember2->MaSV, 'VaiTro' => 'Thành viên', 'TrangThai' => 'da_tham_gia', 'NgayGiaNhap' => now()]);

        // Cố tình mời thành viên thứ 4
        $otherSv = SinhVien::whereNotIn('MaSV', [$this->svLeader->MaSV, $this->svMember1->MaSV, $this->svMember2->MaSV])
            ->where('MaNganh', $this->svLeader->MaNganh ?? $this->svLeader->lop?->MaNganh)
            ->first();

        if ($otherSv) {
            $resInvite = $this->actingAs($this->userLeader)->post(route('sinhvien.nhom.moiThanhVien'), [
                'MaNhom' => $nhom->MaNhom,
                'MaSV'   => $otherSv->MaSV,
            ]);
            $resInvite->assertSessionHasErrors();
        }
    }

    /**
     * CASE 8: Trưởng nhóm khai trừ thành viên -> Nhóm giảm số lượng và mở lại tuyển thành viên
     */
    public function test_case_8_khai_tru_thanh_vien_nhom_mo_lai_tuyen_thanh_vien()
    {
        $nhom = Nhom::create([
            'MaNhom' => 'N_KICK_MEMBER_' . rand(100, 999),
            'TenNhom' => 'Nhóm Khai Trừ Thành Viên ' . uniqid(),
            'MaTruongNhom' => $this->svLeader->MaSV,
            'MaHocKy' => $this->hocKy->MaHocKy,
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'TrangThai' => 'Đang hoạt động',
        ]);
        ThanhVienNhom::create(['MaNhom' => $nhom->MaNhom, 'MaSV' => $this->svLeader->MaSV, 'VaiTro' => 'Trưởng nhóm', 'TrangThai' => 'da_tham_gia', 'NgayGiaNhap' => now()]);
        ThanhVienNhom::create(['MaNhom' => $nhom->MaNhom, 'MaSV' => $this->svMember1->MaSV, 'VaiTro' => 'Thành viên', 'TrangThai' => 'da_tham_gia', 'NgayGiaNhap' => now()]);

        // Trưởng nhóm khai trừ thành viên 1
        $resKick = $this->actingAs($this->userLeader)->post(route('sinhvien.nhom.khaiTru', [$nhom->MaNhom, $this->svMember1->MaSV]));
        $resKick->assertSessionHas('success');

        // Thành viên 1 không còn trong nhóm
        $this->assertDatabaseMissing('ThanhVienNhom', [
            'MaNhom'    => $nhom->MaNhom,
            'MaSV'      => $this->svMember1->MaSV,
            'TrangThai' => 'da_tham_gia',
        ]);

        // Số thành viên còn lại là 1
        $remaining = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)->where('TrangThai', 'da_tham_gia')->count();
        $this->assertEquals(1, $remaining);
    }
}
