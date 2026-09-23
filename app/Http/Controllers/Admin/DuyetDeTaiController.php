<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeTai;
use Illuminate\Http\Request;

class DuyetDeTaiController extends Controller
{
    public function index(Request $request)
    {
        $counts = [
            'cho_duyet' => DeTai::where('TrangThai', 'Chờ duyệt')->count(),
            'da_duyet'  => DeTai::where('TrangThai', 'Đã duyệt')->count(),
            'tu_choi'   => DeTai::where('TrangThai', 'Từ chối')->count(),
            'total'     => DeTai::count(),
        ];

        $query = DeTai::with(['giangVien.boMon']);

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            $query->where('TrangThai', $request->TrangThai);
        } elseif (!$request->filled('TrangThai')) {
            $query->where('TrangThai', 'Chờ duyệt');
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('TenDeTai', 'LIKE', "%{$search}%")
                  ->orWhere('MaDeTai', 'LIKE', "%{$search}%")
                  ->orWhereHas('giangVien', function($gq) use ($search) {
                      $gq->where('HoTen', 'LIKE', "%{$search}%");
                  });
            });
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.duyet_detai.index', compact('detais', 'counts'));
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
