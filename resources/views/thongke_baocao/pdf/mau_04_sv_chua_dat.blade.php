@extends('thongke_baocao.pdf.layout_pdf')

@section('title', 'Báo Cáo Danh Sách Sinh Viên Chưa Đạt Khóa Luận')
@section('pdf_title', 'BÁO CÁO DANH SÁCH SINH VIÊN CHƯA ĐẠT KHÓA LUẬN TỐT NGHIỆP')

@section('pdf_subtitle')
    Học kỳ: <strong>{{ $hocKy->TenHocKy ?? 'N/A' }}</strong> &bull; Tiêu chuẩn: <strong>Điểm &lt; 4.0 hoặc có CLO chưa đạt</strong>
@endsection

@section('pdf_content')
    @php
        $dsSVChuaDat = $duLieuMauBaoCao['danhSachSV'] ?? [];
        $stt = 1;
    @endphp

    <div style="margin-bottom: 6px; font-size: 8pt;">
        Tổng số sinh viên chưa đạt khóa luận: <strong class="text-danger">{{ count($dsSVChuaDat) }}</strong> sinh viên.
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">STT</th>
                <th style="width: 8%;">MSSV</th>
                <th style="width: 13%;">Họ và Tên</th>
                <th style="width: 7%;">Lớp</th>
                <th style="width: 11%;">Ngành</th>
                <th style="width: 20%;">Tên Đề Tài Khóa Luận</th>
                <th style="width: 11%;">GVHD</th>
                <th style="width: 11%;">Hội Đồng Bảo Vệ</th>
                <th style="width: 4%;">Điểm 10</th>
                <th style="width: 3%;">Chữ</th>
                <th style="width: 4%;">Trạng Thái</th>
                <th style="width: 5%;">Phương Án Xử Lý</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dsSVChuaDat as $sv)
            <tr>
                <td class="text-center">{{ $stt++ }}</td>
                <td class="text-center">{{ $sv['maSV'] }}</td>
                <td class="text-bold">{{ $sv['hoTen'] }}</td>
                <td class="text-center">{{ $sv['tenLop'] }}</td>
                <td>{{ $sv['tenNganh'] }}</td>
                <td style="line-height: 1.2;">{{ $sv['tenDeTai'] }}</td>
                <td>{{ \App\Services\ThongKeService::formatLecturerName($sv['tenGVHD'] ?? $sv['gvhd'] ?? null) }}</td>
                <td>{{ $sv['tenHoiDong'] }}</td>
                <td class="text-center text-bold text-danger">{{ number_format($sv['diemTongKet'], 2) }}</td>
                <td class="text-center text-bold text-danger">{{ $sv['diemChu'] }}</td>
                <td class="text-center text-bold text-danger">Chưa đạt</td>
                <td class="text-center small">Bảo vệ lại đợt sau</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center text-muted" style="padding: 15px;">Không có sinh viên nào chưa đạt trong đợt này.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
@endsection
