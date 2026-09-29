@extends('layouts.truongkhoa')

@section('page_title', 'Chi Tiết Đề Tài ' . $detai->MaDeTai)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="{{ route('truongkhoa.duyet_detai.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-stamp me-2 text-danger"></i>
                Phê Duyệt Cấp Khoa: {{ $detai->TenDeTai }}
            </h4>
            <div class="text-muted small">
                <span>Mã đề tài: <strong class="text-danger">{{ $detai->MaDeTai }}</strong></span>
                <span class="mx-2">•</span>
                <span>Bộ môn: <strong>{{ $detai->giangVien->boMon->TenBoMon ?? 'N/A' }}</strong></span>
                <span class="mx-2">•</span>
                <span>Học kỳ: <strong>{{ $detai->hocKy->TenHocKy ?? $detai->MaHocKy }}</strong></span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($detai->TrangThai === 'Chờ duyệt cấp Khoa')
                <span class="badge bg-danger px-3 py-2 fs-6"><i class="fa-solid fa-clock me-1"></i> Chờ Trưởng khoa duyệt</span>
            @elseif($detai->TrangThai === 'Trưởng khoa đã duyệt')
                <span class="badge bg-success px-3 py-2 fs-6"><i class="fa-solid fa-stamp me-1"></i> Trưởng khoa đã duyệt</span>
            @elseif($detai->TrangThai === 'Đã công bố')
                <span class="badge bg-primary px-3 py-2 fs-6"><i class="fa-solid fa-bullhorn me-1"></i> Đã công bố</span>
            @elseif($detai->TrangThai === 'Yêu cầu chỉnh sửa')
                <span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="fa-solid fa-triangle-exclamation me-1"></i> Yêu cầu chỉnh sửa</span>
            @elseif($detai->TrangThai === 'Từ chối')
                <span class="badge bg-secondary px-3 py-2 fs-6"><i class="fa-solid fa-circle-xmark me-1"></i> Từ chối</span>
            @else
                <span class="badge bg-light text-muted border px-3 py-2 fs-6">{{ $detai->TrangThai }}</span>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Column -->
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

                    @if($detai->TenDeTaiTiengAnh)
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Tên Đề Tài (Tiếng Anh):</label>
                        <div class="text-secondary fst-italic">{{ $detai->TenDeTaiTiengAnh }}</div>
                    </div>
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Chuyên ngành áp dụng:</label>
                            <div><i class="fa-solid fa-graduation-cap text-muted me-1"></i>{{ $detai->nganh->TenNganh ?? 'Toàn ngành' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Số lượng sinh viên tối đa:</label>
                            <div><i class="fa-solid fa-users text-muted me-1"></i>{{ $detai->SoLuongSV ?? 3 }} sinh viên / nhóm</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Mục tiêu & Sản phẩm dự kiến:</label>
                        <div class="p-3 bg-light rounded" style="white-space: pre-line;">{{ $detai->MucTieu ?? 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Mô tả tóm tắt:</label>
                        <div class="p-3 bg-light rounded" style="white-space: pre-line;">{{ $detai->MoTa ?? 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Yêu cầu đối với sinh viên:</label>
                        <div class="p-3 bg-light rounded" style="white-space: pre-line;">{{ $detai->YeuCau ?? 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">File Đề cương chi tiết:</label>
                        <div>
                            @if($detai->FileDeCuong)
                                <a href="{{ asset('storage/' . $detai->FileDeCuong) }}" target="_blank" class="btn btn-outline-danger">
                                    <i class="fa-solid fa-file-pdf me-2"></i> Xem Đề Cương Đề Tài (.pdf / .docx)
                                </a>
                            @else
                                <span class="text-muted small">Không có file đính kèm.</span>
                            @endif
                        </div>
                    </div>

                    @if($detai->LyDoTuChoi)
                        <div class="alert alert-warning border-0 mt-3" style="border-left: 4px solid #ffc107 !important;">
                            <h6 class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Ý kiến phản hồi / Ghi chú:</h6>
                            <p class="mb-0">{{ $detai->LyDoTuChoi }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Actions & Evaluation Column -->
        <div class="col-lg-4">
            <!-- Department & Lecturer Details -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-building-columns text-primary me-2"></i>
                        Đơn Vị & Giảng Viên Đề Xuất
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="small text-muted fw-bold">Bộ môn:</div>
                        <div class="fw-bold text-dark">{{ $detai->giangVien->boMon->TenBoMon ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-muted fw-bold">Giảng viên hướng dẫn:</div>
                        <div class="fw-bold text-dark fs-6">{{ $detai->giangVien->HoTen ?? 'N/A' }}</div>
                        <div class="small text-muted">{{ $detai->giangVien->HocVi ?? '' }} (Mã GV: {{ $detai->giangVien->MaGV ?? '' }})</div>
                    </div>
                    <div class="border-top pt-2 small text-muted">
                        <div><i class="fa-solid fa-circle-check text-success me-1"></i> Bộ môn duyệt lúc: <strong>{{ $detai->NgayDuyetBM ? \Carbon\Carbon::parse($detai->NgayDuyetBM)->format('d/m/Y H:i') : 'Đã duyệt' }}</strong></div>
                    </div>
                </div>
            </div>

            <!-- Outline Review Details -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-file-pen text-info me-2"></i>
                        Kết Quả Phản Biện Đề Cương
                    </h6>
                </div>
                <div class="card-body">
                    @if($phanBienHienTai)
                        <div class="mb-2">
                            <div class="small text-muted fw-bold">Giảng viên phản biện:</div>
                            <div class="fw-bold text-dark">{{ $phanBienHienTai->giangVien->HoTen ?? 'N/A' }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted fw-bold">Đánh giá chuyên môn:</div>
                            <div class="mt-1">
                                @if($phanBienHienTai->KetQua === 'Đạt')
                                    <span class="badge bg-success px-3 py-2 fs-6"><i class="fa-solid fa-check me-1"></i> ĐẠT YÊU CẦU</span>
                                @elseif($phanBienHienTai->KetQua === 'Yêu cầu chỉnh sửa')
                                    <span class="badge bg-warning text-dark px-3 py-2"><i class="fa-solid fa-pen me-1"></i> Yêu cầu chỉnh sửa</span>
                                @elseif($phanBienHienTai->KetQua === 'Không đạt')
                                    <span class="badge bg-danger px-3 py-2"><i class="fa-solid fa-xmark me-1"></i> Không đạt</span>
                                @else
                                    <span class="badge bg-light text-muted border">Chưa có kết quả</span>
                                @endif
                            </div>
                        </div>

                        @if($phanBienHienTai->NhanXet)
                            <div class="mb-2">
                                <div class="small text-muted fw-bold">Nhận xét của GV phản biện:</div>
                                <div class="p-3 bg-light rounded mt-1 small" style="white-space: pre-line;">{{ $phanBienHienTai->NhanXet }}</div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3 text-muted small">
                            <i class="fa-solid fa-circle-question fa-2x mb-2 text-secondary"></i>
                            <div>Chưa có thông tin phản biện đề cương.</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- TK Decision Actions -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-sliders text-danger me-2"></i>
                        Quyết Định Của Trưởng Khoa
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <!-- Nút Phê Duyệt Cấp Khoa -->
                        <form action="{{ route('truongkhoa.duyet_detai.duyet', $detai->MaDeTai) }}" method="POST"
                              onsubmit="return confirm('Bạn có chắc chắn muốn PHÊ DUYỆT đề tài này ở cấp Khoa? Đề tài sẽ sẵn sàng để Giáo vụ công bố cho sinh viên.');">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                                <i class="fa-solid fa-stamp me-1"></i> Phê Duyệt Cấp Khoa
                            </button>
                        </form>

                        <!-- Nút Yêu Cầu Chỉnh Sửa -->
                        <button type="button" class="btn btn-outline-warning w-100 py-2" data-bs-toggle="modal" data-bs-target="#modalTKYeuCauSua">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Yêu Cầu Chỉnh Sửa
                        </button>

                        <!-- Nút Từ Chối -->
                        <button type="button" class="btn btn-outline-danger w-100 py-2" data-bs-toggle="modal" data-bs-target="#modalTKTuChoi">
                            <i class="fa-solid fa-circle-xmark me-1"></i> Từ Chối Đề Tài
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Yêu Cầu Chỉnh Sửa -->
<div class="modal fade" id="modalTKYeuCauSua" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
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
                        <label class="form-label fw-bold">Nội dung ý kiến chỉ đạo / Yêu cầu chỉnh sửa <span class="text-danger">*</span></label>
                        <textarea name="YeuCauSua" class="form-control" rows="5" required 
                                  placeholder="Ghi rõ nội dung cần giảng viên và bộ môn điều chỉnh lại..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning fw-bold">
                        <i class="fa-solid fa-paper-plane me-1"></i> Gửi Yêu Cầu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Từ Chối -->
<div class="modal fade" id="modalTKTuChoi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('truongkhoa.duyet_detai.tuChoi', $detai->MaDeTai) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="fa-solid fa-circle-xmark me-2"></i>
                        Từ Chối Phê Duyệt Đề Tài
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lý do từ chối cấp Khoa <span class="text-danger">*</span></label>
                        <textarea name="LyDoTuChoi" class="form-control" rows="5" required 
                                  placeholder="Nhập lý do không phê duyệt đề tài này..."></textarea>
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
