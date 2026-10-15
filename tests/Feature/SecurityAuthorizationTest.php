<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\GiangVien;
use App\Models\DeTai;
use App\Models\HocKy;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\ThongBao;
use App\Models\BaoCaoTienDo;
use App\Models\HoSoBaoVe;
use App\Models\HoiDongBaoVe;
use Illuminate\Support\Facades\DB;

/**
 * Bộ kiểm thử an toàn thông tin & phân quyền toàn diện cho hệ thống Quản lý Khóa luận tốt nghiệp.
 * Bao gồm đầy đủ 7 kịch bản kiểm thử bảo mật cốt lõi:
 *  1. IDOR - Truy cập dữ liệu của người khác qua thay đổi URL/ID
 *  2. Tampering - Sửa/Xóa dữ liệu của người khác bằng request trực tiếp
 *  3. BFLA - Truy cập trái phép chức năng của vai trò khác
 *  4. Scope Boundary - Vượt phạm vi quản lý của Bộ môn / Khoa
 *  5. File Access - Tải file riêng tư trái phép
 *  6. API Bypass - Gửi request API trực tiếp nhằm vượt qua kiểm tra ở UI
 *  7. Non-existent ID - Xử lý ID không tồn tại an toàn (404, không rò rỉ stack trace)
 */
class SecurityAuthorizationTest extends TestCase
{
    // =========================================================================
    // TRƯỜNG HỢP 1: TRUY CẬP DỮ LIỆU CỦA NGƯỜI KHÁC (IDOR)
    // =========================================================================

    /**
     * Test Case 1.1: Sinh viên A đăng nhập, thay ID trên URL thành ID thông báo riêng tư của Sinh viên B.
     * Kỳ vọng: Hệ thống từ chối truy cập (403 Forbidden).
     */
    public function test_student_cannot_view_private_notification_of_another_user()
    {
        $studentA = TaiKhoan::where('TenDangNhap', '2001210001')->firstOrFail();
        $studentB = TaiKhoan::where('TenDangNhap', '2001210002')->firstOrFail();

        // Tạo thông báo gửi riêng đích danh cho Sinh viên B (mã ngắn gọn phù hợp DB)
        $thongBaoPrivateB = ThongBao::create([
            'MaThongBao'   => 'TB_' . rand(10000, 99999),
            'TieuDe'       => 'Cảnh báo học tập riêng tư Sinh viên B',
            'NoiDung'      => 'Thông tin nhạy cảm của sinh viên B.',
            'LoaiThongBao' => 'Cá nhân',
            'DoiTuongNhan' => $studentB->MaTK,
            'NgayTao'      => now(),
            'TrangThai'    => 'Đã gửi',
        ]);

        try {
            $response = $this->actingAs($studentA)->get('/sinhvien/thongbao/' . $thongBaoPrivateB->MaThongBao);
            $response->assertStatus(403);
        } finally {
            $thongBaoPrivateB->delete();
        }
    }

    /**
     * Test Case 1.2: Sinh viên không được xem file đề cương của đề tài chưa được duyệt / chưa công bố.
     * Kỳ vọng: Hệ thống từ chối truy cập (403 Forbidden).
     */
    public function test_student_cannot_view_unapproved_topic_details()
    {
        $studentA = TaiKhoan::where('TenDangNhap', '2001210001')->firstOrFail();
        $hocKy = HocKy::firstOrFail();

        // Tạo đề tài nháp chưa duyệt của giảng viên
        $draftDeTai = DeTai::create([
            'MaDeTai'   => 'DT_' . rand(1000, 9999),
            'TenDeTai'  => 'Đề tài bảo mật thử nghiệm đang nháp',
            'MaGV'      => 'GV00000001',
            'MaHocKy'   => $hocKy->MaHocKy,
            'TrangThai' => 'Chờ duyệt cấp Bộ môn',
            'SoLuongSV' => 2,
        ]);

        try {
            $response = $this->actingAs($studentA)->get('/files/decuong/' . $draftDeTai->MaDeTai);
            $response->assertStatus(403);
        } finally {
            $draftDeTai->delete();
        }
    }

