<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\TaiKhoan;
use App\Models\GiaoVu;
use App\Models\GiangVien;
use App\Models\SinhVien;
use App\Models\DeTai;

class ProfileController extends Controller
{
    /**
     * Hiển thị thông tin hồ sơ cá nhân và trạng thái tài khoản
     */
    public function showProfile()
    {
        /** @var \App\Models\TaiKhoan $user */
        $user = Auth::user();
        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';
        $profile = null;
        $extraData = [];

        if (in_array($role, ['Admin', 'Giáo vụ'])) {
            $layout = 'layouts.admin';
            $profile = GiaoVu::with('khoa')->where('MaTK', $user->MaTK)->first();

            // Fallback nếu tài khoản admin chưa gán MaTK trong bảng GiaoVu
            if (!$profile) {
                $profile = GiaoVu::with('khoa')->first();
            }

            // Thống kê tổng quan cho Giáo vụ
            $extraData['totalDeTai'] = DeTai::count();
            $extraData['totalGiangVien'] = GiangVien::count();
            $extraData['totalSinhVien'] = SinhVien::count();
        } elseif ($role === 'Giảng viên') {
            $layout = 'layouts.giangvien';
            $profile = GiangVien::with(['boMon.khoa', 'deTais'])->where('MaTK', $user->MaTK)->first();

            if ($profile) {
                $extraData['deTaiCount'] = $profile->deTais->count();
                $extraData['deTaiDaDuyet'] = $profile->deTais->where('TrangThai', 'Đã duyệt')->count();
                $extraData['deTaiDaDangKy'] = $profile->deTais->where('TrangThai', 'Đã đăng ký')->count();
            }
        } else {
            $layout = 'layouts.sinhvien';
            $profile = SinhVien::with([
                'lop',
                'nganh',
                'khoa',
                'nhoms.deTai.giangVien',
                'nhoms.thanhViens.sinhVien'
            ])->where('MaTK', $user->MaTK)->first();

            if ($profile) {
                $extraData['currentNhom'] = $profile->nhoms()->with(['deTai.giangVien', 'thanhViens.sinhVien'])->latest()->first();
            }
        }

        return view('profile.show', compact('layout', 'profile', 'role', 'user', 'extraData'));
    }

    /**
     * Cập nhật thông tin liên hệ hồ sơ cá nhân
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\TaiKhoan $user */
        $user = Auth::user();
        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';

