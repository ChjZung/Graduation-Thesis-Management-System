<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PasswordSetupUiTest extends TestCase
{
    use DatabaseTransactions;

    protected $pendingUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pendingUser = TaiKhoan::create([
            'MaTK' => 'TK_PENDING_' . bin2hex(random_bytes(4)),
            'TenDangNhap' => 'pending_' . bin2hex(random_bytes(4)),
            'MatKhau' => bcrypt('Initial123456!'),
            'MaVaiTro' => 'VT03', // Sinh viên
            'TrangThai' => true,
            'TrangThaiMatKhau' => 'PENDING',
            'BatBuocDoiMatKhau' => true,
        ]);
    }

    /**
     * Test setup-password page renders properly with robust password toggle UI structure
     */
    public function test_setup_password_page_renders_with_proper_eye_toggle_elements(): void
    {
        $response = $this->actingAs($this->pendingUser)->get(route('password.setup'));

        $response->assertOk();
        $content = $response->getContent();

        // 1. Title and headers
        $this->assertStringContainsString('Thiết Lập Mật Khẩu Cá Nhân', $content);
        $this->assertStringContainsString('Mật khẩu cá nhân mới', $content);
        $this->assertStringContainsString('Xác nhận lại mật khẩu mới', $content);

        // 2. CSS rules: padding-right: 48px, ::-ms-reveal removal, overflow: visible
        $this->assertStringContainsString('padding-right: 48px !important;', $content);
        $this->assertStringContainsString('::-ms-reveal', $content);
        $this->assertStringContainsString('::-ms-clear', $content);
        $this->assertStringContainsString('overflow: visible', $content);

        // 3. Wrapper structure: input-icon-wrap
        $this->assertStringContainsString('class="input-icon-wrap"', $content);

        // 4. Toggle buttons: type="button", aria-label, aria-pressed, data-target
        $this->assertStringContainsString('data-target="new_password"', $content);
        $this->assertStringContainsString('data-target="new_password_confirmation"', $content);
        $this->assertStringContainsString('aria-label="Hiện mật khẩu"', $content);
        $this->assertStringContainsString('aria-pressed="false"', $content);

        // 5. Inline SVGs for crisp, reliable eye icons
        $this->assertStringContainsString('eye-open', $content);
        $this->assertStringContainsString('eye-closed', $content);
        $this->assertStringContainsString('viewBox="0 0 24 24"', $content);
    }

    /**
     * Test successful password setup flow
     */
    public function test_setup_password_submission_success(): void
    {
        $response = $this->actingAs($this->pendingUser)->post(route('password.setup.post'), [
            'new_password' => 'Huit@2026_SecureKey!',
            'new_password_confirmation' => 'Huit@2026_SecureKey!',
        ]);

        $this->pendingUser->refresh();
        $this->assertSame('ACTIVE', $this->pendingUser->TrangThaiMatKhau);
        $this->assertFalse((bool)$this->pendingUser->BatBuocDoiMatKhau);
        $response->assertRedirect();
    }
}