    // =========================================================================
    // TRƯỜNG HỢP 2: THAO TÁC TRÊN DỮ LIỆU CỦA NGƯỜI KHÁC (TAMPERING)
    // =========================================================================

    /**
     * Test Case 2.1: Sinh viên A không phải Trưởng nhóm cố tình khai trừ thành viên của nhóm B.
     * Kỳ vọng: Bị từ chối (redirect back kèm flash error), thành viên không bị xóa.
     */
    public function test_student_cannot_kick_member_from_another_group()
    {
        $studentA = TaiKhoan::where('TenDangNhap', '2001210001')->firstOrFail();
        $svA = SinhVien::where('MaTK', $studentA->MaTK)->firstOrFail();

        $otherLeader = SinhVien::where('MaTK', '!=', $studentA->MaTK)->firstOrFail();
        $targetMember = SinhVien::where('MaTK', '!=', $studentA->MaTK)->where('MaSV', '!=', $otherLeader->MaSV)->firstOrFail();

        $testGroup = Nhom::firstOrCreate(
            ['MaNhom' => 'NH_SECTEST'],
            [
                'TenNhom'   => 'Nhóm thử nghiệm an toàn',
                'TrangThai' => 'Đang hoạt động',
                'MaHocKy'   => HocKy::first()?->MaHocKy,
            ]
        );

        ThanhVienNhom::updateOrCreate(
            ['MaNhom' => $testGroup->MaNhom, 'MaSV' => $otherLeader->MaSV],
            ['VaiTro' => 'Trưởng nhóm', 'TrangThai' => 'da_tham_gia']
        );
        ThanhVienNhom::updateOrCreate(
            ['MaNhom' => $testGroup->MaNhom, 'MaSV' => $targetMember->MaSV],
            ['VaiTro' => 'Thành viên', 'TrangThai' => 'da_tham_gia']
        );

        $response = $this->actingAs($studentA)->post("/sinhvien/nhom/{$testGroup->MaNhom}/khai-tru/{$targetMember->MaSV}");
        $response->assertSessionHasErrors();

        // Xác minh thành viên vẫn an toàn trong DB, không bị xóa
        $this->assertDatabaseHas('ThanhVienNhom', [
            'MaNhom' => $testGroup->MaNhom,
            'MaSV'   => $targetMember->MaSV,
        ]);
    }

    /**
     * Test Case 2.2: Giảng viên A cố tình chấm điểm hồ sơ của Hội đồng mà mình không tham gia.
     * Kỳ vọng: Bị từ chối 403 Forbidden.
     */
    public function test_lecturer_cannot_grade_thesis_outside_assigned_council()
    {
        // GV00000003 không thuộc hội đồng của hồ sơ bảo vệ
        $gvAccount = TaiKhoan::where('TenDangNhap', 'GV00000003')->firstOrFail();
        $hocKy = HocKy::firstOrFail();

        $hoiDong = \App\Models\HoiDong::create([
            'MaHoiDong'  => 'HD_' . rand(1000, 9999),
            'TenHoiDong' => 'Hội đồng bảo vệ thử nghiệm bảo mật',
            'MaHocKy'    => $hocKy->MaHocKy,
            'TrangThai'  => 'Đang hoạt động',
        ]);

        $hoSo = HoSoBaoVe::create([
            'MaHoSo'    => 'HS_' . rand(1000, 9999),
            'MaHoiDong' => $hoiDong->MaHoiDong,
            'TrangThai' => 'Đã nộp',
        ]);

        try {
            $response = $this->actingAs($gvAccount)->post('/giangvien/cham-diem', [
                'MaHoiDong'    => $hoiDong->MaHoiDong,
                'MaHoSo'       => $hoSo->MaHoSo,
                'LoaiKhoaLuan' => 'KLCN',
                'diems'        => [
                    'SV_TEST' => [
                        'Diem' => 8.0,
                    ]
                ]
            ]);

            // Phải nhận 403 Forbidden vì giảng viên không thuộc hội đồng
            $response->assertStatus(403);
        } finally {
            $hoSo->delete();
            $hoiDong->delete();
        }
    }

