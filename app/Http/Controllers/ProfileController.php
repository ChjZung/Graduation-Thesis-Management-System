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

        if ($role === 'Admin') {
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
        if ($role === 'Admin') {
            $layout = 'layouts.admin';
        } elseif ($role === 'Giảng viên') {
            $layout = 'layouts.giangvien';
        } else {
            $layout = 'layouts.sinhvien';
        }

        return view('profile.password', compact('layout'));
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'min:6', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&]).+$/', 'confirmed'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'new_password.regex' => 'Mật khẩu mới phải chứa chữ hoa, chữ thường, số và ký tự đặc biệt (@$!%*#?&).',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.'
        ]);

        $user = Auth::user();

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->MatKhau)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng']);
        }

        // Update password
        TaiKhoan::where('MaTK', $user->MaTK)->update([
            'MatKhau' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }
}
