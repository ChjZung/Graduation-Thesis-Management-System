@php
    $curUser = Auth::user();
    $role = $curUser?->vaiTro?->TenVaiTro ?? '';

    $allRoute = match($role) {
        'Sinh viên'     => route('sinhvien.thongbao.index'),
        'Giảng viên'    => route('giangvien.thongbao.index'),
        'Trưởng bộ môn' => route('truongbomon.thongbao.index'),
        'Trưởng khoa'   => route('truongkhoa.thongbao.index'),
        default         => route('admin.thongbao.index'),
    };

    $notiQuery = \App\Models\ThongBao::whereIn('TrangThai', ['Đã phát hành', 'ĐÃ GỬI', 'da_gui', 'ACTIVE', 'DaPhatHanh']);
    $curUserMaTK = $curUser?->MaTK ?? '';
    $curUserTen = $curUser?->TenDangNhap ?? '';

    if ($role === 'Sinh viên') {
        $sv = \App\Models\SinhVien::where('MaTK', $curUserMaTK)->orWhere('MaSV', $curUserTen)->first();
        $userNhomCodes = $sv ? \App\Models\ThanhVienNhom::where('MaSV', $sv->MaSV)->where('TrangThai', 'da_tham_gia')->pluck('MaNhom')->toArray() : [];

        $notiQuery->where(function($q) use ($curUserMaTK, $curUserTen, $userNhomCodes) {
            $q->whereIn('DoiTuongNhan', ['Sinh viên', 'Tất cả', 'Toàn thể', 'Toàn trường', 'Toàn thể sinh viên', 'Toàn thể GV và SV'])
              ->orWhereNull('DoiTuongNhan')
              ->orWhere('DoiTuongNhan', '');
            if ($curUserMaTK) {
                $q->orWhere('DoiTuongNhan', $curUserMaTK)->orWhere('DoiTuongNhan', 'like', "%{$curUserMaTK}%");
            }
            if ($curUserTen) {
                $q->orWhere('DoiTuongNhan', $curUserTen)->orWhere('DoiTuongNhan', 'like', "%{$curUserTen}%");
            }
            if (!empty($userNhomCodes)) {
                $q->orWhere('DoiTuongNhan', 'like', '%Tất cả các nhóm%');
                foreach ($userNhomCodes as $code) {
                    $q->orWhere('DoiTuongNhan', 'like', "%{$code}%");
                }
            }
        });
    } elseif (in_array($role, ['Giảng viên', 'Trưởng bộ môn', 'Trưởng khoa'])) {
        $gv = \App\Models\GiangVien::getLoggedInGiangVien($curUser);
        $curMaGV = $gv?->MaGV ?? ($curUser?->MaGV ?? '');
        $notiQuery->where(function($q) use ($curUserMaTK, $curUserTen, $curMaGV) {
            $q->whereIn('DoiTuongNhan', ['Giảng viên', 'Tất cả', 'Toàn thể', 'Toàn trường', 'Toàn thể GV và SV'])
              ->orWhereNull('DoiTuongNhan')
              ->orWhere('DoiTuongNhan', '');
            if ($curUserMaTK) {
                $q->orWhere('DoiTuongNhan', $curUserMaTK)->orWhere('DoiTuongNhan', 'like', "%{$curUserMaTK}%");
            }
            if ($curUserTen) {
                $q->orWhere('DoiTuongNhan', $curUserTen)->orWhere('DoiTuongNhan', 'like', "%{$curUserTen}%");
            }
            if ($curMaGV) {
                $q->orWhere('DoiTuongNhan', $curMaGV)->orWhere('DoiTuongNhan', 'like', "%{$curMaGV}%");
            }
        });
    } elseif (in_array($role, ['Admin', 'Giáo vụ'])) {
        $notiQuery->where(function($q) use ($curUserMaTK, $curUserTen) {
            $q->whereIn('DoiTuongNhan', ['Admin', 'Giáo vụ', 'Toàn thể', 'Tất cả', 'Toàn trường'])
              ->orWhereNull('DoiTuongNhan')
              ->orWhere('DoiTuongNhan', '');
            if ($curUserMaTK) {
                $q->orWhere('DoiTuongNhan', $curUserMaTK);
            }
            if ($curUserTen) {
                $q->orWhere('DoiTuongNhan', $curUserTen);
            }
        })->whereNotIn('LoaiThongBao', ['Nhóm', 'Đề tài']);
    }

    $readIds = \App\Services\ThongBaoService::getReadIds($curUserMaTK);
    $unreadCount = (clone $notiQuery)->whereNotIn('MaThongBao', $readIds)->count();
    $recentNotis = (clone $notiQuery)->orderBy('created_at', 'desc')->limit(6)->get();
