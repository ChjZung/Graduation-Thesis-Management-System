<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lops', function (Blueprint $table) {
            $table->id('MaLop');
            $table->string('TenLop', 50)->unique();
            $table->unsignedBigInteger('MaNganh');
            $table->string('KhoaHoc', 20);
            $table->timestamps();
            
            $table->foreign('MaNganh')->references('MaNganh')->on('nganhs')->onUpdate('cascade')->onDelete('restrict');
        });
    }
    public function down(): void { Schema::dropIfExists('lops'); }
};