<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\GiangVien;
use App\Models\GiaoVu;
use App\Models\SinhVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Hiển thị giao diện nhập email nhận liên kết đặt lại mật khẩu
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Xử lý gửi email chứa liên kết đặt lại mật khẩu
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
        ]);

        $email = trim(strtolower($request->email));

        // Giới hạn tần suất gửi yêu cầu (Rate Limiting) để tránh spam/brute-force
        $throttleKey = 'password-reset:' . $email . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->back()->withInput()->withErrors([
                'email' => "Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau {$seconds} giây."
            ]);
        }
        RateLimiter::hit($throttleKey, 600); // Lưu đếm trong 10 phút

        $neutralMessage = 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi hướng dẫn đặt lại mật khẩu.';

        // Tra cứu tài khoản theo email chính (hỗ trợ không phân biệt hoa/thường & loại bỏ khoảng trắng)
        $user = null;
        $recipientName = 'Người dùng';

        $sv = SinhVien::whereRaw('LOWER(TRIM(Email)) = ?', [$email])->first();
        if ($sv && $sv->taiKhoan) {
            $user = $sv->taiKhoan;
            $recipientName = $sv->HoTen ?? 'Sinh viên';
        } else {
            $gv = GiangVien::whereRaw('LOWER(TRIM(Email)) = ?', [$email])->first();
            if ($gv && $gv->taiKhoan) {
                $user = $gv->taiKhoan;
                $recipientName = $gv->HoTen ?? 'Giảng viên';
            } else {
                $gvu = GiaoVu::whereRaw('LOWER(TRIM(Email)) = ?', [$email])->first();
                if ($gvu && $gvu->taiKhoan) {
                    $user = $gvu->taiKhoan;
                    $recipientName = $gvu->HoTen ?? 'Giáo vụ';
                }
            }
        }

        // Nếu tìm thấy tài khoản hợp lệ, tạo token an toàn và gửi email
        if ($user) {
            $rawToken = Str::random(64);
            $hashedToken = hash('sha256', $rawToken);

            // Lưu token đã băm vào bảng password_reset_tokens
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            DB::table('password_reset_tokens')->insert([
                'email'      => $email,
                'token'      => $hashedToken,
                'created_at' => now(),
            ]);

            $baseUrl = $request->schemeAndHttpHost() . $request->getBasePath();
            $relativeRoute = route('password.reset', ['token' => $rawToken, 'email' => $email], false);
            $resetUrl = rtrim($baseUrl, '/') . $relativeRoute;

            try {
                Mail::to($email)->send(new ResetPasswordMail($resetUrl, $recipientName));
            } catch (\Throwable $e) {
                // Ghi log chi tiết ngoại lệ để chẩn đoán SMTP, không ghi mật khẩu/token/nội dung mail
                Log::error('Lỗi khi gửi email đặt lại mật khẩu', [
                    'exception_type' => get_class($e),
                    'error_message'  => $e->getMessage(),
                    'error_code'     => $e->getCode(),
                ]);
            }
        }

        // Dù email có tồn tại hay không, luôn phản hồi thông báo trung tính giống nhau
        return redirect()->back()->with('status', $neutralMessage);
    }
}
