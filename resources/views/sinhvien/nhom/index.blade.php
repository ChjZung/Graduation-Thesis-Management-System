@extends('layouts.sinhvien')

@section('page_title', 'Nhóm Khóa Luận')

@section('content')
<!-- THANH BỘ LỌC TÌM KIẾM THEO: HỌC KỲ -> HỌC PHẦN CHUYÊN NGÀNH -->
<div class="card border-0 shadow-sm rounded-4 mb-4 p-3 bg-light">
    <div class="row g-2 align-items-center mb-2">
        <div class="col-12 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="fw-bold text-dark"><i class="fa-solid fa-filter text-primary me-2"></i>Bộ Lọc Nhóm Khóa Luận (Học kỳ ➔ Học phần chuyên ngành):</span>
            <span class="small text-muted">Mỗi sinh viên chỉ được tham gia tối đa 1 nhóm trong cùng một đợt/học kỳ khóa luận</span>
        </div>
    </div>
    <div class="row g-2 align-items-center">
        <!-- 1. CHỌN HỌC KỲ -->
        <div class="col-md-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white fw-bold text-secondary border-end-0"><i class="fa-solid fa-calendar-days text-primary me-1"></i>Học kỳ:</span>
                <select class="form-select form-select-sm fw-semibold border-start-0 bg-white" id="filter_hoc_ky" onchange="onHocKyFilterChange()">
                    @foreach($hocKies as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ $maHocKy == $hk->MaHocKy ? 'selected' : '' }}>
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
                @if(isset($nhomHocKyHienTai) && $nhomHocKyHienTai)
                    <span class="form-control form-control-sm bg-light fw-bold text-primary text-truncate">
                        {{ $boMons->firstWhere('MaBoMon', $selectedBoMon)->TenBoMon ?? ($sinhVien->lop->nganh->TenNganh ?? 'Bộ môn chuyên ngành') }}
                    </span>
                    <input type="hidden" id="filter_bo_mon" value="{{ $selectedBoMon }}">
                @else
                    <select class="form-select form-select-sm fw-semibold border-start-0 bg-white" id="filter_bo_mon" onchange="onBoMonChange()">
                        <option value="ALL" {{ ($selectedBoMon === 'ALL' || !$selectedBoMon) ? 'selected' : '' }}>-- Tất cả bộ môn --</option>
                        @foreach($boMons as $bm)
                            <option value="{{ $bm->MaBoMon }}" {{ $selectedBoMon === $bm->MaBoMon ? 'selected' : '' }}>
                                {{ $bm->TenBoMon }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

        <!-- 3. CHỌN HỌC PHẦN CHUYÊN NGÀNH -->
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white fw-bold text-secondary border-end-0"><i class="fa-solid fa-graduation-cap text-primary me-1"></i>Học phần:</span>
                <select class="form-select form-select-sm fw-semibold border-start-0 bg-white" id="filter_hoc_phan" onchange="onHocPhanFilterChange()" {{ (isset($nhomHocKyHienTai) && $nhomHocKyHienTai) ? 'disabled' : '' }}>
                    @if(!isset($nhomHocKyHienTai) || !$nhomHocKyHienTai)
                        <option value="ALL" {{ ($selectedHocPhan == 'ALL' || !$selectedHocPhan) ? 'selected' : '' }}>
                            -- Tất cả học phần (Xem tất cả nhóm) --
                        </option>
                    @endif
                    @foreach($hocPhans as $hp)
                        <option value="{{ $hp->MaHocPhan }}" {{ $selectedHocPhan == $hp->MaHocPhan ? 'selected' : '' }}>
                            {{ $hp->TenHocPhan }} {{ (isset($nhomHocKyHienTai) && $nhomHocKyHienTai) ? '★ (Nhóm của bạn)' : '' }}
                        </option>
                    @endforeach
                </select>
                @if(isset($nhomHocKyHienTai) && $nhomHocKyHienTai)
                    <input type="hidden" id="filter_hoc_phan_fixed" value="{{ $selectedHocPhan }}">
                @endif
            </div>
        </div>
    </div>
</div>

<!-- LỜI MỜI GIA NHẬP NHÓM ĐANG CHỜ (HIỂN THỊ NỔI BẬT NGAY TRÊN ĐẦU TRANG) -->
@if(isset($loiMois) && $loiMois->count() > 0)
<div class="alert alert-warning border-warning shadow-sm mb-4">
    <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-envelope-open-text me-2 text-warning"></i>Bạn có {{ $loiMois->count() }} lời mời tham gia nhóm khóa luận!</h5>
    @foreach($loiMois as $lm)
    <div class="d-flex flex-wrap justify-content-between align-items-center bg-white p-3 rounded-3 mt-2 border">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <span>Lời mời từ Trưởng nhóm <strong>{{ $lm->nhom->truongNhom->HoTen ?? 'Bạn học' }}</strong> (MSSV: <code>{{ $lm->nhom->truongNhom->taiKhoan->TenDangNhap ?? $lm->nhom->MaTruongNhom }}</code>)</span>
                @if($lm->nhom && $lm->nhom->hocKy)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="fa-solid fa-calendar-alt me-1"></i>{{ $lm->nhom->hocKy->TenHocKy }}</span>
                @endif
                @if($lm->nhom && $lm->nhom->hocPhan)
                    <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="fa-solid fa-book me-1"></i>{{ $lm->nhom->hocPhan->TenHocPhan }}</span>
                @endif
            </div>
            <div class="small text-muted">Gia nhập nhóm: <strong class="text-primary">{{ $lm->nhom->TenNhom ?? '' }}</strong> (Hiện có {{ $lm->nhom->thanhViens->count() }}/3 thành viên)</div>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 btn-view-group-detail" data-manhom="{{ $lm->MaNhom }}">
                <i class="fa-solid fa-eye me-1"></i>Xem Nhóm
            </button>
            <form action="{{ route('sinhvien.nhom.xacNhanLoiMoi', $lm->MaNhom) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-check me-1"></i>Chấp Nhận
                </button>
            </form>
            <form action="{{ route('sinhvien.nhom.tuChoiLoiMoi', $lm->MaNhom) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">Từ Chối</button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endif

@if(!$nhomCurrent)
<!-- ======================================================== -->
<!-- 1. CHƯA CÓ NHÓM -> GIAO DIỆN TÌM KIẾM & DANH SÁCH NHÓM THEO PLAN -->
<!-- ======================================================== -->

<!-- HEADER: TIÊU ĐỀ & NÚT TẠO NHÓM NHANH & THANH TÌM KIẾM CHÍNH -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h4 class="fw-bold text-primary mb-1"><i class="fa-solid fa-users me-2"></i>Nhóm Môn: {{ $selectedHocPhan === 'ALL' ? 'Tất Cả Học Phần' : ($hocPhans->firstWhere('MaHocPhan', $selectedHocPhan)->TenHocPhan ?? 'Khóa Luận') }}</h4>
                <p class="text-muted mb-0">Quản lý nhóm của bạn hoặc tìm kiếm nhóm đang mở để xin tham gia.</p>
            </div>
            <div>
                @if(isset($nhomHocKyHienTai) && $nhomHocKyHienTai)
                    <button type="button" class="btn btn-secondary btn-lg rounded-pill px-4 fw-bold shadow-sm" disabled title="Bạn đã thuộc nhóm {{ $nhomHocKyHienTai->TenNhom }} trong học kỳ này">
                        <i class="fa-solid fa-ban me-2"></i>Đã Có Nhóm ({{ $nhomHocKyHienTai->TenNhom }})
                    </button>
                @elseif(isset($isTaoNhomOpen) && $isTaoNhomOpen)
                    <button type="button" class="btn btn-success btn-lg rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateGroup">
                        <i class="fa-solid fa-plus-circle me-2"></i>+ Tạo Nhóm
                    </button>
                @endif
            </div>
        </div>

        <!-- THANH TÌM KIẾM NỔI BẬT ĐẶT NGAY TẠI HEADER -->
        <form method="GET" action="{{ route('sinhvien.nhom.index') }}" class="row g-2 align-items-center bg-light p-3 rounded-3 border">
            <input type="hidden" name="hoc_phan" value="{{ $selectedHocPhan }}">
            <input type="hidden" name="hoc_ky" value="{{ $maHocKy }}">
            <input type="hidden" name="bo_mon" value="{{ $selectedBoMon }}">
            <div class="col-md-9 col-lg-10">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-end-0 text-primary"><i class="fa-solid fa-magnifying-glass fs-5"></i></span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0 fw-semibold fs-6 input-search-sync" placeholder="🔍 Nhập MSSV hoặc tên nhóm (ví dụ: 2001210090, Nhóm 2001210090)..." value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-md-3 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow-sm">
                    <i class="fa-solid fa-search me-1"></i>Tìm Kiếm
                </button>
                @if(request('q'))
                <a href="{{ route('sinhvien.nhom.index', ['hoc_phan' => $selectedHocPhan, 'hoc_ky' => $maHocKy, 'bo_mon' => $selectedBoMon]) }}" class="btn btn-outline-secondary btn-lg rounded-pill px-3" title="Xóa tìm kiếm">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

@if(isset($nhomHocKyHienTai) && $nhomHocKyHienTai)
    <div class="alert alert-warning border-warning shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <i class="fa-solid fa-circle-exclamation fs-3 text-warning"></i>
            <div>
                <strong class="text-dark">Bạn đã thuộc nhóm "{{ $nhomHocKyHienTai->TenNhom }}" (Môn: {{ $nhomHocKyHienTai->hocPhan->TenHocPhan ?? $nhomHocKyHienTai->MaHocPhan }}) trong học kỳ này!</strong>
                <div class="small text-secondary">Quy định: Mỗi sinh viên chỉ được tham gia tối đa 1 nhóm trong cùng một học kỳ khóa luận.</div>
            </div>
        </div>
        <a href="{{ route('sinhvien.nhom.index', ['hocky' => $nhomHocKyHienTai->MaHocKy, 'hocphan' => $nhomHocKyHienTai->MaHocPhan]) }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold">
            <i class="fa-solid fa-arrow-right me-1"></i>Xem Nhóm Của Bạn
        </a>
    </div>
@endif

@if(isset($groupPhaseState) && !$isTaoNhomOpen)
    @if($groupPhaseState['code'] === 'CHUA_MO')
        <div class="alert alert-info bg-info-subtle border-info shadow-sm mb-4 d-flex align-items-center gap-3">
            <i class="fa-solid fa-calendar-day fs-3 text-primary"></i>
            <div>
                <strong class="text-primary fs-6">{{ $groupPhaseState['message'] }}</strong>
                <div class="small text-muted">Hệ thống sẽ mở cổng tạo và ghép nhóm đúng mốc thời gian quy định theo Kế hoạch khóa luận.</div>
            </div>
        </div>
    @else
        <div class="alert alert-danger bg-danger-subtle border-danger shadow-sm mb-4 d-flex align-items-center gap-3">
            <i class="fa-solid fa-clock-rotate-left fs-3 text-danger"></i>
            <div>
                <strong class="text-danger fs-6">{{ $groupPhaseState['message'] }}</strong>
                <div class="small text-muted">Cơ cấu danh sách các nhóm đã được chốt để phục vụ cho các đợt đăng ký đề tài tiếp theo.</div>
            </div>
        </div>
    @endif
@endif


<!-- KHUNG DANH SÁCH NHÓM -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <!-- DANH SÁCH CÁC NHÓM (GRID CARDS) -->
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-list-check me-2"></i>Danh Sách Nhóm Khóa Luận</h5>
                @if(request('q'))
                    <span class="small text-muted"><i class="fa-solid fa-magnifying-glass me-1"></i>Kết quả tìm kiếm cho từ khóa: "<strong>{{ request('q') }}</strong>"</span>
                @endif
            </div>
            <span class="badge bg-light text-dark border fs-6 px-3 py-2" id="badge-group-count">Tìm thấy {{ count($nhomsOpen ?? []) }} nhóm</span>
        </div>

        <div class="row g-3">
            @forelse($nhomsOpen ?? [] as $no)
            @php
                $memberCount = $no->thanhViens->count();
                $isFull = ($memberCount >= 3);
                $hasRequestedThis = isset($yeuCauDaGui[$no->MaNhom]);
                $hasInviteFromThis = isset($loiMois) && $loiMois->contains('MaNhom', $no->MaNhom);
            @endphp
            <div class="col-lg-6 group-item-card">
                <div class="card rounded-3 p-3 h-100 shadow-sm hover-shadow transition {{ $hasInviteFromThis ? 'border border-warning border-2 bg-warning-subtle bg-opacity-10' : 'border' }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            @if($hasInviteFromThis)
                                <div class="mb-1"><span class="badge bg-warning text-dark rounded-pill px-2 py-1"><i class="fa-solid fa-envelope-open-text me-1"></i>Đã mời bạn</span></div>
                            @endif
                            <h5 class="fw-bold text-primary mb-1">{{ $no->TenNhom }}</h5>
                            <div class="d-flex flex-wrap gap-1 align-items-center mb-1">
                                @if($no->hocPhan)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">
                                        <i class="fa-solid fa-graduation-cap me-1"></i>{{ $no->hocPhan->TenHocPhan }}
                                    </span>
                                @endif
                                @if($no->hocKy)
                                    <span class="badge bg-light text-secondary border small">
                                        <i class="fa-solid fa-calendar me-1"></i>{{ $no->hocKy->TenHocKy }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if($isFull)
                            <span class="badge bg-secondary rounded-pill px-3 py-1"><i class="fa-solid fa-lock me-1"></i>Đã Đủ 3/3</span>
                        @else
                            <span class="badge bg-success rounded-pill px-3 py-1"><i class="fa-solid fa-user-plus me-1"></i>Đang Tuyển Thành Viên</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <div class="fw-semibold text-dark">
                            <i class="fa-solid fa-crown text-warning me-1"></i>Nhóm trưởng: <strong>{{ $no->truongNhom->HoTen ?? 'Chưa rõ' }}</strong>
                        </div>
                        <div class="small text-muted">
                            MSSV: <code>{{ $no->truongNhom->taiKhoan->TenDangNhap ?? $no->MaTruongNhom }}</code> | Lớp: {{ $no->truongNhom->lop->TenLop ?? 'Chưa rõ' }}
                        </div>
                        <div class="small text-muted mt-2 d-flex align-items-center gap-2">
                            <span><i class="fa-solid fa-users text-primary me-1"></i><strong>{{ $memberCount }} / 3</strong> thành viên</span>
                            @if(!$isFull)
                                <span class="badge bg-warning text-dark rounded-pill">Còn {{ 3 - $memberCount }} vị trí</span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 btn-view-group-detail" data-manhom="{{ $no->MaNhom }}">
                            <i class="fa-solid fa-eye me-1"></i>Xem Chi Tiết
                        </button>

                        @if($hasInviteFromThis)
                            <div class="d-flex gap-1">
                                <form action="{{ route('sinhvien.nhom.xacNhanLoiMoi', $no->MaNhom) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-bold">
                                        <i class="fa-solid fa-check me-1"></i>Chấp Nhận
                                    </button>
                                </form>
                                <form action="{{ route('sinhvien.nhom.tuChoiLoiMoi', $no->MaNhom) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2">
                                        Từ Chối
                                    </button>
                                </form>
                            </div>
                        @elseif($hasRequestedThis)
                            <form action="{{ route('sinhvien.nhom.huyXinGiaNhap', $no->MaNhom) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 text-dark fw-bold">
                                    <i class="fa-solid fa-clock me-1"></i>Đang Chờ Duyệt (Hủy)
                                </button>
                            </form>
                        @elseif($isFull)
                            <button class="btn btn-secondary btn-sm rounded-pill px-3" disabled>
                                <i class="fa-solid fa-ban me-1"></i>Đã Đủ Thành Viên
                            </button>
                        @elseif(!($isTaoNhomOpen ?? true))
                            <button class="btn btn-secondary btn-sm rounded-pill px-3" disabled title="{{ $groupPhaseState['message'] ?? 'Cổng tạo và ghép nhóm chưa mở' }}">
                                <i class="fa-solid fa-lock me-1"></i>Đã Khóa Xin Vào
                            </button>
                        @elseif(isset($nhomHocKyHienTai) && $nhomHocKyHienTai)
                            <button class="btn btn-secondary btn-sm rounded-pill px-3" disabled title="Bạn đã thuộc nhóm {{ $nhomHocKyHienTai->TenNhom }} trong học kỳ này">
                                <i class="fa-solid fa-ban me-1"></i>Đã Có Nhóm
                            </button>
                        @else
                            <form action="{{ route('sinhvien.nhom.xinGiaNhap', $no->MaNhom) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold" onclick="return confirm('Bạn có chắc muốn gửi yêu cầu xin gia nhập nhóm {{ $no->TenNhom }}?');">
                                    <i class="fa-solid fa-hand me-1"></i>Xin Gia Nhập
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-users-slash fs-1 opacity-50 mb-3 d-block"></i>
                    @if(isset($isTaoNhomOpen) && $isTaoNhomOpen)
                        <p class="small">Hãy thử tìm kiếm với từ khóa khác hoặc bấm nút <strong>"+ Tạo Nhóm"</strong> ở góc trên bên phải để khởi tạo nhóm của riêng bạn!</p>
                    @else
                        <p class="small">Hãy thử tìm kiếm với từ khóa khác.</p>
                    @endif
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>

@else
<!-- ======================================================== -->
<!-- 2. ĐÃ CÓ NHÓM -> HIỂN THỊ THÔNG TIN CHI TIẾT NHÓM CỦA MÌNH -->
<!-- ======================================================== -->

<div class="card card-premium mb-4">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span><i class="fa-solid fa-users text-primary me-2"></i>Nhóm: <strong>{{ $nhomCurrent->TenNhom }}</strong> (Môn: <strong class="text-primary">{{ $nhomCurrent->hocPhan->TenHocPhan ?? 'Khóa luận tốt nghiệp' }}</strong>)</span>
            @if($nhomCurrent->hocKy)
                <span class="badge bg-light text-primary border rounded-pill px-3 py-1">
                    <i class="fa-solid fa-calendar-days me-1"></i>{{ $nhomCurrent->hocKy->TenHocKy }}
                </span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($isNhomLocked)
                <span class="badge bg-success rounded-pill px-3 py-2"><i class="fa-solid fa-lock me-1"></i>Nhóm Đã Khóa (Đã Có Đề Tài)</span>
            @elseif($nhomCurrent->thanhViens->count() === 3)
                <span class="badge bg-success rounded-pill px-3 py-2"><i class="fa-solid fa-check-circle me-1"></i>Đã Đủ 3 Thành Viên</span>
            @else
                <span class="badge bg-warning text-dark rounded-pill px-3 py-2"><i class="fa-solid fa-user-clock me-1"></i>Đang Có {{ $nhomCurrent->thanhViens->count() }}/3 Thành Viên</span>
            @endif
        </div>
    </div>
    <div class="card-body p-4">
        <!-- ĐỀ TÀI CỦA NHÓM -->
        @php
            $currentDeTai = $nhomCurrent->deTai ?? $nhomCurrent->dangKyDeTai?->deTai;
        @endphp
        @if($currentDeTai)
            <div class="p-3 bg-light border-start border-4 border-success rounded-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <span class="small text-muted text-uppercase fw-bold">Đề Tài Đã Gán Cho Nhóm</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            <i class="fa-solid fa-circle-check me-1"></i>Đã phê duyệt
                        </span>
                        @if($currentDeTai->FileDeCuong)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="fa-solid fa-file-lines me-1"></i>Đã có đề cương
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle">
                                <i class="fa-solid fa-clock me-1 text-warning"></i>Đang chờ nhận đề cương
                            </span>
                        @endif
                    </div>
                    <h5 class="fw-bold text-success mb-1">
                        <a href="javascript:void(0)" class="text-success text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalTopicDetail" title="Bấm để xem chi tiết đề tài">
                            {{ $currentDeTai->TenDeTai }} <i class="fa-solid fa-arrow-up-right-from-square fs-6 ms-1 text-muted"></i>
                        </a>
                    </h5>
                    <div class="small text-secondary d-flex flex-wrap gap-3 align-items-center mt-1">
                        <span><i class="fa-solid fa-chalkboard-user me-1 text-primary"></i>Giảng viên hướng dẫn: <strong>{{ $currentDeTai->giangVien->HoTen ?? 'Chưa gán' }}</strong></span>
                        @if($currentDeTai->giangVien?->boMon)
                            <span class="text-muted">| Bộ môn: {{ $currentDeTai->giangVien->boMon->TenBoMon }}</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTopicDetail">
                        <i class="fa-solid fa-circle-info me-1"></i>Xem Chi Tiết Đề Tài
                    </button>
                    @if($currentDeTai->FileDeCuong)
                        @php
                            $fileDcUrl = Str::startsWith($currentDeTai->FileDeCuong, ['http', 'storage/']) ? asset($currentDeTai->FileDeCuong) : asset('storage/' . $currentDeTai->FileDeCuong);
                            $fileName = basename($currentDeTai->FileDeCuong);
                        @endphp
                        <button type="button" class="btn btn-success btn-sm rounded-pill px-3 shadow-xs fw-semibold"
                                onclick="quickPreviewOutline('{{ $fileDcUrl }}', '{{ $fileName }}', '{{ addslashes($currentDeTai->TenDeTai ?? '') }}')">
                            <i class="fa-solid fa-eye me-1"></i>Xem Nhanh Đề Cương
                        </button>
                        <a href="{{ $fileDcUrl }}" download="{{ $fileName }}" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs fw-semibold">
                            <i class="fa-solid fa-download me-1"></i>Tải Về
                        </a>
                    @endif
                </div>
            </div>
        @else
            @php
                $memberCountCurrent = $nhomCurrent->thanhViens->count();
                $isFullGroup = ($memberCountCurrent >= 3);
            @endphp
            @if(!$isFullGroup)
                <div class="alert alert-warning border-warning d-flex flex-wrap justify-content-between align-items-center mb-4">
                    <div>
                        <div class="fw-bold text-dark mb-1">
                            <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Chưa Đủ Thành Viên Để Đăng Ký Đề Tài (Đang có {{ $memberCountCurrent }}/3 thành viên)
                        </div>
                        <div class="small text-muted">
                            Theo quy định, nhóm của bạn phải tập hợp <strong>đủ 3 thành viên chính thức</strong> thì Trưởng nhóm mới có thể đăng ký đề tài. Bạn cần tuyển thêm <strong>{{ 3 - $memberCountCurrent }} thành viên</strong> nữa.
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3 fw-bold mt-2 mt-md-0" disabled title="Nhóm cần đủ 3 thành viên mới được phép đăng ký đề tài">
                            <i class="fa-solid fa-lock me-1"></i>Đăng Ký Đề Tài (Cần Đủ 3/3 SV)
                        </button>
                    </div>
                </div>
            @else
                <div class="alert alert-success border-success d-flex flex-wrap justify-content-between align-items-center mb-4">
                    <div>
                        <div class="fw-bold text-success mb-1">
                            <i class="fa-solid fa-circle-check text-success me-2"></i>Nhóm Đã Đủ 3 Thành Viên! Sẵn Sàng Đăng Ký Đề Tài
                        </div>
                        <div class="small text-muted">
                            Nhóm của bạn đã đạt chỉ tiêu 3 thành viên và chưa đăng ký Đề tài. Trưởng nhóm có thể chọn đề tài ngay bây giờ.
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('sinhvien.dangky.index', ['HocPhan' => $nhomCurrent->MaHocPhan ?? 'HP_KLCN', 'HocKy' => $nhomCurrent->MaHocKy]) }}" class="btn btn-success btn-sm rounded-pill px-3 fw-bold mt-2 mt-md-0 shadow-sm">
                            <i class="fa-solid fa-clipboard-list me-1"></i>Đăng Ký Đề Tài Ngay
                        </a>
                    </div>
                </div>
            @endif
        @endif

        <!-- DANH SÁCH THÀNH VIÊN CHÍNH THỨC -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-primary-custom mb-0">
                <i class="fa-solid fa-user-group me-2"></i>Thành Viên Chính Thức ({{ $nhomCurrent->thanhViens->count() }}/3)
            </h5>
            @if($isNhomLocked)
                <span class="small text-muted fst-italic"><i class="fa-solid fa-lock me-1"></i>Danh sách thành viên đã được chốt và khóa cố định.</span>
            @endif
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="15%">MSSV</th>
                        <th width="30%">Họ Và Tên</th>
                        <th width="25%">Lớp & Ngành</th>
                        <th width="15%" class="text-center">Vai Trò</th>
                        @if($nhomCurrent->MaTruongNhom === $sinhVien->MaSV && !$isNhomLocked)
                        <th width="15%" class="text-center">Thao Tác</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($nhomCurrent->thanhViens as $tv)
                    <tr>
                        <td>
                            <a href="javascript:void(0)" class="btn-view-profile text-decoration-none fw-bold" 
                               data-mssv="{{ $tv->sinhVien->taiKhoan->TenDangNhap ?? $tv->MaSV }}"
                               title="Bấm để xem thông tin chi tiết">
                                <span class="badge bg-light text-primary border"><code>{{ $tv->sinhVien->taiKhoan->TenDangNhap ?? $tv->MaSV }}</code></span>
                            </a>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $tv->sinhVien->HoTen ?? '' }}</div>
                            <div class="small text-muted">{{ $tv->sinhVien->Email ?? '' }}</div>
                        </td>
                        <td>
                            <div>{{ $tv->sinhVien->lop->TenLop ?? 'Chưa rõ' }}</div>
                            <div class="small text-muted">{{ $tv->sinhVien->lop->nganh->TenNganh ?? '' }}</div>
                        </td>
                        <td class="text-center">
                            @if($tv->VaiTro === 'Trưởng nhóm')
                                <span class="badge bg-warning text-dark rounded-pill px-3">Trưởng nhóm</span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3">Thành viên</span>
                            @endif
                        </td>
                        @if($nhomCurrent->MaTruongNhom === $sinhVien->MaSV && !$isNhomLocked)
                        <td class="text-center">
                            @if($tv->MaSV !== $sinhVien->MaSV)
                                <form action="{{ route('sinhvien.nhom.khaiTru', ['id' => $nhomCurrent->MaNhom, 'maSV' => $tv->MaSV]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn KHAI TRỪ sinh viên {{ $tv->sinhVien->HoTen }} khỏi nhóm? Sinh viên sẽ trở về trạng thái tự do.');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Khai trừ thành viên">
                                        <i class="fa-solid fa-user-minus me-1"></i>Khai Trừ
                                    </button>
                                </form>
                            @else
                                <span class="small text-muted fst-italic">Trưởng nhóm</span>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- ======================================================== -->
        <!-- KHU VỰC DÀNH CHO TRƯỞNG NHÓM (CHỈ HIỂN THỊ KHI CHƯA KHÓA NHÓM) -->
        <!-- ======================================================== -->
        @if($nhomCurrent->MaTruongNhom === $sinhVien->MaSV && !$isNhomLocked && $nhomCurrent->thanhViens->count() < 3)
        <hr class="my-4">
        @if(!($isTaoNhomOpen ?? false))
            <div class="alert {{ ($groupPhaseState['code'] ?? '') === 'CHUA_MO' ? 'alert-info bg-info-subtle border-info' : 'alert-danger bg-danger-subtle border-danger' }} p-3 rounded-3 d-flex align-items-center gap-3">
                <i class="fa-solid {{ ($groupPhaseState['code'] ?? '') === 'CHUA_MO' ? 'fa-calendar-day text-primary' : 'fa-lock text-danger' }} fs-3"></i>
                <div>
                    <h6 class="fw-bold mb-1 {{ ($groupPhaseState['code'] ?? '') === 'CHUA_MO' ? 'text-primary' : 'text-danger' }}">
                        {{ ($groupPhaseState['code'] ?? '') === 'CHUA_MO' ? 'Chưa Mở Thời Gian Ghép & Mời Thành Viên' : 'Đã Khóa Tuyển Thành Viên (Hết Hạn Kế Hoạch)' }}
                    </h6>
                    <p class="small mb-0 text-muted">{{ $groupPhaseState['message'] ?? 'Cổng quản lý và ghép nhóm thành viên hiện đang đóng.' }}</p>
                </div>
            </div>
        @else
        <div class="row g-4">
            <!-- TÌM KIẾM, XÁC THỰC & MỜI THÀNH VIÊN THEO FLOW PROMPT -->
            <div class="col-lg-6">
                <div class="card border border-primary-subtle shadow-sm h-100">
                    <div class="card-header bg-light py-2 fw-bold text-primary">
                        <i class="fa-solid fa-user-plus me-2"></i>Mời Sinh Viên Vào Nhóm
                    </div>
                    <div class="card-body">
                        @if(session('invite_success'))
                        <div class="alert alert-success alert-dismissible fade show p-2 px-3 small mb-3 border-0 rounded-3 shadow-sm" role="alert" style="background-color: #d1e7dd; color: #0f5132;">
                            <i class="fa-solid fa-circle-check me-2"></i>{{ session('invite_success') }}
                            <button type="button" class="btn-close p-2" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        <!-- BƯỚC 1: NHẬP MSSV & TRA CỨU -->
                        <div id="stepSearchArea">
                            <label class="form-label small fw-bold">Mã số sinh viên (MSSV):</label>
                            <div class="input-group mb-2">
                                <input type="text" id="inputMSSVSearch" class="form-control" placeholder="Nhập chính xác MSSV (ví dụ: sv02, 22110001)...">
                                <button class="btn btn-primary" type="button" id="btnDoSearch">
                                    <i class="fa-solid fa-magnifying-glass me-1"></i> Tra Cứu
                                </button>
                            </div>
                            <div class="form-text small text-muted">
                                <i class="fa-solid fa-shield-halved me-1"></i>Hệ thống sẽ tra cứu thông tin và kiểm tra điều kiện trước khi gửi lời mời.
                            </div>
                            <div id="searchErrorMessage" class="alert alert-danger p-2 small mt-2 d-none"></div>
                        </div>

                        <!-- BƯỚC 2: STUDENT VERIFICATION CARD (XÁC THỰC THÔNG TIN) -->
                        <div id="stepVerificationCard" class="d-none mt-3">
                            <div class="card border border-2 border-primary bg-light p-3 rounded-3">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <h6 class="fw-bold text-primary mb-0"><i class="fa-solid fa-id-card me-2"></i>Xác Nhận Thông Tin Sinh Viên</h6>
                                    <span id="cardBadgeStatus" class="badge bg-success rounded-pill"></span>
                                </div>
                                <div class="mb-2">
                                    <div class="fs-5 fw-bold text-dark" id="cardHoTen"></div>
                                    <div class="small text-muted">MSSV: <strong id="cardMSSV" class="text-dark"></strong></div>
                                </div>
                                <div class="row g-2 small mb-3">
                                    <div class="col-6">
                                        <span class="text-muted">Lớp:</span> <strong id="cardLop" class="text-dark"></strong>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted">Ngành:</span> <strong id="cardNganh" class="text-dark"></strong>
                                    </div>
                                </div>

                                <div class="p-2 rounded-2 bg-white border mb-3 small" id="cardStatusDetailBox">
                                    <div class="text-muted small mb-1">Trạng thái tham gia nhóm:</div>
                                    <div class="fw-bold" id="cardStatusText"></div>
                                </div>

                                <!-- BƯỚC 3: FORM XÁC NHẬN GỬI LỜI MỜI -->
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="btnBackSearch">
                                        <i class="fa-solid fa-arrow-left me-1"></i>Quay lại
                                    </button>
                                    <form action="{{ route('sinhvien.nhom.moiThanhVien') }}" method="POST" id="formConfirmInvite">
                                        @csrf
                                        <input type="hidden" name="MaNhom" value="{{ $nhomCurrent->MaNhom }}">
                                        <input type="hidden" name="MaSV" id="hiddenMaSV" value="">
                                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm" id="btnSubmitInvite">
                                            <i class="fa-solid fa-paper-plane me-1"></i>Xác Nhận & Gửi Lời Mời
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- YÊU CẦU XIN GIA NHẬP & LỜI MỜI ĐÃ GỬI -->
            <div class="col-lg-6">
                <!-- DANH SÁCH SINH VIÊN XIN VÀO NHÓM -->
                <div class="card border border-warning-subtle shadow-sm mb-3">
                    <div class="card-header bg-light py-2 fw-bold text-warning-emphasis d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-hand-holding-hand me-2"></i>Yêu Cầu Xin Vào Nhóm ({{ $yeuCauXinVao->count() }})</span>
                    </div>
                    <div class="card-body p-2">
                        @forelse($yeuCauXinVao as $yc)
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <div>
                                <div class="fw-bold">{{ $yc->sinhVien->HoTen ?? '' }}</div>
                                <div class="small text-muted">MSSV: <code>{{ $yc->sinhVien->taiKhoan->TenDangNhap ?? $yc->MaSV }}</code> | Lớp: {{ $yc->sinhVien->lop->TenLop ?? '' }}</div>
                            </div>
                            <div class="d-flex gap-1">
                                <form action="{{ route('sinhvien.nhom.duyetYeuCau', ['id' => $nhomCurrent->MaNhom, 'maSV' => $yc->MaSV]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-2 py-1" title="Chấp nhận vào nhóm">
                                        <i class="fa-solid fa-check"></i> Duyệt
                                    </button>
                                </form>
                                <form action="{{ route('sinhvien.nhom.tuChoiYeuCau', ['id' => $nhomCurrent->MaNhom, 'maSV' => $yc->MaSV]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2 py-1" title="Từ chối">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted small py-3">Chưa có sinh viên nào gửi yêu cầu xin gia nhập.</div>
                        @endforelse
                    </div>
                </div>

                <!-- LỜI MỜI NHÓM ĐÃ GỬI ĐANG CHỜ XÁC NHẬN -->
                <div class="card border shadow-sm">
                    <div class="card-header bg-light py-2 fw-bold text-secondary d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-paper-plane me-2"></i>Lời Mời Đã Gửi ({{ $loiMoiDaGui->count() }})</span>
                    </div>
                    <div class="card-body p-2">
                        @forelse($loiMoiDaGui as $lm)
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <div>
                                <div class="fw-semibold">{{ $lm->sinhVien->HoTen ?? '' }}</div>
                                <div class="small text-muted">MSSV: <code>{{ $lm->sinhVien->taiKhoan->TenDangNhap ?? $lm->MaSV }}</code> | Đang chờ bạn học chấp nhận</div>
                            </div>
                            <form action="{{ route('sinhvien.nhom.huyLoiMoiDaGui', ['id' => $nhomCurrent->MaNhom, 'maSV' => $lm->MaSV]) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-2 py-1" title="Thu hồi lời mời">
                                    <i class="fa-solid fa-arrow-rotate-left"></i> Thu Hồi
                                </button>
                            </form>
                        </div>
                        @empty
                        <div class="text-center text-muted small py-2">Không có lời mời nào đang chờ.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endif
    </div>
