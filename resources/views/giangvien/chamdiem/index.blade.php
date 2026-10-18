@extends('layouts.giangvien')

@section('page_title', 'Phiếu Chấm Điểm Khóa Luận Tốt Nghiệp — HUIT')

@section('content')
<div class="container-fluid px-0">

    @if($hoiDongs->isEmpty())
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body text-center py-5">
            <i class="fa-solid fa-inbox fa-3x text-muted mb-3 opacity-50"></i>
            <h5 class="fw-bold text-secondary">Bạn chưa được phân công vào Hội đồng bảo vệ nào</h5>
            <p class="text-muted small mb-0">Khi Trưởng khoa thành lập Hội đồng và thêm Thầy/Cô vào danh sách thành viên, các ca bảo vệ sẽ hiển thị tại đây.</p>
        </div>
    </div>
    @else

    @php
        $activeNhom = $activeHoSo?->nhom;
        $activeDeTai = $activeNhom?->deTai;
        $activeThanhViens = $activeNhom?->thanhViens ?? collect();
        $sinhViens = $activeThanhViens->pluck('sinhVien')->filter();

        // Vai trò của giảng viên trong hội đồng này
        $tvRecord = $activeHoiDong?->thanhViens->firstWhere('MaGV', $giangVien->MaGV);
        $vaiTroGV = $tvRecord?->VaiTro ?? 'Thành viên Hội đồng';

        $isKLKS = ($loaiKhoaLuan === 'KLKS');
        $rubric = $isKLKS ? $rubricKLKS : $rubricKLCN;
        $tenBieuMau = $isKLKS ? 'PHIẾU CHẤM ĐIỂM KHÓA LUẬN KỸ SƯ' : 'PHIẾU CHẤM ĐIỂM KHÓA LUẬN CỬ NHÂN';
    @endphp

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-0" style="color: #00305a;">
                <i class="fa-solid fa-file-signature text-primary me-2"></i>{{ $tenBieuMau }}
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Toggle chọn mẫu KLCN hoặc KLKS -->
            <div class="btn-group btn-group-sm shadow-xs" role="group">
                <a href="{{ route('giangvien.chamdiem.index', ['hd' => $activeHoiDong->MaHoiDong, 'hoso' => $activeHoSo?->MaHoSo, 'loai' => 'KLCN']) }}" 
                   class="btn {{ !$isKLKS ? 'btn-primary fw-bold' : 'btn-outline-primary' }}">
                    <i class="fa-solid fa-graduation-cap me-2"></i> Mẫu Cử Nhân (KLCN)
                </a>
                <a href="{{ route('giangvien.chamdiem.index', ['hd' => $activeHoiDong->MaHoiDong, 'hoso' => $activeHoSo?->MaHoSo, 'loai' => 'KLKS']) }}" 
                   class="btn {{ $isKLKS ? 'btn-primary fw-bold' : 'btn-outline-primary' }}">
                    <i class="fa-solid fa-microchip me-2"></i> Mẫu Kỹ Sư (KLKS)
                </a>
            </div>

            <!-- Nút In Phiếu -->
            <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary shadow-xs d-inline-flex align-items-center">
                <i class="fa-solid fa-print me-2"></i> In Phiếu
            </button>
        </div>
    </div>

    @if(!empty($canhBaoChuaXacDinhLoai))
    <div class="alert alert-warning border-warning rounded-3 shadow-sm py-2 px-3 mb-3 d-flex align-items-center justify-content-between">
        <div class="small">
            <i class="fa-solid fa-triangle-exclamation me-2 fs-6"></i>
            <strong>Cảnh báo phân loại khóa luận:</strong> Hệ thống chưa tự động nhận diện được loại khóa luận (Cử nhân hay Kỹ sư) từ tên học phần hoặc ngành của nhóm này. Vui lòng kiểm tra lại với Trưởng khoa trước khi chốt phiếu điểm.
        </div>
    </div>
    @endif

    @if($daKhoaPhieu)
    <div class="alert alert-success border-success rounded-3 shadow-sm py-3 px-4 mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-check fs-4 text-success"></i>
            <div>
                <strong class="d-block text-success-emphasis">Phiếu chấm điểm đã được KÝ XÁC NHẬN & KHÓA</strong>
                <span class="small text-secondary">
                    Thời điểm ký: <strong>{{ \Carbon\Carbon::parse($phieuCu->NgayXacNhan ?? $phieuCu->updated_at)->format('d/m/Y H:i') }}</strong>. 
                    Dữ liệu đã được bảo đảm toàn vẹn và chuyển sang bảng tổng hợp điểm sơ bộ của Hội đồng.
                </span>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalYeuCauMoKhoa">
            <i class="fa-solid fa-unlock-keyhole me-2"></i> Yêu Cầu Mở Khóa Chỉnh Sửa
        </button>
    </div>
    @endif

    <!-- Thanh Bộ Chọn Hội Đồng & Chọn Nhóm Bảo Vệ -->
    <div class="admin-filter-bar mb-3 p-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-6 col-lg-5">
                <label class="form-label small fw-bold text-secondary mb-1">
                    <i class="fa-solid fa-landmark text-primary me-2"></i> Hội Đồng Bảo Vệ:
                </label>
                <select class="form-select form-select-sm" onchange="location = this.value;">
                    @foreach($hoiDongs as $hd)
                        <option value="{{ route('giangvien.chamdiem.index', ['hd' => $hd->MaHoiDong, 'loai' => $loaiKhoaLuan]) }}" {{ $activeHoiDong && $hd->MaHoiDong === $activeHoiDong->MaHoiDong ? 'selected' : '' }}>
                            {{ $hd->TenHoiDong }} ({{ $hd->DiaDiem ?? 'Phòng B.304' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 col-lg-5">
                <label class="form-label small fw-bold text-secondary mb-1">
                    <i class="fa-solid fa-users-rectangle text-success me-2"></i> Nhóm Sinh Viên Bảo Vệ:
                </label>
                <select class="form-select form-select-sm" onchange="location = this.value;">
                    @forelse($activeHoiDong->hoSoBaoVes as $hs)
                        <option value="{{ route('giangvien.chamdiem.index', ['hd' => $activeHoiDong->MaHoiDong, 'hoso' => $hs->MaHoSo, 'loai' => $loaiKhoaLuan]) }}" {{ $activeHoSo && $hs->MaHoSo === $activeHoSo->MaHoSo ? 'selected' : '' }}>
                            {{ $hs->nhom->TenNhom ?? $hs->MaHoSo }} — {{ Str::limit($hs->nhom->deTai->TenDeTai ?? 'Chưa có tên đề tài', 45) }}
                        </option>
                    @empty
                        <option value="">Chưa có nhóm nào trong hội đồng này</option>
                    @endforelse
                </select>
            </div>

            <div class="col-md-12 col-lg-2 text-lg-end pt-lg-3">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                    <i class="fa-solid fa-user-check me-2"></i>{{ $vaiTroGV }}
                </span>
            </div>
        </div>
    </div>

    @if(!$activeHoSo)
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center text-muted">
        <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
        <h6>Hội đồng này hiện chưa có nhóm sinh viên nào được phân công bảo vệ.</h6>
    </div>
    @else

    <!-- Card Thông Tin Phiếu Chấm Điểm Chuẩn Văn Bản Khoa CNTT -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-header bg-white border-bottom py-3">
            <div class="text-center">
                <div class="text-uppercase fw-bold text-secondary small" style="letter-spacing: 1px;">KHOA CÔNG NGHỆ THÔNG TIN &bull; TRƯỜNG ĐH CÔNG THƯƠNG TP.HCM</div>
                <h5 class="fw-bold mb-1 text-primary mt-1">{{ $tenBieuMau }}</h5>
                <div class="text-muted small fst-italic">(Hướng ứng dụng — Thang điểm 10.0)</div>
            </div>
        </div>
        <div class="card-body p-4 bg-light-subtle">
            <div class="row g-3">
                <div class="col-12">
                    <div class="p-2 bg-white rounded-3 border">
                        <span class="fw-bold text-dark">Tên đề tài:</span>
                        <span class="text-primary fw-semibold fs-6 ms-1">{{ $activeDeTai->TenDeTai ?? 'Chưa cập nhật tên đề tài' }}</span>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="p-2 bg-white rounded-3 border h-100">
                        <div class="text-muted small">Giảng viên hướng dẫn:</div>
                        <div class="fw-bold text-dark mt-1">{{ $activeDeTai->giangVien->HoTen ?? 'Chưa phân công' }}</div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="p-2 bg-white rounded-3 border h-100">
                        <div class="text-muted small">Hội đồng số / Phòng:</div>
                        <div class="fw-bold text-dark mt-1">{{ $activeHoiDong->TenHoiDong }} &bull; {{ $activeHoSo->PhongBaoVe ?? ($activeHoiDong->DiaDiem ?? 'Phòng B.304') }}</div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="p-2 bg-white rounded-3 border h-100">
                        <div class="text-muted small">Ngày chấm:</div>
                        <div class="fw-bold text-dark mt-1">{{ \Carbon\Carbon::parse($activeHoSo->ThoiGianBaoVe ?? ($activeHoiDong->ThoiGianBatDau ?? now()))->format('d/m/Y H:i') }}</div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="p-2 bg-white rounded-3 border h-100">
                        <div class="text-muted small">Giám khảo chấm điểm:</div>
                        <div class="fw-bold text-dark mt-1">{{ $giangVien->HoTen }} ({{ $vaiTroGV }})</div>
                    </div>
                </div>

                <!-- Danh sách sinh viên thực hiện đề tài (SV1, SV2, SV3) -->
                <div class="col-12">
                    <div class="p-3 bg-white rounded-3 border">
                        <div class="fw-bold text-dark small mb-2">
                            <i class="fa-solid fa-user-graduate text-primary me-2"></i> Danh Sách Nhóm Sinh Viên Thực Hiện:
                        </div>
                        <div class="row g-2">
                            @forelse($sinhViens as $idx => $sv)
                            <div class="col-md-4">
                                <div class="p-2 bg-light rounded-2 border">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-primary text-white">SV{{ $idx + 1 }}</span>
                                        <span class="badge-code">{{ $sv->MaSoSinhVien ?? $sv->MaSV }}</span>
                                    </div>
                                    <div class="fw-bold text-dark mt-1">{{ $sv->HoTen }}</div>
                                    <div class="text-muted small">Lớp: {{ $sv->lop->TenLop ?? '14DHTH01' }}</div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-muted small fst-italic">Chưa có danh sách sinh viên trong nhóm.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FORM NHẬP ĐIỂM THEO RUBRIC CHUẨN KHOA CNTT -->
    <form id="formChamDiem" action="{{ route('giangvien.chamdiem.store') }}" method="POST">
        @csrf
        <input type="hidden" name="MaHoiDong" value="{{ $activeHoiDong->MaHoiDong }}">
        <input type="hidden" name="MaHoSo" value="{{ $activeHoSo->MaHoSo }}">
        <input type="hidden" name="LoaiKhoaLuan" value="{{ $loaiKhoaLuan }}">
        <input type="hidden" name="action_type" id="inputActionType" value="xac_nhan_hoan_thanh">

        <!-- Bảng Rubric Tiêu Chí Đánh Giá -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="fw-bold text-dark">
                    <i class="fa-solid fa-list-check text-primary me-2"></i>Bảng Tiêu Chí Đánh Giá &amp; Điểm Chấm Cho Từng Sinh Viên (Thang 10.0)
                </span>
                @if(!$daKhoaPhieu)
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 d-inline-flex align-items-center" onclick="dienDiemNhanh(0.9)" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Điểm Mẫu (90%)
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-3 py-1 d-inline-flex align-items-center" onclick="dienDiemNhanh(1.0)" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-star me-2"></i> Tối Đa (10.0)
                    </button>
                </div>
                @endif
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">STT</th>
                                <th style="min-width: 320px;">NỘI DUNG ĐÁNH GIÁ</th>
                                <th class="text-center" style="width: 85px;">CLO</th>
                                <th class="text-center" style="width: 100px;">ĐIỂM TỐI ĐA</th>
                                @foreach($sinhViens as $idx => $sv)
                                <th class="text-center" style="width: 130px;">
                                    <div>SV{{ $idx + 1 }}</div>
                                    <div class="text-white fw-bold text-truncate" style="font-size: 0.72rem; max-width: 120px;" title="{{ $sv->HoTen }}">
                                        {{ $sv->HoTen }}
                                    </div>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @foreach($rubric as $criterion)
                            @php
                                $isBonus = !empty($criterion['is_bonus']);
                                $cId = $criterion['id'];
                                $cMax = (float)$criterion['max'];
                            @endphp
                            <tr class="{{ $isBonus ? 'table-warning-subtle' : '' }}">
                                <td class="text-center fw-bold {{ $isBonus ? 'text-warning-emphasis' : 'text-secondary' }}">
                                    {{ $criterion['stt'] }}
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark" style="font-size: 0.85rem;">
                                        {{ $criterion['noi_dung'] }}
                                    </div>
                                    @if($isBonus)
                                        <div class="badge bg-warning text-dark mt-1" style="font-size: 0.68rem;">Điểm cộng khuyến khích (NCKH / Bài báo)</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.72rem;">{{ $criterion['clo'] }}</span>
                                </td>
                                <td class="text-center fw-bold {{ $isBonus ? 'text-warning-emphasis' : 'text-primary' }}">
                                    {{ number_format($cMax, 2) }}
                                </td>

                                {{-- Cột điểm cho từng sinh viên --}}
                                @foreach($sinhViens as $sIdx => $sv)
                                @php
                                    $daChamSV = $diemDaCham->get($sv->MaSV);
                                    $svChiTiet = $daChamSV?->ChiTietDiem;
                                    $oldScore = isset($svChiTiet[$cId]) ? (float)$svChiTiet[$cId] : ($isBonus ? 0.0 : $cMax);
                                @endphp
                                <td class="text-center p-2" style="background: #f8fbff;">
                                    <input type="number" 
                                           step="0.05" 
                                           min="0" 
                                           max="{{ $cMax }}" 
                                           name="diems[{{ $sv->MaSV }}][ChiTietDiem][{{ $cId }}]" 
                                           data-max="{{ $cMax }}" 
                                           data-sv="{{ $sv->MaSV }}" 
                                           data-bonus="{{ $isBonus ? '1' : '0' }}" 
                                           class="form-control form-control-sm text-center fw-bold text-primary score-criterion score-sv-{{ $sv->MaSV }}" 
                                           value="{{ number_format((float)$oldScore, 2) }}" 
                                           {{ $daKhoaPhieu ? 'readonly' : '' }}
                                           required>
                                </td>
                                @endforeach
                            </tr>
                            @endforeach

                            {{-- Hàng TỔNG CỘNG --}}
                            <tr style="background: #e6f2ff; border-top: 2px solid #0072ce;">
                                <td colspan="3" class="text-end fw-bold text-dark py-3 fs-6">
                                    <i class="fa-solid fa-calculator text-primary me-2"></i>TỔNG CỘNG (Thang Điểm 10.0):
                                </td>
                                <td class="text-center fw-bold text-primary py-3 fs-6">
                                    10.00
                                </td>
                                @foreach($sinhViens as $sIdx => $sv)
                                @php
                                    $daChamSV = $diemDaCham->get($sv->MaSV);
                                    $curDiem = $daChamSV ? (float)$daChamSV->Diem : 10.0;
                                @endphp
                                <td class="text-center py-3" style="background: #dbeafe;">
                                    <div class="fs-5 fw-bold text-primary" id="totalScore_{{ $sv->MaSV }}">
                                        {{ number_format($curDiem, 2) }}
                                    </div>
                                    <input type="hidden" name="diems[{{ $sv->MaSV }}][Diem]" id="inputTotalScore_{{ $sv->MaSV }}" value="{{ number_format($curDiem, 2) }}">
                                    <div class="small fw-semibold text-success mt-1" id="badgeGrade_{{ $sv->MaSV }}">
                                        Điểm chữ: A+ (4.0)
                                    </div>
                                </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Nhận xét của Giám khảo & Thẻ Tổng Hợp Điểm Realtime -->
        <div class="row g-4 mb-4">
            <!-- Cột trái: Nhận xét chi tiết của Giám khảo -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <span class="fw-bold text-dark">
                            <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Nhận Xét Chi Tiết Của Giám Khảo Chấm Điểm
                        </span>
                    </div>
                    <div class="card-body p-3">
                        @foreach($sinhViens as $idx => $sv)
                        @php
                            $daChamSV = $diemDaCham->get($sv->MaSV);
                        @endphp
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark d-flex justify-content-between align-items-center">
                                <span><i class="fa-solid fa-user me-2 text-primary"></i> Nhận xét cho SV{{ $idx + 1 }}: <strong>{{ $sv->HoTen }}</strong> ({{ $sv->MaSoSinhVien ?? $sv->MaSV }})</span>
                            </label>
                            <textarea name="diems[{{ $sv->MaSV }}][NhanXet]" rows="3" class="form-control" style="font-size: 0.88rem;"
                                {{ $daKhoaPhieu ? 'readonly' : '' }}
                                placeholder="Nhập nhận xét về mức độ đóng góp, tính chủ động, khả năng trả lời phản biện của sinh viên...">{{ $daChamSV?->NhanXet ?? 'Sinh viên nắm vững kiến thức chuyên môn, trả lời tốt câu hỏi của Hội đồng. Sản phẩm thực nghiệm hoạt động ổn định và đáp ứng đầy đủ yêu cầu đề tài.' }}</textarea>
                        </div>
                        @endforeach

                        <div class="alert alert-info py-2 px-3 small rounded-3 mb-0 border-0 bg-info-subtle text-info-emphasis">
                            <i class="fa-solid fa-circle-info me-2"></i>
                            <strong>Lưu ý:</strong> Điểm của Thầy/Cô sau khi Ký xác nhận sẽ được tổng hợp tự động vào bảng điểm trung bình Hội đồng và gửi báo cáo về Trưởng khoa để chuẩn bị công bố chính thức.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Bảng Thống Kê Tổng Kết Học Lực Realtime -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <span class="fw-bold text-dark">
                            <i class="fa-solid fa-award text-primary me-2"></i>Tổng Hợp Kết Quả Đánh Giá Realtime
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex flex-column gap-3">
                            @foreach($sinhViens as $idx => $sv)
                            @php
                                $daChamSV = $diemDaCham->get($sv->MaSV);
                                $curDiem = $daChamSV ? (float)$daChamSV->Diem : 10.0;
                            @endphp
                            <div class="p-3 rounded-3 border bg-light-subtle" id="cardSummary_{{ $sv->MaSV }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-primary text-white">SV{{ $idx + 1 }}</span>
                                        <strong class="text-dark ms-1">{{ $sv->HoTen }}</strong>
                                        <div class="small text-muted">{{ $sv->MaSoSinhVien ?? $sv->MaSV }} &bull; {{ $sv->lop->TenLop ?? 'Lớp' }}</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="fs-4 fw-bold text-primary lh-1" id="displaySummaryScore_{{ $sv->MaSV }}">{{ number_format($curDiem, 2) }}</span>
                                        <div class="small text-muted">/ 10.0</div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1" id="badgeSummaryResult_{{ $sv->MaSV }}">
                                        ĐẠT (Xuất sắc)
                                    </span>
                                    <span class="small fw-semibold text-secondary" id="badgeSummaryScale_{{ $sv->MaSV }}">
                                        Điểm chữ: A+ | Hệ 4: 4.0
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="p-3 bg-warning-subtle border border-warning-subtle rounded-3 mt-3">
                            <div class="small fw-bold text-warning-emphasis mb-1">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i> Quy Chế Điểm Khóa Luận (HUIT):
                            </div>
                            <div class="small text-secondary" style="font-size: 0.78rem; line-height: 1.4;">
                                Điểm cuối cùng của sinh viên được tính bằng <strong>100% điểm trung bình do Hội đồng bảo vệ chấm</strong>. Các phiếu chấm chỉ được tính khi đã Ký xác nhận &amp; Khóa.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-3 bg-white rounded-3 border shadow-sm">
            @if($daKhoaPhieu)
                <div class="d-flex align-items-center text-success fw-bold">
                    <i class="fa-solid fa-lock me-2"></i> Phiếu chấm đã khóa bảo vệ toàn vẹn dữ liệu
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalXemTruoc">
                        <i class="fa-solid fa-eye me-2"></i> Xem Lại Phiếu Chấm Gốc
                    </button>
                    <button type="button" class="btn btn-outline-warning rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalYeuCauMoKhoa">
                        <i class="fa-solid fa-key me-2"></i> Yêu Cầu Mở Khóa
                    </button>
                </div>
            @else
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 d-inline-flex align-items-center" onclick="submitChamDiem('luu_nhap')">
                        <i class="fa-regular fa-floppy-disk me-2"></i> Lưu Nháp Điểm
                    </button>
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4 d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalXemTruoc">
                        <i class="fa-solid fa-eye me-2"></i> Xem Trước Phiếu Chấm
                    </button>
                </div>
                <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm d-inline-flex align-items-center" onclick="submitChamDiem('xac_nhan_hoan_thanh')" style="background: #0072ce; border-color: #0072ce;">
                    <i class="fa-solid fa-lock me-2"></i> Ký Xác Nhận &amp; Khóa Phiếu Chấm
                </button>
            @endif
        </div>
    </form>
    @endif
    @endif
</div>

{{-- MODAL XEM TRƯỚC PHIẾU CHẤM --}}
<div class="modal fade" id="modalXemTruoc" tabindex="-1" aria-labelledby="modalXemTruocLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-light border-bottom py-3">
                <h6 class="modal-title fw-bold text-primary" id="modalXemTruocLabel">
                    <i class="fa-solid fa-file-lines me-2"></i> Xem Trước Phiếu Chấm Điểm Chính Thức
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="text-center mb-4">
                    <div class="text-uppercase fw-bold text-secondary small" style="letter-spacing: 1px;">KHOA CÔNG NGHỆ THÔNG TIN &bull; TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP.HCM</div>
                    <h5 class="fw-bold mb-1 text-primary mt-1">{{ $tenBieuMau ?? '' }}</h5>
                    <div class="text-muted small fst-italic">(Bản xem trước biểu mẫu chấm bảo vệ khóa luận)</div>
                </div>

                <div class="p-3 bg-light rounded-3 border mb-3 small">
                    <div class="row g-2">
                        <div class="col-12"><strong>Đề tài:</strong> {{ $activeDeTai->TenDeTai ?? '' }}</div>
                        <div class="col-md-4"><strong>Hội đồng:</strong> {{ $activeHoiDong->TenHoiDong ?? '' }}</div>
                        <div class="col-md-4"><strong>Phòng:</strong> {{ $activeHoSo->PhongBaoVe ?? ($activeHoiDong->DiaDiem ?? 'Phòng B.304') }}</div>
                        <div class="col-md-4"><strong>Giám khảo:</strong> {{ $giangVien->HoTen }} ({{ $vaiTroGV ?? '' }})</div>
                    </div>
                </div>

                @if(isset($sinhViens) && $sinhViens->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle text-center mb-0" style="font-size: 0.8rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">STT</th>
                                <th class="text-start" style="min-width: 250px;">Nội dung đánh giá</th>
                                <th style="width: 60px;">CLO</th>
                                <th style="width: 70px;">Tối đa</th>
                                @foreach($sinhViens as $idx => $sv)
                                <th style="width: 100px;">{{ $sv->HoTen }} (SV{{ $idx + 1 }})</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(($rubric ?? []) as $cr)
                            <tr>
                                <td>{{ $cr['stt'] }}</td>
                                <td class="text-start">{{ $cr['noi_dung'] }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $cr['clo'] }}</span></td>
                                <td class="fw-bold">{{ number_format($cr['max'], 2) }}</td>
                                @foreach($sinhViens as $idx => $sv)
                                <td class="preview-cell-{{ $sv->MaSV }}-{{ $cr['id'] }} fw-semibold text-primary">0.00</td>
                                @endforeach
                            </tr>
                            @endforeach
                            <tr class="table-primary fw-bold">
                                <td colspan="3" class="text-end">TỔNG ĐIỂM (10.0):</td>
                                <td>10.00</td>
                                @foreach($sinhViens as $idx => $sv)
                                <td class="preview-total-{{ $sv->MaSV }} fs-6 text-primary">10.00</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted small fst-italic">
                    Chưa có danh sách sinh viên thực hiện để hiển thị bảng điểm xem trước.
                </div>
                @endif

                <div class="mt-4 row">
                    <div class="col-6 text-center text-muted small">
                        <em>(Bản lưu điện tử hệ thống)</em>
                    </div>
                    <div class="col-6 text-center">
                        <div class="small text-muted mb-1">TP. Hồ Chí Minh, ngày {{ now()->format('d/m/Y') }}</div>
                        <div class="fw-bold text-dark small text-uppercase">Giám khảo chấm điểm</div>
                        <div class="mt-4 pt-3 fw-bold text-primary">{{ $giangVien->HoTen }}</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                @if(!$daKhoaPhieu)
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold" onclick="submitChamDiem('xac_nhan_hoan_thanh')">
                    <i class="fa-solid fa-lock me-2"></i> Tiến Hành Ký Xác Nhận & Khóa
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODAL YÊU CẦU MỞ KHÓA PHIẾU CHẤM --}}
<div class="modal fade" id="modalYeuCauMoKhoa" tabindex="-1" aria-labelledby="modalYeuCauMoKhoaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('giangvien.chamdiem.yeuCauMoKhoa') }}" method="POST">
            @csrf
            <input type="hidden" name="MaHoiDong" value="{{ $activeHoiDong->MaHoiDong ?? '' }}">
            <input type="hidden" name="MaHoSo" value="{{ $activeHoSo->MaHoSo ?? '' }}">

            <div class="modal-content border-0 shadow rounded-3">
                <div class="modal-header bg-warning-subtle text-warning-emphasis border-bottom py-3">
                    <h6 class="modal-title fw-bold" id="modalYeuCauMoKhoaLabel">
                        <i class="fa-solid fa-unlock-keyhole me-2"></i> Gửi Yêu Cầu Mở Khóa Phiếu Chấm Điểm
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <p class="small text-secondary mb-3">
                        Sau khi phiếu chấm đã Ký xác nhận, việc chỉnh sửa phải có sự phê duyệt của Trưởng khoa nhằm đảm bảo tính minh bạch và toàn vẹn dữ liệu.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Lý do yêu cầu điều chỉnh điểm <span class="text-danger">*</span>:</label>
                        <textarea name="LyDo" rows="4" class="form-control" placeholder="Ví dụ: Nhập nhầm điểm tiêu chí 7, cập nhật thêm minh chứng điểm khuyến khích NCKH..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-warning rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-paper-plane me-2"></i> Gửi Yêu Cầu Đến Trưởng Khoa
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const svInputs = document.querySelectorAll('.score-criterion');

    function calculateScores() {
        const svMap = {};

        // Nhóm inputs theo sinh viên
        svInputs.forEach(input => {
            const svId = input.getAttribute('data-sv');
            if (!svMap[svId]) svMap[svId] = { total: 0, bonus: 0 };

            let val = parseFloat(input.value) || 0;
            const maxVal = parseFloat(input.getAttribute('data-max')) || 0;
            const isBonus = input.getAttribute('data-bonus') === '1';

            if (val > maxVal) {
                val = maxVal;
                input.value = maxVal.toFixed(2);
            }
            if (val < 0) {
                val = 0;
                input.value = '0.00';
            }

            if (isBonus) {
                svMap[svId].bonus += val;
            } else {
                svMap[svId].total += val;
            }

            // Đồng bộ dữ liệu sang Modal Xem Trước
            const nameMatch = input.getAttribute('name').match(/ChiTietDiem\]\[([^\]]+)\]/);
            if (nameMatch) {
                const cId = nameMatch[1];
                const previewCell = document.querySelector(`.preview-cell-${svId}-${cId}`);
                if (previewCell) {
                    previewCell.textContent = val.toFixed(2);
                }
            }
        });

        // Cập nhật từng sinh viên
        Object.keys(svMap).forEach(svId => {
            let bonus = svMap[svId].bonus;
            if (bonus > 1.0) bonus = 1.0;

            let finalScore = svMap[svId].total + bonus;
            // Tổng không vượt quá 10.0
            if (finalScore > 10.0) finalScore = 10.0;
            const formatted = finalScore.toFixed(2);

            const totalEl = document.getElementById('totalScore_' + svId);
            const inputEl = document.getElementById('inputTotalScore_' + svId);
            const badgeGradeEl = document.getElementById('badgeGrade_' + svId);
            const summaryScoreEl = document.getElementById('displaySummaryScore_' + svId);
            const summaryResultEl = document.getElementById('badgeSummaryResult_' + svId);
            const summaryScaleEl = document.getElementById('badgeSummaryScale_' + svId);

            if (totalEl) totalEl.textContent = formatted;
            if (inputEl) inputEl.value = formatted;
            if (summaryScoreEl) summaryScoreEl.textContent = formatted;

            const previewTotal = document.querySelector(`.preview-total-${svId}`);
            if (previewTotal) previewTotal.textContent = formatted;

            // Tính điểm chữ, hệ 4 và xếp loại
            let letter = 'F';
            let scale4 = '0.0';
            let xepLoai = 'Không đạt';
            let badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';

            if (finalScore >= 9.0) {
                letter = 'A+'; scale4 = '4.0'; xepLoai = 'ĐẠT (Xuất sắc)';
                badgeClass = 'bg-success-subtle text-success border border-success-subtle';
            } else if (finalScore >= 8.5) {
                letter = 'A'; scale4 = '3.7'; xepLoai = 'ĐẠT (Giỏi)';
                badgeClass = 'bg-success-subtle text-success border border-success-subtle';
            } else if (finalScore >= 8.0) {
                letter = 'B+'; scale4 = '3.5'; xepLoai = 'ĐẠT (Khá giỏi)';
                badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
            } else if (finalScore >= 7.0) {
                letter = 'B'; scale4 = '3.0'; xepLoai = 'ĐẠT (Khá)';
                badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
            } else if (finalScore >= 6.5) {
                letter = 'C+'; scale4 = '2.5'; xepLoai = 'ĐẠT (Trung bình khá)';
                badgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
            } else if (finalScore >= 5.5) {
                letter = 'C'; scale4 = '2.0'; xepLoai = 'ĐẠT (Trung bình)';
                badgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
            } else if (finalScore >= 5.0) {
                letter = 'D+'; scale4 = '1.5'; xepLoai = 'ĐẠT (Trung bình yếu)';
                badgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
            } else if (finalScore >= 4.0) {
                letter = 'D'; scale4 = '1.0'; xepLoai = 'ĐẠT (Yếu)';
                badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
            }

            if (badgeGradeEl) badgeGradeEl.textContent = `Điểm chữ: ${letter} (${scale4})`;
            if (summaryScaleEl) summaryScaleEl.textContent = `Điểm chữ: ${letter} | Hệ 4: ${scale4}`;
            if (summaryResultEl) {
                summaryResultEl.textContent = xepLoai;
                summaryResultEl.className = `badge rounded-pill px-3 py-1 ${badgeClass}`;
            }
        });
    }

    svInputs.forEach(input => {
        input.addEventListener('input', calculateScores);
    });

    window.dienDiemNhanh = function(ratio) {
        svInputs.forEach(input => {
            const maxVal = parseFloat(input.getAttribute('data-max')) || 0;
            const isBonus = input.getAttribute('data-bonus') === '1';
            if (isBonus) {
                input.value = '0.00';
            } else {
                input.value = (maxVal * ratio).toFixed(2);
            }
        });
        calculateScores();
    };

    window.submitChamDiem = function(actionType) {
        const form = document.getElementById('formChamDiem');
        document.getElementById('inputActionType').value = actionType;

        if (actionType === 'xac_nhan_hoan_thanh') {
            if (!confirm('Thầy/Cô có chắc chắn muốn KÝ XÁC NHẬN & KHÓA phiếu chấm điểm này?\n\nSau khi khóa, phiếu sẽ được bảo đảm toàn vẹn và chuyển sang bảng tổng hợp điểm Hội đồng. Việc sửa đổi sau này phải thông qua Trưởng khoa.')) {
                return;
            }
        }
        form.submit();
    };

    calculateScores();
});
</script>
@endpush
@endsection
