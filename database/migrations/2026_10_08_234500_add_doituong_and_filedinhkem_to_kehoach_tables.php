<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('MocThoiGianKhoaLuan')) {
            Schema::table('MocThoiGianKhoaLuan', function (Blueprint $table) {
                if (!Schema::hasColumn('MocThoiGianKhoaLuan', 'DoiTuongThucHien')) {
                    $table->string('DoiTuongThucHien', 100)->nullable()->default('Tất cả')->after('TenMoc');
                }
            });
        }

        if (Schema::hasTable('KeHoachKhoaLuan')) {
            Schema::table('KeHoachKhoaLuan', function (Blueprint $table) {
                if (!Schema::hasColumn('KeHoachKhoaLuan', 'FileDinhKem')) {
                    $table->string('FileDinhKem', 255)->nullable()->after('NoiDung');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('MocThoiGianKhoaLuan')) {
            Schema::table('MocThoiGianKhoaLuan', function (Blueprint $table) {
                if (Schema::hasColumn('MocThoiGianKhoaLuan', 'DoiTuongThucHien')) {
                    $table->dropColumn('DoiTuongThucHien');
                }
            });
        }

        if (Schema::hasTable('KeHoachKhoaLuan')) {
            Schema::table('KeHoachKhoaLuan', function (Blueprint $table) {
                if (Schema::hasColumn('KeHoachKhoaLuan', 'FileDinhKem')) {
                    $table->dropColumn('FileDinhKem');
                }
            });
        }
    }
};
