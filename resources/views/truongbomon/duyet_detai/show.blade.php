@extends('layouts.truongbomon')

@section('page_title', 'Chi Tiết Đề Tài ' . $detai->MaDeTai)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="{{ route('truongbomon.duyet_detai.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-file-lines me-2 text-primary"></i>
                Chi Tiết Đề Tài: {{ $detai->TenDeTai }}
            </h4>
            <div class="text-muted small">
                <span>Mã đề tài: <strong class="text-primary">{{ $detai->MaDeTai }}</strong></span>
                <span class="mx-2">•</span>
                <span>Bộ môn: <strong>{{ $detai->giangVien->boMon->TenBoMon ?? 'N/A' }}</strong></span>
                <span class="mx-2">•</span>
                <span>Học kỳ: <strong>{{ $detai->hocKy->TenHocKy ?? $detai->MaHocKy }} ({{ $detai->hocKy->NamHoc ?? '' }})</strong></span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($detai->TrangThai === 'Chờ duyệt cấp Bộ môn')
                <span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="fa-solid fa-clock me-1"></i> Chờ duyệt cấp Bộ môn</span>
            @elseif($detai->TrangThai === 'Đang phản biện đề cương')
                <span class="badge bg-info text-dark px-3 py-2 fs-6"><i class="fa-solid fa-spinner me-1"></i> Đang phản biện đề cương</span>
            @elseif($detai->TrangThai === 'Đã phản biện - Chờ duyệt BM')
                <span class="badge bg-primary px-3 py-2 fs-6"><i class="fa-solid fa-check-double me-1"></i> Đã phản biện - Chờ TBM duyệt</span>
            @elseif($detai->TrangThai === 'Chờ duyệt cấp Khoa')
                <span class="badge bg-secondary px-3 py-2 fs-6"><i class="fa-solid fa-paper-plane me-1"></i> Đã chuyển cấp Khoa</span>
            @elseif($detai->TrangThai === 'Trưởng khoa đã duyệt')
                <span class="badge bg-success px-3 py-2 fs-6"><i class="fa-solid fa-stamp me-1"></i> Trưởng khoa đã duyệt</span>
            @elseif($detai->TrangThai === 'Đã công bố')
                <span class="badge bg-success px-3 py-2 fs-6"><i class="fa-solid fa-bullhorn me-1"></i> Đã công bố</span>
            @elseif($detai->TrangThai === 'Yêu cầu chỉnh sửa')
                <span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="fa-solid fa-triangle-exclamation me-1"></i> Yêu cầu chỉnh sửa</span>
            @elseif($detai->TrangThai === 'Từ chối')
                <span class="badge bg-danger px-3 py-2 fs-6"><i class="fa-solid fa-circle-xmark me-1"></i> Từ chối</span>
            @elseif($detai->TrangThai === 'Không đạt phản biện')
                <span class="badge bg-danger px-3 py-2 fs-6"><i class="fa-solid fa-circle-xmark me-1"></i> Không đạt phản biện</span>
            @else
                <span class="badge bg-light text-muted border px-3 py-2 fs-6">{{ $detai->TrangThai }}</span>
            @endif
        </div>
    </div>

    <!-- Topic Lifecycle Workflow Progress Stepper -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-4">
            <div class="row g-2 text-center position-relative">
                @php
                    $isProposed = true;
                    $isAssigned = (bool)$phanBienHienTai;
                    $isReviewed = $phanBienHienTai && $phanBienHienTai->KetQua;
                    $isBmApproved = in_array($detai->TrangThai, ['Chờ duyệt cấp Khoa', 'Trưởng khoa đã duyệt', 'Đã công bố']);
                    $isKhoaApproved = in_array($detai->TrangThai, ['Trưởng khoa đã duyệt', 'Đã công bố']);
                @endphp

                <!-- Step 1 -->
                <div class="col">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white mb-2" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div class="small fw-bold text-dark">1. GV Đề Xuất</div>
                    <div class="text-muted" style="font-size: 0.72rem;">{{ $detai->NgayDeXuat ? \Carbon\Carbon::parse($detai->NgayDeXuat)->format('d/m/Y') : ($detai->created_at ? $detai->created_at->format('d/m/Y') : 'Đã nộp') }}</div>
                </div>

                <!-- Step 2 -->
                <div class="col">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle {{ $isAssigned ? 'bg-success text-white' : 'bg-warning text-dark' }} mb-2" style="width: 36px; height: 36px;">
                        <i class="fa-solid {{ $isAssigned ? 'fa-check' : 'fa-user-pen' }}"></i>
                    </div>
                    <div class="small fw-bold text-dark">2. Phân Công Phản Biện</div>
                    <div class="text-muted" style="font-size: 0.72rem;">{{ $isAssigned ? 'Đã phân công' : 'Chờ TBM phân công' }}</div>
                </div>

                <!-- Step 3 -->
                <div class="col">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle {{ $isReviewed ? 'bg-success text-white' : ($isAssigned ? 'bg-info text-white' : 'bg-light text-muted border') }} mb-2" style="width: 36px; height: 36px;">
                        <i class="fa-solid {{ $isReviewed ? 'fa-check' : 'fa-file-lines' }}"></i>
                    </div>
                    <div class="small fw-bold text-dark">3. Phản Biện Đề Cương</div>
                    <div class="text-muted" style="font-size: 0.72rem;">{{ $isReviewed ? $phanBienHienTai->KetQua : ($isAssigned ? 'Đang phản biện' : 'Chưa gửi') }}</div>
                </div>

                <!-- Step 4 -->
                <div class="col">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle {{ $isBmApproved ? 'bg-success text-white' : ($isReviewed ? 'bg-warning text-dark' : 'bg-light text-muted border') }} mb-2" style="width: 36px; height: 36px;">
                        <i class="fa-solid {{ $isBmApproved ? 'fa-check' : 'fa-stamp' }}"></i>
                    </div>
                    <div class="small fw-bold text-dark">4. Bộ Môn Duyệt</div>
                    <div class="text-muted" style="font-size: 0.72rem;">{{ $detai->NgayDuyetBM ? \Carbon\Carbon::parse($detai->NgayDuyetBM)->format('d/m/Y') : ($detai->TrangThai === 'Từ chối' ? 'Từ chối' : 'Chờ TBM phê duyệt') }}</div>
                </div>

                <!-- Step 5 -->
                <div class="col">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle {{ $isKhoaApproved ? 'bg-success text-white' : 'bg-light text-muted border' }} mb-2" style="width: 36px; height: 36px;">
                        <i class="fa-solid {{ $isKhoaApproved ? 'fa-check' : 'fa-bullhorn' }}"></i>
                    </div>
                    <div class="small fw-bold text-dark">5. Khoa Duyệt & Công Bố</div>
                    <div class="text-muted" style="font-size: 0.72rem;">{{ $detai->NgayDuyetKhoa ? \Carbon\Carbon::parse($detai->NgayDuyetKhoa)->format('d/m/Y') : 'Cấp Khoa' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Info Column -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-circle-info text-primary me-2"></i>
                        Thông Tin Đề Tài Khóa Luận
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Tên Đề Tài (Tiếng Việt):</label>
                        <div class="fs-6 fw-bold text-dark">{{ $detai->TenDeTai }}</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">Chuyên ngành áp dụng:</label>
                            <div><i class="fa-solid fa-graduation-cap text-muted me-1"></i>{{ $detai->nganh->TenNganh ?? 'Toàn ngành' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">Học phần:</label>
                            <div><i class="fa-solid fa-book-open text-muted me-1"></i>{{ $detai->HocPhan ?? 'Khóa luận tốt nghiệp' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">Số lượng sinh viên tối đa:</label>
                            <div><i class="fa-solid fa-users text-muted me-1"></i>{{ $detai->SoLuongSinhVienToiDa ?? 3 }} sinh viên / nhóm</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Lĩnh vực nghiên cứu:</label>
                            <div><i class="fa-solid fa-tags text-muted me-1"></i>{{ $detai->LinhVuc ?? 'Chưa xác định' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Ngày gửi đề xuất:</label>
                            <div><i class="fa-regular fa-calendar-days text-muted me-1"></i>{{ $detai->NgayDeXuat ? \Carbon\Carbon::parse($detai->NgayDeXuat)->format('d/m/Y H:i') : ($detai->created_at ? $detai->created_at->format('d/m/Y H:i') : 'N/A') }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Mô tả tóm tắt nội dung chuyên môn:</label>
                        <div class="p-3 bg-light rounded" style="white-space: pre-line; line-height: 1.6;">{{ $detai->MoTa ?? 'Chưa có mô tả chi tiết' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Yêu cầu cần đạt đối với sinh viên thực hiện:</label>
                        <div class="p-3 bg-light rounded" style="white-space: pre-line; line-height: 1.6;">{{ $detai->YeuCau ?? 'Chưa cập nhật yêu cầu' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">File Đề Cương Chi Tiết Đính Kèm:</label>
                        <div>
                            @if($detai->FileDeCuong)
                                @php
                                    $filePath = Str::startsWith($detai->FileDeCuong, ['http', 'storage/']) ? asset($detai->FileDeCuong) : asset('storage/' . $detai->FileDeCuong);
                                @endphp
                                <a href="{{ $filePath }}" target="_blank" class="btn btn-outline-primary">
                                    <i class="fa-solid fa-file-pdf me-2"></i> Tải / Xem Đề Cương Đề Tài (.pdf / .docx)
                                </a>
                            @else
                                <span class="text-danger small"><i class="fa-solid fa-circle-exclamation me-1"></i> Giảng viên chưa đính kèm file đề cương chi tiết.</span>
                            @endif
                        </div>
                    </div>

                    <!-- Previous rejection reason / modification notes -->
                    @if($detai->LyDoTuChoi)
                        <div class="alert alert-warning border-0 mt-3" style="border-left: 4px solid #ffc107 !important; border-radius: 8px;">
                            <h6 class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1 text-warning"></i> Ghi chú / Ý kiến phản hồi trước đó:</h6>
                            <p class="mb-0 text-dark">{{ $detai->LyDoTuChoi }}</p>
                        </div>
                    @endif

                    <!-- BM Approval Metadata -->
                    @if($detai->NgayDuyetBM)
                        <div class="alert alert-success border-0 mt-3 d-flex align-items-center gap-2" style="border-radius: 8px;">
                            <i class="fa-solid fa-circle-check text-success fa-lg"></i>
                            <div>
                                <strong>Đã duyệt cấp Bộ môn:</strong> ngày {{ \Carbon\Carbon::parse($detai->NgayDuyetBM)->format('d/m/Y H:i') }}
                                @if($detai->NguoiDuyetBM)
                                    <span>bởi cán bộ <strong>{{ $detai->NguoiDuyetBM }}</strong></span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Actions & Reviewer Column -->
        <div class="col-lg-4">
            <!-- Lecturer Proposer Info -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-chalkboard-user text-primary me-2"></i>
                        Giảng Viên Hướng Dẫn Đề Xuất
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                            <i class="fa-solid fa-user-tie fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">{{ $detai->giangVien->HoTen ?? 'N/A' }}</h6>
                            <div class="small text-muted">{{ $detai->giangVien->HocVi ?? 'Giảng viên' }} {{ $detai->giangVien->HocHam ? ' - ' . $detai->giangVien->HocHam : '' }}</div>
                            <div class="small text-muted">Mã GV: <strong>{{ $detai->giangVien->MaGV ?? '' }}</strong></div>
                        </div>
                    </div>
                    <div class="small text-muted border-top pt-2">
                        <div class="mb-1"><i class="fa-solid fa-building me-2 text-secondary"></i> {{ $detai->giangVien->boMon->TenBoMon ?? 'N/A' }}</div>
                        <div class="mb-1"><i class="fa-solid fa-envelope me-2 text-secondary"></i> {{ $detai->giangVien->Email ?? 'Chưa cập nhật' }}</div>
                        <div><i class="fa-solid fa-phone me-2 text-secondary"></i> {{ $detai->giangVien->SoDienThoai ?? 'Chưa cập nhật' }}</div>
                    </div>
                </div>
            </div>

            <!-- Outline Review (Phản Biện Đề Cương) Section -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-file-pen text-info me-2"></i>
                        Phản Biện Đề Cương
                    </h6>
                </div>
                <div class="card-body">
                    @if($phanBienHienTai)
                        <div class="mb-3">
                            <div class="small text-muted fw-bold">Giảng viên phản biện:</div>
                            <div class="fw-bold text-dark fs-6">{{ $phanBienHienTai->giangVien->HoTen ?? 'N/A' }}</div>
                            <div class="small text-muted">Mã GV: {{ $phanBienHienTai->MaGV }} ({{ $phanBienHienTai->giangVien->boMon->TenBoMon ?? '' }})</div>
                            <div class="small text-muted">Ngày phân công: {{ $phanBienHienTai->NgayPhanCong ? \Carbon\Carbon::parse($phanBienHienTai->NgayPhanCong)->format('d/m/Y') : 'N/A' }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted fw-bold">Kết luận đánh giá:</div>
                            <div class="mt-1">
                                @if($phanBienHienTai->KetQua === 'Đạt')
                                    <span class="badge bg-success px-3 py-2"><i class="fa-solid fa-check me-1"></i> ĐẠT YÊU CẦU</span>
                                @elseif($phanBienHienTai->KetQua === 'Yêu cầu chỉnh sửa')
                                    <span class="badge bg-warning text-dark px-3 py-2"><i class="fa-solid fa-pen me-1"></i> YÊU CẦU CHỈNH SỬA</span>
                                @elseif($phanBienHienTai->KetQua === 'Không đạt')
                                    <span class="badge bg-danger px-3 py-2"><i class="fa-solid fa-xmark me-1"></i> KHÔNG ĐẠT</span>
                                @else
                                    <span class="badge bg-light text-muted border px-3 py-2"><i class="fa-solid fa-hourglass-half me-1"></i> Đang chờ GV phản biện đánh giá</span>
                                @endif
                            </div>
                        </div>

                        @if($phanBienHienTai->NhanXet)
                            <div class="mb-3">
                                <div class="small text-muted fw-bold">Ý kiến nhận xét chi tiết:</div>
                                <div class="p-3 bg-light rounded mt-1 small" style="white-space: pre-line; line-height: 1.5;">{{ $phanBienHienTai->NhanXet }}</div>
                                <div class="small text-muted text-end mt-1">Đánh giá lúc: {{ $phanBienHienTai->NgayDanhGia ? \Carbon\Carbon::parse($phanBienHienTai->NgayDanhGia)->format('H:i d/m/Y') : '' }}</div>
                            </div>
                        @endif

                        {{-- Nút thay GV phản biện (chỉ khi chưa có kết quả) --}}
                        @if(!$phanBienHienTai->KetQua)
                        <div class="border-top pt-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalThayGvPB">
                                <i class="fa-solid fa-user-pen me-1"></i> Thay đổi Giảng viên phản biện
                            </button>
                        </div>
                        @else
                        <div class="border-top pt-3">
                            <span class="small text-muted"><i class="fa-solid fa-lock me-1"></i>Đã có kết quả phản biện, không thể thay đổi GV phản biện.</span>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-3">
                            <i class="fa-solid fa-triangle-exclamation text-warning fa-2x mb-2"></i>
                            <p class="small text-muted mb-3">Đề tài chưa được phân công Giảng viên phản biện đề cương.</p>
                            <button type="button" class="btn btn-primary btn-sm w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalPhanCong">
                                <i class="fa-solid fa-user-plus me-1"></i> Phân công phản biện ngay
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- TBM Decision Actions -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-stamp text-primary me-2"></i>
                        Quyết Định Của Trưởng Bộ Môn
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">

                        {{-- BƯỚC 1: Duyệt đề xuất (khi đang chờ TBM, chưa phân công PB) --}}
                        @if($detai->TrangThai === 'Chờ duyệt cấp Bộ môn')
                        <form action="{{ route('truongbomon.duyet_detai.duyet', $detai->MaDeTai) }}" method="POST"
                              onsubmit="return confirm('Duyệt đề xuất này và tiến hành phân công Giảng viên phản biện đề cương?');">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                                <i class="fa-solid fa-check me-1"></i> Duyệt Đề Xuất & Phân Công Phản Biện
                            </button>
                        </form>
                        @endif

                        {{-- BƯỚC 2: Duyệt chuyển BCN Khoa (sau khi PB Đạt) --}}
                        @if($detai->TrangThai === 'Đã phản biện - Chờ duyệt BM' && $phanBienHienTai?->KetQua === 'Đạt')
                        <form action="{{ route('truongbomon.duyet_detai.duyetSauPhanBien', $detai->MaDeTai) }}" method="POST"
                              onsubmit="return confirm('Chuyển đề tài lên BCN Khoa để phê duyệt chính thức?');">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                                <i class="fa-solid fa-paper-plane me-1"></i> Duyệt – Chuyển Lên BCN Khoa
                            </button>
                        </form>
                        @endif

                        {{-- Xử lý Không đạt phản biện --}}
                        @if($detai->TrangThai === 'Không đạt phản biện')
                        <button type="button" class="btn btn-danger w-100 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#modalKhongDat">
                            <i class="fa-solid fa-circle-xmark me-1"></i> Xử Lý Kết Quả Không Đạt
                        </button>
                        @endif

                        {{-- Nút Yêu Cầu Chỉnh Sửa (hiện khi đang ở các bước TBM xử lý) --}}
                        @if(in_array($detai->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Đang phản biện đề cương', 'Đã phản biện - Chờ duyệt BM', 'Không đạt phản biện']))
                        <button type="button" class="btn btn-outline-warning w-100 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalYeuCauSua">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Yêu Cầu Chỉnh Sửa
                        </button>
                        @endif

                        {{-- Nút Từ Chối --}}
                        @if(!in_array($detai->TrangThai, ['Từ chối', 'Chờ duyệt cấp Khoa', 'Trưởng khoa đã duyệt', 'Đã công bố', 'Hoàn thành']))
                        <button type="button" class="btn btn-outline-danger w-100 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTuChoi">
                            <i class="fa-solid fa-circle-xmark me-1"></i> Từ Chối Đề Tài
                        </button>
                        @endif
                    </div>

                    @if(in_array($detai->TrangThai, ['Chờ duyệt cấp Khoa', 'Trưởng khoa đã duyệt', 'Đã công bố', 'Hoàn thành']))
                        <div class="mt-3 p-2 bg-success-subtle rounded text-center small text-success">
                            <i class="fa-solid fa-circle-check me-1"></i> Đề tài đã hoàn tất quy trình thẩm định ở cấp Bộ môn.
                        </div>
                    @endif

                    @if($detai->TrangThai === 'Từ chối')
                        <div class="mt-3 p-2 bg-danger-subtle rounded text-center small text-danger">
                            <i class="fa-solid fa-circle-xmark me-1"></i> Đề tài đã bị từ chối ở cấp Bộ môn.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Lịch Sử Xử Lý --}}
    @if(isset($detai->chiTietDuyets) && $detai->chiTietDuyets->count() > 0)
    <div class="card border-0 shadow-sm mt-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i>
                Lịch Sử Xử Lý Đề Tài
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:130px">Ngày</th>
                            <th>Hành động / Trạng thái</th>
                            <th>Người xử lý</th>
                            <th>Ghi chú / Lý do</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detai->chiTietDuyets->sortByDesc('NgayDuyet') as $log)
                        <tr>
                            <td class="ps-3 small text-muted">{{ \Carbon\Carbon::parse($log->NgayDuyet)->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $logClass = match(true) {
                                        str_contains($log->TrangThai, 'Từ chối') || str_contains($log->TrangThai, 'Dừng') => 'text-danger',
                                        str_contains($log->TrangThai, 'Yêu cầu') => 'text-warning',
                                        str_contains($log->TrangThai, 'phân công') || str_contains($log->TrangThai, 'Chuyển') => 'text-info',
                                        default => 'text-success'
                                    };
                                @endphp
                                <span class="fw-semibold {{ $logClass }}">{{ $log->TrangThai }}</span>
                            </td>
                            <td class="small">{{ $log->giangVien->HoTen ?? $log->MaGV }}</td>
                            <td class="small text-muted">{{ $log->LyDo ?? '–' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal Phân Công Phản Biện (mới, chưa có PB) -->
<div class="modal fade" id="modalPhanCong" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('truongbomon.duyet_detai.phanCongPhanBien', $detai->MaDeTai) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-user-plus text-primary me-2"></i>
                        Phân Công Phản Biện Đề Cương
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Đề tài:</label>
                        <div class="fw-bold text-dark">{{ $detai->TenDeTai }}</div>
                        <div class="small text-muted">GV Đề xuất: {{ $detai->giangVien->HoTen ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn Giảng viên phản biện <span class="text-danger">*</span></label>
                        <select name="MaGVPhanBien" class="form-select" required>
                            <option value="">-- Chọn Giảng viên phản biện --</option>
                            @foreach($allGiangViens as $gvOption)
                                <option value="{{ $gvOption->MaGV }}">
                                    {{ $gvOption->HoTen }} ({{ $gvOption->MaGV }}{{ $gvOption->boMon ? ' – ' . $gvOption->boMon->TenBoMon : '' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted">
                            <i class="fa-solid fa-circle-info me-1"></i> BR07: GV đề xuất đề tài không được làm phản biện cho chính đề tài đó.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check me-1"></i> Lưu Phân Công
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Thay GV Phản Biện (đã có PB, chưa có kết quả) -->
<div class="modal fade" id="modalThayGvPB" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('truongbomon.duyet_detai.thayGvPhanBien', $detai->MaDeTai) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-user-pen text-warning me-2"></i>
                        Thay Đổi Giảng Viên Phản Biện
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Hiện tại GV phản biện là: <strong>{{ $phanBienHienTai?->giangVien?->HoTen ?? '–' }}</strong>. Chỉ có thể thay đổi khi chưa có kết quả phản biện.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn Giảng viên phản biện mới <span class="text-danger">*</span></label>
                        <select name="MaGVPhanBien" class="form-select" required>
                            <option value="">-- Chọn Giảng viên phản biện mới --</option>
                            @foreach($allGiangViens as $gvOption)
                                <option value="{{ $gvOption->MaGV }}">
                                    {{ $gvOption->HoTen }} ({{ $gvOption->MaGV }}{{ $gvOption->boMon ? ' – ' . $gvOption->boMon->TenBoMon : '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning fw-bold">
                        <i class="fa-solid fa-arrows-rotate me-1"></i> Cập Nhật GV Phản Biện
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Xử Lý Không Đạt Phản Biện -->
<div class="modal fade" id="modalKhongDat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('truongbomon.duyet_detai.xuLyKhongDat', $detai->MaDeTai) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger-subtle">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="fa-solid fa-circle-xmark me-2"></i>
                        Xử Lý Kết Quả Không Đạt Phản Biện
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Hướng xử lý <span class="text-danger">*</span></label>
                        <div class="d-grid gap-2">
                            <div class="form-check border rounded p-3">
                                <input class="form-check-input" type="radio" name="HuongXuLy" id="dung_lai" value="dung_lai" required>
                                <label class="form-check-label fw-semibold" for="dung_lai">
                                    <i class="fa-solid fa-ban text-danger me-1"></i> Dừng đề tài (không cho chỉnh sửa tiếp)
                                </label>
                            </div>
                            <div class="form-check border rounded p-3">
                                <input class="form-check-input" type="radio" name="HuongXuLy" id="yeu_cau_sua_lai" value="yeu_cau_sua_lai">
                                <label class="form-check-label fw-semibold" for="yeu_cau_sua_lai">
                                    <i class="fa-solid fa-rotate-left text-warning me-1"></i> Yêu cầu GV chỉnh sửa đề cương & phản biện lại
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lý do / Hướng dẫn cụ thể <span class="text-danger">*</span></label>
                        <textarea name="LyDo" class="form-control" rows="4" required
                                  placeholder="Nêu rõ lý do và hướng dẫn cụ thể cho Giảng viên..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Xác Nhận Xử Lý
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Yêu Cầu Chỉnh Sửa -->
<div class="modal fade" id="modalYeuCauSua" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('truongbomon.duyet_detai.yeuCauChinhSua', $detai->MaDeTai) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-pen-to-square text-warning me-2"></i>
                        Yêu Cầu Giảng Viên Chỉnh Sửa Đề Tài
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nội dung / Ý kiến yêu cầu chỉnh sửa <span class="text-danger">*</span></label>
                        <textarea name="YeuCauSua" class="form-control" rows="5" required 
                                  placeholder="Ghi rõ nội dung đề cương, mục tiêu hoặc phương pháp nghiên cứu cần điều chỉnh..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning fw-bold">
                        <i class="fa-solid fa-paper-plane me-1"></i> Gửi Yêu Cầu Cho GV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Từ Chối -->
<div class="modal fade" id="modalTuChoi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('truongbomon.duyet_detai.tuChoi', $detai->MaDeTai) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="fa-solid fa-circle-xmark me-2"></i>
                        Từ Chối Đề Tài Này
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lý do từ chối đề tài <span class="text-danger">*</span></label>
                        <textarea name="LyDoTuChoi" class="form-control" rows="5" required 
                                  placeholder="Nhập lý do Bộ môn từ chối đề tài này..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger fw-bold">
                        <i class="fa-solid fa-ban me-1"></i> Xác Nhận Từ Chối
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
