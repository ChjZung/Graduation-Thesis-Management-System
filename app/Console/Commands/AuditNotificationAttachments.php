<?php
namespace App\Console\Commands;

use App\Models\{HoiDong, ThongBao};
use App\Services\{HoiDongPdfService, NotificationAttachmentService};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditNotificationAttachments extends Command
{
    protected $signature = 'notifications:audit-attachments {--repair : Repair only explicitly selected councils} {--council=*} {--regenerate-missing : Rebuild a missing decision from the selected council data}';
    protected $description = 'Audit recorded notification attachments; optionally repair exact legacy council links without republishing or sending notices.';

    public function handle(): int
    {
        if ($this->option('repair') && !$this->option('council')) {
            $this->error('Select explicit --council IDs before repairing.');
            return self::FAILURE;
        }
        $counts = ['no_attachment' => 0, 'available' => 0, 'missing' => 0];
        foreach (ThongBao::cursor() as $notice) {
            $attachment = NotificationAttachmentService::resolve($notice);
            $counts[$attachment === null ? 'no_attachment' : ($attachment['missing'] ? 'missing' : 'available')]++;
        }
        $this->line(json_encode($counts));
        if (!$this->option('repair')) return self::SUCCESS;
        $repaired = 0;
        foreach (HoiDong::whereIn('MaHoiDong', $this->option('council'))->where('TrangThaiCongBo', 'Đã công bố')->get() as $council) {
            // Old notifications had no council FK. Never guess when names are ambiguous.
            if (HoiDong::where('TenHoiDong', $council->TenHoiDong)->count() !== 1) {
                $this->warn($council->MaHoiDong.': ambiguous council name; skipped.');
                continue;
            }
            $path = $council->FileQuyetDinh;
            $probe = new ThongBao(['MaThongBao' => 'audit', 'FileDinhKem' => $path]);
            $attachment = NotificationAttachmentService::resolve($probe);
            if ((!$attachment || $attachment['missing']) && $this->option('regenerate-missing')) {
                if ($council->thanhViens()->count() < 3 || !$council->hoSoBaoVes()->exists()) {
                    $this->warn($council->MaHoiDong.': incomplete council data; skipped.');
                    continue;
                }
                $path = HoiDongPdfService::savePdfToStorage($council);
                $probe->FileDinhKem = $path;
                $attachment = NotificationAttachmentService::resolve($probe);
            }
            if (!$attachment || $attachment['missing']) {
                $this->warn($council->MaHoiDong.': missing source PDF; skipped.');
                continue;
            }
            $title = 'Thông báo công bố Hội đồng bảo vệ khóa luận: '.$council->TenHoiDong;
            foreach (ThongBao::where('LoaiThongBao', 'Hội đồng')->where('TieuDe', $title)->get() as $notice) {
                $old = NotificationAttachmentService::resolve($notice);
                if ($old && !$old['missing']) continue;
                DB::table('ThongBao')->where('MaThongBao', $notice->MaThongBao)->update(['FileDinhKem' => $path]);
                $repaired++;
            }
        }
        $this->info('Updated attachment links: '.$repaired.'. No notifications sent; no publication status changed.');
        return self::SUCCESS;
    }
}
