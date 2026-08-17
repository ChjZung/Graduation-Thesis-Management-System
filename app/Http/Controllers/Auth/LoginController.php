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
     * Override attemptLogin để xử lý khóa tài khoản trước
     */
    protected function attemptLogin(\Illuminate\Http\Request $request)
    {
        // Tìm tài khoản theo username
        $taiKhoan = \App\Models\TaiKhoan::where('TenDangNhap', $request->TenDangNhap)->first();

        // Nếu tài khoản bị khóa → từ chối ngay
        if ($taiKhoan && (!$taiKhoan->TrangThai || $taiKhoan->SoLanDangNhapSai >= 5)) {
            return false;
        }

        $attempt = $this->guard()->attempt(
            $this->credentials($request), $request->boolean('remember')
        );

        if (!$attempt && $taiKhoan) {
            // Sai mật khẩu → tăng đếm
            $taiKhoan->increment('SoLanDangNhapSai');
            // Nếu đủ 5 lần → tự động khóa
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

        // Nếu bị bắt buộc đổi mật khẩu → chuyển hướng sang trang đổi MK
        if ($user->BatBuocDoiMatKhau) {
            return redirect()->route('password.change')
                ->with('warning', 'Vui lòng đổi mật khẩu trước khi sử dụng hệ thống.');
        }
    }

    /**
     * Override sendFailedLoginResponse để thêm thông tin khóa TK
     */
    protected function sendFailedLoginResponse(\Illuminate\Http\Request $request)
    {
        $taiKhoan = \App\Models\TaiKhoan::where('TenDangNhap', $request->TenDangNhap)->first();

        if ($taiKhoan && (!$taiKhoan->TrangThai || $taiKhoan->SoLanDangNhapSai >= 5)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $this->username() => ['Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Giáo vụ Khoa để được hỗ trợ khôi phục.'],
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
