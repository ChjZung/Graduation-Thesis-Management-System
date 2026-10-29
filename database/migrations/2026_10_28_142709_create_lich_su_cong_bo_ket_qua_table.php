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
        if (!Schema::hasTable('lich_su_cong_bo_ket_qua')) {
            Schema::create('lich_su_cong_bo_ket_qua', function (Blueprint $table) {
                $table->id();
                $table->string('LoaiHanhDong', 30); // CONG_BO, THU_HOI
                $table->string('PhamVi', 30); // DE_TAI, HOI_DONG, TOAN_DOT
                $table->string('MaHocKy', 30)->nullable();
                $table->string('MaHoiDong', 30)->nullable();
                $table->string('MaHoSo', 30)->nullable();
                $table->integer('SoLuongDeTai')->default(0);
                $table->integer('SoLuongSinhVien')->default(0);
                $table->string('NguoiThucHien', 150);
                $table->string('MaTK', 50)->nullable();
                $table->text('LyDo')->nullable();
                $table->json('DuLieuSnapshot')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lich_su_cong_bo_ket_qua');
    }
};
