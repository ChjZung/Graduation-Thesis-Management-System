@extends('layouts.admin')

@section('page_title', 'Xét Duyệt Đề Tài Khóa Luận')

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
        <span><i class="fa-solid fa-file-signature text-primary me-2"></i>Xét Duyệt Đề Tài Do Giảng Viên Đề Xuất</span>
        <div class="btn-group">
            <a href="{{ route('admin.duyet_detai.index', ['TrangThai' => 'Chờ duyệt']) }}" class="btn btn-sm {{ request('TrangThai', 'Chờ duyệt') === 'Chờ duyệt' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }}">Chờ Duyệt</a>
            <a href="{{ route('admin.duyet_detai.index', ['TrangThai' => 'Đã duyệt']) }}" class="btn btn-sm {{ request('TrangThai') === 'Đã duyệt' ? 'btn-success fw-bold' : 'btn-outline-secondary' }}">Đã Duyệt</a>
            <a href="{{ route('admin.duyet_detai.index', ['TrangThai' => 'Từ chối']) }}" class="btn btn-sm {{ request('TrangThai') === 'Từ chối' ? 'btn-danger fw-bold' : 'btn-outline-secondary' }}">Từ Chối</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th width="10%">Mã</th>
                        <th width="32%">Tên Đề Tài</th>
                        <th width="20%">Giảng Viên Đề Xuất</th>
                        <th width="10%" class="text-center">Số SV</th>
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
                            <div class="small text-muted">{{ Str::limit($dt->MoTa, 80) }}</div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $dt->giangVien->HoTen ?? 'Chưa rõ' }}</div>
                            <div class="small text-muted">{{ $dt->giangVien->HocVi ?? '' }}</div>
                        </td>
                        <td class="text-center fw-bold">{{ $dt->SoLuongSinhVienToiDa }} SV</td>
                        <td class="text-center">
                            @if($dt->TrangThai === 'Đã duyệt')
                                <span class="badge bg-success rounded-pill px-3">Đã duyệt</span>
                            @elseif($dt->TrangThai === 'Từ chối')
                                <span class="badge bg-danger rounded-pill px-3">Từ chối</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill px-3">Chờ duyệt</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($dt->TrangThai === 'Chờ duyệt')
                            <form action="{{ route('admin.duyet_detai.approve', $dt->MaDeTai) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 me-1" onclick="return confirm('Xác nhận phê duyệt đề tài này?');">
                                    <i class="fa-solid fa-check me-1"></i>Duyệt
                                </button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $dt->MaDeTai }}">
                                Từ Chối
                            </button>

                            <!-- Modal Reject -->
                            <div class="modal fade" id="rejectModal{{ $dt->MaDeTai }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ route('admin.duyet_detai.reject', $dt->MaDeTai) }}" method="POST">
                                        @csrf
                                        <div class="modal-content text-start">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">Từ Chối Đề Tài</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Lý Do Từ Chối <span class="text-danger">*</span></label>
                                                    <textarea name="LyDoTuChoi" class="form-control" rows="3" placeholder="Nhập lý do từ chối để Giảng viên điều chỉnh..." required></textarea>
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
                        <td colspan="6" class="text-center py-4 text-muted">Không có đề tài nào cần xử lý.</td>
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
