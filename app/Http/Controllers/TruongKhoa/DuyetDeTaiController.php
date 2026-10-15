<?php

namespace App\Http\Controllers\TruongKhoa;

use App\Http\Controllers\Controller;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\PhanCongPhanBien;
use App\Models\ChiTietDuyetDeTai;
use App\Helpers\IdGenerator;
use App\Services\ThongBaoService;
use App\Services\ChiTieuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $byName = Khoa::where('TruongKhoa', 'like', '%' . ($gv->HoTen ?? '') . '%')->first();
        if ($byName) return $byName;

        return null;
    }

    /**
     * Ràng buộc phân quyền P0: Trưởng khoa chỉ được xem/duyệt đề tài thuộc Khoa mình phụ trách
     */
    private function assertDeTaiBelongsToKhoa(DeTai $detai, $khoa)
    {
        if (!$khoa) {
            abort(403, 'Bạn chưa được phân công phụ trách Khoa nào để thực hiện thao tác này.');
        }
        $detai->loadMissing('giangVien.boMon.khoa');
        $detaiMaKhoa = $detai->giangVien?->boMon?->MaKhoa;
        if (!$detaiMaKhoa || $detaiMaKhoa !== $khoa->MaKhoa) {
            abort(403, "Bạn không có quyền quản lý hay phê duyệt đề tài không thuộc Khoa '{$khoa->TenKhoa}'.");
        }
    }

    /**
     * Ràng buộc phân quyền P0: Trưởng khoa chỉ được xem/quản lý Bộ môn thuộc Khoa mình phụ trách
     */
    private function assertBoMonBelongsToKhoa(BoMon $boMon, $khoa)
    {
        if (!$khoa) {
            abort(403, 'Bạn chưa được phân công phụ trách Khoa nào để thực hiện thao tác này.');
        }
        if ($boMon->MaKhoa !== $khoa->MaKhoa) {
            abort(403, "Bạn không có quyền quản lý hay xem dữ liệu của Bộ môn '{$boMon->TenBoMon}' không thuộc Khoa '{$khoa->TenKhoa}'.");
        }
    }

    /**
     * Màn hình chính của Trưởng khoa (P3.2): Bảng danh sách bộ môn thuộc khoa
     * Route: GET /truongkhoa/duyet-detai
     */
    public function index(Request $request)
    {
        $khoa = $this->getKhoa();
        $maKhoa = $khoa ? $khoa->MaKhoa : 'CNTT';

        // 1. Danh sách học kỳ
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $currentHocKy = HocKy::where('TrangThai', 'Đang diễn ra')->orderBy('MaHocKy', 'desc')->first() ?? $hocKies->first();
        $selectedHocKy = $request->input('MaHocKy', $request->input('hocKy', $currentHocKy?->MaHocKy ?? ''));

        // 2. Danh sách Bộ môn thuộc Khoa
        $boMons = BoMon::where('MaKhoa', $maKhoa)->orderBy('TenBoMon')->get();
        $boMonIds = $boMons->pluck('MaBoMon')->toArray();

        // 3. Danh sách Giảng viên thuộc các Bộ môn của Khoa
        $giangViens = GiangVien::whereIn('MaBoMon', $boMonIds)->get();
        $allGvIds = $giangViens->pluck('MaGV')->toArray();

        // -------------------------------------------------------------
        // TÍNH TOÁN 5 THẺ THỐNG KÊ PHÍA TRÊN CHO TOÀN KHOA (CHUẨN P3.2)
        // -------------------------------------------------------------
        $deTaiStats = DB::table('DeTai')
            ->select(
                DB::raw("COUNT(*) as tong_detai"),
                DB::raw("SUM(CASE WHEN TrangThai = 'Chờ duyệt cấp Khoa' THEN 1 ELSE 0 END) as cho_duyet_khoa"),
                DB::raw("SUM(CASE WHEN TrangThai IN ('Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành') THEN 1 ELSE 0 END) as da_duyet"),
                DB::raw("SUM(CASE WHEN TrangThai IN ('Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương', 'Từ chối', 'Không đạt phản biện') THEN 1 ELSE 0 END) as can_xem_xet_detai"),
                DB::raw("COUNT(DISTINCT MaGV) as so_gv_de_xuat")
            )
            ->where('MaHocKy', $selectedHocKy)
            ->whereIn('MaGV', $allGvIds)
            ->first();

        // Thống kê chỉ tiêu của tất cả GV trong khoa qua ChiTieuService batch (tránh N+1)
        $chiTieuStats = ChiTieuService::getChiTieuStatsBatch($allGvIds, $selectedHocKy);

        $soGvVuotChiTieu = 0;
        foreach ($chiTieuStats as $ct) {
            if ($ct['tinh_trang'] === 'vuot_chi_tieu') {
                $soGvVuotChiTieu++;
            }
        }

        $summaryKPIs = [
            'cho_duyet_khoa'            => (int)($deTaiStats->cho_duyet_khoa ?? 0),
            'tong_bo_mon'               => $boMons->count(),
            'so_gv_de_xuat'             => (int)($deTaiStats->so_gv_de_xuat ?? 0),
            'so_detai_da_duyet'         => (int)($deTaiStats->da_duyet ?? 0),
            'so_truong_hop_can_xem_xet' => (int)($deTaiStats->can_xem_xet_detai ?? 0) + $soGvVuotChiTieu,
        ];

        // -------------------------------------------------------------
        // TỔNG HỢP DỮ LIỆU TỪNG BỘ MÔN (CHUẨN P3.2)
        // -------------------------------------------------------------
        $bmTopicStatsRaw = DB::table('DeTai')
            ->join('GiangVien', 'DeTai.MaGV', '=', 'GiangVien.MaGV')
            ->select(
                'GiangVien.MaBoMon',
                DB::raw("COUNT(*) as tong_detai"),
                DB::raw("SUM(CASE WHEN DeTai.TrangThai = 'Chờ duyệt cấp Khoa' THEN 1 ELSE 0 END) as cho_duyet_khoa"),
                DB::raw("COUNT(DISTINCT DeTai.MaGV) as so_gv_de_xuat")
            )
            ->where('DeTai.MaHocKy', $selectedHocKy)
            ->whereIn('GiangVien.MaBoMon', $boMonIds)
            ->groupBy('GiangVien.MaBoMon')
            ->get()
            ->keyBy('MaBoMon');

        $boMonRows = [];
        foreach ($boMons as $bm) {
            $bmGvs = $giangViens->where('MaBoMon', $bm->MaBoMon);
            $bmGvIds = $bmGvs->pluck('MaGV')->toArray();

            $bmStat = $bmTopicStatsRaw->get($bm->MaBoMon);
            $tongDeTai = (int)($bmStat?->tong_detai ?? 0);
            $choDuyetKhoa = (int)($bmStat?->cho_duyet_khoa ?? 0);
            $soGvDeXuat = (int)($bmStat?->so_gv_de_xuat ?? 0);
            $tongSoGv = $bmGvs->count();

            // Tính chỉ tiêu toàn bộ môn
            $bmTongChiTieu = 0;
            $bmDaDungChiTieu = 0;
            $bmHasVuot = false;
            $bmAllHet = true;
            foreach ($bmGvIds as $gId) {
                $ct = $chiTieuStats[$gId] ?? null;
                if ($ct) {
                    $bmTongChiTieu += $ct['tong'];
                    $bmDaDungChiTieu += $ct['da_dung'];
                    if ($ct['tinh_trang'] === 'vuot_chi_tieu') {
                        $bmHasVuot = true;
                    }
                    if ($ct['tinh_trang'] === 'con_chi_tieu') {
                        $bmAllHet = false;
                    }
                }
            }

            if ($bmHasVuot) {
                $tinhTrangChiTieu = 'vuot_chi_tieu';
                $tinhTrangLabel = 'Vượt chỉ tiêu';
            } elseif ($bmAllHet && count($bmGvIds) > 0) {
                $tinhTrangChiTieu = 'het_chi_tieu';
                $tinhTrangLabel = 'Hết chỉ tiêu';
            } else {
                $tinhTrangChiTieu = 'con_chi_tieu';
                $tinhTrangLabel = 'Còn chỉ tiêu';
            }

            $boMonRows[] = [
                'model'               => $bm,
                'MaBoMon'             => $bm->MaBoMon,
                'TenBoMon'            => $bm->TenBoMon,
                'so_gv_de_xuat'       => $soGvDeXuat,
                'tong_so_gv'          => $tongSoGv,
                'tong_detai'          => $tongDeTai,
                'cho_duyet_khoa'      => $choDuyetKhoa,
                'da_dung_chi_tieu'    => $bmDaDungChiTieu,
                'tong_chi_tieu'       => $bmTongChiTieu,
                'tinh_trang_chi_tieu' => $tinhTrangChiTieu,
                'tinh_trang_label'    => $tinhTrangLabel,
            ];
        }

        // Tìm kiếm và Lọc trên bảng Bộ môn
        $filteredBoMonRows = collect($boMonRows);

        if ($request->filled('search')) {
            $s = trim(mb_strtolower($request->search));
            $filteredBoMonRows = $filteredBoMonRows->filter(function ($row) use ($s) {
                return str_contains(mb_strtolower($row['TenBoMon']), $s)
                    || str_contains(mb_strtolower($row['MaBoMon']), $s);
            });
        }

        if ($request->filled('tinh_trang_chi_tieu') && $request->tinh_trang_chi_tieu !== 'tat_ca' && $request->tinh_trang_chi_tieu !== 'ALL') {
            $ttct = $request->tinh_trang_chi_tieu;
            $filteredBoMonRows = $filteredBoMonRows->filter(function ($row) use ($ttct) {
                return $row['tinh_trang_chi_tieu'] === $ttct;
            });
        }

        // -------------------------------------------------------------
        // TÍNH TOÁN DỮ LIỆU THỐNG KÊ HỌC THUẬT (TAB THỐNG KÊ ĐỀ TÀI)
        // -------------------------------------------------------------
        $linhVucs = DeTai::whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa))
            ->whereNotNull('LinhVuc')
            ->where('LinhVuc', '!=', '')
            ->distinct()
            ->pluck('LinhVuc');

        $baseCountQuery = DeTai::whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));
        if ($selectedHocKy && $selectedHocKy !== 'ALL') {
            $baseCountQuery->where('MaHocKy', $selectedHocKy);
        }

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

        return view('truongkhoa.duyet_detai.index', compact(
            'khoa',
            'summaryKPIs',
            'filteredBoMonRows',
            'boMons',
            'hocKies',
            'currentHocKy',
            'selectedHocKy',
            'statsKPIs',
            'statusChartCounts',
            'boMonChartCounts',
            'topLinhVucChartCounts',
            'quyMoNhomChartCounts',
            'dangKyStats',
            'linhVucs'
        ));
    }

    /**
     * P3.3: Trang danh sách đề tài của một Bộ môn (thuộc Khoa của Trưởng khoa)
     * Route: GET /truongkhoa/duyet-detai/bo-mon/{maBoMon}
     */
    public function danhSachDeTaiBoMon(Request $request, string $maBoMon)
    {
        $khoa = $this->getKhoa();
        $boMon = BoMon::where('MaBoMon', $maBoMon)->firstOrFail();
        $this->assertBoMonBelongsToKhoa($boMon, $khoa);

        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $currentHocKy = HocKy::where('TrangThai', 'Đang diễn ra')->orderBy('MaHocKy', 'desc')->first() ?? $hocKies->first();
        $selectedHocKy = $request->input('MaHocKy', $request->input('hocKy', $currentHocKy?->MaHocKy ?? ''));

        // Lấy chi tiêu của các giảng viên bộ môn qua batch để phục vụ lọc và hiển thị
        $gvIds = $boMon->giangViens()->pluck('MaGV')->toArray();
        $chiTieuStats = ChiTieuService::getChiTieuStatsBatch($gvIds, $selectedHocKy);

        // Query đề tài thuộc bộ môn
        $query = DeTai::with(['giangVien.boMon', 'nganh', 'hocKy', 'phieuDangKys.nhom.thanhVienNhoms'])
            ->whereHas('giangVien', fn($g) => $g->where('MaBoMon', $maBoMon));

        if ($selectedHocKy && $selectedHocKy !== 'ALL') {
            $query->where('MaHocKy', $selectedHocKy);
        }

        // 1. Tìm kiếm theo tên đề tài hoặc tên giảng viên, mã đề tài, mã giảng viên
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('TenDeTai', 'like', "%{$s}%")
                  ->orWhere('MaDeTai', 'like', "%{$s}%")
                  ->orWhereHas('giangVien', fn($g) => $g->where('HoTen', 'like', "%{$s}%")->orWhere('MaGV', 'like', "%{$s}%"));
            });
        }

        // 2. Lọc theo trạng thái phê duyệt của TBM
        if ($request->filled('trang_thai_tbm') && $request->trang_thai_tbm !== 'ALL' && $request->trang_thai_tbm !== 'tat_ca') {
            $ttTBM = $request->trang_thai_tbm;
            if ($ttTBM === 'cho_duyet') {
                $query->whereIn('TrangThai', ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt'])
                      ->whereNull('NgayDuyetBM');
            } elseif ($ttTBM === 'da_duyet') {
                $query->where(function($q) {
                    $q->whereNotNull('NgayDuyetBM')
                      ->orWhereIn('TrangThai', [
                          'Chờ duyệt cấp Khoa', 'Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương',
                          'Đã nộp đề cương - Chờ phân công PB', 'Đang phản biện đề cương',
                          'Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương',
                          'Đã công bố', 'Đã đăng ký', 'Hoàn thành'
                      ]);
                });
            } elseif ($ttTBM === 'yeu_cau_sua') {
                $query->whereIn('TrangThai', ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương'])
                      ->whereNull('NgayDuyetBM');
            } elseif ($ttTBM === 'tu_choi') {
                $query->whereIn('TrangThai', ['Từ chối', 'Không đạt phản biện'])
                      ->whereNull('NgayDuyetBM');
            }
        }

        // 3. Lọc theo trạng thái phê duyệt của TK
        if ($request->filled('trang_thai_tk') && $request->trang_thai_tk !== 'ALL' && $request->trang_thai_tk !== 'tat_ca') {
            $ttTK = $request->trang_thai_tk;
            if (in_array($ttTK, ['cho_duyet', 'chua_phe_duyet', 'Chưa phê duyệt'])) {
                $query->whereIn('TrangThai', ['Chờ duyệt cấp Khoa', 'Chờ duyệt cấp Bộ môn', 'Đang phản biện đề cương', 'Đã phản biện - Chờ duyệt BM']);
            } elseif (in_array($ttTK, ['da_duyet', 'da_phe_duyet', 'Đã phê duyệt'])) {
                $query->where(function($q) {
                    $q->whereIn('TrangThai', ['Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành'])
                      ->orWhereNotNull('NgayDuyetKhoa');
                });
            } elseif (in_array($ttTK, ['yeu_cau_sua', 'yeu_cau_chinh_sua', 'Yêu cầu chỉnh sửa'])) {
                $query->whereIn('TrangThai', ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương', 'Yêu cầu điều chỉnh']);
            } elseif (in_array($ttTK, ['tu_choi', 'Từ chối'])) {
                $query->whereIn('TrangThai', ['Từ chối', 'Không đạt phản biện']);
            } else {
                $query->where('TrangThai', $ttTK);
            }
        }

        // Lọc theo trạng thái tổng quát TrangThai (nếu có)
        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL' && $request->TrangThai !== 'tat_ca') {
            $tt = trim($request->TrangThai);
            if (in_array($tt, ['da_phe_duyet', 'Đã phê duyệt', 'da_duyet', 'Đã duyệt'])) {
                $query->whereIn('TrangThai', ['Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành']);
            } elseif (in_array($tt, ['chua_phe_duyet', 'Chưa phê duyệt', 'cho_duyet', 'Chờ duyệt', 'dang_xet_duyet'])) {
                $query->whereIn('TrangThai', [
                    'Chờ duyệt cấp Bộ môn', 'Chờ duyệt cấp Khoa', 'Chờ duyệt',
                    'Đang phản biện đề cương', 'Đã nộp đề cương - Chờ phân công PB',
                    'Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương'
                ]);
            } elseif (in_array($tt, ['yeu_cau_chinh_sua', 'yeu_cau_sua', 'Yêu cầu chỉnh sửa'])) {
                $query->whereIn('TrangThai', ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương', 'Yêu cầu điều chỉnh']);
            } elseif (in_array($tt, ['tu_choi', 'Từ chối'])) {
                $query->whereIn('TrangThai', ['Từ chối', 'Không đạt phản biện']);
            } else {
                $query->where('TrangThai', $tt);
            }
        }

        // 4. Lọc theo tình trạng chỉ tiêu hướng dẫn
        if ($request->filled('tinh_trang_chi_tieu') && $request->tinh_trang_chi_tieu !== 'ALL' && $request->tinh_trang_chi_tieu !== 'tat_ca') {
            $ttct = $request->tinh_trang_chi_tieu;
            $matchedGvIds = [];
            foreach ($chiTieuStats as $gId => $stat) {
                if ($stat['tinh_trang'] === $ttct) {
                    $matchedGvIds[] = $gId;
                }
            }
            $query->whereIn('DeTai.MaGV', $matchedGvIds);
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('truongkhoa.duyet_detai.bomon', compact(
            'khoa',
            'boMon',
            'detais',
            'hocKies',
            'currentHocKy',
            'selectedHocKy',
            'chiTieuStats'
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

        $this->assertDeTaiBelongsToKhoa($detai, $khoa);

        $phanBienHienTai = $detai->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');

        return view('truongkhoa.duyet_detai.show', compact('khoa', 'detai', 'phanBienHienTai'));
    }

    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        $khoa = $this->getKhoa();

        $detai = DeTai::with('giangVien')->findOrFail($id);
        $this->assertDeTaiBelongsToKhoa($detai, $khoa);

        // Luồng duyệt P0: Trưởng khoa chỉ phê duyệt đề tài đã qua Trưởng bộ môn phê duyệt
        if ($detai->TrangThai === 'Chờ duyệt cấp Bộ môn' || empty($detai->NgayDuyetBM)) {
            return redirect()->back()->withErrors("Đề tài '{$detai->TenDeTai}' chưa được Trưởng bộ môn phê duyệt. Vui lòng chờ Trưởng bộ môn xét duyệt trước khi chuyển lên Trưởng khoa!");
        }

        if ($detai->TrangThai === 'Đã công bố' && !empty($detai->NgayDuyetKhoa)) {
            return redirect()->back()->with('info', "Đề tài '{$detai->TenDeTai}' đã được phê duyệt và công bố trước đó.");
        }

        // P1: TBM/TK được phép duyệt nhiều hơn chỉ tiêu của giảng viên (không chặn ở bước duyệt đề tài)
        $oldStatus = $detai->TrangThai;
        $newStatus = 'Đã công bố';

        $detai->update([
            'TrangThai'       => $newStatus,
            'NgayDuyetKhoa'   => now(),
            'NgayCongBo'      => now(),
            'NguoiDuyetKhoa'  => $gv ? $gv->MaGV : ($user->TenDangNhap ?? 'TK'),
            'LyDoTuChoi'      => null,
        ]);

        // Ghi lịch sử phê duyệt cấp Khoa vào ChiTietDuyetDeTai
        $maDuyet = IdGenerator::nextChiTietDuyetDeTai() ?? ('CTD' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT));
        ChiTietDuyetDeTai::create([
            'MaDuyet'       => $maDuyet,
            'MaDeTai'       => $detai->MaDeTai,
            'MaGV'          => $gv?->MaGV,
            'NguoiThucHien' => ($gv?->HoTen ? $gv->HoTen . ' (' . $gv->MaGV . ')' : $user->TenDangNhap) . ' - Trưởng Khoa',
            'HanhDong'      => 'Phê duyệt',
            'TrangThaiCu'   => $oldStatus,
            'TrangThai'     => $newStatus,
            'LyDo'          => 'Trưởng khoa đã phê duyệt và chính thức công bố đề tài.',
            'NgayDuyet'     => now(),
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

        $returnUrl = $request->input('return_url');
        if ($returnUrl && (str_starts_with($returnUrl, url('/')) || str_starts_with($returnUrl, '/'))) {
            return redirect($returnUrl)->with('success', "Đã phê duyệt đề xuất đề tài '{$detai->TenDeTai}' cấp Khoa và CÔNG BỐ đề tài cho sinh viên đăng ký thành công!");
        }

        return redirect()->route('truongkhoa.duyet_detai.bomon', [
            'maBoMon' => $detai->giangVien?->MaBoMon,
            'MaHocKy' => $detai->MaHocKy
        ])->with('success', "Đã phê duyệt đề xuất đề tài '{$detai->TenDeTai}' cấp Khoa và CÔNG BỐ đề tài cho sinh viên đăng ký thành công!");
    }

    public function requestEdit(Request $request, $id)
    {
        $request->validate([
            'YeuCauSua' => 'required|string|max:1000',
        ], [
            'YeuCauSua.required' => 'Vui lòng nhập nội dung yêu cầu điều chỉnh.',
        ]);

        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        $khoa = $this->getKhoa();
        $detai = DeTai::findOrFail($id);
        $this->assertDeTaiBelongsToKhoa($detai, $khoa);

        if ($detai->TrangThai === 'Đã công bố' && !empty($detai->NgayDuyetKhoa)) {
            return redirect()->back()->withErrors('Đề tài đã hoàn tất phê duyệt cấp Khoa và đã công bố chính thức. Không thể yêu cầu chỉnh sửa đề tài này.');
        }

        $oldStatus = $detai->TrangThai;
        $lyDo = trim($request->YeuCauSua);

        $detai->update([
            'TrangThai'  => 'Yêu cầu chỉnh sửa',
            'LyDoTuChoi' => $lyDo,
        ]);

        // Ghi lịch sử yêu cầu chỉnh sửa cấp Khoa
        $maDuyet = IdGenerator::nextChiTietDuyetDeTai() ?? ('CTD' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT));
        ChiTietDuyetDeTai::create([
            'MaDuyet'       => $maDuyet,
            'MaDeTai'       => $detai->MaDeTai,
            'MaGV'          => $gv?->MaGV,
            'NguoiThucHien' => ($gv?->HoTen ? $gv->HoTen . ' (' . $gv->MaGV . ')' : $user->TenDangNhap) . ' - Trưởng Khoa',
            'HanhDong'      => 'Yêu cầu chỉnh sửa',
            'TrangThaiCu'   => $oldStatus,
            'TrangThai'     => 'Yêu cầu chỉnh sửa',
            'LyDo'          => $lyDo,
            'NgayDuyet'     => now(),
        ]);

        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '⚠️ Yêu cầu chỉnh sửa đề tài từ Trưởng khoa',
                "Đề tài '{$detai->TenDeTai}' có ý kiến chỉnh sửa từ Trưởng khoa: " . $lyDo,
                'Đề tài'
            );
        }

        $returnUrl = $request->input('return_url');
        if ($returnUrl && (str_starts_with($returnUrl, url('/')) || str_starts_with($returnUrl, '/'))) {
            return redirect($returnUrl)->with('success', "Đã gửi yêu cầu chỉnh sửa đề tài tới Giảng viên đề xuất.");
        }

        return redirect()->route('truongkhoa.duyet_detai.bomon', [
            'maBoMon' => $detai->giangVien?->MaBoMon,
            'MaHocKy' => $detai->MaHocKy
        ])->with('success', "Đã gửi yêu cầu chỉnh sửa đề tài tới Giảng viên đề xuất.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'LyDoTuChoi' => 'required|string|max:1000',
        ], [
            'LyDoTuChoi.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        $khoa = $this->getKhoa();
        $detai = DeTai::findOrFail($id);
        $this->assertDeTaiBelongsToKhoa($detai, $khoa);

        if ($detai->TrangThai === 'Đã công bố' && !empty($detai->NgayDuyetKhoa)) {
            return redirect()->back()->withErrors('Đề tài đã hoàn tất phê duyệt cấp Khoa và đã công bố chính thức. Không thể từ chối đề tài này.');
        }

        $oldStatus = $detai->TrangThai;
        $lyDo = trim($request->LyDoTuChoi);

        $detai->update([
            'TrangThai'  => 'Từ chối',
            'LyDoTuChoi' => $lyDo,
        ]);

        // Ghi lịch sử từ chối cấp Khoa
        $maDuyet = IdGenerator::nextChiTietDuyetDeTai() ?? ('CTD' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT));
        ChiTietDuyetDeTai::create([
            'MaDuyet'       => $maDuyet,
            'MaDeTai'       => $detai->MaDeTai,
            'MaGV'          => $gv?->MaGV,
            'NguoiThucHien' => ($gv?->HoTen ? $gv->HoTen . ' (' . $gv->MaGV . ')' : $user->TenDangNhap) . ' - Trưởng Khoa',
            'HanhDong'      => 'Từ chối',
            'TrangThaiCu'   => $oldStatus,
            'TrangThai'     => 'Từ chối',
            'LyDo'          => $lyDo,
            'NgayDuyet'     => now(),
        ]);

        $detai->loadMissing('giangVien.taiKhoan');
        $gvDeTai = $detai->giangVien;
        $maTK = $gvDeTai?->MaTK ?? $gvDeTai?->taiKhoan?->MaTK;
        if (!$maTK && $gvDeTai) {
            $maTK = \App\Models\TaiKhoan::where('TenDangNhap', $gvDeTai->MaGV)->orWhere('Email', $gvDeTai->Email)->value('MaTK');
        }
        if ($maTK) {
            ThongBaoService::guiDen(
                $maTK,
                '❌ Đề tài bị Trưởng khoa từ chối',
                "Đề tài '{$detai->TenDeTai}' đã bị Trưởng khoa từ chối phê duyệt. Lý do: " . $lyDo,
                'Đề tài'
            );
        }

        $returnUrl = $request->input('return_url');
        if ($returnUrl && (str_starts_with($returnUrl, url('/')) || str_starts_with($returnUrl, '/'))) {
            return redirect($returnUrl)->with('success', "Đã từ chối đề tài '{$detai->TenDeTai}'.");
        }

        return redirect()->route('truongkhoa.duyet_detai.bomon', [
            'maBoMon' => $detai->giangVien?->MaBoMon,
            'MaHocKy' => $detai->MaHocKy
        ])->with('success', "Đã từ chối đề tài '{$detai->TenDeTai}'.");
    }
}
