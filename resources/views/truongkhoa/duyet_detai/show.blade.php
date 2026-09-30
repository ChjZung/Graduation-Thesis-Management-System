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

    <!-- ═══ STEPPER: QUY TRÌNH DUYỆT ĐỀ TÀI 5 BƯỚC ═══ -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 text-center" style="font-size: 0.8rem;">
                <!-- Bước 1 -->
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success rounded-circle p-2"><i class="fa-solid fa-check"></i></span>
                    <div class="text-start">
                        <div class="fw-bold text-success">1. GV Đề Xuất</div>
                        <div class="text-muted small">{{ $detai->giangVien->HoTen ?? 'Giảng viên' }}</div>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-muted d-none d-md-block"></i>

                <!-- Bước 2 -->
                <div class="d-flex align-items-center gap-2">
                    <span class="badge {{ $phanBienHienTai ? 'bg-success' : 'bg-secondary' }} rounded-circle p-2">
                        <i class="fa-solid {{ $phanBienHienTai ? 'fa-check' : 'fa-user-plus' }}"></i>
                    </span>
                    <div class="text-start">
                        <div class="fw-bold {{ $phanBienHienTai ? 'text-success' : 'text-muted' }}">2. TBM Phân Công PB</div>
                        <div class="text-muted small">{{ $phanBienHienTai ? 'Đã phân công' : 'Chưa phân công' }}</div>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-muted d-none d-md-block"></i>

                <!-- Bước 3 -->
                <div class="d-flex align-items-center gap-2">
                    @php
                        $isPbDone = $phanBienHienTai && in_array($phanBienHienTai->KetQua, ['Đạt', 'Yêu cầu chỉnh sửa', 'Không đạt']);
                    @endphp
                    <span class="badge {{ $isPbDone ? 'bg-success' : 'bg-secondary' }} rounded-circle p-2">
                        <i class="fa-solid {{ $isPbDone ? 'fa-check' : 'fa-file-pen' }}"></i>
                    </span>
                    <div class="text-start">
                        <div class="fw-bold {{ $isPbDone ? 'text-success' : 'text-muted' }}">3. Phản Biện Đề Cương</div>
                        <div class="text-muted small">{{ $isPbDone ? ($phanBienHienTai->KetQua ?? 'Đã xong') : 'Đang xử lý' }}</div>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-muted d-none d-md-block"></i>

                <!-- Bước 4 -->
                <div class="d-flex align-items-center gap-2">
                    @php
                        $isBmDone = $detai->NgayDuyetBM || in_array($detai->TrangThai, ['Chờ duyệt cấp Khoa', 'Trưởng khoa đã duyệt', 'Đã công bố']);
                    @endphp
                    <span class="badge {{ $isBmDone ? 'bg-success' : 'bg-secondary' }} rounded-circle p-2">
                        <i class="fa-solid {{ $isBmDone ? 'fa-check' : 'fa-clipboard-check' }}"></i>
                    </span>
                    <div class="text-start">
                        <div class="fw-bold {{ $isBmDone ? 'text-success' : 'text-muted' }}">4. TBM Phê Duyệt</div>
                        <div class="text-muted small">{{ $isBmDone ? 'Đã thông qua' : 'Chờ duyệt' }}</div>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-muted d-none d-md-block"></i>

                <!-- Bước 5: Trưởng Khoa -->
                <div class="d-flex align-items-center gap-2">
                    @if(in_array($detai->TrangThai, ['Trưởng khoa đã duyệt', 'Đã công bố']))
                        <span class="badge bg-success rounded-circle p-2"><i class="fa-solid fa-stamp"></i></span>
                        <div class="text-start">
                            <div class="fw-bold text-success">5. Trưởng Khoa Duyệt</div>
                            <div class="text-muted small">Đã phê duyệt</div>
                        </div>
                    @elseif($detai->TrangThai === 'Yêu cầu chỉnh sửa')
                        <span class="badge bg-warning rounded-circle p-2"><i class="fa-solid fa-pen"></i></span>
                        <div class="text-start">
                            <div class="fw-bold text-warning">5. Trưởng Khoa</div>
                            <div class="text-muted small">Yêu cầu chỉnh sửa</div>
                        </div>
                    @elseif($detai->TrangThai === 'Từ chối')
                        <span class="badge bg-danger rounded-circle p-2"><i class="fa-solid fa-xmark"></i></span>
                        <div class="text-start">
                            <div class="fw-bold text-danger">5. Trưởng Khoa</div>
                            <div class="text-muted small">Đã từ chối</div>
                        </div>
                    @else
                        <span class="badge bg-danger rounded-circle p-2"><i class="fa-solid fa-stamp"></i></span>
                        <div class="text-start">
                            <div class="fw-bold text-danger">5. Trưởng Khoa Phê Duyệt</div>
                            <div class="text-danger small fw-semibold">Đang chờ xử lý</div>
                        </div>
                    @endif
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
                                <a href="{{ asset('storage/' . $detai->FileDeCuong) }}" target="_blank" class="btn btn-outline-danger">
                                    <i class="fa-solid fa-file-pdf me-2"></i> Xem Đề Cương Chi Tiết (.pdf / .docx)
                                </a>
                            @else
                                <span class="text-muted small fst-italic">Không có file đề cương đính kèm.</span>
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

            <!-- 2. Kết Quả Đánh Giá Phản Biện Đề Cương -->
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
                            <div class="small text-muted">{{ $phanBienHienTai->giangVien->HocVi ?? '' }} ({{ $phanBienHienTai->giangVien->boMon->TenBoMon ?? '' }})</div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted fw-bold">Kết luận đánh giá chuyên môn:</div>
                            <div class="mt-1">
                                @if($phanBienHienTai->KetQua === 'Đạt')
                                    <span class="badge bg-success px-3 py-2 fs-6"><i class="fa-solid fa-check me-1"></i> ĐẠT YÊU CẦU</span>
                                @elseif($phanBienHienTai->KetQua === 'Yêu cầu chỉnh sửa')
                                    <span class="badge bg-warning text-dark px-3 py-2"><i class="fa-solid fa-pen me-1"></i> Yêu cầu chỉnh sửa</span>
                                @elseif($phanBienHienTai->KetQua === 'Không đạt')
                                    <span class="badge bg-danger px-3 py-2"><i class="fa-solid fa-xmark me-1"></i> Không đạt</span>
                                @else
                                    <span class="badge bg-light text-muted border">Đang phản biện</span>
                                @endif
                            </div>
                        </div>

                        @if($phanBienHienTai->NhanXet)
                            <div class="mb-2">
                                <div class="small text-muted fw-bold">Nhận xét chi tiết của GV phản biện:</div>
                                <div class="p-3 bg-light rounded mt-1 small" style="white-space: pre-line;">{{ $phanBienHienTai->NhanXet }}</div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3 text-muted small">
                            <i class="fa-solid fa-circle-question fa-2x mb-2 text-secondary"></i>
                            <div>Đề tài này chưa có thông tin phân công phản biện đề cương.</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. Quyết Định Của Trưởng Khoa -->
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
                              onsubmit="return confirm('Xác nhận PHÊ DUYỆT đề tài \'{{ $detai->TenDeTai }}\' cấp Khoa?\nĐề tài sẽ sẵn sàng để Giáo vụ Khoa công bố cho sinh viên.');">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
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
