<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaiKhoan;
use App\Models\VaiTro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class YeuCauDoiMatKhauController extends Controller
{
    public function index(Request $request)
    {
        $query = TaiKhoan::with('vaiTro');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where('TenDangNhap', 'LIKE', "%{$s}%");
        }

        if ($request->filled('MaVaiTro')) {
            $query->where('MaVaiTro', $request->MaVaiTro);
        }

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai == '1');
        }

        $taiKhoans = $query->orderBy('MaTK')->paginate(15);
        $vaiTros = VaiTro::all();

        return view('admin.yeucau_matkhau.index', compact('taiKhoans', 'vaiTros'));
    }

    public function approve($id)
    {
        $tk = TaiKhoan::findOrFail($id);

        $tk->update([
            'MatKhau'           => Hash::make('123456'),
            'password_status'   => 'INITIAL',
            'BatBuocDoiMatKhau' => true,
            'SoLanDangNhapSai'  => 0,
            'TrangThai'         => true,
        ]);

        return redirect()->back()->with('success', "Đã reset mật khẩu tài khoản '{$tk->TenDangNhap}' về '123456' thành công! Người dùng sẽ được yêu cầu thiết lập mật khẩu cá nhân mới khi đăng nhập.");
    }

    public function reject($id)
    {
        $tk = TaiKhoan::findOrFail($id);

        if ($tk->MaTK === Auth::user()->MaTK) {
            return redirect()->back()->withErrors('Không thể tự khóa tài khoản Admin đang đăng nhập!');
        }

        $tk->update([
            'TrangThai' => !$tk->TrangThai,
        ]);

        $msg = $tk->TrangThai ? "Đã mở khóa tài khoản '{$tk->TenDangNhap}'." : "Đã khóa tài khoản '{$tk->TenDangNhap}'.";
        return redirect()->back()->with('success', $msg);
    }
}
