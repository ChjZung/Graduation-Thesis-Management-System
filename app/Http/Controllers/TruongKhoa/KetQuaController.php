<?php

namespace App\Http\Controllers\TruongKhoa;

use App\Http\Controllers\Controller;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\KetQuaSinhVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KetQuaController extends Controller
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

        $boMons = BoMon::where('MaKhoa', $maKhoa)->orderBy('TenBoMon')->get();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        $query = KetQuaSinhVien::with([
            'sinhVien.lop.nganh',
            'hocKy',
            'hoSoBaoVe.nhom',
            'hoSoBaoVe.deTai.giangVien.boMon',
            'hoSoBaoVe.hoiDong'
        ])->where(function ($sub) use ($maKhoa) {
            $sub->whereHas('sinhVien', fn($q) => $q->where('MaKhoa', $maKhoa))
                ->orWhereHas('hoSoBaoVe.deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));
        });

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('MaBoMon')) {
            $maBM = $request->MaBoMon;
            $query->whereHas('hoSoBaoVe.deTai.giangVien', fn($q) => $q->where('MaBoMon', $maBM));
        }

        if ($request->filled('xep_loai')) {
            $xl = $request->xep_loai;
            if ($xl === 'XuatSac') {
                $query->where('DiemTongKet', '>=', 9.0);
            } elseif ($xl === 'Gioi') {
                $query->whereBetween('DiemTongKet', [8.0, 8.99]);
            } elseif ($xl === 'Kha') {
                $query->whereBetween('DiemTongKet', [7.0, 7.99]);
            } elseif ($xl === 'TrungBinh') {
                $query->whereBetween('DiemTongKet', [5.0, 6.99]);
            } elseif ($xl === 'KhongDat') {
                $query->where('DiemTongKet', '<', 5.0);
            }
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('MaSV', 'LIKE', "%{$s}%")
                  ->orWhereHas('sinhVien', fn($sv) => $sv->where('HoTen', 'LIKE', "%{$s}%"))
                  ->orWhereHas('hoSoBaoVe.deTai', fn($dt) => $dt->where('TenDeTai', 'LIKE', "%{$s}%"));
            });
        }

        $ketQuas = $query->orderBy('DiemTongKet', 'desc')->paginate(12)->withQueryString();

        // Thống kê xếp loại toàn khoa
        $baseQuery = KetQuaSinhVien::where(function ($sub) use ($maKhoa) {
            $sub->whereHas('sinhVien', fn($q) => $q->where('MaKhoa', $maKhoa))
                ->orWhereHas('hoSoBaoVe.deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));
        });

        if ($request->filled('MaHocKy')) {
            $baseQuery->where('MaHocKy', $request->MaHocKy);
        }

        $stats = [
            'total'        => (clone $baseQuery)->count(),
            'xuat_sac'     => (clone $baseQuery)->where('DiemTongKet', '>=', 9.0)->count(),
            'gioi'         => (clone $baseQuery)->whereBetween('DiemTongKet', [8.0, 8.99])->count(),
            'kha'          => (clone $baseQuery)->whereBetween('DiemTongKet', [7.0, 7.99])->count(),
            'trung_binh'   => (clone $baseQuery)->whereBetween('DiemTongKet', [5.0, 6.99])->count(),
            'khong_dat'    => (clone $baseQuery)->where('DiemTongKet', '<', 5.0)->count(),
        ];

        return view('truongkhoa.ketqua.index', compact('khoa', 'ketQuas', 'boMons', 'hocKies', 'stats'));
    }

    public function exportExcel(Request $request)
    {
        $khoa = $this->getKhoa();
        $maKhoa = $khoa ? $khoa->MaKhoa : 'CNTT';

        $query = KetQuaSinhVien::with([
            'sinhVien.lop.nganh',
            'hocKy',
            'hoSoBaoVe.nhom',
            'hoSoBaoVe.deTai.giangVien.boMon',
            'hoSoBaoVe.hoiDong'
        ])->where(function ($sub) use ($maKhoa) {
            $sub->whereHas('sinhVien', fn($q) => $q->where('MaKhoa', $maKhoa))
                ->orWhereHas('hoSoBaoVe.deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));
        });

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('MaBoMon')) {
            $maBM = $request->MaBoMon;
            $query->whereHas('hoSoBaoVe.deTai.giangVien', fn($q) => $q->where('MaBoMon', $maBM));
        }

        if ($request->filled('xep_loai')) {
            $xl = $request->xep_loai;
            if ($xl === 'XuatSac') {
                $query->where('DiemTongKet', '>=', 9.0);
            } elseif ($xl === 'Gioi') {
                $query->whereBetween('DiemTongKet', [8.0, 8.99]);
            } elseif ($xl === 'Kha') {
                $query->whereBetween('DiemTongKet', [7.0, 7.99]);
            } elseif ($xl === 'TrungBinh') {
                $query->whereBetween('DiemTongKet', [5.0, 6.99]);
            } elseif ($xl === 'KhongDat') {
                $query->where('DiemTongKet', '<', 5.0);
            }
        }

        $list = $query->orderBy('DiemTongKet', 'desc')->get();

        $filename = 'KetQua_KhoaLuan_' . $maKhoa . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($list) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, [
                'STT',
                'Mã SV',
                'Họ và Tên',
                'Lớp',
                'Bộ môn',
                'Đề tài khóa luận',
                'Điểm HD',
                'Điểm PB',
                'Điểm HĐ',
                'Điểm Tổng Kết',
                'Xếp Loại',
                'Ghi Chú',
            ]);

            foreach ($list as $index => $row) {
                fputcsv($handle, [
                    $index + 1,
                    $row->MaSV,
                    $row->sinhVien->HoTen ?? 'N/A',
                    $row->sinhVien->lop->TenLop ?? ($row->sinhVien->MaLop ?? 'N/A'),
                    $row->hoSoBaoVe->deTai->giangVien->boMon->TenBoMon ?? 'N/A',
                    $row->hoSoBaoVe->deTai->TenDeTai ?? 'N/A',
                    $row->DiemHuongDan !== null ? number_format($row->DiemHuongDan, 2) : '-',
                    $row->DiemPhanBien !== null ? number_format($row->DiemPhanBien, 2) : '-',
                    $row->DiemHoiDongTB !== null ? number_format($row->DiemHoiDongTB, 2) : '-',
                    $row->DiemTongKet !== null ? number_format($row->DiemTongKet, 2) : '-',
                    $row->KetQua ?? ($row->xep_loai ?? 'N/A'),
                    $row->NhanXetChung ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
