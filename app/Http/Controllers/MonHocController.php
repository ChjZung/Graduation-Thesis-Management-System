<?php

namespace App\Http\Controllers;

use App\Models\MonHoc;
use App\Models\BoMon;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class MonHocController extends Controller
{
    use HandlesExcelImport;

    public function index()
    {
        $monhocs = MonHoc::with('boMon')->paginate(10);
        return view('admin.monhoc.index', compact('monhocs'));
    }

    public function create()
    {
        $bomons = BoMon::all();
        return view('admin.monhoc.create', compact('bomons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenMon' => 'required|string|max:100|unique:mon_hocs,TenMon',
            'MaBoMon' => 'required|exists:bo_mons,MaBoMon',
            'SoTinChi' => 'required|integer|min:1|max:10'
        ], [
            'TenMon.required' => 'Vui lòng nhập tên môn học.',
            'TenMon.unique' => 'Tên môn học này đã tồn tại.',
            'MaBoMon.required' => 'Vui lòng chọn bộ môn.',
            'SoTinChi.required' => 'Vui lòng nhập số tín chỉ.'
        ]);

        MonHoc::create($request->only(['TenMon', 'MaBoMon', 'SoTinChi']));
        return redirect()->route('monhoc.index')->with('success', 'Thêm môn học thành công!');
    }

    public function edit($id)
    {
        $monhoc = MonHoc::findOrFail($id);
        $bomons = BoMon::all();
        return view('admin.monhoc.edit', compact('monhoc', 'bomons'));
    }

    public function update(Request $request, $id)
    {
        $monhoc = MonHoc::findOrFail($id);

        $request->validate([
            'TenMon' => 'required|string|max:100|unique:mon_hocs,TenMon,' . $id . ',MaMon',
            'MaBoMon' => 'required|exists:bo_mons,MaBoMon',
            'SoTinChi' => 'required|integer|min:1|max:10'
        ], [
            'TenMon.required' => 'Vui lòng nhập tên môn học.',
            'TenMon.unique' => 'Tên môn học này đã tồn tại.',
            'MaBoMon.required' => 'Vui lòng chọn bộ môn.',
            'SoTinChi.required' => 'Vui lòng nhập số tín chỉ.'
        ]);

        $monhoc->update($request->only(['TenMon', 'MaBoMon', 'SoTinChi']));
        return redirect()->route('monhoc.index')->with('success', 'Cập nhật môn học thành công!');
    }

    public function destroy($id)
    {
        $monhoc = MonHoc::findOrFail($id);
        try {
            MonHoc::destroy($id);
            return redirect()->route('monhoc.index')->with('success', 'Xóa môn học thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa môn học '{$monhoc->TenMon}' do đang có đề tài hoặc nhóm đồ án thuộc môn này.");
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importMonHoc', [], 'Môn Học');
    }
}
