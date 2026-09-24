<?php

namespace App\Http\Controllers;

use App\Helpers\IdGenerator;
use App\Models\Nganh;
use App\Models\Khoa;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class NganhController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $query = Nganh::with('khoa')->withCount('lops');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('MaNganh', 'like', "%{$s}%")
                  ->orWhere('TenNganh', 'like', "%{$s}%");
            });
        }

        if ($request->filled('ma_khoa')) {
            $query->where('MaKhoa', $request->ma_khoa);
        }

        $nganhs = $query->paginate(5)->withQueryString();
        $khoas = Khoa::orderBy('TenKhoa')->get();

        $totalNganh = Nganh::count();
        $cnttKhoa = Khoa::where('TenKhoa', 'like', '%Công nghệ thông tin%')->orWhere('MaKhoa', 'like', '%CNTT%')->first();
        $nganhCntt = $cnttKhoa ? Nganh::where('MaKhoa', $cnttKhoa->MaKhoa)->count() : 0;
        $totalSv = \App\Models\SinhVien::count();

        $stats = [
            'total_nganh' => $totalNganh,
            'nganh_cntt' => $nganhCntt,
            'total_sv' => $totalSv,
            'status' => '100%',
        ];

        return view('admin.nganh.index', compact('nganhs', 'khoas', 'stats'));
    }

    public function create()
    {
        $khoas = Khoa::orderBy('TenKhoa')->get();
        return view('admin.nganh.create', compact('khoas') + ['Khoa' => $khoas]);
    }

    public function store(Request $request)
    {
        if ($request->filled('TenNganh')) {
            $request->merge(['TenNganh' => trim($request->TenNganh)]);
        }
        if ($request->filled('MaNganh')) {
            $request->merge(['MaNganh' => strtoupper(trim($request->MaNganh))]);
        }

        $request->validate([
            'MaNganh' => 'nullable|string|max:10|unique:Nganh,MaNganh',
            'TenNganh' => 'required|string|max:100|unique:Nganh,TenNganh',
            'MaKhoa' => 'required|exists:Khoa,MaKhoa',
        ], [
            'MaNganh.unique' => 'Mã ngành đã tồn tại trong hệ thống.',
            'TenNganh.required' => 'Vui lòng nhập tên ngành.',
            'TenNganh.unique' => 'Tên ngành này đã tồn tại trong hệ thống.',
            'MaKhoa.required' => 'Vui lòng chọn Khoa trực thuộc.',
            'MaKhoa.exists' => 'Khoa đã chọn không tồn tại.',
        ]);

        $maNganh = $request->filled('MaNganh') ? $request->MaNganh : IdGenerator::nextNganh();

        Nganh::create([
            'MaNganh' => $maNganh,
            'TenNganh' => $request->TenNganh,
            'MaKhoa' => $request->MaKhoa,
        ]);

        return redirect()->route('nganh.index')->with('success', "Thêm ngành '{$request->TenNganh}' thành công!");
    }

    public function show($id)
    {
        $nganh = Nganh::with([
            'khoa',
            'lops' => function($q) {
                $q->withCount('sinhViens');
            },
            'chuyenNganhs',
        ])->withCount(['lops', 'sinhViens'])->findOrFail($id);

        $totalSv = $nganh->sinh_viens_count;
        $svDuDk = \App\Models\SinhVien::where('MaNganh', $id)->where('SoTinChiTichLuy', '>=', 115)->count();
        $pctDuDk = $totalSv > 0 ? round(($svDuDk / $totalSv) * 100, 1) : 0;

        $stats = [
            'total_lop'  => $nganh->lops_count,
            'total_sv'   => $totalSv,
            'sv_du_dk'   => $svDuDk,
            'pct_du_dk'  => $pctDuDk,
        ];

        return view('admin.nganh.show', compact('nganh', 'stats'));
    }

    public function edit($id)
    {
        $nganh = Nganh::findOrFail($id);
        $khoas = Khoa::orderBy('TenKhoa')->get();
        return view('admin.nganh.edit', compact('nganh', 'khoas') + ['Khoa' => $khoas]);
    }

    public function update(Request $request, $id)
    {
        $nganh = Nganh::findOrFail($id);

        if ($request->filled('TenNganh')) {
            $request->merge(['TenNganh' => trim($request->TenNganh)]);
        }

        $request->validate([
            'TenNganh' => 'required|string|max:100|unique:Nganh,TenNganh,' . $id . ',MaNganh',
            'MaKhoa' => 'required|exists:Khoa,MaKhoa',
        ], [
            'TenNganh.required' => 'Vui lòng nhập tên ngành.',
            'TenNganh.unique' => 'Tên ngành này đã tồn tại trong hệ thống.',
            'MaKhoa.required' => 'Vui lòng chọn Khoa trực thuộc.',
        ]);

        $nganh->update([
            'TenNganh' => $request->TenNganh,
            'MaKhoa' => $request->MaKhoa,
        ]);

        return redirect()->route('nganh.index')->with('success', 'Cập nhật thông tin ngành thành công!');
    }

    public function destroy($id)
    {
        $nganh = Nganh::withCount(['lops', 'sinhViens'])->findOrFail($id);

        $totalRelated = $nganh->lops_count + $nganh->sinh_viens_count;
        if ($totalRelated > 0) {
            return redirect()->back()->withErrors("Không thể xóa ngành '{$nganh->TenNganh}' do đang có dữ liệu liên quan ({$nganh->lops_count} lớp, {$nganh->sinh_viens_count} sinh viên). Vui lòng di chuyển các dữ liệu thuộc ngành trước.");
        }

        try {
            $nganh->delete();
            return redirect()->route('nganh.index')->with('success', "Xóa ngành '{$nganh->TenNganh}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa ngành '{$nganh->TenNganh}': " . $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importNganh', [], 'Ngành');
    }
}