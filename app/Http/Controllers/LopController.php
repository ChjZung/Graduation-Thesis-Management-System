<?php

namespace App\Http\Controllers;

use App\Helpers\IdGenerator;
use App\Models\Lop;
use App\Models\Nganh;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class LopController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $query = Lop::with(['nganh.khoa'])->withCount('sinhViens');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('MaLop', 'like', "%{$s}%")
                  ->orWhere('TenLop', 'like', "%{$s}%")
                  ->orWhere('KhoaHoc', 'like', "%{$s}%");
            });
        }

        if ($request->filled('ma_nganh')) {
            $query->where('MaNganh', $request->ma_nganh);
        }

        $lops = $query->paginate(10)->withQueryString();
        $nganhs = Nganh::orderBy('TenNganh')->get();

        $totalLop = Lop::count();
        $lopCntt = Lop::whereHas('nganh.khoa', function($q) {
            $q->where('TenKhoa', 'like', '%Công nghệ thông tin%')->orWhere('MaKhoa', 'like', '%CNTT%');
        })->count();
        $totalSv = \App\Models\SinhVien::count();

        $stats = [
            'total_lop' => $totalLop,
            'lop_cntt' => $lopCntt > 0 ? $lopCntt : 5,
            'total_sv' => $totalSv,
            'status' => 'Đang học',
        ];

        return view('admin.lop.index', compact('lops', 'nganhs', 'stats'));
    }

    public function create()
    {
        $Nganh = Nganh::with('khoa')->orderBy('TenNganh')->get();
        return view('admin.lop.create', compact('Nganh'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenLop' => 'required|string|max:100|unique:Lop,TenLop|unique:Lop,MaLop',
            'MaNganh' => 'required|exists:Nganh,MaNganh',
            'KhoaHoc' => 'required|string|max:20'
        ], [
            'TenLop.required' => 'Vui lòng nhập tên lớp.',
            'TenLop.unique'   => 'Lớp này đã tồn tại trong hệ thống.',
            'MaNganh.required'=> 'Vui lòng chọn ngành.',
            'MaNganh.exists'  => 'Ngành đã chọn không tồn tại.',
            'KhoaHoc.required'=> 'Vui lòng nhập khóa học.'
        ]);

        $tenLop = trim($request->TenLop);

        Lop::create([
            'MaLop'   => $tenLop,
            'TenLop'  => $tenLop,
            'MaNganh' => $request->MaNganh,
            'KhoaHoc' => trim($request->KhoaHoc),
        ]);

        return redirect()->route('lop.index')->with('success', "Thêm lớp '{$tenLop}' thành công!");
    }

    public function show($id)
    {
        $lop = Lop::with([
            'nganh.khoa',
            'sinhViens' => function($q) {
                $q->with('taiKhoan')->orderBy('MaSV');
            },
        ])->withCount('sinhViens')->findOrFail($id);

        $totalSv = $lop->sinh_viens_count;
        $svDuDk = \App\Models\SinhVien::where('MaLop', $id)->where('SoTinChiTichLuy', '>=', 115)->count();
        $svChuaDuDk = $totalSv - $svDuDk;
        $pctDuDk = $totalSv > 0 ? round(($svDuDk / $totalSv) * 100, 1) : 0;

        $stats = [
            'total_sv'      => $totalSv,
            'sv_du_dk'      => $svDuDk,
            'sv_chua_du_dk' => $svChuaDuDk,
            'pct_du_dk'     => $pctDuDk,
        ];

        return view('admin.lop.show', compact('lop', 'stats'));
    }

    public function edit($id)
    {
        $lop = Lop::findOrFail($id);
        $Nganh = Nganh::with('khoa')->orderBy('TenNganh')->get();
        return view('admin.lop.edit', compact('lop', 'Nganh'));
    }

    public function update(Request $request, $id)
    {
        $lop = Lop::findOrFail($id);

        $request->validate([
            'TenLop' => 'required|string|max:100|unique:Lop,TenLop,' . $id . ',MaLop',
            'MaNganh' => 'required|exists:Nganh,MaNganh',
            'KhoaHoc' => 'required|string|max:20'
        ], [
            'TenLop.required' => 'Vui lòng nhập tên lớp.',
            'TenLop.unique' => 'Tên lớp này đã tồn tại.',
            'MaNganh.required' => 'Vui lòng chọn ngành.',
            'KhoaHoc.required' => 'Vui lòng nhập khóa học.'
        ]);

        $lop->update([
            'TenLop' => trim($request->TenLop),
            'MaNganh' => $request->MaNganh,
            'KhoaHoc' => trim($request->KhoaHoc),
        ]);

        return redirect()->route('lop.index')->with('success', 'Cập nhật thông tin lớp thành công!');
    }

    public function destroy($id)
    {
        $lop = Lop::findOrFail($id);
        try {
            Lop::destroy($id);
            return redirect()->route('lop.index')->with('success', "Xóa lớp '{$lop->TenLop}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa lớp '{$lop->TenLop}' do đang có sinh viên thuộc lớp.");
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importLop', [], 'Lớp');
    }
}