<?php

namespace App\Http\Controllers\TruongBoMon;

use App\Http\Controllers\Controller;
use App\Models\BoMon;
use App\Models\ChiTietDuyetDeTai;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\PhanCongPhanBien;
use App\Helpers\IdGenerator;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DuyetDeTaiController extends Controller
{
    /**
     * Lấy Bộ môn của TBM đang đăng nhập.
     * Nếu không resolve được → abort(403) (không fallback về BoMon::first()).
     */
    private function getBoMon(): BoMon
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);

        // Ưu tiên 1: tra qua MaBoMon của bản ghi GiangVien
        if ($gv && $gv->MaBoMon) {
            $bm = BoMon::where('MaBoMon', $gv->MaBoMon)->first();
            if ($bm) return $bm;
        }

        // Ưu tiên 2: parse từ TenDangNhap (ví dụ: TBM_CNTT_CNPM_001 → CNPM)
        if ($user && preg_match('/^TBM_[A-Z0-9]+_([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
            $bm = BoMon::where('MaBoMon', $m[1])->first();
            if ($bm) return $bm;
        }

        // Không tìm được → từ chối truy cập (không fallback sai dữ liệu)
        abort(403, 'Không thể xác định Bộ môn của Trưởng bộ môn này. Vui lòng liên hệ Giáo vụ để cập nhật hồ sơ.');
    }

    /**
     * Kiểm tra đề tài có thuộc phạm vi Bộ môn của TBM không.
     * Nếu không → abort(403).
     */
    private function assertInScope(DeTai $detai, string $maBoMon): void
    {
        $gvIds = GiangVien::where('MaBoMon', $maBoMon)->pluck('MaGV');
        if (!$gvIds->contains($detai->MaGV)) {
            abort(403, 'Đề tài này không thuộc phạm vi Bộ môn của bạn.');
        }
    }

    /**
     * Ghi log vào ChiTietDuyetDeTai.
     */
    private function ghiLichSu(string $maDeTai, string $maGV, string $trangThai, ?string $lyDo = null): void
    {
        // Sinh mã MaDuyet: DY001, DY002, ...
        $max = DB::table('ChiTietDuyetDeTai')
            ->where('MaDuyet', 'LIKE', 'DY%')
            ->max(DB::raw("CAST(SUBSTRING(MaDuyet, 3) AS UNSIGNED)"));
        $maDuyet = 'DY' . str_pad(($max ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        ChiTietDuyetDeTai::create([
            'MaDuyet'   => $maDuyet,
            'NgayDuyet' => now()->toDateString(),
            'TrangThai' => $trangThai,
            'LyDo'      => $lyDo,
            'MaGV'      => $maGV,
            'MaDeTai'   => $maDeTai,
        ]);
    }

    // =========================================================================
    // INDEX – Danh sách đề tài của Bộ môn
    // =========================================================================
    public function index(Request $request)
    {
        $boMon = $this->getBoMon();
        $maBoMon = $boMon->MaBoMon;

        $giangViens = GiangVien::where('MaBoMon', $maBoMon)->orderBy('HoTen')->get();
        $gvIds = $giangViens->pluck('MaGV');

        // Danh sách học kỳ
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $currentHocKy = HocKy::where('TrangThai', 'Đang diễn ra')->first() ?? $hocKies->first();
        $selectedHocKy = $request->input('MaHocKy', $currentHocKy?->MaHocKy ?? '');

        // Dropdowns bộ lọc
        $linhVucs = DeTai::whereIn('MaGV', $gvIds)
            ->whereNotNull('LinhVuc')
            ->where('LinhVuc', '!=', '')
            ->distinct()
            ->pluck('LinhVuc');

        $hocPhans = DeTai::whereIn('MaGV', $gvIds)
            ->whereNotNull('HocPhan')
            ->where('HocPhan', '!=', '')
            ->distinct()
            ->pluck('HocPhan');

        // Query cơ sở tính toán counts
        $baseCountQuery = DeTai::whereIn('MaGV', $gvIds);
        if ($selectedHocKy && $selectedHocKy !== 'ALL') {
            $baseCountQuery->where('MaHocKy', $selectedHocKy);
        }
        if ($request->filled('MaGV')) {
            $baseCountQuery->where('MaGV', $request->MaGV);
        }
        if ($request->filled('LinhVuc')) {
            $baseCountQuery->where('LinhVuc', $request->LinhVuc);
        }
        if ($request->filled('HocPhan')) {
            $baseCountQuery->where('HocPhan', $request->HocPhan);
        }

        $choDuyetBM   = (clone $baseCountQuery)->where('TrangThai', 'Chờ duyệt cấp Bộ môn')->count();
        $dangPB       = (clone $baseCountQuery)->where('TrangThai', 'Đang phản biện đề cương')->count();
        $daPB         = (clone $baseCountQuery)->where('TrangThai', 'Đã phản biện - Chờ duyệt BM')->count();
        $choDuyetKhoa = (clone $baseCountQuery)->where('TrangThai', 'Chờ duyệt cấp Khoa')->count();
        $tkDaDuyet    = (clone $baseCountQuery)->where('TrangThai', 'Trưởng khoa đã duyệt')->count();
        $daCongBo     = (clone $baseCountQuery)->where('TrangThai', 'Đã công bố')->count();
        $daDangKy     = (clone $baseCountQuery)->where('TrangThai', 'Đã đăng ký')->count();
        $hoanThanh    = (clone $baseCountQuery)->where('TrangThai', 'Hoàn thành')->count();
        $yeuCauSua    = (clone $baseCountQuery)->where('TrangThai', 'Yêu cầu chỉnh sửa')->count();
        $tuChoi       = (clone $baseCountQuery)->where('TrangThai', 'Từ chối')->count();
        $khongDat     = (clone $baseCountQuery)->where('TrangThai', 'Không đạt phản biện')->count();
        $total        = (clone $baseCountQuery)->count();

        $counts = [
            'cho_duyet_bm'         => $choDuyetBM,
            'dang_phan_bien'       => $dangPB,
            'da_phan_bien'         => $daPB,
            'can_duyet'            => ($choDuyetBM + $daPB),
            'cho_duyet_khoa'       => $choDuyetKhoa,
            'truong_khoa_da_duyet' => $tkDaDuyet,
            'da_duyet_khoa'        => $tkDaDuyet,
            'da_cong_bo'           => $daCongBo,
            'da_dang_ky'           => $daDangKy,
            'hoan_thanh'           => $hoanThanh,
            'cho_duyet'            => ($choDuyetBM + $dangPB + $daPB),
            'yeu_cau_sua'          => $yeuCauSua,
            'tu_choi'              => $tuChoi,
            'khong_dat'            => $khongDat,
            'total'                => $total,
        ];

        // -------------------------------------------------------------
        // TÍNH TOÁN DỮ LIỆU THỐNG KÊ HỌC THUẬT (TAB THỐNG KÊ BỘ MÔN)
        // -------------------------------------------------------------
        $allScopedDeTais = (clone $baseCountQuery)->with([
            'giangVien',
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
        $giangVienChartCounts = [];
        $linhVucChartCounts = [];
        $quyMoNhomChartCounts = [
            '1 sinh viên' => 0,
            '2 sinh viên' => 0,
            '3 sinh viên' => 0,
        ];

        // Kết quả phản biện đề cương (cho thống kê)
        $pbChartCounts = [
            'Chưa phản biện' => 0,
            'Đạt'            => 0,
            'Yêu cầu chỉnh sửa' => 0,
            'Không đạt'      => 0,
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
            } elseif (in_array($dt->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Đang phản biện đề cương', 'Đã phản biện - Chờ duyệt BM', 'Yêu cầu chỉnh sửa', 'Không đạt phản biện'])) {
                $statDangChoDuyet++;
                $statusChartCounts['Đang chờ duyệt']++;
            } elseif ($dt->TrangThai === 'Từ chối') {
                $statusChartCounts['Khác']++;
            } elseif (in_array($dt->TrangThai, ['Đã công bố', 'Đã đăng ký', 'Trưởng khoa đã duyệt', 'Chờ duyệt cấp Khoa'])) {
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

            $gvName = $dt->giangVien->HoTen ?? 'Chưa phân GV';
            $giangVienChartCounts[$gvName] = ($giangVienChartCounts[$gvName] ?? 0) + 1;

            $lvName = $dt->LinhVuc ?: 'Lĩnh vực khác';
            $linhVucChartCounts[$lvName] = ($linhVucChartCounts[$lvName] ?? 0) + 1;

            if ($maxSV == 1) {
                $quyMoNhomChartCounts['1 sinh viên']++;
            } elseif ($maxSV == 2) {
                $quyMoNhomChartCounts['2 sinh viên']++;
            } else {
                $quyMoNhomChartCounts['3 sinh viên']++;
            }

            // Thống kê kết quả phản biện đề cương
            if (in_array($dt->TrangThai, ['Đang phản biện đề cương', 'Đã phản biện - Chờ duyệt BM', 'Chờ duyệt cấp Khoa', 'Trưởng khoa đã duyệt', 'Đã công bố', 'Hoàn thành'])) {
                // Lấy kết quả PB đề cương gần nhất
                $pbResult = PhanCongPhanBien::where('MaDeTai', $dt->MaDeTai)
                    ->where('VaiTro', 'Phản biện đề cương')
                    ->whereNotNull('KetQua')
                    ->orderBy('NgayDanhGia', 'desc')
                    ->value('KetQua');
                if ($pbResult) {
                    $pbChartCounts[$pbResult] = ($pbChartCounts[$pbResult] ?? 0) + 1;
                } else {
                    $pbChartCounts['Chưa phản biện']++;
                }
            } elseif ($dt->TrangThai === 'Không đạt phản biện') {
                $pbChartCounts['Không đạt']++;
            } else {
                $pbChartCounts['Chưa phản biện']++;
            }
        }

        arsort($linhVucChartCounts);
        $topLinhVucChartCounts = array_slice($linhVucChartCounts, 0, 8, true);
        arsort($giangVienChartCounts);

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
        // QUERY DANH SÁCH ĐỀ TÀI PHÂN TRANG
        // -------------------------------------------------------------
        $query = DeTai::with([
            'giangVien.boMon',
            'nganh',
            'hocKy',
            'phieuDangKys.nhom.thanhVienNhoms.sinhVien.lop',
            'phanCongPhanBiens.giangVien',
            'chiTietDuyets.giangVien',
        ])->whereIn('MaGV', $gvIds);

        if ($selectedHocKy && $selectedHocKy !== 'ALL') {
            $query->where('MaHocKy', $selectedHocKy);
        }

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            if ($request->TrangThai === 'can_duyet') {
                $query->whereIn('TrangThai', ['Chờ duyệt cấp Bộ môn', 'Đã phản biện - Chờ duyệt BM']);
            } else {
                $query->where('TrangThai', $request->TrangThai);
            }
        }

        if ($request->filled('MaGV')) {
            $query->where('MaGV', $request->MaGV);
        }

        if ($request->filled('LinhVuc')) {
            $query->where('LinhVuc', $request->LinhVuc);
        }

        if ($request->filled('HocPhan')) {
            $query->where('HocPhan', $request->HocPhan);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('TenDeTai', 'like', "%{$s}%")
                  ->orWhere('MaDeTai', 'like', "%{$s}%")
                  ->orWhere('LinhVuc', 'like', "%{$s}%")
                  ->orWhereHas('giangVien', fn($g) => $g->where('HoTen', 'like', "%{$s}%"));
            });
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(5)->withQueryString();

        return view('truongbomon.duyet_detai.index', compact(
            'boMon',
            'detais',
            'counts',
            'statsKPIs',
            'statusChartCounts',
            'giangVienChartCounts',
            'topLinhVucChartCounts',
            'quyMoNhomChartCounts',
            'pbChartCounts',
            'dangKyStats',
            'hocKies',
            'currentHocKy',
            'selectedHocKy',
            'giangViens',
            'linhVucs',
            'hocPhans'
        ));
    }

    // =========================================================================
    // EXPORT CSV
    // =========================================================================
    public function export(Request $request)
    {
        $boMon = $this->getBoMon();
        $maBoMon = $boMon->MaBoMon;
        $gvIds = GiangVien::where('MaBoMon', $maBoMon)->pluck('MaGV');

        $query = DeTai::with(['giangVien.boMon', 'nganh', 'hocKy'])
            ->whereIn('MaGV', $gvIds);

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            $query->where('TrangThai', $request->TrangThai);
        }
        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }
        if ($request->filled('HocPhan')) {
            $query->where('HocPhan', $request->HocPhan);
        }
        if ($request->filled('MaGV')) {
            $query->where('MaGV', $request->MaGV);
        }

        $detais = $query->orderBy('MaDeTai')->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=Danh_Sach_De_Tai_BM_" . $maBoMon . "_" . date('Ymd_His') . ".csv",
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
                    $dt->nganh->TenNganh ?? 'Toàn ngành',
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

    // =========================================================================
    // SHOW – Chi tiết đề tài (kèm lịch sử xử lý)
    // =========================================================================
    public function show($id)
    {
        $boMon = $this->getBoMon();
        $detai = DeTai::with([
            'giangVien.boMon',
            'hocKy',
            'nganh',
            'phanCongPhanBiens.giangVien',
            'chiTietDuyets.giangVien',
        ])->findOrFail($id);

        // Kiểm tra phạm vi Bộ môn
        $this->assertInScope($detai, $boMon->MaBoMon);

        // GV có thể làm phản biện: toàn hệ thống trừ GV đề xuất
        $allGiangViens = GiangVien::where('MaGV', '!=', $detai->MaGV)->orderBy('HoTen')->get();

        // Phân công phản biện đề cương hiện tại
        $phanBienHienTai = $detai->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');

        return view('truongbomon.duyet_detai.show', compact('boMon', 'detai', 'allGiangViens', 'phanBienHienTai'));
    }

    // =========================================================================
    // PHÂN CÔNG GV PHẢN BIỆN ĐỀ CƯƠNG (từ trang show)
    // =========================================================================
    public function phanCongPhanBien(Request $request, $id)
    {
        $request->validate([
            'MaGVPhanBien' => 'required|exists:GiangVien,MaGV',
        ], [
            'MaGVPhanBien.required' => 'Vui lòng chọn Giảng viên phản biện đề cương.',
        ]);

        $boMon = $this->getBoMon();
        $detai = DeTai::findOrFail($id);
        $this->assertInScope($detai, $boMon->MaBoMon);

        // BR07: GV đề xuất không được làm GV phản biện đề cương
        if ($detai->MaGV === $request->MaGVPhanBien) {
            return redirect()->back()->withErrors('Giảng viên đề xuất đề tài không được làm Giảng viên phản biện cho đề tài này (BR07)!');
        }

        // Kiểm tra trạng thái: chỉ phân công khi đề tài đang chờ duyệt BM hoặc đang phản biện mà chưa có kết quả
        $trangThaiHopLe = ['Chờ duyệt cấp Bộ môn', 'Đang phản biện đề cương'];
        if (!in_array($detai->TrangThai, $trangThaiHopLe)) {
            return redirect()->back()->withErrors('Không thể phân công phản biện đề cương ở trạng thái hiện tại của đề tài: ' . $detai->TrangThai);
        }

        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        $maGVTBM = $gv ? $gv->MaGV : ($user->TenDangNhap ?? 'TBM');

        DB::transaction(function () use ($request, $detai, $maGVTBM) {
            $maPC = IdGenerator::nextPhanCong();

            PhanCongPhanBien::updateOrCreate(
                [
                    'MaDeTai' => $detai->MaDeTai,
                    'VaiTro'  => 'Phản biện đề cương',
                ],
                [
                    'MaPhanCong'   => $maPC,
                    'MaGV'         => request('MaGVPhanBien'),
                    'NgayPhanCong' => now()->toDateString(),
                    'TrangThai'    => 'Đang phản biện',
                    'NhanXet'      => null,
                    'KetQua'       => null,
                    'NgayDanhGia'  => null,
                ]
            );

            $detai->update(['TrangThai' => 'Đang phản biện đề cương']);

            $this->ghiLichSu($detai->MaDeTai, $maGVTBM, 'Đã phân công phản biện đề cương', 'GV phản biện: ' . request('MaGVPhanBien'));
        });

        // Gửi thông báo đến GV phản biện
        $gvPB = GiangVien::find($request->MaGVPhanBien);
        if ($gvPB && $gvPB->MaTK) {
            ThongBaoService::guiDen(
                $gvPB->MaTK,
                '📋 Phân công phản biện đề cương đề tài',
                "Bạn vừa được Trưởng bộ môn phân công phản biện đề cương cho đề tài: '{$detai->TenDeTai}'. Vui lòng xem và gửi nhận xét đánh giá.",
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã phân công Giảng viên phản biện đề cương cho đề tài '{$detai->TenDeTai}' thành công!");
    }

    // =========================================================================
    // THAY GV PHẢN BIỆN (chỉ khi chưa có kết quả phản biện)
    // =========================================================================
    public function thayGvPhanBien(Request $request, $id)
    {
        $request->validate([
            'MaGVPhanBien' => 'required|exists:GiangVien,MaGV',
        ], [
            'MaGVPhanBien.required' => 'Vui lòng chọn Giảng viên phản biện mới.',
        ]);

        $boMon = $this->getBoMon();
        $detai = DeTai::findOrFail($id);
        $this->assertInScope($detai, $boMon->MaBoMon);

        // BR07
        if ($detai->MaGV === $request->MaGVPhanBien) {
            return redirect()->back()->withErrors('Giảng viên đề xuất đề tài không được làm Giảng viên phản biện (BR07)!');
        }

        // Chỉ cho phép đổi khi chưa có kết quả
        $phanCong = PhanCongPhanBien::where('MaDeTai', $detai->MaDeTai)
            ->where('VaiTro', 'Phản biện đề cương')
            ->first();

        if ($phanCong && !is_null($phanCong->KetQua)) {
            return redirect()->back()->withErrors('Không thể đổi Giảng viên phản biện vì đã có kết quả phản biện!');
        }

        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        $maGVTBM = $gv ? $gv->MaGV : ($user->TenDangNhap ?? 'TBM');
        $gvCuMaGV = $phanCong ? $phanCong->MaGV : null;

        DB::transaction(function () use ($request, $detai, $phanCong, $maGVTBM, $gvCuMaGV) {
            if ($phanCong) {
                $phanCong->update([
                    'MaGV'         => $request->MaGVPhanBien,
                    'NgayPhanCong' => now()->toDateString(),
                    'TrangThai'    => 'Đang phản biện',
                    'NhanXet'      => null,
                    'KetQua'       => null,
                    'NgayDanhGia'  => null,
                ]);
            } else {
                $maPC = IdGenerator::nextPhanCong();
                PhanCongPhanBien::create([
                    'MaPhanCong'   => $maPC,
                    'MaDeTai'      => $detai->MaDeTai,
                    'MaGV'         => $request->MaGVPhanBien,
                    'VaiTro'       => 'Phản biện đề cương',
                    'NgayPhanCong' => now()->toDateString(),
                    'TrangThai'    => 'Đang phản biện',
                ]);
            }

            $detai->update(['TrangThai' => 'Đang phản biện đề cương']);

            $lyDo = 'Đổi GV phản biện từ ' . ($gvCuMaGV ?? 'chưa có') . ' sang ' . $request->MaGVPhanBien;
            $this->ghiLichSu($detai->MaDeTai, $maGVTBM, 'Đã thay đổi GV phản biện đề cương', $lyDo);
        });

        // Thông báo cho GV phản biện mới
        $gvMoi = GiangVien::find($request->MaGVPhanBien);
        if ($gvMoi && $gvMoi->MaTK) {
            ThongBaoService::guiDen(
                $gvMoi->MaTK,
                '📋 Phân công phản biện đề cương đề tài',
                "Bạn vừa được Trưởng bộ môn phân công phản biện đề cương cho đề tài: '{$detai->TenDeTai}'.",
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã cập nhật Giảng viên phản biện đề cương cho đề tài '{$detai->TenDeTai}'!");
    }

    // =========================================================================
    // DUYỆT ĐỀ TÀI ĐỀ XUẤT (Bước 1 của TBM: kiểm tra và duyệt đề xuất)
    // Sau bước này → TBM phải phân công phản biện đề cương
    // =========================================================================
    public function approve(Request $request, $id)
    {
        $boMon = $this->getBoMon();
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        $maGVTBM = $gv ? $gv->MaGV : ($user->TenDangNhap ?? 'TBM');

        $detai = DeTai::findOrFail($id);
        $this->assertInScope($detai, $boMon->MaBoMon);

        // Chỉ duyệt khi đang ở trạng thái chờ duyệt BM
        if ($detai->TrangThai !== 'Chờ duyệt cấp Bộ môn') {
            return redirect()->back()->withErrors('Đề tài không ở trạng thái "Chờ duyệt cấp Bộ môn" nên không thể duyệt đề xuất.');
        }

        DB::transaction(function () use ($detai, $maGVTBM) {
            // Duyệt đề xuất → cần tiếp tục phân công phản biện đề cương
            // Giữ TrangThai = 'Chờ duyệt cấp Bộ môn' nhưng ghi nhận TBM đã duyệt đề xuất
            // (TrangThai sẽ chuyển khi TBM phân công PB → 'Đang phản biện đề cương')
            // Để tránh lẫn lộn, ta đánh dấu đề tài đã qua bước duyệt bằng NgayDuyetBM
            $detai->update([
                'NgayDuyetBM'  => now(),
                'NguoiDuyetBM' => $maGVTBM,
                'LyDoTuChoi'   => null,
            ]);

            $this->ghiLichSu($detai->MaDeTai, $maGVTBM, 'TBM đã duyệt đề xuất', 'Đề tài đã được duyệt, cần phân công GV phản biện đề cương');
        });

        // Gửi thông báo cho GV đề xuất
        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '✅ Đề tài đề xuất được Trưởng bộ môn chấp thuận',
                "Đề tài '{$detai->TenDeTai}' đã được Bộ môn chấp thuận đề xuất. Trưởng bộ môn sẽ tiến hành phân công Giảng viên phản biện đề cương.",
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã duyệt đề xuất đề tài '{$detai->TenDeTai}'! Vui lòng tiến hành phân công Giảng viên phản biện đề cương.");
    }

    // =========================================================================
    // DUYỆT CHUYỂN LÊN BCN KHOA (Bước 2 của TBM: sau khi phản biện Đạt)
    // =========================================================================
    public function approveAfterPhanBien(Request $request, $id)
    {
        $boMon = $this->getBoMon();
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        $maGVTBM = $gv ? $gv->MaGV : ($user->TenDangNhap ?? 'TBM');

        $detai = DeTai::with('phanCongPhanBiens')->findOrFail($id);
        $this->assertInScope($detai, $boMon->MaBoMon);

        // Chỉ duyệt khi phản biện đã hoàn tất và kết quả là Đạt
        if ($detai->TrangThai !== 'Đã phản biện - Chờ duyệt BM') {
            return redirect()->back()->withErrors('Đề tài chưa có kết quả phản biện "Đạt" để chuyển lên BCN Khoa. Trạng thái hiện tại: ' . $detai->TrangThai);
        }

        $phanBien = $detai->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
        if (!$phanBien || $phanBien->KetQua !== 'Đạt') {
            return redirect()->back()->withErrors('Kết quả phản biện đề cương chưa đạt hoặc chưa có. Không thể chuyển lên BCN Khoa.');
        }

        DB::transaction(function () use ($detai, $maGVTBM) {
            $detai->update([
                'TrangThai'    => 'Chờ duyệt cấp Khoa',
                'NgayDuyetBM'  => $detai->NgayDuyetBM ?? now(),
                'NguoiDuyetBM' => $maGVTBM,
                'LyDoTuChoi'   => null,
            ]);

            $this->ghiLichSu($detai->MaDeTai, $maGVTBM, 'Chuyển lên BCN Khoa', 'Phản biện đề cương đạt, chuyển tiếp BCN Khoa xem xét');
        });

        // Gửi thông báo cho GV đề xuất
        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '✅ Đề tài đã được Bộ môn phê duyệt toàn bộ',
                "Đề tài '{$detai->TenDeTai}' đã hoàn tất phản biện đề cương và được Bộ môn chuyển tiếp lên BCN Khoa xem xét phê duyệt.",
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã chuyển đề tài '{$detai->TenDeTai}' lên BCN Khoa phê duyệt thành công!");
    }

    // =========================================================================
    // XỬ LÝ KẾT QUẢ "KHÔNG ĐẠT PHẢN BIỆN" (TBM chọn hướng xử lý tiếp)
    // =========================================================================
    public function xuLyKhongDat(Request $request, $id)
    {
        $request->validate([
            'HuongXuLy' => 'required|in:dung_lai,yeu_cau_sua_lai',
            'LyDo'      => 'required|string|max:1000',
        ], [
            'HuongXuLy.required' => 'Vui lòng chọn hướng xử lý.',
            'HuongXuLy.in'       => 'Hướng xử lý không hợp lệ.',
            'LyDo.required'      => 'Vui lòng nhập lý do / hướng dẫn cụ thể.',
        ]);

        $boMon = $this->getBoMon();
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        $maGVTBM = $gv ? $gv->MaGV : ($user->TenDangNhap ?? 'TBM');

        $detai = DeTai::findOrFail($id);
        $this->assertInScope($detai, $boMon->MaBoMon);

        if ($detai->TrangThai !== 'Không đạt phản biện') {
            return redirect()->back()->withErrors('Đề tài không ở trạng thái "Không đạt phản biện".');
        }

        if ($request->HuongXuLy === 'dung_lai') {
            $trangThaiMoi = 'Từ chối';
            $thongBaoGV   = "Đề tài '{$detai->TenDeTai}' không đạt phản biện đề cương và đã bị dừng theo quyết định của Bộ môn. Lý do: " . $request->LyDo;
            $lichSuAction = 'Dừng đề tài (Không đạt phản biện)';
        } else {
            $trangThaiMoi = 'Yêu cầu chỉnh sửa';
            $thongBaoGV   = "Đề tài '{$detai->TenDeTai}' không đạt phản biện đề cương. Bộ môn yêu cầu chỉnh sửa và nộp lại đề cương: " . $request->LyDo;
            $lichSuAction = 'Yêu cầu chỉnh sửa lại đề cương (Không đạt phản biện)';
        }

        DB::transaction(function () use ($detai, $trangThaiMoi, $request, $maGVTBM, $lichSuAction) {
            $detai->update([
                'TrangThai'  => $trangThaiMoi,
                'LyDoTuChoi' => trim($request->LyDo),
            ]);

            $this->ghiLichSu($detai->MaDeTai, $maGVTBM, $lichSuAction, trim($request->LyDo));
        });

        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '📋 Kết quả xử lý đề tài không đạt phản biện',
                $thongBaoGV,
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã xử lý đề tài '{$detai->TenDeTai}' theo hướng: " . ($request->HuongXuLy === 'dung_lai' ? 'Dừng đề tài.' : 'Yêu cầu chỉnh sửa lại đề cương.'));
    }

    // =========================================================================
    // YÊU CẦU CHỈNH SỬA ĐỀ XUẤT (TBM → GV)
    // =========================================================================
    public function requestEdit(Request $request, $id)
    {
        $request->validate([
            'YeuCauSua' => 'required|string|max:1000',
        ], [
            'YeuCauSua.required' => 'Vui lòng nhập nội dung yêu cầu Giảng viên chỉnh sửa.',
        ]);

        $boMon = $this->getBoMon();
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        $maGVTBM = $gv ? $gv->MaGV : ($user->TenDangNhap ?? 'TBM');

        $detai = DeTai::findOrFail($id);
        $this->assertInScope($detai, $boMon->MaBoMon);

        // Cho phép yêu cầu chỉnh sửa ở các trạng thái TBM đang xử lý
        $trangThaiHopLe = ['Chờ duyệt cấp Bộ môn', 'Đang phản biện đề cương', 'Đã phản biện - Chờ duyệt BM', 'Không đạt phản biện'];
        if (!in_array($detai->TrangThai, $trangThaiHopLe)) {
            return redirect()->back()->withErrors('Không thể yêu cầu chỉnh sửa ở trạng thái: ' . $detai->TrangThai);
        }

        DB::transaction(function () use ($detai, $request, $maGVTBM) {
            $detai->update([
                'TrangThai'   => 'Yêu cầu chỉnh sửa',
                'LyDoTuChoi'  => trim($request->YeuCauSua),
            ]);

            $this->ghiLichSu($detai->MaDeTai, $maGVTBM, 'Yêu cầu chỉnh sửa', trim($request->YeuCauSua));
        });

        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '⚠️ Yêu cầu chỉnh sửa đề tài từ Trưởng bộ môn',
                "Đề tài '{$detai->TenDeTai}' có yêu cầu chỉnh sửa từ Trưởng bộ môn: " . trim($request->YeuCauSua),
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã gửi yêu cầu chỉnh sửa đề tài tới Giảng viên đề xuất.");
    }

    // =========================================================================
    // TỪ CHỐI ĐỀ TÀI
    // =========================================================================
    public function reject(Request $request, $id)
    {
        $request->validate([
            'LyDoTuChoi' => 'required|string|max:1000',
        ], [
            'LyDoTuChoi.required' => 'Vui lòng nhập lý do từ chối đề tài.',
        ]);

        $boMon = $this->getBoMon();
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        $maGVTBM = $gv ? $gv->MaGV : ($user->TenDangNhap ?? 'TBM');

        $detai = DeTai::findOrFail($id);
        $this->assertInScope($detai, $boMon->MaBoMon);

        DB::transaction(function () use ($detai, $request, $maGVTBM) {
            $detai->update([
                'TrangThai'  => 'Từ chối',
                'LyDoTuChoi' => trim($request->LyDoTuChoi),
            ]);

            $this->ghiLichSu($detai->MaDeTai, $maGVTBM, 'Từ chối đề tài', trim($request->LyDoTuChoi));
        });

        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '❌ Đề tài bị Trưởng bộ môn từ chối',
                "Đề tài '{$detai->TenDeTai}' đã bị Trưởng bộ môn từ chối. Lý do: " . trim($request->LyDoTuChoi),
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã từ chối đề tài '{$detai->TenDeTai}'.");
    }
}
