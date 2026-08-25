<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordSetupController extends Controller
{
    /**
     * Hiển thị trang Onboarding "Thiết lập mật khẩu cá nhân" trong layout chính của hệ thống
     */
    public function showSetupForm()
    {
        /** @var \App\Models\TaiKhoan $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Nếu tài khoản đã ACTIVE → đưa thẳng về Dashboard tương ứng
        if ($user->isPasswordActive()) {
            return $this->redirectToDashboard($user);
        }

        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';

        if (in_array($role, ['Admin', 'Giáo vụ'])) {
            $layout = 'layouts.admin';
        } elseif ($role === 'Giảng viên') {
            $layout = 'layouts.giangvien';
        } else {
            $layout = 'layouts.sinhvien';
        }

        return view('auth.passwords.setup', compact('user', 'layout', 'role'));
    }

    /**
     * Xử lý lưu mật khẩu cá nhân mới cho tài khoản lần đầu đăng nhập
     */
    public function setupPassword(Request $request)
    {
        /** @var \App\Models\TaiKhoan $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',      // Ít nhất 1 chữ hoa
                'regex:/[a-z]/',      // Ít nhất 1 chữ thường
                'regex:/[0-9]/',      // Ít nhất 1 chữ số
                'confirmed',
            ],
        ], [
            'new_password.required'  => 'Vui lòng nhập mật khẩu cá nhân mới.',
            'new_password.min'       => 'Mật khẩu phải có độ dài tối thiểu 8 ký tự.',
            'new_password.regex'     => 'Mật khẩu phải bao gồm cả chữ hoa, chữ thường và chữ số.',
            'new_password.confirmed' => 'Mật khẩu xác nhận chưa khớp với mật khẩu mới.',
        ]);

        // Cập nhật trạng thái sang ACTIVE và lưu mật khẩu mới đã hash
        TaiKhoan::where('MaTK', $user->MaTK)->update([
            'MatKhau'             => Hash::make($request->new_password),
            'password_status'     => 'ACTIVE',
            'BatBuocDoiMatKhau'   => false,
            'first_login_at'      => $user->first_login_at ?? now(),
            'password_changed_at' => now(),
            'SoLanDangNhapSai'    => 0,
        ]);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return $this->redirectToDashboard($user)
            ->with('success', 'Thiết lập mật khẩu cá nhân thành công! Chào mừng bạn đến với hệ thống.');
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
