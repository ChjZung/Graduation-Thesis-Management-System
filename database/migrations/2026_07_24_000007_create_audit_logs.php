<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('MaTK')->nullable();
                $table->string('HanhDong', 100);
                $table->string('DoiTuong', 100);
                $table->unsignedBigInteger('DoiTuongId')->nullable();
                $table->json('DuLieu')->nullable();
                $table->string('IPAddress', 45)->nullable();
                $table->timestamps();

                $table->foreign('MaTK')->references('MaTK')->on('tai_khoans')->onDelete('set null');
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('audit_logs');
    }
};
