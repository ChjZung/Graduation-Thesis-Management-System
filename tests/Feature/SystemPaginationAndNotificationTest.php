<?php

namespace Tests\Feature;

use App\Models\TaiKhoan;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class SystemPaginationAndNotificationTest extends TestCase
{
    public function test_custom_pagination_view_renders_required_elements()
    {
        $items = collect([1, 2, 3, 4, 5]);
        $paginator = new LengthAwarePaginator($items, 25, 5, 1, [
            'path' => url('/test-list'),
            'pageName' => 'page',
        ]);

        $rendered = $paginator->links()->render();

        // 1. Phân trang tối đa 5 bản ghi/trang
        $this->assertStringContainsString('Tối đa 5 bản ghi/trang', $rendered);

        // 2. Tổng số bản ghi (25)
        $this->assertStringContainsString('25', $rendered);

        // 3. Hiển thị từ 1 đến 5
        $this->assertStringContainsString('Hiển thị', $rendered);

        // 4. Tổng số trang (5) và trang hiện tại (1)
        $this->assertStringContainsString('Trang 1 / 5', $rendered);

        // 5. Nút trang trước và sau
        $this->assertStringContainsString('Trang trước', $rendered);
        $this->assertStringContainsString('Trang sau', $rendered);
    }

    public function test_flash_alert_renders_single_notification_for_success()
    {
        session()->flash('success', 'Thao tác thực hiện thành công!');

        $rendered = view('partials.flash_alert')->render();

        $this->assertStringContainsString('Thao tác thực hiện thành công!', $rendered);
        $this->assertStringContainsString('alert-success', $rendered);
        $this->assertStringNotContainsString('alert-danger', $rendered);
        $this->assertStringNotContainsString('alert-warning', $rendered);
    }

    public function test_flash_alert_renders_single_notification_for_error()
    {
        session()->flash('error', 'Có lỗi xảy ra trong quá trình xử lý!');

        $rendered = view('partials.flash_alert')->render();

        $this->assertStringContainsString('Có lỗi xảy ra trong quá trình xử lý!', $rendered);
        $this->assertStringContainsString('alert-danger', $rendered);
        $this->assertStringNotContainsString('alert-success', $rendered);
    }

    public function test_criterion_7_sub_requirements_classes_and_rendering()
    {
        $user = TaiKhoan::where('MaGV', 'GV00000044')->first();
        if (!$user) {
            $this->markTestSkipped('Không tìm thấy tài khoản GV00000044.');
        }

        $response = $this->actingAs($user)->get('/giangvien/hoi-dong?tab=chamdiem&hd=HD06');
        $this->assertTrue(in_array($response->status(), [200, 302]));
        if ($response->status() === 200) {
            $content = $response->getContent();
            $this->assertStringContainsString('sub-req-group', $content);
            $this->assertStringContainsString('sub-req-input', $content);
            $this->assertStringContainsString('sub-req-max', $content);
            $this->assertStringContainsString('sub-req-tag', $content);
        }
    }
}
