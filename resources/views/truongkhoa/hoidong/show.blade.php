@extends('layouts.truongkhoa')

@section('page_title', 'Chi Tiết Hội Đồng — ' . $hoiDong->TenHoiDong)

@section('content')
<div class="container-fluid px-0">

    <!-- Header Section -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-light text-primary border font-monospace fw-bold">{{ $hoiDong->MaHoiDong }}</span>
                @if($hoiDong->TrangThaiCongBo === 'Đã công bố')
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold">
                        <i class="fa-solid fa-circle-check me-1"></i> Đã công bố chính thức
                    </span>
                @elseif($hoiDong->TrangThaiCongBo === 'Đã thu hồi')
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-semibold">
                        <i class="fa-solid fa-arrow-rotate-left me-1"></i> Đã thu hồi công bố
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fw-semibold">
                        <i class="fa-solid fa-pen-ruler me-1"></i> Bản nháp (Chưa công bố)
                    </span>
                @endif
                <span class="badge bg-light text-dark border">{{ $hoiDong->hocKy?->TenHocKy ?? $hoiDong->MaHocKy }} ({{ $hoiDong->DotBaoVe ?? 'Đợt 1' }})</span>
            </div>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-landmark text-primary me-2"></i>
                <span>{{ $hoiDong->TenHoiDong }}</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('truongkhoa.hoidong.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs d-inline-flex align-items-center">
                <i class="fa-solid fa-arrow-left me-2"></i> Danh Sách
            </a>

            <!-- Nút xem trước PDF -->
            <a href="{{ route('truongkhoa.hoidong.previewPdf', $hoiDong->MaHoiDong) }}" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-xs d-inline-flex align-items-center">
                <i class="fa-solid fa-file-pdf me-2"></i> Xem Văn Bản (PDF)
            </a>

            <!-- Nút tải PDF -->
            <a href="{{ route('truongkhoa.hoidong.downloadPdf', $hoiDong->MaHoiDong) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs d-inline-flex align-items-center">
                <i class="fa-solid fa-download me-2"></i> Tải PDF
            </a>

            <!-- Nút Công bố hoặc Thu hồi -->
            @if($hoiDong->TrangThaiCongBo !== 'Đã công bố')
                <form action="{{ route('truongkhoa.hoidong.congBo', $hoiDong->MaHoiDong) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận công bố chính thức Hội đồng này? Văn bản PDF sẽ được ban hành và thông báo gửi đến Giáo vụ, Giảng viên và Sinh viên.');">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 shadow-xs fw-bold d-inline-flex align-items-center">
                        <i class="fa-solid fa-bullhorn me-2"></i> Công Bố Chính Thức
                    </button>
                </form>
            @else
                <form action="{{ route('truongkhoa.hoidong.thuHoiCongBo', $hoiDong->MaHoiDong) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận thu hồi công bố Hội đồng này để điều chỉnh dữ liệu?');">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 shadow-xs fw-semibold d-inline-flex align-items-center text-dark">
                        <i class="fa-solid fa-arrow-rotate-left me-2"></i> Thu Hồi Công Bố
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show p-3 rounded-3 mb-4 shadow-xs" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show p-3 rounded-3 mb-4 shadow-xs" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show p-3 rounded-3 mb-4 shadow-xs" role="alert">
        <i class="fa-solid fa-circle-xmark me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

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
                    <span class="fs-5 fw-bold text-dark lh-1">{{ $hoiDong->TrangThai }}</span>
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
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đề Tài / Nhóm SV</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fef3c7; color: #d97706;">
                        <i class="fa-solid fa-graduation-cap fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $hoiDong->hoSoBaoVes->count() }}</span>
                    <span class="small text-muted fw-medium">đề tài bảo vệ</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Địa Điểm / Phòng</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #ede9fe; color: #7c3aed;">
                        <i class="fa-solid fa-location-dot fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-6 fw-bold text-primary lh-1 text-truncate" title="{{ $hoiDong->DiaDiem ?? 'Chưa xếp' }}">{{ $hoiDong->DiaDiem ?? 'Chưa xếp' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Cột trái: Thông tin Hội đồng, Quyết định & Thành viên -->
        <div class="col-lg-5">
            <!-- Card 1: Thông tin cơ bản & Quyết định -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-circle-info me-2 text-primary"></i> Thông Tin Hội Đồng &amp; Quyết Định</span>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#modalSuaQuyetDinh" title="Sửa thông tin Quyết định">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                </div>
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless mb-3 align-middle">
                        <tr>
                            <td class="text-muted fw-semibold" width="35%">Mã HĐ:</td>
                            <td><span class="badge-code">{{ $hoiDong->MaHoiDong }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Học kỳ / Đợt:</td>
                            <td><strong>{{ $hoiDong->hocKy?->TenHocKy ?? $hoiDong->MaHocKy }}</strong> ({{ $hoiDong->DotBaoVe ?? 'Đợt 1' }})</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Ngày bảo vệ:</td>
                            <td>
                                <div><i class="fa-regular fa-calendar text-primary me-2"></i>{{ $hoiDong->NgayBaoVe ? \Carbon\Carbon::parse($hoiDong->NgayBaoVe)->format('d/m/Y') : (\Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('d/m/Y')) }}</div>
                                <div class="small text-muted mt-1">
                                    <i class="fa-regular fa-clock me-1"></i>
                                    {{ \Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('H:i') }} đến {{ \Carbon\Carbon::parse($hoiDong->ThoiGianKetThuc)->format('H:i') }}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Địa điểm:</td>
                            <td><strong class="text-danger"><i class="fa-solid fa-location-dot me-1"></i>{{ $hoiDong->DiaDiem ?? 'Chưa xác định' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Số Quyết định:</td>
                            <td><span class="badge bg-light text-dark border">{{ $hoiDong->SoQuyetDinh ?? 'Chưa có' }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Người ký QĐ:</td>
                            <td>{{ $hoiDong->NguoiKy ?? 'Hiệu trưởng' }} ({{ $hoiDong->ChucVuNguoiKy ?? 'HIỆU TRƯỞNG' }})</td>
                        </tr>
                        @if($hoiDong->GhiChu)
                        <tr>
                            <td class="text-muted fw-semibold">Ghi chú:</td>
                            <td class="small text-muted">{{ $hoiDong->GhiChu }}</td>
                        </tr>
                        @endif
                    </table>

                    <!-- Cập nhật trạng thái Hội đồng -->
                    <form action="{{ route('truongkhoa.hoidong.updateTrangThai', $hoiDong->MaHoiDong) }}" method="POST" class="d-flex gap-2 pt-2 border-top">
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
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalThemThanhVien" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-user-plus me-2"></i> Thêm TV
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3 py-2">GIẢNG VIÊN</th>
                                    <th class="py-2 text-center" width="35%">VAI TRÒ</th>
                                    <th class="pe-3 py-2 text-center" width="15%">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($hoiDong->thanhViens as $tv)
                                <tr>
                                    <td class="ps-3 py-2">
                                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $tv->giangVien->HoTen ?? '—' }}</div>
                                        <div class="small text-muted" style="font-size: 0.75rem;">{{ $tv->giangVien->HocVi ?? ($tv->giangVien->HocHam ?? 'GV') }} &bull; {{ $tv->giangVien->boMon->TenBoMon ?? 'Khoa' }}</div>
                                    </td>
                                    <td class="text-center py-2">
                                        @php
                                            $badgeStyle = match($tv->VaiTro) {
                                                'Chủ tịch' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                                'Thư ký', 'Ủy viên thư ký' => 'bg-info-subtle text-info border border-info-subtle',
                                                'Phản biện', 'Ủy viên phản biện' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                                default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeStyle }} rounded-pill px-3 py-1" style="font-size: 0.75rem;">{{ $tv->VaiTro }}</span>
                                    </td>
                                    <td class="pe-3 text-center py-2">
                                        <div class="d-flex justify-content-center gap-1">
                                            <!-- Nút sửa vai trò -->
                                            <button type="button" class="btn btn-sm btn-light border btn-action-circle d-inline-flex align-items-center" 
                                                    title="Đổi vai trò" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalDoiVaiTro{{ $tv->MaGV }}">
                                                <i class="fa-solid fa-pen-to-square text-primary" style="font-size: 0.75rem;"></i>
                                            </button>

                                            <!-- Nút xóa thành viên -->
                                            <form action="{{ route('truongkhoa.hoidong.xoaThanhVien', [$hoiDong->MaHoiDong, $tv->MaGV]) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border text-danger btn-action-circle d-inline-flex align-items-center" 
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
                                                    <div class="modal-header py-2 bg-light">
                                                        <h6 class="modal-title fw-bold text-dark"><i class="fa-solid fa-user-gear me-2 text-primary"></i> Đổi Vai Trò</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('truongkhoa.hoidong.doiVaiTroThanhVien', [$hoiDong->MaHoiDong, $tv->MaGV]) }}" method="POST">
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
                                                                    <option value="Ủy viên thư ký" {{ in_array($tv->VaiTro, ['Thư ký', 'Ủy viên thư ký']) ? 'selected' : '' }}>Ủy viên thư ký</option>
                                                                    <option value="Ủy viên phản biện" {{ in_array($tv->VaiTro, ['Phản biện', 'Ủy viên phản biện']) ? 'selected' : '' }}>Ủy viên phản biện</option>
                                                                    <option value="Ủy viên" {{ in_array($tv->VaiTro, ['Ủy viên', 'Thành viên']) ? 'selected' : '' }}>Ủy viên</option>
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
            <!-- Card: Thêm đề tài mới vào Hội đồng -->
            @php
                $nhomChuaPhanCong = $nhomDuDieuKien->filter(function($h) use ($hoiDong) {
                    return $h->MaHoiDong !== $hoiDong->MaHoiDong;
                });
            @endphp

            @if($nhomChuaPhanCong->isNotEmpty())
            <div class="card border-0 shadow-sm rounded-3 mb-4" style="background: #f8fafc; border: 1px dashed #cbd5e1 !important;">
                <div class="card-header bg-white border-bottom py-2 text-primary fw-bold">
                    <i class="fa-solid fa-plus me-2 text-primary"></i> Gắn Thêm Đề Tài / Nhóm Sinh Viên Vào Hội Đồng Này
                </div>
                <div class="card-body p-3">
                    <form action="{{ route('truongkhoa.hoidong.phanCongNhom', $hoiDong->MaHoiDong) }}" method="POST">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-7">
                                <label class="form-label small fw-bold text-dark">Chọn Nhóm Đề Tài (Đủ điều kiện)</label>
                                <select name="MaNhom" class="form-select form-select-sm" required>
                                    <option value="">— Chọn đề tài / nhóm sinh viên —</option>
                                    @foreach($nhomChuaPhanCong as $item)
                                    <option value="{{ $item->MaNhom }}">
                                        Nhóm {{ $item->MaNhom }} — {{ Str::limit($item->nhom->deTai->TenDeTai ?? 'Chưa rõ', 45) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-dark">Phòng bảo vệ</label>
                                <input type="text" name="PhongBaoVe" class="form-control form-control-sm" placeholder="VD: Phòng B.304" value="{{ $hoiDong->DiaDiem }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-dark">Thời gian bảo vệ</label>
                                <input type="datetime-local" name="ThoiGianBaoVe" class="form-control form-control-sm"
                                    value="{{ $hoiDong->ThoiGianBatDau ? \Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('Y-m-d\TH:i') : '' }}">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100 fw-bold shadow-xs d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-plus me-2"></i>Thêm Vào HĐ
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Card: Các đề tài đang trong Hội đồng (Phụ lục 2) -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Đề Tài &amp; Sinh Viên Thuộc Hội Đồng ({{ $hoiDong->hoSoBaoVes->count() }})</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3 py-3" style="width: 45%;">ĐỀ TÀI &amp; NHÓM SINH VIÊN</th>
                                    <th class="py-3" style="width: 25%;">CÁN BỘ HƯỚNG DẪN</th>
                                    <th class="py-3 text-center" style="width: 15%;">TURNITIN</th>
                                    <th class="pe-3 py-3 text-center" style="width: 15%;">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($hoiDong->hoSoBaoVes as $hoSo)
                                @php
                                    $nhom = $hoSo->nhom;
                                    $deTai = $nhom?->deTai;
                                    $thanhViens = $nhom?->thanhVienNhoms ?? collect();
                                @endphp
                                <tr>
                                    <td class="ps-3 py-3">
                                        <div class="fw-bold text-primary">{{ $deTai?->TenDeTai ?? 'Chưa có tên đề tài' }}</div>
                                        <div class="d-flex flex-wrap align-items-center gap-1 mt-1 small text-muted">
                                            <span class="badge bg-light text-dark border">Nhóm {{ $nhom?->MaNhom }}</span>
                                            <span>• Sinh viên:</span>
                                            @foreach($thanhViens as $tvn)
                                                <span class="fw-semibold text-dark">{{ $tvn->sinhVien?->HoTen }} ({{ $tvn->sinhVien?->MaSV }})</span>
                                                @if(!$loop->last) , @endif
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-semibold text-dark">{{ $deTai?->giangVien?->HoTen ?? 'Chưa phân công' }}</div>
                                        <div class="small text-muted">{{ $deTai?->giangVien?->boMon?->TenBoMon ?? 'Bộ môn' }}</div>
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
                                    <td class="pe-3 text-center py-3">
                                        <form action="{{ route('truongkhoa.hoidong.huyPhanCongNhom', [$hoiDong->MaHoiDong, $hoSo->MaHoSo]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light text-danger btn-action-circle border" title="Hủy phân công nhóm này khỏi hội đồng" onclick="return confirm('Bạn có chắc chắn muốn loại nhóm này khỏi Hội đồng? Nhóm sẽ quay về trạng thái đủ điều kiện bảo vệ.');">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50 me-2"></i>
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

<!-- Modal Thêm thành viên Hội đồng (Mở rộng toàn Khoa) -->
<div class="modal fade" id="modalThemThanhVien" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-3 bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-user-plus me-2 text-primary"></i> Thêm Thành Viên Vào Hội Đồng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('truongkhoa.hoidong.themThanhVien', $hoiDong->MaHoiDong) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Chọn Giảng viên: <span class="text-danger">*</span></label>
                        @php
                            $existingMaGVs = $hoiDong->thanhViens->pluck('MaGV')->toArray();
                        @endphp
                        <select name="MaGV" class="form-select" required>
                            <option value="">-- Chọn giảng viên thuộc Khoa --</option>
                            @foreach($giangViens as $gv)
                                @if(!in_array($gv->MaGV, $existingMaGVs))
                                <option value="{{ $gv->MaGV }}">
                                    {{ $gv->HoTen }} — {{ $gv->HocHam ?? ($gv->HocVi ?? 'ThS') }} ({{ $gv->boMon?->TenBoMon ?? 'Khoa' }})
                                </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Vai trò trong Hội đồng: <span class="text-danger">*</span></label>
                        <select name="VaiTro" class="form-select" required>
                            <option value="Ủy viên">Ủy viên</option>
                            <option value="Chủ tịch">Chủ tịch Hội đồng</option>
                            <option value="Ủy viên thư ký">Ủy viên thư ký</option>
                            <option value="Ủy viên phản biện">Ủy viên phản biện</option>
                        </select>
                    </div>
                    <div class="p-3 bg-light rounded-3 text-muted small">
                        <i class="fa-solid fa-circle-info text-primary me-2"></i> Trưởng khoa có thể phân công giảng viên từ bất kỳ Bộ môn nào trong Khoa.
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Xác nhận thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa thông tin Quyết định -->
<div class="modal fade" id="modalSuaQuyetDinh" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-3 bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Sửa Thông Tin Quyết Định Ban Hành</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('truongkhoa.hoidong.updateQuyetDinh', $hoiDong->MaHoiDong) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Số Quyết định:</label>
                        <input type="text" name="SoQuyetDinh" class="form-control" value="{{ $hoiDong->SoQuyetDinh }}" placeholder="VD: 125/QĐ-ĐHCNTP">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Ngày Ban Hành:</label>
                        <input type="date" name="NgayBanHanh" class="form-control" value="{{ $hoiDong->NgayBanHanh ? \Carbon\Carbon::parse($hoiDong->NgayBanHanh)->format('Y-m-d') : '' }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Đợt Bảo Vệ:</label>
                        <input type="text" name="DotBaoVe" class="form-control" value="{{ $hoiDong->DotBaoVe }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Người Ký:</label>
                        <input type="text" name="NguoiKy" class="form-control" value="{{ $hoiDong->NguoiKy }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Chức Vụ Người Ký:</label>
                        <input type="text" name="ChucVuNguoiKy" class="form-control" value="{{ $hoiDong->ChucVuNguoiKy }}">
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Cập nhật Quyết định</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
