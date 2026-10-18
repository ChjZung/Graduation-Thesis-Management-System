{{-- ═══ TAB 2: LỊCH HẸN HƯỚNG DẪN (GIẢNG VIÊN & SINH VIÊN) ═══ --}}
<div class="appointment-tab-wrapper">

    {{-- ── 1. THANH ĐIỀU HƯỚNG TUẦN & TÁC VỤ ── --}}
    <div class="timetable-toolbar">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <h5 class="timetable-title mb-0">
                <i class="fa-solid fa-calendar-check text-primary me-2"></i>
                @if(($roleCode ?? '') === 'GIANG_VIEN')
                    Quản Lý Khung Giờ Hướng Dẫn Khóa Luận Theo Tuần
                @elseif(($roleCode ?? '') === 'SINH_VIEN')
                    Lịch Hẹn Hướng Dẫn Với Giảng Viên
                @else
                    Quản Lý Lịch Hẹn Hướng Dẫn Toàn Khoa
                @endif
            </h5>

            {{-- Bộ lọc trạng thái khung giờ bằng Javascript --}}
            <div class="role-segmented-filter ms-2" id="filterStatusGroup">
                <label class="segmented-btn">
                    <input type="radio" name="filter_appt_status" value="ALL" checked onchange="filterAppointmentSlots('ALL')">
                    <span class="segmented-label">Tất cả</span>
                </label>
                <label class="segmented-btn">
                    <input type="radio" name="filter_appt_status" value="MoDangKy" onchange="filterAppointmentSlots('MoDangKy')">
                    <span class="segmented-label text-success">Còn chỗ / Mở</span>
                </label>
                <label class="segmented-btn">
                    <input type="radio" name="filter_appt_status" value="DaDuCho" onchange="filterAppointmentSlots('DaDuCho')">
                    <span class="segmented-label text-primary">Đã đủ chỗ</span>
                </label>
                <label class="segmented-btn">
                    <input type="radio" name="filter_appt_status" value="DongDangKy" onchange="filterAppointmentSlots('DongDangKy')">
                    <span class="segmented-label text-secondary">Đã đóng</span>
                </label>
                <label class="segmented-btn">
                    <input type="radio" name="filter_appt_status" value="DaHuy" onchange="filterAppointmentSlots('DaHuy')">
                    <span class="segmented-label text-danger">Đã hủy</span>
                </label>
            </div>
        </div>

        {{-- Điều hướng tuần --}}
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form method="GET" action="{{ url()->current() }}" id="formDateFilterAppt" class="d-flex align-items-center gap-2 m-0">
                <input type="hidden" name="tab" value="lich_hen">
                <div class="input-group input-group-sm" style="width: 145px;">
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ $currentDate ?? now()->format('Y-m-d') }}" onchange="document.getElementById('formDateFilterAppt').submit()">
                </div>
            </form>

            <a href="{{ url()->current() }}?tab=lich_hen&date={{ now()->format('Y-m-d') }}" class="btn-tt-blue" title="Về tuần hiện tại">
                <i class="fa-solid fa-calendar-day me-1"></i> Hiện tại
            </a>
            <a href="{{ url()->current() }}?tab=lich_hen&date={{ $prevWeekDate }}" class="btn-tt-blue" title="Tuần trước">
                <i class="fa-solid fa-chevron-left me-1"></i> Trở về
            </a>
            <a href="{{ url()->current() }}?tab=lich_hen&date={{ $nextWeekDate }}" class="btn-tt-blue" title="Tuần tiếp theo">
                Tiếp <i class="fa-solid fa-chevron-right ms-1"></i>
            </a>

            @if(($roleCode ?? '') === 'GIANG_VIEN')
                <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-xs d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTaoKhungGio">
                    <i class="fa-solid fa-plus-circle"></i> Tạo khung giờ hướng dẫn
                </button>
            @endif
        </div>
    </div>

    {{-- Thông báo Flash nếu có --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-xs small mb-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-xs small mb-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ── 2. GIAO DIỆN DÀNH CHO GIẢNG VIÊN ── --}}
    @if(($roleCode ?? '') === 'GIANG_VIEN')
        <div class="card border-0 shadow-xs mb-3 bg-light rounded-3">
            <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h6 class="fw-bold text-dark mb-1">
                        <i class="fa-solid fa-user-tie text-primary me-2"></i> Thầy/Cô: {{ $currentLecturer->HoTen ?? 'Giảng viên' }}
                        <span class="badge bg-primary-subtle text-primary ms-2">{{ $currentLecturer->boMon->TenBoMon ?? 'Bộ môn' }}</span>
                    </h6>
                    <div class="text-muted small">
                        Đang phụ trách hướng dẫn <strong>{{ $managedGroups->count() }} nhóm khóa luận</strong> trong học kỳ này.
                        Thầy/Cô chủ động tạo các khung giờ rảnh theo tuần để sinh viên đăng ký báo cáo tiến độ.
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-center px-3 py-1 bg-white rounded border">
                        <div class="fw-bold text-primary fs-5">{{ $appointmentSlots->count() }}</div>
                        <div class="text-muted" style="font-size: 11px;">Khung giờ tuần này</div>
                    </div>
                    <div class="text-center px-3 py-1 bg-white rounded border">
                        <div class="fw-bold text-success fs-5">{{ $appointmentSlots->where('trang_thai_thuc_te', 'MoDangKy')->count() }}</div>
                        <div class="text-muted" style="font-size: 11px;">Còn chỗ nhận SV</div>
                    </div>
                    <div class="text-center px-3 py-1 bg-white rounded border">
                        <div class="fw-bold text-dark fs-5">{{ $appointmentSlots->sum('SoNhomDaDangKy') }}</div>
                        <div class="text-muted" style="font-size: 11px;">Lượt nhóm đã đặt</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lưới 7 ngày hiển thị Khung giờ của Giảng viên --}}
        <div class="row g-3">
            @foreach($weekDays as $day)
                @php
                    $daySlots = $appointmentSlotsByDay[$day['iso_date']] ?? collect();
                    $isToday = $day['is_today'];
                @endphp
                <div class="col-lg col-md-4 col-sm-6 col-12">
                    <div class="card h-100 border-0 shadow-xs rounded-3 {{ $isToday ? 'border-primary border-2 shadow-sm' : 'border' }}">
                        <div class="card-header py-2 px-3 {{ $isToday ? 'bg-primary text-white' : 'bg-light text-dark' }} d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold small">{{ $day['day_name'] }}</span>
                                <div style="font-size: 11.5px; opacity: 0.85;">{{ $day['date_str'] }}</div>
                            </div>
                            <span class="badge {{ $isToday ? 'bg-white text-primary' : 'bg-secondary text-white' }} rounded-pill" style="font-size: 10px;">
                                {{ $daySlots->count() }} slot
                            </span>
                        </div>

                        <div class="card-body p-2 d-flex flex-column gap-2" style="min-height: 220px; background-color: {{ $isToday ? '#f0f9ff' : '#ffffff' }};">
                            @forelse($daySlots as $slot)
                                @php
                                    $status = $slot->trang_thai_thuc_te;
                                    $badgeClass = match($status) {
                                        'MoDangKy'   => 'bg-success-subtle text-success border border-success-subtle',
                                        'DaDuCho'    => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        'DongDangKy' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                        'DaHuy'      => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        'DaQuaHan'   => 'bg-light text-muted border',
                                        default      => 'bg-light text-dark',
                                    };
                                    $borderCard = match($status) {
                                        'MoDangKy'   => 'border-success',
                                        'DaDuCho'    => 'border-primary',
                                        'DaHuy'      => 'border-danger opacity-75',
                                        default      => 'border-secondary-subtle',
                                    };
                                @endphp
                                <div class="slot-item-card p-2 rounded-2 bg-white border-start border-4 {{ $borderCard }} shadow-xs" data-status="{{ $status }}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark small">
                                            <i class="fa-regular fa-clock me-1 text-primary"></i>
                                            {{ $slot->ThoiGianBatDau->format('H:i') }} - {{ $slot->ThoiGianKetThuc->format('H:i') }}
                                        </span>
                                        <span class="badge {{ $badgeClass }} rounded-pill px-2 py-0" style="font-size: 10px;">
                                            {{ $slot->ten_trang_thai }}
                                        </span>
                                    </div>

                                    <div class="text-muted small mb-1" style="font-size: 11.5px;">
                                        @if($slot->HinhThuc === 'TrucTiep')
                                            <i class="fa-solid fa-location-dot text-danger me-1"></i>
                                            <span>{{ $slot->DiaDiem ?: 'Văn phòng bộ môn' }}</span>
                                        @else
                                            <i class="fa-solid fa-video text-primary me-1"></i>
                                            <a href="{{ $slot->LinkCuocHop }}" target="_blank" class="text-truncate d-inline-block align-middle text-decoration-none" style="max-width: 110px;">
                                                Họp trực tuyến
                                            </a>
                                        @endif
                                    </div>

                                    <div class="text-dark small mb-1 fw-semibold text-truncate" style="font-size: 12px;" title="{{ $slot->NoiDung }}">
                                        {{ $slot->NoiDung }}
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center pt-1 border-top" style="font-size: 11px;">
                                        <span class="text-muted">
                                            <i class="fa-solid fa-users me-1"></i>{{ $slot->SoNhomDaDangKy }}/{{ $slot->SoNhomToiDa }} nhóm
                                        </span>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0 py-0 px-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis-vertical text-secondary"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm small py-1">
                                                @if($status !== 'DaHuy' && $slot->ThoiGianBatDau > now())
                                                    <li>
                                                        <form method="POST" action="{{ route('giangvien.lichhen.chuyenTrangThai', $slot->MaLichGap) }}">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item py-1">
                                                                <i class="fa-solid fa-toggle-{{ $status === 'DongDangKy' ? 'off text-secondary' : 'on text-success' }} me-2"></i>
                                                                {{ $status === 'DongDangKy' ? 'Mở lại đăng ký' : 'Tạm đóng đăng ký' }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif

                                                @if($slot->SoNhomDaDangKy == 0 && $status !== 'DaHuy' && $slot->ThoiGianBatDau > now())
                                                    <li>
                                                        <button type="button" class="dropdown-item py-1" onclick="openModalSuaKhungGio('{{ $slot->MaLichGap }}', '{{ $slot->ThoiGianBatDau->format('Y-m-d') }}', '{{ $slot->ThoiGianBatDau->format('H:i') }}', '{{ $slot->ThoiGianKetThuc->format('H:i') }}', '{{ $slot->HinhThuc }}', '{{ addslashes($slot->DiaDiem ?? '') }}', '{{ addslashes($slot->LinkCuocHop ?? '') }}', '{{ addslashes($slot->NoiDung) }}', '{{ $slot->SoNhomToiDa }}')">
                                                            <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Sửa thông tin
                                                        </button>
                                                    </li>
                                                @endif

                                                @if($status !== 'DaHuy' && $slot->ThoiGianBatDau > now())
                                                    <li><hr class="dropdown-divider my-1"></li>
                                                    <li>
                                                        <button type="button" class="dropdown-item py-1 text-danger" onclick="openModalHuyKhungGio('{{ $slot->MaLichGap }}', '{{ $slot->ThoiGianBatDau->format('H:i d/m/Y') }}', {{ $slot->SoNhomDaDangKy }})">
                                                            <i class="fa-solid fa-ban me-2"></i> Hủy khung giờ
                                                        </button>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>

                                    {{-- Nếu có nhóm đã đặt lịch -> Hiển thị danh sách nhóm đã đặt --}}
                                    @if($slot->dangKyHieuLucs->isNotEmpty())
                                        <div class="mt-2 pt-2 border-top bg-light p-1 rounded">
                                            <div class="text-primary fw-bold" style="font-size: 10.5px;">
                                                <i class="fa-solid fa-user-check me-1"></i> Nhóm đã đặt ({{ $slot->dangKyHieuLucs->count() }}):
                                            </div>
                                            @foreach($slot->dangKyHieuLucs as $bk)
                                                <div class="d-flex justify-content-between align-items-center mt-1 p-1 bg-white rounded border" style="font-size: 11px;">
                                                    <div>
                                                        <strong class="text-dark">{{ $bk->nhom->TenNhom ?? $bk->MaNhom }}</strong>
                                                        <div class="text-muted" style="font-size: 10px;">{{ $bk->sinhVien->HoTen ?? '' }}</div>
                                                        @if($bk->GhiChu)
                                                            <div class="text-secondary fst-italic" style="font-size: 10px;" title="{{ $bk->GhiChu }}">{{ \Illuminate\Support\Str::limit($bk->GhiChu, 25) }}</div>
                                                        @endif
                                                    </div>
                                                    @if($slot->ThoiGianBatDau > now())
                                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" title="Hủy lượt đặt của nhóm này" onclick="openModalHuyLuotDat('{{ $slot->MaLichGap }}', '{{ $bk->id }}', '{{ $bk->nhom->TenNhom ?? $bk->MaNhom }}')">
                                                            <i class="fa-solid fa-xmark"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($status === 'DaHuy' && $slot->LyDoHuy)
                                        <div class="mt-1 text-danger small fst-italic" style="font-size: 10px;">
                                            Lý do hủy: {{ $slot->LyDoHuy }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted small my-auto">
                                    <i class="fa-regular fa-calendar-xmark fa-2x mb-2 d-block opacity-25"></i>
                                    Chưa có khung giờ
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── 3. GIAO DIỆN DÀNH CHO SINH VIÊN ── --}}
    @if(($roleCode ?? '') === 'SINH_VIEN')
        @if(!$currentGVHD)
            {{-- Sinh viên chưa có GVHD chính thức --}}
            <div class="card border-0 shadow-xs rounded-3 bg-light text-center py-5">
                <div class="card-body">
                    <i class="fa-solid fa-user-clock text-warning fa-3x mb-3"></i>
                    <h5 class="fw-bold text-dark">Chưa Có Giảng Viên Hướng Dẫn Chính Thức</h5>
                    <p class="text-muted max-w-600 mx-auto small mb-0">
                        Cổng đặt lịch hẹn hướng dẫn chỉ mở sau khi đề tài của nhóm đã được Khoa phê duyệt và phân công Giảng viên hướng dẫn chính thức.
                        Vui lòng theo dõi thông báo hoặc liên hệ Giáo vụ Khoa để biết thêm chi tiết.
                    </p>
                </div>
            </div>
        @else
            {{-- Banner thông tin GVHD & Nhóm của Sinh viên --}}
            <div class="card border-0 shadow-xs mb-3 bg-light rounded-3">
                <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary px-2 py-1 rounded-pill">GVHD Phụ Trách</span>
                            <h6 class="fw-bold text-dark mb-0 fs-6">
                                {{ $currentGVHD->HoTen }} 
                                <span class="fw-normal text-muted small">({{ $currentGVHD->boMon->TenBoMon ?? 'Khoa CNTT' }})</span>
                            </h6>
                        </div>
                        <div class="text-muted small">
                            <i class="fa-solid fa-folder-open text-warning me-1"></i> Đề tài nhóm: <strong>{{ $currentGroup->deTai?->TenDeTai ?? 'Khóa luận tốt nghiệp' }}</strong>
                            (Nhóm: <strong>{{ $currentGroup->TenNhom }}</strong>)
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="text-center px-3 py-1 bg-white rounded border">
                            <div class="fw-bold text-primary fs-5">{{ $appointmentSlots->count() }}</div>
                            <div class="text-muted" style="font-size: 11px;">Khung giờ Thầy/Cô mở tuần này</div>
                        </div>
                        <div class="text-center px-3 py-1 bg-white rounded border">
                            <div class="fw-bold text-success fs-5">{{ $appointmentSlots->where('is_kha_dung', true)->count() }}</div>
                            <div class="text-muted" style="font-size: 11px;">Còn chỗ đặt được</div>
                        </div>
                        <div class="text-center px-3 py-1 bg-white rounded border">
                            <div class="fw-bold text-dark fs-5">{{ $myBookings->where('TrangThai', 'DaXacNhan')->count() }}</div>
                            <div class="text-muted" style="font-size: 11px;">Lịch hẹn nhóm đã đặt</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Thông báo quy tắc đặt lịch --}}
            <div class="alert alert-info border-info-subtle bg-info-subtle py-2 px-3 rounded-3 small text-dark d-flex align-items-center gap-2 mb-3">
                <i class="fa-solid fa-circle-info text-info fs-5"></i>
                <div>
                    <strong>Quy định đặt lịch hướng dẫn:</strong> Sinh viên chỉ được chọn các khung giờ còn trống do Thầy/Cô đã định sẵn trong tuần và nhấn nút <strong>Đặt lịch</strong> để xác nhận. Hệ thống không cho phép tự ý nhập ngày/giờ tự do.
                </div>
            </div>

            {{-- Lưới 7 ngày hiển thị khung giờ của GVHD cho Sinh viên chọn --}}
            <h6 class="fw-bold text-dark mb-2">
                <i class="fa-regular fa-calendar-days text-primary me-2"></i> Các Khung Giờ Của Thầy/Cô Trong Tuần ({{ $appointmentSlots->count() }})
            </h6>

            <div class="row g-3 mb-4">
                @foreach($weekDays as $day)
                    @php
                        $daySlots = $appointmentSlotsByDay[$day['iso_date']] ?? collect();
                        $isToday = $day['is_today'];
                    @endphp
                    <div class="col-lg col-md-4 col-sm-6 col-12">
                        <div class="card h-100 border-0 shadow-xs rounded-3 {{ $isToday ? 'border-primary border-2 shadow-sm' : 'border' }}">
                            <div class="card-header py-2 px-3 {{ $isToday ? 'bg-primary text-white' : 'bg-light text-dark' }} d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold small">{{ $day['day_name'] }}</span>
                                    <div style="font-size: 11.5px; opacity: 0.85;">{{ $day['date_str'] }}</div>
                                </div>
                                <span class="badge {{ $isToday ? 'bg-white text-primary' : 'bg-secondary text-white' }} rounded-pill" style="font-size: 10px;">
                                    {{ $daySlots->count() }} slot
                                </span>
                            </div>

                            <div class="card-body p-2 d-flex flex-column gap-2" style="min-height: 220px; background-color: {{ $isToday ? '#f0f9ff' : '#ffffff' }};">
                                @forelse($daySlots as $slot)
                                    @php
                                        $status = $slot->trang_thai_thuc_te;
                                        $isKhaDung = $slot->is_kha_dung;
                                        $daDatNhomNay = $slot->dangKys->where('MaNhom', $currentGroup->MaNhom)->where('TrangThai', 'DaXacNhan')->first();
                                    @endphp
                                    <div class="slot-item-card p-2 rounded-2 bg-white border shadow-xs {{ $daDatNhomNay ? 'border-success border-2 bg-success-subtle' : '' }}" data-status="{{ $status }}">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold text-dark small">
                                                <i class="fa-regular fa-clock me-1 text-primary"></i>
                                                {{ $slot->ThoiGianBatDau->format('H:i') }} - {{ $slot->ThoiGianKetThuc->format('H:i') }}
                                            </span>
                                            @if($daDatNhomNay)
                                                <span class="badge bg-success rounded-pill px-2 py-0" style="font-size: 10px;">
                                                    <i class="fa-solid fa-check me-1"></i> Nhóm đã đặt
                                                </span>
                                            @else
                                                <span class="badge {{ $isKhaDung ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary' }} rounded-pill px-2 py-0" style="font-size: 10px;">
                                                    {{ $slot->ten_trang_thai }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="text-muted small mb-1" style="font-size: 11.5px;">
                                            @if($slot->HinhThuc === 'TrucTiep')
                                                <i class="fa-solid fa-location-dot text-danger me-1"></i>
                                                <span>{{ $slot->DiaDiem ?: 'Văn phòng bộ môn' }}</span>
                                            @else
                                                <i class="fa-solid fa-video text-primary me-1"></i>
                                                @if($daDatNhomNay && $slot->LinkCuocHop)
                                                    <a href="{{ $slot->LinkCuocHop }}" target="_blank" class="fw-bold text-primary text-decoration-underline">
                                                        Vào phòng họp trực tuyến
                                                    </a>
                                                @else
                                                    <span>Họp trực tuyến</span>
                                                @endif
                                            @endif
                                        </div>

                                        <div class="text-dark small mb-2 fw-semibold text-truncate" style="font-size: 12px;" title="{{ $slot->NoiDung }}">
                                            {{ $slot->NoiDung }}
                                        </div>

                                        {{-- Nút Thao tác dành cho Sinh viên --}}
                                        @if($daDatNhomNay)
                                            <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                                <span class="text-success small fw-bold" style="font-size: 11px;">
                                                    <i class="fa-solid fa-circle-check"></i> Đã giữ chỗ
                                                </span>
                                                @if($slot->ThoiGianBatDau > now())
                                                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 rounded-pill" style="font-size: 11px;" onclick="openModalHuyLichSV('{{ $daDatNhomNay->id }}', '{{ $slot->ThoiGianBatDau->format('H:i d/m/Y') }}')">
                                                        Hủy lịch
                                                    </button>
                                                @endif
                                            </div>
                                        @elseif($isKhaDung)
                                            <div class="pt-2 border-top text-end">
                                                <button type="button" class="btn btn-primary btn-sm rounded-pill w-100 py-1 fw-bold shadow-xs" style="font-size: 11.5px;" onclick="openModalXacNhanDatLich('{{ $slot->MaLichGap }}', '{{ $currentGVHD->HoTen }}', '{{ $slot->ThoiGianBatDau->format('d/m/Y') }}', '{{ $slot->ThoiGianBatDau->format('H:i') }} - {{ $slot->ThoiGianKetThuc->format('H:i') }}', '{{ $slot->HinhThuc === 'TrucTiep' ? 'Trực tiếp tại ' . ($slot->DiaDiem ?? 'Bộ môn') : 'Trực tuyến' }}', '{{ addslashes($slot->NoiDung) }}', '{{ $slot->so_cho_con_lai }}')">
                                                    <i class="fa-solid fa-calendar-plus me-1"></i> Đặt lịch hẹn này
                                                </button>
                                            </div>
                                        @else
                                            <div class="pt-2 border-top text-center text-muted small" style="font-size: 11px;">
                                                <i class="fa-solid fa-lock me-1"></i> Không khả dụng
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted small my-auto">
                                        <i class="fa-regular fa-calendar-xmark fa-2x mb-2 d-block opacity-25"></i>
                                        Không có khung giờ
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Danh sách các buổi hẹn đã đặt của nhóm --}}
            <div class="card border-0 shadow-xs rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-list-check text-success me-2"></i> Lịch Sử Lịch Hẹn Hướng Dẫn Của Nhóm ({{ $myBookings->count() }})
                    </h6>
                    <span class="text-muted small">Cập nhật tự động</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Thời Gian Gặp</th>
                                <th>Giảng Viên Hướng Dẫn</th>
                                <th>Hình Thức / Địa Điểm</th>
                                <th>Nội Dung Buổi Gặp &amp; Ghi Chú Của Nhóm</th>
                                <th>Trạng Thái</th>
                                <th class="text-end">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myBookings as $bk)
                                @php
                                    $slot = $bk->lichGap;
                                    $statusBadge = match($bk->TrangThai) {
                                        'DaXacNhan'   => 'bg-success-subtle text-success border border-success-subtle',
                                        'DaHuy'       => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        'DaHoanThanh' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        default       => 'bg-light text-dark',
                                    };
                                    $statusText = match($bk->TrangThai) {
                                        'DaXacNhan'   => 'Đã xác nhận',
                                        'DaHuy'       => 'Đã hủy',
                                        'DaHoanThanh' => 'Đã hoàn thành',
                                        default       => $bk->TrangThai,
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $slot?->ThoiGianBatDau?->format('H:i') }} - {{ $slot?->ThoiGianKetThuc?->format('H:i') }}</div>
                                        <div class="text-muted">{{ $slot?->ThoiGianBatDau?->format('d/m/Y') }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $slot?->giangVien?->HoTen ?? 'GVHD' }}</div>
                                        <div class="text-muted" style="font-size: 11px;">{{ $slot?->giangVien?->Email ?? '' }}</div>
                                    </td>
                                    <td>
                                        @if($slot?->HinhThuc === 'TrucTiep')
                                            <span class="badge bg-light text-dark border">Trực tiếp</span>
                                            <div class="text-muted mt-1">{{ $slot->DiaDiem ?? 'Văn phòng bộ môn' }}</div>
                                        @else
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">Trực tuyến</span>
                                            @if($slot?->LinkCuocHop && $bk->TrangThai === 'DaXacNhan')
                                                <div class="mt-1">
                                                    <a href="{{ $slot->LinkCuocHop }}" target="_blank" class="text-primary text-decoration-underline fw-semibold">
                                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Link phòng họp
                                                    </a>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $slot?->NoiDung }}</div>
                                        @if($bk->GhiChu)
                                            <div class="text-muted mt-1 fst-italic">
                                                <strong>Ghi chú của nhóm:</strong> {{ $bk->GhiChu }}
                                            </div>
                                        @endif
                                        @if($bk->TrangThai === 'DaHuy' && $bk->LyDoHuy)
                                            <div class="text-danger mt-1">
                                                <strong>Lý do hủy (bởi {{ $bk->NguoiHuy === 'GiangVien' ? 'GVHD' : 'Sinh viên' }}):</strong> {{ $bk->LyDoHuy }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusBadge }} rounded-pill px-2 py-1">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($bk->TrangThai === 'DaXacNhan' && $slot?->ThoiGianBatDau > now())
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1 fw-semibold" onclick="openModalHuyLichSV('{{ $bk->id }}', '{{ $slot->ThoiGianBatDau->format('H:i d/m/Y') }}')">
                                                <i class="fa-solid fa-xmark me-1"></i> Hủy lịch
                                            </button>
                                        @else
                                            <span class="text-muted fst-italic">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fa-regular fa-calendar-xmark fa-2x mb-2 d-block opacity-25"></i>
                                        Nhóm chưa đăng ký lịch hẹn hướng dẫn nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ── 4. CÁC MODAL HỖ TRỢ NGHIỆP VỤ (GIẢNG VIÊN & SINH VIÊN) ── --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}

