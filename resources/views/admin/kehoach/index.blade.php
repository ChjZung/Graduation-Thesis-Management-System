@extends('layouts.admin')

@section('page_title', 'Quản Lý Kế Hoạch Khóa Luận')

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
        <span><i class="fa-solid fa-calendar-check text-primary me-2"></i> Kế Hoạch Khóa Luận Tốt Nghiệp</span>
        <a href="{{ route('admin.kehoach.create') }}" class="btn btn-success btn-sm rounded-pill px-3">
            <i class="fa-solid fa-plus me-1"></i> Lập Kế Hoạch Mới & 5 Mốc Báo Cáo
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th width="15%">Mã Kế Hoạch</th>
                        <th width="30%">Tên Kế Hoạch</th>
                        <th width="20%">Học Kỳ</th>
                        <th width="15%">Trạng Thái</th>
                        <th width="20%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($keHoachs as $kh)
                    <tr>
                        <td><span class="badge bg-light text-dark fw-bold border">{{ $kh->MaKeHoach }}</span></td>
                        <td class="fw-bold text-primary">{{ $kh->TenKeHoach }}</td>
                        <td>{{ $kh->hocKy->TenHocKy ?? $kh->MaHocKy }}</td>
                        <td>
                            <span class="badge bg-success rounded-pill">{{ $kh->TrangThai }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.kehoach.show', $kh->MaKeHoach) }}" class="btn btn-sm btn-light text-info me-1 rounded-circle" title="Xem 5 mốc">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.kehoach.edit', $kh->MaKeHoach) }}" class="btn btn-sm btn-light text-primary me-1 rounded-circle" title="Sửa">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('admin.kehoach.destroy', $kh->MaKeHoach) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa kế hoạch này?');">
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
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-calendar-xmark fs-1 text-light mb-3 d-block"></i>
                            Chưa có Kế hoạch Khóa luận nào được lập.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($keHoachs->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $keHoachs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
