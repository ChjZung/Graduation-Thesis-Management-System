<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mon_hocs', function (Blueprint $table) {
            $table->id('MaMon');
            $table->string('TenMon', 100)->unique();
            $table->unsignedBigInteger('MaBoMon');
            $table->integer('SoTinChi');
            $table->timestamps();
            
            $table->foreign('MaBoMon')->references('MaBoMon')->on('bo_mons')->onUpdate('cascade')->onDelete('restrict');
        });
    }
    public function down(): void { Schema::dropIfExists('mon_hocs'); }
};