<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HoSoBaoVe;
use App\Services\ThongBaoService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AuditDefenseDossiersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'defense:audit-reports 
                            {--dry-run : Chỉ quét và liệt kê danh sách hồ sơ vi phạm, không cập nhật CSDL}
                            {--status=cho_xac_nhan : Trạng thái chuyển về khi xử lý thật: cho_xac_nhan hoặc yeu_cau_sua}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rà soát và xử lý các hồ sơ bảo vệ đã duyệt nhưng chưa có báo cáo toàn văn hợp lệ';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $targetStatusOpt = $this->option('status');

        $targetXacNhan = ($targetStatusOpt === 'yeu_cau_sua') ? 'Yêu cầu chỉnh sửa' : 'Chờ xác nhận';
        $targetTrangThai = ($targetStatusOpt === 'yeu_cau_sua') ? 'Yêu cầu chỉnh sửa' : 'Chờ GVHD kiểm tra';

        $this->info("================================================================================");
        $this->info("  HỆ THỐNG QUẢN LÝ KHÓA LUẬN - RÀ SOÁT HỒ SƠ BẢO VỆ THIẾU BÁO CÁO TOÀN VĂN");
        $this->info("================================================================================");
        if ($isDryRun) {
            $this->warn(">>> CHẾ ĐỘ THỬ NGHIỆM (--dry-run): Không có dữ liệu nào trong CSDL bị thay đổi. <<<");
        } else {
            $this->alert(">>> CHẾ ĐỘ THỰC THI THẬT: Sẽ đưa các hồ sơ chưa vào hội đồng về '{$targetXacNhan}'. <<<");
        }
        $this->newLine();

        // 1. Quét toàn bộ hồ sơ đang ở trạng thái đã duyệt hoặc đã đủ điều kiện bảo vệ
        $candidates = HoSoBaoVe::with([
            'nhom.truongNhom',
            'deTai.giangVien',
            'hoiDong',
            'tepHoSoBaoVes',
            'phienBans',
        ])->where(function ($q) {
            $q->where('XacNhanGVHD', 'Đã duyệt')
              ->orWhereIn('TrangThai', ['Đã phê duyệt', 'Đủ điều kiện bảo vệ', 'Đã phân công', 'Đã phân công hội đồng']);
        })->get();

        $invalidNotInCouncil = [];
        $invalidInCouncil    = [];

        foreach ($candidates as $hs) {
            if (!$hs->hasBaoCaoToanVan()) {
                if (!empty($hs->MaHoiDong)) {
                    $invalidInCouncil[] = $hs;
                } else {
                    $invalidNotInCouncil[] = $hs;
                }
            }
        }

        $totalViolations = count($invalidNotInCouncil) + count($invalidInCouncil);

        if ($totalViolations === 0) {
            $this->info("✅ Tuyệt vời! Không phát hiện hồ sơ nào 'Đã duyệt bảo vệ' mà thiếu báo cáo toàn văn.");
            return Command::SUCCESS;
        }

        $this->warn("⚠️  Phát hiện {$totalViolations} hồ sơ bảo vệ vi phạm quy định (đã duyệt bảo vệ nhưng chưa có báo cáo toàn văn hợp lệ):");
        $this->line("   - Số hồ sơ chưa vào Hội đồng (sẽ được thu hồi trạng thái): " . count($invalidNotInCouncil));
        $this->line("   - Số hồ sơ ĐÃ ĐƯỢC XẾP VÀO HỘI ĐỒNG (giữ nguyên, cần xử lý thủ công): " . count($invalidInCouncil));
        $this->newLine();

        // BẢNG 1: Hồ sơ đã được xếp vào Hội đồng (Cảnh báo đặc biệt, không tự ý xóa)
        if (!empty($invalidInCouncil)) {
            $this->error("🔴 DANH SÁCH HỒ SƠ THIẾU BÁO CÁO NHƯNG ĐÃ ĐƯỢC XẾP VÀO HỘI ĐỒNG BẢO VỆ:");
            $this->comment("   (Lưu ý: Không tự động xóa để tránh ảnh hưởng quyết định và lịch bảo vệ; cần người phụ trách xử lý thủ công)");
            
            $rowsInCouncil = [];
            foreach ($invalidInCouncil as $hs) {
                $rowsInCouncil[] = [
                    'MaHoSo'     => $hs->MaHoSo,
                    'MaNhom'     => $hs->MaNhom,
                    'DeTai'      => \Illuminate\Support\Str::limit($hs->deTai?->TenDeTai ?? 'N/A', 35),
                    'GVHD'       => $hs->deTai?->giangVien?->HoTen ?? 'N/A',
                    'MaHoiDong'  => $hs->MaHoiDong . ' (' . ($hs->hoiDong?->TenHoiDong ?? 'HĐ') . ')',
                    'TrangThai'  => $hs->TrangThai,
                    'XacNhan'    => $hs->XacNhanGVHD,
                ];
            }
            $this->table(
                ['Mã Hồ Sơ', 'Nhóm', 'Đề Tài', 'GVHD', 'Hội Đồng Hiện Tại', 'Trạng Thái', 'Xác Nhận GVHD'],
                $rowsInCouncil
            );
            $this->newLine();
        }

        // BẢNG 2: Hồ sơ chưa vào Hội đồng (đối tượng được đưa về trạng thái chờ xử lý)
        if (!empty($invalidNotInCouncil)) {
            $this->info("🟡 DANH SÁCH HỒ SƠ THIẾU BÁO CÁO CHƯA VÀO HỘI ĐỒNG:");
            $rowsNotInCouncil = [];
            foreach ($invalidNotInCouncil as $hs) {
                $rowsNotInCouncil[] = [
                    'MaHoSo'       => $hs->MaHoSo,
                    'MaNhom'       => $hs->MaNhom,
                    'DeTai'        => \Illuminate\Support\Str::limit($hs->deTai?->TenDeTai ?? 'N/A', 40),
                    'GVHD'         => $hs->deTai?->giangVien?->HoTen ?? 'N/A',
                    'TrangThaiCu'  => "{$hs->TrangThai} | {$hs->XacNhanGVHD}",
                    'DuKienChuyen' => "{$targetTrangThai} | {$targetXacNhan}",
                ];
            }
            $this->table(
                ['Mã Hồ Sơ', 'Nhóm', 'Đề Tài', 'GVHD', 'Trạng Thái Cũ', 'Chuyển Về Trạng Thái'],
                $rowsNotInCouncil
            );
            $this->newLine();

            if ($isDryRun) {
                $this->info("💡 Để thực hiện cập nhật thật vào CSDL, hãy chạy lại lệnh mà không có cờ --dry-run:");
                $this->line("   php artisan defense:audit-reports");
                $this->line("   (hoặc tùy chọn chuyển về yêu cầu sửa: php artisan defense:audit-reports --status=yeu_cau_sua)");
                return Command::SUCCESS;
            }

            // TIẾN HÀNH CẬP NHẬT THỰC TẾ
            $this->info("⏳ Đang tiến hành cập nhật trạng thái hồ sơ trong CSDL...");
            $updatedCount = 0;

            DB::transaction(function () use ($invalidNotInCouncil, $targetXacNhan, $targetTrangThai, &$updatedCount) {
                foreach ($invalidNotInCouncil as $hs) {
                    $oldTrangThai = $hs->TrangThai;
                    $oldXacNhan = $hs->XacNhanGVHD;
                    $timestamp = now()->format('Y-m-d H:i:s');
                    $rollbackMarker = "[AUTO_AUDIT_RESET {$timestamp}: TrangThaiCu='{$oldTrangThai}'; XacNhanCu='{$oldXacNhan}']";

                    $hs->update([
                        'XacNhanGVHD' => $targetXacNhan,
                        'TrangThai'   => $targetTrangThai,
                        'GhiChu'      => trim(($hs->GhiChu ?? '') . ' ' . $rollbackMarker),
                    ]);

                    // Cập nhật snapshot phiên bản mới nhất nếu có
                    $latestPb = $hs->phienBans->first();
                    if ($latestPb) {
                        $latestPb->update([
                            'TrangThai' => $targetTrangThai,
                        ]);
                    }

                    // Gửi thông báo đến nhóm sinh viên
                    $msgSV = "Hồ sơ bảo vệ của nhóm ({$hs->MaHoSo}) đã được chuyển về trạng thái '{$targetXacNhan}' do chưa có tệp Báo cáo khóa luận toàn văn hợp lệ theo quy định. Vui lòng tải lên báo cáo toàn văn sớm nhất để GVHD kiểm tra lại.";
                    ThongBaoService::guiDenNhom(
                        $hs->MaNhom,
                        '⚠️ Yêu cầu nộp Báo cáo khóa luận toàn văn để đủ điều kiện bảo vệ',
                        $msgSV,
                        'Bảo vệ',
                        route('sinhvien.hoso.index', ['nhom_id' => $hs->MaNhom])
                    );

                    // Gửi thông báo đến Giảng viên hướng dẫn
                    $gvhd = $hs->deTai?->giangVien;
                    if ($gvhd) {
                        $msgGV = "Hệ thống đã thu hồi trạng thái phê duyệt của nhóm '{$hs->nhom?->TenNhom}' (Mã hồ sơ: {$hs->MaHoSo}) về '{$targetXacNhan}' vì nhóm chưa có tệp báo cáo toàn văn. Sau khi sinh viên nộp báo cáo, Thầy/Cô có thể vào thẩm định lại.";
                        ThongBaoService::gui(
                            $gvhd->MaGV,
                            'ℹ️ Thông báo rà soát: Thu hồi phê duyệt nhóm chưa có báo cáo toàn văn',
                            $msgGV,
                            'Bảo vệ',
                            route('giangvien.xacnhan_baove.show', $hs->MaHoSo)
                        );
                    }

                    Log::warning("AuditDefenseDossiersCommand: Chuyển hồ sơ {$hs->MaHoSo} từ {$oldTrangThai}/{$oldXacNhan} về {$targetTrangThai}/{$targetXacNhan} do thiếu báo cáo toàn văn.");
                    $updatedCount++;
                }
            });

            $this->info("✅ Đã cập nhật thành công {$updatedCount} hồ sơ về trạng thái '{$targetXacNhan}'.");
            $this->info("   - Đã gửi thông báo đến các nhóm sinh viên và giảng viên hướng dẫn tương ứng.");
            $this->info("   - Đã ghi vết lịch sử đối soát vào trường GhiChu phục vụ hoàn tác nếu cần.");
            $this->newLine();
            $this->comment("📝 HƯỚNG DẪN HOÀN TÁC (ROLLBACK):");
            $this->line("   Nếu cần hoàn tác trạng thái cũ cho các hồ sơ vừa cập nhật, có thể trích xuất giá trị `TrangThaiCu` và `XacNhanCu` trong chuỗi marker `[AUTO_AUDIT_RESET ...]` được lưu tại cột `GhiChu` của từng hồ sơ.");
        }

        return Command::SUCCESS;
    }
}
