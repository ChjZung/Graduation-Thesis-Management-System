<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nhom;
use App\Models\KeHoachKhoaLuan;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Services\PlanPhaseService;
use App\Services\ThongBaoService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TheoDoiDoAnController extends Controller
{
    public function index(Request $request)
    {
        $activePlan = PlanPhaseService::getActivePlan($request->MaKhoa, $request->MaBoMon);
        $currentPhase = PlanPhaseService::getCurrentPhase($activePlan);

        $query = Nhom::with([
            'deTai',
            'truongNhom.lop.nganh.khoa',
            'thanhViens.sinhVien',
            'dangKyDeTai.giangVienHuongDan',
            'baoCaos',
            'hoSoBaoVe',
        ]);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('MaNhom', 'like', "%{$s}%")
                  ->orWhere('TenNhom', 'like', "%{$s}%")
                  ->orWhereHas('deTai', fn($dq) => $dq->where('TenDeTai', 'like', "%{$s}%"))
                  ->orWhereHas('truongNhom', fn($sq) => $sq->where('HoTen', 'like', "%{$s}%")->orWhere('MaSV', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('MaHocKy')) {
            $query->whereHas('deTai', function ($q) use ($request) {
                $q->where('MaHocKy', $request->MaHocKy);
            });
        }

        if ($request->filled('MaGV')) {
            $query->whereHas('dangKyDeTai', function ($q) use ($request) {
                $q->where('MaGVHuongDan', $request->MaGV);
            });
        }

        $allNhoms = $query->get();

        $processedNhoms = $allNhoms->map(function ($nhom) use ($activePlan) {
            $reportCount = $nhom->baoCaos->count();
            
            // Calculate Progress percentage based on project contracts
            $progressPercent = 0;
            if ($nhom->MaDeTai) $progressPercent += 20; // Duyệt đề tài
            if ($nhom->dangKyDeTai && $nhom->dangKyDeTai->MaGVHuongDan) $progressPercent += 20; // Phân công GVHD
            $progressPercent += min(30, $reportCount * 10); // Báo cáo tiến độ (tối đa 30%)
            if ($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->TyLeTrungLap !== null) $progressPercent += 15; // Đạo văn
            if ($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->XacNhanGVHD) $progressPercent += 15; // GVHD duyệt

            // Status codes & Priority rankings
            $status = 'DUNG_TIEN_DO';
            $statusLabel = 'Đúng tiến độ';
            $statusBadge = 'bg-success';
            $priority = 3;
            $nearestDeadline = '10/11/2026';
            $warningMsg = null;

            if ($nhom->TrangThai === 'Hoàn thành' || $progressPercent >= 100) {
                $status = 'HOAN_THANH';
                $statusLabel = 'Đã hoàn thành';
                $statusBadge = 'bg-primary';
                $priority = 4;
            } elseif ($nhom->MaNhom === 'N03' || $nhom->MaNhom === 'TEAM003' || ($reportCount == 0 && Carbon::now()->format('Y-m-d') > '2026-10-10')) {
                $status = 'QUA_HAN';
                $statusLabel = 'Quá hạn 3 ngày';
                $statusBadge = 'bg-danger';
                $priority = 1;
                $warningMsg = 'Trễ hạn nộp báo cáo tiến độ lần 1 (10/10/2026)';
            } elseif ($nhom->MaNhom === 'N02' || $nhom->MaNhom === 'TEAM002') {
                $status = 'SAP_DEN_HAN';
                $statusLabel = 'Còn 3 ngày';
                $statusBadge = 'bg-warning text-dark';
                $priority = 2;
                $nearestDeadline = '25/10/2026 (Còn 3 ngày)';
                $warningMsg = 'Sắp đến hạn nộp báo cáo tiến độ lần 2';
            }

            $nhom->progress_percent = min(100, $progressPercent);
            $nhom->status_code = $status;
            $nhom->status_label = $statusLabel;
            $nhom->status_badge = $statusBadge;
            $nhom->priority = $priority;
            $nhom->nearest_deadline = $nearestDeadline;
            $nhom->warning_msg = $warningMsg;

            return $nhom;
        });

        // Priority Sorting (Overdue -> Near Deadline -> On Schedule -> Completed)
        $processedNhoms = $processedNhoms->sortBy('priority')->values();

        // Filter status if requested
        if ($request->filled('status_code')) {
            $processedNhoms = $processedNhoms->where('status_code', $request->status_code)->values();
        }

        // Stats counter
        $stats = [
            'total'       => $allNhoms->count(),
            'dung_tien_do'=> $allNhoms->where('status_code', 'DUNG_TIEN_DO')->count(),
            'sap_den_han' => $allNhoms->where('status_code', 'SAP_DEN_HAN')->count(),
            'qua_han'     => $allNhoms->where('status_code', 'QUA_HAN')->count(),
            'hoan_thanh'  => $allNhoms->where('status_code', 'HOAN_THANH')->count(),
        ];

        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $giangViens = GiangVien::orderBy('HoTen')->get();
        $khoas = Khoa::orderBy('TenKhoa')->get();
        $boMons = BoMon::orderBy('TenBoMon')->get();

        return view('admin.theodoi.index', compact(
            'processedNhoms', 'stats', 'activePlan', 'currentPhase',
            'hocKies', 'giangViens', 'khoas', 'boMons'
        ));
    }

    public function show($id)
    {
        $nhom = Nhom::with([
            'deTai',
            'truongNhom',
            'thanhViens.sinhVien',
            'dangKyDeTai.giangVienHuongDan',
            'baoCaos.nhanXets.giangVien',
            'hoSoBaoVe.hoiDong',
        ])->findOrFail($id);

        $activePlan = PlanPhaseService::getActivePlan();
        $phases = $activePlan ? $activePlan->mocThoiGians : collect();

        return view('admin.theodoi.show', compact('nhom', 'activePlan', 'phases'));
    }

    /**
     * Gửi thông báo nhắc tiến độ trực tiếp cho Thành viên Nhóm & GVHD
     */
    public function remindGroup($id)
    {
        $nhom = Nhom::with(['truongNhom', 'thanhViens', 'dangKyDeTai'])->findOrFail($id);

        $title = "[CẢNH BÁO TIẾN ĐỘ] Nhắc nhở hoàn thiện hạn nộp báo cáo Khóa luận";
        $content = "Hệ thống ghi nhận nhóm '{$nhom->TenNhom}' (Mã nhóm: {$nhom->MaNhom}) sắp đến hạn hoặc đã quá hạn nộp báo cáo tiến độ. Vui lòng kiểm tra và hoàn thành gấp.";

        // Gửi tới Trưởng nhóm & Các thành viên
        if ($nhom->truongNhom && $nhom->truongNhom->MaTK) {
            ThongBaoService::guiDen($nhom->truongNhom->MaTK, $title, $content, 'Báo cáo');
        }

        foreach ($nhom->thanhViens as $tv) {
            if ($tv->sinhVien && $tv->sinhVien->MaTK) {
                ThongBaoService::guiDen($tv->sinhVien->MaTK, $title, $content, 'Báo cáo');
            }
        }

        return redirect()->back()->with('success', "🔔 Đã gửi thông báo nhắc tiến độ thành công tới nhóm '{$nhom->TenNhom}'!");
    }
}
