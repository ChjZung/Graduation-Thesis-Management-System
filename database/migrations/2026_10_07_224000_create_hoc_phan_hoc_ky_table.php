<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('HocPhan_HocKy')) {
            Schema::create('HocPhan_HocKy', function (Blueprint $table) {
                $table->id();
                $table->string('MaHocPhan', 20);
                $table->string('MaHocKy', 20);
                $table->string('TrangThai', 50)->default('Đang mở'); // Đang mở, Đã đóng
                $table->text('GhiChu')->nullable();
                $table->timestamps();

                $table->unique(['MaHocPhan', 'MaHocKy']);
                $table->foreign('MaHocPhan')->references('MaHocPhan')->on('HocPhan')->cascadeOnDelete();
                $table->foreign('MaHocKy')->references('MaHocKy')->on('HocKy')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('HocPhan_HocKy');
    }
};
