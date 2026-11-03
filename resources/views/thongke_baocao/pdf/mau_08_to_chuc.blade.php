@extends('thongke_baocao.pdf.layout_pdf')

@section('title', 'Báo Cáo Tình Hình Tổ Chức Khóa Luận Tốt Nghiệp')
@section('pdf_title', 'BÁO CÁO TÌNH HÌNH TỔ CHỨC VÀ TIẾN ĐỘ KHÓA LUẬN TỐT NGHIỆP')

@section('pdf_subtitle')
    Học kỳ: <strong>{{ $hocKy->TenHocKy ?? 'N/A' }}</strong> &bull; Năm học: <strong>{{ $hocKy->NamHoc ?? 'N/A' }}</strong>
@endsection

@section('pdf_content')
    <div style="font-weight: bold; margin-bottom: 4px; color: #003366;">1. THỐNG KÊ TỔNG QUAN CÔNG TÁC TỔ CHỨC:</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">STT</th>
                <th style="width: 43%;">Chỉ Tiêu Đánh Giá Công Tác Tổ Chức</th>
                <th style="width: 16%;">Số Lượng</th>
                <th style="width: 14%;">Tỷ Lệ</th>
                <th style="width: 22%;">Ghi Chú Tiến Độ</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Tổng số sinh viên đủ điều kiện tham gia khóa luận</td>
                <td class="text-center text-bold">{{ $kpi['tongSVThamGia'] }}</td>
                <td class="text-center">100%</td>
                <td>Theo danh sách thẩm định điều kiện</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Sinh viên đã được phân công vào Hội đồng bảo vệ</td>
                <td class="text-center text-bold text-primary">{{ $kpi['tongSVDaPhanCongHD'] }}</td>
                <td class="text-center text-primary">{{ number_format(($kpi['tongSVDaPhanCongHD'] / max(1, $kpi['tongSVThamGia'])) * 100, 1) }}%</td>
                <td>Đã bố trí lịch bảo vệ chính thức</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Sinh viên chưa được phân công Hội đồng</td>
                <td class="text-center text-bold {{ $kpi['tongSVChuaPhanCongHD'] > 0 ? 'text-warning' : '' }}">{{ $kpi['tongSVChuaPhanCongHD'] }}</td>
                <td class="text-center">{{ number_format(($kpi['tongSVChuaPhanCongHD'] / max(1, $kpi['tongSVThamGia'])) * 100, 1) }}%</td>
                <td>Chờ bố trí đợt bổ sung</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Tổng số đề tài khóa luận được phê duyệt</td>
                <td class="text-center text-bold">{{ $kpi['tongDeTai'] }}</td>
                <td class="text-center">--</td>
                <td>Đã qua phê duyệt của Bộ môn &amp; Khoa</td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td>Tổng số Hội đồng chấm điểm được thành lập</td>
                <td class="text-center text-bold">{{ $kpi['tongHoiDong'] }}</td>
                <td class="text-center">100%</td>
                <td>Theo Quyết định thành lập hội đồng</td>
            </tr>
            <tr>
                <td class="text-center">6</td>
                <td>Hội đồng đã hoàn thành toàn bộ công tác chấm điểm</td>
                <td class="text-center text-bold text-success">{{ $kpi['soHoiDongHoanThanh'] }}</td>
                <td class="text-center text-success">{{ number_format(($kpi['soHoiDongHoanThanh'] / max(1, $kpi['tongHoiDong'])) * 100, 1) }}%</td>
                <td>Đã nộp đủ các phiếu chấm hợp lệ</td>
            </tr>
            <tr>
                <td class="text-center">7</td>
                <td>Hội đồng đang tiến hành chấm điểm / Chưa xong</td>
                <td class="text-center text-bold {{ $kpi['soHoiDongChuaHoanThanh'] > 0 ? 'text-warning' : '' }}">{{ $kpi['soHoiDongChuaHoanThanh'] }}</td>
                <td class="text-center">{{ number_format(($kpi['soHoiDongChuaHoanThanh'] / max(1, $kpi['tongHoiDong'])) * 100, 1) }}%</td>
                <td>Đang hoàn thiện biên bản và phiếu</td>
            </tr>
        </tbody>
    </table>

    <div style="font-weight: bold; margin-top: 10px; margin-bottom: 4px; color: #003366;">2. TIẾN ĐỘ THỰC HIỆN THEO TỪNG HỘI ĐỒNG:</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">STT</th>
                <th style="width: 26%;">Tên Hội Đồng Bảo Vệ</th>
                <th style="width: 18%;">Ngành Đào Tạo</th>
                <th style="width: 12%;">Địa Điểm / Phòng</th>
                <th style="width: 10%;">Đã Phân Công</th>
                <th style="width: 10%;">Đã Chấm Điểm</th>
                <th style="width: 10%;">Chưa Chấm</th>
                <th style="width: 10%;">Tình Trạng</th>
            </tr>
        </thead>
        <tbody>
            @php $sttHd = 1; @endphp
            @foreach($danhSachHoiDong as $hd)
            <tr>
                <td class="text-center">{{ $sttHd++ }}</td>
                <td class="text-bold">{{ $hd['hoiDong']->TenHoiDong }}</td>
                <td>{{ $hd['hoiDong']->nganh?->TenNganh ?? 'Chung' }}</td>
                <td class="text-center">{{ $hd['hoiDong']->DiaDiem ?? 'HUIT' }}</td>
                <td class="text-center text-bold">{{ $hd['soSV'] }} SV</td>
                <td class="text-center text-success text-bold">{{ $hd['soSV'] - $hd['soChuaXong'] }} SV</td>
                <td class="text-center {{ $hd['soChuaXong'] > 0 ? 'text-warning text-bold' : '' }}">{{ $hd['soChuaXong'] }} SV</td>
                <td class="text-center">
                    @if($hd['isHoanThanh'])
                        <span class="text-success text-bold">Đã hoàn thành</span>
                    @else
                        <span class="text-warning text-bold">Đang chấm</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
