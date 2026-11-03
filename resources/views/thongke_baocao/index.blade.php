@extends($scope['layout'])

@section('page_title', 'Thống Kê Và Xuất Báo Cáo — ' . $scope['roleTitle'])

@section('content')
<style>
/* ═══ BỘ LỌC VÀ THẺ GIAO DIỆN CHUẨN ĐỒNG BỘ ═══ */
.admin-filter-card {
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}
.admin-filter-card .form-select-sm,
.admin-filter-card .form-control-sm {
    font-size: 0.82rem;
    height: 38px;
    border-color: #cbd5e1;
    border-radius: 8px;
}
.admin-filter-card .form-select-sm:focus,
.admin-filter-card .form-control-sm:focus {
    border-color: #0072ce;
    box-shadow: 0 0 0 0.2rem rgba(0, 114, 206, 0.15);
}

/* ═══ THANH TIẾN TRÌNH QUY TRÌNH 5 BƯỚC (WORKFLOW STEPPER) ═══ */
.stepper-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 18px;
    margin-bottom: 22px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.02);
    overflow-x: auto;
}
.step-item {
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    text-decoration: none;
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 600;
    transition: all 0.2s ease;
}
.step-item .step-num {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background-color: #f1f5f9;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
}
.step-item.active {
    color: #0072ce;
}
.step-item.active .step-num {
    background-color: #0072ce;
    color: #ffffff;
}
.step-divider {
    flex-grow: 1;
    height: 2px;
    background-color: #e2e8f0;
    min-width: 20px;
}

/* Template Selector Cards */
.template-card {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1.5px solid #e2e8f0 !important;
    background-color: #ffffff;
    user-select: none;
}
.template-card:hover {
    transform: translateY(-2px);
    border-color: #93c5fd !important;
    box-shadow: 0 6px 14px rgba(0, 114, 206, 0.08) !important;
}
.template-card.active-template {
    border-color: #0072ce !important;
    background-color: #f0f7ff !important;
    box-shadow: 0 4px 12px rgba(0, 114, 206, 0.14) !important;
}
.template-card.active-template .template-title {
    color: #00529b !important;
}

/* KPI Summary Cards */
.kpi-card {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08) !important;
}

/* Tiện ích clamp & text */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.35;
}
.cursor-pointer {
    cursor: pointer;
}

/* ═══ MÔ PHỎNG VĂN BẢN IN A4 (LIVE PREVIEW) ═══ */
.a4-preview-paper {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    font-family: 'DejaVu Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #1e293b;
    line-height: 1.45;
}
.a4-landscape {
    max-width: 1050px;
    min-height: 600px;
}
.a4-portrait {
    max-width: 800px;
    min-height: 750px;
}
@media print {
    @page {
        margin: 8mm 8mm 10mm 8mm;
        size: auto;
    }
    html, body {
        width: 100% !important;
        height: auto !important;
        overflow: visible !important;
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    body * {
        visibility: hidden !important;
    }
    #modalPreviewReport,
    #modalPreviewReport .modal-dialog,
    #modalPreviewReport .modal-content,
    #modalPreviewReport .modal-body,
    .a4-preview-paper,
    .a4-preview-paper * {
        visibility: visible !important;
    }
    #modalPreviewReport {
        position: static !important;
        display: block !important;
        overflow: visible !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
    }
    #modalPreviewReport .modal-dialog {
        position: static !important;
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        transform: none !important;
    }
    #modalPreviewReport .modal-content {
        position: static !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        background: transparent !important;
    }
    #modalPreviewReport .modal-header,
    #modalPreviewReport .modal-footer,
    .modal-backdrop {
        display: none !important;
    }
    #modalPreviewReport .modal-body {
        position: static !important;
        overflow: visible !important;
        padding: 0 !important;
        margin: 0 !important;
        max-height: none !important;
    }
    .a4-preview-paper {
        position: static !important;
        width: 100% !important;
        max-width: 100% !important;
        min-height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border: none !important;
        background: #ffffff !important;
    }
    .table {
        width: 100% !important;
        table-layout: fixed !important;
        page-break-inside: auto !important;
        word-wrap: break-word !important;
    }
    tr {
        page-break-inside: avoid !important;
        page-break-after: auto !important;
    }
    thead {
        display: table-header-group !important;
    }
    tfoot {
        display: table-footer-group !important;
    }
}
</style>

