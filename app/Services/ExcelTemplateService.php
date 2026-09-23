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
            'khoa' => [
                'filename' => 'Template_Khoa.xlsx',
                'title'    => 'MẪU NHẬP LIỆU KHOA',
                'headers'  => ['TenKhoa', 'MoTa'],
                'examples' => [
                    ['Khoa Công Nghệ Thông Tin', 'Khoa Công Nghệ Thông Tin HUIT'],
                    ['Khoa Lý Luận Chính Trị', 'Khoa Lý Luận Chính Trị HUIT'],
                ],
                'note'     => 'Lưu ý: TenKhoa là bắt buộc và không được trùng lặp.'
            ],
            'bomon' => [
                'filename' => 'Template_BoMon.xlsx',
                'title'    => 'MẪU NHẬP LIỆU BỘ MÔN',
                'headers'  => ['TenBoMon', 'MoTa', 'MaKhoa'],
                'examples' => [
                    ['Công Nghệ Phần Mềm', 'Bộ môn phụ trách đào tạo phần mềm', 'Khoa Công Nghệ Thông Tin'],
                    ['Khoa Học Máy Tính', 'Bộ môn máy tính', 'K01'],
                ],
                'note'     => 'Lưu ý: TenBoMon là bắt buộc không trùng. MaKhoa có thể điền Mã Khoa (K01) hoặc Tên Khoa.'
            ],
            'sinhvien_lophocphan' => [
                'filename' => 'Template_SinhVien_LopHocPhan.xlsx',
                'title'    => 'MẪU NHẬP DANH SÁCH SINH VIÊN VÀO LỚP HỌC PHẦN',
                'headers'  => ['MSSV', 'HoTen', 'TenLop', 'Email', 'SoDienThoai', 'TenLopHP'],
                'examples' => [
                    ['2001230104', 'Nguyễn Văn B', '14DHTH05', '2001230104@st.huit.edu.vn', '0901234567', '14DHTH005'],
                    ['2001230105', 'Lê Thị C', '14DHTH11', '2001230105@st.huit.edu.vn', '0987654321', '14DHTH005'],
                ],
                'note'     => 'Lưu ý: Cột MSSV là bắt buộc. Cột TenLop (Lớp hành chính), Email (@st.huit.edu.vn) dùng để tự động tạo sinh viên nếu chưa có.'
            ],
            'nganh' => [
                'filename' => 'Template_Nganh.xlsx',
                'title'    => 'MẪU NHẬP LIỆU NGÀNH HỌC',
                'headers'  => ['TenNganh', 'MoTa', 'MaBoMon'],
                'examples' => [
                    ['Công Nghệ Thông Tin', 'Đào tạo kỹ sư CNTT toàn diện', 'Công Nghệ Phần Mềm'],
                    ['Khoa Học Máy Tính', 'Chuyên ngành khoa học máy tính', 'BM01'],
                ],
                'note'     => 'Lưu ý: TenNganh bắt buộc không trùng. MaBoMon có thể điền Mã Bộ Môn (BM01) hoặc Tên Bộ Môn.'
            ],
            'lop' => [
                'filename' => 'Template_Lop.xlsx',
                'title'    => 'MẪU NHẬP LIỆU LỚP HỌC HÀNH CHÍNH',
                'headers'  => ['TenLop', 'MaNganh', 'KhoaHoc'],
                'examples' => [
                    ['14DHTH05', 'Công Nghệ Thông Tin', '2023-2027'],
                    ['14DHTH11', 'NG01', '2023-2027'],
                ],
                'note'     => 'Lưu ý: TenLop bắt buộc không trùng. MaNganh có thể nhập Mã Ngành (NG01) hoặc Tên Ngành.'
            ],
            'monhoc' => [
                'filename' => 'Template_MonHoc.xlsx',
                'title'    => 'MẪU NHẬP LIỆU MÔN HỌC',
                'headers'  => ['TenMon', 'SoTinChi', 'MaBoMon'],
                'examples' => [
                    ['Đồ Án Chuyên Ngành Web', 3, 'Công Nghệ Phần Mềm'],
                    ['Lập Trình Di Động', 3, 'BM01'],
                ],
                'note'     => 'Lưu ý: TenMon bắt buộc. SoTinChi là số nguyên (>0). MaBoMon có thể nhập Mã Bộ Môn hoặc Tên Bộ Môn.'
            ],
            'hocky' => [
                'filename' => 'Template_HocKy.xlsx',
                'title'    => 'MẪU NHẬP LIỆU HỌC KỲ',
                'headers'  => ['TenHocKy', 'NamHoc', 'NgayBatDau', 'NgayKetThuc'],
                'examples' => [
                    ['Học kỳ 1', '2025-2026', '2025-09-01', '2026-01-15'],
                    ['Học kỳ 2', '2025-2026', '2026-01-20', '2026-06-01'],
                ],
                'note'     => 'Lưu ý: TenHocKy và NamHoc không trùng. Định dạng ngày YYYY-MM-DD (ví dụ 2025-09-01).'
            ],
            'giangvien' => [
                'filename' => 'Template_GiangVien.xlsx',
                'title'    => 'MẪU NHẬP LIỆU GIẢNG VIÊN',
                'headers'  => ['MaGV', 'HoTen', 'Email', 'SoDienThoai', 'HocVi', 'MaBoMon'],
                'examples' => [
                    ['GV001', 'Nguyễn Văn A', 'gv001@huit.edu.vn', '0901234567', 'Thạc sĩ', 'Công Nghệ Phần Mềm'],
                    ['GV002', 'Trần Thị B', 'gv002@huit.edu.vn', '0912345678', 'Tiến sĩ', 'BM01'],
                ],
                'note'     => 'Lưu ý: MaGV (Mã Giảng viên) là duy nhất và không trùng lặp. Email nên dùng đuôi @huit.edu.vn. MaBoMon có thể điền Mã Bộ Môn (BM01) hoặc Tên Bộ Môn. Hệ thống tự động xác định Mã tài khoản & Tên đăng nhập từ MaGV.'
            ],
            'sinhvien' => [
                'filename' => 'Template_SinhVien.xlsx',
                'title'    => 'MẪU NHẬP LIỆU SINH VIÊN',
                'headers'  => ['MSSV', 'HoTen', 'Email', 'SoDienThoai', 'MaLop'],
                'examples' => [
                    ['2001230104', 'Nguyễn Văn B', '2001230104@st.huit.edu.vn', '0987654321', '14DHTH05'],
                    ['2001230105', 'Lê Thị C', '2001230105@st.huit.edu.vn', '0976543210', '14DHTH11'],
                ],
                'note'     => 'Lưu ý: MSSV (Mã số sinh viên) là duy nhất và không trùng lặp. Email nên dùng đuôi @st.huit.edu.vn. MaLop có thể nhập Mã Lớp (L01) hoặc Tên Lớp (14DHTH05). Hệ thống tự động xác định Mã tài khoản & Tên đăng nhập từ MSSV.'
            ],
            'taikhoan' => [
                'filename' => 'Template_TaiKhoan.xlsx',
                'title'    => 'MẪU NHẬP LIỆU TÀI KHOẢN NGƯỜI DÙNG',
                'headers'  => ['TenDangNhap', 'HoTen', 'Email', 'MaVaiTro', 'MatKhau'],
                'examples' => [
                    ['giaovu_cntt', 'Nguyễn Thị Giáo Vụ', 'giaovu_cntt@huit.edu.vn', 'VT01', '123456'],
                    ['gv_hoangnam', 'ThS. Hoàng Nam', 'namh@huit.edu.vn', 'VT02', '123456'],
                ],
                'note'     => 'Lưu ý: TenDangNhap là bắt buộc và duy nhất. MaVaiTro: VT01 (Admin/Giáo vụ), VT02 (Giảng viên), VT03 (Sinh viên). Mật khẩu để trống sẽ lấy mặc định 123456.'
            ],
            'lophocphan' => [
                'filename' => 'Template_LopHocPhan.xlsx',
                'title'    => 'MẪU NHẬP LIỆU LỚP HỌC PHẦN (LỚP TÍN CHỈ)',
                'headers'  => ['TenLopHP', 'MaMon', 'MaHocKy', 'MaGV', 'SiSoToiDa', 'MoTa'],
                'examples' => [
                    ['21DTH01_WEB_N01', 'Đồ Án Chuyên Ngành Web', 'Học kỳ 1', 'GV001', 40, 'Lớp học phần tín chỉ đồ án web nhom 01'],
                    ['21DTH02_MOBILE_N01', 'Lập Trình Di Động', 'Học kỳ 1', 'GV002', 45, 'Lớp học phần di động'],
                ],
                'note'     => 'Lưu ý: TenLopHP bắt buộc không trùng. MaMon (ID hoặc Tên môn), MaHocKy (ID hoặc Tên học kỳ), MaGV (ID hoặc Mã GV/Tên GV).'
            ],
            'detai' => [
                'filename' => 'Template_DeTai.xlsx',
                'title'    => 'MẪU NHẬP LIỆU ĐỀ TÀI ĐỒ ÁN',
                'headers'  => ['TenDeTai', 'MaLopHP', 'MaMon', 'MaHocKy', 'MoTa', 'YeuCau', 'HanDangKy', 'HanBaoCao', 'HanNopSanPham'],
                'examples' => [
                    [
                        'Xây Dựng Hệ Thống Quản Lý Đồ Án Tín Chỉ',
                        '21DTH01_WEB_N01',
                        'Đồ Án Chuyên Ngành Web',
                        'Học kỳ 1',
                        'Đề tài nghiên cứu và lập trình ứng dụng Web Laravel',
                        'Sử dụng MySQL, Bootstrap 5 và Vite',
                        '2026-08-10',
                        '2026-08-25',
                        '2026-09-05'
                    ],
                ],
                'note'     => 'Lưu ý: TenDeTai bắt buộc. MaLopHP có thể nhập ID Lớp HP hoặc Tên Lớp HP.'
            ],
            'nhom' => [
                'filename' => 'Template_NhomDoAn.xlsx',
                'title'    => 'MẪU NHẬP LIỆU NHÓM ĐỒ ÁN',
                'headers'  => ['TenNhom', 'MaLopHP', 'MaMon', 'MaHocKy', 'MaSV_TruongNhom'],
                'examples' => [
                    ['Nhóm Đồ Án Web 01', '21DTH01_WEB_N01', 'Đồ Án Chuyên Ngành Web', 'Học kỳ 1', '22110001'],
                    ['Nhóm Mobile 02', 1, 1, 1, '22110002'],
                ],
                'note'     => 'Lưu ý: TenNhom không trùng trong môn/lớp. MaSV_TruongNhom có thể nhập ID hoặc MSSV.'
            ],
            'phancong' => [
                'filename' => 'Template_PhanCongHuongDan.xlsx',
                'title'    => 'MẪU NHẬP LIỆU PHÂN CÔNG HƯỚNG DẪN',
                'headers'  => ['LoaiPhanCong', 'MaGV', 'TenLop_Hoac_TenLopHP', 'MaHocKy', 'NgayPhanCong'],
                'examples' => [
                    ['Lớp Hành Chính', 'GV001', '21DTH01', 'Học kỳ 1', date('Y-m-d')],
                    ['Lớp Học Phần', 'GV002', '14DHTH005', 'Học kỳ 1', date('Y-m-d')],
                ],
                'note'     => 'Lưu ý: LoaiPhanCong ("Lớp Hành Chính" hoặc "Lớp Học Phần"). MaGV (ID, Mã GV hoặc Họ Tên).'
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
            'detais'              => 'detai',
            'bomons'              => 'bomon',
            'Nganh'              => 'nganh',
            'Lop'                => 'lop',
            'monhocs'             => 'monhoc',
            'giangviens'          => 'giangvien',
            'sinhviens'           => 'sinhvien',
            'hockys'              => 'hocky',
            'hockies'             => 'hocky',
            'phancongs'           => 'phancong',
            'Nhom'               => 'nhom',
            'lophocphan'          => 'lophocphan',
            'lophocphans'         => 'lophocphan',
            'lop-hoc-phan'        => 'lophocphan',
            'lop-hoc-phans'       => 'lophocphan',
            'sinhvien_lophocphan' => 'sinhvien_lophocphan',
            'sinhvien-lophocphan' => 'sinhvien_lophocphan',
        ];

        $key = $aliasMap[strtolower($key)] ?? strtolower($key);

        if (!isset($configs[$key])) {
            abort(404, "Không tìm thấy cấu hình file mẫu cho loại: {$key}");
        }

        $cfg = $configs[$key];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template');

        // Row 1: Instruction Note (Merged across headers to avoid wide column A)
        $headers = $cfg['headers'];
        $colCount = count($headers);
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);
        if ($colCount > 1) {
            $sheet->mergeCells("A1:{$lastColLetter}1");
        }

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
