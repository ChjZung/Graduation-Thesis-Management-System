@extends('layouts.sinhvien')
@section('title', 'Nộp Sản Phẩm Cuối Kỳ')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 text-primary-custom"><i class="fa-solid fa-box-open me-2"></i>Nộp Sản Phẩm Cuối Kỳ - {{ $nhom->TenNhom ?? '' }}</h4>
        <small class="text-muted"><i class="fa-solid fa-book me-1"></i>Môn: {{ $nhom->monHoc->TenMon ?? 'N/A' }}</small>
    </div>

    <div class="d-flex gap-2 align-items-center">
        @if(isset($allNhoms) && $allNhoms->count() > 1)
        <form method="GET" action="{{ route('sinhvien.sanpham.index') }}" class="d-flex gap-2 align-items-center me-2">
            <select name="maNhom" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach($allNhoms as $n)
                    <option value="{{ $n->MaNhom }}" {{ $nhom->MaNhom == $n->MaNhom ? 'selected' : '' }}>
                        {{ $n->TenNhom }} ({{ $n->monHoc->TenMon ?? '' }})
                    </option>
                @endforeach
            </select>
        </form>
        @endif

        @if($nhom->TruongNhom == $sv->MaSV)
        <button class="btn btn-primary-custom rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-upload me-2"></i>Nộp Sản Phẩm
        </button>
        @endif
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-premium">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4">Tên Sản Phẩm / Mã Nguồn</th>
                    <th>Link Tải/Xem</th>
                    <th>Ngày Nộp</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sanphams as $sp)
                <tr>
                    <td class="px-4 fw-bold text-muted">{{ $sp->TenSanPham }}</td>
                    <td>
                        @php
                            $fileUrl = $sp->LinkFile && !filter_var($sp->LinkFile, FILTER_VALIDATE_URL) ? asset(ltrim($sp->LinkFile, '/')) : null;
                            $gitUrl = $sp->LinkSourceCode ?? (filter_var($sp->LinkFile, FILTER_VALIDATE_URL) ? $sp->LinkFile : null);
                        @endphp
                        <div class="d-flex gap-2">
                            @if($fileUrl)
                                <a href="{{ $fileUrl }}" download class="btn btn-sm btn-outline-success rounded-pill px-3"><i class="fa-solid fa-download me-1"></i>Tải File</a>
                            @endif
                            @if($gitUrl)
                                <a href="{{ $gitUrl }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3"><i class="fa-brands fa-github me-1"></i>GitHub</a>
                            @endif
                            @if(!$fileUrl && !$gitUrl)
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </td>
                    <td>{{ date('d/m/Y', strtotime($sp->NgayNop)) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">Chưa có sản phẩm nào được nộp.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nộp -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('sinhvien.sanpham.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="MaNhom" value="{{ $nhom->MaNhom }}">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-box-open me-2"></i>Nộp Sản Phẩm / Source Code</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Tên Dự Án/Sản Phẩm <span class="text-danger">*</span></label>
                        <input type="text" name="TenSanPham" class="form-control" required placeholder="Ví dụ: Source code Web App">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">1. Tải Lên File Nén/Tài Liệu (.ZIP, .RAR, .PDF) <span class="text-danger">*</span></label>
                        <input type="file" name="FileUpLoad" class="form-control" accept=".zip,.rar,.pdf" required>
                        <div class="form-text text-muted">Dung lượng tối đa 20MB.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">2. Link GitHub Repository <span class="text-danger">*</span></label>
                        <input type="url" name="LinkFile" class="form-control" placeholder="https://github.com/..." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4">Hoàn Tất</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
