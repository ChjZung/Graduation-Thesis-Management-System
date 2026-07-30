<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('cham_diems', function (Blueprint $table) {
            $table->id('MaCham');
            $table->unsignedBigInteger('MaNhom');
            $table->unsignedBigInteger('MaGV');
            $table->string('LoaiCham', 50);
            $table->decimal('DiemBaoCao', 4, 2);
            $table->decimal('DiemBaoVe', 4, 2);
            $table->decimal('DiemTong', 4, 2);
            $table->text('NhanXet')->nullable();
            $table->date('NgayCham');
            $table->timestamps();
            
            $table->foreign('MaNhom')->references('MaNhom')->on('nhom_do_ans')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('MaGV')->references('MaGV')->on('giang_viens')->onUpdate('cascade')->onDelete('restrict');
        });
    }
    public function down(): void { Schema::dropIfExists('cham_diems'); }
};