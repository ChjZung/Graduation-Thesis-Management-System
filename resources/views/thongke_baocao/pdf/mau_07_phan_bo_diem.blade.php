@extends('thongke_baocao.pdf.layout_pdf')

@section('title', 'Báo Cáo Phân Bố Điểm Khóa Luận Tốt Nghiệp')
@section('pdf_title', 'BÁO CÁO PHÂN BỐ ĐIỂM SỐ KHÓA LUẬN TỐT NGHIỆP')

@section('pdf_subtitle')
    Học kỳ: <strong>{{ $hocKy->TenHocKy ?? 'N/A' }}</strong> &bull; Năm học: <strong>{{ $hocKy->NamHoc ?? 'N/A' }}</strong>
@endsection

@section('pdf_content')
    @php
        $khoang = $duLieuMauBaoCao['khoangDiem'] ?? [];
        $diemChu = $duLieuMauBaoCao['diemChu'] ?? [];
        $tongScored = $kpi['tongSVHoanThanhCham'] ?: 1;
    @endphp

    <div style="font-weight: bold; margin-bottom: 4px; color: #003366;">1. BẢNG PHÂN BỐ THEO KHOẢNG ĐIỂM HỆ 10:</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">STT</th>
                <th style="width: 34%;">Khoảng Điểm (Thang 10)</th>
                <th style="width: 20%;">Xếp Loại Tương Ứng</th>
                <th style="width: 15%;">Số Lượng Sinh Viên</th>
                <th style="width: 12%;">Tỷ Lệ (%)</th>
                <th style="width: 13%;">Đánh Giá</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Dưới 4.0 điểm</td>
                <td>Kém (F)</td>
                <td class="text-center text-bold text-danger">{{ $khoang['duoi_4'] ?? 0 }}</td>
                <td class="text-center text-danger">{{ number_format((($khoang['duoi_4'] ?? 0) / $tongScored) * 100, 2) }}%</td>
                <td class="text-center text-danger text-bold">Không đạt</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Từ 4.0 đến dưới 5.0 điểm</td>
                <td>Yếu (D)</td>
                <td class="text-center text-bold">{{ $khoang['4_5'] ?? 0 }}</td>
                <td class="text-center">{{ number_format((($khoang['4_5'] ?? 0) / $tongScored) * 100, 2) }}%</td>
                <td class="text-center text-success">Đạt</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Từ 5.0 đến dưới 6.5 điểm</td>
                <td>Trung bình (D+, C)</td>
                <td class="text-center text-bold">{{ $khoang['5_65'] ?? 0 }}</td>
                <td class="text-center">{{ number_format((($khoang['5_65'] ?? 0) / $tongScored) * 100, 2) }}%</td>
                <td class="text-center text-success">Đạt</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Từ 6.5 đến dưới 8.0 điểm</td>
                <td>Khá (C+, B)</td>
                <td class="text-center text-bold">{{ $khoang['65_8'] ?? 0 }}</td>
                <td class="text-center">{{ number_format((($khoang['65_8'] ?? 0) / $tongScored) * 100, 2) }}%</td>
                <td class="text-center text-success">Đạt</td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td>Từ 8.0 đến dưới 9.0 điểm</td>
                <td>Giỏi (B+)</td>
                <td class="text-center text-bold text-primary">{{ $khoang['8_9'] ?? 0 }}</td>
                <td class="text-center">{{ number_format((($khoang['8_9'] ?? 0) / $tongScored) * 100, 2) }}%</td>
                <td class="text-center text-success">Đạt</td>
            </tr>
            <tr>
                <td class="text-center">6</td>
                <td>Từ 9.0 đến 10.0 điểm</td>
                <td>Xuất sắc (A)</td>
                <td class="text-center text-bold text-success">{{ $khoang['9_10'] ?? 0 }}</td>
                <td class="text-center text-success">{{ number_format((($khoang['9_10'] ?? 0) / $tongScored) * 100, 2) }}%</td>
                <td class="text-center text-success">Đạt</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="bg-total">
                <td colspan="3" class="text-bold text-start">TỔNG CỘNG SINH VIÊN ĐÃ CHẤM ĐIỂM:</td>
                <td class="text-center text-bold">{{ $kpi['tongSVHoanThanhCham'] }}</td>
                <td class="text-center text-bold">100%</td>
                <td class="text-center text-bold text-success">Tỷ lệ Đạt: {{ number_format($kpi['tyLeDat'], 2) }}%</td>
            </tr>
        </tfoot>
    </table>

    <div style="font-weight: bold; margin-top: 10px; margin-bottom: 4px; color: #003366;">2. BẢNG PHÂN BỐ THEO THANG ĐIỂM CHỮ:</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 28%;">Thang Điểm Chữ</th>
                <th style="width: 9%;" class="text-center">A</th>
                <th style="width: 9%;" class="text-center">B+</th>
                <th style="width: 9%;" class="text-center">B</th>
                <th style="width: 9%;" class="text-center">C+</th>
                <th style="width: 9%;" class="text-center">C</th>
                <th style="width: 9%;" class="text-center">D+</th>
                <th style="width: 9%;" class="text-center">D</th>
                <th style="width: 9%;" class="text-center">F</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-bold">Số lượng sinh viên</td>
                <td class="text-center text-bold text-success">{{ $diemChu['A'] ?? 0 }}</td>
                <td class="text-center text-bold text-primary">{{ $diemChu['B+'] ?? 0 }}</td>
                <td class="text-center text-bold">{{ $diemChu['B'] ?? 0 }}</td>
                <td class="text-center text-bold">{{ $diemChu['C+'] ?? 0 }}</td>
                <td class="text-center text-bold">{{ $diemChu['C'] ?? 0 }}</td>
                <td class="text-center text-bold">{{ $diemChu['D+'] ?? 0 }}</td>
                <td class="text-center text-bold">{{ $diemChu['D'] ?? 0 }}</td>
                <td class="text-center text-bold text-danger">{{ $diemChu['F'] ?? 0 }}</td>
            </tr>
            <tr>
                <td class="text-bold">Tỷ lệ phần trăm</td>
                <td class="text-center">{{ number_format((($diemChu['A'] ?? 0)/$tongScored)*100, 1) }}%</td>
                <td class="text-center">{{ number_format((($diemChu['B+'] ?? 0)/$tongScored)*100, 1) }}%</td>
                <td class="text-center">{{ number_format((($diemChu['B'] ?? 0)/$tongScored)*100, 1) }}%</td>
                <td class="text-center">{{ number_format((($diemChu['C+'] ?? 0)/$tongScored)*100, 1) }}%</td>
                <td class="text-center">{{ number_format((($diemChu['C'] ?? 0)/$tongScored)*100, 1) }}%</td>
                <td class="text-center">{{ number_format((($diemChu['D+'] ?? 0)/$tongScored)*100, 1) }}%</td>
                <td class="text-center">{{ number_format((($diemChu['D'] ?? 0)/$tongScored)*100, 1) }}%</td>
                <td class="text-center text-danger">{{ number_format((($diemChu['F'] ?? 0)/$tongScored)*100, 1) }}%</td>
            </tr>
        </tbody>
    </table>
@endsection
