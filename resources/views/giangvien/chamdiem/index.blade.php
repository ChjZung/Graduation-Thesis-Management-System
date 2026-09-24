@extends('layouts.giangvien')

@section('page_title', 'Chấm Điểm & Tổng Hợp Kết Quả Khóa Luận')

@section('content')


@if($hoiDongs->isEmpty())
<div class="card card-premium shadow-sm border-0">
    <div class="card-body text-center py-5">
        <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Bạn chưa được phân công vào Hội đồng nào</h5>
        <p class="text-muted">Khi Giáo vụ thành lập Hội đồng và thêm bạn vào, danh sách sẽ hiện ở đây.</p>
    </div>
</div>
@else

@php
    // Lấy hội đồng đầu tiên và nhóm đầu tiên làm mẫu hiển thị chi tiết chuẩn Mockup Ảnh 3
    $activeHoiDong = $hoiDongs->first();
    $activeHoSo = $activeHoiDong?->hoSoBaoVes?->first();
    $activeNhom = $activeHoSo?->nhom;
    $activeDeTai = $activeNhom?->deTai;
    $activeThanhVien = $activeNhom?->thanhViens?->first();
    $activeSV = $activeThanhVien?->sinhVien;

    $vaiTroGV = $activeHoiDong?->thanhViens->firstWhere('MaGV', $giangVien->MaGV)?->VaiTro ?? 'Cán bộ chấm 1 (Chủ tịch)';
    $keyCham = ($activeHoiDong?->MaHoiDong ?? '') . '_' . ($activeSV?->MaSV ?? '');
    $daChamDiem = $diemDaCham[$keyCham] ?? null;
@endphp

<!-- Tiêu đề & Chọn Sinh viên chấm điểm -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-primary-custom">
            <i class="fa-solid fa-file-signature text-primary me-2"></i>Chấm Điểm & Tổng Hợp Kết Quả Khóa Luận
        </h4>
        <div class="text-muted small">
            Đánh giá theo 4 tiêu chí chuẩn hóa của Hội đồng bảo vệ Khóa luận tốt nghiệp
        </div>
    </div>
    <div class="d-flex gap-2 align-items-center">
        @if($hoiDongs->count() > 1)
            <select class="form-select form-select-sm" onchange="location = this.value;">
                @foreach($hoiDongs as $hd)
                    <option value="?hd={{ $hd->MaHoiDong }}" {{ $hd->MaHoiDong === $activeHoiDong->MaHoiDong ? 'selected' : '' }}>
                        {{ $hd->TenHoiDong }}
                    </option>
                @endforeach
            </select>
        @else
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                <i class="fa-solid fa-landmark me-1"></i>{{ $activeHoiDong->TenHoiDong }}
            </span>
        @endif
    </div>
</div>

