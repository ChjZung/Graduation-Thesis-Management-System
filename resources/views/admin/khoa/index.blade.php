@extends('layouts.admin')

@section('page_title', 'Danh Sách Khoa')

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
            <span><i class="fa-solid fa-university text-primary me-2"></i> Quản Lý Khoa</span>
            <a href="{{ route('khoa.create') }}" class="btn btn-success btn-sm rounded-pill px-3">
                <i class="fa-solid fa-plus me-1"></i> Thêm Mới Khoa
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="20%">Mã Khoa</th>
                            <th width="60%">Tên Khoa</th>
                            <th width="20%" class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($khoas as $khoa)
                            <tr>
                                <td><span class="badge bg-light text-dark fw-bold border">{{ $khoa->MaKhoa }}</span></td>
                                <td class="fw-medium">{{ $khoa->TenKhoa }}</td>
                                <td class="text-center">
                                    <a href="{{ route('khoa.edit', $khoa->MaKhoa) }}"
                                        class="btn btn-sm btn-light text-primary me-1 rounded-circle" title="Sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('khoa.destroy', $khoa->MaKhoa) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa khoa này?');">
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
                                <td colspan="3" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-folder-open fs-1 text-light mb-3 d-block"></i>
                                    Chưa có dữ liệu Khoa
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($khoas->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $khoas->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
