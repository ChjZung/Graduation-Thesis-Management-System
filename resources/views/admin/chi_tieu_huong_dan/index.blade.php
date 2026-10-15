@extends('layouts.admin')

@section('page_title', 'Chỉ Tiêu Hướng Dẫn Khóa Luận')

@section('content')
<div class="container-fluid px-0" style="font-size: 13.5px;">



    {{-- Tiêu đề trang & Thanh công cụ (Chuẩn G: không shadow) --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small" style="font-size: 13px;">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('giangvien.index') }}" class="text-decoration-none text-muted">Quản lý Giảng viên</a></li>
                    <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Chỉ tiêu hướng dẫn</li>
                </ol>
            </nav>
            <h4 class="fw-bold text-dark mb-1" style="font-size: 1.25rem;">
                Thiết Lập &amp; Giám Sát Chỉ Tiêu Hướng Dẫn Giảng Viên
            </h4>
            <div class="text-muted" style="font-size: 13px;">
                Quản lý số nhóm khóa luận tối đa mỗi giảng viên được phân công hướng dẫn theo từng học kỳ (Định mức mặc định: 5 nhóm).
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-primary btn-sm rounded-2 px-3 fw-medium" data-bs-toggle="modal" data-bs-target="#modalBulkUpdate" style="font-size: 13px;">
                <i class="fa-solid fa-sliders me-1"></i> Thiết lập hàng loạt
            </button>
            <a href="{{ route('giangvien.index') }}" class="btn btn-outline-secondary btn-sm rounded-2 px-3 fw-medium" style="font-size: 13px;">
                <i class="fa-solid fa-users me-1"></i> Hồ sơ giảng viên
            </a>
        </div>
    </div>

    {{-- 4 Thẻ KPI thống kê (Chuẩn G: phẳng, border 1px, không shadow, typography tối thiểu 13px) --}}
    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 rounded-2 border bg-white p-3" style="border-color: #e2e8f0 !important;">
                <div class="text-muted fw-semibold text-uppercase" style="font-size: 13px; letter-spacing: 0.3px;">
                    Tổng số giảng viên
                </div>
                <div class="fs-4 fw-bold text-dark mt-2">
                    {{ $kpiStats['tong_gv'] }}
                </div>
                <div class="text-muted mt-1" style="font-size: 13px;">
                    Đang hiển thị theo bộ lọc
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 rounded-2 border bg-white p-3" style="border-color: #e2e8f0 !important;">
                <div class="text-muted fw-semibold text-uppercase" style="font-size: 13px; letter-spacing: 0.3px;">
                    Đã thiết lập riêng
                </div>
                <div class="fs-4 fw-bold text-primary mt-2">
                    {{ $kpiStats['da_thiet_lap'] }} <span class="fs-6 fw-normal text-muted">/ {{ $kpiStats['tong_gv'] }}</span>
                </div>
                <div class="text-muted mt-1" style="font-size: 13px;">
                    {{ $kpiStats['dung_mac_dinh'] }} GV đang dùng mức mặc định (5 nhóm)
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 rounded-2 border bg-white p-3" style="border-color: #e2e8f0 !important;">
                <div class="text-muted fw-semibold text-uppercase" style="font-size: 13px; letter-spacing: 0.3px;">
                    Đang hướng dẫn
                </div>
                <div class="fs-4 fw-bold text-success mt-2">
                    {{ $kpiStats['dang_huong_dan'] }}
                </div>
                <div class="text-muted mt-1" style="font-size: 13px;">
                    Có ít nhất 1 nhóm sinh viên được duyệt
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 rounded-2 border bg-white p-3" style="border-color: #e2e8f0 !important;">
                <div class="text-muted fw-semibold text-uppercase" style="font-size: 13px; letter-spacing: 0.3px;">
                    Hết / Vượt chỉ tiêu
                </div>
                <div class="fs-4 fw-bold {{ ($kpiStats['het_chi_tieu'] + $kpiStats['vuot_chi_tieu']) > 0 ? 'text-danger' : 'text-dark' }} mt-2">
                    {{ $kpiStats['het_chi_tieu'] + $kpiStats['vuot_chi_tieu'] }}
                </div>
                <div class="text-muted mt-1" style="font-size: 13px;">
                    {{ $kpiStats['het_chi_tieu'] }} đã đủ &bull; {{ $kpiStats['vuot_chi_tieu'] }} vượt định mức
                </div>
            </div>
        </div>
    </div>

    {{-- Bộ lọc & Tìm kiếm (Chuẩn G: Không icon trong select, không icon trong input) --}}
    <div class="card border rounded-2 bg-white mb-3" style="border-color: #e2e8f0 !important;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.chi_tieu_huong_dan.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label mb-1 text-muted fw-medium" style="font-size: 13px;">Học kỳ áp dụng</label>
                        <select name="MaHocKy" class="form-select form-select-sm rounded-2" style="font-size: 13px;" onchange="this.form.submit()">
                            @foreach($hocKys as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy === $hk->MaHocKy ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }} ({{ $hk->MaHocKy }}){{ $hk->TrangThai === 'Đang diễn ra' ? ' - Hiện tại' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label mb-1 text-muted fw-medium" style="font-size: 13px;">Khoa</label>
                        <select name="MaKhoa" class="form-select form-select-sm rounded-2" style="font-size: 13px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả Khoa --</option>
                            @foreach($khoas as $k)
                                <option value="{{ $k->MaKhoa }}" {{ request('MaKhoa') === $k->MaKhoa ? 'selected' : '' }}>
                                    {{ $k->TenKhoa }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label mb-1 text-muted fw-medium" style="font-size: 13px;">Bộ môn</label>
                        <select name="MaBoMon" class="form-select form-select-sm rounded-2" style="font-size: 13px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả Bộ môn --</option>
                            @foreach($bomons as $bm)
                                <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') === $bm->MaBoMon ? 'selected' : '' }}>
                                    {{ $bm->TenBoMon }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label mb-1 text-muted fw-medium" style="font-size: 13px;">Tình trạng chỉ tiêu</label>
                        <select name="tinh_trang" class="form-select form-select-sm rounded-2" style="font-size: 13px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả tình trạng --</option>
                            <option value="con_chi_tieu" {{ request('tinh_trang') === 'con_chi_tieu' ? 'selected' : '' }}>Còn chỉ tiêu</option>
                            <option value="het_chi_tieu" {{ request('tinh_trang') === 'het_chi_tieu' ? 'selected' : '' }}>Đã đủ chỉ tiêu</option>
                            <option value="vuot_chi_tieu" {{ request('tinh_trang') === 'vuot_chi_tieu' ? 'selected' : '' }}>Vượt chỉ tiêu</option>
                            <option value="da_thiet_lap" {{ request('tinh_trang') === 'da_thiet_lap' ? 'selected' : '' }}>Đã thiết lập riêng</option>
                            <option value="dung_mac_dinh" {{ request('tinh_trang') === 'dung_mac_dinh' ? 'selected' : '' }}>Đang dùng mặc định (5)</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label mb-1 text-muted fw-medium" style="font-size: 13px;">Tìm kiếm giảng viên</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control rounded-start-2" placeholder="Nhập tên, mã GV, email..." value="{{ request('search') }}" style="font-size: 13px;">
                            <button type="submit" class="btn btn-primary rounded-end-2 px-3 fw-medium" style="font-size: 13px;">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> Lọc
                            </button>
                            @if(request()->anyFilled(['search', 'MaKhoa', 'MaBoMon', 'tinh_trang']))
                                <a href="{{ route('admin.chi_tieu_huong_dan.index', ['MaHocKy' => $selectedHocKy]) }}" class="btn btn-outline-secondary rounded-2 ms-1 fw-medium" title="Xóa bộ lọc" style="font-size: 13px;">
                                    <i class="fa-solid fa-arrow-rotate-left"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Bảng danh sách Chỉ tiêu giảng viên (Chuẩn G: Phẳng, font >= 13px, không icon trong ô, chỉ icon ở nút thao tác) --}}
    <div class="card border rounded-2 bg-white" style="border-color: #e2e8f0 !important;">
        <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between" style="border-color: #e2e8f0 !important;">
            <div class="fw-semibold text-dark" style="font-size: 13.5px;">
                Danh sách Giảng viên &amp; Định mức hướng dẫn (Học kỳ: {{ $selectedHocKy }})
            </div>
            <div class="text-muted" style="font-size: 13px;">
                Hiển thị {{ $giangviens->firstItem() ?? 0 }} - {{ $giangviens->lastItem() ?? 0 }} trong tổng số {{ $giangviens->total() }} giảng viên
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                <thead class="table-light text-secondary" style="border-bottom: 1px solid #e2e8f0; font-size: 13px;">
                    <tr>
                        <th class="text-center" style="width: 50px;">STT</th>
                        <th style="width: 120px;">Mã GV</th>
                        <th>Họ và tên giảng viên</th>
                        <th>Bộ môn / Khoa</th>
                        <th class="text-center" style="width: 140px;">Chỉ tiêu tối đa</th>
                        <th class="text-center" style="width: 120px;">Đã hướng dẫn</th>
                        <th class="text-center" style="width: 110px;">Còn lại</th>
                        <th class="text-center" style="width: 140px;">Tình trạng</th>
                        <th class="text-center" style="width: 130px;">Cấu hình</th>
                        <th class="text-center" style="width: 160px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($giangviens as $index => $gv)
                        <tr>
                            <td class="text-center text-muted">
                                {{ $giangviens->firstItem() + $index }}
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $gv->MaGV }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $gv->HoTen }}</div>
                                <div class="text-muted" style="font-size: 12.5px;">{{ $gv->HocVi ?? 'Giảng viên' }} &bull; {{ $gv->Email }}</div>
                            </td>
                            <td>
                                <div>{{ $gv->boMon->TenBoMon ?? 'Chưa phân bộ môn' }}</div>
                                <div class="text-muted" style="font-size: 12.5px;">{{ $gv->boMon->khoa->TenKhoa ?? '' }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border px-2 py-1 fw-bold fs-6" style="border-color: #cbd5e1 !important;">
                                    {{ $gv->chi_tieu_tong }} nhóm
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold {{ $gv->da_dung > 0 ? 'text-primary' : 'text-muted' }}">
                                    {{ $gv->da_dung }} nhóm
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold {{ $gv->con_lai > 0 ? 'text-success' : 'text-muted' }}">
                                    {{ $gv->con_lai }} nhóm
                                </span>
                            </td>
                            <td class="text-center">
                                @if($gv->tinh_trang === 'con_chi_tieu')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-medium" style="font-size: 12px;">
                                        Còn chỉ tiêu ({{ $gv->label }})
                                    </span>
                                @elseif($gv->tinh_trang === 'het_chi_tieu')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 fw-medium" style="font-size: 12px;">
                                        Đã đủ chỉ tiêu ({{ $gv->label }})
                                    </span>
                                @elseif($gv->tinh_trang === 'vuot_chi_tieu')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-medium" style="font-size: 12px;">
                                        Vượt chỉ tiêu ({{ $gv->label }})
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($gv->is_custom)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fw-normal" style="font-size: 11.5px;" title="Cập nhật ngày: {{ $gv->ngay_phan_bo }}">
                                        Thiết lập riêng
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fw-normal" style="font-size: 11.5px;">
                                        Mặc định (5)
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    {{-- Nút Thiết lập / Sửa chỉ tiêu --}}
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm rounded-1 px-2 py-1 fw-medium" 
                                            style="font-size: 12.5px;"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalSetQuota"
                                            data-magv="{{ $gv->MaGV }}"
                                            data-hoten="{{ $gv->HoTen }}"
                                            data-bomon="{{ $gv->boMon->TenBoMon ?? '' }}"
                                            data-chitieu="{{ $gv->chi_tieu_tong }}"
                                            data-iscustom="{{ $gv->is_custom ? '1' : '0' }}">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Thiết lập
                                    </button>

                                    {{-- Nút Khôi phục về mặc định nếu đã thiết lập riêng --}}
                                    @if($gv->is_custom)
                                        <form method="POST" action="{{ route('admin.chi_tieu_huong_dan.reset') }}" class="d-inline" onsubmit="return confirm('Khôi phục chỉ tiêu của giảng viên {{ $gv->HoTen }} về mức mặc định 5 nhóm?');">
                                            @csrf
                                            <input type="hidden" name="MaGV" value="{{ $gv->MaGV }}">
                                            <input type="hidden" name="MaHocKy" value="{{ $selectedHocKy }}">
                                            <button type="submit" class="btn btn-outline-secondary btn-sm rounded-1 px-2 py-1" style="font-size: 12.5px;" title="Khôi phục về mặc định hệ thống (5 nhóm)">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                Không tìm thấy giảng viên nào phù hợp với điều kiện tìm kiếm.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Phân trang --}}
        @if($giangviens->hasPages())
            <div class="card-footer bg-white border-top py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-color: #e2e8f0 !important;">
                <div class="text-muted" style="font-size: 13px;">
                    Đang hiển thị trang {{ $giangviens->currentPage() }} / {{ $giangviens->lastPage() }}
                </div>
                <div>
                    {{ $giangviens->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>

</div>

{{-- Modal 1: Thiết lập chỉ tiêu cho 1 giảng viên --}}
<div class="modal fade" id="modalSetQuota" tabindex="-1" aria-labelledby="modalSetQuotaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content rounded-2 border" style="border-color: #cbd5e1 !important;">
            <div class="modal-header py-2 px-3 border-bottom" style="border-color: #e2e8f0 !important; background-color: #f8fafc;">
                <h5 class="modal-title fw-bold text-dark fs-6" id="modalSetQuotaLabel">
                    Thiết Lập Chỉ Tiêu Hướng Dẫn
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
            </div>
            <form method="POST" action="{{ route('admin.chi_tieu_huong_dan.update') }}" id="formSetQuota">
                @csrf
                <input type="hidden" name="MaGV" id="modal_MaGV" value="">
                <input type="hidden" name="MaHocKy" value="{{ $selectedHocKy }}">

                <div class="modal-body p-3" style="font-size: 13.5px;">
                    <div class="mb-3 p-2 rounded-2" style="background-color: #f1f5f9;">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Mã giảng viên:</span>
                            <span class="fw-bold text-dark" id="modal_txtMaGV">---</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Họ và tên:</span>
                            <span class="fw-bold text-dark" id="modal_txtHoTen">---</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Bộ môn:</span>
                            <span class="text-dark" id="modal_txtBoMon">---</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Học kỳ áp dụng:</span>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fw-medium">{{ $selectedHocKy }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_SoNhomToiDa" class="form-label fw-semibold text-dark mb-1">
                            Số nhóm tối đa được hướng dẫn <span class="text-danger">*</span>
                        </label>
                        <input type="number" 
                               name="SoNhomToiDa" 
                               id="modal_SoNhomToiDa" 
                               class="form-control rounded-2" 
                               min="0" 
                               step="1" 
                               required 
                               style="font-size: 14px; font-weight: 600;" 
                               placeholder="Nhập số nguyên >= 0">
                        <div class="form-text text-muted" style="font-size: 12.5px;">
                            Bắt buộc là số nguyên &gt;= 0. Nhập 0 nếu giảng viên không nhận hướng dẫn trong học kỳ này.
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2 px-3 border-top bg-light" style="border-color: #e2e8f0 !important;">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3 fw-medium" data-bs-dismiss="modal" style="font-size: 13px;">
                        Hủy
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-2 px-3 fw-semibold" style="font-size: 13px;">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Lưu chỉ tiêu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 2: Thiết lập chỉ tiêu hàng loạt --}}
<div class="modal fade" id="modalBulkUpdate" tabindex="-1" aria-labelledby="modalBulkUpdateLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content rounded-2 border" style="border-color: #cbd5e1 !important;">
            <div class="modal-header py-2 px-3 border-bottom" style="border-color: #e2e8f0 !important; background-color: #f8fafc;">
                <h5 class="modal-title fw-bold text-dark fs-6" id="modalBulkUpdateLabel">
                    Thiết Lập Chỉ Tiêu Hàng Loạt
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
            </div>
            <form method="POST" action="{{ route('admin.chi_tieu_huong_dan.bulk_update') }}">
                @csrf
                <input type="hidden" name="MaHocKy" value="{{ $selectedHocKy }}">

                <div class="modal-body p-3" style="font-size: 13.5px;">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-medium mb-1">Học kỳ áp dụng</label>
                        <input type="text" class="form-control form-control-sm rounded-2 bg-light text-dark fw-semibold" value="{{ $selectedHocKy }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="bulk_MaBoMon" class="form-label text-dark fw-medium mb-1">Phạm vi Bộ môn</label>
                        <select name="MaBoMon" id="bulk_MaBoMon" class="form-select form-select-sm rounded-2">
                            <option value="">-- Toàn bộ giảng viên tất cả các bộ môn --</option>
                            @foreach($bomons as $bm)
                                <option value="{{ $bm->MaBoMon }}">{{ $bm->TenBoMon }} ({{ $bm->khoa->TenKhoa ?? '' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="bulk_apply_scope" class="form-label text-dark fw-medium mb-1">Đối tượng áp dụng</label>
                        <select name="apply_scope" id="bulk_apply_scope" class="form-select form-select-sm rounded-2">
                            <option value="all">Tất cả giảng viên trong phạm vi đã chọn (Ghi đè)</option>
                            <option value="default_only">Chỉ các giảng viên đang dùng định mức mặc định (Chưa có thiết lập riêng)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="bulk_SoNhomToiDa" class="form-label fw-semibold text-dark mb-1">
                            Số nhóm tối đa áp dụng chung <span class="text-danger">*</span>
                        </label>
                        <input type="number" 
                               name="SoNhomToiDa" 
                               id="bulk_SoNhomToiDa" 
                               class="form-control rounded-2" 
                               value="5" 
                               min="0" 
                               step="1" 
                               required 
                               style="font-size: 14px; font-weight: 600;" 
                               placeholder="Nhập số nguyên >= 0">
                        <div class="form-text text-muted" style="font-size: 12.5px;">
                            Bắt buộc là số nguyên &gt;= 0. Tất cả giảng viên được chọn sẽ nhận mức chỉ tiêu này trong học kỳ {{ $selectedHocKy }}.
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2 px-3 border-top bg-light" style="border-color: #e2e8f0 !important;">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3 fw-medium" data-bs-dismiss="modal" style="font-size: 13px;">
                        Hủy
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-2 px-3 fw-semibold" style="font-size: 13px;" onclick="return confirm('Bạn có chắc chắn muốn cập nhật hàng loạt chỉ tiêu cho giảng viên?');">
                        <i class="fa-solid fa-sliders me-1"></i> Áp dụng hàng loạt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalSetQuota = document.getElementById('modalSetQuota');
    if (modalSetQuota) {
        modalSetQuota.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const maGV = button.getAttribute('data-magv') || '';
            const hoTen = button.getAttribute('data-hoten') || '';
            const boMon = button.getAttribute('data-bomon') || '';
            const chiTieu = button.getAttribute('data-chitieu') || '5';

            document.getElementById('modal_MaGV').value = maGV;
            document.getElementById('modal_txtMaGV').textContent = maGV;
            document.getElementById('modal_txtHoTen').textContent = hoTen;
            document.getElementById('modal_txtBoMon').textContent = boMon || '---';
            document.getElementById('modal_SoNhomToiDa').value = chiTieu;
        });
    }
});
</script>
@endpush
@endsection