</div>
@endif

<!-- MODAL XEM CHI TIẾT NHÓM (DÀNH CHO NGƯỜI XIN VÀO HOẶC XEM THÀNH VIÊN) -->
<div class="modal fade" id="modalGroupDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title fw-bold" id="groupDetailName"><i class="fa-solid fa-users me-2"></i>Nhóm Khóa Luận</h5>
                    <div class="small opacity-75" id="groupDetailSub">Chi tiết thành viên trong nhóm</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1"><i class="fa-solid fa-calendar-alt me-1 text-primary"></i>Học kỳ:</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-7 text-wrap text-start" id="groupDetailHocKy">--</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1"><i class="fa-solid fa-book-open me-1 text-info"></i>Môn học / Học phần:</span>
                            <span class="badge bg-info-subtle text-info border border-info-subtle fs-7 text-wrap text-start" id="groupDetailHocPhan">--</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <span class="text-muted small">Số lượng:</span>
                        <strong class="text-dark fs-6" id="groupDetailCount">--</strong>
                    </div>
                    <div id="groupDetailStatusBadge"></div>
                </div>

                <h6 class="fw-bold text-primary-custom mb-3"><i class="fa-solid fa-user-group me-2"></i>DANH SÁCH THÀNH VIÊN</h6>
                <div id="groupDetailMembersList" class="d-flex flex-column gap-2 mb-3">
                    <!-- Sẽ render động qua JS -->
                </div>

                <div class="p-2 rounded-2 bg-light text-center small text-muted" id="groupDetailSlotMessage">
                    <!-- Hiển thị còn bao nhiêu vị trí -->
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
                <div id="groupDetailActionArea">
                    <!-- Nút Xin gia nhập sẽ được JS đưa vào đây -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL XEM THÔNG TIN PROFILE CHI TIẾT SINH VIÊN -->
