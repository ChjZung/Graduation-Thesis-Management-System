<?php

namespace App\Http\Controllers;

use App\Models\HocKy;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class HocKyController extends Controller
{
    use HandlesExcelImport;

    public function index()
    {
        $hockys = HocKy::paginate(10);
        return view('admin.hocky.index', compact('hockys'));
    }

    public function create()
    {
        return view('admin.hocky.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenHocKy' => 'required|string|max:50',
            'NamHoc' => 'required|string|max:20',
            'NgayBatDau' => 'nullable|date',
            'NgayKetThuc' => 'nullable|date|after_or_equal:NgayBatDau'
        ], [
            'TenHocKy.required' => 'Vui lòng nhập tên học kỳ.',
            'NamHoc.required' => 'Vui lòng nhập năm học.',
            'NgayKetThuc.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.'
        ]);

        HocKy::create($request->only(['TenHocKy', 'NamHoc', 'NgayBatDau', 'NgayKetThuc']));
        return redirect()->route('hocky.index')->with('success', 'Thêm học kỳ thành công!');
    }

    public function edit($id)
    {
        $hocky = HocKy::findOrFail($id);
        return view('admin.hocky.edit', compact('hocky'));
    }

    public function update(Request $request, $id)
    {
        $hocky = HocKy::findOrFail($id);

        $request->validate([
            'TenHocKy' => 'required|string|max:50',
            'NamHoc' => 'required|string|max:20',
            'NgayBatDau' => 'nullable|date',
            'NgayKetThuc' => 'nullable|date|after_or_equal:NgayBatDau'
        ], [
            'TenHocKy.required' => 'Vui lòng nhập tên học kỳ.',
            'NamHoc.required' => 'Vui lòng nhập năm học.',
            'NgayKetThuc.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.'
        ]);

        $hocky->update($request->only(['TenHocKy', 'NamHoc', 'NgayBatDau', 'NgayKetThuc']));
        return redirect()->route('hocky.index')->with('success', 'Cập nhật học kỳ thành công!');
    }

    public function destroy($id)
    {
        $hocky = HocKy::findOrFail($id);
        try {
            HocKy::destroy($id);
            return redirect()->route('hocky.index')->with('success', 'Xóa học kỳ thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa học kỳ '{$hocky->TenHocKy}' do đang có đề tài, nhóm đồ án hoặc phân công liên quan.");
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importHocKy', [], 'Học Kỳ');
    }
}
