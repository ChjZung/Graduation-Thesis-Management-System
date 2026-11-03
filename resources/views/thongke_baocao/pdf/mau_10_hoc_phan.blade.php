@extends('thongke_baocao.pdf.layout_pdf')

@section('title', 'Báo Cáo Thống Kê Khóa Luận Theo Học Phần')
@section('pdf_title', 'BÁO CÁO THỐNG KÊ KẾT QUẢ KHÓA LUẬN THEO HỌC PHẦN')

@section('pdf_subtitle')
    Học kỳ: <strong>{{ $hocKy->TenHocKy ?? 'N/A' }}</strong> &bull; Năm học: <strong>{{ $hocKy->NamHoc ?? 'N/A' }}</strong>
@endsection

@section('pdf_content')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">STT</th>
                <th style="width: 15%;">Mã Học Phần</th>
                <th style="width: 32%;">Tên Học Phần Khóa Luận</th>
                <th style="width: 8%;">Tổng SV</th>
                <th style="width: 8%;">Số Đạt</th>
                <th style="width: 8%;">Không Đạt</th>
                <th style="width: 8%;">Chưa KQ</th>
                <th style="width: 8%;">Tỷ Lệ Đạt</th>
                <th style="width: 8%;">Điểm TB</th>
            </tr>
        </thead>
        <tbody>
            @php $sttHp = 1; @endphp
            @foreach($statsTheoHocPhan as $hp)
            @php
                $tyLe = ($hp['soDat'] + $hp['soKhongDat']) > 0 ? round(($hp['soDat'] / ($hp['soDat'] + $hp['soKhongDat'])) * 100, 2) : 0;
                $tb = !empty($hp['scores']) ? round(array_sum($hp['scores']) / count($hp['scores']), 2) : 0;
            @endphp
            <tr>
                <td class="text-center">{{ $sttHp++ }}</td>
                <td class="text-center font-monospace">{{ $hp['maHocPhan'] }}</td>
                <td class="text-bold">{{ $hp['tenHocPhan'] }}</td>
                <td class="text-center">{{ $hp['soSV'] }}</td>
                <td class="text-center text-success text-bold">{{ $hp['soDat'] }}</td>
                <td class="text-center text-danger">{{ $hp['soKhongDat'] }}</td>
                <td class="text-center">{{ $hp['soChuaXong'] }}</td>
                <td class="text-center text-bold text-success">{{ number_format($tyLe, 2) }}%</td>
                <td class="text-center text-bold text-primary">{{ number_format($tb, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-total">
                <td colspan="3" class="text-bold text-start">TỔNG CỘNG TOÀN BỘ CÁC HỌC PHẦN:</td>
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
