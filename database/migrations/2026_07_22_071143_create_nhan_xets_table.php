<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('nhan_xets', function (Blueprint $table) {
            $table->id('MaNhanXet');
            $table->unsignedBigInteger('MaBaoCao');
            $table->unsignedBigInteger('MaGV');
            $table->text('NoiDung');
            $table->date('NgayNhanXet');
            $table->timestamps();
            
            $table->foreign('MaBaoCao')->references('MaBaoCao')->on('bao_cao_tien_dos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('MaGV')->references('MaGV')->on('giang_viens')->onUpdate('cascade')->onDelete('restrict');
        });
    }
    public function down(): void { Schema::dropIfExists('nhan_xets'); }
};