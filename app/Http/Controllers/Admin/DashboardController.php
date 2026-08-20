<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SinhVien;
use App\Models\GiangVien;
use App\Models\DeTai;
use App\Models\Nhom;
use App\Models\TaiKhoan;
use App\Models\HoiDong;
use App\Models\KetQuaSinhVien;
use App\Models\BaoCaoTienDo;
use App\Models\HoSoBaoVe;
use App\Models\DangKyDeTai;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Thống kê tổng quan ──
        $soSinhVien  = SinhVien::count();
        $soGiangVien = GiangVien::count();
        $soDeTai     = DeTai::count();
        $soNhom      = Nhom::count();
        $soHoiDong   = HoiDong::count();

        // ── Thống kê đề tài theo trạng thái ──
        $deTaiTheoTrangThai = DeTai::select('TrangThai', DB::raw('count(*) as total'))
            ->groupBy('TrangThai')
            ->pluck('total', 'TrangThai')
            ->toArray();

        // ── Thống kê đăng ký đề tài ──
        $dkDaDuyet  = DangKyDeTai::where('TrangThai', 'Đã duyệt')->count();
        $dkChoDuyet = DangKyDeTai::where('TrangThai', 'Chờ duyệt')->count();
        $dkTuChoi   = DangKyDeTai::where('TrangThai', 'Từ chối')->count();

        // ── Thống kê kết quả xếp loại sinh viên ──
        $ketQuaXepLoai = KetQuaSinhVien::select('KetQua', DB::raw('count(*) as total'))
            ->whereNotNull('KetQua')
            ->groupBy('KetQua')
            ->pluck('total', 'KetQua')
            ->toArray();

        // Đảm bảo đủ các nhãn xếp loại (kể cả khi = 0)
        $allXepLoai = ['Xuất sắc', 'Giỏi', 'Khá', 'Trung bình', 'Trung bình yếu', 'Không đạt'];
        $xepLoaiData = [];
        foreach ($allXepLoai as $xl) {
            $xepLoaiData[$xl] = $ketQuaXepLoai[$xl] ?? 0;
        }

        // ── Tiến độ 5 mốc (bao nhiêu nhóm đã đạt từng mốc) ──
        $mocProgress = [];
        for ($moc = 1; $moc <= 5; $moc++) {
            $mocProgress[$moc] = BaoCaoTienDo::where('LanBaoCao', $moc)
                ->where('TrangThai', 'Đạt')->count();
        }

        // ── Hồ sơ bảo vệ ──
        $hoSoCho      = HoSoBaoVe::where('TrangThai', 'Chờ xác nhận')->count();
        $hoSoPhanCong = HoSoBaoVe::where('TrangThai', 'Đã phân công')->count();
        $hoSoTong     = HoSoBaoVe::count();

        // ── 5 nhóm mới nhất ──
        $nhomMoiNhat = Nhom::with(['truongNhom', 'deTai'])
            ->orderBy('created_at', 'desc')->limit(5)->get();

        // ── Biểu đồ trạng thái nhóm ──
        $trangThaiNhom = Nhom::select('TrangThai', DB::raw('count(*) as total'))
            ->groupBy('TrangThai')->pluck('total', 'TrangThai')->toArray();

        return view('admin.dashboard', compact(
            'soSinhVien', 'soGiangVien', 'soDeTai', 'soNhom', 'soHoiDong',
            'deTaiTheoTrangThai', 'dkDaDuyet', 'dkChoDuyet', 'dkTuChoi',
            'xepLoaiData', 'mocProgress',
            'hoSoCho', 'hoSoPhanCong', 'hoSoTong',
            'nhomMoiNhat', 'trangThaiNhom'
        ));
    }

    public function toggleLockAccount($id)
    {
        $tk = TaiKhoan::findOrFail($id);
        if ($tk->MaVaiTro == 1 && $tk->MaTK == auth()->user()->MaTK) {
            return redirect()->back()->withErrors('Không thể tự khóa tài khoản Admin đang sử dụng!');
        }
        $tk->TrangThai = !$tk->TrangThai;
        $tk->save();
        $msg = $tk->TrangThai ? 'Mở khóa tài khoản thành công!' : 'Đã khóa tài khoản thành công!';
        return redirect()->back()->with('success', $msg);
    }
}
