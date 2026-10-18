<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Quyết định thành lập Hội đồng chấm Khóa luận tốt nghiệp</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 20mm;
            size: a4 portrait;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            line-height: 1.35;
            color: #111;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-bold { font-weight: bold; }
        .text-italic { font-style: italic; }
        .text-uppercase { text-transform: uppercase; }

        table.header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        table.header-table td {
            vertical-align: top;
            padding: 0;
            border: none;
        }

        .line-divider {
            width: 140px;
            height: 1px;
            background-color: #000;
            margin: 4px auto 0 auto;
        }
        .line-divider-short {
            width: 70px;
            height: 1px;
            background-color: #000;
            margin: 4px auto 0 auto;
        }

        .title-doc {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            margin-top: 15px;
            margin-bottom: 4px;
        }
        .subtitle-doc {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
        }

        .recital {
            text-align: justify;
            font-style: italic;
            margin-bottom: 6px;
            text-indent: 25px;
        }

        .article-title {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 3px;
            text-indent: 25px;
            text-align: justify;
        }
        .article-content {
            text-align: justify;
            text-indent: 25px;
            margin-bottom: 6px;
        }

        table.sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        table.sign-table td {
            vertical-align: top;
            border: none;
            padding: 0;
        }

        .recipient-box {
            font-size: 9pt;
            line-height: 1.3;
        }
        .recipient-title {
            font-weight: bold;
            font-style: italic;
        }

        .page-break {
            page-break-after: always;
        }

        /* Tables for Appendix */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #222;
            padding: 6px 5px;
            font-size: 9.5pt;
        }
        table.data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }

        .appendix-header {
            text-align: center;
            margin-bottom: 12px;
        }
        .appendix-title {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .appendix-subtitle {
            font-size: 10pt;
            font-style: italic;
            margin-bottom: 10px;
        }
        .council-info-box {
            font-size: 10pt;
            margin-bottom: 8px;
            line-height: 1.4;
        }
    </style>
</head>
<body>

    <!-- ======================================================== -->
    <!-- PHẦN 1: QUYẾT ĐỊNH THÀNH LẬP HỘI ĐỒNG                      -->
    <!-- ======================================================== -->
    <table class="header-table">
        <tr>
            <td style="width: 45%; text-align: center;">
                <div style="font-size: 10pt;">BỘ CÔNG THƯƠNG</div>
                <div class="text-bold" style="font-size: 10pt; text-transform: uppercase;">TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG</div>
                <div class="text-bold" style="font-size: 10pt; text-transform: uppercase;">THÀNH PHỐ HỒ CHÍ MINH</div>
                <div class="line-divider-short"></div>
                <div style="font-size: 10pt; margin-top: 8px;">Số: {{ $soQuyetDinh }}</div>
            </td>
            <td style="width: 55%; text-align: center;">
                <div class="text-bold" style="font-size: 10.5pt;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
                <div class="text-bold" style="font-size: 11pt;">Độc lập - Tự do - Hạnh phúc</div>
                <div class="line-divider"></div>
                <div class="text-italic" style="font-size: 10pt; margin-top: 8px;">
                    TP. Hồ Chí Minh, ngày {{ $ngayBanHanh->format('d') }} tháng {{ $ngayBanHanh->format('m') }} năm {{ $ngayBanHanh->format('Y') }}
                </div>
            </td>
        </tr>
    </table>

    <div class="title-doc">QUYẾT ĐỊNH</div>
    <div class="subtitle-doc">
        Về việc thành lập Hội đồng chấm khóa luận tốt nghiệp ngành {{ $nganh->TenNganh ?? 'Công nghệ thông tin' }}<br>
        {{ $hocKy->TenHocKy ?? 'Học kỳ II' }} năm học {{ $hocKy->NamHoc ?? '2025 - 2026' }}
    </div>

    <div class="text-center text-bold" style="margin-bottom: 12px; font-size: 11pt;">
        {{ mb_strtoupper($chucVuNguoiKy) }} TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH
    </div>

    <div class="recital">
        Căn cứ Quyết định số 722/QĐ-TTg ngày 01 tháng 7 năm 2023 của Thủ tướng Chính phủ về việc đổi tên Trường Đại học Công nghiệp Thực phẩm Thành phố Hồ Chí Minh thành Trường Đại học Công Thương Thành phố Hồ Chí Minh;
    </div>
    <div class="recital">
        Căn cứ Quy chế đào tạo trình độ đại học ban hành theo Thông tư số 08/2021/TT-BGDĐT ngày 18 tháng 3 năm 2021 của Bộ trưởng Bộ Giáo dục và Đào tạo;
    </div>
    <div class="recital">
        Căn cứ Kế hoạch tổ chức khóa luận tốt nghiệp {{ $hocKy->TenHocKy ?? 'Học kỳ II' }} năm học {{ $hocKy->NamHoc ?? '2025 - 2026' }};
    </div>
    <div class="recital">
        Theo đề nghị của Trưởng khoa {{ $khoa->TenKhoa ?? 'Công nghệ Thông tin' }} và Trưởng phòng Đào tạo,
    </div>

    <div class="text-center text-bold" style="margin-top: 10px; margin-bottom: 10px; font-size: 11pt;">
        QUYẾT ĐỊNH:
    </div>

    <div class="article-title">
        Điều 1. Thành lập Hội đồng chấm khóa luận tốt nghiệp ngành {{ $nganh->TenNganh ?? 'Công nghệ thông tin' }} đợt bảo vệ {{ $hoiDong->DotBaoVe ?? 'Đợt 1' }} {{ $hocKy->TenHocKy ?? 'Học kỳ II' }} năm học {{ $hocKy->NamHoc ?? '2025 - 2026' }} gồm các Thầy (Cô) có tên trong danh sách kèm theo (Phụ lục 1).
    </div>

    <div class="article-title">
        Điều 2. Hội đồng có nhiệm vụ tổ chức chấm khóa luận tốt nghiệp cho các sinh viên đủ điều kiện theo danh sách đính kèm (Phụ lục 2).
    </div>
    <div class="article-content">
        - Thời gian tổ chức: <strong>{{ $ngayBaoVe->format('d/m/Y') }}</strong> (Bắt đầu từ: {{ $hoiDong->ThoiGianBatDau ? \Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('H:i') : '07:30' }} đến {{ $hoiDong->ThoiGianKetThuc ? \Carbon\Carbon::parse($hoiDong->ThoiGianKetThuc)->format('H:i') : '17:00' }}).<br>
        - Địa điểm: <strong>{{ $hoiDong->DiaDiem ?? 'Văn phòng Khoa' }}</strong>.<br>
        - Trách nhiệm: Các thành viên Hội đồng thực hiện đánh giá công tâm, chính xác theo Quy chế đào tạo và Quy định quản lý khóa luận tốt nghiệp hiện hành của Nhà trường.
    </div>

    <div class="article-title">
        Điều 3. Trưởng phòng Đào tạo, Trưởng khoa {{ $khoa->TenKhoa ?? 'Công nghệ Thông tin' }}, các đơn vị liên quan, các Thầy (Cô) có tên tại Điều 1 và các sinh viên có tên tại Điều 2 chịu trách nhiệm thi hành Quyết định này.
    </div>

    <!-- CHỮ KÝ -->
    <table class="sign-table">
        <tr>
            <td style="width: 50%;">
                <div class="recipient-box">
                    <span class="recipient-title">Nơi nhận:</span><br>
                    - Như Điều 3;<br>
                    - Ban Giám hiệu (để b/c);<br>
                    - Phòng Đào tạo;<br>
                    - Khoa {{ $khoa->TenKhoa ?? 'CNTT' }};<br>
                    - Lưu: VT, Khoa.
                </div>
            </td>
            <td style="width: 50%; text-align: center;">
                <div class="text-bold" style="font-size: 11pt; text-transform: uppercase;">
                    {{ $chucVuNguoiKy }}
                </div>
                <div class="text-italic" style="font-size: 9.5pt; margin-bottom: 55px;">
                    (Ký và ghi rõ họ tên)
                </div>
                <div class="text-bold" style="font-size: 11pt;">
                    {{ $nguoiKy }}
                </div>
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- ======================================================== -->
    <!-- PHẦN 2: PHỤ LỤC 1 - DANH SÁCH HỘI ĐỒNG CHẤM KHÓA LUẬN     -->
    <!-- ======================================================== -->
    <div class="appendix-header">
        <div class="text-bold" style="font-size: 11pt;">PHỤ LỤC 1</div>
        <div class="appendix-title">
            DANH SÁCH HỘI ĐỒNG CHẤM KHÓA LUẬN TỐT NGHIỆP<br>
            NGÀNH {{ mb_strtoupper($nganh->TenNganh ?? 'CÔNG NGHỆ THÔNG TIN') }}
        </div>
        <div class="appendix-subtitle">
            (Ban hành kèm theo Quyết định số: {{ $soQuyetDinh }} ngày {{ $ngayBanHanh->format('d/m/Y') }} của {{ $chucVuNguoiKy }})
        </div>
    </div>

    <div class="council-info-box">
        <strong>Tên hội đồng:</strong> {{ $hoiDong->TenHoiDong }} (Mã HĐ: {{ $hoiDong->MaHoiDong }})<br>
        <strong>Thời gian:</strong> {{ $ngayBaoVe->format('d/m/Y') }} ({{ $hoiDong->ThoiGianBatDau ? \Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('H:i') : '07:30' }} - {{ $hoiDong->ThoiGianKetThuc ? \Carbon\Carbon::parse($hoiDong->ThoiGianKetThuc)->format('H:i') : '17:00' }})<br>
        <strong>Địa điểm/Phòng:</strong> {{ $hoiDong->DiaDiem ?? 'Phòng Hội đồng' }}<br>
        @if($hoiDong->GhiChu)
        <strong>Ghi chú:</strong> {{ $hoiDong->GhiChu }}<br>
        @endif
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">STT</th>
                <th style="width: 28%;">Họ và tên</th>
                <th style="width: 16%;">Học hàm, Học vị</th>
                <th style="width: 25%;">Bộ môn / Đơn vị công tác</th>
                <th style="width: 25%;">Chức danh trong Hội đồng</th>
            </tr>
        </thead>
        <tbody>
            @forelse($thanhViens as $index => $tv)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-bold">{{ $tv->giangVien?->HoTen ?? 'Chưa rõ' }}</td>
                    <td class="text-center">{{ $tv->giangVien?->HocVi ?? ($tv->giangVien?->HocHam ?? 'Thạc sĩ') }}</td>
                    <td>{{ $tv->giangVien?->boMon?->TenBoMon ?? ($khoa->TenKhoa ?? 'Khoa CNTT') }}</td>
                    <td class="text-center text-bold" style="color: #00305a;">{{ $tv->VaiTro }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-italic">Chưa có thành viên nào được phân công vào hội đồng.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="font-size: 9.5pt; font-style: italic; margin-top: 5px;">
        * Tổng số thành viên Hội đồng: {{ $thanhViens->count() }} Giảng viên.
    </div>

    <table class="sign-table" style="margin-top: 20px;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; text-align: center;">
                <div class="text-italic" style="font-size: 9.5pt;">
                    TP. Hồ Chí Minh, ngày {{ $ngayBanHanh->format('d') }} tháng {{ $ngayBanHanh->format('m') }} năm {{ $ngayBanHanh->format('Y') }}
                </div>
                <div class="text-bold" style="font-size: 10.5pt; text-transform: uppercase; margin-top: 4px;">
                    TRƯỞNG KHOA
                </div>
                <div class="text-italic" style="font-size: 9pt; margin-bottom: 50px;">
                    (Ký và ghi rõ họ tên)
                </div>
                <div class="text-bold" style="font-size: 10.5pt;">
                    {{ $khoa->TruongKhoa ?? 'Trưởng khoa' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- ======================================================== -->
    <!-- PHẦN 3: PHỤ LỤC 2 - DANH SÁCH SINH VIÊN BẢO VỆ KHÓA LUẬN   -->
    <!-- ======================================================== -->
    <div class="appendix-header">
        <div class="text-bold" style="font-size: 11pt;">PHỤ LỤC 2</div>
        <div class="appendix-title">
            DANH SÁCH SINH VIÊN BẢO VỆ KHÓA LUẬN TỐT NGHIỆP<br>
            NGÀNH {{ mb_strtoupper($nganh->TenNganh ?? 'CÔNG NGHỆ THÔNG TIN') }}
        </div>
        <div class="appendix-subtitle">
            (Ban hành kèm theo Quyết định số: {{ $soQuyetDinh }} ngày {{ $ngayBanHanh->format('d/m/Y') }} của {{ $chucVuNguoiKy }})
        </div>
    </div>

    <div class="council-info-box">
        <strong>Hội đồng:</strong> {{ $hoiDong->TenHoiDong }} | <strong>Phòng:</strong> {{ $hoiDong->DiaDiem ?? 'Phòng HĐ' }} | <strong>Ngày bảo vệ:</strong> {{ $ngayBaoVe->format('d/m/Y') }}<br>
        <strong>Tổng số đề tài:</strong> {{ count($danhSachDeTai) }} đề tài — <strong>Tổng số sinh viên:</strong> {{ count($danhSachSinhVien) }} sinh viên
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">STT</th>
                <th style="width: 12%;">MSSV</th>
                <th style="width: 18%;">Họ và tên</th>
                <th style="width: 11%;">Lớp</th>
                <th style="width: 28%;">Tên đề tài khóa luận</th>
                <th style="width: 16%;">Cán bộ hướng dẫn</th>
                <th style="width: 10%;">Ghi chú</th>
            </tr>
        </thead>
        <tbody>
            @forelse($danhSachSinhVien as $sv)
                <tr>
                    <td class="text-center">{{ $sv['stt'] }}</td>
                    <td class="text-center text-bold">{{ $sv['mssv'] }}</td>
                    <td>{{ $sv['ho_ten'] }}</td>
                    <td class="text-center">{{ $sv['lop'] }}</td>
                    <td>{{ $sv['ten_de_tai'] }}</td>
                    <td>{{ $sv['gvhd'] }}</td>
                    <td class="text-center font-monospace" style="font-size: 8.5pt;">{{ $sv['ghi_chu'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-italic">Chưa có sinh viên/đề tài nào được gắn vào hội đồng này.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="sign-table" style="margin-top: 25px;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; text-align: center;">
                <div class="text-italic" style="font-size: 9.5pt;">
                    TP. Hồ Chí Minh, ngày {{ $ngayBanHanh->format('d') }} tháng {{ $ngayBanHanh->format('m') }} năm {{ $ngayBanHanh->format('Y') }}
                </div>
                <div class="text-bold" style="font-size: 10.5pt; text-transform: uppercase; margin-top: 4px;">
                    TRƯỞNG KHOA
                </div>
                <div class="text-italic" style="font-size: 9pt; margin-bottom: 50px;">
                    (Ký và ghi rõ họ tên)
                </div>
                <div class="text-bold" style="font-size: 10.5pt;">
                    {{ $khoa->TruongKhoa ?? 'Trưởng khoa' }}
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
