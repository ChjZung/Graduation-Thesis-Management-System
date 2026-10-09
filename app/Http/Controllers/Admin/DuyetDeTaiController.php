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
        $currentHocKy = HocKy::where('TrangThai', 'Đang diễn ra')->orWhere('TrangThai', '1')->first() ?? HocKy::orderBy('MaHocKy', 'desc')->first();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $khoas = \App\Models\Khoa::orderBy('TenKhoa')->get();
        $nganhs = Nganh::orderBy('TenNganh')->get();
        $hocPhans = ['Khóa luận tốt nghiệp', 'Đồ án tốt nghiệp', 'Đồ án chuyên ngành'];

        $selectedHocKy = $request->input('MaHocKy', $currentHocKy?->MaHocKy);

        // Danh sách Bộ môn (lọc theo Khoa nếu chọn Khoa)
        if ($request->filled('MaKhoa')) {
            $boMons = BoMon::where('MaKhoa', $request->MaKhoa)->orderBy('TenBoMon')->get();
        } else {
            $boMons = BoMon::orderBy('TenBoMon')->get();
        }

        // Danh sách Lĩnh vực duy nhất
        $linhVucs = DeTai::whereNotNull('LinhVuc')->where('LinhVuc', '!=', '')
            ->distinct()->pluck('LinhVuc')->sort()->values();
        if ($linhVucs->isEmpty()) {
            $linhVucs = collect([
                'Công nghệ Web & Cloud',
                'Trí tuệ nhân tạo (AI)',
                'Ứng dụng Di động (Mobile)',
                'Hệ thống Thông tin & ERP',
                'An toàn thông tin & Mạng',
                'Khoa học dữ liệu (Data Science)',
                'Khoa học máy tính & Thuật toán',
                'IoT & Hệ thống nhúng'
            ]);
        }

        // Tính toán các chỉ số thống kê (Counts) scoped theo bộ lọc đang chọn
        $baseCountQuery = DeTai::query();
        if ($selectedHocKy && $selectedHocKy !== 'ALL') {
            $baseCountQuery->where('MaHocKy', $selectedHocKy);
        }
        if ($request->filled('MaKhoa')) {
            $baseCountQuery->whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $request->MaKhoa));
        }
        if ($request->filled('MaBoMon')) {
            $baseCountQuery->whereHas('giangVien', fn($q) => $q->where('MaBoMon', $request->MaBoMon));
        }
        if ($request->filled('LinhVuc')) {
            $baseCountQuery->where('LinhVuc', $request->LinhVuc);
        }
        if ($request->filled('HocPhan')) {
            $baseCountQuery->where('HocPhan', $request->HocPhan);
        }

        $dangXetDuyetStatuses = [
            'Chờ duyệt cấp Bộ môn',
            'Đang phản biện đề cương',
            'Chờ duyệt cấp Khoa',
            'Đã nộp đề cương - Chờ phân công PB',
            'Đã phản biện - Chờ duyệt BM',
            'Đã phản biện - Chờ TBM duyệt đề cương'
        ];

        $daCongBoStatuses = [
            'Đã công bố',
            'Trưởng khoa đã duyệt',
            'Đã đăng ký',
            'Hoàn thành',
            'Đã duyệt'
        ];

        $tuChoiCanSuaStatuses = [
            'Yêu cầu chỉnh sửa',
            'Yêu cầu chỉnh sửa đề cương',
            'Từ chối',
            'Không đạt phản biện'
        ];

        $choDuyetBM   = (clone $baseCountQuery)->where('TrangThai', 'Chờ duyệt cấp Bộ môn')->count();
        $dangPB       = (clone $baseCountQuery)->where('TrangThai', 'Đang phản biện đề cương')->count();
        $choDuyetKhoa = (clone $baseCountQuery)->where('TrangThai', 'Chờ duyệt cấp Khoa')->count();
        $tkDaDuyet    = (clone $baseCountQuery)->where('TrangThai', 'Trưởng khoa đã duyệt')->count();
        $daCongBo     = (clone $baseCountQuery)->where('TrangThai', 'Đã công bố')->count();
        $daDangKy     = (clone $baseCountQuery)->where('TrangThai', 'Đã đăng ký')->count();
        $hoanThanh    = (clone $baseCountQuery)->where('TrangThai', 'Hoàn thành')->count();
        $yeuCauSua    = (clone $baseCountQuery)->where('TrangThai', 'Yêu cầu chỉnh sửa')->count();
        $tuChoi       = (clone $baseCountQuery)->where('TrangThai', 'Từ chối')->count();
        $total        = (clone $baseCountQuery)->count();

        $countDangXetDuyet = (clone $baseCountQuery)->whereIn('TrangThai', $dangXetDuyetStatuses)->count();
        $countDaCongBoTab  = (clone $baseCountQuery)->whereIn('TrangThai', $daCongBoStatuses)->count();
        $countTuChoiCanSua = (clone $baseCountQuery)->whereIn('TrangThai', $tuChoiCanSuaStatuses)->count();

        $counts = [
            'dang_xet_duyet'      => $countDangXetDuyet,
            'da_cong_bo_tab'      => $countDaCongBoTab,
            'tu_choi_can_sua'     => $countTuChoiCanSua,
            'cho_duyet_bm'        => $choDuyetBM,
            'dang_phan_bien'      => $dangPB,
            'cho_duyet_khoa'      => $choDuyetKhoa,
            'truong_khoa_da_duyet'=> $tkDaDuyet,
            'da_duyet'            => $tkDaDuyet,
            'da_cong_bo'          => $daCongBo,
            'da_dang_ky'          => $daDangKy,
            'hoan_thanh'          => $hoanThanh,
            'cho_duyet'           => ($choDuyetBM + $dangPB + $choDuyetKhoa),
            'yeu_cau_sua'         => $yeuCauSua,
            'tu_choi'             => $tuChoi,
            'total'               => $total,
        ];

        // ------------------------------------------------------------------
        // TÍNH TOÁN DỮ LIỆU THỐNG KÊ HỌC THUẬT CHO TAB "THỐNG KÊ ĐỀ TÀI"
        // ------------------------------------------------------------------
        $allScopedDeTais = (clone $baseCountQuery)->with([
            'giangVien.boMon',
            'phieuDangKys.nhom.thanhVienNhoms'
        ])->get();

        $statTotal = $allScopedDeTais->count();
        $statDaCongBo = 0;
        $statDangDangKy = 0;
        $statDaDuNhom = 0;
        $statDangChoDuyet = 0;
        $statDaHoanThanh = 0;

        $tongMoDangKy = 0;
        $coNhomDangKy = 0;
        $conCho = 0;
        $daDuSV = 0;
        $chuaCoSVDangKy = 0;

        $statusChartCounts = [
            'Đã công bố' => 0,
            'Đang đăng ký / Đủ nhóm' => 0,
            'Đang chờ duyệt' => 0,
            'Hoàn thành' => 0,
            'Khác' => 0,
        ];
        $boMonChartCounts = [];
        $linhVucChartCounts = [];
        $quyMoNhomChartCounts = [
            '1 sinh viên' => 0,
            '2 sinh viên' => 0,
            '3 sinh viên' => 0,
        ];

        foreach ($allScopedDeTais as $dt) {
            $maxSV = $dt->SoLuongSinhVienToiDa ?? 3;
            $regApproved = $dt->phieuDangKys ? $dt->phieuDangKys->where('TrangThai', 'Đã duyệt') : collect();
            $cntSV = 0;
            foreach ($regApproved as $p) {
                if ($p->nhom && $p->nhom->thanhVienNhoms) {
                    $cntSV += $p->nhom->thanhVienNhoms->count();
                } else {
                    $cntSV += 1;
                }
            }

            if ($dt->TrangThai === 'Hoàn thành') {
                $statDaHoanThanh++;
                $statusChartCounts['Hoàn thành']++;
            } elseif (in_array($dt->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt cấp Khoa', 'Đang phản biện đề cương', 'Yêu cầu chỉnh sửa'])) {
                $statDangChoDuyet++;
                $statusChartCounts['Đang chờ duyệt']++;
            } elseif ($dt->TrangThai === 'Từ chối') {
                $statusChartCounts['Khác']++;
            } elseif (in_array($dt->TrangThai, ['Đã công bố', 'Đã đăng ký', 'Trưởng khoa đã duyệt'])) {
                $tongMoDangKy++;
                if ($cntSV > 0) {
                    $coNhomDangKy++;
                    if ($cntSV >= $maxSV) {
                        $statDaDuNhom++;
                        $daDuSV++;
                    } else {
                        $statDangDangKy++;
                        $conCho++;
                    }
                    $statusChartCounts['Đang đăng ký / Đủ nhóm']++;
                } else {
                    $chuaCoSVDangKy++;
                    $conCho++;
                    $statDaCongBo++;
                    $statusChartCounts['Đã công bố']++;
                }
            } else {
                $statusChartCounts['Khác']++;
            }

            $bmName = $dt->giangVien?->boMon?->TenBoMon ?? 'Chưa phân bộ môn';
            $boMonChartCounts[$bmName] = ($boMonChartCounts[$bmName] ?? 0) + 1;

            $lvName = $dt->LinhVuc ?: 'Lĩnh vực khác';
            $linhVucChartCounts[$lvName] = ($linhVucChartCounts[$lvName] ?? 0) + 1;

            if ($maxSV == 1) {
                $quyMoNhomChartCounts['1 sinh viên']++;
            } elseif ($maxSV == 2) {
                $quyMoNhomChartCounts['2 sinh viên']++;
            } else {
                $quyMoNhomChartCounts['3 sinh viên']++;
            }
        }

        arsort($linhVucChartCounts);
        $topLinhVucChartCounts = array_slice($linhVucChartCounts, 0, 8, true);
        arsort($boMonChartCounts);

        $statsKPIs = [
            'total'          => $statTotal,
            'da_cong_bo'     => $statDaCongBo,
            'dang_dang_ky'   => $statDangDangKy,
            'da_du_nhom'     => $statDaDuNhom,
            'dang_cho_duyet' => $statDangChoDuyet,
            'da_hoan_thanh'  => $statDaHoanThanh,
        ];

        $dangKyStats = [
            'tong_cong_bo' => $tongMoDangKy,
            'co_nhom_dk'   => $coNhomDangKy,
            'con_cho'      => $conCho,
            'da_du_sv'     => $daDuSV,
            'chua_co_sv'   => $chuaCoSVDangKy,
        ];

        // Query danh sách đề tài
        $query = DeTai::with([
            'giangVien.boMon.khoa', 
            'nganh', 
            'hocKy', 
            'phieuDangKys.nhom.thanhVienNhoms.sinhVien.lop', 
            'phanCongPhanBiens.giangVien'
        ]);

        if ($selectedHocKy && $selectedHocKy !== 'ALL') {
            $query->where('MaHocKy', $selectedHocKy);
        }

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            if ($request->TrangThai === 'dang_xet_duyet') {
                $query->whereIn('TrangThai', $dangXetDuyetStatuses);
            } elseif ($request->TrangThai === 'da_cong_bo') {
                $query->whereIn('TrangThai', $daCongBoStatuses);
            } elseif ($request->TrangThai === 'tu_choi_can_sua') {
                $query->whereIn('TrangThai', $tuChoiCanSuaStatuses);
            } else {
                $query->where('TrangThai', $request->TrangThai);
            }
        }

        if ($request->filled('MaKhoa')) {
            $maKhoa = $request->MaKhoa;
            $query->whereHas('giangVien.boMon', function($gq) use ($maKhoa) {
                $gq->where('MaKhoa', $maKhoa);
            });
        }

        if ($request->filled('MaBoMon')) {
            $maBM = $request->MaBoMon;
            $query->whereHas('giangVien', function($gq) use ($maBM) {
                $gq->where('MaBoMon', $maBM);
            });
        }

        if ($request->filled('LinhVuc')) {
            $query->where('LinhVuc', $request->LinhVuc);
        }

        if ($request->filled('HocPhan')) {
            $query->where('HocPhan', $request->HocPhan);
        }

        if ($request->filled('MaNganh')) {
            $query->where('MaNganh', $request->MaNganh);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $cleanSearch = preg_replace('/[^A-Za-z0-9]/', '', $search);
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('TenDeTai', 'LIKE', "%{$search}%")
                  ->orWhere('MaDeTai', 'LIKE', "%{$search}%")
                  ->orWhere('LinhVuc', 'LIKE', "%{$search}%");
                if (!empty($cleanSearch)) {
                    $q->orWhere('MaDeTai', 'LIKE', "%{$cleanSearch}%");
                }
                $q->orWhereHas('giangVien', function($gq) use ($search, $cleanSearch) {
                    $gq->where('HoTen', 'LIKE', "%{$search}%");
                    if (!empty($cleanSearch)) {
                        $gq->orWhere('MaGV', 'LIKE', "%{$cleanSearch}%");
                    }
                });
            });
        }

        // Giới hạn 5 đề tài / 1 trang theo chuẩn thống nhất hệ thống
        $detais = $query->orderBy('created_at', 'desc')->paginate(5)->withQueryString();

        return view('admin.duyet_detai.index', compact(
            'detais', 
            'counts', 
            'statsKPIs',
            'statusChartCounts',
            'boMonChartCounts',
            'topLinhVucChartCounts',
            'quyMoNhomChartCounts',
            'dangKyStats',
            'hocKies', 
            'selectedHocKy',
            'khoas',
            'boMons', 
            'linhVucs',
            'nganhs', 
            'currentHocKy',
            'hocPhans'
        ));
    }

    /**
     * Giáo vụ công bố đề tài đã được Trưởng khoa phê duyệt chính thức
     */
    public function publish(Request $request)
    {
        $maHocKy = $request->input('MaHocKy');
        $query = DeTai::where('TrangThai', 'Trưởng khoa đã duyệt');

        if ($maHocKy) {
            $query->where('MaHocKy', $maHocKy);
        }

        $count = $query->count();
        if ($count === 0) {
            return redirect()->back()->with('warning', 'Không có đề tài nào ở trạng thái "Trưởng khoa đã duyệt" để công bố.');
        }

        // Cập nhật trạng thái thành Đã công bố
        $query->update([
            'TrangThai'   => 'Đã công bố',
            'NgayCongBo'  => now(),
        ]);

        // Tạo thông báo cho sinh viên
        try {
            $maTB = 'TB_' . strtoupper(Str::random(6));
            ThongBao::create([
                'MaThongBao'   => $maTB,
                'TieuDe'       => 'Công bố danh mục đề tài Khóa luận tốt nghiệp',
                'NoiDung'      => "Giáo vụ Khoa vừa công bố chính thức {$count} đề tài Khóa luận tốt nghiệp đã được Trưởng bộ môn và Trưởng khoa phê duyệt. Sinh viên/nhóm đủ điều kiện có thể xem chi tiết và đăng ký theo kế hoạch.",
                'LoaiThongBao' => 'Thông báo chung',
                'DoiTuongNhan' => 'Sinh viên',
                'NgayTao'      => now(),
                'TrangThai'    => 'da_gui',
                'MaGVu'        => 'GVU01',
            ]);
        } catch (\Throwable $e) {
            // bỏ qua lỗi nếu có
        }

        return redirect()->back()->with('success', "Đã công bố chính thức {$count} đề tài Khóa luận cho sinh viên đăng ký!");
    }

    public function export(Request $request)
    {
        $query = DeTai::with(['giangVien.boMon', 'nganh', 'hocKy']);

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            if ($request->TrangThai === 'dang_xet_duyet') {
                $query->whereIn('TrangThai', ['Chờ duyệt cấp Bộ môn', 'Đang phản biện đề cương', 'Chờ duyệt cấp Khoa', 'Đã nộp đề cương - Chờ phân công PB', 'Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương']);
            } elseif ($request->TrangThai === 'da_cong_bo' || $request->TrangThai === 'Đã công bố') {
                $query->whereIn('TrangThai', ['Đã công bố', 'Trưởng khoa đã duyệt', 'Đã đăng ký', 'Hoàn thành', 'Đã duyệt']);
            } elseif ($request->TrangThai === 'tu_choi_can_sua') {
                $query->whereIn('TrangThai', ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương', 'Từ chối', 'Không đạt phản biện']);
            } else {
                $query->where('TrangThai', $request->TrangThai);
            }
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

        $columns = ['Mã ĐT', 'Tên Đề Tài', 'Môn / Học Phần', 'Lĩnh Vực', 'Ngành', 'Giảng Viên Hướng Dẫn', 'Bộ Môn', 'Số SV Tối Đa', 'Học Kỳ', 'Trạng Thái', 'Có Đề Cương', 'Ngày Duyệt BM', 'Ngày Duyệt Khoa', 'Ngày Công Bố'];

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
                    $dt->SoLuongSinhVienToiDa ?? 3,
                    $dt->hocKy->TenHocKy ?? '',
                    $dt->TrangThai,
                    $dt->FileDeCuong ? 'Có' : 'Chưa có',
                    $dt->NgayDuyetBM ?? '',
                    $dt->NgayDuyetKhoa ?? '',
                    $dt->NgayCongBo ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
