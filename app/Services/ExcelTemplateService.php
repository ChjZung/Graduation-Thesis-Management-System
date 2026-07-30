<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelTemplateService
{
    /**
     * Get templates dictionary mapping keys to file details.
     */
    public function getTemplatesConfig(): array
    {
        return [
            'bomon' => [
                'filename' => 'Template_BoMon.xlsx',
                'title'    => 'MẪU NHẬP LIỆU BỘ MÔN',
                'headers'  => ['TenBoMon', 'MoTa'],
                'examples' => [
                    ['Công Nghệ Phần Mềm', 'Bộ môn phụ trách đào tạo công nghệ phần mềm'],
                    ['Hệ Thống Thông Tin', 'Bộ môn quản lý dữ liệu và hệ thống thông tin'],
                ],
                'note'     => 'Lưu ý: TenBoMon là bắt buộc và không được trùng lặp.'
            ],
            'nganh' => [
                'filename' => 'Template_Nganh.xlsx',
                'title'    => 'MẪU NHẬP LIỆU NGÀNH HỌC',
                'headers'  => ['TenNganh', 'MaBoMon'],
                'examples' => [
                    ['Công Nghệ Thông Tin', 1],
                    ['Hệ Thống Thông Tin', 1],
                ],
                'note'     => 'Lưu ý: TenNganh bắt buộc. MaBoMon phải là ID bộ môn đã tồn tại.'
            ],
            'lop' => [
                'filename' => 'Template_Lop.xlsx',
                'title'    => 'MẪU NHẬP LIỆU LỚP HỌC',
                'headers'  => ['TenLop', 'MaNganh', 'KhoaHoc'],
                'examples' => [
                    ['21DTH01', 1, '2021-2025'],
                    ['21DTH02', 1, '2021-2025'],
                ],
                'note'     => 'Lưu ý: TenLop bắt buộc không trùng. MaNganh phải là ID ngành đã tồn tại.'
            ],
            'monhoc' => [
                'filename' => 'Template_MonHoc.xlsx',
                'title'    => 'MẪU NHẬP LIỆU MÔN HỌC',
                'headers'  => ['TenMon', 'SoTinChi', 'MaBoMon'],
                'examples' => [
                    ['Đồ Án Chuyên Ngành Web', 3, 1],
                    ['Lập Trình Di Động', 3, 1],
                ],
                'note'     => 'Lưu ý: TenMon bắt buộc. SoTinChi là số nguyên (>0).'
            ],
            'hocky' => [
                'filename' => 'Template_HocKy.xlsx',
                'title'    => 'MẪU NHẬP LIỆU HỌC KỲ',
                'headers'  => ['TenHocKy', 'NamHoc', 'NgayBatDau', 'NgayKetThuc'],
                'examples' => [
                    ['Học kỳ 1', '2025-2026', '2025-09-01', '2026-01-15'],
                    ['Học kỳ 2', '2025-2026', '2026-01-20', '2026-06-01'],
                ],
                'note'     => 'Lưu ý: Định dạng ngày YYYY-MM-DD (ví dụ 2025-09-01).'
            ],
            'giangvien' => [
                'filename' => 'Template_GiangVien.xlsx',
                'title'    => 'MẪU NHẬP LIỆU GIẢNG VIÊN',
                'headers'  => ['TenDangNhap', 'HoTen', 'Email', 'SoDienThoai', 'HocVi', 'MaBoMon'],
                'examples' => [
                    ['gv001', 'Nguyễn Văn A', 'gv001@fe.edu.vn', '0901234567', 'Thạc sĩ', 1],
                    ['gv002', 'Trần Thị B', 'gv002@fe.edu.vn', '0912345678', 'Tiến sĩ', 1],
                ],
                'note'     => 'Lưu ý: TenDangNhap (mã GV) bắt buộc không trùng. Email & SDT đúng định dạng.'
            ],
            'sinhvien' => [
                'filename' => 'Template_SinhVien.xlsx',
                'title'    => 'MẪU NHẬP LIỆU SINH VIÊN',
                'headers'  => ['TenDangNhap', 'HoTen', 'Email', 'SoDienThoai', 'MaLop'],
                'examples' => [
                    ['sv001', 'Phạm Văn C', 'sv001@st.fe.edu.vn', '0987654321', 1],
                    ['sv002', 'Lê Thị D', 'sv002@st.fe.edu.vn', '0976543210', 1],
                ],
                'note'     => 'Lưu ý: TenDangNhap (MSSV) bắt buộc không trùng. MaLop phải là ID lớp đã tồn tại.'
            ],
            'detai' => [
                'filename' => 'Template_DeTai.xlsx',
                'title'    => 'MẪU NHẬP LIỆU ĐỀ TÀI ĐỒ ÁN',
                'headers'  => ['TenDeTai', 'MaMon', 'MaLop', 'MaHocKy', 'MoTa', 'YeuCau', 'HanDangKy', 'HanBaoCao', 'HanNopSanPham'],
                'examples' => [
                    [
                        'Xây Dựng Hệ Thống Quản Lý Đồ Án',
                        1, 1, 1,
                        'Đề tài nghiên cứu và lập trình ứng dụng Web Laravel',
                        'Sử dụng MySQL, Bootstrap 5 và Vite',
                        '2026-08-10',
                        '2026-08-25',
                        '2026-09-05'
                    ],
                ],
                'note'     => 'Lưu ý: TenDeTai bắt buộc. MaMon, MaLop, MaHocKy phải hợp lệ trong hệ thống.'
            ],
            'nhom' => [
                'filename' => 'Template_NhomDoAn.xlsx',
                'title'    => 'MẪU NHẬP LIỆU NHÓM ĐỒ ÁN',
                'headers'  => ['TenNhom', 'MaMon', 'MaLop', 'MaHocKy', 'MaSV_TruongNhom'],
                'examples' => [
                    ['Nhóm Đồ Án 01', 1, 1, 1, 1],
                    ['Nhóm Đồ Án 02', 1, 1, 1, 2],
                ],
                'note'     => 'Lưu ý: TenNhom không trùng trong môn/lớp. MaSV_TruongNhom là ID sinh viên trưởng nhóm.'
            ],
            'phancong' => [
                'filename' => 'Template_PhanCongHuongDan.xlsx',
                'title'    => 'MẪU NHẬP LIỆU PHÂN CÔNG HƯỚNG DẪN',
                'headers'  => ['MaGV', 'MaLop', 'MaHocKy', 'NgayPhanCong'],
                'examples' => [
                    [1, 1, 1, date('Y-m-d')],
                    [2, 2, 1, date('Y-m-d')],
                ],
                'note'     => 'Lưu ý: MaGV, MaLop, MaHocKy là ID hợp lệ tồn tại trong hệ thống.'
            ]
        ];
    }

    /**
     * Download styled .xlsx template file using StreamedResponse.
     */
    public function downloadTemplate(string $key): StreamedResponse
    {
        $configs = $this->getTemplatesConfig();
        
        // Normalize alias
        $aliasMap = [
            'detais'             => 'detai',
            'bomons'             => 'bomon',
            'nganhs'             => 'nganh',
            'lops'               => 'lop',
            'monhocs'            => 'monhoc',
            'giangviens'         => 'giangvien',
            'sinhviens'          => 'sinhvien',
            'hockys'             => 'hocky',
            'hockies'            => 'hocky',
            'phancongs'          => 'phancong',
            'nhoms'              => 'nhom',
        ];

        $key = $aliasMap[strtolower($key)] ?? strtolower($key);

        if (!isset($configs[$key])) {
            abort(404, "Không tìm thấy cấu hình file mẫu cho loại: {$key}");
        }

        $cfg = $configs[$key];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template');

        // Row 1: Instruction Note
        $sheet->setCellValue('A1', '💡 ' . $cfg['note']);
        $sheet->getStyle('A1')->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF555555'));

        // Row 3: Header Row
        $startRow = 3;
        $headers = $cfg['headers'];
        $colCount = count($headers);

        foreach ($headers as $index => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $cell = $colLetter . $startRow;
            $sheet->setCellValue($cell, $header);
            
            // Header Styling
            $sheet->getStyle($cell)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E40AF'], // Deep Blue
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }
        $sheet->getRowDimension($startRow)->setRowHeight(26);

        // Row 4+: Data Examples
        $dataRowIndex = $startRow + 1;
        foreach ($cfg['examples'] as $exampleRow) {
            foreach ($exampleRow as $index => $val) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
                $cell = $colLetter . $dataRowIndex;
                $sheet->setCellValue($cell, $val);

                // Data Styling
                $sheet->getStyle($cell)->applyFromArray([
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                ]);
            }
            $sheet->getRowDimension($dataRowIndex)->setRowHeight(22);
            $dataRowIndex++;
        }

        // Auto-fit Column Widths
        for ($i = 1; $i <= $colCount; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $filename = $cfg['filename'];

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
