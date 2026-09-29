@extends('layouts.giangvien')

@section('page_title', 'Đánh Giá Phản Biện Đề Cương - ' . ($detai->MaDeTai ?? ''))

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="{{ route('giangvien.phanbien.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-file-pen me-2 text-primary"></i>
                Phản Biện Đề Cương: {{ $detai->TenDeTai ?? '' }}
            </h4>
            <div class="text-muted small">
                <span>Mã đề tài: <strong class="text-primary">{{ $detai->MaDeTai ?? '' }}</strong></span>
                <span class="mx-2">•</span>
                <span>GV Đề xuất: <strong>{{ $detai->giangVien->HoTen ?? 'N/A' }}</strong></span>
                <span class="mx-2">•</span>
                <span>Bộ môn: <strong>{{ $detai->giangVien->boMon->TenBoMon ?? 'N/A' }}</strong></span>
            </div>
        </div>
        <div>
            @if($phanCong->KetQua === 'Đạt')
                <span class="badge bg-success px-3 py-2 fs-6"><i class="fa-solid fa-check me-1"></i> ĐÃ ĐÁNH GIÁ: ĐẠT</span>
            @elseif($phanCong->KetQua === 'Yêu cầu chỉnh sửa')
                <span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="fa-solid fa-pen me-1"></i> ĐÃ ĐÁNH GIÁ: YÊU CẦU SỬA</span>
            @elseif($phanCong->KetQua === 'Không đạt')
                <span class="badge bg-danger px-3 py-2 fs-6"><i class="fa-solid fa-xmark me-1"></i> ĐÃ ĐÁNH GIÁ: KHÔNG ĐẠT</span>
            @else
                <span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="fa-solid fa-clock me-1"></i> CHƯA ĐÁNH GIÁ</span>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Topic Content -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-file-lines text-primary me-2"></i>
                        Thông Tin Đề Tài & File Đề Cương
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Tên Đề Tài:</label>
                        <div class="fw-bold text-dark fs-6">{{ $detai->TenDeTai ?? '' }}</div>
                    </div>

                    @if($detai->TenDeTaiTiengAnh)
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Tên Tiếng Anh:</label>
                        <div class="text-secondary fst-italic">{{ $detai->TenDeTaiTiengAnh }}</div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Mục tiêu & Sản phẩm dự kiến:</label>
                        <div class="p-3 bg-light rounded" style="white-space: pre-line;">{{ $detai->MucTieu ?? 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Mô tả tóm tắt nội dung:</label>
                        <div class="p-3 bg-light rounded" style="white-space: pre-line;">{{ $detai->MoTa ?? 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Yêu cầu đối với sinh viên:</label>
                        <div class="p-3 bg-light rounded" style="white-space: pre-line;">{{ $detai->YeuCau ?? 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">File Đề Cương Đính Kèm Của GVHD:</label>
                        <div>
                            @if($detai->FileDeCuong)
                                <a href="{{ asset('storage/' . $detai->FileDeCuong) }}" target="_blank" class="btn btn-primary">
                                    <i class="fa-solid fa-file-pdf me-2"></i> Tải / Xem Đề Cương Chi Tiết (.pdf / .docx)
                                </a>
                            @else
                                <span class="text-danger small"><i class="fa-solid fa-circle-exclamation me-1"></i> Chưa có file đề cương đính kèm.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Evaluation Form -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-signature text-primary me-2"></i>
                        Phiếu Đánh Giá Phản Biện Đề Cương
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('giangvien.phanbien.store', $phanCong->MaPhanCong) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kết luận đánh giá đề cương <span class="text-danger">*</span></label>
                            <div class="d-flex flex-column gap-2 mt-1">
                                <div class="form-check p-2 border rounded" style="background: #f8fdf9;">
                                    <input class="form-check-input ms-1 me-2" type="radio" name="KetQua" id="kqDat" value="Đạt" 
                                           {{ old('KetQua', $phanCong->KetQua) === 'Đạt' ? 'checked' : '' }} required>
                                    <label class="form-check-label fw-bold text-success" for="kqDat">
                                        <i class="fa-solid fa-circle-check me-1"></i> ĐẠT YÊU CẦU
                                    </label>
                                    <div class="small text-muted ms-4">Đề cương phù hợp mục tiêu đào tạo, cấu trúc rõ ràng, tính khả thi cao.</div>
                                </div>

                                <div class="form-check p-2 border rounded" style="background: #fffdf5;">
                                    <input class="form-check-input ms-1 me-2" type="radio" name="KetQua" id="kqSua" value="Yêu cầu chỉnh sửa" 
                                           {{ old('KetQua', $phanCong->KetQua) === 'Yêu cầu chỉnh sửa' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-warning" for="kqSua">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> YÊU CẦU CHỈNH SỬA
                                    </label>
                                    <div class="small text-muted ms-4">Cần bổ sung phạm vi nghiên cứu, điều chỉnh mục tiêu hoặc phương pháp thực hiện.</div>
                                </div>

                                <div class="form-check p-2 border rounded" style="background: #fff8f8;">
                                    <input class="form-check-input ms-1 me-2" type="radio" name="KetQua" id="kqKhongDat" value="Không đạt" 
                                           {{ old('KetQua', $phanCong->KetQua) === 'Không đạt' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-danger" for="kqKhongDat">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> KHÔNG ĐẠT
                                    </label>
                                    <div class="small text-muted ms-4">Nội dung không phù hợp, trùng lặp hoặc không đảm bảo khối lượng tốt nghiệp.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nhận xét chi tiết & Ý kiến đóng góp <span class="text-danger">*</span></label>
                            <textarea name="NhanXet" class="form-control" rows="7" required
                                      placeholder="Ghi rõ nhận xét về tính cấp thiết, mục tiêu, đối tượng, phương pháp nghiên cứu và các điểm cần khắc phục...">{{ old('NhanXet', $phanCong->NhanXet) }}</textarea>
                            <div class="form-text text-muted">Ý kiến này sẽ được chuyển đến Giảng viên đề xuất và Trưởng bộ môn theo dõi.</div>
                        </div>

                        @if($phanCong->NgayDanhGia)
                            <div class="small text-muted mb-3">
                                <i class="fa-regular fa-clock me-1"></i> Lần đánh giá gần nhất: {{ \Carbon\Carbon::parse($phanCong->NgayDanhGia)->format('H:i d/m/Y') }}
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"
                                onclick="return confirm('Bạn có chắc chắn muốn gửi kết quả đánh giá phản biện này?');">
                            <i class="fa-solid fa-paper-plane me-1"></i> Gửi Kết Quả Đánh Giá Phản Biện
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
