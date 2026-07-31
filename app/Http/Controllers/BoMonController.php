<?php

namespace App\Http\Controllers;

use App\Models\BoMon;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class BoMonController extends Controller
{
    use HandlesExcelImport;
    public function index()
    {
        $bomons = BoMon::paginate(10);
        return view('admin.bomon.index', compact('bomons'));
    }

    public function create()
    {
        return view('admin.bomon.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenBoMon' => 'required|string|max:100|unique:bo_mons,TenBoMon',
            'MoTa' => 'nullable|string|max:500'
        ], [
            'TenBoMon.required' => 'Vui lòng nhập tên bộ môn.',
            'TenBoMon.unique' => 'Tên bộ môn này đã tồn tại trong hệ thống.',
            'TenBoMon.max' => 'Tên bộ môn không được vượt quá 100 ký tự.'
        ]);

        BoMon::create($request->only(['TenBoMon', 'MoTa']));
        return redirect()->route('bomon.index')->with('success', 'Thêm bộ môn thành công!');
    }

    public function edit($id)
    {
        $bomon = BoMon::findOrFail($id);
        return view('admin.bomon.edit', compact('bomon'));
    }

    public function update(Request $request, $id)
    {
        $bomon = BoMon::findOrFail($id);

        $request->validate([
            'TenBoMon' => 'required|string|max:100|unique:bo_mons,TenBoMon,' . $id . ',MaBoMon',
            'MoTa' => 'nullable|string|max:500'
        ], [
            'TenBoMon.required' => 'Vui lòng nhập tên bộ môn.',
            'TenBoMon.unique' => 'Tên bộ môn này đã tồn tại trong hệ thống.',
            'TenBoMon.max' => 'Tên bộ môn không được vượt quá 100 ký tự.'
        ]);

        $bomon->update($request->only(['TenBoMon', 'MoTa']));
        return redirect()->route('bomon.index')->with('success', 'Cập nhật bộ môn thành công!');
    }

    public function destroy($id)
    {
        try {
            BoMon::destroy($id);
            return redirect()->route('bomon.index')->with('success', 'Xóa thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors('Không thể xóa bộ môn này do đang có ngành học hoặc môn học liên quan.');
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importBoMon', [], 'Bộ Môn');
    }
}