<div class="container-fluid px-2 px-md-4" style="padding-bottom: 70px;">
    <!-- ═══ HEADER TRANG ═══ -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1" style="color: #00529b; letter-spacing: 0.3px;">
                    <i class="fa-solid fa-file-waveform text-primary me-2"></i>
                    THỐNG KÊ VÀ XUẤT BÁO CÁO
                </h4>
                <div class="text-muted small">
                    Lựa chọn tiêu chí thống kê và mẫu báo cáo để tổng hợp dữ liệu, xem trước và xuất báo cáo PDF.
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-bold">
                    <i class="fa-solid fa-user-tie me-1"></i> {{ $scope['roleTitle'] }}
                </span>
            </div>
        </div>
    </div>

    <!-- ═══ THANH TIẾN TRÌNH QUY TRÌNH 5 BƯỚC ═══ -->
    <div class="stepper-wrap">
        <a href="#sectionFilterBar" class="step-item active">
            <span class="step-num">1</span>
            <span>Chọn bộ lọc</span>
        </a>
        <div class="step-divider"></div>
        <a href="#sectionTemplateSelector" class="step-item active">
            <span class="step-num">2</span>
            <span>Chọn mẫu báo cáo</span>
        </a>
        <div class="step-divider"></div>
        <a href="javascript:void(0)" class="step-item active" onclick="document.getElementById('btnOpenPreviewModal').click();">
            <span class="step-num">3</span>
            <span>Xem trước nội dung</span>
        </a>
        <div class="step-divider"></div>
        <a href="javascript:void(0)" class="step-item" onclick="document.getElementById('btnOpenPreviewModal').click(); setTimeout(() => document.getElementById('tab-preview-settings-btn').click(), 200);">
            <span class="step-num">4</span>
            <span>Tùy chỉnh nội dung</span>
        </a>
        <div class="step-divider"></div>
        <a href="javascript:void(0)" class="step-item text-danger" onclick="document.getElementById('btnDirectExportPdf').click();">
            <span class="step-num bg-danger-subtle text-danger"><i class="fa-solid fa-file-pdf"></i></span>
            <span class="text-danger fw-bold">Xuất PDF</span>
        </a>
    </div>

    <!-- ═══ 1. BỘ LỌC ĐA TIÊU CHÍ (CASCADING FILTER) ═══ -->
    @include('thongke_baocao.partials.filter_bar')

    <!-- ═══ 2. BỘ CHỌN BIỂU MẪU BÁO CÁO (10 MẪU CHUẨN) ═══ -->
    @include('thongke_baocao.partials.template_selector')

    <!-- ═══ 3. 10 THẺ CHỈ SỐ KPI TỔNG HỢP ═══ -->
    @include('thongke_baocao.partials.kpi_cards')

    <!-- ═══ 4. 6 BIỂU ĐỒ PHÂN TÍCH TRỰC QUAN (CHART.JS) ═══ -->
    @include('thongke_baocao.partials.charts')

    <!-- ═══ 5. BẢNG DỮ LIỆU CHI TIẾT THEO MẪU BÁO CÁO ═══ -->
    @include('thongke_baocao.partials.data_table')
</div>

<!-- ═══ 6. MODAL XEM TRƯỚC (LIVE PREVIEW) & TÙY CHỈNH BÁO CÁO ═══ -->
@include('thongke_baocao.partials.modal_preview')

