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
        $curMaSV = $sv?->MaSV ?? '';

        $notiQuery->where(function($q) use ($curUserMaTK, $curUserTen, $curMaSV, $userNhomCodes) {
            $q->whereIn('DoiTuongNhan', ['Sinh viên', 'Tất cả', 'Toàn thể', 'Toàn trường', 'Toàn thể sinh viên', 'Toàn thể GV và SV', ''])
              ->orWhereNull('DoiTuongNhan')
              ->orWhere('MaThongBao', 'like', 'TB_KH_%')
              ->orWhere('MaThongBao', 'like', 'TB_DK_%');
            if ($curUserMaTK) {
                $q->orWhere('DoiTuongNhan', $curUserMaTK)->orWhere('DoiTuongNhan', 'like', "%{$curUserMaTK}%");
            }
            if ($curUserTen) {
                $q->orWhere('DoiTuongNhan', $curUserTen)->orWhere('DoiTuongNhan', 'like', "%{$curUserTen}%");
            }
            if ($curMaSV) {
                $q->orWhere('DoiTuongNhan', $curMaSV)->orWhere('DoiTuongNhan', 'like', "%{$curMaSV}%");
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
            $q->whereIn('DoiTuongNhan', ['Giảng viên', 'Tất cả', 'Toàn thể', 'Toàn trường', 'Toàn thể GV và SV', ''])
              ->orWhereNull('DoiTuongNhan')
              ->orWhere('MaThongBao', 'like', 'TB_KH_%')
              ->orWhere('MaThongBao', 'like', 'TB_DK_%');
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
        $notiQuery->whereNotIn('LoaiThongBao', ['Nhóm', 'Đề tài'])
                  ->where(function($q) use ($curUserMaTK, $curUserTen) {
                      $q->whereIn('DoiTuongNhan', ['Tất cả', 'Toàn thể', 'Toàn trường', 'Toàn thể sinh viên', 'Toàn thể GV và SV', 'Sinh viên', 'Giảng viên', 'Admin', 'Giáo vụ'])
                        ->orWhereNull('DoiTuongNhan')
                        ->orWhere('DoiTuongNhan', '')
                        ->orWhere('MaThongBao', 'like', 'TB_KH_%')
                        ->orWhere('MaThongBao', 'like', 'TB_DK_%');
                      if ($curUserMaTK) {
                          $q->orWhere('DoiTuongNhan', $curUserMaTK);
                      }
                      if ($curUserTen) {
                          $q->orWhere('DoiTuongNhan', $curUserTen);
                      }
                  });
    }

    $readIds = \App\Services\ThongBaoService::getReadIds($curUserMaTK);
    $unreadCount = (clone $notiQuery)->whereNotIn('MaThongBao', $readIds)->count();
    $recentNotis = (clone $notiQuery)->orderBy('created_at', 'desc')->limit(8)->get();
@endphp

<style>
    /* Nút kích hoạt chuông thông báo */
    .header-bell-btn {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        text-decoration: none !important;
    }
    .header-bell-btn:hover,
    .header-bell-btn:focus,
    .show > .header-bell-btn {
        background-color: #eff6ff;
        border-color: #bfdbfe;
        color: #0072ce;
        box-shadow: 0 2px 8px rgba(0, 114, 206, 0.15);
    }
    .header-bell-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        min-width: 19px;
        height: 19px;
        padding: 0 5px;
        background-color: #ef4444 !important;
        color: #ffffff !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        border-radius: 9999px !important;
        border: 2px solid #ffffff !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        box-shadow: 0 2px 5px rgba(239, 68, 68, 0.45);
        z-index: 10;
        pointer-events: none;
    }
    .header-bell-ping {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 19px;
        height: 19px;
        border-radius: 9999px;
        background-color: #ef4444;
        opacity: 0.75;
        animation: bell-ping 1.8s cubic-bezier(0, 0, 0.2, 1) infinite;
        pointer-events: none;
        z-index: 9;
    }
    @keyframes bell-ping {
        75%, 100% {
            transform: scale(2);
            opacity: 0;
        }
    }

    /* Menu Dropdown Thông Báo */
    .noti-dropdown-menu {
        width: 390px;
        max-width: calc(100vw - 2rem);
        border-radius: 14px;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.16), 0 4px 12px rgba(15, 23, 42, 0.08) !important;
        padding: 0;
        overflow: hidden;
        z-index: var(--z-dropdown, 1060) !important;
        background: #ffffff !important;
    }
    .noti-dropdown-header {
        padding: 12px 16px 8px 16px;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
    }
    .noti-tab-pill {
        font-size: 12px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 20px;
        border: none;
        background: transparent;
        color: #64748b;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .noti-tab-pill:hover {
        color: #0f172a;
        background: #f1f5f9;
    }
    .noti-tab-pill.active {
        background: #e0f2fe;
        color: #0072ce;
    }

    /* Item danh sách thông báo */
    .noti-dropdown-list {
        max-height: 380px;
        overflow-y: auto;
        overscroll-behavior: contain;
    }
    .noti-dropdown-list::-webkit-scrollbar {
        width: 5px;
    }
    .noti-dropdown-list::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .noti-item-link {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 16px;
        text-decoration: none;
        color: inherit;
        border-bottom: 1px solid #f8fafc;
        transition: background-color 0.15s ease;
        position: relative;
    }
    .noti-item-link:hover {
        background-color: #f8fafc;
    }
    .noti-item-link.unread {
        background-color: #f0f7ff;
    }
    .noti-item-link.unread:hover {
        background-color: #e6f2ff;
    }
    .noti-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 14px;
    }
    .noti-title-text {
        font-size: 13px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 3px;
    }
    .noti-desc-text {
        font-size: 11.5px;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 270px;
    }
    .noti-meta-text {
        font-size: 11px;
        color: #94a3b8;
    }
    .noti-unread-dot {
        width: 7px;
        height: 7px;
        background-color: #0072ce;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 6px;
    }
    .noti-dropdown-footer {
        padding: 10px 16px;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        text-align: center;
    }
    .btn-view-all-noti {
        font-size: 12.5px;
        font-weight: 600;
        color: #0072ce;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 6px 16px;
        border-radius: 20px;
        transition: all 0.15s ease;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        width: 100%;
    }
    .btn-view-all-noti:hover {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #00569c;
    }
