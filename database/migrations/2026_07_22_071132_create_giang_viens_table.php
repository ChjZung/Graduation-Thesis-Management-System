<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('giang_viens', function (Blueprint $table) {
            $table->id('MaGV');
            $table->unsignedBigInteger('MaTK')->unique();
            $table->unsignedBigInteger('MaBoMon');
            $table->string('HoTen', 100);
            $table->string('Email', 100)->unique();
            $table->string('SoDienThoai', 15)->unique();
            $table->string('HocVi', 50);
            $table->timestamps();
            
            $table->foreign('MaTK')->references('MaTK')->on('tai_khoans')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('MaBoMon')->references('MaBoMon')->on('bo_mons')->onUpdate('cascade')->onDelete('restrict');
        });
    }
    public function down(): void { Schema::dropIfExists('giang_viens'); }
};