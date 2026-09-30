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
            $bm = BoMon::where('MaBoMon', $gv->MaBoMon)->first();
            if ($bm) return $bm;
        }

        if ($user && preg_match('/^TBM_[A-Z0-9]+_([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
            $bm = BoMon::where('MaBoMon', $m[1])->first();
            if ($bm) return $bm;
        }

        return BoMon::where('TruongBoMon', 'like', '%' . ($gv->HoTen ?? '') . '%')->first() ?? BoMon::first();
    }

    public function index(Request $request)
    {
        $boMon = $this->getBoMon();
        $maBoMon = $boMon ? $boMon->MaBoMon : 'CNPM';

        // Lấy danh sách GV thuộc Bộ môn kèm thống kê đề tài và nhóm hướng dẫn
        $giangViens = GiangVien::where('MaBoMon', $maBoMon)
            ->withCount([
                'deTais',
                'deTais as de_tais_cong_bo_count' => fn($q) => $q->where('TrangThai', 'Đã công bố'),
            ])
            ->orderBy('HoTen')
            ->get();

        $gvIds = $giangViens->pluck('MaGV');

        // Gắn số nhóm đang hướng dẫn cho từng GV
        foreach ($giangViens as $gvItem) {
            $gvItem->nhoms_count = Nhom::whereHas('deTai', fn($q) => $q->where('MaGV', $gvItem->MaGV))->count();
        }

        // Danh sách nhóm sinh viên thuộc đề tài của GV trong Bộ môn
        $queryNhoms = Nhom::with([
            'deTai.giangVien',
            'truongNhom.lop',
            'thanhViens.sinhVien',
            'dangKyDeTai',
            'baoCaos.mocThoiGian',
            'hoSoBaoVe.hoiDong',
            'hoSoBaoVe.tepHoSoBaoVes',
        ])->whereHas('deTai', fn($q) => $q->whereIn('MaGV', $gvIds));

        // Lọc theo Giảng viên hướng dẫn
        if ($request->filled('MaGV')) {
            $queryNhoms->whereHas('deTai', fn($q) => $q->where('MaGV', $request->MaGV));
        }

        // Lọc theo Trạng thái nhóm
        if ($request->filled('TrangThaiNhom')) {
            $queryNhoms->where('TrangThai', $request->TrangThaiNhom);
        }

        // Tìm kiếm tự do
        if ($request->filled('search')) {
            $s = trim($request->search);
            $queryNhoms->where(function ($q) use ($s) {
                $q->where('TenNhom', 'like', "%{$s}%")
                  ->orWhere('MaNhom', 'like', "%{$s}%")
                  ->orWhereHas('deTai', fn($dq) => $dq->where('TenDeTai', 'like', "%{$s}%"))
                  ->orWhereHas('truongNhom', fn($sq) => $sq->where('HoTen', 'like', "%{$s}%")->orWhere('MaSV', 'like', "%{$s}%"));
            });
        }

        $nhoms = $queryNhoms->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Thống kê tiến độ các nhóm
        $stats = [
            'total_nhom'      => Nhom::whereHas('deTai', fn($q) => $q->whereIn('MaGV', $gvIds))->count(),
            'total_gv'        => $giangViens->count(),
            'total_detai'     => DeTai::whereIn('MaGV', $gvIds)->count(),
            'detai_cong_bo'   => DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Đã công bố')->count(),
            'nhom_hoan_thanh' => Nhom::whereHas('deTai', fn($q) => $q->whereIn('MaGV', $gvIds))->where('TrangThai', 'Hoàn thành')->count(),
            'nhom_dang_thuc_hien' => Nhom::whereHas('deTai', fn($q) => $q->whereIn('MaGV', $gvIds))->where('TrangThai', '!=', 'Hoàn thành')->count(),
        ];

        return view('truongbomon.theodoi.index', compact('boMon', 'giangViens', 'nhoms', 'stats'));
    }
}
