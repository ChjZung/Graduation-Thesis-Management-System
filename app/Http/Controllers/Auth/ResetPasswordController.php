<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Models\GiaoVu;
use App\Models\SinhVien;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /**
     * Hiển thị giao diện nhập mật khẩu mới
     */
    public function showResetForm(Request $request, string $token = null)
    {
        return view('auth.passwords.reset')->with([
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Xử lý đặt lại mật khẩu mới cho người dùng
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/', // Ít nhất 1 chữ cái viết hoa
                'regex:/[a-z]/', // Ít nhất 1 chữ cái viết thường
                'regex:/[0-9]/', // Ít nhất 1 chữ số
                'confirmed',
            ],
        ], [
            'token.required'     => 'Mã token đặt lại mật khẩu không hợp lệ.',
            'email.required'     => 'Vui lòng nhập email.',
            'email.email'        => 'Email không đúng định dạng.',
            'password.required'  => 'Vui lòng nhập mật khẩu mới.',
            'password.min'       => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.regex'     => 'Mật khẩu mới phải chứa ít nhất 1 chữ cái viết hoa, 1 chữ cái viết thường và 1 chữ số.',
            'password.confirmed' => 'Mật khẩu xác nhận không trùng khớp với mật khẩu mới.',
        ]);

        $email = trim(strtolower($request->email));
        $hashedToken = hash('sha256', $request->token);

        // 1. Kiểm tra sự tồn tại của token trong bảng password_reset_tokens
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record || !hash_equals($record->token, $hashedToken)) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã được sử dụng.']);
        }

        // 2. Kiểm tra thời gian hết hạn (Token hết hạn sau 15 phút)
        $createdAt = Carbon::parse($record->created_at);
        if (now()->diffInMinutes($createdAt) > 15) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Liên kết đặt lại mật khẩu đã hết hạn (chỉ có hiệu lực trong 15 phút). Vui lòng gửi yêu cầu mới.']);
        }

        // 3. Tra cứu tài khoản liên kết qua email chính
        $user = null;
        $sv = SinhVien::whereRaw('LOWER(TRIM(Email)) = ?', [$email])->first();
        if ($sv && $sv->taiKhoan) {
            $user = $sv->taiKhoan;
        } else {
            $gv = GiangVien::whereRaw('LOWER(TRIM(Email)) = ?', [$email])->first();
            if ($gv && $gv->taiKhoan) {
                $user = $gv->taiKhoan;
            } else {
                $gvu = GiaoVu::whereRaw('LOWER(TRIM(Email)) = ?', [$email])->first();
                if ($gvu && $gvu->taiKhoan) {
                    $user = $gvu->taiKhoan;
                }
            }
        }

        if (!$user) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Không tìm thấy tài khoản hệ thống tương ứng với email này.']);
        }

        // 4. Đặt lại mật khẩu mới (Áp dụng đúng băm mật khẩu và cập nhật trạng thái mật khẩu)
        // LƯU Ý: KHÔNG tự động mở khóa tài khoản. Giữ nguyên TrangThai hiện tại.
        $user->MatKhau = Hash::make($request->password);
        $user->NgayDoiMatKhau = now();
        $user->SoLanDangNhapSai = 0; // Xóa số lần đăng nhập sai trước đó

        if ($user->BatBuocDoiMatKhau) {
            $user->BatBuocDoiMatKhau = false;
        }
        if ($user->TrangThaiMatKhau === 'INITIAL' || $user->TrangThaiMatKhau === 'EXPIRED') {
            $user->TrangThaiMatKhau = 'ACTIVE';
        }

        $user->save();

        // 5. Vô hiệu hóa (xóa) token để chỉ sử dụng được 1 lần
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // 6. Chuyển hướng về trang đăng nhập kèm thông báo thành công
        return redirect()->route('login')->with('warning', 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập lại bằng mật khẩu mới.');
    }
}