@endphp

<div class="dropdown" id="headerNotificationDropdown">
    <a href="#" class="position-relative text-decoration-none dropdown-toggle no-caret d-inline-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false" style="color: #004E64;">
        <i class="fa-solid fa-bell" style="font-size: 1.25rem;"></i>
        @if($unreadCount > 0)
            <span id="headerNotificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                  style="font-size: 11px !important; padding: 2px 6px !important;">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @else
            <span id="headerNotificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" 
                  style="font-size: 11px !important; padding: 2px 6px !important; display: none !important;">
                0
            </span>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-end shadow border border-slate-200" style="width: 380px; max-height: 440px; overflow-y: auto; border-radius: 12px; z-index: 1060;">
        <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center bg-light rounded-top-3">
            <strong style="font-size: 13px !important; color: #004E64;">
                <i class="fa-solid fa-bell me-1 text-primary"></i> Thông Báo Hệ Thống
            </strong>
            <div class="d-flex align-items-center gap-2">
                <button type="button" id="btnMarkAllNotiRead" class="btn btn-link p-0 text-decoration-none text-muted fw-semibold" style="font-size: 12px !important;" title="Đánh dấu tất cả thông báo là đã đọc">
                    <i class="fa-solid fa-check-double text-success me-1"></i>Đọc hết
                </button>
                <span class="text-muted" style="font-size: 11px;">•</span>
                <a href="{{ $allRoute }}" class="text-primary text-decoration-none fw-semibold" style="font-size: 12px !important;">
                    Tất cả <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        <div id="headerNotificationList">
            @forelse($recentNotis as $noti)
                @php
                    $isRead = in_array($noti->MaThongBao, $readIds);
                    $itemShowUrl = match($role) {
                        'Sinh viên'     => route('sinhvien.thongbao.show', $noti->MaThongBao),
                        'Giảng viên'    => route('giangvien.thongbao.show', $noti->MaThongBao),
                        'Trưởng bộ môn' => route('truongbomon.thongbao.show', $noti->MaThongBao),
                        'Trưởng khoa'   => route('truongkhoa.thongbao.show', $noti->MaThongBao),
                        default         => route('admin.thongbao.show', $noti->MaThongBao),
                    };
                @endphp
                <a href="{{ $itemShowUrl }}" 
                   data-noti-id="{{ $noti->MaThongBao }}"
                   data-is-read="{{ $isRead ? '1' : '0' }}"
                   class="dropdown-item px-3 py-2 border-bottom text-wrap text-decoration-none noti-dropdown-item {{ $isRead ? 'opacity-75' : 'bg-light-subtle' }}">
                    <div class="d-flex gap-2 align-items-start">
                        <i class="fa-solid fa-bullhorn {{ $isRead ? 'text-secondary' : 'text-primary' }} mt-1" style="font-size: 13px; flex-shrink: 0;"></i>
                        <div class="w-100">
                            <div class="{{ $isRead ? 'text-secondary' : 'fw-semibold text-dark' }} text-truncate-2 noti-title" style="font-size: 13px !important; line-height: 1.35;">
                                {{ $noti->TieuDe }}
                            </div>
                            <div class="text-muted mt-1 d-flex align-items-center justify-content-between" style="font-size: 11px !important;">
                                <span class="d-flex align-items-center gap-1">
                                    <i class="fa-regular fa-clock"></i>
                                    <span>{{ \Carbon\Carbon::parse($noti->created_at ?? $noti->NgayTao)->diffForHumans() }}</span>
                                </span>
                                @if(!$isRead)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle unread-dot" style="font-size: 9px; padding: 1px 4px;">Mới</span>
                                @endif
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
</div>

