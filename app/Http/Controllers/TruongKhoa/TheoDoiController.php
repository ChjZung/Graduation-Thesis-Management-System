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
use App\Models\KetQuaSinhVien;
use App\Models\HocKy;
use Illuminate\Http\Request;

class TheoDoiController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'tien_do');
        $boMons = BoMon::orderBy('TenBoMon')->get();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        // 1. Theo dõi nhóm & tiến độ
        $queryNhoms = Nhom::with([
            'deTai.giangVien.boMon',
            'truongNhom.lop.nganh',
            'thanhViens.sinhVien',
            'dangKyDeTai.giangVienHuongDan',
            'baoCaos',
            'hoSoBaoVe.hoiDong',
        ]);

        if ($request->filled('MaBoMon')) {
            $maBM = $request->MaBoMon;
            $queryNhoms->whereHas('deTai.giangVien', fn($q) => $q->where('MaBoMon', $maBM));
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $queryNhoms->where(function ($q) use ($s) {
                $q->where('TenNhom', 'like', "%{$s}%")
                  ->orWhere('MaNhom', 'like', "%{$s}%")
                  ->orWhereHas('deTai', fn($dq) => $dq->where('TenDeTai', 'like', "%{$s}%"));
            });
        }

        $nhoms = $queryNhoms->paginate(12, ['*'], 'page_nhom')->withQueryString();

        // 2. Tra cứu kết quả toàn khoa
        $queryKetQua = KetQuaSinhVien::with([
            'sinhVien.lop.nganh',
            'hocKy',
            'hoSoBaoVe.nhom',
            'hoSoBaoVe.deTai.giangVien.boMon',
            'hoSoBaoVe.hoiDong'
        ]);

        if ($request->filled('MaHocKy')) {
            $queryKetQua->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('search_kq')) {
            $skq = trim($request->search_kq);
            $queryKetQua->where(function ($q) use ($skq) {
                $q->where('MaSV', 'like', "%{$skq}%")
                  ->orWhereHas('sinhVien', fn($s) => $s->where('HoTen', 'like', "%{$skq}%"));
            });
        }

        $ketQuas = $queryKetQua->orderBy('DiemTongKet', 'desc')->paginate(15, ['*'], 'page_kq')->withQueryString();

        // Thống kê tổng hợp
        $stats = [
            'total_nhom'      => Nhom::count(),
            'total_detai'     => DeTai::count(),
            'detai_cong_bo'   => DeTai::where('TrangThai', 'Đã công bố')->count(),
            'total_hoidong'   => HoiDong::count(),
            'total_sv_ketqua' => KetQuaSinhVien::count(),
            'sv_xuat_sac'     => KetQuaSinhVien::where('DiemTongKet', '>=', 9.0)->count(),
            'sv_gioi'         => KetQuaSinhVien::whereBetween('DiemTongKet', [8.0, 8.99])->count(),
            'sv_kha'          => KetQuaSinhVien::whereBetween('DiemTongKet', [7.0, 7.99])->count(),
        ];

        return view('truongkhoa.theodoi.index', compact('nhoms', 'ketQuas', 'boMons', 'hocKies', 'stats', 'tab'));
    }
}
