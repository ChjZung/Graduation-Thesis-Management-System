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
        if (Schema::hasTable('detai') && !Schema::hasColumn('detai', 'DuLieuNhap')) {
            Schema::table('detai', function (Blueprint $table) {
                $table->longText('DuLieuNhap')->nullable()->after('LyDoTuChoi');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('detai') && Schema::hasColumn('detai', 'DuLieuNhap')) {
            Schema::table('detai', function (Blueprint $table) {
                $table->dropColumn('DuLieuNhap');
            });
        }
    }
};
