<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yeu_cau_doi_mat_khaus', function (Blueprint $table) {
            $table->id('MaYeuCau');
            $table->string('TenDangNhap', 100);
            $table->string('Email', 150)->nullable();
            $table->string('Role', 50)->default('Sinh viên');
            $table->string('TrangThai', 50)->default('Chờ duyệt');
            $table->timestamp('NgayGui')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yeu_cau_doi_mat_khaus');
    }
};
