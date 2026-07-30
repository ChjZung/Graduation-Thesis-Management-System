<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('bao_cao_tien_dos', function (Blueprint $table) {
            $table->id('MaBaoCao');
            $table->unsignedBigInteger('MaNhom');
            $table->integer('LanBaoCao');
            $table->text('NoiDung');
            $table->string('FileBaoCao', 255)->nullable();
            $table->string('TrangThai', 50)->default('Chờ duyệt');
            $table->date('NgayNop');
            $table->timestamps();
            
            $table->foreign('MaNhom')->references('MaNhom')->on('nhom_do_ans')->onUpdate('cascade')->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('bao_cao_tien_dos'); }
};