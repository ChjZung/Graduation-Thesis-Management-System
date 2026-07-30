<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use App\Models\NhomDoAn;
use App\Models\HocKy;
use App\Models\MonHoc;
use App\Models\Lop;
use App\Models\GiangVien;
use App\Models\BaoCaoTienDo;
use Illuminate\Http\Request;

class SanPhamController extends Controller
{
    public function index(Request $request)
    {
        $hockys = HocKy::all();
        $monhocs = MonHoc::all();
        $lops = Lop::all();
        $giangviens = GiangVien::all();

        $query = SanPham::with([
            'nhomDoAn.monHoc',
            'nhomDoAn.hocKy',
            'nhomDoAn.dangKyDeTai.deTai.giangVien',
            'nhomDoAn.thanhVienNhoms.sinhVien.lop'
        ]);

        // 1. Lọc theo Học kỳ
        if ($request->filled('MaHocKy')) {
            $query->whereHas('nhomDoAn', function ($q) use ($request) {
                $q->where('MaHocKy', $request->MaHocKy);
            });
        }

        // 2. Lọc theo Môn học
        if ($request->filled('MaMon')) {
            $query->whereHas('nhomDoAn', function ($q) use ($request) {
                $q->where('MaMon', $request->MaMon);
            });
        }

        // 3. Lọc theo Lớp
        if ($request->filled('MaLop')) {
            $query->whereHas('nhomDoAn.thanhVienNhoms.sinhVien', function ($q) use ($request) {
                $q->where('MaLop', $request->MaLop);
            });
        }

        // 4. Lọc theo Giảng viên
        if ($request->filled('MaGV')) {
            $gv = GiangVien::find($request->MaGV);
            if ($gv) {
                $query->whereHas('nhomDoAn.dangKyDeTai.deTai', function ($q) use ($gv) {
                    $q->where('MaTK', $gv->MaTK);
                });
            }
        }

        // 5. Tìm theo tên nhóm / sản phẩm
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('TenSanPham', 'LIKE', "%{$search}%")
                  ->orWhereHas('nhomDoAn', function ($nq) use ($search) {
                      $nq->where('TenNhom', 'LIKE', "%{$search}%");
                  });
            });
        }

        $sanphams = $query->orderBy('NgayNop', 'desc')->paginate(15);

        // Nạp thêm danh sách báo cáo tiến độ cho từng nhóm sản phẩm
        foreach ($sanphams as $sp) {
            if ($sp->nhomDoAn) {
                $sp->baoCaos = BaoCaoTienDo::where('MaNhom', $sp->nhomDoAn->MaNhom)->get();
            } else {
                $sp->baoCaos = collect();
            }
        }

        return view('admin.sanpham.index', compact('sanphams', 'hockys', 'monhocs', 'lops', 'giangviens'));
    }
}
