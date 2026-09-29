<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Models\HoSoBaoVe;
use App\Models\Nhom;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class XacNhanBaoVeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        // Lấy hồ sơ bảo vệ của các nhóm mà giảng viên này đang hướng dẫn
        $query = HoSoBaoVe::with(['nhom.truongNhom', 'nhom.thanhViens.sinhVien', 'deTai', 'tepHoSoBaoVes'])
            ->whereHas('deTai', fn($d) => $d->where('MaGV', $gv->MaGV));

        if ($request->filled('xac_nhan')) {
            $query->where('XacNhanGVHD', $request->xac_nhan);
        }

        $hoSos = $query->orderBy('created_at', 'desc')->paginate(10);

        $counts = [
            'cho_xac_nhan' => HoSoBaoVe::whereHas('deTai', fn($d) => $d->where('MaGV', $gv->MaGV))->where(fn($q) => $q->whereNull('XacNhanGVHD')->orWhere('XacNhanGVHD', 'Chưa duyệt')->orWhere('XacNhanGVHD', 'Chờ xác nhận'))->count(),
            'da_xac_nhan'  => HoSoBaoVe::whereHas('deTai', fn($d) => $d->where('MaGV', $gv->MaGV))->where('XacNhanGVHD', 'Đã duyệt')->count(),
            'khong_dat'    => HoSoBaoVe::whereHas('deTai', fn($d) => $d->where('MaGV', $gv->MaGV))->where('XacNhanGVHD', 'Không duyệt')->count(),
            'total'        => HoSoBaoVe::whereHas('deTai', fn($d) => $d->where('MaGV', $gv->MaGV))->count(),
        ];

        return view('giangvien.xacnhan_baove.index', compact('gv', 'hoSos', 'counts'));
    }

    public function xacNhan(Request $request, $id)
    {
        $request->validate([
            'HanhDong' => 'required|in:dong_y,khong_dong_y',
            'NhanXet'  => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        $hoSo = HoSoBaoVe::with(['deTai', 'nhom'])->findOrFail($id);

        if (!$hoSo->deTai || $hoSo->deTai->MaGV !== $gv->MaGV) {
            abort(403, 'Bạn không phải Giảng viên hướng dẫn của nhóm này.');
        }

        $isApprove = ($request->HanhDong === 'dong_y');

        $hoSo->update([
            'XacNhanGVHD' => $isApprove ? 'Đã duyệt' : 'Không duyệt',
            'NgayXacNhan' => now()->toDateString(),
            'TrangThai'   => $isApprove ? 'Đủ điều kiện bảo vệ' : 'Không đủ điều kiện',
            'GhiChu'      => $hoSo->GhiChu . ' [GVHD: ' . ($request->NhanXet ?: ($isApprove ? 'Đồng ý cho bảo vệ' : 'Chưa đạt yêu cầu')) . ']',
        ]);

        // Gửi thông báo cho nhóm
        ThongBaoService::guiDenNhom(
            $hoSo->MaNhom,
            $isApprove ? '✅ GVHD xác nhận đủ điều kiện bảo vệ!' : '⚠️ GVHD chưa duyệt đủ điều kiện bảo vệ',
            $isApprove 
                ? "Giảng viên hướng dẫn {$gv->HoTen} đã kiểm tra khóa luận và xác nhận nhóm đủ điều kiện bảo vệ. Giáo vụ Khoa sẽ sắp xếp Hội đồng bảo vệ."
                : "Giảng viên hướng dẫn {$gv->HoTen} có ý kiến về hồ sơ: " . ($request->NhanXet ?: 'Chưa đạt yêu cầu'),
            'Bảo vệ'
        );

        $msg = $isApprove 
            ? "Đã xác nhận nhóm '{$hoSo->nhom->TenNhom}' đủ điều kiện tham gia Hội đồng bảo vệ!" 
            : "Đã ghi nhận ý kiến không đồng ý cho bảo vệ đối với nhóm '{$hoSo->nhom->TenNhom}'.";

        return redirect()->back()->with('success', $msg);
    }
}
