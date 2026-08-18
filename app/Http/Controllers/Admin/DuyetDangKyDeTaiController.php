<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DangKyDeTai;
use App\Models\Nhom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DuyetDangKyDeTaiController extends Controller
{
    public function index(Request $request)
    {
        $query = DangKyDeTai::with(['nhom.truongNhom', 'nhom.thanhViens.sinhVien', 'deTai.giangVien']);

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai);
        } else {
            $query->where('TrangThai', 'Chờ duyệt');
        }

        $dangKys = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.duyet_dangky.index', compact('dangKys'));
    }

    public function approve($id)
    {
        $dangKy = DangKyDeTai::with('nhom')->findOrFail($id);

        DB::transaction(function () use ($dangKy) {
            $dangKy->update([
                'TrangThai' => 'Đã duyệt',
                'NgayDuyet' => now(),
                'LyDoTuChoi' => null,
            ]);

            if ($dangKy->nhom) {
                $dangKy->nhom->update([
                    'MaDeTai' => $dangKy->MaDeTai,
                    'TrangThai' => 'Đã duyệt',
                ]);
            }
        });

        return redirect()->back()->with('success', "Đã phê duyệt đơn đăng ký đề tài cho Nhóm '{$dangKy->nhom->TenNhom}' thành công!");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'LyDoTuChoi' => 'required|string|max:500',
        ], [
            'LyDoTuChoi.required' => 'Vui lòng nhập lý do từ chối đơn đăng ký.',
        ]);

        $dangKy = DangKyDeTai::with('nhom')->findOrFail($id);

        DB::transaction(function () use ($dangKy, $request) {
            $dangKy->update([
                'TrangThai' => 'Từ chối',
                'LyDoTuChoi' => $request->LyDoTuChoi,
            ]);

            if ($dangKy->nhom) {
                $dangKy->nhom->update([
                    'MaDeTai' => null,
                    'TrangThai' => 'Đang hoạt động',
                ]);
            }
        });

        return redirect()->back()->with('success', "Đã từ chối đơn đăng ký đề tài của Nhóm '{$dangKy->nhom->TenNhom}'.");
    }
}
