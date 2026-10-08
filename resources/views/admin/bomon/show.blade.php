@extends('layouts.admin')

@section('page_title', 'Chi Tiết Bộ Môn - ' . $bomon->TenBoMon)

@section('content')
<div class="container-fluid px-0">
    @if(session('import_result'))
        @php
            $alertType = session('import_alert_type') ?? (session('import_danger') ? 'import_danger' : (session('import_warning') ? 'import_warning' : 'import_result'));
            $alertClass = ($alertType === 'import_danger') ? 'alert-danger' : (($alertType === 'import_warning') ? 'alert-warning' : 'alert-success');
            $borderColor = ($alertType === 'import_danger') ? '#dc3545' : (($alertType === 'import_warning') ? '#f59e0b' : '#198754');
            $bgColor = ($alertType === 'import_danger') ? '#fef2f2' : (($alertType === 'import_warning') ? '#fffbeb' : '#f0fdf4');
        @endphp
        <div class="alert {{ $alertClass }} alert-dismissible fade show mb-3 p-3 shadow-sm rounded-3 border-0" role="alert" style="border-left: 5px solid {{ $borderColor }} !important; background-color: {{ $bgColor }};">
            {!! session('import_result') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Breadcrumb & Actions Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bomon.index') }}" class="text-decoration-none">Danh Mục Bộ Môn</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $bomon->MaBoMon }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-building text-primary"></i>
                <span>{{ $bomon->TenBoMon }}</span>
                <span class="badge-code ms-2">{{ $bomon->MaBoMon }}</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('bomon.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay Lại
            </a>
            <a href="{{ route('bomon.edit', $bomon->MaBoMon) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-pen me-1"></i> Chỉnh Sửa
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards: Cân đối, đồng đều, icon chuẩn, typography đồng nhất -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tổng Giảng Viên</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #e0f2fe; color: #0284c7;">
                        <i class="fa-solid fa-chalkboard-user fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $stats['total_gv'] }}</span>
                    <span class="small text-muted fw-medium">biên chế bộ môn</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">GV Hướng Dẫn</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #dcfce7; color: #16a34a;">
                        <i class="fa-solid fa-user-check fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-success lh-1">{{ $stats['gv_huong_dan'] }}</span>
                    <span class="small text-muted fw-medium">giảng viên nhận đề tài</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tổng Số Đề Tài</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fef3c7; color: #d97706;">
                        <i class="fa-solid fa-file-signature fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $stats['total_detai'] }}</span>
                    <span class="small text-muted fw-medium">đề tài tốt nghiệp</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Khoa Trực Thuộc</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #e0e7ff; color: #4f46e5;">
                        <i class="fa-solid fa-building-columns fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between gap-1 mt-auto">
                    <span class="fs-6 fw-bold text-primary lh-1 text-truncate" title="{{ $bomon->khoa->TenKhoa ?? 'Chưa rõ' }}">
                        {{ $bomon->khoa->TenKhoa ?? 'Chưa rõ' }}
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 small fw-semibold">
                        {{ $bomon->MaKhoa }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Left: Giảng viên trực thuộc table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-chalkboard-user text-primary"></i>
                        <span>Đội Ngũ Giảng Viên Trực Thuộc</span>
                    </h6>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addLecturerModal">
                        <i class="fa-solid fa-user-plus me-1"></i> Thêm Giảng Viên
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3" width="15%">MÃ GV</th>
                                <th class="py-3">HỌ VÀ TÊN</th>
                                <th class="py-3" width="15%">HỌC VỊ</th>
                                <th class="py-3 text-center" width="15%">ĐỀ TÀI HD</th>
                                <th class="py-3 text-center" width="15%">TRẠNG THÁI</th>
                                <th class="pe-4 py-3 text-center" width="14%">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bomon->giangViens as $gv)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge-code">{{ $gv->MaGV }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $gv->HoTen }}</div>
                                        <div class="small text-muted">{{ $gv->Email ?? 'Chưa cập nhật email' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
                                            {{ $gv->HocVi ?? 'Giảng viên' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-primary border rounded-pill px-3 py-1">
                                            {{ $gv->de_tais_count ?? 0 }} đề tài
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                            {{ $gv->TrangThai ?? 'Đang công tác' }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <a href="{{ route('giangvien.show', $gv->MaGV) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('giangvien.edit', $gv->MaGV) }}" class="btn btn-sm btn-light text-warning btn-action-circle border" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('admin.bomon.remove_lecturer', [$bomon->MaBoMon, $gv->MaGV]) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn gỡ giảng viên {{ $gv->HoTen }} khỏi bộ môn này?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light text-danger btn-action-circle border" title="Gỡ khỏi bộ môn">
                                                <i class="fa-solid fa-user-minus"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-folder-open mb-2" style="font-size: 1.5rem;"></i>
                                        <div>Chưa có giảng viên nào thuộc bộ môn này.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Thông tin Bộ Môn Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-circle-info text-primary me-2"></i>Thông Tin Bộ Môn
                    </h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Mã Bộ Môn</span>
                            <span class="badge-code">{{ $bomon->MaBoMon }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Tên Bộ Môn</span>
                            <span class="fw-bold text-dark text-end">{{ $bomon->TenBoMon }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Khoa Quản Lý</span>
                            <a href="{{ route('khoa.show', $bomon->MaKhoa) }}" class="fw-bold text-primary text-decoration-none">
                                {{ $bomon->khoa->TenKhoa ?? $bomon->MaKhoa }}
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Trưởng Bộ Môn</span>
                            <span class="fw-bold text-primary text-end">{{ $bomon->TruongBoMon ?: 'Chưa cập nhật' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Trạng Thái</span>
                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                <i class="fa-solid fa-circle me-1 small" style="font-size: 0.5rem;"></i> Hoạt động
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Ngày Khởi Tạo</span>
                            <span class="text-dark">{{ $bomon->created_at ? $bomon->created_at->format('d/m/Y') : 'Mặc định' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Hướng dẫn & Lưu ý -->
            <div class="card border-0 shadow-sm rounded-3 bg-light">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-primary mb-2">
                        <i class="fa-solid fa-lightbulb me-1"></i> Nghiệp vụ Khóa luận
                    </h6>
                    <p class="small text-muted mb-0">
                        Giảng viên thuộc bộ môn sẽ đề xuất đề tài thuộc định hướng chuyên ngành của bộ môn và thẩm định đề cương khóa luận sinh viên trước khi gửi lên Hội đồng.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm / Gán Giảng Viên Vào Bộ Môn (Không rời trang, Không đổi sidebar) -->
<div class="modal fade" id="addLecturerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom py-3 bg-light">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-chalkboard-user text-primary"></i>
                    <span>Thêm Giảng Viên Vào Bộ Môn: <span class="text-primary">{{ $bomon->TenBoMon }}</span></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- Nav tabs -->
                <ul class="nav nav-pills nav-fill mb-4 bg-light p-1 rounded-3" id="lecturerTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold py-2" id="assign-tab" data-bs-toggle="tab" data-bs-target="#tabAssign" type="button" role="tab">
                            <i class="fa-solid fa-list-check me-1"></i> 1. Chọn Giảng Viên Có Sẵn
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold py-2" id="import-tab" data-bs-toggle="tab" data-bs-target="#tabImport" type="button" role="tab">
                            <i class="fa-solid fa-file-excel me-1 text-success"></i> 2. Upload Excel Danh Sách
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold py-2" id="create-tab" data-bs-toggle="tab" data-bs-target="#tabCreate" type="button" role="tab">
                            <i class="fa-solid fa-user-plus me-1 text-primary"></i> 3. Tạo Mới Giảng Viên
                        </button>
                    </li>
                </ul>

                <!-- Tab content -->
                <div class="tab-content" id="lecturerTabContent">
                    <!-- Tab 1: Gán Giảng viên có sẵn -->
                    <div class="tab-pane fade show active" id="tabAssign" role="tabpanel">
                        <form action="{{ route('admin.bomon.assign_lecturer', $bomon->MaBoMon) }}" method="POST">
                            @csrf
                            <p class="small text-muted mb-3">
                                <i class="fa-solid fa-circle-info text-primary me-1"></i>
                                Chọn một hoặc nhiều giảng viên trong trường để phân bổ biên chế vào bộ môn <strong>{{ $bomon->TenBoMon }}</strong>.
                            </p>
                            @if(isset($otherGiangViens) && $otherGiangViens->count() > 0)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Danh Sách Giảng Viên Khả Dụng <span class="text-danger">*</span></label>
                                    <div class="border rounded-3 p-3 bg-white" style="max-height: 280px; overflow-y: auto;">
                                        @foreach($otherGiangViens as $ogv)
                                            <div class="form-check py-2 border-bottom border-light d-flex align-items-center justify-content-between">
                                                <div>
                                                    <input class="form-check-input me-2" type="checkbox" name="MaGV[]" value="{{ $ogv->MaGV }}" id="gv_{{ $ogv->MaGV }}">
                                                    <label class="form-check-label fw-semibold text-dark cursor-pointer" for="gv_{{ $ogv->MaGV }}">
                                                        {{ $ogv->HocVi ? $ogv->HocVi . '. ' : '' }}{{ $ogv->HoTen }}
                                                        <span class="badge-code ms-1">{{ $ogv->MaGV }}</span>
                                                    </label>
                                                </div>
                                                <span class="small text-muted">
                                                    @if($ogv->boMon)
                                                        <span class="badge bg-light text-secondary border">Hiện thuộc: {{ $ogv->boMon->TenBoMon }}</span>
                                                    @else
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Chưa phân bổ bộ môn</span>
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="form-text small text-muted mt-1">Đánh dấu chọn các giảng viên bạn muốn chuyển / gán vào bộ môn này.</div>
                                </div>
                                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                                        <i class="fa-solid fa-check me-1"></i> Lưu &amp; Phân Bổ Vào Bộ Môn
                                    </button>
                                </div>
                            @else
                                <div class="alert alert-light border text-center py-4 rounded-3">
                                    <i class="fa-solid fa-check-circle text-success fs-3 mb-2 d-block"></i>
                                    <div class="fw-semibold text-dark">Tất cả giảng viên trong trường đã được phân bổ vào các bộ môn.</div>
                                    <div class="small text-muted mt-1">Nếu có giảng viên mới, vui lòng tải file Excel lên hoặc tạo mới hồ sơ.</div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                                </div>
                            @endif
                        </form>
                    </div>

                    <!-- Tab 2: Upload Excel -->
                    <div class="tab-pane fade" id="tabImport" role="tabpanel">
                        <form action="{{ route('admin.bomon.import_lecturers', $bomon->MaBoMon) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <p class="small text-muted mb-3">
                                <i class="fa-solid fa-file-excel text-success me-1"></i>
                                Nhập danh sách giảng viên thuộc bộ môn <strong>{{ $bomon->TenBoMon }}</strong> bằng file Excel (.xlsx, .xls, .csv).
                            </p>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Chọn File Excel <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            </div>
                            <div class="alert alert-info py-2 px-3 small rounded-3 border-0 mb-4">
                                <i class="fa-solid fa-circle-info me-1"></i>
                                Tải file mẫu chuẩn: <a href="{{ route('admin.import.template', 'giangvien') }}" class="fw-bold text-decoration-underline text-primary">Template Giảng Viên</a>
                                (Cột <code>MaBoMon</code> điền <code>{{ $bomon->MaBoMon }}</code>).
                            </div>
                            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                                    <i class="fa-solid fa-upload me-1"></i> Bắt Đầu Import Excel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab 3: Tạo Mới Giảng Viên -->
                    <div class="tab-pane fade" id="tabCreate" role="tabpanel">
                        <div class="text-center py-4 px-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; background: #e0f2fe; color: #0284c7;">
                                <i class="fa-solid fa-user-plus fs-4"></i>
                            </div>
                            <h6 class="fw-bold text-dark">Thêm Mới Giảng Viên Toàn Phần</h6>
                            <p class="text-muted small mx-auto mb-4" style="max-width: 480px;">
                                Dành cho trường hợp giảng viên mới tuyển dụng chưa có hồ sơ và tài khoản trong hệ thống. Hệ thống sẽ mở trang tạo hồ sơ chi tiết và tự động điền sẵn Bộ môn là <strong>{{ $bomon->TenBoMon }}</strong>.
                            </p>
                            <a href="{{ route('giangvien.create') }}?MaBoMon={{ $bomon->MaBoMon }}" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở Trang Tạo Hồ Sơ Giảng Viên Mới
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
