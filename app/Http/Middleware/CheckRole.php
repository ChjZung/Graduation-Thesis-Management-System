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

        // Map "Giáo vụ" → "Admin" để tương thích với route cũ role:Admin
        $checkRoles = $roles;
        if (in_array('Admin', $roles)) {
            $checkRoles[] = 'Giáo vụ';
        }

        if (!in_array($roleName, $checkRoles)) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
