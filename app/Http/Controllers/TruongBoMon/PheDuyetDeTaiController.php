<?php

namespace App\Http\Controllers\TruongBoMon;

use App\Http\Controllers\Controller;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\ChiTietDuyetDeTai;
use App\Helpers\IdGenerator;
use App\Services\ThongBaoService;
use App\Services\ChiTieuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PheDuyetDeTaiController extends Controller
{
    /**
     * Xác định Bộ môn do Trưởng bộ môn đang đăng nhập phụ trách
     */
    private function getBoMon()
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv && $gv->MaBoMon) {
            $bm = BoMon::where('MaBoMon', $gv->MaBoMon)->first();
            if ($bm) return $bm;
        }

        // Dự phòng parse từ username TBM_BM_CNPM_... hoặc TBM_CNPM_...
        if ($user && preg_match('/^TBM_(?:BM_)?([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
            $bm = BoMon::where('MaBoMon', $m[1])->orWhere('MaBoMon', 'BM_' . $m[1])->first();
            if ($bm) return $bm;
        }

        return BoMon::where('TruongBoMon', 'like', '%' . ($gv->HoTen ?? '') . '%')->first() ?? BoMon::first();
    }

    /**
     * Kiểm tra Giảng viên có thuộc Bộ môn của Trưởng bộ môn không (Chống truy cập chéo)
     */
    private function assertGiangVienBelongsToBoMon(GiangVien $giangVien, $boMon)
    {
        if (!$boMon) {
            abort(403, 'Không xác định được Bộ môn của tài khoản hiện tại.');
        }
        if ($giangVien->MaBoMon !== $boMon->MaBoMon) {
            abort(403, "Bạn không có quyền xem thông tin hay đề tài của giảng viên thuộc Bộ môn khác ({$giangVien->MaBoMon}).");
        }
    }

    /**
     * Kiểm tra Đề tài có thuộc Bộ môn của Trưởng bộ môn không (Chống truy cập chéo)
     */
    private function assertDeTaiBelongsToBoMon(DeTai $detai, $boMon)
    {
        if (!$boMon) {
            abort(403, 'Không xác định được Bộ môn của tài khoản hiện tại.');
        }
        $detai->loadMissing('giangVien.boMon', 'hocPhanRef');
        $detaiMaBoMon = $detai->giangVien?->MaBoMon ?? $detai->hocPhanRef?->MaBoMon;
        if ($detaiMaBoMon && $detaiMaBoMon !== $boMon->MaBoMon) {
            abort(403, "Bạn không có quyền quản lý hay phê duyệt đề tài không thuộc Bộ môn '{$boMon->TenBoMon}'.");
        }
    }

    /**
     * TRANG 1: Danh sách giảng viên theo học kỳ (P2 - Màn hình chính của TBM)
     * Route: GET /truong-bo-mon/phe-duyet-de-tai
     */
    public function index(Request $request)
    {
        $boMon = $this->getBoMon();
        $maBoMon = $boMon ? $boMon->MaBoMon : 'BM_CNPM';

        // Danh sách học kỳ (Mặc định học kỳ đang diễn ra hoặc mới nhất)
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $currentHocKy = HocKy::where('TrangThai', 'Đang diễn ra')->orderBy('MaHocKy', 'desc')->first() ?? $hocKies->first();
        $selectedHocKy = $request->input('hocKy', $request->input('MaHocKy', $currentHocKy?->MaHocKy ?? ''));

        // Tính 5 thẻ thống kê chuẩn P2 cho toàn bộ môn trong học kỳ đang chọn
        $allBmMocStats = DB::table('DeTai')
            ->select(
                DB::raw("COUNT(*) as tong_detai"),
                DB::raw("SUM(CASE WHEN TrangThai IN ('Chờ duyệt cấp Bộ môn', 'Chờ duyệt') THEN 1 ELSE 0 END) as cho_duyet"),
                DB::raw("SUM(CASE WHEN TrangThai IN ('Chờ duyệt cấp Khoa', 'Đã phê duyệt', 'Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành') THEN 1 ELSE 0 END) as da_duyet"),
                DB::raw("SUM(CASE WHEN TrangThai IN ('Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương') THEN 1 ELSE 0 END) as yeu_cau_sua"),
                DB::raw("SUM(CASE WHEN TrangThai IN ('Từ chối', 'Không đạt phản biện') THEN 1 ELSE 0 END) as tu_choi")
            )
            ->where('MaHocKy', $selectedHocKy)
            ->whereIn('MaGV', function($q) use ($maBoMon) {
                $q->select('MaGV')->from('GiangVien')->where('MaBoMon', $maBoMon);
            })
            ->first();

        $summaryKPIs = [
            'tong_detai'       => (int)($allBmMocStats->tong_detai ?? 0),
            'cho_duyet'        => (int)($allBmMocStats->cho_duyet ?? 0),
            'da_duyet'         => (int)($allBmMocStats->da_duyet ?? 0),
            'yeu_cau_sua'      => (int)($allBmMocStats->yeu_cau_sua ?? 0),
            'tu_choi'          => (int)($allBmMocStats->tu_choi ?? 0),
        ];

        // Danh sách Giảng viên CHỈ thuộc Bộ môn của Trưởng bộ môn
        $gvQuery = GiangVien::where('MaBoMon', $maBoMon);

        // 1. Tìm theo tên giảng viên, mã giảng viên hoặc tên đề tài
        if ($request->filled('search')) {
            $s = trim($request->search);
            $gvQuery->where(function ($q) use ($s, $selectedHocKy) {
                $q->where('HoTen', 'like', "%{$s}%")
                  ->orWhere('MaGV', 'like', "%{$s}%")
                  ->orWhere('Email', 'like', "%{$s}%")
                  ->orWhereHas('deTais', function ($dq) use ($s, $selectedHocKy) {
                      $dq->where('TenDeTai', 'like', "%{$s}%");
                      if ($selectedHocKy) {
                          $dq->where('MaHocKy', $selectedHocKy);
                      }
                  });
            });
        }

        // 2. Lọc theo trạng thái đề tài
        if ($request->filled('trang_thai') && $request->trang_thai !== 'tat_ca') {
            $status = $request->trang_thai;
            $gvQuery->whereHas('deTais', function ($dq) use ($status, $selectedHocKy) {
                if ($selectedHocKy) {
                    $dq->where('MaHocKy', $selectedHocKy);
                }
                if ($status === 'cho_duyet') {
                    $dq->whereIn('TrangThai', ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt']);
                } elseif ($status === 'yeu_cau_sua') {
                    $dq->whereIn('TrangThai', ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương']);
                } elseif ($status === 'da_duyet') {
                    $dq->whereIn('TrangThai', ['Chờ duyệt cấp Khoa', 'Đã phê duyệt', 'Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành']);
                } elseif ($status === 'tu_choi') {
                    $dq->whereIn('TrangThai', ['Từ chối', 'Không đạt phản biện']);
                }
            });
        }

        $giangViens = $gvQuery->orderBy('HoTen')->get();
        $gvIds = $giangViens->pluck('MaGV')->toArray();

        // 1 QUERY GỘP (GROUP BY) tính số liệu cho từng GV trong học kỳ đang chọn -> Tránh N+1
        $topicStats = [];
        if (!empty($gvIds) && !empty($selectedHocKy)) {
            $rawStats = DB::table('DeTai')
                ->select(
                    'MaGV',
                    DB::raw("COUNT(*) as total"),
                    DB::raw("SUM(CASE WHEN TrangThai IN ('Chờ duyệt cấp Bộ môn', 'Chờ duyệt') THEN 1 ELSE 0 END) as cho_duyet"),
                    DB::raw("SUM(CASE WHEN TrangThai IN ('Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương') THEN 1 ELSE 0 END) as yeu_cau_sua"),
                    DB::raw("SUM(CASE WHEN TrangThai IN ('Chờ duyệt cấp Khoa', 'Đã phê duyệt', 'Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành') THEN 1 ELSE 0 END) as da_duyet"),
                    DB::raw("SUM(CASE WHEN TrangThai IN ('Từ chối', 'Không đạt phản biện') THEN 1 ELSE 0 END) as tu_choi")
                )
                ->where('MaHocKy', $selectedHocKy)
                ->whereIn('MaGV', $gvIds)
                ->groupBy('MaGV')
                ->get();

            foreach ($rawStats as $stat) {
                $topicStats[$stat->MaGV] = [
                    'total'        => (int)$stat->total,
                    'cho_duyet'    => (int)$stat->cho_duyet,
                    'yeu_cau_sua'  => (int)$stat->yeu_cau_sua,
                    'da_duyet'     => (int)$stat->da_duyet,
                    'tu_choi'      => (int)$stat->tu_choi,
                ];
            }
        }

        // Tính chỉ tiêu cho tất cả giảng viên qua ChiTieuService batch (Tránh N+1)
        $chiTieuStats = ChiTieuService::getChiTieuStatsBatch($gvIds, $selectedHocKy);

        // 3. Lọc theo tình trạng chỉ tiêu hướng dẫn (còn chỉ tiêu / hết chỉ tiêu / vượt chỉ tiêu)
        if ($request->filled('tinh_trang_chi_tieu') && $request->tinh_trang_chi_tieu !== 'tat_ca') {
            $ttct = $request->tinh_trang_chi_tieu;
            $giangViens = $giangViens->filter(function ($gv) use ($chiTieuStats, $ttct) {
                $stat = $chiTieuStats[$gv->MaGV] ?? null;
                return $stat && ($stat['tinh_trang'] === $ttct);
            });
        }

        return view('truongbomon.phe_duyet_detai.index', compact(
            'boMon',
            'giangViens',
            'topicStats',
            'chiTieuStats',
            'hocKies',
            'currentHocKy',
            'selectedHocKy',
            'summaryKPIs'
        ));
    }

    /**
     * TRANG 2: Danh sách đề tài của giảng viên
     * Route: GET /truong-bo-mon/phe-duyet-de-tai/giang-vien/{maGV}?hocKy=...
     */
    public function danhSachDeTaiGV(Request $request, $maGV)
    {
        $boMon = $this->getBoMon();
        $giangVien = GiangVien::with('boMon')->where('MaGV', $maGV)->firstOrFail();

        // BẢO MẬT: Chặn truy cập chéo nếu giảng viên không thuộc bộ môn
        $this->assertGiangVienBelongsToBoMon($giangVien, $boMon);

        // Học kỳ đang xem
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $currentHocKy = HocKy::where('TrangThai', 'Đang diễn ra')->orderBy('MaHocKy', 'desc')->first() ?? $hocKies->first();
        $selectedHocKy = $request->input('hocKy', $request->input('MaHocKy', $currentHocKy?->MaHocKy ?? ''));
        $currentHocKyModel = HocKy::where('MaHocKy', $selectedHocKy)->first() ?? $currentHocKy;

        // Query đề tài của giảng viên này
        $query = DeTai::with(['hocKy', 'hocPhanRef'])
            ->where('MaGV', $maGV);

        if ($selectedHocKy && $selectedHocKy !== 'ALL') {
            $query->where('MaHocKy', $selectedHocKy);
        }

        // Lọc theo trạng thái đề tài
        $selectedStatus = $request->input('trang_thai', 'ALL');
        if ($selectedStatus && $selectedStatus !== 'ALL') {
            if ($selectedStatus === 'cho_duyet') {
                $query->whereIn('TrangThai', ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt']);
            } elseif ($selectedStatus === 'yeu_cau_sua') {
                $query->whereIn('TrangThai', ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương']);
            } elseif ($selectedStatus === 'da_duyet') {
                $query->whereIn('TrangThai', [
                    'Chờ duyệt cấp Khoa', 'Đã phê duyệt', 'Trưởng khoa đã duyệt',
                    'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành'
                ]);
            } elseif ($selectedStatus === 'tu_choi') {
                $query->whereIn('TrangThai', ['Từ chối', 'Không đạt phản biện']);
            } else {
                $query->where('TrangThai', $selectedStatus);
            }
        }

        // SẮP XẾP MẶC ĐỊNH: "Chờ duyệt" lên đầu, rồi theo ngày đề xuất mới nhất
        $query->orderByRaw("CASE 
            WHEN TrangThai IN ('Chờ duyệt cấp Bộ môn', 'Chờ duyệt') THEN 1 
            WHEN TrangThai IN ('Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương') THEN 2
            WHEN TrangThai IN ('Chờ duyệt cấp Khoa', 'Đã phê duyệt', 'Trưởng khoa đã duyệt', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành') THEN 3 
            ELSE 4 END")
        ->orderBy('created_at', 'desc');

        $detais = $query->get();

        // Đếm theo từng trạng thái để làm tabs/filter pills
        $baseCounts = DeTai::where('MaGV', $maGV);
        if ($selectedHocKy && $selectedHocKy !== 'ALL') {
            $baseCounts->where('MaHocKy', $selectedHocKy);
        }
        $rawCountList = (clone $baseCounts)->pluck('TrangThai');

        $statFilter = [
            'total'       => $rawCountList->count(),
            'cho_duyet'   => $rawCountList->filter(fn($t) => in_array($t, ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt']))->count(),
            'yeu_cau_sua' => $rawCountList->filter(fn($t) => in_array($t, ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương']))->count(),
            'da_duyet'    => $rawCountList->filter(fn($t) => in_array($t, [
                'Chờ duyệt cấp Khoa', 'Đã phê duyệt', 'Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành'
            ]))->count(),
            'tu_choi'     => $rawCountList->filter(fn($t) => in_array($t, ['Từ chối', 'Không đạt phản biện']))->count(),
        ];

        return view('truongbomon.phe_duyet_detai.giang_vien', compact(
            'boMon',
            'giangVien',
            'detais',
            'hocKies',
            'selectedHocKy',
            'currentHocKyModel',
            'selectedStatus',
            'statFilter'
        ));
    }

    /**
     * TRANG 3: Chi tiết và phê duyệt đề tài
     * Route: GET /truong-bo-mon/phe-duyet-de-tai/de-tai/{maDeTai}
     */
    public function chiTietDeTai(Request $request, $maDeTai)
    {
        $boMon = $this->getBoMon();
        $detai = DeTai::with([
            'giangVien.boMon',
            'hocKy',
            'hocPhanRef',
            'chiTietDuyetDeTais' => fn($q) => $q->orderBy('created_at', 'desc')->orderBy('NgayDuyet', 'desc'),
        ])->where('MaDeTai', $maDeTai)->firstOrFail();

        // BẢO MẬT: Chặn truy cập chéo nếu đề tài không thuộc bộ môn
        $this->assertDeTaiBelongsToBoMon($detai, $boMon);

        $selectedHocKy = $request->input('hocKy', $detai->MaHocKy);

        // Kiểm tra xem đề tài có đang ở trạng thái Chờ duyệt hay không
        $isChoDuyet = in_array($detai->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt']);

        return view('truongbomon.phe_duyet_detai.show', compact(
            'boMon',
            'detai',
            'selectedHocKy',
            'isChoDuyet'
        ));
    }

    /**
     * THAO TÁC 1: Phê duyệt đề tài (cấp Bộ môn)
     * Route: POST /truong-bo-mon/phe-duyet-de-tai/de-tai/{maDeTai}/duyet
     */
    public function duyetDeTai(Request $request, $maDeTai)
    {
        $request->validate([
            'GhiChu' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $currentGv = GiangVien::getLoggedInGiangVien($user);
        $boMon = $this->getBoMon();

        try {
            DB::transaction(function () use ($request, $maDeTai, $boMon, $currentGv, $user) {
                // Lock dòng để tránh race condition duyệt trùng (mở 2 tab)
                $detai = DeTai::where('MaDeTai', $maDeTai)->lockForUpdate()->firstOrFail();

                // Kiểm tra bộ môn
                $this->assertDeTaiBelongsToBoMon($detai, $boMon);

                // Kiểm tra trạng thái ở server-side: chỉ được duyệt khi trạng thái là "Chờ duyệt"
                if (!in_array($detai->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt'])) {
                    throw new \Exception("Đề tài hiện đang ở trạng thái '{$detai->TrangThai}', không thể phê duyệt lại.");
                }

                $oldStatus = $detai->TrangThai;
                $newStatus = 'Chờ duyệt cấp Khoa'; // Tương thích hoàn toàn với luồng Trưởng khoa bước tiếp theo

                $ghiChu = trim($request->GhiChu ?? '');
                $lyDoLuu = !empty($ghiChu) ? $ghiChu : 'Trưởng bộ môn đã phê duyệt đề xuất đề tài.';

                $detai->update([
                    'TrangThai'    => $newStatus,
                    'NgayDuyetBM'  => now(),
                    'NguoiDuyetBM' => $currentGv ? $currentGv->MaGV : ($user->TenDangNhap ?? 'TBM'),
                    'LyDoTuChoi'   => null,
                ]);

                // Ghi 1 dòng lịch sử xử lý bất biến
                $maDuyet = IdGenerator::nextChiTietDuyetDeTai() ?? ('CTD' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT));
                ChiTietDuyetDeTai::create([
                    'MaDuyet'       => $maDuyet,
                    'MaDeTai'       => $detai->MaDeTai,
                    'MaGV'          => $currentGv?->MaGV,
                    'NguoiThucHien' => ($currentGv?->HoTen ? $currentGv->HoTen . ' (' . $currentGv->MaGV . ')' : $user->TenDangNhap) . ' - Trưởng Bộ Môn',
                    'HanhDong'      => 'Phê duyệt',
                    'TrangThaiCu'   => $oldStatus,
                    'TrangThai'     => $newStatus,
                    'LyDo'          => $lyDoLuu,
                    'NgayDuyet'     => now(),
                ]);

                // Gửi thông báo đến giảng viên đề xuất
                $detai->loadMissing('giangVien.taiKhoan');
                $maTK = $detai->giangVien?->MaTK ?? $detai->giangVien?->taiKhoan?->MaTK;
                if ($maTK) {
                    ThongBaoService::guiDen(
                        $maTK,
                        '✅ Đề tài đã được Trưởng bộ môn phê duyệt',
                        "Đề tài '{$detai->TenDeTai}' đã được Trưởng bộ môn phê duyệt và chuyển tiếp lên Ban Chủ Nhiệm Khoa xem xét." . (!empty($ghiChu) ? " (Ghi chú: {$ghiChu})" : ""),
                        'Đề tài'
                    );
                }
            });

            return redirect()->route('truongbomon.phe_duyet_detai.show', $maDeTai)
                ->with('success', "Đã phê duyệt đề tài thành công! Đề tài đã được chuyển tiếp lên Trưởng khoa.");
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * THAO TÁC 2: Yêu cầu chỉnh sửa đề tài
     * Route: POST /truong-bo-mon/phe-duyet-de-tai/de-tai/{maDeTai}/yeu-cau-chinh-sua
     */
    public function yeuCauChinhSua(Request $request, $maDeTai)
    {
        $request->validate([
            'YeuCauSua' => 'required|string|min:5|max:1000',
        ], [
            'YeuCauSua.required' => 'Vui lòng nhập nội dung góp ý / yêu cầu chỉnh sửa cho giảng viên.',
            'YeuCauSua.min'      => 'Nội dung góp ý phải có tối thiểu 5 ký tự.',
            'YeuCauSua.max'      => 'Nội dung góp ý không được vượt quá 1000 ký tự.',
        ]);

        $user = Auth::user();
        $currentGv = GiangVien::getLoggedInGiangVien($user);
        $boMon = $this->getBoMon();

        try {
            DB::transaction(function () use ($request, $maDeTai, $boMon, $currentGv, $user) {
                // Lock dòng chống race condition
                $detai = DeTai::where('MaDeTai', $maDeTai)->lockForUpdate()->firstOrFail();

                // Kiểm tra bộ môn
                $this->assertDeTaiBelongsToBoMon($detai, $boMon);

                // Kiểm tra trạng thái server-side
                if (!in_array($detai->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt'])) {
                    throw new \Exception("Đề tài hiện đang ở trạng thái '{$detai->TrangThai}', không thể gửi yêu cầu chỉnh sửa.");
                }

                $oldStatus = $detai->TrangThai;
                $newStatus = 'Yêu cầu chỉnh sửa';
                $noiDungGopY = trim($request->YeuCauSua);

                $detai->update([
                    'TrangThai'   => $newStatus,
                    'LyDoTuChoi'  => $noiDungGopY,
                ]);

                // Ghi 1 dòng lịch sử xử lý bất biến
                $maDuyet = IdGenerator::nextChiTietDuyetDeTai() ?? ('CTD' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT));
                ChiTietDuyetDeTai::create([
                    'MaDuyet'       => $maDuyet,
                    'MaDeTai'       => $detai->MaDeTai,
                    'MaGV'          => $currentGv?->MaGV,
                    'NguoiThucHien' => ($currentGv?->HoTen ? $currentGv->HoTen . ' (' . $currentGv->MaGV . ')' : $user->TenDangNhap) . ' - Trưởng Bộ Môn',
                    'HanhDong'      => 'Yêu cầu chỉnh sửa',
                    'TrangThaiCu'   => $oldStatus,
                    'TrangThai'     => $newStatus,
                    'LyDo'          => $noiDungGopY,
                    'NgayDuyet'     => now(),
                ]);

                // Gửi thông báo đến GV đề xuất
                $detai->loadMissing('giangVien.taiKhoan');
                $maTK = $detai->giangVien?->MaTK ?? $detai->giangVien?->taiKhoan?->MaTK;
                if ($maTK) {
                    ThongBaoService::guiDen(
                        $maTK,
                        '⚠️ Yêu cầu chỉnh sửa đề tài từ Trưởng bộ môn',
                        "Đề tài '{$detai->TenDeTai}' có yêu cầu chỉnh sửa từ Trưởng bộ môn: {$noiDungGopY}",
                        'Đề tài'
                    );
                }
            });

            return redirect()->route('truongbomon.phe_duyet_detai.show', $maDeTai)
                ->with('success', "Đã gửi yêu cầu chỉnh sửa đề tài tới Giảng viên thành công.");
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * THAO TÁC 3: Từ chối đề tài
     * Route: POST /truong-bo-mon/phe-duyet-de-tai/de-tai/{maDeTai}/tu-choi
     */
    public function tuChoi(Request $request, $maDeTai)
    {
        $request->validate([
            'LyDoTuChoi' => 'required|string|min:5|max:1000',
        ], [
            'LyDoTuChoi.required' => 'Vui lòng nhập lý do từ chối đề tài.',
            'LyDoTuChoi.min'      => 'Lý do từ chối phải có tối thiểu 5 ký tự.',
            'LyDoTuChoi.max'      => 'Lý do từ chối không được vượt quá 1000 ký tự.',
        ]);

        $user = Auth::user();
        $currentGv = GiangVien::getLoggedInGiangVien($user);
        $boMon = $this->getBoMon();

        try {
            DB::transaction(function () use ($request, $maDeTai, $boMon, $currentGv, $user) {
                // Lock dòng chống race condition
                $detai = DeTai::where('MaDeTai', $maDeTai)->lockForUpdate()->firstOrFail();

                // Kiểm tra bộ môn
                $this->assertDeTaiBelongsToBoMon($detai, $boMon);

                // Kiểm tra trạng thái server-side
                if (!in_array($detai->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt'])) {
                    throw new \Exception("Đề tài hiện đang ở trạng thái '{$detai->TrangThai}', không thể từ chối.");
                }

                $oldStatus = $detai->TrangThai;
                $newStatus = 'Từ chối';
                $lyDo = trim($request->LyDoTuChoi);

                $detai->update([
                    'TrangThai'  => $newStatus,
                    'LyDoTuChoi' => $lyDo,
                ]);

                // Ghi 1 dòng lịch sử xử lý bất biến
                $maDuyet = IdGenerator::nextChiTietDuyetDeTai() ?? ('CTD' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT));
                ChiTietDuyetDeTai::create([
                    'MaDuyet'       => $maDuyet,
                    'MaDeTai'       => $detai->MaDeTai,
                    'MaGV'          => $currentGv?->MaGV,
                    'NguoiThucHien' => ($currentGv?->HoTen ? $currentGv->HoTen . ' (' . $currentGv->MaGV . ')' : $user->TenDangNhap) . ' - Trưởng Bộ Môn',
                    'HanhDong'      => 'Từ chối',
                    'TrangThaiCu'   => $oldStatus,
                    'TrangThai'     => $newStatus,
                    'LyDo'          => $lyDo,
                    'NgayDuyet'     => now(),
                ]);

                // Gửi thông báo đến GV đề xuất
                $detai->loadMissing('giangVien.taiKhoan');
                $maTK = $detai->giangVien?->MaTK ?? $detai->giangVien?->taiKhoan?->MaTK;
                if ($maTK) {
                    ThongBaoService::guiDen(
                        $maTK,
                        '❌ Đề tài bị Trưởng bộ môn từ chối',
                        "Đề tài '{$detai->TenDeTai}' đã bị Trưởng bộ môn từ chối. Lý do: {$lyDo}",
                        'Đề tài'
                    );
                }
            });

            return redirect()->route('truongbomon.phe_duyet_detai.show', $maDeTai)
                ->with('success', "Đã từ chối đề tài thành công.");
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
