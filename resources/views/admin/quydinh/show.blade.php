@extends('layouts.admin')

@section('page_title', 'Chi Tiết Quy Định - ' . $quyDinh->TenQuyDinh)

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.quydinh.index') }}" class="text-decoration-none">Quy Định Khóa Luận</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $quyDinh->MaQuyDinh }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-scale-balanced text-primary"></i>
                <span>{{ $quyDinh->TenQuyDinh }}</span>
                <span class="badge-code ms-2">{{ $quyDinh->MaQuyDinh }}</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.quydinh.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay Lại
            </a>
            <a href="{{ route('admin.quydinh.edit', $quyDinh->MaQuyDinh) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-pen me-1"></i> Chỉnh Sửa
            </a>
        </div>
    </div>

    <div class="row g-4 justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-circle-info text-primary me-2"></i>Nội Dung Tham Số Tiêu Chuẩn
                    </h6>
                    <span class="badge bg-primary fs-6 rounded-pill px-3 py-1">
                        Giá trị: {{ $quyDinh->GiaTri }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div class="text-muted small">Tên Quy Định Đầy Đủ:</div>
                        <h5 class="fw-bold text-dark mt-1">{{ $quyDinh->TenQuyDinh }}</h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <div class="p-3 rounded bg-light">
                                <div class="text-muted small">Kế Hoạch Khóa Luận Áp Dụng:</div>
                                <div class="fw-bold text-dark mt-1">
                                    {{ $quyDinh->keHoachKhoaLuan->TenKeHoach ?? 'Áp dụng chung' }}
                                </div>
                                <div class="small text-muted mt-1">
                                    {{ $quyDinh->keHoachKhoaLuan->hocKy->TenHocKy ?? '' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded bg-light">
                                <div class="text-muted small">Mã Nhận Diện Tham Số:</div>
                                <div class="mt-1"><span class="badge-code fs-6">{{ $quyDinh->MaQuyDinh }}</span></div>
                                <div class="small text-muted mt-1">
                                    Cập nhật: {{ $quyDinh->updated_at ? $quyDinh->updated_at->format('d/m/Y H:i') : 'Mặc định' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="text-muted small mb-2 fw-bold">Mô Tả Chi Tiết &amp; Căn Cứ Điều Khoản:</div>
                        <div class="p-3 rounded bg-light border text-dark" style="line-height: 1.7;">
                            {{ $quyDinh->MoTa ?? 'Không có ghi chú diễn giải bổ sung cho quy định này.' }}
                        </div>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <form action="{{ route('admin.quydinh.destroy', $quyDinh->MaQuyDinh) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa quy định này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                <i class="fa-solid fa-trash me-1"></i> Xóa Quy Định
                            </button>
                        </form>
                        <a href="{{ route('admin.quydinh.edit', $quyDinh->MaQuyDinh) }}" class="btn btn-primary btn-sm rounded-pill px-4">
                            <i class="fa-solid fa-pen me-1"></i> Chỉnh Sửa Nội Dung
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
