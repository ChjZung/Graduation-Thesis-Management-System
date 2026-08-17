@extends('layouts.admin')

@section('page_title', 'Chi Tiết Kế Hoạch 5 Mốc Báo Cáo')

@section('content')
<div class="card card-premium mb-4">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-calendar-check text-primary me-2"></i>{{ $keHoach->TenKeHoach }}</span>
        <div>
            <a href="{{ route('admin.kehoach.edit', $keHoach->MaKeHoach) }}" class="btn btn-primary btn-sm rounded-pill me-1 px-3">
                <i class="fa-solid fa-pen me-1"></i>Chỉnh Sửa
            </a>
            <a href="{{ route('admin.kehoach.index') }}" class="btn btn-light border btn-sm rounded-pill px-3">Quay Lại</a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3">
                    <div class="small text-muted text-uppercase fw-bold">Mã Kế Hoạch</div>
                    <div class="fs-6 fw-bold text-dark">{{ $keHoach->MaKeHoach }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3">
                    <div class="small text-muted text-uppercase fw-bold">Học Kỳ</div>
                    <div class="fs-6 fw-bold text-dark">{{ $keHoach->hocKy->TenHocKy ?? $keHoach->MaHocKy }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3">
                    <div class="small text-muted text-uppercase fw-bold">Trạng Thái</div>
                    <span class="badge bg-success rounded-pill px-3 mt-1">{{ $keHoach->TrangThai }}</span>
                </div>
            </div>
        </div>

        <h5 class="fw-bold text-primary-custom mb-3"><i class="fa-solid fa-list-check me-2"></i>DANH SÁCH 5 MỐC THỜI GIAN TRÌNH TỰ CHUẨN</h5>
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="8%">STT</th>
                        <th width="30%">Tên Mốc Thời Gian</th>
                        <th width="20%">Ngày Bắt Đầu</th>
                        <th width="20%">Ngày Hạn Nộp (Kết thúc)</th>
                        <th width="22%">Yêu Cầu Nộp Bài</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($keHoach->mocThoiGians as $idx => $moc)
                    <tr>
                        <td><span class="badge bg-primary rounded-circle px-2 py-1">{{ $idx + 1 }}</span></td>
                        <td class="fw-bold text-primary-custom">{{ $moc->TenMoc }}</td>
                        <td><i class="fa-regular fa-calendar me-1 text-muted"></i>{{ date('d/m/Y', strtotime($moc->NgayBatDau)) }}</td>
                        <td><i class="fa-solid fa-calendar-day me-1 text-danger"></i><strong>{{ date('d/m/Y', strtotime($moc->NgayKetThuc)) }}</strong></td>
                        <td class="small text-secondary">{{ $moc->MoTa ?? 'Theo quy định chung' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-3 text-muted">Chưa có mốc thời gian nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
