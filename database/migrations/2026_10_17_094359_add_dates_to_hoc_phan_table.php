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
        Schema::table('HocPhan', function (Blueprint $table) {
            if (!Schema::hasColumn('HocPhan', 'NgayBatDau')) {
                $table->date('NgayBatDau')->nullable()->after('TrangThai');
            }
            if (!Schema::hasColumn('HocPhan', 'NgayKetThuc')) {
                $table->date('NgayKetThuc')->nullable()->after('NgayBatDau');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('HocPhan', function (Blueprint $table) {
            if (Schema::hasColumn('HocPhan', 'NgayKetThuc')) {
                $table->dropColumn('NgayKetThuc');
            }
            if (Schema::hasColumn('HocPhan', 'NgayBatDau')) {
                $table->dropColumn('NgayBatDau');
            }
        });
    }
};
