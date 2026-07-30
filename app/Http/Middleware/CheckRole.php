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
        // Load relationship vaiTro to get the role name
        $user->loadMissing('vaiTro');
        $roleName = $user->vaiTro->TenVaiTro ?? '';

        // If user role is not in the allowed roles array
        if (!in_array($roleName, $roles)) {
            \Illuminate\Support\Facades\Log::error('CheckRole Failed', ['roleName' => $roleName, 'roles' => $roles, 'user' => $user->toArray(), 'vaiTro' => $user->vaiTro]);
            abort(403, 'Bạn không có quyền truy cập trang này. Vui lòng liên hệ Admin. (' . $roleName . ' vs ' . implode(',', $roles) . ')');
        }

        return $next($request);
    }
}
