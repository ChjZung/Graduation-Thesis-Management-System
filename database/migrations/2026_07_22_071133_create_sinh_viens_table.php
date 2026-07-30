<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sinh_viens', function (Blueprint $table) {
            $table->id('MaSV');
            $table->unsignedBigInteger('MaTK')->unique();
            $table->unsignedBigInteger('MaLop');
            $table->string('HoTen', 100);
            $table->string('Email', 100)->unique();
            $table->string('SoDienThoai', 15)->unique();
            $table->timestamps();
            
            $table->foreign('MaTK')->references('MaTK')->on('tai_khoans')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('MaLop')->references('MaLop')->on('lops')->onUpdate('cascade')->onDelete('restrict');
        });
    }
    public function down(): void { Schema::dropIfExists('sinh_viens'); }
};