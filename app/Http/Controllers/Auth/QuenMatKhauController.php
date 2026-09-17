<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TaiKhoan;
use App\Models\YeuCauDoiMatKhau;
use Illuminate\Http\Request;

class QuenMatKhauController extends Controller
{
    public function showForm()
    {
        return view('auth.passwords.email');
    }

    public function sendRequest(Request $request)
    {
        $request->validate([
            'TenDangNhap' => 'required|string|max:100',
            'HoTen' => 'required|string|max:150',
            'Role' => 'required|in:Sinh viên,Giảng viên',
            'LyDo' => 'nullable|string|max:500',
        ], [
            'TenDangNhap.required' => 'Vui lòng nhập Mã số (MSSV / Mã GV).',
            'HoTen.required' => 'Vui lòng nhập Họ và tên.',
            'Role.required' => 'Vui lòng chọn vai trò (Sinh viên hoặc Giảng viên).'
        ]);

        $tenDangNhap = trim($request->TenDangNhap);
        $tk = TaiKhoan::where('TenDangNhap', $tenDangNhap)->first();

        if (!$tk) {
            return redirect()->back()->withInput()->withErrors(['TenDangNhap' => 'Mã số này không tồn tại trong hệ thống. Vui lòng kiểm tra lại.']);
        }

        // Kiểm tra xem đã có yêu cầu chờ duyệt chưa
        $existing = YeuCauDoiMatKhau::where('TenDangNhap', $tenDangNhap)
                                    ->where('TrangThai', 'Chờ duyệt')
                                    ->first();

        if ($existing) {
            return redirect()->back()->with('status', 'Bạn đã gửi yêu cầu cấp lại mật khẩu rồi. Vui lòng chờ Giáo vụ Khoa phê duyệt!');
        }

        YeuCauDoiMatKhau::create([
            'TenDangNhap' => $tenDangNhap,
            'HoTen' => trim($request->HoTen),
            'Role' => $request->Role,
            'Email' => $request->Email ? trim($request->Email) : null,
            'LyDo' => $request->LyDo ? trim($request->LyDo) : 'Quên mật khẩu đăng nhập cá nhân',
            'TrangThai' => 'Chờ duyệt',
            'NgayGui' => now(),
        ]);

        return redirect()->route('login')->with('success', "Đã gửi yêu cầu cấp lại mật khẩu cho {$request->Role}: {$request->HoTen} ({$tenDangNhap}) thành công! Vui lòng chờ Giáo vụ Khoa phê duyệt (mật khẩu sẽ được reset về 123456).");
    }
}
