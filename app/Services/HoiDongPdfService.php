<?php

namespace App\Services;

use App\Models\HoiDong;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class HoiDongPdfService
{
    public static function batchFilename(\Illuminate\Support\Collection $councils): string
    {
        $first = $councils->first();
        return 'Quyet_dinh_va_danh_sach_hoi_dong_'.\Illuminate\Support\Str::slug($first->MaKhoa.' '.$first->MaHocKy.' '.$first->DotBaoVe, '_')
            .'_'.now()->format('Ymd_His').'_'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6)).'.pdf';
    }

    public static function generateBatchPdf(\Illuminate\Support\Collection $councils)
    {
        if ($councils->isEmpty()) throw new \InvalidArgumentException('Không có hội đồng để xuất PDF.');
        $items = $councils->sortBy('TenHoiDong', SORT_NATURAL)->values()->map(function ($council) {
            $data = self::prepareData($council);
            // Batch decisions must not invent official metadata from generic fallbacks.
            $data['soQuyetDinh'] = trim((string) $council->SoQuyetDinh) ?: '[Chưa nhập số quyết định]';
            $data['nguoiKy'] = trim((string) $council->NguoiKy) ?: '[Chưa cấu hình người ký]';
            $data['chucVuNguoiKy'] = trim((string) $council->ChucVuNguoiKy) ?: '[Chưa cấu hình chức vụ]';
            $data['ngayBanHanh'] = $council->NgayBanHanh ? Carbon::parse($council->NgayBanHanh) : null;
            $data['ngayBaoVe'] = $council->NgayBaoVe ? Carbon::parse($council->NgayBaoVe) : ($council->ThoiGianBatDau ? Carbon::parse($council->ThoiGianBatDau) : null);
            $data['nganh'] = $council->nganh;
            return $data;
        });
        return Pdf::loadView('pdf.quyet_dinh_hoi_dong_toan_dot', ['items' => $items, 'first' => $items->first()])
            ->setPaper('a4', 'portrait')->setOptions(['defaultFont' => 'DejaVu Serif', 'isRemoteEnabled' => false]);
    }

    public static function saveBatchPdfToStorage(\Illuminate\Support\Collection $councils): string
    {
        $path = 'quyet_dinh_hoi_dong/'.self::batchFilename($councils);
        if (!Storage::disk('public')->put($path, self::generateBatchPdf($councils)->output())) throw new \RuntimeException('Không thể lưu PDF công bố hội đồng.');
        return 'storage/'.$path;
    }

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
        $thanhViens = $hoiDong->sorted_thanh_viens;

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
