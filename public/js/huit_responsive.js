/**
 * HUIT Graduation Thesis Management System - Responsive Navigation & Layout Controller
 * Tự động hỗ trợ tối ưu hiển thị trên Điện thoại (Mobile), Máy tính bảng (Tablet), và Desktop
 */
(function () {
    'use strict';

    function initResponsive() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;

        // 1. Tạo Backdrop cho Mobile Sidebar nếu chưa tồn tại
        let backdrop = document.getElementById('sidebarBackdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.id = 'sidebarBackdrop';
            backdrop.className = 'sidebar-backdrop';
            document.body.appendChild(backdrop);
        }

        // 2. Tìm hoặc tự động chèn nút Hamburger Toggle trên Topbar
        let toggleBtn = document.getElementById('btnToggleSidebar');
        if (!toggleBtn) {
            const topBar = document.querySelector('.admin-top-bar');
            if (topBar) {
                const breadcrumb = topBar.querySelector('.admin-breadcrumb');
                toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                toggleBtn.id = 'btnToggleSidebar';
                toggleBtn.className = 'btn-sidebar-toggle d-lg-none me-2 p-1 text-primary border-0 bg-transparent flex-shrink-0';
                toggleBtn.setAttribute('aria-label', 'Menu');
                toggleBtn.setAttribute('title', 'Mở danh mục điều hướng');
                toggleBtn.innerHTML = '<i class="fa-solid fa-bars fs-5"></i>';

                if (breadcrumb && breadcrumb.parentNode) {
                    breadcrumb.parentNode.insertBefore(toggleBtn, breadcrumb);
                } else {
                    topBar.prepend(toggleBtn);
                }
            }
        }

        // 3. Xử lý đóng / mở Sidebar trên Mobile/Tablet
        function openSidebar() {
            sidebar.classList.add('show');
            backdrop.classList.add('show');
            document.body.classList.add('sidebar-mobile-open');
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
            document.body.classList.remove('sidebar-mobile-open');
        }

        if (toggleBtn) {
            toggleBtn.onclick = function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (sidebar.classList.contains('show')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            };
        }

        backdrop.onclick = closeSidebar;

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                closeSidebar();
            }
        });

        // 4. Đóng sidebar khi click vào link submenu trên màn hình nhỏ
        sidebar.querySelectorAll('.submenu-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 992) {
                    closeSidebar();
                }
            });
        });

        // 5. Tự động đóng sidebar nếu người dùng xoay ngang hoặc phóng to màn hình desktop (> 992px)
        window.addEventListener('resize', function () {
            if (window.innerWidth > 992 && sidebar.classList.contains('show')) {
                closeSidebar();
            }
        });

        // 6. Tự động bọc table trong table-responsive nếu table chưa có wrapper
        document.querySelectorAll('table.table').forEach(function (table) {
            if (!table.parentElement.classList.contains('table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initResponsive);
    } else {
        initResponsive();
    }
})();