<div class="modal fade" id="modalStudentProfile" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold" id="modalProfileTitle"><i class="fa-solid fa-user-graduate me-2"></i>Hồ Sơ Sinh Viên</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <div class="avatar-circle bg-primary-subtle text-primary fs-2 fw-bold mx-auto mb-2" style="width: 70px; height: 70px; line-height: 70px; border-radius: 50%;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <h5 class="fw-bold mb-1" id="profileModalHoTen">--</h5>
                    <span class="badge bg-light text-primary border" id="profileModalMSSV">--</span>
                </div>
                <div class="list-group list-group-flush border-top border-bottom my-3">
                    <div class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-chalkboard me-2"></i>Lớp học:</span>
                        <strong id="profileModalLop">--</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-graduation-cap me-2"></i>Chuyên ngành:</span>
                        <strong id="profileModalNganh">--</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-envelope me-2"></i>Email sinh viên:</span>
                        <span id="profileModalEmail" class="text-dark">--</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-phone me-2"></i>Số điện thoại:</span>
                        <span id="profileModalPhone" class="text-dark">--</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-users me-2"></i>Tình trạng nhóm:</span>
                        <span id="profileModalGroupStatus">--</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL CHI TIẾT ĐỀ TÀI ĐÃ GÁN CHO NHÓM -->
