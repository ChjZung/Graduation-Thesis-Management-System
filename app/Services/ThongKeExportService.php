<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ThongKeExportService
{
    /**
     * Xuất PDF báo cáo theo biểu mẫu được chọn (Mẫu 01 -> Mẫu 10)
     */
    public function xuatPdfBaoCao(string $mauBaoCao, array $data, array $config = [])
    {
        // Định hướng khổ giấy thông minh: mẫu tổng quan / phân bố ít cột dùng Portrait, mẫu nhiều cột dùng Landscape
        $defaultOrientation = match ($mauBaoCao) {
            'mau_07', 'mau_08' => 'portrait',
            default => 'landscape',
        };
        $chosenOrientation = !empty($config['kho_giay']) ? strtolower($config['kho_giay']) : $defaultOrientation;
        $orientation = in_array($chosenOrientation, ['portrait', 'landscape']) ? $chosenOrientation : $defaultOrientation;

        $data['orientation'] = $orientation;
        $data['config'] = array_merge([
            'show_kpi'         => true,
            'show_table'       => true,
            'show_detail'      => true,
            'show_conclusion'  => true,
            'show_signatures'  => true,
        ], $config);

        // Ánh xạ template tương ứng
        $viewMap = [
            'mau_01' => 'thongke_baocao.pdf.mau_01_dot',
            'mau_02' => 'thongke_baocao.pdf.mau_02_nganh',
            'mau_03' => 'thongke_baocao.pdf.mau_03_sv_dat',
            'mau_04' => 'thongke_baocao.pdf.mau_04_sv_chua_dat',
            'mau_05' => 'thongke_baocao.pdf.mau_05_chua_ket_qua',
            'mau_06' => 'thongke_baocao.pdf.mau_06_hoi_dong',
            'mau_07' => 'thongke_baocao.pdf.mau_07_phan_bo_diem',
            'mau_08' => 'thongke_baocao.pdf.mau_08_to_chuc',
            'mau_09' => 'thongke_baocao.pdf.mau_09_bo_mon',
            'mau_10' => 'thongke_baocao.pdf.mau_10_hoc_phan',
            'mau_11' => 'thongke_baocao.pdf.mau_11_diem_clo',
        ];

        $viewName = $viewMap[$mauBaoCao] ?? 'thongke_baocao.pdf.mau_01_dot';

        $pdf = Pdf::loadView($viewName, $data)
            ->setPaper('a4', $orientation)
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf;
    }

    /**
     * Backward-compatible methods
     */
    public function xuatPdfDot(array $data, string $orientation = 'landscape')
    {
        return $this->xuatPdfBaoCao('mau_01', $data, ['kho_giay' => $orientation]);
    }

    public function xuatPdfNganh(array $data, string $orientation = 'landscape')
    {
        return $this->xuatPdfBaoCao('mau_02', $data, ['kho_giay' => $orientation]);
    }

    public function xuatPdfTrangThai(array $data, string $orientation = 'landscape')
    {
        return $this->xuatPdfBaoCao('mau_03', $data, ['kho_giay' => $orientation]);
    }

    public function xuatPdfKhoaNganh(array $data)
    {
        return $this->xuatPdfBaoCao('mau_02', $data, ['kho_giay' => 'landscape']);
    }

    public function xuatPdfNganhHoiDong(array $data)
    {
        return $this->xuatPdfBaoCao('mau_06', $data, ['kho_giay' => 'landscape']);
    }
}
