<?php

namespace App\Http\Controllers\TruongBoMon;

use App\Http\Controllers\Controller;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\Nhom;
use App\Models\HocKy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TheoDoiController extends Controller
{
    private function getBoMon()
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv && $gv->MaBoMon) {
            return BoMon::where('MaBoMon', $gv->MaBoMon)->first() ?? BoMon::first();
        }
        return BoMon::where('TruongBoMon', 'like', '%' . ($gv->HoTen ?? '') . '%')->first() ?? BoMon::first();
    }

    public function index(Request $request)
    {
        $boMon = $this->getBoMon();
        $maBoMon = $boMon ? $boMon->MaBoMon : 'CNPM';

        $giangViens = GiangVien::where('MaBoMon', $maBoMon)->withCount(['deTais'])->get();
        $gvIds = $giangViens->pluck('MaGV');

        // Danh sách nhóm sinh viên thuộc đề tài của GV trong Bộ môn
        $queryNhoms = Nhom::with([
            'deTai.giangVien',
            'truongNhom.lop',
            'thanhViens.sinhVien',
            'dangKyDeTai',
            'baoCaos',
            'hoSoBaoVe.hoiDong',
        ])->whereHas('deTai', fn($q) => $q->whereIn('MaGV', $gvIds));

        if ($request->filled('MaGV')) {
            $queryNhoms->whereHas('deTai', fn($q) => $q->where('MaGV', $request->MaGV));
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $queryNhoms->where(function ($q) use ($s) {
                $q->where('TenNhom', 'like', "%{$s}%")
                  ->orWhere('MaNhom', 'like', "%{$s}%")
                  ->orWhereHas('deTai', fn($dq) => $dq->where('TenDeTai', 'like', "%{$s}%"))
                  ->orWhereHas('truongNhom', fn($sq) => $sq->where('HoTen', 'like', "%{$s}%")->orWhere('MaSV', 'like', "%{$s}%"));
            });
        }

        $nhoms = $queryNhoms->paginate(10)->withQueryString();

        // Thống kê tiến độ các nhóm
        $stats = [
            'total_nhom'      => Nhom::whereHas('deTai', fn($q) => $q->whereIn('MaGV', $gvIds))->count(),
            'total_gv'        => $giangViens->count(),
            'total_detai'     => DeTai::whereIn('MaGV', $gvIds)->count(),
            'detai_cong_bo'   => DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Đã công bố')->count(),
            'nhom_hoan_thanh' => Nhom::whereHas('deTai', fn($q) => $q->whereIn('MaGV', $gvIds))->where('TrangThai', 'Hoàn thành')->count(),
        ];

        return view('truongbomon.theodoi.index', compact('boMon', 'giangViens', 'nhoms', 'stats'));
    }
}
