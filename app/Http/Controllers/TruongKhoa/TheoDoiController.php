<?php

namespace App\Http\Controllers\TruongKhoa;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\TheoDoiDoAnController;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\SinhVien;
use App\Models\Nhom;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\BaoCaoTienDo;
use App\Models\HocKy;
use App\Models\GiaiDoanThucHien;
use App\Models\LichSuChuyenGiaiDoan;
use App\Models\TienDoNghiepVuKhoaLuan;
use App\Models\PhanCongPhanBien;
use App\Models\TaiKhoan;
use App\Models\HocPhan;
use App\Services\PlanPhaseService;
use App\Services\ThongBaoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TheoDoiController extends Controller
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

        $tab = $request->get('tab', 'tien_do');
        $boMons = BoMon::where('MaKhoa', $maKhoa)->orderBy('TenBoMon')->get();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $stageDefinitions = TienDoNghiepVuKhoaLuan::DANH_SACH_GIAI_DOAN;

        $activePlan = PlanPhaseService::getActivePlan($maKhoa, $request->MaBoMon);
        $currentPhase = PlanPhaseService::getCurrentPhase($activePlan);

        // 1. Nhóm & Tiến độ nộp báo cáo (Đánh giá 6 giai đoạn chuẩn)
        $queryNhoms = Nhom::with([
            'deTai.giangVien.boMon',
            'deTai.hocPhan',
            'hocPhan',
            'truongNhom.lop.nganh',
            'thanhViens.sinhVien.lop',
            'dangKyDeTai.giangVienHuongDan',
            'baoCaos.mocThoiGian',
            'hoSoBaoVe.hoiDong',
            'tienDoNghiepVu.lichSu',
        ])->whereHas('deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));

        if ($request->filled('MaHocKy')) {
            $maHK = $request->MaHocKy;
            $queryNhoms->where(function ($q) use ($maHK) {
                $q->where('MaHocKy', $maHK)
                  ->orWhereHas('deTai', fn($dq) => $dq->where('MaHocKy', $maHK));
            });
        }

        if ($request->filled('MaGV')) {
            $queryNhoms->whereHas('deTai', fn($q) => $q->where('MaGV', $request->MaGV));
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $queryNhoms->where(function ($q) use ($s, $sClean) {
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

        $allFacultyNhoms = $queryNhoms->get();

        // Xử lý và thẩm định nghiệp vụ 6 giai đoạn chuẩn của hệ thống
        $processedAll = $allFacultyNhoms->map(function ($nhom) {
            return TheoDoiDoAnController::processNhomStageData($nhom);
        });

        // 5 Thẻ KPI tiến độ nghiệp vụ toàn Khoa
        $stats = [
            'total_nhom'        => $processedAll->count(),
            'dang_thuc_hien'    => $processedAll->where('status_code', 'DANG_THUC_HIEN')->count(),
            'cho_xu_ly'         => $processedAll->where('status_code', 'CHO_XU_LY')->count(),
            'qua_han'           => $processedAll->where('status_code', 'QUA_HAN')->count(),
            'hoan_thanh'        => $processedAll->where('status_code', 'HOAN_THANH')->count(),
            'total_detai'       => DeTai::whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa))->count(),
            'detai_cong_bo'     => DeTai::whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa))->where('TrangThai', 'Đã công bố')->count(),
            'total_sv'          => SinhVien::where('MaKhoa', $maKhoa)->count(),
            'total_hoidong'     => HoiDong::count(),
            'total_hoso'        => HoSoBaoVe::whereHas('deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa))->count(),
            'hoso_du_dieu_kien' => HoSoBaoVe::whereHas('deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa))->where('XacNhanGVHD', 1)->count(),
            'total_baocao'      => BaoCaoTienDo::whereHas('nhom.deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa))->count(),
            'total_bomon'       => $boMons->count(),
        ];

        // Thống kê chi tiết cho 6 Giai đoạn nghiệp vụ toàn Khoa
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

        // ── XÁC ĐỊNH CẤP ĐỘ ĐIỀU HƯỚNG CỦA TRƯỞNG KHOA ──
        // Level 1: Tổng quan Khoa (!MaBoMon && !MaHocPhan)
        // Level 2: Chi tiết Bộ môn (MaBoMon && !MaHocPhan)
        // Level 3: Danh sách Nhóm học phần (MaHocPhan)
        $selectedMaBoMon = $request->get('MaBoMon');
        $selectedMaHocPhan = $request->get('MaHocPhan');

        $navLevel = 1;
        if (!empty($selectedMaHocPhan)) {
            $navLevel = 3;
        } elseif (!empty($selectedMaBoMon)) {
            $navLevel = 2;
        }

        $selectedBoMon = $selectedMaBoMon ? $boMons->firstWhere('MaBoMon', $selectedMaBoMon) : null;

        // Thống kê tiến độ theo từng Bộ môn trong Khoa (Cấp 1)
        $departmentStats = [];
        foreach ($boMons as $bm) {
            $bmNhoms = $processedAll->filter(function($n) use ($bm) {
                return ($n->deTai?->giangVien?->MaBoMon === $bm->MaBoMon) || ($n->deTai?->hocPhan?->MaBoMon === $bm->MaBoMon) || ($n->hoc_phan?->MaBoMon === $bm->MaBoMon);
            });
            $bmCourses = HocPhan::where('MaBoMon', $bm->MaBoMon)->count();
            if ($bmCourses === 0) {
                $bmCourses = $bmNhoms->pluck('ma_hoc_phan')->unique()->filter()->count();
            }
            $avgProgress = $bmNhoms->count() > 0 ? (int)round($bmNhoms->avg('progress_percent')) : 0;
            $departmentStats[$bm->MaBoMon] = [
                'bomon'          => $bm,
                'total_courses'  => max($bmCourses, 1),
                'total'          => $bmNhoms->count(),
                'dang_thuc_hien' => $bmNhoms->where('status_code', 'DANG_THUC_HIEN')->count(),
                'cho_xu_ly'      => $bmNhoms->where('status_code', 'CHO_XU_LY')->count(),
                'qua_han'        => $bmNhoms->where('status_code', 'QUA_HAN')->count(),
                'hoan_thanh'     => $bmNhoms->where('status_code', 'HOAN_THANH')->count(),
                'avg_progress'   => $avgProgress,
                'nhoms'          => $bmNhoms,
            ];
        }

        // Thống kê theo từng Học phần (Cấp 2 hoặc theo Bộ môn đã chọn)
        $scopeNhoms = $selectedMaBoMon ? ($departmentStats[$selectedMaBoMon]['nhoms'] ?? collect()) : $processedAll;
        $hpQuery = HocPhan::where('MaKhoa', $maKhoa);
        if ($selectedMaBoMon) {
            $hpQuery->where('MaBoMon', $selectedMaBoMon);
        }
        $facultyHocPhans = $hpQuery->orderBy('TenHocPhan')->get();

        $scopeHpCodes = $scopeNhoms->pluck('ma_hoc_phan')->unique()->filter();
        if ($scopeHpCodes->isNotEmpty()) {
            $extraHps = HocPhan::whereIn('MaHocPhan', $scopeHpCodes)->get();
            $facultyHocPhans = $facultyHocPhans->concat($extraHps)->unique('MaHocPhan');
        }

        $hocPhanStats = [];
        foreach ($facultyHocPhans as $hp) {
            $hpNhoms = $scopeNhoms->filter(function($n) use ($hp) {
                return $n->ma_hoc_phan === $hp->MaHocPhan;
            });
            $avgHpProgress = $hpNhoms->count() > 0 ? (int)round($hpNhoms->avg('progress_percent')) : 0;
            $hocPhanStats[$hp->MaHocPhan] = [
                'hoc_phan'       => $hp,
                'total_nhom'     => $hpNhoms->count(),
                'dang_thuc_hien' => $hpNhoms->where('status_code', 'DANG_THUC_HIEN')->count(),
                'cho_xu_ly'      => $hpNhoms->where('status_code', 'CHO_XU_LY')->count(),
                'qua_han'        => $hpNhoms->where('status_code', 'QUA_HAN')->count(),
                'hoan_thanh'     => $hpNhoms->where('status_code', 'HOAN_THANH')->count(),
                'avg_progress'   => $avgHpProgress,
                'nhoms'          => $hpNhoms,
            ];
        }

        $selectedHocPhan = null;
        if (!empty($selectedMaHocPhan)) {
            $selectedHocPhan = $facultyHocPhans->firstWhere('MaHocPhan', $selectedMaHocPhan)
                ?? HocPhan::find($selectedMaHocPhan);
        }

        $stats['total_courses'] = HocPhan::where('MaKhoa', $maKhoa)->count() ?: count($hocPhanStats);

        // Lọc danh sách nhóm khi ở Cấp 3 (hoặc khi tìm kiếm)
        $filteredNhoms = $scopeNhoms;
        if ($navLevel === 3 && !empty($selectedMaHocPhan)) {
            $filteredNhoms = $filteredNhoms->filter(function($n) use ($selectedMaHocPhan) {
                return $n->ma_hoc_phan === $selectedMaHocPhan;
            })->values();
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

        // Phân trang danh sách nhóm (10 bản ghi/trang)
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage('page_nhom');
        $perPage = 10;
        $currentItems = $filteredNhoms->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $nhoms = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $filteredNhoms->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(), 'pageName' => 'page_nhom', 'query' => $request->query()]
        );

        // Danh sách nhóm chậm tiến độ / quá hạn cần được Trưởng khoa quan tâm & đôn đốc
        $warningNhoms = $scopeNhoms->filter(function($n) {
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

        // 2. Tình hình bảo vệ & Hội đồng chấm (Tab 2)
        $queryHoSo = HoSoBaoVe::with([
            'nhom.truongNhom',
            'nhom.thanhViens.sinhVien',
            'deTai.giangVien.boMon',
            'hoiDong.thanhViens.giangVien',
            'ketQuaSinhViens',
        ])->whereHas('deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));

        if ($request->filled('MaBoMon')) {
            $maBM = $request->MaBoMon;
            $queryHoSo->whereHas('deTai.giangVien', fn($q) => $q->where('MaBoMon', $maBM));
        }

        if ($request->filled('search_baove')) {
            $sbv = trim($request->search_baove);
            $sbvClean = preg_replace('/[^A-Za-z0-9]/', '', $sbv);
            $queryHoSo->where(function ($q) use ($sbv, $sbvClean) {
                $q->where('MaHoSo', 'like', "%{$sbv}%")
                  ->orWhereHas('nhom', function($nq) use ($sbv, $sbvClean) {
                      $nq->where('TenNhom', 'like', "%{$sbv}%")
                         ->orWhere('MaNhom', 'like', "%{$sbv}%")
                         ->orWhereHas('thanhViens.sinhVien', function($sq) use ($sbv, $sbvClean) {
                             $sq->where('HoTen', 'like', "%{$sbv}%")
                                ->orWhere('MaSV', 'like', "%{$sbv}%");
                             if (!empty($sbvClean)) {
                                 $sq->orWhere('MaSV', 'like', "%{$sbvClean}%");
                             }
                         });
                      if (!empty($sbvClean)) {
                          $nq->orWhere('MaNhom', 'like', "%{$sbvClean}%");
                      }
                  })
                  ->orWhereHas('deTai', function($dq) use ($sbv, $sbvClean) {
                      $dq->where('TenDeTai', 'like', "%{$sbv}%");
                      if (!empty($sbvClean)) {
                          $dq->orWhere('MaDeTai', 'like', "%{$sbvClean}%");
                      }
                  });
                if (!empty($sbvClean)) {
                    $q->orWhere('MaHoSo', 'like', "%{$sbvClean}%");
                }
            });
        }

        $hoSoBaoVes = $queryHoSo->orderBy('created_at', 'desc')->paginate(10, ['*'], 'page_hoso')->withQueryString();

        $giangViens = GiangVien::whereHas('boMon', fn($q) => $q->where('MaKhoa', $maKhoa))->orderBy('HoTen')->get();

        return view('truongkhoa.theodoi.index', compact(
            'khoa', 'nhoms', 'hoSoBaoVes', 'boMons', 'hocKies', 'stats', 'stageStats',
            'departmentStats', 'warningNhoms', 'stageDefinitions', 'activePlan', 'currentPhase', 'giangViens', 'tab',
            'navLevel', 'selectedBoMon', 'selectedHocPhan', 'hocPhanStats', 'facultyHocPhans'
        ));
    }

    /**
     * Trưởng khoa xem chi tiết tiến độ 1 nhóm thuộc Khoa
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

        $khoa = $this->getKhoa();
        $maKhoa = $khoa ? $khoa->MaKhoa : null;
        $nhomKhoa = $nhom->deTai?->giangVien?->boMon?->MaKhoa 
            ?? $nhom->deTai?->hocPhan?->boMon?->MaKhoa 
            ?? $nhom->truongNhom?->lop?->nganh?->MaKhoa;

        if ($maKhoa && $nhomKhoa && $nhomKhoa !== $maKhoa) {
            abort(403, 'Bạn không có quyền xem thông tin của nhóm sinh viên ngoài khoa quản lý.');
        }

        $activePlan = PlanPhaseService::getActivePlan($maKhoa);
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

        return view('truongkhoa.theodoi.show', compact(
            'nhom', 'activePlan', 'currentPhase', 'phanBien', 'giaiDoansGV', 'stageDefinitions', 'khoa'
        ));
    }

    /**
     * Tra cứu toàn bộ lịch sử nghiệp vụ & nhắc nhở của nhóm thuộc Khoa
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

        $khoa = $this->getKhoa();
        $maKhoa = $khoa ? $khoa->MaKhoa : null;
        $nhomKhoa = $nhom->deTai?->giangVien?->boMon?->MaKhoa 
            ?? $nhom->deTai?->hocPhan?->boMon?->MaKhoa 
            ?? $nhom->truongNhom?->lop?->nganh?->MaKhoa;

        if ($maKhoa && $nhomKhoa && $nhomKhoa !== $maKhoa) {
            abort(403, 'Bạn không có quyền truy cập lịch sử của nhóm ngoài khoa quản lý.');
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

        return view('truongkhoa.theodoi.lichsu', compact(
            'nhom', 'logs', 'stageDefinitions', 'availableActions', 'khoa'
        ));
    }

    /**
     * Xem chi tiết liên kết tiến độ thực hiện do Giảng viên quản lý (Ajax JSON)
     */
    public function chiTietTienDoGV($id)
    {
        $nhom = Nhom::with(['deTai.giangVien'])->findOrFail($id);

        $khoa = $this->getKhoa();
        $maKhoa = $khoa ? $khoa->MaKhoa : null;
        $nhomKhoa = $nhom->deTai?->giangVien?->boMon?->MaKhoa 
            ?? $nhom->deTai?->hocPhan?->boMon?->MaKhoa;

        if ($maKhoa && $nhomKhoa && $nhomKhoa !== $maKhoa) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập dữ liệu ngoài khoa quản lý.',
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
     * Trưởng khoa gửi yêu cầu nhắc nhở tiến độ (đến TBM, GVHD, hoặc Sinh viên thuộc Khoa)
     */
    public function remind(Request $request, $id)
    {
        $nhom = Nhom::with(['truongNhom', 'thanhViens.sinhVien', 'deTai.giangVien.boMon', 'tienDoNghiepVu'])->findOrFail($id);

        $khoa = $this->getKhoa();
        $maKhoa = $khoa ? $khoa->MaKhoa : null;
        $nhomKhoa = $nhom->deTai?->giangVien?->boMon?->MaKhoa 
            ?? $nhom->deTai?->hocPhan?->boMon?->MaKhoa 
            ?? $nhom->truongNhom?->lop?->nganh?->MaKhoa;

        if ($maKhoa && $nhomKhoa && $nhomKhoa !== $maKhoa) {
            return redirect()->back()->with('error', 'Thao tác bị từ chối: Nhóm này không thuộc quyền quản lý của khoa.');
        }

        $request->validate([
            'doi_tuong'  => 'required|in:tbm,gvhd,sinh_vien,tat_ca',
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
        $senderName = $gvLoggedIn ? $gvLoggedIn->HoTen : ($user->TenDangNhap ?? 'Trưởng khoa');

        $tieuDe = "[CHỈ ĐẠO TIẾN ĐỘ TỪ TRƯỞNG KHOA] Nhóm {$nhom->MaNhom} – {$stageLabel}";
        $noiDungThongBao = "Kính gửi các bên liên quan: \n" .
            "- Đề tài: " . ($nhom->deTai?->TenDeTai ?? 'Khóa luận tốt nghiệp') . "\n" .
            "- Nhóm: {$nhom->TenNhom} ({$nhom->MaNhom})\n" .
            "- Giai đoạn: {$stageLabel}\n" .
            "- Bộ môn: " . ($nhom->deTai?->giangVien?->boMon?->TenBoMon ?? 'Bộ môn') . "\n" .
            "- Lý do: {$request->ly_do}\n" .
            "- Hạn xử lý / báo cáo: {$hanXuLyText}\n\n" .
            "Ý kiến chỉ đạo / Đôn đốc từ Trưởng khoa ({$senderName}):\n" .
            $request->noi_dung;

        $sentCount = 0;
        $recipientLabels = [];

        // 1. Gửi đến Trưởng bộ môn phụ trách
        if (in_array($request->doi_tuong, ['tbm', 'tat_ca'])) {
            $bm = $nhom->deTai?->giangVien?->boMon;
            if ($bm) {
                // Tìm tài khoản Trưởng bộ môn tương ứng
                $tbmAccount = TaiKhoan::where('MaVaiTro', 'VT04')
                    ->whereHas('giangVien', fn($g) => $g->where('MaBoMon', $bm->MaBoMon))
                    ->first();
                if ($tbmAccount) {
                    ThongBaoService::guiDen($tbmAccount->MaTK, $tieuDe, $noiDungThongBao, 'Nhắc nhở tiến độ');
                    $sentCount++;
                    $recipientLabels[] = "Trưởng bộ môn {$bm->TenBoMon}";
                } elseif (!empty($bm->TruongBoMon)) {
                    $recipientLabels[] = "Trưởng bộ môn ({$bm->TruongBoMon})";
                }
            }
        }

        // 2. Gửi đến Giảng viên hướng dẫn
        if (in_array($request->doi_tuong, ['gvhd', 'tat_ca'])) {
            $gvhd = $nhom->deTai?->giangVien;
            if ($gvhd && $gvhd->MaTK) {
                ThongBaoService::guiDen($gvhd->MaTK, $tieuDe, $noiDungThongBao, 'Nhắc nhở tiến độ');
                $sentCount++;
                $recipientLabels[] = "GVHD ({$gvhd->HoTen})";
            }
        }

        // 3. Gửi đến Nhóm sinh viên
        if (in_array($request->doi_tuong, ['sinh_vien', 'tat_ca'])) {
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
        $recipientStr = implode(', ', $recipientLabels);
        LichSuChuyenGiaiDoan::create([
            'MaNhom'         => $nhom->MaNhom,
            'GiaiDoanTruoc'  => $stageNum,
            'GiaiDoanSau'    => $stageNum,
            'TrangThaiTruoc' => $nhom->tienDoNghiepVu?->TrangThaiGiaiDoan ?? 'DANG_THUC_HIEN',
            'TrangThaiSau'   => $nhom->tienDoNghiepVu?->TrangThaiGiaiDoan ?? 'DANG_THUC_HIEN',
            'HanhDong'       => 'Gửi yêu cầu nhắc nhở tiến độ',
            'NguoiThucHien'  => "Trưởng khoa ({$senderName})",
            'LyDo'           => "[Người nhận: {$recipientStr}] [GĐ: {$stageLabel}] [Hạn xử lý: {$hanXuLyText}] Lý do: {$request->ly_do}. Yêu cầu: {$request->noi_dung}",
        ]);

        return redirect()->back()->with('success', "🔔 Đã gửi chỉ đạo nhắc nhở tiến độ thành công tới {$recipientStr}!");
    }
}
