@extends('layouts.giangvien')

@section('page_title', 'Quản Lý Đề Tài Của Tôi')

@push('styles')
<style>
    /* CSS Tùy chỉnh Layout dạng Card hiện đại, hạn chế ngột ngạt */
    :root {
        --gv-border: #e2e8f0;
        --gv-bg-subtle: #f8fafc;
    }

    .topic-card {
        background: #ffffff;
        border: 1px solid var(--gv-border);
        border-radius: 1rem;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
    }

    .topic-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        border-color: #cbd5e1;
    }

    .topic-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.4;
    }

    .filter-section {
        background: #ffffff;
        border: 1px solid var(--gv-border);
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02);
    }
</style>
@endpush

@section('content')

@if(session('import_result'))
    <div class="alert alert-{{ session('import_alert_type') == 'import_danger' ? 'danger' : (session('import_alert_type') == 'import_warning' ? 'warning' : 'success') }} alert-dismissible fade show shadow-sm mb-3 rounded-3 border-0" role="alert">
        {!! session('import_result') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(!($deXuatState['is_open'] ?? true))
    <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4 p-3 d-flex align-items-center gap-3">
        <i class="fa-solid fa-clock-rotate-left fs-4 text-warning"></i>
        <div>
            <div class="fw-bold text-dark">Cổng Đề Xuất Đề Tài Đã Khóa</div>
            <div class="small text-muted">{{ $deXuatState['message'] ?? 'Hiện đang trong thời gian sinh viên tạo nhóm và đăng ký đề tài. Cổng đề xuất đề tài của Giảng viên đã kết thúc!' }}</div>
        </div>
    </div>
@endif



<!-- HEADER TITLE & ACTIONS -->
<div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="fa-solid fa-folder-open text-primary me-2"></i>Quản Lý Đề Tài Của Tôi
        </h4>
        <p class="text-muted small mb-0">Theo dõi danh sách đề tài đề xuất, trạng thái kiểm duyệt và phân công nhóm sinh viên.</p>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        @if($deXuatState['is_open'] ?? true)
            <button type="button" class="btn btn-outline-success rounded-pill px-3 py-2 shadow-xs fw-medium small d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#importDeTaiModal">
                <i class="fa-solid fa-file-excel me-2"></i> Import Excel
            </button>
            <a href="{{ route('giangvien.detai.create') }}" class="btn btn-success rounded-pill px-3 py-2 shadow-xs fw-medium small d-inline-flex align-items-center">
                <i class="fa-solid fa-plus me-2"></i> Đề Xuất Đề Tài Mới
            </a>
        @else
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 shadow-xs small d-inline-flex align-items-center" disabled title="{{ $deXuatState['message'] ?? 'Đã hết thời hạn đề xuất' }}">
                <i class="fa-solid fa-lock me-2"></i> Import Excel
            </button>
            <button type="button" class="btn btn-secondary rounded-pill px-3 py-2 shadow-xs small d-inline-flex align-items-center" disabled title="{{ $deXuatState['message'] ?? 'Đã hết thời hạn đề xuất' }}">
                <i class="fa-solid fa-lock me-2"></i> Đã Hết Hạn Đề Xuất
            </button>
        @endif
    </div>
</div>

<!-- Filter Bar Quản Lý Đề Tài (Đồng bộ tỷ lệ 9 - 3 và nút khối lớn #1e3a8a với Sinh Viên & Giảng Viên) -->
<div class="admin-filter-bar mb-4 p-3 bg-white border rounded-3 shadow-sm">
    <form action="{{ route('giangvien.detai.index') }}" method="GET" class="w-100">
        <div class="row g-3 align-items-center">
            <!-- BÊN TRÁI: 9 PHẦN -->
            <div class="col-12 col-xl-9">
                <div class="d-flex flex-column gap-2">
                    <!-- Hàng 1: Ô tìm kiếm -->
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0 bg-light" placeholder="Tìm theo tên đề tài..." value="{{ request('search') }}">
                        <button class="btn px-4 fw-semibold text-white" type="submit" style="background-color: #1e3a8a;">Tìm</button>
                    </div>

                    <!-- Hàng 2: Các ô lọc dữ liệu -->
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <select name="MaHocKy" class="form-select form-select-sm" style="width: auto; min-width: 170px; flex: 1;" onchange="this.form.submit()">
                            <option value="">-- Tất cả học kỳ --</option>
                            @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }}
                                </option>
                            @endforeach
                        </select>

                        <select name="TrangThai" class="form-select form-select-sm" style="width: auto; min-width: 180px; flex: 1;" onchange="this.form.submit()">
                            <option value="">-- Tất cả trạng thái --</option>
                            <optgroup label="Nhóm trạng thái chính">
                                <option value="da_phe_duyet" {{ in_array(request('TrangThai'), ['da_phe_duyet', 'Đã phê duyệt', 'da_duyet', 'Đã duyệt']) ? 'selected' : '' }}>Đã phê duyệt</option>
                                <option value="chua_phe_duyet" {{ in_array(request('TrangThai'), ['chua_phe_duyet', 'Chưa phê duyệt', 'cho_duyet', 'Chờ duyệt', 'dang_xet_duyet']) ? 'selected' : '' }}>Chưa phê duyệt / Đang chờ duyệt</option>
                                <option value="yeu_cau_chinh_sua" {{ in_array(request('TrangThai'), ['yeu_cau_chinh_sua', 'yeu_cau_sua', 'Yêu cầu chỉnh sửa']) ? 'selected' : '' }}>Yêu cầu chỉnh sửa</option>
                                <option value="tu_choi" {{ in_array(request('TrangThai'), ['tu_choi', 'Từ chối']) ? 'selected' : '' }}>Từ chối</option>
                                <option value="da_cong_bo" {{ in_array(request('TrangThai'), ['da_cong_bo', 'Đã công bố']) ? 'selected' : '' }}>Đã công bố chính thức</option>
                            </optgroup>
                        </select>

                        @if(request()->hasAny(['search', 'MaHocKy', 'TrangThai']))
                            <a href="{{ route('giangvien.detai.index') }}" class="btn btn-outline-secondary btn-sm text-nowrap d-inline-flex align-items-center">
                                <i class="fa-solid fa-xmark me-2"></i>Xóa lọc
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- BÊN PHẢI: 3 PHẦN (Nút khối lớn Đề xuất đề tài mới #1e3a8a) -->
            <div class="col-12 col-xl-3">
                @if($deXuatState['is_open'] ?? true)
                    <a href="{{ route('giangvien.detai.create') }}" class="btn shadow fw-bold rounded-3 w-100 d-flex flex-column align-items-center justify-content-center text-white text-decoration-none" style="background-color: #1e3a8a; border-color: #1e3a8a; min-height: 82px; padding: 10px 15px;">
                        <span style="font-size: 0.95rem;"><i class="fa-solid fa-plus-circle me-2"></i>Đề Xuất Đề Tài Mới</span>
                    </a>
                @else
                    <div class="btn shadow rounded-3 w-100 d-flex flex-column align-items-center justify-content-center text-white opacity-75" style="background-color: #475569; min-height: 82px; padding: 10px 15px;">
                        <span style="font-size: 0.95rem;"><i class="fa-solid fa-lock me-2"></i>Đã Hết Hạn Đề Xuất</span>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- TOPIC CARDS GRID LAYOUT -->
<div class="row g-4 mb-4">
    @forelse($detais as $dt)
    @php
        $dangKyApproved = $dt->dangKyDeTais->where('TrangThai', 'Đã duyệt')->first();
        $dangKyPending = $dt->dangKyDeTais->where('TrangThai', 'Chờ duyệt')->first();
        $hasGroupAssigned = !empty($dangKyApproved) && !empty($dangKyApproved->nhom);
        $isDaCongBo = ($dt->TrangThai === 'Đã công bố');

        $isProposalApproved = in_array($dt->TrangThai, [
            'Đã công bố', 'Trưởng khoa đã duyệt', 'Đã duyệt',
            'Trưởng khoa đã duyệt - Chờ nộp đề cương',
            'Đã nộp đề cương - Chờ phân công PB',
            'Đang phản biện đề cương', 'Đã cập nhật đề cương - Chờ phản biện lại',
            'Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương',
            'Yêu cầu chỉnh sửa đề cương', 'Hoàn thành'
        ]);

        $phanBien = $dt->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
        $isOutlineFinalized = !empty($dt->FileDeCuong) && $phanBien && $phanBien->KetQua === 'Đạt' && !empty($dt->NgayDuyetBM);
        $isLocked = $isOutlineFinalized || ($dt->TrangThai === 'Hoàn thành');

        $canNopDeCuong = $isProposalApproved && $hasGroupAssigned && !$isLocked;
        $isNopLai = in_array($dt->TrangThai, ['Đã cập nhật đề cương - Chờ phản biện lại', 'Yêu cầu chỉnh sửa đề cương']);
        $canEdit = in_array($dt->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Yêu cầu chỉnh sửa', 'Yêu cầu điều chỉnh']) && !$hasGroupAssigned && $dt->TrangThai !== 'Từ chối';
    @endphp

    <div class="col-12 col-md-6 col-xl-4">
        <div class="topic-card p-4">
            <div>
                <!-- Top Card: Mã đề tài & Trạng thái -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-light text-dark fw-bold border px-3 py-1 font-monospace">
                        {{ $dt->MaDeTai }}
                    </span>

                    <!-- Trạng Thái Badge -->
                    <div>
                        @if($dt->TrangThai === 'Đã công bố')
                            <span class="badge bg-success text-white rounded-pill px-3 py-1 small"><i class="fa-solid fa-bullhorn me-2"></i>Đã công bố</span>
                        @elseif($dt->TrangThai === 'Trưởng khoa đã duyệt - Chờ nộp đề cương')
                            <span class="badge bg-warning-subtle text-warning-emphasis border rounded-pill px-3 py-1 small"><i class="fa-solid fa-file-arrow-up me-2"></i>Chờ nộp ĐC</span>
                        @elseif($dt->TrangThai === 'Đã nộp đề cương - Chờ phân công PB')
                            <span class="badge bg-info-subtle text-info border rounded-pill px-3 py-1 small"><i class="fa-solid fa-file-circle-check me-2"></i>Đã nộp ĐC</span>
                        @elseif($dt->TrangThai === 'Đã cập nhật đề cương - Chờ phản biện lại')
                            <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1 small"><i class="fa-solid fa-rotate me-2"></i>Nộp lại ĐC</span>
                        @elseif($dt->TrangThai === 'Đang phản biện đề cương')
                            <span class="badge bg-info-subtle text-info border rounded-pill px-3 py-1 small"><i class="fa-solid fa-spinner me-2"></i>Đang PB</span>
                        @elseif($dt->TrangThai === 'Đã phản biện - Chờ duyệt BM' || $dt->TrangThai === 'Đã phản biện - Chờ TBM duyệt đề cương')
                            <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1 small"><i class="fa-solid fa-check-double me-2"></i>Chờ TBM</span>
                        @elseif($dt->TrangThai === 'Chờ duyệt cấp Khoa')
                            <span class="badge bg-danger-subtle text-danger border rounded-pill px-3 py-1 small"><i class="fa-solid fa-paper-plane me-2"></i>Chờ Khoa</span>
                        @elseif($dt->TrangThai === 'Chờ duyệt cấp Bộ môn')
                            <span class="badge bg-warning-subtle text-warning-emphasis border rounded-pill px-3 py-1 small"><i class="fa-solid fa-clock me-2"></i>Chờ BM</span>
                        @elseif(in_array($dt->TrangThai, ['Yêu cầu chỉnh sửa đề cương', 'Yêu cầu chỉnh sửa', 'Yêu cầu điều chỉnh']))
                            <span class="badge bg-warning-subtle text-warning-emphasis border rounded-pill px-3 py-1 small"><i class="fa-solid fa-wrench me-2"></i>Cần sửa</span>
                        @elseif($dt->TrangThai === 'Không đạt phản biện' || $dt->TrangThai === 'Từ chối')
                            <span class="badge bg-danger-subtle text-danger border rounded-pill px-3 py-1 small"><i class="fa-solid fa-xmark me-2"></i>Từ chối</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-3 py-1 small">{{ $dt->TrangThai }}</span>
                        @endif
                    </div>
                </div>

                <!-- Tên Đề Tài -->
                <h6 class="topic-title mb-2" title="{{ $dt->TenDeTai }}">
                    {{ $dt->TenDeTai }}
                </h6>

                @if($dt->hasDraft())
                    <div class="mb-2">
                        <span class="badge bg-warning-subtle text-warning-emphasis border px-2 py-1 small">
                            <i class="fa-solid fa-file-pen me-2"></i>Có bản nháp chỉnh sửa
                        </span>
                    </div>
                @endif

                <!-- Meta Badges (Học phần, lĩnh vực, đề cương) -->
                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <span class="badge bg-primary-subtle text-primary border px-2 py-1 small">
                        {{ $dt->HocPhan ?? 'Khóa luận tốt nghiệp' }}
                    </span>
                    <span class="badge bg-light text-secondary border px-2 py-1 small">
                        <i class="fa-regular fa-calendar-days me-2"></i>{{ $dt->hocKy->TenHocKy ?? $dt->MaHocKy }}
                    </span>
                    @if($dt->LinhVuc)
                        <span class="badge bg-light text-secondary border px-2 py-1 small">{{ $dt->LinhVuc }}</span>
                    @endif

                    @if($dt->FileDeCuong)
                        @php
                            $filePath = Str::startsWith($dt->FileDeCuong, ['http', 'storage/']) ? asset($dt->FileDeCuong) : asset('storage/' . $dt->FileDeCuong);
                            $pbDt = $dt->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
                        @endphp
                        <button type="button" class="badge bg-success-subtle text-success border text-decoration-none btn px-2 py-1 small d-inline-flex align-items-center" 
                                onclick="quickPreviewOutline('{{ $filePath }}', '{{ basename($dt->FileDeCuong) }}', '{{ addslashes($dt->TenDeTai) }}')"
                                title="Xem nhanh đề cương chi tiết">
                            <i class="fa-solid fa-paperclip me-2"></i>Đề cương
                        </button>
                        @if(!$pbDt)
                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 small" title="Đề cương đã nộp, chưa phân công phản biện">
                                <i class="fa-solid fa-clock me-2"></i>Chưa phản biện
                            </span>
                        @elseif(empty($pbDt->KetQua) || $pbDt->TrangThai === 'Đang phản biện')
                            <span class="badge bg-info-subtle text-info border px-2 py-1 small" title="Đang thẩm định phản biện">
                                <i class="fa-solid fa-clock me-2"></i>Chưa phản biện
                            </span>
                        @elseif($pbDt->KetQua === 'Đạt')
                            <span class="badge bg-success-subtle text-success border px-2 py-1 small">
                                <i class="fa-solid fa-check me-2"></i>PB Đạt
                            </span>
                        @elseif($pbDt->KetQua === 'Yêu cầu chỉnh sửa')
                            <span class="badge bg-warning-subtle text-warning-emphasis border px-2 py-1 small">
                                <i class="fa-solid fa-wrench me-2"></i>PB yêu cầu sửa
                            </span>
                        @elseif($pbDt->KetQua === 'Không đạt')
                            <span class="badge bg-danger-subtle text-danger border px-2 py-1 small">
                                <i class="fa-solid fa-xmark me-2"></i>PB Không đạt
                            </span>
                        @endif
                    @endif
                </div>

                <!-- Ý kiến phản hồi / Lý do từ chối (nếu có) -->
                @if(in_array($dt->TrangThai, ['Yêu cầu chỉnh sửa', 'Yêu cầu điều chỉnh', 'Yêu cầu chỉnh sửa đề cương', 'Từ chối']) && $dt->LyDoTuChoi)
                    <div class="small text-danger mb-3 p-2 bg-danger-subtle rounded-2 border border-danger-subtle">
                        <strong><i class="fa-solid fa-circle-exclamation me-2"></i>Phản hồi:</strong>
                        <div class="mt-1">{!! \App\Helpers\HtmlHelper::clean($dt->LyDoTuChoi) !!}</div>
                    </div>
                @endif

                <!-- Thông Tin Nhóm Thực Hiện -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="small text-muted mb-1 fw-semibold">Nhóm thực hiện:</div>
                    @if($dangKyApproved && $dangKyApproved->nhom)
                        <div class="fw-bold text-success small"><i class="fa-solid fa-users me-2"></i>{{ $dangKyApproved->nhom->TenNhom }}</div>
                        <div class="text-muted" style="font-size: 0.78rem;">
                            Thành viên: {{ $dangKyApproved->nhom->thanhViens->where('TrangThai', 'da_tham_gia')->count() }}/3 SV
                        </div>
                    @elseif($dangKyPending && $dangKyPending->nhom)
                        <div class="fw-semibold text-warning-emphasis small"><i class="fa-solid fa-clock me-2"></i>{{ $dangKyPending->nhom->TenNhom }} (Đang chờ duyệt)</div>
                    @else
                        <span class="text-muted small fst-italic">Chưa có nhóm đăng ký</span>
                    @endif
                </div>
            </div>

            <!-- Footer Card: Các nút Thao tác -->
            <div class="pt-3 border-top d-flex justify-content-between align-items-center gap-1 flex-wrap">
                <div class="d-flex gap-1">
                    <!-- Xem Chi Tiết -->
                    <a href="{{ route('giangvien.detai.show', $dt->MaDeTai) }}" class="btn btn-sm btn-light border text-primary rounded-circle" title="Xem chi tiết" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-eye"></i>
                    </a>

                    <!-- Sửa Đề Tài -->
                    @if(!$isDaCongBo && !$hasGroupAssigned && $dt->TrangThai !== 'Từ chối')
                    <a href="{{ route('giangvien.detai.edit', $dt->MaDeTai) }}" class="btn btn-sm btn-light border text-secondary rounded-circle" title="Chỉnh sửa đề tài" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    @endif

                    <!-- Xóa Đề Tài -->
                    @if(!$hasGroupAssigned && !in_array($dt->TrangThai, ['Đã duyệt', 'Trưởng khoa đã duyệt', 'Đã công bố', 'Từ chối']))
                    <form action="{{ route('giangvien.detai.destroy', $dt->MaDeTai) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đề tài này?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-light border text-danger rounded-circle" title="Xóa đề tài" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                    @endif
                </div>

                <div class="d-flex gap-1 align-items-center">
                    <!-- Nút Nộp Đề Cương -->
                    @if($canNopDeCuong)
                        <a href="{{ route('giangvien.detai.nop_decuong.show', $dt->MaDeTai) }}" 
                           class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold small shadow-xs">
                            <i class="fa-solid fa-file-arrow-up me-2"></i>{{ $isNopLai ? 'Nộp lại ĐC' : 'Nộp ĐC' }}
                        </a>
                    @elseif($isLocked)
                        <span class="badge bg-light text-muted border py-2 px-2 rounded-2 small" title="Đã khóa nộp đề cương">
                            <i class="fa-solid fa-lock text-secondary me-2"></i>Đã khóa
                        </span>
                    @elseif(in_array($dt->TrangThai, ['Đã công bố', 'Trưởng khoa đã duyệt', 'Đã duyệt']) && !$hasGroupAssigned)
                        <span class="badge bg-warning-subtle text-warning-emphasis border py-2 px-2 rounded-2 small">
                            <i class="fa-solid fa-users-viewfinder me-2"></i>Chờ SV
                        </span>
                    @endif

                    <!-- Gán Nhóm -->
                    @if($dt->TrangThai === 'Đã công bố' && !$hasGroupAssigned)
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-medium small d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalGanNhom{{ $dt->MaDeTai }}">
                            <i class="fa-solid fa-user-check me-2"></i>Gán Nhóm
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL GÁN NHÓM CHO ĐỀ TÀI NÀY -->
    @if($dt->TrangThai === 'Đã công bố' && !$dangKyApproved)
    <div class="modal fade" id="modalGanNhom{{ $dt->MaDeTai }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form action="{{ route('giangvien.detai.ganNhom', $dt->MaDeTai) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white px-4 py-3">
                        <h6 class="modal-title fw-bold">
                            <i class="fa-solid fa-user-check me-2"></i>Gán Nhóm Cho Đề Tài: {{ $dt->TenDeTai }}
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="small text-muted mb-3">
                            Danh sách nhóm thuộc <strong>{{ $dt->hocKy->TenHocKy ?? 'học kỳ này' }}</strong> - Môn <strong>{{ $dt->HocPhan ?? 'Khóa luận' }}</strong> chưa đăng ký đề tài:
                        </p>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Chọn Nhóm Sinh Viên <span class="text-danger">*</span>:</label>
                            @php
                                $allNhoms = $nhomsChuaCoDeTai ?? $NhomChuaCoDeTai ?? collect();
                                $dsNhoms = $allNhoms->filter(function($n) use ($dt) {
                                    if (!empty($dt->MaHocKy) && !empty($n->MaHocKy) && $n->MaHocKy !== $dt->MaHocKy) {
                                        return false;
                                    }
                                    if (!empty($dt->MaHocPhan) && !empty($n->MaHocPhan) && $n->MaHocPhan !== $dt->MaHocPhan) {
                                        return false;
                                    }
                                    $soTV = $n->thanhViens->where('TrangThai', 'da_tham_gia')->count();
                                    return $soTV >= 3;
                                });
                            @endphp
                            <select name="MaNhom" class="form-select" required style="font-size: 13px;">
                                <option value="">-- Chọn nhóm sinh viên --</option>
                                @forelse($dsNhoms as $nhomOption)
                                    @php
                                        $soTV = $nhomOption->thanhViens->where('TrangThai', 'da_tham_gia')->count();
                                    @endphp
                                    <option value="{{ $nhomOption->MaNhom }}">
                                        {{ $nhomOption->TenNhom }} (Trưởng nhóm: {{ $nhomOption->truongNhom->HoTen ?? 'Chưa rõ' }} - {{ $soTV }}/3 SV)
                                    </option>
                                @empty
                                    <option value="" disabled>Không tìm thấy nhóm đủ 3 thành viên phù hợp trong học kỳ này</option>
                                @endforelse
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer px-4 py-3 bg-light border-top">
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 fw-bold shadow-xs d-inline-flex align-items-center">
                            <i class="fa-solid fa-check me-2"></i>Xác Nhận Gán
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
            <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
            <p class="mb-0 fw-medium">Bạn chưa có đề tài nào phù hợp với bộ lọc. Bấm <strong>"Đề Xuất Đề Tài Mới"</strong> để bắt đầu.</p>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($detais->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $detais->links('pagination::bootstrap-5') }}
</div>
@endif

<!-- Modal Import Đề Tài -->
<div class="modal fade" id="importDeTaiModal" tabindex="-1" aria-labelledby="importDeTaiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('giangvien.detai.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-success text-white px-4 py-3">
                    <h5 class="modal-title fs-6 fw-bold" id="importDeTaiModalLabel">
                        <i class="fa-solid fa-file-excel me-2"></i> Import Đề Tài Từ File Excel / CSV
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Tải danh sách đề tài khóa luận tốt nghiệp của bạn từ file Excel (.xlsx) hoặc CSV. Vui lòng sử dụng file mẫu chuẩn để đảm bảo tính toàn vẹn dữ liệu.
                    </p>
                    <div class="mb-3">
                        <label for="importFile" class="form-label fw-semibold small">Chọn tệp (.xlsx, .xls, .csv):</label>
                        <input type="file" name="file" id="importFile" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="small text-muted"><i class="fa-solid fa-download me-2 text-primary"></i> File mẫu chuẩn:</span>
                            <a href="{{ asset('sample_imports/11_DeTai_KhoaLuan_Mau.xlsx') }}" download class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 d-inline-flex align-items-center">
                                <i class="fa-solid fa-file-excel me-2"></i> Tải file mẫu
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3 border-top">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 shadow-sm fw-bold d-inline-flex align-items-center">
                        <i class="fa-solid fa-upload me-2"></i> Tiến Hành Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection