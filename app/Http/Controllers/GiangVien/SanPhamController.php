<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Models\HuongDan;
use App\Models\NhomDoAn;
use App\Models\PhanCongHuongDanLop;
use App\Models\DangKyDeTai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SanPhamController extends Controller
{
    public function index(Request $request)
    {
        $gv = GiangVien::where('MaTK', Auth::user()->MaTK)->first();
        if (!$gv) abort(403);

        // 1. Nhóm từ bảng HuongDan
        $nhomIds1 = HuongDan::where('MaGV', $gv->MaGV)->pluck('MaNhom')->toArray();

        // 2. Nhóm thuộc Lớp Học Phần do Giảng viên phụ trách
        $nhomIds2 = NhomDoAn::whereHas('lopHocPhan', function ($q) use ($gv) {
            $q->where('MaGV', $gv->MaGV);
        })->pluck('MaNhom')->toArray();

        // 3. Nhóm có đề tài do giảng viên tạo
        $nhomIds3 = DangKyDeTai::whereHas('deTai', function ($q) use ($gv) {
            $q->where('MaTK', $gv->MaTK);
        })->pluck('MaNhom')->toArray();

        $allNhomIds = array_unique(array_merge($nhomIds1, $nhomIds2, $nhomIds3));

        $allNhoms = NhomDoAn::whereIn('MaNhom', $allNhomIds)->get();

        $selectedNhomId = $request->get('maNhom');

        $query = NhomDoAn::whereIn('MaNhom', $allNhomIds)
                         ->with([
                             'thanhVienNhoms.sinhVien',
                             'sanPhams',
                             'dangKyDeTai.deTai',
                             'monHoc',
                             'hocKy'
                         ]);

        if (!empty($selectedNhomId)) {
            $query->where('MaNhom', $selectedNhomId);
        }

        $nhoms = $query->paginate(10);

        // Nạp báo cáo tiến độ và nhận xét của từng nhóm
        foreach ($nhoms as $nhom) {
            $nhom->baoCaos = \App\Models\BaoCaoTienDo::with('nhanXets')
                                ->where('MaNhom', $nhom->MaNhom)
                                ->orderBy('LanBaoCao', 'desc')
                                ->get();
        }

        return view('giangvien.sanpham.index', compact('nhoms', 'allNhoms', 'selectedNhomId'));
    }
}

