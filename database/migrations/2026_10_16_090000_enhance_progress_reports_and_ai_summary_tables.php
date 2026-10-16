<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Mở rộng bảng baocaotiendo (Báo cáo lớn giai đoạn)
        Schema::table('baocaotiendo', function (Blueprint $table) {
            if (!Schema::hasColumn('baocaotiendo', 'PhanTramHoanThanh')) {
                $table->integer('PhanTramHoanThanh')->default(0)->after('TrangThai');
            }
            if (!Schema::hasColumn('baocaotiendo', 'NgayXacNhan')) {
                $table->dateTime('NgayXacNhan')->nullable()->after('NhanXet');
            }
            if (!Schema::hasColumn('baocaotiendo', 'NguoiXacNhan')) {
                $table->string('NguoiXacNhan', 100)->nullable()->after('NgayXacNhan');
            }
        });

        // 2. Mở rộng bảng khoibaocaotiendo (Báo cáo nhỏ / Khối chi tiết công việc)
        Schema::table('khoibaocaotiendo', function (Blueprint $table) {
            if (!Schema::hasColumn('khoibaocaotiendo', 'KetQuaDatDuoc')) {
                $table->longText('KetQuaDatDuoc')->nullable()->after('NoiDungBaoCao');
            }
            if (!Schema::hasColumn('khoibaocaotiendo', 'KhoKhan')) {
                $table->longText('KhoKhan')->nullable()->after('KetQuaDatDuoc');
            }
            if (!Schema::hasColumn('khoibaocaotiendo', 'HuongGiaiQuyet')) {
                $table->longText('HuongGiaiQuyet')->nullable()->after('KhoKhan');
            }
            if (!Schema::hasColumn('khoibaocaotiendo', 'KeHoachTiepTheo')) {
                $table->longText('KeHoachTiepTheo')->nullable()->after('HuongGiaiQuyet');
            }
            if (!Schema::hasColumn('khoibaocaotiendo', 'PhanTramHoanThanh')) {
                $table->integer('PhanTramHoanThanh')->default(0)->after('TrangThai');
            }
            if (!Schema::hasColumn('khoibaocaotiendo', 'TomTatJson')) {
                $table->longText('TomTatJson')->nullable()->after('TomTatAI');
            }
        });

        // 3. Mở rộng bảng tomtatbaocao (Dữ liệu tóm tắt AI Map-Reduce)
        Schema::table('tomtatbaocao', function (Blueprint $table) {
            if (!Schema::hasColumn('tomtatbaocao', 'MaKhoi')) {
                $table->string('MaKhoi', 30)->nullable()->after('MaBaoCao');
            }
            if (!Schema::hasColumn('tomtatbaocao', 'TenFile')) {
                $table->string('TenFile', 255)->nullable()->after('MaKhoi');
            }
            if (!Schema::hasColumn('tomtatbaocao', 'DuongDanFile')) {
                $table->string('DuongDanFile', 500)->nullable()->after('TenFile');
            }
            if (!Schema::hasColumn('tomtatbaocao', 'SoTrang')) {
                $table->integer('SoTrang')->default(0)->after('DuongDanFile');
            }
            if (!Schema::hasColumn('tomtatbaocao', 'TomTatJson')) {
                $table->longText('TomTatJson')->nullable()->after('NoiDungAI');
            }
            if (!Schema::hasColumn('tomtatbaocao', 'TrangThaiXuLy')) {
                $table->string('TrangThaiXuLy', 50)->default('hoan_thanh')->after('TrangThai');
            }
            if (!Schema::hasColumn('tomtatbaocao', 'ThoiGianXuLy')) {
                $table->double('ThoiGianXuLy', 8, 2)->nullable()->after('TrangThaiXuLy');
            }
            if (!Schema::hasColumn('tomtatbaocao', 'LoiXuLy')) {
                $table->text('LoiXuLy')->nullable()->after('ThoiGianXuLy');
            }
        });
    }

    public function down(): void
    {
        Schema::table('baocaotiendo', function (Blueprint $table) {
            $table->dropColumn(['PhanTramHoanThanh', 'NgayXacNhan', 'NguoiXacNhan']);
        });

        Schema::table('khoibaocaotiendo', function (Blueprint $table) {
            $table->dropColumn([
                'KetQuaDatDuoc', 'KhoKhan', 'HuongGiaiQuyet', 'KeHoachTiepTheo', 
                'PhanTramHoanThanh', 'TomTatJson'
            ]);
        });

        Schema::table('tomtatbaocao', function (Blueprint $table) {
            $table->dropColumn([
                'MaKhoi', 'TenFile', 'DuongDanFile', 'SoTrang', 
                'TomTatJson', 'TrangThaiXuLy', 'ThoiGianXuLy', 'LoiXuLy'
            ]);
        });
    }
};
