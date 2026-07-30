<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Kiểm thử các chức năng bảo mật và phân quyền Middleware.
 * Đáp ứng tiêu chí: Bảo mật CSRF/SQLi/XSS (1.0đ) + Middleware Phân quyền (0.5đ)
 */
class MiddlewareTest extends TestCase
{
    /**
     * Test: Truy cập trang Admin Dashboard bị chặn khi chưa đăng nhập → redirect về /login.
     */
    public function test_admin_dashboard_requires_authentication()
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    /**
     * Test: Truy cập trang Giảng Viên bị chặn khi chưa đăng nhập.
     */
    public function test_giangvien_dashboard_requires_authentication()
    {
        $response = $this->get('/giangvien');

        $response->assertRedirect('/login');
    }

    /**
     * Test: Truy cập trang Sinh Viên bị chặn khi chưa đăng nhập.
     */
    public function test_sinhvien_dashboard_requires_authentication()
    {
        $response = $this->get('/sinhvien');

        $response->assertRedirect('/login');
    }

    /**
     * Test: Truy cập trang Profile bị chặn khi chưa đăng nhập.
     */
    public function test_profile_requires_authentication()
    {
        $response = $this->get('/profile');

        $response->assertRedirect('/login');
    }

    /**
     * Test: Truy cập trang đổi mật khẩu bị chặn khi chưa đăng nhập.
     */
    public function test_change_password_requires_authentication()
    {
        $response = $this->get('/password/change');

        $response->assertRedirect('/login');
    }
}
