<?php

namespace App\Http\Controllers;

use App\Helpers\IdGenerator;
use App\Models\Khoa;
use Illuminate\Http\Request;

class KhoaController extends Controller
{
    public function index()
    {
        $khoas = Khoa::withCount(['boMons', 'nganhs'])->paginate(10);
        return view('admin.khoa.index', compact('khoas'));
    }

    public function create()
    {
        return view('admin.khoa.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaKhoa' => 'nullable|string|max:10|unique:khoas,MaKhoa',
            'TenKhoa' => 'required|string|max:100|unique:khoas,TenKhoa',
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

    public function edit($id)
    {
        $khoa = Khoa::findOrFail($id);
        return view('admin.khoa.edit', compact('khoa'));
    }

    public function update(Request $request, $id)
    {
        $khoa = Khoa::findOrFail($id);

        $request->validate([
            'TenKhoa' => 'required|string|max:100|unique:khoas,TenKhoa,' . $id . ',MaKhoa',
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
}
