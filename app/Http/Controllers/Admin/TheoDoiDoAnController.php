<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nhom;
use App\Models\KeHoachKhoaLuan;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\Nganh;
use App\Models\TienDoNghiepVuKhoaLuan;
use App\Models\LichSuChuyenGiaiDoan;
use App\Models\GiaiDoanThucHien;
use App\Services\PlanPhaseService;
use App\Services\ThongBaoService;
use App\Services\ThesisProgressService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TheoDoiDoAnController extends Controller
{
    protected ThesisProgressService $progressService;

    public function __construct(ThesisProgressService $progressService)
    {
        $this->progressService = $progressService;
    }

    public function index(Request $request)
    {
        $activePlan = PlanPhaseService::getActivePlan($request->MaKhoa, $request->MaBoMon);
        $currentPhase = PlanPhaseService::getCurrentPhase($activePlan);

        $query = Nhom::with([
            'deTai.giangVien.boMon.khoa',
            'truongNhom.lop.nganh.khoa',
            'thanhViens.sinhVien.lop',
            'dangKyDeTai.giangVienHuongDan',
            'hoSoBaoVe',
            'tienDoNghiepVu.lichSu',
        ]);

        // 1. Tìm kiếm từ khóa (Mã nhóm, Tên nhóm, Tên đề tài, Mã đề tài, Tên/Mã SV)
        if ($request->filled('search')) {
            $s = trim($request->search);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $query->where(function ($q) use ($s, $sClean) {
                $q->where('MaNhom', 'like', "%{$s}%")
                  ->orWhere('TenNhom', 'like', "%{$s}%")
                  ->orWhereHas('deTai', function($dq) use ($s, $sClean) {
                      $dq->where('TenDeTai', 'like', "%{$s}%");
                      if (!empty($sClean)) {
                          $dq->orWhere('MaDeTai', 'like', "%{$sClean}%");
                      }
                  })
                  ->orWhereHas('thanhViens.sinhVien', function($sq) use ($s, $sClean) {
                      $sq->where('HoTen', 'like', "%{$s}%")
                         ->orWhere('MaSV', 'like', "%{$s}%");
                      if (!empty($sClean)) {
                          $sq->orWhere('MaSV', 'like', "%{$sClean}%");
                      }
                  });
                if (!empty($sClean)) {
                    $q->orWhere('MaNhom', 'like', "%{$sClean}%");
                }
            });
        }

        // 2. Lọc theo Học kỳ
        if ($request->filled('MaHocKy')) {
            $query->where(function ($q) use ($request) {
                $q->where('MaHocKy', $request->MaHocKy)
                  ->orWhereHas('deTai', fn($dq) => $dq->where('MaHocKy', $request->MaHocKy));
            });
        }

        // 3. Lọc theo Khoa
        if ($request->filled('MaKhoa')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('truongNhom.lop.nganh', fn($nq) => $nq->where('MaKhoa', $request->MaKhoa))
                  ->orWhereHas('deTai.giangVien.boMon', fn($bq) => $bq->where('MaKhoa', $request->MaKhoa));
            });
        }

        // 4. Lọc theo Bộ môn
        if ($request->filled('MaBoMon')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('deTai.giangVien', fn($gq) => $gq->where('MaBoMon', $request->MaBoMon))
                  ->orWhereHas('deTai', fn($dq) => $dq->where('MaBoMon', $request->MaBoMon));
            });
        }

        // 5. Lọc theo Giảng viên hướng dẫn
        if ($request->filled('MaGV')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('deTai', fn($dtQ) => $dtQ->where('MaGV', $request->MaGV))
                  ->orWhereHas('dangKyDeTai.deTai', fn($dtQ) => $dtQ->where('MaGV', $request->MaGV));
            });
        }

        // 6. Lọc theo Ngành
        if ($request->filled('MaNganh')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('truongNhom.lop', fn($lq) => $lq->where('MaNganh', $request->MaNganh))
                  ->orWhereHas('deTai', fn($dq) => $dq->where('MaNganh', $request->MaNganh));
            });
        }

        $allNhoms = $query->get();

        // 6 Giai đoạn nghiệp vụ chuẩn của Giáo vụ
        $stageDefinitions = TienDoNghiepVuKhoaLuan::DANH_SACH_GIAI_DOAN;

        // Xử lý và thẩm định nghiệp vụ 6 giai đoạn cho từng nhóm
        $processedAll = $allNhoms->map(function ($nhom) {
            return self::processNhomStageData($nhom);
        });

        // Thống kê 5 thẻ KPI tổng quan (Không đếm trùng lặp)
        $stats = [
            'total'          => $processedAll->count(),
            'dang_thuc_hien' => $processedAll->where('status_code', 'DANG_THUC_HIEN')->count(),
            'cho_xu_ly'      => $processedAll->where('status_code', 'CHO_XU_LY')->count(),
            'qua_han'        => $processedAll->where('status_code', 'QUA_HAN')->count(),
            'hoan_thanh'     => $processedAll->where('status_code', 'HOAN_THANH')->count(),
        ];

        // Thống kê chi tiết cho 6 Giai đoạn nghiệp vụ
        $stageStats = [];
        foreach ($stageDefinitions as $stNum => $stMeta) {
            $inStage = $processedAll->where('current_stage', $stNum);
            $completedConditionCount = $processedAll->filter(function($n) use ($stNum) {
                return !empty($n->dieu_kien_giai_doan[$stNum]['dat']);
            })->count();

            $stageStats[$stNum] = [
                'meta' => $stMeta,
                'total_in_stage' => $inStage->count(),
                'dang_thuc_hien' => $inStage->where('status_code', 'DANG_THUC_HIEN')->count(),
                'cho_xu_ly'      => $inStage->where('status_code', 'CHO_XU_LY')->count(),
                'qua_han'        => $inStage->where('status_code', 'QUA_HAN')->count(),
                'hoan_thanh'     => $inStage->where('status_code', 'HOAN_THANH')->count(),
                'completed_condition_count' => $completedConditionCount,
                'completion_rate' => $processedAll->count() > 0 ? (int)round(($completedConditionCount / $processedAll->count()) * 100) : 0,
            ];
        }

        // Lọc danh sách nhóm cho Tab 2 (Danh sách theo dõi)
        $filteredNhoms = $processedAll;
        if ($request->filled('stage') && is_numeric($request->stage)) {
            $stageFilter = (int) $request->stage;
            $filteredNhoms = $filteredNhoms->where('current_stage', $stageFilter)->values();
        }
        if ($request->filled('status_code')) {
            $filteredNhoms = $filteredNhoms->where('status_code', $request->status_code)->values();
        }

        // PHÂN TRANG 5 DÒNG MỖI TRANG CHO TAB 2
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 5;
        $currentItems = $filteredNhoms->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedNhoms = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $filteredNhoms->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        // Danh sách nhóm cần theo dõi & nhắc nhở (Tab 3)
        $warningNhoms = $processedAll->filter(function($n) {
            if ($n->status_code === 'QUA_HAN' || $n->status_code === 'CHO_XU_LY') return true;
            if (!$n->deTai || !$n->deTai->giangVien) return true;
            if ($n->current_stage >= 3 && empty($n->dieu_kien_giai_doan[3]['dat'])) return true;
            return false;
        })->map(function($n) {
            $reasons = [];
            if ($n->status_code === 'QUA_HAN') {
                $reasons[] = 'Nhóm bị trễ hạn hoặc quá hạn xử lý ở giai đoạn nghiệp vụ hiện tại';
            }
            if ($n->status_code === 'CHO_XU_LY') {
                $reasons[] = 'Hồ sơ đang chờ bước xử lý / phê duyệt từ phía Bộ môn hoặc Khoa';
            }
            if (!$n->deTai) {
                $reasons[] = 'Chưa đăng ký đề tài hoặc chưa được duyệt đề tài chính thức';
            } elseif (!$n->deTai->giangVien) {
                $reasons[] = 'Đề tài chưa được phân công Giảng viên hướng dẫn';
            }
            if ($n->current_stage >= 3 && empty($n->dieu_kien_giai_doan[3]['dat'])) {
                $reasons[] = 'Chưa nộp file đề cương chi tiết theo quy định';
            }
            $n->warning_reasons = $reasons;
            return $n;
        })->values();

        $hocKies = HocKy::orderBy('NamHoc', 'desc')->orderBy('TenHocKy', 'desc')->get();
        $giangViens = GiangVien::orderBy('HoTen')->get();
        $khoas = Khoa::orderBy('TenKhoa')->get();
        $boMons = BoMon::orderBy('TenBoMon')->get();
        $nganhs = Nganh::orderBy('TenNganh')->get();

        return view('admin.theodoi.index', compact(
            'paginatedNhoms', 'filteredNhoms', 'stats', 'stageStats', 'stageDefinitions',
            'activePlan', 'currentPhase', 'warningNhoms',
            'hocKies', 'giangViens', 'khoas', 'boMons', 'nganhs'
        ));
    }

    /**
     * Giáo vụ xem chi tiết theo dõi một đề tài / nhóm sinh viên
     */
    public function show($id)
    {
        $nhom = Nhom::with([
            'truongNhom.lop.nganh.khoa',
            'thanhViens.sinhVien.lop.nganh',
            'deTai.giangVien.boMon.khoa',
            'deTai.hocPhan.boMon',
            'dangKyDeTai.giangVienHuongDan.boMon',
            'dangKyDeTai.deTai.giangVien.boMon',
            'hoSoBaoVe.hoiDong.thanhViens.giangVien',
            'tienDoNghiepVu',
            'hocKy',
            'hocPhan.boMon',
        ])->findOrFail($id);

        $activePlan = PlanPhaseService::getActivePlan();
        $currentPhase = PlanPhaseService::getCurrentPhase($activePlan);

        $nhom = self::processNhomStageData($nhom);

        // Lấy thông tin GV phản biện nếu có
        $phanBien = null;
        if ($nhom->deTai) {
            $phanBien = \App\Models\PhanCongPhanBien::with('giangVien.boMon')
                ->where('MaDeTai', $nhom->deTai->MaDeTai)
                ->first();
        }

        // Lấy chi tiết các giai đoạn kỹ thuật & đợt nộp do GVHD quản lý (Giám sát liên kết)
        $giaiDoansGV = collect();
        if ($nhom->deTai) {
            $giaiDoansGV = GiaiDoanThucHien::where('MaDeTai', $nhom->deTai->MaDeTai)
                ->with(['dotNops.khoiBaoCaos.tomTat'])
                ->orderBy('ThuTu')
                ->get();
        }

        $stageDefinitions = TienDoNghiepVuKhoaLuan::DANH_SACH_GIAI_DOAN;

        return view('admin.theodoi.show', compact(
            'nhom', 'activePlan', 'currentPhase', 'phanBien', 'giaiDoansGV', 'stageDefinitions'
        ));
    }

    /**
     * Bảo vệ phía máy chủ: Giáo vụ không được phép tự ý thay đổi tiến độ chuyên môn hoặc chuyển trạng thái tùy tiện
     */
    public function chuyenGiaiDoan(Request $request)
    {
        return redirect()->route('admin.theodoi.index')
            ->with('error', 'Thao tác bị từ chối: Giáo vụ chỉ có quyền theo dõi và giám sát tiến độ nghiệp vụ. Trạng thái 6 giai đoạn được hệ thống đồng bộ tự động từ quy trình phê duyệt và báo cáo thực tế.');
    }

    /**
     * Tra cứu toàn bộ lịch sử nghiệp vụ & nhắc nhở của nhóm (Trang riêng phân trang 5 bản ghi/trang)
     */
    public function lichSu(Request $request, $id)
    {
        $nhom = Nhom::with([
            'truongNhom.lop.nganh.khoa',
            'deTai.giangVien.boMon',
            'dangKyDeTai.giangVienHuongDan',
            'tienDoNghiepVu',
            'hocKy',
        ])->findOrFail($id);

        if ($request->ajax() || ($request->wantsJson() && !$request->has('page'))) {
            $lichSu = LichSuChuyenGiaiDoan::where('MaNhom', $id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'gd_truoc' => $item->GiaiDoanTruoc ? (TienDoNghiepVuKhoaLuan::DANH_SACH_GIAI_DOAN[$item->GiaiDoanTruoc]['short'] ?? "GĐ {$item->GiaiDoanTruoc}") : 'Khởi tạo',
                        'gd_sau' => TienDoNghiepVuKhoaLuan::DANH_SACH_GIAI_DOAN[$item->GiaiDoanSau]['short'] ?? "GĐ {$item->GiaiDoanSau}",
                        'trang_thai_truoc' => $item->TrangThaiTruoc,
                        'trang_thai_sau' => $item->TrangThaiSau,
                        'hanh_dong' => $item->HanhDong,
                        'nguoi_thuc_hien' => $item->NguoiThucHien,
                        'ly_do' => $item->LyDo,
                        'thoi_gian' => \Carbon\Carbon::parse($item->created_at)->format('H:i d/m/Y'),
                    ];
                });

            return response()->json([
                'success' => true,
                'nhom' => [
                    'ma_nhom' => $nhom->MaNhom,
                    'ten_nhom' => $nhom->TenNhom,
                    'de_tai' => $nhom->deTai?->TenDeTai,
                    'gvhd' => $nhom->deTai?->giangVien?->HoTen,
                ],
                'data' => $lichSu,
            ]);
        }

        $nhom = self::processNhomStageData($nhom);

        $query = LichSuChuyenGiaiDoan::where('MaNhom', $id);

        // Bộ lọc theo loại hoạt động
        if ($request->filled('action')) {
            $query->where('HanhDong', $request->action);
        }

        // Bộ lọc theo giai đoạn
        if ($request->filled('stage') && is_numeric($request->stage)) {
            $query->where('GiaiDoanSau', (int) $request->stage);
        }

        // Bộ lọc theo khoảng thời gian
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // PHÂN TRANG ĐÚNG 5 BẢN GHI MỖI TRANG
        $logs = $query->orderBy('created_at', 'desc')->paginate(5)->withQueryString();

        $availableActions = LichSuChuyenGiaiDoan::where('MaNhom', $id)
            ->distinct()
            ->pluck('HanhDong')
            ->filter()
            ->values();

        $stageDefinitions = TienDoNghiepVuKhoaLuan::DANH_SACH_GIAI_DOAN;

        return view('admin.theodoi.lichsu', compact(
            'nhom', 'logs', 'stageDefinitions', 'availableActions'
        ));
    }

    /**
     * Xem chi tiết liên kết tiến độ thực hiện do Giảng viên quản lý (Ajax JSON)
     */
    public function chiTietTienDoGV($id)
    {
        $nhom = Nhom::with(['deTai.giangVien'])->findOrFail($id);
        $deTai = $nhom->deTai;

        if (!$deTai) {
            return response()->json([
                'success' => false,
                'message' => 'Nhóm chưa có đề tài được phân công.',
            ]);
        }

        $giaiDoans = GiaiDoanThucHien::where('MaDeTai', $deTai->MaDeTai)
            ->with(['dotNops.khoiBaoCaos'])
            ->orderBy('ThuTu')
            ->get()
            ->map(function ($gd) {
                $dotNops = $gd->dotNops->map(function ($d) {
                    $latest = $d->khoiBaoCaos->first();
                    return [
                        'ma_dot' => $d->MaDotNop,
                        'ten_dot' => $d->TenDotNop,
                        'han_nop' => $d->HanNop ? Carbon::parse($d->HanNop)->format('d/m/Y H:i') : 'Không giới hạn',
                        'trang_thai' => $d->TrangThai,
                        'so_phien_ban' => $d->khoiBaoCaos->count(),
                        'phien_ban_moi_nhat' => $latest ? [
                            'lan_nop' => $latest->LanNop,
                            'trang_thai' => $latest->TrangThai,
                            'ngay_nop' => $latest->NgayNop ? Carbon::parse($latest->NgayNop)->format('d/m/Y H:i') : null,
                            'file' => $latest->TenFile,
                            'link_code' => $latest->LinkCode,
                            'nhan_xet_gv' => $latest->NhanXetGV,
                            'ngay_nhan_xet' => $latest->NgayNhanXet ? Carbon::parse($latest->NgayNhanXet)->format('d/m/Y H:i') : null,
                        ] : null,
                    ];
                });

                return [
                    'ma_gd' => $gd->MaGiaiDoan,
                    'ten_gd' => $gd->TenGiaiDoan,
                    'trang_thai' => $gd->TrangThai,
                    'ngay_bat_dau' => $gd->NgayBatDau ? Carbon::parse($gd->NgayBatDau)->format('d/m/Y') : null,
                    'ngay_ket_thuc' => $gd->NgayKetThuc ? Carbon::parse($gd->NgayKetThuc)->format('d/m/Y') : null,
                    'dot_nops' => $dotNops,
                ];
            });

        return response()->json([
            'success' => true,
            'nhom' => [
                'ma_nhom' => $nhom->MaNhom,
                'ten_nhom' => $nhom->TenNhom,
                'de_tai' => $deTai->TenDeTai,
                'gvhd' => $deTai->giangVien?->HoTen,
            ],
            'giai_doan_gv' => $giaiDoans,
        ]);
    }

    /**
     * Nhắc nhở tiến độ nhóm
     */
    public function remindGroup($id)
    {
        $nhom = Nhom::with(['truongNhom', 'thanhViens', 'dangKyDeTai'])->findOrFail($id);

        $title = "[CẢNH BÁO TIẾN ĐỘ] Nhắc nhở hoàn thiện hạn nộp báo cáo Khóa luận";
        $content = "Hệ thống ghi nhận nhóm '{$nhom->TenNhom}' (Mã nhóm: {$nhom->MaNhom}) sắp đến hạn hoặc đã quá hạn nộp báo cáo tiến độ. Vui lòng kiểm tra và hoàn thành gấp.";

        if ($nhom->truongNhom && $nhom->truongNhom->MaTK) {
            ThongBaoService::guiDen($nhom->truongNhom->MaTK, $title, $content, 'Báo cáo');
        }

        foreach ($nhom->thanhViens as $tv) {
            if ($tv->sinhVien && $tv->sinhVien->MaTK) {
                ThongBaoService::guiDen($tv->sinhVien->MaTK, $title, $content, 'Báo cáo');
            }
        }

        // Ghi lại lịch sử thao tác của Giáo vụ: Gửi nhắc nhở tiến độ (Không cập nhật trạng thái nghiệp vụ)
        $tienDo = $nhom->tienDoNghiepVu;
        LichSuChuyenGiaiDoan::create([
            'MaNhom' => $nhom->MaNhom,
            'GiaiDoanTruoc' => $tienDo?->GiaiDoanHienTai ?? 1,
            'GiaiDoanSau' => $tienDo?->GiaiDoanHienTai ?? 1,
            'TrangThaiTruoc' => $tienDo?->TrangThaiGiaiDoan ?? 'DANG_THUC_HIEN',
            'TrangThaiSau' => $tienDo?->TrangThaiGiaiDoan ?? 'DANG_THUC_HIEN',
            'HanhDong' => 'Gửi thông báo nhắc tiến độ',
            'NguoiThucHien' => 'Giáo vụ',
            'LyDo' => 'Giáo vụ gửi thông báo cảnh báo nhắc nhở tiến độ tới nhóm sinh viên và GVHD',
        ]);

        return redirect()->back()->with('success', "🔔 Đã gửi thông báo nhắc tiến độ thành công tới nhóm '{$nhom->TenNhom}'!");
    }

    /**
     * Phương thức tĩnh xử lý và gán dữ liệu tiến độ nghiệp vụ cho nhóm
     */
    public static function processNhomStageData($nhom)
    {
        $progressService = app(ThesisProgressService::class);
        $eval = $progressService->tuDongDanhGiaGiaiDoanNghiepVu($nhom);
        $tienDo = $eval['tien_do'];
        $nhom->tien_do_nghiep_vu = $tienDo;
        $nhom->dieu_kien_giai_doan = $eval['dieu_kien'];
        $nhom->suggested_stage = $eval['suggested_stage'];

        $currStage = $tienDo->GiaiDoanHienTai;
        $nhom->current_stage = $currStage;
        $nhom->current_stage_label = TienDoNghiepVuKhoaLuan::DANH_SACH_GIAI_DOAN[$currStage]['short'] ?? "GĐ {$currStage}";
        $nhom->status_code = $tienDo->TrangThaiGiaiDoan;
        $nhom->status_label = match($tienDo->TrangThaiGiaiDoan) {
            'HOAN_THANH' => 'Đã hoàn thành',
            'QUA_HAN' => 'Quá hạn',
            'CHO_XU_LY' => 'Chờ xử lý',
            default => 'Đang thực hiện',
        };

        // Gắn stage breakdown chi tiết từng giai đoạn 1..6
        $breakdownData = \App\Services\ThesisProgressService::getStageProgressBreakdown($nhom, $eval['dieu_kien'], $currStage);
        $nhom->stage_breakdown = $breakdownData['stages'];
        $nhom->completed_stages = $breakdownData['completed_stages_count'];
        $nhom->progress_percent = $breakdownData['overall_percent'];

        // Gắn thông tin học phần
        $hocPhan = $nhom->hocPhan ?? $nhom->deTai?->hocPhan;
        $nhom->hoc_phan = $hocPhan;
        $nhom->ma_hoc_phan = $hocPhan?->MaHocPhan ?? $nhom->MaHocPhan;
        $nhom->ten_hoc_phan = $hocPhan?->TenHocPhan ?? 'Khóa luận tốt nghiệp';

        return $nhom;
    }
}

