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
        Schema::table('Khoa', function (Blueprint $table) {
            if (!Schema::hasColumn('Khoa', 'TruongKhoa')) {
                $table->string('TruongKhoa', 100)->nullable()->after('TenKhoa');
            }
            if (!Schema::hasColumn('Khoa', 'MoTa')) {
                $table->text('MoTa')->nullable()->after('TruongKhoa');
            }
        });

        Schema::table('BoMon', function (Blueprint $table) {
            if (!Schema::hasColumn('BoMon', 'TruongBoMon')) {
                $table->string('TruongBoMon', 100)->nullable()->after('TenBoMon');
            }
            if (!Schema::hasColumn('BoMon', 'MoTa')) {
                $table->text('MoTa')->nullable()->after('TruongBoMon');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('BoMon', function (Blueprint $table) {
            if (Schema::hasColumn('BoMon', 'MoTa')) {
                $table->dropColumn('MoTa');
            }
            if (Schema::hasColumn('BoMon', 'TruongBoMon')) {
                $table->dropColumn('TruongBoMon');
            }
        });

        Schema::table('Khoa', function (Blueprint $table) {
            if (Schema::hasColumn('Khoa', 'MoTa')) {
                $table->dropColumn('MoTa');
            }
            if (Schema::hasColumn('Khoa', 'TruongKhoa')) {
                $table->dropColumn('TruongKhoa');
            }
        });
    }
};
