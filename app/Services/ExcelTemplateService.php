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
                    ['HK2425_1', 'Học kỳ 1', '2024-2025', '2024-09-02', '2025-01-15', 'Đã kết thúc'],
                    ['HK2425_2', 'Học kỳ 2', '2024-2025', '2025-01-20', '2025-06-15', 'Đã kết thúc'],
                ],
                'note'     => 'Lưu ý: TenHocKy và NamHoc là bắt buộc (không được trùng cặp Học kỳ - Năm học). MaHocKy tùy chọn (để trống hệ thống tự sinh HK01, HK02...). NgayBatDau và NgayKetThuc định dạng YYYY-MM-DD. TrangThai tùy chọn (Đang diễn ra, Chưa bắt đầu, Đã kết thúc).'
            ],
            'giangvien' => [
                'filename' => 'Template_GiangVien.xlsx',
                'title'    => 'MẪU DỮ LIỆU GIẢNG VIÊN THEO HỌC KỲ',
                'headers'  => ['MaGV', 'HoTen', 'Email', 'SoDienThoai', 'NgaySinh', 'GioiTinh', 'HocHam', 'HocVi', 'MaBoMon', 'TrangThai', 'MaHocKy', 'SoNhomToiDa'],
                'examples' => [
                    ['GV91', 'Vũ Thị Thanh Mai', 'gv91_test@huit.edu.vn', '0981112233', '1982-04-12', 'Nữ', '', 'Tiến sĩ', 'CNPM', 'Đang công tác', 'HK2627_2', 5],
                    ['GV92', 'Đỗ Quốc Bảo', 'gv92_test@huit.edu.vn', '0982223344', '1979-09-25', 'Nam', 'Phó Giáo sư', 'Tiến sĩ', 'KHMT', 'Đang công tác', 'HK2627_2', 5],
                ],
                'note'     => 'Lưu ý: MaGV, HoTen và MaBoMon là bắt buộc. MaGV là duy nhất. Hệ thống hỗ trợ import mới và import lại để cập nhật hồ sơ giảng viên. Cột MaHocKy và SoNhomToiDa là tùy chọn (nếu có sẽ tự động phân bổ chỉ tiêu hướng dẫn cho học kỳ đó). Tự động tạo Tài khoản Portal cho giảng viên mới (Hệ thống khởi tạo mật khẩu tạm thời an toàn, bắt buộc đổi mật khẩu khi đăng nhập lần đầu).'
            ],
            'giangvien_hocky' => [
                'filename' => 'Template_GiangVien_TheoHocKy.xlsx',
                'title'    => 'MẪU DỮ LIỆU GIẢNG VIÊN THEO HỌC KỲ',
                'headers'  => ['MaGV', 'HoTen', 'Email', 'SoDienThoai', 'NgaySinh', 'GioiTinh', 'HocHam', 'HocVi', 'MaBoMon', 'TrangThai', 'MaHocKy', 'SoNhomToiDa'],
                'examples' => [
                    ['GV91', 'Vũ Thị Thanh Mai', 'gv91_test@huit.edu.vn', '0981112233', '1982-04-12', 'Nữ', '', 'Tiến sĩ', 'CNPM', 'Đang công tác', 'HK2627_2', 5],
                    ['GV92', 'Đỗ Quốc Bảo', 'gv92_test@huit.edu.vn', '0982223344', '1979-09-25', 'Nam', 'Phó Giáo sư', 'Tiến sĩ', 'KHMT', 'Đang công tác', 'HK2627_2', 5],
                ],
                'note'     => 'Lưu ý: MaGV, HoTen và MaBoMon là bắt buộc. Cột MaHocKy và SoNhomToiDa là tùy chọn.'
            ],
            'sinhvien' => [
                'filename' => 'Template_SinhVien.xlsx',
                'title'    => 'MẪU DỮ LIỆU SINH VIÊN',
                'headers'  => ['MSSV', 'HoTen', 'Email', 'SoDienThoai', 'NgaySinh', 'GioiTinh', 'MaLop', 'KhoaHoc', 'SoTinChiTichLuy', 'DiemTichLuy', 'TrangThai'],
                'examples' => [
                    ['2001240001', 'Nguyễn Hoàng Long', '2001240001@st.huit.edu.vn', '0971234567', '2003-05-10', 'Nam', '15DHNNA01', '2024-2028', 95, 3.25, 'Đang học'],
                    ['2001240002', 'Trần Khánh Linh', '2001240002@st.huit.edu.vn', '0972345678', '2003-11-28', 'Nữ', '15DHQTKD01', '2024-2028', 90, 3.40, 'Đang học'],
                ],
                'note'     => 'Lưu ý: MSSV, HoTen và MaLop là bắt buộc. MSSV là duy nhất. Email dùng đuôi @st.huit.edu.vn. MaLop có thể điền Mã Lớp hoặc Tên Lớp. Hệ thống tự động liên kết Khoa và Ngành từ Lớp học và tạo Tài khoản sinh viên (Hệ thống khởi tạo mật khẩu tạm thời an toàn, bắt buộc đổi mật khẩu khi đăng nhập lần đầu).'
            ],
            'sinhvien_hocky' => [
                'filename' => 'Template_SinhVien_TheoHocKy.xlsx',
                'title'    => 'MẪU DANH SÁCH SINH VIÊN THAM GIA KHÓA LUẬN THEO HỌC KỲ',
                'headers'  => ['MSSV', 'HoTen', 'Email', 'SoDienThoai', 'NgaySinh', 'GioiTinh', 'MaLop', 'KhoaHoc', 'SoTinChiTichLuy', 'DiemTichLuy', 'TrangThai', 'GhiChu'],
                'examples' => [
                    ['2001210041', 'Châu Gia Hưng', '2001210041@st.huit.edu.vn', '0971000041', '2003-04-15', 'Nam', '12DHCNTT01', '2021-2025', 125, 3.25, 'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp'],
                    ['2001210042', 'Nguyễn Thị Hoa', '2001210042@st.huit.edu.vn', '0971000042', '2003-08-20', 'Nữ', '12DHCNTT01', '2021-2025', 105, 2.10, 'Chưa đủ điều kiện', 'Thiếu tín chỉ tích lũy (105/115)'],
                    ['2001210043', 'Trần Văn Nam', '2001210043@st.huit.edu.vn', '0971000043', '2003-11-05', 'Nam', '12DHCNTT02', '2021-2025', 118, 2.85, 'Đủ điều kiện', 'Đủ điều kiện làm khóa luận tốt nghiệp'],
                ],
                'note'     => 'Lưu ý: MSSV và HoTen là bắt buộc khi thêm mới sinh viên. MaLop có thể điền Mã Lớp hoặc Tên Lớp. Cột TrangThai có thể điền "Đủ điều kiện" hoặc "Chưa đủ điều kiện" (nếu để trống, hệ thống sẽ tự động rà soát chuẩn Tín chỉ >= 115 và ĐTB >= 2.0). Hệ thống tự tạo tài khoản Portal cho sinh viên mới (Hệ thống khởi tạo mật khẩu tạm thời an toàn, bắt buộc đổi mật khẩu khi đăng nhập lần đầu).'
            ],
            'taikhoan' => [
                'filename' => 'Template_TaiKhoan.xlsx',
                'title'    => 'MẪU NHẬP LIỆU TÀI KHOẢN NGƯỜI DÙNG',
                'headers'  => ['TenDangNhap', 'HoTen', 'Email', 'MaVaiTro', 'MatKhau'],
                'examples' => [
                    ['giaovu_cntt', 'Nguyễn Thị Giáo Vụ', 'giaovu_cntt@huit.edu.vn', 'VT01', ''],
                    ['gv_hoangnam', 'ThS. Hoàng Nam', 'namh@huit.edu.vn', 'VT02', ''],
                ],
                'note'     => 'Lưu ý: TenDangNhap là bắt buộc và duy nhất. MaVaiTro: VT01 (Admin/Giáo vụ), VT02 (Giảng viên), VT03 (Sinh viên). Cột MatKhau để trống hoặc hệ thống sẽ tự khởi tạo mật khẩu tạm thời ngẫu nhiên an toàn và yêu cầu đổi mật khẩu ở lần đăng nhập đầu tiên.'
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
                'filename' => '11_DeTai_KhoaLuan_Mau.xlsx',
                'title'    => 'MẪU NHẬP LIỆU ĐỀ TÀI KHÓA LUẬN TỐT NGHIỆP',
                'headers'  => ['MaDeTai', 'TenDeTai', 'MoTa', 'YeuCau', 'LinhVuc', 'MaHocPhan', 'TenHocPhan', 'MaGV', 'TenGV', 'MaHocKy', 'SoLuongSinhVienToiDa', 'TrangThai'],
                'examples' => [
                    [
                        'DT01',
                        'Xây dựng hệ thống quản lý và hỗ trợ học tập trực tuyến tích hợp AI Assistant cho trường Đại học Công Thương TP.HCM',
                        'Nghiên cứu thiết kế kiến trúc vi dịch vụ (Microservices), phát triển nền tảng học tập trực tuyến hỗ trợ tương tác thời gian thực, tích hợp trợ lý ảo AI giải đáp thắc mắc bài giảng.',
                        'Thành thạo lập trình Web Fullstack (NodeJS/Laravel, ReactJS/VueJS), kiến thức về RESTful API, Docker và hiểu biết cơ bản về tích hợp LLM API.',
                        'Công nghệ phần mềm',
                        'HP_KL_CNPM',
                        'Khóa luận tốt nghiệp - Công nghệ phần mềm',
                        'GV00000002',
                        'TS. Trần Anh Dũng',
                        'HK2627_1',
                        3,
                        'Đã công bố'
                    ],
                ],
                'note'     => 'Lưu ý: TenDeTai bắt buộc. MaGV hoặc TenGV bắt buộc. MaHocPhan có thể nhập mã hoặc tên học phần.'
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
            ],
            'hocphan' => [
                'filename' => 'Template_HocPhan.xlsx',
                'title'    => 'MẪU DỮ LIỆU HỌC PHẦN KHÓA LUẬN VÀ CHUYÊN NGÀNH',
                'headers'  => ['MaHocPhan', 'TenHocPhan', 'SoTinChi', 'MaKhoa', 'MaBoMon', 'LoaiHocPhan', 'MoTa'],
                'examples' => [
                    ['HP_KLTN', 'Khóa luận tốt nghiệp', 10, 'CNTT', '', 'Khóa luận', 'Học phần Khóa luận tốt nghiệp (KLTN) cử nhân hệ chính quy khoa CNTT.'],
                    ['HP_KLKS', 'Khóa luận kỹ sư', 10, 'CNTT', '', 'Khóa luận', 'Học phần Khóa luận kỹ sư chuyên sâu thực hành khoa CNTT.'],
                    ['HP_KLCN', 'Khóa luận cử nhân', 10, 'CNTT', '', 'Khóa luận', 'Học phần Khóa luận cử nhân khoa CNTT.'],
                    ['HP_KL_CNPM', 'Khóa luận tốt nghiệp - CNPM', 10, 'CNTT', 'CNPM', 'Khóa luận', 'Học phần Khóa luận tốt nghiệp chuyên ngành Công nghệ phần mềm.'],
                    ['HP_KL_HTTT', 'Khóa luận tốt nghiệp - HTTT', 10, 'CNTT', 'HTTT', 'Khóa luận', 'Học phần Khóa luận tốt nghiệp chuyên ngành Hệ thống thông tin.'],
                    ['HP_KL_ATTT', 'Khóa luận tốt nghiệp - ATTT', 10, 'CNTT', 'ATTT', 'Khóa luận', 'Học phần Khóa luận tốt nghiệp chuyên ngành An toàn thông tin.'],
                    ['HP_KL_MMT',  'Khóa luận tốt nghiệp - MMT',  10, 'CNTT', 'MMT',  'Khóa luận', 'Học phần Khóa luận tốt nghiệp chuyên ngành Mạng máy tính.'],
                    ['HP_KL_KHMT', 'Khóa luận tốt nghiệp - KHMT', 10, 'CNTT', 'KHMT', 'Khóa luận', 'Học phần Khóa luận tốt nghiệp chuyên ngành Khoa học máy tính.'],
                    ['HP_KL_KHDL', 'Khóa luận tốt nghiệp - KHDL', 10, 'CNTT', 'KHDL', 'Khóa luận', 'Học phần Khóa luận tốt nghiệp chuyên ngành Khoa học dữ liệu.'],
                ],
                'note'     => 'Lưu ý: TenHocPhan là bắt buộc. SoTinChi mặc định 10 cho Khóa luận (hoặc 3 cho môn chuyên ngành). MaKhoa mặc định CNTT. MaBoMon để trống nếu là học phần dùng chung toàn khoa hoặc điền mã bộ môn (CNPM, HTTT, ATTT, MMT, KHMT, KHDL). LoaiHocPhan điền "Khóa luận" hoặc "Chuyên ngành".'
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
            'sinhvien_hocky'      => 'sinhvien_hocky',
            'sinhvien-hocky'      => 'sinhvien_hocky',
            'giangvien_hocky'     => 'giangvien_hocky',
            'giangvien-hocky'     => 'giangvien_hocky',
            'hocphan'             => 'hocphan',
            'hocphans'            => 'hocphan',
            'hoc-phan'            => 'hocphan',
            'hoc-phans'           => 'hocphan',
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
