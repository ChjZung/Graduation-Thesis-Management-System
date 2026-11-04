@extends('thongke_baocao.pdf.layout_pdf')

@section('title', 'Báo Cáo Danh Sách Sinh Viên Đạt Khóa Luận')
@section('pdf_title', 'BÁO CÁO DANH SÁCH SINH VIÊN ĐẠT KHÓA LUẬN TỐT NGHIỆP')

@section('pdf_subtitle')
    Học kỳ: <strong>{{ $hocKy->TenHocKy ?? 'N/A' }}</strong> &bull; Tiêu chuẩn: <strong>Điểm &ge; 4.0 và đạt tất cả CLO</strong>
@endsection

@section('pdf_content')
    @php
        $dsSVDat = $duLieuMauBaoCao['danhSachSV'] ?? [];
        $stt = 1;
    @endphp

    <div style="margin-bottom: 6px; font-size: 8pt;">
        Tổng số sinh viên được công nhận đạt khóa luận: <strong class="text-success">{{ count($dsSVDat) }}</strong> sinh viên.
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
                <th style="width: 4.5%;">Điểm 10</th>
                <th style="width: 3.5%;">Chữ</th>
                <th style="width: 4%;">Xếp Loại</th>
                <th style="width: 4%;">Trạng Thái</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dsSVDat as $sv)
            <tr>
                <td class="text-center">{{ $stt++ }}</td>
                <td class="text-center">{{ $sv['maSV'] }}</td>
                <td class="text-bold">{{ $sv['hoTen'] }}</td>
                <td class="text-center">{{ $sv['tenLop'] }}</td>
                <td>{{ $sv['tenNganh'] }}</td>
                <td style="line-height: 1.2;">{{ $sv['tenDeTai'] }}</td>
                <td>{{ \App\Services\ThongKeService::formatLecturerName($sv['tenGVHD'] ?? $sv['gvhd'] ?? null) }}</td>
                <td>{{ $sv['tenHoiDong'] }}</td>
                <td class="text-center text-bold text-success">{{ number_format($sv['diemTongKet'], 2) }}</td>
                <td class="text-center text-bold">{{ $sv['diemChu'] }}</td>
                <td class="text-center">{{ $sv['xepLoai'] }}</td>
                <td class="text-center text-bold text-success">Đạt</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center text-muted" style="padding: 15px;">Không có sinh viên nào đạt theo tiêu chí đã chọn.</td>
            </tr>
            @endforelse
        </tbody>
        @if(!empty($dsSVDat))
        <tfoot>
            <tr class="bg-total">
                <td colspan="8" class="text-bold text-start">TỔNG CỘNG SINH VIÊN ĐẠT KHÓA LUẬN:</td>
                <td class="text-center text-bold text-success">{{ number_format($kpi['diemTB'], 2) }}</td>
                <td colspan="3" class="text-center text-bold text-success">{{ count($dsSVDat) }} SV (100%)</td>
            </tr>
        </tfoot>
        @endif
    </table>
@endsection
