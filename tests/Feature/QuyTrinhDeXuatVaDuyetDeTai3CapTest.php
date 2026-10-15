<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\GiangVien;
use App\Models\DeTai;
use App\Models\HocKy;
use App\Models\HocPhan;
use App\Models\HocPhanHocKy;
use App\Models\ChiTietDuyetDeTai;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class QuyTrinhDeXuatVaDuyetDeTai3CapTest extends TestCase
{
    use DatabaseTransactions;

    protected $userGV;
    protected $gv;
    protected $userTbmCnpm;
    protected $userTbmAttt;
    protected $userTruongKhoa;
    protected $userSinhVien;
    protected $hocKy;
    protected $hocPhan;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Giảng viên đề xuất (thuộc BM_CNPM)
        $this->userGV = TaiKhoan::where('TenDangNhap', 'GV00000021')->firstOrFail();
        $this->userGV->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->gv = GiangVien::where('MaTK', $this->userGV->MaTK)->orWhere('MaGV', 'GV00000021')->firstOrFail();

        // 2. Trưởng bộ môn CNPM
        $this->userTbmCnpm = TaiKhoan::where('TenDangNhap', 'TBM_CNTT_CNPM_GV002')->firstOrFail();
        $this->userTbmCnpm->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);

        // 3. Trưởng bộ môn ATTT (Bộ môn khác)
        $this->userTbmAttt = TaiKhoan::where('TenDangNhap', 'TBM_CNTT_ATTT_GV004')->firstOrFail();
        $this->userTbmAttt->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);

        // 4. Trưởng khoa CNTT
        $this->userTruongKhoa = TaiKhoan::where('TenDangNhap', 'TK_CNTT_GV001')->firstOrFail();
        $this->userTruongKhoa->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);

        // 5. Sinh viên
        $this->userSinhVien = TaiKhoan::where('TenDangNhap', '2001210060')->firstOrFail();
        $this->userSinhVien->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);

        // 6. Học kỳ & Học phần test
        $this->hocKy = HocKy::where('MaHocKy', 'HK2627_1')->first() ?? HocKy::first();
        $this->hocPhan = HocPhan::where('MaBoMon', 'CNPM')->first() ?? HocPhan::first();

        // Đảm bảo học phần mở trong học kỳ
        HocPhanHocKy::firstOrCreate([
            'MaHocKy' => $this->hocKy->MaHocKy,
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
        ], [
            'TrangThai' => 'Đang mở',
        ]);
    }

    /**
     * CASE 1: Luồng hoàn chỉnh thông suốt 3 cấp: GV đề xuất -> TBM duyệt -> TK duyệt -> Đã công bố cho SV
     */
    public function test_case_1_luong_de_xuat_va_duyet_3_cap_hoan_chinh_thong_suot()
    {
        // ── CẤP 1: Giảng viên đề xuất đề tài mới ──
        $tenDeTai = 'Đề tài Nghiên cứu Microservices ' . uniqid();
        $postData = [
            'TenDeTai'  => $tenDeTai,
            'MaHocKy'   => $this->hocKy->MaHocKy,
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'MoTa'      => 'Mô tả chi tiết kiến trúc microservices và triển khai Kubernetes phân tán hoàn chỉnh.',
            'YeuCau'    => 'Sinh viên cần nắm vững kiến thức Docker và Golang.',
            'LinhVuc'   => 'Công nghệ phần mềm',
        ];

        $resGV = $this->actingAs($this->userGV)->post(route('giangvien.detai.store'), $postData);
        $resGV->assertRedirect(route('giangvien.detai.index'));
        $resGV->assertSessionHas('success');

        $deTai = DeTai::where('TenDeTai', $tenDeTai)->first();
        $this->assertNotNull($deTai, 'Đề tài phải được tạo thành công.');
        $this->assertEquals('Chờ duyệt cấp Bộ môn', $deTai->TrangThai);
        $this->assertEquals($this->gv->MaGV, $deTai->MaGV);

        // Lịch sử duyệt phải ghi nhận bước đề xuất
        $history1 = ChiTietDuyetDeTai::where('MaDeTai', $deTai->MaDeTai)->latest('created_at')->first();
        $this->assertNotNull($history1);
        $this->assertEquals('Gửi đề xuất mới', $history1->HanhDong);

        // ── CẤP 2: Trưởng bộ môn CNPM duyệt ──
        $resTBM = $this->actingAs($this->userTbmCnpm)->post("/truong-bo-mon/phe-duyet-de-tai/de-tai/{$deTai->MaDeTai}/duyet", [
            'GhiChu' => 'Bộ môn đồng ý thông qua đề tài, chuyển lên Khoa thẩm định.',
        ]);
        $resTBM->assertSessionHas('success');

        $deTai->refresh();
        $this->assertEquals('Chờ duyệt cấp Khoa', $deTai->TrangThai);
        $this->assertNotNull($deTai->NgayDuyetBM);

        // ── CẤP 3: Trưởng khoa CNTT phê duyệt và Công bố ──
        $resTK = $this->actingAs($this->userTruongKhoa)->post("/truongkhoa/duyet-detai/{$deTai->MaDeTai}/duyet");
        $resTK->assertSessionHas('success');

        $deTai->refresh();
        $this->assertEquals('Đã công bố', $deTai->TrangThai);
        $this->assertNotNull($deTai->NgayDuyetKhoa);
        $this->assertNotNull($deTai->NgayCongBo);

        // ── SINH VIÊN: Nhìn thấy đề tài trong danh sách đăng ký ──
        $resSV = $this->actingAs($this->userSinhVien)->get(route('sinhvien.dangky.index', [
            'HocKy' => $this->hocKy->MaHocKy,
            'HocPhan' => $this->hocPhan->MaHocPhan,
        ]));
        $resSV->assertStatus(200);
        $resSV->assertSee($tenDeTai);
    }

    /**
     * CASE 2: Trưởng bộ môn yêu cầu chỉnh sửa -> Giảng viên cập nhật nộp lại -> TBM duyệt
     */
    public function test_case_2_tbm_yeu_cau_chinh_sua_giang_vien_cap_nhat_va_nop_lai()
    {
        // 1. Tạo đề tài đang chờ TBM duyệt
        $deTai = DeTai::create([
            'MaDeTai' => 'DT_TEST_EDIT_' . rand(100, 999),
            'MaGV' => $this->gv->MaGV,
            'TenDeTai' => 'Đề tài kiểm thử yêu cầu chỉnh sửa ' . uniqid(),
            'MoTa' => 'Mô tả cần được làm rõ thêm chi tiết về cơ sở dữ liệu.',
            'YeuCau' => 'Yêu cầu năng lực sinh viên lập trình web.',
            'LinhVuc' => 'Công nghệ phần mềm',
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'HocPhan' => $this->hocPhan->TenHocPhan,
            'SoLuongSinhVienToiDa' => 3,
            'MaHocKy' => $this->hocKy->MaHocKy,
            'TrangThai' => 'Chờ duyệt cấp Bộ môn',
            'NgayDeXuat' => now(),
        ]);

        // 2. TBM gửi yêu cầu chỉnh sửa
        $lyDo = 'Cần bổ sung phạm vi công nghệ và công cụ sử dụng trong đề cương.';
        $resTbmEdit = $this->actingAs($this->userTbmCnpm)->post("/truong-bo-mon/phe-duyet-de-tai/de-tai/{$deTai->MaDeTai}/yeu-cau-chinh-sua", [
            'YeuCauSua' => $lyDo,
        ]);
        $resTbmEdit->assertSessionHas('success');

        $deTai->refresh();
        $this->assertEquals('Yêu cầu chỉnh sửa', $deTai->TrangThai);
        $this->assertEquals($lyDo, $deTai->LyDoTuChoi);

        // 3. Giảng viên cập nhật và nộp lại đề tài
        $resGvUpdate = $this->actingAs($this->userGV)->put(route('giangvien.detai.update', $deTai->MaDeTai), [
            'TenDeTai'  => $deTai->TenDeTai . ' (Đã cập nhật)',
            'MaHocKy'   => $this->hocKy->MaHocKy,
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'MoTa'      => 'Đã bổ sung chi tiết công nghệ Docker, PostgreSQL và Redis theo góp ý của Bộ môn.',
            'YeuCau'    => 'Sinh viên cần nắm vững Docker và PostgreSQL.',
            'LinhVuc'   => 'Công nghệ phần mềm',
        ]);
        $resGvUpdate->assertSessionHas('success');

        $deTai->refresh();
        $this->assertEquals('Chờ duyệt cấp Bộ môn', $deTai->TrangThai);

        // 4. TBM duyệt sau khi đã sửa đạt yêu cầu
        $resTbmApprove = $this->actingAs($this->userTbmCnpm)->post("/truong-bo-mon/phe-duyet-de-tai/de-tai/{$deTai->MaDeTai}/duyet", [
            'GhiChu' => 'Đề tài đã hoàn thiện tốt, đồng ý thông qua.',
        ]);
        $resTbmApprove->assertSessionHas('success');

        $deTai->refresh();
        $this->assertEquals('Chờ duyệt cấp Khoa', $deTai->TrangThai);
    }

    /**
     * CASE 3: Trưởng bộ môn từ chối đề tài -> Trạng thái thành 'Từ chối', không lên Khoa
     */
    public function test_case_3_tbm_tu_choi_de_tai()
    {
        $deTai = DeTai::create([
            'MaDeTai' => 'DT_TEST_REJ_' . rand(100, 999),
            'MaGV' => $this->gv->MaGV,
            'TenDeTai' => 'Đề tài không phù hợp phạm vi ' . uniqid(),
            'MoTa' => 'Đề tài trùng lặp với hướng nghiên cứu của năm trước.',
            'YeuCau' => 'Yêu cầu sinh viên có kinh nghiệm.',
            'LinhVuc' => 'Công nghệ phần mềm',
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'HocPhan' => $this->hocPhan->TenHocPhan,
            'SoLuongSinhVienToiDa' => 3,
            'MaHocKy' => $this->hocKy->MaHocKy,
            'TrangThai' => 'Chờ duyệt cấp Bộ môn',
            'NgayDeXuat' => now(),
        ]);

        $lyDo = 'Đề tài trùng lặp hoàn toàn với khóa luận K21 đã nghiệm thu.';
        $res = $this->actingAs($this->userTbmCnpm)->post("/truong-bo-mon/phe-duyet-de-tai/de-tai/{$deTai->MaDeTai}/tu-choi", [
            'LyDoTuChoi' => $lyDo,
        ]);
        $res->assertSessionHas('success');

        $deTai->refresh();
        $this->assertEquals('Từ chối', $deTai->TrangThai);
        $this->assertEquals($lyDo, $deTai->LyDoTuChoi);
        $this->assertNull($deTai->NgayDuyetBM);
    }

    /**
     * CASE 4: Trưởng khoa yêu cầu chỉnh sửa đề tài
     */
    public function test_case_4_truong_khoa_yeu_cau_chinh_sua_de_tai()
    {
        $deTai = DeTai::create([
            'MaDeTai' => 'DT_TEST_TK_EDIT_' . rand(100, 999),
            'MaGV' => $this->gv->MaGV,
            'TenDeTai' => 'Đề tài đã qua TBM chờ Khoa duyệt ' . uniqid(),
            'MoTa' => 'Đề tài đã được Bộ môn đồng ý, chờ Khoa xem xét.',
            'YeuCau' => 'Yêu cầu kỹ năng phân tích thiết kế.',
            'LinhVuc' => 'Công nghệ phần mềm',
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'HocPhan' => $this->hocPhan->TenHocPhan,
            'SoLuongSinhVienToiDa' => 3,
            'MaHocKy' => $this->hocKy->MaHocKy,
            'TrangThai' => 'Chờ duyệt cấp Khoa',
            'NgayDeXuat' => now(),
            'NgayDuyetBM' => now(),
            'NguoiDuyetBM' => $this->userTbmCnpm->MaGV,
        ]);

        $lyDo = 'Trưởng khoa yêu cầu chuẩn hóa lại tên đề tài không viết tắt.';
        $res = $this->actingAs($this->userTruongKhoa)->post("/truongkhoa/duyet-detai/{$deTai->MaDeTai}/yeu-cau-chinh-sua", [
            'YeuCauSua' => $lyDo,
        ]);
        $res->assertSessionHas('success');

        $deTai->refresh();
        $this->assertEquals('Yêu cầu chỉnh sửa', $deTai->TrangThai);
        $this->assertEquals($lyDo, $deTai->LyDoTuChoi);
    }

    /**
     * CASE 5: Trưởng khoa từ chối đề tài -> Trạng thái 'Từ chối', không công bố
     */
    public function test_case_5_truong_khoa_tu_choi_de_tai()
    {
        $deTai = DeTai::create([
            'MaDeTai' => 'DT_TEST_TK_REJ_' . rand(100, 999),
            'MaGV' => $this->gv->MaGV,
            'TenDeTai' => 'Đề tài bị Khoa từ chối ' . uniqid(),
            'MoTa' => 'Đề tài không đáp ứng chuẩn đầu ra của ngành.',
            'YeuCau' => 'Yêu cầu sinh viên.',
            'LinhVuc' => 'Công nghệ phần mềm',
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'HocPhan' => $this->hocPhan->TenHocPhan,
            'SoLuongSinhVienToiDa' => 3,
            'MaHocKy' => $this->hocKy->MaHocKy,
            'TrangThai' => 'Chờ duyệt cấp Khoa',
            'NgayDeXuat' => now(),
            'NgayDuyetBM' => now(),
            'NguoiDuyetBM' => $this->userTbmCnpm->MaGV,
        ]);

        $lyDo = 'Khoa quyết định không phê duyệt do đề tài không đáp ứng chuẩn đầu ra chuyên môn.';
        $res = $this->actingAs($this->userTruongKhoa)->post("/truongkhoa/duyet-detai/{$deTai->MaDeTai}/tu-choi", [
            'LyDoTuChoi' => $lyDo,
        ]);
        $res->assertSessionHas('success');

        $deTai->refresh();
        $this->assertEquals('Từ chối', $deTai->TrangThai);
        $this->assertEquals($lyDo, $deTai->LyDoTuChoi);
        $this->assertNull($deTai->NgayCongBo);
    }

    /**
     * CASE 6: Ràng buộc bảo mật & phân quyền (TBM khác bộ môn, Sinh viên không được duyệt)
     */
    public function test_case_6_bao_mat_phan_quyen_duyet_de_tai()
    {
        $deTai = DeTai::create([
            'MaDeTai' => 'DT_TEST_SEC_' . rand(100, 999),
            'MaGV' => $this->gv->MaGV,
            'TenDeTai' => 'Đề tài kiểm thử bảo mật phân quyền ' . uniqid(),
            'MoTa' => 'Đề tài thuộc BM CNPM không cho phép TBM ATTT phê duyệt.',
            'YeuCau' => 'Yêu cầu bảo mật.',
            'LinhVuc' => 'Công nghệ phần mềm',
            'MaHocPhan' => $this->hocPhan->MaHocPhan,
            'HocPhan' => $this->hocPhan->TenHocPhan,
            'SoLuongSinhVienToiDa' => 3,
            'MaHocKy' => $this->hocKy->MaHocKy,
            'TrangThai' => 'Chờ duyệt cấp Bộ môn',
            'NgayDeXuat' => now(),
        ]);

        // 1. TBM ATTT cố tình duyệt đề tài của BM CNPM -> 403
        $resTbmAttt = $this->actingAs($this->userTbmAttt)->post("/truong-bo-mon/phe-duyet-de-tai/de-tai/{$deTai->MaDeTai}/duyet", [
            'GhiChu' => 'Cố tình duyệt trái quyền hạn bộ môn.',
        ]);
        $resTbmAttt->assertStatus(403);

        // 2. Sinh viên cố tình gửi request duyệt đề tài -> 403
        $resSV = $this->actingAs($this->userSinhVien)->post("/truong-bo-mon/phe-duyet-de-tai/de-tai/{$deTai->MaDeTai}/duyet", [
            'GhiChu' => 'Sinh viên cố tình hack quyền duyệt.',
        ]);
        $resSV->assertStatus(403);

        // 3. Giảng viên thường cố tình duyệt cấp Khoa -> 403
        $resGvTk = $this->actingAs($this->userGV)->post("/truongkhoa/duyet-detai/{$deTai->MaDeTai}/duyet");
        $resGvTk->assertStatus(403);
    }
}