<!-- ======================================================== -->
@if(isset($nhomCurrent) && ($nhomCurrent->deTai || $nhomCurrent->dangKyDeTai?->deTai))
@php
    $dtDetail = $nhomCurrent->deTai ?? $nhomCurrent->dangKyDeTai?->deTai;
@endphp
<div class="modal fade" id="modalTopicDetail" tabindex="-1" aria-labelledby="modalTopicDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white py-3 px-4">
                <div>
                    <h5 class="modal-title fw-bold" id="modalTopicDetailLabel">
                        <i class="fa-solid fa-book-bookmark me-2"></i>Chi Tiết Đề Tài Khóa Luận
                    </h5>
                    <div class="small opacity-75">Thông tin đề tài đã được gán chính thức cho nhóm của bạn</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- TÊN ĐỀ TÀI & MÃ ĐỀ TÀI -->
                <div class="mb-4 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="badge bg-light text-dark border">Mã: <strong>{{ $dtDetail->MaDeTai }}</strong></span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa-solid fa-circle-check me-1"></i>{{ $dtDetail->TrangThai }}</span>
                        @if($dtDetail->LinhVuc)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $dtDetail->LinhVuc }}</span>
                        @endif
                    </div>
                    <h4 class="fw-bold text-dark mb-1">{{ $dtDetail->TenDeTai }}</h4>
                    @if($dtDetail->TenDeTaiTiengAnh)
                        <div class="text-secondary fst-italic mb-2">{{ $dtDetail->TenDeTaiTiengAnh }}</div>
                    @endif
                </div>

                <!-- THÔNG TIN CHUNG HỌC PHẦN, HỌC KỲ, GVHD -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-graduation-cap me-2"></i>Thông tin môn học</h6>
                            <div class="small mb-1">
                                <span class="text-muted">Học kỳ:</span> 
                                <strong>{{ $dtDetail->hocKy->TenHocKy ?? $nhomCurrent->hocKy->TenHocKy ?? 'Chưa xác định' }}</strong>
                            </div>
                            <div class="small mb-1">
                                <span class="text-muted">Học phần:</span> 
                                <strong>{{ $dtDetail->hocPhanRef->TenHocPhan ?? $dtDetail->HocPhan ?? $nhomCurrent->hocPhan->TenHocPhan ?? 'Khóa luận' }}</strong>
                            </div>
                            <div class="small">
                                <span class="text-muted">Quy mô nhóm:</span> 
                                <strong>Tối đa {{ $dtDetail->SoLuongSinhVienToiDa ?? 3 }} sinh viên</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-chalkboard-user me-2"></i>Giảng viên hướng dẫn</h6>
                            <div class="small mb-1">
                                <span class="text-muted">Họ tên:</span> 
                                <strong class="text-dark">{{ $dtDetail->giangVien->HoTen ?? 'Chưa gán' }}</strong>
                            </div>
                            <div class="small mb-1">
                                <span class="text-muted">Học vị:</span> 
                                <span>{{ $dtDetail->giangVien->HocVi ?? 'Giảng viên' }}</span>
                            </div>
                            <div class="small mb-1">
                                <span class="text-muted">Bộ môn:</span> 
                                <span>{{ $dtDetail->giangVien->boMon->TenBoMon ?? 'Khoa CNTT' }}</span>
                            </div>
                            @if($dtDetail->giangVien && $dtDetail->giangVien->Email)
                            <div class="small">
                                <span class="text-muted">Email:</span> 
                                <code>{{ $dtDetail->giangVien->Email }}</code>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- MÔ TẢ ĐỀ TÀI -->
                @if($dtDetail->MoTa)
                <div class="mb-3">
                    <label class="fw-bold small text-muted text-uppercase mb-1"><i class="fa-solid fa-align-left me-1 text-primary"></i>Mô tả tóm tắt nội dung:</label>
                    <div class="p-3 bg-light rounded-3 border small text-dark" style="white-space: pre-line;">
                        {{ $dtDetail->MoTa }}
                    </div>
                </div>
                @endif

                <!-- MỤC TIÊU & YÊU CẦU -->
                @if($dtDetail->MucTieu)
                <div class="mb-3">
                    <label class="fw-bold small text-muted text-uppercase mb-1"><i class="fa-solid fa-bullseye me-1 text-success"></i>Mục tiêu & Sản phẩm dự kiến:</label>
                    <div class="p-3 bg-light rounded-3 border small text-dark" style="white-space: pre-line;">
                        {{ $dtDetail->MucTieu }}
                    </div>
                </div>
                @endif

                @if($dtDetail->YeuCau)
                <div class="mb-3">
                    <label class="fw-bold small text-muted text-uppercase mb-1"><i class="fa-solid fa-list-check me-1 text-warning"></i>Yêu cầu thực hiện đối với sinh viên:</label>
                    <div class="p-3 bg-light rounded-3 border small text-dark" style="white-space: pre-line;">
                        {{ $dtDetail->YeuCau }}
                    </div>
                </div>
                @endif

                <!-- KHUNG ĐỀ CƯƠNG CHI TIẾT (NẾU CÓ THÌ KÈM ĐỀ CƯƠNG, KHÔNG THÌ BÁO CHƯA CÓ) -->
                <div class="mb-2">
                    <label class="fw-bold small text-muted text-uppercase mb-2"><i class="fa-solid fa-file-lines me-1 text-info"></i>Đề cương chi tiết:</label>
                    @if($dtDetail->FileDeCuong)
                        @php
                            $fileDcUrl = Str::startsWith($dtDetail->FileDeCuong, ['http', 'storage/']) ? asset($dtDetail->FileDeCuong) : asset('storage/' . $dtDetail->FileDeCuong);
                            $fileName = basename($dtDetail->FileDeCuong);
                        @endphp
                        <div class="p-3 bg-success-subtle border border-success-subtle rounded-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <div class="fw-bold text-success mb-1">
                                    <i class="fa-solid fa-circle-check me-1"></i>File đề cương chi tiết đã được Giảng viên cập nhật
                                </div>
                                <div class="small text-muted">
                                    <i class="fa-solid fa-paperclip me-1 text-primary"></i>Tên tệp: <code>{{ $fileName }}</code>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 shadow-xs fw-semibold"
                                        onclick="quickPreviewOutline('{{ $fileDcUrl }}', '{{ $fileName }}', '{{ addslashes($dtDetail->TenDeTai ?? '') }}')">
                                    <i class="fa-solid fa-eye me-1"></i>Xem Nhanh Đề Cương
                                </button>
                                <a href="{{ $fileDcUrl }}" download="{{ $fileName }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
                                    <i class="fa-solid fa-download me-1"></i>Tải Về
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="p-3 bg-light border rounded-3 text-center text-muted">
                            <i class="fa-solid fa-file-circle-xmark text-secondary fs-4 mb-1 d-block"></i>
                            <div class="fw-semibold small text-dark">Chưa có file đề cương chi tiết</div>
                            <div class="small text-muted">Đề tài đã được Trưởng khoa phê duyệt chính thức cho nhóm (không thể hủy). Nhóm sinh viên vui lòng chờ Giảng viên hướng dẫn nộp Đề cương chi tiết.</div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer px-4 py-3 bg-light border-top">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- ======================================================== -->
