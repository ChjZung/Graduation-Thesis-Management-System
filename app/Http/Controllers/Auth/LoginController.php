<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller implements HasMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     */
    protected function redirectTo()
    {
        /** @var \App\Models\TaiKhoan $user */
        $user = auth()->user();
        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';

        if ($role === 'Admin') return route('admin.dashboard');
        if ($role === 'Giáo vụ') return route('admin.dashboard');
        if ($role === 'Giảng viên') return route('giangvien.dashboard');
        if ($role === 'Sinh viên') return route('sinhvien.dashboard');

        return '/';
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public static function middleware(): array
    {
        return [
            new Middleware('guest', except: ["logout"]),
            new Middleware('auth', only: ["logout"])
        ];
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'TenDangNhap';
    }

    /**
     * Chỉ lấy credentials cơ bản, không filter TrangThai ở đây
     * vì cần xử lý thông báo khóa TK riêng biệt
     */
    protected function credentials(\Illuminate\Http\Request $request)
    {
        return [
            'TenDangNhap' => $request->get('TenDangNhap'),
            'password'    => $request->get('password'),
        ];
    }

    /**
     * Đồng bộ kiểm tra giới hạn đăng nhập với dữ liệu CSDL (BR01)
     */
    protected function hasTooManyLoginAttempts(\Illuminate\Http\Request $request)
    {
        $inputUsername = trim($request->TenDangNhap);
        $taiKhoan = \App\Models\TaiKhoan::where('TenDangNhap', $inputUsername)
            ->orWhereRaw('LOWER(TenDangNhap) = ?', [strtolower($inputUsername)])
            ->first();

        if ($taiKhoan && (!$taiKhoan->TrangThai || $taiKhoan->SoLanDangNhapSai >= 5)) {
            return true;
        }

        return false;
    }

    /**
     * Override attemptLogin để xử lý khóa tài khoản trước
     */
    protected function attemptLogin(\Illuminate\Http\Request $request)
    {
        $inputUsername = trim($request->TenDangNhap);
        // Tìm tài khoản theo username (hỗ trợ không phân biệt chữ hoa / thường)
        $taiKhoan = \App\Models\TaiKhoan::where('TenDangNhap', $inputUsername)
            ->orWhereRaw('LOWER(TenDangNhap) = ?', [strtolower($inputUsername)])
            ->first();

        // Nếu tài khoản bị khóa → từ chối ngay
        if ($taiKhoan && (!$taiKhoan->TrangThai || $taiKhoan->SoLanDangNhapSai >= 5)) {
            return false;
        }

        $credentials = [
            'TenDangNhap' => $taiKhoan ? $taiKhoan->TenDangNhap : $inputUsername,
            'password'    => $request->get('password'),
        ];

        $attempt = $this->guard()->attempt(
            $credentials, $request->boolean('remember')
        );

        if (!$attempt && $taiKhoan) {
            // Sai mật khẩu → tăng đếm
            $taiKhoan->increment('SoLanDangNhapSai');
            // Nếu đủ 5 lần → tự động khóa (BR01)
            if ($taiKhoan->SoLanDangNhapSai >= 5) {
                $taiKhoan->update([
                    'TrangThai' => false,
                    'NgayKhoa'  => now(),
                ]);
            }
        }

        return $attempt;
    }

    /**
     * Xử lý sau khi đăng nhập thành công
     */
    protected function authenticated(\Illuminate\Http\Request $request, $user)
    {
        // Kiểm tra tài khoản bị khóa (trường hợp khóa thủ công)
        if (!$user->TrangThai) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['TenDangNhap' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Giáo vụ Khoa để được hỗ trợ khôi phục.']);
        }

        // Reset đếm sai mật khẩu + ghi thời gian đăng nhập
        $user->update([
            'SoLanDangNhapSai' => 0,
            'LanDangNhapCuoi'  => now(),
        ]);

        // Nếu là lần đầu đăng nhập (INITIAL) → Chuyển hướng sang trang Thiết lập mật khẩu cá nhân (BR02)
        if ($user->isPasswordInitial()) {
            return redirect()->route('password.setup');
        }

        // Nếu mật khẩu hết hạn (EXPIRED) → Chuyển hướng sang trang Đổi mật khẩu
        if ($user->isPasswordExpired()) {
            return redirect()->route('password.change')
                ->with('warning', 'Mật khẩu của bạn đã hết hạn, vui lòng đổi mật khẩu mới để tiếp tục.');
        }

        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';

        if (in_array($role, ['Admin', 'Giáo vụ'])) {
            return redirect()->route('admin.dashboard');
        } elseif ($role === 'Giảng viên') {
            return redirect()->route('giangvien.dashboard');
        } elseif ($role === 'Sinh viên') {
            return redirect()->route('sinhvien.dashboard');
        }

        return redirect()->route('admin.dashboard');
    }


    /**
     * Override sendFailedLoginResponse để thêm thông tin khóa TK
     */
    protected function sendFailedLoginResponse(\Illuminate\Http\Request $request)
    {
        $inputUsername = trim($request->TenDangNhap);
        $taiKhoan = \App\Models\TaiKhoan::where('TenDangNhap', $inputUsername)
            ->orWhereRaw('LOWER(TenDangNhap) = ?', [strtolower($inputUsername)])
            ->first();

        if ($taiKhoan && (!$taiKhoan->TrangThai || $taiKhoan->SoLanDangNhapSai >= 5)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $this->username() => ['Tài khoản của bạn đã bị khóa do nhập sai mật khẩu quá 5 lần liên tiếp. Vui lòng liên hệ Giáo vụ Khoa để mở khóa.'],
            ]);
        }

        $soLanCon = $taiKhoan ? max(0, 5 - $taiKhoan->SoLanDangNhapSai) : null;
        $msg = __('auth.failed');
        if ($soLanCon !== null && $soLanCon > 0) {
            $msg .= " (Còn {$soLanCon} lần thử trước khi tài khoản bị khóa)";
        }

        throw \Illuminate\Validation\ValidationException::withMessages([
            $this->username() => [$msg],
        ]);
    }
}
