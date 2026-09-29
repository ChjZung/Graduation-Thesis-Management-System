<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HoSoBaoVe;
use App\Models\HoiDong;
use App\Models\GiangVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HoSoBaoVeController extends Controller
{
    public function index(Request $request)
    {
        $query = HoSoBaoVe::with([
            'nhom.truongNhom',
            'nhom.thanhViens.sinhVien',
            'nhom.deTai.giangVien',
            'hoiDong',
            'tepHoSoBaoVes',
        ]);

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('MaHoSo', 'like', "%{$s}%")
                  ->orWhereHas('nhom', function ($qn) use ($s) {
                      $qn->where('TenNhom', 'like', "%{$s}%")
                         ->orWhere('MaNhom', 'like', "%{$s}%");
                  })
                  ->orWhereHas('nhom.deTai', function ($qd) use ($s) {
                      $qd->where('TenDeTai', 'like', "%{$s}%");
                  });
            });
        }

        // 4 KPI Stats
        $tongSoHoSo = HoSoBaoVe::count();
        $choThamDinh = HoSoBaoVe::where('TrangThai', 'Chờ thẩm định')->count();
        $duDieuKien = HoSoBaoVe::where('TrangThai', 'Đủ điều kiện bảo vệ')->count();
        $daPhanCong = HoSoBaoVe::where(function ($q) {
            $q->whereIn('TrangThai', ['Đã phân công', 'Đã phân công hội đồng'])
              ->orWhereNotNull('MaHoiDong');
        })->count();

        $hoSos = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $hoiDongs = HoiDong::orderBy('TenHoiDong')->get();

        return view('admin.hoso_baove.index', compact(
            'hoSos',
            'hoiDongs',
            'tongSoHoSo',
            'choThamDinh',
            'duDieuKien',
            'daPhanCong'
        ));
    }

    /**
     * Giáo vụ kiểm tra tính hợp lệ & thẩm định hồ sơ
     */
    public function xacNhan(Request $request, $id)
    {
        $hoSo = HoSoBaoVe::findOrFail($id);

        $user = Auth::user();
        $maGVu = $user?->giaoVu?->MaGVu ?? \App\Models\GiaoVu::first()?->MaGVu;

        $action = $request->input('action', 'approve'); // approve or reject
        $ghiChuAdmin = $request->input('GhiChu', '');

        if ($action === 'reject') {
            $hoSo->update([
                'TrangThai'   => 'Không đủ điều kiện',
                'NgayXacNhan' => now()->toDateString(),
                'MaGVu'       => $maGVu,
                'GhiChu'      => $hoSo->GhiChu . ($ghiChuAdmin ? " [Từ chối: {$ghiChuAdmin}]" : " [Từ chối: Thiếu hồ sơ / Turnitin không đạt]"),
            ]);
            return redirect()->back()->with('warning', 'Đã từ chối hồ sơ bảo vệ (không đủ điều kiện)!');
        }

        $hoSo->update([
            'TrangThai'   => 'Đủ điều kiện bảo vệ',
            'NgayXacNhan' => now()->toDateString(),
            'MaGVu'       => $maGVu,
            'GhiChu'      => $ghiChuAdmin ? ($hoSo->GhiChu . " [GV duyệt: {$ghiChuAdmin}]") : $hoSo->GhiChu,
        ]);

        return redirect()->back()->with('success', 'Đã thẩm định hồ sơ: Đủ điều kiện tham gia Hội đồng bảo vệ!');
    }

    /**
     * Phân bổ nhóm đủ điều kiện vào Hội đồng bảo vệ (KHÔNG có chức năng phân công GVPB hồ sơ)
     */
    public function phanCong(Request $request, $id)
    {
        $request->validate([
            'MaHoiDong'     => 'required|exists:HoiDong,MaHoiDong',
            'ThoiGianBaoVe' => 'nullable|date',
            'PhongBaoVe'    => 'nullable|string|max:100',
        ], [
            'MaHoiDong.required' => 'Vui lòng chọn Hội đồng bảo vệ.',
        ]);

        $hoSo = HoSoBaoVe::findOrFail($id);

        $updateData = [
            'MaHoiDong' => $request->MaHoiDong,
            'TrangThai' => 'Đã phân công',
        ];
        if ($request->filled('ThoiGianBaoVe')) {
            $updateData['ThoiGianBaoVe'] = $request->ThoiGianBaoVe;
        }
        if ($request->filled('PhongBaoVe')) {
            $updateData['PhongBaoVe'] = $request->PhongBaoVe;
        }

        $hoSo->update($updateData);

        return redirect()->back()->with('success', 'Đã phân công nhóm vào Hội đồng bảo vệ thành công!');
    }

    /**
     * Giáo vụ lưu trữ hồ sơ khóa luận sau khi đã bảo vệ và nộp bản hoàn chỉnh
     */
    public function luuTru($id)
    {
        $hoSo = HoSoBaoVe::findOrFail($id);
        $hoSo->update([
            'TrangThai' => 'Đã lưu trữ',
        ]);

        return redirect()->back()->with('success', "Đã chuyển hồ sơ '{$hoSo->MaHoSo}' sang trạng thái lưu trữ chính thức!");
    }
}
