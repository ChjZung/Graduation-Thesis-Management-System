<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Models\DeTai;
use App\Models\Nhom;
use App\Models\BaoCaoTienDo;
use App\Models\ChiTieuHuongDan;
use App\Models\LichGapHuongDan;
use App\Models\HoiDong;
use App\Models\HocKy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $giangVien = GiangVien::with('boMon.khoa')->where('MaTK', $user->MaTK)->firstOrFail();
        
        $hocKyHienTai = HocKy::where('TrangThai', 'Đang mở')
            ->orWhere('TrangThai', 'active')
            ->first() ?? HocKy::latest('MaHocKy')->first();

        // 1. Thống kê số đề tài đảm nhận
        $soDeTai = DeTai::where('MaGV', $giangVien->MaGV)->count();
        $deTaiDangMo = DeTai::where('MaGV', $giangVien->MaGV)
            ->whereIn('TrangThai', ['Đã duyệt', 'da_duyet', 'Đang thực hiện'])
            ->count();

        // 2. Nhóm hướng dẫn chính thức & chỉ tiêu
        $chiTieu = ChiTieuHuongDan::where('MaGV', $giangVien->MaGV)->first();
        $soNhomToiDa = $chiTieu ? $chiTieu->SoNhomToiDa : 5;

        $nhoms = Nhom::whereHas('phieuDangKys', function ($q) use ($giangVien) {
                $q->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])
                  ->whereHas('deTai', fn($d) => $d->where('MaGV', $giangVien->MaGV));
            })
            ->with([
                'deTai',
                'truongNhom',
                'thanhViens.sinhVien',
                'baoCaos' => fn($q) => $q->orderByDesc('LanBaoCao')->with('tomTatBaoCao'),
            ])
            ->get();

        $soNhomHD = $nhoms->count();

        // 3. Báo cáo chờ đánh giá
        $maDeTais = $nhoms->map(fn($n) => $n->deTai?->MaDeTai)->filter();
        $soBaoCaoChoDuyet = BaoCaoTienDo::whereIn('MaDeTai', $maDeTais)
            ->where('TrangThai', 'Chờ duyệt')
            ->count();

        // 4. Nhóm rủi ro trễ tiến độ (chưa nộp mốc mới nhất hoặc có báo cáo nộp lại)
        $soNhomTreHan = 0;
        foreach ($nhoms as $nhom) {
            $lastBaoCao = $nhom->baoCaos->first();
            if (!$lastBaoCao || $lastBaoCao->TrangThai === 'Yêu cầu nộp lại') {
                $soNhomTreHan++;
            }
        }

        // 5. Lịch làm việc & Hội đồng sắp tới
        $lichGaps = LichGapHuongDan::where('MaGV', $giangVien->MaGV)
            ->where('ThoiGianBatDau', '>=', now()->subDays(1))
            ->orderBy('ThoiGianBatDau')
            ->take(3)
            ->get();

        $hoiDongs = HoiDong::whereHas('thanhViens', fn($q) => $q->where('MaGV', $giangVien->MaGV))
            ->where('ThoiGianBatDau', '>=', now()->subDays(2))
            ->orderBy('ThoiGianBatDau')
            ->take(3)
            ->get();

        return view('giangvien.dashboard', compact(
            'giangVien',
            'hocKyHienTai',
            'soDeTai',
            'deTaiDangMo',
            'soNhomHD',
            'soNhomToiDa',
            'soBaoCaoChoDuyet',
            'soNhomTreHan',
            'nhoms',
            'lichGaps',
            'hoiDongs'
        ));
    }
}
