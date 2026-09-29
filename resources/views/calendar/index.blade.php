@extends($layout)

@section('page_title', 'Lịch Quy Trình & Cột Mốc Thời Gian')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<style>
    .fc-toolbar-title { font-size: 1.15rem !important; font-weight: 700; color: #003b73; }
    .fc-button-primary { background: #0072ce !important; border-color: #0072ce !important; border-radius: 8px !important; }
    .fc-event { border-radius: 6px; padding: 2px 4px; font-weight: 500; font-size: 0.8rem; }
</style>
@endpush

@section('content')

@if($layout === 'layouts.admin')
{{-- ── GIAO DIỆN ADMIN CHUẨN FIGMA ── --}}
<div class="page-header-flex mb-3">
    <div>
        <h4 class="page-main-title">
            <i class="fa-regular fa-calendar-days text-primary"></i>
            Quản Lý Lịch Biểu Quy Trình Khóa Luận (Hệ Thống)
        </h4>
        <p class="page-main-subtitle">
            Widget lịch tương tác kết hợp danh mục 12 mốc quy trình, tự động kích hoạt thông báo Deadline toàn hệ thống.
        </p>
    </div>
    <div>
        <select class="form-select form-select-sm shadow-sm" style="min-width: 280px; border-radius: 8px;">
            @if(isset($plans))
                @foreach($plans as $p)
                    <option value="{{ $p->MaKeHoach }}" {{ isset($activePlan) && $activePlan->MaKeHoach == $p->MaKeHoach ? 'selected' : '' }}>
                        {{ $p->TenKeHoach }}
                    </option>
                @endforeach
            @else
                <option selected>Kế hoạch Khóa 14DHTH — Học kỳ 1 (2026–2027)</option>
            @endif
        </select>
    </div>
</div>

{{-- ── HÀNG 1: LỊCH THÁNG & CÁC SỰ KIỆN / SYSTEM AUTOMATION ── --}}
<div class="row g-4 mb-4">
    {{-- Cột trái: FullCalendar Widget --}}
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="border: 1px solid #e2e8f0 !important; background: #fff;">
            <div id="admin-calendar-view" style="min-height: 380px;"></div>
            <div class="mt-3 pt-2 border-top d-flex justify-content-around flex-wrap gap-2 text-muted" style="font-size: 0.78rem;">
                <span><span class="rounded-circle d-inline-block me-1" style="width:8px;height:8px;background:#0072ce;"></span>Hôm nay (15/09)</span>
                <span><span class="rounded-circle d-inline-block me-1" style="width:8px;height:8px;background:#f59e0b;"></span>Deadline (18/09)</span>
                <span><span class="rounded-circle d-inline-block me-1" style="width:8px;height:8px;background:#10b981;"></span>Đã xong</span>
            </div>
        </div>
    </div>

    {{-- Cột phải: Các Sự Kiện & Deadline Trong Tháng + System Automation --}}
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="border: 1px solid #e2e8f0 !important; background: #fff;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">
                    📌 Các Sự Kiện &amp; Deadline Quy Trình Trong Tháng {{ date('m/Y') }}
                </h6>
                <span class="text-primary fw-semibold" style="font-size: 0.8rem;">Đang chạy 2 sự kiện</span>
            </div>

            {{-- Event Card 1 --}}
            <div class="p-3 rounded-3 mb-2" style="background: #fefce8; border: 1px solid #fef08a;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">
                            Mốc 6: Báo cáo tiến độ đợt 1 (Đề cương &amp; Thiết kế CSDL 30 bảng)
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.76rem;">
                            <i class="fa-regular fa-clock me-1 text-warning"></i>
                            Thời gian: 15/09/2026 → 18/09/2026 (Hạn chót: 23:59 ngày 18/09) • Đối tượng: Sinh viên &amp; GVHD
                        </div>
                    </div>
                    <span class="badge" style="background: #fef9c3; color: #a16207; font-size: 0.76rem; border-radius: 9999px;">
                        ● Đang mở nộp
                    </span>
                </div>
            </div>

            {{-- Event Card 2 --}}
            <div class="p-3 rounded-3 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">
                            Mốc 7: Báo cáo tiến độ đợt 2 (Thiết kế hệ thống &amp; Mockup UI/UX)
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.76rem;">
                            <i class="fa-regular fa-clock me-1 text-primary"></i>
                            Thời gian: 24/09/2026 → 28/09/2026 • Hệ thống sẽ tự động kích hoạt Trợ lý AI tóm tắt báo cáo
                        </div>
                    </div>
                    <span class="badge" style="background: #e2e8f0; color: #475569; font-size: 0.76rem; border-radius: 9999px;">
                        ○ Sắp diễn ra
                    </span>
                </div>
            </div>

            {{-- System Automation Box --}}
            <div class="p-3 rounded-3 mt-auto" style="background: #eff6ff; border: 1px solid #bae6fd;">
                <div class="fw-bold mb-2 text-primary" style="font-size: 0.82rem;">
                    <i class="fa-solid fa-bell me-1"></i> THÔNG BÁO NHẮC NHỞ TỰ ĐỘNG CỦA HỆ THỐNG (SYSTEM AUTOMATION):
                </div>
                <div style="font-size: 0.78rem; color: #1e3a8a; line-height: 1.6;">
                    • Hệ thống sẽ tự động gửi email &amp; thông báo đẩy tới tất cả 48 Sinh viên và 10 Giảng viên trước Deadline 24 giờ.<br>
                    • Các nhóm chưa nộp báo cáo Mốc 1: <strong>Nhóm N02 (Sinh Viên 4)</strong> — Cần gửi tin nhắn cảnh báo trực tiếp.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── HÀNG 2: BẢNG THIẾT LẬP CHI TIẾT 12 CỘT MỐC QUY TRÌNH TOÀN KHÓA ── --}}
