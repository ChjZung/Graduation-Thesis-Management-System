@extends('layouts.sinhvien')

@section('page_title', 'Đăng Ký Đề Tài Khóa Luận')

@section('content')

<!-- TIẾN ĐỘ THỜI GIAN ĐĂNG KÝ THEO KẾ HOẠCH -->
@if(isset($regState))
<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="border-left: 5px solid {{ $regState['code'] === 'DANG_KY_CHINH_THUC' ? '#10b981' : ($regState['code'] === 'DANG_KY_BO_SUNG' ? '#f59e0b' : ($regState['code'] === 'KHOA_TAM_THOI' ? '#ef4444' : '#6b7280')) }} !important;">
    <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="fs-2">{{ $regState['code'] === 'DANG_KY_CHINH_THUC' ? '🟢' : ($regState['code'] === 'DANG_KY_BO_SUNG' ? '🔄' : ($regState['code'] === 'KHOA_TAM_THOI' ? '🔒' : '⏳')) }}</span>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge {{ $regState['badge'] }} rounded-pill px-3 py-1 fs-6">{{ $regState['label'] }}</span>
                    <span class="text-muted small">| Học kỳ: <strong>{{ $hocKies->firstWhere('MaHocKy', $selectedHocKy)->TenHocKy ?? 'Hiện tại' }}</strong></span>
                </div>
                <div class="text-dark mt-1 fw-medium" style="font-size: 0.92rem;">{{ $regState['message'] }}</div>
            </div>
        </div>
        <div class="text-end">
            <span class="badge bg-light text-dark border px-3 py-2 rounded-3">
                <i class="fa-solid fa-clock me-1 text-primary"></i>Giờ hệ thống: <strong>{{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</strong>
            </span>
        </div>
    </div>
</div>
@endif

<!-- THÔNG TIN NHÓM SINH VIÊN -->
@if(isset($nhom) && $nhom)
@php
    $isGroupReady = ($soThanhVien >= 3);
@endphp
@if(!$isGroupReady)
<div class="alert alert-warning border-warning shadow-sm mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <div class="fw-bold text-dark">
            <i class="fa-solid fa-triangle-exclamation text-warning fs-5 me-2"></i>
            Nhóm của bạn: <strong>{{ $nhom->TenNhom }}</strong> ({{ $soThanhVien }}/3 thành viên) — <span class="text-danger fw-bold">CHƯA ĐỦ ĐIỀU KIỆN ĐĂNG KÝ ĐỀ TÀI</span>
        </div>
        <div class="small text-muted mt-1">
            Quy định: Nhóm phải có <strong>đủ 3 thành viên</strong> thì Trưởng nhóm mới được phép đăng ký đề tài. Bạn còn thiếu <strong>{{ 3 - $soThanhVien }} thành viên</strong>.
        </div>
    </div>
    <div>
        <a href="{{ route('sinhvien.nhom.index', ['hoc_phan' => $nhom->MaHocPhan, 'hoc_ky' => $nhom->MaHocKy]) }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold text-dark shadow-xs">
            <i class="fa-solid fa-users me-1"></i>Vào Quản Lý Nhóm Để Mời Thêm (Còn thiếu {{ 3 - $soThanhVien }} TV)
        </a>
    </div>
</div>
@else
<div class="alert alert-success border-success shadow-sm mb-4 d-flex justify-content-between align-items-center">
    <div>
        <i class="fa-solid fa-circle-check fs-5 me-2 text-success"></i>
        <strong>Nhóm của bạn:</strong> <strong>{{ $nhom->TenNhom }}</strong> ({{ $soThanhVien }}/3 thành viên) — <span class="text-success fw-bold">Đã đủ 3 thành viên, sẵn sàng đăng ký đề tài!</span>
    </div>
</div>
@endif
@endif

<!-- TÌNH TRẠNG ĐĂNG KÝ CỦA NHÓM -->
@if(isset($dangKyCurrent) && $dangKyCurrent)
<div class="card card-premium mb-4 border-start border-4 {{ $dangKyCurrent->TrangThai === 'Đã duyệt' ? 'border-success' : ($dangKyCurrent->TrangThai === 'Từ chối' ? 'border-danger' : 'border-warning') }}">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-2">
            <div>
                <div class="small text-muted text-uppercase fw-bold mb-1">Đơn Đăng Ký Hiện Tại Của Nhóm</div>
                <h5 class="fw-bold text-primary mb-1">{{ $dangKyCurrent->deTai->TenDeTai ?? 'Chưa rõ đề tài' }}</h5>
                <div class="small text-secondary">
                    <i class="fa-solid fa-chalkboard-user me-1"></i>Giảng viên hướng dẫn: <strong>{{ $dangKyCurrent->deTai->giangVien->HoTen ?? 'Chưa rõ' }}</strong>
                </div>
            </div>
            <div class="text-end">
                @if($dangKyCurrent->TrangThai === 'Từ chối')
                    <span class="badge bg-danger rounded-pill fs-6 px-3 py-2"><i class="fa-solid fa-circle-xmark me-1"></i>Đã Từ Chối</span>
                @else
                    <span class="badge bg-success rounded-pill fs-6 px-3 py-2"><i class="fa-solid fa-check-circle me-1"></i>Đã Gán Chính Thức Cho Nhóm</span>
                @endif
            </div>
        </div>

        @if($dangKyCurrent->TrangThai === 'Từ chối')
            <div class="alert alert-danger bg-danger-subtle border-0 rounded-3 p-3 mt-3 mb-0">
                <div class="fw-bold text-danger mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i>Lý do từ chối:</div>
                <div class="text-dark small">{{ $dangKyCurrent->LyDoTuChoi ?? 'Đề tài không phù hợp hoặc đã được phân công cho nhóm khác.' }}</div>
                <div class="small text-danger fw-semibold mt-2">
                    <i class="fa-solid fa-arrow-down me-1"></i>Nhóm của bạn có thể lựa chọn và đăng ký một đề tài khác trong danh sách bên dưới.
                </div>
            </div>
        @else
            @php
                $isDuyetKhoa = $dangKyCurrent->deTai && (in_array($dangKyCurrent->deTai->TrangThai, ['Đã duyệt', 'Trưởng khoa đã duyệt', 'Đã công bố']) || !empty($dangKyCurrent->deTai->NgayDuyetKhoa));
                $hasDeCuong = !empty($dangKyCurrent->deTai?->FileDeCuong);
            @endphp
            <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                @if($isDuyetKhoa)
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="small text-success fw-medium"><i class="fa-solid fa-circle-check me-1"></i>Đề tài đã được Trưởng khoa phê duyệt & gán chính thức cho nhóm.</span>
                        @if($hasDeCuong)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                                <i class="fa-solid fa-file-circle-check me-1"></i>Đã có Đề cương chi tiết
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-3 py-1">
                                <i class="fa-solid fa-lock me-1 text-warning"></i>Không thể hủy — Đang chờ nhận Đề cương
                            </span>
                        @endif
                    </div>
                    @if($hasDeCuong)
                        @php
                            $fileDcUrl = Str::startsWith($dangKyCurrent->deTai->FileDeCuong, ['http', 'storage/']) ? asset($dangKyCurrent->deTai->FileDeCuong) : asset('storage/' . $dangKyCurrent->deTai->FileDeCuong);
                            $fileName = basename($dangKyCurrent->deTai->FileDeCuong);
                        @endphp
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold shadow-xs"
                                    onclick="quickPreviewOutline('{{ $fileDcUrl }}', '{{ $fileName }}', '{{ addslashes($dangKyCurrent->deTai->TenDeTai ?? '') }}')">
                                <i class="fa-solid fa-eye me-1"></i>Xem Nhanh Đề Cương
                            </button>
                            <a href="{{ $fileDcUrl }}" download="{{ $fileName }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                                <i class="fa-solid fa-download me-1"></i>Tải Về
                            </a>
                        </div>
                    @endif
                @elseif(isset($nhom) && $nhom && $nhom->MaTruongNhom === $sinhVien->MaSV)
                    <span class="small text-success fw-medium"><i class="fa-solid fa-circle-check me-1"></i>Đề tài đã được gán cho nhóm của bạn.</span>
                    <form action="{{ route('sinhvien.dangky.destroy', $dangKyCurrent->MaDangKy) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đăng ký đề tài này để chọn đề tài khác?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                            <i class="fa-solid fa-rotate-left me-1"></i>Hủy Đăng Ký Đề Tài Này
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>
@endif

<!-- BƯỚC 1: LỌC DANH SÁCH ĐỀ TÀI THEO: HỌC KỲ -> HỌC PHẦN CHUYÊN NGÀNH -->
<div class="card border-0 shadow-sm rounded-4 mb-3 p-3 bg-light">
    <div class="row g-2 align-items-center mb-2">
        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <span class="fw-bold text-dark"><i class="fa-solid fa-filter text-primary me-2"></i>Bước 1: Lọc Đề Tài (Học kỳ ➔ Học phần chuyên ngành):</span>
                <div class="small text-muted">Lọc danh sách đề tài chính thức theo học kỳ và học phần chuyên ngành</div>
            </div>
        </div>
    </div>
    <div class="row g-2 align-items-center">
        <!-- 1. HỌC KỲ -->
        <div class="col-md-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white fw-bold text-secondary border-end-0"><i class="fa-solid fa-calendar-days text-primary me-1"></i>Học kỳ:</span>
                <select class="form-select form-select-sm fw-semibold border-start-0 bg-white" onchange="window.location.href=this.value;">
                    @foreach($hocKies as $hk)
                        <option value="{{ route('sinhvien.dangky.index', ['HocKy' => $hk->MaHocKy, 'HocPhan' => $selectedHocPhan]) }}" {{ ($selectedHocKy == $hk->MaHocKy || $maHocKy == $hk->MaHocKy) ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }} {{ $hk->TrangThai === 'Đang diễn ra' ? '🔥' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- 2. BỘ MÔN CHUYÊN NGÀNH CỦA SINH VIÊN -->
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white fw-bold text-secondary border-end-0"><i class="fa-solid fa-sitemap text-primary me-1"></i>Bộ môn:</span>
                <span class="form-control form-control-sm bg-light fw-bold text-primary text-truncate">
                    {{ $boMons->firstWhere('MaBoMon', $selectedBoMon)->TenBoMon ?? ($sinhVien->lop->nganh->TenNganh ?? 'Bộ môn chuyên ngành') }}
                </span>
            </div>
        </div>

        <!-- 3. CHỌN HỌC PHẦN (DROPDOWN GỌN GÀNG) -->
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white fw-bold text-secondary border-end-0"><i class="fa-solid fa-graduation-cap text-primary me-1"></i>Học phần:</span>
                <select class="form-select form-select-sm fw-semibold border-start-0 bg-white" onchange="window.location.href=this.value;">
                    <option value="{{ route('sinhvien.dangky.index', ['HocKy' => $selectedHocKy]) }}">-- Tất cả học phần của ngành --</option>
                    @foreach($hocPhans as $hp)
                        @php
                            $hpCode = is_object($hp) ? $hp->MaHocPhan : $hp;
                            $hpName = is_object($hp) ? $hp->TenHocPhan : $hp;
                            $isSelected = ($selectedHocPhan === $hpCode || $selectedHocPhan === $hpName);
                        @endphp
                        <option value="{{ route('sinhvien.dangky.index', ['HocKy' => $selectedHocKy, 'HocPhan' => $hpCode]) }}" {{ $isSelected ? 'selected' : '' }}>
                            {{ $hpName }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card card-premium">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-clipboard-list text-primary me-2"></i>Bước 2: Danh Sách Đề Tài Đã Công Bố ({{ $detais->total() }} đề tài)</span>
        @if($selectedHocPhan)
            @php
                $selHpName = $hocPhans->firstWhere('MaHocPhan', $selectedHocPhan)->TenHocPhan ?? $selectedHocPhan;
            @endphp
            <span class="badge bg-primary rounded-pill px-3 py-1">{{ $selHpName }}</span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th width="12%">Mã Đề Tài</th>
                        <th width="38%">Tên Đề Tài & Học Phần</th>
                        <th width="20%">Giảng Viên Đề Xuất</th>
                        <th width="13%" class="text-center">Số SV Tối Đa</th>
                        <th width="17%" class="text-center">Trạng Thái / Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detais as $dt)
                    @php
                        // Kiểm tra xem nhóm mình có đăng ký đề tài này không
                        $myGroupRegThis = (isset($dangKyCurrent) && $dangKyCurrent && $dangKyCurrent->MaDeTai === $dt->MaDeTai) ? $dangKyCurrent : null;

                        // Đề tài đã có nhóm khác đăng ký (Chờ duyệt hoặc Đã duyệt)
                        $isTakenByOther = isset($deTaiDaDangKys[$dt->MaDeTai]) && (!isset($nhom) || $deTaiDaDangKys[$dt->MaDeTai]->MaNhom !== $nhom->MaNhom);

                        // Nhóm đang có 1 đơn ACTIVE (Chờ duyệt hoặc Đã duyệt)
                        $hasActiveRegistration = isset($dangKyCurrent) && $dangKyCurrent && in_array($dangKyCurrent->TrangThai, ['Chờ duyệt', 'Đã duyệt']);
                    @endphp
                    <tr>
                        <td><span class="badge bg-light text-dark fw-bold border">{{ $dt->MaDeTai }}</span></td>
                        <td>
                            <div class="fw-bold text-primary-custom">{{ $dt->TenDeTai }}</div>
                            <div class="small text-muted mb-1">{{ Str::limit($dt->MoTa, 90) }}</div>
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.72rem;">
                                    {{ $dt->hocPhanRef->TenHocPhan ?? $dt->HocPhan ?? 'Khóa luận cử nhân' }}
                                </span>
                                @if($dt->LinhVuc)
                                    <span class="badge bg-light text-secondary border" style="font-size: 0.72rem;">{{ $dt->LinhVuc }}</span>
                                @endif
                                @if($dt->nganh)
                                    <span class="badge bg-light text-secondary border" style="font-size: 0.72rem;">{{ $dt->nganh->TenNganh }}</span>
                                @endif
                                @if($dt->FileDeCuong)
                                    @php
                                        $dtDcUrl = Str::startsWith($dt->FileDeCuong, ['http', 'storage/']) ? asset($dt->FileDeCuong) : asset('storage/' . $dt->FileDeCuong);
                                        $dtDcName = basename($dt->FileDeCuong);
                                    @endphp
                                    <button type="button" class="btn badge bg-success-subtle text-success border text-decoration-none" style="font-size: 0.72rem; cursor: pointer;"
                                            onclick="quickPreviewOutline('{{ $dtDcUrl }}', '{{ $dtDcName }}', '{{ addslashes($dt->TenDeTai) }}')">
                                        <i class="fa-solid fa-eye me-1"></i> Xem đề cương
                                    </button>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $dt->giangVien->HoTen ?? 'Chưa rõ' }}</div>
                            <div class="small text-muted">{{ $dt->giangVien->HocVi ?? '' }}</div>
                        </td>
                        <td class="text-center fw-bold">{{ $dt->SoLuongSinhVienToiDa }} SV</td>
                        <td class="text-center">
                            @if($myGroupRegThis)
                                @if($myGroupRegThis->TrangThai === 'Từ chối')
                                    <span class="badge bg-danger rounded-pill px-3 py-2" title="{{ $myGroupRegThis->LyDoTuChoi }}">
                                        <i class="fa-solid fa-circle-xmark me-1"></i>Đã Bị Từ Chối
                                    </span>
                                @else
                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                        <i class="fa-solid fa-check-circle me-1"></i>Đã Gán Cho Nhóm Của Bạn
                                    </span>
                                @endif
                            @elseif($isTakenByOther)
                                <button class="btn btn-sm btn-secondary rounded-pill px-3" disabled title="Đề tài này đã có nhóm khác đăng ký">
                                    <i class="fa-solid fa-lock me-1"></i>Đã Có Nhóm Đăng Ký
                                </button>
                            @elseif(isset($nhom) && $nhom)
                                @if(isset($regState) && !$regState['can_register'])
                                    <button class="btn btn-sm btn-secondary rounded-pill px-3" disabled title="{{ $regState['message'] }}">
                                        <i class="fa-solid fa-lock me-1"></i>{{ $regState['label'] }}
                                    </button>
                                @elseif($nhom->MaTruongNhom !== $sinhVien->MaSV)
                                    <span class="text-muted small">Chỉ Trưởng nhóm mới có quyền đăng ký</span>
                                @elseif($hasActiveRegistration)
                                    <button class="btn btn-sm btn-secondary rounded-pill px-3" disabled title="Nhóm của bạn đã đăng ký một đề tài khác rồi">
                                        <i class="fa-solid fa-lock me-1"></i>Nhóm Đã Có Đề Tài
                                    </button>
                                @elseif($soThanhVien < 3)
                                    <button class="btn btn-sm btn-secondary rounded-pill px-3" disabled title="Quy định: Nhóm phải có đủ 3 thành viên mới được phép đăng ký đề tài">
                                        <i class="fa-solid fa-lock me-1"></i>Chưa Đủ 3 TV ({{ $soThanhVien }}/3)
                                    </button>
                                @else
                                    <form action="{{ route('sinhvien.dangky.store') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="MaDeTai" value="{{ $dt->MaDeTai }}">
                                        <button type="submit" class="btn btn-sm {{ isset($regState) && $regState['is_supplementary'] ? 'btn-warning text-dark' : 'btn-primary' }} rounded-pill px-3 fw-bold shadow-sm" onclick="return confirm('Bạn có chắc chắn muốn đại diện nhóm đăng ký đề tài {{ $dt->TenDeTai }}? Sau khi đăng ký, đề tài sẽ được gán trực tiếp cho nhóm.');">
                                            <i class="fa-solid fa-pen-to-square me-1"></i>{{ isset($regState) && $regState['is_supplementary'] ? 'Đăng Ký Bổ Sung' : 'Đăng Ký' }}
                                        </button>
                                    </form>
                                @endif
                            @else
                                <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3">Cần tạo nhóm trước</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-clipboard-question fs-1 text-light mb-3 d-block"></i>
                            Hiện tại chưa có đề tài nào được công bố để đăng ký.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($detais->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $detais->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@include('partials.modal_preview_decuong')
@endsection