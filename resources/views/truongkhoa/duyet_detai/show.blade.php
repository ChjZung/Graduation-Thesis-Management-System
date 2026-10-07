@extends('layouts.truongkhoa')

@section('page_title', 'Chi Tiết & Phê Duyệt Đề Tài ' . $detai->MaDeTai)

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="{{ route('truongkhoa.duyet_detai.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách đề tài
            </a>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-stamp me-2 text-primary"></i>
                Phê Duyệt Đề Tài Cấp Khoa: {{ $detai->TenDeTai }}
            </h4>
            <div class="text-muted small">
                <span>Mã đề tài: <span class="badge-code">{{ $detai->MaDeTai }}</span></span>
                <span class="mx-2">•</span>
                <span>Bộ môn: <strong>{{ $detai->giangVien->boMon->TenBoMon ?? 'N/A' }}</strong></span>
                <span class="mx-2">•</span>
                <span>Học kỳ: <strong>{{ $detai->hocKy->TenHocKy ?? $detai->MaHocKy }}</strong></span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($detai->TrangThai === 'Chờ duyệt cấp Khoa')
                <span class="badge bg-danger px-3 py-2 fs-6"><i class="fa-solid fa-clock me-1"></i> Chờ Trưởng khoa duyệt</span>
            @elseif(in_array($detai->TrangThai, ['Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương']))
                <span class="badge bg-success px-3 py-2 fs-6"><i class="fa-solid fa-stamp me-1"></i> Trưởng khoa đã duyệt (Chờ nộp đề cương)</span>
            @elseif($detai->TrangThai === 'Đã nộp đề cương - Chờ phân công PB')
                <span class="badge bg-info px-3 py-2 fs-6"><i class="fa-solid fa-file-circle-check me-1"></i> Đã nộp ĐC - Chờ phân công PB</span>
            @elseif($detai->TrangThai === 'Đang phản biện đề cương')
                <span class="badge bg-info px-3 py-2 fs-6"><i class="fa-solid fa-file-pen me-1"></i> Đang phản biện đề cương</span>
            @elseif(in_array($detai->TrangThai, ['Đã phản biện - Chờ TBM duyệt đề cương', 'Đã phản biện - Chờ duyệt BM']))
                <span class="badge bg-primary px-3 py-2 fs-6"><i class="fa-solid fa-check-double me-1"></i> Đã PB - Chờ TBM duyệt &amp; công bố</span>
            @elseif($detai->TrangThai === 'Đã công bố')
                <span class="badge bg-success px-3 py-2 fs-6"><i class="fa-solid fa-bullhorn me-1"></i> Đã công bố</span>
            @elseif(in_array($detai->TrangThai, ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương']))
                <span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="fa-solid fa-triangle-exclamation me-1"></i> Yêu cầu chỉnh sửa</span>
            @elseif(in_array($detai->TrangThai, ['Từ chối', 'Không đạt phản biện']))
                <span class="badge bg-secondary px-3 py-2 fs-6"><i class="fa-solid fa-circle-xmark me-1"></i> Từ chối</span>
            @else
                <span class="badge bg-light text-muted border px-3 py-2 fs-6">{{ $detai->TrangThai }}</span>
            @endif
        </div>
    </div>

    <!-- ═══ STEPPER: QUY TRÌNH DUYỆT ĐỀ TÀI 5 BƯỚC ═══ -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 text-center" style="font-size: 0.8rem;">
                @php
                    $isBmApproved1 = in_array($detai->TrangThai, ['Chờ duyệt cấp Khoa', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Trưởng khoa đã duyệt', 'Đã nộp đề cương - Chờ phân công PB', 'Đang phản biện đề cương', 'Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương', 'Đã công bố']);
                    $isKhoaApproved = in_array($detai->TrangThai, ['Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Trưởng khoa đã duyệt', 'Đã nộp đề cương - Chờ phân công PB', 'Đang phản biện đề cương', 'Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương', 'Đã công bố']);
                    $isOutlineSubmitted = (bool)$detai->FileDeCuong;
                    $isReviewed = $phanBienHienTai && $phanBienHienTai->KetQua;
                    $isPublished = $detai->TrangThai === 'Đã công bố';
                @endphp

                <!-- Bước 1: GV Đề Xuất -->
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success rounded-circle p-2"><i class="fa-solid fa-check"></i></span>
                    <div class="text-start">
                        <div class="fw-bold text-success">1. GV Đề Xuất</div>
                        <div class="text-muted small">{{ $detai->giangVien->HoTen ?? 'Giảng viên' }}</div>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-muted d-none d-md-block"></i>

                <!-- Bước 2: TBM Duyệt Đề Xuất -->
                <div class="d-flex align-items-center gap-2">
                    <span class="badge {{ $isBmApproved1 ? 'bg-success' : 'bg-warning' }} rounded-circle p-2">
                        <i class="fa-solid {{ $isBmApproved1 ? 'fa-check' : 'fa-building-columns' }}"></i>
                    </span>
                    <div class="text-start">
                        <div class="fw-bold {{ $isBmApproved1 ? 'text-success' : 'text-warning' }}">2. BM Duyệt Đề Xuất</div>
                        <div class="text-muted small">{{ $detai->NgayDuyetBM ? \Carbon\Carbon::parse($detai->NgayDuyetBM)->format('d/m/Y') : 'Chờ TBM' }}</div>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-muted d-none d-md-block"></i>

                <!-- Bước 3: Khoa Phê Duyệt -->
                <div class="d-flex align-items-center gap-2">
                    <span class="badge {{ $isKhoaApproved ? 'bg-success' : ($detai->TrangThai === 'Chờ duyệt cấp Khoa' ? 'bg-danger' : 'bg-secondary') }} rounded-circle p-2">
                        <i class="fa-solid {{ $isKhoaApproved ? 'fa-check' : 'fa-stamp' }}"></i>
                    </span>
                    <div class="text-start">
                        <div class="fw-bold {{ $isKhoaApproved ? 'text-success' : ($detai->TrangThai === 'Chờ duyệt cấp Khoa' ? 'text-danger' : 'text-muted') }}">3. Khoa Phê Duyệt</div>
                        <div class="small {{ $isKhoaApproved ? 'text-muted' : ($detai->TrangThai === 'Chờ duyệt cấp Khoa' ? 'text-danger fw-semibold' : 'text-muted') }}">
                            {{ $detai->NgayDuyetKhoa ? \Carbon\Carbon::parse($detai->NgayDuyetKhoa)->format('d/m/Y') : ($detai->TrangThai === 'Chờ duyệt cấp Khoa' ? 'Chờ Khoa duyệt' : 'Chưa đến lượt') }}
                        </div>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-muted d-none d-md-block"></i>

                <!-- Bước 4: Phản Biện Đề Cương -->
                <div class="d-flex align-items-center gap-2">
                    <span class="badge {{ $isReviewed ? 'bg-success' : ($isOutlineSubmitted ? 'bg-info' : 'bg-secondary') }} rounded-circle p-2">
                        <i class="fa-solid {{ $isReviewed ? 'fa-check' : 'fa-file-signature' }}"></i>
                    </span>
                    <div class="text-start">
                        <div class="fw-bold {{ $isReviewed ? 'text-success' : ($isOutlineSubmitted ? 'text-info' : 'text-muted') }}">4. Phản Biện Đề Cương</div>
                        <div class="text-muted small">
                            {{ $isReviewed ? $phanBienHienTai->KetQua : ($phanBienHienTai ? 'Đang phản biện' : ($isOutlineSubmitted ? 'Đã nộp ĐC' : 'Chờ nộp ĐC')) }}
                        </div>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-muted d-none d-md-block"></i>

                <!-- Bước 5: BM Duyệt & Công Bố -->
                <div class="d-flex align-items-center gap-2">
                    <span class="badge {{ $isPublished ? 'bg-success' : 'bg-secondary' }} rounded-circle p-2">
                        <i class="fa-solid {{ $isPublished ? 'fa-check' : 'fa-bullhorn' }}"></i>
                    </span>
                    <div class="text-start">
                        <div class="fw-bold {{ $isPublished ? 'text-success' : 'text-muted' }}">5. BM Duyệt &amp; Công Bố</div>
                        <div class="text-muted small">{{ $detai->NgayCongBo ? \Carbon\Carbon::parse($detai->NgayCongBo)->format('d/m/Y') : 'Chờ công bố' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Column: Thông tin đề tài -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-circle-info text-danger me-2"></i>
                        Thông Tin Đề Tài Khóa Luận
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Tên Đề Tài (Tiếng Việt):</label>
                        <div class="fs-5 fw-bold text-dark">{{ $detai->TenDeTai }}</div>
                    </div>

                    @if($detai->TenDeTaiTiengAnh)
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Tên Đề Tài (Tiếng Anh):</label>
                        <div class="text-secondary fst-italic">{{ $detai->TenDeTaiTiengAnh }}</div>
                    </div>
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">Chuyên ngành áp dụng:</label>
                            <div><i class="fa-solid fa-graduation-cap text-primary me-1"></i>{{ $detai->nganh->TenNganh ?? 'Toàn ngành' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">Lĩnh vực nghiên cứu:</label>
                            <div><span class="badge bg-light text-secondary border">{{ $detai->LinhVuc ?? 'Không phân loại' }}</span></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">Số lượng sinh viên:</label>
                            <div><i class="fa-solid fa-users text-primary me-1"></i>Tối đa {{ $detai->SoLuongSV ?? 3 }} sinh viên</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Mục tiêu & Sản phẩm dự kiến:</label>
                        <div class="p-3 bg-light rounded" style="white-space: pre-line;">{{ $detai->MucTieu ?? 'Chưa cập nhật mục tiêu cụ thể.' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Mô tả tóm tắt nội dung đề tài:</label>
                        <div class="p-3 bg-light rounded" style="white-space: pre-line;">{{ $detai->MoTa ?? 'Chưa cập nhật nội dung tóm tắt.' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Yêu cầu đối với sinh viên thực hiện:</label>
                        <div class="p-3 bg-light rounded" style="white-space: pre-line;">{{ $detai->YeuCau ?? 'Chưa có yêu cầu tiên quyết cụ thể.' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">File Đề Cương Chi Tiết:</label>
                        <div>
                            @if($detai->FileDeCuong)
                                @php
                                    $filePath = Str::startsWith($detai->FileDeCuong, ['http', 'storage/']) ? asset($detai->FileDeCuong) : asset('storage/' . $detai->FileDeCuong);
                                    $fileName = basename($detai->FileDeCuong);
                                @endphp
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <button type="button" class="btn btn-primary shadow-sm" 
                                            onclick="quickPreviewOutline('{{ $filePath }}', '{{ $fileName }}', '{{ addslashes($detai->TenDeTai) }}')">
                                        <i class="fa-solid fa-eye me-2"></i> Xem Nhanh Đề Cương
                                    </button>
                                    <a href="{{ $filePath }}" target="_blank" class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở tab mới
                                    </a>
                                    <a href="{{ $filePath }}" download="{{ $fileName }}" class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-download me-1"></i> Tải file
                                    </a>
                                </div>
                                <div class="small text-muted mt-2">
                                    <i class="fa-solid fa-paperclip me-1 text-primary"></i> Tệp: <strong>{{ $fileName }}</strong>
                                </div>
                            @else
                                <span class="badge bg-light text-muted border px-3 py-2 fw-normal">
                                    <i class="fa-solid fa-clock me-1 text-secondary"></i> Chưa nộp đề cương (Giảng viên sẽ nộp sau khi Trưởng khoa phê duyệt chủ trương)
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($detai->LyDoTuChoi)
                        <div class="alert alert-warning border-0 mt-3" style="border-left: 4px solid #ffc107 !important;">
                            <h6 class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Ý kiến phản hồi / Lý do trước đó:</h6>
                            <p class="mb-0">{{ $detai->LyDoTuChoi }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Column: Đơn vị, Kết quả phản biện & Quyết định Trưởng Khoa -->
        <div class="col-lg-4">
            <!-- 1. Đơn Vị & Giảng Viên Đề Xuất -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-building-columns text-primary me-2"></i>
                        Đơn Vị & Giảng Viên Hướng Dẫn
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="small text-muted fw-bold">Bộ môn:</div>
                        <div class="fw-bold text-dark fs-6">{{ $detai->giangVien->boMon->TenBoMon ?? 'N/A' }}</div>
                        <div class="small text-muted">Mã BM: {{ $detai->giangVien->boMon->MaBoMon ?? '' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-muted fw-bold">Giảng viên đề xuất / GVHD:</div>
                        <div class="fw-bold text-dark fs-6">{{ $detai->giangVien->HoTen ?? 'N/A' }}</div>
                        <div class="small text-muted">{{ $detai->giangVien->HocVi ?? 'Giảng viên' }} – {{ $detai->giangVien->Email ?? '' }}</div>
                    </div>
                    <div class="border-top pt-2 small text-muted">
                        <div>
                            <i class="fa-solid fa-circle-check text-success me-1"></i>
                            Bộ môn duyệt: 
                            <strong>{{ $detai->NgayDuyetBM ? \Carbon\Carbon::parse($detai->NgayDuyetBM)->format('d/m/Y H:i') : 'Đã duyệt' }}</strong>
                        </div>
                        @if($detai->NguoiDuyetBM)
                            <div class="mt-1">Người duyệt BM: <strong>{{ $detai->NguoiDuyetBM }}</strong></div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 2. Quyết Định Của Trưởng Khoa -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-stamp text-primary me-2"></i>
                        Quyết Định Phê Duyệt Của Trưởng Khoa
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <!-- Nút 1: Phê Duyệt Cấp Khoa -->
                        <form action="{{ route('truongkhoa.duyet_detai.duyet', $detai->MaDeTai) }}" method="POST"
                              onsubmit="return confirm('Xác nhận PHÊ DUYỆT đề xuất đề tài \'{{ $detai->TenDeTai }}\' cấp Khoa?\nSau khi phê duyệt, hệ thống sẽ gửi thông báo yêu cầu Giảng viên nộp Đề cương chi tiết để Bộ môn phân công phản biện.');">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold" {{ $detai->TrangThai !== 'Chờ duyệt cấp Khoa' ? 'disabled' : '' }}>
                                <i class="fa-solid fa-stamp me-1"></i> Phê Duyệt Cấp Khoa
                            </button>
                        </form>

                        <!-- Nút 2: Yêu Cầu Chỉnh Sửa -->
                        <button type="button" class="btn btn-outline-warning w-100 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#modalTKYeuCauSua">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Yêu Cầu Chỉnh Sửa
                        </button>

                        <!-- Nút 3: Từ Chối -->
                        <button type="button" class="btn btn-outline-danger w-100 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#modalTKTuChoi">
                            <i class="fa-solid fa-circle-xmark me-1"></i> Từ Chối Đề Tài
                        </button>
                    </div>

                    @if($detai->NgayDuyetKhoa)
                        <div class="mt-3 p-2 bg-success-subtle text-success small rounded text-center">
                            <i class="fa-solid fa-circle-check me-1"></i>
                            Đã duyệt lúc: <strong>{{ \Carbon\Carbon::parse($detai->NgayDuyetKhoa)->format('d/m/Y H:i') }}</strong>
                            @if($detai->NguoiDuyetKhoa)
                                (Bởi: {{ $detai->NguoiDuyetKhoa }})
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Yêu Cầu Chỉnh Sửa -->
<div class="modal fade" id="modalTKYeuCauSua" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px;">
            <form action="{{ route('truongkhoa.duyet_detai.yeuCauChinhSua', $detai->MaDeTai) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-pen-to-square text-warning me-2"></i>
                        Ý Kiến Yêu Cầu Chỉnh Sửa Của Trưởng Khoa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nội dung ý kiến chỉ đạo / Yêu cầu điều chỉnh <span class="text-danger">*</span></label>
                        <textarea name="YeuCauSua" class="form-control" rows="5" required 
                                  placeholder="Ghi rõ nội dung cần giảng viên và bộ môn điều chỉnh lại (ví dụ: giới hạn lại phạm vi nghiên cứu, làm rõ sản phẩm bàn giao)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning fw-bold">
                        <i class="fa-solid fa-paper-plane me-1"></i> Gửi Yêu Cầu Chỉnh Sửa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Từ Chối -->
<div class="modal fade" id="modalTKTuChoi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px;">
            <form action="{{ route('truongkhoa.duyet_detai.tuChoi', $detai->MaDeTai) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="fa-solid fa-circle-xmark me-2"></i>
                        Từ Chối Phê Duyệt Đề Tài Cấp Khoa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lý do từ chối phê duyệt <span class="text-danger">*</span></label>
                        <textarea name="LyDoTuChoi" class="form-control" rows="5" required 
                                  placeholder="Nhập lý do không chấp thuận phê duyệt đề tài này (ví dụ: trùng lặp hướng nghiên cứu đã nghiệm thu, không phù hợp định hướng đào tạo)..."></textarea>
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
