<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hoc_kies', function (Blueprint $table) {
            $table->id('MaHocKy');
            $table->string('TenHocKy', 50);
            $table->string('NamHoc', 20);
            $table->date('NgayBatDau');
            $table->date('NgayKetThuc');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('hoc_kies'); }
};