@extends($layout ?? 'layouts.admin')

@section('page_title', 'Quản Lý Thông Báo Hệ Thống')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('info'))
<div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-circle-info me-2"></i>{{ session('info') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Page Header & Main Action -->
<div class="card card-premium mb-4">
    <div class="card-header-premium">
        <div>
            <span class="fs-6"><i class="fa-solid fa-bell me-2"></i> Danh Sách Thông Báo Hệ Thống</span>
            <div class="small text-muted font-normal fw-normal mt-1">Quản lý, lên lịch phát hành và theo dõi lịch sử gửi thông báo tới Sinh viên &amp; Giảng viên.</div>
        </div>
        <a href="{{ route('thongbao.create') }}" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm fw-bold">
            <i class="fa-solid fa-plus me-1"></i> Tạo Thông Báo Mới
        </a>
    </div>
    <div class="card-body p-0">
        <!-- Filter Bar -->
        <div class="p-3 bg-light border-bottom">
            <form method="GET" action="{{ route('thongbao.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm theo tiêu đề, nội dung, mã thông báo..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="LoaiThongBao" class="form-select form-select-sm">
                        <option value="">-- Tất cả Phân loại --</option>
                        <option value="Kế hoạch" {{ request('LoaiThongBao') == 'Kế hoạch' ? 'selected' : '' }}>Kế hoạch</option>
                        <option value="Hệ thống" {{ request('LoaiThongBao') == 'Hệ thống' ? 'selected' : '' }}>Hệ thống</option>
                        <option value="Báo cáo" {{ request('LoaiThongBao') == 'Báo cáo' ? 'selected' : '' }}>Báo cáo</option>
                        <option value="Hội đồng" {{ request('LoaiThongBao') == 'Hội đồng' ? 'selected' : '' }}>Hội đồng</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="TrangThai" class="form-select form-select-sm">
                        <option value="">-- Tất cả Trạng thái --</option>
                        <option value="NHÁP" {{ request('TrangThai') == 'NHÁP' ? 'selected' : '' }}>⚪ NHÁP</option>
                        <option value="ĐÃ LÊN LỊCH" {{ request('TrangThai') == 'ĐÃ LÊN LỊCH' ? 'selected' : '' }}>🟡 ĐÃ LÊN LỊCH</option>
                        <option value="ĐÃ GỬI" {{ request('TrangThai') == 'ĐÃ GỬI' ? 'selected' : '' }}>🟢 ĐÃ GỬI</option>
                        <option value="ĐÃ HỦY" {{ request('TrangThai') == 'ĐÃ HỦY' ? 'selected' : '' }}>🔴 ĐÃ HỦY</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 w-100"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
                    <a href="{{ route('thongbao.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2" title="Xóa lọc"><i class="fa-solid fa-rotate"></i></a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="12%">Mã TB</th>
                        <th width="35%">Tiêu Đề Thông Báo</th>
                        <th width="15%">Đối Tượng Nhận</th>
                        <th width="13%" class="text-center">Trạng Thái</th>
                        <th width="13%">Ngày Tạo / Gửi</th>
                        <th width="12%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($thongbaos as $tb)
                    @php
                        $badgeClass = match($tb->TrangThai) {
                            'NHÁP'       => 'bg-secondary',
                            'ĐÃ LÊN LỊCH' => 'bg-warning text-dark',
                            'ĐÃ GỬI'     => 'bg-success',
                            'ĐÃ HỦY'     => 'bg-danger',
                            default      => 'bg-info',
                        };
                    @endphp
                    <tr>
                        <td><span class="badge-code">{{ $tb->MaThongBao }}</span></td>
                        <td>
                            <div class="fw-bold text-primary">{{ $tb->TieuDe }}</div>
                            <div class="small text-muted">
                                <span class="badge bg-light text-secondary border me-1">{{ $tb->LoaiThongBao }}</span>
                                @if($tb->FileDinhKem)
                                    <span class="text-success"><i class="fa-solid fa-paperclip me-1"></i>File đính kèm</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border rounded-pill px-3"><i class="fa-solid fa-users me-1 text-muted"></i>{{ $tb->DoiTuongNhan }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $badgeClass }} rounded-pill px-3 py-1">{{ $tb->TrangThai }}</span>
                        </td>
                        <td class="small text-muted">
                            <div><i class="fa-regular fa-clock me-1"></i>Tạo: {{ date('d/m/Y H:i', strtotime($tb->NgayTao)) }}</div>
                            @if($tb->NgayGui)
                                <div class="text-success"><i class="fa-solid fa-paper-plane me-1"></i>Gửi: {{ date('d/m/Y H:i', strtotime($tb->NgayGui)) }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <!-- Nút Xem Chi Tiết -->
                                <a href="{{ route('thongbao.show', $tb->MaThongBao) }}" class="btn btn-sm btn-light text-info btn-action-circle" title="Xem Chi Tiết & Người Nhận">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                <!-- Nút Sửa (Nếu Nháp hoặc Đã lên lịch) -->
                                @if(in_array($tb->TrangThai, ['NHÁP', 'ĐÃ LÊN LỊCH']))
                                <a href="{{ route('thongbao.edit', $tb->MaThongBao) }}" class="btn btn-sm btn-light text-primary btn-action-circle" title="Chỉnh sửa nội dung">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('thongbao.sendNow', $tb->MaThongBao) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận phát hành ngay thông báo này?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-light text-success btn-action-circle" title="Phát hành ngay">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </button>
                                </form>
                                @endif

                                <!-- Nút Xóa / Hủy -->
                                <button type="button" class="btn btn-sm btn-light text-danger btn-action-circle" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $tb->MaThongBao }}" title="Xóa / Thu hồi">
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
                                                    <div>Mã TB: <code>{{ $tb->MaThongBao }}</code> | Trạng thái: <span class="badge {{ $badgeClass }}">{{ $tb->TrangThai }}</span></div>
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
                        <td colspan="6">
                            <div class="empty-state-box">
                                <i class="fa-solid fa-bell-slash"></i>
                                <h6>Chưa có thông báo nào</h6>
                                <p class="small text-muted mb-3">Vui lòng bấm nút dưới đây để phát hành thông báo mới.</p>
                                <a href="{{ route('thongbao.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">Tạo Thông Báo Mới</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($thongbaos->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $thongbaos->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
