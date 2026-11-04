<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Báo Cáo Tổng Hợp &amp; Chi Tiết Khóa Luận Theo Khoa &amp; Ngành — {{ $hocKy->TenHocKy ?? '' }}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        @page {
            margin: 10mm 8mm 12mm 8mm;
            size: a4 landscape;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            line-height: 1.3;
            color: #111111;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-bold { font-weight: bold; }
        .text-muted { color: #666666; }
        .text-danger { color: #dc3545; }
        .text-success { color: #198754; }

        table.header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        table.header-table td {
            vertical-align: top;
            padding: 0;
            border: none;
        }

        .line-divider {
            width: 140px;
            height: 1px;
            background-color: #000000;
            margin: 3px auto 0 auto;
        }
        .line-divider-short {
            width: 80px;
            height: 1px;
            background-color: #000000;
            margin: 3px auto 0 auto;
        }

        .title-doc {
            font-size: 12.5pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin-top: 4px;
            margin-bottom: 2px;
            color: #003366;
        }
        .subtitle-doc {
            font-size: 8.5pt;
            text-align: center;
            margin-bottom: 12px;
        }

        .section-heading {
            font-size: 9.5pt;
            font-weight: bold;
            color: #003366;
            text-transform: uppercase;
            margin-top: 14px;
            margin-bottom: 6px;
            padding-bottom: 3px;
            border-bottom: 1.5pt solid #00529b;
        }

        .major-group-header {
            background-color: #f1f5f9;
            border: 0.5pt solid #94a3b8;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 8.5pt;
            color: #0f172a;
            margin-top: 10px;
            margin-bottom: 4px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 7.5pt;
            table-layout: fixed;
        }
        table.data-table th, table.data-table td {
            border: 0.5pt solid #555555;
            padding: 4px 5px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }
        table.data-table th {
            background-color: #00529b;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            font-size: 7.5pt;
        }
        .bg-total {
            background-color: #dbeafe;
            font-weight: bold;
            font-size: 8pt;
        }
        .badge-pending {
            color: #d97706;
            font-weight: bold;
            font-style: italic;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            page-break-inside: avoid;
        }
        .signature-table td {
            border: none;
            padding: 0 15px;
            vertical-align: top;
            text-align: center;
            font-size: 8.5pt;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Header Trường & Quốc hiệu -->
    <table class="header-table">
        <tr>
            <td style="width: 45%; text-align: center;">
                <div style="font-size: 8.5pt;">BỘ CÔNG THƯƠNG</div>
                <div class="text-bold" style="font-size: 8.5pt;">TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP.HCM</div>
                <div class="line-divider"></div>
            </td>
            <td style="width: 55%; text-align: center;">
                <div class="text-bold" style="font-size: 8.5pt;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
                <div style="font-size: 8pt; font-style: italic;">Độc lập - Tự do - Hạnh phúc</div>
                <div class="line-divider-short"></div>
            </td>
        </tr>
    </table>

    <div class="title-doc">BÁO CÁO TỔNG HỢP &amp; CHI TIẾT KẾT QUẢ KHÓA LUẬN THEO KHOA - NGÀNH</div>
    <div class="subtitle-doc">
        Học kỳ: <strong>{{ $hocKy->TenHocKy ?? 'N/A' }}</strong> &bull; 
        Thời điểm xuất báo cáo: <strong>{{ $thoiDiemXuat->format('d/m/Y H:i') }}</strong>
    </div>

    <!-- PHẦN 1: BẢNG TỔNG HỢP KHOA - NGÀNH -->
    <div class="section-heading">I. BẢNG TỔNG HỢP SỐ LIỆU THEO NGÀNH ĐÀO TẠO</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">STT</th>
                <th style="width: 140px;">Khoa</th>
                <th style="width: 180px;">Ngành Đào Tạo</th>
                <th style="width: 70px;">Tổng SV ĐK</th>
                <th style="width: 75px;">Đủ ĐK Bảo Vệ</th>
                <th style="width: 70px;">Số SV Đạt</th>
                <th style="width: 75px;">Số SV K.Đạt</th>
                <th style="width: 75px;">Chưa Có KQ</th>
                <th style="width: 75px;">Tỷ Lệ Đạt</th>
                <th style="width: 75px;">Điểm TB Ngành</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bangNganh as $idx => $row)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ $row['tenKhoa'] }}</td>
                <td class="text-bold">{{ $row['tenNganh'] }}</td>
                <td class="text-right">{{ number_format($row['tongDangKy']) }}</td>
                <td class="text-right">{{ number_format($row['duDieuKien']) }}</td>
                <td class="text-right text-success text-bold">{{ number_format($row['soDat']) }}</td>
                <td class="text-right text-danger text-bold">{{ number_format($row['soKhongDat']) }}</td>
                <td class="text-right {{ $row['chuaCoKetQua'] > 0 ? 'badge-pending' : 'text-muted' }}">
                    {{ number_format($row['chuaCoKetQua']) }}
                </td>
                <td class="text-right text-bold text-success">{{ number_format($row['tyLeDat'], 2) }}%</td>
                <td class="text-right text-bold text-primary">{{ number_format($row['diemTB'], 2) }}</td>
            </tr>
            @endforeach

            <!-- Dòng tổng cộng -->
            <tr class="bg-total">
                <td colspan="3" class="text-center text-bold" style="padding: 5px;">TỔNG CỘNG TOÀN BỘ CÁC NGÀNH</td>
                <td class="text-right text-bold">{{ number_format($tongDangKy) }}</td>
                <td class="text-right text-bold">{{ number_format($tongDuDieuKien) }}</td>
                <td class="text-right text-bold text-success">{{ number_format($tongDat) }}</td>
                <td class="text-right text-bold text-danger">{{ number_format($tongKhongDat) }}</td>
                <td class="text-right text-bold {{ $tongChuaXong > 0 ? 'badge-pending' : '' }}">
                    {{ number_format($tongChuaXong) }}
                </td>
                <td class="text-right text-bold text-success">{{ number_format($tyLeDat, 2) }}%</td>
                <td class="text-right text-bold text-primary">{{ number_format($diemTB, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- PHẦN 2: DANH SÁCH CHI TIẾT SINH VIÊN (GOM NHÓM THEO KHOA & NGÀNH) -->
    <div class="page-break"></div>
    <div class="section-heading">II. DANH SÁCH CHI TIẾT KẾT QUẢ KHÓA LUẬN CỦA TỪNG SINH VIÊN</div>

    @php $globalSTT = 1; @endphp
    @foreach($bangNganh as $row)
        @if(count($row['danhSachSV']) > 0)
        <div class="major-group-header">
            &bull; NGÀNH: {{ mb_strtoupper($row['tenNganh']) }} &mdash; KHOA: {{ mb_strtoupper($row['tenKhoa']) }}
            <span style="font-weight: normal; font-size: 7.5pt; color: #475569; margin-left: 10px;">
                (Tổng số: <strong>{{ count($row['danhSachSV']) }}</strong> SV | 
                Đạt: <strong class="text-success">{{ $row['soDat'] }}</strong> | 
                Không đạt: <strong class="text-danger">{{ $row['soKhongDat'] }}</strong> | 
                Chưa có KQ: <strong class="badge-pending">{{ $row['chuaCoKetQua'] }}</strong> | 
                Điểm TB: <strong>{{ number_format($row['diemTB'], 2) }}</strong>)
            </span>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 3.5%;">STT</th>
                    <th style="width: 8%;">MSSV</th>
                    <th style="width: 13%;">Họ và Tên</th>
                    <th style="width: 7%;">Lớp</th>
                    <th style="width: 5%;">Nhóm</th>
                    <th style="width: 22%;">Tên Đề Tài Khóa Luận</th>
                    <th style="width: 12%;">GVHD</th>
                    <th style="width: 11%;">Hội Đồng</th>
                    <th style="width: 4.5%;">Điểm 10</th>
                    <th style="width: 4%;">Điểm 4</th>
                    <th style="width: 3%;">Chữ</th>
                    <th style="width: 4%;">Xếp Loại</th>
                    <th style="width: 3% ;">Kết Quả</th>
                </tr>
            </thead>
            <tbody>
                @foreach($row['danhSachSV'] as $sv)
                <tr>
                    <td class="text-center">{{ $globalSTT++ }}</td>
                    <td class="text-center text-bold">{{ $sv['maSV'] }}</td>
                    <td class="text-bold">{{ $sv['hoTen'] }}</td>
                    <td class="text-center">{{ $sv['tenLop'] }}</td>
                    <td class="text-center">{{ $sv['tenNhom'] }}</td>
                    <td>{{ $sv['tenDeTai'] }}</td>
                    <td>{{ \App\Services\ThongKeService::formatLecturerName($sv['tenGVHD'] ?? $sv['gvhd'] ?? null) }}</td>
                    <td>{{ $sv['tenHoiDong'] }}</td>
                    <td class="text-right text-bold">{{ $sv['diemHe10'] !== null ? number_format($sv['diemHe10'], 2) : '--' }}</td>
                    <td class="text-right">{{ $sv['diemHe4'] !== null ? number_format($sv['diemHe4'], 2) : '--' }}</td>
                    <td class="text-center text-bold">{{ $sv['diemChu'] ?: '--' }}</td>
                    <td class="text-center">{{ $sv['xepLoai'] }}</td>
                    <td class="text-center text-bold">
                        @if($sv['ketQua'] === 'Đạt')
                            <span class="text-success">Đạt</span>
                        @elseif($sv['ketQua'] === 'Không đạt')
                            <span class="text-danger">Không đạt</span>
                        @else
                            <span class="badge-pending">Chưa có kết quả</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    @endforeach

    <!-- Chữ ký xác nhận -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                <div class="text-bold">NGƯỜI LẬP BÁO CÁO (GIÁO VỤ)</div>
                <div class="text-muted" style="font-size: 7.5pt; font-style: italic;">(Ký và ghi rõ họ tên)</div>
                <div style="height: 55px;"></div>
                <div class="text-bold">{{ Auth::user()->HoTen ?? 'Giáo vụ' }}</div>
            </td>
            <td style="width: 50%;">
                <div style="font-size: 7.5pt; font-style: italic;">TP.HCM, ngày {{ $thoiDiemXuat->format('d') }} tháng {{ $thoiDiemXuat->format('m') }} năm {{ $thoiDiemXuat->format('Y') }}</div>
                <div class="text-bold">BAN CHỦ NHIỆM KHOA / BAN GIÁM HIỆU</div>
                <div class="text-muted" style="font-size: 7.5pt; font-style: italic;">(Ký và ghi rõ họ tên)</div>
                <div style="height: 55px;"></div>
                <div class="text-muted">...................................................</div>
            </td>
        </tr>
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $size = 7.5;
            $pdf->page_text(730, 565, "Trang {PAGE_NUM} / {PAGE_COUNT}", $font, $size, array(0.4, 0.4, 0.4));
            $pdf->page_text(30, 565, "HUIT - Thống kê Khóa Luận Theo Khoa & Ngành - {{ $thoiDiemXuat->format('d/m/Y H:i') }}", $font, $size, array(0.4, 0.4, 0.4));
        }
    </script>
</body>
</html>
