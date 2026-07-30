<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    /**
     * Test trang login hiển thị thành công.
     */
    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Đăng Nhập');
    }

    /**
     * Test đăng nhập thất bại với sai mật khẩu.
     */
    public function test_login_fails_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'TenDangNhap' => 'admin',
            'password' => 'sai_mat_khau_123',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }
}
