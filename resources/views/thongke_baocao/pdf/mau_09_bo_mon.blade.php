@extends('thongke_baocao.pdf.layout_pdf')

@section('title', 'Báo Cáo Thống Kê Khóa Luận Theo Bộ Môn')
@section('pdf_title', 'BÁO CÁO THỐNG KÊ KẾT QUẢ KHÓA LUẬN THEO BỘ MÔN')

@section('pdf_subtitle')
    Học kỳ: <strong>{{ $hocKy->TenHocKy ?? 'N/A' }}</strong> &bull; Năm học: <strong>{{ $hocKy->NamHoc ?? 'N/A' }}</strong>
@endsection

@section('pdf_content')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">STT</th>
                <th style="width: 35%;">Bộ Môn Quản Lý</th>
                <th style="width: 10%;">Tổng Số SV</th>
                <th style="width: 10%;">Số Đạt</th>
                <th style="width: 10%;">Không Đạt</th>
                <th style="width: 10%;">Chưa Có KQ</th>
                <th style="width: 10%;">Tỷ Lệ Đạt</th>
                <th style="width: 10%;">Điểm TB</th>
            </tr>
        </thead>
        <tbody>
            @php $sttBm = 1; @endphp
            @foreach($statsTheoBoMon as $bm)
            @php
                $tyLe = ($bm['soDat'] + $bm['soKhongDat']) > 0 ? round(($bm['soDat'] / ($bm['soDat'] + $bm['soKhongDat'])) * 100, 2) : 0;
                $tb = !empty($bm['scores']) ? round(array_sum($bm['scores']) / count($bm['scores']), 2) : 0;
            @endphp
            <tr>
                <td class="text-center">{{ $sttBm++ }}</td>
                <td class="text-bold">{{ $bm['tenBoMon'] }}</td>
                <td class="text-center">{{ $bm['soSV'] }}</td>
                <td class="text-center text-success text-bold">{{ $bm['soDat'] }}</td>
                <td class="text-center text-danger">{{ $bm['soKhongDat'] }}</td>
                <td class="text-center">{{ $bm['soChuaXong'] }}</td>
                <td class="text-center text-bold text-success">{{ number_format($tyLe, 2) }}%</td>
                <td class="text-center text-bold text-primary">{{ number_format($tb, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-total">
                <td colspan="2" class="text-bold text-start">TỔNG CỘNG TOÀN BỘ CÁC BỘ MÔN:</td>
                <td class="text-center text-bold">{{ $kpi['tongSVDaPhanCongHD'] }}</td>
                <td class="text-center text-bold text-success">{{ $kpi['tongSVDat'] }}</td>
                <td class="text-center text-bold text-danger">{{ $kpi['tongSVChuaDat'] }}</td>
                <td class="text-center text-bold">{{ $kpi['tongSVChuaCoKQ'] }}</td>
                <td class="text-center text-bold text-success">{{ number_format($kpi['tyLeDat'], 2) }}%</td>
                <td class="text-center text-bold text-primary">{{ number_format($kpi['diemTB'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
