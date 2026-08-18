@extends('layouts.admin')

@section('page_title', 'Xét Duyệt Đơn Đăng Ký Đề Tài SV')

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
        <span><i class="fa-solid fa-user-check text-primary me-2"></i>Duyệt Đơn Đăng Ký Đề Tài Của Nhóm Sinh Viên</span>
        <div class="btn-group">
            <a href="{{ route('admin.duyet_dangky.index', ['TrangThai' => 'Chờ duyệt']) }}" class="btn btn-sm {{ request('TrangThai', 'Chờ duyệt') === 'Chờ duyệt' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }}">Chờ Duyệt</a>
            <a href="{{ route('admin.duyet_dangky.index', ['TrangThai' => 'Đã duyệt']) }}" class="btn btn-sm {{ request('TrangThai') === 'Đã duyệt' ? 'btn-success fw-bold' : 'btn-outline-secondary' }}">Đã Duyệt</a>
            <a href="{{ route('admin.duyet_dangky.index', ['TrangThai' => 'Từ chối']) }}" class="btn btn-sm {{ request('TrangThai') === 'Từ chối' ? 'btn-danger fw-bold' : 'btn-outline-secondary' }}">Từ Chối</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th width="18%">Tên Nhóm SV</th>
                        <th width="32%">Đề Tài Đăng Ký</th>
                        <th width="20%">GV Hướng Dẫn</th>
                        <th width="15%" class="text-center">Trạng Thái</th>
                        <th width="15%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dangKys as $dk)
                    <tr>
                        <td>
                            <div class="fw-bold text-primary-custom">{{ $dk->nhom->TenNhom ?? 'Chưa rõ' }}</div>
                            <div class="small text-muted">Trưởng nhóm: {{ $dk->nhom->truongNhom->HoTen ?? '' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $dk->deTai->TenDeTai ?? '' }}</div>
                            <div class="small text-muted">Mã đề tài: {{ $dk->MaDeTai }}</div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $dk->deTai->giangVien->HoTen ?? 'Chưa rõ' }}</div>
                            <div class="small text-muted">{{ $dk->deTai->giangVien->HocVi ?? '' }}</div>
                        </td>
                        <td class="text-center">
                            @if($dk->TrangThai === 'Đã duyệt')
                                <span class="badge bg-success rounded-pill px-3">Đã duyệt</span>
                            @elseif($dk->TrangThai === 'Từ chối')
                                <span class="badge bg-danger rounded-pill px-3">Từ chối</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill px-3">Chờ duyệt</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($dk->TrangThai === 'Chờ duyệt')
                            <form action="{{ route('admin.duyet_dangky.approve', $dk->MaDangKy) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 me-1" onclick="return confirm('Xác nhận duyệt đề tài này cho nhóm SV?');">
                                    <i class="fa-solid fa-check me-1"></i>Duyệt
                                </button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $dk->MaDangKy }}">
                                Từ Chối
                            </button>

                            <!-- Modal Reject -->
                            <div class="modal fade" id="rejectModal{{ $dk->MaDangKy }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ route('admin.duyet_dangky.reject', $dk->MaDangKy) }}" method="POST">
                                        @csrf
                                        <div class="modal-content text-start">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">Từ Chối Đơn Đăng Ký</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Lý Do Từ Chối <span class="text-danger">*</span></label>
                                                    <textarea name="LyDoTuChoi" class="form-control" rows="3" placeholder="Nhập lý do từ chối để Nhóm SV chọn đề tài khác..." required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-danger rounded-pill">Xác Nhận Từ Chối</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @else
                                <span class="text-muted small">Đã xử lý</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Không có đơn đăng ký đề tài nào cần xử lý.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($dangKys->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $dangKys->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
