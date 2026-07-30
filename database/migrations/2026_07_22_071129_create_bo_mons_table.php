<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bo_mons', function (Blueprint $table) {
            $table->id('MaBoMon');
            $table->string('TenBoMon', 100)->unique();
            $table->text('MoTa')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('bo_mons'); }
};