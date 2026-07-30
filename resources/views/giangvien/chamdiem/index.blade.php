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
                <p class="mb-2 fw-bold small text-muted">Sản Phẩm Đã Nộp:</p>
                @foreach($nhom->sanPhams as $sp)
                    <div class="border rounded p-3 mb-3 bg-light shadow-sm">
                        <div class="fw-bold text-dark mb-2" style="word-wrap: break-word;">
                            <i class="fa-solid fa-file-code text-primary me-2"></i>{{ $sp->TenSanPham }}
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-link text-muted"></i></span>
                            <input type="text" class="form-control bg-white text-primary" value="{{ $sp->LinkFile }}" readonly>
                            <a href="{{ $sp->LinkFile }}" target="_blank" class="btn btn-primary"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở</a>
                        </div>
                    </div>
                @endforeach
                
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
