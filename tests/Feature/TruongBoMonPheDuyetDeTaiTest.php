<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\ChiTietDuyetDeTai;
use Illuminate\Support\Facades\DB;

class TruongBoMonPheDuyetDeTaiTest extends TestCase
{
    protected $tbmCnpm;
    protected $tbmAttt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tbmCnpm = TaiKhoan::where('TenDangNhap', 'TBM_BM_CNPM_GV00000001')->first();
        $this->tbmAttt = TaiKhoan::where('TenDangNhap', 'TBM_BM_ATTT_GV00000005')->first();

        $this->assertNotNull($this->tbmCnpm, 'Tài khoản TBM CNPM phải tồn tại.');
        $this->assertNotNull($this->tbmAttt, 'Tài khoản TBM ATTT phải tồn tại.');
    }

    /**
     * CASE 1: Đổi học kỳ -> danh sách giảng viên và số liệu thay đổi tương ứng.
     */
    public function test_case_1_doi_hoc_ky_thay_doi_so_lieu_giang_vien()
    {
        // 1. Xem ở học kỳ HK2627_2
        $responseHk2 = $this->actingAs($this->tbmCnpm)->get('/truong-bo-mon/phe-duyet-de-tai?hocKy=HK2627_2');
        $responseHk2->assertStatus(200);
        $responseHk2->assertSee('Phê duyệt đề tài');
        $responseHk2->assertSee('GV00000007'); // GV có đề tài
        $responseHk2->assertSee('GV00000004'); // GV có đề tài chờ duyệt

        // 2. Đổi sang học kỳ HK2425_1
        $responseHk1 = $this->actingAs($this->tbmCnpm)->get('/truong-bo-mon/phe-duyet-de-tai?hocKy=HK2425_1');
        $responseHk1->assertStatus(200);
        $responseHk1->assertSee('HK2425_1');
        // Ở HK2425_1, GV00000004 không có đề tài nào -> không có đề tài chờ duyệt
        $responseHk1->assertSee('GV00000004');
    }

    /**
     * CASE 2: Cố tình truy cập giảng viên/đề tài của bộ môn khác bằng URL -> bị chặn (403/404).
     */
    public function test_case_2_chan_truy_cap_giang_vien_va_de_tai_bo_mon_khac_403()
    {
        // TBM CNPM truy cập danh sách đề tài của GV00000005 (thuộc BM_ATTT) -> 403
        $responseGv = $this->actingAs($this->tbmCnpm)->get('/truong-bo-mon/phe-duyet-de-tai/giang-vien/GV00000005?hocKy=HK2627_2');
        $responseGv->assertStatus(403);

        // TBM CNPM truy cập chi tiết đề tài DT_ATTT_CHO_01 (thuộc BM_ATTT) -> 403
        $responseDeTai = $this->actingAs($this->tbmCnpm)->get('/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_ATTT_CHO_01');
        $responseDeTai->assertStatus(403);

        // TBM CNPM truy cập đề tài không tồn tại -> 404
        $responseNotFound = $this->actingAs($this->tbmCnpm)->get('/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_KHONG_TON_TAI_999');
        $responseNotFound->assertStatus(404);
    }

    /**
     * CASE 3A: Phê duyệt đề tài thành công và ghi nhận lịch sử xử lý.
     */
    public function test_case_3a_phe_duyet_de_tai_thanh_cong_va_ghi_lich_su()
    {
        // Chuẩn bị đề tài DT_CNPM_CHO_01 ở trạng thái 'Chờ duyệt cấp Bộ môn'
        DeTai::where('MaDeTai', 'DT_CNPM_CHO_01')->update([
            'TrangThai' => 'Chờ duyệt cấp Bộ môn',
            'NgayDuyetBM' => null,
            'NguoiDuyetBM' => null,
            'LyDoTuChoi' => null,
        ]);

        $postData = [
            'GhiChu' => 'Đồng ý thông qua đề tài khóa luận này.',
        ];

        $response = $this->actingAs($this->tbmCnpm)
            ->post('/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_CNPM_CHO_01/duyet', $postData);

        $response->assertRedirect('/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_CNPM_CHO_01');
        $response->assertSessionHas('success');

        // Kiểm tra database DeTai đã chuyển sang 'Chờ duyệt cấp Khoa'
        $updatedDeTai = DeTai::where('MaDeTai', 'DT_CNPM_CHO_01')->first();
        $this->assertEquals('Chờ duyệt cấp Khoa', $updatedDeTai->TrangThai);
        $this->assertEquals('GV00000001', $updatedDeTai->NguoiDuyetBM);
        $this->assertNotNull($updatedDeTai->NgayDuyetBM);

        // Kiểm tra database ChiTietDuyetDeTai có bản ghi lịch sử tương ứng
        $history = ChiTietDuyetDeTai::where('MaDeTai', 'DT_CNPM_CHO_01')
            ->where('HanhDong', 'Phê duyệt')
            ->latest('created_at')
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals('Chờ duyệt cấp Khoa', $history->TrangThai);
        $this->assertStringContainsString('Đồng ý thông qua', $history->LyDo);
    }

    /**
     * CASE 3B: Yêu cầu chỉnh sửa: Thiếu lý do -> validation error, Đủ lý do -> thành công + ghi lịch sử.
     */
    public function test_case_3b_yeu_cau_chinh_sua_validation_va_thanh_cong()
    {
        // Chuẩn bị đề tài DT_CNPM_CHO_01
        DeTai::where('MaDeTai', 'DT_CNPM_CHO_01')->update([
            'TrangThai' => 'Chờ duyệt cấp Bộ môn',
            'LyDoTuChoi' => null,
        ]);

        // 1. Bỏ trống nội dung yêu cầu sửa -> Báo lỗi validation
        $responseEmpty = $this->actingAs($this->tbmCnpm)
            ->post('/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_CNPM_CHO_01/yeu-cau-chinh-sua', [
                'YeuCauSua' => '',
            ]);
        $responseEmpty->assertSessionHasErrors(['YeuCauSua']);

        // 2. Nội dung quá ngắn (< 5 ký tự) -> Báo lỗi validation
        $responseShort = $this->actingAs($this->tbmCnpm)
            ->post('/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_CNPM_CHO_01/yeu-cau-chinh-sua', [
                'YeuCauSua' => 'Sửa',
            ]);
        $responseShort->assertSessionHasErrors(['YeuCauSua']);

        // 3. Gửi nội dung hợp lệ -> Thành công
        $noiDungGopY = 'Cần bổ sung chi tiết sơ đồ kiến trúc hệ thống và công nghệ WebSocket.';
        $responseValid = $this->actingAs($this->tbmCnpm)
            ->post('/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_CNPM_CHO_01/yeu-cau-chinh-sua', [
                'YeuCauSua' => $noiDungGopY,
            ]);

        $responseValid->assertRedirect('/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_CNPM_CHO_01');
        $responseValid->assertSessionHas('success');

        // Kiểm tra database DeTai
        $updatedDeTai = DeTai::where('MaDeTai', 'DT_CNPM_CHO_01')->first();
        $this->assertEquals('Yêu cầu chỉnh sửa', $updatedDeTai->TrangThai);
        $this->assertEquals($noiDungGopY, $updatedDeTai->LyDoTuChoi);

        // Kiểm tra lịch sử ChiTietDuyetDeTai
        $history = ChiTietDuyetDeTai::where('MaDeTai', 'DT_CNPM_CHO_01')
            ->where('HanhDong', 'Yêu cầu chỉnh sửa')
            ->latest('created_at')
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals($noiDungGopY, $history->LyDo);
    }

    /**
     * CASE 3C: Từ chối: Thiếu lý do -> validation error, Đủ lý do -> thành công + ghi lịch sử.
     */
    public function test_case_3c_tu_choi_validation_va_thanh_cong()
    {
        // Chuẩn bị đề tài DT_CNPM_CHO_01
        DeTai::where('MaDeTai', 'DT_CNPM_CHO_01')->update([
            'TrangThai' => 'Chờ duyệt cấp Bộ môn',
            'LyDoTuChoi' => null,
        ]);

        // 1. Bỏ trống lý do từ chối -> Báo lỗi validation
        $responseEmpty = $this->actingAs($this->tbmCnpm)
            ->post('/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_CNPM_CHO_01/tu-choi', [
                'LyDoTuChoi' => '',
            ]);
        $responseEmpty->assertSessionHasErrors(['LyDoTuChoi']);

        // 2. Lý do quá ngắn (< 5 ký tự) -> Báo lỗi validation
        $responseShort = $this->actingAs($this->tbmCnpm)
            ->post('/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_CNPM_CHO_01/tu-choi', [
                'LyDoTuChoi' => 'Hủy',
            ]);
        $responseShort->assertSessionHasErrors(['LyDoTuChoi']);

        // 3. Gửi lý do hợp lệ -> Thành công
        $lyDo = 'Đề tài không đáp ứng chuẩn đầu ra của chương trình đào tạo chuyên ngành.';
        $responseValid = $this->actingAs($this->tbmCnpm)
            ->post('/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_CNPM_CHO_01/tu-choi', [
                'LyDoTuChoi' => $lyDo,
            ]);

        $responseValid->assertRedirect('/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_CNPM_CHO_01');
        $responseValid->assertSessionHas('success');

        // Kiểm tra database DeTai
        $updatedDeTai = DeTai::where('MaDeTai', 'DT_CNPM_CHO_01')->first();
        $this->assertEquals('Từ chối', $updatedDeTai->TrangThai);
        $this->assertEquals($lyDo, $updatedDeTai->LyDoTuChoi);

        // Kiểm tra lịch sử ChiTietDuyetDeTai
        $history = ChiTietDuyetDeTai::where('MaDeTai', 'DT_CNPM_CHO_01')
            ->where('HanhDong', 'Từ chối')
            ->latest('created_at')
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals($lyDo, $history->LyDo);
    }

    /**
     * CASE 4: Mở 2 tab cùng 1 đề tài (Race Condition):
     * Tab 1 đã duyệt/từ chối -> Tab 2 bấm thao tác tiếp -> Server từ chối, không lỗi dữ liệu.
     */
    public function test_case_4_mo_hai_tab_thao_tac_trung_lap_bi_chan_server_side()
    {
        // Giả lập đề tài đã được Tab 1 duyệt xong trước đó (trạng thái là 'Chờ duyệt cấp Khoa')
        DeTai::where('MaDeTai', 'DT_CNPM_CHO_02')->update([
            'TrangThai' => 'Chờ duyệt cấp Khoa',
            'NgayDuyetBM' => now(),
            'NguoiDuyetBM' => 'GV00000001',
        ]);

        // Tab 2 cố gắng gửi request Duyệt lại
        $responseTab2Duyet = $this->actingAs($this->tbmCnpm)
            ->post('/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_CNPM_CHO_02/duyet');
        $responseTab2Duyet->assertSessionHasErrors();

        // Tab 2 cố gắng gửi request Từ chối
        $responseTab2TuChoi = $this->actingAs($this->tbmCnpm)
            ->post('/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_CNPM_CHO_02/tu-choi', [
                'LyDoTuChoi' => 'Lý do gửi từ tab cũ.',
            ]);
        $responseTab2TuChoi->assertSessionHasErrors();

        // Trạng thái đề tài vẫn giữ nguyên vẹn
        $detai = DeTai::where('MaDeTai', 'DT_CNPM_CHO_02')->first();
        $this->assertEquals('Chờ duyệt cấp Khoa', $detai->TrangThai);
    }

    /**
     * CASE 5: Giảng viên chưa có đề tài -> hiển thị đúng (0 đề tài, không crash).
     */
    public function test_case_5_giang_vien_chua_co_de_tai_hien_thi_dung()
    {
        // GV00000002 thuộc BM_CNPM nhưng chưa có đề tài nào trong HK2627_2
        $responseIndex = $this->actingAs($this->tbmCnpm)
            ->get('/truong-bo-mon/phe-duyet-de-tai?hocKy=HK2627_2');
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('GV00000002');

        // Trang danh sách đề tài của GV00000002
        $responseGv = $this->actingAs($this->tbmCnpm)
            ->get('/truong-bo-mon/phe-duyet-de-tai/giang-vien/GV00000002?hocKy=HK2627_2');
        $responseGv->assertStatus(200);
        $responseGv->assertSee('Giảng viên chưa đề xuất đề tài nào trong học kỳ này');
    }

    /**
     * CASE BỔ SUNG: Kiểm tra render chi tiết đề tài và hiển thị lịch sử xử lý (Timeline).
     */
    public function test_case_timeline_and_view_render()
    {
        // DT_CNPM_SUA_01 có lịch sử Yêu cầu chỉnh sửa
        $responseShow = $this->actingAs($this->tbmCnpm)
            ->get('/truong-bo-mon/phe-duyet-de-tai/de-tai/DT_CNPM_SUA_01');

        $responseShow->assertStatus(200);
        $responseShow->assertSee('Lịch Sử Xử Lý');
        $responseShow->assertSee('Cần làm rõ phương pháp thu thập dữ liệu');
        $responseShow->assertSee('Yêu cầu chỉnh sửa');
    }

    /**
     * P2 TEST 1: Kiểm tra hiển thị đủ 5 thẻ thống kê và các cột bảng chuẩn Admin (Mã GV, Tên, Số lượng đề tài, Chỉ tiêu, Tình trạng phê duyệt, Thao tác).
     */
    public function test_p2_render_5_the_thong_ke_va_cac_cot_bang_chuan_admin()
    {
        $response = $this->actingAs($this->tbmCnpm)
            ->get('/truong-bo-mon/phe-duyet-de-tai?hocKy=HK2627_2');

        $response->assertStatus(200);

        // 5 Thẻ thống kê chuẩn P2
        $response->assertSee('Tổng số đề tài');
        $response->assertSee('Số đề tài chờ duyệt');
        $response->assertSee('Số đề tài đã duyệt');
        $response->assertSee('Yêu cầu chỉnh sửa');
        $response->assertSee('Số đề tài bị từ chối');

        // Các cột chuẩn trên bảng
        $response->assertSee('Mã GV');
        $response->assertSee('Họ và tên giảng viên');
        $response->assertSee('Số lượng đề tài');
        $response->assertSee('Chỉ tiêu (Đã dùng/Tổng)');
        $response->assertSee('Tình trạng phê duyệt');
        $response->assertSee('Thao tác');

        // Nút Xem chi tiết và Duyệt
        $response->assertSee('Xem chi tiết');
    }

    /**
     * P2 TEST 2: Tìm kiếm theo tên đề tài -> trả về giảng viên đề xuất đề tài đó.
     */
    public function test_p2_tim_kiem_theo_ten_de_tai()
    {
        // Lấy 1 đề tài trong HK2627_2 của GV thuộc BM_CNPM
        $detai = DeTai::where('MaHocKy', 'HK2627_2')
            ->whereHas('giangVien', fn($q) => $q->where('MaBoMon', 'BM_CNPM'))
            ->first();

        $this->assertNotNull($detai);

        // Tìm kiếm theo một phần tên đề tài
        $keyword = mb_substr($detai->TenDeTai, 0, 10);
        $response = $this->actingAs($this->tbmCnpm)
            ->get('/truong-bo-mon/phe-duyet-de-tai?hocKy=HK2627_2&search=' . urlencode($keyword));

        $response->assertStatus(200);
        // Phải nhìn thấy GV đề xuất đề tài này
        $response->assertSee($detai->MaGV);
    }

    /**
     * P2 TEST 3: Lọc theo trạng thái đề tài và theo tình trạng chỉ tiêu hướng dẫn.
     */
    public function test_p2_loc_theo_trang_thai_va_tinh_trang_chi_tieu()
    {
        $origStatus = DeTai::where('MaDeTai', 'DT_CNPM_CHO_02')->value('TrangThai');
        DeTai::where('MaDeTai', 'DT_CNPM_CHO_02')->update(['TrangThai' => 'Chờ duyệt cấp Bộ môn']);

        try {
            // 1. Lọc theo trạng thái 'cho_duyet'
            $responsePending = $this->actingAs($this->tbmCnpm)
                ->get('/truong-bo-mon/phe-duyet-de-tai?hocKy=HK2627_2&trang_thai=cho_duyet');

            $responsePending->assertStatus(200);
            // GV00000004 có đề tài chờ duyệt -> phải thấy
            $responsePending->assertSee('GV00000004');

            // 2. Lọc theo tình trạng chỉ tiêu 'con_chi_tieu'
            $responseQuota = $this->actingAs($this->tbmCnpm)
                ->get('/truong-bo-mon/phe-duyet-de-tai?hocKy=HK2627_2&tinh_trang_chi_tieu=con_chi_tieu');

            $responseQuota->assertStatus(200);
            $responseQuota->assertSee('Còn');
        } finally {
            DeTai::where('MaDeTai', 'DT_CNPM_CHO_02')->update(['TrangThai' => $origStatus]);
        }
    }
}
