@extends('layouts.admin')

@section('page_title', 'Xét Duyệt Đơn Đăng Ký Đề Tài')

@section('content')

{{-- ── TIÊU ĐỀ TRANG ── --}}
<div class="page-header-flex mb-3">
    <div>
        <h4 class="page-main-title">
            <i class="fa-solid fa-user-check text-primary"></i>
            Xét Duyệt Đơn Đăng Ký Đề Tài Của Sinh Viên
        </h4>
        <p class="page-main-subtitle">
            Phê duyệt nhóm sinh viên đăng ký đề tài, kích hoạt đề tài chính thức và đồng bộ với GVHD.
        </p>
    </div>
</div>

{{-- ── 4 PILL TABS TRẠNG THÁI ── --}}
<div class="admin-pill-tabs">
    <a href="{{ route('admin.duyet_dangky.index', ['TrangThai' => 'Chờ duyệt']) }}" 
       class="admin-pill-tab {{ request('TrangThai', 'Chờ duyệt') === 'Chờ duyệt' ? 'active' : '' }}">
        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#f59e0b;"></span>
        <span class="fw-bold">{{ $counts['cho_duyet'] ?? 0 }}</span> Đơn Chờ Duyệt
    </a>
    <a href="{{ route('admin.duyet_dangky.index', ['TrangThai' => 'Đã duyệt']) }}" 
       class="admin-pill-tab {{ request('TrangThai') === 'Đã duyệt' ? 'active' : '' }}">
        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#10b981;"></span>
        <span class="fw-bold">{{ $counts['da_duyet'] ?? 0 }}</span> Đã Phê Duyệt
    </a>
    <a href="{{ route('admin.duyet_dangky.index', ['TrangThai' => 'Từ chối']) }}" 
       class="admin-pill-tab {{ request('TrangThai') === 'Từ chối' ? 'active' : '' }}">
        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#ef4444;"></span>
        <span class="fw-bold">{{ $counts['tu_choi'] ?? 0 }}</span> Đã Từ Chối
    </a>
    <a href="{{ route('admin.duyet_dangky.index', ['TrangThai' => 'ALL']) }}" 
       class="admin-pill-tab {{ request('TrangThai') === 'ALL' ? 'active' : '' }}">
        <span class="fw-bold">{{ $counts['total'] ?? 0 }}</span> Tất Cả Đơn
    </a>
</div>

{{-- ── BẢNG DANH SÁCH ĐƠN ĐĂNG KÝ ── --}}
<div class="admin-table-card">
    <div class="admin-table-header">
        <h5 class="admin-table-header-title">
            <i class="fa-regular fa-clipboard text-primary"></i>
            Danh Sách Đơn Đăng Ký Đề Tài Của Nhóm Sinh Viên
        </h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.ketqua.export') }}" class="btn btn-sm btn-outline-primary rounded-3 px-3 fw-semibold">
                <i class="fa-solid fa-file-excel me-1 text-success"></i> Xuất Excel
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width: 200px;">Tên Nhóm SV</th>
                    <th style="min-width: 320px;">Đề Tài Đăng Ký</th>
                    <th style="min-width: 200px;">GV Hướng Dẫn</th>
                    <th class="text-center" style="width: 140px;">Trạng Thái</th>
                    <th class="text-center" style="width: 160px;">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dangKys as $dk)
                <tr>
                    {{-- Nhóm SV --}}
                    <td>
                        <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                            {{ $dk->nhom->TenNhom ?? $dk->MaNhom }}
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.76rem;">
                            <i class="fa-solid fa-user-tag me-1 text-primary"></i>
                            Trưởng nhóm: <strong>{{ $dk->nhom->truongNhom->HoTen ?? 'Chưa rõ' }}</strong>
                        </div>
                        <div class="text-muted" style="font-size: 0.74rem;">
                            Thành viên: {{ $dk->nhom->sinhViens->count() ?: 1 }}/3 SV
                        </div>
                    </td>

                    {{-- Đề tài --}}
                    <td>
                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">
                            {{ $dk->deTai->TenDeTai ?? 'Chưa có tên đề tài' }}
                        </div>
                        <div class="mt-1">
                            <span class="badge-code">Mã ĐT: {{ $dk->MaDeTai }}</span>
                        </div>
                    </td>

                    {{-- GVHD --}}
                    <td>
                        <div class="fw-bold text-dark" style="font-size: 0.86rem;">
                            {{ $dk->deTai->giangVien->HocHamHocVi ? $dk->deTai->giangVien->HocHamHocVi . ' ' : '' }}{{ $dk->deTai->giangVien->HoTen ?? 'Chưa rõ' }}
                        </div>
                        <div class="text-muted" style="font-size: 0.74rem;">
                            Bộ môn: {{ $dk->deTai->giangVien->boMon->TenBoMon ?? 'CNPM' }}
                        </div>
                    </td>

                    {{-- Trạng thái --}}
                    <td class="text-center">
                        @if($dk->TrangThai === 'Đã duyệt')
                            <span class="badge-status-green">Đã phê duyệt</span>
                        @elseif($dk->TrangThai === 'Từ chối')
                            <span class="badge-status-red">Đã từ chối</span>
                        @else
                            <span class="badge-status-amber">Chờ phê duyệt</span>
                        @endif
                    </td>

                    {{-- Thao tác --}}
                    <td class="text-center">
                        @if($dk->TrangThai === 'Chờ duyệt')
                        <div class="d-inline-flex gap-1">
                            <form action="{{ route('admin.duyet_dangky.approve', $dk->MaDangKy) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success rounded-3 px-2 py-1 fw-semibold" style="font-size: 0.78rem;" onclick="return confirm('Xác nhận duyệt đề tài này cho nhóm SV?');">
                                    <i class="fa-solid fa-check me-1"></i>Duyệt
                                </button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1 fw-semibold" style="font-size: 0.78rem;" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $dk->MaDangKy }}">
                                Từ Chối
                            </button>
                        </div>

                        <!-- Modal Reject -->
                        <div class="modal fade" id="rejectModal{{ $dk->MaDangKy }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('admin.duyet_dangky.reject', $dk->MaDangKy) }}" method="POST">
                                    @csrf
                                    <div class="modal-content rounded-4 border-0 shadow text-start">
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Từ Chối Đơn Đăng Ký</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body py-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Lý Do Từ Chối <span class="text-danger">*</span></label>
                                                <textarea name="LyDoTuChoi" class="form-control" rows="3" placeholder="Nhập lý do từ chối để Nhóm SV chọn đề tài khác..." required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 pt-0">
                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Xác Nhận Từ Chối</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @else
                            <span class="text-muted" style="font-size: 0.78rem;">Đã xử lý</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="fa-solid fa-user-check fa-2x mb-2 d-block opacity-50"></i>
                        Không có đơn đăng ký đề tài nào trong danh sách.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($dangKys->hasPages())
    <div class="px-3 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #ffffff;">
        <div class="text-muted" style="font-size: 0.82rem;">
            Hiển thị <strong>{{ $dangKys->firstItem() ?? 1 }}–{{ $dangKys->lastItem() ?? $dangKys->count() }}</strong> trên tổng số <strong>{{ $dangKys->total() }}</strong> đơn đăng ký
        </div>
        <div>
            {{ $dangKys->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>

@endsection
