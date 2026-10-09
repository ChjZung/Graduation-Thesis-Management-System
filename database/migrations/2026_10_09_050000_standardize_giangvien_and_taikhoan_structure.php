<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();

        // Tạm thời tắt foreign key checks để cập nhật an toàn
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // 1. Lấy danh sách toàn bộ giảng viên hiện có
            $giangViens = DB::table('GiangVien')->get();

            // Mảng ánh xạ mã cũ sang mã mới (ví dụ: GV001 -> GV00000001)
            $mapOldToNew = [];

            foreach ($giangViens as $gv) {
                $oldMa = $gv->MaGV;
                if (strlen($oldMa) === 10 && str_starts_with($oldMa, 'GV')) {
                    $newMa = $oldMa;
                } elseif (preg_match('/^GV(\d+)$/i', $oldMa, $matches)) {
                    $num = (int)$matches[1];
                    $newMa = 'GV' . str_pad($num, 8, '0', STR_PAD_LEFT);
                } else {
                    $clean = preg_replace('/[^\d]/', '', $oldMa);
                    $num = $clean !== '' ? (int)$clean : 1;
                    $newMa = 'GV' . str_pad($num, 8, '0', STR_PAD_LEFT);
                }

                $mapOldToNew[$oldMa] = $newMa;

                // Chuẩn hóa số điện thoại đúng 10 số (0...)
                $sdt = $gv->SoDienThoai;
                if ($sdt) {
                    $cleanPhone = preg_replace('/[^\d]/', '', $sdt);
                    if (strlen($cleanPhone) < 10) {
                        $cleanPhone = '0' . str_pad($cleanPhone, 9, '0', STR_PAD_LEFT);
                    } elseif (strlen($cleanPhone) > 10) {
                        $cleanPhone = substr($cleanPhone, 0, 10);
                    }
                    if (!str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '0' . substr($cleanPhone, 1);
                    }
                    $sdt = $cleanPhone;
                } else {
                    $sdt = '0903' . str_pad(substr($newMa, -6), 6, '0', STR_PAD_LEFT);
                }

                $newMaTK = 'TK_' . $newMa;

                // 2. Tạo/cập nhật tài khoản Giảng viên chuẩn (VT02 - 10 ký tự)
                DB::table('TaiKhoan')->updateOrInsert(
                    ['TenDangNhap' => $newMa],
                    [
                        'MaTK' => $newMaTK,
                        'MaVaiTro' => 'VT02', // Luôn là Giảng viên thường
                        'MatKhau' => Hash::make('123456'),
                        'TrangThai' => true,
                        'SoLanDangNhapSai' => 0,
                        'BatBuocDoiMatKhau' => false,
                        'TrangThaiMatKhau' => 'ACTIVE',
                        'LanDangNhapDau' => $now,
                        'updated_at' => $now,
                    ]
                );

                // Cập nhật bảng GiangVien
                DB::table('GiangVien')->where('MaGV', $oldMa)->update([
                    'MaGV' => $newMa,
                    'MaTK' => $newMaTK,
                    'SoDienThoai' => $sdt,
                    'updated_at' => $now,
                ]);

                // Cập nhật các bảng liên quan có MaGV
                $relatedTables = [
                    'chitietduyetdetai',
                    'chitieuhuongdan',
                    'detai',
                    'hoidong',
                    'lichgaphuongdan',
                    'phancongphanbien',
                    'phieuchamdiem',
                    'thanhvienhoidong',
                    'thongbao',
                ];

                foreach ($relatedTables as $tbl) {
                    if (DB::getSchemaBuilder()->hasTable($tbl) && DB::getSchemaBuilder()->hasColumn($tbl, 'MaGV')) {
                        DB::table($tbl)->where('MaGV', $oldMa)->update(['MaGV' => $newMa]);
                    }
                }
            }

            // 3. Xóa các tài khoản cũ bị ghi đè role hoặc mã cũ ngắn (như GV001-GV050, TBM_CNTT_BM_CNPM_GV001...)
            $oldUsernamesToDelete = [];
            foreach (array_keys($mapOldToNew) as $oldMa) {
                if ($oldMa !== $mapOldToNew[$oldMa]) {
                    $oldUsernamesToDelete[] = $oldMa;
                    $oldUsernamesToDelete[] = 'TK_' . $oldMa;
                }
            }
            $oldUsernamesToDelete[] = 'TBM_CNTT_BM_CNPM_GV001';
            $oldUsernamesToDelete[] = 'TK_CNTT_GV002';

            DB::table('TaiKhoan')
                ->whereIn('TenDangNhap', $oldUsernamesToDelete)
                ->orWhereIn('MaTK', $oldUsernamesToDelete)
                ->delete();

            // 4. Cấp TÀI KHOẢN CHỨC VỤ RIÊNG BIỆT cho Trưởng khoa (VT05)
            $khoas = DB::table('Khoa')->whereNotNull('TruongKhoa')->get();
            foreach ($khoas as $k) {
                $gvTK = DB::table('GiangVien')->where('HoTen', $k->TruongKhoa)->first();
                if ($gvTK) {
                    $uNameTK = "TK_{$k->MaKhoa}_{$gvTK->MaGV}";
                    $mTK = "TK_{$uNameTK}";
                    DB::table('TaiKhoan')->updateOrInsert(
                        ['TenDangNhap' => $uNameTK],
                        [
                            'MaTK' => $mTK,
                            'MaVaiTro' => 'VT05', // Trưởng khoa
                            'MatKhau' => Hash::make('123456'),
                            'TrangThai' => true,
                            'SoLanDangNhapSai' => 0,
                            'BatBuocDoiMatKhau' => false,
                            'TrangThaiMatKhau' => 'ACTIVE',
                            'LanDangNhapDau' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }
            }

            // 5. Cấp TÀI KHOẢN CHỨC VỤ RIÊNG BIỆT cho Trưởng bộ môn (VT04)
            $boMons = DB::table('BoMon')->whereNotNull('TruongBoMon')->get();
            foreach ($boMons as $bm) {
                $gvTBM = DB::table('GiangVien')->where('HoTen', $bm->TruongBoMon)->first();
                if ($gvTBM) {
                    $uNameTBM = "TBM_{$bm->MaBoMon}_{$gvTBM->MaGV}";
                    $mTBM = "TK_{$uNameTBM}";
                    DB::table('TaiKhoan')->updateOrInsert(
                        ['TenDangNhap' => $uNameTBM],
                        [
                            'MaTK' => $mTBM,
                            'MaVaiTro' => 'VT04', // Trưởng bộ môn
                            'MatKhau' => Hash::make('123456'),
                            'TrangThai' => true,
                            'SoLanDangNhapSai' => 0,
                            'BatBuocDoiMatKhau' => false,
                            'TrangThaiMatKhau' => 'ACTIVE',
                            'LanDangNhapDau' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }
            }

        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không rollback dữ liệu chuẩn hóa
    }
};
