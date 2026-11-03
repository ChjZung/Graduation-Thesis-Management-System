<div class="modal fade" id="modalPreviewReport" tabindex="-1" aria-labelledby="modalPreviewReportLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border shadow-lg rounded-3">
            
            <div class="modal-header border-bottom px-4 py-2.5 bg-white">
                <div>
                    <h6 class="modal-title fw-bold text-dark mb-0" id="modalPreviewReportLabel">Xem trước báo cáo</h6>
                    <div class="text-muted small">
                        Mẫu: <strong class="text-dark text-uppercase" id="modalHeaderMauTitle">{{ $data['mauBaoCao'] ?? 'MAU_01' }}</strong> &bull; 
                        {{ $data['hocKy']->TenHocKy ?? '' }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="bg-light border-bottom px-4">
                <ul class="nav nav-tabs border-0" id="previewModalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold small py-2" id="tab-preview-doc-btn" data-bs-toggle="tab" data-bs-target="#tab-preview-doc" type="button" role="tab">
                            Bản in A4
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold small py-2" id="tab-preview-settings-btn" data-bs-toggle="tab" data-bs-target="#tab-preview-settings" type="button" role="tab">
                            Cấu hình hiển thị
                        </button>
                    </li>
                </ul>
            </div>

            <div class="modal-body p-3 p-md-4 bg-light" style="min-height: 480px;">
                <div class="tab-content" id="previewModalTabContent">
                    
                    <div class="tab-pane fade show active" id="tab-preview-doc" role="tabpanel">
                        <div id="previewDocLoading" class="text-center py-5 d-none">
                            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                            <div class="text-muted small">Đang tạo bản xem trước...</div>
                        </div>

                        <div id="previewDocContainer">
                            @include('thongke_baocao.partials.preview_content', [
                                'data'      => $data,
                                'mauBaoCao' => $data['mauBaoCao'] ?? 'mau_01',
                                'config'    => [
                                    'kho_giay'           => 'landscape',
                                    'show_kpi'           => true,
                                    'show_table'         => true,
                                    'show_detail'        => true,
                                    'show_charts'        => true,
                                    'show_council_info'  => true,
                                    'show_score_info'    => true,
                                    'show_result_status' => true,
                                    'show_export_date'   => true,
                                    'show_reporter'      => true,
                                    'show_conclusion'    => true,
                                    'show_signatures'    => true,
                                ]
                            ])
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-preview-settings" role="tabpanel">
                        <div class="card border shadow-none rounded-3 bg-white p-3 p-md-4 mx-auto" style="max-width: 720px;">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark small mb-2">Khổ giấy</label>
                                <div class="d-flex gap-4 small">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="preview_kho_giay" id="previewKhoGiayLandscape" value="landscape" checked>
                                        <label class="form-check-label" for="previewKhoGiayLandscape">A4 Ngang</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="preview_kho_giay" id="previewKhoGiayPortrait" value="portrait">
                                        <label class="form-check-label" for="previewKhoGiayPortrait">A4 Dọc</label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">

                            <label class="form-label fw-semibold text-dark small mb-2">Thành phần hiển thị</label>
                            <div class="row g-2 small mb-4">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="previewToggleKpi" checked>
                                        <label class="form-check-label" for="previewToggleKpi">Chỉ số tổng quan (KPIs)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="previewToggleTable" checked>
                                        <label class="form-check-label" for="previewToggleTable">Bảng tổng hợp kết quả</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="previewToggleDetail" checked>
                                        <label class="form-check-label" for="previewToggleDetail">Danh sách sinh viên chi tiết</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="previewToggleCouncilInfo" checked>
                                        <label class="form-check-label" for="previewToggleCouncilInfo">Thông tin hội đồng</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="previewToggleScoreInfo" checked>
                                        <label class="form-check-label" for="previewToggleScoreInfo">Điểm số chi tiết</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="previewToggleResultStatus" checked>
                                        <label class="form-check-label" for="previewToggleResultStatus">Trạng thái kết quả</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="previewToggleExportDate" checked>
                                        <label class="form-check-label" for="previewToggleExportDate">Ngày lập báo cáo</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="previewToggleReporter" checked>
                                        <label class="form-check-label" for="previewToggleReporter">Tên người lập báo cáo</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="previewToggleConclusion" checked>
                                        <label class="form-check-label" for="previewToggleConclusion">Nhận xét & kết luận</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="previewToggleSignatures" checked>
                                        <label class="form-check-label" for="previewToggleSignatures">Chữ ký xác nhận</label>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="button" id="btnApplyPreviewSettings" class="btn btn-primary btn-sm px-4 fw-semibold">
                                    Cập nhật bản xem trước
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top px-4 py-2.5 bg-white justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="btnModalPrintDoc" class="btn btn-sm btn-outline-primary fw-semibold">In</button>
                    <button type="button" id="btnModalExportPdf" class="btn btn-sm btn-danger fw-semibold px-3">
                        <i class="fa-solid fa-file-arrow-down me-1" id="modalExportPdfIcon"></i>
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="modalExportPdfSpinner"></span>
                        <span id="modalExportPdfText">Tải PDF</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>