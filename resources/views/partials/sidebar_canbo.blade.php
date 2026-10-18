{{-- 
    Component Sidebar Dùng Chung Cho Cán Bộ Quản Lý (Trưởng Bộ Môn & Trưởng Khoa)
    Tuân thủ Chuẩn G: Không box-shadow, font tối thiểu 13px, căn trái, không icon trang trí thừa ở cấp 2
--}}
<nav id="sidebar">
    <div class="sidebar-header">
        <a href="{{ $dashboardRoute }}" class="sidebar-brand text-decoration-none text-white d-flex align-items-center">
            <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT" class="sidebar-logo">
            <div>
                <div class="sidebar-title">ĐH Công Thương<br>TP. Hồ Chí Minh</div>
                <span class="sidebar-subtitle">{{ $roleSubtitle }}</span>
            </div>
        </a>
    </div>

    <div class="px-3 pb-2">
        <div class="role-badge">
            <i class="{{ $roleBadgeIcon }} me-2"></i>
            <span>{{ $roleBadgeText }}</span>
        </div>
    </div>

    <div class="px-2 py-2">
        @foreach($menuGroups as $group)
            <div class="sidebar-menu-group">
                <button class="menu-parent-btn text-start {{ !empty($group['active']) ? 'active-parent' : 'collapsed' }}"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#{{ $group['id'] }}"
                        aria-expanded="{{ !empty($group['active']) ? 'true' : 'false' }}">
                    <span class="parent-icon-title text-start">
                        <i class="{{ $group['icon'] }} me-2"></i>
                        <span>{{ $group['title'] }}</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ !empty($group['active']) ? 'show' : '' }}" id="{{ $group['id'] }}">
                    @foreach($group['items'] as $item)
                        <a href="{{ $item['url'] }}" class="submenu-link text-start {{ !empty($item['active']) ? 'active-sub' : '' }}">
                            <span>{{ $item['title'] }}</span>
                            @if(isset($item['badge_count']) && $item['badge_count'] > 0)
                                <span class="badge {{ $item['badge_class'] ?? 'bg-danger' }} rounded-pill ms-auto">{{ $item['badge_count'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</nav>
