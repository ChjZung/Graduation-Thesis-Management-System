@extends('layouts.admin')
@section('page_title', 'Danh Sách Ngành')
@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('import_result'))
<div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-circle-info me-2"></i>{!! session('import_result') !!}
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
        <span><i class="fa-solid fa-book-open text-primary me-2"></i> Quản Lý Ngành</span>
        <a href="{{ route('nganh.create') }}" class="btn btn-success btn-sm rounded-pill px-3">
            <i class="fa-solid fa-plus me-1"></i> Thêm Mới Ngành
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th width="15%">Mã Ngành</th>
                    <th width="35%">Tên Ngành</th>
                    <th width="35%">Mô Tả</th>
                    <th width="15%" class="text-center">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($nganhs as $item)
                <tr>
                    <td><span class="badge bg-light text-dark fw-bold border">{{ $item->MaNganh }}</span></td>
                    <td class="fw-medium">{{ $item->TenNganh }}</td>
                    <td class="text-muted">{{ $item->MoTa ?? 'Không có mô tả' }}</td>
                    <td class="text-center">
                        <a href="{{ route('nganh.edit', $item->MaNganh) }}" class="btn btn-sm btn-light text-primary rounded-circle" title="Sửa"><i class="fa-solid fa-pen"></i></a>
                        <form action="{{ route('nganh.destroy', $item->MaNganh) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa ngành này?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle" title="Xóa"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">
                        <i class="fa-solid fa-folder-open fs-1 text-light mb-3 d-block"></i>
                        Chưa có dữ liệu Ngành
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($nganhs, 'hasPages') && $nganhs->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $nganhs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection