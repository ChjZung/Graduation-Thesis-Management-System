@php
    $curUser = Auth::user();
    $role = $curUser?->VaiTro ?? '';

    $allRoute = route('thongbao.index');
    if ($role === 'SinhVien') {
        $allRoute = route('sinhvien.thongbao.index');
    } elseif ($role === 'GiangVien') {
        $allRoute = route('giangvien.thongbao.index');
    }

    $notiQuery = \App\Models\ThongBao::whereIn('TrangThai', ['Đã phát hành', 'ĐÃ GỬI', 'da_gui', 'ACTIVE', 'DaPhatHanh']);
    if ($role === 'SinhVien') {
        $notiQuery->where(function($q) {
            $q->where('DoiTuongNhan', 'Sinh viên')
              ->orWhere('DoiTuongNhan', 'Tất cả')
              ->orWhereNull('DoiTuongNhan');
        });
    } elseif (in_array($role, ['GiangVien', 'TruongBoMon', 'TruongKhoa'])) {
        $notiQuery->where(function($q) {
            $q->where('DoiTuongNhan', 'Giảng viên')
              ->orWhere('DoiTuongNhan', 'Tất cả')
              ->orWhereNull('DoiTuongNhan');
        });
    }

    $recentNotis = (clone $notiQuery)->orderBy('created_at', 'desc')->limit(6)->get();
    $unreadCount = $recentNotis->count();
@endphp

<div class="dropdown">
    <a href="#" class="position-relative text-decoration-none dropdown-toggle no-caret" data-bs-toggle="dropdown" aria-expanded="false" style="color: #0072ce;">
        <i class="fa-solid fa-bell" style="font-size: 1.25rem;"></i>
        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 13px !important; padding: 3px 6px !important;">{{ $unreadCount }}</span>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-end shadow border-0" style="width: 360px; max-height: 420px; overflow-y: auto; border-radius: 12px; z-index: 1060;">
        <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center bg-light rounded-top-3">
            <strong style="font-size: 13px !important; color: #003b73;">
                <i class="fa-solid fa-bell me-1 text-primary"></i> Thông Báo Hệ Thống
            </strong>
            <a href="{{ $allRoute }}" class="text-primary text-decoration-none fw-semibold" style="font-size: 13px !important;">
                Tất cả <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
        @forelse($recentNotis as $noti)
            <a href="{{ route('thongbao.show', $noti->MaThongBao) }}" class="dropdown-item px-3 py-2 border-bottom text-wrap text-decoration-none">
                <div class="d-flex gap-2 align-items-start">
                    <i class="fa-solid fa-bullhorn text-primary mt-1" style="font-size: 13px; flex-shrink: 0;"></i>
                    <div class="w-100">
                        <div class="fw-semibold text-dark text-truncate-2" style="font-size: 13px !important; line-height: 1.35;">
                            {{ $noti->TieuDe }}
                        </div>
                        <div class="text-muted mt-1 d-flex align-items-center gap-1" style="font-size: 13px !important;">
                            <i class="fa-regular fa-clock"></i>
                            <span>{{ \Carbon\Carbon::parse($noti->created_at ?? $noti->NgayTao)->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="text-center py-4 text-muted" style="font-size: 13px !important;">
                <i class="fa-regular fa-bell-slash d-block mb-1 fs-5 opacity-50"></i>
                Chưa có thông báo nào.
            </div>
        @endforelse
    </div>
</div>
