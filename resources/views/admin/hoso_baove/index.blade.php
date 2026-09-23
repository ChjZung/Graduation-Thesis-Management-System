@extends('layouts.admin')
@section('page_title', 'Quản Lý Hồ Sơ Bảo Vệ Khóa Luận')
@section('content')



@if(session('warning'))
<div class="alert alert-warning alert-dismissible fade show mb-4 shadow-sm" role="alert">
    <i class="fa-solid fa-circle-exclamation me-2 fs-5"></i>{{ session('warning') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
    <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm">
    <div class="fw-bold mb-1"><i class="fa-solid fa-circle-xmark me-2"></i>Có lỗi xảy ra:</div>
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- ── TIÊU ĐỀ TRANG ── --}}
<div class="page-header-flex mb-3">
    <div>
        <h4 class="page-main-title">
            <i class="fa-solid fa-folder-tree text-primary"></i>
            Quản Lý Hồ Sơ Bảo Vệ &amp; Thẩm Định Turnitin
        </h4>
        <p class="page-main-subtitle">
            Kiểm tra báo cáo toàn văn, kết quả quét trùng lặp Turnitin và phân bổ nhóm đủ điều kiện sang Hội đồng chấm bảo vệ.
        </p>
    </div>
</div>

{{-- ── 4 KPI STATS CARDS CHUẨN FIGMA ── --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="admin-kpi-card border-accent-blue">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-title">Tổng Số Hồ Sơ</div>
                    <div class="kpi-value">{{ $tongSoHoSo }}</div>
                    <div class="kpi-subtext text-muted">Hồ sơ đã nộp</div>
                </div>
                <div class="kpi-icon-wrap" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="admin-kpi-card border-accent-amber">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-title">Chờ Thẩm Định</div>
                    <div class="kpi-value">{{ $choThamDinh }}</div>
                    <div class="kpi-subtext" style="color: #d97706;">Cần xét duyệt</div>
                </div>
                <div class="kpi-icon-wrap" style="background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="admin-kpi-card border-accent-green">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-title">Đủ Điều Kiện Bảo Vệ</div>
                    <div class="kpi-value">{{ $duDieuKien }}</div>
                    <div class="kpi-subtext text-success">Turnitin &le; 20%</div>
                </div>
                <div class="kpi-icon-wrap" style="background: #dcfce7; color: #16a34a;">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="admin-kpi-card border-accent-purple">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-title">Đã Xếp Hội Đồng</div>
                    <div class="kpi-value">{{ $daPhanCong }}</div>
                    <div class="kpi-subtext" style="color: #6366f1;">Sẵn sàng bảo vệ</div>
                </div>
                <div class="kpi-icon-wrap" style="background: #ede9fe; color: #7c3aed;">
                    <i class="fa-solid fa-landmark"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── 4 PILL TABS TRẠNG THÁI & TÌM KIẾM ── --}}
<div class="admin-pill-tabs">
    <a href="{{ route('admin.hosoBaoVe.index') }}" 
       class="admin-pill-tab {{ !request('TrangThai') ? 'active' : '' }}">
        <span class="fw-bold">{{ $tongSoHoSo }}</span> Tất Cả Hồ Sơ
    </a>
    <a href="{{ route('admin.hosoBaoVe.index', ['TrangThai' => 'Chờ thẩm định']) }}" 
       class="admin-pill-tab {{ request('TrangThai') === 'Chờ thẩm định' ? 'active' : '' }}">
        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#f59e0b;"></span>
        <span class="fw-bold">{{ $choThamDinh }}</span> Chờ Thẩm Định
    </a>
    <a href="{{ route('admin.hosoBaoVe.index', ['TrangThai' => 'Đủ điều kiện bảo vệ']) }}" 
       class="admin-pill-tab {{ request('TrangThai') === 'Đủ điều kiện bảo vệ' ? 'active' : '' }}">
        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#10b981;"></span>
        <span class="fw-bold">{{ $duDieuKien }}</span> Đủ ĐK Bảo Vệ
    </a>
    <a href="{{ route('admin.hosoBaoVe.index', ['TrangThai' => 'Đã phân công']) }}" 
       class="admin-pill-tab {{ request('TrangThai') === 'Đã phân công' ? 'active' : '' }}">
        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#6366f1;"></span>
        <span class="fw-bold">{{ $daPhanCong }}</span> Đã Phân Công HĐ
    </a>
</div>

