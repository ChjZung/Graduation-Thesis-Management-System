<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('san_phams', function (Blueprint $table) {
            $table->id('MaSanPham');
            $table->unsignedBigInteger('MaNhom');
            $table->string('TenSanPham', 200);
            $table->string('LinkFile', 255)->nullable();
            $table->date('NgayNop');
            $table->timestamps();
            
            $table->foreign('MaNhom')->references('MaNhom')->on('nhom_do_ans')->onUpdate('cascade')->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('san_phams'); }
};