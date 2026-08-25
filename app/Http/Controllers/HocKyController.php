<?php

namespace App\Http\Controllers;

use App\Helpers\IdGenerator;
use App\Models\HocKy;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class HocKyController extends Controller
{
    use HandlesExcelImport;

    public function index()
    {
        $hockys = HocKy::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.hocky.index', compact('hockys'));
    }

    public function create()
    {
        return view('admin.hocky.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaHocKy' => 'nullable|string|max:10|unique:hoc_kies,MaHocKy',
            'TenHocKy' => 'required|string|max:50',
            'NamHoc' => 'required|string|max:20',
            'NgayBatDau' => 'required|date',
            'NgayKetThuc' => 'required|date|after_or_equal:NgayBatDau'
        ], [
            'MaHocKy.unique' => 'Mã học kỳ đã tồn tại.',
            'TenHocKy.required' => 'Vui lòng nhập tên học kỳ.',
            'NamHoc.required' => 'Vui lòng nhập năm học.',
            'NgayBatDau.required' => 'Vui lòng chọn ngày bắt đầu.',
            'NgayKetThuc.required' => 'Vui lòng chọn ngày kết thúc.',
            'NgayKetThuc.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.'
        ]);

        $maHK = $request->filled('MaHocKy') ? strtoupper(trim($request->MaHocKy)) : IdGenerator::nextHocKy();

        HocKy::create([
            'MaHocKy' => $maHK,
            'TenHocKy' => trim($request->TenHocKy),
            'NamHoc' => trim($request->NamHoc),
            'NgayBatDau' => $request->NgayBatDau,
            'NgayKetThuc' => $request->NgayKetThuc,
            'TrangThai' => true,
        ]);

        return redirect()->route('hocky.index')->with('success', "Thêm học kỳ '{$request->TenHocKy}' thành công!");
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
            'NgayBatDau' => 'required|date',
            'NgayKetThuc' => 'required|date|after_or_equal:NgayBatDau'
        ], [
            'TenHocKy.required' => 'Vui lòng nhập tên học kỳ.',
            'NamHoc.required' => 'Vui lòng nhập năm học.',
            'NgayBatDau.required' => 'Vui lòng chọn ngày bắt đầu.',
            'NgayKetThuc.required' => 'Vui lòng chọn ngày kết thúc.',
            'NgayKetThuc.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.'
        ]);

        $hocky->update([
            'TenHocKy' => trim($request->TenHocKy),
            'NamHoc' => trim($request->NamHoc),
            'NgayBatDau' => $request->NgayBatDau,
            'NgayKetThuc' => $request->NgayKetThuc,
            'TrangThai' => $request->has('TrangThai') ? (bool)$request->TrangThai : $hocky->TrangThai,
        ]);

        return redirect()->route('hocky.index')->with('success', 'Cập nhật học kỳ thành công!');
    }

    public function destroy($id)
    {
        $hocky = HocKy::findOrFail($id);
        try {
            HocKy::destroy($id);
            return redirect()->route('hocky.index')->with('success', "Xóa học kỳ '{$hocky->TenHocKy}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa học kỳ '{$hocky->TenHocKy}' do đang có đề tài hoặc kế hoạch khóa luận liên quan.");
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importHocKy', [], 'Học Kỳ');
    }
}
