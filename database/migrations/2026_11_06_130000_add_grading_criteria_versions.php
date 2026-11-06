<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('HoSoBaoVe', function (Blueprint $table) {
            $table->unsignedInteger('PhienBanTieuChi')->default(0);
        });
        Schema::table('PhieuChamDiem', function (Blueprint $table) {
            $table->unsignedInteger('PhienBanTieuChi')->nullable();
            $table->longText('TieuChiSnapshot')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('PhieuChamDiem', fn (Blueprint $table) => $table->dropColumn(['PhienBanTieuChi', 'TieuChiSnapshot']));
        Schema::table('HoSoBaoVe', fn (Blueprint $table) => $table->dropColumn('PhienBanTieuChi'));
    }
};
