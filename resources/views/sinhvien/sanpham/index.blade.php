@extends('layouts.sinhvien')
@section('page_title', 'Nộp Sản Phẩm Khóa Luận')
@section('content')

<!-- Header Trang -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="fa-solid fa-box-open text-primary me-2"></i>Nộp Sản Phẩm &amp; Mã Nguồn Khóa Luận
        </h4>
        <p class="text-muted mb-0" style="font-size: 13.5px;">
            Nhóm: <strong>{{ $nhom->TenNhom ?? 'N/A' }}</strong> 
            @if(isset($nhom->monHoc))
                &bull; Học phần: <strong>{{ $nhom->monHoc->TenMon }}</strong>
            @endif
        </p>
    </div>

    <div class="d-flex flex-wrap gap-2 align-items-center">
        @if(isset($allNhoms) && $allNhoms->count() > 1)
        <form method="GET" action="{{ route('sinhvien.sanpham.index') }}" class="d-flex align-items-center">
            <select name="maNhom" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach($allNhoms as $n)
                    <option value="{{ $n->MaNhom }}" {{ ($nhom && $nhom->MaNhom == $n->MaNhom) ? 'selected' : '' }}>
                        {{ $n->TenNhom }} ({{ $n->monHoc->TenMon ?? '' }})
                    </option>
                @endforeach
            </select>
        </form>
        @endif

        @if($nhom && $nhom->TruongNhom == $sv->MaSV)
        <button class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-cloud-arrow-up me-2"></i>Nộp Sản Phẩm
        </button>
        @endif
    </div>
</div>

<!-- Bảng Danh Sách Sản Phẩm -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center gap-2">
        <i class="fa-solid fa-folder-tree text-primary fs-5"></i>
        <h5 class="mb-0 fw-bold text-dark fs-6">Danh Sách Sản Phẩm &amp; Mã Nguồn Đã Nộp</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="45%" class="px-4">Tên Sản Phẩm / Dự Án</th>
                        <th width="35%">Tệp Tin &amp; Liên Kết</th>
                        <th width="20%">Ngày Nộp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sanphams as $sp)
                    <tr>
                        <td class="px-4">
                            <div class="fw-bold text-dark">{{ $sp->TenSanPham }}</div>
                        </td>
                        <td>
                            @php
                                $fileUrl = $sp->LinkFile && !filter_var($sp->LinkFile, FILTER_VALIDATE_URL) ? asset(ltrim($sp->LinkFile, '/')) : null;
                                $gitUrl = $sp->LinkSourceCode ?? (filter_var($sp->LinkFile, FILTER_VALIDATE_URL) ? $sp->LinkFile : null);
                            @endphp
                            <div class="d-flex flex-wrap gap-2">
                                @if($fileUrl)
                                    <a href="{{ $fileUrl }}" download class="btn btn-sm btn-outline-success rounded-pill px-3 d-inline-flex align-items-center">
                                        <i class="fa-solid fa-download me-2"></i>Tải Tệp
                                    </a>
                                @endif
                                @if($gitUrl)
                                    <a href="{{ $gitUrl }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 d-inline-flex align-items-center">
                                        <i class="fa-brands fa-github me-2"></i>GitHub
                                    </a>
                                @endif
                                @if(!$fileUrl && !$gitUrl)
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="text-muted" style="font-size: 13.5px;">
                                <i class="fa-regular fa-calendar-days me-2"></i>{{ date('d/m/Y', strtotime($sp->NgayNop)) }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-box-open fa-2x mb-2 d-block opacity-40 me-2"></i>
                            Chưa có sản phẩm hoặc mã nguồn nào được nộp.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nộp Sản Phẩm -->
@if($nhom)
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('sinhvien.sanpham.store') }}" method="POST" enctype="multipart/form-data" class="w-100">
            @csrf
            <input type="hidden" name="MaNhom" value="{{ $nhom->MaNhom }}">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark fs-6" id="addModalLabel">
                        <i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i>Nộp Sản Phẩm &amp; Mã Nguồn
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 13.5px;">
                            Tên Dự Án / Sản Phẩm <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="TenSanPham" class="form-control" required placeholder="VD: Mã nguồn hệ thống quản lý khóa luận">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 13.5px;">
                            Tải Lên Tệp Nén / Tài Liệu (.zip, .rar, .pdf) <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="FileUpLoad" class="form-control" accept=".zip,.rar,.pdf" required>
                        <div class="form-text text-muted" style="font-size: 13px;">Dung lượng tối đa 20MB.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 13.5px;">
                            Liên Kết GitHub Repository <span class="text-danger">*</span>
                        </label>
                        <input type="url" name="LinkFile" class="form-control" placeholder="https://github.com/..." required>
                        <div class="form-text text-muted" style="font-size: 13px;">Đường dẫn kho mã nguồn dự án công khai hoặc cấp quyền cho GVHD.</div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center">
                        <i class="fa-solid fa-check me-2"></i>Xác Nhận Nộp
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
