<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HoSoBaoVe;
use App\Models\HoiDong;
use App\Models\Nhom;
use App\Models\GiangVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HoSoBaoVeController extends Controller
{
    public function index(Request $request)
    {
        $query = HoSoBaoVe::with(['nhom.truongNhom', 'nhom.deTai', 'hoiDong', 'giangVienPhanBien']);

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai);
        }

        $hoSos = $query->orderBy('created_at', 'desc')->paginate(10);
        $hoiDongs = HoiDong::orderBy('TenHoiDong')->get();
        $giangViens = GiangVien::orderBy('HoTen')->get();

        return view('admin.hoso_baove.index', compact('hoSos', 'hoiDongs', 'giangViens'));
    }

    public function phanCong(Request $request, $id)
    {
        $request->validate([
            'MaHoiDong'    => 'required|exists:hoi_dongs,MaHoiDong',
            'MaGVPhanBien' => 'nullable|exists:giang_viens,MaGV',
        ], [
            'MaHoiDong.required' => 'Vui lòng chọn Hội đồng.',
        ]);

        $hoSo = HoSoBaoVe::findOrFail($id);
        $hoSo->update([
            'MaHoiDong'    => $request->MaHoiDong,
            'MaGVPhanBien' => $request->MaGVPhanBien,
            'TrangThai'    => 'Đã phân công',
        ]);


        return redirect()->back()->with('success', 'Đã phân công Hội đồng và Giảng viên Phản biện thành công!');
    }

    public function xacNhan($id)
    {
        $hoSo = HoSoBaoVe::findOrFail($id);
        $hoSo->update([
            'XacNhanGVHD'    => true,
            'NgayXacNhanGVHD'=> now()->toDateString(),
            'TrangThai'      => 'Đã xác nhận',
        ]);
        return redirect()->back()->with('success', 'Đã xác nhận hồ sơ bảo vệ hợp lệ!');
    }
}
