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
                        <th width="10%">Mã Đề Tài</th>
                        <th width="30%">Tên Đề Tài</th>
                        <th width="12%">Lĩnh Vực</th>
                        <th width="20%">Nhóm Thực Hiện</th>
                        <th width="13%" class="text-center">Trạng Thái</th>
                        <th width="15%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detais as $dt)
                    @php
                        $dangKyApproved = $dt->dangKyDeTais->where('TrangThai', 'Đã duyệt')->first();
                        $dangKyPending = $dt->dangKyDeTais->where('TrangThai', 'Chờ duyệt')->first();
                    @endphp
                    <tr>
                        <td><span class="badge bg-light text-dark fw-bold border">{{ $dt->MaDeTai }}</span></td>
                        <td>
                            <div class="fw-bold text-primary-custom">{{ $dt->TenDeTai }}</div>
                            @if($dt->TrangThai === 'Từ chối' && $dt->LyDoTuChoi)
                                <div class="small text-danger mt-1"><i class="fa-solid fa-circle-exclamation me-1"></i>Lý do từ chối: {{ $dt->LyDoTuChoi }}</div>
                            @endif
                        </td>
                        <td><span class="badge bg-light text-secondary border">{{ $dt->LinhVuc ?? 'CNTT' }}</span></td>
                        <td>
                            @if($dangKyApproved && $dangKyApproved->nhom)
                                <div class="fw-bold text-success"><i class="fa-solid fa-users me-1"></i>{{ $dangKyApproved->nhom->TenNhom }}</div>
                                <div class="small text-muted">
                                    {{ $dangKyApproved->nhom->thanhViens->where('TrangThai', 'da_tham_gia')->count() }}/3 SV
                                </div>
                            @elseif($dangKyPending && $dangKyPending->nhom)
                                <div class="fw-semibold text-warning-emphasis"><i class="fa-solid fa-clock me-1"></i>{{ $dangKyPending->nhom->TenNhom }}</div>
                                <div class="small text-muted">(Đang chờ Giáo vụ duyệt)</div>
                            @else
                                <span class="text-muted small">Chưa có nhóm</span>
                            @endif
                        </td>
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
                            <div class="d-flex justify-content-center gap-1">
                                @if($dt->TrangThai === 'Đã duyệt' && !$dangKyApproved)
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" data-bs-toggle="modal" data-bs-target="#modalGanNhom{{ $dt->MaDeTai }}" title="Gán nhóm cho đề tài này">
                                        <i class="fa-solid fa-user-check me-1"></i>Gán Nhóm
                                    </button>
                                @endif
                                <a href="{{ route('giangvien.detai.edit', $dt->MaDeTai) }}" class="btn btn-sm btn-light text-primary rounded-circle" title="Sửa">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                @if(!$dangKyApproved)
                                <form action="{{ route('giangvien.detai.destroy', $dt->MaDeTai) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đề tài này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle" title="Xóa">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- MODAL GÁN NHÓM CHO ĐỀ TÀI NÀY -->
                    @if($dt->TrangThai === 'Đã duyệt' && !$dangKyApproved)
                    <div class="modal fade" id="modalGanNhom{{ $dt->MaDeTai }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('giangvien.detai.ganNhom', $dt->MaDeTai) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h6 class="modal-title fw-bold text-primary">
                                            <i class="fa-solid fa-user-check me-2"></i>Gán Nhóm Cho Đề Tài: {{ $dt->TenDeTai }}
                                        </h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="small text-muted">Chỉ hiển thị các nhóm đã có <strong>đủ 3 thành viên chính thức</strong> và chưa đăng ký đề tài nào.</p>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Chọn Nhóm Sinh Viên:</label>
                                            <select name="MaNhom" class="form-select" required>
                                                <option value="">-- Chọn nhóm sinh viên --</option>
                                                @foreach($nhomsChuaCoDeTai ?? [] as $nhomOption)
                                                    <option value="{{ $nhomOption->MaNhom }}">
                                                        {{ $nhomOption->TenNhom }} (Trưởng nhóm: {{ $nhomOption->truongNhom->HoTen ?? '' }} - 3 SV)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-bold">
                                            <i class="fa-solid fa-check me-1"></i>Xác Nhận Gán
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
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