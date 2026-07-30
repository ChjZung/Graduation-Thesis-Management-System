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
     * Chỉ cho phép đăng nhập nếu tài khoản chưa bị khóa (TrangThai = 1)
     */
    protected function credentials(\Illuminate\Http\Request $request)
    {
        return [
            'TenDangNhap' => $request->get('TenDangNhap'),
            'password' => $request->get('password'),
            'TrangThai' => 1
        ];
    }

    /**
     * Attempt to log the user into the application.
     */
    protected function attemptLogin(\Illuminate\Http\Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->boolean('remember')
        );
    }

    protected function authenticated(\Illuminate\Http\Request $request, $user)
    {
        if (!$user->TrangThai) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['TenDangNhap' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.']);
        }
        \App\Models\AuditLog::log('dang_nhap', 'TaiKhoan', $user->MaTK, ['TenDangNhap' => $user->TenDangNhap]);
    }
}
