<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Báo Cáo Thống Kê Theo Ngành &amp; Hội Đồng — {{ $hocKy->TenHocKy ?? '' }}</title>
    <style>
        @page {
            margin: 15mm 12mm 15mm 12mm;
            size: a4 landscape;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            line-height: 1.35;
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
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin-top: 6px;
            margin-bottom: 3px;
            color: #003366;
        }
        .subtitle-doc {
            font-size: 9pt;
            text-align: center;
            margin-bottom: 14px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 7.5pt;
        }
        table.data-table th, table.data-table td {
            border: 0.5pt solid #555555;
            padding: 4px 5px;
            vertical-align: middle;
        }
        table.data-table th {
            background-color: #00529b;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
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
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signature-table td {
            border: none;
            padding: 0 15px;
            vertical-align: top;
            text-align: center;
            font-size: 8.5pt;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 45%; text-align: center;">
                <div style="font-size: 9pt;">BỘ CÔNG THƯƠNG</div>
                <div class="text-bold" style="font-size: 9pt;">TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP.HCM</div>
                <div class="line-divider"></div>
            </td>
            <td style="width: 55%; text-align: center;">
                <div class="text-bold" style="font-size: 9pt;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
                <div style="font-size: 8.5pt; font-style: italic;">Độc lập - Tự do - Hạnh phúc</div>
                <div class="line-divider-short"></div>
            </td>
        </tr>
    </table>

    <div class="title-doc">BÁO CÁO THỐNG KÊ KẾT QUẢ THEO NGÀNH &amp; HỘI ĐỒNG</div>
    <div class="subtitle-doc">
        Học kỳ: <strong>{{ $hocKy->TenHocKy ?? 'N/A' }}</strong> &bull; 
        Thời điểm xuất báo cáo: <strong>{{ $thoiDiemXuat->format('d/m/Y H:i') }}</strong>
    </div>

    <!-- Bảng Ma Trận Tổng Hợp Hội Đồng -->
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">STT</th>
                <th rowspan="2" style="width: 110px;">Ngành</th>
                <th rowspan="2" style="min-width: 140px;">Tên Hội Đồng</th>
                <th rowspan="2" style="width: 40px;">Số ĐT</th>
                <th rowspan="2" style="width: 40px;">Số SV</th>
                <th rowspan="2" style="width: 45px;">Cao Nhất</th>
                <th rowspan="2" style="width: 45px;">Thấp Nhất</th>
                <th rowspan="2" style="width: 45px;">Điểm TB</th>
                <th colspan="8">Phân Bố Thang Điểm (Số Lượng &amp; %)</th>
                <th rowspan="2" style="width: 40px;">Đạt</th>
                <th rowspan="2" style="width: 45px;">K.Đạt</th>
                <th rowspan="2" style="width: 45px;">Chưa Xong</th>
            </tr>
            <tr>
                <th style="width: 32px;">A</th>
                <th style="width: 32px;">B+</th>
                <th style="width: 32px;">B</th>
                <th style="width: 32px;">C+</th>
                <th style="width: 32px;">C</th>
                <th style="width: 32px;">D+</th>
                <th style="width: 32px;">D</th>
                <th style="width: 32px;">F</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bangHoiDong as $idx => $row)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ $row['tenNganh'] }}</td>
                <td class="text-bold">{{ $row['tenHoiDong'] }}</td>
                <td class="text-center">{{ $row['soDeTai'] }}</td>
                <td class="text-center">{{ $row['soSV'] }}</td>
                <td class="text-center text-bold">{{ number_format($row['diemCaoNhat'], 2) }}</td>
                <td class="text-center text-bold">{{ number_format($row['diemThapNhat'], 2) }}</td>
                <td class="text-center text-bold" style="background-color: #f8fafc;">{{ number_format($row['diemTB'], 2) }}</td>
                <td class="text-center">{{ $row['phanBo']['A']['count'] }}</td>
                <td class="text-center">{{ $row['phanBo']['B+']['count'] }}</td>
                <td class="text-center">{{ $row['phanBo']['B']['count'] }}</td>
                <td class="text-center">{{ $row['phanBo']['C+']['count'] }}</td>
                <td class="text-center">{{ $row['phanBo']['C']['count'] }}</td>
                <td class="text-center">{{ $row['phanBo']['D+']['count'] }}</td>
                <td class="text-center">{{ $row['phanBo']['D']['count'] }}</td>
                <td class="text-center {{ $row['phanBo']['F']['count'] > 0 ? 'text-danger text-bold' : '' }}">{{ $row['phanBo']['F']['count'] }}</td>
                <td class="text-center text-success text-bold">{{ $row['soDat'] }}</td>
                <td class="text-center text-danger text-bold">{{ $row['soKhongDat'] }}</td>
                <td class="text-center {{ $row['chuaHoanTat'] > 0 ? 'badge-pending' : 'text-muted' }}">{{ $row['chuaHoanTat'] }}</td>
            </tr>
            @endforeach

            <!-- Dòng tổng kết toàn bộ -->
            <tr class="bg-total">
                <td colspan="3" class="text-center text-bold" style="padding: 5px;">TỔNG CỘNG TOÀN BỘ HỘI ĐỒNG</td>
                <td class="text-center text-bold">{{ $tongSoDeTai }}</td>
                <td class="text-center text-bold">{{ $tongSoSV }}</td>
                <td class="text-center text-bold">{{ number_format($diemCaoNhat, 2) }}</td>
                <td class="text-center text-bold">{{ number_format($diemThapNhat, 2) }}</td>
                <td class="text-center text-bold">{{ number_format($diemTB, 2) }}</td>
                <td class="text-center text-bold">{{ $phanBo['A']['count'] }}</td>
                <td class="text-center text-bold">{{ $phanBo['B+']['count'] }}</td>
                <td class="text-center text-bold">{{ $phanBo['B']['count'] }}</td>
                <td class="text-center text-bold">{{ $phanBo['C+']['count'] }}</td>
                <td class="text-center text-bold">{{ $phanBo['C']['count'] }}</td>
                <td class="text-center text-bold">{{ $phanBo['D+']['count'] }}</td>
                <td class="text-center text-bold">{{ $phanBo['D']['count'] }}</td>
                <td class="text-center text-bold {{ $phanBo['F']['count'] > 0 ? 'text-danger' : '' }}">{{ $phanBo['F']['count'] }}</td>
                <td class="text-center text-bold text-success">{{ $tongDat }}</td>
                <td class="text-center text-bold text-danger">{{ $tongKhongDat }}</td>
                <td class="text-center text-bold {{ $tongChuaHoanTat > 0 ? 'badge-pending' : '' }}">{{ $tongChuaHoanTat }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                <div class="text-bold">NGƯỜI LẬP BÁO CÁO (GIÁO VỤ)</div>
                <div class="text-muted" style="font-size: 8pt; font-style: italic;">(Ký và ghi rõ họ tên)</div>
                <div style="height: 60px;"></div>
                <div class="text-bold">{{ Auth::user()->HoTen ?? 'Giáo vụ' }}</div>
            </td>
            <td style="width: 50%;">
                <div style="font-size: 8pt; font-style: italic;">TP.HCM, ngày {{ $thoiDiemXuat->format('d') }} tháng {{ $thoiDiemXuat->format('m') }} năm {{ $thoiDiemXuat->format('Y') }}</div>
                <div class="text-bold">TRƯỞNG KHOA</div>
                <div class="text-muted" style="font-size: 8pt; font-style: italic;">(Ký và ghi rõ họ tên)</div>
                <div style="height: 60px;"></div>
                <div class="text-muted">...................................................</div>
            </td>
        </tr>
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $size = 8;
            $pdf->page_text(730, 565, "Trang {PAGE_NUM} / {PAGE_COUNT}", $font, $size, array(0.4, 0.4, 0.4));
            $pdf->page_text(35, 565, "HUIT - Thống kê Ngành & Hội đồng - {{ $thoiDiemXuat->format('d/m/Y H:i') }}", $font, $size, array(0.4, 0.4, 0.4));
        }
    </script>
</body>
</html>