<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Enums\VaiTroHoiDong;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $allMembers = DB::table('ThanhVienHoiDong')->get();
        $backupData = [];
        $changedData = [];
        $updatedCount = 0;

        foreach ($allMembers as $tv) {
            $backupData[] = [
                'MaHoiDong' => $tv->MaHoiDong,
                'MaGV'      => $tv->MaGV,
                'VaiTro'    => $tv->VaiTro,
            ];

            $normalized = VaiTroHoiDong::normalize($tv->VaiTro);
            if ($normalized !== $tv->VaiTro) {
                DB::table('ThanhVienHoiDong')
                    ->where('MaHoiDong', $tv->MaHoiDong)
                    ->where('MaGV', $tv->MaGV)
                    ->update([
                        'VaiTro'     => $normalized,
                        'updated_at' => now(),
                    ]);

                $changedData[] = [
                    'MaHoiDong' => $tv->MaHoiDong,
                    'MaGV'      => $tv->MaGV,
                    'OldVaiTro' => $tv->VaiTro,
                    'NewVaiTro' => $normalized,
                ];
                $updatedCount++;
            }
        }

        $timestamp = date('Y_m_d_His');
        $backupFilePath = $backupDir . "/thanh_vien_hoi_dong_vai_tro_backup_{$timestamp}.json";
        File::put($backupFilePath, json_encode([
            'created_at'    => now()->toDateTimeString(),
            'total_records' => count($backupData),
            'updated_count' => $updatedCount,
            'changes'       => $changedData,
            'backup'        => $backupData,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Lưu bản backup mới nhất để tiện rollback nếu cần
        File::put($backupDir . "/thanh_vien_hoi_dong_vai_tro_backup_latest.json", json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        Log::info("Migration normalize_vai_tro_thanh_vien_hoi_dong: Đã chuẩn hóa {$updatedCount} bản ghi thành viên hội đồng.", [
            'updated_count' => $updatedCount,
            'changes'       => $changedData,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $latestBackup = storage_path('app/backups/thanh_vien_hoi_dong_vai_tro_backup_latest.json');
        if (File::exists($latestBackup)) {
            $data = json_decode(File::get($latestBackup), true);
            if (is_array($data)) {
                foreach ($data as $tv) {
                    DB::table('ThanhVienHoiDong')
                        ->where('MaHoiDong', $tv['MaHoiDong'])
                        ->where('MaGV', $tv['MaGV'])
                        ->update(['VaiTro' => $tv['VaiTro']]);
                }
            }
        }
    }
};