{{-- ── BẢNG DANH SÁCH HỒ SƠ BẢO VỆ ── --}}
<div class="admin-table-card">
    <div class="admin-table-header">
        <h5 class="admin-table-header-title">
            <i class="fa-regular fa-folder-closed text-primary"></i>
            Danh Sách Hồ Sơ Khóa Luận &amp; Thẩm Định Turnitin
        </h5>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('admin.hosoBaoVe.index') }}" method="GET" class="d-flex align-items-center gap-1">
                @if(request('TrangThai'))
                    <input type="hidden" name="TrangThai" value="{{ request('TrangThai') }}">
                @endif
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm nhóm, đề tài..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                    @if(request('search'))
                        <a href="{{ route('admin.hosoBaoVe.index', ['TrangThai' => request('TrangThai')]) }}" class="btn btn-outline-danger"><i class="fa-solid fa-times"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="10%">Mã HS</th>
                        <th width="24%">Nhóm / Đề Tài</th>
                        <th width="14%">Khóa Luận</th>
                        <th width="14%" class="text-center">Turnitin %</th>
                        <th width="12%" class="text-center">Trạng Thái</th>
                        <th width="14%">Hội Đồng & GVPB</th>
                        <th width="12%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hoSos as $hs)
                    <tr>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary font-monospace border">{{ $hs->MaHoSo }}</span>
                            <div class="small text-muted mt-1">{{ \Carbon\Carbon::parse($hs->NgayNop)->format('d/m/Y') }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-primary-custom">{{ $hs->nhom->TenNhom ?? 'Chưa rõ nhóm' }}</div>
                            <div class="small text-dark fw-semibold">{{ Str::limit($hs->nhom->deTai->TenDeTai ?? 'Chưa có đề tài', 55) }}</div>
                            <div class="small text-muted">
                                <span>TN: <strong>{{ $hs->nhom->truongNhom->HoTen ?? '—' }}</strong></span>
                                @if($hs->nhom && $hs->nhom->deTai && $hs->nhom->deTai->giangVien)
                                    | <span>GVHD: {{ $hs->nhom->deTai->giangVien->HoTen }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($hs->ToanVanKhoaLuan)
                                <a href="{{ asset('storage/' . $hs->ToanVanKhoaLuan) }}" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-file-pdf me-1"></i>Toàn văn (PDF)
                                </a>
                            @else
                                <span class="text-muted small">Chưa có tệp</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php $pct = (float)$hs->TyLeTrungLap; @endphp
                            <div class="fs-5 fw-bold {{ $pct <= 20 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($pct, 2) }}%
                            </div>
                            @if($pct <= 20)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2" style="font-size: 0.7rem;">
                                    <i class="fa-solid fa-check me-1"></i>Hợp lệ (&le; 20%)
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2" style="font-size: 0.7rem;">
                                    <i class="fa-solid fa-xmark me-1"></i>Vượt ngưỡng (&gt; 20%)
                                </span>
                            @endif

                            @if($hs->MinhChungDaoVan)
                                <div class="mt-1">
                                    <a href="{{ asset('storage/' . $hs->MinhChungDaoVan) }}" target="_blank" class="btn btn-xs btn-outline-danger rounded-pill px-2" style="font-size: 0.7rem; padding: 1px 6px;">
                                        <i class="fa-solid fa-file-shield me-1"></i>Minh chứng
                                    </a>
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $badgeStyle = match($hs->TrangThai) {
                                    'Đã phân công', 'Đã phân công hội đồng' => 'bg-success-subtle text-success border border-success-subtle',
                                    'Đủ điều kiện bảo vệ' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'Không đủ điều kiện'  => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    default => 'bg-warning-subtle text-warning border border-warning-subtle',
                                };
                            @endphp
                            <span class="badge {{ $badgeStyle }} rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                {{ $hs->TrangThai }}
                            </span>
                        </td>
                        <td>
                            @if($hs->hoiDong)
                                <div class="fw-bold text-dark small"><i class="fa-solid fa-landmark me-1 text-primary"></i>{{ $hs->hoiDong->TenHoiDong }}</div>
                                @if($hs->ThoiGianBaoVe || $hs->hoiDong->ThoiGianBatDau)
                                    <div class="small text-muted"><i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($hs->ThoiGianBaoVe ?? $hs->hoiDong->ThoiGianBatDau)->format('d/m H:i') }}</div>
                                @endif
                                @if($hs->PhongBaoVe || $hs->hoiDong->DiaDiem)
                                    <div class="small text-muted"><i class="fa-solid fa-location-dot me-1"></i>{{ $hs->PhongBaoVe ?? $hs->hoiDong->DiaDiem }}</div>
                                @endif
                                <div class="small text-secondary mt-1">
                                    <strong>GVPB:</strong> {{ $hs->giangVienPhanBien->HoTen ?? 'Chưa phân công' }}
                                </div>
                            @else
                                <span class="text-muted small fst-italic">Chưa xếp Hội đồng</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex flex-column gap-1 align-items-center">
                                <!-- Thao tác Thẩm định nếu đang Chờ thẩm định hoặc xem xét lại -->
                                @if($hs->TrangThai === 'Chờ thẩm định' || $hs->TrangThai === 'Không đủ điều kiện')
                                    <form action="{{ route('admin.hosoBaoVe.xacNhan', $hs->MaHoSo) }}" method="POST" class="d-inline w-100">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-xs btn-success rounded-pill w-100 py-1" style="font-size: 0.75rem;" onclick="return confirm('Xác nhận duyệt hồ sơ này ĐỦ ĐIỀU KIỆN BẢO VỆ?')">
                                            <i class="fa-solid fa-check me-1"></i>Duyệt Đủ ĐK
                                        </button>
                                    </form>

                                    @if($hs->TrangThai === 'Chờ thẩm định')
                                    <button type="button" class="btn btn-xs btn-outline-danger rounded-pill w-100 py-1" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#modalTuChoi{{ $hs->MaHoSo }}">
                                        <i class="fa-solid fa-xmark me-1"></i>Từ chối
                                    </button>
                                    @endif
                                @endif

                                <!-- Nút Phân công Hội đồng & GVPB (Dành cho hồ sơ Đủ điều kiện hoặc Đã phân công) -->
                                @if(in_array($hs->TrangThai, ['Đủ điều kiện bảo vệ', 'Đã phân công', 'Đã phân công hội đồng']))
                                    <button type="button" class="btn btn-xs btn-primary rounded-pill w-100 py-1" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#modalPhanCong{{ $hs->MaHoSo }}">
                                        <i class="fa-solid fa-user-gear me-1"></i>{{ $hs->hoiDong ? 'Đổi HĐ / PB' : 'Xếp HĐ' }}
                                    </button>
                                @endif
                            </div>

                            <!-- Modal Từ chối Hồ sơ -->
                            <div class="modal fade text-start" id="modalTuChoi{{ $hs->MaHoSo }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-danger text-white">
                                            <h6 class="modal-title fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>Từ Chối Hồ Sơ Bảo Vệ — {{ $hs->MaHoSo }}</h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.hosoBaoVe.xacNhan', $hs->MaHoSo) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="reject">
                                            <div class="modal-body">
                                                <p class="text-muted small mb-2">Nhóm: <strong>{{ $hs->nhom->TenNhom ?? '' }}</strong> | Tỷ lệ Turnitin: <strong>{{ $hs->TyLeTrungLap }}%</strong></p>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Lý do từ chối <span class="text-danger">*</span></label>
                                                    <textarea name="GhiChu" class="form-control" rows="3" placeholder="Nhập lý do (VD: Tỷ lệ đạo văn Turnitin vượt quá 20%, thiếu minh chứng hợp lệ...)" required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light py-2">
                                                <button type="button" class="btn btn-sm btn-light rounded-pill" data-bs-dismiss="modal">Đóng</button>
                                                <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3">Xác nhận từ chối</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Phân công Hội đồng & GV Phản biện -->
                            <div class="modal fade text-start" id="modalPhanCong{{ $hs->MaHoSo }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-primary text-white">
                                            <h6 class="modal-title fw-bold"><i class="fa-solid fa-landmark me-2"></i>Phân Công Hội Đồng & GVPB — {{ $hs->MaHoSo }}</h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.hosoBaoVe.phanCong', $hs->MaHoSo) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Chọn Hội Đồng Bảo Vệ <span class="text-danger">*</span></label>
                                                    <select name="MaHoiDong" class="form-select form-select-sm" required>
                                                        <option value="">— Chọn Hội đồng —</option>
                                                        @foreach($hoiDongs as $hd)
                                                            <option value="{{ $hd->MaHoiDong }}" {{ $hs->MaHoiDong === $hd->MaHoiDong ? 'selected' : '' }}>
                                                                {{ $hd->TenHoiDong }} ({{ $hd->DiaDiem ?? 'Phòng chưa xếp' }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Giảng Viên Phản Biện (GVPB)</label>
                                                    <select name="MaGVPhanBien" class="form-select form-select-sm">
                                                        <option value="">— Chọn Giảng viên phản biện —</option>
                                                        @foreach($giangViens as $gv)
                                                            <option value="{{ $gv->MaGV }}" {{ $hs->MaGVPhanBien === $gv->MaGV ? 'selected' : '' }}>
                                                                {{ $gv->HoTen }} ({{ $gv->HocVi ?? 'Giảng viên' }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="form-text text-muted small">GVPB sẽ chấm nhận xét và cho điểm phản biện đồ án.</div>
                                                </div>

                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold small">Ngày & Giờ Bảo Vệ</label>
                                                        <input type="datetime-local" name="ThoiGianBaoVe" class="form-control form-control-sm" 
                                                            value="{{ $hs->ThoiGianBaoVe ? \Carbon\Carbon::parse($hs->ThoiGianBaoVe)->format('Y-m-d\TH:i') : '' }}">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold small">Phòng Bảo Vệ</label>
                                                        <input type="text" name="PhongBaoVe" class="form-control form-control-sm" 
                                                            placeholder="VD: Phòng A101" value="{{ $hs->PhongBaoVe }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light py-2">
                                                <button type="button" class="btn btn-sm btn-light rounded-pill" data-bs-dismiss="modal">Đóng</button>
                                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4">Lưu phân công</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fa-3x text-secondary mb-3 d-block"></i>
                            Không có hồ sơ bảo vệ nào phù hợp với điều kiện tìm kiếm.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($hoSos->hasPages())
    <div class="card-footer bg-white border-0 py-3">{{ $hoSos->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection

