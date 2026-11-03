<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestNhomBaoCaoSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('testing') || !config('database.allow_destructive_demo_seed', false)) {
            throw new \RuntimeException('Demo seed disabled: use an isolated testing database with explicit opt-in.');
        }
        $path = database_path('seed_data_test_nhom_baocao.sql');
        if (!file_exists($path)) {
            if (isset($this->command)) {
                $this->command->error("Không tìm thấy file: {$path}");
            }
            return;
        }

        if (isset($this->command)) {
            $this->command->info('Đang nạp dữ liệu nhóm báo cáo tiến độ test (Đề tài DT27, Nhóm 2001210073)...');
        }

        DB::unprepared(file_get_contents($path));

        if (isset($this->command)) {
            $this->command->info('Nạp dữ liệu nhóm báo cáo tiến độ thành công!');
        }
    }
}
