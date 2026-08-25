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
        $query = KetQuaSinhVien::with(['sinhVien.lop', 'hocKy']);

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        $ketQuas = $query->orderBy('DiemTongKet', 'desc')->paginate(15);
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        return view('admin.ketqua.index', compact('ketQuas', 'hocKies'));
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
