<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('huong_dans', function (Blueprint $table) {
            $table->id('MaHuongDan');
            $table->unsignedBigInteger('MaNhom');
            $table->unsignedBigInteger('MaGV');
            $table->unsignedBigInteger('MaDeTai')->nullable();
            $table->date('NgayPhanCong');
            $table->string('TrangThai', 50)->default('Đang hướng dẫn');
            $table->timestamps();
            
            $table->foreign('MaNhom')->references('MaNhom')->on('nhom_do_ans')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('MaGV')->references('MaGV')->on('giang_viens')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('MaDeTai')->references('MaDeTai')->on('de_tais')->onUpdate('cascade')->onDelete('restrict');
        });
    }
    public function down(): void { Schema::dropIfExists('huong_dans'); }
};