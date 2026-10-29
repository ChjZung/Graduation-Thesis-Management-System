@extends('layouts.sinhvien')

@section('page_title', 'Hội Đồng Bảo Vệ Của Tôi')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark, #003366);">
                <i class="fa-solid fa-graduation-cap me-2 text-primary"></i>Hội Đồng Bảo Vệ Khóa Luận Của Tôi
            </h4>
            <div class="text-muted small">
                Tra cứu thông tin phiên bảo vệ khóa luận tốt nghiệp, lịch trình, phòng bảo vệ, danh sách thành viên Hội đồng đánh giá và các lưu ý bắt buộc.
            </div>
        </div>
        <div class="d-flex gap-2">
            @if($hoSo && (!empty($hoSo->YeuCauChinhSuaSauBaoVe) || in_array($hoSo->TrangThaiKetQua, ['CoYeuCauChinhSua', 'DaNopChinhSua', 'DaHoanThanh'])))
                <a href="{{ route('sinhvien.ketqua.hoanthien') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-file-circle-check me-1"></i> Báo Cáo Chỉnh Sửa Sau Bảo Vệ
                </a>
            @endif
            <a href="{{ route('sinhvien.ketqua.index') }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-xs" style="background: #0072ce; border-color: #0072ce;">
                <i class="fa-solid fa-award me-1"></i> Bảng Điểm Khóa Luận
            </a>
        </div>
    </div>

    @if(!$nhom)
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white mb-4">
            <i class="fa-solid fa-users-slash text-secondary fa-3x mb-3 opacity-50"></i>
            <h5 class="fw-bold text-dark">Bạn chưa tham gia nhóm khóa luận nào</h5>
            <p class="text-muted small mb-0">Vui lòng tạo hoặc tham gia nhóm tại mục Quản lý nhóm để tiếp tục quy trình.</p>
        </div>
    @elseif(!$hoiDong)
        <!-- Chưa được phân công Hội đồng -->
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white mb-4">
            <div class="p-4 bg-primary-subtle text-primary rounded-circle d-inline-flex mx-auto mb-3" style="width: 80px; height: 80px; align-items: center; justify-content: center;">
                <i class="fa-solid fa-hourglass-half fa-2x"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">Nhóm của bạn hiện đang chờ phân công Hội đồng bảo vệ</h5>
            <p class="text-muted small max-w-600 mx-auto mb-4" style="line-height: 1.6;">
                Hồ sơ bảo vệ của nhóm (Đề tài: <strong>{{ $nhom->deTai->TenDeTai ?? 'Chưa cập nhật' }}</strong>) đang được Giáo vụ và Trưởng khoa rà soát, sắp xếp lịch bảo vệ phù hợp với chuyên ngành đào tạo. Thông tin chi tiết về Hội đồng sẽ hiển thị tại trang này ngay khi có Quyết định chính thức.
            </p>

            <!-- Hướng dẫn chuẩn bị trước bảo vệ -->
            <div class="text-start p-4 bg-light rounded-4 border max-w-700 mx-auto">
                <div class="fw-bold text-primary mb-2">
                    <i class="fa-solid fa-list-check me-1"></i> Các việc nhóm cần chuẩn bị trước khi vào Hội đồng:
                </div>
                <ul class="text-secondary small mb-0 ps-3" style="line-height: 1.8;">
                    <li>Kiểm tra và hoàn thiện toàn bộ báo cáo toàn văn theo đúng biểu mẫu quy định của Khoa CNTT.</li>
                    <li>Chuẩn bị bài thuyết trình Slide (thời lượng từ 10 - 15 phút, nhấn mạnh vào phương pháp, giải pháp và kết quả đạt được).</li>
                    <li>Triển khai và chạy thử ứng dụng/sản phẩm phần mềm thực tế, sẵn sàng demo trực tiếp trước Hội đồng.</li>
                    <li>In 03 - 05 quyển báo cáo khóa luận (đóng bìa mềm hoặc gáy xoắn) để chuyển cho các Thầy/Cô thành viên Hội đồng vào ngày bảo vệ.</li>
                </ul>
            </div>
        </div>
    @else
        <!-- Đã có Hội đồng bảo vệ -->
        <!-- Khối 1: Banner Thông Tin Phiên Bảo Vệ -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden" style="border-left: 6px solid #0072ce !important;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <span class="badge bg-primary text-white rounded-pill px-3 py-1 font-monospace">Mã HĐ: {{ $hoiDong->MaHoiDong }}</span>
                    <div>
                        @if($hoiDong->trang_thai_thoi_gian === 'DaKetThuc')
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-bold">
                                <i class="fa-solid fa-check me-1"></i> Đã Kết Thúc
                            </span>
                        @elseif($hoiDong->trang_thai_thoi_gian === 'DangDienRa')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 fw-bold">
                                <i class="fa-solid fa-spinner fa-spin me-1"></i> Đang Diễn Ra
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2 fw-bold">
                                <i class="fa-regular fa-clock me-1"></i> Sắp Diễn Ra
                            </span>
                        @endif
                    </div>
                </div>

                <h4 class="fw-bold text-primary mb-2">{{ $hoiDong->TenHoiDong }}</h4>
                <div class="text-secondary small mb-3">
                    <span>Khoa: <strong>{{ $hoiDong->khoa->TenKhoa ?? 'Công nghệ Thông tin' }}</strong></span> &bull; 
                    <span>Học kỳ: <strong>{{ $hoiDong->hocKy->TenHocKy ?? 'Hiện tại' }}</strong></span> &bull; 
                    <span>Chuyên ngành: <strong>{{ $hoiDong->nganh->TenNganh ?? ($hoSo->ten_nganh ?? 'CNTT') }}</strong></span>
                </div>

                <div class="row g-3 pt-3 border-top">
                    <div class="col-md-4">
                        <div class="p-3 rounded-3 bg-light border border-slate-200">
                            <span class="text-muted small d-block mb-1"><i class="fa-solid fa-location-dot text-danger me-1"></i>Phòng bảo vệ cụ thể:</span>
                            <div class="fw-bold text-dark fs-5">{{ $hoSo->PhongBaoVe ?? ($hoiDong->DiaDiem ?? 'Phòng B.304 - Nhà B') }}</div>
                            <div class="small text-muted mt-1">Trường ĐH Công Thương TP.HCM (HUIT)</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 rounded-3 bg-light border border-slate-200">
                            <span class="text-muted small d-block mb-1"><i class="fa-regular fa-calendar me-1 text-primary"></i>Ngày bảo vệ chính thức:</span>
                            <div class="fw-bold text-dark fs-5">
                                {{ $hoiDong->NgayBaoVe ? $hoiDong->NgayBaoVe->format('d/m/Y') : 'Chưa cập nhật' }}
                            </div>
                            <div class="small text-muted mt-1">
                                Khung giờ: <strong>{{ $hoSo->ThoiGianBaoVe ? \Carbon\Carbon::parse($hoSo->ThoiGianBaoVe)->format('H:i') : ($hoiDong->ThoiGianBatDau ? \Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('H:i') : '07:30') }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 rounded-3 bg-light border border-slate-200">
                            <span class="text-muted small d-block mb-1"><i class="fa-solid fa-file-signature text-info me-1"></i>Văn bản quyết định thành lập:</span>
                            <div class="fw-bold text-dark fs-6">{{ $hoiDong->SoQuyetDinh ?? 'Số: .../QĐ-ĐHTP' }}</div>
                            <div class="small text-muted mt-1">
                                Ký ngày: {{ $hoiDong->NgayBanHanh ? $hoiDong->NgayBanHanh->format('d/m/Y') : '...' }} &bull; Đã công bố
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Khối 2: Đề Tài & Thành Viên Nhóm -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 p-4">
            <h6 class="fw-bold text-dark mb-3">
                <i class="fa-solid fa-book-bookmark text-primary me-2"></i>Thông Tin Đề Tài Báo Cáo Của Nhóm
            </h6>
            <div class="row g-3">
                <div class="col-md-8">
                    <span class="badge bg-light text-secondary border font-monospace mb-1">Mã đề tài: {{ $nhom->deTai->MaDeTai ?? '...' }}</span>
                    <h5 class="fw-bold text-primary mb-1">{{ $nhom->deTai->TenDeTai ?? 'Chưa cập nhật tên đề tài' }}</h5>
                    @if($nhom->deTai && $nhom->deTai->TenDeTaiTiengAnh)
                        <div class="text-muted fst-italic small mb-2">{{ $nhom->deTai->TenDeTaiTiengAnh }}</div>
                    @endif
                    <div class="small text-muted">
                        <span>Giảng viên hướng dẫn: <strong>{{ $nhom->deTai->giangVien->HoTen ?? 'Chưa phân công' }}</strong> ({{ $nhom->deTai->giangVien->boMon->TenBoMon ?? '' }})</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3 bg-light border">
                        <span class="text-muted small d-block mb-1 fw-bold text-uppercase">Các thành viên trong nhóm:</span>
                        @foreach($nhom->thanhViens ?? [] as $tvn)
                            <div class="small text-dark py-1 border-bottom border-light">
                                <i class="fa-solid fa-user me-1 text-primary"></i> 
                                <strong>{{ $tvn->sinhVien->HoTen ?? $tvn->MaSV }}</strong> 
                                <span class="text-muted">({{ $tvn->MaSV }} - {{ $tvn->sinhVien->lop->TenLop ?? '' }})</span>
                                @if($tvn->MaSV === $nhom->MaTruongNhom || $tvn->MaSV === $nhom->truongNhom?->MaSV)
                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">Trưởng nhóm</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Khối 3: Danh Sách Thành Viên Hội Đồng Được Công Khai Cho Sinh Viên -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark">
                    <i class="fa-solid fa-users text-primary me-2"></i>Danh Sách Thành Viên Hội Đồng Đánh Giá (Công Khai)
                </span>
                <span class="badge bg-light text-secondary border font-monospace">{{ $publicThanhViens->count() }} thành viên</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;" class="text-center">STT</th>
                                <th style="width: 150px;">Vai Trò Hội Đồng</th>
                                <th>Họ Và Tên Thầy/Cô</th>
                                <th>Học Vị / Chức Vụ</th>
                                <th>Bộ Môn / Đơn Vị</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($publicThanhViens as $pIdx => $ptv)
                                <tr>
                                    <td class="text-center font-monospace">{{ $pIdx + 1 }}</td>
                                    <td>
                                        @if($ptv->VaiTro === 'Chủ tịch')
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold">
                                                <i class="fa-solid fa-crown me-1"></i> Chủ Tịch
                                            </span>
                                        @elseif($ptv->VaiTro === 'Thư ký')
                                            <span class="badge bg-info text-white rounded-pill px-3 py-1 fw-bold">
                                                <i class="fa-solid fa-pen-nib me-1"></i> Thư Ký
                                            </span>
                                        @elseif($ptv->VaiTro === 'Phản biện')
                                            <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-bold">
                                                <i class="fa-solid fa-user-check me-1"></i> Phản Biện
                                            </span>
                                        @else
                                            <span class="badge bg-secondary text-white rounded-pill px-3 py-1 fw-bold">
                                                Ủy Viên
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $ptv->giangVien->HoTen ?? $ptv->MaGV }}</div>
                                    </td>
                                    <td>{{ $ptv->giangVien->HocVi ?? 'Thạc sĩ' }}</td>
                                    <td>{{ $ptv->giangVien->boMon->TenBoMon ?? 'Khoa Công nghệ Thông tin' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Khối 4: Kết Quả & Yêu Cầu Chỉnh Sửa Sau Bảo Vệ (Nếu có) -->
        @if(!empty($hoSo->YeuCauChinhSuaSauBaoVe) || in_array($hoSo->TrangThaiKetQua, ['DaCongBo', 'CoYeuCauChinhSua', 'DaNopChinhSua', 'DaHoanThanh']))
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 p-4" style="border-left: 5px solid {{ $hoSo->TrangThaiChinhSuaSauBaoVe === 'DaHoanThanh' ? '#10B981' : '#0072ce' }} !important;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-clipboard-check text-success me-2"></i>Kết Quả Đánh Giá &amp; Kết Luận Của Hội Đồng
                    </h6>
                    <div>
                        @if($hoSo->TrangThaiChinhSuaSauBaoVe === 'DaHoanThanh')
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold">
                                <i class="fa-solid fa-circle-check me-1"></i> Đã Hoàn Thành Toàn Bộ Quy Trình
                            </span>
                        @elseif(!empty($hoSo->YeuCauChinhSuaSauBaoVe))
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1 fw-bold">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Có Yêu Cầu Chỉnh Sửa Hoàn Thiện
                            </span>
                        @endif
                    </div>
                </div>

                @if(!empty($hoSo->YeuCauChinhSuaSauBaoVe))
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="small fw-bold text-uppercase text-secondary mb-1">Nội dung Hội đồng yêu cầu nhóm chỉnh sửa:</div>
                        <div class="text-dark small" style="line-height: 1.6;">{!! \App\Helpers\HtmlHelper::renderRichText($hoSo->YeuCauChinhSuaSauBaoVe) !!}</div>
                        @if($hoSo->HanNopBaoCaoSauBaoVe)
                            <div class="mt-2 pt-2 border-top small text-muted">
                                Thời hạn hoàn thành: <strong class="text-primary">{{ \Carbon\Carbon::parse($hoSo->HanNopBaoCaoSauBaoVe)->format('d/m/Y H:i') }}</strong>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted small">
                        Người phụ trách kiểm tra: <strong>{{ $hoSo->NguoiKiemTraChinhSua ?? ($nhom->deTai->giangVien->HoTen ?? 'Giảng viên hướng dẫn') }}</strong>
                    </div>
                    <div>
                        <a href="{{ route('sinhvien.ketqua.hoanthien') }}" class="btn btn-sm btn-outline-primary rounded-pill px-4 fw-semibold">
                            <i class="fa-solid fa-cloud-arrow-up me-1"></i> Nộp Lại Báo Cáo Chỉnh Sửa Sau Bảo Vệ
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Khối 5: Các Lưu Ý & Hướng Dẫn Sinh Viên Khi Tham Gia Bảo Vệ -->
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
            <h6 class="fw-bold text-dark mb-3">
                <i class="fa-solid fa-circle-exclamation text-warning me-2"></i>Lưu Ý &amp; Quy Định Khi Tham Gia Buổi Bảo Vệ Khóa Luận
            </h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <div class="fw-bold text-primary small mb-2"><i class="fa-solid fa-shirt me-1"></i> 1. Trang Phục &amp; Tác Phong:</div>
                        <p class="text-secondary small mb-0" style="line-height: 1.6;">
                            Sinh viên bắt buộc mặc trang phục lịch sự, trang trọng (Áo sơ mi trắng, quần tây/chân váy tối màu hoặc đồng phục Nhà trường HUIT, đeo thẻ sinh viên đầy đủ). Giữ thái độ nghiêm túc, cầu thị trong suốt phiên báo cáo và trả lời chất vấn của Hội đồng.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <div class="fw-bold text-primary small mb-2"><i class="fa-solid fa-stopwatch me-1"></i> 2. Thời Gian Có Mặt:</div>
                        <p class="text-secondary small mb-0" style="line-height: 1.6;">
                            Toàn bộ các thành viên trong nhóm phải có mặt tại phòng bảo vệ trước giờ diễn ra ít nhất <strong>20 - 30 phút</strong> để ổn định vị trí, chuẩn bị thiết bị kết nối máy chiếu, test microphone và kiểm tra đường truyền sản phẩm demo.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <div class="fw-bold text-primary small mb-2"><i class="fa-solid fa-person-chalkboard me-1"></i> 3. Trình Bày Báo Cáo (Slide &amp; Demo):</div>
                        <p class="text-secondary small mb-0" style="line-height: 1.6;">
                            Thời lượng thuyết trình tối đa <strong>15 phút</strong>. Trình bày súc tích mục tiêu, phương pháp, kết quả đạt được và demo trực tiếp các chức năng tiêu biểu nhất. Sau đó Hội đồng sẽ dành 15 - 20 phút đặt câu hỏi chất vấn từng thành viên.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <div class="fw-bold text-primary small mb-2"><i class="fa-solid fa-book me-1"></i> 4. Hồ Sơ Báo Cáo Cần Mang Theo:</div>
                        <p class="text-secondary small mb-0" style="line-height: 1.6;">
                            Chuẩn bị sẵn <strong>03 - 05 quyển báo cáo khóa luận</strong> in sạch đẹp để kính chuyển các Thầy/Cô Hội đồng khi bắt đầu phiên báo cáo. Sau buổi bảo vệ, nhóm ghi nhận đầy đủ góp ý của Thư ký và Chủ tịch để chỉnh sửa nộp lại.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