        if (in_array($role, ['Admin', 'Giáo vụ'])) {
            $profile = GiaoVu::where('MaTK', $user->MaTK)->first();
            if (!$profile) {
                $profile = GiaoVu::first();
            }
            if (!$profile) {
                return back()->withErrors(['general' => 'Không tìm thấy thông tin cán bộ giáo vụ để cập nhật.']);
            }

            $validated = $request->validate([
                'HoTen'       => 'required|string|max:255',
                'Email'       => 'required|email|max:255|unique:GiaoVu,Email,' . $profile->MaGVu . ',MaGVu',
                'SoDienThoai' => ['nullable', 'regex:/^(0[3|5|7|8|9])[0-9]{8}$/'],
            ], [
                'HoTen.required'    => 'Họ và tên không được để trống.',
                'Email.required'    => 'Email không được để trống.',
                'Email.email'       => 'Email không đúng định dạng.',
                'Email.unique'      => 'Email này đã được sử dụng bởi người khác.',
                'SoDienThoai.regex' => 'Số điện thoại không hợp lệ (cần đúng 10 số, bắt đầu bằng 03, 05, 07, 08, 09).',
            ]);

            $profile->update($validated);

        } elseif ($role === 'Giảng viên') {
            $profile = GiangVien::where('MaTK', $user->MaTK)->first();
            if (!$profile) {
                return back()->withErrors(['general' => 'Không tìm thấy thông tin giảng viên để cập nhật.']);
            }

            $validated = $request->validate([
                'Email'       => 'required|email|max:255|unique:GiangVien,Email,' . $profile->MaGV . ',MaGV',
                'SoDienThoai' => ['nullable', 'regex:/^(0[3|5|7|8|9])[0-9]{8}$/'],
                'GioiTinh'    => 'nullable|in:Nam,Nữ',
                'NgaySinh'    => 'nullable|date|before:today',
            ], [
                'Email.required'    => 'Email không được để trống.',
                'Email.email'       => 'Email không đúng định dạng.',
                'Email.unique'      => 'Email này đã được sử dụng bởi người khác.',
                'SoDienThoai.regex' => 'Số điện thoại không hợp lệ (cần đúng 10 số, bắt đầu bằng 03, 05, 07, 08, 09).',
                'GioiTinh.in'       => 'Giới tính chỉ có thể là Nam hoặc Nữ.',
                'NgaySinh.before'   => 'Ngày sinh không hợp lệ.',
            ]);

            $profile->update($validated);

        } else {
            $profile = SinhVien::where('MaTK', $user->MaTK)->first();
            if (!$profile) {
                return back()->withErrors(['general' => 'Không tìm thấy hồ sơ sinh viên để cập nhật.']);
            }

            $validated = $request->validate([
                'Email'       => 'required|email|max:255|unique:SinhVien,Email,' . $profile->MaSV . ',MaSV',
                'SoDienThoai' => ['nullable', 'regex:/^(0[3|5|7|8|9])[0-9]{8}$/'],
                'GioiTinh'    => 'nullable|in:Nam,Nữ',
                'NgaySinh'    => 'nullable|date|before:today',
            ], [
                'Email.required'    => 'Email không được để trống.',
                'Email.email'       => 'Email không đúng định dạng.',
                'Email.unique'      => 'Email này đã được sử dụng bởi người khác.',
                'SoDienThoai.regex' => 'Số điện thoại không hợp lệ (cần đúng 10 số, bắt đầu bằng 03, 05, 07, 08, 09).',
                'GioiTinh.in'       => 'Giới tính chỉ có thể là Nam hoặc Nữ.',
                'NgaySinh.before'   => 'Ngày sinh không hợp lệ.',
            ]);

            $profile->update($validated);
        }

        return back()->with('success', 'Cập nhật thông tin hồ sơ thành công!');
    }

    /**
     * Hiển thị giao diện đổi mật khẩu
     */
    public function showChangePasswordForm()
    {
        /** @var \App\Models\TaiKhoan $user */
        $user = Auth::user();
        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';

        if (in_array($role, ['Admin', 'Giáo vụ'])) {
            $layout = 'layouts.admin';
        } elseif ($role === 'Giảng viên') {
            $layout = 'layouts.giangvien';
        } else {
            $layout = 'layouts.sinhvien';
        }

        return view('profile.password', compact('layout', 'user', 'role'));
    }

    /**
     * Xử lý đổi mật khẩu tài khoản
     */
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

        // 1. Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->MatKhau)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không chính xác.']);
        }

        // 2. Không cho phép đặt mật khẩu mới trùng với mật khẩu hiện tại
        if (Hash::check($request->new_password, $user->MatKhau)) {
            return back()->withErrors(['new_password' => 'Mật khẩu mới không được trùng với mật khẩu hiện tại đang dùng.']);
        }

        // 3. Cập nhật mật khẩu mới và các trường bảo mật
        $tk = TaiKhoan::findOrFail($user->MaTK);
        $tk->MatKhau = Hash::make($request->new_password);
        $tk->TrangThaiMatKhau = 'ACTIVE';
        $tk->BatBuocDoiMatKhau = false;
        $tk->NgayDoiMatKhau = now();
        $tk->SoLanDangNhapSai = 0;
        $tk->save();

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return back()->with('success', 'Đổi mật khẩu thành công! Mật khẩu mới của bạn đã được cập nhật an toàn.');
    }
}
