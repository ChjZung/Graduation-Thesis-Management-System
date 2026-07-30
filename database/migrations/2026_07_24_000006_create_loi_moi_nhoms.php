<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('loi_moi_nhoms')) {
            Schema::create('loi_moi_nhoms', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('MaNhom');
                $table->unsignedBigInteger('MaSV_Moi');
                $table->unsignedBigInteger('MaSV_DuocMoi');
                $table->string('TrangThai', 30)->default('cho_xac_nhan'); // cho_xac_nhan, da_chap_nhan, da_tu_choi
                $table->timestamp('NgayMoi')->useCurrent();
                $table->timestamp('NgayPhanHoi')->nullable();
                $table->timestamps();

                $table->foreign('MaNhom')->references('MaNhom')->on('nhom_do_ans')->onDelete('cascade');
                $table->foreign('MaSV_Moi')->references('MaSV')->on('sinh_viens')->onDelete('cascade');
                $table->foreign('MaSV_DuocMoi')->references('MaSV')->on('sinh_viens')->onDelete('cascade');

                $table->unique(['MaNhom', 'MaSV_DuocMoi']);
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('loi_moi_nhoms');
    }
};
