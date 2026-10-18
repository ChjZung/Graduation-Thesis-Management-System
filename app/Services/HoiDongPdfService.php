<?php

namespace App\Services;

use App\Models\HoiDong;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class HoiDongPdfService
{
    /**
     * Tạo dữ liệu chuẩn hóa cho mẫu PDF Quyết định & Phụ lục Hội đồng
     */
    public static function prepareData(HoiDong $hoiDong): array
    {
        $hoiDong->loadMissing([
            'khoa',
            'nganh',
            'hocKy',
            'thanhViens.giangVien.boMon',
            'hoSoBaoVes.nhom.thanhVienNhoms.sinhVien.lop',
            'hoSoBaoVes.nhom.deTai.giangVien.boMon',
            'hoSoBaoVes.phanCongPhanBien.giangVien.boMon',
        ]);

        $khoa = $hoiDong->khoa ?? $hoiDong->thanhViens->first()?->giangVien?->boMon?->khoa;
        if (!$khoa) {
            $khoa = \App\Models\Khoa::first();
        }

        $hocKy = $hoiDong->hocKy ?? \App\Models\HocKy::where('TrangThai', 'Đang diễn ra')->first() ?? \App\Models\HocKy::latest('created_at')->first();
        $nganh = $hoiDong->nganh ?? \App\Models\Nganh::first();

        // Chuẩn bị danh sách thành viên Hội đồng
        $thanhViens = $hoiDong->thanhViens->sortBy(function ($tv) {
            $order = [
                'Chủ tịch' => 1,
                'Ủy viên phản biện' => 2,
                'Phản biện' => 2,
                'Ủy viên thư ký' => 3,
                'Thư ký' => 3,
                'Ủy viên' => 4,
                'Thành viên' => 5,
            ];
            return $order[$tv->VaiTro] ?? 99;
        });

        // Chuẩn bị danh sách đề tài và sinh viên
        $danhSachDeTai = [];
        $danhSachSinhVien = [];
        $sttSV = 1;

        foreach ($hoiDong->hoSoBaoVes as $hoSo) {
            $deTai = $hoSo->nhom?->deTai;
            $nhom = $hoSo->nhom;
            $gvhd = $deTai?->giangVien?->HoTen ?? 'Chưa phân công';
            $gvpb = $hoSo->phanCongPhanBien?->giangVien?->HoTen ?? 'Theo phân công HĐ';

            $sinhViens = [];
            if ($nhom && $nhom->thanhVienNhoms) {
                foreach ($nhom->thanhVienNhoms as $tvn) {
                    if ($tvn->sinhVien) {
                        $sv = $tvn->sinhVien;
                        $sinhViens[] = [
                            'stt' => $sttSV++,
                            'mssv' => $sv->MaSV,
                            'ho_ten' => $sv->HoTen,
                            'lop' => $sv->lop?->TenLop ?? ($sv->MaLop ?? ''),
                            'ten_de_tai' => $deTai?->TenDeTai ?? 'Chưa có tên đề tài',
                            'gvhd' => $gvhd,
                            'gvpb' => $gvpb,
                            'ghi_chu' => $hoSo->GhiChu ?? '',
                        ];
                    }
                }
            }

            $danhSachDeTai[] = [
                'ma_de_tai' => $deTai?->MaDeTai ?? ($hoSo->MaDeTai ?? ''),
                'ten_de_tai' => $deTai?->TenDeTai ?? 'Chưa có tên đề tài',
                'ma_nhom' => $nhom?->MaNhom ?? '',
                'gvhd' => $gvhd,
                'gvpb' => $gvpb,
                'sinh_viens' => $sinhViens,
                'so_sv' => count($sinhViens),
            ];

            foreach ($sinhViens as $item) {
                $danhSachSinhVien[] = $item;
            }
        }

        $ngayBanHanh = $hoiDong->NgayBanHanh ? Carbon::parse($hoiDong->NgayBanHanh) : Carbon::now();
        $ngayBaoVe = $hoiDong->NgayBaoVe ? Carbon::parse($hoiDong->NgayBaoVe) : ($hoiDong->ThoiGianBatDau ? Carbon::parse($hoiDong->ThoiGianBatDau) : Carbon::now());

        return [
            'hoiDong' => $hoiDong,
            'khoa' => $khoa,
            'hocKy' => $hocKy,
            'nganh' => $nganh,
            'thanhViens' => $thanhViens,
            'danhSachDeTai' => $danhSachDeTai,
            'danhSachSinhVien' => $danhSachSinhVien,
            'ngayBanHanh' => $ngayBanHanh,
            'ngayBaoVe' => $ngayBaoVe,
            'soQuyetDinh' => $hoiDong->SoQuyetDinh ?: '.../QĐ-ĐHCNTP',
            'nguoiKy' => $hoiDong->NguoiKy ?: ($khoa->TruongKhoa ?: 'PGS.TS. Nguyễn Xuân Hoàn'),
            'chucVuNguoiKy' => $hoiDong->ChucVuNguoiKy ?: 'HIỆU TRƯỞNG',
        ];
    }

    /**
     * Xuất PDF instance
     */
    public static function generatePdf(HoiDong $hoiDong)
    {
        $data = self::prepareData($hoiDong);

        $pdf = Pdf::loadView('pdf.quyet_dinh_hoi_dong', $data);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        return $pdf;
    }

    /**
     * Tạo file PDF và lưu vào storage, trả về đường dẫn public/storage
     */
    public static function savePdfToStorage(HoiDong $hoiDong): string
    {
        $pdf = self::generatePdf($hoiDong);
        $filename = 'QD_HoiDong_' . $hoiDong->MaHoiDong . '_' . date('Ymd_His') . '.pdf';
        $relativeDir = 'quyet_dinh_hoi_dong';
        $fullDir = storage_path('app/public/' . $relativeDir);

        if (!file_exists($fullDir)) {
            mkdir($fullDir, 0755, true);
        }

        $filePath = $fullDir . '/' . $filename;
        $pdf->save($filePath);

        $storagePath = 'storage/' . $relativeDir . '/' . $filename;
        $hoiDong->update(['FileQuyetDinh' => $storagePath]);

        return $storagePath;
    }
}
