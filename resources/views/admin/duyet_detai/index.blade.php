@extends('layouts.admin')

@section('page_title', 'Xét Duyệt Đề Tài Khóa Luận')

@section('content')

{{-- ── TIÊU ĐỀ TRANG ── --}}
<div class="page-header-flex mb-3">
    <div>
        <h4 class="page-main-title">
            <i class="fa-solid fa-file-signature text-primary"></i>
            Xét Duyệt Danh Sách Đề Tài Khóa Luận (Hệ Thống)
        </h4>
        <p class="page-main-subtitle">
            Phê duyệt danh mục đề xuất từ Giảng viên, đối chiếu chỉ tiêu hướng dẫn và công bố cho Sinh viên đăng ký.
        </p>
    </div>
</div>

{{-- ── 4 PILL TABS TRẠNG THÁI ── --}}
<div class="admin-pill-tabs">
    <a href="{{ route('admin.duyet_detai.index', ['TrangThai' => 'Chờ duyệt']) }}" 
       class="admin-pill-tab {{ request('TrangThai', 'Chờ duyệt') === 'Chờ duyệt' ? 'active' : '' }}">
        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#f59e0b;"></span>
        <span class="fw-bold">{{ $counts['cho_duyet'] ?? 0 }}</span> Chờ Xét Duyệt
    </a>
    <a href="{{ route('admin.duyet_detai.index', ['TrangThai' => 'Đã duyệt']) }}" 
       class="admin-pill-tab {{ request('TrangThai') === 'Đã duyệt' ? 'active' : '' }}">
        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#10b981;"></span>
        <span class="fw-bold">{{ $counts['da_duyet'] ?? 0 }}</span> Đã Phê Duyệt
    </a>
    <a href="{{ route('admin.duyet_detai.index', ['TrangThai' => 'Từ chối']) }}" 
       class="admin-pill-tab {{ request('TrangThai') === 'Từ chối' ? 'active' : '' }}">
        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#ef4444;"></span>
        <span class="fw-bold">{{ $counts['tu_choi'] ?? 0 }}</span> Yêu Cầu Chỉnh Sửa
    </a>
    <a href="{{ route('admin.duyet_detai.index', ['TrangThai' => 'ALL']) }}" 
       class="admin-pill-tab {{ request('TrangThai') === 'ALL' ? 'active' : '' }}">
        <span class="fw-bold">{{ $counts['total'] ?? 0 }}</span> Tổng Đề Xuất
    </a>
</div>

{{-- ── THANH TÌM KIẾM & BỘ LỌC DỮ LIỆU ── --}}
<div class="admin-filter-bar">
    <form method="GET" action="{{ route('admin.duyet_detai.index') }}" class="row g-2 align-items-center">
        <input type="hidden" name="TrangThai" value="{{ request('TrangThai', 'Chờ duyệt') }}">
        <div class="col-12 col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" class="form-control form-control-sm border-start-0" placeholder="Tìm kiếm tên đề tài, mã đề tài, tên GV..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="BoMon" class="form-select form-select-sm">
                <option value="">-- Tất cả Bộ môn --</option>
                <option value="CNPM">Công nghệ phần mềm</option>
                <option value="HTTT">Hệ thống thông tin</option>
                <option value="MMT">Mạng máy tính &amp; An ninh</option>
                <option value="KHMT">Khoa học máy tính &amp; AI</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="LinhVuc" class="form-select form-select-sm">
                <option value="">Lĩnh vực / Chuyên ngành</option>
                <option value="AI">Trí tuệ nhân tạo</option>
                <option value="Web">Phát triển Web</option>
                <option value="Mobile">Ứng dụng Di động</option>
                <option value="Cloud">Điện toán đám mây</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 flex-grow-1 fw-semibold" style="background: #0072ce; border-color: #0072ce;">
                <i class="fa-solid fa-bolt me-1"></i> Lọc Dữ Liệu
            </button>
            <a href="{{ route('admin.duyet_detai.index') }}" class="btn btn-sm btn-light border rounded-3 px-2 text-secondary" title="Tải lại">
                <i class="fa-solid fa-rotate-right"></i>
            </a>
        </div>
    </form>
</div>

