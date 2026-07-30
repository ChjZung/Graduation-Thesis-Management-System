<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('de_tais', function (Blueprint $table) {
            $table->id('MaDeTai');
            $table->unsignedBigInteger('MaTK');
            $table->unsignedBigInteger('MaMon');
            $table->unsignedBigInteger('MaHocKy');
            $table->string('TenDeTai', 200);
            $table->text('MoTa')->nullable();
            $table->text('YeuCau')->nullable();
            $table->string('TrangThai', 50)->default('Đang mở đăng ký');
            $table->date('NgayTao');
            $table->timestamps();
            
            $table->foreign('MaTK')->references('MaTK')->on('tai_khoans')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('MaMon')->references('MaMon')->on('mon_hocs')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('MaHocKy')->references('MaHocKy')->on('hoc_kies')->onUpdate('cascade')->onDelete('restrict');
        });
    }
    public function down(): void { Schema::dropIfExists('de_tais'); }
};