<!-- MODAL TẠO NHÓM MỚI THEO QUY TRÌNH RÀNG BUỘC (HỌC KỲ -> BỘ MÔN -> HỌC PHẦN) -->
<!-- ======================================================== -->
<div class="modal fade" id="modalCreateGroup" tabindex="-1" aria-labelledby="modalCreateGroupLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('sinhvien.nhom.store') }}" method="POST" id="formCreateGroup">
                @csrf
                <div class="modal-header border-bottom px-4 py-3 bg-light">
                    <h5 class="modal-title fw-bold text-primary" id="modalCreateGroupLabel">
                        <i class="fa-solid fa-users-gear me-2"></i>Tạo Nhóm Môn Học / Học Phần Mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @if(isset($nhomHocKyHienTai) && $nhomHocKyHienTai)
                        <div class="alert alert-warning border-warning shadow-sm rounded-3 mb-0 p-4 text-center">
                            <i class="fa-solid fa-triangle-exclamation text-warning fs-1 mb-3 d-block"></i>
                            <h5 class="fw-bold text-dark mb-2">Bạn đã có nhóm khóa luận trong học kỳ này!</h5>
                            <p class="text-secondary mb-3">
                                Hiện tại bạn đang là thành viên của nhóm <strong>{{ $nhomHocKyHienTai->TenNhom }}</strong> (Môn: <strong>{{ $nhomHocKyHienTai->hocPhan->TenHocPhan ?? $nhomHocKyHienTai->MaHocPhan }}</strong> - {{ $nhomHocKyHienTai->hocKy->TenHocKy ?? '' }}).
                            </p>
                            <div class="alert alert-light border small text-muted mb-3 text-start">
                                <i class="fa-solid fa-circle-info text-primary me-1"></i>
                                <strong>Quy định:</strong> Mỗi sinh viên chỉ được tham gia tối đa 1 nhóm trong cùng một đợt/học kỳ khóa luận. Bạn không thể tạo thêm nhóm mới.
                            </div>
                            <a href="{{ route('sinhvien.nhom.index', ['hocky' => $nhomHocKyHienTai->MaHocKy, 'hocphan' => $nhomHocKyHienTai->MaHocPhan]) }}" class="btn btn-primary rounded-pill px-4 fw-bold">
                                <i class="fa-solid fa-users me-1"></i>Đến Trang Quản Lý Nhóm Của Bạn
                            </a>
                        </div>
                    @elseif(isset($isTaoNhomOpen) && !$isTaoNhomOpen)
                        <div class="alert alert-danger border-danger shadow-sm rounded-3 mb-0 p-4 text-center">
                            <i class="fa-solid fa-lock text-danger fs-1 mb-3 d-block"></i>
                            <h5 class="fw-bold text-dark mb-2">{{ $groupPhaseState['message'] ?? 'Đã hết thời hạn tạo nhóm khóa luận!' }}</h5>
                            <p class="text-secondary mb-0">Thời hạn tạo nhóm khóa luận đã kết thúc hoặc chưa mở theo Kế hoạch đào tạo.</p>
                        </div>
                    @else
                    <!-- Callout quy trình -->
                    <div class="alert alert-primary bg-primary-subtle border-0 rounded-3 mb-4 p-3 d-flex align-items-start gap-3">
                        <i class="fa-solid fa-circle-info fs-3 text-primary mt-1"></i>
                        <div>
                            <div class="fw-bold text-primary mb-1 fs-6">Quy trình khởi tạo nhóm khóa luận:</div>
                            <div class="small text-dark">
                                Vui lòng thực hiện tuần tự 2 bước:
                                <span class="badge bg-primary rounded-pill me-1">1</span> <strong>Chọn Học kỳ áp dụng</strong> ➔ 
                                <span class="badge bg-primary rounded-pill me-1">2</span> <strong>Chọn Học phần Khóa luận tốt nghiệp</strong>.
                            </div>
                        </div>
                    </div>

                    <!-- BƯỚC 1: CHỌN HỌC KỲ -->
                    <div class="mb-3 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold text-dark d-flex justify-content-between align-items-center mb-2">
                            <span>
                                <span class="badge bg-primary rounded-pill px-2 py-1 me-1">Bước 1</span> 
                                Chọn Học Kỳ Áp Dụng <span class="text-danger">*</span>
                            </span>
                            <span class="text-muted small fw-normal"><i class="fa-solid fa-calendar-days me-1"></i>Kỳ học khóa luận</span>
                        </label>
                        <select name="MaHocKy" id="create_group_hocky" class="form-select form-select-lg border-2 shadow-xs" required>
                            <option value="">-- Chọn Học Kỳ --</option>
                            @if(isset($hocKies))
                                @foreach($hocKies as $hk)
                                    <option value="{{ $hk->MaHocKy }}" {{ (isset($maHocKy) && $maHocKy === $hk->MaHocKy) ? 'selected' : '' }}>
                                        {{ $hk->TenHocKy }} — [{{ $hk->TrangThai }}]
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <div class="form-text text-muted small mt-1">
                            <i class="fa-solid fa-circle-check text-success me-1"></i>Chọn học kỳ mà bạn dự kiến thực hiện khóa luận tốt nghiệp.
                        </div>
                    </div>

                    <!-- BƯỚC 2: CHỌN HỌC PHẦN KHÓA LUẬN -->
                    <div class="mb-3 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold text-dark d-flex justify-content-between align-items-center mb-2">
                            <span>
                                <span class="badge bg-primary rounded-pill px-2 py-1 me-1">Bước 2</span> 
                                Chọn Học Phần Khóa Luận <span class="text-danger">*</span>
                            </span>
                            <span class="text-muted small fw-normal" id="step2_badge_status">
                                <i class="fa-solid fa-lock me-1"></i>Khóa cho đến khi chọn Bước 1
                            </span>
                        </label>
                        <select name="MaHocPhan" id="create_group_hocphan" class="form-select form-select-lg border-2 shadow-xs" required disabled>
                            <option value="">-- Bước 2: Vui lòng chọn học kỳ ở bước 1 trước --</option>
                        </select>
                        <div class="form-text text-muted small mt-1" id="create_group_hocphan_help">
                            <i class="fa-solid fa-graduation-cap me-1"></i>Học phần khóa luận: <strong>Khóa luận tốt nghiệp theo Bộ môn</strong> mở trong kỳ này.
                        </div>
                    </div>

                    <!-- THÔNG TIN NHÓM TỰ SINH -->
                    <div class="card border border-primary-subtle bg-white rounded-3 p-3 shadow-xs">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <span class="text-muted small d-block">Tên nhóm tự sinh theo chuẩn:</span>
                                <div class="fw-bold text-primary fs-5">
                                    <i class="fa-solid fa-users-rectangle me-1"></i>Nhóm {{ $sinhVien->taiKhoan->TenDangNhap ?? $sinhVien->MaSV }}
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="text-muted small d-block">Trưởng nhóm sáng lập:</span>
                                <div class="fw-bold text-dark fs-6">
                                    {{ $sinhVien->HoTen }} <code class="text-muted">({{ $sinhVien->taiKhoan->TenDangNhap ?? $sinhVien->MaSV }})</code>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="modal-footer border-top px-4 py-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        Hủy Bỏ
                    </button>
                    @if((!isset($nhomHocKyHienTai) || !$nhomHocKyHienTai) && (isset($isTaoNhomOpen) && $isTaoNhomOpen))
                        <button type="submit" id="btn_submit_create_group" class="btn btn-success btn-lg rounded-pill px-4 fw-bold shadow-sm" disabled>
                            <i class="fa-solid fa-check-circle me-2"></i>Xác Nhận Tạo Nhóm
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── XỬ LÝ CASCADE FILTER: HỌC KỲ -> BỘ MÔN -> HỌC PHẦN TRÊN TRANG NHÓM ──
const allHocPhansList = @json($hocPhans ?? []);
const hasActiveGroupThisSemester = @json(isset($nhomHocKyHienTai) && $nhomHocKyHienTai ? true : false);
const userGroupHpIds = @json(isset($sinhVienAllGroups) ? $sinhVienAllGroups->map(fn($g) => $g->nhom?->MaHocPhan ?? 'HP_KLCN')->values() : []);
const groupSemestersMap = @json(isset($sinhVienAllGroups) ? $sinhVienAllGroups->filter(fn($g) => $g->nhom && $g->nhom->MaHocKy)->mapWithKeys(fn($g) => [$g->nhom->MaHocPhan ?? 'HP_KLCN' => $g->nhom->MaHocKy]) : []);

