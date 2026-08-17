@extends($layout)

@section('page_title', 'Lịch Tiến Độ Khóa Luận Tốt Nghiệp')

@push('styles')
<!-- FullCalendar 6.1.10 CSS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<style>
    .calendar-card {
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 59, 115, 0.08);
        border: none;
    }

    .fc-event {
        cursor: pointer;
        border-radius: 6px;
        padding: 2px 6px;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .fc-toolbar-title {
        font-size: 1.3rem !important;
        font-weight: bold;
        color: #003B73;
    }

    .fc-button-primary {
        background-color: #0072CE !important;
        border-color: #0072CE !important;
        border-radius: 8px !important;
    }

    .fc-button-primary:hover {
        background-color: #004A8F !important;
    }

    /* Countdown Widget Styling */
    .countdown-box {
        background: linear-gradient(135deg, #003B73 0%, #0072CE 100%);
        color: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 8px 25px rgba(0, 114, 206, 0.25);
    }

    .countdown-timer-wrap {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 15px;
    }

    .time-card {
        background: rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 12px 18px;
        text-align: center;
        min-width: 75px;
    }

    .time-num {
        font-size: 2rem;
        font-weight: bold;
        line-height: 1;
    }

    .time-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.85;
        margin-top: 4px;
    }

    .milestone-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="row g-4 mb-4">
    <!-- Countdown Widget -->
    <div class="col-lg-12">
        <div class="countdown-box">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <div class="text-uppercase small tracking-wide opacity-75">
                        <i class="fa-solid fa-hourglass-half me-1"></i> Mốc Báo Cáo Tiếp Theo
                    </div>
                    <h4 class="mb-0 fw-bold mt-1">
                        {{ $nextMilestone->TenMoc ?? 'Hiện tại chưa có mốc mới' }}
                    </h4>
                    @if($nextMilestone)
                    <div class="small opacity-85 mt-1">
                        <i class="fa-solid fa-calendar-day me-1"></i> Hạn nộp:
                        <strong>{{ date('d/m/Y', strtotime($nextMilestone->NgayKetThuc)) }}</strong>
                    </div>
                    @endif
                </div>

                @if($nextMilestone)
                <div class="countdown-timer-wrap" id="countdown-timer">
                    <div class="time-card">
                        <div class="time-num" id="cd-days">00</div>
                        <div class="time-label">Ngày</div>
                    </div>
                    <div class="time-card">
                        <div class="time-num" id="cd-hours">00</div>
                        <div class="time-label">Giờ</div>
                    </div>
                    <div class="time-card">
                        <div class="time-num" id="cd-minutes">00</div>
                        <div class="time-label">Phút</div>
                    </div>
                    <div class="time-card">
                        <div class="time-num" id="cd-seconds">00</div>
                        <div class="time-label">Giây</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card calendar-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-primary-custom mb-0">
            <i class="fa-regular fa-calendar-check me-2"></i>Lịch Tiến Độ Khóa Luận — {{ $roleTitle }}
        </h5>
        <div class="d-flex gap-2">
            <span class="badge bg-primary px-3 py-2">Mốc 1-3: File PDF</span>
            <span class="badge bg-warning text-dark px-3 py-2">Mốc 4: Link Code Git</span>
            <span class="badge bg-success px-3 py-2">Mốc 5: Hoàn Thành</span>
        </div>
    </div>

    <!-- Calendar Container -->
    <div id="full-calendar-view"></div>
</div>

<!-- Modal Chi Tiết Mốc -->
<div class="modal fade" id="eventDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" id="modal-header-bg">
                <h5 class="modal-title fw-bold" id="modal-event-title">Chi Tiết Mốc</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p id="modal-event-kehoach" class="text-muted small mb-2"></p>
                <div class="mb-3">
                    <label class="fw-bold small text-uppercase text-muted">Thời Gian Thực Hiện</label>
                    <div id="modal-event-time" class="fw-bold text-dark fs-6"></div>
                </div>
                <div>
                    <label class="fw-bold small text-uppercase text-muted">Mô Tả & Quy Định Nộp</label>
                    <div id="modal-event-desc" class="p-3 bg-light rounded-3 text-secondary"></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // FullCalendar Init
        const eventsData = {!! json_encode($events) !!};
        const calendarEl = document.getElementById('full-calendar-view');

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'vi',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            events: eventsData,
            eventClick: function(info) {
                const event = info.event;
                document.getElementById('modal-event-title').innerText = event.title;
                document.getElementById('modal-event-kehoach').innerText = 'Kế hoạch: ' + (event.extendedProps.keHoach || 'Chung');
                document.getElementById('modal-event-time').innerText = 
                    event.start.toLocaleDateString('vi-VN') + ' ➔ ' + 
                    (event.end ? new Date(event.end.getTime() - 86400000).toLocaleDateString('vi-VN') : event.start.toLocaleDateString('vi-VN'));
                document.getElementById('modal-event-desc').innerText = event.extendedProps.description || 'Không có mô tả chi tiết.';
                document.getElementById('modal-header-bg').style.backgroundColor = event.backgroundColor || '#0072CE';

                const modal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
                modal.show();
            }
        });
        calendar.render();

        // Countdown Timer Logic
        @if($nextMilestone)
            const targetDate = new Date("{{ $nextMilestone->NgayKetThuc }}T23:59:59").getTime();

            function updateCountdown() {
                const now = new Date().getTime();
                const diff = targetDate - now;

                if (diff <= 0) {
                    document.getElementById('countdown-timer').innerHTML = "<div class='fw-bold fs-5 text-warning'>Đã hết hạn nộp!</div>";
                    return;
                }

                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                document.getElementById('cd-days').innerText = String(days).padStart(2, '0');
                document.getElementById('cd-hours').innerText = String(hours).padStart(2, '0');
                document.getElementById('cd-minutes').innerText = String(minutes).padStart(2, '0');
                document.getElementById('cd-seconds').innerText = String(seconds).padStart(2, '0');
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);
        @endif
    });
</script>
@endpush
@endsection
