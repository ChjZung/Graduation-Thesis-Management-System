<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\YeuCauDoiMatKhau;
use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class YeuCauDoiMatKhauController extends Controller
{
    public function index()
    {
        $yeucaus = YeuCauDoiMatKhau::orderBy('MaYeuCau', 'desc')->paginate(15);
        return view('admin.yeucau_matkhau.index', compact('yeucaus'));
    }

    public function approve($id)
    {
        $yc = YeuCauDoiMatKhau::findOrFail($id);

        $tk = TaiKhoan::where('TenDangNhap', $yc->TenDangNhap)->first();
        if ($tk) {
            $tk->update([
                'MatKhau' => Hash::make('123456')
            ]);
        }

        $yc->update(['TrangThai' => 'Đã duyệt']);

        return redirect()->back()->with('success', "Đã duyệt yêu cầu của {$yc->TenDangNhap}! Mật khẩu đã được reset về 123456.");
    }

    public function reject($id)
    {
        $yc = YeuCauDoiMatKhau::findOrFail($id);
        $yc->update(['TrangThai' => 'Từ chối']);

        return redirect()->back()->with('success', "Đã từ chối yêu cầu của {$yc->TenDangNhap}.");
    }
}
