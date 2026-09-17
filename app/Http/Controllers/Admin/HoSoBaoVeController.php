<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HoSoBaoVe;
use App\Models\HoiDong;
use App\Models\GiangVien;
use App\Models\PhanCongPhanBien;
use App\Helpers\IdGenerator;
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
            'phanCongPhanBien.giangVien'
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
        $giangViens = GiangVien::orderBy('HoTen')->get();

        return view('admin.hoso_baove.index', compact(
            'hoSos',
            'hoiDongs',
            'giangViens',
            'tongSoHoSo',
            'choThamDinh',
            'duDieuKien',
            'daPhanCong'
        ));
    }

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
                'GhiChu'      => $hoSo->GhiChu . ($ghiChuAdmin ? " [Từ chối: {$ghiChuAdmin}]" : " [Từ chối: Tỷ lệ Turnitin không đạt]"),
            ]);
            return redirect()->back()->with('warning', 'Đã từ chối hồ sơ bảo vệ (không đủ điều kiện)!');
        }

        $hoSo->update([
            'TrangThai'   => 'Đủ điều kiện bảo vệ',
            'NgayXacNhan' => now()->toDateString(),
            'MaGVu'       => $maGVu,
            'GhiChu'      => $ghiChuAdmin ? ($hoSo->GhiChu . " [GV duyệt: {$ghiChuAdmin}]") : $hoSo->GhiChu,
        ]);

        return redirect()->back()->with('success', 'Đã phê duyệt hồ sơ: Đủ điều kiện bảo vệ!');
    }

    public function phanCong(Request $request, $id)
    {
        $request->validate([
            'MaHoiDong'     => 'required|exists:HoiDong,MaHoiDong',
            'MaGVPhanBien'  => 'nullable|exists:GiangVien,MaGV',
            'ThoiGianBaoVe' => 'nullable|date',
            'PhongBaoVe'    => 'nullable|string|max:100',
        ], [
            'MaHoiDong.required' => 'Vui lòng chọn Hội đồng bảo vệ.',
        ]);

        $hoSo = HoSoBaoVe::findOrFail($id);

        DB::transaction(function () use ($request, $hoSo) {
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

            if ($request->filled('MaGVPhanBien') && $hoSo->MaDeTai) {
                $pc = PhanCongPhanBien::where('MaDeTai', $hoSo->MaDeTai)->first();
                if ($pc) {
                    $pc->update([
                        'MaGV'         => $request->MaGVPhanBien,
                        'NgayPhanCong' => now()->toDateString(),
                        'TrangThai'    => 'Đã phân công',
                    ]);
                } else {
                    PhanCongPhanBien::create([
                        'MaPhanCong'   => IdGenerator::nextPhanCong(),
                        'VaiTro'       => 'Phản biện',
                        'NgayPhanCong' => now()->toDateString(),
                        'TrangThai'    => 'Đã phân công',
                        'MaGV'         => $request->MaGVPhanBien,
                        'MaDeTai'      => $hoSo->MaDeTai,
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Đã phân công Hội đồng và Giảng viên phản biện thành công!');
    }
}
