<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('dang_ky_de_tais', function (Blueprint $table) {
            $table->id('MaDangKy');
            $table->unsignedBigInteger('MaNhom')->unique();
            $table->unsignedBigInteger('MaDeTai');
            $table->date('NgayDangKy');
            $table->string('TrangThai', 50)->default('Chờ duyệt');
            $table->date('NgayDuyet')->nullable();
            $table->timestamps();
            
            $table->foreign('MaNhom')->references('MaNhom')->on('nhom_do_ans')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('MaDeTai')->references('MaDeTai')->on('de_tais')->onUpdate('cascade')->onDelete('restrict');
        });
    }
    public function down(): void { Schema::dropIfExists('dang_ky_de_tais'); }
};