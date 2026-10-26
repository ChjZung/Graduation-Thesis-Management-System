@extends('layouts.giangvien')

@section('page_title', 'Đánh Giá Báo Cáo Chỉnh Sửa — ' . ($hoSo->nhom->TenNhom ?? $hoSo->MaHoSo))

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div class="mb-1">
                <a href="{{ route('giangvien.kiemtra_baocao_sau_baove.index') }}" class="text-decoration-none small text-muted">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Danh Sách Hồ Sơ
                </a>
            </div>
            <h4 class="fw-bold mb-0" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-file-signature me-2 text-primary"></i>
                Kiểm Tra &amp; Đánh Giá Báo Cáo Hoàn Thiện Sau Bảo Vệ
            </h4>
        </div>
        <div>
            @if($hoSo->TrangThaiChinhSuaSauBaoVe === 'DaHoanThanh')
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-bold">
                    <i class="fa-solid fa-circle-check me-1"></i> Báo Cáo Đã Xác Nhận Đạt Chuẩn Lưu Trữ
                </span>
            @endif
        </div>
    </div>

    <!-- Thông Tin Hồ Sơ & Đề Tài -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary text-white">{{ $hoSo->MaHoSo }}</span>
                        <span class="badge bg-light text-secondary border font-monospace">{{ $hoSo->LoaiKhoaLuan ?? 'KLCN' }}</span>
                    </div>
                    <h5 class="fw-bold text-primary mb-1">{{ $hoSo->nhom->deTai->TenDeTai ?? 'Chưa xác định' }}</h5>
                    <div class="small text-muted">
                        <span>Nhóm: <strong>{{ $hoSo->nhom->TenNhom ?? '' }}</strong></span> &bull; 
                        <span>GVHD: <strong>{{ $hoSo->nhom->deTai->giangVien->HoTen ?? 'Chưa rõ' }}</strong></span> &bull; 
                        <span>Hội đồng: <strong>{{ $hoSo->hoiDong->TenHoiDong ?? '' }}</strong> ({{ $hoSo->PhongBaoVe ?? ($hoSo->hoiDong->DiaDiem ?? 'Phòng B.304') }})</span>
                    </div>
                </div>

                <div class="col-12 pt-2 border-top">
                    <div class="small fw-bold text-secondary mb-2">Danh sách thành viên thực hiện:</div>
                    <div class="row g-2">
                        @foreach($hoSo->nhom->thanhViens as $idx => $tv)
                        <div class="col-md-4">
                            <div class="p-2 bg-light rounded-2 border">
                                <div class="fw-bold text-dark small">{{ $tv->sinhVien->HoTen ?? $tv->MaSV }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">MSSV: {{ $tv->sinhVien->MaSoSinhVien ?? $tv->MaSV }} &bull; {{ $tv->sinhVien->lop->TenLop ?? 'Lớp' }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Khối 1: Yêu Cầu Chỉnh Sửa Ban Đầu Của Hội Đồng -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white" style="border-left: 5px solid #0072ce !important;">
        <div class="card-header bg-white border-bottom py-3">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-list-check text-primary me-2"></i>Nội Dung Hội Đồng Bảo Vệ Yêu Cầu Chỉnh Sửa
            </span>
        </div>
        <div class="card-body p-4">
            <div class="p-3 rounded-3 bg-light border text-dark mb-3" style="white-space: pre-line; line-height: 1.6;">{{ $hoSo->YeuCauChinhSuaSauBaoVe }}</div>

            <div class="row g-2 small text-muted">
                <div class="col-md-6">
                    <span>Thời hạn nộp lại:</span>
                    <strong class="text-dark ms-1">
                        {{ $hoSo->HanNopBaoCaoSauBaoVe ? \Carbon\Carbon::parse($hoSo->HanNopBaoCaoSauBaoVe)->format('d/m/Y H:i') : 'Chưa thiết lập' }}
                    </strong>
                </div>
                <div class="col-md-6">
                    <span>Người được phân công kiểm tra:</span>
                    <strong class="text-dark ms-1">{{ $hoSo->NguoiKiemTraChinhSua ?? 'GVHD' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Khối 2: Bản Báo Cáo Chỉnh Sửa Mới Nhất Do Nhóm Nộp -->
    @if(!$latestBaoCao)
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center bg-white mb-4 text-muted">
        <i class="fa-solid fa-file-arrow-up fa-3x mb-3 text-secondary opacity-50"></i>
        <h6>Nhóm sinh viên hiện chưa nộp lại bản báo cáo chỉnh sửa sau bảo vệ.</h6>
    </div>
    @else
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white" style="border-left: 5px solid #198754 !important;">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-file-pdf text-danger me-2"></i>Bản Báo Cáo Chỉnh Sửa Mới Nhất (Lần {{ $latestBaoCao->LanChinhSua }})
            </span>
            <span class="badge {{ $latestBaoCao->TrangThai === 'Đạt' ? 'bg-success' : ($latestBaoCao->TrangThai === 'Yêu cầu sửa tiếp' ? 'bg-danger' : 'bg-warning text-dark') }} rounded-pill px-3 py-2">
                {{ $latestBaoCao->TrangThai }}
            </span>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-7">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark small"><i class="fa-solid fa-paperclip me-1 text-primary"></i> Tệp Báo Cáo Đính Kèm:</span>
                            <span class="text-muted small">{{ $latestBaoCao->DungLuong ? round($latestBaoCao->DungLuong / 1024 / 1024, 2) . ' MB' : '' }}</span>
                        </div>
                        <div class="fw-bold text-primary mb-2">
                            <i class="fa-solid fa-file-pdf text-danger me-1"></i>{{ $latestBaoCao->TenFile }}
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ asset('storage/' . $latestBaoCao->DuongDanFile) }}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                                <i class="fa-solid fa-eye me-1"></i> Mở Xem Báo Cáo PDF
                            </a>
                            <a href="{{ asset('storage/' . $latestBaoCao->DuongDanFile) }}" download class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                <i class="fa-solid fa-download me-1"></i> Tải Báo Cáo
                            </a>
                            @if($latestBaoCao->FileGiaiTrinh)
                            <a href="{{ asset('storage/' . $latestBaoCao->FileGiaiTrinh) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-semibold">
                                <i class="fa-solid fa-file-lines me-1"></i> Xem Bản Giải Trình
                            </a>
                            @endif
                            @if($latestBaoCao->FileMinhChung)
                            <a href="{{ asset('storage/' . $latestBaoCao->FileMinhChung) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
                                <i class="fa-solid fa-paperclip me-1"></i> Tải Minh Chứng
                            </a>
                            @endif
                        </div>
                        <div class="small text-muted mt-3">
                            <i class="fa-regular fa-clock me-1"></i> Nộp lúc: <strong>{{ \Carbon\Carbon::parse($latestBaoCao->NgayNop)->format('d/m/Y H:i') }}</strong> 
                            &bull; Người nộp: <strong>{{ $latestBaoCao->sinhVienNop->HoTen ?? $latestBaoCao->NguoiNop }}</strong>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <div class="fw-bold text-dark small mb-2"><i class="fa-solid fa-quote-left text-primary me-1"></i> Tóm Tắt Nội Dung Tiếp Thu &amp; Chỉnh Sửa Của Nhóm:</div>
                        <div class="text-secondary small" style="white-space: pre-line; line-height: 1.5;">{{ $latestBaoCao->TomTatChinhSua }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Khối 3: Form Đánh Giá & Xác Nhận Báo Cáo Chỉnh Sửa -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-header bg-white border-bottom py-3">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-gavel text-primary me-2"></i>Ý Kiến Đánh Giá &amp; Xác Nhận Của Giảng Viên Kiểm Tra
            </span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('giangvien.kiemtra_baocao_sau_baove.danhgia', $hoSo->MaHoSo) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Kết quả đánh giá bản chỉnh sửa <span class="text-danger">*</span>:</label>
                    <div class="d-flex gap-4 p-3 bg-light rounded-3 border">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="HanhDong" id="actionDat" value="dat" {{ $latestBaoCao->TrangThai === 'Đạt' ? 'checked' : '' }} required onchange="toggleHanNop(this.value)">
                            <label class="form-check-label fw-bold text-success" for="actionDat">
                                <i class="fa-solid fa-circle-check me-1"></i> ĐẠT YÊU CẦU (Xác nhận hoàn thành lưu trữ Khoa)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="HanhDong" id="actionSuaTiep" value="yeu_cau_sua_tiep" {{ $latestBaoCao->TrangThai === 'Yêu cầu sửa tiếp' ? 'checked' : '' }} onchange="toggleHanNop(this.value)">
                            <label class="form-check-label fw-bold text-danger" for="actionSuaTiep">
                                <i class="fa-solid fa-rotate me-1"></i> CHƯA ĐẠT (Yêu cầu nhóm chỉnh sửa tiếp)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3" id="groupHanNopMoi" style="display: none;">
                    <label class="form-label small fw-bold text-dark">Gia hạn thời hạn nộp lại mới (nếu yêu cầu sửa tiếp):</label>
                    <input type="datetime-local" name="HanNopMoi" class="form-control" style="max-width: 320px;"
                           value="{{ now()->addDays(3)->format('Y-m-d\T17:00') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Ý kiến nhận xét chi tiết của Giảng viên <span class="text-danger">*</span>:</label>
                    <textarea name="NhanXetGV" rows="4" class="form-control" required
                        placeholder="Nhận xét cụ thể các điểm đã đạt hoặc các điểm nhóm cần tiếp tục chỉnh sửa, bổ sung...">{{ $latestBaoCao->NhanXetGV ?? 'Báo cáo toàn văn đã được chỉnh sửa đúng theo ý kiến góp ý của Hội đồng bảo vệ. Hình thức và nội dung đạt chuẩn quy định lưu trữ Khoa.' }}</textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <span class="small text-muted">
                        <i class="fa-solid fa-shield me-1"></i> Sau khi duyệt Đạt, trạng thái hồ sơ sẽ chuyển sang 'Đã hoàn thành' và phát thông báo đến Trưởng khoa cùng nhóm sinh viên.
                    </span>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" style="background: #0072ce; border-color: #0072ce;">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Lưu &amp; Xác Nhận Đánh Giá
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Khối 4: Toàn Bộ Lịch Sử Các Lần Nộp Lại Báo Cáo -->
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Lịch Sử Các Lần Nộp Lại Của Nhóm ({{ $lichSuNop->count() }} lần)
            </span>
        </div>
        <div class="card-body p-0">
            @if($lichSuNop->isEmpty())
                <div class="text-center py-4 text-muted small">Chưa có lịch sử nộp lại.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.84rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;" class="text-center">Lần nộp</th>
                                <th>Tệp Báo Cáo &amp; Đính Kèm</th>
                                <th style="min-width: 200px;">Nội dung tiếp thu</th>
                                <th style="width: 140px;">Thời gian nộp</th>
                                <th class="text-center" style="width: 130px;">Trạng thái</th>
                                <th style="min-width: 200px;">Nhận xét Giảng viên</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lichSuNop as $b)
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-primary text-white rounded-pill px-3 py-1">Lần {{ $b->LanChinhSua }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><i class="fa-solid fa-file-pdf text-danger me-1"></i>{{ $b->TenFile }}</div>
                                    <a href="{{ asset('storage/' . $b->DuongDanFile) }}" target="_blank" class="small text-primary text-decoration-none d-inline-block mt-1">
                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Xem báo cáo PDF
                                    </a>
                                    @if($b->FileGiaiTrinh)
                                    <div class="mt-1">
                                        <a href="{{ asset('storage/' . $b->FileGiaiTrinh) }}" target="_blank" class="small text-info text-decoration-none">
                                            <i class="fa-solid fa-file-lines me-1"></i> Xem bản giải trình
                                        </a>
                                    </div>
                                    @endif
                                    @if($b->FileMinhChung)
                                    <div class="mt-1">
                                        <a href="{{ asset('storage/' . $b->FileMinhChung) }}" target="_blank" class="small text-success text-decoration-none">
                                            <i class="fa-solid fa-paperclip me-1"></i> Tải minh chứng
                                        </a>
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="small text-secondary" style="white-space: pre-line; max-width: 300px;">{{ $b->TomTatChinhSua }}</div>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark">{{ \Carbon\Carbon::parse($b->NgayNop)->format('d/m/Y H:i') }}</div>
                                    <div class="small text-muted">{{ $b->sinhVienNop->HoTen ?? $b->NguoiNop }}</div>
                                </td>
                                <td class="text-center">
                                    @if($b->TrangThai === 'Đạt')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">Đạt Yêu Cầu</span>
                                    @elseif($b->TrangThai === 'Yêu cầu sửa tiếp')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">Sửa Lại</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1">Đã Nộp</span>
                                    @endif
                                </td>
                                <td>
                                    @if($b->NhanXetGV)
                                        <div class="small fw-semibold text-dark">{{ $b->NhanXetGV }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">{{ $b->NgayKiemTra ? \Carbon\Carbon::parse($b->NgayKiemTra)->format('d/m/Y H:i') : '' }} &bull; {{ $b->NguoiKiemTra }}</div>
                                    @else
                                        <span class="text-muted small fst-italic">--</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleHanNop(val) {
    const el = document.getElementById('groupHanNopMoi');
    if (el) {
        el.style.display = (val === 'yeu_cau_sua_tiep') ? 'block' : 'none';
    }
}
</script>
@endpush
@endsection
