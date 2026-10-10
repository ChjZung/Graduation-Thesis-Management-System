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
        if (Schema::hasTable('ChiTietDuyetDeTai')) {
            Schema::table('ChiTietDuyetDeTai', function (Blueprint $table) {
                if (!Schema::hasColumn('ChiTietDuyetDeTai', 'HanhDong')) {
                    $table->string('HanhDong', 100)->nullable()->after('TrangThai');
                }
                if (!Schema::hasColumn('ChiTietDuyetDeTai', 'TrangThaiCu')) {
                    $table->string('TrangThaiCu', 50)->nullable()->after('HanhDong');
                }
                if (!Schema::hasColumn('ChiTietDuyetDeTai', 'NguoiThucHien')) {
                    $table->string('NguoiThucHien', 100)->nullable()->after('MaGV');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ChiTietDuyetDeTai')) {
            Schema::table('ChiTietDuyetDeTai', function (Blueprint $table) {
                if (Schema::hasColumn('ChiTietDuyetDeTai', 'NguoiThucHien')) {
                    $table->dropColumn('NguoiThucHien');
                }
                if (Schema::hasColumn('ChiTietDuyetDeTai', 'TrangThaiCu')) {
                    $table->dropColumn('TrangThaiCu');
                }
                if (Schema::hasColumn('ChiTietDuyetDeTai', 'HanhDong')) {
                    $table->dropColumn('HanhDong');
                }
            });
        }
    }
};
