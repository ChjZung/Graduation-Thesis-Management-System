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

    public function index()
    {
        $nganhs = Nganh::with('khoa')->withCount('lops')->paginate(10);
        return view('admin.nganh.index', compact('nganhs'));
    }

    public function create()
    {
        $khoas = Khoa::orderBy('TenKhoa')->get();
        return view('admin.nganh.create', compact('khoas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaNganh' => 'nullable|string|max:10|unique:nganhs,MaNganh',
            'TenNganh' => 'required|string|max:100|unique:nganhs,TenNganh',
            'MaKhoa' => 'required|exists:khoas,MaKhoa',
        ], [
            'MaNganh.unique' => 'Mã ngành đã tồn tại.',
            'TenNganh.required' => 'Vui lòng nhập tên ngành.',
            'TenNganh.unique' => 'Tên ngành này đã tồn tại trong hệ thống.',
            'MaKhoa.required' => 'Vui lòng chọn Khoa trực thuộc.',
            'MaKhoa.exists' => 'Khoa đã chọn không tồn tại.',
        ]);

        $maNganh = $request->filled('MaNganh') ? strtoupper(trim($request->MaNganh)) : IdGenerator::nextNganh();

        Nganh::create([
            'MaNganh' => $maNganh,
            'TenNganh' => trim($request->TenNganh),
            'MaKhoa' => $request->MaKhoa,
        ]);

        return redirect()->route('nganh.index')->with('success', "Thêm ngành '{$request->TenNganh}' thành công!");
    }

    public function edit($id)
    {
        $nganh = Nganh::findOrFail($id);
        $khoas = Khoa::orderBy('TenKhoa')->get();
        return view('admin.nganh.edit', compact('nganh', 'khoas'));
    }

    public function update(Request $request, $id)
    {
        $nganh = Nganh::findOrFail($id);

        $request->validate([
            'TenNganh' => 'required|string|max:100|unique:nganhs,TenNganh,' . $id . ',MaNganh',
            'MaKhoa' => 'required|exists:khoas,MaKhoa',
        ], [
            'TenNganh.required' => 'Vui lòng nhập tên ngành.',
            'TenNganh.unique' => 'Tên ngành này đã tồn tại trong hệ thống.',
            'MaKhoa.required' => 'Vui lòng chọn Khoa trực thuộc.',
        ]);

        $nganh->update([
            'TenNganh' => trim($request->TenNganh),
            'MaKhoa' => $request->MaKhoa,
        ]);

        return redirect()->route('nganh.index')->with('success', 'Cập nhật thông tin ngành thành công!');
    }

    public function destroy($id)
    {
        $nganh = Nganh::findOrFail($id);
        try {
            Nganh::destroy($id);
            return redirect()->route('nganh.index')->with('success', "Xóa ngành '{$nganh->TenNganh}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa ngành '{$nganh->TenNganh}' do đang có các lớp học trực thuộc.");
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importNganh', [], 'Ngành');
    }
}