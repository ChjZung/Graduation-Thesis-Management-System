<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeTai;
use App\Models\BoMon;
use App\Models\GiangVien;
use App\Models\Nganh;
use App\Models\HocKy;
use App\Models\DangKyDeTai;
use App\Models\ThongBao;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DuyetDeTaiController extends Controller
{
    public function index(Request $request)
    {
        $currentHocKy = HocKy::where('TrangThai', 1)->first() ?? HocKy::orderBy('MaHocKy', 'desc')->first();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $boMons = BoMon::orderBy('TenBoMon')->get();
        $nganhs = Nganh::orderBy('TenNganh')->get();

        $counts = [
            'cho_duyet'    => DeTai::where('TrangThai', 'Chờ duyệt')->count(),
            'da_duyet'     => DeTai::where('TrangThai', 'Đã duyệt')->count(),
            'da_cong_bo'   => DeTai::where('TrangThai', 'Đã công bố')->count(),
            'yeu_cau_sua'  => DeTai::where('TrangThai', 'Yêu cầu điều chỉnh')->count(),
            'tu_choi'      => DeTai::where('TrangThai', 'Từ chối')->count(),
            'total'        => DeTai::count(),
        ];

        // 1. Query danh sách đề tài xét duyệt
        $query = DeTai::with(['giangVien.boMon', 'nganh', 'hocKy', 'dangKyDeTais.nhom']);

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            $query->where('TrangThai', $request->TrangThai);
        } elseif (!$request->filled('TrangThai')) {
            $query->where('TrangThai', 'Chờ duyệt');
        }

        $hocPhans = ['Khóa luận tốt nghiệp', 'Đồ án tốt nghiệp', 'Đồ án chuyên ngành'];

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('HocPhan')) {
            $query->where('HocPhan', $request->HocPhan);
        }

        if ($request->filled('MaBoMon')) {
            $maBM = $request->MaBoMon;
            $query->whereHas('giangVien', function($gq) use ($maBM) {
                $gq->where('MaBoMon', $maBM);
            });
        }

        if ($request->filled('MaNganh')) {
            $query->where('MaNganh', $request->MaNganh);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('TenDeTai', 'LIKE', "%{$search}%")
                  ->orWhere('MaDeTai', 'LIKE', "%{$search}%")
                  ->orWhere('LinhVuc', 'LIKE', "%{$search}%")
                  ->orWhereHas('giangVien', function($gq) use ($search) {
                      $gq->where('HoTen', 'LIKE', "%{$search}%");
                  });
            });
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // 2. Thống kê theo Bộ Môn
        $thongKeBoMon = BoMon::withCount('giangViens')->get()->map(function($bm) {
            $gvIds = GiangVien::where('MaBoMon', $bm->MaBoMon)->pluck('MaGV');
            $dtQuery = DeTai::whereIn('MaGV', $gvIds);

            $bm->tong_detai = (clone $dtQuery)->count();
            $bm->cho_duyet = (clone $dtQuery)->where('TrangThai', 'Chờ duyệt')->count();
            $bm->da_duyet = (clone $dtQuery)->where('TrangThai', 'Đã duyệt')->count();
            $bm->da_cong_bo = (clone $dtQuery)->where('TrangThai', 'Đã công bố')->count();
            $bm->yeu_cau_sua = (clone $dtQuery)->where('TrangThai', 'Yêu cầu điều chỉnh')->count();
            $bm->tu_choi = (clone $dtQuery)->where('TrangThai', 'Từ chối')->count();
            return $bm;
        });

        // 3. Thống kê theo Giảng Viên
        $thongKeGiangVien = GiangVien::with(['boMon'])->get()->map(function($gv) {
            $dtQuery = DeTai::where('MaGV', $gv->MaGV);
            $gv->tong_detai = (clone $dtQuery)->count();
            $gv->cho_duyet = (clone $dtQuery)->where('TrangThai', 'Chờ duyệt')->count();
            $gv->da_duyet = (clone $dtQuery)->where('TrangThai', 'Đã duyệt')->count();
            $gv->da_cong_bo = (clone $dtQuery)->where('TrangThai', 'Đã công bố')->count();
            $gv->yeu_cau_sua = (clone $dtQuery)->where('TrangThai', 'Yêu cầu điều chỉnh')->count();
            $gv->so_nhom_nhan = DangKyDeTai::whereHas('deTai', fn($dq) => $dq->where('MaGV', $gv->MaGV))->where('TrangThai', 'Đã duyệt')->count();
            return $gv;
        })->sortByDesc('tong_detai')->values();

        // 4. Thống kê theo Ngành & Chuyên ngành
        $thongKeNganh = Nganh::with(['chuyenNganhs'])->get()->map(function($ng) {
            $dtQuery = DeTai::where('MaNganh', $ng->MaNganh);
            $ng->tong_detai = (clone $dtQuery)->count();
            $ng->cho_duyet = (clone $dtQuery)->where('TrangThai', 'Chờ duyệt')->count();
            $ng->da_duyet = (clone $dtQuery)->where('TrangThai', 'Đã duyệt')->count();
            $ng->da_cong_bo = (clone $dtQuery)->where('TrangThai', 'Đã công bố')->count();
            $ng->tong_chi_tieu_sv = (clone $dtQuery)->whereIn('TrangThai', ['Đã duyệt', 'Đã công bố'])->sum('SoLuongSinhVienToiDa');
            return $ng;
        });

        $deTaiToanKhoa = [
            'tong' => DeTai::whereNull('MaNganh')->count(),
            'da_cong_bo' => DeTai::whereNull('MaNganh')->where('TrangThai', 'Đã công bố')->count(),
            'da_duyet' => DeTai::whereNull('MaNganh')->where('TrangThai', 'Đã duyệt')->count(),
        ];

        return view('admin.duyet_detai.index', compact(
            'detais', 
            'counts', 
            'hocKies', 
            'boMons', 
            'nganhs', 
            'currentHocKy',
            'thongKeBoMon',
            'thongKeGiangVien',
            'thongKeNganh',
            'deTaiToanKhoa',
            'hocPhans'
        ));
    }

    public function approve($id)
    {
        $detai = DeTai::findOrFail($id);
        $detai->update([
            'TrangThai' => 'Đã duyệt',
            'NgayDuyet' => now(),
            'LyDoTuChoi' => null,
        ]);

        return redirect()->back()->with('success', "Đã phê duyệt đề tài '{$detai->TenDeTai}' thành công!");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'LyDoTuChoi' => 'required|string|max:500',
        ], [
            'LyDoTuChoi.required' => 'Vui lòng nhập lý do từ chối đề tài.',
        ]);

        $detai = DeTai::findOrFail($id);
        $detai->update([
            'TrangThai' => 'Từ chối',
            'LyDoTuChoi' => trim($request->LyDoTuChoi),
        ]);

        return redirect()->back()->with('success', "Đã từ chối đề tài '{$detai->TenDeTai}'.");
    }

    public function requestEdit(Request $request, $id)
    {
        $request->validate([
            'YeuCauSua' => 'required|string|max:500',
        ], [
            'YeuCauSua.required' => 'Vui lòng nhập nội dung yêu cầu điều chỉnh, bổ sung.',
        ]);

        $detai = DeTai::findOrFail($id);
        $detai->update([
            'TrangThai' => 'Yêu cầu điều chỉnh',
            'LyDoTuChoi' => trim($request->YeuCauSua),
        ]);

        return redirect()->back()->with('success', "Đã gửi yêu cầu điều chỉnh đề tài '{$detai->TenDeTai}' tới Giảng viên!");
    }

    public function publish(Request $request)
    {
        $maHocKy = $request->input('MaHocKy');
        $query = DeTai::where('TrangThai', 'Đã duyệt');

        if ($maHocKy) {
            $query->where('MaHocKy', $maHocKy);
        }

        $count = $query->count();
        if ($count === 0) {
            return redirect()->back()->with('warning', 'Không có đề tài nào ở trạng thái "Đã duyệt" để công bố.');
        }

        // Cập nhật trạng thái thành Đã công bố
        $query->update([
            'TrangThai' => 'Đã công bố',
        ]);

        // Tạo thông báo cho sinh viên
        try {
            $maTB = 'TB_' . strtoupper(Str::random(6));
            ThongBao::create([
                'MaThongBao'   => $maTB,
                'TieuDe'       => 'Công bố danh mục đề tài Khóa luận tốt nghiệp',
                'NoiDung'      => "Giáo vụ Khoa vừa công bố chính thức {$count} đề tài Khóa luận tốt nghiệp đã được phê duyệt. Các nhóm sinh viên đủ điều kiện có thể xem chi tiết và đăng ký ngay trong thời gian quy định.",
                'LoaiThongBao' => 'Thông báo chung',
                'DoiTuongNhan' => 'Sinh viên',
                'NgayTao'      => now(),
                'TrangThai'    => 'da_gui',
                'MaGVu'        => 'GVU01',
            ]);
        } catch (\Throwable $e) {
            // bỏ qua nếu phát sinh lỗi thông báo phụ
        }

        return redirect()->back()->with('success', "Đã công bố chính thức {$count} đề tài Khóa luận cho sinh viên đăng ký!");
    }

    public function export(Request $request)
    {
        $query = DeTai::with(['giangVien.boMon', 'nganh', 'hocKy']);

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            $query->where('TrangThai', $request->TrangThai);
        }
        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }
        if ($request->filled('HocPhan')) {
            $query->where('HocPhan', $request->HocPhan);
        }

        $detais = $query->orderBy('MaDeTai')->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=Danh_Sach_De_Tai_KLTN_" . date('Ymd_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Mã ĐT', 'Tên Đề Tài', 'Môn / Học Phần', 'Lĩnh Vực', 'Ngành', 'Giảng Viên Hướng Dẫn', 'Bộ Môn', 'Số SV Tối Đa', 'Học Kỳ', 'Trạng Thái', 'Có Đề Cương'];

        $callback = function() use ($detais, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($detais as $dt) {
                fputcsv($file, [
                    $dt->MaDeTai,
                    $dt->TenDeTai,
                    $dt->HocPhan ?? 'Khóa luận tốt nghiệp',
                    $dt->LinhVuc ?? '',
                    $dt->nganh->TenNganh ?? 'Toàn khoa',
                    $dt->giangVien->HoTen ?? '',
                    $dt->giangVien->boMon->TenBoMon ?? '',
                    $dt->SoLuongSinhVienToiDa ?? 2,
                    $dt->hocKy->TenHocKy ?? '',
                    $dt->TrangThai,
                    $dt->FileDeCuong ? 'Có' : 'Chưa có',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
