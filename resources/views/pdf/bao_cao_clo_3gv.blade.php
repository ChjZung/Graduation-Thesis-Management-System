<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>BÁO CÁO TỔNG HỢP CLO 3 GIẢNG VIÊN - {{ $hoiDong->MaHoiDong }}</title>
    <style>
        @page {
            margin: 10mm 12mm 12mm 12mm;
            size: a4 landscape;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            line-height: 1.3;
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
            margin-bottom: 6px;
        }
        table.header-table td {
            vertical-align: top;
            border: none;
            padding: 1px 3px;
        }

        .title-doc {
            font-size: 11.5pt;
            font-weight: bold;
            color: #002060;
            text-transform: uppercase;
            margin-top: 3px;
            margin-bottom: 2px;
            text-align: center;
        }
        .subtitle-doc {
            font-size: 8.5pt;
            text-align: center;
            margin-bottom: 6px;
            font-style: italic;
        }

        .meta-box {
            font-size: 8pt;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 8px;
        }
        table.data-table th, table.data-table td {
            border: 0.5pt solid #333;
            padding: 3px 2px;
            vertical-align: middle;
            font-size: 7.2pt;
        }
        table.data-table th {
            background-color: #D9E1F2;
            font-weight: bold;
            text-align: center;
        }
        table.data-table tr.summary-row td {
            background-color: #F2F2F2;
            font-weight: bold;
        }

        .badge-pass {
            color: #006100;
            font-weight: bold;
        }
        .badge-fail {
            color: #9C0006;
            font-weight: bold;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .footer-table td {
            border: none;
            vertical-align: top;
            text-align: center;
            padding: 2px;
            font-size: 8pt;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 50%;" class="text-center">
                <div class="text-uppercase" style="font-size: 8pt;">BỘ CÔNG THƯƠNG</div>
                <div class="text-bold text-uppercase" style="font-size: 8.5pt;">TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH</div>
                <div class="text-bold text-uppercase" style="font-size: 8.5pt; color: #002060;">KHOA CÔNG NGHỆ THÔNG TIN</div>
                <div style="border-bottom: 0.8pt solid #000; width: 140px; margin: 3px auto 0 auto;"></div>
            </td>
            <td style="width: 50%;" class="text-center">
                <div class="text-bold text-uppercase" style="font-size: 8.5pt;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
                <div class="text-bold" style="font-size: 8.5pt;">Độc lập - Tự do - Hạnh phúc</div>
                <div class="text-italic" style="font-size: 7.5pt; margin-top: 3px;">Mẫu 5B_CĐR &bull; Báo Cáo 3 Giảng Viên</div>
                <div style="border-bottom: 0.8pt solid #000; width: 140px; margin: 3px auto 0 auto;"></div>
            </td>
        </tr>
    </table>

    <!-- Document Title -->
    <div class="title-doc">
        BẢNG TỔNG HỢP SO SÁNH ĐIỂM CLO CỦA CẢ 3 GIẢNG VIÊN VÀ ĐIỂM TRUNG BÌNH HỘI ĐỒNG
    </div>
    <div class="subtitle-doc">
        Áp dụng Quy chế đào tạo theo hệ thống tín chỉ &bull; Học kỳ: {{ $hoiDong->hocKy?->TenHocKy ?? 'Học kỳ 1' }} ({{ $hoiDong->hocKy?->NamHoc ?? '2026-2027' }})
    </div>

    <!-- Metadata Info -->
    <div class="meta-box">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 50%;"><strong>Hội đồng:</strong> {{ $hoiDong->TenHoiDong }} ({{ $hoiDong->MaHoiDong }})</td>
                <td style="width: 50%;"><strong>Học phần:</strong> {{ $loaiKhoaLuan === 'KLKS' ? 'Khóa luận tốt nghiệp Kỹ sư' : 'Khóa luận tốt nghiệp Cử nhân' }}</td>
            </tr>
            <tr>
                <td><strong>Chủ tịch (GV1):</strong> {{ $sortedMembers->get(0)?->giangVien?->HoTen ?? 'Chưa phân công' }}</td>
                <td><strong>Thư ký (GV2):</strong> {{ $sortedMembers->get(1)?->giangVien?->HoTen ?? 'Chưa phân công' }}</td>
            </tr>
            <tr>
                <td><strong>Phản biện/Ủy viên (GV3):</strong> {{ $sortedMembers->get(2)?->giangVien?->HoTen ?? 'Chưa phân công' }}</td>
                <td><strong>Trạng thái dữ liệu:</strong> {{ !empty($isOfficial) ? 'Đã đủ 3 phiếu hợp lệ (Chính thức)' : 'Đang chấm điểm (Sơ bộ)' }}</td>
            </tr>
        </table>
    </div>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 22px;">STT</th>
                <th rowspan="2" style="width: 55px;">Mã SV</th>
                <th rowspan="2" style="width: 95px;">Họ và Tên</th>
                <th rowspan="2" style="width: 45px;">Lớp</th>
                <th rowspan="2" style="width: 110px;">Tên Đề Tài</th>
                @foreach($cloGroups as $code => $g)
                    <th colspan="4">{{ $code }} ({{ $g['plo'] }})</th>
                @endforeach
                <th rowspan="2" style="width: 38px;">Tỷ lệ<br/>đạt</th>
                <th rowspan="2" style="width: 44px;">Đánh<br/>giá</th>
                <th rowspan="2" style="width: 44px;">Điểm<br/>TB HĐ</th>
            </tr>
            <tr>
                @foreach($cloGroups as $code => $g)
                    <th style="width: 24px;">GV1</th>
                    <th style="width: 24px;">GV2</th>
                    <th style="width: 24px;">GV3</th>
                    <th style="width: 27px; background-color: #BDD7EE;">ĐTB</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($students as $stu)
                <tr>
                    <td class="text-center">{{ $stu['stt'] }}</td>
                    <td class="text-center text-bold">{{ $stu['maSV'] }}</td>
                    <td class="text-left">{{ $stu['hoTen'] }}</td>
                    <td class="text-center">{{ $stu['lop'] }}</td>
                    <td class="text-left" style="font-size: 6.8pt;">{{ \Illuminate\Support\Str::limit($stu['tenDeTai'], 50) }}</td>
                    @foreach($cloGroups as $code => $g)
                        @php
                            $cloObj = $stu['cloScores'][$code] ?? [];
                            $gv1 = $cloObj['gv1'] ?? null;
                            $gv2 = $cloObj['gv2'] ?? null;
                            $gv3 = $cloObj['gv3'] ?? null;
                            $avg = $cloObj['score'] ?? 0.0;
                        @endphp
                        <td class="text-right">{{ $gv1 !== null ? number_format($gv1, 1) : '-' }}</td>
                        <td class="text-right">{{ $gv2 !== null ? number_format($gv2, 1) : '-' }}</td>
                        <td class="text-right">{{ $gv3 !== null ? number_format($gv3, 1) : '-' }}</td>
                        <td class="text-right text-bold" style="background-color: #EBF1F5;">{{ number_format($avg, 2) }}</td>
                    @endforeach
                    <td class="text-center">{{ $stu['clo_pass_rate'] ?? 0 }}%</td>
                    <td class="text-center {{ !empty($stu['all_clo_passed']) ? 'badge-pass' : 'badge-fail' }}">
                        {{ !empty($stu['all_clo_passed']) ? 'Đạt' : 'Chưa đạt' }}
                    </td>
                    <td class="text-right text-bold">{{ number_format($stu['finalScore'], 2) }}</td>
                </tr>
            @endforeach

            <!-- Summary Row -->
            <tr class="summary-row">
                <td colspan="5" class="text-right text-bold">TRUNG BÌNH TOÀN HỘI ĐỒNG:</td>
                @foreach($cloGroups as $code => $g)
                    @php
                        $gv1Vals = array_filter(array_map(fn($s) => $s['cloScores'][$code]['gv1'] ?? null, $students), fn($v) => $v !== null);
                        $gv2Vals = array_filter(array_map(fn($s) => $s['cloScores'][$code]['gv2'] ?? null, $students), fn($v) => $v !== null);
                        $gv3Vals = array_filter(array_map(fn($s) => $s['cloScores'][$code]['gv3'] ?? null, $students), fn($v) => $v !== null);
                        $avgVals = array_map(fn($s) => $s['cloScores'][$code]['score'] ?? 0, $students);

                        $avgGv1 = !empty($gv1Vals) ? array_sum($gv1Vals) / count($gv1Vals) : null;
                        $avgGv2 = !empty($gv2Vals) ? array_sum($gv2Vals) / count($gv2Vals) : null;
                        $avgGv3 = !empty($gv3Vals) ? array_sum($gv3Vals) / count($gv3Vals) : null;
                        $avgTb = !empty($avgVals) ? array_sum($avgVals) / count($avgVals) : 0;
                    @endphp
                    <td class="text-right">{{ $avgGv1 !== null ? number_format($avgGv1, 1) : '-' }}</td>
                    <td class="text-right">{{ $avgGv2 !== null ? number_format($avgGv2, 1) : '-' }}</td>
                    <td class="text-right">{{ $avgGv3 !== null ? number_format($avgGv3, 1) : '-' }}</td>
                    <td class="text-right text-bold" style="background-color: #D9E1F2;">{{ number_format($avgTb, 2) }}</td>
                @endforeach
                <td class="text-center text-bold">{{ $overall_pass_rate ?? 0 }}%</td>
                <td class="text-center text-bold">{{ $passed_students ?? 0 }}/{{ $total_students ?? 0 }}</td>
                <td class="text-right text-bold">
                    @php
                        $allScores = array_map(fn($s) => $s['finalScore'], $students);
                        $councilAvg = !empty($allScores) ? array_sum($allScores) / count($allScores) : 0;
                    @endphp
                    {{ number_format($councilAvg, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Note -->
    <div style="font-size: 7.2pt; font-style: italic; margin-bottom: 10px;">
        * Ghi chú: Điểm CLO Trung Bình = (Điểm CLO GV1 + Điểm CLO GV2 + Điểm CLO GV3) / 3. Chuẩn đầu ra Đạt khi Điểm CLO TB &ge; 4.00 (thang điểm 10.0). Điểm trung bình của 3 giảng viên là dữ liệu chính thức duy nhất dùng để đánh giá và xuất báo cáo.
    </div>

    <!-- Signatures of 3 Examiners -->
    <table class="footer-table">
        <tr>
            <td style="width: 33%;">
                <div class="text-bold">CHỦ TỊCH HỘI ĐỒNG (GV1)</div>
                <div class="text-italic" style="font-size: 7.5pt;">(Ký và ghi rõ họ tên)</div>
                <div style="height: 48px;"></div>
                <div class="text-bold">{{ $sortedMembers->get(0)?->giangVien?->HoTen ?? '' }}</div>
            </td>
            <td style="width: 34%;">
                <div class="text-bold">THƯ KÝ HỘI ĐỒNG (GV2)</div>
                <div class="text-italic" style="font-size: 7.5pt;">(Ký và ghi rõ họ tên)</div>
                <div style="height: 48px;"></div>
                <div class="text-bold">{{ $sortedMembers->get(1)?->giangVien?->HoTen ?? '' }}</div>
            </td>
            <td style="width: 33%;">
                <div class="text-italic" style="font-size: 7.5pt; margin-bottom: 2px;">
                    TP.HCM, ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}
                </div>
                <div class="text-bold">{{ mb_strtoupper($sortedMembers->get(2)?->VaiTro ?? 'PHẢN BIỆN / ỦY VIÊN', 'UTF-8') }} (GV3)</div>
                <div class="text-italic" style="font-size: 7.5pt;">(Ký và ghi rõ họ tên)</div>
                <div style="height: 48px;"></div>
                <div class="text-bold">{{ $sortedMembers->get(2)?->giangVien?->HoTen ?? '' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
