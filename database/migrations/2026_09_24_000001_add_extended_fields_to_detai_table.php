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
        Schema::table('DeTai', function (Blueprint $table) {
            if (!Schema::hasColumn('DeTai', 'LinhVuc')) {
                $table->string('LinhVuc', 150)->nullable()->after('YeuCau');
            }
            if (!Schema::hasColumn('DeTai', 'SoLuongSinhVienToiDa')) {
                $table->unsignedInteger('SoLuongSinhVienToiDa')->default(2)->after('LinhVuc');
            }
            if (!Schema::hasColumn('DeTai', 'FileDeCuong')) {
                $table->string('FileDeCuong', 255)->nullable()->after('SoLuongSinhVienToiDa');
            }
            if (!Schema::hasColumn('DeTai', 'MaNganh')) {
                $table->string('MaNganh', 20)->nullable()->after('FileDeCuong');
            }
            if (!Schema::hasColumn('DeTai', 'LyDoTuChoi')) {
                $table->text('LyDoTuChoi')->nullable()->after('TrangThai');
            }
            if (!Schema::hasColumn('DeTai', 'NgayDuyet')) {
                $table->dateTime('NgayDuyet')->nullable()->after('NgayDeXuat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('DeTai', function (Blueprint $table) {
            $cols = ['LinhVuc', 'SoLuongSinhVienToiDa', 'FileDeCuong', 'MaNganh', 'LyDoTuChoi', 'NgayDuyet'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('DeTai', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
