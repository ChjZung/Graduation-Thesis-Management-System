<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\LopHocPhan;
use App\Models\GiangVien;
use App\Models\NhomDoAn;
use App\Models\SinhVienLopHocPhan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LopController extends Controller
{
    public function index(Request $request)
    {
        $gv = GiangVien::where('MaTK', Auth::user()->MaTK)->first();
        if (!$gv) abort(403);
        
        $query = LopHocPhan::with(['monHoc', 'hocKy', 'sinhVienLopHocPhans'])
            ->where('MaGV', $gv->MaGV);

        if ($request->filled('ma_hoc_ky')) {
            $query->where('MaHocKy', $request->ma_hoc_ky);
        }

        $lopHocPhans = $query->orderBy('MaLopHP', 'desc')->get();
        $hocKies = \App\Models\HocKy::orderBy('MaHocKy', 'desc')->get();

        return view('giangvien.lop.index', compact('lopHocPhans', 'hocKies'));
    }

    public function show($id)
    {
        $gv = GiangVien::where('MaTK', Auth::user()->MaTK)->first();
        if (!$gv) abort(403);
        
        $lopHP = LopHocPhan::with(['monHoc', 'hocKy', 'giangVien'])
            ->where('MaLopHP', $id)
            ->firstOrFail();

        // Lấy danh sách sinh viên đăng ký thuộc Lớp Học Phần này
        $sinhVienLhps = SinhVienLopHocPhan::with('sinhVien.lop')
            ->where('MaLopHP', $lopHP->MaLopHP)
            ->get();

        // Lấy danh sách nhóm đồ án thuộc Lớp Học Phần này
        $nhoms = NhomDoAn::with(['sinhVienTruongNhom', 'thanhVienNhoms.sinhVien', 'dangKyDeTai.deTai', 'monHoc', 'hocKy'])
            ->where('MaLopHP', $lopHP->MaLopHP)
            ->get();

        return view('giangvien.lop.show', compact('lopHP', 'sinhVienLhps', 'nhoms'));
    }
}
