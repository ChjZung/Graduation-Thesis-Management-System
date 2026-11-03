<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Báo Cáo Thống Kê Theo Ngành Đào Tạo — {{ $hocKy->TenHocKy ?? '' }}</title>
    <style>
        @page {
            margin: 12mm 10mm 14mm 10mm;
            size: a4 {{ $orientation ?? 'landscape' }};
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 7.8pt;
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
            margin-bottom: 10px;
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
            margin-bottom: 10px;
        }

        .watermark-banner {
            background-color: #fff3cd;
            border: 1px dashed #e0a800;
            color: #856404;
            font-size: 8pt;
            font-weight: bold;
            text-align: center;
            padding: 4px;
            margin-bottom: 8px;
            border-radius: 4px;
        }

        .summary-box {
            background-color: #f8fafc;
            border: 0.5pt solid #94a3b8;
            padding: 5px 8px;
            margin-bottom: 6px;
            border-radius: 3px;
            font-size: 7.2pt;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 7.2pt;
        }
        table.data-table thead {
            display: table-header-group;
        }
        table.data-table tr {
            page-break-inside: avoid;
        }
        table.data-table th, table.data-table td {
            border: 0.5pt solid #555555;
            padding: 3.5px 3px;
            vertical-align: middle;
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
            font-size: 7.8pt;
        }
        .badge-pending {
            color: #d97706;
            font-weight: bold;
            font-style: italic;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .signature-table td {
            border: none;
            padding: 0 10px;
            vertical-align: top;
            text-align: center;
            font-size: 8.2pt;
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
    <div class="title-doc">BÁO CÁO THỐNG KÊ KẾT QUẢ KHÓA LUẬN THEO NGÀNH ĐÀO TẠO</div>
    <div class="subtitle-doc">
        Học kỳ: <strong>{{ $hocKy->TenHocKy ?? 'N/A' }}</strong> &bull; 
        Thời điểm xuất: <strong>{{ $thoiDiemXuat->format('d/m/Y H:i') }}</strong>
    </div>

    @if(!empty($hasChuaCongBo))
    <div class="watermark-banner">
        *** BẢN DỰ THẢO - CHƯA CÔNG BỐ CHÍNH THỨC - DÙNG CHO CÔNG TÁC THẨM ĐỊNH NỘI BỘ ***
    </div>
    @endif

    @if(!empty($canhBaoChuaHoanTat))
    <div style="background-color: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 4px 8px; font-size: 7.5pt; margin-bottom: 8px; border-radius: 3px;">
        <strong>Lưu ý:</strong> {{ $canhBaoChuaHoanTat }} Các đề tài chưa đủ điểm được tách riêng và không tính vào tỷ lệ Đạt/Không đạt.
    </div>
    @endif

    <!-- Danh Sách Theo Ngành -->
    @php $sttGlobal = 1; @endphp
    @foreach($danhSachNganh as $ngData)
    <div style="margin-top: 10px; margin-bottom: 4px; font-weight: bold; font-size: 8.5pt; color: #003366;">
        NGÀNH: {{ mb_strtoupper($ngData['tenNganh']) }} 
        <span style="font-weight: normal; font-size: 7.5pt; color: #444;">
            (Mã: {{ $ngData['maNganh'] }} &bull; {{ $ngData['khoa'] }})
        </span>
    </div>

    <!-- Hộp Thống Kê Chỉ Số Của Ngành & Phân Bố Điểm A-F -->
    <div class="summary-box">
        <table style="width: 100%; border-collapse: collapse; font-size: 7.2pt;">
            <tr>
                <td style="width: 55%; vertical-align: top;">
                    <strong>Chỉ số tổng quan:</strong> 
                    Số HĐ: <strong>{{ $ngData['soHoiDong'] }}</strong> &bull; 
                    Số ĐT: <strong>{{ $ngData['soDeTai'] }}</strong> &bull; 
                    Số SV: <strong>{{ $ngData['soSV'] }}</strong> &bull; 
                    Điểm cao nhất: <strong class="text-success">{{ number_format($ngData['diemCaoNhat'], 2) }}</strong> &bull; 
                    Thấp nhất: <strong class="text-danger">{{ number_format($ngData['diemThapNhat'], 2) }}</strong> &bull; 
                    Điểm TB: <strong>{{ number_format($ngData['diemTB'], 2) }}</strong> &bull; 
                    Đạt: <strong class="text-success">{{ $ngData['soDat'] }} ({{ number_format($ngData['tyLeDat'], 2) }}%)</strong> &bull; 
                    K.Đạt: <strong class="text-danger">{{ $ngData['soKhongDat'] }}</strong>
                    @if($ngData['soChuaXong'] > 0)
                    &bull; Chưa có kết quả: <span class="badge-pending">{{ $ngData['soChuaXong'] }}</span>
                    @endif
                </td>
                <td style="width: 45%; vertical-align: top; text-align: right;">
                    <strong>Phân bố điểm chữ:</strong> 
                    A: <strong>{{ $ngData['phanBo']['A'] }}</strong> | 
                    B+: <strong>{{ $ngData['phanBo']['B+'] }}</strong> | 
                    B: <strong>{{ $ngData['phanBo']['B'] }}</strong> | 
                    C+: <strong>{{ $ngData['phanBo']['C+'] }}</strong> | 
                    C: <strong>{{ $ngData['phanBo']['C'] }}</strong> | 
                    D+: <strong>{{ $ngData['phanBo']['D+'] }}</strong> | 
                    D: <strong>{{ $ngData['phanBo']['D'] }}</strong> | 
                    F: <strong class="text-danger">{{ $ngData['phanBo']['F'] }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">STT</th>
                <th style="width: 110px;">Hội Đồng Bảo Vệ</th>
                <th style="width: 50px;">Mã ĐT</th>
                <th style="width: 170px;">Tên Đề Tài Khóa Luận</th>
                <th style="width: 45px;">Nhóm</th>
                <th style="width: 60px;">MSSV</th>
                <th style="width: 100px;">Họ và Tên SV</th>
                <th style="width: 35px;">Điểm 10</th>
                <th style="width: 32px;">Điểm 4</th>
                <th style="width: 26px;">Chữ</th>
                <th style="width: 55px;">Xếp Loại</th>
                <th style="width: 50px;">Kết Quả</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ngData['danhSachHoiDong'] as $hdData)
                @foreach($hdData['deTais'] as $dtItem)
                    @foreach($dtItem['sinhViens'] as $sv)
                    <tr>
                        <td class="text-center">{{ $sttGlobal++ }}</td>
                        <td>{{ $hdData['hoiDong']->TenHoiDong }}</td>
                        <td class="text-center text-bold">{{ $dtItem['deTai']->MaDeTai ?? $dtItem['hoSo']->MaHoSo }}</td>
                        <td style="line-height: 1.2;">{{ $dtItem['deTai']->TenDeTai ?? 'Chưa xác định' }}</td>
                        <td class="text-center">{{ $dtItem['nhom']->TenNhom ?? '' }}</td>
                        <td class="text-center">{{ $sv['maSV'] }}</td>
                        <td class="text-bold">{{ $sv['hoTen'] }}</td>
                        <td class="text-center text-bold">{{ $sv['diemTongKet'] !== null ? number_format($sv['diemTongKet'], 2) : '--' }}</td>
                        <td class="text-center">{{ $sv['diemHe4'] !== null ? number_format($sv['diemHe4'], 2) : '--' }}</td>
                        <td class="text-center text-bold">{{ $sv['diemChu'] ?: '--' }}</td>
                        <td class="text-center">{{ $sv['xepLoai'] }}</td>
                        <td class="text-center text-bold {{ $sv['ketQua'] === 'Đạt' ? 'text-success' : ($sv['ketQua'] === 'Không đạt' ? 'text-danger' : 'badge-pending') }}">
                            {{ $sv['ketQua'] }}
                        </td>
                    </tr>
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>
    @endforeach

    <!-- Dòng Tổng Kết Chung Toàn Bộ Ngành -->
    <table class="data-table" style="margin-top: 4px;">
        <tr class="bg-total">
            <td colspan="4" class="text-bold" style="padding: 6px;">
                TỔNG CỘNG TOÀN BỘ {{ $tongSoNganh }} NGÀNH: {{ $tongSoHoiDong }} Hội đồng &bull; {{ $tongSoDeTai }} đề tài &bull; {{ $tongSoSV }} sinh viên
            </td>
            <td colspan="4" class="text-right text-bold">
                Điểm TB: {{ number_format($diemTB, 2) }} | Cao nhất: {{ number_format($diemCaoNhat, 2) }} | Thấp nhất: {{ number_format($diemThapNhat, 2) }}
            </td>
            <td colspan="4" class="text-center text-bold">
                Tổng Đạt: {{ $tongDat }} ({{ number_format($tyLeDat, 2) }}%) | K.Đạt: {{ $tongKhongDat }}
                @if($tongChuaXong > 0)
                | Chưa xong: {{ $tongChuaXong }}
                @endif
            </td>
        </tr>
    </table>

    <!-- Khối Chữ Ký -->
    <table class="signature-table">
        <tr>
            <td style="width: 33%;">
                <div class="text-bold">NGƯỜI LẬP BẢNG</div>
                <div class="text-muted" style="font-size: 7.5pt; font-style: italic;">(Ký và ghi rõ họ tên)</div>
                <div style="height: 48px;"></div>
                <div class="text-bold">{{ Auth::user()->HoTen ?? 'Giáo vụ Khoa' }}</div>
            </td>
            <td style="width: 33%;">
                <div class="text-bold">TRƯỞNG BỘ MÔN</div>
                <div class="text-muted" style="font-size: 7.5pt; font-style: italic;">(Ký và ghi rõ họ tên)</div>
                <div style="height: 48px;"></div>
                <div class="text-muted">...................................................</div>
            </td>
            <td style="width: 34%;">
                <div style="font-size: 7.5pt; font-style: italic;">TP.HCM, ngày {{ $thoiDiemXuat->format('d') }} tháng {{ $thoiDiemXuat->format('m') }} năm {{ $thoiDiemXuat->format('Y') }}</div>
                <div class="text-bold">TRƯỞNG KHOA</div>
                <div class="text-muted" style="font-size: 7.5pt; font-style: italic;">(Ký và ghi rõ họ tên)</div>
                <div style="height: 48px;"></div>
                <div class="text-muted">...................................................</div>
            </td>
        </tr>
    </table>

    <!-- Script Đánh Số Trang DomPDF -->
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $size = 7.5;
            $w = $pdf->get_width();
            $h = $pdf->get_height();
            $pdf->page_text($w - 95, $h - 22, "Trang {PAGE_NUM} / {PAGE_COUNT}", $font, $size, array(0.4, 0.4, 0.4));
            $pdf->page_text(30, $h - 22, "HUIT - Khóa luận tốt nghiệp - {{ $thoiDiemXuat->format('d/m/Y H:i') }}", $font, $size, array(0.4, 0.4, 0.4));
        }
    </script>
</body>
</html>
