<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeTai;
use Illuminate\Http\Request;

class DuyetDeTaiController extends Controller
{
    public function index(Request $request)
    {
        $query = DeTai::with('giangVien');

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai);
        } else {
            $query->where('TrangThai', 'Chờ duyệt');
        }

        if ($request->filled('search')) {
            $query->where('TenDeTai', 'LIKE', '%' . trim($request->search) . '%');
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.duyet_detai.index', compact('detais'));
    }

    public function approve($id)
    {
        $detai = DeTai::findOrFail($id);
        $detai->update([
            'TrangThai' => 'Đã duyệt',
            'NgayDuyet' => now(),
            'LyDoTuChoi' => null,
        ]);

        return redirect()->back()->with('success', "Đã phê duyệt đề tài '{$detai->TenDeTai}' thành công!");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'LyDoTuChoi' => 'required|string|max:500',
        ], [
            'LyDoTuChoi.required' => 'Vui lòng nhập lý do từ chối đề tài.',
        ]);

        $detai = DeTai::findOrFail($id);
        $detai->update([
            'TrangThai' => 'Từ chối',
            'LyDoTuChoi' => $request->LyDoTuChoi,
        ]);

        return redirect()->back()->with('success', "Đã từ chối đề tài '{$detai->TenDeTai}'.");
    }
}
