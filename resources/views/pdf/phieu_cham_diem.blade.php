<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $tenBieuMau ?? 'PHIẾU CHẤM ĐIỂM KHÓA LUẬN' }}</title>
    <style>
        @page {
            margin: 12mm 15mm 15mm 15mm;
            size: a4 portrait;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5pt;
            line-height: 1.35;
            color: #000;
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
            margin-bottom: 8px;
        }
        table.header-table td {
            vertical-align: middle;
            border: none;
            padding: 2px;
        }

        .title-doc {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 4px;
            margin-bottom: 2px;
        }
        .subtitle-doc {
            font-size: 9.5pt;
            font-style: italic;
            margin-bottom: 8px;
        }

        .info-box {
            margin-bottom: 10px;
            line-height: 1.5;
            font-size: 9.5pt;
        }

        table.rubric-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
        }
        table.rubric-table th, table.rubric-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        table.rubric-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            font-size: 9pt;
        }

        .noi-dung-cell {
            text-align: left;
            vertical-align: top;
            font-size: 9pt;
            line-height: 1.35;
        }
        .noi-dung-line {
            display: block;
        }
        .noi-dung-bullet {
            padding-left: 10px;
        }
        .noi-dung-subbullet {
            padding-left: 22px;
        }
        .noi-dung-subsubbullet {
            padding-left: 34px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .footer-table td {
            border: none;
            vertical-align: top;
            padding: 0;
        }
    </style>
</head>
<body>

    {{-- HEADER CÓ LOGO VÀ TÊN KHOA / TRƯỜNG --}}
    <table class="header-table">
        <tr>
            <td style="width: 15%; text-align: left;">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="max-height: 60px; max-width: 90px;" alt="Logo HUIT">
                @endif
            </td>
            <td style="width: 85%; text-align: center;">
                <div style="font-size: 10pt; font-weight: bold;" class="text-uppercase">TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH</div>
                <div style="font-size: 10pt; font-weight: bold;" class="text-uppercase">KHOA CÔNG NGHỆ THÔNG TIN</div>
                <div class="title-doc">{{ $tenBieuMau ?? ($loaiKhoaLuan === 'KLKS' ? 'PHIẾU CHẤM ĐIỂM KHÓA LUẬN KỸ SƯ' : 'PHIẾU CHẤM ĐIỂM KHÓA LUẬN CỬ NHÂN') }}</div>
                <div class="subtitle-doc">(Hướng ứng dụng)</div>
            </td>
        </tr>
    </table>

    {{-- THÔNG TIN ĐỀ TÀI VÀ NHÓM SINH VIÊN --}}
    <div class="info-box">
        <div><strong>Tên đề tài:</strong> {{ $activeDeTai->TenDeTai ?? 'Chưa cập nhật tên đề tài' }}</div>
        <div style="margin-top: 3px;"><strong>Nhóm sinh viên thực hiện:</strong></div>
        @foreach($sinhViens as $idx => $sv)
        <div style="margin-left: 15px;">
            {{ $idx + 1 }}. Mã SV: <strong>{{ $sv->MaSV }}</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
            Họ tên: <strong>{{ $sv->HoTen }}</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
            Lớp: <strong>{{ $sv->lop?->TenLop ?? ($sv->MaLop ?? '') }}</strong>
        </div>
        @endforeach
        <div style="margin-top: 3px;">
            <strong>Giảng viên hướng dẫn:</strong> {{ $gvhdHoTen ?? 'Chưa phân công' }}
        </div>
        <div style="margin-top: 3px;">
            <strong>Hội đồng:</strong> {{ $activeHoiDong->TenHoiDong ?? $activeHoiDong->MaHoiDong }}
        </div>
        <div style="margin-top: 3px;">
            <strong>Phòng:</strong> {{ $activeHoSo->PhongBaoVe ?? ($activeHoiDong->DiaDiem ?? 'Chưa xếp phòng') }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
            <strong>Ngày chấm:</strong> {{ isset($ngayCham) ? \Carbon\Carbon::parse($ngayCham)->format('d/m/Y') : now()->format('d/m/Y') }}
        </div>
    </div>

    {{-- BẢNG RUBRIC CHẤM ĐIỂM CHUẨN --}}
    <table class="rubric-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 32px;">STT</th>
                <th rowspan="2" class="text-start">Nội dung</th>
                <th rowspan="2" style="width: 58px;">CLO</th>
                <th rowspan="2" style="width: 75px;">Điểm tối đa</th>
                <th colspan="{{ max(1, $sinhViens->count()) }}">Điểm chấm</th>
            </tr>
            <tr>
                @foreach($sinhViens as $idx => $sv)
                <th style="width: 55px;">SV{{ $idx + 1 }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rubric as $cr)
            @php
                $isBonus = !empty($cr['is_bonus']);
                $cId = $cr['id'];
                $lines = explode("\n", $cr['noi_dung']);
            @endphp
            <tr>
                <td class="text-center" style="vertical-align: top; font-weight: {{ $isBonus ? 'bold' : 'normal' }};">
                    {{ $cr['stt'] }}
                </td>
                <td class="noi-dung-cell">
                    @if($cr['id'] === 'tc7' && !empty($functionalRequirements))
                        <div class="text-bold">Xây dựng các chức năng cơ bản của ứng dụng: đăng nhập, đăng xuất, backup &amp; restore,…</div>
                        <div class="text-bold" style="margin-top: 2px;">Cài đặt chức năng theo yêu cầu của bài toán:</div>
                        @php $groupedReqs = collect($functionalRequirements)->groupBy('group'); @endphp
                        @foreach($groupedReqs as $groupName => $reqItems)
                            @if($groupName !== 'Chức năng cơ bản')
                                <div class="noi-dung-bullet text-bold" style="margin-top: 2px;">&bull; {{ $groupName }}:</div>
                                @foreach($reqItems as $r)
                                    @if($r['id'] !== 'tc7_co_ban')
                                        <div class="noi-dung-subbullet">&ndash; {{ $r['label'] }} (0.50đ)</div>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    @else
                        @foreach($lines as $lineIdx => $line)
                            @php
                                $trimmed = trim($line);
                                $isBullet = str_starts_with($trimmed, '-') || str_starts_with($trimmed, '•');
                                $isSubBullet = str_starts_with($trimmed, '+');
                                $isSubSubBullet = str_starts_with($trimmed, '*');
                            @endphp
                            @if($isSubSubBullet)
                                <div class="noi-dung-subsubbullet">&bull; {{ trim(substr($trimmed, 1)) }}</div>
                            @elseif($isSubBullet)
                                <div class="noi-dung-subbullet">&bull; {{ trim(substr($trimmed, 1)) }}</div>
                            @elseif($isBullet)
                                <div class="noi-dung-bullet">&bull; {{ trim(substr($trimmed, 1)) }}</div>
                            @else
                                <div class="noi-dung-line {{ $lineIdx === 0 ? 'text-bold' : '' }}">{{ $trimmed }}</div>
                            @endif
                        @endforeach
                    @endif
                </td>
                <td class="text-center" style="vertical-align: top; font-size: 8.5pt;">
                    {{ $cr['clo'] }}
                </td>
                <td class="text-center" style="vertical-align: top; width: 75px; padding: 3px;">
                    {{-- Khung Điểm Tổng --}}
                    <div style="border: 1px solid #00569e; background-color: #f0f7ff; padding: 2px 3px; font-weight: bold; font-size: 8.5pt; margin-bottom: 3px; text-align: center;">
                        <span style="font-size: 6.2pt; color: #555; display: block; text-transform: uppercase;">Tổng</span>
                        {{ number_format($cr['max'], 2) }}
                    </div>

                    {{-- Khung Điểm Chi Tiết Các Dòng --}}
                    @if(isset($cr['diem_toi_da_lines']) && is_array($cr['diem_toi_da_lines']))
                    <div style="border: 1px dashed #777; background-color: #fafafa; padding: 2px; font-size: 7.2pt; text-align: center; line-height: 1.25;">
                        <span style="font-size: 6pt; color: #777; display: block; border-bottom: 1px dotted #ccc; margin-bottom: 2px; text-transform: uppercase;">Chi tiết</span>
                        @foreach($cr['diem_toi_da_lines'] as $dLine)
                            <div>{{ $dLine }}</div>
                        @endforeach
                    </div>
                    @endif
                </td>

                {{-- Điểm chấm cho từng sinh viên --}}
                @foreach($sinhViens as $sIdx => $sv)
                @php
                    $daChamSV = $diemDaCham->get($sv->MaSV);
                    $svChiTiet = $daChamSV?->ChiTietDiem;
                    $scoreVal = isset($svChiTiet[$cId]) ? (float)$svChiTiet[$cId] : null;
                @endphp
                <td class="text-center" style="vertical-align: top; font-weight: bold;">
                    {{ $scoreVal !== null ? number_format($scoreVal, 2) : '' }}
                </td>
                @endforeach
            </tr>
            @endforeach

            {{-- HÀNG TỔNG CỘNG --}}
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="3" class="text-end" style="padding-right: 10px;">
                    Tổng cộng:
                </td>
                <td class="text-center">
                    10.0
                </td>
                @foreach($sinhViens as $sIdx => $sv)
                @php
                    $daChamSV = $diemDaCham->get($sv->MaSV);
                    $curDiem = $daChamSV ? (float)$daChamSV->Diem : null;
                @endphp
                <td class="text-center" style="font-size: 10pt;">
                    {{ $curDiem !== null ? number_format($curDiem, 2) : '' }}
                </td>
                @endforeach
            </tr>
        </tbody>
    </table>

    {{-- KÝ TÊN VÀ GHI RÕ HỌ TÊN --}}
    <table class="footer-table">
        <tr>
            <td style="width: 50%;">
                @if(isset($diemDaCham) && $diemDaCham->isNotEmpty() && $diemDaCham->first()->NhanXet)
                <div style="font-size: 8.5pt; margin-top: 10px;">
                    <strong>Nhận xét chung của Giám khảo:</strong>
                    <div style="font-style: italic; margin-top: 2px;">
                        {{ $diemDaCham->first()->NhanXet }}
                    </div>
                </div>
                @endif
            </td>
            <td style="width: 50%; text-align: center;">
                <div class="text-italic" style="margin-bottom: 4px; font-size: 9pt;">
                    TP. Hồ Chí Minh, ngày {{ isset($ngayCham) ? \Carbon\Carbon::parse($ngayCham)->format('d') : now()->format('d') }} 
                    tháng {{ isset($ngayCham) ? \Carbon\Carbon::parse($ngayCham)->format('m') : now()->format('m') }} 
                    năm {{ isset($ngayCham) ? \Carbon\Carbon::parse($ngayCham)->format('Y') : now()->format('Y') }}
                </div>
                <div class="text-bold text-uppercase" style="font-size: 9.5pt;">Giám khảo chấm điểm</div>
                <div class="text-italic" style="font-size: 8.5pt; margin-bottom: 4px;">(Ký và ghi rõ họ tên)</div>
                @if(!empty($chuKy))
                    <div style="height: 55px; margin: 2px 0;">
                        <img src="{{ $chuKy }}" style="max-height: 50px; max-width: 150px;" alt="Chữ ký">
                    </div>
                @else
                    <div style="height: 50px;"></div>
                @endif
                <div class="text-bold" style="font-size: 10pt;">{{ $giangVien->HoTen ?? '' }}</div>
                <div style="font-size: 8.5pt; color: #555;">{{ $vaiTroGV ?? 'Thành viên Hội đồng' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
