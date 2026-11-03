@extends('thongke_baocao.pdf.layout_pdf')

@section('title', 'Báo Cáo Thống Kê Kết Quả Khóa Luận Theo Ngành')
@section('pdf_title', 'BÁO CÁO THỐNG KÊ KẾT QUẢ KHÓA LUẬN THEO NGÀNH ĐÀO TẠO')

@section('pdf_subtitle')
    Học kỳ: <strong>{{ $hocKy->TenHocKy ?? 'N/A' }}</strong> &bull; Năm học: <strong>{{ $hocKy->NamHoc ?? 'N/A' }}</strong>
@endsection

@section('pdf_content')
    <!-- Bảng tổng hợp theo ngành -->
    <div style="font-weight: bold; margin-bottom: 4px; color: #003366;">1. BẢNG TỔNG HỢP THEO TỪNG NGÀNH ĐÀO TẠO:</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">STT</th>
                <th style="width: 35%;">Ngành Đào Tạo</th>
                <th style="width: 10%;">Tổng SV</th>
                <th style="width: 10%;">Số Đạt</th>
                <th style="width: 10%;">Chưa Đạt</th>
                <th style="width: 10%;">Chưa Có KQ</th>
                <th style="width: 10%;">Tỷ Lệ Đạt</th>
                <th style="width: 10%;">Điểm TB</th>
            </tr>
        </thead>
        <tbody>
            @php $sttNg = 1; @endphp
            @foreach($statsTheoNganh as $stNg)
            @php
                $tyLe = ($stNg['soDat'] + $stNg['soKhongDat']) > 0 ? round(($stNg['soDat'] / ($stNg['soDat'] + $stNg['soKhongDat'])) * 100, 2) : 0;
                $tb = !empty($stNg['scores']) ? round(array_sum($stNg['scores']) / count($stNg['scores']), 2) : 0;
            @endphp
            <tr>
                <td class="text-center">{{ $sttNg++ }}</td>
                <td class="text-bold">{{ $stNg['tenNganh'] }}</td>
                <td class="text-center">{{ $stNg['soSV'] }}</td>
                <td class="text-center text-success text-bold">{{ $stNg['soDat'] }}</td>
                <td class="text-center text-danger">{{ $stNg['soKhongDat'] }}</td>
                <td class="text-center">{{ $stNg['soChuaXong'] }}</td>
                <td class="text-center text-bold text-success">{{ number_format($tyLe, 2) }}%</td>
                <td class="text-center text-bold text-primary">{{ number_format($tb, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Danh sách sinh viên chi tiết phân theo ngành -->
    @if($config['show_detail'] ?? true)
    <div style="font-weight: bold; margin-top: 10px; margin-bottom: 4px; color: #003366;">2. DANH SÁCH CHI TIẾT SINH VIÊN THEO NGÀNH:</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3.5%;">STT</th>
                <th style="width: 8.5%;">MSSV</th>
                <th style="width: 14%;">Họ và Tên</th>
                <th style="width: 8%;">Lớp</th>
                <th style="width: 12%;">Ngành</th>
                <th style="width: 24%;">Tên Đề Tài Khóa Luận</th>
                <th style="width: 14%;">GVHD</th>
                <th style="width: 5.5%;">Điểm 10</th>
                <th style="width: 4.5%;">Chữ</th>
                <th style="width: 6%;">Kết Quả</th>
            </tr>
        </thead>
        <tbody>
            @php $sttSV = 1; @endphp
            @foreach($danhSachTatCaSV as $sv)
            <tr>
                <td class="text-center">{{ $sttSV++ }}</td>
                <td class="text-center">{{ $sv['maSV'] }}</td>
                <td class="text-bold">{{ $sv['hoTen'] }}</td>
                <td class="text-center">{{ $sv['tenLop'] }}</td>
                <td>{{ $sv['tenNganh'] }}</td>
                <td style="line-height: 1.2;">{{ $sv['tenDeTai'] }}</td>
                <td>{{ \App\Services\ThongKeService::formatLecturerName($sv['tenGVHD'] ?? $sv['gvhd'] ?? null) }}</td>
                <td class="text-center text-bold">{{ $sv['diemTongKet'] !== null ? number_format($sv['diemTongKet'], 2) : '--' }}</td>
                <td class="text-center text-bold">{{ $sv['diemChu'] ?: '--' }}</td>
                <td class="text-center text-bold {{ $sv['ketQua'] === 'Đạt' ? 'text-success' : ($sv['ketQua'] === 'Không đạt' ? 'text-danger' : 'badge-pending') }}">
                    {{ $sv['ketQua'] }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
@endsection