@push('scripts')
<!-- Load Chart.js 4.4.0 -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ═════════════════════════════════════════════════════════════════
    // 1. CÁC BIẾN & ENDPOINTS HỆ THỐNG
    // ═════════════════════════════════════════════════════════════════
    const currentRouteUrl = "{{ route($scope['routePrefix'] . '.index') }}";
    const previewReportUrl = "{{ route($scope['routePrefix'] . '.preview_report') }}";
    const exportPdfUrl = "{{ route($scope['routePrefix'] . '.export_pdf') }}";

    const filterForm = document.getElementById('filterForm');
    const filterMauBaoCao = document.getElementById('filterMauBaoCao');
    const filterNamHoc = document.getElementById('filterNamHoc');
    const filterHocKy = document.getElementById('filterHocKy');
    const filterKhoa = document.getElementById('filterKhoa');
    const filterBoMon = document.getElementById('filterBoMon');
    const filterNganh = document.getElementById('filterNganh');
    const filterHocPhan = document.getElementById('filterHocPhan');
    const filterHoiDong = document.getElementById('filterHoiDong');
    const filterGiangVien = document.getElementById('filterGiangVien');
    const filterDotBaoVe = document.getElementById('filterDotBaoVe');
    const filterKetQua = document.getElementById('filterKetQua');
    const filterHinhThuc = document.getElementById('filterHinhThuc');
    const filterSearch = document.getElementById('filterSearch');
    const btnClearSearch = document.getElementById('btnClearSearch');

    const btnFilterSubmit = document.getElementById('btnFilterSubmit');
    const btnResetFilter = document.getElementById('btnResetFilter');
    const btnDirectExportPdf = document.getElementById('btnDirectExportPdf');
    const btnOpenPreviewModal = document.getElementById('btnOpenPreviewModal');
    const filterLoadingIndicator = document.getElementById('filterLoadingIndicator');

    const templateCards = document.querySelectorAll('.template-card');
    const badgeActiveTemplate = document.getElementById('badgeActiveTemplate');
    const modalHeaderMauTitle = document.getElementById('modalHeaderMauTitle');

    // Modal elements
    const modalPreviewReportEl = document.getElementById('modalPreviewReport');
    const previewDocLoading = document.getElementById('previewDocLoading');
    const previewDocContainer = document.getElementById('previewDocContainer');
    const btnApplyPreviewSettings = document.getElementById('btnApplyPreviewSettings');
    const btnModalPrintDoc = document.getElementById('btnModalPrintDoc');
    const btnModalExportPdf = document.getElementById('btnModalExportPdf');
    const modalExportPdfIcon = document.getElementById('modalExportPdfIcon');
    const modalExportPdfSpinner = document.getElementById('modalExportPdfSpinner');
    const modalExportPdfText = document.getElementById('modalExportPdfText');

    // Dữ liệu biểu đồ ban đầu được inject từ server
    let chartsPayload = {!! json_encode($data['charts']) !!};
    let chartInstances = {
        tyLeDat: null,
        svTheoNganh: null,
        diemTheoNganh: null,
        theoHoiDong: null,
        phanBoDiem: null,
        tienDoChamDiem: null
    };

    // ═════════════════════════════════════════════════════════════════
    // 2. KHỞI TẠO VÀ CẬP NHẬT 6 BIỂU ĐỒ (CHART.JS)
    // ═════════════════════════════════════════════════════════════════
    function initOrUpdateCharts(payload) {
        if (!payload) return;

        // Biểu đồ 1: Tỷ Lệ Đạt & Chưa Đạt (Doughnut)
        const ctxTyLe = document.getElementById('chartTyLeDatCanvas');
        if (ctxTyLe && payload.chartTyLeDat) {
            if (chartInstances.tyLeDat) chartInstances.tyLeDat.destroy();
            chartInstances.tyLeDat = new Chart(ctxTyLe, {
                type: 'doughnut',
                data: {
                    labels: payload.chartTyLeDat.labels,
                    datasets: [{
                        data: payload.chartTyLeDat.data,
                        backgroundColor: payload.chartTyLeDat.colors || ['#10b981', '#ef4444', '#f59e0b'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const val = context.raw || 0;
                                    const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return ` ${context.label}: ${val} SV (${pct}%)`;
                                }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }

        // Biểu đồ 2: Số Lượng Sinh Viên Theo Ngành (Grouped Bar)
        const ctxSVNg = document.getElementById('chartSVTheoNganhCanvas');
        if (ctxSVNg && payload.chartSVTheoNganh) {
            if (chartInstances.svTheoNganh) chartInstances.svTheoNganh.destroy();
            chartInstances.svTheoNganh = new Chart(ctxSVNg, {
                type: 'bar',
                data: {
                    labels: payload.chartSVTheoNganh.labels,
                    datasets: [
                        {
                            label: 'Tổng SV',
                            data: payload.chartSVTheoNganh.tongSV,
                            backgroundColor: '#0072ce',
                            borderRadius: 4
                        },
                        {
                            label: 'Đạt',
                            data: payload.chartSVTheoNganh.dat,
                            backgroundColor: '#10b981',
                            borderRadius: 4
                        },
                        {
                            label: 'Chưa đạt',
                            data: payload.chartSVTheoNganh.chuaDat,
                            backgroundColor: '#ef4444',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { ticks: { font: { size: 11 } } }
                    },
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12 } }
                    }
                }
            });
        }

        // Biểu đồ 3: Điểm Trung Bình Theo Ngành (Bar)
        const ctxDiemNg = document.getElementById('chartDiemTheoNganhCanvas');
        if (ctxDiemNg && payload.chartDiemTheoNganh) {
            if (chartInstances.diemTheoNganh) chartInstances.diemTheoNganh.destroy();
            chartInstances.diemTheoNganh = new Chart(ctxDiemNg, {
                type: 'bar',
                data: {
                    labels: payload.chartDiemTheoNganh.labels,
                    datasets: [{
                        label: 'Điểm TB (Thang 10)',
                        data: payload.chartDiemTheoNganh.diemTB,
                        backgroundColor: '#f59e0b',
                        borderRadius: 6,
                        maxBarThickness: 45
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { min: 0, max: 10, ticks: { stepSize: 2 } },
                        x: { ticks: { font: { size: 11 } } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        // Biểu đồ 4: Phân Bố Khoảng Điểm (Histogram)
        const ctxPhanBo = document.getElementById('chartPhanBoDiemCanvas');
        if (ctxPhanBo && payload.chartPhanBoDiem) {
            if (chartInstances.phanBoDiem) chartInstances.phanBoDiem.destroy();
            chartInstances.phanBoDiem = new Chart(ctxPhanBo, {
                type: 'bar',
                data: {
                    labels: payload.chartPhanBoDiem.labels,
                    datasets: [{
                        label: 'Số sinh viên',
                        data: payload.chartPhanBoDiem.data,
                        backgroundColor: ['#ef4444', '#f97316', '#eab308', '#3b82f6', '#8b5cf6', '#10b981'],
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { ticks: { font: { size: 10 } } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        // Biểu đồ 5: Tình Hình Theo Hội Đồng (Bar)
        const ctxHD = document.getElementById('chartTheoHoiDongCanvas');
        if (ctxHD && payload.chartTheoHoiDong) {
            if (chartInstances.theoHoiDong) chartInstances.theoHoiDong.destroy();
            chartInstances.theoHoiDong = new Chart(ctxHD, {
                type: 'bar',
                data: {
                    labels: payload.chartTheoHoiDong.labels,
                    datasets: [
                        {
                            label: 'Đã phân công',
                            data: payload.chartTheoHoiDong.daPhanCong,
                            backgroundColor: '#6366f1',
                            borderRadius: 4
                        },
                        {
                            label: 'Đã hoàn thành',
                            data: payload.chartTheoHoiDong.daHoanThanh,
                            backgroundColor: '#0d9488',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { ticks: { font: { size: 10 } } }
                    },
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12 } }
                    }
                }
            });
        }

        // Biểu đồ 6: Tiến Độ Chấm Điểm (Doughnut)
        const ctxTienDo = document.getElementById('chartTienDoChamDiemCanvas');
        if (ctxTienDo && payload.chartTienDoChamDiem) {
            if (chartInstances.tienDoChamDiem) chartInstances.tienDoChamDiem.destroy();
            chartInstances.tienDoChamDiem = new Chart(ctxTienDo, {
                type: 'doughnut',
                data: {
                    labels: payload.chartTienDoChamDiem.labels,
                    datasets: [{
                        data: payload.chartTienDoChamDiem.data,
                        backgroundColor: payload.chartTienDoChamDiem.colors || ['#0072ce', '#f59e0b'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const val = context.raw || 0;
                                    const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return ` ${context.label}: ${val} SV (${pct}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }
    }

    // Khởi tạo ban đầu
    initOrUpdateCharts(chartsPayload);

    // ═════════════════════════════════════════════════════════════════
    // 3. CASCADING FILTER DROPDOWNS (BỘ LỌC PHỤ THUỘC TỰ ĐỘNG)
    // ═════════════════════════════════════════════════════════════════
    function applyCascadingFilters() {
        const selectedNamHoc = filterNamHoc ? filterNamHoc.value : '';
        const selectedKhoa = filterKhoa ? filterKhoa.value : '';
        const selectedBoMon = filterBoMon ? filterBoMon.value : '';
        const selectedNganh = filterNganh ? filterNganh.value : '';
        const selectedHocKy = filterHocKy ? filterHocKy.value : '';

        // 1. Năm học -> Lọc Học kỳ
        if (filterHocKy) {
            Array.from(filterHocKy.options).forEach(opt => {
                const optNamHoc = opt.getAttribute('data-nam-hoc');
                if (!selectedNamHoc || !optNamHoc || optNamHoc === selectedNamHoc) {
                    opt.hidden = false;
                } else {
                    opt.hidden = true;
                    if (opt.selected) opt.selected = false;
                }
            });
        }

        // 2. Khoa -> Lọc Bộ môn, Ngành, Hội đồng, GVHD
        if (filterBoMon) {
            Array.from(filterBoMon.options).forEach(opt => {
                if (!opt.value) return;
                const optKhoa = opt.getAttribute('data-khoa');
                opt.hidden = (selectedKhoa && optKhoa && optKhoa !== selectedKhoa);
                if (opt.hidden && opt.selected) filterBoMon.value = '';
            });
        }

        if (filterNganh) {
            Array.from(filterNganh.options).forEach(opt => {
                if (!opt.value) return;
                const optKhoa = opt.getAttribute('data-khoa');
                opt.hidden = (selectedKhoa && optKhoa && optKhoa !== selectedKhoa);
                if (opt.hidden && opt.selected) filterNganh.value = '';
            });
        }

        if (filterHoiDong) {
            Array.from(filterHoiDong.options).forEach(opt => {
                if (!opt.value) return;
                const optKhoa = opt.getAttribute('data-khoa');
                const optNganh = opt.getAttribute('data-nganh');
                const optHK = opt.getAttribute('data-hocky');

                let match = true;
                if (selectedKhoa && optKhoa && optKhoa !== selectedKhoa) match = false;
                if (selectedNganh && optNganh && optNganh !== selectedNganh) match = false;
                if (selectedHocKy && optHK && optHK !== selectedHocKy) match = false;

                opt.hidden = !match;
                if (opt.hidden && opt.selected) filterHoiDong.value = '';
            });
        }

        if (filterGiangVien) {
            Array.from(filterGiangVien.options).forEach(opt => {
                if (!opt.value) return;
                const optKhoa = opt.getAttribute('data-khoa');
                const optBm = opt.getAttribute('data-bomon');

                let match = true;
                if (selectedKhoa && optKhoa && optKhoa !== selectedKhoa) match = false;
                if (selectedBoMon && optBm && optBm !== selectedBoMon) match = false;

                opt.hidden = !match;
                if (opt.hidden && opt.selected) filterGiangVien.value = '';
            });
        }

        if (filterHocPhan) {
            Array.from(filterHocPhan.options).forEach(opt => {
                if (!opt.value) return;
                const optKhoa = opt.getAttribute('data-khoa');
                const optBm = opt.getAttribute('data-bomon');

                let match = true;
                if (selectedKhoa && optKhoa && optKhoa !== selectedKhoa) match = false;
                if (selectedBoMon && optBm && optBm !== selectedBoMon) match = false;

                opt.hidden = !match;
                if (opt.hidden && opt.selected) filterHocPhan.value = '';
            });
        }
    }

    if (filterNamHoc) filterNamHoc.addEventListener('change', applyCascadingFilters);
    if (filterKhoa) filterKhoa.addEventListener('change', applyCascadingFilters);
    if (filterBoMon) filterBoMon.addEventListener('change', applyCascadingFilters);
    if (filterNganh) filterNganh.addEventListener('change', applyCascadingFilters);
    if (filterHocKy) filterHocKy.addEventListener('change', applyCascadingFilters);

    applyCascadingFilters();

    // ═════════════════════════════════════════════════════════════════
    // 4. AJAX DATA FETCH & REAL-TIME RE-RENDER
    // ═════════════════════════════════════════════════════════════════
    let fetchController = null;

    function fetchFilteredData() {
        if (fetchController) {
            fetchController.abort();
        }
        fetchController = new AbortController();

        if (filterLoadingIndicator) {
            filterLoadingIndicator.classList.remove('d-none');
            filterLoadingIndicator.classList.add('d-flex');
        }

        const formData = new FormData(filterForm);
        const params = new URLSearchParams();
        for (let [key, val] of formData.entries()) {
            if (val !== '' && val !== null) {
                params.set(key, val);
            }
        }
        params.set('ajax', '1');

        const requestUrl = currentRouteUrl + '?' + params.toString();

        fetch(requestUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            signal: fetchController.signal
        })
        .then(res => {
            if (!res.ok) throw new Error('Không thể tải dữ liệu thống kê. Mã lỗi: ' + res.status);
            return res.json();
        })
        .then(res => {
            if (res.status === 'success') {
                // 1. Cập nhật thẻ KPI
                const kpiContainer = document.getElementById('kpiCardsContainer');
                if (kpiContainer && res.html_kpi) {
                    kpiContainer.outerHTML = res.html_kpi;
                }

                // 2. Cập nhật bảng dữ liệu chi tiết
                const dataTableWrapper = document.getElementById('dataTableWrapper');
                if (dataTableWrapper && res.html_table) {
                    dataTableWrapper.outerHTML = res.html_table;
                }

                // 3. Cập nhật lại 6 biểu đồ Chart.js
                if (res.charts) {
                    chartsPayload = res.charts;
                    initOrUpdateCharts(res.charts);
                }

                // 4. Cập nhật trạng thái nút Xuất PDF
                if (btnDirectExportPdf) {
                    btnDirectExportPdf.disabled = !res.hasData;
                }

                // 5. Cập nhật URL thanh địa chỉ (không reload)
                params.delete('ajax');
                const cleanUrl = currentRouteUrl + (params.toString() ? '?' + params.toString() : '');
                window.history.replaceState({}, '', cleanUrl);

                // 6. Nếu modal preview đang mở, cập nhật nội dung preview ngay lập tức
                if (modalPreviewReportEl && modalPreviewReportEl.classList.contains('show')) {
                    loadPreviewReport();
                }
            }
        })
        .catch(err => {
            if (err.name === 'AbortError') return;
            console.error('Lỗi khi tải dữ liệu thống kê:', err);
        })
        .finally(() => {
            if (filterLoadingIndicator) {
                filterLoadingIndicator.classList.add('d-none');
                filterLoadingIndicator.classList.remove('d-flex');
            }
        });
    }

    // Submit form lọc dữ liệu
    if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            fetchFilteredData();
        });
    }

    // Tự động lọc khi đổi các select quan trọng
    const autoFilterSelects = [filterDotBaoVe, filterHocKy, filterKhoa, filterBoMon, filterNganh, filterHoiDong, filterKetQua, filterHinhThuc];
    autoFilterSelects.forEach(sel => {
        if (sel) {
            sel.addEventListener('change', function () {
                fetchFilteredData();
            });
        }
    });

    // Xử lý ô tìm kiếm nhanh (debounce 400ms)
    let searchTimeout = null;
    if (filterSearch) {
        filterSearch.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(fetchFilteredData, 400);
        });
    }

    if (btnClearSearch) {
        btnClearSearch.addEventListener('click', function () {
            filterSearch.value = '';
            btnClearSearch.remove();
            fetchFilteredData();
        });
    }

    // ═════════════════════════════════════════════════════════════════
    // 5. TƯƠNG TÁC CHỌN BIỂU MẪU (10 MẪU BÁO CÁO)
    // ═════════════════════════════════════════════════════════════════
    function setActiveTemplate(mauCode) {
        if (!mauCode) return;
        templateCards.forEach(card => {
            const cardMau = card.getAttribute('data-mau');
            const isCurrent = (cardMau === mauCode);
            const btn = card.querySelector('.template-btn');
            const badge = card.querySelector('.template-badge');
            const icon = card.querySelector('.template-icon');

            if (isCurrent) {
                card.classList.add('active-template');
                if (btn) {
                    btn.textContent = 'Đang chọn';
                    btn.className = 'btn btn-primary btn-sm w-100 py-1 fw-semibold template-btn';
                }
                if (badge) {
                    badge.className = 'badge bg-primary text-white template-badge';
                }
                if (icon) {
                    icon.classList.remove('text-muted');
                    icon.classList.add('text-primary');
                }
            } else {
                card.classList.remove('active-template');
                if (btn) {
                    btn.textContent = 'Chọn mẫu';
                    btn.className = 'btn btn-outline-secondary btn-sm w-100 py-1 template-btn';
                }
                if (badge) {
                    badge.className = 'badge bg-light text-secondary border template-badge';
                }
                if (icon) {
                    icon.classList.remove('text-primary');
                    icon.classList.add('text-muted');
                }
            }
        });

        if (filterMauBaoCao) filterMauBaoCao.value = mauCode;
        if (badgeActiveTemplate) badgeActiveTemplate.textContent = mauCode.toUpperCase();
        if (modalHeaderMauTitle) modalHeaderMauTitle.textContent = mauCode.toUpperCase();
    }

    templateCards.forEach(card => {
        card.addEventListener('click', function () {
            const mauCode = this.getAttribute('data-mau');
            if (!mauCode) return;

            setActiveTemplate(mauCode);

            // Kích hoạt nạp dữ liệu mới
            fetchFilteredData();

            // Nếu modal preview đang mở thì reload bản in ngay
            if (modalPreviewReportEl && modalPreviewReportEl.classList.contains('show')) {
                loadPreviewReport();
            }
        });
    });

    // Nút làm mới bộ lọc
    if (btnResetFilter) {
        btnResetFilter.addEventListener('click', function () {
            filterForm.reset();
            setActiveTemplate('mau_01');
            
            // Tìm học kỳ hiện hành
            if (filterHocKy) {
                const curOpt = filterHocKy.querySelector('option[data-is-current="1"]');
                if (curOpt) filterHocKy.value = curOpt.value;
            }

            applyCascadingFilters();
            fetchFilteredData();

            if (modalPreviewReportEl && modalPreviewReportEl.classList.contains('show')) {
                loadPreviewReport();
            }
        });
    }

    // ═════════════════════════════════════════════════════════════════
    // 6. MODAL XEM TRƯỚC (LIVE PREVIEW) & TÙY CHỈNH NỘI DUNG A4
    // ═════════════════════════════════════════════════════════════════
    function getPreviewConfigParams() {
        const params = new URLSearchParams();
        const formData = new FormData(filterForm);
        for (let [k, v] of formData.entries()) {
            if (v !== '' && v !== null) params.set(k, v);
        }

        // Đọc cấu hình từ tab Tùy chỉnh
        const khoGiay = document.querySelector('input[name="preview_kho_giay"]:checked')?.value || 'landscape';
        const showKpi = document.getElementById('previewToggleKpi')?.checked ? '1' : '0';
        const showTable = document.getElementById('previewToggleTable')?.checked ? '1' : '0';
        const showDetail = document.getElementById('previewToggleDetail')?.checked ? '1' : '0';
        const showCouncilInfo = document.getElementById('previewToggleCouncilInfo')?.checked ? '1' : '0';
        const showScoreInfo = document.getElementById('previewToggleScoreInfo')?.checked ? '1' : '0';
        const showResultStatus = document.getElementById('previewToggleResultStatus')?.checked ? '1' : '0';
        const showExportDate = document.getElementById('previewToggleExportDate')?.checked ? '1' : '0';
        const showReporter = document.getElementById('previewToggleReporter')?.checked ? '1' : '0';
        const showConclusion = document.getElementById('previewToggleConclusion')?.checked ? '1' : '0';
        const showSignatures = document.getElementById('previewToggleSignatures')?.checked ? '1' : '0';

        params.set('kho_giay', khoGiay);
        params.set('show_kpi', showKpi);
        params.set('show_table', showTable);
        params.set('show_detail', showDetail);
        params.set('show_council_info', showCouncilInfo);
        params.set('show_score_info', showScoreInfo);
        params.set('show_result_status', showResultStatus);
        params.set('show_export_date', showExportDate);
        params.set('show_reporter', showReporter);
        params.set('show_conclusion', showConclusion);
        params.set('show_signatures', showSignatures);

        return params;
    }

    function loadPreviewReport() {
        if (!previewDocContainer) return;

        if (previewDocLoading) previewDocLoading.classList.remove('d-none');
        previewDocContainer.classList.add('d-none');

        const params = getPreviewConfigParams();
        const url = previewReportUrl + '?' + params.toString();

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Không thể tạo bản xem trước. Mã lỗi: ' + res.status);
            return res.json();
        })
        .then(res => {
            if (res.status === 'success') {
                previewDocContainer.innerHTML = res.html;
            }
        })
        .catch(err => {
            console.error('Lỗi xem trước:', err);
            previewDocContainer.innerHTML = `
                <div class="alert alert-danger my-4 mx-auto text-center" style="max-width: 500px;">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    Không thể tải bản xem trước: ${err.message}
                </div>`;
        })
        .finally(() => {
            if (previewDocLoading) previewDocLoading.classList.add('d-none');
            previewDocContainer.classList.remove('d-none');
        });
    }

    // Khi mở Modal -> Tải Live Preview
    if (modalPreviewReportEl) {
        modalPreviewReportEl.addEventListener('show.bs.modal', function () {
            loadPreviewReport();
        });
    }

    // Nút áp dụng tùy chỉnh & cập nhật xem trước
    if (btnApplyPreviewSettings) {
        btnApplyPreviewSettings.addEventListener('click', function () {
            // Chuyển tab về tab Xem trước A4
            const tabDocBtn = document.getElementById('tab-preview-doc-btn');
            if (tabDocBtn) {
                bootstrap.Tab.getOrCreateInstance(tabDocBtn).show();
            }
            loadPreviewReport();
        });
    }

    // In trực tiếp văn bản xem trước
    if (btnModalPrintDoc) {
        btnModalPrintDoc.addEventListener('click', function () {
            window.print();
        });
    }

    // ═════════════════════════════════════════════════════════════════
    // 7. XUẤT FILE PDF BÁO CÁO (DIRECT DOWNLOAD HOẶC TỪ MODAL)
    // ═════════════════════════════════════════════════════════════════
    function handleExportPdf(triggerBtn) {
        const params = getPreviewConfigParams();
        const downloadUrl = exportPdfUrl + '?' + params.toString();

        // Hiệu ứng loading trên nút
        if (triggerBtn) {
            triggerBtn.disabled = true;
        }
        if (modalExportPdfIcon) modalExportPdfIcon.classList.add('d-none');
        if (modalExportPdfSpinner) modalExportPdfSpinner.classList.remove('d-none');
        if (modalExportPdfText) modalExportPdfText.textContent = 'Đang xuất PDF...';

        fetch(downloadUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Không thể tạo file báo cáo PDF. Mã lỗi: ' + res.status);
            
            let filename = 'BaoCao_KhoaLuan.pdf';
            const disposition = res.headers.get('Content-Disposition');
            if (disposition && disposition.indexOf('filename=') !== -1) {
                const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                if (matches != null && matches[1]) {
                    filename = matches[1].replace(/['"]/g, '');
                }
            }

            return res.blob().then(blob => ({ blob, filename }));
        })
        .then(({ blob, filename }) => {
            const blobUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = blobUrl;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(blobUrl);
            a.remove();
        })
        .catch(err => {
            console.error('Lỗi tải file PDF:', err);
            alert('Đã có lỗi xảy ra khi tạo file PDF: ' + err.message);
        })
        .finally(() => {
            if (triggerBtn) triggerBtn.disabled = false;
            if (modalExportPdfIcon) modalExportPdfIcon.classList.remove('d-none');
            if (modalExportPdfSpinner) modalExportPdfSpinner.classList.add('d-none');
            if (modalExportPdfText) modalExportPdfText.textContent = 'Tải File PDF Báo Cáo';
        });
    }

    if (btnDirectExportPdf) {
        btnDirectExportPdf.addEventListener('click', function () {
            handleExportPdf(this);
        });
    }

    if (btnModalExportPdf) {
        btnModalExportPdf.addEventListener('click', function () {
            handleExportPdf(this);
        });
    }
});
</script>
@endpush
@endsection