<script>
(function() {
    function initHeaderNoti() {
        const btnMarkAll = document.getElementById('btnMarkAllNotiRead');
        const badge = document.getElementById('headerNotificationBadge');

        // 1. Xử lý click từng thông báo chưa đọc
        document.querySelectorAll('.noti-dropdown-item').forEach(function(item) {
            if (item.dataset.boundClick) return;
            item.dataset.boundClick = 'true';

            item.addEventListener('click', function(e) {
                const notiId = this.dataset.notiId;
                const isRead = this.dataset.isRead === '1';

                if (!isRead && notiId) {
                    this.dataset.isRead = '1';
                    this.classList.add('opacity-75');
                    this.classList.remove('bg-light-subtle');
                    const dot = this.querySelector('.unread-dot');
                    if (dot) dot.remove();

                    const title = this.querySelector('.noti-title');
                    if (title) {
                        title.classList.remove('text-dark', 'fw-semibold');
                        title.classList.add('text-secondary');
                    }

                    // Giảm badge
                    if (badge) {
                        let currentCount = parseInt(badge.textContent.trim(), 10) || 0;
                        let nextCount = Math.max(0, currentCount - 1);
                        badge.textContent = nextCount > 99 ? '99+' : nextCount;
                        if (nextCount <= 0) {
                            badge.style.setProperty('display', 'none', 'important');
                            badge.classList.add('d-none');
                        }
                    }

                    // Gửi request đánh dấu đọc
                    const markUrl = '{{ url("/thongbao/mark-read") }}/' + encodeURIComponent(notiId);
                    if (navigator.sendBeacon) {
                        const formData = new FormData();
                        formData.append('_token', '{{ csrf_token() }}');
                        navigator.sendBeacon(markUrl, formData);
                    } else {
                        fetch(markUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        }).catch(function() {});
                    }
                }
            });
        });

        // 2. Xử lý nút "Đọc hết"
        if (btnMarkAll && !btnMarkAll.dataset.bound) {
            btnMarkAll.dataset.bound = 'true';

            btnMarkAll.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Ẩn badge ngay lập tức trên giao diện
                if (badge) {
                    badge.style.setProperty('display', 'none', 'important');
                    badge.classList.add('d-none');
                    badge.textContent = '0';
                }

                // Chuyển đổi trạng thái hiển thị của các thông báo trong dropdown
                document.querySelectorAll('.unread-dot').forEach(function(el) {
                    el.remove();
                });
                document.querySelectorAll('.noti-dropdown-item').forEach(function(el) {
                    el.dataset.isRead = '1';
                    el.classList.add('opacity-75');
                    el.classList.remove('bg-light-subtle');
                    const title = el.querySelector('.noti-title');
                    if (title) {
                        title.classList.remove('text-dark', 'fw-semibold');
                        title.classList.add('text-secondary');
                    }
                });

                // Phản hồi nút bấm
                btnMarkAll.innerHTML = '<i class="fa-solid fa-check text-success me-1"></i>Đã đọc';
                btnMarkAll.style.pointerEvents = 'none';

                // Gửi request đồng bộ session & cache server-side
                fetch('{{ route("thongbao.markAllReadGlobal") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).catch(function(err) {
                    console.warn('Lỗi lưu trạng thái đã đọc thông báo:', err);
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeaderNoti);
    } else {
        initHeaderNoti();
    }
})();
</script>
