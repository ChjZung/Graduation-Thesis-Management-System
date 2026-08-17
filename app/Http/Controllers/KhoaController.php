<?php

namespace App\Http\Controllers;

use App\Models\Khoa;
use Illuminate\Http\Request;

class KhoaController extends Controller
{
    public function index()
    {
        $khoas = Khoa::paginate(10);
        return view('admin.khoa.index', compact('khoas'));
    }

    public function create()
    {
        return view('admin.khoa.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaKhoa' => 'required|string|max:10|unique:khoas,MaKhoa',
            'TenKhoa' => 'required|string|max:100|unique:khoas,TenKhoa',
        ], [
            'MaKhoa.required' => 'Vui lòng nhập mã khoa.',
            'MaKhoa.unique' => 'Mã khoa này đã tồn tại.',
            'TenKhoa.required' => 'Vui lòng nhập tên khoa.',
            'TenKhoa.unique' => 'Tên khoa này đã tồn tại.',
        ]);

        Khoa::create($request->only(['MaKhoa', 'TenKhoa']));
        return redirect()->route('khoa.index')->with('success', 'Thêm Khoa thành công!');
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

        $khoa->update($request->only(['TenKhoa']));
        return redirect()->route('khoa.index')->with('success', 'Cập nhật Khoa thành công!');
    }

    public function destroy($id)
    {
        $khoa = Khoa::findOrFail($id);
        try {
            Khoa::destroy($id);
            return redirect()->route('khoa.index')->with('success', 'Xóa Khoa thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa Khoa '{$khoa->TenKhoa}' do đang có Bộ môn hoặc dữ liệu liên quan.");
        }
    }
}