function populateFilterHocPhans(selectedBm, currentHpVal) {
    const filterHocPhan = document.getElementById('filter_hoc_phan');
    if (!filterHocPhan) return;
    filterHocPhan.innerHTML = '';

    // Nếu sinh viên đã có nhóm trong học kỳ: chỉ hiển thị duy nhất học phần của nhóm đã tạo
    if (hasActiveGroupThisSemester && allHocPhansList.length > 0) {
        allHocPhansList.forEach(hp => {
            const opt = document.createElement('option');
            opt.value = hp.MaHocPhan;
            opt.textContent = hp.TenHocPhan + ' ★ (Nhóm của bạn)';
            opt.selected = true;
            filterHocPhan.appendChild(opt);
        });
        filterHocPhan.disabled = true;
        return;
    }

    // Khi chưa có nhóm: Thêm lựa chọn Tất cả học phần lên đầu
    const optAll = document.createElement('option');
    optAll.value = 'ALL';
    optAll.textContent = '-- Tất cả học phần (Xem tất cả nhóm) --';
    if (!currentHpVal || currentHpVal === 'ALL') {
        optAll.selected = true;
    }
    filterHocPhan.appendChild(optAll);

    let filtered = allHocPhansList;
    if (selectedBm && selectedBm !== 'ALL') {
        filtered = allHocPhansList.filter(hp => hp.MaBoMon === selectedBm);
    }

    filtered.forEach(hp => {
        const opt = document.createElement('option');
        opt.value = hp.MaHocPhan;
        opt.textContent = hp.TenHocPhan;
        if (hp.MaHocPhan === currentHpVal) {
            opt.selected = true;
        }
        filterHocPhan.appendChild(opt);
    });
}

