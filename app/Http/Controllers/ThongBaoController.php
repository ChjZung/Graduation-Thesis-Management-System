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
        $lops = collect();
        $lopHocPhans = collect();

        if ($role === 'Admin') {
            $layout = 'layouts.admin';
            $thongbaos = ThongBao::where('MaTK', $user->MaTK)->with(['lop', 'lopHocPhan'])->orderBy('NgayTao', 'desc')->get();
        } elseif ($role === 'Giảng viên') {
            $layout = 'layouts.giangvien';
            $gv = \App\Models\GiangVien::where('MaTK', $user->MaTK)->first();
            $gvId = $gv->MaGV ?? 0;

            // Lớp Hành chính
            $assignedLopIds = \App\Models\PhanCongHuongDanLop::where('MaGV', $gvId)->pluck('MaLop')->toArray();
            $lops = \App\Models\Lop::whereIn('MaLop', $assignedLopIds)->get();

            // Lớp Học Phần (Lớp Tín Chỉ)
            $lopHocPhans = \App\Models\LopHocPhan::with(['monHoc', 'hocKy'])->where('MaGV', $gvId)->get();
            $assignedLhpIds = $lopHocPhans->pluck('MaLopHP')->toArray();

            $adminIds = \App\Models\TaiKhoan::where('MaVaiTro', 1)->pluck('MaTK')->toArray();
            $allowedIds = array_merge([$user->MaTK], $adminIds);
            
            $thongbaos = ThongBao::whereIn('MaTK', $allowedIds)
                                ->orWhereIn('MaLop', $assignedLopIds)
                                ->orWhereIn('MaLopHP', $assignedLhpIds)
                                ->with(['lop', 'lopHocPhan', 'taiKhoan'])
                                ->orderBy('NgayTao', 'desc')
                                ->get();
        } else {
            abort(403);
        }

        return view('thongbao.index', compact('layout', 'thongbaos', 'lops', 'lopHocPhans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TieuDe' => 'required|max:255',
            'NoiDung' => 'required',
        ], [
            'TieuDe.required' => 'Vui lòng nhập tiêu đề thông báo.',
            'TieuDe.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'NoiDung.required' => 'Vui lòng nhập nội dung thông báo.'
        ]);

        $maLop = null;
        $maLopHP = null;

        if ($request->filled('Target')) {
            $val = $request->Target;
            if (str_starts_with($val, 'lhp_')) {
                $maLopHP = (int) str_replace('lhp_', '', $val);
            } elseif (str_starts_with($val, 'lh_')) {
                $maLop = (int) str_replace('lh_', '', $val);
            }
        } elseif ($request->filled('MaLop')) {
            $maLop = $request->MaLop;
        } elseif ($request->filled('MaLopHP')) {
            $maLopHP = $request->MaLopHP;
        }

        ThongBao::create([
            'MaTK' => Auth::user()->MaTK,
            'MaLop' => $maLop,
            'MaLopHP' => $maLopHP,
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
