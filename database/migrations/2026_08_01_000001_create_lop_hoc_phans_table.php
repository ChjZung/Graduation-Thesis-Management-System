<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lop_hoc_phans', function (Blueprint $table) {
            $table->bigIncrements('MaLopHP');
            $table->string('TenLopHP', 100)->unique();
            $table->unsignedBigInteger('MaMon');
            $table->unsignedBigInteger('MaHocKy');
            $table->unsignedBigInteger('MaGV')->nullable();
            $table->integer('SiSoToiDa')->default(40);
            $table->string('TrangThai', 30)->default('Đang mở');
            $table->timestamps();

            $table->foreign('MaMon')->references('MaMon')->on('mon_hocs')->onDelete('cascade');
            $table->foreign('MaHocKy')->references('MaHocKy')->on('hoc_kies')->onDelete('cascade');
            $table->foreign('MaGV')->references('MaGV')->on('giang_viens')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lop_hoc_phans');
    }
};