function onHocKyFilterChange() {
    const filterHocKy = document.getElementById('filter_hoc_ky');
    const hk = filterHocKy ? filterHocKy.value : '';
    const url = new URL("{{ route('sinhvien.nhom.index') }}", window.location.origin);
    if (hk) url.searchParams.set('hoc_ky', hk);
    window.location.href = url.toString();
}

function onHocPhanFilterChange() {
    const filterHocPhan = document.getElementById('filter_hoc_phan');
    const hpVal = filterHocPhan ? filterHocPhan.value : '';
    if (hpVal && groupSemestersMap[hpVal]) {
        const filterHocKy = document.getElementById('filter_hoc_ky');
        if (filterHocKy) {
            filterHocKy.value = groupSemestersMap[hpVal];
        }
    }
    applyCascadeFilter();
}

function onBoMonChange() {
    const filterBoMon = document.getElementById('filter_bo_mon');
    const selectedBm = filterBoMon ? filterBoMon.value : 'ALL';
    populateFilterHocPhans(selectedBm, null);
    applyCascadeFilter();
}

function applyCascadeFilter() {
    const filterHocKy = document.getElementById('filter_hoc_ky');
    const filterBoMon = document.getElementById('filter_bo_mon');
    const filterHocPhan = document.getElementById('filter_hoc_phan') || document.getElementById('filter_hoc_phan_fixed');

    const hk = filterHocKy ? filterHocKy.value : '';
    const bm = filterBoMon ? filterBoMon.value : '';
    const hp = filterHocPhan ? filterHocPhan.value : '';

    const url = new URL("{{ route('sinhvien.nhom.index') }}", window.location.origin);
    if (hk) url.searchParams.set('hoc_ky', hk);
    if (bm) url.searchParams.set('bo_mon', bm);
    if (hp) url.searchParams.set('hoc_phan', hp);
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    // ── KHỞI TẠO OPTIONS BỘ LỌC HỌC PHẦN ──
    populateFilterHocPhans("{{ $selectedBoMon }}", "{{ $selectedHocPhan }}");

    // ── LIVE SEARCH: LỌC TỨC THÌ CÁC THẺ NHÓM TRÊN GIAO DIỆN KHI GÕ TỪ KHÓA ──
    const searchInputs = document.querySelectorAll('.input-search-sync');
    function applyLiveFilter(keyword) {
        const lowerKey = keyword.trim().toLowerCase();
        const cards = document.querySelectorAll('.group-item-card');
        let visibleCount = 0;
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            if (!lowerKey || text.includes(lowerKey)) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        const badgeCount = document.getElementById('badge-group-count');
        if (badgeCount) {
            badgeCount.textContent = `Tìm thấy ${visibleCount} nhóm`;
        }
    }

    searchInputs.forEach(inputEl => {
        inputEl.addEventListener('input', function() {
            const val = this.value;
            // Đồng bộ giá trị sang ô tìm kiếm còn lại
            searchInputs.forEach(other => {
                if (other !== inputEl) other.value = val;
            });
            applyLiveFilter(val);
        });
    });

    // ── XỬ LÝ QUY TRÌNH 2 BƯỚC KHI TẠO NHÓM (HỌC KỲ -> HỌC PHẦN KHÓA LUẬN) ──
    const hocPhansData = @json($hocPhans ?? []);
    const selectHocKy = document.getElementById('create_group_hocky');
    const selectHocPhan = document.getElementById('create_group_hocphan');
    const btnSubmit = document.getElementById('btn_submit_create_group');
    const helpHocPhan = document.getElementById('create_group_hocphan_help');
    const step2Badge = document.getElementById('step2_badge_status');

    function checkCanSubmit() {
        if (selectHocKy && selectHocPhan && btnSubmit) {
            const isValid = selectHocKy.value.trim() !== '' && 
                            selectHocPhan.value.trim() !== '';
            btnSubmit.disabled = !isValid;
        }
    }

    function updateHocPhanByHocKy() {
        const currentHk = selectHocKy.value;
        selectHocPhan.innerHTML = '';

        if (!currentHk) {
            selectHocPhan.disabled = true;
            selectHocPhan.innerHTML = '<option value="">-- Bước 2: Vui lòng chọn học kỳ ở bước 1 trước --</option>';
            if (step2Badge) step2Badge.innerHTML = '<i class="fa-solid fa-lock me-1"></i>Khóa cho đến khi chọn Bước 1';
            if (helpHocPhan) helpHocPhan.innerHTML = '<i class="fa-solid fa-graduation-cap me-1"></i>Học phần khóa luận: <strong>Khóa luận tốt nghiệp theo Bộ môn</strong> mở trong kỳ này.';
            checkCanSubmit();
            return;
        }

        // Lọc các học phần ĐƯỢC MỞ trong học kỳ đã chọn
        const filtered = hocPhansData.filter(hp => {
            if (hp.hoc_phan_hoc_kies && Array.isArray(hp.hoc_phan_hoc_kies)) {
                return hp.hoc_phan_hoc_kies.some(hphk => hphk.MaHocKy === currentHk && hphk.TrangThai === 'Đang mở');
            }
            return false;
        });

        if (filtered.length === 0) {
            selectHocPhan.disabled = true;
            selectHocPhan.innerHTML = '<option value="">-- Học kỳ này chưa mở học phần khóa luận nào --</option>';
            if (step2Badge) step2Badge.innerHTML = '<span class="text-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>Chưa mở môn</span>';
            if (helpHocPhan) helpHocPhan.innerHTML = '<span class="text-danger"><i class="fa-solid fa-circle-exclamation me-1"></i>Học kỳ đã chọn hiện chưa mở đợt đăng ký Khóa luận.</span>';
        } else {
            selectHocPhan.disabled = false;
            selectHocPhan.innerHTML = '<option value="">-- Bước 2: Chọn học phần khóa luận (' + filtered.length + ' học phần) --</option>';
            filtered.forEach(hp => {
                const opt = document.createElement('option');
                opt.value = hp.MaHocPhan;
                opt.textContent = hp.TenHocPhan;
                selectHocPhan.appendChild(opt);
            });
            if (step2Badge) step2Badge.innerHTML = '<span class="text-success"><i class="fa-solid fa-lock-open me-1"></i>Sẵn sàng chọn</span>';
            if (helpHocPhan) helpHocPhan.innerHTML = '<span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Đã tải ' + filtered.length + ' học phần khóa luận mở trong kỳ này. Vui lòng chọn để lập nhóm.</span>';
        }
        checkCanSubmit();
    }

    if (selectHocKy && selectHocPhan) {
        selectHocKy.addEventListener('change', updateHocPhanByHocKy);
        selectHocPhan.addEventListener('change', checkCanSubmit);

        if (selectHocKy.value) {
            updateHocPhanByHocKy();
        }
    }
    // ── XỬ LÝ TRA CỨU & STUDENT VERIFICATION CARD ──
    const inputMSSVSearch = document.getElementById('inputMSSVSearch');
    const btnDoSearch = document.getElementById('btnDoSearch');
    const stepVerificationCard = document.getElementById('stepVerificationCard');
    const btnBackSearch = document.getElementById('btnBackSearch');
    const searchErrorMessage = document.getElementById('searchErrorMessage');
    const btnSubmitInvite = document.getElementById('btnSubmitInvite');
    const hiddenMaSV = document.getElementById('hiddenMaSV');

    if (btnDoSearch && inputMSSVSearch) {
        function executeSearch() {
            const mssv = inputMSSVSearch.value.trim();
            if (!mssv) {
                showError('Vui lòng nhập Mã số sinh viên (MSSV).');
                return;
            }

            hideError();
            btnDoSearch.disabled = true;
            btnDoSearch.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang Tra Cứu...';

            fetch(`{{ route('sinhvien.nhom.traCuuSinhVien') }}?mssv=${encodeURIComponent(mssv)}&ma_nhom={{ $nhomCurrent->MaNhom ?? '' }}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        showError(data.message || 'Không tìm thấy sinh viên.');
                        stepVerificationCard.classList.add('d-none');
                        return;
                    }

                    // Render dữ liệu vào Student Verification Card
                    const sv = data.student;
                    document.getElementById('cardHoTen').textContent = sv.HoTen;
                    document.getElementById('cardMSSV').textContent = sv.MSSV;
                    document.getElementById('cardLop').textContent = sv.TenLop;
                    document.getElementById('cardNganh').textContent = sv.TenNganh;
                    document.getElementById('cardStatusText').textContent = data.status_text;
                    
                    const badge = document.getElementById('cardBadgeStatus');
                    badge.className = `badge rounded-pill ${data.badge_class}`;
                    badge.textContent = data.can_invite ? 'Có Thể Mời' : 'Không Thể Mời';

                    if (hiddenMaSV) {
                        hiddenMaSV.value = sv.MaSV;
                    }

                    if (btnSubmitInvite) {
                        if (data.can_invite) {
                            btnSubmitInvite.classList.remove('d-none');
                            if (data.is_join_request) {
                                btnSubmitInvite.innerHTML = '<i class="fa-solid fa-check me-1"></i>Xác Nhận & Duyệt Vào Nhóm';
                            } else {
                                btnSubmitInvite.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Xác Nhận & Gửi Lời Mời';
                            }
                        } else {
                            btnSubmitInvite.classList.add('d-none');
                        }
                    }

                    stepVerificationCard.classList.remove('d-none');
                })
                .catch(err => {
                    console.error(err);
                    showError('Có lỗi xảy ra trong quá trình tra cứu.');
                })
                .finally(() => {
                    btnDoSearch.disabled = false;
                    btnDoSearch.innerHTML = '<i class="fa-solid fa-magnifying-glass me-1"></i> Tra Cứu';
                });
        }

        function showError(msg) {
            if (searchErrorMessage) {
                searchErrorMessage.textContent = msg;
                searchErrorMessage.classList.remove('d-none');
            }
        }

        function hideError() {
            if (searchErrorMessage) {
                searchErrorMessage.classList.add('d-none');
            }
        }

        btnDoSearch.addEventListener('click', executeSearch);
        inputMSSVSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                executeSearch();
            }
        });

        if (btnBackSearch) {
            btnBackSearch.addEventListener('click', function() {
                stepVerificationCard.classList.add('d-none');
                inputMSSVSearch.focus();
            });
        }
    }

    // ── XỬ LÝ XEM CHI TIẾT NHÓM (MODAL) ──
    const groupDetailModalEl = document.getElementById('modalGroupDetail');
    const groupDetailModal = groupDetailModalEl ? new bootstrap.Modal(groupDetailModalEl) : null;

    document.querySelectorAll('.btn-view-group-detail').forEach(btn => {
        btn.addEventListener('click', function() {
            const maNhom = this.dataset.manhom;
            if (!maNhom || !groupDetailModal) return;

            fetch(`{{ url('sinhvien/nhom') }}/${maNhom}/chi-tiet`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return;

                    const nhom = data.nhom;
                    document.getElementById('groupDetailName').innerHTML = `<i class="fa-solid fa-users me-2"></i>${nhom.TenNhom}`;
                    if (document.getElementById('groupDetailHocKy')) {
                        document.getElementById('groupDetailHocKy').textContent = nhom.TenHocKy || 'Chưa xác định';
                    }
                    if (document.getElementById('groupDetailHocPhan')) {
                        document.getElementById('groupDetailHocPhan').textContent = nhom.TenHocPhan || 'Khóa luận cử nhân';
                    }
                    document.getElementById('groupDetailCount').textContent = `${nhom.SoLuong} / 3 thành viên`;

                    const badgeArea = document.getElementById('groupDetailStatusBadge');
                    if (nhom.DaDu) {
                        badgeArea.innerHTML = `<span class="badge bg-secondary rounded-pill px-3 py-1"><i class="fa-solid fa-lock me-1"></i>Đã Đủ Thành Viên</span>`;
                    } else {
                        badgeArea.innerHTML = `<span class="badge bg-success rounded-pill px-3 py-1"><i class="fa-solid fa-user-plus me-1"></i>Đang Tuyển Thành Viên</span>`;
                    }

                    const membersList = document.getElementById('groupDetailMembersList');
                    membersList.innerHTML = '';

                    data.thanhViens.forEach(tv => {
                        const item = document.createElement('div');
                        item.className = 'p-3 rounded-3 border ' + (tv.IsLeader ? 'bg-warning-subtle border-warning' : 'bg-light');
                        item.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="fw-bold text-dark">
                                    ${tv.IsLeader ? '<i class="fa-solid fa-crown text-warning me-1"></i>' : '<i class="fa-solid fa-user text-secondary me-1"></i>'}
                                    ${tv.HoTen}
                                </div>
                                <span class="badge ${tv.IsLeader ? 'bg-warning text-dark' : 'bg-secondary'} rounded-pill">${tv.VaiTro}</span>
                            </div>
                            <div class="small text-muted">
                                MSSV: <code>${tv.MSSV}</code> | Lớp: ${tv.TenLop} | Ngành: ${tv.TenNganh}
                            </div>
                        `;
                        membersList.appendChild(item);
                    });

                    const slotMsg = document.getElementById('groupDetailSlotMessage');
                    if (nhom.DaDu) {
                        slotMsg.innerHTML = `<i class="fa-solid fa-circle-exclamation text-danger me-1"></i>Nhóm đã đạt số lượng tối đa 3 thành viên.`;
                    } else {
                        slotMsg.innerHTML = `<i class="fa-solid fa-circle-check text-success me-1"></i>Nhóm hiện còn <strong>${nhom.ConSlot} vị trí</strong> tuyển thêm.`;
                    }

                    // Action buttons in modal
                    const actionArea = document.getElementById('groupDetailActionArea');
                    const userStatus = data.userStatus;

                    if (userStatus.hasGroup) {
                        actionArea.innerHTML = `<span class="small text-muted fst-italic">Bạn đã thuộc một nhóm khóa luận</span>`;
                    } else if (userStatus.hasRequested) {
                        actionArea.innerHTML = `
                            <form action="{{ url('sinhvien/nhom') }}/${nhom.MaNhom}/huy-xin-gia-nhap" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold">
                                    <i class="fa-solid fa-clock me-1"></i>Hủy Yêu Cầu Đã Gửi
                                </button>
                            </form>
                        `;
                    } else if (!nhom.DaDu) {
                        actionArea.innerHTML = `
                            <form action="{{ url('sinhvien/nhom') }}/${nhom.MaNhom}/xin-gia-nhap" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                                    <i class="fa-solid fa-hand me-1"></i>Xin Gia Nhập Nhóm
                                </button>
                            </form>
                        `;
                    } else {
                        actionArea.innerHTML = '';
                    }

                    groupDetailModal.show();
                })
                .catch(err => console.error(err));
        });
    });

    // ── XỬ LÝ XEM PROFILE CHI TIẾT SINH VIÊN ──
    const profileModalEl = document.getElementById('modalStudentProfile');
    const profileModal = profileModalEl ? new bootstrap.Modal(profileModalEl) : null;

    document.querySelectorAll('.btn-view-profile').forEach(btn => {
        btn.addEventListener('click', function() {
            const mssv = this.dataset.mssv;
            if (!mssv || !profileModal) return;

            fetch(`{{ route('sinhvien.nhom.traCuuSinhVien') }}?mssv=${encodeURIComponent(mssv)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.student) {
                        const sv = data.student;
                        document.getElementById('profileModalHoTen').textContent = sv.HoTen;
                        document.getElementById('profileModalMSSV').textContent = 'MSSV: ' + sv.MSSV;
                        document.getElementById('profileModalLop').textContent = sv.TenLop;
                        document.getElementById('profileModalNganh').textContent = sv.TenNganh;
                        document.getElementById('profileModalEmail').textContent = sv.Email;
                        document.getElementById('profileModalPhone').textContent = sv.SoDienThoai;
                        document.getElementById('profileModalGroupStatus').innerHTML = `<span class="badge ${data.badge_class}">${data.status_text}</span>`;
                        profileModal.show();
                    }
                })
                .catch(err => console.error(err));
        });
    });
});
</script>
@endpush

@include('partials.modal_preview_decuong')
@endsection