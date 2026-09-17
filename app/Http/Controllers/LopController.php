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

    public function index()
    {
        $lops = Lop::with('nganh.khoa')->withCount('sinhViens')->paginate(10);
        return view('admin.lop.index', compact('lops'));
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