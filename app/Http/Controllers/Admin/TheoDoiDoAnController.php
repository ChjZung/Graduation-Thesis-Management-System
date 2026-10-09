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

        if ($request->filled('MaHocKy')) {
            $query->whereHas('deTai', function ($q) use ($request) {
                $q->where('MaHocKy', $request->MaHocKy);
            });
        }

        if ($request->filled('MaGV')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('dangKyDeTai.deTai', function($dtQ) use ($request) {
                    $dtQ->where('MaGV', $request->MaGV);
                })->orWhereHas('deTai', function($dtQ) use ($request) {
                    $dtQ->where('MaGV', $request->MaGV);
                });
            });
        }

        $allNhoms = $query->get();

        $stageDefinitions = [
            1 => ['name' => 'GĐ 1: Nhận đề cương & Thiết kế CSDL', 'short' => 'Đề cương & CSDL', 'icon' => 'fa-database'],
            2 => ['name' => 'GĐ 2: Thiết kế hệ thống & Xây dựng chức năng', 'short' => 'Hệ thống & Chức năng', 'icon' => 'fa-code'],
            3 => ['name' => 'GĐ 3: Kiểm thử hoàn thiện & Báo cáo dự thảo', 'short' => 'Kiểm thử & Báo cáo', 'icon' => 'fa-vial-circle-check'],
            4 => ['name' => 'GĐ 4: Hoàn thiện hồ sơ bảo vệ', 'short' => 'Hồ sơ bảo vệ', 'icon' => 'fa-file-shield'],
            5 => ['name' => 'GĐ 5: Tiến hành bảo vệ trước Hội đồng', 'short' => 'Bảo vệ Hội đồng', 'icon' => 'fa-chalkboard-user'],
            6 => ['name' => 'GĐ 6: Nhận kết quả & Nộp lại kết quả hoàn chỉnh', 'short' => 'Kết quả & Nộp lại', 'icon' => 'fa-award'],
        ];

        $processedNhoms = $allNhoms->map(function ($nhom) use ($activePlan, $stageDefinitions) {
            $bcMap = $nhom->baoCaos->keyBy('LanBaoCao');
            
            // Đánh giá chi tiết 6 giai đoạn
            $stages = [];
            foreach ($stageDefinitions as $stNum => $stMeta) {
                $bc = $bcMap[$stNum] ?? null;
                $stStatus = 'CHUA_NOP';
                $stStatusLabel = 'Chưa nộp';
                $stBadge = 'bg-secondary';

                if ($stNum <= 3) {
                    if ($bc) {
                        if ($bc->TrangThai === 'Đạt') {
                            $stStatus = 'DAT';
                            $stStatusLabel = 'Đạt';
                            $stBadge = 'bg-success';
                        } elseif ($bc->TrangThai === 'Chờ duyệt') {
                            $stStatus = 'CHO_DUYET';
                            $stStatusLabel = 'Chờ GVHD duyệt';
                            $stBadge = 'bg-warning text-dark';
                        } elseif ($bc->TrangThai === 'Yêu cầu nộp lại') {
                            $stStatus = 'NOP_LAI';
                            $stStatusLabel = 'Cần nộp lại';
                            $stBadge = 'bg-info text-dark';
                        }
                    }
                } elseif ($stNum === 4) {
                    // GĐ 4: Hồ sơ bảo vệ
                    if ($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->XacNhanGVHD) {
                        $stStatus = 'DAT';
                        $stStatusLabel = 'GVHD đã duyệt hồ sơ';
                        $stBadge = 'bg-success';
                    } elseif ($nhom->hoSoBaoVe) {
                        $stStatus = 'CHO_DUYET';
                        $stStatusLabel = 'Chờ duyệt hồ sơ';
                        $stBadge = 'bg-warning text-dark';
                    } elseif ($bc && $bc->TrangThai === 'Đạt') {
                        $stStatus = 'DAT';
                        $stStatusLabel = 'Đạt';
                        $stBadge = 'bg-success';
                    }
                } elseif ($stNum === 5) {
                    // GĐ 5: Bảo vệ Hội đồng
                    if ($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->ThoiGianBaoVe) {
                        $stStatus = 'DAT';
                        $stStatusLabel = 'Đã xếp lịch bảo vệ';
                        $stBadge = 'bg-primary';
                    } elseif ($bc && $bc->TrangThai === 'Đạt') {
                        $stStatus = 'DAT';
                        $stStatusLabel = 'Đã bảo vệ';
                        $stBadge = 'bg-success';
                    }
                } elseif ($stNum === 6) {
                    // GĐ 6: Nhận kết quả & nộp lại kết quả
                    if ($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->FileBanChinhSua) {
                        $stStatus = 'DAT';
                        $stStatusLabel = 'Đã hoàn thiện sau bảo vệ';
                        $stBadge = 'bg-success';
                    } elseif ($nhom->TrangThai === 'Hoàn thành' || ($bc && $bc->TrangThai === 'Đạt')) {
                        $stStatus = 'DAT';
                        $stStatusLabel = 'Đã hoàn thành';
                        $stBadge = 'bg-success';
                    }
                }

                $stages[$stNum] = [
                    'name'         => $stMeta['name'],
                    'short'        => $stMeta['short'],
                    'icon'         => $stMeta['icon'],
                    'status'       => $stStatus,
                    'status_label' => $stStatusLabel,
                    'badge'        => $stBadge,
                    'date'         => $bc?->NgayNop ? Carbon::parse($bc->NgayNop)->format('d/m/Y') : null,
                ];
            }

            // Tính số giai đoạn đã hoàn thành & giai đoạn hiện tại
            $completedStages = 0;
            $currentStage = 1;
            foreach ($stages as $stNum => $stInfo) {
                if ($stInfo['status'] === 'DAT') {
                    $completedStages++;
                    $currentStage = min(6, $stNum + 1);
                } else {
                    $currentStage = $stNum;
                    break;
                }
            }
            if ($completedStages === 6) {
                $currentStage = 6;
            }

            $progressPercent = (int) round(($completedStages / 6) * 100);

            // Xác định trạng thái cảnh báo / tiến độ
            $status = 'DUNG_TIEN_DO';
            $statusLabel = 'Đúng tiến độ';
            $statusBadge = 'bg-success';
            $priority = 3;

            if ($completedStages === 6 || $nhom->TrangThai === 'Hoàn thành') {
                $status = 'HOAN_THANH';
                $statusLabel = 'Đã hoàn thành';
                $statusBadge = 'bg-primary';
                $priority = 4;
            } elseif ($currentStage === 1 && $stages[1]['status'] === 'CHUA_NOP' && Carbon::now()->format('Y-m-d') > '2026-09-18') {
                $status = 'QUA_HAN';
                $statusLabel = 'Trễ hạn GĐ 1';
                $statusBadge = 'bg-danger';
                $priority = 1;
            } elseif ($currentStage === 2 && $stages[2]['status'] === 'CHUA_NOP' && Carbon::now()->format('Y-m-d') > '2026-10-16') {
                $status = 'QUA_HAN';
                $statusLabel = 'Trễ hạn GĐ 2';
                $statusBadge = 'bg-danger';
                $priority = 1;
            } elseif ($stages[$currentStage]['status'] === 'NOP_LAI') {
                $status = 'SAP_DEN_HAN';
                $statusLabel = 'Cần nộp lại GĐ ' . $currentStage;
                $statusBadge = 'bg-warning text-dark';
                $priority = 2;
            }

            $nhom->stages = $stages;
            $nhom->current_stage = $currentStage;
            $nhom->current_stage_label = $stageDefinitions[$currentStage]['short'];
            $nhom->completed_stages = $completedStages;
            $nhom->progress_percent = min(100, $progressPercent);
            $nhom->status_code = $status;
            $nhom->status_label = $statusLabel;
            $nhom->status_badge = $statusBadge;
            $nhom->priority = $priority;

            return $nhom;
        });

        // Lọc theo Giai đoạn nếu có
        if ($request->filled('stage') && is_numeric($request->stage)) {
            $stageFilter = (int) $request->stage;
            $processedNhoms = $processedNhoms->where('current_stage', $stageFilter)->values();
        }

        // Lọc theo Status Code nếu có
        if ($request->filled('status_code')) {
            $processedNhoms = $processedNhoms->where('status_code', $request->status_code)->values();
        }

        // Sắp xếp ưu tiên (Cảnh báo quá hạn -> Sắp đến hạn -> Đang làm -> Hoàn thành)
        $processedNhoms = $processedNhoms->sortBy('priority')->values();

        // Thống kê theo 6 giai đoạn và trạng thái
        $stats = [
            'total'        => $allNhoms->count(),
            'gd1'          => $allNhoms->where('current_stage', 1)->count(),
            'gd2'          => $allNhoms->where('current_stage', 2)->count(),
            'gd3'          => $allNhoms->where('current_stage', 3)->count(),
            'gd4'          => $allNhoms->where('current_stage', 4)->count(),
            'gd5'          => $allNhoms->where('current_stage', 5)->count(),
            'gd6'          => $allNhoms->where('current_stage', 6)->count(),
            'dung_tien_do' => $allNhoms->where('status_code', 'DUNG_TIEN_DO')->count(),
            'sap_den_han'  => $allNhoms->where('status_code', 'SAP_DEN_HAN')->count(),
            'qua_han'      => $allNhoms->where('status_code', 'QUA_HAN')->count(),
            'hoan_thanh'   => $allNhoms->where('status_code', 'HOAN_THANH')->count(),
        ];

        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $giangViens = GiangVien::orderBy('HoTen')->get();
        $khoas = Khoa::orderBy('TenKhoa')->get();
        $boMons = BoMon::orderBy('TenBoMon')->get();

        return view('admin.theodoi.index', compact(
            'processedNhoms', 'stats', 'stageDefinitions', 'activePlan', 'currentPhase',
            'hocKies', 'giangViens', 'khoas', 'boMons'
        ) + ['Khoa' => $khoas]);
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
