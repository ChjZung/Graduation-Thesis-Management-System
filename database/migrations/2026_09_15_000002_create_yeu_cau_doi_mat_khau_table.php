<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('YeuCauDoiMatKhau')) {
            Schema::create('YeuCauDoiMatKhau', function (Blueprint $table) {
                $table->id('MaYeuCau');
                $table->string('TenDangNhap', 50);
                $table->string('HoTen', 150);
                $table->string('Role', 50);
                $table->string('Email', 150)->nullable();
                $table->text('LyDo')->nullable();
                $table->string('TrangThai', 50)->default('Chờ duyệt'); // Chờ duyệt, Đã duyệt, Từ chối
                $table->dateTime('NgayGui')->useCurrent();
                $table->dateTime('NgayDuyet')->nullable();
                $table->string('NguoiDuyet', 50)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('YeuCauDoiMatKhau');
    }
};
