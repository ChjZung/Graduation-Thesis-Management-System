<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\VaiTro;
use App\Models\HoiDong;
use App\Models\ThanhVienHoiDong;
use App\Models\GiangVien;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TruongKhoaHoiDongCardLayoutTest extends TestCase
{
    use DatabaseTransactions;

    protected $userTruongKhoa;

    protected function setUp(): void
    {
        parent::setUp();

        // Lấy tài khoản có vai trò Trưởng khoa (VT05)
        $this->userTruongKhoa = TaiKhoan::where('MaVaiTro', 'VT05')->first();
        if (!$this->userTruongKhoa) {
            $this->userTruongKhoa = TaiKhoan::whereHas('vaiTro', function($q) {
                $q->where('TenVaiTro', 'like', '%Trưởng khoa%');
            })->first();
        }
    }

    public function test_route_thu_hoi_cong_bo_bi_xoa_tra_ve_404_hoac_405()
    {
        if ($this->userTruongKhoa) {
            $response = $this->actingAs($this->userTruongKhoa)
                ->post('/truongkhoa/hoi-dong/HD01/thu-hoi-cong-bo');
        } else {
            $response = $this->post('/truongkhoa/hoi-dong/HD01/thu-hoi-cong-bo');
        }

        // Route không tồn tại trong routes/web.php nên trả về 404
        $this->assertContains($response->status(), [404, 405]);
    }

    public function test_danh_sach_hoi_dong_hien_thi_dang_card_va_khong_con_thu_hoi_cong_bo()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        $response = $this->actingAs($this->userTruongKhoa)->get(route('truongkhoa.hoidong.index'));

        $response->assertStatus(200);

        $hoiDongsInView = $response->viewData('hoiDongs');
        $khoaInView = $response->viewData('khoa');

        $content = $response->getContent();
        // Không còn bảng hay dropdown menu thu hồi
        $this->assertStringNotContainsString('table-responsive', $content);
        $this->assertStringNotContainsString('thu-hoi-cong-bo', $content);
        $this->assertStringNotContainsString('Thu hồi công bố', $content);

        // Kiểm tra style và layout Card HUIT
        $this->assertStringContainsString('huit-card', $content);
        $this->assertStringContainsString('Danh Sách Hội Đồng Thuộc', $content);

        // Kiểm tra tiêu chí lọc và giao diện hiển thị
        if ($hoiDongsInView && $hoiDongsInView->count() > 0) {
            $this->assertStringContainsString('btn-card-icon', $content);
            $this->assertStringContainsString('fa-file-pdf', $content);
            $this->assertStringContainsString('fa-download', $content);
            $this->assertStringContainsString('fa-trash', $content);
        } else {
            // Khi rỗng thì hiển thị empty state
            $this->assertStringContainsString('Lập Hội Đồng Mới', $content);
        }
    }

    public function test_accessor_sorted_thanh_viens_sap_xep_dung_thu_tu_vai_tro()
    {
        $tv1 = new ThanhVienHoiDong(['VaiTro' => 'Ủy viên']);
        $tv2 = new ThanhVienHoiDong(['VaiTro' => 'Chủ tịch']);
        $tv3 = new ThanhVienHoiDong(['VaiTro' => 'Ủy viên thư ký']);
        $tv4 = new ThanhVienHoiDong(['VaiTro' => 'Ủy viên phản biện']);

        $this->assertEquals(1, $tv2->role_priority);
        $this->assertEquals(2, $tv4->role_priority);
        $this->assertEquals(3, $tv3->role_priority);
        $this->assertEquals(4, $tv1->role_priority);

        $this->assertEquals('Chủ tịch', $tv2->short_vai_tro);
        $this->assertEquals('Phản biện', $tv4->short_vai_tro);
        $this->assertEquals('Thư ký', $tv3->short_vai_tro);
        $this->assertEquals('Ủy viên', $tv1->short_vai_tro);

        $this->assertStringContainsString('danger', $tv2->badge_class);
        $this->assertStringContainsString('primary', $tv4->badge_class);
        $this->assertStringContainsString('info', $tv3->badge_class);
        $this->assertStringContainsString('secondary', $tv1->badge_class);
    }

    public function test_card_render_day_du_thanh_phan_va_xu_ly_chuoi_dai()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        // Tạo hội đồng test với dữ liệu dài
        $maHD = 'HD_TEST_' . time();
        $hd = HoiDong::create([
            'MaHoiDong' => $maHD,
            'TenHoiDong' => 'Hội đồng Bảo vệ Khóa Luận Tốt Nghiệp Chuyên Ngành Kỹ Thuật Dữ Liệu Lớn Và Trí Tuệ Nhân Tạo Ứng Dụng Đa Nền Tảng Siêu Dài',
            'MaKhoa' => 'CNTT',
            'MaHocKy' => 'HK2627_1',
            'DiaDiem' => 'Phòng Hội Thảo Tòa Nhà Thí Nghiệm Đa Năng Tầng 12 Khu B Phòng VIP 1205 Cơ Sở Chính HUIT',
            'ThoiGianBatDau' => now()->addDays(2),
            'SoQuyetDinh' => '1234/QĐ-ĐHCT-KCNTT-2026-BAN-HANH-DAC-BIET',
            'TrangThaiCongBo' => 'Đã công bố',
            'TrangThai' => 'Chưa diễn ra',
        ]);

        $response = $this->actingAs($this->userTruongKhoa)->get(route('truongkhoa.hoidong.index'));
        $response->assertStatus(200);

        $content = $response->getContent();
        // Kiểm tra mã hội đồng xuất hiện trên card
        $this->assertStringContainsString($maHD, $content);
        // Nút xem chi tiết
        $this->assertStringContainsString(route('truongkhoa.hoidong.show', $maHD), $content);
        // Nút xem PDF
        $this->assertStringContainsString(route('truongkhoa.hoidong.previewPdf', $maHD), $content);
        // Nút tải PDF
        $this->assertStringContainsString(route('truongkhoa.hoidong.downloadPdf', $maHD), $content);
        // Nút xóa hội đồng
        $this->assertStringContainsString(route('truongkhoa.hoidong.destroy', $maHD), $content);
        $this->assertStringContainsString('btn-card-icon-delete', $content);
        $this->assertStringContainsString('handleConfirmAction', $content);
    }
}
