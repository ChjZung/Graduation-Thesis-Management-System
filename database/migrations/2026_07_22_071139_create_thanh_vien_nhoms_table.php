<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('thanh_vien_nhoms', function (Blueprint $table) {
            $table->unsignedBigInteger('MaNhom');
            $table->unsignedBigInteger('MaSV');
            $table->string('VaiTro', 50)->default('Thành viên');
            $table->timestamps();
            
            $table->primary(['MaNhom', 'MaSV']);
            
            $table->foreign('MaNhom')->references('MaNhom')->on('nhom_do_ans')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('MaSV')->references('MaSV')->on('sinh_viens')->onUpdate('cascade')->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('thanh_vien_nhoms'); }
};