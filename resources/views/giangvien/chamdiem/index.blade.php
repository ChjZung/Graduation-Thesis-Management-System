@extends('layouts.giangvien')

@section('page_title', 'Phiếu Chấm Điểm Khóa Luận Tốt Nghiệp — HUIT')

@section('content')
<div class="container-fluid px-0">


    @if($hoiDongs->isEmpty())
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body text-center py-5">
            <i class="fa-solid fa-inbox fa-3x text-muted mb-3 opacity-50"></i>
            <h5 class="fw-bold text-secondary">Bạn chưa được phân công vào Hội đồng bảo vệ nào</h5>
            <p class="text-muted small mb-0">Khi Giáo vụ thành lập Hội đồng và thêm Thầy/Cô vào danh sách thành viên, các ca bảo vệ sẽ hiển thị tại đây.</p>
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

    <!-- Header Section (Đồng bộ chuẩn Học Kỳ) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;">
                <i class="fa-solid fa-file-signature text-primary me-2"></i>{{ $tenBieuMau }}
            </h4>
            <p class="text-muted small mb-0">
                Thang điểm Rubric chuẩn hóa Khoa CNTT — Trường ĐH Công Thương TP.HCM (Điểm khóa luận 100% do Hội đồng chấm).
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Toggle chọn mẫu KLCN hoặc KLKS -->
            <div class="btn-group btn-group-sm shadow-xs" role="group">
                <a href="{{ route('giangvien.chamdiem.index', ['hd' => $activeHoiDong->MaHoiDong, 'hoso' => $activeHoSo?->MaHoSo, 'loai' => 'KLCN']) }}" 
                   class="btn {{ !$isKLKS ? 'btn-primary fw-bold' : 'btn-outline-primary' }}">
                    <i class="fa-solid fa-graduation-cap me-1"></i> Mẫu Cử Nhân (KLCN)
                </a>
                <a href="{{ route('giangvien.chamdiem.index', ['hd' => $activeHoiDong->MaHoiDong, 'hoso' => $activeHoSo?->MaHoSo, 'loai' => 'KLKS']) }}" 
                   class="btn {{ $isKLKS ? 'btn-primary fw-bold' : 'btn-outline-primary' }}">
                    <i class="fa-solid fa-microchip me-1"></i> Mẫu Kỹ Sư (KLKS)
                </a>
            </div>

            <!-- Nút In Phiếu -->
            <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary shadow-xs">
                <i class="fa-solid fa-print me-1"></i> In Phiếu
            </button>
        </div>
    </div>

    <!-- Thanh Bộ Chọn Hội Đồng & Chọn Nhóm Bảo Vệ -->
    <div class="admin-filter-bar mb-3 p-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-6 col-lg-5">
                <label class="form-label small fw-bold text-secondary mb-1">
                    <i class="fa-solid fa-landmark text-primary me-1"></i> Hội Đồng Bảo Vệ:
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
                    <i class="fa-solid fa-users-rectangle text-success me-1"></i> Nhóm Sinh Viên Bảo Vệ:
                </label>
                <select class="form-select form-select-sm" onchange="location = this.value;">
                    @forelse($activeHoiDong->hoSoBaoVes as $hs)
                        <option value="{{ route('giangvien.chamdiem.index', ['hd' => $activeHoiDong->MaHoiDong, 'hoso' => $hs->MaHoSo, 'loai' => $loaiKhoaLuan]) }}" {{ $activeHoSo && $hs->MaHoSo === $activeHoSo->MaHoSo ? 'selected' : '' }}>
                            {{ $hs->nhom->TenNhom ?? $hs->MaHoSo }} — {{ Str::limit($hs->nhom->deTai->TenDeTai ?? 'Chưa có tên đề tài', 45) }}
                        </option>
                    @empty
                        <option value="">(Chưa có nhóm nào được phân vào Hội đồng này)</option>
                    @endforelse
                </select>
            </div>

            <div class="col-md-12 col-lg-2 text-lg-end pt-lg-3">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-semibold">
                    <i class="fa-solid fa-user-check me-1"></i>{{ $vaiTroGV }}
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
                    <div class="p-2.5 bg-white rounded-3 border">
                        <span class="fw-bold text-dark">Tên đề tài:</span>
                        <span class="text-primary fw-semibold fs-6 ms-1">{{ $activeDeTai->TenDeTai ?? 'Chưa cập nhật tên đề tài' }}</span>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="p-2.5 bg-white rounded-3 border h-100">
                        <div class="text-muted small">Giảng viên hướng dẫn:</div>
                        <div class="fw-bold text-dark mt-1">{{ $activeDeTai->giangVien->HoTen ?? 'Chưa phân công' }}</div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="p-2.5 bg-white rounded-3 border h-100">
                        <div class="text-muted small">Hội đồng số / Phòng:</div>
                        <div class="fw-bold text-dark mt-1">{{ $activeHoiDong->TenHoiDong }} &bull; {{ $activeHoSo->PhongBaoVe ?? ($activeHoiDong->DiaDiem ?? 'Phòng B.304') }}</div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="p-2.5 bg-white rounded-3 border h-100">
                        <div class="text-muted small">Ngày chấm:</div>
                        <div class="fw-bold text-dark mt-1">{{ \Carbon\Carbon::parse($activeHoSo->ThoiGianBaoVe ?? ($activeHoiDong->ThoiGianBatDau ?? now()))->format('d/m/Y H:i') }}</div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="p-2.5 bg-white rounded-3 border h-100">
                        <div class="text-muted small">Giám khảo chấm điểm:</div>
                        <div class="fw-bold text-dark mt-1">{{ $giangVien->HoTen }} ({{ $vaiTroGV }})</div>
                    </div>
                </div>

                <!-- Danh sách sinh viên thực hiện đề tài (SV1, SV2, SV3) -->
                <div class="col-12">
                    <div class="p-3 bg-white rounded-3 border">
                        <div class="fw-bold text-dark small mb-2">
                            <i class="fa-solid fa-user-graduate text-primary me-1"></i> Danh Sách Nhóm Sinh Viên Thực Hiện:
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

        <!-- Bảng Rubric Tiêu Chí Đánh Giá -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="fw-bold text-dark">
                    <i class="fa-solid fa-list-check text-primary me-2"></i>Bảng Tiêu Chí Đánh Giá &amp; Điểm Chấm Cho Từng Sinh Viên (Thang 10.0)
                </span>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1" onclick="dienDiemNhanh(0.9)" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Điểm Mẫu (90%)
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-3 py-1" onclick="dienDiemNhanh(1.0)" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-star me-1"></i> Tối Đa (10.0)
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                            <tr>
                                <th class="text-center py-3" style="width: 50px;">STT</th>
                                <th class="py-3" style="min-width: 320px;">NỘI DUNG ĐÁNH GIÁ</th>
                                <th class="text-center py-3" style="width: 85px;">CLO</th>
                                <th class="text-center py-3" style="width: 100px;">ĐIỂM TỐI ĐA</th>
                                @foreach($sinhViens as $idx => $sv)
                                <th class="text-center py-3" style="width: 130px; background: #f0f7ff;">
                                    <div>SV{{ $idx + 1 }}</div>
                                    <div class="text-primary fw-bold text-truncate" style="font-size: 0.72rem; max-width: 120px;" title="{{ $sv->HoTen }}">
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
                                        <div class="badge bg-warning text-dark mt-1" style="font-size: 0.68rem;">Điểm cộng khuyến khích</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border px-2 py-0.5" style="font-size: 0.72rem;">{{ $criterion['clo'] }}</span>
                                </td>
                                <td class="text-center fw-bold {{ $isBonus ? 'text-warning-emphasis' : 'text-primary' }}">
                                    {{ number_format($cMax, 2) }}
                                </td>

                                {{-- Cột điểm cho từng sinh viên --}}
                                @foreach($sinhViens as $sIdx => $sv)
                                @php
                                    $daChamSV = $diemDaCham->get($sv->MaSV);
                                    $svChiTiet = $daChamSV?->ChiTietDiem;
                                    $oldScore = $svChiTiet[$cId] ?? ($isBonus ? 0.0 : $cMax);
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
                                <td class="text-center py-3" style="background: #dbeafe;">
                                    <div class="fs-5 fw-bold text-primary" id="totalScore_{{ $sv->MaSV }}">
                                        10.00
                                    </div>
                                    <input type="hidden" name="diems[{{ $sv->MaSV }}][Diem]" id="inputTotalScore_{{ $sv->MaSV }}" value="10.00">
                                    <div class="small fw-semibold text-success mt-0.5" id="badgeGrade_{{ $sv->MaSV }}">
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
                                <span><i class="fa-solid fa-user me-1 text-primary"></i> Nhận xét cho SV{{ $idx + 1 }}: <strong>{{ $sv->HoTen }}</strong> ({{ $sv->MaSoSinhVien ?? $sv->MaSV }})</span>
                            </label>
                            <textarea name="diems[{{ $sv->MaSV }}][NhanXet]" rows="3" class="form-control" style="font-size: 0.88rem;"
                                placeholder="Nhập nhận xét về mức độ đóng góp, tính chủ động, khả năng trả lời phản biện của sinh viên...">{{ $daChamSV?->NhanXet ?? 'Sinh viên nắm vững kiến thức chuyên môn, trả lời tốt câu hỏi của Hội đồng. Sản phẩm thực nghiệm hoạt động ổn định và đáp ứng đầy đủ yêu cầu đề tài.' }}</textarea>
                        </div>
                        @endforeach

                        <div class="alert alert-info py-2 px-3 small rounded-3 mb-0 border-0 bg-info-subtle text-info-emphasis">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            <strong>Lưu ý:</strong> Điểm sau khi lưu sẽ được hệ thống tự động cộng dồn với các thành viên khác trong Hội đồng để tính ra điểm trung bình chính thức của nhóm.
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
                            <div class="p-3 rounded-3 border bg-light-subtle" id="cardSummary_{{ $sv->MaSV }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-primary text-white">SV{{ $idx + 1 }}</span>
                                        <strong class="text-dark ms-1">{{ $sv->HoTen }}</strong>
                                        <div class="small text-muted">{{ $sv->MaSoSinhVien ?? $sv->MaSV }} &bull; {{ $sv->lop->TenLop ?? 'Lớp' }}</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="fs-4 fw-bold text-primary lh-1" id="displaySummaryScore_{{ $sv->MaSV }}">10.00</span>
                                        <div class="small text-muted">/ 10.0</div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" id="badgeSummaryResult_{{ $sv->MaSV }}">
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
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Quy Chế Điểm Khóa Luận (HUIT):
                            </div>
                            <div class="small text-secondary" style="font-size: 0.78rem; line-height: 1.4;">
                                Điểm cuối cùng của sinh viên được tính bằng <strong>100% điểm trung bình do Hội đồng bảo vệ chấm</strong>. Không cộng trọng số 30% GVHD hay 30% GVPB.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-3 bg-white rounded-3 border shadow-sm">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="alert('Đã lưu nháp bảng điểm vào phiên làm việc hiện tại!');">
                <i class="fa-regular fa-floppy-disk me-1"></i> Lưu Nháp Điểm
            </button>
            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" style="background: #0072ce; border-color: #0072ce;">
                <i class="fa-solid fa-lock me-1"></i> Ký Xác Nhận &amp; Khóa Phiếu Chấm
            </button>
        </div>
    </form>
    @endif
    @endif
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
        });

        // Cập nhật từng sinh viên
        Object.keys(svMap).forEach(svId => {
            let finalScore = svMap[svId].total + svMap[svId].bonus;
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
                summaryResultEl.className = `badge rounded-pill px-2.5 py-1 ${badgeClass}`;
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

    calculateScores();
});
</script>
@endpush
@endsection
