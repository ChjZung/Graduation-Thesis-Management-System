<?php

namespace App\Http\Controllers;

use App\Models\Nganh;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class NganhController extends Controller
{
    use HandlesExcelImport;
    public function index()
    {
        $nganhs = Nganh::paginate(10);
        return view('admin.nganh.index', compact('nganhs'));
    }

    public function create()
    {
        return view('admin.nganh.create');
    }

    public function store(Request $request)
    {
        Nganh::create($request->all());
        return redirect()->route('nganh.index')->with('success', 'Thêm thành công!');
    }

    public function edit($id)
    {
        $nganh = Nganh::findOrFail($id);
        return view('admin.nganh.edit', compact('nganh'));
    }

    public function update(Request $request, $id)
    {
        $nganh = Nganh::findOrFail($id);
        $nganh->update($request->all());
        return redirect()->route('nganh.index')->with('success', 'Cập nhật thành công!');
    }

    public function destroy($id)
    {
        Nganh::destroy($id);
        return redirect()->route('nganh.index')->with('success', 'Xóa thành công!');
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importNganh', [], 'Ngành');
    }
}