<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('HocPhan', function (Blueprint $table) {
            if (!Schema::hasColumn('HocPhan', 'SoTietLT')) {
                $table->integer('SoTietLT')->default(0)->after('SoTinChi');
            }
            if (!Schema::hasColumn('HocPhan', 'SoTietTH')) {
                $table->integer('SoTietTH')->default(0)->after('SoTietLT');
            }
            if (!Schema::hasColumn('HocPhan', 'SoTietKhac')) {
                $table->integer('SoTietKhac')->default(0)->after('SoTietTH');
            }
        });
    }

    public function down(): void
    {
        Schema::table('HocPhan', function (Blueprint $table) {
            $table->dropColumn(['SoTietLT', 'SoTietTH', 'SoTietKhac']);
        });
    }
};
