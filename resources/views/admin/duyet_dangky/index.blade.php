@extends('layouts.admin')

@section('page_title', 'Xem & Quản Lý Đăng Ký Đề Tài')

@section('content')

{{-- ── TIÊU ĐỀ TRANG & NÚT XUẤT FILE ── --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h4 class="page-main-title mb-1">
            <i class="fa-solid fa-clipboard-list text-primary me-2"></i>
            Xem & Quản Lý Đăng Ký Đề Tài Khóa Luận
        </h4>
        <p class="page-main-subtitle text-muted mb-0">
            Theo dõi, tra cứu trạng thái đăng ký đề tài của các nhóm sinh viên theo kết quả phê duyệt từ Giảng viên hướng dẫn (GVHD).
        </p>
    </div>
    <div>
        <a href="{{ route('admin.duyet_dangky.export', request()->all()) }}" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-semibold shadow-sm">
            <i class="fa-solid fa-file-excel me-1"></i> Xuất File Excel / CSV
        </a>
    </div>
</div>

{{-- ── PILL TABS TRẠNG THÁI ── --}}
<div class="admin-pill-tabs mb-3">
    <a href="{{ route('admin.duyet_dangky.index', array_merge(request()->except('page'), ['TrangThai' => 'ALL'])) }}" 
       class="admin-pill-tab {{ request('TrangThai', 'ALL') === 'ALL' ? 'active' : '' }}">
        <span class="fw-bold">{{ $counts['total'] ?? 0 }}</span> Tất Cả Đơn
    </a>
    <a href="{{ route('admin.duyet_dangky.index', array_merge(request()->except('page'), ['TrangThai' => 'Chờ duyệt'])) }}" 
       class="admin-pill-tab {{ request('TrangThai') === 'Chờ duyệt' ? 'active' : '' }}">
        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#f59e0b;"></span>
        <span class="fw-bold">{{ $counts['cho_duyet'] ?? 0 }}</span> Chờ GVHD Duyệt
    </a>
    <a href="{{ route('admin.duyet_dangky.index', array_merge(request()->except('page'), ['TrangThai' => 'Đã duyệt'])) }}" 
       class="admin-pill-tab {{ request('TrangThai') === 'Đã duyệt' ? 'active' : '' }}">
        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#10b981;"></span>
        <span class="fw-bold">{{ $counts['da_duyet'] ?? 0 }}</span> GVHD Đã Duyệt
    </a>
    <a href="{{ route('admin.duyet_dangky.index', array_merge(request()->except('page'), ['TrangThai' => 'Từ chối'])) }}" 
       class="admin-pill-tab {{ request('TrangThai') === 'Từ chối' ? 'active' : '' }}">
        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#ef4444;"></span>
        <span class="fw-bold">{{ $counts['tu_choi'] ?? 0 }}</span> GVHD Đã Từ Chối
    </a>
</div>

{{-- ── BỘ LỌC TÌM KIẾM ── --}}
<div class="admin-filter-bar mb-3 p-3 bg-white rounded-3 shadow-sm border">
    <form method="GET" action="{{ route('admin.duyet_dangky.index') }}" class="row g-2 align-items-center">
        @if(request('TrangThai'))
            <input type="hidden" name="TrangThai" value="{{ request('TrangThai') }}">
        @endif

        {{-- Tìm kiếm từ khóa --}}
        <div class="col-12 col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0">
                    <i class="fa-solid fa-magnifying-glass text-muted"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0" 
                       placeholder="Tìm theo Mã ĐT, Tên đề tài, Tên nhóm, MSSV, GVHD..." 
                       value="{{ request('search') }}">
            </div>
        </div>

        {{-- Lọc Học kỳ --}}
        <div class="col-6 col-md-3">
            <select name="MaHocKy" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Tất cả học kỳ --</option>
                @foreach($hocKies as $hk)
                    <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy', $currentHocKy->MaHocKy ?? '') == $hk->MaHocKy ? 'selected' : '' }}>
                        {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Nút bấm --}}
        <div class="col-6 col-md-4 d-flex gap-2 justify-content-end">
            <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3">
                <i class="fa-solid fa-filter me-1"></i> Lọc Dữ Liệu
            </button>
            <a href="{{ route('admin.duyet_dangky.index') }}" class="btn btn-sm btn-light border rounded-3 text-secondary" title="Đặt lại bộ lọc">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>
</div>

{{-- ── BẢNG DANH SÁCH ĐƠN ĐĂNG KÝ ── --}}
<div class="admin-table-card card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-dark mb-0 fs-6">
            <i class="fa-regular fa-clipboard text-primary me-2"></i>
            Danh Sách Đăng Ký Đề Tài Của Nhóm Sinh Viên
        </h5>
        <span class="small text-muted">
            Tổng cộng: <strong>{{ $dangKys->total() }}</strong> đơn đăng ký
        </span>
    </div>

    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 220px;">Nhóm Sinh Viên</th>
                    <th style="min-width: 300px;">Đề Tài Đăng Ký</th>
                    <th style="min-width: 200px;">GV Hướng Dẫn</th>
                    <th class="text-center" style="width: 150px;">Kết Quả Từ GVHD</th>
                    <th class="text-center" style="width: 130px;">Ngày ĐK</th>
                    <th class="text-center" style="width: 110px;">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dangKys as $dk)
                <tr>
                    {{-- Nhóm SV --}}
                    <td>
                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">
                            {{ $dk->nhom->TenNhom ?? $dk->MaNhom }}
                        </div>
                        <div class="text-muted small mt-1" style="font-size: 0.76rem;">
                            TN: <strong>{{ $dk->nhom->truongNhom->HoTen ?? 'Chưa rõ' }}</strong> &bull; {{ $dk->nhom->sinhViens->count() ?: 1 }} SV
                        </div>
                    </td>

                    {{-- Đề tài --}}
                    <td>
                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">
                            {{ $dk->deTai->TenDeTai ?? 'Chưa có tên đề tài' }}
                        </div>
                        <div class="text-muted small mt-1" style="font-size: 0.76rem;">
                            Mã: {{ $dk->MaDeTai }} &bull; {{ $dk->deTai->HocPhan ?? 'Khóa luận tốt nghiệp' }}
                        </div>
                    </td>

                    {{-- GVHD --}}
                    <td>
                        <div class="fw-bold text-dark" style="font-size: 0.86rem;">
                            {{ $dk->deTai->giangVien->HoTen ?? 'Chưa rõ' }}
                        </div>
                        <div class="text-muted small" style="font-size: 0.74rem;">
                            {{ $dk->deTai->giangVien->boMon->TenBoMon ?? 'Bộ môn' }}
                        </div>
                    </td>

                    {{-- Trạng thái phê duyệt của GVHD --}}
                    <td class="text-center">
                        @if($dk->TrangThai === 'Đã duyệt')
                            <span class="badge bg-success text-white rounded-pill px-3 py-1">
                                <i class="fa-solid fa-circle-check me-1"></i>GVHD đã duyệt
                            </span>
                            @if($dk->NgayDuyet)
                                <div class="text-muted small mt-1" style="font-size: 0.72rem;">{{ \Carbon\Carbon::parse($dk->NgayDuyet)->format('d/m/Y') }}</div>
                            @endif
                        @elseif($dk->TrangThai === 'Từ chối')
                            <span class="badge bg-danger text-white rounded-pill px-3 py-1">
                                <i class="fa-solid fa-circle-xmark me-1"></i>GVHD từ chối
                            </span>
                        @else
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1">
                                <i class="fa-solid fa-clock me-1"></i>Chờ GVHD duyệt
                            </span>
                        @endif
                    </td>

                    {{-- Ngày ĐK --}}
                    <td class="text-center text-muted small" style="font-size: 0.78rem;">
                        {{ $dk->created_at ? $dk->created_at->format('d/m/Y') : ($dk->NgayDangKy ?? 'N/A') }}
                    </td>

                    {{-- Thao tác: Giáo vụ chỉ xem chi tiết --}}
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-light border rounded-3 px-2 py-1 text-primary fw-semibold" 
                                data-bs-toggle="modal" data-bs-target="#detailModal{{ $dk->MaDangKy }}" title="Xem chi tiết đăng ký">
                            <i class="fa-solid fa-eye me-1"></i>Chi tiết
                        </button>

                        {{-- MODAL CHI TIẾT ĐĂNG KÝ --}}
                        <div class="modal fade" id="detailModal{{ $dk->MaDangKy }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content rounded-4 border-0 shadow text-start">
                                    <div class="modal-header border-bottom pb-3">
                                        <h5 class="modal-title fw-bold text-primary">
                                            <i class="fa-solid fa-circle-info me-2"></i>Chi Tiết Đơn Đăng Ký #{{ $dk->MaDangKy }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body py-3">
                                        {{-- THÔNG TIN ĐỀ TÀI --}}
                                        <div class="p-3 bg-light rounded-3 mb-3 border">
                                            <div class="small fw-bold text-secondary mb-1">ĐỀ TÀI ĐĂNG KÝ</div>
                                            <h6 class="fw-bold text-dark mb-1">{{ $dk->deTai->TenDeTai ?? 'Chưa rõ' }}</h6>
                                            <div class="small text-muted">
                                                Mã ĐT: <strong>{{ $dk->MaDeTai }}</strong> | 
                                                Học phần: <strong>{{ $dk->deTai->HocPhan ?? 'Khóa luận tốt nghiệp' }}</strong> |
                                                Học kỳ: <strong>{{ $dk->deTai->hocKy->TenHocKy ?? '' }}</strong>
                                            </div>
                                            <div class="small text-muted mt-1">
                                                Giảng viên hướng dẫn: <strong>{{ $dk->deTai->giangVien->HoTen ?? 'Chưa rõ' }}</strong>
                                                ({{ $dk->deTai->giangVien->boMon->TenBoMon ?? '' }})
                                            </div>
                                        </div>

                                        {{-- THÔNG TIN NHÓM SINH VIÊN --}}
                                        <div class="mb-3">
                                            <h6 class="fw-bold text-dark mb-2">
                                                <i class="fa-solid fa-users text-primary me-1"></i> Danh Sách Thành Viên Nhóm ({{ $dk->nhom->TenNhom ?? $dk->MaNhom }})
                                            </h6>
                                            <div class="table-responsive border rounded-3">
                                                <table class="table table-sm table-hover mb-0 align-middle">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>MSSV</th>
                                                            <th>Họ và Tên</th>
                                                            <th>Lớp</th>
                                                            <th>Vai Trò</th>
                                                            <th>SĐT / Email</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($dk->nhom->sinhViens ?? [] as $sv)
                                                        <tr>
                                                            <td class="fw-bold">{{ $sv->MaSV }}</td>
                                                            <td>{{ $sv->HoTen }}</td>
                                                            <td>{{ $sv->lop->TenLop ?? ($sv->MaLop ?? '') }}</td>
                                                            <td>
                                                                @if($sv->pivot && str_contains(mb_strtolower($sv->pivot->VaiTro ?? ''), 'trưởng'))
                                                                    <span class="badge bg-primary text-white">Nhóm trưởng</span>
                                                                @else
                                                                    <span class="badge bg-light text-dark border">Thành viên</span>
                                                                @endif
                                                            </td>
                                                            <td class="small text-muted">
                                                                {{ $sv->SoDienThoai ?? $sv->Email ?? 'N/A' }}
                                                            </td>
                                                        </tr>
                                                        @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted py-2">Chưa có thông tin thành viên nhóm.</td>
                                                        </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        {{-- TRẠNG THÁI PHÊ DUYỆT --}}
                                        <div class="p-3 rounded-3 border {{ $dk->TrangThai === 'Đã duyệt' ? 'bg-success-subtle border-success-subtle' : ($dk->TrangThai === 'Từ chối' ? 'bg-danger-subtle border-danger-subtle' : 'bg-warning-subtle border-warning-subtle') }}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="fw-bold">Trạng thái từ GVHD:</span>
                                                    @if($dk->TrangThai === 'Đã duyệt')
                                                        <span class="text-success fw-bold ms-1"><i class="fa-solid fa-check-circle me-1"></i>Đã phê duyệt chính thức</span>
                                                    @elseif($dk->TrangThai === 'Từ chối')
                                                        <span class="text-danger fw-bold ms-1"><i class="fa-solid fa-times-circle me-1"></i>Đã từ chối đơn đăng ký</span>
                                                    @else
                                                        <span class="text-warning-emphasis fw-bold ms-1"><i class="fa-solid fa-clock me-1"></i>Đang chờ GVHD xem xét</span>
                                                    @endif
                                                </div>
                                                <div class="small text-muted">
                                                    Ngày nộp đơn: {{ $dk->created_at ? $dk->created_at->format('d/m/Y H:i') : ($dk->NgayDangKy ?? 'N/A') }}
                                                </div>
                                            </div>
                                            @if($dk->TrangThai === 'Từ chối' && $dk->LyDoTuChoi)
                                                <div class="mt-2 text-danger small">
                                                    <strong>Lý do GVHD phản hồi:</strong> {{ $dk->LyDoTuChoi }}
                                                </div>
                                            @endif
                                            @if($dk->NgayDuyet)
                                                <div class="mt-1 text-muted small">
                                                    Ngày phê duyệt: {{ \Carbon\Carbon::parse($dk->NgayDuyet)->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top pt-2">
                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fa-regular fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
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
