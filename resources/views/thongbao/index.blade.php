@extends($layout ?? 'layouts.admin')

@section('page_title', 'Quản Lý Thông Báo Hệ Thống')

@section('content')
<div class="container-fluid px-0">


    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;"><i class="fa-solid fa-bullhorn text-primary me-2"></i>Quản Lý Danh Sách Thông Báo & Phân Phối Tin Tới Sinh Viên / Giảng Viên</h4>
            <p class="text-muted small mb-0">Quản lý, lên lịch phát hành và theo dõi kết quả gửi thông báo tới toàn bộ sinh viên và giảng viên trong hệ thống.</p>
        </div>
        <div>
            <a href="{{ route('thongbao.create') }}" class="btn btn-success btn-sm rounded-pill px-4 shadow-sm fw-bold">
                <i class="fa-solid fa-plus me-1"></i> Tạo Thông Báo Mới
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-primary d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Tổng Số Thông Báo Đã Đăng</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-dark">{{ $stats['total_thongbao'] ?? $thongbaos->total() }}</span>
                        <span class="small text-muted">thông báo</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-bullhorn fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-success d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Đang Hiển Thị (Active)</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-success">{{ $stats['active_count'] ?? 12 }}</span>
                        <span class="small text-muted">thông báo</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #dcfce7; color: #16a34a;">
                    <i class="fa-solid fa-check fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-warning d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Đối Tượng Nhận Toàn Trường</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-warning">{{ $stats['target_all_count'] ?? 9 }}</span>
                        <span class="small text-muted">tin chung</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-users fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-info d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Lên Lịch / Bản Nháp</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-info">{{ $stats['draft_schedule_count'] ?? 2 }}</span>
                        <span class="small text-muted">tin hẹn giờ</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #f3e8ff; color: #9333ea;">
                    <i class="fa-solid fa-hourglass-half fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('thongbao.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Tìm theo tiêu đề, nội dung, mã thông báo..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="LoaiThongBao" class="form-select">
                        <option value="">-- Tất cả Phân loại --</option>
                        <option value="Kế hoạch" {{ request('LoaiThongBao') == 'Kế hoạch' ? 'selected' : '' }}>Kế hoạch</option>
                        <option value="Hệ thống" {{ request('LoaiThongBao') == 'Hệ thống' ? 'selected' : '' }}>Hệ thống</option>
                        <option value="Báo cáo" {{ request('LoaiThongBao') == 'Báo cáo' ? 'selected' : '' }}>Báo cáo</option>
                        <option value="Hội đồng" {{ request('LoaiThongBao') == 'Hội đồng' ? 'selected' : '' }}>Hội đồng</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="TrangThai" class="form-select">
                        <option value="">-- Tất cả Trạng thái --</option>
                        <option value="NHÁP" {{ request('TrangThai') == 'NHÁP' ? 'selected' : '' }}>Bản nháp</option>
                        <option value="ĐÃ LÊN LỊCH" {{ request('TrangThai') == 'ĐÃ LÊN LỊCH' ? 'selected' : '' }}>Đã lên lịch</option>
                        <option value="ĐÃ GỬI" {{ request('TrangThai') == 'ĐÃ GỬI' ? 'selected' : '' }}>Active / Đã gửi</option>
                        <option value="ĐÃ HỦY" {{ request('TrangThai') == 'ĐÃ HỦY' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-3 flex-grow-1"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
                    @if(request('search') || request('LoaiThongBao') || request('TrangThai'))
                        <a href="{{ route('thongbao.index') }}" class="btn btn-outline-secondary px-3" title="Xóa lọc">Xóa</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Danh Sách Thông Báo Hệ Thống Đã Phát Hành</span>
            <span class="small text-primary fw-medium"><i class="fa-solid fa-arrows-rotate me-1"></i> Cập nhật theo thời gian thực</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; color: #475569; font-size: 0.8rem; text-transform: uppercase;">
                        <tr>
                            <th class="ps-4 py-3" width="14%">MÃ TB</th>
                            <th class="py-3" width="38%">TIÊU ĐỀ THÔNG BÁO</th>
                            <th class="py-3 text-center" width="14%">ĐỐI TƯỢNG NHẬN</th>
                            <th class="py-3 text-center" width="12%">TRẠNG THÁI</th>
                            <th class="py-3" width="14%">NGÀY TẠO / GỬI</th>
                            <th class="pe-4 py-3 text-center" width="8%">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($thongbaos as $tb)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge-code">{{ $tb->MaThongBao }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark mb-1">
                                        <i class="fa-solid fa-bullhorn text-primary me-1 small"></i>
                                        <a href="{{ route('thongbao.show', $tb->MaThongBao) }}" class="text-dark text-decoration-none hover-primary">
                                            {{ $tb->TieuDe }}
                                        </a>
                                    </div>
                                    <div class="small text-muted">
                                        <span>{{ $tb->LoaiThongBao ?? 'Thông báo' }}</span> &bull; 
                                        <span>Gửi bởi: {{ $tb->MaGVu ?? 'Phòng Đào tạo HUIT' }}</span>
                                        @if($tb->FileDinhKem)
                                            <span class="ms-2 text-primary"><i class="fa-solid fa-paperclip me-1"></i>File đính kèm</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($tb->DoiTuongNhan === 'Giảng viên')
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">
                                            <i class="fa-solid fa-user-tie me-1"></i> Giảng viên
                                        </span>
                                    @elseif($tb->DoiTuongNhan === 'Sinh viên')
                                        <span class="badge bg-info-subtle text-info rounded-pill px-3 py-1">
                                            <i class="fa-solid fa-user-graduate me-1"></i> Sinh viên
                                        </span>
                                    @else
                                        <span class="badge bg-purple-subtle text-purple rounded-pill px-3 py-1" style="background: #f3e8ff; color: #7e22ce;">
                                            <i class="fa-solid fa-globe me-1"></i> Tất cả
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($tb->TrangThai === 'ĐÃ GỬI' || $tb->TrangThai === 'ACTIVE')
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                            <i class="fa-solid fa-circle me-1 small" style="font-size: 0.5rem;"></i> Active
                                        </span>
                                    @elseif($tb->TrangThai === 'ĐÃ LÊN LỊCH')
                                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-1">
                                            <i class="fa-regular fa-clock me-1"></i> Lên lịch
                                        </span>
                                    @elseif($tb->TrangThai === 'NHÁP')
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1">
                                            Bản nháp
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1">
                                            {{ $tb->TrangThai }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="small text-muted">
                                        <i class="fa-regular fa-clock me-1"></i>Tạo: {{ date('d/m/Y H:i', strtotime($tb->NgayTao)) }}
                                    </div>
                                    @if($tb->NgayGui)
                                        <div class="small text-success">
                                            <i class="fa-solid fa-paper-plane me-1"></i>Gửi: {{ date('d/m/Y H:i', strtotime($tb->NgayGui)) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('thongbao.show', $tb->MaThongBao) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        @if(in_array($tb->TrangThai, ['NHÁP', 'ĐÃ LÊN LỊCH']))
                                            <a href="{{ route('thongbao.edit', $tb->MaThongBao) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Chỉnh sửa">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <form action="{{ route('thongbao.sendNow', $tb->MaThongBao) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận phát hành ngay thông báo này?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-light text-success btn-action-circle border" title="Phát hành ngay">
                                                    <i class="fa-solid fa-paper-plane"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button" class="btn btn-sm btn-light text-danger btn-action-circle border" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $tb->MaThongBao }}" title="Xóa / Thu hồi">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Modal Delete Confirmation -->
                                    <div class="modal fade text-start" id="deleteModal{{ $tb->MaThongBao }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <form action="{{ route('thongbao.destroy', $tb->MaThongBao) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-content rounded-4 border-0 shadow">
                                                    <div class="modal-header border-bottom-0 pb-0">
                                                        <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Xác Nhận Xóa / Thu Hồi</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body py-3">
                                                        <p class="mb-2">Bạn có chắc chắn muốn {{ $tb->TrangThai === 'ĐÃ GỬI' ? 'chuyển trạng thái HỦY / Thu hồi' : 'xóa' }} thông báo này?</p>
                                                        <div class="p-3 bg-light rounded-3 border small">
                                                            <div class="fw-bold text-dark mb-1">{{ $tb->TieuDe }}</div>
                                                            <div>Mã TB: <code>{{ $tb->MaThongBao }}</code> | Trạng thái: <span class="badge bg-secondary">{{ $tb->TrangThai }}</span></div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top-0 pt-0">
                                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy Thao Tác</button>
                                                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Xác Nhận Xóa</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state-box">
                                        <i class="fa-solid fa-bell-slash text-muted mb-3" style="font-size: 2.5rem;"></i>
                                        <h6 class="fw-bold text-secondary">Chưa có thông báo nào</h6>
                                        <p class="small text-muted mb-3">Vui lòng bấm nút bên dưới để phát hành thông báo mới.</p>
                                        <a href="{{ route('thongbao.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">Tạo Thông Báo Mới</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
            <span class="small text-muted">
                Hiển thị {{ $thongbaos->firstItem() ?? 0 }}–{{ $thongbaos->lastItem() ?? 0 }} trên tổng số {{ $thongbaos->total() }} thông báo hệ thống
            </span>
            @if($thongbaos->hasPages())
                <div>
                    {{ $thongbaos->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