    // =========================================================================
    // TRƯỜNG HỢP 3: TRUY CẬP SAI VAI TRÒ (BFLA)
    // =========================================================================

    /**
     * Test Case 3.1: Sinh viên nhập trực tiếp URL quản trị của Giáo vụ / Admin.
     * Kỳ vọng: 403 Forbidden.
     */
    public function test_student_cannot_access_admin_management_routes()
    {
        $student = TaiKhoan::where('TenDangNhap', '2001210001')->firstOrFail();

        $adminUrls = [
            '/admin',
            '/admin/sinhvien',
            '/admin/giangvien',
            '/admin/kehoach',
            '/admin/hoi-dong',
        ];

        foreach ($adminUrls as $url) {
            $response = $this->actingAs($student)->get($url);
            $response->assertStatus(403);
        }
    }

    /**
     * Test Case 3.2: Sinh viên nhập trực tiếp URL phê duyệt đề tài của Giảng viên / TBM.
     * Kỳ vọng: 403 Forbidden.
     */
    public function test_student_cannot_access_lecturer_approval_routes()
    {
        $student = TaiKhoan::where('TenDangNhap', '2001210001')->firstOrFail();

        $response = $this->actingAs($student)->get('/giangvien/duyet-detai');
        $response->assertStatus(403);

        $response = $this->actingAs($student)->get('/truongbomon/duyet-detai');
        $response->assertStatus(403);
    }

    /**
     * Test Case 3.3: Giảng viên thường nhập trực tiếp URL của Trưởng bộ môn hoặc Trưởng khoa.
     * Kỳ vọng: 403 Forbidden.
     */
    public function test_regular_lecturer_cannot_access_tbm_or_tk_routes()
    {
        // GV00000002 là giảng viên thường (Role VT02)
        $regularGv = TaiKhoan::where('TenDangNhap', 'GV00000002')->firstOrFail();

        $response = $this->actingAs($regularGv)->get('/truongbomon/duyet-detai');
        $response->assertStatus(403);

        $response = $this->actingAs($regularGv)->get('/truongkhoa/duyet-detai');
        $response->assertStatus(403);
    }

    // =========================================================================
    // TRƯỜNG HỢP 4: TRUY CẬP NGOÀI PHẠM VI QUẢN LÝ (SCOPE BOUNDARIES)
    // =========================================================================

    /**
     * Test Case 4.1: Trưởng bộ môn HTTT không được xem/duyệt đề tài thuộc Bộ môn CNPM.
     * Kỳ vọng: 403 Forbidden.
     */
    public function test_tbm_cannot_access_topic_outside_department_scope()
    {
        $userHTTT = TaiKhoan::where('TenDangNhap', 'TBM_BM_HTTT_GV00000003')->firstOrFail();
        $detaiCNPM = DeTai::where('MaDeTai', 'DT01')->firstOrFail();

        $response = $this->actingAs($userHTTT)->get('/truongbomon/duyet-detai/' . $detaiCNPM->MaDeTai);
        $response->assertStatus(403);
    }

    /**
     * Test Case 4.2: Trưởng khoa ATTT không được xem/duyệt đề tài thuộc Khoa CNTT.
     * Kỳ vọng: 403 Forbidden.
     */
    public function test_tk_cannot_access_topic_outside_faculty_scope()
    {
        $userTKATTT = TaiKhoan::where('TenDangNhap', 'TK_ATTT_GV00000004')->firstOrFail();
        $detaiCNTT = DeTai::where('MaDeTai', 'DT01')->firstOrFail();

        $response = $this->actingAs($userTKATTT)->get('/truongkhoa/duyet-detai/' . $detaiCNTT->MaDeTai);
        $response->assertStatus(403);
    }

    // =========================================================================
    // TRƯỜNG HỢP 5: TRUY CẬP FILE TRÁI PHÉP (INSECURE FILE DOWNLOAD)
    // =========================================================================

