@extends('layouts.admin')

@section('page_title', 'Quản Lý Biểu Mẫu Khóa Luận')

@section('content')
<div class="container-fluid px-0">


    <!-- Page Title & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;">Quản Lý Biểu Mẫu Khóa Luận Tốt Nghiệp</h4>
            <p class="text-muted small mb-0">Quản lý và tải lên các file mẫu đăng ký, bìa báo cáo, phiếu nhận xét, phiếu chấm điểm khóa luận cho GV &amp; SV.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.kehoach.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm fw-semibold">
                <i class="fa-solid fa-calendar-days me-1"></i> Xem Kế Hoạch
            </a>
            <button type="button" class="btn btn-primary rounded-pill px-3 py-2 btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateBieuMau">
                <i class="fa-solid fa-plus me-1"></i> Thêm Biểu Mẫu Mới
            </button>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-primary d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Tổng Số Biểu Mẫu</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-dark">{{ $stats['total_bieumau'] ?? 0 }}</span>
                        <span class="small text-muted fw-medium">tệp biểu mẫu</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-file-lines fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-success d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Kế Hoạch Khóa Luận</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-success">{{ $stats['total_kehoach'] ?? 0 }}</span>
                        <span class="small text-muted fw-medium">kế hoạch học kỳ</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #dcfce7; color: #16a34a;">
                    <i class="fa-solid fa-calendar-check fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-warning d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Đang Thực Hiện</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-4 fw-bold text-warning text-truncate" style="max-width: 140px;">
                            {{ $stats['active_plan']->TenKeHoach ?? 'HK2_2627' }}
                        </span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-bullhorn fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-info d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Trạng Thái Truy Cập</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-info">Công Khai</span>
                        <span class="small text-muted fw-medium">100% GV &amp; SV</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0e7ff; color: #4f46e5;">
                    <i class="fa-solid fa-download fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card & Filter -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <!-- Filter Bar -->
            <form method="GET" action="{{ route('admin.bieumau.index') }}" class="row g-3 mb-4">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted">Lọc Theo Kế Hoạch Khóa Luận</label>
                    <select name="make_hoach" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">-- Tất cả Kế hoạch Khóa luận --</option>
                        @foreach($keHoachs as $kh)
                            <option value="{{ $kh->MakeHoach }}" {{ request('make_hoach') == $kh->MakeHoach ? 'selected' : '' }}>
                                {{ $kh->TenKeHoach }} ({{ $kh->hocKy->TenHocKy ?? $kh->MaHocKy }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted">Tìm Kiếm Biểu Mẫu</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control rounded-start-pill" placeholder="Nhập tên biểu mẫu, mã biểu mẫu..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary rounded-end-pill px-3"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    @if(request('make_hoach') || request('search'))
                        <a href="{{ route('admin.bieumau.index') }}" class="btn btn-light rounded-pill w-100 border"><i class="fa-solid fa-rotate-left me-1"></i> Xóa lọc</a>
                    @endif
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle border mb-0">
                    <thead class="table-light text-secondary small text-uppercase">
                        <tr>
                            <th class="text-center" style="width: 50px;">STT</th>
                            <th>Mã Mẫu</th>
                            <th>Tên Biểu Mẫu Hướng Dẫn</th>
                            <th>Kế Hoạch Khóa Luận Áp Dụng</th>
                            <th class="text-center">Tệp Đính Kèm</th>
                            <th class="text-center" style="width: 140px;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bieuMaus as $idx => $bm)
                            <tr>
                                <td class="text-center text-muted fw-bold">{{ $bieuMaus->firstItem() + $idx }}</td>
                                <td><span class="badge bg-light text-dark border fw-semibold">{{ $bm->MaBieuMau }}</span></td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $bm->TenBieuMau }}</div>
                                    <div class="small text-muted">Cập nhật: {{ $bm->updated_at ? $bm->updated_at->format('d/m/Y H:i') : 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-primary">{{ $bm->keHoachKhoaLuan->TenKeHoach ?? $bm->MakeHoach }}</div>
                                    <div class="small text-muted">Học kỳ: {{ $bm->keHoachKhoaLuan->hocKy->TenHocKy ?? '' }}</div>
                                </td>
                                <td class="text-center">
                                    @if($bm->DuongDanFile && $bm->DuongDanFile !== '#')
                                        <a href="{{ asset($bm->DuongDanFile) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                            <i class="fa-solid fa-download me-1"></i> Tải Về
                                        </a>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border px-3 py-1.5 rounded-pill">Chưa có file</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#modalEditBieuMau{{ $bm->MaBieuMau }}" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.bieumau.destroy', $bm->MaBieuMau) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa biểu mẫu này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Xóa">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Modal Edit -->
                                    <div class="modal fade text-start" id="modalEditBieuMau{{ $bm->MaBieuMau }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header bg-light rounded-top-4">
                                                    <h5 class="modal-title fw-bold text-primary">Chỉnh Sửa Biểu Mẫu: {{ $bm->MaBieuMau }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.bieumau.update', $bm->MaBieuMau) }}" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Tên Biểu Mẫu <span class="text-danger">*</span></label>
                                                            <input type="text" name="TenBieuMau" class="form-control" value="{{ $bm->TenBieuMau }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Kế Hoạch Khóa Luận Áp Dụng <span class="text-danger">*</span></label>
                                                            <select name="MakeHoach" class="form-select" required>
                                                                @foreach($keHoachs as $kh)
                                                                    <option value="{{ $kh->MakeHoach }}" {{ $bm->MakeHoach == $kh->MakeHoach ? 'selected' : '' }}>
                                                                        {{ $kh->TenKeHoach }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Thay Đổi File Đính Kèm (.doc, .pdf, .xls, .zip)</label>
                                                            <input type="file" name="File" class="form-control">
                                                            <div class="form-text small">File hiện tại: <code>{{ $bm->DuongDanFile }}</code></div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light rounded-bottom-4">
                                                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Cập Nhật</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary"></i>
                                    <div class="fw-bold">Chưa có biểu mẫu khóa luận nào</div>
                                    <div class="small">Bấm nút <strong>+ Thêm Biểu Mẫu Mới</strong> để tải lên các văn bản mẫu cho Giảng viên &amp; Sinh viên.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $bieuMaus->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Create BieuMau -->
<div class="modal fade" id="modalCreateBieuMau" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus me-2"></i> Thêm Mới Biểu Mẫu Khóa Luận</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.bieumau.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên Biểu Mẫu <span class="text-danger">*</span></label>
                        <input type="text" name="TenBieuMau" class="form-control" placeholder="Ví dụ: Mẫu 01 - Phiếu đăng ký đề tài khóa luận" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kế Hoạch Khóa Luận Áp Dụng <span class="text-danger">*</span></label>
                        <select name="MakeHoach" class="form-select" required>
                            <option value="">-- Chọn Kế hoạch Khóa luận --</option>
                            @foreach($keHoachs as $kh)
                                <option value="{{ $kh->MakeHoach }}">
                                    {{ $kh->TenKeHoach }} ({{ $kh->hocKy->TenHocKy ?? $kh->MaHocKy }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tải Lên File Đính Kèm (.doc, .pdf, .xls, .zip)</label>
                        <input type="file" name="File" class="form-control">
                        <div class="form-text small">Tối đa 10MB. Hoặc nhập đường dẫn bên dưới:</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Đường Dẫn File (URL / Link ngoài)</label>
                        <input type="text" name="DuongDanFile" class="form-control" placeholder="https://drive.google.com/...">
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Lưu Biểu Mẫu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
