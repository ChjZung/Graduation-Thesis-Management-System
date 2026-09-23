<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KetQuaSinhVien;
use App\Models\HocKy;
use Illuminate\Http\Request;

class KetQuaController extends Controller
{
    public function index(Request $request)
    {
        $query = KetQuaSinhVien::with([
            'sinhVien.lop',
            'hocKy',
            'hoSoBaoVe.nhom',
            'hoSoBaoVe.deTai',
            'hoSoBaoVe.hoiDong'
        ]);

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('MaHoiDong')) {
            $query->whereHas('hoSoBaoVe', function ($q) use ($request) {
                $q->where('MaHoiDong', $request->MaHoiDong);
            });
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('MaSV', 'LIKE', "%{$s}%")
                  ->orWhereHas('sinhVien', function ($sv) use ($s) {
                      $sv->where('HoTen', 'LIKE', "%{$s}%")
                         ->orWhere('MaSoSinhVien', 'LIKE', "%{$s}%");
                  })
                  ->orWhereHas('hoSoBaoVe.nhom', fn($n) => $n->where('TenNhom', 'LIKE', "%{$s}%"))
                  ->orWhereHas('hoSoBaoVe.deTai', fn($dt) => $dt->where('TenDeTai', 'LIKE', "%{$s}%"));
            });
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

        // Stats calculation
        $allKq = (clone $query)->get();
        $totalSV = $allKq->count();
        $avgDiem10 = $totalSV > 0 ? round($allKq->avg('DiemTongKet'), 2) : 0;
        $avgDiem4 = $totalSV > 0 ? round($allKq->avg(fn($item) => $item->diem_he4), 2) : 0;
        $countXSGioi = $allKq->where('DiemTongKet', '>=', 8.0)->count();
        $countKha = $allKq->whereBetween('DiemTongKet', [7.0, 7.99])->count();
        $countDatTN = $allKq->where('DiemTongKet', '>=', 5.0)->count();

        $stats = [
            'total' => $totalSV,
            'avg_10' => $avgDiem10,
            'avg_4' => $avgDiem4,
            'count_xs_gioi' => $countXSGioi,
            'pct_xs_gioi' => $totalSV > 0 ? round(($countXSGioi / $totalSV) * 100, 1) : 0,
            'count_kha' => $countKha,
            'pct_kha' => $totalSV > 0 ? round(($countKha / $totalSV) * 100, 1) : 0,
            'count_dat_tn' => $countDatTN,
            'pct_dat_tn' => $totalSV > 0 ? round(($countDatTN / $totalSV) * 100, 1) : 100,
        ];

        $ketQuas = $query->orderBy('DiemTongKet', 'desc')->paginate(15);
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $hoiDongs = \App\Models\HoiDong::orderBy('TenHoiDong')->get();

        return view('admin.ketqua.index', compact('ketQuas', 'hocKies', 'hoiDongs', 'stats'));
    }

    /**
     * Xuất Bảng Điểm Tổng Kết Bảo Vệ Khóa Luận Tốt Nghiệp dạng CSV/Excel
     */
    public function exportExcel(Request $request)
    {
        $query = KetQuaSinhVien::with(['sinhVien.lop', 'hocKy']);

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        $ketQuas = $query->orderBy('DiemTongKet', 'desc')->get();

        $filename = "BANG_DIEM_KHOA_LUAN_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($ketQuas) {
            $file = fopen('php://output', 'w');
            // BOM UTF-8 cho Excel đọc tiếng Việt có dấu
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'STT',
                'MSSV',
                'Họ và Tên',
                'Lớp',
                'Học Kỳ',
                'Điểm GVHD (30%)',
                'Điểm Phản Biện (30%)',
                'Điểm Hội Đồng (40%)',
                'Điểm Tổng Kết',
                'Xếp Loại'
            ]);

            foreach ($ketQuas as $idx => $kq) {
                $sv = $kq->sinhVien;

                fputcsv($file, [
                    $idx + 1,
                    $sv->MaSoSinhVien ?? $sv->MaSV ?? '',
                    $sv->HoTen ?? '',
                    $sv->lop->TenLop ?? '',
                    $kq->hocKy->TenHocKy ?? $kq->MaHocKy,
                    $kq->DiemHuongDan,
                    $kq->DiemPhanBien,
                    $kq->DiemHoiDongTB,
                    $kq->DiemTongKet,
                    $kq->KetQua
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