{{-- Modal A: Giảng viên Tạo Khung Giờ Hướng Dẫn Mới --}}
@if(($roleCode ?? '') === 'GIANG_VIEN')
<div class="modal fade" id="modalTaoKhungGio" tabindex="-1" aria-labelledby="modalTaoKhungGioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('giangvien.lichhen.tao') }}">
                @csrf
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title fw-bold text-dark fs-6" id="modalTaoKhungGioLabel">
                        <i class="fa-solid fa-calendar-plus text-success me-2"></i> Tạo Khung Giờ Hướng Dẫn Mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-info-subtle bg-info-subtle py-2 px-3 rounded-3 small text-dark mb-3">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Thầy/Cô tạo các khung giờ rảnh trong tuần để sinh viên chủ động chọn và đặt lịch hẹn. Hệ thống sẽ tự động kiểm soát chống trùng lịch và số nhóm tối đa.
                    </div>

                    <div class="row g-3">
                        {{-- Ngày gặp --}}
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">Ngày gặp <span class="text-danger">*</span></label>
                            <input type="date" name="NgayGap" class="form-control" min="{{ now()->format('Y-m-d') }}" value="{{ $currentDate ?? now()->format('Y-m-d') }}" required>
                        </div>

                        {{-- Giờ bắt đầu & Giờ kết thúc --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Giờ bắt đầu <span class="text-danger">*</span></label>
                            <input type="time" name="GioBatDau" class="form-control" value="08:00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Giờ kết thúc <span class="text-danger">*</span></label>
                            <input type="time" name="GioKetThuc" class="form-control" value="09:30" required>
                        </div>

                        {{-- Hình thức gặp --}}
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">Hình thức gặp <span class="text-danger">*</span></label>
                            <select name="HinhThuc" id="selectHinhThuc" class="form-select" onchange="toggleHinhThucFields(this.value)">
                                <option value="TrucTiep" selected>Trực tiếp (Tại trường / Văn phòng bộ môn / Lab)</option>
                                <option value="TrucTuyen">Trực tuyến (Google Meet / MS Teams / Zoom)</option>
                            </select>
                        </div>

                        {{-- Địa điểm gặp trực tiếp --}}
                        <div class="col-12" id="divDiaDiem">
                            <label class="form-label fw-bold small text-dark">Địa điểm gặp trực tiếp <span class="text-danger">*</span></label>
                            <input type="text" name="DiaDiem" class="form-control" placeholder="Ví dụ: Văn phòng Bộ môn CNPM (B.304)" value="Văn phòng Bộ môn CNPM (B.304)">
                        </div>

                        {{-- Link họp trực tuyến --}}
                        <div class="col-12 d-none" id="divLinkCuocHop">
                            <label class="form-label fw-bold small text-dark">Đường dẫn phòng họp trực tuyến <span class="text-danger">*</span></label>
                            <input type="url" name="LinkCuocHop" class="form-control" placeholder="https://meet.google.com/... hoặc MS Teams link">
                        </div>

                        {{-- Số nhóm tối đa --}}
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">Số nhóm tối đa được đăng ký <span class="text-danger">*</span></label>
                            <input type="number" name="SoNhomToiDa" class="form-control" min="1" max="10" value="1" required>
                            <div class="form-text small text-muted">Mặc định: 1 nhóm trong 1 khung giờ. Nếu muốn tiếp đồng thời nhiều nhóm, Thầy/Cô có thể tăng số lượng này.</div>
                        </div>

                        {{-- Nội dung / Mục đích --}}
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">Mục đích / Nội dung hướng dẫn <span class="text-danger">*</span></label>
                            <textarea name="NoiDung" class="form-control" rows="2" placeholder="Ví dụ: Trao đổi tiến độ báo cáo lần 1, giải đáp thắc mắc kiến trúc hệ thống..." required>Hướng dẫn khóa luận tốt nghiệp và trao đổi tiến độ</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Lưu &amp; Mở Đăng Ký
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal B: Giảng viên Sửa Khung Giờ (Chỉ khi chưa có ai đăng ký) --}}
<div class="modal fade" id="modalSuaKhungGio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="formSuaKhungGio" action="">
                @csrf
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title fw-bold text-dark fs-6">
                        <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Chỉnh Sửa Khung Giờ Hướng Dẫn
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">Ngày gặp <span class="text-danger">*</span></label>
                            <input type="date" name="NgayGap" id="sua_NgayGap" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Giờ bắt đầu <span class="text-danger">*</span></label>
                            <input type="time" name="GioBatDau" id="sua_GioBatDau" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Giờ kết thúc <span class="text-danger">*</span></label>
                            <input type="time" name="GioKetThuc" id="sua_GioKetThuc" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">Hình thức gặp <span class="text-danger">*</span></label>
                            <select name="HinhThuc" id="sua_HinhThuc" class="form-select" onchange="toggleHinhThucSuaFields(this.value)">
                                <option value="TrucTiep">Trực tiếp</option>
                                <option value="TrucTuyen">Trực tuyến</option>
                            </select>
                        </div>
                        <div class="col-12" id="sua_divDiaDiem">
                            <label class="form-label fw-bold small text-dark">Địa điểm gặp trực tiếp</label>
                            <input type="text" name="DiaDiem" id="sua_DiaDiem" class="form-control">
                        </div>
                        <div class="col-12 d-none" id="sua_divLinkCuocHop">
                            <label class="form-label fw-bold small text-dark">Đường dẫn họp trực tuyến</label>
                            <input type="url" name="LinkCuocHop" id="sua_LinkCuocHop" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">Số nhóm tối đa</label>
                            <input type="number" name="SoNhomToiDa" id="sua_SoNhomToiDa" class="form-control" min="1" max="10" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">Mục đích / Nội dung hướng dẫn</label>
                            <textarea name="NoiDung" id="sua_NoiDung" class="form-control" rows="2" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-save me-1"></i> Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal C: Giảng viên Hủy Khung Giờ (Kèm lý do) --}}
