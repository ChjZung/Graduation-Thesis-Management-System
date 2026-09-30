<?php

namespace App\Http\Controllers\TruongKhoa;

use App\Http\Controllers\Controller;
use App\Models\Khoa;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\KeHoachKhoaLuan;
use App\Models\MocThoiGianKhoaLuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeHoachController extends Controller
{
    private function getKhoa()
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv) {
            $gv->loadMissing('boMon.khoa');
            if ($gv->boMon && $gv->boMon->khoa) {
                return $gv->boMon->khoa;
            }
        }

        if ($user && preg_match('/^TK_([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
            $khoa = Khoa::where('MaKhoa', $m[1])->first();
            if ($khoa) return $khoa;
        }

        return Khoa::first();
    }

    public function index(Request $request)
    {
        $khoa = $this->getKhoa();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        $query = KeHoachKhoaLuan::with(['hocKy', 'mocThoiGians', 'quyDinhs', 'giaoVu']);

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('TenKeHoach', 'like', "%{$s}%")
                  ->orWhere('NoiDung', 'like', "%{$s}%");
            });
        }

        $keHoachs = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Thống kê các mốc thời gian sắp tới
        $upcomingMocs = MocThoiGianKhoaLuan::with('keHoachKhoaLuan.hocKy')
            ->where('NgayKetThuc', '>=', now()->toDateString())
            ->orderBy('NgayBatDau')
            ->limit(5)
            ->get();

        return view('truongkhoa.kehoach.index', compact('khoa', 'keHoachs', 'hocKies', 'upcomingMocs'));
    }
}
