@extends('layouts.giangvien')

@section('page_title', 'Đánh Giá Phản Biện Đề Cương - ' . ($detai->MaDeTai ?? ''))

@push('styles')
<style>
    :root {
        --gv-border: #e2e8f0;
        --gv-bg-subtle: #f8fafc;
        --huit-primary: #00305a;
        --huit-accent: #0284c7;
    }

    .card-dashboard {
        background: #ffffff;
        border: 1px solid var(--gv-border);
        border-radius: 1.25rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }

    .sticky-evaluation-card {
        position: sticky;
        top: 2rem;
    }

    /* Radio Card Customization */
    .evaluation-card-option {
        cursor: pointer;
        transition: all 0.25s ease-in-out;
        border: 1.5px solid var(--gv-border);
        border-radius: 0.85rem;
        padding: 1.15rem 1.25rem;
        background-color: #ffffff;
    }

    .evaluation-card-option:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }

    .evaluation-card-option input[type="radio"]:checked ~ .evaluation-content {
        font-weight: 600;
    }

    .evaluation-card-option.option-dat:has(input:checked) {
        border-color: #198754 !important;
        background-color: #f4fcf7 !important;
    }

    .evaluation-card-option.option-sua:has(input:checked) {
        border-color: #ffc107 !important;
        background-color: #fffdf5 !important;
    }

    .evaluation-card-option.option-khongdat:has(input:checked) {
        border-color: #dc3545 !important;
        background-color: #fff8f8 !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    
    <!-- TOP HEADER HERO BAR -->
    <div class="card-dashboard mb-4 p-4 p-lg-5 bg-white border-0 shadow-sm">
        <div class="d-flex justify-content-between align-items-lg-center flex-column flex-lg-row gap-3">
            <div>
                <a href="{{ route('giangvien.phanbien.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold mb-3 shadow-xs d-inline-flex align-items-center">
                    <i class="fa-solid fa-arrow-left me-2"></i> Quay lại danh sách phân công
                </a>
                <h3 class="fw-bold text-dark mb-2" style="color: var(--huit-primary);">
                    Đánh Giá Phản Biện Đề Cương
                </h3>
                <p class="text-secondary mb-2 fs-6 fw-medium">
                    {{ $detai->TenDeTai ?? '' }}
                </p>
                <div class="text-muted small d-flex flex-wrap align-items-center gap-2 mt-2">
                    <span class="bg-light px-2.5 py-1 rounded border font-monospace text-dark">Mã ĐT: {{ $detai->MaDeTai ?? '' }}</span>
                    <span>•</span>
                    <span>GV Đề xuất: <strong class="text-dark">{{ $detai->giangVien->HoTen ?? 'N/A' }}</strong></span>
                    <span>•</span>
                    <span>Bộ môn: <strong class="text-dark">{{ $detai->giangVien->boMon->TenBoMon ?? 'N/A' }}</strong></span>
                </div>
            </div>

            <div class="flex-shrink-0">
                @php
                    $isNopLai = $phanCong->KetQua === 'Đã nộp lại' 
                        || $phanCong->TrangThai === 'Đã nộp lại đề cương' 
                        || ($detai && $detai->TrangThai === 'Đã cập nhật đề cương - Chờ phản biện lại');
                @endphp
                @if($phanCong->KetQua === 'Đạt')
                    <div class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-4 py-3 fs-6 fw-bold shadow-xs">
                        <i class="fa-solid fa-circle-check me-2"></i> ĐÃ ĐÁNH GIÁ: ĐẠT
                    </div>
                @elseif($isNopLai)
                    <div class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-4 py-3 fs-6 fw-bold shadow-xs">
                        <i class="fa-solid fa-rotate me-2"></i> ĐÃ NỘP LẠI ĐỀ CƯƠNG (CHỜ CHẤM LẠI)
                    </div>
                @elseif($phanCong->KetQua === 'Yêu cầu chỉnh sửa')
                    <div class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-4 py-3 fs-6 fw-bold shadow-xs">
                        <i class="fa-solid fa-pen me-2"></i> ĐÃ ĐÁNH GIÁ: YÊU CẦU SỬA
                    </div>
                @elseif($phanCong->KetQua === 'Không đạt')
                    <div class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-4 py-3 fs-6 fw-bold shadow-xs">
                        <i class="fa-solid fa-circle-xmark me-2"></i> ĐÃ ĐÁNH GIÁ: KHÔNG ĐẠT
                    </div>
                @else
                    <div class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-4 py-3 fs-6 fw-bold shadow-xs">
                        <i class="fa-solid fa-clock me-2"></i> CHƯA ĐÁNH GIÁ
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ALERT NỘP LẠI (NẾU CÓ) -->
    @if($isNopLai)
    <div class="alert alert-primary border-0 shadow-sm d-flex align-items-start gap-3 mb-4 rounded-4 p-4 bg-primary-subtle text-primary-emphasis">
        <div class="fs-2 text-primary mt-1">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <div>
            <h5 class="fw-bold mb-1">Giảng viên đề xuất đã cập nhật và nộp lại bản Đề cương mới!</h5>
            <div class="fs-6" style="line-height: 1.6;">
                Giảng viên đề xuất đã tiếp thu ý kiến chỉnh sửa trước đó và tải lên bản đề cương chi tiết mới. Thầy/Cô vui lòng nhấn nút <strong>Xem Nhanh Đề Cương</strong> bên dưới để kiểm tra các nội dung chỉnh sửa, sau đó nhập nhận xét và cập nhật lại kết luận vào phiếu đánh giá bên phải.
            </div>
        </div>
    </div>
    @endif

    <!-- MAIN 1-PAGE SPLIT LAYOUT -->
    <div class="row g-4">
        
        <!-- CỘT TRÁI (7 COLS): THÔNG TIN ĐỀ TÀI & FILE ĐỀ CƯƠNG -->
        <div class="col-lg-7">
            <div class="card-dashboard mb-4">
                <div class="bg-white border-bottom py-4 px-4 px-lg-5 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-file-lines text-primary me-2"></i>
                        Nội Dung Chi Tiết Đề Tài
                    </h5>
                    <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small fw-semibold">
                        Học kỳ: {{ $detai->hocKy->TenHocKy ?? $detai->MaHocKy }}
                    </span>
                </div>
                
                <div class="card-body p-4 p-lg-5">
                    <!-- Tên đề tài -->
                    <div class="mb-4 pb-3 border-bottom">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px;">Tên Đề Tài</label>
                        <h4 class="fw-bold text-dark" style="line-height: 1.4;">{{ $detai->TenDeTai ?? '' }}</h4>
                    </div>

                    @if($detai->TenDeTaiTiengAnh)
                    <!-- Tên tiếng anh -->
                    <div class="mb-4 pb-3 border-bottom">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px;">Tên Tiếng Anh</label>
                        <div class="text-secondary fst-italic fs-6">{{ $detai->TenDeTaiTiengAnh }}</div>
                    </div>
                    @endif

                    <!-- Mục tiêu -->
                    <div class="mb-4 pb-3 border-bottom">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px;">Mục Tiêu &amp; Sản Phẩm Dự Kiến</label>
                        <div class="p-4 bg-light rounded-4 border text-dark fs-6" style="line-height: 1.7; white-space: pre-line;">{{ $detai->MucTieu ?? 'Chưa cập nhật' }}</div>
                    </div>

                    <!-- Mô tả -->
                    <div class="mb-4 pb-3 border-bottom">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px;">Mô Tả Tóm Tắt Nội Dung</label>
                        <div class="p-4 bg-light rounded-4 border text-dark fs-6 rich-content-display" style="line-height: 1.7;">{!! \App\Helpers\HtmlHelper::clean($detai->MoTa) ?: 'Chưa cập nhật' !!}</div>
                    </div>

                    <!-- Yêu cầu -->
                    <div class="mb-4 pb-3 border-bottom">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px;">Yêu Cầu Đối Với Sinh Viên</label>
                        <div class="p-4 bg-light rounded-4 border text-dark fs-6" style="line-height: 1.7; white-space: pre-line;">{{ $detai->YeuCau ?? 'Chưa cập nhật' }}</div>
                    </div>

                    <!-- File đề cương -->
                    <div>
                        <label class="form-label text-muted small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px;">Tài Liệu Đề Cương Chi Tiết Đính Kèm</label>
                        <div>
                            @if($detai->FileDeCuong)
                                @php
                                    $filePath = Str::startsWith($detai->FileDeCuong, ['http', 'storage/']) ? asset($detai->FileDeCuong) : asset('storage/' . $detai->FileDeCuong);
                                    $fileName = basename($detai->FileDeCuong);
                                @endphp
                                <div class="p-4 bg-light rounded-4 border">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="bg-primary text-white rounded-3 p-3 d-flex align-items-center justify-content-center shadow-xs">
                                            <i class="fa-solid fa-file-word fs-3"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="fw-bold text-dark text-truncate fs-6" title="{{ $fileName }}">
                                                {{ $fileName }}
                                            </div>
                                            <span class="text-muted d-block mt-0.5 small">Tệp đề cương chính thức</span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 pt-2">
                                        <button type="button" class="btn btn-primary rounded-pill px-4 py-2.5 shadow-xs fw-bold small d-inline-flex align-items-center justify-content-center" 
                                                onclick="quickPreviewOutline('{{ $filePath }}', '{{ $fileName }}', '{{ addslashes($detai->TenDeTai) }}')">
                                            <i class="fa-solid fa-eye me-2"></i> Xem Nhanh Đề Cương
                                        </button>
                                        <a href="{{ $filePath }}" target="_blank" class="btn btn-outline-secondary rounded-pill px-3.5 py-2.5 fw-semibold small d-inline-flex align-items-center justify-content-center">
                                            <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Mở tab mới
                                        </a>
                                        <a href="{{ $filePath }}" download="{{ $fileName }}" class="btn btn-outline-secondary rounded-pill px-3.5 py-2.5 fw-semibold small d-inline-flex align-items-center justify-content-center">
                                            <i class="fa-solid fa-download me-2"></i> Tải về máy
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="text-danger small p-4 bg-light rounded-4 border text-center fw-medium">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i> Chưa có file đề cương đính kèm.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI (5 COLS): PHIẾU ĐÁNH GIÁ PHẢN BIỆN (STICKY SIDEBAR) -->
        <div class="col-lg-5">
            <div class="card-dashboard sticky-evaluation-card mb-4 border-0 shadow">
                <div class="bg-primary text-white py-4 px-4 px-lg-5 d-flex justify-content-between align-items-center" style="background: var(--huit-primary) !important;">
                    <h5 class="fw-bold mb-0 text-white">
                        <i class="fa-solid fa-signature me-2"></i>
                        Phiếu Đánh Giá Phản Biện
                    </h5>
                    <span class="badge bg-white text-primary px-3 py-1.5 rounded-pill fw-bold small">Chính thức</span>
                </div>

                <div class="card-body p-4 p-lg-5 bg-white">
                    <form action="{{ route('giangvien.phanbien.store', $phanCong->MaPhanCong) }}" method="POST">
                        @csrf
                        
                        <!-- Lựa chọn kết quả đánh giá (Interactive Cards) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small text-uppercase mb-3" style="letter-spacing: 0.5px;">Kết luận đánh giá đề cương <span class="text-danger">*</span></label>
                            
                            <div class="d-flex flex-column gap-3">
                                <!-- Đạt -->
                                <label class="evaluation-card-option option-dat d-flex align-items-start gap-3">
                                    <input class="form-check-input mt-1 flex-shrink-0" type="radio" name="KetQua" id="kqDat" value="Đạt" 
                                           {{ old('KetQua', $phanCong->KetQua) === 'Đạt' ? 'checked' : '' }} required>
                                    <div class="evaluation-content w-100">
                                        <div class="fw-bold text-success d-flex align-items-center mb-1">
                                            <i class="fa-solid fa-circle-check me-2"></i> ĐẠT YÊU CẦU
                                        </div>
                                        <div class="small text-muted" style="line-height: 1.4;">Đề cương phù hợp mục tiêu đào tạo, cấu trúc rõ ràng, tính khả thi cao.</div>
                                    </div>
                                </label>

                                <!-- Yêu cầu chỉnh sửa -->
                                <label class="evaluation-card-option option-sua d-flex align-items-start gap-3">
                                    <input class="form-check-input mt-1 flex-shrink-0" type="radio" name="KetQua" id="kqSua" value="Yêu cầu chỉnh sửa" 
                                           {{ old('KetQua', $phanCong->KetQua) === 'Yêu cầu chỉnh sửa' ? 'checked' : '' }}>
                                    <div class="evaluation-content w-100">
                                        <div class="fw-bold text-warning-emphasis d-flex align-items-center mb-1">
                                            <i class="fa-solid fa-pen-to-square me-2"></i> YÊU CẦU CHỈNH SỬA
                                        </div>
                                        <div class="small text-muted" style="line-height: 1.4;">Cần bổ sung phạm vi nghiên cứu, điều chỉnh mục tiêu hoặc phương pháp thực hiện.</div>
                                    </div>
                                </label>

                                <!-- Không đạt -->
                                <label class="evaluation-card-option option-khongdat d-flex align-items-start gap-3">
                                    <input class="form-check-input mt-1 flex-shrink-0" type="radio" name="KetQua" id="kqKhongDat" value="Không đạt" 
                                           {{ old('KetQua', $phanCong->KetQua) === 'Không đạt' ? 'checked' : '' }}>
                                    <div class="evaluation-content w-100">
                                        <div class="fw-bold text-danger d-flex align-items-center mb-1">
                                            <i class="fa-solid fa-circle-xmark me-2"></i> KHÔNG ĐẠT
                                        </div>
                                        <div class="small text-muted" style="line-height: 1.4;">Nội dung không phù hợp, trùng lặp hoặc không đảm bảo khối lượng tốt nghiệp.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Nhận xét chi tiết -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small text-uppercase mb-2" style="letter-spacing: 0.5px;">Nhận xét chi tiết &amp; Ý kiến đóng góp <span class="text-danger">*</span></label>
                            <textarea name="NhanXet" class="form-control rounded-4 p-3.5 shadow-xs border" rows="6" required
                                      placeholder="Ghi rõ nhận xét về tính cấp thiết, mục tiêu, đối tượng, phương pháp nghiên cứu và các điểm cần khắc phục...">{{ old('NhanXet', $phanCong->NhanXet) }}</textarea>
                            <div class="form-text text-muted small mt-2">Ý kiến này sẽ được chuyển trực tiếp đến Giảng viên đề xuất và Trưởng bộ môn theo dõi.</div>
                        </div>

                        @if($phanCong->NgayDanhGia)
                            <div class="small text-muted mb-4 bg-light p-3 rounded-3 border d-flex align-items-center gap-2">
                                <i class="fa-regular fa-clock text-primary fs-5"></i> 
                                <div>Lần đánh giá gần nhất: <strong class="text-dark">{{ \Carbon\Carbon::parse($phanCong->NgayDanhGia)->format('H:i d/m/Y') }}</strong></div>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary w-100 py-3.5 rounded-pill fw-bold shadow-sm d-inline-flex align-items-center justify-content-center fs-6"
                                style="background: var(--huit-primary); border-color: var(--huit-primary);"
                                onclick="return confirm('Bạn có chắc chắn muốn gửi kết quả đánh giá phản biện này?');">
                            <i class="fa-solid fa-paper-plane me-2"></i> Gửi Kết Quả Đánh Giá Phản Biện
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection