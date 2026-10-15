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
        
        // Danh sách Học phần động từ CSDL
        $hocPhans = \App\Models\HocPhan::orderBy('TenHocPhan')->get();
        $danhSachTrangThai = [
            'Chờ duyệt cấp Bộ môn',
            'Đang phản biện đề cương',
            'Chờ duyệt cấp Khoa',
            'Trưởng khoa đã duyệt',
            'Đã công bố',
            'Đã đăng ký',
            'Hoàn thành',
            'Yêu cầu chỉnh sửa',
            'Từ chối',
        ];

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
            $maBM = $request->MaBoMon;
            $cleanBM = str_replace('BM_', '', $maBM);
            $bmList = array_unique([$maBM, $cleanBM, 'BM_' . $cleanBM]);
            $baseCountQuery->where(function($q) use ($bmList) {
                $q->whereHas('giangVien', fn($gq) => $gq->whereIn('MaBoMon', $bmList))
                  ->orWhereHas('hocPhanRef', fn($hq) => $hq->whereIn('MaBoMon', $bmList));
            });
        }
        if ($request->filled('LinhVuc')) {
            $baseCountQuery->where('LinhVuc', $request->LinhVuc);
        }
        if ($request->filled('HocPhan')) {
            $hpVal = $request->HocPhan;
            $baseCountQuery->where(function($q) use ($hpVal) {
                $q->where('HocPhan', $hpVal)
                  ->orWhere('MaHocPhan', $hpVal)
                  ->orWhereHas('hocPhanRef', fn($hr) => $hr->where('MaHocPhan', $hpVal)->orWhere('TenHocPhan', $hpVal));
            });
        }
        if ($request->filled('MaNganh')) {
            $baseCountQuery->where('MaNganh', $request->MaNganh);
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $cleanSearch = preg_replace('/[^A-Za-z0-9]/', '', $search);
            $baseCountQuery->where(function ($q) use ($search, $cleanSearch) {
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
            $tt = trim($request->TrangThai);
            if (in_array($tt, ['da_phe_duyet', 'Đã phê duyệt', 'da_duyet', 'Đã duyệt', 'da_cong_bo', 'Đã công bố'])) {
                $query->whereIn('TrangThai', $daCongBoStatuses);
            } elseif (in_array($tt, ['chua_phe_duyet', 'Chưa phê duyệt', 'dang_xet_duyet', 'cho_duyet', 'Chờ duyệt'])) {
                $query->whereIn('TrangThai', $dangXetDuyetStatuses);
            } elseif (in_array($tt, ['yeu_cau_chinh_sua', 'yeu_cau_sua', 'Yêu cầu chỉnh sửa'])) {
                $query->whereIn('TrangThai', ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương', 'Yêu cầu điều chỉnh']);
            } elseif (in_array($tt, ['tu_choi', 'Từ chối'])) {
                $query->whereIn('TrangThai', ['Từ chối', 'Không đạt phản biện']);
            } elseif ($tt === 'tu_choi_can_sua') {
                $query->whereIn('TrangThai', $tuChoiCanSuaStatuses);
            } else {
                $query->where('TrangThai', $tt);
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
            $cleanBM = str_replace('BM_', '', $maBM);
            $bmList = array_unique([$maBM, $cleanBM, 'BM_' . $cleanBM]);
            $query->where(function($q) use ($bmList) {
                $q->whereHas('giangVien', fn($gq) => $gq->whereIn('MaBoMon', $bmList))
                  ->orWhereHas('hocPhanRef', fn($hq) => $hq->whereIn('MaBoMon', $bmList));
            });
        }

        if ($request->filled('LinhVuc')) {
            $query->where('LinhVuc', $request->LinhVuc);
        }

        if ($request->filled('HocPhan')) {
            $hpVal = $request->HocPhan;
            $query->where(function($q) use ($hpVal) {
                $q->where('HocPhan', $hpVal)
                  ->orWhere('MaHocPhan', $hpVal)
                  ->orWhereHas('hocPhanRef', fn($hr) => $hr->where('MaHocPhan', $hpVal)->orWhere('TenHocPhan', $hpVal));
            });
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
            'hocPhans',
            'danhSachTrangThai'
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

        $detais = $query->with('giangVien')->get();
        if ($detais->isEmpty()) {
            return redirect()->back()->with('warning', 'Không có đề tài nào ở trạng thái "Trưởng khoa đã duyệt" để công bố.');
        }

        $count = 0;

        foreach ($detais as $dt) {
            $dt->update([
                'TrangThai'   => 'Đã công bố',
                'NgayCongBo'  => now(),
            ]);
            $count++;
        }

        // Tạo thông báo cho sinh viên
        try {
            $maTB = 'TB_' . strtoupper(Str::random(6));
            ThongBao::create([
                'MaThongBao'   => $maTB,
                'TieuDe'       => 'Công bố danh mục đề tài Khóa luận tốt nghiệp',
                'NoiDung'      => "Giáo vụ Khoa vừa công bố chính thức {$count} đề tài Khóa luận tốt nghiệp đã được Trưởng bộ môn và Trưởng khoa phê duyệt. Sinh viên/nhóm đủ điều kiện có thể xem chi tiết và đăng ký theo kế hoạch.",
                'LoaiThongBao' => 'Thông báo chung',
                'DoiTuongNhan' => 'Toàn trường',
                'NgayTao'      => now(),
                'TrangThai'    => 'Đã phát hành',
                'MaGVu'        => 'GVU01',
            ]);
        } catch (\Throwable $e) {
            // bỏ qua lỗi nếu có
        }

        return redirect()->back()->with('success', "Đã công bố chính thức {$count} đề tài Khóa luận cho sinh viên đăng ký!");
    }

    public function export(Request $request)
    {
        $query = DeTai::with([
            'giangVien.boMon.khoa', 
            'nganh', 
            'hocKy', 
            'hocPhanRef',
            'phieuDangKys.nhom.thanhVienNhoms.sinhVien.lop'
        ]);

        $isOnlyCongBo = ($request->TrangThai === 'da_cong_bo' || $request->TrangThai === 'Đã công bố' || $request->has('only_cong_bo'));

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            if ($request->TrangThai === 'dang_xet_duyet') {
                $query->whereIn('TrangThai', [
                    'Chờ duyệt cấp Bộ môn', 'Đang phản biện đề cương', 'Chờ duyệt cấp Khoa', 
                    'Đã nộp đề cương - Chờ phân công PB', 'Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương'
                ]);
            } elseif ($request->TrangThai === 'da_cong_bo' || $request->TrangThai === 'Đã công bố') {
                $query->whereIn('TrangThai', ['Đã công bố', 'Trưởng khoa đã duyệt', 'Đã đăng ký', 'Hoàn thành', 'Đã duyệt']);
            } elseif ($request->TrangThai === 'tu_choi_can_sua') {
                $query->whereIn('TrangThai', ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương', 'Từ chối', 'Không đạt phản biện']);
            } else {
                $query->where('TrangThai', $request->TrangThai);
            }
        }
        if ($request->filled('MaHocKy') && $request->MaHocKy !== 'ALL') {
            $query->where('MaHocKy', $request->MaHocKy);
        }
        if ($request->filled('MaKhoa')) {
            $maKhoa = $request->MaKhoa;
            $query->whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));
        }
        if ($request->filled('MaBoMon')) {
            $maBM = $request->MaBoMon;
            $cleanBM = str_replace('BM_', '', $maBM);
            $bmList = array_unique([$maBM, $cleanBM, 'BM_' . $cleanBM]);
            $query->where(function($q) use ($bmList) {
                $q->whereHas('giangVien', fn($gq) => $gq->whereIn('MaBoMon', $bmList))
                  ->orWhereHas('hocPhanRef', fn($hq) => $hq->whereIn('MaBoMon', $bmList));
            });
        }
        if ($request->filled('LinhVuc')) {
            $query->where('LinhVuc', $request->LinhVuc);
        }
        if ($request->filled('HocPhan')) {
            $hpVal = $request->HocPhan;
            $query->where(function($q) use ($hpVal) {
                $q->where('HocPhan', $hpVal)
                  ->orWhere('MaHocPhan', $hpVal)
                  ->orWhereHas('hocPhanRef', fn($hr) => $hr->where('MaHocPhan', $hpVal)->orWhere('TenHocPhan', $hpVal));
            });
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

        $detais = $query->orderBy('MaDeTai')->get();

        $prefix = $isOnlyCongBo ? 'Danh_Sach_De_Tai_Da_Cong_Bo_' : 'Danh_Sach_De_Tai_KLTN_';
        $hkPart = $request->filled('MaHocKy') ? $request->MaHocKy . '_' : '';
        $fileName = $prefix . $hkPart . date('Ymd_His') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'STT',
            'Mã Đề Tài',
            'Tên Đề Tài',
            'Học Phần / Môn',
            'Lĩnh Vực',
            'Chuyên Ngành',
            'Khoa',
            'Bộ Môn',
            'Giảng Viên Hướng Dẫn',
            'Email Giảng Viên',
            'Số SV Tối Đa',
            'Số SV Đã ĐK',
            'Tình Trạng Chỗ',
            'Mã Nhóm ĐK',
            'Danh Sách Sinh Viên Thực Hiện',
            'Trạng Thái',
            'Học Kỳ',
            'Ngày Duyệt BM',
            'Ngày Duyệt Khoa',
            'Ngày Công Bố'
        ];

        $callback = function() use ($detais, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM cho Excel mở tiếng Việt chuẩn
            fputcsv($file, $columns);

            $stt = 1;
            foreach ($detais as $dt) {
                $maxSV = $dt->SoLuongSinhVienToiDa ?? 3;
                $regApproved = $dt->phieuDangKys ? $dt->phieuDangKys->where('TrangThai', 'Đã duyệt') : collect();
                $regNhom = $regApproved->first()?->nhom ?? $dt->phieuDangKys->first()?->nhom;
                $regSV = 0;
                $svList = [];
                $maNhomStr = '';

                if ($regNhom) {
                    $maNhomStr = $regNhom->MaNhom . ($regNhom->TenNhom ? ' (' . $regNhom->TenNhom . ')' : '');
                    if ($regNhom->thanhVienNhoms) {
                        $regSV = $regNhom->thanhVienNhoms->count();
                        foreach ($regNhom->thanhVienNhoms as $tv) {
                            if ($tv->sinhVien) {
                                $svList[] = $tv->sinhVien->MaSV . ' - ' . $tv->sinhVien->HoTen . ($tv->sinhVien->lop ? ' (' . $tv->sinhVien->lop->TenLop . ')' : '');
                            }
                        }
                    }
                } elseif ($regApproved->count() > 0) {
                    $regSV = $regApproved->count();
                }

                $tinhTrangCho = ($regSV >= $maxSV) ? 'Đã đủ' : (($regSV > 0) ? 'Còn chỗ' : 'Chưa có SV');
                $danhSachSVStr = !empty($svList) ? implode('; ', $svList) : ($regSV > 0 ? "{$regSV} sinh viên" : 'Chưa có sinh viên');

                fputcsv($file, [
                    $stt++,
                    $dt->MaDeTai,
                    $dt->TenDeTai,
                    $dt->HocPhan ?? ($dt->hocPhanRef->TenHocPhan ?? 'Khóa luận tốt nghiệp'),
                    $dt->LinhVuc ?? '',
                    $dt->nganh->TenNganh ?? 'Toàn khoa',
                    $dt->giangVien?->boMon?->khoa?->TenKhoa ?? 'Công nghệ Thông tin',
                    $dt->giangVien?->boMon?->TenBoMon ?? '',
                    $dt->giangVien?->HoTen ?? 'Chưa phân công',
                    $dt->giangVien?->Email ?? '',
                    $maxSV,
                    $regSV,
                    $tinhTrangCho,
                    $maNhomStr,
                    $danhSachSVStr,
                    $dt->TrangThai,
                    $dt->hocKy->TenHocKy ?? $dt->MaHocKy,
                    $dt->NgayDuyetBM ? \Carbon\Carbon::parse($dt->NgayDuyetBM)->format('d/m/Y') : '',
                    $dt->NgayDuyetKhoa ? \Carbon\Carbon::parse($dt->NgayDuyetKhoa)->format('d/m/Y') : '',
                    $dt->NgayCongBo ? \Carbon\Carbon::parse($dt->NgayCongBo)->format('d/m/Y') : '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
