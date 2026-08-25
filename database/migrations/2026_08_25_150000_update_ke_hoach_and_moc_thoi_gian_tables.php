<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ke_hoach_khoa_luans', function (Blueprint $table) {
            if (!Schema::hasColumn('ke_hoach_khoa_luans', 'MaKhoa')) {
                $table->string('MaKhoa', 10)->nullable()->after('MaHocKy');
            }
            if (!Schema::hasColumn('ke_hoach_khoa_luans', 'MaBoMon')) {
                $table->string('MaBoMon', 10)->nullable()->after('MaKhoa');
            }
            if (!Schema::hasColumn('ke_hoach_khoa_luans', 'NamHoc')) {
                $table->string('NamHoc', 20)->default('2026-2027')->after('MaHocKy');
            }
            if (!Schema::hasColumn('ke_hoach_khoa_luans', 'NgayBatDau')) {
                $table->date('NgayBatDau')->nullable()->after('NoiDung');
            }
            if (!Schema::hasColumn('ke_hoach_khoa_luans', 'NgayKetThuc')) {
                $table->date('NgayKetThuc')->nullable()->after('NgayBatDau');
            }
            if (!Schema::hasColumn('ke_hoach_khoa_luans', 'NguoiLap')) {
                $table->string('NguoiLap', 10)->nullable()->after('MaGVu');
            }
        });

        Schema::table('moc_thoi_gian_khoa_luans', function (Blueprint $table) {
            if (!Schema::hasColumn('moc_thoi_gian_khoa_luans', 'LoaiGiaiDoan')) {
                $table->string('LoaiGiaiDoan', 50)->default('THUC_HIEN')->after('MaKeHoach');
            }
            if (!Schema::hasColumn('moc_thoi_gian_khoa_luans', 'ThuTu')) {
                $table->integer('ThuTu')->default(1)->after('LoaiGiaiDoan');
            }
            if (!Schema::hasColumn('moc_thoi_gian_khoa_luans', 'DoiTuongThucHien')) {
                $table->string('DoiTuongThucHien', 100)->default('Tất cả')->after('ThuTu');
            }
            if (!Schema::hasColumn('moc_thoi_gian_khoa_luans', 'BatBuoc')) {
                $table->boolean('BatBuoc')->default(true)->after('MoTa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ke_hoach_khoa_luans', function (Blueprint $table) {
            $table->dropColumn(['MaKhoa', 'MaBoMon', 'NamHoc', 'NgayBatDau', 'NgayKetThuc', 'NguoiLap']);
        });

        Schema::table('moc_thoi_gian_khoa_luans', function (Blueprint $table) {
            $table->dropColumn(['LoaiGiaiDoan', 'ThuTu', 'DoiTuongThucHien', 'BatBuoc']);
        });
    }
};
