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

    public function index()
    {
        $bomons = BoMon::with('khoa')->withCount('giangViens')->paginate(10);
        return view('admin.bomon.index', compact('bomons'));
    }

    public function create()
    {
        $khoas = Khoa::orderBy('TenKhoa')->get();
        return view('admin.bomon.create', compact('khoas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaBoMon' => 'nullable|string|max:10|unique:bo_mons,MaBoMon',
            'TenBoMon' => 'required|string|max:100|unique:bo_mons,TenBoMon',
            'MaKhoa' => 'required|exists:khoas,MaKhoa',
        ], [
            'MaBoMon.unique' => 'Mã bộ môn đã tồn tại.',
            'TenBoMon.required' => 'Vui lòng nhập tên bộ môn.',
            'TenBoMon.unique' => 'Tên bộ môn này đã tồn tại trong hệ thống.',
            'MaKhoa.required' => 'Vui lòng chọn Khoa trực thuộc.',
            'MaKhoa.exists' => 'Khoa đã chọn không tồn tại.',
        ]);

        $maBoMon = $request->filled('MaBoMon') ? strtoupper(trim($request->MaBoMon)) : IdGenerator::nextBoMon();

        BoMon::create([
            'MaBoMon' => $maBoMon,
            'TenBoMon' => trim($request->TenBoMon),
            'MaKhoa' => $request->MaKhoa,
        ]);

        return redirect()->route('bomon.index')->with('success', "Thêm bộ môn '{$request->TenBoMon}' thành công!");
    }

    public function edit($id)
    {
        $bomon = BoMon::findOrFail($id);
        $khoas = Khoa::orderBy('TenKhoa')->get();
        return view('admin.bomon.edit', compact('bomon', 'khoas'));
    }

    public function update(Request $request, $id)
    {
        $bomon = BoMon::findOrFail($id);

        $request->validate([
            'TenBoMon' => 'required|string|max:100|unique:bo_mons,TenBoMon,' . $id . ',MaBoMon',
            'MaKhoa' => 'required|exists:khoas,MaKhoa',
        ], [
            'TenBoMon.required' => 'Vui lòng nhập tên bộ môn.',
            'TenBoMon.unique' => 'Tên bộ môn này đã tồn tại trong hệ thống.',
            'MaKhoa.required' => 'Vui lòng chọn Khoa trực thuộc.',
        ]);

        $bomon->update([
            'TenBoMon' => trim($request->TenBoMon),
            'MaKhoa' => $request->MaKhoa,
        ]);

        return redirect()->route('bomon.index')->with('success', 'Cập nhật bộ môn thành công!');
    }

    public function destroy($id)
    {
        $bomon = BoMon::findOrFail($id);
        try {
            BoMon::destroy($id);
            return redirect()->route('bomon.index')->with('success', "Xóa bộ môn '{$bomon->TenBoMon}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa bộ môn '{$bomon->TenBoMon}' do đang có giảng viên thuộc bộ môn.");
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importBoMon', [], 'Bộ Môn');
    }
}
