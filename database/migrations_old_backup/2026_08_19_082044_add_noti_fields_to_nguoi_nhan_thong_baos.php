<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nguoi_nhan_thong_baos', function (Blueprint $table) {
            $table->string('TieuDe', 255)->nullable()->after('MaTK');
            $table->text('NoiDung')->nullable()->after('TieuDe');
            $table->string('Loai', 50)->default('Hệ thống')->after('NoiDung');
            $table->string('DuongDan', 500)->nullable()->after('Loai');
        });
    }

    public function down(): void
    {
        Schema::table('nguoi_nhan_thong_baos', function (Blueprint $table) {
            $table->dropColumn(['TieuDe', 'NoiDung', 'Loai', 'DuongDan']);
        });
    }
};
