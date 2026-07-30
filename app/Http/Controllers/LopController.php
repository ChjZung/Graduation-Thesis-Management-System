<?php

namespace App\Http\Controllers;

use App\Models\Lop;
use App\Models\Nganh;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class LopController extends Controller
{
    use HandlesExcelImport;

    public function index()
    {
        $lops = Lop::with('nganh')->paginate(10);
        return view('admin.lop.index', compact('lops'));
    }

    public function create()
    {
        $nganhs = Nganh::all();
        return view('admin.lop.create', compact('nganhs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenLop' => 'required|string|max:50|unique:lops,TenLop',
            'MaNganh' => 'required|exists:nganhs,MaNganh',
            'KhoaHoc' => 'required|string|max:20'
        ], [
            'TenLop.required' => 'Vui lòng nhập tên lớp.',
            'TenLop.unique' => 'Tên lớp này đã tồn tại.',
            'MaNganh.required' => 'Vui lòng chọn ngành.',
            'KhoaHoc.required' => 'Vui lòng nhập khóa học.'
        ]);

        Lop::create($request->only(['TenLop', 'MaNganh', 'KhoaHoc']));
        return redirect()->route('lop.index')->with('success', 'Thêm lớp thành công!');
    }

    public function edit($id)
    {
        $lop = Lop::findOrFail($id);
        $nganhs = Nganh::all();
        return view('admin.lop.edit', compact('lop', 'nganhs'));
    }

    public function update(Request $request, $id)
    {
        $lop = Lop::findOrFail($id);

        $request->validate([
            'TenLop' => 'required|string|max:50|unique:lops,TenLop,' . $id . ',MaLop',
            'MaNganh' => 'required|exists:nganhs,MaNganh',
            'KhoaHoc' => 'required|string|max:20'
        ], [
            'TenLop.required' => 'Vui lòng nhập tên lớp.',
            'TenLop.unique' => 'Tên lớp này đã tồn tại.',
            'MaNganh.required' => 'Vui lòng chọn ngành.',
            'KhoaHoc.required' => 'Vui lòng nhập khóa học.'
        ]);

        $lop->update($request->only(['TenLop', 'MaNganh', 'KhoaHoc']));
        return redirect()->route('lop.index')->with('success', 'Cập nhật thông tin lớp thành công!');
    }

    public function destroy($id)
    {
        $lop = Lop::findOrFail($id);
        try {
            Lop::destroy($id);
            return redirect()->route('lop.index')->with('success', 'Xóa lớp thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa lớp '{$lop->TenLop}' do đang có sinh viên, đề tài hoặc phân công liên quan.");
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importLop', [], 'Lớp');
    }
}