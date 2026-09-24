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

        $khoas = $query->paginate(5)->withQueryString();

        $totalKhoa = Khoa::count();
        $cnttKhoa = Khoa::where('TenKhoa', 'like', '%Công nghệ thông tin%')->orWhere('MaKhoa', 'like', '%CNTT%')->first();
        $bomonCntt = $cnttKhoa ? $cnttKhoa->boMons()->count() : 0;
        $totalGv = \App\Models\GiangVien::count();

        $giangViens = \App\Models\GiangVien::orderBy('HoTen')->get();

        $stats = [
            'total_khoa' => $totalKhoa,
            'bomon_cntt' => $bomonCntt,
            'total_gv' => $totalGv,
            'status' => '100%',
        ];

        return view('admin.khoa.index', compact('khoas', 'stats', 'giangViens'));
    }

    public function create()
    {
        $giangViens = \App\Models\GiangVien::orderBy('HoTen')->get();
        return view('admin.khoa.create', compact('giangViens'));
    }

    public function store(Request $request)
    {
        if ($request->filled('TenKhoa')) {
            $request->merge(['TenKhoa' => trim($request->TenKhoa)]);
        }
        if ($request->filled('MaKhoa')) {
            $request->merge(['MaKhoa' => strtoupper(trim($request->MaKhoa))]);
        }

        $request->validate([
            'MaKhoa' => 'nullable|string|max:10|unique:Khoa,MaKhoa',
            'TenKhoa' => 'required|string|max:100|unique:Khoa,TenKhoa',
        ], [
            'MaKhoa.unique' => 'Mã khoa này đã tồn tại trong hệ thống.',
            'TenKhoa.required' => 'Vui lòng nhập tên khoa.',
            'TenKhoa.unique' => 'Tên khoa này đã tồn tại trong hệ thống.',
        ]);

        $maKhoa = $request->filled('MaKhoa') ? $request->MaKhoa : IdGenerator::nextKhoa();

        Khoa::create([
            'MaKhoa' => $maKhoa,
            'TenKhoa' => $request->TenKhoa,
            'TruongKhoa' => $request->TruongKhoa,
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
        $giangViens = \App\Models\GiangVien::orderBy('HoTen')->get();
        return view('admin.khoa.edit', compact('khoa', 'giangViens'));
    }

    public function update(Request $request, $id)
    {
        $khoa = Khoa::findOrFail($id);

        if ($request->filled('TenKhoa')) {
            $request->merge(['TenKhoa' => trim($request->TenKhoa)]);
        }

        $request->validate([
            'TenKhoa' => 'required|string|max:100|unique:Khoa,TenKhoa,' . $id . ',MaKhoa',
        ], [
            'TenKhoa.required' => 'Vui lòng nhập tên khoa.',
            'TenKhoa.unique' => 'Tên khoa này đã tồn tại trong hệ thống.',
        ]);

        $khoa->update([
            'TenKhoa' => $request->TenKhoa,
            'TruongKhoa' => $request->TruongKhoa,
        ]);

        return redirect()->route('khoa.index')->with('success', 'Cập nhật Khoa thành công!');
    }

    public function destroy($id)
    {
        $khoa = Khoa::withCount(['boMons', 'nganhs', 'lops', 'sinhViens', 'giaoVus'])->findOrFail($id);

        $totalRelated = $khoa->bo_mons_count + $khoa->nganhs_count + $khoa->lops_count + $khoa->sinh_viens_count + $khoa->giao_vus_count;

        if ($totalRelated > 0) {
            return redirect()->back()->withErrors("Không thể xóa Khoa '{$khoa->TenKhoa}' do đang có dữ liệu liên quan ({$khoa->bo_mons_count} bộ môn, {$khoa->nganhs_count} ngành, {$khoa->sinh_viens_count} sinh viên). Vui lòng di chuyển hoặc xóa các dữ liệu thuộc khoa trước.");
        }

        try {
            $khoa->delete();
            return redirect()->route('khoa.index')->with('success', "Xóa Khoa '{$khoa->TenKhoa}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa Khoa '{$khoa->TenKhoa}': " . $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importKhoa', [], 'Khoa');
    }
}
