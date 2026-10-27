<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\DanhSachSVDuDieuKien;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\PhieuDangKy;
use App\Models\DeTai;
use App\Models\TienDoNghiepVuKhoaLuan;
use App\Helpers\IdGenerator;

class CreateThreeStudentsAndAssignToDT35Seeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            $now = Carbon::now();
            $passwordHash = Hash::make('Huit@2026');

            // 1. Dữ liệu 3 sinh viên mới thuộc Bộ môn CNPM
            $students = [
                '2001210086' => [
                    'HoTen'       => 'Đặng Hoàng Long',
                    'GioiTinh'    => 'Nam',
                    'NgaySinh'    => '2003-03-12',
                    'SoDienThoai' => '0901234086',
                    'DiemTichLuy' => 3.55,
                    'TinChi'      => 128,
                    'VaiTro'      => 'Trưởng nhóm',
                ],
                '2001210087' => [
                    'HoTen'       => 'Vũ Hải Yến',
                    'GioiTinh'    => 'Nữ',
                    'NgaySinh'    => '2003-07-25',
                    'SoDienThoai' => '0901234087',
                    'DiemTichLuy' => 3.62,
                    'TinChi'      => 130,
                    'VaiTro'      => 'Thành viên',
                ],
                '2001210088' => [
                    'HoTen'       => 'Phạm Minh Đức',
                    'GioiTinh'    => 'Nam',
                    'NgaySinh'    => '2003-09-18',
                    'SoDienThoai' => '0901234088',
                    'DiemTichLuy' => 3.45,
                    'TinChi'      => 125,
                    'VaiTro'      => 'Thành viên',
                ],
            ];

            $deTai = DeTai::where('MaDeTai', 'DT35')->firstOrFail();
            $maHocKy = $deTai->MaHocKy ?? 'HK2627_1';
            $maHocPhan = $deTai->MaHocPhan ?? 'HP_KLCN_CNPM';

            // Tạo từng tài khoản, sinh viên và xét đủ điều kiện KLTN
            foreach ($students as $mssv => $info) {
                TaiKhoan::updateOrCreate(
                    ['TenDangNhap' => $mssv],
                    [
                        'MaTK'              => $mssv,
                        'MatKhau'           => $passwordHash,
                        'MaVaiTro'          => 'VT03', // Sinh viên
                        'TrangThai'         => true,
                        'SoLanDangNhapSai'  => 0,
                        'BatBuocDoiMatKhau' => false,
                        'TrangThaiMatKhau'  => 'ACTIVE',
                        'LanDangNhapDau'    => $now,
                        'NgayDoiMatKhau'    => $now,
                        'LanDangNhapCuoi'   => null,
                        'NgayKhoa'          => null,
                    ]
                );

                SinhVien::updateOrCreate(
                    ['MaSV' => $mssv],
                    [
                        'MaTK'            => $mssv,
                        'HoTen'           => $info['HoTen'],
                        'NgaySinh'        => $info['NgaySinh'],
                        'GioiTinh'        => $info['GioiTinh'],
                        'Email'           => "{$mssv}@st.huit.edu.vn",
                        'SoDienThoai'     => $info['SoDienThoai'],
                        'NgayNhapHoc'     => '2021-09-05',
                        'KhoaHoc'         => '2021-2025',
                        'SoTinChiTichLuy' => $info['TinChi'],
                        'DiemTichLuy'     => $info['DiemTichLuy'],
                        'TrangThai'       => 'Đang học',
                        'MaKhoa'          => 'CNTT',
                        'MaNganh'         => '7480103', // Kỹ thuật Phần mềm (Bộ môn CNPM)
                        'MaLop'           => '12DHKPM01',
                    ]
                );

                // Gán đủ điều kiện làm khóa luận
                foreach (['HK2627_1' => '261', 'HK2425_1' => '241'] as $hk => $short) {
                    DanhSachSVDuDieuKien::updateOrCreate(
                        [
                            'MaSV'    => $mssv,
                            'MaHocKy' => $hk,
                        ],
                        [
                            'MaDSDK'       => "DK_{$mssv}_{$short}",
                            'NgayXetDuyet' => $now->toDateString(),
                        ]
                    );
                }
            }

            // 2. Tạo hoặc lấy Nhóm
            // Kiểm tra xem đã có nhóm nào chứa sinh viên trưởng nhóm chưa
            $truongNhomMSSV = '2001210086';
            $existingThanhVien = ThanhVienNhom::where('MaSV', $truongNhomMSSV)->first();

            if ($existingThanhVien) {
                $maNhom = $existingThanhVien->MaNhom;
                $nhom = Nhom::where('MaNhom', $maNhom)->first();
            } else {
                $maNhom = IdGenerator::nextNhom();
                $nhom = Nhom::create([
                    'MaNhom'    => $maNhom,
                    'TenNhom'   => "Nhóm {$truongNhomMSSV}",
                    'TrangThai' => 'Đang hoạt động',
                    'NgayTao'   => '2026-08-10',
                    'MaHocKy'   => $maHocKy,
                    'MaHocPhan' => $maHocPhan,
                ]);
            }

            // 3. Thêm 3 sinh viên vào nhóm
            foreach ($students as $mssv => $info) {
                ThanhVienNhom::updateOrCreate(
                    [
                        'MaNhom' => $nhom->MaNhom,
                        'MaSV'   => $mssv,
                    ],
                    [
                        'VaiTro'      => $info['VaiTro'],
                        'TrangThai'   => 'da_tham_gia',
                        'NgayThamGia' => '2026-08-10',
                    ]
                );
            }

            // 4. Gán nhóm vào đề tài DT35 qua Phiếu đăng ký (Trạng thái: Đã duyệt)
            $pdk = PhieuDangKy::where('MaNhom', $nhom->MaNhom)
                ->where('MaDeTai', 'DT35')
                ->first();

            if (!$pdk) {
                $randomSuffix = strtoupper(Str::random(6));
                $pdk = PhieuDangKy::create([
                    'MaDangKy'   => "DK_{$randomSuffix}",
                    'NgayDangKy' => '2026-08-11',
                    'TrangThai'  => 'Đã duyệt',
                    'NgayDuyet'  => '2026-08-11',
                    'LyDoTuChoi' => null,
                    'MaNhom'     => $nhom->MaNhom,
                    'MaDeTai'    => 'DT35',
                ]);
            } else {
                $pdk->update([
                    'TrangThai' => 'Đã duyệt',
                    'NgayDuyet' => '2026-08-11',
                ]);
            }

            // 5. Khởi tạo Tiến độ nghiệp vụ cho nhóm thông qua ThesisProgressService
            try {
                $progressService = app(\App\Services\ThesisProgressService::class);
                $progressService->getOrCreateTienDoNghiepVu($nhom);
            } catch (\Throwable $e) {
                // Không chặn nếu service có cảnh báo
            }

            echo ">>> Đã tạo 3 sinh viên (2001210086, 2001210087, 2001210088), nhóm {$nhom->MaNhom} và gán thành công vào đề tài DT35!\n";
        });
    }
}
