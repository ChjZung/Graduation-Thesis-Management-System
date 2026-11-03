@extends('thongke_baocao.pdf.layout_pdf')

@section('title', 'Báo Cáo Sinh Viên Chưa Có Kết Quả (Đang Chấm)')
@section('pdf_title', 'BÁO CÁO TIẾN ĐỘ CHẤM ĐIỂM: SINH VIÊN CHƯA CÓ KẾT QUẢ')

@section('pdf_subtitle')
    Học kỳ: <strong>{{ $hocKy->TenHocKy ?? 'N/A' }}</strong> &bull; Tình trạng: <strong>Chưa thu đủ phiếu chấm của Hội đồng</strong>
@endsection

@section('pdf_content')
    @php
        $dsChuaCoKQ = $duLieuMauBaoCao['danhSachSV'] ?? [];
        $stt = 1;
    @endphp

    <div style="margin-bottom: 6px; font-size: 8pt;">
        Tổng số sinh viên chưa có kết quả chính thức: <strong class="text-warning">{{ count($dsChuaCoKQ) }}</strong> sinh viên.
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3.5%;">STT</th>
                <th style="width: 8.5%;">MSSV</th>
                <th style="width: 14%;">Họ và Tên</th>
                <th style="width: 8%;">Lớp</th>
                <th style="width: 12%;">Ngành</th>
                <th style="width: 22%;">Tên Đề Tài Khóa Luận</th>
                <th style="width: 12%;">GVHD</th>
                <th style="width: 11%;">Hội Đồng Bảo Vệ</th>
                <th style="width: 4.5%;">Điểm HĐ</th>
                <th style="width: 4.5%;">Trạng Thái</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dsChuaCoKQ as $sv)
            <tr>
                <td class="text-center">{{ $stt++ }}</td>
                <td class="text-center">{{ $sv['maSV'] }}</td>
                <td class="text-bold">{{ $sv['hoTen'] }}</td>
                <td class="text-center">{{ $sv['tenLop'] }}</td>
                <td>{{ $sv['tenNganh'] }}</td>
                <td style="line-height: 1.2;">{{ $sv['tenDeTai'] }}</td>
                <td>{{ \App\Services\ThongKeService::formatLecturerName($sv['tenGVHD'] ?? $sv['gvhd'] ?? null) }}</td>
                <td>{{ $sv['tenHoiDong'] }}</td>
                <td class="text-center">{{ $sv['diemHD'] !== null ? number_format($sv['diemHD'], 2) : 'Đang chấm' }}</td>
                <td class="text-center text-warning text-bold">Chưa đủ phiếu</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center text-muted" style="padding: 15px;">Toàn bộ sinh viên đã hoàn thành chấm điểm đủ các phiếu.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
@endsection
