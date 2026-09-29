<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DangKyDeTai;
use App\Models\HocKy;
use Illuminate\Http\Request;

class DuyetDangKyDeTaiController extends Controller
{
    /**
     * Giáo vụ xem, tra cứu và theo dõi danh sách đăng ký đề tài của các nhóm sinh viên.
     * Lưu ý: Việc phê duyệt/từ chối đơn đăng ký do Giảng viên hướng dẫn (GVHD) thực hiện.
     */
    public function index(Request $request)
    {
        $currentHocKy = HocKy::where('TrangThai', 1)->first() ?? HocKy::orderBy('MaHocKy', 'desc')->first();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        $counts = [
            'cho_duyet' => DangKyDeTai::where('TrangThai', 'Chờ duyệt')->count(),
            'da_duyet'  => DangKyDeTai::where('TrangThai', 'Đã duyệt')->count(),
            'tu_choi'   => DangKyDeTai::where('TrangThai', 'Từ chối')->count(),
            'total'     => DangKyDeTai::count(),
        ];

        $query = DangKyDeTai::with([
            'nhom.truongNhom.lop',
            'nhom.sinhViens.lop',
            'deTai.giangVien.boMon',
            'deTai.hocKy',
        ]);

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            $query->where('TrangThai', $request->TrangThai);
        }

        if ($request->filled('MaHocKy')) {
            $maHK = $request->MaHocKy;
            $query->whereHas('deTai', function($dq) use ($maHK) {
                $dq->where('MaHocKy', $maHK);
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('MaDangKy', 'LIKE', "%{$search}%")
                  ->orWhere('MaNhom', 'LIKE', "%{$search}%")
                  ->orWhere('MaDeTai', 'LIKE', "%{$search}%")
                  ->orWhereHas('nhom', function($nq) use ($search) {
                      $nq->where('TenNhom', 'LIKE', "%{$search}%")
                         ->orWhereHas('sinhViens', function($sq) use ($search) {
                             $sq->where('HoTen', 'LIKE', "%{$search}%")
                                ->orWhere('MaSV', 'LIKE', "%{$search}%");
                         });
                  })
                  ->orWhereHas('deTai', function($dq) use ($search) {
                      $dq->where('TenDeTai', 'LIKE', "%{$search}%")
                         ->orWhereHas('giangVien', function($gq) use ($search) {
                             $gq->where('HoTen', 'LIKE', "%{$search}%");
                         });
                  });
            });
        }

        $dangKys = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.duyet_dangky.index', compact('dangKys', 'counts', 'hocKies', 'currentHocKy'));
    }

    /**
     * Xuất danh sách đăng ký đề tài ra file CSV
     */
    public function export(Request $request)
    {
        $query = DangKyDeTai::with([
            'nhom.truongNhom.lop',
            'nhom.sinhViens',
            'deTai.giangVien.boMon',
            'deTai.hocKy',
        ]);

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            $query->where('TrangThai', $request->TrangThai);
        }

        if ($request->filled('MaHocKy')) {
            $maHK = $request->MaHocKy;
            $query->whereHas('deTai', function($dq) use ($maHK) {
                $dq->where('MaHocKy', $maHK);
            });
        }

        $dangKys = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=Danh_Sach_Dang_Ky_De_Tai_" . date('Ymd_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Mã Đăng Ký', 'Mã Nhóm', 'Tên Nhóm', 'Trưởng Nhóm', 'Số Thành Viên', 
            'Mã Đề Tài', 'Tên Đề Tài', 'GV Hướng Dẫn', 'Bộ Môn', 
            'Trạng Thái GVHD', 'Ngày Đăng Ký', 'Ngày Duyệt', 'Lý Do Từ Chối'
        ];

        $callback = function() use ($dangKys, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($dangKys as $dk) {
                fputcsv($file, [
                    $dk->MaDangKy,
                    $dk->MaNhom,
                    $dk->nhom->TenNhom ?? $dk->MaNhom,
                    $dk->nhom->truongNhom->HoTen ?? 'Chưa rõ',
                    $dk->nhom->sinhViens->count() ?: 1,
                    $dk->MaDeTai,
                    $dk->deTai->TenDeTai ?? 'Chưa rõ',
                    $dk->deTai->giangVien->HoTen ?? 'Chưa rõ',
                    $dk->deTai->giangVien->boMon->TenBoMon ?? '',
                    $dk->TrangThai,
                    $dk->created_at ? $dk->created_at->format('d/m/Y H:i') : ($dk->NgayDangKy ?? ''),
                    $dk->NgayDuyet ?? '',
                    $dk->LyDoTuChoi ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
