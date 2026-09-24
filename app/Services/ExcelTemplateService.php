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
                'title'    => 'MẪU DỮ LIỆU KHOA',
                'headers'  => ['MaKhoa', 'TenKhoa', 'TruongKhoa', 'MoTa'],
                'examples' => [
                    ['NN', 'Khoa Ngoại Ngữ', 'TS. Lê Thị Mai', 'Khoa Ngoại ngữ trường Đại học Công Thương'],
                    ['QTKD', 'Khoa Quản Trị Kinh Doanh', 'PGS.TS Hoàng Đình Tuấn', 'Khoa Quản trị kinh doanh trường Đại học Công Thương'],
                ],
                'note'     => 'Lưu ý: TenKhoa là bắt buộc và không trùng lặp. MaKhoa tùy chọn (để trống hệ thống tự sinh K01, K02...). TruongKhoa và MoTa là tùy chọn.'
            ],
            'bomon' => [
                'filename' => 'Template_BoMon.xlsx',
                'title'    => 'MẪU DỮ LIỆU BỘ MÔN',
                'headers'  => ['MaBoMon', 'TenBoMon', 'MaKhoa', 'TruongBoMon', 'MoTa'],
                'examples' => [
                    ['BMANH', 'Bộ Môn Tiếng Anh', 'NN', 'TS. Vũ Minh', 'Bộ môn tiếng Anh chuyên ngành & cơ bản'],
                    ['BMQTKD', 'Bộ Môn Quản Trị Tổng Hợp', 'QTKD', 'ThS. Nguyễn Lan', 'Bộ môn phụ trách các chuyên ngành QTKD'],
                ],
                'note'     => 'Lưu ý: TenBoMon và MaKhoa là bắt buộc. MaBoMon tùy chọn (để trống hệ thống tự sinh BM01, BM02...). MaKhoa có thể điền Mã Khoa (NN, QTKD, CNTT...) hoặc Tên Khoa. TruongBoMon và MoTa là tùy chọn.'
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
                'title'    => 'MẪU DỮ LIỆU NGÀNH HỌC',
                'headers'  => ['MaNganh', 'TenNganh', 'MaKhoa'],
                'examples' => [
                    ['7220201', 'Ngôn Ngữ Anh', 'NN'],
                    ['7340101', 'Quản Trị Kinh Doanh', 'QTKD'],
                ],
                'note'     => 'Lưu ý: TenNganh và MaKhoa là bắt buộc. MaNganh tùy chọn (để trống hệ thống tự sinh NG01, NG02...). MaKhoa có thể điền Mã Khoa hoặc Tên Khoa trực thuộc.'
            ],
            'lop' => [
                'filename' => 'Template_Lop.xlsx',
                'title'    => 'MẪU DỮ LIỆU LỚP HỌC',
                'headers'  => ['MaLop', 'TenLop', 'MaNganh', 'KhoaHoc', 'MaKhoa'],
                'examples' => [
                    ['15DHNNA01', '15DHNNA01', '7220201', '2024-2028', 'NN'],
                    ['15DHQTKD01', '15DHQTKD01', '7340101', '2024-2028', 'QTKD'],
                ],
                'note'     => 'Lưu ý: TenLop và MaNganh là bắt buộc. MaLop tùy chọn (để trống sẽ lấy theo TenLop). KhoaHoc tùy chọn (mặc định 2024-2028). MaKhoa tùy chọn (để trống hệ thống tự động suy ra từ Ngành).'
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
                'title'    => 'MẪU DỮ LIỆU HỌC KỲ',
                'headers'  => ['MaHocKy', 'TenHocKy', 'NamHoc', 'NgayBatDau', 'NgayKetThuc', 'TrangThai'],
                'examples' => [
                    ['HK2425_1', 'Học kỳ 1 (2024-2025)', '2024-2025', '2024-09-02', '2025-01-15', 'Đã kết thúc'],
                    ['HK2425_2', 'Học kỳ 2 (2024-2025)', '2024-2025', '2025-01-20', '2025-06-15', 'Đã kết thúc'],
                ],
                'note'     => 'Lưu ý: TenHocKy và NamHoc là bắt buộc (không được trùng cặp Học kỳ - Năm học). MaHocKy tùy chọn (để trống hệ thống tự sinh HK01, HK02...). NgayBatDau và NgayKetThuc định dạng YYYY-MM-DD. TrangThai tùy chọn (Đang diễn ra, Chưa bắt đầu, Đã kết thúc).'
            ],
            'giangvien' => [
                'filename' => 'Template_GiangVien.xlsx',
                'title'    => 'MẪU DỮ LIỆU GIẢNG VIÊN',
                'headers'  => ['MaGV', 'HoTen', 'Email', 'SoDienThoai', 'NgaySinh', 'GioiTinh', 'HocHam', 'HocVi', 'MaBoMon', 'TrangThai'],
                'examples' => [
                    ['GV91', 'Vũ Thị Thanh Mai', 'gv91_test@huit.edu.vn', '0981112233', '1982-04-12', 'Nữ', '', 'Tiến sĩ', 'BMANH', 'Đang công tác'],
                    ['GV92', 'Đỗ Quốc Bảo', 'gv92_test@huit.edu.vn', '0982223344', '1979-09-25', 'Nam', 'Phó Giáo sư', 'Tiến sĩ', 'BMQTKD', 'Đang công tác'],
                ],
                'note'     => 'Lưu ý: MaGV, HoTen và MaBoMon là bắt buộc. MaGV là duy nhất. Email nên dùng đuôi @huit.edu.vn. MaBoMon có thể điền Mã Bộ Môn (BMANH...) hoặc Tên Bộ Môn. Định dạng ngày YYYY-MM-DD. Hệ thống tự động tạo Tài khoản đăng nhập từ MaGV (Mật khẩu mặc định: 123456).'
            ],
            'sinhvien' => [
                'filename' => 'Template_SinhVien.xlsx',
                'title'    => 'MẪU DỮ LIỆU SINH VIÊN',
                'headers'  => ['MSSV', 'HoTen', 'Email', 'SoDienThoai', 'NgaySinh', 'GioiTinh', 'MaLop', 'KhoaHoc', 'SoTinChiTichLuy', 'DiemTichLuy', 'TrangThai'],
                'examples' => [
                    ['2001240001', 'Nguyễn Hoàng Long', '2001240001@st.huit.edu.vn', '0971234567', '2003-05-10', 'Nam', '15DHNNA01', '2024-2028', 95, 3.25, 'Đang học'],
                    ['2001240002', 'Trần Khánh Linh', '2001240002@st.huit.edu.vn', '0972345678', '2003-11-28', 'Nữ', '15DHQTKD01', '2024-2028', 90, 3.40, 'Đang học'],
                ],
                'note'     => 'Lưu ý: MSSV, HoTen và MaLop là bắt buộc. MSSV là duy nhất. Email dùng đuôi @st.huit.edu.vn. MaLop có thể điền Mã Lớp hoặc Tên Lớp. Hệ thống tự động liên kết Khoa và Ngành từ Lớp học và tạo Tài khoản sinh viên (Mật khẩu mặc định: 123456).'
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
            'khoa'                => 'khoa',
            'khoas'               => 'khoa',
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
        $headers = $cfg['headers'];
        $examples = $cfg['examples'];
        $title = $cfg['title'];
        $colCount = count($headers);
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DuLieuMau');
        $sheet->setShowGridLines(true);

        // Row 1: Title Header (Merged across all columns)
        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Row 2: Header Row
        $sheet->fromArray($headers, null, 'A2');
        $headerRange = "A2:{$lastColLetter}2";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'], // Deep Blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '93C5FD'],
                ],
            ],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(24);

        // Row 3+: Data Rows (Data Examples)
        $rowIdx = 3;
        foreach ($examples as $data) {
            $sheet->fromArray($data, null, "A{$rowIdx}");
            $bg = ($rowIdx % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle("A{$rowIdx}:{$lastColLetter}{$rowIdx}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($rowIdx)->setRowHeight(20);
            $rowIdx++;
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
