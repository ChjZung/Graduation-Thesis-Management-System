<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecretaryOutlineDisplayTest extends TestCase
{
    /**
     * Test kiểm tra giao diện thiết lập phiếu chấm có đầy đủ tab hiển thị Đề Cương đối chiếu và nút mẫu chuẩn
     */
    public function test_cham_diem_view_contains_outline_display_and_reference_panel(): void
    {
        $viewPath = resource_path('views/giangvien/hoidong/_cham-diem.blade.php');
        $this->assertFileExists($viewPath);

        $content = file_get_contents($viewPath);

        // 1. Kiểm tra có 2 tab: Đề Cương Đề Tài (đối chiếu) và Xem Trước Khung Chấm
        $this->assertStringContainsString('tab-decuong-btn', $content);
        $this->assertStringContainsString('pane-decuong', $content);
        $this->assertStringContainsString('Đề Cương Đề Tài', $content);
        $this->assertStringContainsString('tab-preview-btn', $content);
        $this->assertStringContainsString('pane-preview', $content);
        $this->assertStringContainsString('Xem Trước Khung Chấm', $content);

        // 2. Nút "Nạp vào ô soạn thảo" đã được loại bỏ hoàn toàn khỏi giao diện đối chiếu đề cương
        $this->assertStringNotContainsString('Nạp vào ô soạn thảo', $content);

        // 3. Kiểm tra các nút áp dụng mẫu chuẩn (Cử nhân 3.5đ & Kỹ sư 4.0đ)
        $this->assertStringContainsString('Mẫu Cử nhân (3.5đ)', $content);
        $this->assertStringContainsString('Mẫu Kỹ sư (4.0đ)', $content);
        $this->assertStringContainsString('loadSampleTemplate', $content);

        // 4. Kiểm tra kho dữ liệu đề cương ẩn đầy đủ MoTa, YeuCau, NoiDung
        $this->assertStringContainsString('id="outlineDataStore"', $content);
        $this->assertStringContainsString('data-mo-ta=', $content);
        $this->assertStringContainsString('data-noi-dung=', $content);
        $this->assertStringContainsString('data-yeu-cau=', $content);

        // 5. Kiểm tra các khối nội dung hiển thị đề cương cho thư ký đối chiếu
        $this->assertStringContainsString('Mô tả &amp; Phân hệ chức năng', $content);
        $this->assertStringContainsString('Yêu cầu bài toán &amp; Kỹ thuật', $content);
        $this->assertStringContainsString('outlineNoticeBanner', $content);
        $this->assertStringContainsString('textareaChucNang', $content);
    }
}
