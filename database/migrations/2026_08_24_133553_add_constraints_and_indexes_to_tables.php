<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Performance indexes cho de_tais ──
        Schema::table('de_tais', function (Blueprint $table) {
            if (!$this->hasIndex('de_tais', 'idx_de_tais_trang_thai_hoc_ky')) {
                $table->index(['TrangThai', 'MaHocKy'], 'idx_de_tais_trang_thai_hoc_ky');
            }
        });

        // ── 2. Index cho bao_cao_tien_dos ──
        Schema::table('bao_cao_tien_dos', function (Blueprint $table) {
            if (!$this->hasIndex('bao_cao_tien_dos', 'idx_bc_nhom_lan')) {
                $table->index(['MaNhom', 'LanBaoCao'], 'idx_bc_nhom_lan');
            }
        });

        // ── 3. Performance index cho dang_ky_de_tais ──
        Schema::table('dang_ky_de_tais', function (Blueprint $table) {
            if (!$this->hasIndex('dang_ky_de_tais', 'idx_dkdt_detai_trang_thai')) {
                $table->index(['MaDeTai', 'TrangThai'], 'idx_dkdt_detai_trang_thai');
            }
        });

        // ── 4. Index cho thanh_vien_nhoms ──
        Schema::table('thanh_vien_nhoms', function (Blueprint $table) {
            if (!$this->hasIndex('thanh_vien_nhoms', 'idx_tv_sv_trang_thai')) {
                $table->index(['MaSV', 'TrangThai'], 'idx_tv_sv_trang_thai');
            }
        });

        // ── 5. Index cho nguoi_nhan_thong_baos ──
        Schema::table('nguoi_nhan_thong_baos', function (Blueprint $table) {
            if (!$this->hasIndex('nguoi_nhan_thong_baos', 'idx_nntb_matk_dadoc')) {
                $table->index(['MaTK', 'DaDoc'], 'idx_nntb_matk_dadoc');
            }
        });
    }

    public function down(): void
    {
        Schema::table('de_tais', function (Blueprint $table) {
            $table->dropIndex('idx_de_tais_trang_thai_hoc_ky');
        });
        Schema::table('bao_cao_tien_dos', function (Blueprint $table) {
            $table->dropIndex('idx_bc_nhom_lan');
        });
        Schema::table('dang_ky_de_tais', function (Blueprint $table) {
            $table->dropIndex('idx_dkdt_detai_trang_thai');
        });
        Schema::table('thanh_vien_nhoms', function (Blueprint $table) {
            $table->dropIndex('idx_tv_sv_trang_thai');
        });
        Schema::table('nguoi_nhan_thong_baos', function (Blueprint $table) {
            $table->dropIndex('idx_nntb_matk_dadoc');
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$indexName}'");
        return count($indexes) > 0;
    }
};
