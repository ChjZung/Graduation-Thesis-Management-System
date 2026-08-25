@extends('layouts.admin')

@section('page_title', 'Quản Lý Khoa')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card card-premium">
        <div class="card-header-premium d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-university text-primary me-2"></i> Quản Lý Khoa</span>
            <a href="{{ route('khoa.create') }}" class="btn btn-success btn-sm rounded-pill px-3 fw-bold">
                <i class="fa-solid fa-plus me-1"></i> Thêm Mới Khoa
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th width="15%">Mã Khoa</th>
                            <th width="45%">Tên Khoa</th>
                            <th width="15%" class="text-center">Số Bộ Môn</th>
                            <th width="15%" class="text-center">Số Ngành</th>
                            <th width="10%" class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($khoas as $khoa)
                            <tr>
                                <td><span class="badge bg-light text-dark fw-bold border">{{ $khoa->MaKhoa }}</span></td>
                                <td class="fw-bold text-primary-custom">{{ $khoa->TenKhoa }}</td>
                                <td class="text-center"><span class="badge bg-info-subtle text-info-emphasis rounded-pill px-3">{{ $khoa->bo_mons_count ?? 0 }} Bộ môn</span></td>
                                <td class="text-center"><span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ $khoa->nganhs_count ?? 0 }} Ngành</span></td>
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
                                <td colspan="5" class="text-center py-4 text-muted">
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
