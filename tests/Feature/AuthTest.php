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

    /**
     * Test tự động khóa tài khoản khi nhập sai 5 lần liên tiếp.
     */
    public function test_account_locks_after_5_failed_attempts()
    {
        // Tạo tài khoản test
        $user = TaiKhoan::firstOrCreate(
            ['TenDangNhap' => 'test_lock_user'],
            [
                'MaTK' => 'TK_LOCK',
                'MatKhau' => \Illuminate\Support\Facades\Hash::make('Password123!'),
                'MaVaiTro' => 'VT03',
                'TrangThai' => true,
                'SoLanDangNhapSai' => 0,
            ]
        );

        // Thử nhập sai 5 lần
        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', [
                'TenDangNhap' => 'test_lock_user',
                'password' => 'wrong_password',
            ]);
        }

        // Kiểm tra tài khoản đã bị khóa trong CSDL
        $user->refresh();
        $this->assertEquals(0, $user->TrangThai);
        $this->assertGreaterThanOrEqual(5, $user->SoLanDangNhapSai);

        // Thử đăng nhập lần 6 → phải nhận thông báo bị khóa
        $response = $this->post('/login', [
            'TenDangNhap' => 'test_lock_user',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors(['TenDangNhap']);
        $this->assertGuest();
    }
}
