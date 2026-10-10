<?php

namespace App\Http\Controllers\TruongKhoa;

use App\Http\Controllers\Controller;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\PhanCongPhanBien;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DuyetDeTaiController extends Controller
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

        // Danh sách học kỳ
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $currentHocKy = HocKy::where('TrangThai', 'Đang diễn ra')->orderBy('MaHocKy', 'desc')->first() ?? $hocKies->first();
        $selectedHocKy = $request->input('MaHocKy', $currentHocKy?->MaHocKy ?? '');

        // Các dropdown lọc theo Khoa
        $boMons = BoMon::where('MaKhoa', $maKhoa)->orderBy('TenBoMon')->get();
        $linhVucs = DeTai::whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa))
            ->whereNotNull('LinhVuc')
            ->where('LinhVuc', '!=', '')
            ->distinct()
            ->pluck('LinhVuc');

        $hocPhans = DeTai::whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa))
            ->whereNotNull('HocPhan')
            ->where('HocPhan', '!=', '')
            ->distinct()
            ->pluck('HocPhan');

        // Query cơ sở tính toán counts
        $baseCountQuery = DeTai::whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));
        if ($selectedHocKy && $selectedHocKy !== 'ALL') {
            $baseCountQuery->where('MaHocKy', $selectedHocKy);
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

        $choDuyetBM   = (clone $baseCountQuery)->where('TrangThai', 'Chờ duyệt cấp Bộ môn')->count();
        $dangPB       = (clone $baseCountQuery)->whereIn('TrangThai', ['Đang phản biện đề cương', 'Đã nộp đề cương - Chờ phân công PB', 'Đã phản biện - Chờ TBM duyệt đề cương', 'Đã phản biện - Chờ duyệt BM'])->count();
        $choDuyetKhoa = (clone $baseCountQuery)->where('TrangThai', 'Chờ duyệt cấp Khoa')->count();
        $tkDaDuyet    = (clone $baseCountQuery)->whereIn('TrangThai', ['Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương'])->count();
        $daCongBo     = (clone $baseCountQuery)->where('TrangThai', 'Đã công bố')->count();
        $daDangKy     = (clone $baseCountQuery)->where('TrangThai', 'Đã đăng ký')->count();
        $hoanThanh    = (clone $baseCountQuery)->where('TrangThai', 'Hoàn thành')->count();
        $yeuCauSua    = (clone $baseCountQuery)->whereIn('TrangThai', ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương'])->count();
        $tuChoi       = (clone $baseCountQuery)->whereIn('TrangThai', ['Từ chối', 'Không đạt phản biện'])->count();
        $total        = (clone $baseCountQuery)->count();

        $countDangXetDuyet = (clone $baseCountQuery)->whereIn('TrangThai', [
            'Chờ duyệt cấp Bộ môn', 'Đang phản biện đề cương', 'Chờ duyệt cấp Khoa',
            'Đã nộp đề cương - Chờ phân công PB', 'Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương'
        ])->count();
        $countDaCongBoTab  = (clone $baseCountQuery)->whereIn('TrangThai', [
            'Đã công bố', 'Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã đăng ký', 'Hoàn thành'
        ])->count();
        $countTuChoiCanSua = (clone $baseCountQuery)->whereIn('TrangThai', [
            'Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương', 'Từ chối', 'Không đạt phản biện'
        ])->count();

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

        // -------------------------------------------------------------
        // TÍNH TOÁN DỮ LIỆU THỐNG KÊ HỌC THUẬT (TAB THỐNG KÊ ĐỀ TÀI)
        // -------------------------------------------------------------
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

        // -------------------------------------------------------------
        // QUERY DANH SÁCH ĐỀ TÀI PHÂN TRANG (CHUẨN 5 ĐỀ TÀI/TRANG)
        // -------------------------------------------------------------
        $query = DeTai::with([
            'giangVien.boMon.khoa', 
            'nganh', 
            'hocKy', 
            'phieuDangKys.nhom.thanhVienNhoms.sinhVien.lop', 
            'phanCongPhanBiens.giangVien'
        ])->whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));

        if ($selectedHocKy && $selectedHocKy !== 'ALL') {
            $query->where('MaHocKy', $selectedHocKy);
        }

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            if ($request->TrangThai === 'dang_xet_duyet' || $request->TrangThai === 'cho_duyet') {
                $query->whereIn('TrangThai', [
                    'Chờ duyệt cấp Bộ môn', 'Đang phản biện đề cương', 'Chờ duyệt cấp Khoa',
                    'Đã nộp đề cương - Chờ phân công PB', 'Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương'
                ]);
            } elseif ($request->TrangThai === 'da_cong_bo') {
                $query->whereIn('TrangThai', [
                    'Đã công bố', 'Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã đăng ký', 'Hoàn thành'
                ]);
            } elseif ($request->TrangThai === 'tu_choi_can_sua') {
                $query->whereIn('TrangThai', [
                    'Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương', 'Từ chối', 'Không đạt phản biện'
                ]);
            } elseif ($request->TrangThai === 'Trưởng khoa đã duyệt') {
                $query->whereIn('TrangThai', ['Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương']);
            } elseif ($request->TrangThai === 'Đang phản biện đề cương') {
                $query->whereIn('TrangThai', ['Đang phản biện đề cương', 'Đã nộp đề cương - Chờ phân công PB', 'Đã phản biện - Chờ TBM duyệt đề cương', 'Đã phản biện - Chờ duyệt BM']);
            } elseif ($request->TrangThai === 'Yêu cầu chỉnh sửa') {
                $query->whereIn('TrangThai', ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương']);
            } elseif ($request->TrangThai === 'Từ chối') {
                $query->whereIn('TrangThai', ['Từ chối', 'Không đạt phản biện']);
            } else {
                $query->where('TrangThai', $request->TrangThai);
            }
        }

        if ($request->filled('MaBoMon')) {
            $maBM = $request->MaBoMon;
            $query->whereHas('giangVien', fn($g) => $g->where('MaBoMon', $maBM));
        }

        if ($request->filled('LinhVuc')) {
            $query->where('LinhVuc', $request->LinhVuc);
        }

        if ($request->filled('HocPhan')) {
            $query->where('HocPhan', $request->HocPhan);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $query->where(function ($q) use ($s, $sClean) {
                $q->where('TenDeTai', 'like', "%{$s}%")
                  ->orWhere('MaDeTai', 'like', "%{$s}%")
                  ->orWhere('LinhVuc', 'like', "%{$s}%");
                if (!empty($sClean)) {
                    $q->orWhere('MaDeTai', 'like', "%{$sClean}%");
                }
                $q->orWhereHas('giangVien', function($g) use ($s, $sClean) {
                    $g->where('HoTen', 'like', "%{$s}%");
                    if (!empty($sClean)) {
                        $g->orWhere('MaGV', 'like', "%{$sClean}%");
                    }
                });
            });
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(5)->withQueryString();

        return view('truongkhoa.duyet_detai.index', compact(
            'khoa',
            'detais',
            'counts',
            'statsKPIs',
            'statusChartCounts',
            'boMonChartCounts',
            'topLinhVucChartCounts',
            'quyMoNhomChartCounts',
            'dangKyStats',
            'hocKies',
            'currentHocKy',
            'selectedHocKy',
            'boMons',
            'linhVucs',
            'hocPhans'
        ));
    }

    public function export(Request $request)
    {
        $khoa = $this->getKhoa();
        $maKhoa = $khoa ? $khoa->MaKhoa : 'CNTT';

        $query = DeTai::with(['giangVien.boMon', 'nganh', 'hocKy'])
            ->whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            $query->where('TrangThai', $request->TrangThai);
        }
        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }
        if ($request->filled('HocPhan')) {
            $query->where('HocPhan', $request->HocPhan);
        }
        if ($request->filled('MaBoMon')) {
            $query->whereHas('giangVien', fn($q) => $q->where('MaBoMon', $request->MaBoMon));
        }

        $detais = $query->orderBy('MaDeTai')->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=Danh_Sach_De_Tai_Khoa_" . $maKhoa . "_" . date('Ymd_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Mã ĐT', 'Tên Đề Tài', 'Môn / Học Phần', 'Lĩnh Vực', 'Ngành', 'Giảng Viên Hướng Dẫn', 'Bộ Môn', 'Số SV Tối Đa', 'Học Kỳ', 'Trạng Thái', 'Có Đề Cương', 'Ngày Duyệt BM', 'Ngày Duyệt Khoa', 'Ngày Công Bố'];

        $callback = function() use ($detais, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
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

    public function show($id)
    {
        $khoa = $this->getKhoa();
        $detai = DeTai::with(['giangVien.boMon', 'hocKy', 'nganh', 'phanCongPhanBiens.giangVien'])
            ->findOrFail($id);

        $phanBienHienTai = $detai->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');

        return view('truongkhoa.duyet_detai.show', compact('khoa', 'detai', 'phanBienHienTai'));
    }

    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);

        $detai = DeTai::with('giangVien')->findOrFail($id);

        if ($detai->TrangThai === 'Đã công bố' && !empty($detai->NgayDuyetKhoa)) {
            return redirect()->back()->with('info', "Đề tài '{$detai->TenDeTai}' đã được phê duyệt và công bố trước đó.");
        }

        // Quy định: Giảng viên chỉ có thể được phê duyệt và công bố tối đa đúng 5 đề tài trong một học kỳ
        $soDeTaiDaDuyet = DeTai::where('MaGV', $detai->MaGV)
            ->where('MaHocKy', $detai->MaHocKy)
            ->where('MaDeTai', '!=', $detai->MaDeTai)
            ->whereIn('TrangThai', ['Đã công bố', 'Trưởng khoa đã duyệt', 'Đã duyệt', 'Đã đăng ký', 'Hoàn thành'])
            ->count();

        if ($soDeTaiDaDuyet >= 5) {
            $tenGV = $detai->giangVien?->HoTen ?? $detai->MaGV;
            return redirect()->back()->withErrors("Giảng viên {$tenGV} đã đạt định mức tối đa 5 đề tài được phê duyệt & công bố trong học kỳ này! Không thể phê duyệt thêm đề tài thứ 6.");
        }

        $detai->update([
            'TrangThai'       => 'Đã công bố',
            'NgayDuyetKhoa'   => now(),
            'NgayCongBo'      => now(),
            'NguoiDuyetKhoa'  => $gv ? $gv->MaGV : ($user->TenDangNhap ?? 'TK'),
            'LyDoTuChoi'      => null,
        ]);

        // Gửi thông báo cho GV đề xuất
        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '✅ Đề tài đã được Trưởng khoa phê duyệt & Công bố',
                "Đề tài '{$detai->TenDeTai}' đã được Trưởng khoa phê duyệt và chính thức CÔNG BỐ để sinh viên đăng ký theo kế hoạch.",
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã phê duyệt đề xuất đề tài '{$detai->TenDeTai}' cấp Khoa và CÔNG BỐ đề tài cho sinh viên đăng ký thành công!");
    }

    public function requestEdit(Request $request, $id)
    {
        $request->validate([
            'YeuCauSua' => 'required|string|max:1000',
        ], [
            'YeuCauSua.required' => 'Vui lòng nhập nội dung yêu cầu điều chỉnh.',
        ]);

        $detai = DeTai::findOrFail($id);

        if ($detai->TrangThai === 'Đã công bố' && !empty($detai->NgayDuyetKhoa)) {
            return redirect()->back()->withErrors('Đề tài đã hoàn tất phê duyệt cấp Khoa và đã công bố chính thức. Không thể yêu cầu chỉnh sửa đề tài này.');
        }

        $detai->update([
            'TrangThai'  => 'Yêu cầu chỉnh sửa',
            'LyDoTuChoi' => trim($request->YeuCauSua),
        ]);

        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '⚠️ Yêu cầu chỉnh sửa đề tài từ Trưởng khoa',
                "Đề tài '{$detai->TenDeTai}' có ý kiến chỉnh sửa từ Trưởng khoa: " . trim($request->YeuCauSua),
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã gửi yêu cầu chỉnh sửa đề tài tới Giảng viên đề xuất.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'LyDoTuChoi' => 'required|string|max:1000',
        ], [
            'LyDoTuChoi.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        $detai = DeTai::findOrFail($id);

        if ($detai->TrangThai === 'Đã công bố' && !empty($detai->NgayDuyetKhoa)) {
            return redirect()->back()->withErrors('Đề tài đã hoàn tất phê duyệt cấp Khoa và đã công bố chính thức. Không thể từ chối đề tài này.');
        }

        $detai->update([
            'TrangThai'  => 'Từ chối',
            'LyDoTuChoi' => trim($request->LyDoTuChoi),
        ]);

        $detai->loadMissing('giangVien.taiKhoan');
        $gv = $detai->giangVien;
        $maTK = $gv?->MaTK ?? $gv?->taiKhoan?->MaTK;
        if (!$maTK && $gv) {
            $maTK = \App\Models\TaiKhoan::where('TenDangNhap', $gv->MaGV)->orWhere('Email', $gv->Email)->value('MaTK');
        }
        if ($maTK) {
            ThongBaoService::guiDen(
                $maTK,
                '❌ Đề tài bị Trưởng khoa từ chối',
                "Đề tài '{$detai->TenDeTai}' đã bị Trưởng khoa từ chối phê duyệt. Lý do: " . trim($request->LyDoTuChoi),
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã từ chối đề tài '{$detai->TenDeTai}'.");
    }
}
