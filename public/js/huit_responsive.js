/**
 * HUIT Graduation Thesis Management System - Responsive Navigation & Layout Controller
 * Tự động hỗ trợ tối ưu hiển thị trên Điện thoại (Mobile), Máy tính bảng (Tablet), và Desktop
 */
(function () {
    'use strict';

    function initResponsive() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;

        // 1. Nhúng Style CSS cho Nút Toggle & Nút Đóng để tránh bị ảnh hưởng bởi cache CSS cũ
        const styleId = 'huit-mobile-sidebar-style';
        if (!document.getElementById(styleId)) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = `
                #btnToggleSidebar,
                .btn-sidebar-toggle,
                .btn-sidebar-close {
                    display: none;
                }
                @media (max-width: 991.98px) {
                    #btnToggleSidebar,
                    .btn-sidebar-toggle {
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        width: 36px !important;
                        height: 36px !important;
                        min-width: 36px !important;
                        border-radius: 8px !important;
                        background: #f1f5f9 !important;
                        color: #003366 !important;
                        border: 1px solid #cbd5e1 !important;
                        cursor: pointer !important;
                        font-size: 1.15rem !important;
                        margin-right: 10px !important;
                        padding: 0 !important;
                        line-height: 1 !important;
                        visibility: visible !important;
                        opacity: 1 !important;
                        pointer-events: auto !important;
                        flex-shrink: 0 !important;
                        box-shadow: 0 1px 2px rgba(0,0,0,0.06) !important;
                    }
                    #btnToggleSidebar:hover,
                    .btn-sidebar-toggle:hover,
                    #btnToggleSidebar:active,
                    .btn-sidebar-toggle:active {
                        background: #003366 !important;
                        color: #ffffff !important;
                        border-color: #003366 !important;
                    }
                    .btn-sidebar-close {
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        width: 32px !important;
                        height: 32px !important;
                        min-width: 32px !important;
                        border-radius: 6px !important;
                        background: rgba(255, 255, 255, 0.15) !important;
                        color: #ffffff !important;
                        border: 1px solid rgba(255, 255, 255, 0.3) !important;
                        cursor: pointer !important;
                        font-size: 1.1rem !important;
                        margin-right: 12px !important;
                        visibility: visible !important;
                        opacity: 1 !important;
                        pointer-events: auto !important;
                        line-height: 1 !important;
                        padding: 0 !important;
                    }
                    .btn-sidebar-close:hover,
                    .btn-sidebar-close:active {
                        background: rgba(255, 255, 255, 0.28) !important;
                        color: #ffffff !important;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        // 2. Tạo Backdrop cho Mobile Sidebar nếu chưa tồn tại
        let backdrop = document.getElementById('sidebarBackdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.id = 'sidebarBackdrop';
            backdrop.className = 'sidebar-backdrop';
            document.body.appendChild(backdrop);
        }

        // 3. Tự động tiêm nút Hamburger Toggle nếu layout chưa có
        let toggleBtns = document.querySelectorAll('#btnToggleSidebar, .btn-sidebar-toggle');
        if (toggleBtns.length === 0) {
            const topBar = document.querySelector('.admin-top-bar');
            if (topBar) {
                const firstDiv = topBar.firstElementChild;
                if (firstDiv) {
                    const toggleBtn = document.createElement('button');
                    toggleBtn.type = 'button';
                    toggleBtn.id = 'btnToggleSidebar';
                    toggleBtn.className = 'btn-sidebar-toggle d-lg-none';
                    toggleBtn.setAttribute('aria-label', 'Mở menu');
                    toggleBtn.innerHTML = '<i class="fa-solid fa-bars"></i>';
                    firstDiv.insertBefore(toggleBtn, firstDiv.firstChild);
                }
            }
        }

        // 4. Tự động thêm nút Đóng (X) trong Sidebar Header nếu chưa có
        let closeBtns = sidebar.querySelectorAll('#btnCloseSidebar, .btn-sidebar-close');
        if (closeBtns.length === 0) {
            const sidebarHeader = sidebar.querySelector('.sidebar-header');
            if (sidebarHeader) {
                sidebarHeader.classList.add('d-flex', 'justify-content-between', 'align-items-center');
                const closeBtn = document.createElement('button');
                closeBtn.type = 'button';
                closeBtn.id = 'btnCloseSidebar';
                closeBtn.className = 'btn-sidebar-close d-lg-none';
                closeBtn.setAttribute('aria-label', 'Đóng menu');
                closeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                sidebarHeader.appendChild(closeBtn);
            }
        }

        // 5. Các hàm xử lý Đóng / Mở Sidebar trên Mobile/Tablet
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

        // 6. Gắn sự kiện click cho nút mở/đóng Sidebar
        document.querySelectorAll('#btnToggleSidebar, .btn-sidebar-toggle').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (sidebar.classList.contains('show')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });
        });

        document.querySelectorAll('#btnCloseSidebar, .btn-sidebar-close').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                closeSidebar();
            });
        });

        backdrop.addEventListener('click', function (e) {
            e.preventDefault();
            closeSidebar();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                closeSidebar();
            }
        });

        // 7. Đóng sidebar khi click vào link submenu trên màn hình nhỏ
        sidebar.querySelectorAll('.submenu-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 992) {
                    closeSidebar();
                }
            });
        });

        // 8. Tự động đóng sidebar nếu người dùng xoay ngang hoặc phóng to màn hình desktop (> 992px)
        window.addEventListener('resize', function () {
            if (window.innerWidth > 992 && sidebar.classList.contains('show')) {
                closeSidebar();
            }
        });

        // 9. Tự động bọc table trong table-responsive nếu table chưa có wrapper
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
