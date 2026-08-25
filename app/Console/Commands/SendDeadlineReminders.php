<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KeHoachKhoaLuan;
use App\Models\Nhom;
use App\Services\ThongBaoService;
use Carbon\Carbon;

class SendDeadlineReminders extends Command
{
    /**
     * Tên và cú pháp của Artisan Command
     */
    protected $signature = 'app:send-deadline-reminders';

    /**
     * Mô tả nhiệm vụ
     */
    protected $description = 'Tự động kiểm tra các mốc deadline kế hoạch và phát hành thông báo nhắc nhở tới Sinh viên & GVHD';

    public function handle()
    {
        $this->info("=== BẮT ĐẦU TIẾN TRÌNH QUÉT VÀ GỬI THÔNG BÁO NHẮC DEADLINE TỰ ĐỘNG ===");

        $activePlans = KeHoachKhoaLuan::with('mocThoiGians')->get();

        if ($activePlans->isEmpty()) {
            $this->info("Không có kế hoạch khóa luận nào đang hoạt động.");
            return Command::SUCCESS;
        }

        $now = Carbon::now();
        $totalSent = 0;

        foreach ($activePlans as $plan) {
            foreach ($plan->mocThoiGians as $moc) {
                if (!$moc->NgayKetThuc) continue;

                $endDate = Carbon::parse($moc->NgayKetThuc);
                $diffDays = $now->diffInDays($endDate, false);

                // Quét các mốc còn đúng 3 ngày hoặc đã quá hạn dưới 7 ngày
                if (($diffDays >= 0 && $diffDays <= 3) || ($diffDays < 0 && $diffDays >= -7)) {
                    $isOverdue = $diffDays < 0;
                    $statusText = $isOverdue ? "ĐÃ QUÁ HẠN (" . abs((int)$diffDays) . " ngày)" : "SẮP ĐẾN HẠN (Còn " . (int)$diffDays . " ngày)";

                    $title = "[THÔNG BÁO TIẾN ĐỘ] " . ($isOverdue ? "🔴 Cảnh báo quá hạn: " : "🟡 Nhắc nhở sắp đến hạn: ") . $moc->TenMoc;
                    $content = "Mốc quy trình '{$moc->TenMoc}' thuộc kế hoạch '{$plan->TenKeHoach}' {$statusText}. Hạn chót: " . date('d/m/Y H:i', strtotime($moc->NgayKetThuc)) . ". Vui lòng hoàn thành báo cáo/hồ sơ đúng hạn.";

                    $nhoms = Nhom::with(['truongNhom', 'thanhViens.sinhVien'])->get();

                    foreach ($nhoms as $nhom) {
                        // Gửi tới Trưởng nhóm
                        if ($nhom->truongNhom && $nhom->truongNhom->MaTK) {
                            ThongBaoService::guiDen($nhom->truongNhom->MaTK, $title, $content, 'Kế hoạch');
                            $totalSent++;
                        }
                        // Gửi tới Thành viên
                        foreach ($nhom->thanhViens as $tv) {
                            if ($tv->sinhVien && $tv->sinhVien->MaTK) {
                                ThongBaoService::guiDen($tv->sinhVien->MaTK, $title, $content, 'Kế hoạch');
                                $totalSent++;
                            }
                        }
                    }
                }
            }
        }

        $this->info("🎉 Đã phát hành tổng cộng {$totalSent} thông báo nhắc deadline tự động!");
        return Command::SUCCESS;
    }
}
