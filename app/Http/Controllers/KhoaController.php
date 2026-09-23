<?php

namespace App\Http\Controllers;

use App\Helpers\IdGenerator;
use App\Models\Khoa;
use Illuminate\Http\Request;

use App\Http\Traits\HandlesExcelImport;

class KhoaController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $query = Khoa::withCount(['boMons', 'nganhs', 'sinhViens'])->with(['giaoVus']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('MaKhoa', 'like', "%{$s}%")
                  ->orWhere('TenKhoa', 'like', "%{$s}%");
            });
        }

        $khoas = $query->paginate(10)->withQueryString();

        $totalKhoa = Khoa::count();
        $cnttKhoa = Khoa::where('TenKhoa', 'like', '%Công nghệ thông tin%')->orWhere('MaKhoa', 'like', '%CNTT%')->first();
        $bomonCntt = $cnttKhoa ? $cnttKhoa->boMons()->count() : 0;
        $totalGv = \App\Models\GiangVien::count();

        $stats = [
            'total_khoa' => $totalKhoa,
            'bomon_cntt' => $bomonCntt,
            'total_gv' => $totalGv,
            'status' => '100%',
        ];

        return view('admin.khoa.index', compact('khoas', 'stats'));
    }

    public function create()
    {
        return view('admin.khoa.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaKhoa' => 'nullable|string|max:10|unique:Khoa,MaKhoa',
            'TenKhoa' => 'required|string|max:100|unique:Khoa,TenKhoa',
        ], [
            'MaKhoa.unique' => 'Mã khoa này đã tồn tại.',
            'TenKhoa.required' => 'Vui lòng nhập tên khoa.',
            'TenKhoa.unique' => 'Tên khoa này đã tồn tại.',
        ]);

        $maKhoa = $request->filled('MaKhoa') ? strtoupper(trim($request->MaKhoa)) : IdGenerator::nextKhoa();

        Khoa::create([
            'MaKhoa' => $maKhoa,
            'TenKhoa' => trim($request->TenKhoa),
        ]);

        return redirect()->route('khoa.index')->with('success', "Thêm Khoa '{$request->TenKhoa}' thành công!");
    }

    public function show($id)
    {
        $khoa = Khoa::with([
            'boMons' => function($q) {
                $q->withCount('giangViens');
            },
            'nganhs' => function($q) {
                $q->withCount('lops');
            },
            'giaoVus',
        ])->withCount(['boMons', 'nganhs', 'lops', 'sinhViens'])->findOrFail($id);

        $totalGiangVien = \App\Models\GiangVien::whereHas('boMon', function($q) use ($id) {
            $q->where('MaKhoa', $id);
        })->count();

        $stats = [
            'total_bomon' => $khoa->bo_mons_count,
            'total_nganh' => $khoa->nganhs_count,
            'total_lop'   => $khoa->lops_count,
            'total_sv'    => $khoa->sinh_viens_count,
            'total_gv'    => $totalGiangVien,
        ];

        return view('admin.khoa.show', compact('khoa', 'stats'));
    }

    public function edit($id)
    {
        $khoa = Khoa::findOrFail($id);
        return view('admin.khoa.edit', compact('khoa'));
    }

    public function update(Request $request, $id)
    {
        $khoa = Khoa::findOrFail($id);

        $request->validate([
            'TenKhoa' => 'required|string|max:100|unique:Khoa,TenKhoa,' . $id . ',MaKhoa',
        ], [
            'TenKhoa.required' => 'Vui lòng nhập tên khoa.',
            'TenKhoa.unique' => 'Tên khoa này đã tồn tại.',
        ]);

        $khoa->update([
            'TenKhoa' => trim($request->TenKhoa),
        ]);

        return redirect()->route('khoa.index')->with('success', 'Cập nhật Khoa thành công!');
    }

    public function destroy($id)
    {
        $khoa = Khoa::findOrFail($id);
        try {
            Khoa::destroy($id);
            return redirect()->route('khoa.index')->with('success', "Xóa Khoa '{$khoa->TenKhoa}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa Khoa '{$khoa->TenKhoa}' do đang có Bộ môn hoặc Ngành học trực thuộc.");
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importKhoa', [], 'Khoa');
    }
}
