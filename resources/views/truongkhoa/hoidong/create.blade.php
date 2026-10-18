@extends('layouts.truongkhoa')

@section('page_title', 'Lập Hội Đồng Bảo Vệ Khóa Luận Tốt Nghiệp')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">

            <!-- Card Container -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="p-4 text-white" style="background: linear-gradient(135deg, #00305a 0%, #0072ce 100%);">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-white text-dark rounded-pill px-3 py-1 fw-bold">{{ $khoa->TenKhoa ?? 'Khoa CNTT' }}</span>
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1 small fw-semibold">Quy trình Trưởng khoa</span>
                            </div>
                            <h4 class="fw-bold mb-0 text-white">
                                <i class="fa-solid fa-landmark me-2"></i>Lập Hội Đồng Bảo Vệ &amp; Tạo Quyết Định Công Bố
                            </h4>
                            <div class="text-white-50 small mt-1">
                                Quy trình 4 bước: Khai báo thông tin → Chọn đề tài/nhóm SV → Phân công giảng viên → Kiểm tra &amp; Ban hành
                            </div>
                        </div>
                        <a href="{{ route('truongkhoa.hoidong.index') }}" class="btn btn-outline-light rounded-pill px-3 btn-sm">
                            <i class="fa-solid fa-arrow-left me-1"></i> Danh sách Hội đồng
                        </a>
                    </div>
                </div>

                <!-- Wizard Step Navigator -->
                <div class="bg-light border-bottom px-4 py-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2" id="wizardNav">
                        <div class="wizard-step active" id="navStep1" onclick="jumpToStep(1)">
                            <div class="step-circle">1</div>
                            <div class="step-label">
                                <div class="step-title">Bước 1</div>
                                <div class="step-subtitle">Khai báo thông tin &amp; Quyết định</div>
                            </div>
                        </div>
                        <div class="wizard-arrow d-none d-md-block"><i class="fa-solid fa-chevron-right text-muted"></i></div>

                        <div class="wizard-step" id="navStep2" onclick="jumpToStep(2)">
                            <div class="step-circle">2</div>
                            <div class="step-label">
                                <div class="step-title">Bước 2</div>
                                <div class="step-subtitle">Chọn đề tài &amp; Nhóm SV</div>
                            </div>
                        </div>
                        <div class="wizard-arrow d-none d-md-block"><i class="fa-solid fa-chevron-right text-muted"></i></div>

                        <div class="wizard-step" id="navStep3" onclick="jumpToStep(3)">
                            <div class="step-circle">3</div>
                            <div class="step-label">
                                <div class="step-title">Bước 3</div>
                                <div class="step-subtitle">Phân công thành viên Hội đồng</div>
                            </div>
                        </div>
                        <div class="wizard-arrow d-none d-md-block"><i class="fa-solid fa-chevron-right text-muted"></i></div>

                        <div class="wizard-step" id="navStep4" onclick="jumpToStep(4)">
                            <div class="step-circle">4</div>
                            <div class="step-label">
                                <div class="step-title">Bước 4</div>
                                <div class="step-subtitle">Kiểm tra &amp; Ban hành</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Card Body -->
                <div class="card-body p-4 p-md-5">
                    @if(isset($errors) && $errors->any())
                    <div class="alert alert-danger p-3 rounded-3 mb-4 shadow-xs">
                        <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-2"></i>Đã có lỗi xảy ra, vui lòng kiểm tra lại:</div>
                        <ul class="mb-0 small ps-3">
                            @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form id="formLapHoiDong" action="{{ route('truongkhoa.hoidong.store') }}" method="POST">
                        @csrf

                        <!-- ========================================================== -->
                        <!-- BƯỚC 1: KHAI BÁO THÔNG TIN HỘI ĐỒNG & QUYẾT ĐỊNH           -->
                        <!-- ========================================================== -->
                        <div class="wizard-content-step" id="stepContent1">
                            <h5 class="fw-bold text-primary mb-3 d-flex align-items-center">
                                <i class="fa-solid fa-circle-info me-2"></i>1. Khai Báo Thông Tin Hội Đồng &amp; Văn Bản Quyết Định
                            </h5>

                            <div class="row g-4 mb-4">
                                <div class="col-md-7">
                                    <label class="form-label fw-bold text-dark small">Tên Hội Đồng Bảo Vệ <span class="text-danger">*</span></label>
                                    <input type="text" id="TenHoiDong" name="TenHoiDong" class="form-control form-control-lg fs-6"
                                           value="{{ old('TenHoiDong', 'Hội đồng 01 — Ngành ' . ($nganhs->first()?->TenNganh ?? 'CNTT')) }}"
                                           placeholder="VD: Hội đồng chuyên ngành Công nghệ phần mềm 01" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold text-dark small">Mã Hội Đồng</label>
                                    <input type="text" class="form-control form-control-lg fs-6 bg-light text-muted"
                                           value="{{ $nextMaHoiDong }} (Tự động tạo)" readonly>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">Học Kỳ Áp Dụng <span class="text-danger">*</span></label>
                                    <select name="MaHocKy" id="MaHocKy" class="form-select" required>
                                        @foreach($hocKys as $hk)
                                        <option value="{{ $hk->MaHocKy }}" {{ old('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }} data-namhoc="{{ $hk->NamHoc }}">
                                            {{ $hk->TenHocKy }} (Năm học {{ $hk->NamHoc }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">Ngành Đào Tạo <span class="text-danger">*</span></label>
                                    <select name="MaNganh" id="MaNganh" class="form-select" required>
                                        @foreach($nganhs as $ng)
                                        <option value="{{ $ng->MaNganh }}" {{ old('MaNganh') == $ng->MaNganh ? 'selected' : '' }}>
                                            {{ $ng->TenNganh }} ({{ $ng->MaNganh }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">Đợt Bảo Vệ Khóa Luận <span class="text-danger">*</span></label>
                                    <input type="text" name="DotBaoVe" id="DotBaoVe" class="form-control"
                                           value="{{ old('DotBaoVe', 'Đợt 1') }}"
                                           placeholder="VD: Đợt 1, Đợt chính quy..." required>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">Ngày Tổ Chức Bảo Vệ <span class="text-danger">*</span></label>
                                    <input type="date" name="NgayBaoVe" id="NgayBaoVe" class="form-control"
                                           value="{{ old('NgayBaoVe', date('Y-m-d', strtotime('+7 days'))) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">Thời Gian Bắt Đầu <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="ThoiGianBatDau" id="ThoiGianBatDau" class="form-control"
                                           value="{{ old('ThoiGianBatDau', date('Y-m-d\T07:30', strtotime('+7 days'))) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">Thời Gian Kết Thúc <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="ThoiGianKetThuc" id="ThoiGianKetThuc" class="form-control"
                                           value="{{ old('ThoiGianKetThuc', date('Y-m-d\T17:00', strtotime('+7 days'))) }}" required>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">Địa Điểm / Phòng Bảo Vệ <span class="text-danger">*</span></label>
                                    <input type="text" name="DiaDiem" id="DiaDiem" class="form-control"
                                           value="{{ old('DiaDiem', 'Phòng B.304 (Tòa nhà B - 140 Lê Trọng Tấn, Tân Phú)') }}"
                                           placeholder="VD: Phòng B.304 - 140 Lê Trọng Tấn" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">Ghi Chú Chung</label>
                                    <input type="text" name="GhiChu" id="GhiChu" class="form-control"
                                           value="{{ old('GhiChu') }}"
                                           placeholder="Ghi chú về thiết bị trình chiếu, máy chiếu, thứ tự bảo vệ...">
                                </div>
                            </div>

                            <!-- Khối thông tin biểu mẫu quyết định -->
                            <div class="card border border-primary-subtle rounded-3 bg-light p-3 p-md-4 mb-4">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="fa-solid fa-file-contract me-2"></i>Thông Tin Biểu Mẫu Quyết Định Ban Hành (Tạo PDF hợp nhất)
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold text-secondary">Số Quyết Định</label>
                                        <input type="text" name="SoQuyetDinh" id="SoQuyetDinh" class="form-control form-control-sm"
                                               value="{{ old('SoQuyetDinh', '125/QĐ-ĐHCNTP') }}"
                                               placeholder="VD: 125/QĐ-ĐHCNTP">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold text-secondary">Ngày Ban Hành QĐ</label>
                                        <input type="date" name="NgayBanHanh" id="NgayBanHanh" class="form-control form-control-sm"
                                               value="{{ old('NgayBanHanh', date('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold text-secondary">Người Ký Quyết Định</label>
                                        <input type="text" name="NguoiKy" id="NguoiKy" class="form-control form-control-sm"
                                               value="{{ old('NguoiKy', $khoa->TruongKhoa ?: 'PGS.TS. Nguyễn Xuân Hoàn') }}"
                                               placeholder="Họ và tên người ký">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold text-secondary">Chức Vụ Người Ký</label>
                                        <input type="text" name="ChucVuNguoiKy" id="ChucVuNguoiKy" class="form-control form-control-sm"
                                               value="{{ old('ChucVuNguoiKy', 'HIỆU TRƯỞNG') }}"
                                               placeholder="VD: HIỆU TRƯỞNG hoặc TRƯỞNG KHOA">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold" onclick="nextStep(1)">
                                    Tiếp tục: Chọn Đề Tài &amp; Nhóm SV <i class="fa-solid fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ========================================================== -->
                        <!-- BƯỚC 2: CHỌN DANH SÁCH ĐỀ TÀI & NHÓM SINH VIÊN             -->
                        <!-- ========================================================== -->
                        <div class="wizard-content-step d-none" id="stepContent2">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                                <div>
                                    <h5 class="fw-bold text-primary mb-0 d-flex align-items-center">
                                        <i class="fa-solid fa-list-check me-2"></i>2. Chọn Danh Sách Đề Tài &amp; Nhóm Sinh Viên
                                    </h5>
                                    <div class="text-muted small mt-1">
                                        Chỉ các đề tài đáp ứng điều kiện bảo vệ mới được lựa chọn vào Hội đồng.
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill shadow-xs" id="badgeSoDeTaiChon">
                                        Đã chọn: <span id="soDeTaiChonCount">0</span> đề tài (<span id="soSinhVienChonCount">0</span> SV)
                                    </span>
                                </div>
                            </div>

                            <!-- Bộ lọc danh sách đề tài -->
                            <div class="card border rounded-3 p-3 bg-light mb-3">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                            <input type="text" id="searchDeTai" class="form-control border-start-0"
                                                   placeholder="Tìm mã ĐT, tên đề tài, MSSV, tên SV...">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <select id="filterBoMonDeTai" class="form-select">
                                            <option value="">— Tất cả Bộ môn —</option>
                                            @foreach($boMons as $bm)
                                            <option value="{{ $bm->TenBoMon }}">{{ $bm->TenBoMon }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select id="filterTrangThaiHoSo" class="form-select">
                                            <option value="">— Tất cả trạng thái hồ sơ —</option>
                                            <option value="du_dieu_kien" selected>Đủ điều kiện bảo vệ</option>
                                            <option value="tat_ca">Tất cả đề tài</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill w-100" id="btnChonTatCa">
                                            <i class="fa-solid fa-check-double me-1"></i> Chọn tất cả
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Bảng đề tài -->
                            <div class="table-responsive border rounded-3 overflow-hidden mb-3">
                                <table class="table table-hover align-middle mb-0" id="tableDeTai">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40px;" class="text-center">
                                                <input type="checkbox" class="form-check-input" id="checkAllDeTai">
                                            </th>
                                            <th style="width: 100px;">Mã ĐT</th>
                                            <th>Tên Đề Tài &amp; Thông Tin Nhóm Sinh Viên</th>
                                            <th style="width: 200px;">Cán Bộ Hướng Dẫn</th>
                                            <th style="width: 140px;" class="text-center">Trạng Thái Hồ Sơ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyDeTai">
                                        @forelse($danhSachHoSo as $hs)
                                        @php
                                            $dt = $hs->nhom?->deTai;
                                            $nhom = $hs->nhom;
                                            $sinhViens = $nhom?->thanhVienNhoms ?? collect();
                                            $soSV = $sinhViens->count();
                                            $isDaCoHoiDong = !empty($hs->MaHoiDong);
                                            $gvhd = $dt?->giangVien;
                                            $tenBM = $gvhd?->boMon?->TenBoMon ?? '';
                                            $svSearchText = $sinhViens->map(fn($tv) => ($tv->sinhVien?->MaSV ?? '') . ' ' . ($tv->sinhVien?->HoTen ?? ''))->join(' ');
                                        @endphp
                                        <tr class="row-detai {{ $isDaCoHoiDong ? 'bg-light opacity-75' : '' }}"
                                            data-mahoso="{{ $hs->MaHoSo }}"
                                            data-madetai="{{ $dt?->MaDeTai }}"
                                            data-tendetai="{{ $dt?->TenDeTai }}"
                                            data-manhom="{{ $nhom?->MaNhom }}"
                                            data-gvhd="{{ $gvhd?->HoTen }}"
                                            data-bomon="{{ $tenBM }}"
                                            data-sosv="{{ $soSV }}"
                                            data-search="{{ mb_strtolower(($dt?->MaDeTai ?? '') . ' ' . ($dt?->TenDeTai ?? '') . ' ' . ($nhom?->MaNhom ?? '') . ' ' . ($gvhd?->HoTen ?? '') . ' ' . $svSearchText) }}">
                                            <td class="text-center">
                                                @if($isDaCoHoiDong)
                                                    <input type="checkbox" class="form-check-input" disabled title="Đã được phân vào Hội đồng khác">
                                                @else
                                                    <input type="checkbox" name="danh_sach_ho_so[]" value="{{ $hs->MaHoSo }}"
                                                           class="form-check-input check-item-detai"
                                                           data-sosv="{{ $soSV }}"
                                                           onchange="updateDeTaiCount()">
                                                @endif
                                            </td>
                                            <td class="font-monospace fw-bold text-primary">{{ $dt?->MaDeTai ?? $hs->MaHoSo }}</td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $dt?->TenDeTai ?? 'Chưa cập nhật tên đề tài' }}</div>
                                                <div class="d-flex flex-wrap align-items-center gap-2 mt-1 small text-muted">
                                                    <span class="badge bg-secondary-subtle text-secondary border">Nhóm {{ $nhom?->MaNhom }}</span>
                                                    <span>• {{ $soSV }} Sinh viên:</span>
                                                    @foreach($sinhViens as $tv)
                                                        <span class="text-dark fw-semibold">{{ $tv->sinhVien?->HoTen }} ({{ $tv->sinhVien?->MaSV }})</span>
                                                        @if(!$loop->last) , @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $gvhd?->HoTen ?? 'Chưa phân công' }}</div>
                                                <div class="text-muted small">{{ $tenBM }}</div>
                                            </td>
                                            <td class="text-center">
                                                @if($isDaCoHoiDong)
                                                    <span class="badge bg-warning-subtle text-warning border rounded-pill px-2 py-1 small">
                                                        <i class="fa-solid fa-lock me-1"></i>HĐ {{ $hs->hoiDong?->TenHoiDong ?? $hs->MaHoiDong }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success border rounded-pill px-2 py-1 small">
                                                        <i class="fa-solid fa-circle-check me-1"></i>{{ $hs->TrangThai }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fa-solid fa-circle-exclamation fa-2x mb-2 text-warning"></i>
                                                <div>Hiện chưa có đề tài / hồ sơ nào đủ điều kiện bảo vệ trong hệ thống.</div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="prevStep(2)">
                                    <i class="fa-solid fa-arrow-left me-2"></i> Quay lại Bước 1
                                </button>
                                <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold" onclick="nextStep(2)">
                                    Tiếp tục: Phân Công Giảng Viên <i class="fa-solid fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ========================================================== -->
                        <!-- BƯỚC 3: PHÂN CÔNG THÀNH VIÊN HỘI ĐỒNG                      -->
                        <!-- ========================================================== -->
                        <div class="wizard-content-step d-none" id="stepContent3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                                <div>
                                    <h5 class="fw-bold text-primary mb-0 d-flex align-items-center">
                                        <i class="fa-solid fa-users me-2"></i>3. Phân Công Thành Viên Hội Đồng Chấm Khóa Luận
                                    </h5>
                                    <div class="text-muted small mt-1">
                                        Không bắt buộc giảng viên cùng Bộ môn với đề tài. Danh sách lựa chọn mở rộng toàn Khoa.
                                    </div>
                                </div>
                                <button type="button" id="btnThemUyVien" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="fa-solid fa-plus me-1"></i> Thêm Ủy Viên Khác
                                </button>
                            </div>

                            <!-- Banner hướng dẫn & cảnh báo xung đột lợi ích -->
                            <div class="alert alert-info py-2 px-3 small rounded-3 mb-3 border-0 bg-info-subtle text-info-emphasis d-flex align-items-center">
                                <i class="fa-solid fa-circle-info fs-5 me-2"></i>
                                <div>
                                    Cơ cấu bắt buộc tối thiểu: <strong>01 Chủ tịch</strong>, <strong>01 Ủy viên phản biện</strong>, <strong>01 Ủy viên thư ký</strong>.
                                    Không phân công một giảng viên vào nhiều vị trí trong cùng một hội đồng.
                                </div>
                            </div>

                            <div id="conflictWarningBox" class="alert alert-warning py-2 px-3 small rounded-3 mb-3 border-0 d-none">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                <span id="conflictWarningText"></span>
                            </div>

                            <!-- 3 Vị trí bắt buộc -->
                            <div class="card border rounded-3 p-3 p-md-4 mb-3 bg-light">
                                <div class="row g-3">
                                    <!-- 1. Chủ tịch -->
                                    <div class="col-md-12">
                                        <div class="p-3 bg-white rounded-3 border">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label fw-bold text-danger mb-0">
                                                    <i class="fa-solid fa-crown me-2"></i>1. CHỦ TỊCH HỘI ĐỒNG <span class="text-danger">*</span>
                                                </label>
                                                <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1">Bắt buộc</span>
                                            </div>
                                            <input type="hidden" name="thanh_viens[0][VaiTro]" value="Chủ tịch">
                                            <select name="thanh_viens[0][MaGV]" id="selectChuTich" class="form-select select-faculty" required onchange="validateFacultySelection()">
                                                <option value="">— Chọn Giảng viên giữ chức Chủ tịch Hội đồng —</option>
                                                @foreach($giangViens as $gv)
                                                <option value="{{ $gv->MaGV }}" data-hoten="{{ $gv->HoTen }}" data-bomon="{{ $gv->boMon?->TenBoMon ?? 'Khoa' }}" data-hocham="{{ $gv->HocHam ?? ($gv->HocVi ?? 'Thạc sĩ') }}">
                                                    {{ $gv->HoTen }} — {{ $gv->HocHam ?? ($gv->HocVi ?? 'Thạc sĩ') }} ({{ $gv->boMon?->TenBoMon ?? 'Khoa' }})
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- 2. Phản biện -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-white rounded-3 border h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label fw-bold text-primary mb-0">
                                                    <i class="fa-solid fa-clipboard-check me-2"></i>2. ỦY VIÊN PHẢN BIỆN <span class="text-danger">*</span>
                                                </label>
                                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">Bắt buộc</span>
                                            </div>
                                            <input type="hidden" name="thanh_viens[1][VaiTro]" value="Ủy viên phản biện">
                                            <select name="thanh_viens[1][MaGV]" id="selectPhanBien" class="form-select select-faculty" required onchange="validateFacultySelection()">
                                                <option value="">— Chọn Giảng viên Phản biện —</option>
                                                @foreach($giangViens as $gv)
                                                <option value="{{ $gv->MaGV }}" data-hoten="{{ $gv->HoTen }}" data-bomon="{{ $gv->boMon?->TenBoMon ?? 'Khoa' }}" data-hocham="{{ $gv->HocHam ?? ($gv->HocVi ?? 'Thạc sĩ') }}">
                                                    {{ $gv->HoTen }} — {{ $gv->HocHam ?? ($gv->HocVi ?? 'Thạc sĩ') }} ({{ $gv->boMon?->TenBoMon ?? 'Khoa' }})
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- 3. Thư ký -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-white rounded-3 border h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label fw-bold text-success mb-0">
                                                    <i class="fa-solid fa-pen-to-square me-2"></i>3. ỦY VIÊN THƯ KÝ <span class="text-danger">*</span>
                                                </label>
                                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">Bắt buộc</span>
                                            </div>
                                            <input type="hidden" name="thanh_viens[2][VaiTro]" value="Ủy viên thư ký">
                                            <select name="thanh_viens[2][MaGV]" id="selectThuKy" class="form-select select-faculty" required onchange="validateFacultySelection()">
                                                <option value="">— Chọn Giảng viên Thư ký —</option>
                                                @foreach($giangViens as $gv)
                                                <option value="{{ $gv->MaGV }}" data-hoten="{{ $gv->HoTen }}" data-bomon="{{ $gv->boMon?->TenBoMon ?? 'Khoa' }}" data-hocham="{{ $gv->HocHam ?? ($gv->HocVi ?? 'Thạc sĩ') }}">
                                                    {{ $gv->HoTen }} — {{ $gv->HocHam ?? ($gv->HocVi ?? 'Thạc sĩ') }} ({{ $gv->boMon?->TenBoMon ?? 'Khoa' }})
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Danh sách Ủy viên bổ sung (nếu có) -->
                            <div id="containerUyVienThem"></div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="prevStep(3)">
                                    <i class="fa-solid fa-arrow-left me-2"></i> Quay lại Bước 2
                                </button>
                                <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold" onclick="nextStep(3)">
                                    Tiếp tục: Kiểm Tra &amp; Xác Nhận <i class="fa-solid fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ========================================================== -->
                        <!-- BƯỚC 4: KIỂM TRA, XÁC NHẬN & BAN HÀNH QUYẾT ĐỊNH           -->
                        <!-- ========================================================== -->
                        <div class="wizard-content-step d-none" id="stepContent4">
                            <h5 class="fw-bold text-primary mb-3 d-flex align-items-center">
                                <i class="fa-solid fa-clipboard-check me-2"></i>4. Kiểm Tra Toàn Diện &amp; Ban Hành Quyết Định
                            </h5>

                            <!-- Card Tổng kết thông tin HĐ -->
                            <div class="card border rounded-3 p-4 mb-4 bg-light">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-white rounded-3 border h-100">
                                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">
                                                <i class="fa-solid fa-landmark text-primary me-2"></i>Thông Tin Hội Đồng
                                            </h6>
                                            <div class="small lh-lg">
                                                <div><strong>Tên Hội đồng:</strong> <span id="pvTenHoiDong" class="text-primary fw-bold"></span></div>
                                                <div><strong>Học kỳ &amp; Ngành:</strong> <span id="pvHocKyNganh"></span></div>
                                                <div><strong>Đợt bảo vệ:</strong> <span id="pvDotBaoVe"></span></div>
                                                <div><strong>Ngày &amp; Giờ:</strong> <span id="pvNgayGio"></span></div>
                                                <div><strong>Địa điểm/Phòng:</strong> <span id="pvDiaDiem"></span></div>
                                                <div><strong>Ghi chú:</strong> <span id="pvGhiChu" class="text-muted">Không có</span></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-3 bg-white rounded-3 border h-100">
                                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">
                                                <i class="fa-solid fa-file-contract text-primary me-2"></i>Văn Bản Quyết Định Ban Hành
                                            </h6>
                                            <div class="small lh-lg">
                                                <div><strong>Số Quyết định:</strong> <span id="pvSoQuyetDinh" class="fw-bold text-danger"></span></div>
                                                <div><strong>Ngày ban hành:</strong> <span id="pvNgayBanHanh"></span></div>
                                                <div><strong>Người ký:</strong> <span id="pvNguoiKy" class="fw-semibold"></span></div>
                                                <div><strong>Chức danh ký:</strong> <span id="pvChucVuNguoiKy"></span></div>
                                                <div class="text-success mt-2">
                                                    <i class="fa-solid fa-file-pdf me-1"></i> Định dạng xuất bản: <strong>PDF Hợp nhất (Quyết định + Phụ lục 1 + Phụ lục 2)</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bảng tóm tắt thành viên HĐ -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark mb-2">
                                    <i class="fa-solid fa-users text-primary me-2"></i>Thành Viên Hội Đồng Đã Phân Công (Phụ lục 1)
                                </h6>
                                <div class="table-responsive border rounded-3">
                                    <table class="table table-sm table-hover align-middle mb-0" id="pvTableThanhVien">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 10%;">STT</th>
                                                <th style="width: 35%;">Họ và Tên Giảng Viên</th>
                                                <th style="width: 25%;">Bộ Môn / Đơn Vị</th>
                                                <th style="width: 30%;">Chức Danh Trong Hội Đồng</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pvTbodyThanhVien"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Bảng tóm tắt đề tài & SV -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark mb-2">
                                    <i class="fa-solid fa-graduation-cap text-primary me-2"></i>Danh Sách Đề Tài &amp; Sinh Viên Bảo Vệ (Phụ lục 2)
                                </h6>
                                <div class="table-responsive border rounded-3">
                                    <table class="table table-sm table-hover align-middle mb-0" id="pvTableDeTai">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 8%;">STT</th>
                                                <th style="width: 12%;">Mã Nhóm</th>
                                                <th style="width: 40%;">Tên Đề Tài Khóa Luận</th>
                                                <th style="width: 25%;">Sinh Viên Bảo Vệ</th>
                                                <th style="width: 15%;">GVHD</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pvTbodyDeTai"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Khối xác nhận & Tùy chọn công bố -->
                            <div class="card border border-success-subtle bg-success-subtle p-3 rounded-3 mb-4">
                                <div class="form-check form-switch fs-6">
                                    <input class="form-check-input" type="checkbox" name="cong_bo_ngay" id="checkCongBoNgay" value="1" checked>
                                    <label class="form-check-label fw-bold text-success-emphasis" for="checkCongBoNgay">
                                        <i class="fa-solid fa-bullhorn me-1"></i> Công bố chính thức ngay sau khi lưu
                                    </label>
                                </div>
                                <div class="small text-muted ps-4 mt-1">
                                    Hệ thống sẽ ngay lập tức tạo tệp PDF Quyết định kèm 2 Phụ lục, lưu trữ số hóa và phát thông báo lịch bảo vệ tới Giáo vụ, Giảng viên Hội đồng và Sinh viên các nhóm.
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="prevStep(4)">
                                    <i class="fa-solid fa-arrow-left me-2"></i> Quay lại Bước 3
                                </button>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success rounded-pill px-5 py-2 fw-bold shadow-xs">
                                        <i class="fa-solid fa-stamp me-2"></i> Ký Duyệt &amp; Ban Hành Quyết Định
                                    </button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .wizard-step {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 16px;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        background-color: transparent;
    }
    .wizard-step:hover {
        background-color: rgba(0, 114, 206, 0.08);
    }
    .wizard-step.active {
        background-color: #00305a;
        color: white;
    }
    .wizard-step.active .step-circle {
        background-color: #f7a800;
        color: #00305a;
        font-weight: bold;
    }
    .wizard-step.completed .step-circle {
        background-color: #198754;
        color: white;
    }
    .wizard-step.active .step-subtitle,
    .wizard-step.active .step-title {
        color: white !important;
    }
    .step-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #e9ecef;
        color: #495057;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 600;
    }
    .step-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #00305a;
    }
    .step-subtitle {
        font-size: 11px;
        color: #6c757d;
    }
</style>
@endpush

@push('scripts')
<script>
    let currentStep = 1;
    let soUyVienThem = 0;

    // Chuyển bước
    function jumpToStep(step) {
        if (step > currentStep && !validateStep(currentStep)) {
            return;
        }
        setStep(step);
    }

    function nextStep(fromStep) {
        if (!validateStep(fromStep)) return;
        setStep(fromStep + 1);
    }

    function prevStep(fromStep) {
        setStep(fromStep - 1);
    }

    function setStep(step) {
        currentStep = step;

        // Ẩn tất cả các step
        for (let i = 1; i <= 4; i++) {
            const content = document.getElementById('stepContent' + i);
            const nav = document.getElementById('navStep' + i);
            if (content) {
                if (i === step) {
                    content.classList.remove('d-none');
                } else {
                    content.classList.add('d-none');
                }
            }
            if (nav) {
                if (i === step) {
                    nav.classList.add('active');
                } else {
                    nav.classList.remove('active');
                    if (i < step) {
                        nav.classList.add('completed');
                    } else {
                        nav.classList.remove('completed');
                    }
                }
            }
        }

        // Nếu chuyển sang bước 4: chuẩn bị nội dung Preview
        if (step === 4) {
            buildPreviewStep4();
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Validate từng bước
    function validateStep(step) {
        if (step === 1) {
            const ten = document.getElementById('TenHoiDong')?.value?.trim();
            const hk = document.getElementById('MaHocKy')?.value;
            const ngay = document.getElementById('NgayBaoVe')?.value;
            const batDau = document.getElementById('ThoiGianBatDau')?.value;
            const ketThuc = document.getElementById('ThoiGianKetThuc')?.value;
            const diaDiem = document.getElementById('DiaDiem')?.value?.trim();

            if (!ten || !hk || !ngay || !batDau || !ketThuc || !diaDiem) {
                alert('Vui lòng điền đầy đủ các thông tin bắt buộc có dấu (*) ở Bước 1.');
                return false;
            }
            if (new Date(ketThuc) <= new Date(batDau)) {
                alert('Thời gian kết thúc phải sau thời gian bắt đầu bảo vệ.');
                return false;
            }
            return true;
        }

        if (step === 2) {
            const checkedItems = document.querySelectorAll('.check-item-detai:checked');
            if (checkedItems.length === 0) {
                alert('Vui lòng chọn ít nhất 01 đề tài/nhóm sinh viên cho Hội đồng bảo vệ.');
                return false;
            }
            return true;
        }

        if (step === 3) {
            const chuTich = document.getElementById('selectChuTich')?.value;
            const phanBien = document.getElementById('selectPhanBien')?.value;
            const thuKy = document.getElementById('selectThuKy')?.value;

            if (!chuTich || !phanBien || !thuKy) {
                alert('Vui lòng phân công đủ 3 vị trí bắt buộc: Chủ tịch, Phản biện và Thư ký Hội đồng.');
                return false;
            }

            // Kiểm tra trùng nhau
            const allSelected = [];
            document.querySelectorAll('.select-faculty').forEach(sel => {
                if (sel.value) allSelected.push(sel.value);
            });

            const unique = new Set(allSelected);
            if (unique.size !== allSelected.length) {
                alert('Không được phân công cùng một Giảng viên vào nhiều vị trí trong cùng một Hội đồng.');
                return false;
            }
            return true;
        }

        return true;
    }

    // Cập nhật số đề tài đã chọn
    function updateDeTaiCount() {
        const checkedItems = document.querySelectorAll('.check-item-detai:checked');
        let totalSV = 0;
        checkedItems.forEach(item => {
            totalSV += parseInt(item.getAttribute('data-sosv') || 0);
        });

        document.getElementById('soDeTaiChonCount').textContent = checkedItems.length;
        document.getElementById('soSinhVienChonCount').textContent = totalSV;
    }

    // Chọn tất cả / Bỏ chọn tất cả đề tài
    document.getElementById('btnChonTatCa')?.addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.check-item-detai:not(:disabled)');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => {
            // Chỉ tác động vào hàng đang hiển thị
            const row = cb.closest('tr');
            if (row && row.style.display !== 'none') {
                cb.checked = !allChecked;
            }
        });
        updateDeTaiCount();
    });

    document.getElementById('checkAllDeTai')?.addEventListener('change', function() {
        const isChecked = this.checked;
        document.querySelectorAll('.check-item-detai:not(:disabled)').forEach(cb => {
            const row = cb.closest('tr');
            if (row && row.style.display !== 'none') {
                cb.checked = isChecked;
            }
        });
        updateDeTaiCount();
    });

    // Tìm kiếm đề tài
    document.getElementById('searchDeTai')?.addEventListener('input', filterDeTaiTable);
    document.getElementById('filterBoMonDeTai')?.addEventListener('change', filterDeTaiTable);
    document.getElementById('filterTrangThaiHoSo')?.addEventListener('change', filterDeTaiTable);

    function filterDeTaiTable() {
        const query = document.getElementById('searchDeTai')?.value?.toLowerCase().trim() || '';
        const bmFilter = document.getElementById('filterBoMonDeTai')?.value?.toLowerCase() || '';
        const statusFilter = document.getElementById('filterTrangThaiHoSo')?.value || '';

        document.querySelectorAll('#tbodyDeTai tr.row-detai').forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            const rowBM = (row.getAttribute('data-bomon') || '').toLowerCase();
            const isLock = row.classList.contains('opacity-75');

            let matchQuery = !query || searchData.includes(query);
            let matchBM = !bmFilter || rowBM.includes(bmFilter);
            let matchStatus = true;
            if (statusFilter === 'du_dieu_kien') {
                matchStatus = !isLock;
            }

            if (matchQuery && matchBM && matchStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Thêm Ủy viên bổ sung ở Bước 3
    document.getElementById('btnThemUyVien')?.addEventListener('click', function() {
        soUyVienThem++;
        const index = 2 + soUyVienThem; // 0, 1, 2 là Chủ tịch, Phản biện, Thư ký
        const container = document.getElementById('containerUyVienThem');

        const div = document.createElement('div');
        div.className = 'card border rounded-3 p-3 mb-2 bg-white dong-uy-vien-them';
        div.id = 'uyVienThem_' + index;
        div.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1">Chức danh:</label>
                    <input type="text" name="thanh_viens[${index}][VaiTro]" class="form-control form-control-sm fw-semibold" value="Ủy viên ${soUyVienThem}">
                </div>
                <div class="col-md-8">
                    <label class="form-label small fw-bold text-secondary mb-1">Giảng viên thành viên:</label>
                    <select name="thanh_viens[${index}][MaGV]" class="form-select form-select-sm select-faculty" required onchange="validateFacultySelection()">
                        <option value="">— Chọn Giảng viên —</option>
                        @foreach($giangViens as $gv)
                        <option value="{{ $gv->MaGV }}" data-hoten="{{ $gv->HoTen }}" data-bomon="{{ $gv->boMon?->TenBoMon ?? 'Khoa' }}" data-hocham="{{ $gv->HocHam ?? ($gv->HocVi ?? 'Thạc sĩ') }}">
                            {{ $gv->HoTen }} — {{ $gv->HocHam ?? ($gv->HocVi ?? 'Thạc sĩ') }} ({{ $gv->boMon?->TenBoMon ?? 'Khoa' }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 text-center pt-md-3">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-circle" onclick="xoaDongUyVien('${div.id}')" title="Xóa ủy viên này">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(div);
    });

    function xoaDongUyVien(id) {
        document.getElementById(id)?.remove();
        validateFacultySelection();
    }

    // Kiểm tra xung đột lợi ích: Giảng viên được chọn có phải là GVHD của đề tài đã chọn?
    function validateFacultySelection() {
        const selectedGVs = [];
        document.querySelectorAll('.select-faculty').forEach(sel => {
            if (sel.value) selectedGVs.push(sel.value);
        });

        // Lấy danh sách GVHD của các đề tài đã chọn ở bước 2
        const selectedDeTaiGVHDs = [];
        document.querySelectorAll('.check-item-detai:checked').forEach(cb => {
            const row = cb.closest('tr');
            if (row) {
                const gvhd = row.getAttribute('data-gvhd');
                const tendt = row.getAttribute('data-tendetai');
                if (gvhd) selectedDeTaiGVHDs.push({ gvhd, tendt });
            }
        });

        let conflicts = [];
        document.querySelectorAll('.select-faculty').forEach(sel => {
            if (!sel.value) return;
            const opt = sel.options[sel.selectedIndex];
            const tenGV = opt?.getAttribute('data-hoten') || '';
            const match = selectedDeTaiGVHDs.find(item => item.gvhd && tenGV && item.gvhd.toLowerCase() === tenGV.toLowerCase());
            if (match) {
                conflicts.push(`Giảng viên <strong>${tenGV}</strong> đang là GVHD của đề tài: <em>"${match.tendt}"</em>.`);
            }
        });

        const box = document.getElementById('conflictWarningBox');
        const text = document.getElementById('conflictWarningText');
        if (conflicts.length > 0) {
            box.classList.remove('d-none');
            text.innerHTML = '<strong>Lưu ý về xung đột lợi ích:</strong> ' + conflicts.join(' ');
        } else {
            box.classList.add('d-none');
        }
    }

    // Xây dựng bảng preview ở Bước 4
    function buildPreviewStep4() {
        // 1. Thông tin HĐ
        document.getElementById('pvTenHoiDong').textContent = document.getElementById('TenHoiDong')?.value || '—';
        const optHK = document.getElementById('MaHocKy')?.options[document.getElementById('MaHocKy').selectedIndex];
        const optNganh = document.getElementById('MaNganh')?.options[document.getElementById('MaNganh').selectedIndex];
        document.getElementById('pvHocKyNganh').textContent = (optHK?.text || '') + ' — ' + (optNganh?.text || '');
        document.getElementById('pvDotBaoVe').textContent = document.getElementById('DotBaoVe')?.value || '—';

        const ngay = document.getElementById('NgayBaoVe')?.value || '';
        const batDau = document.getElementById('ThoiGianBatDau')?.value || '';
        const ketThuc = document.getElementById('ThoiGianKetThuc')?.value || '';
        document.getElementById('pvNgayGio').textContent = ngay + ' (' + (batDau ? batDau.replace('T', ' ') : '') + ' đến ' + (ketThuc ? ketThuc.replace('T', ' ') : '') + ')';
        document.getElementById('pvDiaDiem').textContent = document.getElementById('DiaDiem')?.value || '—';
        document.getElementById('pvGhiChu').textContent = document.getElementById('GhiChu')?.value || 'Không có';

        // 2. Quyết định
        document.getElementById('pvSoQuyetDinh').textContent = document.getElementById('SoQuyetDinh')?.value || 'Chưa nhập';
        document.getElementById('pvNgayBanHanh').textContent = document.getElementById('NgayBanHanh')?.value || 'Hôm nay';
        document.getElementById('pvNguoiKy').textContent = document.getElementById('NguoiKy')?.value || 'Hiệu trưởng';
        document.getElementById('pvChucVuNguoiKy').textContent = document.getElementById('ChucVuNguoiKy')?.value || 'HIỆU TRƯỞNG';

        // 3. Bảng Thành viên HĐ
        const tbodyTV = document.getElementById('pvTbodyThanhVien');
        tbodyTV.innerHTML = '';
        let sttTV = 1;

        // Chủ tịch
        addPreviewFacultyRow(tbodyTV, sttTV++, 'selectChuTich', 'Chủ tịch Hội đồng', 'text-danger fw-bold');
        // Phản biện
        addPreviewFacultyRow(tbodyTV, sttTV++, 'selectPhanBien', 'Ủy viên phản biện', 'text-primary fw-bold');
        // Thư ký
        addPreviewFacultyRow(tbodyTV, sttTV++, 'selectThuKy', 'Ủy viên thư ký', 'text-success fw-bold');

        // Các ủy viên thêm
        document.querySelectorAll('.dong-uy-vien-them').forEach(dong => {
            const roleInput = dong.querySelector('input[type="text"]');
            const sel = dong.querySelector('select');
            if (sel && sel.value) {
                const opt = sel.options[sel.selectedIndex];
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-center">${sttTV++}</td>
                    <td class="fw-semibold">${opt.getAttribute('data-hoten') || opt.text}</td>
                    <td>${opt.getAttribute('data-bomon') || 'Khoa'}</td>
                    <td class="text-secondary fw-bold">${roleInput?.value || 'Ủy viên'}</td>
                `;
                tbodyTV.appendChild(tr);
            }
        });

        // 4. Bảng Đề tài & SV
        const tbodyDT = document.getElementById('pvTbodyDeTai');
        tbodyDT.innerHTML = '';
        let sttDT = 1;

        document.querySelectorAll('.check-item-detai:checked').forEach(cb => {
            const row = cb.closest('tr');
            if (row) {
                const tendt = row.getAttribute('data-tendetai');
                const manhom = row.getAttribute('data-manhom');
                const gvhd = row.getAttribute('data-gvhd');

                // Lấy danh sách SV
                const svContainer = row.querySelector('.text-muted.small');
                const svText = svContainer ? svContainer.innerHTML : '';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-center">${sttDT++}</td>
                    <td class="font-monospace fw-bold text-primary">Nhóm ${manhom}</td>
                    <td class="fw-bold">${tendt}</td>
                    <td>${svText}</td>
                    <td class="fw-semibold">${gvhd}</td>
                `;
                tbodyDT.appendChild(tr);
            }
        });
    }

    function addPreviewFacultyRow(tbody, stt, selectId, defaultRole, badgeClass) {
        const sel = document.getElementById(selectId);
        if (sel && sel.value) {
            const opt = sel.options[sel.selectedIndex];
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center">${stt}</td>
                <td class="fw-semibold">${opt.getAttribute('data-hoten') || opt.text}</td>
                <td>${opt.getAttribute('data-bomon') || 'Khoa'}</td>
                <td class="${badgeClass}">${defaultRole}</td>
            `;
            tbody.appendChild(tr);
        }
    }
</script>
@endpush