</style>

<div class="dropdown" id="headerNotificationDropdown">
    <!-- Nút Chuông Kích Hoạt -->
    <a href="#" class="header-bell-btn dropdown-toggle no-caret d-inline-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false" id="headerBellBtn" title="Thông báo hệ thống">
        <i class="fa-solid fa-bell me-2" style="font-size: 1.15rem;"></i>
        <span class="header-bell-ping {{ $unreadCount > 0 ? '' : 'd-none' }}" id="headerBellPing"></span>
        <span class="header-bell-badge {{ $unreadCount > 0 ? '' : 'd-none' }}" id="headerNotificationBadge">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
    </a>

    <!-- Dropdown Menu Nội Dung -->
    <div class="dropdown-menu dropdown-menu-end noti-dropdown-menu" aria-labelledby="headerBellBtn">
        <!-- 1. Dropdown Header -->
        <div class="noti-dropdown-header">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 14.5px;">
                        Thông Báo
                    </h6>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small" id="headerNotiUnreadBadge" style="font-size: 11px; font-weight: 700; padding: 2px 8px; {{ $unreadCount <= 0 ? 'display: none;' : '' }}">
                        {{ $unreadCount }} mới
                    </span>
                </div>
                <div>
                    <button type="button" id="btnMarkAllNotiRead" class="btn btn-link p-0 text-decoration-none text-muted d-inline-flex align-items-center" style="font-size: 11.5px; font-weight: 600;" title="Đánh dấu tất cả là đã đọc">
                        <i class="fa-solid fa-check-double text-success me-2"></i>Đọc tất cả
                    </button>
                </div>
            </div>

            <!-- Tab Chuyển đổi nhanh Tất cả / Chưa đọc -->
            <div class="d-flex gap-1">
                <button type="button" class="noti-tab-pill active" id="tabBtnAll" onclick="switchNotiTab('all')">
                    Tất cả
                </button>
                <button type="button" class="noti-tab-pill" id="tabBtnUnread" onclick="switchNotiTab('unread')">
                    Chưa đọc (<span id="unreadTabCount" class="text-danger fw-bold">{{ $unreadCount }}</span>)
                </button>
            </div>
        </div>

        <!-- 2. Danh Sách Thông Báo -->
        <div class="noti-dropdown-list" id="headerNotificationList">
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

                    // Cấu hình Icon và màu sắc theo loại thông báo
                    $loai = $noti->LoaiThongBao ?? '';
                    $iconData = match($loai) {
                        'Kế hoạch'  => ['icon' => 'fa-calendar-check', 'bg' => '#dbeafe', 'color' => '#1d4ed8'],
                        'Báo cáo'   => ['icon' => 'fa-clipboard-check', 'bg' => '#ccfbf1', 'color' => '#0f766e'],
                        'Hội đồng'  => ['icon' => 'fa-user-tie', 'bg' => '#f3e8ff', 'color' => '#7e22ce'],
                        'Hệ thống'  => ['icon' => 'fa-shield-halved', 'bg' => '#ffedd5', 'color' => '#c2410c'],
                        default     => ['icon' => 'fa-bullhorn', 'bg' => '#e0f2fe', 'color' => '#0284c7'],
                    };

                    $shortDesc = trim(preg_replace('/\s+/', ' ', strip_tags($noti->NoiDung ?? '')));
                @endphp
                <a href="{{ $itemShowUrl }}" 
                   data-noti-id="{{ $noti->MaThongBao }}"
                   data-is-read="{{ $isRead ? '1' : '0' }}"
                   class="noti-item-link noti-dropdown-item {{ $isRead ? 'read' : 'unread' }}">
                    
                    <!-- Icon Box đại diện cho loại thông báo -->
                    <div class="noti-icon-box" style="background-color: {{ $iconData['bg'] }}; color: {{ $iconData['color'] }};">
                        <i class="fa-solid {{ $iconData['icon'] }}"></i>
                    </div>

                    <!-- Nội dung thông báo -->
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-1" style="font-size: 10px;">
                                {{ $loai ?: 'Thông báo' }}
                            </span>
                            <span class="noti-meta-text">
                                <i class="fa-regular fa-clock me-2"></i>{{ \Carbon\Carbon::parse($noti->created_at ?? $noti->NgayTao)->diffForHumans() }}
                            </span>
                        </div>

                        <div class="noti-title-text {{ $isRead ? 'text-secondary fw-normal' : 'text-dark fw-bold' }} noti-title">
                            {{ $noti->TieuDe }}
                        </div>

                        @if(!empty($shortDesc))
                            <div class="noti-desc-text">
                                {{ Str::limit($shortDesc, 60, '...') }}
                            </div>
                        @endif
                    </div>

                    <!-- Dấu chấm chỉ báo chưa đọc -->
                    @if(!$isRead)
                        <span class="noti-unread-dot" title="Chưa đọc"></span>
                    @endif
                </a>
            @empty
                <div class="text-center py-5 text-muted" id="notiEmptyState">
                    <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                        <i class="fa-regular fa-bell-slash fs-5 opacity-50"></i>
                    </div>
                    <div class="fw-semibold text-dark small">Không có thông báo nào</div>
                    <div class="text-muted" style="font-size: 11px;">Bạn đã cập nhật tất cả thông tin mới nhất.</div>
                </div>
            @endforelse

            <!-- Placeholder khi lọc tab Chưa đọc mà không có tin nào -->
            <div class="text-center py-5 text-muted d-none" id="notiUnreadEmptyState">
                <div class="rounded-circle bg-light text-success d-inline-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                    <i class="fa-solid fa-check-double fs-5"></i>
                </div>
                <div class="fw-semibold text-dark small">Đã đọc hết thông báo!</div>
                <div class="text-muted" style="font-size: 11px;">Tuyệt vời, bạn không có thông báo nào chưa xem.</div>
            </div>
        </div>

        <!-- 3. Dropdown Footer -->
        <div class="noti-dropdown-footer">
            <a href="{{ $allRoute }}" class="btn-view-all-noti d-inline-flex align-items-center">
                <span>Xem tất cả thông báo</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<script>
