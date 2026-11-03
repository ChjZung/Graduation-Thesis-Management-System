<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title', 'Báo Cáo Khóa Luận Tốt Nghiệp') — {{ $hocKy->TenHocKy ?? '' }}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        @page {
            margin: 10mm 8mm 12mm 8mm;
            size: a4 {{ $orientation ?? 'landscape' }};
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 7.5pt;
            line-height: 1.25;
            color: #111111;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-bold { font-weight: bold; }
        .text-muted { color: #666666; }
        .text-danger { color: #dc3545; }
        .text-success { color: #198754; }

        table.header-table {
            width: 100% !important;
            table-layout: fixed;
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
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin-top: 2px;
            margin-bottom: 2px;
            color: #003366;
        }
        .subtitle-doc {
            font-size: 8pt;
            text-align: center;
            margin-bottom: 8px;
        }

        .watermark-banner {
            background-color: #fff3cd;
            border: 1px dashed #e0a800;
            color: #856404;
            font-size: 7.5pt;
            font-weight: bold;
            text-align: center;
            padding: 3px;
            margin-bottom: 6px;
            border-radius: 4px;
        }

        /* Thẻ KPI tóm tắt trong PDF */
        .kpi-table {
            width: 100% !important;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 8px;
            background-color: #f8fafc;
            border: 0.5pt solid #cbd5e1;
            font-size: 7pt;
        }
        .kpi-table td {
            padding: 4px 6px;
            border: 0.5pt solid #cbd5e1;
            word-wrap: break-word;
        }

        table.data-table {
            width: 100% !important;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 7pt;
            word-wrap: break-word;
        }
        table.data-table thead {
            display: table-header-group;
        }
        table.data-table tfoot {
            display: table-footer-group;
        }
        table.data-table tr {
            page-break-inside: avoid;
        }
        table.data-table th, table.data-table td {
            border: 0.5pt solid #555555;
            padding: 2.5px 2px;
            vertical-align: middle;
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        table.data-table th {
            background-color: #00529b;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }
        .bg-subtotal {
            background-color: #f0f4f8;
            font-weight: bold;
        }
        .bg-total {
            background-color: #dbeafe;
            font-weight: bold;
            font-size: 7.5pt;
        }
        .badge-pending {
            color: #d97706;
            font-weight: bold;
            font-style: italic;
        }

        .conclusion-box {
            background-color: #f8fafc;
            border: 0.5pt solid #cbd5e1;
            padding: 5px 8px;
            margin-top: 6px;
            margin-bottom: 10px;
            font-size: 7.2pt;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100% !important;
            table-layout: fixed;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .signature-table td {
            border: none;
            padding: 0 6px;
            vertical-align: top;
            text-align: center;
            font-size: 7.8pt;
        }
    </style>
</head>
<body>
    <!-- Header Quốc Hiệu & Trường -->
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

    <!-- Tiêu Đề Văn Bản -->
    <div class="title-doc">@yield('pdf_title')</div>
    <div class="subtitle-doc">
        @yield('pdf_subtitle')
        @if($config['show_export_date'] ?? true)
            &bull; Thời điểm xuất: <strong>{{ $thoiDiemXuat->format('d/m/Y H:i') }}</strong>
        @endif
    </div>

    @if(!empty($hasChuaCongBo))
    <div class="watermark-banner">
        *** BẢN DỰ THẢO - CHƯA CÔNG BỐ CHÍNH THỨC - DÙNG CHO CÔNG TÁC QUẢN LÝ NỘI BỘ ***
    </div>
    @endif

    <!-- Phần Tổng Quan (Nếu cấu hình bật) -->
    @if(($config['show_kpi'] ?? true) && !empty($kpi))
    <table class="kpi-table">
        <tr>
            <td style="width: 25%;">Tổng SV tham gia: <strong>{{ $kpi['tongSVThamGia'] }}</strong></td>
            <td style="width: 25%;">Tổng số đề tài: <strong>{{ $kpi['tongDeTai'] }}</strong></td>
            <td style="width: 25%;">Tổng số hội đồng: <strong>{{ $kpi['tongHoiDong'] }}</strong></td>
            <td style="width: 25%;">Đã hoàn thành chấm: <strong class="text-primary">{{ $kpi['tongSVHoanThanhCham'] }}</strong></td>
        </tr>
        <tr>
            <td>SV Đạt: <strong class="text-success">{{ $kpi['tongSVDat'] }}</strong></td>
            <td>SV Chưa đạt: <strong class="text-danger">{{ $kpi['tongSVChuaDat'] }}</strong></td>
            <td>Tỷ lệ Đạt: <strong class="text-success">{{ number_format($kpi['tyLeDat'], 2) }}%</strong></td>
            <td>Điểm TB: <strong class="text-primary">{{ number_format($kpi['diemTB'], 2) }}</strong> (Max: {{ number_format($kpi['diemCaoNhat'], 2) }}, Min: {{ number_format($kpi['diemThapNhat'], 2) }})</td>
        </tr>
    </table>
    @endif

    <!-- Nội dung bảng biểu & chi tiết của từng Mẫu -->
    @yield('pdf_content')

    <!-- Phần Kết luận / Nhận xét nghiệp vụ -->
    @if($config['show_conclusion'] ?? true)
    <div class="conclusion-box">
        <strong>Kết luận &amp; Nhận xét chung:</strong>
        <div style="margin-top: 3px; line-height: 1.4;">
            Đợt khóa luận đã tổ chức nghiệm thu và chấm điểm cho <strong>{{ $kpi['tongSVHoanThanhCham'] }}/{{ $kpi['tongSVThamGia'] }}</strong> sinh viên.
            Tỷ lệ sinh viên hoàn thành đạt chuẩn đầu ra khóa luận tốt nghiệp đạt <strong>{{ number_format($kpi['tyLeDat'], 2) }}%</strong>, điểm trung bình toàn đợt là <strong>{{ number_format($kpi['diemTB'], 2) }}</strong>.
            @if($kpi['tongSVChuaDat'] > 0)
                Có <strong>{{ $kpi['tongSVChuaDat'] }}</strong> sinh viên chưa đạt chuẩn, đề nghị bộ môn và khoa xem xét kế hoạch hỗ trợ và tổ chức bảo vệ bổ sung theo đúng quy chế đào tạo.
            @endif
        </div>
    </div>
    @endif

    <!-- Khối Chữ Ký Xác Nhận -->
    @if($config['show_signatures'] ?? true)
    <table class="signature-table">
        <tr>
            <td style="width: 33%;">
                <div class="text-bold">NGƯỜI LẬP BẢNG</div>
                <div class="text-muted" style="font-size: 7.5pt; font-style: italic;">(Ký và ghi rõ họ tên)</div>
                <div style="height: 48px;"></div>
                <div class="text-bold">{{ ($config['show_reporter'] ?? true) ? (Auth::user()->HoTen ?? 'Giáo vụ Khoa') : '...................................................' }}</div>
            </td>
            <td style="width: 33%;">
                <div class="text-bold">TRƯỞNG BỘ MÔN</div>
                <div class="text-muted" style="font-size: 7.5pt; font-style: italic;">(Ký và ghi rõ họ tên)</div>
                <div style="height: 48px;"></div>
                <div class="text-muted">...................................................</div>
            </td>
            <td style="width: 34%;">
                @if($config['show_export_date'] ?? true)
                    <div style="font-size: 7.5pt; font-style: italic;">TP.HCM, ngày {{ $thoiDiemXuat->format('d') }} tháng {{ $thoiDiemXuat->format('m') }} năm {{ $thoiDiemXuat->format('Y') }}</div>
                @else
                    <div style="font-size: 7.5pt; font-style: italic;">TP.HCM, ngày ..... tháng ..... năm 20...</div>
                @endif
                <div class="text-bold">TRƯỞNG KHOA</div>
                <div class="text-muted" style="font-size: 7.5pt; font-style: italic;">(Ký và ghi rõ họ tên)</div>
                <div style="height: 48px;"></div>
                <div class="text-muted">...................................................</div>
            </td>
        </tr>
    </table>
    @endif

    <!-- Script Đánh Số Trang DomPDF -->
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $size = 7.5;
            $w = $pdf->get_width();
            $h = $pdf->get_height();
            $pdf->page_text($w - 95, $h - 22, "Trang {PAGE_NUM} / {PAGE_COUNT}", $font, $size, array(0.4, 0.4, 0.4));
            $pdf->page_text(30, $h - 22, "HUIT - Hệ thống quản lý khóa luận tốt nghiệp - {{ $thoiDiemXuat->format('d/m/Y H:i') }}", $font, $size, array(0.4, 0.4, 0.4));
        }
    </script>
</body>
</html>
