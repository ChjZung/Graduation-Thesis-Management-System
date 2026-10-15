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
        if (!Schema::hasTable('chitieuphanbien')) {
            Schema::create('chitieuphanbien', function (Blueprint $table) {
                $table->id();
                $table->string('MaChiTieuPB', 30)->unique();
                $table->string('MaGV', 20);
                $table->string('MaHocKy', 20)->nullable();
                $table->integer('SoDeTaiToiDa')->default(5);
                $table->string('GhiChu', 255)->nullable();
                $table->timestamps();

                $table->index('MaGV', 'idx_ctpb_magv');
                $table->index('MaHocKy', 'idx_ctpb_mahocky');
                $table->foreign('MaGV', 'fk_ctpb_giangvien')->references('MaGV')->on('GiangVien')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chitieuphanbien');
    }
};