<div class="modal fade" id="modalHuyKhungGio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="formHuyKhungGio" action="">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold fs-6">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> Xác Nhận Hủy Khung Giờ Hướng Dẫn
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-dark small mb-2">
                        Thầy/Cô đang thực hiện hủy khung giờ vào lúc: <strong id="huy_thoiGianStr" class="text-danger"></strong>.
                    </p>
                    <div class="alert alert-warning border-0 small py-2 px-3 rounded-3 mb-3" id="alertHuyCanhBaoCoNhom">
                        <i class="fa-solid fa-bell me-1"></i>
                        Khung giờ này hiện đã có <strong id="huy_soNhomDaDat"></strong> nhóm đăng ký. Việc hủy khung giờ sẽ tự động hủy các lượt đặt lịch liên quan và gửi thông báo lý do đến sinh viên.
                    </div>
                    <div>
                        <label class="form-label fw-bold small text-dark">Lý do hủy khung giờ <span class="text-danger">*</span></label>
                        <textarea name="LyDoHuy" class="form-control" rows="3" placeholder="Nhập lý do hủy (ví dụ: Bận công tác đột xuất, dời sang tuần sau...)" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Không hủy</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-ban me-1"></i> Xác Nhận Hủy Khung Giờ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal D: Giảng viên Hủy Lượt Đặt Của Một Nhóm --}}
