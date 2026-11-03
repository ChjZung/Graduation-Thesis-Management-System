@extends('thongke_baocao.pdf.layout_pdf')

@section('title', 'Báo Cáo Thống Kê Điểm Số Theo Hội Đồng')
@section('pdf_title', 'BÁO CÁO THỐNG KÊ ĐIỂM SỐ CHI TIẾT THEO HỘI ĐỒNG')

@section('pdf_subtitle')
    Học kỳ: <strong>{{ $hocKy->TenHocKy ?? 'N/A' }}</strong> &bull; Năm học: <strong>{{ $hocKy->NamHoc ?? 'N/A' }}</strong>
@endsection

@section('pdf_content')
    @php $sttGlobal = 1; @endphp
    @foreach($danhSachHoiDong as $hdData)
    <div style="margin-top: 10px; margin-bottom: 4px; font-weight: bold; font-size: 8.5pt; color: #003366;">
        HỘI ĐỒNG: {{ mb_strtoupper($hdData['hoiDong']->TenHoiDong) }}
        <span style="font-weight: normal; font-size: 7.5pt; color: #444;">
            &bull; Ngành: {{ $hdData['hoiDong']->nganh?->TenNganh ?? 'Chung' }}
            &bull; Địa điểm: {{ $hdData['hoiDong']->DiaDiem ?? 'HUIT' }}
            &bull; Giám khảo: 
            CT: {{ $hdData['ct']->giangVien?->HoTen ?? 'Chưa rõ' }}, 
            TK: {{ $hdData['tk']->giangVien?->HoTen ?? 'Chưa rõ' }}, 
            UV: {{ $hdData['uvs']->pluck('giangVien.HoTen')->filter()->join(', ') ?: 'Không có' }}
        </span>
    </div>

    @php
        $uvCount = count($hdData['uvs']);
        $uvWidth = $uvCount > 0 ? round(10.0 / $uvCount, 1) : 0;
        $deTaiWidth = 25.0 + (10.0 - ($uvWidth * $uvCount));
    @endphp
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3.5%;">STT</th>
                <th style="width: 8.5%;">MSSV</th>
                <th style="width: 14%;">Họ và Tên</th>
                <th style="width: 7%;">Lớp</th>
                <th style="width: {{ $deTaiWidth }}%;">Tên Đề Tài Khóa Luận</th>
                <th style="width: 4.5%;">Điểm CT</th>
                <th style="width: 4.5%;">Điểm TK</th>
                @foreach($hdData['uvs'] as $uIdx => $u)
                    <th style="width: {{ $uvWidth }}%;">UV{{ $uIdx + 1 }}</th>
                @endforeach
                <th style="width: 4.5%;">Độ Lệch</th>
                <th style="width: 4.5%;">Điểm HĐ</th>
                <th style="width: 4.5%;">Điểm Tổng</th>
                <th style="width: 3.5%;">Chữ</th>
                <th style="width: 6%;">Kết Quả</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hdData['deTais'] as $dtItem)
                @foreach($dtItem['sinhViens'] as $sv)
                <tr>
                    <td class="text-center">{{ $sttGlobal++ }}</td>
                    <td class="text-center">{{ $sv['maSV'] }}</td>
                    <td class="text-bold">{{ $sv['hoTen'] }}</td>
                    <td class="text-center">{{ $sv['tenLop'] }}</td>
                    <td style="line-height: 1.2;">{{ $sv['tenDeTai'] }}</td>
                    <td class="text-center">{{ $sv['diemCT'] !== null ? number_format($sv['diemCT'], 2) : '--' }}</td>
                    <td class="text-center">{{ $sv['diemTK'] !== null ? number_format($sv['diemTK'], 2) : '--' }}</td>
                    @foreach($sv['diemUVs'] as $uScore)
                        <td class="text-center">{{ $uScore['diem'] !== null ? number_format($uScore['diem'], 2) : '--' }}</td>
                    @endforeach
                    <td class="text-center {{ $sv['canhBaoLech'] ? 'text-danger text-bold' : '' }}">
                        {{ number_format($sv['diffScore'], 2) }}
                        @if($sv['canhBaoLech']) * @endif
                    </td>
                    <td class="text-center text-bold">{{ $sv['diemHD'] !== null ? number_format($sv['diemHD'], 2) : '--' }}</td>
                    <td class="text-center text-bold text-primary">{{ $sv['diemTongKet'] !== null ? number_format($sv['diemTongKet'], 2) : '--' }}</td>
                    <td class="text-center text-bold">{{ $sv['diemChu'] ?: '--' }}</td>
                    <td class="text-center text-bold {{ $sv['ketQua'] === 'Đạt' ? 'text-success' : ($sv['ketQua'] === 'Không đạt' ? 'text-danger' : 'badge-pending') }}">
                        {{ $sv['ketQua'] }}
                    </td>
                </tr>
                @endforeach
            @endforeach
            <tr class="bg-subtotal">
                <td colspan="5" class="text-start">Tổng kết Hội đồng {{ $hdData['hoiDong']->TenHoiDong }}: {{ $hdData['soDeTai'] }} ĐT ({{ $hdData['soSV'] }} SV)</td>
                <td colspan="{{ 4 + count($hdData['uvs']) }}" class="text-right">
                    Đạt: <strong>{{ $hdData['soDat'] }}</strong> &bull; Không đạt: <strong>{{ $hdData['soKhongDat'] }}</strong> &bull; Tỷ lệ: <strong>{{ number_format($hdData['tyLeDat'], 2) }}%</strong>
                </td>
                <td colspan="3" class="text-center text-bold text-primary">Điểm TB: {{ number_format($hdData['diemTB'], 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endforeach
@endsection
