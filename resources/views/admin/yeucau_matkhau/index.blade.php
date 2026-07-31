@extends('layouts.admin')
@section('title', 'Duyệt Quên Mật Khẩu')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-primary-custom"><i class="fa-solid fa-key me-2"></i>Duyệt Yêu Cầu Cấp Lại Mật Khẩu</h4>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-premium">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4">#</th>
                    <th>Tên đăng nhập</th>
                    <th>Vai trò</th>
                    <th>Họ và tên</th>
                    <th>Ngày gửi</th>
                    <th>Trạng thái</th>
                    <th class="text-end px-4">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($yeucaus as $index => $yc)
                <tr>
                    <td class="px-4">{{ $yeucaus->firstItem() + $index }}</td>
                    <td class="fw-bold text-primary">{{ $yc->TenDangNhap }}</td>
                    <td>
                        <span class="badge {{ $yc->Role === 'Giảng viên' ? 'bg-info text-dark' : 'bg-secondary' }}">
                            {{ $yc->Role }}
                        </span>
                    </td>
                    <td>{{ $yc->Email ?? '—' }}</td>
                    <td class="small text-muted">{{ \Carbon\Carbon::parse($yc->NgayGui)->format('H:i d/m/Y') }}</td>
                    <td>
                        @if($yc->TrangThai === 'Chờ duyệt')
                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i>Chờ duyệt</span>
                        @elseif($yc->TrangThai === 'Đã duyệt')
                            <span class="badge bg-success"><i class="fa-solid fa-check-circle me-1"></i>Đã reset 123456</span>
                        @else
                            <span class="badge bg-danger"><i class="fa-solid fa-times-circle me-1"></i>Từ chối</span>
                        @endif
                    </td>
                    <td class="text-end px-4">
                        @if($yc->TrangThai === 'Chờ duyệt')
                            <div class="btn-group btn-group-sm">
                                <form action="{{ route('admin.yeucau.approve', $yc->MaYeuCau) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn reset mật khẩu tài khoản này về 123456?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success px-3">
                                        <i class="fa-solid fa-check me-1"></i>Duyệt (Reset 123456)
                                    </button>
                                </form>
                                <form action="{{ route('admin.yeucau.reject', $yc->MaYeuCau) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('Bạn có chắc muốn từ chối yêu cầu này?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-2">
                                        <i class="fa-solid fa-xmark me-1"></i>Từ chối
                                    </button>
                                </form>
                            </div>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Chưa có yêu cầu cấp lại mật khẩu nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($yeucaus->hasPages())
    <div class="card-footer bg-white py-3">
        {{ $yeucaus->links() }}
    </div>
    @endif
</div>
@endsection
