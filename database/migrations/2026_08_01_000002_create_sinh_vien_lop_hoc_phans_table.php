<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sinh_vien_lop_hoc_phans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('MaSV');
            $table->unsignedBigInteger('MaLopHP');
            $table->unsignedBigInteger('MaMon');
            $table->unsignedBigInteger('MaHocKy');
            $table->date('NgayDangKy')->nullable();
            $table->timestamps();

            $table->foreign('MaSV')->references('MaSV')->on('sinh_viens')->onDelete('cascade');
            $table->foreign('MaLopHP')->references('MaLopHP')->on('lop_hoc_phans')->onDelete('cascade');
            $table->foreign('MaMon')->references('MaMon')->on('mon_hocs')->onDelete('cascade');
            $table->foreign('MaHocKy')->references('MaHocKy')->on('hoc_kies')->onDelete('cascade');

            $table->unique(['MaSV', 'MaMon', 'MaHocKy'], 'unique_sv_mon_hk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinh_vien_lop_hoc_phans');
    }
};