<div class="modal fade" id="modalHuyLuotDat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="formHuyLuotDat" action="">
                @csrf
                <div class="modal-header bg-warning-subtle text-dark border-bottom">
                    <h5 class="modal-title fw-bold fs-6">
                        <i class="fa-solid fa-user-xmark text-danger me-2"></i> Hủy Lượt Đặt Lịch Của Nhóm
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-dark small mb-2">
                        Xác nhận hủy lượt đặt lịch của nhóm: <strong id="huy_tenNhom" class="text-primary"></strong>.
                        Sau khi hủy, chỗ trống sẽ được hoàn lại để nhóm khác có thể đăng ký.
                    </p>
                    <div>
                        <label class="form-label fw-bold small text-dark">Lý do hủy lịch <span class="text-danger">*</span></label>
                        <textarea name="LyDoHuy" class="form-control" rows="3" placeholder="Nhập lý do hủy để thông báo cho nhóm sinh viên..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Xác Nhận Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Modal E: Sinh viên Xác Nhận Đặt Lịch Theo Khung Giờ Có Sẵn --}}
@if(($roleCode ?? '') === 'SINH_VIEN')
<div class="modal fade" id="modalXacNhanDatLich" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="formDatLich" action="">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold fs-6">
                        <i class="fa-solid fa-calendar-check me-2"></i> Xác Nhận Đặt Lịch Hẹn Hướng Dẫn
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border small text-dark mb-3 p-3 rounded-3">
                        <div class="d-flex justify-content-between mb-2 pb-1 border-bottom">
                            <span class="text-muted">Giảng viên hướng dẫn:</span>
                            <strong class="text-dark" id="modal_dl_gvhd"></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-1 border-bottom">
                            <span class="text-muted">Ngày gặp:</span>
                            <strong class="text-primary" id="modal_dl_ngay"></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-1 border-bottom">
                            <span class="text-muted">Thời gian:</span>
                            <strong class="text-dark" id="modal_dl_gio"></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-1 border-bottom">
                            <span class="text-muted">Hình thức / Địa điểm:</span>
                            <strong class="text-dark" id="modal_dl_hinhthuc"></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Mục đích:</span>
                            <span class="text-dark fw-semibold" id="modal_dl_noidung"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">
                            Nội dung nhóm muốn trao đổi trước với Thầy/Cô (Tùy chọn)
                        </label>
                        <textarea name="GhiChu" class="form-control" rows="3" placeholder="Nhóm cần giải đáp những vướng mắc gì, nội dung chuẩn bị báo cáo..."></textarea>
                        <div class="form-text small text-muted">
                            Thông tin này sẽ được gửi kèm thông báo đến Thầy/Cô hướng dẫn ngay sau khi xác nhận.
                        </div>
                    </div>

                    <div class="text-muted small fst-italic">
                        <i class="fa-solid fa-lock text-secondary me-1"></i>
                        Lịch hẹn được xác nhận trực tiếp theo khung giờ Thầy/Cô đã tạo, không cần phê duyệt lại.
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fa-solid fa-check me-1"></i> Xác Nhận Đặt Lịch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal F: Sinh viên Hủy Lịch Đã Đặt --}}
<div class="modal fade" id="modalHuyLichSV" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="formHuyLichSV" action="">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold fs-6">
                        <i class="fa-solid fa-calendar-xmark me-2"></i> Xác Nhận Hủy Lịch Hẹn Của Nhóm
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-dark small mb-2">
                        Nhóm xác nhận muốn hủy lịch hẹn vào lúc: <strong id="sv_huy_thoiGian" class="text-danger"></strong>?
                    </p>
                    <div class="alert alert-info border-0 small py-2 px-3 rounded-3 mb-3">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Sau khi hủy, chỗ trống trong khung giờ sẽ được giải phóng để nhóm khác có thể đăng ký, đồng thời hệ thống sẽ gửi thông báo lý do hủy đến Thầy/Cô hướng dẫn.
                    </div>
                    <div>
                        <label class="form-label fw-bold small text-dark">Lý do nhóm xin hủy lịch <span class="text-danger">*</span></label>
                        <textarea name="LyDoHuy" class="form-control" rows="3" placeholder="Nhập lý do nhóm không thể tham gia buổi hẹn hướng dẫn này..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Giữ lại lịch</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Xác Nhận Hủy Lịch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ── 5. JAVASCRIPT HỖ TRỢ TAB LỊCH HẸN ── --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script>
    // 1. Chuyển đổi hiển thị trường Trực tiếp vs Trực tuyến trong Modal Tạo
    function toggleHinhThucFields(val) {
        const divDiaDiem = document.getElementById('divDiaDiem');
        const divLinkCuocHop = document.getElementById('divLinkCuocHop');
        if (val === 'TrucTiep') {
            divDiaDiem.classList.remove('d-none');
            divLinkCuocHop.classList.add('d-none');
        } else {
            divDiaDiem.classList.add('d-none');
            divLinkCuocHop.classList.remove('d-none');
        }
    }

    // 2. Chuyển đổi hiển thị trong Modal Sửa
    function toggleHinhThucSuaFields(val) {
        const divDiaDiem = document.getElementById('sua_divDiaDiem');
        const divLinkCuocHop = document.getElementById('sua_divLinkCuocHop');
        if (val === 'TrucTiep') {
            divDiaDiem.classList.remove('d-none');
            divLinkCuocHop.classList.add('d-none');
        } else {
            divDiaDiem.classList.add('d-none');
            divLinkCuocHop.classList.remove('d-none');
        }
    }

    // 3. Mở Modal Sửa Khung Giờ
    function openModalSuaKhungGio(maLich, ngay, gioBD, gioKT, hinhThuc, diaDiem, linkHoc, noiDung, soNhom) {
        const form = document.getElementById('formSuaKhungGio');
        form.action = '/giangvien/lich-hen/' + maLich + '/cap-nhat';

        document.getElementById('sua_NgayGap').value = ngay;
        document.getElementById('sua_GioBatDau').value = gioBD;
        document.getElementById('sua_GioKetThuc').value = gioKT;
        document.getElementById('sua_HinhThuc').value = hinhThuc;
        document.getElementById('sua_DiaDiem').value = diaDiem;
        document.getElementById('sua_LinkCuocHop').value = linkHoc;
        document.getElementById('sua_NoiDung').value = noiDung;
        document.getElementById('sua_SoNhomToiDa').value = soNhom;

        toggleHinhThucSuaFields(hinhThuc);

        const modal = new bootstrap.Modal(document.getElementById('modalSuaKhungGio'));
        modal.show();
    }

    // 4. Mở Modal Hủy Khung Giờ
    function openModalHuyKhungGio(maLich, thoiGianStr, soNhomDaDat) {
        const form = document.getElementById('formHuyKhungGio');
        form.action = '/giangvien/lich-hen/' + maLich + '/huy';

        document.getElementById('huy_thoiGianStr').innerText = thoiGianStr;
        document.getElementById('huy_soNhomDaDat').innerText = soNhomDaDat;

        const alertBox = document.getElementById('alertHuyCanhBaoCoNhom');
        if (soNhomDaDat > 0) {
            alertBox.classList.remove('d-none');
        } else {
            alertBox.classList.add('d-none');
        }

        const modal = new bootstrap.Modal(document.getElementById('modalHuyKhungGio'));
        modal.show();
    }

    // 5. Mở Modal Hủy Lượt Đặt Của Nhóm (Giảng viên)
    function openModalHuyLuotDat(maLich, dangKyId, tenNhom) {
        const form = document.getElementById('formHuyLuotDat');
        form.action = '/giangvien/lich-hen/' + maLich + '/huy-luot-dat/' + dangKyId;

        document.getElementById('huy_tenNhom').innerText = tenNhom;

        const modal = new bootstrap.Modal(document.getElementById('modalHuyLuotDat'));
        modal.show();
    }

    // 6. Mở Modal Xác Nhận Đặt Lịch (Sinh viên)
    function openModalXacNhanDatLich(maLich, gvhd, ngay, gio, hinhThuc, noiDung, soCho) {
        const form = document.getElementById('formDatLich');
        form.action = '/sinhvien/lich-hen/' + maLich + '/dat-lich';

        document.getElementById('modal_dl_gvhd').innerText = gvhd;
        document.getElementById('modal_dl_ngay').innerText = ngay;
        document.getElementById('modal_dl_gio').innerText = gio;
        document.getElementById('modal_dl_hinhthuc').innerText = hinhThuc;
        document.getElementById('modal_dl_noidung').innerText = noiDung;

        const modal = new bootstrap.Modal(document.getElementById('modalXacNhanDatLich'));
        modal.show();
    }

    // 7. Mở Modal Hủy Lịch (Sinh viên)
    function openModalHuyLichSV(dangKyId, thoiGianStr) {
        const form = document.getElementById('formHuyLichSV');
        form.action = '/sinhvien/lich-hen/' + dangKyId + '/huy-lich';

        document.getElementById('sv_huy_thoiGian').innerText = thoiGianStr;

        const modal = new bootstrap.Modal(document.getElementById('modalHuyLichSV'));
        modal.show();
    }

    // 8. Lọc hiển thị khung giờ theo trạng thái bằng Javascript
    function filterAppointmentSlots(status) {
        const cards = document.querySelectorAll('.slot-item-card');
        cards.forEach(card => {
            const cardStatus = card.getAttribute('data-status');
            if (status === 'ALL') {
                card.style.display = '';
            } else if (cardStatus === status) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
@endpush
