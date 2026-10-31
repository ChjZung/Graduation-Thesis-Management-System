<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecretaryOutlineDisplayTest extends TestCase
{
    /**
     * Test kiểm tra giao diện thiết lập phiếu chấm có đầy đủ tab hiển thị Đề Cương và nút Nhập từ đề cương
     */
    public function test_cham_diem_view_contains_outline_display_and_import_button(): void
    {
        $viewPath = resource_path('views/giangvien/hoidong/_cham-diem.blade.php');
        $this->assertFileExists($viewPath);

        $content = file_get_contents($viewPath);

        // 1. Kiểm tra có nút Nhập từ đề cương và nút Xem đề cương
        $this->assertStringContainsString('Nhập từ đề cương', $content);
        $this->assertStringContainsString('btnImportDeCuong', $content);
        $this->assertStringContainsString('importFromDeCuong()', $content);
        $this->assertStringContainsString('btnToggleXemDeCuong', $content);
        $this->assertStringContainsString('toggleDeCuongTab()', $content);

        // 2. Kiểm tra có 2 tab: Đề Cương Đề Tài và Xem Trước Khung Chấm
        $this->assertStringContainsString('tab-decuong-btn', $content);
        $this->assertStringContainsString('pane-decuong', $content);
        $this->assertStringContainsString('Đề Cương Đề Tài', $content);
        $this->assertStringContainsString('tab-preview-btn', $content);
        $this->assertStringContainsString('pane-preview', $content);
        $this->assertStringContainsString('Xem Trước Khung Chấm', $content);

        // 3. Kiểm tra kho dữ liệu đề cương ẩn đầy đủ MoTa, YeuCau, NoiDung
        $this->assertStringContainsString('id="outlineDataStore"', $content);
        $this->assertStringContainsString('data-mo-ta=', $content);
        $this->assertStringContainsString('data-noi-dung=', $content);
        $this->assertStringContainsString('data-yeu-cau=', $content);

        // 4. Kiểm tra các chức năng hỗ trợ Thư ký coi và chèn/nạp thông tin
        $this->assertStringContainsString('applyOutlineAllToTextarea', $content);
        $this->assertStringContainsString('insertSnippetToTextarea', $content);
        $this->assertStringContainsString('showOutlineTab', $content);
        $this->assertStringContainsString('quickPreviewOutline', $content);

        // 5. Kiểm tra các khối nội dung hiển thị đề cương cho thư ký coi
        $this->assertStringContainsString('Mô tả &amp; Phân hệ chức năng', $content);
        $this->assertStringContainsString('Yêu cầu bài toán &amp; Kỹ thuật', $content);
        $this->assertStringContainsString('outlineNoticeBanner', $content);
        $this->assertStringContainsString('textareaChucNang', $content);
    }
}
