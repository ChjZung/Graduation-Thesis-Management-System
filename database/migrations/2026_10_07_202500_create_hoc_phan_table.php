<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('HocPhan')) {
            Schema::create('HocPhan', function (Blueprint $table) {
                $table->string('MaHocPhan', 20)->primary();
                $table->string('TenHocPhan', 150);
                $table->integer('SoTinChi')->default(3);
                $table->string('MaKhoa', 20)->nullable();
                $table->string('MaBoMon', 20)->nullable();
                $table->string('LoaiHocPhan', 50)->default('Chuyên ngành');
                $table->string('TrangThai', 50)->default('Đang áp dụng');
                $table->text('MoTa')->nullable();
                $table->timestamps();

                $table->foreign('MaKhoa')->references('MaKhoa')->on('Khoa')->nullOnDelete();
                $table->foreign('MaBoMon')->references('MaBoMon')->on('BoMon')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('HocPhan');
    }
};