(function() {
    // 1. Chuyển đổi tab Tất cả / Chưa đọc
    window.switchNotiTab = function(type) {
        const tabAll = document.getElementById('tabBtnAll');
        const tabUnread = document.getElementById('tabBtnUnread');
        const items = document.querySelectorAll('.noti-dropdown-item');
        const emptyAll = document.getElementById('notiEmptyState');
        const emptyUnread = document.getElementById('notiUnreadEmptyState');

        if (type === 'unread') {
            tabAll?.classList.remove('active');
            tabUnread?.classList.add('active');

            let unreadCount = 0;
            items.forEach(function(item) {
                if (item.dataset.isRead === '0') {
                    item.classList.remove('d-none');
                    unreadCount++;
                } else {
                    item.classList.add('d-none');
                }
            });

            if (emptyAll) emptyAll.classList.add('d-none');
            if (emptyUnread) {
                if (unreadCount === 0) {
                    emptyUnread.classList.remove('d-none');
                } else {
                    emptyUnread.classList.add('d-none');
                }
            }
        } else {
            tabAll?.classList.add('active');
            tabUnread?.classList.remove('active');

            items.forEach(function(item) {
                item.classList.remove('d-none');
            });

            if (emptyUnread) emptyUnread.classList.add('d-none');
            if (emptyAll && items.length === 0) {
                emptyAll.classList.remove('d-none');
            }
        }
    };

    function initHeaderNoti() {
        const btnMarkAll = document.getElementById('btnMarkAllNotiRead');
        const badge = document.getElementById('headerNotificationBadge');
        const ping = document.getElementById('headerBellPing');
        const unreadTopBadge = document.getElementById('headerNotiUnreadBadge');
        const unreadTabCount = document.getElementById('unreadTabCount');

        // Cập nhật số đếm chưa đọc trên UI
        function updateBadgeCount(count) {
            if (count > 0) {
                const text = count > 99 ? '99+' : count;
                if (badge) {
                    badge.textContent = text;
                    badge.classList.remove('d-none');
                    badge.style.display = 'inline-flex';
                }
                if (ping) {
                    ping.classList.remove('d-none');
                    ping.style.display = 'block';
                }
                if (unreadTopBadge) {
                    unreadTopBadge.textContent = count + ' mới';
                    unreadTopBadge.style.display = 'inline-block';
                }
                if (unreadTabCount) unreadTabCount.textContent = count;
            } else {
                if (badge) {
                    badge.textContent = '0';
                    badge.classList.add('d-none');
                    badge.style.display = 'none';
                }
                if (ping) {
                    ping.classList.add('d-none');
                    ping.style.display = 'none';
                }
                if (unreadTopBadge) unreadTopBadge.style.display = 'none';
                if (unreadTabCount) unreadTabCount.textContent = '0';
            }
        }

        // 2. Click từng thông báo -> Đánh dấu đọc ngay
        document.querySelectorAll('.noti-dropdown-item').forEach(function(item) {
            if (item.dataset.boundClick) return;
            item.dataset.boundClick = 'true';

            item.addEventListener('click', function() {
                const notiId = this.dataset.notiId;
                const isRead = this.dataset.isRead === '1';

                if (!isRead && notiId) {
                    this.dataset.isRead = '1';
                    this.classList.remove('unread');
                    this.classList.add('read');

                    const dot = this.querySelector('.noti-unread-dot');
                    if (dot) dot.remove();

                    const title = this.querySelector('.noti-title');
                    if (title) {
                        title.classList.remove('text-dark', 'fw-bold');
                        title.classList.add('text-secondary', 'fw-normal');
                    }

                    // Giảm số đếm
                    let cur = parseInt(unreadTabCount?.textContent || '0', 10) || 0;
                    let next = Math.max(0, cur - 1);
                    updateBadgeCount(next);

                    // Gửi request ngầm lưu trạng thái đọc
                    const markUrl = '{{ url("/thongbao/mark-read") }}/' + encodeURIComponent(notiId);
                    if (navigator.sendBeacon) {
                        const fd = new FormData();
                        fd.append('_token', '{{ csrf_token() }}');
                        navigator.sendBeacon(markUrl, fd);
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

        // 3. Xử lý nút "Đọc tất cả"
        if (btnMarkAll && !btnMarkAll.dataset.bound) {
            btnMarkAll.dataset.bound = 'true';

            btnMarkAll.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                updateBadgeCount(0);

                // Chuyển tất cả item sang trạng thái read
                document.querySelectorAll('.noti-unread-dot').forEach(el => el.remove());
                document.querySelectorAll('.noti-dropdown-item').forEach(function(el) {
                    el.dataset.isRead = '1';
                    el.classList.remove('unread');
                    el.classList.add('read');
                    const title = el.querySelector('.noti-title');
                    if (title) {
                        title.classList.remove('text-dark', 'fw-bold');
                        title.classList.add('text-secondary', 'fw-normal');
                    }
                });

                btnMarkAll.innerHTML = '<i class="fa-solid fa-check text-success me-2"></i>Đã đọc';
                btnMarkAll.style.pointerEvents = 'none';

                // Đồng bộ cập nhật ngay trên trang danh sách nếu người dùng đang ở trang thông báo
                document.querySelectorAll('.unread-indicator-dot').forEach(el => el.remove());
                document.querySelectorAll('tr.bg-light-subtle').forEach(tr => tr.classList.remove('bg-light-subtle'));
                document.querySelectorAll('button[onclick*="markSingleRead"]').forEach(btn => btn.remove());

                // Nếu đang ở tab unread, hiển thị ngay unread empty state
                const tabUnread = document.getElementById('tabBtnUnread');
                if (tabUnread && tabUnread.classList.contains('active')) {
                    switchNotiTab('unread');
                }

                fetch('{{ route("thongbao.markAllReadGlobal") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).catch(function(err) {
                    console.warn('Lỗi lưu trạng thái đọc tất cả:', err);
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
