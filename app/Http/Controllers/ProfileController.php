<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\TaiKhoan;

class ProfileController extends Controller
{
    public function showProfile()
    {
        $user = Auth::user();
        $role = $user->vaiTro->TenVaiTro ?? '';
        $profile = null;

        if (in_array($role, ['Admin', 'Giáo vụ'])) {
            $layout = 'layouts.admin';
        } elseif ($role === 'Giảng viên') {
            $layout = 'layouts.giangvien';
            $profile = \App\Models\GiangVien::with('boMon')->where('MaTK', $user->MaTK)->first();
        } else {
            $layout = 'layouts.sinhvien';
            $profile = \App\Models\SinhVien::with('lop')->where('MaTK', $user->MaTK)->first();
        }

        return view('profile.show', compact('layout', 'profile', 'role', 'user'));
    }

    public function showChangePasswordForm()
    {
        $user = Auth::user();
        $role = $user->vaiTro->TenVaiTro ?? '';
        
        // Determine which layout to use based on role
        if (in_array($role, ['Admin', 'Giáo vụ'])) {
            $layout = 'layouts.admin';
        } elseif ($role === 'Giảng viên') {
            $layout = 'layouts.giangvien';
        } else {
            $layout = 'layouts.sinhvien';
        }

        return view('profile.password', compact('layout', 'user'));
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'confirmed'
            ],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required'     => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min'          => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.regex'        => 'Mật khẩu mới phải bao gồm chữ hoa, chữ thường và chữ số.',
            'new_password.confirmed'    => 'Xác nhận mật khẩu mới không khớp.'
        ]);

        /** @var \App\Models\TaiKhoan $user */
        $user = Auth::user();

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->MatKhau)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không chính xác.']);
        }

        // Cập nhật mật khẩu mới và chuyển trạng thái sang ACTIVE
        TaiKhoan::where('MaTK', $user->MaTK)->update([
            'MatKhau'             => Hash::make($request->new_password),
            'password_status'     => 'ACTIVE',
            'BatBuocDoiMatKhau'   => false,
            'password_changed_at' => now(),
        ]);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }


        return back()->with('success', 'Đổi mật khẩu thành công!');
    }
}
