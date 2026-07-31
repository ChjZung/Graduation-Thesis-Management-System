@extends('layouts.giangvien')
@section('title', 'Chấm Điểm Đồ Án')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-primary-custom"><i class="fa-solid fa-gavel me-2"></i>Chấm Điểm Nhóm</h4>
</div>

<div class="row">
    @foreach($nhoms as $nhom)
    <div class="col-md-6 mb-4">
        <div class="card card-premium h-100">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-users text-success me-2"></i>{{ $nhom->TenNhom }}</span>
                @if($nhom->chamDiem)
                <span class="badge bg-success">Điểm Tổng: {{ $nhom->chamDiem->DiemTong }}</span>
                @else
                <span class="badge bg-warning text-dark">Chưa chấm</span>
                @endif
            </div>
            <div class="card-body">
                <p class="mb-2 fw-bold small text-muted"><i class="fa-solid fa-box-archive me-1 text-primary"></i>Sản Phẩm Đã Nộp:</p>
                @forelse($nhom->sanPhams as $sp)
                    <div class="border rounded-3 p-3 mb-3 bg-white shadow-sm border-start border-4 border-primary">
                        <div class="fw-bold text-dark mb-2">
                            <i class="fa-solid fa-folder-closed text-warning me-2"></i>{{ $sp->TenSanPham }}
                            @if($sp->NgayNop)
                            <span class="badge bg-light text-muted border ms-2 small fw-normal">
                                <i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($sp->NgayNop)->format('d/m/Y H:i') }}
                            </span>
                            @endif
                        </div>
                        
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            {{-- 1. File đính kèm tải từ máy --}}
                            @if($sp->LinkFile && !str_starts_with($sp->LinkFile, 'http'))
                                @php
                                    $filePath = asset(ltrim($sp->LinkFile, '/'));
                                @endphp
                                <a href="{{ $filePath }}" target="_blank" download class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="fa-solid fa-download me-1"></i>Tải File Báo Cáo (.pdf/.zip)
                                </a>
                            @elseif($sp->LinkFile && str_starts_with($sp->LinkFile, 'http'))
                                <a href="{{ $sp->LinkFile }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Xem Link File
                                </a>
                            @else
                                <span class="badge bg-light text-muted border py-2 px-3"><i class="fa-solid fa-file-xmark me-1"></i>Chưa nộp file từ máy</span>
                            @endif

                            {{-- 2. Link Source Code / GitHub / Drive --}}
                            @if($sp->LinkSourceCode)
                                <a href="{{ $sp->LinkSourceCode }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                    <i class="fa-brands fa-github me-1"></i>Xem Link Source Code / GitHub
                                </a>
                            @else
                                <span class="badge bg-light text-muted border py-2 px-3"><i class="fa-solid fa-link-slash me-1"></i>Chưa nộp link GitHub</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="alert alert-light border text-muted small py-2 text-center mb-3">
                        <i class="fa-solid fa-circle-info me-1"></i>Nhóm chưa nộp sản phẩm đồ án.
                    </div>
                @endforelse
                
                <hr>
                
                <form action="{{ route('giangvien.chamdiem.store', $nhom->MaNhom) }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Điểm Báo Cáo (50%)</label>
                            <input type="number" step="0.1" name="DiemBaoCao" class="form-control" value="{{ $nhom->chamDiem->DiemBaoCao ?? '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Điểm Bảo Vệ (50%)</label>
                            <input type="number" step="0.1" name="DiemBaoVe" class="form-control" value="{{ $nhom->chamDiem->DiemBaoVe ?? '' }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Nhận xét cuối kỳ</label>
                        <textarea name="NhanXet" class="form-control" rows="2">{{ $nhom->chamDiem->NhanXet ?? '' }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100 rounded-pill">
                        <i class="fa-solid fa-check me-2"></i>{{ $nhom->chamDiem ? 'Cập Nhật Điểm' : 'Chốt Điểm' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
    
    @if($nhoms->count() == 0)
    <div class="col-12">
        <div class="alert alert-info text-center py-4">Chưa có nhóm nào nộp sản phẩm để chấm điểm.</div>
    </div>
    @endif
</div>
@endsection
