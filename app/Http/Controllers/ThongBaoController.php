<?php

namespace App\Http\Controllers;

use App\Models\ThongBao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThongBaoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->vaiTro->TenVaiTro ?? '';

        if ($role === 'Admin') {
            $layout = 'layouts.admin';
            $thongbaos = ThongBao::where('MaTK', $user->MaTK)->orderBy('NgayTao', 'desc')->get();
        } elseif ($role === 'Giảng viên') {
            $layout = 'layouts.giangvien';
            $adminIds = \App\Models\TaiKhoan::where('MaVaiTro', 1)->pluck('MaTK')->toArray();
            $allowedIds = array_merge([$user->MaTK], $adminIds);
            $thongbaos = ThongBao::whereIn('MaTK', $allowedIds)->orderBy('NgayTao', 'desc')->get();
        } else {
            abort(403);
        }

        return view('thongbao.index', compact('layout', 'thongbaos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TieuDe' => 'required|max:255',
            'NoiDung' => 'required'
        ], [
            'TieuDe.required' => 'Vui lòng nhập tiêu đề thông báo.',
            'TieuDe.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'NoiDung.required' => 'Vui lòng nhập nội dung thông báo.'
        ]);

        ThongBao::create([
            'MaTK' => Auth::user()->MaTK,
            'TieuDe' => $request->TieuDe,
            'NoiDung' => $request->NoiDung,
            'NgayTao' => date('Y-m-d')
        ]);

        return redirect()->back()->with('success', 'Tạo thông báo thành công!');
    }

    public function destroy($id)
    {
        $tb = ThongBao::where('MaThongBao', $id)->where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $tb->delete();

        return redirect()->back()->with('success', 'Đã xóa thông báo!');
    }
}
