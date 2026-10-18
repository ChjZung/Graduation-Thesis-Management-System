<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('HoSoBaoVe', function (Blueprint $table) {
            if (!Schema::hasColumn('HoSoBaoVe', 'LinkSourceCode')) {
                $table->string('LinkSourceCode', 500)->nullable()->after('GhiChu');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'GhiChuSourceCode')) {
                $table->text('GhiChuSourceCode')->nullable()->after('LinkSourceCode');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'LinkDeploy')) {
                $table->string('LinkDeploy', 500)->nullable()->after('GhiChuSourceCode');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'GhiChuDeploy')) {
                $table->text('GhiChuDeploy')->nullable()->after('LinkDeploy');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'LyDoKhongDeploy')) {
                $table->text('LyDoKhongDeploy')->nullable()->after('GhiChuDeploy');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'LienKetKhac')) {
                $table->json('LienKetKhac')->nullable()->after('LyDoKhongDeploy');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'PhienBanHienTai')) {
                $table->integer('PhienBanHienTai')->default(1)->after('LienKetKhac');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'NhanXetGVHD')) {
                $table->text('NhanXetGVHD')->nullable()->after('PhienBanHienTai');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'ThanhPhanCanSua')) {
                $table->json('ThanhPhanCanSua')->nullable()->after('NhanXetGVHD');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'HanNopLai')) {
                $table->dateTime('HanNopLai')->nullable()->after('ThanhPhanCanSua');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'NguoiDuyet')) {
                $table->string('NguoiDuyet', 50)->nullable()->after('HanNopLai');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'NgayNopGanNhat')) {
                $table->dateTime('NgayNopGanNhat')->nullable()->after('NguoiDuyet');
            }
        });

        if (!Schema::hasTable('phien_ban_ho_so_bao_ve')) {
            Schema::create('phien_ban_ho_so_bao_ve', function (Blueprint $table) {
                $table->id();
                $table->string('MaHoSo', 50)->index();
                $table->string('PhienBan', 20)->default('1.0');
                $table->string('TenFileBaoCao', 255)->nullable();
                $table->string('DuongDanBaoCao', 500)->nullable();
                $table->bigInteger('DungLuongBaoCao')->nullable();
                $table->string('LinkSourceCode', 500)->nullable();
                $table->text('GhiChuSourceCode')->nullable();
                $table->string('LinkDeploy', 500)->nullable();
                $table->text('GhiChuDeploy')->nullable();
                $table->text('LyDoKhongDeploy')->nullable();
                $table->json('LienKetKhac')->nullable();
                $table->decimal('TyLeTurnitin', 5, 2)->nullable();
                $table->string('TenFileTurnitin', 255)->nullable();
                $table->string('DuongDanTurnitin', 500)->nullable();
                $table->text('GhiChuSinhVien')->nullable();
                $table->dateTime('NgayNop');
                $table->string('NguoiNop', 50)->nullable();
                $table->string('TrangThai', 50)->default('Chờ GVHD kiểm tra');
                $table->text('NhanXetGVHD')->nullable();
                $table->json('ThanhPhanCanSua')->nullable();
                $table->dateTime('HanNopLai')->nullable();
                $table->dateTime('NgayDanhGia')->nullable();
                $table->string('NguoiDanhGia', 50)->nullable();
                $table->timestamps();

                $table->foreign('MaHoSo')
                    ->references('MaHoSo')
                    ->on('HoSoBaoVe')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phien_ban_ho_so_bao_ve');

        Schema::table('HoSoBaoVe', function (Blueprint $table) {
            $columns = [
                'LinkSourceCode',
                'GhiChuSourceCode',
                'LinkDeploy',
                'GhiChuDeploy',
                'LyDoKhongDeploy',
                'LienKetKhac',
                'PhienBanHienTai',
                'NhanXetGVHD',
                'ThanhPhanCanSua',
                'HanNopLai',
                'NguoiDuyet',
                'NgayNopGanNhat',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('HoSoBaoVe', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
