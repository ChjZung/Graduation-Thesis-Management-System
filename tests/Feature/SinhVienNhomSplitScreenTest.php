<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\HocKy;
use App\Models\HocPhan;
use App\Models\MocThoiGianKhoaLuan;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SinhVienNhomSplitScreenTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        MocThoiGianKhoaLuan::where(function($q) {
            $q->where('TenMoc', 'LIKE', '%tạo nhóm%')
              ->orWhere('TenMoc', 'LIKE', '%ghép nhóm%')
              ->orWhere('MoTa', 'LIKE', '%[TAO_NHOM]%');
        })->update([
            'NgayBatDau'  => now()->subDays(5)->format('Y-m-d'),
            'NgayKetThuc' => now()->addDays(5)->format('Y-m-d'),
        ]);
    }

    public function test_giao_dien_nhom_sinh_vien_chia_hai_cot_va_luon_co_nut_tao_nhom()
    {
        $tk = TaiKhoan::where('MaVaiTro', 'VT03')->first();
        $this->assertNotNull($tk, 'Cần ít nhất một tài khoản sinh viên trong DB test');

        $tk->update([
            'TrangThaiMatKhau'  => 'ACTIVE',
            'BatBuocDoiMatKhau' => false,
        ]);

        $response = $this->actingAs($tk)->get(route('sinhvien.nhom.index'));

        $response->assertStatus(200);
        // Kiểm tra chia 2 cột: Quản Trị Nhóm Của Bạn và Quản Trị Các Nhóm
        $response->assertSee('Quản Trị Nhóm Của Bạn');
        $response->assertSee('Quản Trị Các Nhóm');
        // Kiểm tra nút + Tạo Nhóm Mới luôn hiển thị
        $response->assertSee('+ Tạo Nhóm Mới');
        // Kiểm tra danh sách nhóm mở có khung cuộn tối giản nhom-open-scroll-container
        $response->assertSee('nhom-open-scroll-container');
    }

    public function test_thong_bao_loi_moi_chi_hien_o_khung_tren_cung_khong_lan_vao_danh_sach_nhom_mo_ben_duoi()
    {
        $tk = TaiKhoan::where('MaVaiTro', 'VT03')->first();
        $this->assertNotNull($tk);
        $sv = SinhVien::where('MaTK', $tk->MaTK)->first();
        $this->assertNotNull($sv);

        $hk = HocKy::first();
        $hp = HocPhan::first();

        // Chọn sinh viên cùng ngành để đúng quy tắc cùng ngành đào tạo
        $otherSv = SinhVien::where('MaSV', '!=', $sv->MaSV)->where('MaNganh', $sv->MaNganh)->first();
        $this->assertNotNull($otherSv);

        // Tạo 2 nhóm mở: Nhóm A gửi lời mời cho sinh viên, Nhóm B là nhóm mở bình thường
        $nhomA = Nhom::create([
            'MaNhom' => 'TEST_NHA_' . rand(1000, 9999),
            'TenNhom' => 'Nhóm Mời Test',
            'MaHocKy' => $hk->MaHocKy,
            'MaHocPhan' => $hp->MaHocPhan,
            'TrangThai' => 'Đang mở',
        ]);

        ThanhVienNhom::create([
            'MaNhom'      => $nhomA->MaNhom,
            'MaSV'        => $otherSv->MaSV,
            'VaiTro'      => 'Trưởng nhóm',
            'TrangThai'   => 'da_tham_gia',
            'NgayThamGia' => now(),
        ]);

        $nhomB = Nhom::create([
            'MaNhom' => 'TEST_NHB_' . rand(1000, 9999),
            'TenNhom' => 'Nhóm Mở Tự Do',
            'MaHocKy' => $hk->MaHocKy,
            'MaHocPhan' => $hp->MaHocPhan,
            'TrangThai' => 'Đang mở',
        ]);

        ThanhVienNhom::create([
            'MaNhom'      => $nhomB->MaNhom,
            'MaSV'        => $otherSv->MaSV,
            'VaiTro'      => 'Trưởng nhóm',
            'TrangThai'   => 'da_tham_gia',
            'NgayThamGia' => now(),
        ]);

        // Tạo lời mời từ Nhóm A cho sinh viên
        ThanhVienNhom::create([
            'MaNhom' => $nhomA->MaNhom,
            'MaSV' => $sv->MaSV,
            'VaiTro' => 'Thành viên',
            'TrangThai' => 'cho_xac_nhan',
            'NgayThamGia' => now(),
        ]);

        $response = $this->actingAs($tk)->get(route('sinhvien.nhom.index', ['hoc_ky' => $hk->MaHocKy, 'hoc_phan' => $hp->MaHocPhan, 'bo_mon' => 'ALL']));
        $response->assertStatus(200);

        // Khung thông báo trên cùng có lời mời của Nhóm Mời Test
        $response->assertSee('Bạn có 1 lời mời tham gia nhóm!');
        $response->assertSee('Nhóm Mời Test');

        // Danh sách nhóm khóa luận đang mở bên dưới KHÔNG được chứa "Đã mời bạn"
        $response->assertDontSee('Đã mời bạn');
        // Và nhóm mở tự do vẫn hiển thị bình thường
        $response->assertSee('Nhóm Mở Tự Do');
    }

    public function test_nhom_du_3_thanh_vien_thi_loi_moi_cua_cac_sinh_vien_khac_tu_dong_bien_mat()
    {
        // Mở cổng ghép nhóm cho thời điểm hiện tại
        MocThoiGianKhoaLuan::where(function($q) {
            $q->where('TenMoc', 'LIKE', '%tạo nhóm%')
              ->orWhere('TenMoc', 'LIKE', '%ghép nhóm%')
              ->orWhere('MoTa', 'LIKE', '%[TAO_NHOM]%');
        })->update([
            'NgayBatDau'  => now()->subDays(5)->format('Y-m-d'),
            'NgayKetThuc' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $students = SinhVien::with('taiKhoan')->has('taiKhoan')->where('MaNganh', '7480201')->take(4)->get();
        $this->assertGreaterThanOrEqual(4, $students->count(), 'Cần ít nhất 4 sinh viên để test');

        $sv1 = $students[0]; // Trưởng nhóm
        $sv2 = $students[1]; // Thành viên thứ 2
        $sv3 = $students[2]; // Thành viên thứ 3 (sẽ chấp nhận lời mời)
        $sv4 = $students[3]; // Thành viên thứ 4 (lời mời của bạn này phải tự động biến mất khi nhóm đủ 3)

        foreach ([$sv1, $sv2, $sv3, $sv4] as $s) {
            $s->taiKhoan->update([
                'TrangThaiMatKhau'  => 'ACTIVE',
                'BatBuocDoiMatKhau' => false,
            ]);
        }

        // Xóa liên kết nhóm cũ trong transaction để tránh ràng buộc trùng nhóm
        ThanhVienNhom::whereIn('MaSV', [$sv1->MaSV, $sv2->MaSV, $sv3->MaSV, $sv4->MaSV])->delete();

        $hk = HocKy::where('MaHocKy', 'HK2627_1')->first() ?? HocKy::first();
        $hp = HocPhan::first();

        // 1. Tạo nhóm với 2 thành viên chính thức
        $nhom = Nhom::create([
            'MaNhom'       => 'TEST_FULL_' . rand(1000, 9999),
            'TenNhom'      => 'Nhóm Test Tự Động Biến Mất Lời Mời',
            'MaHocKy'      => $hk->MaHocKy,
            'MaHocPhan'    => $hp->MaHocPhan,
            'MaTruongNhom' => $sv1->MaSV,
            'TrangThai'    => 'Đang mở',
        ]);

        ThanhVienNhom::create([
            'MaNhom'      => $nhom->MaNhom,
            'MaSV'        => $sv1->MaSV,
            'VaiTro'      => 'Trưởng nhóm',
            'TrangThai'   => 'da_tham_gia',
            'NgayThamGia' => now(),
        ]);

        ThanhVienNhom::create([
            'MaNhom'      => $nhom->MaNhom,
            'MaSV'        => $sv2->MaSV,
            'VaiTro'      => 'Thành viên',
            'TrangThai'   => 'da_tham_gia',
            'NgayThamGia' => now(),
        ]);

        // Tạo 2 lời mời: gửi cho sv3 và sv4
        ThanhVienNhom::create([
            'MaNhom'      => $nhom->MaNhom,
            'MaSV'        => $sv3->MaSV,
            'VaiTro'      => 'Thành viên',
            'TrangThai'   => 'cho_xac_nhan',
            'NgayThamGia' => null,
        ]);

        ThanhVienNhom::create([
            'MaNhom'      => $nhom->MaNhom,
            'MaSV'        => $sv4->MaSV,
            'VaiTro'      => 'Thành viên',
            'TrangThai'   => 'cho_xac_nhan',
            'NgayThamGia' => null,
        ]);

        // SV4 vào trang xem: đang có 1 lời mời tham gia nhóm
        $resBefore = $this->actingAs($sv4->taiKhoan)->get(route('sinhvien.nhom.index', ['hoc_ky' => $hk->MaHocKy, 'hoc_phan' => $hp->MaHocPhan, 'bo_mon' => 'ALL']));
        $resBefore->assertStatus(200);
        $resBefore->assertSee('Bạn có 1 lời mời tham gia nhóm!');
        $resBefore->assertSee('Nhóm Test Tự Động Biến Mất Lời Mời');

        // SV3 chấp nhận lời mời -> Nhóm đạt 3/3 thành viên
        $acceptRes = $this->actingAs($sv3->taiKhoan)->post(route('sinhvien.nhom.xacNhanLoiMoi', $nhom->MaNhom));
        $acceptRes->assertRedirect();

        // Kiểm tra nhóm đã đủ 3 thành viên
        $activeMembers = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)->where('TrangThai', 'da_tham_gia')->count();
        $this->assertEquals(3, $activeMembers);

        // Kiểm tra lời mời của SV4 đã bị tự động xóa khỏi CSDL
        $sv4InviteStillExists = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)
            ->where('MaSV', $sv4->MaSV)
            ->where('TrangThai', 'cho_xac_nhan')
            ->exists();
        $this->assertFalse($sv4InviteStillExists, 'Bản ghi lời mời của SV4 phải tự động bị xóa khỏi DB');

        // Khi SV4 xem lại trang Nhóm: Lời mời hoàn toàn biến mất, không hiển thị
        $resAfter = $this->actingAs($sv4->taiKhoan)->get(route('sinhvien.nhom.index', ['hoc_ky' => $hk->MaHocKy, 'hoc_phan' => $hp->MaHocPhan, 'bo_mon' => 'ALL']));
        $resAfter->assertStatus(200);
        $resAfter->assertDontSee('Bạn có 1 lời mời tham gia nhóm!');
        $resAfter->assertDontSee(route('sinhvien.nhom.xacNhanLoiMoi', $nhom->MaNhom));
        $resAfter->assertDontSee(route('sinhvien.nhom.tuChoiLoiMoi', $nhom->MaNhom));

        // Khi SV4 cố tình gửi request xác nhận: không bị ném ra lỗi (errors), mà điều hướng êm đẹp
        $resDirect = $this->actingAs($sv4->taiKhoan)->post(route('sinhvien.nhom.xacNhanLoiMoi', $nhom->MaNhom));
        $resDirect->assertRedirect();
        $resDirect->assertSessionMissing('errors');

        // Khi SV4 xem Dashboard: Lời mời cũng không xuất hiện
        $resDashboard = $this->actingAs($sv4->taiKhoan)->get(route('sinhvien.dashboard'));
        $resDashboard->assertStatus(200);
        $resDashboard->assertDontSee('Nhóm Test Tự Động Biến Mất Lời Mời');
    }

    public function test_nhieu_hon_2_loi_moi_se_co_khung_cuon_va_nhan_cuon_xem_them()
    {
        $tk = TaiKhoan::where('MaVaiTro', 'VT03')->first();
        $this->assertNotNull($tk);
        $sv = SinhVien::where('MaTK', $tk->MaTK)->first();
        $this->assertNotNull($sv);

        $hk = HocKy::first();
        $hp = HocPhan::first();

        // Chọn sinh viên cùng ngành để các lời mời hợp lệ
        $otherSv = SinhVien::where('MaSV', '!=', $sv->MaSV)->where('MaNganh', $sv->MaNganh)->first();
        $this->assertNotNull($otherSv);

        // Tạo 3 nhóm mở gửi 3 lời mời cho SV này
        for ($i = 1; $i <= 3; $i++) {
            $nhom = Nhom::create([
                'MaNhom'       => 'TEST_SCROLL_' . $i . '_' . rand(1000, 9999),
                'TenNhom'      => 'Nhóm Cuộn ' . $i,
                'MaHocKy'      => $hk->MaHocKy,
                'MaHocPhan'    => $hp->MaHocPhan,
                'TrangThai'    => 'Đang mở',
            ]);

            ThanhVienNhom::create([
                'MaNhom'      => $nhom->MaNhom,
                'MaSV'        => $otherSv->MaSV,
                'VaiTro'      => 'Trưởng nhóm',
                'TrangThai'   => 'da_tham_gia',
                'NgayThamGia' => now(),
            ]);

            ThanhVienNhom::create([
                'MaNhom'      => $nhom->MaNhom,
                'MaSV'        => $sv->MaSV,
                'VaiTro'      => 'Thành viên',
                'TrangThai'   => 'cho_xac_nhan',
                'NgayThamGia' => null,
            ]);
        }

        $res = $this->actingAs($tk)->get(route('sinhvien.nhom.index', ['hoc_ky' => $hk->MaHocKy, 'hoc_phan' => $hp->MaHocPhan]));
        $res->assertStatus(200);

        // Khung cuộn phải tồn tại
        $res->assertSee('loi-moi-scroll-container');
        // Vì có 3 lời mời (>2), phải hiển thị badge "Cuộn để xem thêm"
        $res->assertSee('Cuộn để xem thêm');
    }

    public function test_sinh_vien_khac_nganh_khong_the_nhin_thay_nhom_mo_cua_nganh_khac()
    {
        // svCNTT ngành CNTT (7480201)
        $svCNTT = SinhVien::with('taiKhoan')->where('MaNganh', '7480201')->first();
        $this->assertNotNull($svCNTT);

        // svKTPM ngành KTPM (7480103)
        $svKTPM = SinhVien::with('taiKhoan')->where('MaNganh', '7480103')->first();
        $this->assertNotNull($svKTPM);

        $hk = HocKy::where('MaHocKy', 'HK2627_1')->first() ?? HocKy::first();
        $hp = HocPhan::first();

        // Tạo nhóm mở của svKTPM
        $nhomKTPM = Nhom::create([
            'MaNhom'    => 'TEST_KTPM_' . rand(1000, 9999),
            'TenNhom'   => 'Nhóm KTPM Riêng Biệt 999',
            'MaHocKy'   => $hk->MaHocKy,
            'MaHocPhan' => $hp->MaHocPhan,
            'TrangThai' => 'Đang mở',
        ]);
        ThanhVienNhom::create([
            'MaNhom'      => $nhomKTPM->MaNhom,
            'MaSV'        => $svKTPM->MaSV,
            'VaiTro'      => 'Trưởng nhóm',
            'TrangThai'   => 'da_tham_gia',
            'NgayThamGia' => now(),
        ]);

        // Tạo nhóm mở của svCNTT khác
        $otherCNTT = SinhVien::where('MaNganh', '7480201')->where('MaSV', '!=', $svCNTT->MaSV)->first();
        $nhomCNTT = Nhom::create([
            'MaNhom'    => 'TEST_CNTT_' . rand(1000, 9999),
            'TenNhom'   => 'Nhóm CNTT Cùng Ngành 888',
            'MaHocKy'   => $hk->MaHocKy,
            'MaHocPhan' => $hp->MaHocPhan,
            'TrangThai' => 'Đang mở',
        ]);
        ThanhVienNhom::create([
            'MaNhom'      => $nhomCNTT->MaNhom,
            'MaSV'        => $otherCNTT->MaSV,
            'VaiTro'      => 'Trưởng nhóm',
            'TrangThai'   => 'da_tham_gia',
            'NgayThamGia' => now(),
        ]);

        // svCNTT đăng nhập vào trang nhóm
        $response = $this->actingAs($svCNTT->taiKhoan)->get(route('sinhvien.nhom.index', ['hoc_ky' => $hk->MaHocKy, 'hoc_phan' => 'ALL', 'bo_mon' => 'ALL']));
        $response->assertStatus(200);

        // PHẢI nhìn thấy nhóm cùng ngành CNTT
        $response->assertSee('Nhóm CNTT Cùng Ngành 888');
        // KHÔNG ĐƯỢC nhìn thấy nhóm khác ngành KTPM
        $response->assertDontSee('Nhóm KTPM Riêng Biệt 999');
    }

    public function test_tra_cuu_va_moi_sinh_vien_khac_nganh_se_bi_tu_choi()
    {
        $svCNTT = SinhVien::with('taiKhoan')->where('MaNganh', '7480201')->first();
        $svKTPM = SinhVien::with('taiKhoan')->where('MaNganh', '7480103')->first();

        $hk = HocKy::where('MaHocKy', 'HK2627_1')->first() ?? HocKy::first();
        $hp = HocPhan::first();

        // svCNTT làm trưởng nhóm
        $nhom = Nhom::create([
            'MaNhom'    => 'TEST_LEADER_' . rand(1000, 9999),
            'TenNhom'   => 'Nhóm Leader CNTT',
            'MaHocKy'   => $hk->MaHocKy,
            'MaHocPhan' => $hp->MaHocPhan,
            'TrangThai' => 'Đang mở',
        ]);
        ThanhVienNhom::create([
            'MaNhom'      => $nhom->MaNhom,
            'MaSV'        => $svCNTT->MaSV,
            'VaiTro'      => 'Trưởng nhóm',
            'TrangThai'   => 'da_tham_gia',
            'NgayThamGia' => now(),
        ]);

        // 1. Tra cứu sinh viên khác ngành KTPM
        $lookupRes = $this->actingAs($svCNTT->taiKhoan)->getJson(route('sinhvien.nhom.traCuuSinhVien', [
            'mssv'    => $svKTPM->MaSV,
            'ma_nhom' => $nhom->MaNhom,
        ]));

        $lookupRes->assertStatus(200);
        $lookupData = $lookupRes->json();
        $this->assertFalse($lookupData['can_invite'], 'Không được phép mời sinh viên khác ngành');
        $this->assertStringContainsString('Khác ngành đào tạo', $lookupData['status_text']);

        // 2. Cố tình gửi request POST mời sinh viên khác ngành
        $inviteRes = $this->actingAs($svCNTT->taiKhoan)->post(route('sinhvien.nhom.moiThanhVien'), [
            'MaNhom' => $nhom->MaNhom,
            'MaSV'   => $svKTPM->MaSV,
        ]);

        $inviteRes->assertRedirect();
        $inviteRes->assertSessionHasErrors();
        $this->assertStringContainsString(
            'Chỉ có thể mời sinh viên cùng ngành đào tạo',
            session('errors')->first()
        );
    }

    public function test_xin_gia_nhap_nhom_khac_nganh_se_bi_tu_choi()
    {
        $svCNTT = SinhVien::with('taiKhoan')->where('MaNganh', '7480201')->first();
        $svKTPM = SinhVien::with('taiKhoan')->where('MaNganh', '7480103')->first();

        $hk = HocKy::where('MaHocKy', 'HK2627_1')->first() ?? HocKy::first();
        $hp = HocPhan::first();

        // Nhóm của svKTPM
        $nhomKTPM = Nhom::create([
            'MaNhom'    => 'TEST_REQ_' . rand(1000, 9999),
            'TenNhom'   => 'Nhóm KTPM Không Cùng Ngành',
            'MaHocKy'   => $hk->MaHocKy,
            'MaHocPhan' => $hp->MaHocPhan,
            'TrangThai' => 'Đang mở',
        ]);
        ThanhVienNhom::create([
            'MaNhom'      => $nhomKTPM->MaNhom,
            'MaSV'        => $svKTPM->MaSV,
            'VaiTro'      => 'Trưởng nhóm',
            'TrangThai'   => 'da_tham_gia',
            'NgayThamGia' => now(),
        ]);

        // svCNTT cố xin gia nhập nhóm KTPM
        $joinRes = $this->actingAs($svCNTT->taiKhoan)->post(route('sinhvien.nhom.xinGiaNhap', $nhomKTPM->MaNhom));
        $joinRes->assertRedirect();
        $joinRes->assertSessionHasErrors();
        $this->assertStringContainsString(
            'Bạn chỉ có thể xin gia nhập nhóm có cùng ngành đào tạo',
            session('errors')->first()
        );
    }

    public function test_sinh_vien_trong_danh_sach_16_sv_moi_tao_co_the_tra_cuu_moi_va_gia_nhap_thanh_cong()
    {
        // Lấy 2 bạn trong dải 2001210060 - 2001210075
        $sv60 = SinhVien::with('taiKhoan')->where('MaSV', '2001210060')->first();
        $sv61 = SinhVien::with('taiKhoan')->where('MaSV', '2001210061')->first();

        $this->assertNotNull($sv60, 'SV 2001210060 cần tồn tại');
        $this->assertNotNull($sv61, 'SV 2001210061 cần tồn tại');
        $this->assertEquals($sv60->MaNganh, $sv61->MaNganh, 'Cả hai bạn phải cùng ngành đào tạo');

        // Đảm bảo mật khẩu đã được kích hoạt ACTIVE để thực hiện các thao tác nhóm
        $sv60->taiKhoan->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $sv61->taiKhoan->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);

        $hk = HocKy::where('MaHocKy', 'HK2627_1')->first() ?? HocKy::first();
        $hp = HocPhan::first();

        // 1. Tạo nhóm cho sv60
        $nhom = Nhom::create([
            'MaNhom'    => 'TEST_60_' . rand(1000, 9999),
            'TenNhom'   => 'Nhóm Test Sinh Viên 2001210060',
            'MaHocKy'   => $hk->MaHocKy,
            'MaHocPhan' => $hp->MaHocPhan,
            'TrangThai' => 'Đang mở',
        ]);
        ThanhVienNhom::create([
            'MaNhom'      => $nhom->MaNhom,
            'MaSV'        => $sv60->MaSV,
            'VaiTro'      => 'Trưởng nhóm',
            'TrangThai'   => 'da_tham_gia',
            'NgayThamGia' => now(),
        ]);

        // 2. Tra cứu sv61: Can invite = true
        $lookupRes = $this->actingAs($sv60->taiKhoan)->getJson(route('sinhvien.nhom.traCuuSinhVien', [
            'mssv'    => $sv61->MaSV,
            'ma_nhom' => $nhom->MaNhom,
        ]));
        $lookupRes->assertStatus(200);
        $lookupData = $lookupRes->json();
        $this->assertTrue($lookupData['can_invite'], 'Cùng ngành đào tạo phải mời được');

        // 3. sv60 gửi lời mời cho sv61
        $inviteRes = $this->actingAs($sv60->taiKhoan)->post(route('sinhvien.nhom.moiThanhVien'), [
            'MaNhom' => $nhom->MaNhom,
            'MaSV'   => $sv61->MaSV,
        ]);
        $inviteRes->assertRedirect();
        $inviteRes->assertSessionMissing('errors');

        // Kiểm tra bản ghi lời mời trong DB
        $hasInvite = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)
            ->where('MaSV', $sv61->MaSV)
            ->where('TrangThai', 'cho_xac_nhan')
            ->exists();
        $this->assertTrue($hasInvite, 'Lời mời phải được lưu trong DB');

        // 4. sv61 đăng nhập và chấp nhận lời mời
        $acceptRes = $this->actingAs($sv61->taiKhoan)->post(route('sinhvien.nhom.xacNhanLoiMoi', $nhom->MaNhom));
        $acceptRes->assertRedirect();
        $acceptRes->assertSessionMissing('errors');

        // Kiểm tra sv61 đã trở thành thành viên chính thức
        $isOfficial = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)
            ->where('MaSV', $sv61->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->exists();
        $this->assertTrue($isOfficial, 'SV61 phải trở thành thành viên chính thức của nhóm');
    }

    public function test_nhom_da_du_3_thanh_vien_se_bi_an_khoi_danh_sach_nhom_mo()
    {
        $students = SinhVien::with('taiKhoan')->has('taiKhoan')->where('MaNganh', '7480201')->take(5)->get();
        $this->assertGreaterThanOrEqual(5, $students->count(), 'Cần ít nhất 5 sinh viên cùng ngành để test');

        $viewer = $students[0];
        $sv1 = $students[1];
        $sv2 = $students[2];
        $sv3 = $students[3];
        $sv4 = $students[4];

        // Xóa liên kết cũ trong transaction
        ThanhVienNhom::whereIn('MaSV', [$viewer->MaSV, $sv1->MaSV, $sv2->MaSV, $sv3->MaSV, $sv4->MaSV])->delete();

        $hk = HocKy::where('MaHocKy', 'HK2627_1')->first() ?? HocKy::first();
        $hp = HocPhan::first();

        // 1. Tạo nhóm đã đủ 3 thành viên
        $nhomFull = Nhom::create([
            'MaNhom'    => 'TEST_FULL3_' . rand(1000, 9999),
            'TenNhom'   => 'Nhóm Đã Đủ 3 Người Ẩn Đi',
            'MaHocKy'   => $hk->MaHocKy,
            'MaHocPhan' => $hp->MaHocPhan,
            'TrangThai' => 'Đang mở',
        ]);
        foreach ([$sv1, $sv2, $sv3] as $idx => $s) {
            ThanhVienNhom::create([
                'MaNhom'      => $nhomFull->MaNhom,
                'MaSV'        => $s->MaSV,
                'VaiTro'      => ($idx === 0) ? 'Trưởng nhóm' : 'Thành viên',
                'TrangThai'   => 'da_tham_gia',
                'NgayThamGia' => now(),
            ]);
        }

        // 2. Tạo nhóm còn thiếu thành viên (1 người)
        $nhomNotFull = Nhom::create([
            'MaNhom'    => 'TEST_NOTFULL_' . rand(1000, 9999),
            'TenNhom'   => 'Nhóm Còn Thiếu Người Cần Tuyển Thêm',
            'MaHocKy'   => $hk->MaHocKy,
            'MaHocPhan' => $hp->MaHocPhan,
            'TrangThai' => 'Đang mở',
        ]);
        ThanhVienNhom::create([
            'MaNhom'      => $nhomNotFull->MaNhom,
            'MaSV'        => $sv4->MaSV,
            'VaiTro'      => 'Trưởng nhóm',
            'TrangThai'   => 'da_tham_gia',
            'NgayThamGia' => now(),
        ]);

        // 3. Viewer đăng nhập vào danh sách nhóm
        $response = $this->actingAs($viewer->taiKhoan)->get(route('sinhvien.nhom.index', [
            'hoc_ky'   => $hk->MaHocKy,
            'hoc_phan' => 'ALL',
            'bo_mon'   => 'ALL',
        ]));

        $response->assertStatus(200);

        // Nhóm đã đủ người PHẢI BỊ ẨN HOÀN TOÀN
        $response->assertDontSee('Nhóm Đã Đủ 3 Người Ẩn Đi');

        // Nhóm còn thiếu người PHẢI ĐƯỢC HIỂN THỊ
        $response->assertSee('Nhóm Còn Thiếu Người Cần Tuyển Thêm');
    }

    public function test_sinh_vien_moi_tao_co_trang_thai_initial_bat_buoc_phai_doi_mat_khau_o_lan_dau_dang_nhap()
    {
        // Lấy 1 tài khoản sinh viên mới tạo có trạng thái INITIAL
        $sv = SinhVien::with('taiKhoan')->where('MaSV', '2001210065')->first();
        $this->assertNotNull($sv);

        // Đảm bảo trạng thái INITIAL và bắt buộc đổi mật khẩu
        $sv->taiKhoan->update([
            'TrangThaiMatKhau'  => 'INITIAL',
            'BatBuocDoiMatKhau' => true,
        ]);

        // 1. Khi truy cập vào Dashboard hoặc bất kỳ trang nào: Bị chuyển hướng (302) đến /setup-password
        $response = $this->actingAs($sv->taiKhoan)->get(route('sinhvien.dashboard'));
        $response->assertRedirect(route('password.setup'));

        // 2. Vào trang /setup-password thành công
        $setupPageRes = $this->actingAs($sv->taiKhoan)->get(route('password.setup'));
        $setupPageRes->assertStatus(200);
        $setupPageRes->assertSee('Thiết Lập Mật Khẩu Cá Nhân');
        $setupPageRes->assertSee('Lần Đầu Đăng Nhập');

        // 3. Gửi mật khẩu mới hợp lệ (>= 8 ký tự, có chữ hoa, thường, số)
        $submitRes = $this->actingAs($sv->taiKhoan)->post(route('password.setup.post'), [
            'new_password'              => 'SinhVien@2026',
            'new_password_confirmation' => 'SinhVien@2026',
        ]);

        // Chuyển hướng về Dashboard thành công
        $submitRes->assertRedirect(route('sinhvien.dashboard'));

        // Kiểm tra trong DB: Tài khoản đã được chuyển sang ACTIVE và không còn bắt buộc đổi mật khẩu
        $tkFresh = $sv->taiKhoan->fresh();
        $this->assertEquals('ACTIVE', $tkFresh->TrangThaiMatKhau);
        $this->assertFalse((bool)$tkFresh->BatBuocDoiMatKhau);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('SinhVien@2026', $tkFresh->MatKhau));
    }
}



