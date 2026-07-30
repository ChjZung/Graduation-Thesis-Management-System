<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tai_khoans', function (Blueprint $table) {
            $table->id('MaTK');
            $table->string('TenDangNhap', 50)->unique();
            $table->string('MatKhau', 255);
            $table->unsignedBigInteger('MaVaiTro');
            $table->boolean('TrangThai')->default(true);
            $table->timestamps();
            
            $table->foreign('MaVaiTro')->references('MaVaiTro')->on('vai_tros')->onUpdate('cascade')->onDelete('restrict');
        });
    }
    public function down(): void { Schema::dropIfExists('tai_khoans'); }
};