<?php

namespace App\Http\Controllers\TruongKhoa;

use App\Http\Controllers\Controller;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\SinhVien;
use App\Models\Nhom;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\BaoCaoTienDo;
use App\Models\HocKy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TheoDoiController extends Controller
{
    private function getKhoa()
    {
        $user = Auth::user();
        if ($user && preg_match('/^TK_([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
            $khoa = Khoa::where('MaKhoa', $m[1])->first();
            if ($khoa) return $khoa;
        }

        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv) {
            $gv->loadMissing('boMon.khoa');
            if ($gv->boMon && $gv->boMon->khoa) {
                return $gv->boMon->khoa;
            }
        }

        return Khoa::where('MaKhoa', 'CNTT')->first() ?? Khoa::first();
    }

    public function index(Request $request)
    {
        $khoa = $this->getKhoa();
        $maKhoa = $khoa ? $khoa->MaKhoa : 'CNTT';

        $tab = $request->get('tab', 'tien_do');
        $boMons = BoMon::where('MaKhoa', $maKhoa)->orderBy('TenBoMon')->get();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        // 1. Nhóm & Tiến độ nộp báo cáo
        $queryNhoms = Nhom::with([
            'deTai.giangVien.boMon',
            'truongNhom.lop.nganh',
            'thanhViens.sinhVien',
            'dangKyDeTai.giangVienHuongDan',
            'baoCaos',
            'hoSoBaoVe.hoiDong',
        ])->whereHas('deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));

        if ($request->filled('MaBoMon')) {
            $maBM = $request->MaBoMon;
            $queryNhoms->whereHas('deTai.giangVien', fn($q) => $q->where('MaBoMon', $maBM));
        }

        if ($request->filled('MaHocKy')) {
            $maHK = $request->MaHocKy;
            $queryNhoms->whereHas('deTai', fn($q) => $q->where('MaHocKy', $maHK));
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $queryNhoms->where(function ($q) use ($s) {
                $q->where('TenNhom', 'like', "%{$s}%")
                  ->orWhere('MaNhom', 'like', "%{$s}%")
                  ->orWhereHas('deTai', fn($dq) => $dq->where('TenDeTai', 'like', "%{$s}%"))
                  ->orWhereHas('dangKyDeTai.giangVienHuongDan', fn($g) => $g->where('HoTen', 'like', "%{$s}%"));
            });
        }

        $nhoms = $queryNhoms->orderBy('created_at', 'desc')->paginate(10, ['*'], 'page_nhom')->withQueryString();

        // 2. Tình hình bảo vệ & Hội đồng chấm
        $queryHoSo = HoSoBaoVe::with([
            'nhom.truongNhom',
            'nhom.thanhViens.sinhVien',
            'deTai.giangVien.boMon',
            'hoiDong.thanhViens.giangVien',
            'ketQuaSinhViens',
        ])->whereHas('deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));

        if ($request->filled('MaBoMon')) {
            $maBM = $request->MaBoMon;
            $queryHoSo->whereHas('deTai.giangVien', fn($q) => $q->where('MaBoMon', $maBM));
        }

        if ($request->filled('search_baove')) {
            $sbv = trim($request->search_baove);
            $queryHoSo->where(function ($q) use ($sbv) {
                $q->where('MaHoSo', 'like', "%{$sbv}%")
                  ->orWhereHas('nhom', fn($nq) => $nq->where('TenNhom', 'like', "%{$sbv}%"))
                  ->orWhereHas('deTai', fn($dq) => $dq->where('TenDeTai', 'like', "%{$sbv}%"));
            });
        }

        $hoSoBaoVes = $queryHoSo->orderBy('created_at', 'desc')->paginate(10, ['*'], 'page_hoso')->withQueryString();

        // Thống kê tổng hợp toàn Khoa
        $baseDeTai = DeTai::whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));
        $baseNhom  = Nhom::whereHas('deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));

        $stats = [
            'total_nhom'        => (clone $baseNhom)->count(),
            'total_detai'       => (clone $baseDeTai)->count(),
            'detai_cong_bo'     => (clone $baseDeTai)->where('TrangThai', 'Đã công bố')->count(),
            'total_sv'          => SinhVien::where('MaKhoa', $maKhoa)->count(),
            'total_hoidong'     => HoiDong::count(),
            'total_hoso'        => HoSoBaoVe::whereHas('deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa))->count(),
            'hoso_du_dieu_kien' => HoSoBaoVe::whereHas('deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa))->where('XacNhanGVHD', 1)->count(),
            'total_baocao'      => BaoCaoTienDo::whereHas('nhom.deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa))->count(),
        ];

        return view('truongkhoa.theodoi.index', compact(
            'khoa',
            'nhoms',
            'hoSoBaoVes',
            'boMons',
            'hocKies',
            'stats',
            'tab'
        ));
    }
}
