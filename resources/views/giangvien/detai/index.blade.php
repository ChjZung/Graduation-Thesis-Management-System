@extends('layouts.giangvien')

@section('page_title', 'Quản Lý Đề Tài Của Tôi')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-premium">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-folder-open text-primary me-2"></i>Danh Sách Đề Tài Đề Xuất</span>
        <a href="{{ route('giangvien.detai.create') }}" class="btn btn-success btn-sm rounded-pill px-3">
            <i class="fa-solid fa-plus me-1"></i> Đề Xuất Đề Tài Mới
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th width="12%">Mã Đề Tài</th>
                        <th width="35%">Tên Đề Tài</th>
                        <th width="15%">Lĩnh Vực</th>
                        <th width="10%" class="text-center">Số SV Tối Đa</th>
                        <th width="13%" class="text-center">Trạng Thái</th>
                        <th width="15%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detais as $dt)
                    <tr>
                        <td><span class="badge bg-light text-dark fw-bold border">{{ $dt->MaDeTai }}</span></td>
                        <td>
                            <div class="fw-bold text-primary-custom">{{ $dt->TenDeTai }}</div>
                            @if($dt->TrangThai === 'Từ chối' && $dt->LyDoTuChoi)
                                <div class="small text-danger mt-1"><i class="fa-solid fa-circle-exclamation me-1"></i>Lý do từ chối: {{ $dt->LyDoTuChoi }}</div>
                            @endif
                        </td>
                        <td><span class="badge bg-light text-secondary border">{{ $dt->LinhVuc ?? 'CNTT' }}</span></td>
                        <td class="text-center fw-bold">{{ $dt->SoLuongSinhVienToiDa }} SV</td>
                        <td class="text-center">
                            @if($dt->TrangThai === 'Đã duyệt')
                                <span class="badge bg-success rounded-pill px-3"><i class="fa-solid fa-check me-1"></i>Đã duyệt</span>
                            @elseif($dt->TrangThai === 'Từ chối')
                                <span class="badge bg-danger rounded-pill px-3"><i class="fa-solid fa-xmark me-1"></i>Từ chối</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill px-3"><i class="fa-solid fa-clock me-1"></i>Chờ duyệt</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('giangvien.detai.edit', $dt->MaDeTai) }}" class="btn btn-sm btn-light text-primary me-1 rounded-circle" title="Sửa">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('giangvien.detai.destroy', $dt->MaDeTai) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đề tài này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle" title="Xóa">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-folder-open fs-1 text-light mb-3 d-block"></i>
                            Bạn chưa đề xuất đề tài nào. Bấm <strong>"Đề Xuất Đề Tài Mới"</strong> để bắt đầu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($detais->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $detais->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection