<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        /** @var \App\Models\TaiKhoan $user */
        $user = Auth::user();
        $user->loadMissing('vaiTro');
        $roleName = $user->vaiTro->TenVaiTro ?? '';

        // Chuẩn hóa danh sách role được phép
        $checkRoles = $roles;

        // "Giáo vụ" tương thích với "Admin"
        if (in_array('Admin', $roles) || in_array('Giáo vụ', $roles)) {
            $checkRoles[] = 'Admin';
            $checkRoles[] = 'Giáo vụ';
        }

        // Trưởng bộ môn và Trưởng khoa đều xuất thân từ Giảng viên nên có thể truy cập các tính năng Giảng viên (hướng dẫn, phản biện, chấm điểm)
        if (in_array('Giảng viên', $roles)) {
            $checkRoles[] = 'Trưởng bộ môn';
            $checkRoles[] = 'Trưởng khoa';
        }

        if (!in_array($roleName, $checkRoles)) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
