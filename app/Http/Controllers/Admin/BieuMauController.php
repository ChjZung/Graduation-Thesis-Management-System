<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BieuMau;
use App\Models\KeHoachKhoaLuan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BieuMauController extends Controller
{
    public function index(Request $request)
    {
        $query = BieuMau::with('keHoachKhoaLuan.hocKy');

        if ($request->filled('make_hoach')) {
            $query->where('MakeHoach', $request->make_hoach);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('TenBieuMau', 'LIKE', "%{$s}%")
                  ->orWhere('MaBieuMau', 'LIKE', "%{$s}%");
            });
        }

        $bieuMaus = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $keHoachs = KeHoachKhoaLuan::with('hocKy')->orderBy('created_at', 'desc')->get();

        $stats = [
            'total_bieumau' => BieuMau::count(),
            'total_kehoach' => KeHoachKhoaLuan::count(),
            'active_plan'   => KeHoachKhoaLuan::where('TrangThai', 'ĐANG THỰC HIỆN')->orWhere('TrangThai', 'ĐÃ CÔNG BỐ')->first(),
        ];

        return view('admin.bieumau.index', compact('bieuMaus', 'keHoachs', 'stats'));
    }

    public function create()
    {
        $keHoachs = KeHoachKhoaLuan::with('hocKy')->orderBy('created_at', 'desc')->get();
        return view('admin.bieumau.create', compact('keHoachs'));
    }

    public function store(Request $request)
    {
        if ($request->filled('TenBieuMau')) {
            $request->merge(['TenBieuMau' => trim($request->TenBieuMau)]);
        }

        $request->validate([
            'TenBieuMau' => 'required|string|max:200',
            'MakeHoach'  => 'required|exists:KeHoachKhoaLuan,MakeHoach',
            'File'       => 'nullable|file|mimes:doc,docx,pdf,xls,xlsx,zip|max:10240',
            'DuongDanFile' => 'nullable|string|max:255',
        ], [
            'TenBieuMau.required' => 'Vui lòng nhập tên biểu mẫu.',
            'MakeHoach.required'  => 'Vui lòng chọn Kế hoạch khóa luận áp dụng.',
            'File.mimes'          => 'File biểu mẫu phải thuộc định dạng doc, docx, pdf, xls, xlsx, zip.',
            'File.max'            => 'Kích thước file không được vượt quá 10MB.',
        ]);

        $duongDan = $request->DuongDanFile ? trim($request->DuongDanFile) : '#';

        if ($request->hasFile('File')) {
            $file = $request->file('File');
            $filename = 'bieumau_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('bieumau', $filename, 'public');
            $duongDan = '/storage/' . $path;
        }

        $maBM = 'BM_' . Str::upper(Str::random(6));

        BieuMau::create([
            'MaBieuMau'   => $maBM,
            'TenBieuMau'  => $request->TenBieuMau,
            'DuongDanFile' => $duongDan,
            'MakeHoach'   => $request->MakeHoach,
        ]);

        return redirect()->route('admin.bieumau.index')->with('success', "Thêm mới biểu mẫu '{$request->TenBieuMau}' thành công!");
    }

    public function edit($id)
    {
        $bieuMau = BieuMau::findOrFail($id);
        $keHoachs = KeHoachKhoaLuan::with('hocKy')->orderBy('created_at', 'desc')->get();
        return view('admin.bieumau.edit', compact('bieuMau', 'keHoachs'));
    }

    public function update(Request $request, $id)
    {
        $bieuMau = BieuMau::findOrFail($id);

        if ($request->filled('TenBieuMau')) {
            $request->merge(['TenBieuMau' => trim($request->TenBieuMau)]);
        }

        $request->validate([
            'TenBieuMau' => 'required|string|max:200',
            'MakeHoach'  => 'required|exists:KeHoachKhoaLuan,MakeHoach',
            'File'       => 'nullable|file|mimes:doc,docx,pdf,xls,xlsx,zip|max:10240',
        ], [
            'TenBieuMau.required' => 'Vui lòng nhập tên biểu mẫu.',
            'MakeHoach.required'  => 'Vui lòng chọn Kế hoạch khóa luận áp dụng.',
            'File.mimes'          => 'File biểu mẫu phải thuộc định dạng doc, docx, pdf, xls, xlsx, zip.',
        ]);

        $duongDan = $bieuMau->DuongDanFile;

        if ($request->hasFile('File')) {
            $file = $request->file('File');
            $filename = 'bieumau_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('bieumau', $filename, 'public');
            $duongDan = '/storage/' . $path;
        } elseif ($request->filled('DuongDanFile')) {
            $duongDan = trim($request->DuongDanFile);
        }

        $bieuMau->update([
            'TenBieuMau'   => $request->TenBieuMau,
            'DuongDanFile' => $duongDan,
            'MakeHoach'    => $request->MakeHoach,
        ]);

        return redirect()->route('admin.bieumau.index')->with('success', "Cập nhật biểu mẫu thành công!");
    }

    public function destroy($id)
    {
        $bieuMau = BieuMau::findOrFail($id);
        $bieuMau->delete();
        return redirect()->route('admin.bieumau.index')->with('success', "Xóa biểu mẫu thành công!");
    }
}
