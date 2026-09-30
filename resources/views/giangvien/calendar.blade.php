@extends('layouts.giangvien')

@section('page_title', 'Lịch Công Việc')

@push('styles')
<style>
    /* Mini Calendar Styling */
    .mini-cal-card {
        background: #ffffff;
        border: 0;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        padding: 18px 20px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .mini-cal-table th {
        font-size: 0.78rem;
        font-weight: 700;
        color: #64748b;
        text-align: center;
        padding: 6px 0;
        border: none;
    }
    .mini-cal-table th.th-cn {
        color: #ef4444;
    }
    .mini-cal-table td {
        font-size: 0.85rem;
        text-align: center;
        padding: 4px 0;
        border: none;
        vertical-align: middle;
        height: 38px;
        width: 14.28%;
    }
    .mini-cal-day {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        color: #1e293b;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .mini-cal-day:hover {
        background-color: #f1f5f9;
    }
    .mini-cal-day.muted-day {
        color: #cbd5e1;
        cursor: default;
    }
    .mini-cal-day.muted-day:hover {
        background-color: transparent;
    }
    .mini-cal-day.sunday-day {
        color: #ef4444;
    }
    .mini-cal-day.active-day-8 {
        background-color: #0072ce !important;
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 3px 8px rgba(0, 114, 206, 0.35);
    }
    .mini-cal-day.active-day-9 {
        background-color: #f59e0b !important;
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 3px 8px rgba(245, 158, 11, 0.35);
    }
    .mini-cal-day.active-day-10 {
        background-color: #10b981 !important;
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 3px 8px rgba(16, 185, 129, 0.35);
    }

    /* Stepper Styling */
    .timeline-card {
        background: #ffffff;
        border: 0;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        padding: 18px 20px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .timeline-stepper {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        position: relative;
        padding: 10px 0 12px 0;
    }
    .timeline-stepper::before {
        content: '';
        position: absolute;
        top: 23px;
        left: 35px;
        right: 35px;
        height: 3px;
        background-color: #cbd5e1;
        z-index: 1;
    }
    .stepper-progress-line {
        position: absolute;
        top: 23px;
        left: 35px;
        width: 50%;
        height: 3px;
        background-color: #10b981;
        z-index: 1;
    }
    .stepper-step {
        position: relative;
        z-index: 2;
        text-align: center;
        flex: 1;
    }
    .stepper-node {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background-color: #ffffff;
        border: 3px solid #94a3b8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 6px;
        font-size: 0.7rem;
        transition: all 0.2s;
    }
    .stepper-step.completed .stepper-node {
        background-color: #10b981;
        border-color: #10b981;
        color: #ffffff;
    }
    .stepper-step.current-blue .stepper-node {
        background-color: #0072ce;
        border-color: #0072ce;
        color: #ffffff;
        box-shadow: 0 0 0 4px rgba(0, 114, 206, 0.2);
    }
    .stepper-step.danger-ring .stepper-node {
        background-color: #ffffff;
        border-color: #ef4444;
        color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
    }
    .stepper-title {
        font-size: 0.76rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 2px;
        line-height: 1.25;
    }
    .stepper-date {
        font-size: 0.72rem;
        color: #64748b;
    }

    /* Warning Notice Box */
    .notice-box {
        background-color: #fff5f5;
        border: 1px solid #fed7d7;
        border-radius: 8px;
        padding: 10px 14px;
        color: #9b2c2c;
        font-size: 0.8rem;
        line-height: 1.55;
    }

    /* Table styling matching mockup */
    .table-schedule {
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
    }
    .table-schedule th {
        background: #f8fafc;
        font-size: 0.76rem;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        border-bottom: 1px solid #e2e8f0;
        padding: 14px 16px;
    }
    .table-schedule td {
        padding: 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    .group-item-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 12px;
        margin-bottom: 6px;
        transition: all 0.2s ease;
    }
    .group-item-box:last-child {
        margin-bottom: 0;
    }
    .group-item-box:hover {
        background: #f0f9ff;
        border-color: #bae6fd;
    }
    .group-item-box.active-box {
        background: #f0f9ff;
        border-color: #0284c7;
    }

    .btn-export-google {
        background-color: #0099cc !important;
        border-color: #0099cc !important;
        color: #ffffff !important;
        font-weight: 600;
        font-size: 0.82rem;
        border-radius: 6px;
        padding: 6px 14px;
        box-shadow: 0 2px 6px rgba(0, 153, 204, 0.25);
    }
    .btn-export-google:hover {
        background-color: #0088b6 !important;
        color: #ffffff !important;
    }

    .badge-time-active {
        background: #fef9c3;
        color: #b45309;
        font-weight: 700;
        font-size: 0.75rem;
        border: 1px solid #fde047;
        padding: 4px 8px;
        border-radius: 6px;
        display: inline-block;
    }
    .badge-time-muted {
        background: #f1f5f9;
        color: #475569;
        font-weight: 600;
        font-size: 0.75rem;
        border: 1px solid #e2e8f0;
        padding: 4px 8px;
        border-radius: 6px;
        display: inline-block;
    }

    .role-badge-gold {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 4px 12px;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .role-badge-silver {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 4px 12px;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    <!-- BREADCRUMB -->
    <div class="text-muted small mb-2 d-flex align-items-center gap-1">
        <i class="fa-solid fa-angle-right text-muted" style="font-size: 0.75rem;"></i>
        <span>Trang chủ</span>
        <i class="fa-solid fa-angle-right text-muted" style="font-size: 0.75rem;"></i>
        <span>Khóa luận tốt nghiệp</span>
        <i class="fa-solid fa-angle-right text-muted" style="font-size: 0.75rem;"></i>
        <span class="text-primary fw-bold">Lịch công việc</span>
    </div>

    <!-- HEADER TITLE & GOOGLE CALENDAR BUTTON -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div class="d-flex align-items-center">
            <i class="fa-solid fa-calendar-days text-danger fs-4 me-2"></i>
            <h5 class="fw-bold text-dark mb-0" style="font-size: 1.15rem;">
                Lịch Biểu Báo Cáo Khóa Luận &amp; Lịch Chấm Hội Đồng Bảo Vệ
            </h5>
        </div>
        <div>
            <button class="btn btn-export-google" onclick="exportToGoogleCalendar()">
                <i class="fa-brands fa-google me-1"></i> Xuất Lịch Google
            </button>
        </div>
    </div>

    <!-- TOP SECTION: Mini Calendar (Left) & Timeline + Ghi chú (Right) -->
    <div class="row g-3 mb-4">
        <!-- 1. Mini Calendar (Tháng 01 / 2026) -->
        <div class="col-lg-5 col-md-12">
            <div class="mini-cal-card">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                        <h6 class="fw-bold text-dark mb-0 fs-6">Tháng 01 / 2026</h6>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-light border py-0 px-2 text-muted" title="Tháng trước">&lt;</button>
                            <button class="btn btn-sm btn-light border py-0 px-2 text-muted" title="Tháng sau">&gt;</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table mini-cal-table mb-0">
                            <thead>
                                <tr>
                                    <th>Hai</th>
                                    <th>Ba</th>
                                    <th>Tư</th>
                                    <th>Năm</th>
                                    <th>Sáu</th>
                                    <th>Bảy</th>
                                    <th class="th-cn">CN</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="mini-cal-day muted-day">29</span></td>
                                    <td><span class="mini-cal-day muted-day">30</span></td>
                                    <td><span class="mini-cal-day muted-day">31</span></td>
                                    <td><span class="mini-cal-day">1</span></td>
                                    <td><span class="mini-cal-day">2</span></td>
                                    <td><span class="mini-cal-day">3</span></td>
                                    <td><span class="mini-cal-day sunday-day">4</span></td>
                                </tr>
                                <tr>
                                    <td><span class="mini-cal-day">5</span></td>
                                    <td><span class="mini-cal-day">6</span></td>
                                    <td><span class="mini-cal-day">7</span></td>
                                    <td>
                                        <span class="mini-cal-day active-day-8" onclick="focusRow('row-hd-01')" title="08/01/2026: Hội đồng 01 (Chủ tịch HĐ)">
                                            8
                                        </span>
                                    </td>
                                    <td>
                                        <span class="mini-cal-day active-day-9" onclick="focusRow('row-hd-03')" title="09/01/2026: Hội đồng 03 (Ủy viên HĐ)">
                                            9
                                        </span>
                                    </td>
                                    <td>
                                        <span class="mini-cal-day active-day-10" onclick="focusRow('row-hd-05')" title="10/01/2026: Hội đồng 05 (Ủy viên HĐ)">
                                            10
                                        </span>
                                    </td>
                                    <td><span class="mini-cal-day sunday-day">11</span></td>
                                </tr>
                                <tr>
                                    <td><span class="mini-cal-day">12</span></td>
                                    <td><span class="mini-cal-day">13</span></td>
                                    <td><span class="mini-cal-day">14</span></td>
                                    <td><span class="mini-cal-day">15</span></td>
                                    <td><span class="mini-cal-day">16</span></td>
                                    <td><span class="mini-cal-day">17</span></td>
                                    <td><span class="mini-cal-day sunday-day">18</span></td>
                                </tr>
                                <tr>
                                    <td><span class="mini-cal-day">19</span></td>
                                    <td><span class="mini-cal-day">20</span></td>
                                    <td><span class="mini-cal-day">21</span></td>
                                    <td><span class="mini-cal-day">22</span></td>
                                    <td><span class="mini-cal-day">23</span></td>
                                    <td><span class="mini-cal-day">24</span></td>
                                    <td><span class="mini-cal-day sunday-day">25</span></td>
                                </tr>
                                <tr>
                                    <td><span class="mini-cal-day">26</span></td>
                                    <td><span class="mini-cal-day">27</span></td>
                                    <td><span class="mini-cal-day">28</span></td>
                                    <td><span class="mini-cal-day">29</span></td>
                                    <td><span class="mini-cal-day">30</span></td>
                                    <td><span class="mini-cal-day">31</span></td>
                                    <td><span class="mini-cal-day muted-day">1</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Legend matching mockup -->
                <div class="d-flex justify-content-center align-items-center gap-3 pt-2 border-top mt-3 small">
                    <span class="d-inline-flex align-items-center">
                        <span class="badge rounded-circle p-1 me-1" style="background-color: #0072ce;"> </span>
                        <span class="text-secondary" style="font-size: 0.75rem;">Chủ tịch HĐ</span>
                    </span>
                    <span class="d-inline-flex align-items-center">
                        <span class="badge rounded-circle p-1 me-1" style="background-color: #f59e0b;"> </span>
                        <span class="text-secondary" style="font-size: 0.75rem;">Ủy viên HĐ</span>
                    </span>
                    <span class="d-inline-flex align-items-center">
                        <span class="badge rounded-circle p-1 me-1" style="background-color: #10b981;"> </span>
                        <span class="text-secondary" style="font-size: 0.75rem;">Báo cáo Mốc</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. Timeline Card & Ghi chú -->
        <div class="col-lg-7 col-md-12">
            <div class="timeline-card">
                <div>
                    <h6 class="fw-bold text-dark mb-3 fs-6">Timeline Kế Hoạch Đồ Án &amp; Khóa Luận (Học kỳ II 2025-2026)</h6>
                    
                    <!-- Stepper Bar -->
                    <div class="timeline-stepper">
                        <div class="stepper-progress-line"></div>
                        
                        <!-- Step 1 -->
                        <div class="stepper-step completed">
                            <div class="stepper-node"><i class="fa-solid fa-check"></i></div>
                            <div class="stepper-title text-dark">Đăng ký đề tài</div>
                            <div class="stepper-date">15/02/2026</div>
                        </div>
                        
                        <!-- Step 2 -->
                        <div class="stepper-step completed">
                            <div class="stepper-node"><i class="fa-solid fa-check"></i></div>
                            <div class="stepper-title text-dark">Báo cáo Mốc 1</div>
                            <div class="stepper-date">15/10/2026</div>
                        </div>
                        
                        <!-- Step 3 (Current Active) -->
                        <div class="stepper-step current-blue">
                            <div class="stepper-node"><i class="fa-solid fa-circle" style="font-size: 0.45rem;"></i></div>
                            <div class="stepper-title text-primary">Báo cáo Mốc 2</div>
                            <div class="stepper-date text-primary fw-semibold">15/11/2026</div>
                        </div>
                        
                        <!-- Step 4 -->
                        <div class="stepper-step">
                            <div class="stepper-node"></div>
                            <div class="stepper-title text-muted">Hồ sơ bảo vệ</div>
                            <div class="stepper-date">15/12/2026</div>
                        </div>
                        
                        <!-- Step 5 (Council Defense) -->
                        <div class="stepper-step danger-ring">
                            <div class="stepper-node"></div>
                            <div class="stepper-title text-dark">Bảo vệ Hội đồng</div>
                            <div class="stepper-date text-danger fw-bold">08/01/2026</div>
                        </div>
                    </div>
                </div>

                <!-- Warning Notice Box matching mockup text -->
                <div class="notice-box mt-3">
                    <div class="fw-bold mb-1 text-danger">
                        * LƯU Ý CHO GIẢNG VIÊN HƯỚNG DẪN &amp; THÀNH VIÊN HỘI ĐỒNG:
                    </div>
                    <ul class="mb-1 ps-3">
                        <li>Giảng viên hoàn tất nhận xét Mốc 2 trước ngày <strong>20/11/2026</strong> trên hệ thống.</li>
                        <li>Xác nhận điều kiện bảo vệ (Turnitin &lt; 20%) cho các nhóm hướng dẫn trước ngày <strong>25/12/2026</strong>.</li>
                        <li>Hội đồng 01 bắt đầu lúc <strong>08:00 ngày 08/01/2026</strong> tại B.402, đề nghị cán bộ chấm có mặt trước 15 phút.</li>
                    </ul>
                    <div class="text-muted" style="font-size: 0.75rem;">
                        Mọi thắc mắc về phân công lịch hội đồng liên hệ Giáo vụ Khoa: (028) 38163 318
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM SECTION: Bảng Danh Sách Buổi Hội Đồng Bảo Vệ & Nhóm Báo Cáo -->
    <div class="table-schedule mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="18%" class="ps-3">NGÀY &amp; CA BẢO VỆ</th>
                        <th width="16%">HỘI ĐỒNG</th>
                        <th width="12%">VAI TRÒ</th>
                        <th width="14%">ĐỊA ĐIỂM</th>
                        <th width="30%">DANH SÁCH NHÓM BÁO CÁO (MSSV / ĐỀ TÀI)</th>
                        <th width="10%" class="text-center pe-3">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- HỘI ĐỒNG 01 -->
                    <tr id="row-hd-01">
                        <td class="ps-3 py-3">
                            <span class="badge-time-active mb-1">
                                08:00 - 11:30
                            </span>
                            <div class="fw-bold text-dark fs-6 mt-1">08/01/2026 (Thứ Năm)</div>
                            <div class="small text-success fw-semibold mt-1">
                                <i class="fa-solid fa-circle me-1" style="font-size: 0.5rem;"></i>Đang mở chấm điểm
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark fs-6">Hội đồng 01</div>
                            <div class="small text-muted">Khoa CNTT (KTPM)</div>
                        </td>
                        <td>
                            <span class="role-badge-gold">
                                <i class="fa-solid fa-crown text-warning"></i> Chủ tịch HĐ
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">Phòng B.402</div>
                            <div class="small text-muted">140 Lê Trọng Tấn</div>
                        </td>
                        <td>
                            <div class="group-item-box active-box">
                                <div class="fw-bold text-primary" style="font-size: 0.85rem;">
                                    Ca 1 (08:00): Nhóm 01 – Hồ Chí Dũng
                                </div>
                                <div class="small text-muted mt-1">
                                    QL công tác khóa luận tốt nghiệp tại HUIT
                                </div>
                            </div>
                            <div class="group-item-box active-box">
                                <div class="fw-bold text-primary" style="font-size: 0.85rem;">
                                    Ca 2 (08:50): Nhóm 04 – Trần Văn An
                                </div>
                                <div class="small text-muted mt-1">
                                    Hỗ trợ người khiếm thị nhận diện vật cản
                                </div>
                            </div>
                        </td>
                        <td class="text-center pe-3">
                            <a href="{{ route('giangvien.chamdiem.index') }}" class="btn btn-sm text-white px-3 fw-semibold shadow-sm" style="background-color: #0099cc; border-radius: 6px;">
                                Chấm Điểm
                            </a>
                        </td>
                    </tr>

                    <!-- HỘI ĐỒNG 03 -->
                    <tr id="row-hd-03">
                        <td class="ps-3 py-3">
                            <span class="badge-time-muted mb-1">
                                13:30 - 16:30
                            </span>
                            <div class="fw-bold text-dark fs-6 mt-1">09/01/2026 (Thứ Sáu)</div>
                            <div class="small text-muted mt-1">
                                <i class="fa-regular fa-circle me-1" style="font-size: 0.5rem;"></i>Chưa diễn ra
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark fs-6">Hội đồng 03</div>
                            <div class="small text-muted">Khoa CNTT (HTTT)</div>
                        </td>
                        <td>
                            <span class="role-badge-silver">
                                Ủy viên HĐ
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">Phòng B.301</div>
                            <div class="small text-muted">140 Lê Trọng Tấn</div>
                        </td>
                        <td>
                            <div class="group-item-box">
                                <div class="fw-bold text-primary" style="font-size: 0.85rem;">
                                    Ca 1 (13:30): Nhóm 07 – Phạm Quốc Huy
                                </div>
                                <div class="small text-muted mt-1">
                                    Nghiên cứu Microservices cổng thông tin đào tạo
                                </div>
                            </div>
                            <div class="group-item-box">
                                <div class="fw-bold text-primary" style="font-size: 0.85rem;">
                                    Ca 2 (14:20): Nhóm 12 – Vũ Minh Anh
                                </div>
                                <div class="small text-muted mt-1">
                                    Phát hiện tấn công DDoS mạng IoT bằng AI
                                </div>
                            </div>
                        </td>
                        <td class="text-center pe-3">
                            <button class="btn btn-outline-primary btn-sm px-3" style="border-radius: 6px;" onclick="showDetailModal('Hội đồng 03', '09/01/2026', 'Phòng B.301', 'Ủy viên HĐ')">
                                Chi Tiết
                            </button>
                        </td>
                    </tr>

                    <!-- HỘI ĐỒNG 05 -->
                    <tr id="row-hd-05">
                        <td class="ps-3 py-3">
                            <span class="badge-time-muted mb-1">
                                08:00 - 11:30
                            </span>
                            <div class="fw-bold text-dark fs-6 mt-1">10/01/2026 (Thứ Bảy)</div>
                            <div class="small text-muted mt-1">
                                <i class="fa-regular fa-circle me-1" style="font-size: 0.5rem;"></i>Chưa diễn ra
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark fs-6">Hội đồng 05</div>
                            <div class="small text-muted">Khoa CNTT (KHDL &amp; AI)</div>
                        </td>
                        <td>
                            <span class="role-badge-silver">
                                Ủy viên HĐ
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">Phòng B.402</div>
                            <div class="small text-muted">140 Lê Trọng Tấn</div>
                        </td>
                        <td>
                            <div class="group-item-box">
                                <div class="fw-bold text-primary" style="font-size: 0.85rem;">
                                    Ca 1 (08:00): Nhóm 09 – Nguyễn Văn Bình
                                </div>
                                <div class="small text-muted mt-1">
                                    Nhận diện biển số xe thông minh tích hợp IoT
                                </div>
                            </div>
                            <div class="group-item-box">
                                <div class="fw-bold text-primary" style="font-size: 0.85rem;">
                                    Ca 2 (08:50): Nhóm 15 – Lê Hoàng Nam
                                </div>
                                <div class="small text-muted mt-1">
                                    Trợ lý ảo hỏi đáp quy chế đào tạo HUIT
                                </div>
                            </div>
                        </td>
                        <td class="text-center pe-3">
                            <button class="btn btn-outline-primary btn-sm px-3" style="border-radius: 6px;" onclick="showDetailModal('Hội đồng 05', '10/01/2026', 'Phòng B.402', 'Ủy viên HĐ')">
                                Chi Tiết
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2 bg-white">
            <div class="text-muted small fw-medium">
                Tổng cộng: <strong class="text-dark">3 buổi Hội đồng bảo vệ</strong> (6 nhóm báo cáo)
            </div>
            <div>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                    <li class="page-item active"><span class="page-link" style="background-color: #0099cc; border-color: #0099cc;">1</span></li>
                    <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                </ul>
            </div>
        </div>
    </div>

</div>

<!-- Modal Chi Tiết Hội Đồng -->
<div class="modal fade" id="modalDetailHD" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 border-0 shadow">
            <div class="modal-header text-white" style="background-color: #0072ce;">
                <h6 class="modal-title fw-bold" id="detailHdTitle">Chi Tiết Hội Đồng</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="text-muted small fw-bold">NGÀY BẢO VỆ</label>
                    <div class="fw-bold fs-6" id="detailHdDate"></div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small fw-bold">ĐỊA ĐIỂM</label>
                    <div class="fw-bold fs-6" id="detailHdLocation"></div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small fw-bold">VAI TRÒ CỦA BẠN</label>
                    <div class="fw-bold fs-6 text-primary" id="detailHdRole"></div>
                </div>
                <div class="alert alert-info py-2 px-3 small mb-0 rounded-2">
                    <i class="fa-solid fa-circle-info me-1"></i> Cổng chấm điểm cho Hội đồng này sẽ tự động mở vào đúng ngày bảo vệ theo quy định của Khoa.
                </div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-secondary btn-sm px-3 rounded-2" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function focusRow(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            row.style.transition = 'background-color 0.4s ease';
            const origBg = row.style.backgroundColor;
            row.style.backgroundColor = '#e0f2fe';
            setTimeout(() => {
                row.style.backgroundColor = origBg;
            }, 1500);
        }
    }

    function showDetailModal(title, date, location, role) {
        document.getElementById('detailHdTitle').innerText = 'Chi Tiết ' + title;
        document.getElementById('detailHdDate').innerText = date;
        document.getElementById('detailHdLocation').innerText = location + ' (140 Lê Trọng Tấn, P. Tây Thạnh, Q. Tân Phú)';
        document.getElementById('detailHdRole').innerText = role;
        const modal = new bootstrap.Modal(document.getElementById('modalDetailHD'));
        modal.show();
    }

    function exportToGoogleCalendar() {
        const events = [
            {
                title: 'Hội đồng 01 - Chấm Bảo Vệ Khóa Luận HUIT',
                start: '20260108T080000',
                end: '20260108T113000',
                details: 'Chủ tịch HĐ 01 tại Phòng B.402. Chấm điểm 2 ca: Nhóm 01 và Nhóm 04.',
                location: 'Phòng B.402, 140 Lê Trọng Tấn, Tây Thạnh, Tân Phú, TP.HCM'
            }
        ];
        const ev = events[0];
        const url = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(ev.title)}&dates=${ev.start}/${ev.end}&details=${encodeURIComponent(ev.details)}&location=${encodeURIComponent(ev.location)}`;
        window.open(url, '_blank');
    }
</script>
@endpush
