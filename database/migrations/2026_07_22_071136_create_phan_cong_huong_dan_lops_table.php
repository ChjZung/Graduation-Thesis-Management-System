<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('phan_cong_huong_dan_lops', function (Blueprint $table) {
            $table->id('MaPhanCong');
            $table->unsignedBigInteger('MaGV');
            $table->unsignedBigInteger('MaLop');
            $table->unsignedBigInteger('MaHocKy');
            $table->date('NgayPhanCong');
            $table->timestamps();
            
            $table->foreign('MaGV')->references('MaGV')->on('giang_viens')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('MaLop')->references('MaLop')->on('lops')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('MaHocKy')->references('MaHocKy')->on('hoc_kies')->onUpdate('cascade')->onDelete('restrict');
        });
    }
    public function down(): void { Schema::dropIfExists('phan_cong_huong_dan_lops'); }
};