    /**
     * Test Case 5.1: Sinh viên cố tải báo cáo tiến độ của đề tài thuộc nhóm khác.
     * Kỳ vọng: 403 Forbidden.
     */
    public function test_student_cannot_download_other_group_progress_report()
    {
        $studentA = TaiKhoan::where('TenDangNhap', '2001210001')->firstOrFail();

        // Tạo 1 báo cáo tiến độ thuộc về 1 nhóm khác hoàn toàn
        $baoCao = BaoCaoTienDo::create([
            'MaBaoCao'     => 'BC_' . rand(10000, 99999),
            'MaDeTai'      => 'DT01',
            'MaGV'         => 'GV00000001',
            'MaNhom'       => 'NH_OTHER',
            'LanBaoCao'    => 1,
            'TieuDe'       => 'Báo cáo mật tiến độ',
            'NoiDung'      => 'Nội dung riêng tư',
            'DuongDanFile' => 'baocao/secret_report.pdf',
            'NgayNop'      => now(),
            'TrangThai'    => 'Đã nộp',
        ]);

        try {
            $response = $this->actingAs($studentA)->get('/files/baocao/' . $baoCao->MaBaoCao);
            $response->assertStatus(403);
        } finally {
            $baoCao->delete();
        }
    }

    /**
     * Test Case 5.2: Khách vãng lai (Guest) không thể tải bất kỳ file nào từ endpoint tải file an toàn.
     * Kỳ vọng: Chuyển hướng về trang đăng nhập (302 Redirect to /login).
     */
    public function test_guest_cannot_download_files()
    {
        $response = $this->get('/files/baocao/BC01');
        $response->assertRedirect('/login');

        $response = $this->get('/files/hoso/HS01');
        $response->assertRedirect('/login');

        $response = $this->get('/files/decuong/DT01');
        $response->assertRedirect('/login');
    }

    // =========================================================================
    // TRƯỜNG HỢP 6: GỬI REQUEST TRỰC TIẾP QUA API (API BYPASS)
    // =========================================================================

    /**
     * Test Case 6.1: Sinh viên gửi request trực tiếp PUT /api/detais/{id} để sửa đổi nội dung đề tài.
     * Kỳ vọng: 403 Forbidden (Vì chỉ Giảng viên sở hữu hoặc Giáo vụ mới có quyền sửa).
     */
    public function test_student_cannot_mutate_topics_via_direct_api_call()
    {
        $student = TaiKhoan::where('TenDangNhap', '2001210001')->firstOrFail();
        $detai = DeTai::where('MaDeTai', 'DT01')->firstOrFail();

        $response = $this->actingAs($student)->putJson('/api/detais/' . $detai->MaDeTai, [
            'TenDeTai' => 'Hacker modified this topic name',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test Case 6.2: Khách chưa đăng nhập gửi POST trực tiếp tới /api/detais.
     * Kỳ vọng: Bị từ chối (401 Unauthorized).
     */
    public function test_guest_cannot_post_to_api_endpoints()
    {
        $response = $this->postJson('/api/detais', [
            'TenDeTai' => 'Malicious Topic',
        ]);

        $response->assertStatus(401);
    }

    // =========================================================================
    // TRƯỜNG HỢP 7: TRUY CẬP DỮ LIỆU KHÔNG TỒN TẠI (CLEAN 404 HANDLING)
    // =========================================================================

    /**
     * Test Case 7.1: Truy cập ID không tồn tại trên các route phân quyền.
     * Kỳ vọng: 404 Not Found, không làm sập ứng dụng hay rò rỉ stack trace.
     */
    public function test_accessing_non_existent_resources_returns_clean_404()
    {
        $userCNPM = TaiKhoan::where('TenDangNhap', 'TBM_BM_CNPM_GV00000001')->firstOrFail();

        // 1. Xem chi tiết đề tài không tồn tại
        $response = $this->actingAs($userCNPM)->get('/truongbomon/duyet-detai/DT_NON_EXISTENT_99999');
        $response->assertStatus(404);

        // 2. Tải file không tồn tại
        $admin = TaiKhoan::where('TenDangNhap', 'admin')->firstOrFail();
        $response = $this->actingAs($admin)->get('/files/baocao/BC_NON_EXISTENT_99999');
        $response->assertStatus(404);
    }
}
