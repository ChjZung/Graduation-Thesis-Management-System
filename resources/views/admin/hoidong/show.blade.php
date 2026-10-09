@extends('layouts.admin')

@section('page_title', 'Chi Tiết Hội Đồng — ' . $hoiDong->TenHoiDong)

@section('content')
<div class="container-fluid px-0">


    <!-- Breadcrumb & Header Section (Đồng bộ chuẩn Học Kỳ) -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.hoidong.index') }}" class="text-decoration-none">Hội Đồng Bảo Vệ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $hoiDong->MaHoiDong }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-landmark text-primary"></i>
                <span>{{ $hoiDong->TenHoiDong }}</span>
                <span class="badge-code ms-2">{{ $hoiDong->MaHoiDong }}</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.hoidong.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay Lại
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards thống kê nhanh của Hội đồng -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Trạng Thái HĐ</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #e0f2fe; color: #0284c7;">
                        <i class="fa-solid fa-circle-dot fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-4 fw-bold text-dark lh-1">{{ $hoiDong->TrangThai }}</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Thành Viên HĐ</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #dcfce7; color: #16a34a;">
                        <i class="fa-solid fa-users fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-success lh-1">{{ $hoiDong->thanhViens->count() }}</span>
                    <span class="small text-muted fw-medium">giảng viên</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Nhóm Sinh Viên</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fef3c7; color: #d97706;">
                        <i class="fa-solid fa-user-graduate fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $hoiDong->hoSoBaoVes->count() }}</span>
                    <span class="small text-muted fw-medium">nhóm bảo vệ</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Địa Điểm</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #ede9fe; color: #7c3aed;">
                        <i class="fa-solid fa-location-dot fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-5 fw-bold text-primary lh-1 text-truncate" title="{{ $hoiDong->DiaDiem ?? 'Chưa xếp' }}">{{ $hoiDong->DiaDiem ?? 'Chưa xếp' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Cột trái: Thông tin Hội đồng & Thành viên -->
        <div class="col-lg-5">
            <!-- Card 1: Thông tin cơ bản -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-circle-info me-2 text-primary"></i> Thông Tin Hội Đồng</span>
                    @php
                        $st = mb_strtolower(trim((string)$hoiDong->TrangThai));
                        $isEnded = in_array($st, ['đã kết thúc', 'da ket thuc', 'completed', 'ended']);
                        $isRunning = in_array($st, ['đang diễn ra', 'dang dien ra', 'active', 'running']);
                    @endphp
                    @if($isEnded)
                        <span class="badge bg-light text-muted border rounded-pill px-3 py-1 fw-medium">Đã kết thúc</span>
                    @elseif($isRunning)
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold">
                            <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem; color: #16a34a;"></i> Đang diễn ra
                        </span>
                    @else
                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1 fw-medium">
                            <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem; color: #0284c7;"></i> Sắp diễn ra
                        </span>
                    @endif
                </div>
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless mb-3 align-middle">
                        <tr>
                            <td class="text-muted fw-semibold" width="35%">Mã HĐ:</td>
                            <td><span class="badge-code">{{ $hoiDong->MaHoiDong }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Thời gian:</td>
                            <td>
                                <div><i class="fa-regular fa-clock text-primary me-1"></i>{{ \Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('H:i d/m/Y') }}</div>
                                <div class="small text-muted mt-0.5"><i class="fa-solid fa-arrow-right-long me-1"></i>{{ \Carbon\Carbon::parse($hoiDong->ThoiGianKetThuc)->format('H:i d/m/Y') }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Địa điểm:</td>
                            <td><strong>{{ $hoiDong->DiaDiem ?? 'Chưa xác định' }}</strong></td>
                        </tr>
                        @if($hoiDong->GhiChu)
                        <tr>
                            <td class="text-muted fw-semibold">Ghi chú:</td>
                            <td class="small text-muted">{{ $hoiDong->GhiChu }}</td>
                        </tr>
                        @endif
                    </table>

                    <!-- Cập nhật trạng thái Hội đồng -->
                    <form action="{{ route('admin.hoidong.updateTrangThai', $hoiDong->MaHoiDong) }}" method="POST" class="d-flex gap-2 pt-2 border-top">
                        @csrf
                        <select name="TrangThai" class="form-select form-select-sm">
                            <option value="Chưa diễn ra" {{ $hoiDong->TrangThai === 'Chưa diễn ra' ? 'selected' : '' }}>Chưa diễn ra</option>
                            <option value="Đang diễn ra" {{ $hoiDong->TrangThai === 'Đang diễn ra' ? 'selected' : '' }}>Đang diễn ra</option>
                            <option value="Đã kết thúc" {{ $hoiDong->TrangThai === 'Đã kết thúc' ? 'selected' : '' }}>Đã kết thúc</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 flex-shrink-0 fw-semibold">Cập nhật</button>
                    </form>
                </div>
            </div>

            <!-- Card 2: Thành viên Hội đồng -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-users me-2 text-primary"></i> Thành Viên Hội Đồng ({{ $hoiDong->thanhViens->count() }})</span>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalThemThanhVien" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-user-plus me-1"></i> Thêm TV
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                                <tr>
                                    <th class="ps-3 py-2.5">GIẢNG VIÊN</th>
                                    <th class="py-2.5 text-center" width="30%">VAI TRÒ</th>
                                    <th class="pe-3 py-2.5 text-center" width="20%">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($hoiDong->thanhViens as $tv)
                                <tr>
                                    <td class="ps-3 py-2.5">
                                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $tv->giangVien->HoTen ?? '—' }}</div>
                                        <div class="small text-muted" style="font-size: 0.75rem;">{{ $tv->giangVien->HocVi ?? 'GV' }} &bull; {{ $tv->giangVien->boMon->TenBoMon ?? 'Bộ môn' }}</div>
                                    </td>
                                    <td class="text-center py-2.5">
                                        @php
                                            $badgeStyle = match($tv->VaiTro) {
                                                'Chủ tịch' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                                'Thư ký'   => 'bg-info-subtle text-info border border-info-subtle',
                                                'Phản biện'=> 'bg-warning-subtle text-warning border border-warning-subtle',
                                                default    => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeStyle }} rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">{{ $tv->VaiTro }}</span>
                                    </td>
                                    <td class="pe-3 text-center py-2.5">
                                        <div class="d-flex justify-content-center gap-1">
                                            <!-- Nút sửa vai trò -->
                                            <button type="button" class="btn btn-sm btn-light border btn-action-circle" 
                                                    title="Đổi vai trò" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalDoiVaiTro{{ $tv->MaGV }}">
                                                <i class="fa-solid fa-pen-to-square text-primary" style="font-size: 0.75rem;"></i>
                                            </button>

                                            <!-- Nút xóa thành viên -->
                                            <form action="{{ route('admin.hoidong.xoaThanhVien', [$hoiDong->MaHoiDong, $tv->MaGV]) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border text-danger btn-action-circle" 
                                                        title="Xóa thành viên này" 
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa giảng viên {{ $tv->giangVien->HoTen ?? $tv->MaGV }} khỏi Hội đồng?');">
                                                    <i class="fa-solid fa-trash" style="font-size: 0.75rem;"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Modal Đổi vai trò cho từng thành viên -->
                                        <div class="modal fade text-start" id="modalDoiVaiTro{{ $tv->MaGV }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header py-2.5 bg-light">
                                                        <h6 class="modal-title fw-bold text-dark"><i class="fa-solid fa-user-gear me-1 text-primary"></i> Đổi Vai Trò</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('admin.hoidong.doiVaiTroThanhVien', [$hoiDong->MaHoiDong, $tv->MaGV]) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body p-3">
                                                            <div class="mb-2">
                                                                <small class="text-muted d-block">Giảng viên:</small>
                                                                <span class="fw-bold text-dark">{{ $tv->giangVien->HoTen ?? $tv->MaGV }}</span>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small fw-semibold text-secondary">Vai trò mới:</label>
                                                                <select name="VaiTro" class="form-select form-select-sm" required>
                                                                    <option value="Chủ tịch" {{ $tv->VaiTro === 'Chủ tịch' ? 'selected' : '' }}>Chủ tịch</option>
                                                                    <option value="Thư ký" {{ $tv->VaiTro === 'Thư ký' ? 'selected' : '' }}>Thư ký</option>
                                                                    <option value="Phản biện" {{ $tv->VaiTro === 'Phản biện' ? 'selected' : '' }}>Phản biện</option>
                                                                    <option value="Ủy viên" {{ in_array($tv->VaiTro, ['Ủy viên', 'Thành viên']) ? 'selected' : '' }}>Ủy viên / Thành viên</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer py-2 bg-light">
                                                            <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                                                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">Lưu thay đổi</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Chưa có thành viên nào.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Danh sách nhóm sinh viên bảo vệ -->
        <div class="col-lg-7">
            <!-- Card: Thêm nhóm bảo vệ mới vào Hội đồng -->
            @php
                $nhomChuaPhanCong = $nhomDuDieuKien->filter(function($h) use ($hoiDong) {
                    return $h->MaHoiDong !== $hoiDong->MaHoiDong;
                });
            @endphp

            @if($nhomChuaPhanCong->isNotEmpty())
            <div class="card border-0 shadow-sm rounded-3 mb-4" style="background: #f8fafc; border: 1px dashed #cbd5e1 !important;">
                <div class="card-header bg-white border-bottom py-2.5 text-primary fw-bold">
                    <i class="fa-solid fa-user-plus me-1 text-primary"></i> Thêm Nhóm Bảo Vệ Vào Hội Đồng Này
                </div>
                <div class="card-body p-3">
                    <form action="{{ route('admin.hoidong.phanCongNhom', $hoiDong->MaHoiDong) }}" method="POST">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Chọn Nhóm (Đủ điều kiện)</label>
                                <select name="MaNhom" class="form-select form-select-sm" required>
                                    <option value="">— Chọn nhóm sinh viên —</option>
                                    @foreach($nhomChuaPhanCong as $item)
                                    <option value="{{ $item->MaNhom }}">
                                        {{ $item->nhom->TenNhom ?? $item->MaNhom }} — {{ Str::limit($item->nhom->deTai->TenDeTai ?? '', 35) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Giảng Viên Phản Biện</label>
                                <select name="MaGVPhanBien" class="form-select form-select-sm">
                                    <option value="">— Chọn GV Phản biện —</option>
                                    @foreach($giangViens as $gv)
                                    <option value="{{ $gv->MaGV }}">{{ $gv->HoTen }} ({{ $gv->HocVi ?? 'GV' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-dark">Thời gian bảo vệ</label>
                                <input type="datetime-local" name="ThoiGianBaoVe" class="form-control form-control-sm"
                                    value="{{ $hoiDong->ThoiGianBatDau ? \Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('Y-m-d\TH:i') : '' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Phòng bảo vệ</label>
                                <input type="text" name="PhongBaoVe" class="form-control form-control-sm" placeholder="VD: Phòng B.304" value="{{ $hoiDong->DiaDiem }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100 fw-bold shadow-xs">
                                    <i class="fa-solid fa-plus me-1"></i>Thêm Nhóm
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Card: Các nhóm đang trong Hội đồng -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Nhóm Bảo Vệ Trong Hội Đồng ({{ $hoiDong->hoSoBaoVes->count() }})</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                                <tr>
                                    <th class="ps-3 py-3" width="35%">NHÓM / ĐỀ TÀI</th>
                                    <th class="py-3 text-center" width="15%">TURNITIN</th>
                                    <th class="py-3" width="35%">GV PHẢN BIỆN &amp; LỊCH</th>
                                    <th class="pe-3 py-3 text-center" width="15%">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($hoiDong->hoSoBaoVes as $hoSo)
                                <tr>
                                    <td class="ps-3 py-3">
                                        <div class="fw-bold text-primary">{{ $hoSo->nhom->TenNhom ?? '' }}</div>
                                        <div class="small text-dark fw-semibold">{{ Str::limit($hoSo->nhom->deTai->TenDeTai ?? '', 40) }}</div>
                                        <div class="small text-muted">Trưởng nhóm: {{ $hoSo->nhom->truongNhom->HoTen ?? '—' }}</div>
                                    </td>
                                    <td class="text-center py-3">
                                        @php $pct = (float)$hoSo->TyLeTrungLap; @endphp
                                        <span class="fw-bold {{ $pct <= 20 ? 'text-success' : 'text-danger' }}">{{ number_format($pct, 2) }}%</span>
                                        @if($hoSo->MinhChungDaoVan)
                                        <div>
                                            <a href="{{ asset('storage/' . $hoSo->MinhChungDaoVan) }}" target="_blank" class="badge bg-danger-subtle text-danger border border-danger-subtle text-decoration-none">
                                                Minh chứng
                                            </a>
                                        </div>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <form action="{{ route('admin.hoidong.phanCongNhom', $hoiDong->MaHoiDong) }}" method="POST" class="d-flex flex-column gap-1">
                                            @csrf
                                            <input type="hidden" name="MaNhom" value="{{ $hoSo->MaNhom }}">
                                            <select name="MaGVPhanBien" class="form-select form-select-sm" style="font-size: 0.8rem;">
                                                <option value="">— GVPB: Chưa chọn —</option>
                                                @foreach($giangViens as $gv)
                                                <option value="{{ $gv->MaGV }}" {{ $hoSo->MaGVPhanBien === $gv->MaGV ? 'selected' : '' }}>
                                                    {{ $gv->HoTen }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <div class="d-flex gap-1">
                                                <input type="text" name="PhongBaoVe" class="form-control form-control-sm" placeholder="Phòng" value="{{ $hoSo->PhongBaoVe }}" style="font-size: 0.75rem;">
                                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-2 py-0" style="font-size: 0.75rem;">Lưu</button>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="pe-3 text-center py-3">
                                        <form action="{{ route('admin.hoidong.huyPhanCongNhom', [$hoiDong->MaHoiDong, $hoSo->MaHoSo]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light text-danger btn-action-circle border" title="Hủy phân công nhóm này" onclick="return confirm('Bạn có chắc chắn muốn hủy phân công nhóm này khỏi hội đồng?')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                                        Chưa có nhóm sinh viên nào được phân công vào Hội đồng này.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm thành viên Hội đồng -->
<div class="modal fade" id="modalThemThanhVien" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-3 bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-user-plus me-2 text-primary"></i> Thêm Thành Viên Vào Hội Đồng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.hoidong.themThanhVien', $hoiDong->MaHoiDong) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Chọn Giảng viên: <span class="text-danger">*</span></label>
                        @php
                            $existingMaGVs = $hoiDong->thanhViens->pluck('MaGV')->toArray();
                        @endphp
                        <select name="MaGV" class="form-select" required>
                            <option value="">-- Chọn giảng viên --</option>
                            @foreach($giangViens as $gv)
                                @if(!in_array($gv->MaGV, $existingMaGVs))
                                <option value="{{ $gv->MaGV }}">
                                    {{ $gv->HoTen }} ({{ $gv->MaGV }} - {{ $gv->HocVi ?? 'GV' }})
                                </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Vai trò trong Hội đồng: <span class="text-danger">*</span></label>
                        <select name="VaiTro" class="form-select" required>
                            <option value="Thành viên">Ủy viên / Thành viên</option>
                            <option value="Chủ tịch">Chủ tịch Hội đồng</option>
                            <option value="Thư ký">Thư ký Hội đồng</option>
                            <option value="Phản biện">Ủy viên Phản biện</option>
                        </select>
                    </div>
                    <div class="p-3 bg-light rounded-3 text-muted small">
                        <i class="fa-solid fa-circle-info text-primary me-1"></i> Thành viên mới sẽ có quyền truy cập vào phân hệ Chấm điểm của Hội đồng này ngay sau khi được thêm.
                    </div>
                </div>
                <div class="modal-footer py-2.5 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Xác nhận thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