{{-- ── BẢNG DANH SÁCH ĐỀ TÀI ── --}}
<div class="admin-table-card">
    <div class="admin-table-header">
        <h5 class="admin-table-header-title">
            <i class="fa-regular fa-file-lines text-primary"></i>
            Danh Sách Đề Tài Đăng Ký Chờ Phê Duyệt
        </h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.ketqua.export') }}" class="btn btn-sm btn-outline-primary rounded-3 px-3 fw-semibold">
                <i class="fa-solid fa-file-excel me-1 text-success"></i> Xuất Danh Sách
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width: 110px;">Mã Đề Tài</th>
                    <th style="min-width: 320px;">Tên Đề Tài &amp; Nội Dung Yêu Cầu</th>
                    <th style="min-width: 220px;">Giảng Viên Đề Xuất</th>
                    <th class="text-center" style="width: 100px;">Chỉ Tiêu</th>
                    <th class="text-center" style="width: 130px;">Trạng Thái</th>
                    <th class="text-center" style="width: 160px;">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detais as $dt)
                <tr>
                    {{-- Mã ĐT --}}
                    <td>
                        <span class="badge-code">{{ $dt->MaDeTai }}</span>
                    </td>

                    {{-- Tên ĐT --}}
                    <td>
                        <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                            {{ $dt->TenDeTai }}
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.76rem;">
                            {{ Str::limit($dt->MoTa, 85) }}
                        </div>
                        <div class="mt-1">
                            <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.7rem;">
                                <i class="fa-solid fa-code me-1 text-primary"></i>Laravel / VueJS / MySQL
                            </span>
                        </div>
                    </td>

                    {{-- GV đề xuất --}}
                    <td>
                        <div class="fw-bold text-dark" style="font-size: 0.86rem;">
                            {{ $dt->giangVien->HocHamHocVi ? $dt->giangVien->HocHamHocVi . ' ' : '' }}{{ $dt->giangVien->HoTen ?? 'Chưa rõ' }}
                        </div>
                        <div class="text-muted" style="font-size: 0.74rem;">
                            Bộ môn: {{ $dt->giangVien->boMon->TenBoMon ?? 'Công nghệ phần mềm' }}
                        </div>
                        <div class="text-primary mt-1" style="font-size: 0.74rem; font-weight: 500;">
                            Định mức: 3/5 nhóm
                        </div>
                    </td>

                    {{-- Chỉ tiêu --}}
                    <td class="text-center fw-bold text-dark" style="font-size: 0.85rem;">
                        {{ $dt->SoLuongSinhVienToiDa ?? 3 }} SV
                    </td>

                    {{-- Trạng thái --}}
                    <td class="text-center">
                        @if($dt->TrangThai === 'Đã duyệt')
                            <span class="badge-status-green">Đã phê duyệt</span>
                        @elseif($dt->TrangThai === 'Từ chối')
                            <span class="badge-status-red">Yêu cầu sửa</span>
                        @else
                            <span class="badge-status-amber">Chờ phê duyệt</span>
                        @endif
                    </td>

                    {{-- Thao tác --}}
                    <td class="text-center">
                        @if($dt->TrangThai === 'Chờ duyệt')
                        <div class="d-inline-flex gap-1">
                            <form action="{{ route('admin.duyet_detai.approve', $dt->MaDeTai) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success rounded-3 px-2 py-1 fw-semibold" style="font-size: 0.78rem;" onclick="return confirm('Xác nhận phê duyệt đề tài này?');">
                                    <i class="fa-solid fa-check me-1"></i>Duyệt
                                </button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1 fw-semibold" style="font-size: 0.78rem;" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $dt->MaDeTai }}">
                                Sửa
                            </button>
                        </div>

                        <!-- Modal Reject -->
                        <div class="modal fade" id="rejectModal{{ $dt->MaDeTai }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('admin.duyet_detai.reject', $dt->MaDeTai) }}" method="POST">
                                    @csrf
                                    <div class="modal-content rounded-4 border-0 shadow text-start">
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Yêu Cầu Chỉnh Sửa Đề Tài</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body py-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nội Dung Cần Điều Chỉnh <span class="text-danger">*</span></label>
                                                <textarea name="LyDoTuChoi" class="form-control" rows="3" placeholder="Nhập chi tiết yêu cầu để Giảng viên cập nhật..." required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 pt-0">
                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Gửi Yêu Cầu</button>
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
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fa-solid fa-file-signature fa-2x mb-2 d-block opacity-50"></i>
                        Không có đề tài nào phù hợp trong danh sách.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($detais->hasPages())
    <div class="px-3 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #ffffff;">
        <div class="text-muted" style="font-size: 0.82rem;">
            Hiển thị <strong>{{ $detais->firstItem() ?? 1 }}–{{ $detais->lastItem() ?? $detais->count() }}</strong> trên tổng số <strong>{{ $detais->total() }}</strong> đề tài
        </div>
        <div>
            {{ $detais->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>

@endsectiontable>
        </div>
    </div>
    @if($detais->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $detais->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
