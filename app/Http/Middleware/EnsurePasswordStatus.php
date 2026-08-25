<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePasswordStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        /** @var \App\Models\TaiKhoan $user */
        $user = Auth::user();

        // 1. TÀI KHOẢN INITIAL: Bắt buộc thiết lập mật khẩu cá nhân ở lần đầu đăng nhập
        if ($user->isPasswordInitial()) {
            $allowedRoutes = ['password.setup', 'password.setup.post', 'logout'];
            $currentRoute = $request->route() ? $request->route()->getName() : null;

            if (!in_array($currentRoute, $allowedRoutes) && !$request->is('setup-password*') && !$request->is('logout')) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success'  => false,
                        'message'  => 'Tài khoản cần thiết lập mật khẩu cá nhân trước khi tiếp tục.',
                        'redirect' => route('password.setup'),
                    ], 403);
                }
                return redirect()->route('password.setup');
            }

            return $next($request);
        }

        // 2. TÀI KHOẢN EXPIRED: Bắt buộc cập nhật mật khẩu khi hết hạn
        if ($user->isPasswordExpired()) {
            $allowedRoutes = ['password.change', 'password.change.post', 'logout'];
            $currentRoute = $request->route() ? $request->route()->getName() : null;

            if (!in_array($currentRoute, $allowedRoutes) && !$request->is('password/change*') && !$request->is('logout')) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success'  => false,
                        'message'  => 'Mật khẩu đã hết hạn, vui lòng cập nhật mật khẩu mới.',
                        'redirect' => route('password.change'),
                    ], 403);
                }
                return redirect()->route('password.change')
                    ->with('warning', 'Mật khẩu của bạn đã hết hạn, vui lòng đổi mật khẩu mới để tiếp tục.');
            }

            return $next($request);
        }

        // 3. TÀI KHOẢN ACTIVE: Nếu cố vào trang setup-password -> Chuyển về Dashboard phù hợp
        if ($request->routeIs('password.setup') || $request->is('setup-password')) {
            return $this->redirectToDashboard($user);
        }

        return $next($request);
    }

    protected function redirectToDashboard($user)
    {
        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';

        if (in_array($role, ['Admin', 'Giáo vụ'])) return redirect()->route('admin.dashboard');
        if ($role === 'Giảng viên') return redirect()->route('giangvien.dashboard');
        if ($role === 'Sinh viên') return redirect()->route('sinhvien.dashboard');

        return redirect('/');
    }
}
