@extends($layout)
@section('title', 'Quản Lý Thông Báo')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-primary-custom"><i class="fa-solid fa-bullhorn me-2"></i>Quản Lý Thông Báo</h4>
    <button class="btn btn-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fa-solid fa-plus me-2"></i>Đăng Thông Báo Mới
    </button>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-premium">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4">Ngày đăng</th>
                    <th>Tiêu đề</th>
                    <th>Nội dung</th>
                    <th class="text-end px-4">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($thongbaos as $tb)
                <tr>
                    <td class="px-4 text-muted small"><i class="fa-regular fa-clock me-1"></i> {{ date('d/m/Y', strtotime($tb->NgayTao)) }}</td>
                    <td class="fw-bold">{{ $tb->TieuDe }}</td>
                    <td>{{ Str::limit($tb->NoiDung, 80) }}</td>
                    <td class="text-end px-4">
                        @if($tb->MaTK == Auth::user()->MaTK)
                            @php
                                $destroyRoute = Auth::user()->vaiTro->TenVaiTro === 'Admin' ? route('thongbao.destroy', $tb->MaThongBao) : route('giangvien.thongbao.destroy', $tb->MaThongBao);
                            @endphp
                            <form action="{{ $destroyRoute }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        @else
                            <span class="badge bg-secondary">Từ Ban Quản Trị</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">Bạn chưa đăng thông báo nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Thêm Thông Báo -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        @php
            $storeRoute = Auth::user()->vaiTro->TenVaiTro === 'Admin' ? route('thongbao.store') : route('giangvien.thongbao.store');
        @endphp
        <form action="{{ $storeRoute }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Đăng Thông Báo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Tiêu đề thông báo <span class="text-danger">*</span></label>
                        <input type="text" name="TieuDe" class="form-control" required placeholder="Nhập tiêu đề...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Nội dung <span class="text-danger">*</span></label>
                        <textarea name="NoiDung" class="form-control" rows="5" required placeholder="Nhập nội dung chi tiết..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Đăng Thông Báo</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
