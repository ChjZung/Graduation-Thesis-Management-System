<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('thong_baos', function (Blueprint $table) {
            if (!Schema::hasColumn('thong_baos', 'LoaiThongBao')) {
                $table->string('LoaiThongBao', 50)->nullable()->after('NoiDung');
            }
            if (!Schema::hasColumn('thong_baos', 'DuongDan')) {
                $table->string('DuongDan', 255)->nullable()->after('LoaiThongBao');
            }
            if (!Schema::hasColumn('thong_baos', 'DaDoc')) {
                $table->boolean('DaDoc')->default(false)->after('DuongDan');
            }
        });
    }

    public function down(): void {
        Schema::table('thong_baos', function (Blueprint $table) {
            $table->dropColumn(['LoaiThongBao', 'DuongDan', 'DaDoc']);
        });
    }
};
