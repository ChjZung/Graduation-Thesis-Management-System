<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bảng Tổng Hợp Kết Quả Khóa Luận Theo Đợt — {{ $hocKy->TenHocKy ?? '' }}</title>
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
    <div class="title-doc">BẢNG TỔNG HỢP KẾT QUẢ KHÓA LUẬN TỐT NGHIỆP THEO ĐỢT</div>
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

    <!-- Danh Sách Theo Hội Đồng -->
    @php $sttGlobal = 1; @endphp
    @foreach($danhSachHoiDong as $hdData)
    <div style="margin-bottom: 4px; font-weight: bold; font-size: 8pt; color: #003366;">
        HỘI ĐỒNG: {{ mb_strtoupper($hdData['hoiDong']->TenHoiDong) }} 
        <span style="font-weight: normal; font-size: 7.2pt; color: #444;">
            (Ngành: {{ $hdData['hoiDong']->nganh?->TenNganh ?? 'Chung' }} &bull; Phòng: {{ $hdData['hoiDong']->DiaDiem ?? 'HUIT' }})
        </span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 22px;">STT</th>
                <th style="width: 45px;">Mã ĐT</th>
                <th style="width: 140px;">Tên Đề Tài</th>
                <th style="width: 45px;">Nhóm</th>
                <th style="width: 55px;">MSSV</th>
                <th style="width: 85px;">Họ Tên</th>
                <th style="width: 25px;" title="Điểm Hướng dẫn">GVHD</th>
                <th style="width: 25px;" title="Điểm Phản biện">GVPB</th>
                <th style="width: 25px;" title="Chủ tịch">CT</th>
                <th style="width: 25px;" title="Thư ký">TK</th>
                <th style="width: 25px;" title="Ủy viên">UV</th>
                <th style="width: 32px;">Điểm 10</th>
                <th style="width: 28px;">Điểm 4</th>
                <th style="width: 24px;">Chữ</th>
                <th style="width: 50px;">Xếp Loại</th>
                <th style="width: 45px;">Kết Quả</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hdData['deTais'] as $dtItem)
                @foreach($dtItem['sinhViens'] as $sv)
                <tr>
                    <td class="text-center">{{ $sttGlobal++ }}</td>
                    <td class="text-center text-bold">{{ $dtItem['deTai']->MaDeTai ?? $dtItem['hoSo']->MaHoSo }}</td>
                    <td style="line-height: 1.2;">{{ $dtItem['deTai']->TenDeTai ?? 'Chưa xác định' }}</td>
                    <td class="text-center">{{ $dtItem['nhom']->TenNhom ?? '' }}</td>
                    <td class="text-center">{{ $sv['maSV'] }}</td>
                    <td class="text-bold">{{ $sv['hoTen'] }}</td>
                    <td class="text-center">{{ $sv['diemGVHD'] !== null ? number_format($sv['diemGVHD'], 2) : '--' }}</td>
                    <td class="text-center">{{ $sv['diemGVPB'] !== null ? number_format($sv['diemGVPB'], 2) : '--' }}</td>
                    <td class="text-center">{{ $sv['diemCT'] !== null ? number_format($sv['diemCT'], 2) : '--' }}</td>
                    <td class="text-center">{{ $sv['diemTK'] !== null ? number_format($sv['diemTK'], 2) : '--' }}</td>
                    <td class="text-center">
                        @if(!empty($sv['diemUVs']))
                            @foreach($sv['diemUVs'] as $uvItem)
                                <span>{{ $uvItem['diem'] !== null ? number_format($uvItem['diem'], 2) : '--' }}</span>
                            @endforeach
                        @else
                            <span>--</span>
                        @endif
                    </td>
                    <td class="text-center text-bold">
                        {{ $sv['diemTongKet'] !== null ? number_format($sv['diemTongKet'], 2) : '--' }}
                    </td>
                    <td class="text-center">{{ $sv['diemHe4'] !== null ? number_format($sv['diemHe4'], 2) : '--' }}</td>
                    <td class="text-center text-bold">{{ $sv['diemChu'] ?: '--' }}</td>
                    <td class="text-center">{{ $sv['xepLoai'] }}</td>
                    <td class="text-center text-bold {{ $sv['ketQua'] === 'Đạt' ? 'text-success' : ($sv['ketQua'] === 'Không đạt' ? 'text-danger' : 'badge-pending') }}">
                        {{ $sv['ketQua'] }}
                    </td>
                </tr>
                @endforeach
            @endforeach
            <!-- Dòng tổng kết Hội đồng -->
            <tr class="bg-subtotal">
                <td colspan="4" class="text-left">
                    Tổng kết Hội đồng {{ $hdData['hoiDong']->TenHoiDong }}: {{ $hdData['soDeTai'] }} đề tài ({{ $hdData['soSV'] }} SV)
                </td>
                <td colspan="7" class="text-right">
                    Đạt: <strong>{{ $hdData['soDat'] }}</strong> &bull; 
                    K.Đạt: <strong>{{ $hdData['soKhongDat'] }}</strong> 
                    @if($hdData['soChuaXong'] > 0)
                    &bull; Chưa xong: <span class="badge-pending">{{ $hdData['soChuaXong'] }}</span>
                    @endif
                </td>
                <td class="text-center text-bold">{{ number_format($hdData['diemTB'], 2) }}</td>
                <td colspan="4" class="text-center">
                    Tỷ lệ Đạt: <strong>{{ number_format($hdData['tyLeDat'], 2) }}%</strong>
                </td>
            </tr>
        </tbody>
    </table>
    @endforeach

    <!-- Dòng Tổng Kết Chung Toàn Đợt -->
    <table class="data-table" style="margin-top: -6px;">
        <tr class="bg-total">
            <td colspan="5" class="text-bold" style="padding: 6px;">
                TỔNG KẾT TOÀN ĐỢT: {{ $tongSoHoiDong }} Hội đồng &bull; {{ $tongSoDeTai }} đề tài &bull; {{ $tongSoSV }} sinh viên
            </td>
            <td colspan="6" class="text-right text-bold">
                Tổng Đạt: {{ $tongDat }} | Không Đạt: {{ $tongKhongDat }}
                @if($tongChuaXong > 0)
                | Chưa xong: {{ $tongChuaXong }}
                @endif
            </td>
            <td class="text-center text-bold">{{ number_format($diemTB, 2) }}</td>
            <td colspan="4" class="text-center text-bold">
                Tỷ lệ Đạt: {{ number_format($tyLeDat, 2) }}%
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