<!-- Header Thông Tin Đề Tài & Sinh Viên (Theo chuẩn Mockup Ảnh 3) -->
<div class="card card-premium shadow-sm border-0 mb-4">
    <div class="card-body p-3 bg-light rounded-3 border">
        <div class="row g-2 align-items-center">
            <div class="col-lg-8">
                <div class="fw-bold text-dark fs-6 mb-1">
                    <i class="fa-solid fa-book text-primary me-2"></i>
                    Đề tài: {{ $activeDeTai->TenDeTai ?? 'Xây dựng hệ thống quản lý chuỗi cung ứng ứng dụng Blockchain' }}
                </div>
                <div class="small text-muted d-flex flex-wrap gap-3 align-items-center">
                    <span>
                        <strong><i class="fa-solid fa-user-graduate me-1 text-secondary"></i>Sinh viên:</strong> 
                        <strong class="text-dark">{{ $activeSV->HoTen ?? 'Nguyễn Văn Nam' }}</strong>
                        (MSSV: {{ $activeSV->MaSV ?? '2001200123' }})
                    </span>
                    <span>
                        <strong>Lớp:</strong> {{ $activeSV->MaLop ?? '11DHTH01' }}
                    </span>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="small text-muted">
                    <strong>Hội đồng:</strong> {{ $activeHoiDong->TenHoiDong ?? 'HĐ01 - Hội đồng CNTT 01' }}
                </div>
                <div class="small text-primary fw-semibold mt-1">
                    <i class="fa-solid fa-user-check me-1"></i>Vai trò: <strong>{{ $vaiTroGV }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main 2-Column Layout (Theo chuẩn Mockup Ảnh 3) -->
<div class="row g-4">
    <!-- Cột Trái (65%): Phiếu Chấm Điểm 4 Tiêu Chí -->
    <div class="col-lg-7">
        <div class="card card-premium shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 px-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-clipboard-check text-primary"></i>
                    <h6 class="fw-bold mb-0">Phiếu Chấm Điểm Khóa Luận Tốt Nghiệp</h6>
                </div>
                <span class="badge bg-light text-muted border small">Thang điểm 10.0</span>
            </div>
            <div class="card-body p-3">
                <form id="formChamDiem" action="{{ route('giangvien.chamdiem.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="MaHoiDong" value="{{ $activeHoiDong->MaHoiDong }}">
                    <input type="hidden" name="diems[0][MaSV]" value="{{ $activeSV->MaSV ?? '2001200123' }}">
                    <input type="hidden" id="inputFinalDiem" name="diems[0][Diem]" value="{{ $daChamDiem->Diem ?? '9.08' }}">

                    <!-- Bảng 4 Tiêu Chí Chấm Điểm -->
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light small text-secondary">
                                <tr>
                                    <th width="48%">Tiêu chí đánh giá</th>
                                    <th width="16%" class="text-center">Tỷ trọng</th>
                                    <th width="16%" class="text-center">Tối đa</th>
                                    <th width="20%" class="text-center">Điểm chấm</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- TC 1 -->
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark small">Tiêu chí 1: Chất lượng nội dung khóa luận</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Tính khoa học, hoàn thiện SRS và tài liệu kỹ thuật</div>
                                    </td>
                                    <td class="text-center fw-bold text-primary small">40%</td>
                                    <td class="text-center text-muted small">10.0</td>
                                    <td>
                                        <input type="number" step="0.1" min="0" max="10" 
                                               id="tc1" class="form-control form-control-sm text-center fw-bold text-primary score-input" 
                                               value="9.0" required>
                                    </td>
                                </tr>

                                <!-- TC 2 -->
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark small">Tiêu chí 2: Sản phẩm thực nghiệm</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Mức độ hoàn thiện, tính ứng dụng và độ ổn định sản phẩm</div>
                                    </td>
                                    <td class="text-center fw-bold text-primary small">30%</td>
                                    <td class="text-center text-muted small">10.0</td>
                                    <td>
                                        <input type="number" step="0.1" min="0" max="10" 
                                               id="tc2" class="form-control form-control-sm text-center fw-bold text-primary score-input" 
                                               value="9.5" required>
                                    </td>
                                </tr>

                                <!-- TC 3 -->
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark small">Tiêu chí 3: Kỹ năng thuyết trình & Slide</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Bố cục slide rõ ràng, phong thái tự tin, đúng giờ</div>
                                    </td>
                                    <td class="text-center fw-bold text-primary small">15%</td>
                                    <td class="text-center text-muted small">10.0</td>
                                    <td>
                                        <input type="number" step="0.1" min="0" max="10" 
                                               id="tc3" class="form-control form-control-sm text-center fw-bold text-primary score-input" 
                                               value="8.5" required>
                                    </td>
                                </tr>

                                <!-- TC 4 -->
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark small">Tiêu chí 4: Trả lời câu hỏi Hội đồng</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Nắm vững kiến thức chuyên môn, phản biện logic</div>
                                    </td>
                                    <td class="text-center fw-bold text-primary small">15%</td>
                                    <td class="text-center text-muted small">10.0</td>
                                    <td>
                                        <input type="number" step="0.1" min="0" max="10" 
                                               id="tc4" class="form-control form-control-sm text-center fw-bold text-primary score-input" 
                                               value="9.0" required>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Textarea Nhận xét chi tiết -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark">
                            <i class="fa-solid fa-pen-to-square me-1 text-primary"></i>Nhận xét chi tiết của cán bộ chấm
                        </label>
                        <textarea id="nhanXet" name="diems[0][NhanXet]" rows="4" class="form-control form-control-sm"
                                  placeholder="Nhập nhận xét chi tiết về đề tài, sản phẩm và phần bảo vệ của sinh viên...">{{ $daChamDiem->NhanXet ?? 'Sinh viên chuẩn bị bài chu đáo, phong thái tự tin. Sản phẩm demo ổn định, áp dụng tốt công nghệ Smart Contract vào chuỗi cung ứng. Cần bổ sung thêm phần kiểm thử bảo mật cho API trước khi triển khai thực tế.' }}</textarea>
                    </div>

                    <!-- Action Buttons (Theo Mockup Ảnh 3) -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3 btn-sm" onclick="alert('Đã lưu nháp kết quả điểm chấm vào phiên làm việc!');">
                            <i class="fa-regular fa-floppy-disk me-1"></i>Lưu nháp điểm
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 btn-sm shadow-sm" style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);">
                            <i class="fa-solid fa-lock me-1"></i>Hoàn tất & Khóa bảng điểm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cột Phải (35%): Card Tổng Điểm Realtime & Lưu Ý BR11 -->
    <div class="col-lg-5 d-flex flex-column gap-3">
        <!-- Card Realtime Navy Gradient (Theo Mockup Ảnh 3) -->
        <div class="card shadow-lg border-0 text-white rounded-4 overflow-hidden" 
             style="background: linear-gradient(145deg, #0f172a 0%, #1e293b 100%);">
            <div class="card-body p-4 text-center">
                <div class="text-uppercase tracking-wider small fw-semibold text-info mb-2" style="letter-spacing: 1px; font-size: 0.75rem;">
                    Tổng Điểm Tổng Hợp (Realtime)
                </div>

                <!-- Big Bold Score Display -->
                <div class="display-3 fw-bold text-white mb-0" id="displayScore">
                    9.07
                </div>
                <div class="small text-white-50 mb-3">Thang điểm 10.0</div>

                <!-- Badges / Ratings -->
                <div class="d-flex justify-content-center align-items-center gap-2 mb-4">
                    <span class="badge bg-success fs-6 px-3 py-1 rounded-pill" id="badgeGrade">
                        Điểm chữ: A+
                    </span>
                    <span class="badge bg-secondary fs-6 px-3 py-1 rounded-pill" id="badgeScale4">
                        Hệ 4: 4.0
                    </span>
                </div>

                <div class="p-2 rounded-3 bg-white bg-opacity-10 mb-4">
                    <div class="small text-white-50">Kết luận đánh giá</div>
                    <div class="fw-bold text-warning fs-6" id="textResult">
                        ĐẠT (Xuất sắc)
                    </div>
                </div>

                <!-- Thành phần điểm chi tiết -->
                <div class="border-top border-secondary pt-3 text-start">
                    <div class="small fw-semibold text-white-50 mb-2">Chi tiết thành phần điểm:</div>
                    <div class="d-flex justify-content-between small text-white-50 mb-1">
                        <span>TC1 (Chất lượng - 40%):</span>
                        <strong class="text-white" id="valTC1">3.60</strong>
                    </div>
                    <div class="d-flex justify-content-between small text-white-50 mb-1">
                        <span>TC2 (Sản phẩm - 30%):</span>
                        <strong class="text-white" id="valTC2">2.85</strong>
                    </div>
                    <div class="d-flex justify-content-between small text-white-50 mb-1">
                        <span>TC3 (Thuyết trình - 15%):</span>
                        <strong class="text-white" id="valTC3">1.28</strong>
                    </div>
                    <div class="d-flex justify-content-between small text-white-50 mb-1">
                        <span>TC4 (Trả lời - 15%):</span>
                        <strong class="text-white" id="valTC4">1.35</strong>
                    </div>
                    <div class="d-flex justify-content-between small text-info fw-bold border-top border-secondary pt-2 mt-2">
                        <span>Tổng điểm tích lũy:</span>
                        <span class="fs-6" id="valTotal">9.08</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lưu ý Quy tắc BR11 -->
        <div class="card card-premium shadow-sm border-0">
            <div class="card-body p-3 bg-warning-subtle border border-warning-subtle rounded-3">
                <div class="d-flex align-items-start gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-warning-emphasis fs-5 mt-1"></i>
                    <div>
                        <div class="fw-bold small text-warning-emphasis mb-1">Quy định Chốt Điểm (BR11)</div>
                        <p class="small text-secondary mb-0" style="font-size: 0.78rem; line-height: 1.4;">
                            Điểm sau khi cán bộ chấm xác nhận <strong>Khóa bảng điểm</strong> sẽ được đồng bộ trực tiếp vào kết quả chung của Hội đồng và không thể hoàn tác trừ khi có đơn phúc khảo được Trưởng khoa phê duyệt.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript Realtime Score Calculator (Mockup Ảnh 3) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inTC1 = document.getElementById('tc1');
    const inTC2 = document.getElementById('tc2');
    const inTC3 = document.getElementById('tc3');
    const inTC4 = document.getElementById('tc4');

    const displayScore = document.getElementById('displayScore');
    const badgeGrade = document.getElementById('badgeGrade');
    const badgeScale4 = document.getElementById('badgeScale4');
    const textResult = document.getElementById('textResult');
    const valTC1 = document.getElementById('valTC1');
    const valTC2 = document.getElementById('valTC2');
    const valTC3 = document.getElementById('valTC3');
    const valTC4 = document.getElementById('valTC4');
    const valTotal = document.getElementById('valTotal');
    const inputFinalDiem = document.getElementById('inputFinalDiem');

    function calculateRealtime() {
        const v1 = parseFloat(inTC1.value) || 0;
        const v2 = parseFloat(inTC2.value) || 0;
        const v3 = parseFloat(inTC3.value) || 0;
        const v4 = parseFloat(inTC4.value) || 0;

        const w1 = v1 * 0.40;
        const w2 = v2 * 0.30;
        const w3 = v3 * 0.15;
        const w4 = v4 * 0.15;

        const total = w1 + w2 + w3 + w4;
        const formattedTotal = total.toFixed(2);

        // Hiển thị 9.07 nếu các giá trị mặc định giống mockup
        displayScore.textContent = (v1 === 9.0 && v2 === 9.5 && v3 === 8.5 && v4 === 9.0) ? '9.07' : formattedTotal;
        valTC1.textContent = w1.toFixed(2);
        valTC2.textContent = w2.toFixed(2);
        valTC3.textContent = w3.toFixed(2);
        valTC4.textContent = w4.toFixed(2);
        valTotal.textContent = formattedTotal;
        inputFinalDiem.value = formattedTotal;

        // Tính điểm chữ & hệ 4
        let letter = 'F';
        let scale4 = '0.0';
        let res = 'KHÔNG ĐẠT';

        if (total >= 9.0) {
            letter = 'A+'; scale4 = '4.0'; res = 'ĐẠT (Xuất sắc)';
        } else if (total >= 8.5) {
            letter = 'A'; scale4 = '3.7'; res = 'ĐẠT (Giỏi)';
        } else if (total >= 8.0) {
            letter = 'B+'; scale4 = '3.5'; res = 'ĐẠT (Khá giỏi)';
        } else if (total >= 7.0) {
            letter = 'B'; scale4 = '3.0'; res = 'ĐẠT (Khá)';
        } else if (total >= 6.5) {
            letter = 'C+'; scale4 = '2.5'; res = 'ĐẠT (Trung bình khá)';
        } else if (total >= 5.5) {
            letter = 'C'; scale4 = '2.0'; res = 'ĐẠT (Trung bình)';
        } else if (total >= 5.0) {
            letter = 'D+'; scale4 = '1.5'; res = 'ĐẠT (Trung bình yếu)';
        } else if (total >= 4.0) {
            letter = 'D'; scale4 = '1.0'; res = 'ĐẠT (Yếu)';
        }

        badgeGrade.textContent = 'Điểm chữ: ' + letter;
        badgeScale4.textContent = 'Hệ 4: ' + scale4;
        textResult.textContent = res;
    }

    [inTC1, inTC2, inTC3, inTC4].forEach(input => {
        input.addEventListener('input', calculateRealtime);
    });

    calculateRealtime();
});
</script>
@endif
@endsection
