<?php

namespace App\Http\Controllers;

use App\Helpers\IdGenerator;
use App\Models\BoMon;
use App\Models\Khoa;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class BoMonController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $query = BoMon::with('khoa')->withCount('giangViens');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('MaBoMon', 'like', "%{$s}%")
                  ->orWhere('TenBoMon', 'like', "%{$s}%");
            });
        }

        if ($request->filled('ma_khoa')) {
            $query->where('MaKhoa', $request->ma_khoa);
        }

        $bomons = $query->paginate(5)->withQueryString();
        $khoas = Khoa::orderBy('TenKhoa')->get();

        $totalBoMon = BoMon::count();
        $cnttKhoa = Khoa::where('TenKhoa', 'like', '%Công nghệ thông tin%')->orWhere('MaKhoa', 'like', '%CNTT%')->first();
        $bomonCntt = $cnttKhoa ? BoMon::where('MaKhoa', $cnttKhoa->MaKhoa)->count() : 0;
        
        $totalGvCntt = $cnttKhoa ? \App\Models\GiangVien::whereHas('boMon', function($q) use ($cnttKhoa) {
            $q->where('MaKhoa', $cnttKhoa->MaKhoa);
        })->count() : \App\Models\GiangVien::count();

        $giangViens = \App\Models\GiangVien::orderBy('HoTen')->get();

        $stats = [
            'total_bomon' => $totalBoMon,
            'bomon_cntt' => $bomonCntt,
            'total_gv' => $totalGvCntt,
            'status' => '100%',
        ];

        return view('admin.bomon.index', compact('bomons', 'khoas', 'stats', 'giangViens'));
    }

    public function create()
    {
        $khoas = Khoa::orderBy('TenKhoa')->get();
        $giangViens = \App\Models\GiangVien::orderBy('HoTen')->get();
        return view('admin.bomon.create', compact('khoas', 'giangViens') + ['Khoa' => $khoas]);
    }

    public function store(Request $request)
    {
        if ($request->filled('TenBoMon')) {
            $request->merge(['TenBoMon' => trim($request->TenBoMon)]);
        }
        if ($request->filled('MaBoMon')) {
            $request->merge(['MaBoMon' => strtoupper(trim($request->MaBoMon))]);
        }

        $request->validate([
            'MaBoMon' => 'nullable|string|max:10|unique:BoMon,MaBoMon',
            'TenBoMon' => 'required|string|max:100|unique:BoMon,TenBoMon',
            'MaKhoa' => 'required|exists:Khoa,MaKhoa',
        ], [
            'MaBoMon.unique' => 'Mã bộ môn đã tồn tại trong hệ thống.',
            'TenBoMon.required' => 'Vui lòng nhập tên bộ môn.',
            'TenBoMon.unique' => 'Tên bộ môn này đã tồn tại trong hệ thống.',
            'MaKhoa.required' => 'Vui lòng chọn Khoa trực thuộc.',
            'MaKhoa.exists' => 'Khoa đã chọn không tồn tại.',
        ]);

        $maBoMon = $request->filled('MaBoMon') ? $request->MaBoMon : IdGenerator::nextBoMon();

        BoMon::create([
            'MaBoMon' => $maBoMon,
            'TenBoMon' => $request->TenBoMon,
            'TruongBoMon' => $request->TruongBoMon,
            'MaKhoa' => $request->MaKhoa,
        ]);

        return redirect()->route('bomon.index')->with('success', "Thêm bộ môn '{$request->TenBoMon}' thành công!");
    }

    public function show($id)
    {
        $bomon = BoMon::with([
            'khoa',
            'giangViens' => function($q) {
                $q->withCount('deTais');
            },
        ])->withCount('giangViens')->findOrFail($id);

        $totalDeTai = \App\Models\DeTai::whereHas('giangVien', function($q) use ($id) {
            $q->where('MaBoMon', $id);
        })->count();

        $gvHuongDan = \App\Models\GiangVien::where('MaBoMon', $id)->has('deTais')->count();

        $stats = [
            'total_gv'     => $bomon->giang_viens_count,
            'gv_huong_dan' => $gvHuongDan,
            'total_detai'  => $totalDeTai,
        ];

        return view('admin.bomon.show', compact('bomon', 'stats'));
    }

    public function edit($id)
    {
        $bomon = BoMon::findOrFail($id);
        $khoas = Khoa::orderBy('TenKhoa')->get();
        $giangViens = \App\Models\GiangVien::orderBy('HoTen')->get();
        return view('admin.bomon.edit', compact('bomon', 'khoas', 'giangViens') + ['Khoa' => $khoas]);
    }

    public function update(Request $request, $id)
    {
        $bomon = BoMon::findOrFail($id);

        if ($request->filled('TenBoMon')) {
            $request->merge(['TenBoMon' => trim($request->TenBoMon)]);
        }

        $request->validate([
            'TenBoMon' => 'required|string|max:100|unique:BoMon,TenBoMon,' . $id . ',MaBoMon',
            'MaKhoa' => 'required|exists:Khoa,MaKhoa',
        ], [
            'TenBoMon.required' => 'Vui lòng nhập tên bộ môn.',
            'TenBoMon.unique' => 'Tên bộ môn này đã tồn tại trong hệ thống.',
            'MaKhoa.required' => 'Vui lòng chọn Khoa trực thuộc.',
        ]);

        $bomon->update([
            'TenBoMon' => $request->TenBoMon,
            'TruongBoMon' => $request->TruongBoMon,
            'MaKhoa' => $request->MaKhoa,
        ]);

        return redirect()->route('bomon.index')->with('success', 'Cập nhật bộ môn thành công!');
    }

    public function destroy($id)
    {
        $bomon = BoMon::withCount('giangViens')->findOrFail($id);

        if ($bomon->giang_viens_count > 0) {
            return redirect()->back()->withErrors("Không thể xóa bộ môn '{$bomon->TenBoMon}' do đang có {$bomon->giang_viens_count} giảng viên trực thuộc.");
        }

        try {
            $bomon->delete();
            return redirect()->route('bomon.index')->with('success', "Xóa bộ môn '{$bomon->TenBoMon}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa bộ môn '{$bomon->TenBoMon}': " . $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importBoMon', [], 'Bộ Môn');
    }
}
