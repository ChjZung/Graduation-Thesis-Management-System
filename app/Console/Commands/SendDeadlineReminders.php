<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KeHoachKhoaLuan;
use App\Models\LichGapHuongDan;
use App\Models\DangKyLichHuongDan;
use App\Models\DotNopBaoCao;
use App\Models\BaoCaoTienDo;
use App\Models\DeTai;
use App\Models\Nhom;
use App\Models\ThongBao;
use App\Services\ThongBaoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SendDeadlineReminders extends Command
{
    /**
     * Tên và cú pháp của Artisan Command
     */
    protected $signature = 'app:send-deadline-reminders {--force : Bỏ qua kiểm tra chống gửi trùng lặp trong ngày}';

    /**
     * Mô tả nhiệm vụ
     */
    protected $description = 'Tự động kiểm tra và phát hành thông báo nhắc nhở: (1) Lịch gặp giảng viên, (2) Hạn nộp báo cáo tiến độ, (3) Mốc kế hoạch khóa luận';

    public function handle()
    {
        $this->info("=== BẮT ĐẦU TIẾN TRÌNH QUÉT VÀ PHÁT HÀNH THÔNG BÁO TỰ ĐỘNG ===");

        $isForce = (bool) $this->option('force');
        $now = Carbon::now();

        // 1. Nhắc lịch gặp giảng viên (trong vòng 48h tới: hôm nay hoặc ngày mai)
        $sentLich = $this->remindMeetingSchedules($now, $isForce);

        // 2. Nhắc sắp đến hạn nộp báo cáo tiến độ (còn <= 3 ngày và chưa nộp)
        $sentBaoCao = $this->remindReportDeadlines($now, $isForce);

        // 3. Nhắc các mốc kế hoạch khóa luận chung
        $sentKeHoach = $this->remindPlanMilestones($now, $isForce);

        $totalSent = $sentLich + $sentBaoCao + $sentKeHoach;
        $this->info("=========================================================");
        $this->info("🎉 HOÀN THÀNH TIẾN TRÌNH NHẮC NHỞ TỰ ĐỘNG:");
        $this->info("   - Nhắc lịch gặp giảng viên: {$sentLich} thông báo");
        $this->info("   - Nhắc hạn nộp báo cáo tiến độ: {$sentBaoCao} thông báo");
        $this->info("   - Nhắc mốc kế hoạch tổng thể: {$sentKeHoach} thông báo");
        $this->info("   => TỔNG CỘNG ĐÃ PHÁT HÀNH: {$totalSent} THÔNG BÁO");
        $this->info("=========================================================");

        return Command::SUCCESS;
    }

    /**
     * 1. Nhắc nhở lịch gặp hướng dẫn sắp diễn ra (trong vòng 48h tới)
     */
    public function remindMeetingSchedules(Carbon $now, bool $isForce = false): int
    {
        $sentCount = 0;
        $this->info("-> Đang quét lịch gặp hướng dẫn sắp diễn ra...");

        // Quét các lịch gặp diễn ra từ thời điểm hiện tại đến hết 48 giờ tới
        $windowEnd = (clone $now)->addHours(48);

        $upcomingMeetings = LichGapHuongDan::with([
            'giangVien.taiKhoan',
            'dangKys' => function ($q) {
                $q->whereIn('TrangThai', ['DaXacNhan', 'ChoXacNhan', 'da_xac_nhan', 'cho_xac_nhan'])
                  ->with(['nhom.truongNhom.taiKhoan', 'nhom.thanhViens.sinhVien.taiKhoan', 'nhom.deTai']);
            }
        ])
        ->whereNotIn('TrangThai', ['DaHuy', 'da_huy', 'DaHoanThanh'])
        ->where('ThoiGianBatDau', '>=', $now)
        ->where('ThoiGianBatDau', '<=', $windowEnd)
        ->get();

        foreach ($upcomingMeetings as $slot) {
            $gv = $slot->giangVien;
            $start = Carbon::parse($slot->ThoiGianBatDau);
            $diffHours = (int) $now->diffInHours($start, false);
            $thoiGianStr = $start->format('H:i') . ' ngày ' . $start->format('d/m/Y');
            $hinhThucStr = ($slot->HinhThuc === 'TrucTuyen') ? 'Trực tuyến' : 'Trực tiếp';
            $diaDiemStr = ($slot->HinhThuc === 'TrucTuyen') ? ($slot->LinkCuocHop ?: 'Link trực tuyến') : ($slot->DiaDiem ?: 'Phòng làm việc GV');

            $timeDesc = ($diffHours <= 12) ? "SẮP DIỄN RA TRONG HÔM NAY" : "DIỄN RA VÀO NGÀY MAI (còn khoảng {$diffHours} giờ)";

            foreach ($slot->dangKys as $dk) {
                $nhom = $dk->nhom;
                if (!$nhom) continue;

                $tenDeTai = $nhom->deTai?->TenDeTai ?: 'Khóa luận tốt nghiệp';

                // 1.1. Gửi thông báo nhắc nhở cho Sinh viên trong nhóm
                $titleSV = "[NHẮC LỊCH HẸN] Nhắc nhở lịch gặp hướng dẫn ({$timeDesc})";
                $contentSV = "Nhóm bạn có lịch hẹn gặp Thầy/Cô {$gv?->HoTen} vào lúc {$thoiGianStr} [Hình thức: {$hinhThucStr} - Địa điểm: {$diaDiemStr}]. Đề tài: {$tenDeTai}. Nội dung trao đổi: {$slot->NoiDung}. Vui lòng chuẩn bị tài liệu báo cáo và tham gia đúng giờ!";

                // Chống spam: kiểm tra đã gửi thông báo nhắc lịch này cho nhóm trong ngày chưa
                $alreadySentSV = !$isForce && ThongBao::where('LoaiThongBao', 'Lịch hẹn')
                    ->where('TieuDe', 'like', "%{$slot->MaLichGap}%")
                    ->whereDate('created_at', $now->toDateString())
                    ->exists();

                if (!$alreadySentSV) {
                    ThongBaoService::guiDenNhom(
                        $nhom->MaNhom,
                        $titleSV . " [{$slot->MaLichGap}]",
                        $contentSV,
                        'Lịch hẹn'
                    );
                    $sentCount++;
                }

                // 1.2. Gửi thông báo nhắc nhở cho Giảng viên hướng dẫn
                if ($gv && $gv->taiKhoan?->MaTK) {
                    $titleGV = "[NHẮC LỊCH HẸN] Lịch gặp hướng dẫn với nhóm {$nhom->TenNhom} ({$timeDesc})";
                    $contentGV = "Thầy/Cô có buổi hẹn gặp hướng dẫn nhóm {$nhom->TenNhom} (Đề tài: {$tenDeTai}) vào lúc {$thoiGianStr} [{$hinhThucStr} - {$diaDiemStr}]. Nội dung đăng ký: {$dk->GhiChuYeuCau}.";

                    $alreadySentGV = !$isForce && ThongBao::where('LoaiThongBao', 'Lịch hẹn')
                        ->where('DoiTuongNhan', $gv->taiKhoan->MaTK)
                        ->where('TieuDe', 'like', "%{$slot->MaLichGap}%")
                        ->whereDate('created_at', $now->toDateString())
                        ->exists();

                    if (!$alreadySentGV) {
                        ThongBaoService::guiDen(
                            $gv->taiKhoan->MaTK,
                            $titleGV . " [{$slot->MaLichGap}]",
                            $contentGV,
                            'Lịch hẹn'
                        );
                        $sentCount++;
                    }
                }
            }
        }

        return $sentCount;
    }

    /**
     * 2. Nhắc nhở sắp đến hạn nộp báo cáo tiến độ (còn <= 3 ngày hoặc quá hạn dưới 3 ngày mà chưa nộp)
     */
    public function remindReportDeadlines(Carbon $now, bool $isForce = false): int
    {
        $sentCount = 0;
        $this->info("-> Đang quét các đợt nộp báo cáo tiến độ sắp đến hạn...");

        // Quét các đợt nộp báo cáo đang mở hoặc chưa kết thúc
        $dotNops = DotNopBaoCao::with([
            'deTai.giangVien.taiKhoan',
            'deTai.nhoms.thanhViens.sinhVien.taiKhoan',
            'deTai.nhoms.truongNhom.taiKhoan',
        ])
        ->whereIn('TrangThai', ['DANG_MO', 'dang_mo', 'MO', 'MoDangKy', 'Đang mở'])
        ->whereNotNull('HanNop')
        ->get();

        foreach ($dotNops as $dot) {
            $hanNop = Carbon::parse($dot->HanNop);
            $diffDays = $now->diffInDays($hanNop, false);
            $diffHours = $now->diffInHours($hanNop, false);

            // Kiểm tra các đợt còn <= 3 ngày (từ 0 đến 3 ngày) hoặc vừa quá hạn trong vòng 2 ngày
            if (($diffDays >= 0 && $diffDays <= 3) || ($diffDays < 0 && $diffDays >= -2)) {
                $isOverdue = $diffDays < 0;
                $statusBadge = $isOverdue
                    ? "🔴 ĐÃ QUÁ HẠN NỘP (" . abs((int)$diffDays) . " ngày)"
                    : "🟡 SẮP ĐẾN HẠN NỘP (Còn " . ($diffDays == 0 ? "hôm nay - " . (int)$diffHours . " giờ" : (int)$diffDays . " ngày") . ")";

                $hanNopStr = $hanNop->format('H:i') . ' ngày ' . $hanNop->format('d/m/Y');
                $deTai = $dot->deTai;
                if (!$deTai) continue;

                // Lấy danh sách nhóm làm đề tài này
                $nhoms = $deTai->nhoms ?? collect();
                if ($nhoms->isEmpty()) {
                    // Thử tìm qua PhieuDangKy
                    $nhoms = Nhom::whereHas('phieuDangKys', fn($p) => $p->where('MaDeTai', $deTai->MaDeTai))->get();
                }

                foreach ($nhoms as $nhom) {
                    // Kiểm tra nhóm đã nộp báo cáo cho đợt này chưa
                    $daNop = BaoCaoTienDo::where('MaDotNop', $dot->MaDotNop)
                        ->where('MaNhom', $nhom->MaNhom)
                        ->whereNotIn('TrangThai', ['TU_CHOI', 'tu_choi', 'Bị từ chối'])
                        ->exists();

                    // Nếu đã nộp rồi thì không cần nhắc
                    if ($daNop) continue;

                    // 2.1. Gửi thông báo nhắc nhở cho Sinh viên
                    $titleSV = "[NHẮC HẠN NỘP BÁO CÁO] {$statusBadge}: {$dot->TenDotNop}";
                    $contentSV = "Đợt nộp báo cáo '{$dot->TenDotNop}' thuộc đề tài '{$deTai->TenDeTai}' {$statusBadge}. Hạn chót nộp: {$hanNopStr}. Nhóm bạn CHƯA NỘP báo cáo. Vui lòng hoàn thiện nội dung và nộp báo cáo kèm sản phẩm đúng hạn trên hệ thống!";

                    $uniqueKeySV = "TB_REMIND_BC_{$dot->MaDotNop}_{$nhom->MaNhom}";
                    $alreadySentSV = !$isForce && ThongBao::where('LoaiThongBao', 'Báo cáo')
                        ->where('TieuDe', 'like', "%{$uniqueKeySV}%")
                        ->whereDate('created_at', $now->toDateString())
                        ->exists();

                    if (!$alreadySentSV) {
                        ThongBaoService::guiDenNhom(
                            $nhom->MaNhom,
                            $titleSV . " [{$uniqueKeySV}]",
                            $contentSV,
                            'Báo cáo'
                        );
                        $sentCount++;
                    }

                    // 2.2. Gửi thông báo đôn đốc cho Giảng viên hướng dẫn
                    $gvhd = $deTai->giangVien;
                    if ($gvhd && $gvhd->taiKhoan?->MaTK) {
                        $titleGV = "[ĐÔN ĐỐC TIẾN ĐỘ] Nhóm {$nhom->TenNhom} chưa nộp báo cáo ({$dot->TenDotNop})";
                        $contentGV = "Nhóm sinh viên {$nhom->TenNhom} thực hiện đề tài '{$deTai->TenDeTai}' chưa nộp báo cáo cho đợt '{$dot->TenDotNop}' (Hạn chót: {$hanNopStr} - {$statusBadge}). Kính đề nghị Thầy/Cô đôn đốc nhóm sinh viên nộp báo cáo đúng hạn.";

                        $uniqueKeyGV = "TB_REMIND_GV_BC_{$dot->MaDotNop}_{$nhom->MaNhom}";
                        $alreadySentGV = !$isForce && ThongBao::where('LoaiThongBao', 'Tiến độ')
                            ->where('DoiTuongNhan', $gvhd->taiKhoan->MaTK)
                            ->where('TieuDe', 'like', "%{$uniqueKeyGV}%")
                            ->whereDate('created_at', $now->toDateString())
                            ->exists();

                        if (!$alreadySentGV) {
                            ThongBaoService::guiDen(
                                $gvhd->taiKhoan->MaTK,
                                $titleGV . " [{$uniqueKeyGV}]",
                                $contentGV,
                                'Tiến độ'
                            );
                            $sentCount++;
                        }
                    }
                }
            }
        }

        return $sentCount;
    }

    /**
     * 3. Nhắc nhở mốc tiến độ kế hoạch khóa luận chung
     */
    public function remindPlanMilestones(Carbon $now, bool $isForce = false): int
    {
        $sentCount = 0;
        $this->info("-> Đang quét các mốc kế hoạch khóa luận tổng thể...");

        $activePlans = KeHoachKhoaLuan::with('mocThoiGians')->get();
        if ($activePlans->isEmpty()) return 0;

        foreach ($activePlans as $plan) {
            foreach ($plan->mocThoiGians as $moc) {
                if (!$moc->NgayKetThuc) continue;

                $endDate = Carbon::parse($moc->NgayKetThuc);
                $diffDays = $now->diffInDays($endDate, false);

                if (($diffDays >= 0 && $diffDays <= 3) || ($diffDays < 0 && $diffDays >= -3)) {
                    $isOverdue = $diffDays < 0;
                    $statusText = $isOverdue ? "ĐÃ QUÁ HẠN (" . abs((int)$diffDays) . " ngày)" : "SẮP ĐẾN HẠN (Còn " . (int)$diffDays . " ngày)";

                    $title = "[THÔNG BÁO TIẾN ĐỘ] " . ($isOverdue ? "🔴 Cảnh báo quá hạn: " : "🟡 Nhắc nhở sắp đến hạn: ") . $moc->TenMoc;
                    $content = "Mốc quy trình '{$moc->TenMoc}' thuộc kế hoạch '{$plan->TenKeHoach}' {$statusText}. Hạn chót: " . date('d/m/Y H:i', strtotime($moc->NgayKetThuc)) . ". Vui lòng hoàn thành báo cáo/hồ sơ đúng hạn.";

                    $uniqueKey = "MOC_{$moc->MaMoc}";
                    $alreadySent = !$isForce && ThongBao::where('LoaiThongBao', 'Kế hoạch')
                        ->where('TieuDe', 'like', "%{$uniqueKey}%")
                        ->whereDate('created_at', $now->toDateString())
                        ->exists();

                    if (!$alreadySent) {
                        $nhoms = Nhom::with(['truongNhom.taiKhoan', 'thanhViens.sinhVien.taiKhoan'])->get();
                        foreach ($nhoms as $nhom) {
                            if ($nhom->truongNhom?->taiKhoan?->MaTK) {
                                ThongBaoService::guiDen($nhom->truongNhom->taiKhoan->MaTK, $title . " [{$uniqueKey}]", $content, 'Kế hoạch');
                                $sentCount++;
                            }
                        }
                    }
                }
            }
        }

        return $sentCount;
    }
}
