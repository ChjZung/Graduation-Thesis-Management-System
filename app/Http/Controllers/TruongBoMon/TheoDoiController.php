<?php

namespace App\Http\Controllers\TruongBoMon;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\TheoDoiDoAnController;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\Nhom;
use App\Models\HocKy;
use App\Models\GiaiDoanThucHien;
use App\Models\LichSuChuyenGiaiDoan;
use App\Models\TienDoNghiepVuKhoaLuan;
use App\Models\PhanCongPhanBien;
use App\Models\HocPhan;
use App\Services\PlanPhaseService;
use App\Services\ThongBaoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TheoDoiController extends Controller
{
    private function getBoMon()
    {
        $user = Auth::user();
        if (!$user) return null;

        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv && $gv->MaBoMon) {
            $bm = BoMon::where('MaBoMon', $gv->MaBoMon)->first();
            if ($bm) return $bm;
        }

        $allBoMons = BoMon::all();
        foreach ($allBoMons as $itemBm) {
            $code = $itemBm->MaBoMon;
            if (str_contains($user->TenDangNhap, '_' . $code . '_') || str_contains($user->TenDangNhap, '_' . $code) || str_contains($user->TenDangNhap, 'BM_' . $code)) {
                return $itemBm;
            }
        }

        if (!empty($gv?->HoTen)) {
            $found = BoMon::where('TruongBoMon', 'like', '%' . $gv->HoTen . '%')->first();
            if ($found) return $found;
        }

        return BoMon::where('MaBoMon', 'CNPM')->first() ?? BoMon::first();
    }

    public function index(Request $request)
    {
        $boMon = $this->getBoMon();
        $maBoMon = $boMon ? $boMon->MaBoMon : 'CNPM';

        $activePlan = PlanPhaseService::getActivePlan(null, $maBoMon);
        $currentPhase = PlanPhaseService::getCurrentPhase($activePlan);
        $stageDefinitions = TienDoNghiepVuKhoaLuan::DANH_SACH_GIAI_DOAN;

        // Lấy danh sách GV thuộc Bộ môn kèm thống kê đề tài và nhóm hướng dẫn
        $giangViens = GiangVien::where('MaBoMon', $maBoMon)
            ->withCount([
                'deTais',
                'deTais as de_tais_cong_bo_count' => fn($q) => $q->where('TrangThai', 'Đã công bố'),
            ])
            ->orderBy('HoTen')
            ->get();

        $gvIds = $giangViens->pluck('MaGV');

        // Gắn số nhóm đang hướng dẫn cho từng GV
        foreach ($giangViens as $gvItem) {
            $gvItem->nhoms_count = Nhom::whereHas('deTai', fn($q) => $q->where('MaGV', $gvItem->MaGV))->count();
        }

        // Truy vấn tất cả nhóm sinh viên thuộc bộ môn để đánh giá 6 giai đoạn
        $baseQuery = Nhom::with([
            'deTai.giangVien.boMon',
            'deTai.hocPhan',
            'hocPhan',
            'truongNhom.lop',
            'thanhViens.sinhVien.lop',
            'dangKyDeTai.giangVienHuongDan',
            'baoCaos.mocThoiGian',
            'hoSoBaoVe.hoiDong',
            'tienDoNghiepVu.lichSu',
        ])->whereHas('deTai.giangVien', fn($q) => $q->where('MaBoMon', $maBoMon));

        // Lọc theo Giảng viên hướng dẫn
        if ($request->filled('MaGV')) {
            $baseQuery->whereHas('deTai', fn($q) => $q->where('MaGV', $request->MaGV));
        }

        // Lọc theo Học kỳ
        if ($request->filled('MaHocKy')) {
            $baseQuery->where(function ($q) use ($request) {
                $q->where('MaHocKy', $request->MaHocKy)
                  ->orWhereHas('deTai', fn($dq) => $dq->where('MaHocKy', $request->MaHocKy));
            });
        }

        // Tìm kiếm tự do
        if ($request->filled('search')) {
            $s = trim($request->search);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $baseQuery->where(function ($q) use ($s, $sClean) {
                $q->where('TenNhom', 'like', "%{$s}%")
                  ->orWhere('MaNhom', 'like', "%{$s}%")
                  ->orWhereHas('deTai', function($dq) use ($s, $sClean) {
                      $dq->where('TenDeTai', 'like', "%{$s}%");
                      if (!empty($sClean)) {
                          $dq->orWhere('MaDeTai', 'like', "%{$sClean}%");
                      }
                  })
                  ->orWhereHas('dangKyDeTai.giangVienHuongDan', fn($g) => $g->where('HoTen', 'like', "%{$s}%"))
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

        $allDepartmentNhoms = $baseQuery->get();

        // Xử lý và thẩm định nghiệp vụ 6 giai đoạn chuẩn
        $processedAll = $allDepartmentNhoms->map(function ($nhom) {
            return TheoDoiDoAnController::processNhomStageData($nhom);
        });

        // 5 Thẻ KPI tiến độ nghiệp vụ chuẩn
        $stats = [
            'total_nhom'          => $processedAll->count(),
            'dang_thuc_hien'      => $processedAll->where('status_code', 'DANG_THUC_HIEN')->count(),
            'cho_xu_ly'           => $processedAll->where('status_code', 'CHO_XU_LY')->count(),
            'qua_han'             => $processedAll->where('status_code', 'QUA_HAN')->count(),
            'hoan_thanh'          => $processedAll->where('status_code', 'HOAN_THANH')->count(),
            'total_gv'            => $giangViens->count(),
            'total_detai'         => DeTai::whereIn('MaGV', $gvIds)->count(),
            'detai_cong_bo'       => DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Đã công bố')->count(),
            'nhom_hoan_thanh'     => $processedAll->where('status_code', 'HOAN_THANH')->count(),
            'nhom_dang_thuc_hien' => $processedAll->where('status_code', '!=', 'HOAN_THANH')->count(),
        ];

        // Thống kê chi tiết cho 6 Giai đoạn nghiệp vụ trong Bộ môn
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

        // ── THỐNG KÊ THEO TỪNG HỌC PHẦN (CẤP 1 & 2 - TỔNG QUAN) ──
        $departmentHocPhans = HocPhan::where('MaBoMon', $maBoMon)->orderBy('TenHocPhan')->get();
        $referencedHpCodes = $processedAll->pluck('ma_hoc_phan')->unique()->filter();
        if ($referencedHpCodes->isNotEmpty()) {
            $extraHps = HocPhan::whereIn('MaHocPhan', $referencedHpCodes)->where('MaBoMon', '!=', $maBoMon)->get();
            $departmentHocPhans = $departmentHocPhans->concat($extraHps)->unique('MaHocPhan');
        }

        $hocPhanStats = [];
        foreach ($departmentHocPhans as $hp) {
            $hpNhoms = $processedAll->filter(function($n) use ($hp) {
                return $n->ma_hoc_phan === $hp->MaHocPhan;
            });
            $avgProgress = $hpNhoms->count() > 0 ? (int)round($hpNhoms->avg('progress_percent')) : 0;
            $hocPhanStats[$hp->MaHocPhan] = [
                'hoc_phan'       => $hp,
                'total_nhom'     => $hpNhoms->count(),
                'dang_thuc_hien' => $hpNhoms->where('status_code', 'DANG_THUC_HIEN')->count(),
                'cho_xu_ly'      => $hpNhoms->where('status_code', 'CHO_XU_LY')->count(),
                'qua_han'        => $hpNhoms->where('status_code', 'QUA_HAN')->count(),
                'hoan_thanh'     => $hpNhoms->where('status_code', 'HOAN_THANH')->count(),
                'avg_progress'   => $avgProgress,
                'groups'         => $hpNhoms,
            ];
        }

        // Nhóm khác nếu có mã học phần chưa xác định
        $unassignedNhoms = $processedAll->filter(function($n) use ($departmentHocPhans) {
            return !$departmentHocPhans->pluck('MaHocPhan')->contains($n->ma_hoc_phan);
        });
        if ($unassignedNhoms->count() > 0) {
            $hocPhanStats['KHAC'] = [
                'hoc_phan'       => (object)[
                    'MaHocPhan'   => 'KHAC',
                    'TenHocPhan'  => 'Học phần khác / Đang cập nhật',
                    'SoTinChi'    => 10,
                    'LoaiHocPhan' => 'Khóa luận',
                ],
                'total_nhom'     => $unassignedNhoms->count(),
                'dang_thuc_hien' => $unassignedNhoms->where('status_code', 'DANG_THUC_HIEN')->count(),
                'cho_xu_ly'      => $unassignedNhoms->where('status_code', 'CHO_XU_LY')->count(),
                'qua_han'        => $unassignedNhoms->where('status_code', 'QUA_HAN')->count(),
                'hoan_thanh'     => $unassignedNhoms->where('status_code', 'HOAN_THANH')->count(),
                'avg_progress'   => (int)round($unassignedNhoms->avg('progress_percent')),
                'groups'         => $unassignedNhoms,
            ];
        }

        $activeCoursesCount = collect($hocPhanStats)->where('total_nhom', '>', 0)->count();
        $stats['total_courses'] = $activeCoursesCount > 0 ? $activeCoursesCount : count($hocPhanStats);

        // Xác định chế độ hiển thị: Tổng quan (isOverviewMode) hay Chi tiết học phần (Level 3)
        $selectedMaHocPhan = $request->get('MaHocPhan');
        $isOverviewMode = empty($selectedMaHocPhan);
        $selectedHocPhan = null;

        if (!$isOverviewMode) {
            $selectedHocPhan = $departmentHocPhans->firstWhere('MaHocPhan', $selectedMaHocPhan)
                ?? HocPhan::find($selectedMaHocPhan)
                ?? ($selectedMaHocPhan === 'KHAC' ? ($hocPhanStats['KHAC']['hoc_phan'] ?? null) : null);
        }

        // Lọc danh sách nhóm khi ở Cấp 3 (chọn học phần)
        $filteredNhoms = $processedAll;
        if (!$isOverviewMode) {
            if ($selectedMaHocPhan === 'KHAC') {
                $filteredNhoms = $unassignedNhoms;
            } else {
                $filteredNhoms = $filteredNhoms->filter(function($n) use ($selectedMaHocPhan) {
                    return $n->ma_hoc_phan === $selectedMaHocPhan;
                })->values();
            }
        }

        // Lọc theo Giai đoạn hoặc Status Code nếu người dùng chọn
        if ($request->filled('stage') && is_numeric($request->stage)) {
            $filteredNhoms = $filteredNhoms->where('current_stage', (int)$request->stage)->values();
        }
        if ($request->filled('status_code')) {
            $filteredNhoms = $filteredNhoms->where('status_code', $request->status_code)->values();
        }

        // Ưu tiên sắp xếp nhóm cần chú ý lên đầu: QUA_HAN -> CHO_XU_LY -> DANG_THUC_HIEN -> HOAN_THANH
        $filteredNhoms = $filteredNhoms->sortByDesc(function($n) {
            if ($n->status_code === 'QUA_HAN') return 400 + (100 - ($n->progress_percent ?? 0));
            if ($n->status_code === 'CHO_XU_LY') return 300 + (100 - ($n->progress_percent ?? 0));
            if ($n->status_code === 'DANG_THUC_HIEN') return 200 + ($n->progress_percent ?? 0);
            return 100 + ($n->progress_percent ?? 0);
        })->values();

        // Phân trang danh sách nhóm
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentItems = $filteredNhoms->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $nhoms = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $filteredNhoms->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        // Danh sách nhóm có dấu hiệu chậm tiến độ cần được quan tâm / nhắc nhở (warningNhoms)
        $warningNhoms = $processedAll->filter(function($n) {
            if ($n->status_code === 'QUA_HAN' || $n->status_code === 'CHO_XU_LY') return true;
            if (!$n->deTai || !$n->deTai->giangVien) return true;
            if ($n->current_stage >= 3 && empty($n->dieu_kien_giai_doan[3]['dat'])) return true;
            return false;
        })->map(function($n) {
            $reasons = [];
            if ($n->status_code === 'QUA_HAN') {
                $reasons[] = 'Nhóm bị trễ hạn hoặc quá hạn nộp báo cáo / xử lý giai đoạn';
            }
            if ($n->status_code === 'CHO_XU_LY') {
                $reasons[] = 'Đang chờ bước phê duyệt hồ sơ từ Bộ môn hoặc GVHD';
            }
            if (!$n->deTai) {
                $reasons[] = 'Chưa đăng ký đề tài hoặc chưa được duyệt đề tài';
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

        return view('truongbomon.theodoi.index', compact(
            'boMon', 'giangViens', 'nhoms', 'stats', 'stageStats', 'stageDefinitions',
            'activePlan', 'currentPhase', 'warningNhoms', 'hocKies',
            'hocPhanStats', 'isOverviewMode', 'selectedHocPhan', 'departmentHocPhans'
        ));
    }

    /**
     * Trưởng bộ môn xem chi tiết tiến độ 1 nhóm thuộc Bộ môn
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

        $boMon = $this->getBoMon();
        $maBoMon = $boMon ? $boMon->MaBoMon : null;
        $nhomBm = $nhom->deTai?->giangVien?->MaBoMon ?? $nhom->deTai?->hocPhan?->MaBoMon;

        if (!$maBoMon || ($nhomBm && $nhomBm !== $maBoMon)) {
            abort(403, 'Bạn không có quyền xem thông tin của nhóm sinh viên ngoài bộ môn quản lý.');
        }

        $activePlan = PlanPhaseService::getActivePlan(null, $maBoMon);
        $currentPhase = PlanPhaseService::getCurrentPhase($activePlan);

        $nhom = TheoDoiDoAnController::processNhomStageData($nhom);

        // Lấy thông tin GV phản biện nếu có
        $phanBien = null;
        if ($nhom->deTai) {
            $phanBien = PhanCongPhanBien::with('giangVien.boMon')
                ->where('MaDeTai', $nhom->deTai->MaDeTai)
                ->first();
        }

        // Lấy chi tiết các giai đoạn kỹ thuật & đợt nộp do GVHD quản lý
        $giaiDoansGV = collect();
        if ($nhom->deTai) {
            $giaiDoansGV = GiaiDoanThucHien::where('MaDeTai', $nhom->deTai->MaDeTai)
                ->with(['dotNops.khoiBaoCaos.tomTat'])
                ->orderBy('ThuTu')
                ->get();
        }

        $stageDefinitions = TienDoNghiepVuKhoaLuan::DANH_SACH_GIAI_DOAN;

        return view('truongbomon.theodoi.show', compact(
            'nhom', 'activePlan', 'currentPhase', 'phanBien', 'giaiDoansGV', 'stageDefinitions', 'boMon'
        ));
    }

    /**
     * Tra cứu toàn bộ lịch sử nghiệp vụ & nhắc nhở của nhóm thuộc Bộ môn
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

        $boMon = $this->getBoMon();
        $maBoMon = $boMon ? $boMon->MaBoMon : null;
        $nhomBm = $nhom->deTai?->giangVien?->MaBoMon ?? $nhom->deTai?->hocPhan?->MaBoMon;

        if (!$maBoMon || ($nhomBm && $nhomBm !== $maBoMon)) {
            abort(403, 'Bạn không có quyền truy cập lịch sử của nhóm ngoài bộ môn quản lý.');
        }

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
                        'thoi_gian' => Carbon::parse($item->created_at)->format('H:i d/m/Y'),
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

        $nhom = TheoDoiDoAnController::processNhomStageData($nhom);

        $query = LichSuChuyenGiaiDoan::where('MaNhom', $id);

        if ($request->filled('action')) {
            $query->where('HanhDong', $request->action);
        }

        if ($request->filled('stage') && is_numeric($request->stage)) {
            $query->where('GiaiDoanSau', (int) $request->stage);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(5)->withQueryString();

        $availableActions = LichSuChuyenGiaiDoan::where('MaNhom', $id)
            ->distinct()
            ->pluck('HanhDong')
            ->filter()
            ->values();

        $stageDefinitions = TienDoNghiepVuKhoaLuan::DANH_SACH_GIAI_DOAN;

        return view('truongbomon.theodoi.lichsu', compact(
            'nhom', 'logs', 'stageDefinitions', 'availableActions', 'boMon'
        ));
    }

    /**
     * Xem chi tiết liên kết tiến độ thực hiện do Giảng viên quản lý (Ajax JSON)
     */
    public function chiTietTienDoGV($id)
    {
        $nhom = Nhom::with(['deTai.giangVien'])->findOrFail($id);

        $boMon = $this->getBoMon();
        $maBoMon = $boMon ? $boMon->MaBoMon : null;
        $nhomBm = $nhom->deTai?->giangVien?->MaBoMon ?? $nhom->deTai?->hocPhan?->MaBoMon;

        if (!$maBoMon || ($nhomBm && $nhomBm !== $maBoMon)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập dữ liệu ngoài bộ môn quản lý.',
            ], 403);
        }

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
     * Trưởng bộ môn gửi yêu cầu nhắc nhở tiến độ (đến GVHD hoặc Sinh viên / Nhóm trưởng thuộc Bộ môn)
     */
    public function remind(Request $request, $id)
    {
        $nhom = Nhom::with(['truongNhom', 'thanhViens.sinhVien', 'deTai.giangVien', 'tienDoNghiepVu'])->findOrFail($id);

        $boMon = $this->getBoMon();
        $maBoMon = $boMon ? $boMon->MaBoMon : null;
        $nhomBm = $nhom->deTai?->giangVien?->MaBoMon ?? $nhom->deTai?->hocPhan?->MaBoMon;

        if (!$maBoMon || ($nhomBm && $nhomBm !== $maBoMon)) {
            return redirect()->back()->with('error', 'Thao tác bị từ chối: Nhóm này không thuộc quyền quản lý của bộ môn.');
        }

        $request->validate([
            'doi_tuong'  => 'required|in:gvhd,sinh_vien,ca_hai',
            'giai_doan'  => 'nullable|integer|between:1,6',
            'ly_do'      => 'required|string|max:255',
            'noi_dung'   => 'required|string',
            'han_xu_ly'  => 'nullable|date',
        ], [
            'doi_tuong.required' => 'Vui lòng chọn đối tượng nhận yêu cầu nhắc nhở.',
            'ly_do.required'     => 'Vui lòng nhập hoặc chọn lý do nhắc nhở.',
            'noi_dung.required'  => 'Vui lòng nhập nội dung yêu cầu cụ thể.',
        ]);

        $stageNum = (int) ($request->giai_doan ?: ($nhom->tienDoNghiepVu?->GiaiDoanHienTai ?: 1));
        $stageMeta = TienDoNghiepVuKhoaLuan::DANH_SACH_GIAI_DOAN[$stageNum] ?? ['short' => "GĐ {$stageNum}"];
        $stageLabel = $stageMeta['short'] ?? "GĐ {$stageNum}";

        $hanXuLyText = $request->han_xu_ly ? Carbon::parse($request->han_xu_ly)->format('d/m/Y') : 'Không giới hạn';
        $user = Auth::user();
        $gvLoggedIn = GiangVien::getLoggedInGiangVien($user);
        $senderName = $gvLoggedIn ? $gvLoggedIn->HoTen : ($user->TenDangNhap ?? 'Trưởng bộ môn');

        $tieuDe = "[NHẮC NHỞ TIẾN ĐỘ TỪ BỘ MÔN] Nhóm {$nhom->MaNhom} – {$stageLabel}";
        $noiDungThongBao = "Kính gửi: \n" .
            "- Đề tài: " . ($nhom->deTai?->TenDeTai ?? 'Khóa luận tốt nghiệp') . "\n" .
            "- Nhóm: {$nhom->TenNhom} ({$nhom->MaNhom})\n" .
            "- Giai đoạn: {$stageLabel}\n" .
            "- Lý do: {$request->ly_do}\n" .
            "- Hạn hoàn thành/phản hồi: {$hanXuLyText}\n\n" .
            "Nội dung yêu cầu từ Trưởng bộ môn ({$senderName}):\n" .
            $request->noi_dung;

        $sentCount = 0;
        $recipientLabels = [];

        // 1. Gửi đến GVHD
        if (in_array($request->doi_tuong, ['gvhd', 'ca_hai'])) {
            $gvhd = $nhom->deTai?->giangVien;
            if ($gvhd && $gvhd->MaTK) {
                ThongBaoService::guiDen($gvhd->MaTK, $tieuDe, $noiDungThongBao, 'Nhắc nhở tiến độ');
                $sentCount++;
                $recipientLabels[] = "GVHD ({$gvhd->HoTen})";
            }
        }

        // 2. Gửi đến Nhóm sinh viên (Trưởng nhóm + Thành viên)
        if (in_array($request->doi_tuong, ['sinh_vien', 'ca_hai'])) {
            if ($nhom->truongNhom && $nhom->truongNhom->MaTK) {
                ThongBaoService::guiDen($nhom->truongNhom->MaTK, $tieuDe, $noiDungThongBao, 'Nhắc nhở tiến độ');
                $sentCount++;
            }
            foreach ($nhom->thanhViens as $tv) {
                if ($tv->sinhVien && $tv->sinhVien->MaTK && (!$nhom->truongNhom || $tv->sinhVien->MaTK !== $nhom->truongNhom->MaTK)) {
                    ThongBaoService::guiDen($tv->sinhVien->MaTK, $tieuDe, $noiDungThongBao, 'Nhắc nhở tiến độ');
                    $sentCount++;
                }
            }
            $recipientLabels[] = "Nhóm sinh viên ({$nhom->TenNhom})";
        }

        // Ghi lại lịch sử nhắc nhở vào bảng LichSuChuyenGiaiDoan
        $recipientStr = implode(' & ', $recipientLabels);
        LichSuChuyenGiaiDoan::create([
            'MaNhom'         => $nhom->MaNhom,
            'GiaiDoanTruoc'  => $stageNum,
            'GiaiDoanSau'    => $stageNum,
            'TrangThaiTruoc' => $nhom->tienDoNghiepVu?->TrangThaiGiaiDoan ?? 'DANG_THUC_HIEN',
            'TrangThaiSau'   => $nhom->tienDoNghiepVu?->TrangThaiGiaiDoan ?? 'DANG_THUC_HIEN',
            'HanhDong'       => 'Gửi yêu cầu nhắc nhở tiến độ',
            'NguoiThucHien'  => "Trưởng bộ môn ({$senderName})",
            'LyDo'           => "[Người nhận: {$recipientStr}] [GĐ: {$stageLabel}] [Hạn xử lý: {$hanXuLyText}] Lý do: {$request->ly_do}. Yêu cầu: {$request->noi_dung}",
        ]);

        return redirect()->back()->with('success', "🔔 Đã gửi yêu cầu nhắc nhở thành công tới {$recipientStr}!");
    }
}
