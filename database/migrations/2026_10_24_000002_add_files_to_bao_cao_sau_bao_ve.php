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
        if (Schema::hasTable('bao_cao_sau_bao_ve')) {
            Schema::table('bao_cao_sau_bao_ve', function (Blueprint $table) {
                if (!Schema::hasColumn('bao_cao_sau_bao_ve', 'FileGiaiTrinh')) {
                    $table->string('FileGiaiTrinh', 500)->nullable()->after('TomTatChinhSua');
                }
                if (!Schema::hasColumn('bao_cao_sau_bao_ve', 'TenFileGiaiTrinh')) {
                    $table->string('TenFileGiaiTrinh', 255)->nullable()->after('FileGiaiTrinh');
                }
                if (!Schema::hasColumn('bao_cao_sau_bao_ve', 'FileMinhChung')) {
                    $table->string('FileMinhChung', 500)->nullable()->after('TenFileGiaiTrinh');
                }
                if (!Schema::hasColumn('bao_cao_sau_bao_ve', 'TenFileMinhChung')) {
                    $table->string('TenFileMinhChung', 255)->nullable()->after('FileMinhChung');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bao_cao_sau_bao_ve')) {
            Schema::table('bao_cao_sau_bao_ve', function (Blueprint $table) {
                $columns = ['FileGiaiTrinh', 'TenFileGiaiTrinh', 'FileMinhChung', 'TenFileMinhChung'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('bao_cao_sau_bao_ve', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
