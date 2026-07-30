<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('nhom_do_ans', function (Blueprint $table) {
            $table->id('MaNhom');
            $table->string('TenNhom', 100);
            $table->unsignedBigInteger('MaLop')->nullable();
            $table->unsignedBigInteger('MaMon')->nullable();
            $table->unsignedBigInteger('MaHocKy');
            $table->unsignedBigInteger('TruongNhom')->nullable();
            $table->string('TrangThai', 50)->default('Đang tạo');
            $table->timestamps();
            
            $table->foreign('MaLop')->references('MaLop')->on('lops')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('MaMon')->references('MaMon')->on('mon_hocs')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('MaHocKy')->references('MaHocKy')->on('hoc_kies')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('TruongNhom')->references('MaSV')->on('sinh_viens')->onUpdate('cascade')->onDelete('set null');
        });
    }
    public function down(): void { Schema::dropIfExists('nhom_do_ans'); }
};