<div class="admin-table-card">
    <div class="admin-table-header">
        <h5 class="admin-table-header-title">
            <i class="fa-solid fa-calendar-check text-primary"></i>
            Thiết Lập Chi Tiết 12 Cột Mốc Thời Gian Quy Trình Toàn Khóa
        </h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-3 fw-semibold">
                <i class="fa-solid fa-paper-plane me-1"></i> Gửi Nhắc Nhở Toàn Trường
            </button>
            <a href="{{ route('admin.kehoach.create') }}" class="btn btn-sm btn-success rounded-3 px-3 fw-semibold" style="background: #198754;">
                <i class="fa-solid fa-plus me-1"></i> Thêm Mốc Mới
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width: 50px;">STT</th>
                    <th style="min-width: 280px;">Giai Đoạn / Nội Dung Quy Trình</th>
                    <th style="min-width: 140px;">Đối Tượng</th>
                    <th style="min-width: 180px;">Thời Gian Thực Hiện</th>
                    <th style="min-width: 150px;">Mã Loại Giai Đoạn</th>
                    <th class="text-center" style="width: 120px;">Trạng Thái</th>
                    <th class="text-center" style="width: 90px;">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($allMilestones) && $allMilestones->count() > 0)
                    @foreach($allMilestones as $idx => $m)
                    @php
                        $today = now()->format('Y-m-d');
                        $statusClass = '';
                        $statusLabel = 'Chưa mở';
                        $statusBadgeClass = 'text-muted';
                        $daysLeft = (int) now()->diffInDays(\Carbon\Carbon::parse($m->NgayKetThuc), false);
                        
                        if ($m->NgayKetThuc < $today) {
                            $statusLabel = '✓ Đã xong';
                            $statusBadgeClass = 'badge-status-green';
                        } elseif ($today >= $m->NgayBatDau && $today <= $m->NgayKetThuc) {
                            $statusLabel = '● Đang mở';
                            $statusBadgeClass = 'badge-status-amber';
                            $statusClass = 'bg-warning bg-opacity-10';
                        }
                    @endphp
                    <tr class="{{ $statusClass }}">
                        <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $m->TenMoc }}</div>
                            @if($today >= $m->NgayBatDau && $today <= $m->NgayKetThuc)
                                <div class="text-danger fw-bold mt-1" style="font-size: 0.72rem;">
                                    ⚡ Hạn chót: {{ date('d/m/Y', strtotime($m->NgayKetThuc)) }} ({{ $daysLeft >= 0 ? "Còn {$daysLeft} ngày" : "Hôm nay" }})
                                </div>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size: 0.82rem;">
                            {{ str_contains(strtolower($m->TenMoc), 'sinh viên') ? 'Sinh viên' : (str_contains(strtolower($m->TenMoc), 'giảng viên') || str_contains(strtolower($m->TenMoc), 'gvhd') ? 'Giảng viên & GVHD' : 'Khoa / Giáo vụ') }}
                        </td>
                        <td class="fw-semibold text-dark" style="font-size: 0.82rem;">
                            {{ date('d/m/Y', strtotime($m->NgayBatDau)) }} → {{ date('d/m/Y', strtotime($m->NgayKetThuc)) }}
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                {{ $m->MaMoc }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="{{ $statusBadgeClass }}" style="font-size: 0.78rem;">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1">
                                <a href="#" class="btn-action-icon btn-action-edit" title="Chỉnh sửa mốc">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="#" class="btn-action-icon btn-action-view" title="Gửi thông báo nhắc">
                                    <i class="fa-solid fa-bell"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                @else
                    @php
                        $defaultPhases = [
                            ['stt' => 1, 'name' => '1. Sinh viên tạo nhóm trên phần mềm', 'target' => 'Sinh viên', 'time' => '10/08/2026 → 10/08/2026', 'code' => 'TAO_NHOM', 'status' => 'done'],
                            ['stt' => 2, 'name' => '2. Nhóm trưởng đăng ký đề tài chính thức', 'target' => 'Sinh viên', 'time' => '11/08/2026 → 11/08/2026', 'code' => 'DANG_KY_DE_TAI', 'status' => 'done'],
                            ['stt' => 3, 'name' => '3. Xử lý các trường hợp ngoại lệ & duyệt đề tài', 'target' => 'Khoa / Giáo vụ', 'time' => '12/08/2026 → 12/08/2026', 'code' => 'XU_LY_NGOAI_LE', 'status' => 'done'],
                            ['stt' => 4, 'name' => '4. Công bố danh sách SV & phân công GVHD', 'target' => 'Khoa / Bộ môn', 'time' => '14/08/2026 → 14/08/2026', 'code' => 'CONG_BO_GVHD', 'status' => 'done'],
                            ['stt' => 5, 'name' => '5. Sinh viên liên hệ GVHD & Họp giao nhiệm vụ', 'target' => 'Sinh viên & GVHD', 'time' => '14/08/2026 → 17/08/2026', 'code' => 'LIEN_HE_GVHD', 'status' => 'done'],
                            ['stt' => 6, 'name' => '6. Bắt đầu thực hiện & hoàn thiện đề cương chi tiết', 'target' => 'Sinh viên & GVHD', 'time' => '17/08/2026 → 28/08/2026', 'code' => 'THUC_HIEN', 'status' => 'done'],
                            ['stt' => 7, 'name' => '7. Báo cáo tiến độ đợt 1 (Đề cương & CSDL)', 'target' => 'Sinh viên & GVHD', 'time' => '14/09/2026 → 18/09/2026', 'code' => 'BAO_CAO_TIEN_DO_1', 'status' => 'active'],
                            ['stt' => 8, 'name' => '8. Báo cáo tiến độ đợt 2 (Thiết kế & Xây dựng hệ thống)', 'target' => 'Sinh viên & GVHD', 'time' => '12/10/2026 → 16/10/2026', 'code' => 'BAO_CAO_TIEN_DO_2', 'status' => 'pending'],
                            ['stt' => 9, 'name' => '9. Báo cáo tiến độ đợt 3 (Kiểm thử & Dự thảo báo cáo)', 'target' => 'Sinh viên & GVHD', 'time' => '02/11/2026 → 06/11/2026', 'code' => 'BAO_CAO_TIEN_DO_3', 'status' => 'pending'],
                            ['stt' => 10, 'name' => '10. Kiểm tra đạo văn (Quét Turnitin < 20%)', 'target' => 'Sinh viên & Khoa', 'time' => '09/11/2026 → 12/11/2026', 'code' => 'DAO_VAN', 'status' => 'pending'],
                            ['stt' => 11, 'name' => '11. GVHD xác nhận đủ điều kiện bảo vệ', 'target' => 'GVHD', 'time' => '13/11/2026 → 16/11/2026', 'code' => 'GVHD_XAC_NHAN', 'status' => 'pending'],
                            ['stt' => 12, 'name' => '12. Nộp khóa luận chính thức & hoàn tất hồ sơ', 'target' => 'Sinh viên & Giáo vụ', 'time' => '18/11/2026 → 20/11/2026', 'code' => 'NOP_BAO_CAO', 'status' => 'pending'],
                            ['stt' => 13, 'name' => '13. Lễ bảo vệ chính thức trước Hội đồng', 'target' => 'Hội đồng & Sinh viên', 'time' => '25/11/2026 → 28/11/2026', 'code' => 'BAO_VE', 'status' => 'pending'],
                        ];
                    @endphp
                    @foreach($defaultPhases as $phase)
                    <tr class="{{ $phase['status'] === 'active' ? 'bg-warning bg-opacity-10' : '' }}">
                        <td class="text-center fw-bold">{{ $phase['stt'] }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $phase['name'] }}</div>
                            @if($phase['status'] === 'active')
                                <div class="text-danger fw-bold mt-1" style="font-size: 0.72rem;">
                                    ⚡ Hạn chót: 18/09/2026 (Còn 3 ngày)
                                </div>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size: 0.82rem;">{{ $phase['target'] }}</td>
                        <td class="fw-semibold text-dark" style="font-size: 0.82rem;">{{ $phase['time'] }}</td>
                        <td>
                            <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                {{ $phase['code'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($phase['status'] === 'done')
                                <span class="badge-status-green">✓ Đã xong</span>
                            @elseif($phase['status'] === 'active')
                                <span class="badge-status-amber">● Đang mở</span>
                            @else
                                <span class="text-muted" style="font-size: 0.78rem;">Chưa mở</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1">
                                <a href="#" class="btn-action-icon btn-action-edit" title="Chỉnh sửa mốc">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="#" class="btn-action-icon btn-action-view" title="Gửi thông báo nhắc">
                                    <i class="fa-solid fa-bell"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

@else
{{-- ── GIAO DIỆN GIẢNG VIÊN / SINH VIÊN ── --}}
<div class="card calendar-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-primary mb-0">
            <i class="fa-regular fa-calendar-check me-2"></i>Lịch Tiến Độ Khóa Luận — {{ $roleTitle }}
        </h5>
        <div class="d-flex gap-2">
            <span class="badge bg-primary px-3 py-2">Mốc 1-3: File PDF</span>
            <span class="badge bg-warning text-dark px-3 py-2">Mốc 4: Link Code Git</span>
            <span class="badge bg-success px-3 py-2">Mốc 5: Hoàn Thành</span>
        </div>
    </div>
    <div id="full-calendar-view"></div>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventsData = {!! json_encode($events ?? []) !!};
    const adminCalEl = document.getElementById('admin-calendar-view');
    const fullCalEl = document.getElementById('full-calendar-view');

    if (adminCalEl) {
        const cal = new FullCalendar.Calendar(adminCalEl, {
            initialView: 'dayGridMonth',
            locale: 'vi',
            height: 380,
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: 'today'
            },
            events: eventsData
        });
        cal.render();
    }

    if (fullCalEl) {
        const cal2 = new FullCalendar.Calendar(fullCalEl, {
            initialView: 'dayGridMonth',
            locale: 'vi',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            events: eventsData
        });
        cal2.render();
    }
});
</script>
@endpush

@endsection

