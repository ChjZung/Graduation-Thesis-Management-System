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
        BoMon::create($request->all());
        return redirect()->route('bomon.index')->with('success', 'Thêm thành công!');
    }

    public function edit($id)
    {
        $bomon = BoMon::findOrFail($id);
        return view('admin.bomon.edit', compact('bomon'));
    }

    public function update(Request $request, $id)
    {
        $bomon = BoMon::findOrFail($id);
        $bomon->update($request->all());
        return redirect()->route('bomon.index')->with('success', 'Cập nhật thành công!');
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
