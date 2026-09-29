@extends($layout ?? 'layouts.admin')

@section('page_title', 'Quản Lý Thông Báo')

@section('content')
<div class="container-fluid px-0">

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-bullhorn text-primary me-2"></i>Quản Lý Thông Báo
            </h4>
        </div>
        <div>
            <a href="{{ route('thongbao.create') }}" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm fw-semibold">
                <i class="fa-solid fa-plus me-1"></i> Tạo Thông Báo Mới
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards gọn gàng -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="text-muted small fw-medium mb-1">Tổng thông báo</div>
                <div class="fs-4 fw-bold text-dark">{{ $stats['total_thongbao'] ?? $thongbaos->total() }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="text-muted small fw-medium mb-1">Đã phát hành</div>
                <div class="fs-4 fw-bold text-success">{{ $stats['active_count'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="text-muted small fw-medium mb-1">Tin toàn trường</div>
                <div class="fs-4 fw-bold text-primary">{{ $stats['target_all_count'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="text-muted small fw-medium mb-1">Bản nháp / Hẹn giờ</div>
                <div class="fs-4 fw-bold text-warning">{{ $stats['draft_schedule_count'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('thongbao.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm theo tiêu đề, mã thông báo..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="LoaiThongBao" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Tất cả phân loại --</option>
                        <option value="Kế hoạch" {{ request('LoaiThongBao') == 'Kế hoạch' ? 'selected' : '' }}>Kế hoạch</option>
                        <option value="Hệ thống" {{ request('LoaiThongBao') == 'Hệ thống' ? 'selected' : '' }}>Hệ thống</option>
                        <option value="Báo cáo" {{ request('LoaiThongBao') == 'Báo cáo' ? 'selected' : '' }}>Báo cáo</option>
                        <option value="Hội đồng" {{ request('LoaiThongBao') == 'Hội đồng' ? 'selected' : '' }}>Hội đồng</option>
                        <option value="Thông báo chung" {{ request('LoaiThongBao') == 'Thông báo chung' ? 'selected' : '' }}>Thông báo chung</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="TrangThai" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="NHÁP" {{ request('TrangThai') == 'NHÁP' ? 'selected' : '' }}>Bản nháp</option>
                        <option value="ĐÃ LÊN LỊCH" {{ request('TrangThai') == 'ĐÃ LÊN LỊCH' ? 'selected' : '' }}>Đã lên lịch</option>
                        <option value="ĐÃ GỬI" {{ request('TrangThai') == 'ĐÃ GỬI' ? 'selected' : '' }}>Đã gửi</option>
                        <option value="ĐÃ HỦY" {{ request('TrangThai') == 'ĐÃ HỦY' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary px-3 flex-grow-1">Lọc</button>
                    @if(request('search') || request('LoaiThongBao') || request('TrangThai'))
                        <a href="{{ route('thongbao.index') }}" class="btn btn-sm btn-light border px-2" title="Xóa lọc"><i class="fa-solid fa-rotate-left"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="fa-regular fa-clipboard me-2 text-primary"></i>Danh Sách Thông Báo</span>
            <span class="small text-muted">Tổng cộng: <strong>{{ $thongbaos->total() }}</strong> tin</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted" style="font-size: 0.8rem; text-transform: uppercase;">
                        <tr>
                            <th class="ps-3 py-3" style="width: 120px;">Mã TB</th>
                            <th class="py-3" style="min-width: 320px;">Tiêu Đề Thông Báo</th>
                            <th class="py-3 text-center" style="width: 140px;">Đối Tượng</th>
                            <th class="py-3 text-center" style="width: 130px;">Trạng Thái</th>
                            <th class="py-3 text-center" style="width: 130px;">Ngày Đăng</th>
                            <th class="pe-3 py-3 text-center" style="width: 110px;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($thongbaos as $tb)
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-light text-dark border">{{ $tb->MaThongBao }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <a href="{{ route('thongbao.show', $tb->MaThongBao) }}" class="fw-semibold text-dark text-decoration-none hover-primary" style="font-size: 0.88rem;">
                                            {{ $tb->TieuDe }}
                                        </a>
                                        @if($tb->FileDinhKem)
                                            <i class="fa-solid fa-paperclip text-muted small ms-1" title="Có tệp đính kèm"></i>
                                        @endif
                                    </div>
                                    @if($tb->LoaiThongBao)
                                        <span class="badge bg-light text-secondary border mt-1" style="font-size: 0.72rem;">{{ $tb->LoaiThongBao }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($tb->DoiTuongNhan === 'Giảng viên')
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1" style="font-size: 0.78rem;">
                                            Giảng viên
                                        </span>
                                    @elseif($tb->DoiTuongNhan === 'Sinh viên')
                                        <span class="badge bg-info-subtle text-info rounded-pill px-3 py-1" style="font-size: 0.78rem;">
                                            Sinh viên
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark border rounded-pill px-3 py-1" style="font-size: 0.78rem;">
                                            Tất cả
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(in_array($tb->TrangThai, ['ĐÃ GỬI', 'ACTIVE', 'da_gui', 'Đã phát hành']))
                                        <span class="badge bg-success text-white rounded-pill px-3 py-1" style="font-size: 0.76rem;">
                                            Đã gửi
                                        </span>
                                    @elseif($tb->TrangThai === 'ĐÃ LÊN LỊCH')
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1" style="font-size: 0.76rem;">
                                            Lên lịch
                                        </span>
                                    @elseif($tb->TrangThai === 'NHÁP')
                                        <span class="badge bg-secondary text-white rounded-pill px-3 py-1" style="font-size: 0.76rem;">
                                            Bản nháp
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border rounded-pill px-3 py-1" style="font-size: 0.76rem;">
                                            {{ $tb->TrangThai }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center text-muted small" style="font-size: 0.8rem;">
                                    {{ date('d/m/Y', strtotime($tb->NgayGui ?? $tb->NgayTao ?? $tb->created_at)) }}
                                </td>
                                <td class="pe-3 text-center">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('thongbao.show', $tb->MaThongBao) }}" class="btn btn-sm btn-light border text-primary px-2 py-1 rounded-3" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        @if(in_array($tb->TrangThai, ['NHÁP', 'ĐÃ LÊN LỊCH']))
                                            <a href="{{ route('thongbao.edit', $tb->MaThongBao) }}" class="btn btn-sm btn-light border text-warning px-2 py-1 rounded-3" title="Chỉnh sửa">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <form action="{{ route('thongbao.sendNow', $tb->MaThongBao) }}" method="POST" class="d-inline" onsubmit="return confirm('Phát hành ngay thông báo này?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-light border text-success px-2 py-1 rounded-3" title="Phát hành">
                                                    <i class="fa-solid fa-paper-plane"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button" class="btn btn-sm btn-light border text-danger px-2 py-1 rounded-3" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $tb->MaThongBao }}" title="Xóa">
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
                                                    <div class="modal-header border-bottom pb-3">
                                                        <h6 class="modal-title fw-bold text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Xác Nhận Xóa</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body py-3">
                                                        <p class="mb-2">Bạn có chắc chắn muốn xóa thông báo này?</p>
                                                        <div class="p-3 bg-light rounded-3 border small">
                                                            <div class="fw-bold text-dark">{{ $tb->TieuDe }}</div>
                                                            <div class="text-muted mt-1">Mã TB: <code>{{ $tb->MaThongBao }}</code></div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top pt-2">
                                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
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
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-bell-slash fa-2x mb-2 d-block opacity-50"></i>
                                    Chưa có thông báo nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($thongbaos->hasPages())
        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
            <span class="small text-muted">
                Hiển thị {{ $thongbaos->firstItem() ?? 0 }}–{{ $thongbaos->lastItem() ?? 0 }} trên tổng số {{ $thongbaos->total() }} thông báo
            </span>
            <div>
                {{ $thongbaos->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
