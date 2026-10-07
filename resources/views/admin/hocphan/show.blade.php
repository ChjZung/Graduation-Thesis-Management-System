@extends('layouts.admin')

@section('page_title', 'Chi Tiết Học Phần')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;"><i class="fa-solid fa-graduation-cap text-primary me-2"></i>Chi Tiết Học Phần</h4>
            <p class="text-muted small mb-0">Học phần: <strong>{{ $hocphan->TenHocPhan }}</strong> ({{ $hocphan->MaHocPhan }})</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('hocphan.edit', $hocphan->MaHocPhan) }}" class="btn btn-warning rounded-pill px-3 fw-semibold">
                <i class="fa-solid fa-pen me-1"></i> Chỉnh sửa
            </a>
            <a href="{{ route('hocphan.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-circle-info me-2 text-primary"></i> Thông Tin Chung</span>
                </div>
                <div class="card-body p-4">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 pb-2 border-bottom">
                            <span class="text-muted d-block small">Mã học phần:</span>
                            <span class="fw-bold fs-5 text-primary">{{ $hocphan->MaHocPhan }}</span>
                        </li>
                        <li class="mb-3 pb-2 border-bottom">
                            <span class="text-muted d-block small">Tên học phần:</span>
                            <span class="fw-semibold text-dark">{{ $hocphan->TenHocPhan }}</span>
                        </li>
                        <li class="mb-3 pb-2 border-bottom">
                            <span class="text-muted d-block small">Số tín chỉ:</span>
                            <span class="badge bg-primary px-3 py-1.5 fs-6">{{ $hocphan->SoTinChi }} Tín chỉ</span>
                        </li>
                        <li class="mb-3 pb-2 border-bottom">
                            <span class="text-muted d-block small">Phân cấp trực thuộc:</span>
                            @if($hocphan->isDungChung())
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold mt-1">
                                    <i class="fa-solid fa-share-nodes me-1"></i> Học phần dùng chung
                                </span>
                            @else
                                <span class="badge bg-info-subtle text-dark border border-info-subtle rounded-pill px-3 py-1.5 fw-semibold mt-1">
                                    <i class="fa-solid fa-layer-group me-1 text-primary"></i> {{ $hocphan->boMon->TenBoMon ?? $hocphan->MaBoMon }}
                                </span>
                            @endif
                            <div class="small text-muted mt-1">Khoa: {{ $hocphan->khoa->TenKhoa ?? 'Khoa CNTT' }}</div>
                        </li>
                        <li class="mb-3 pb-2 border-bottom">
                            <span class="text-muted d-block small">Loại học phần:</span>
                            <span class="badge bg-light text-dark border px-2.5 py-1">{{ $hocphan->LoaiHocPhan }}</span>
                        </li>
                        <li class="mb-3 pb-2 border-bottom">
                            <span class="text-muted d-block small">Trạng thái:</span>
                            <span class="badge bg-success px-2.5 py-1">{{ $hocphan->TrangThai }}</span>
                        </li>
                        @if($hocphan->MoTa)
                        <li>
                            <span class="text-muted d-block small">Mô tả:</span>
                            <p class="small text-dark mb-0 mt-1">{{ $hocphan->MoTa }}</p>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-book-open me-2 text-primary"></i> Đề Tài Thuộc Học Phần Này ({{ $hocphan->de_tais_count }})</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8fafc; font-size: 0.8rem;">
                                <tr>
                                    <th class="ps-3 py-2">Mã ĐT</th>
                                    <th class="py-2">Tên Đề Tài</th>
                                    <th class="py-2">Giảng Viên Hướng Dẫn</th>
                                    <th class="py-2 text-center">Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hocphan->deTais as $dt)
                                    <tr>
                                        <td class="ps-3 py-2"><code>{{ $dt->MaDeTai }}</code></td>
                                        <td class="py-2 fw-semibold">{{ $dt->TenDeTai }}</td>
                                        <td class="py-2 small">{{ $dt->giangVien->HoTen ?? 'Chưa rõ' }}</td>
                                        <td class="py-2 text-center">
                                            <span class="badge bg-info-subtle text-dark border px-2 py-1 small">{{ $dt->TrangThai }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">Chưa có đề tài nào gắn với học phần này.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-users me-2 text-primary"></i> Nhóm Khóa Luận Thuộc Học Phần Này ({{ $hocphan->nhoms_count }})</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8fafc; font-size: 0.8rem;">
                                <tr>
                                    <th class="ps-3 py-2">Mã Nhóm</th>
                                    <th class="py-2">Tên Nhóm</th>
                                    <th class="py-2">Trưởng Nhóm</th>
                                    <th class="py-2 text-center">Học Kỳ</th>
                                    <th class="py-2 text-center">Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hocphan->nhoms as $nhom)
                                    <tr>
                                        <td class="ps-3 py-2"><code>{{ $nhom->MaNhom }}</code></td>
                                        <td class="py-2 fw-semibold">{{ $nhom->TenNhom }}</td>
                                        <td class="py-2 small">{{ $nhom->truongNhom->HoTen ?? 'Chưa rõ' }}</td>
                                        <td class="py-2 text-center small">{{ $nhom->MaHocKy }}</td>
                                        <td class="py-2 text-center">
                                            <span class="badge bg-success-subtle text-success border px-2 py-1 small">{{ $nhom->TrangThai }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">Chưa có nhóm nào đăng ký học phần này.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
