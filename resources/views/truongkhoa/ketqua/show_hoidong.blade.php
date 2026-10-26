@extends('layouts.truongkhoa')

@section('page_title', 'Chi Tiết Hội Đồng Bảo Vệ — ' . $hoiDong->TenHoiDong)

@section('content')
<div class="container-fluid px-0">
    <!-- Header Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <div class="mb-1">
                <a href="{{ route('truongkhoa.ketqua.index', ['view' => 'hoidong']) }}" class="text-decoration-none small text-muted">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Danh Sách Hội Đồng
                </a>
            </div>
            <h4 class="fw-bold mb-0" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-users-rectangle me-2 text-primary"></i>
                Hội Đồng: {{ $hoiDong->TenHoiDong }}
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($tienDo['hoiDongDuPhieu'])
            <form action="{{ route('truongkhoa.ketqua.congbo') }}" method="POST" onsubmit="return confirm('Trưởng khoa có chắc chắn muốn CÔNG BỐ KẾT QUẢ CHÍNH THỨC cho Hội đồng này?\n\nĐiểm số sẽ được khóa bảo vệ toàn vẹn và gửi thông báo đến các sinh viên, giảng viên.');">
                @csrf
                <input type="hidden" name="MaHoiDong" value="{{ $hoiDong->MaHoiDong }}">
                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-xs">
                    <i class="fa-solid fa-bullhorn me-2"></i> Công Bố Kết Quả &amp; Chuyển Lưu Trữ
                </button>
            </form>
            @else
            <form action="{{ route('truongkhoa.ketqua.nhacnho') }}" method="POST">
                @csrf
                <input type="hidden" name="MaHoiDong" value="{{ $hoiDong->MaHoiDong }}">
                <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-xs">
                    <i class="fa-regular fa-bell me-2"></i> Nhắc Nhở Tất Cả Giám Khảo Chưa Nộp
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Thông Tin Tóm Tắt Hội Đồng -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <div class="p-2 bg-light rounded-2 border">
                        <div class="text-muted small">Phòng / Địa điểm:</div>
                        <div class="fw-bold text-dark fs-6">{{ $hoiDong->DiaDiem ?? 'Phòng B.304' }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 bg-light rounded-2 border">
                        <div class="text-muted small">Thời gian bảo vệ:</div>
                        <div class="fw-bold text-dark fs-6">{{ \Carbon\Carbon::parse($hoiDong->NgayBaoVe ?? now())->format('d/m/Y') }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 bg-light rounded-2 border">
                        <div class="text-muted small">Giám khảo / Đề tài:</div>
                        <div class="fw-bold text-dark fs-6">{{ $tienDo['totalMembers'] }} Giám khảo &bull; {{ $tienDo['tongSoDeTai'] }} Đề tài</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 rounded-2 border {{ $tienDo['hoiDongDuPhieu'] ? 'bg-success-subtle border-success' : 'bg-warning-subtle border-warning' }}">
                        <div class="small fw-semibold {{ $tienDo['hoiDongDuPhieu'] ? 'text-success-emphasis' : 'text-warning-emphasis' }}">Trạng thái chấm điểm:</div>
                        <div class="fw-bold {{ $tienDo['hoiDongDuPhieu'] ? 'text-success' : 'text-warning-emphasis' }}">
                            {{ $tienDo['hoiDongDuPhieu'] ? 'Đã đủ 100% phiếu chấm hợp lệ' : "Còn thiếu phiếu ({$tienDo['soDeTaiDuPhieu']}/{$tienDo['tongSoDeTai']} đề tài đủ)" }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách thành viên hội đồng -->
            <div class="mt-3 pt-3 border-top">
                <div class="small fw-bold text-secondary mb-2">Thành phần Giám khảo Hội đồng:</div>
                <div class="row g-2">
                    @foreach($hoiDong->thanhViens as $idx => $tv)
                    <div class="col-md-4 col-lg-3">
                        <div class="p-2 bg-white rounded-2 border d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark small">{{ $tv->giangVien->HoTen ?? $tv->MaGV }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $tv->VaiTro ?? 'Thành viên' }}</div>
                            </div>
                            <span class="badge bg-light text-secondary border font-monospace">{{ $tv->MaGV }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ DANH SÁCH TỪNG ĐỀ TÀI / NHÓM TRONG HỘI ĐỒNG ═══ -->
    <h5 class="fw-bold text-dark mb-3">
        <i class="fa-solid fa-list-check text-primary me-2"></i> Danh Sách Đề Tài &amp; Bảng Đối Chiếu Điểm Giám Khảo
    </h5>

    @forelse($tienDo['danhSachHoSo'] as $idx => $item)
    @php
        $hoSo = $item['hoSo'];
        $nhom = $item['nhom'];
        $deTai = $item['deTai'];
        $duPhieu = $item['duPhieu'];
        $loaiKL = $item['loaiKhoaLuan'] ?? 'KLCN';

        // Kiểm tra xem đề tài có độ lệch lớn không
        $coLechLon = collect($item['chiTietSinhVien'])->contains('canhBaoLech', true);
    @endphp
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white" id="cardHoSo_{{ $hoSo->MaHoSo }}">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary text-white">Đề tài {{ $idx + 1 }}</span>
                        <span class="badge bg-light text-dark border font-monospace">{{ $loaiKL === 'KLKS' ? 'Khóa Luận Kỹ Sư' : 'Khóa Luận Cử Nhân' }}</span>
                        @if($duPhieu)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2">Đã đủ phiếu hợp lệ</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2">Chưa đủ phiếu</span>
                        @endif

                        @if($coLechLon)
                            <span class="badge bg-danger text-white rounded-pill px-2 animate__animated animate__pulse animate__infinite">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Cảnh báo: Độ lệch điểm giám khảo &gt;= 1.5đ
                            </span>
                        @endif
                    </div>
                    <h6 class="fw-bold text-primary mb-1">{{ $deTai->TenDeTai ?? 'Chưa cập nhật tên đề tài' }}</h6>
                    <div class="small text-muted">
                        <span>Nhóm: <strong>{{ $nhom->TenNhom ?? $hoSo->MaNhom }}</strong></span> &bull; 
                        <span>GVHD: <strong>{{ $deTai->giangVien->HoTen ?? 'Chưa phân công' }}</strong></span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @if(!$duPhieu)
                    <form action="{{ route('truongkhoa.ketqua.nhacnho') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="MaHoiDong" value="{{ $hoiDong->MaHoiDong }}">
                        <input type="hidden" name="MaHoSo" value="{{ $hoSo->MaHoSo }}">
                        <button type="submit" class="btn btn-xs btn-outline-warning rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem;">
                            <i class="fa-regular fa-bell me-1"></i> Nhắc Nhở
                        </button>
                    </form>
                    @endif

                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalXacNhan_{{ $hoSo->MaHoSo }}">
                        <i class="fa-solid fa-signature me-1"></i> Xác Nhận Kết Quả
                    </button>

                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalYeuCauSua_{{ $hoSo->MaHoSo }}">
                        <i class="fa-solid fa-file-pen me-1"></i> Yêu Cầu Chỉnh Sửa
                    </button>
                </div>
            </div>

            <!-- Tình trạng nộp phiếu của các giám khảo cho đề tài này -->
            <div class="mt-2 pt-2 border-top d-flex flex-wrap align-items-center gap-2" style="font-size: 0.78rem;">
                <span class="text-muted fw-bold">Giám khảo:</span>
                @foreach($item['gvHoanThanh'] as $gv)
                    <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2">
                        <i class="fa-solid fa-check me-1"></i> {{ $gv['HoTen'] }} (Đã ký)
                    </span>
                @endforeach
                @foreach($item['gvLuuNhap'] as $gv)
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle py-1 px-2">
                        <i class="fa-solid fa-pen me-1"></i> {{ $gv['HoTen'] }} (Lưu nháp)
                    </span>
                @endforeach
                @foreach($item['gvChuaCham'] as $gv)
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1 px-2">
                        <i class="fa-solid fa-xmark me-1"></i> {{ $gv['HoTen'] }} (Chưa chấm)
                    </span>
                @endforeach
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.84rem;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">STT</th>
                            <th style="min-width: 180px;">Sinh viên thực hiện</th>
                            @foreach($hoiDong->thanhViens as $sIdx => $tv)
                            <th class="text-center" style="min-width: 130px;">
                                <div class="text-truncate" style="max-width: 120px;" title="{{ $tv->giangVien->HoTen ?? $tv->MaGV }}">
                                    {{ $tv->giangVien->HoTen ?? $tv->MaGV }}
                                </div>
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $tv->VaiTro ?? 'Giám khảo' }}</div>
                            </th>
                            @endforeach
                            <th class="text-center" style="width: 110px;">Độ lệch điểm</th>
                            <th class="text-center" style="width: 110px;">Điểm TB Sơ Bộ</th>
                            <th class="text-center text-primary" style="width: 120px;">Điểm Chính Thức</th>
                            <th class="text-center" style="width: 110px;">Trạng Thái</th>
                            <th class="text-center" style="width: 100px;">Xếp Loại</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item['chiTietSinhVien'] as $sIdx => $svDetail)
                        @php
                            $sv = $svDetail['sinhVien'];
                        @endphp
                        <tr>
                            <td class="text-center text-muted">{{ $sIdx + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $sv->HoTen }}</div>
                                <div class="small text-muted font-monospace">{{ $sv->MaSoSinhVien ?? $sv->MaSV }} &bull; {{ $sv->lop->TenLop ?? 'Lớp' }}</div>
                            </td>

                            {{-- Điểm từng Giám khảo --}}
                            @foreach($hoiDong->thanhViens as $tv)
                            @php
                                $judgeInfo = $svDetail['diemCacGiamKhao'][$tv->MaGV] ?? null;
                                $scoreVal = $judgeInfo['Diem'] ?? null;
                                $trangThaiPhieu = $judgeInfo['TrangThai'] ?? 'Chưa chấm';
                            @endphp
                            <td class="text-center">
                                @if($scoreVal !== null)
                                    <div class="fw-bold {{ $trangThaiPhieu === 'Đã hoàn thành' ? 'text-dark' : 'text-warning' }}">
                                        {{ number_format($scoreVal, 2) }}
                                    </div>
                                    <div class="mt-1">
                                        <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-2 py-0" style="font-size: 0.68rem;"
                                            data-bs-toggle="modal" data-bs-target="#modalXemPhieu_{{ $tv->MaGV }}_{{ $sv->MaSV }}">
                                            <i class="fa-solid fa-eye me-1"></i> Xem phiếu
                                        </button>
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border">Chưa nộp</span>
                                @endif
                            </td>
                            @endforeach

                            {{-- Độ lệch điểm --}}
                            <td class="text-center">
                                @if($svDetail['validScoresCount'] >= 2)
                                    @if($svDetail['canhBaoLech'])
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-bold">
                                            &Delta; {{ number_format($svDetail['diff'], 2) }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-secondary border px-2 py-1">
                                            &Delta; {{ number_format($svDetail['diff'], 2) }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted small">--</span>
                                @endif
                            </td>

                            {{-- Điểm TB sơ bộ --}}
                            <td class="text-center fw-semibold text-dark fs-6">
                                {{ $svDetail['validScoresCount'] > 0 ? number_format($svDetail['diemTB'], 2) : '--' }}
                            </td>

                            {{-- Điểm chính thức --}}
                            <td class="text-center fw-bold fs-6 text-primary">
                                {{ $svDetail['diemChinhThuc'] !== null ? number_format($svDetail['diemChinhThuc'], 2) : ($svDetail['validScoresCount'] > 0 ? number_format($svDetail['diemTB'], 2) : '--') }}
                            </td>

                            {{-- Trạng thái --}}
                            <td class="text-center">
                                @if($svDetail['trangThaiKetQua'] === 'Đã công bố')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">Đã công bố</span>
                                @elseif($svDetail['trangThaiKetQua'] === 'Đã xác nhận')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1">Đã xác nhận</span>
                                @elseif($svDetail['trangThaiKetQua'] === 'Đã đủ phiếu hợp lệ')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1">Đã đủ phiếu</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1">Chưa đủ phiếu</span>
                                @endif
                            </td>

                            {{-- Xếp loại --}}
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $svDetail['xepLoai'] }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Ghi chú kết luận hội đồng & Yêu cầu chỉnh sửa nếu có -->
            @if(!empty($hoSo->YeuCauChinhSuaSauBaoVe))
            <div class="p-3 bg-light-subtle border-top">
                <div class="alert alert-info py-2 px-3 small rounded-3 mb-0 border-0 bg-info-subtle text-info-emphasis">
                    <div class="fw-bold mb-1">
                        <i class="fa-solid fa-pen-ruler me-1"></i> Yêu Cầu Chỉnh Sửa Báo Cáo Sau Bảo Vệ:
                    </div>
                    <div>{{ $hoSo->YeuCauChinhSuaSauBaoVe }}</div>
                    <div class="mt-1 text-muted" style="font-size: 0.75rem;">
                        <span>Hạn nộp: <strong>{{ \Carbon\Carbon::parse($hoSo->HanNopBaoCaoSauBaoVe)->format('d/m/Y H:i') }}</strong></span> &bull; 
                        <span>Người kiểm tra: <strong>{{ $hoSo->NguoiKiemTraChinhSua }}</strong></span> &bull; 
                        <span>Trạng thái: <strong>{{ $hoSo->TrangThaiChinhSuaSauBaoVe ?? 'Chờ sinh viên nộp' }}</strong></span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- MODAL XÁC NHẬN KẾT QUẢ CHO ĐỀ TÀI NÀY --}}
    <div class="modal fade" id="modalXacNhan_{{ $hoSo->MaHoSo }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('truongkhoa.ketqua.xacnhan') }}" method="POST">
                @csrf
                <input type="hidden" name="MaHoSo" value="{{ $hoSo->MaHoSo }}">

                <div class="modal-content border-0 shadow rounded-3">
                    <div class="modal-header bg-primary text-white py-3">
                        <h6 class="modal-title fw-bold">
                            <i class="fa-solid fa-signature me-2"></i> Xác Nhận Điểm Chính Thức & Chốt Kết Quả Bảo Vệ
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <h6 class="fw-bold text-dark mb-2">{{ $deTai->TenDeTai ?? '' }}</h6>
                        <p class="small text-muted mb-3">
                            Trưởng khoa có thể xác nhận giữ nguyên Điểm trung bình sơ bộ hoặc điều chỉnh điểm chính thức cho từng sinh viên (kèm lý do ghi nhận vết kiểm toán).
                        </p>

                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-sm align-middle text-center mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sinh viên</th>
                                        <th style="width: 120px;">Điểm TB Sơ Bộ</th>
                                        <th style="width: 140px;">Điểm Chính Thức</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item['chiTietSinhVien'] as $svDetail)
                                    @php
                                        $sv = $svDetail['sinhVien'];
                                        $curCT = $svDetail['diemChinhThuc'] ?? $svDetail['diemTB'];
                                    @endphp
                                    <tr>
                                        <td class="text-start">
                                            <strong>{{ $sv->HoTen }}</strong> ({{ $sv->MaSoSinhVien ?? $sv->MaSV }})
                                        </td>
                                        <td class="fw-semibold text-secondary">
                                            {{ number_format($svDetail['diemTB'], 2) }}
                                        </td>
                                        <td>
                                            <input type="number" step="0.05" min="0" max="10.0" 
                                                   name="dieu_chinh_diem[{{ $sv->MaSV }}]" 
                                                   class="form-control form-control-sm text-center fw-bold text-primary" 
                                                   value="{{ number_format((float)$curCT, 2) }}" required>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Ghi chú xác nhận / Lý do điều chỉnh (nếu có):</label>
                            <textarea name="ghi_chu" rows="3" class="form-control" placeholder="Ghi nhận căn cứ xác nhận kết luận của Hội đồng..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">
                            <i class="fa-solid fa-lock me-1"></i> Lưu &amp; Xác Nhận Kết Quả
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL GHI NHẬN YÊU CẦU CHỈNH SỬA BÁO CÁO SAU BẢO VỆ --}}
    <div class="modal fade" id="modalYeuCauSua_{{ $hoSo->MaHoSo }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('truongkhoa.ketqua.yeucauchinhsua') }}" method="POST">
                @csrf
                <input type="hidden" name="MaHoSo" value="{{ $hoSo->MaHoSo }}">

                <div class="modal-content border-0 shadow rounded-3">
                    <div class="modal-header bg-secondary text-white py-3">
                        <h6 class="modal-title fw-bold">
                            <i class="fa-solid fa-file-pen me-2"></i> Yêu Cầu Chỉnh Sửa Báo Cáo Sau Bảo Vệ (Hội Đồng)
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="small text-secondary mb-3">
                            Sau phiên bảo vệ, nếu Hội đồng yêu cầu nhóm chỉnh sửa báo cáo toàn văn trước khi lưu trữ chính thức tại Khoa, Trưởng khoa nhập yêu cầu và thời hạn tại đây.
                        </p>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Nội dung yêu cầu chỉnh sửa theo kết luận của Hội đồng <span class="text-danger">*</span>:</label>
                            <textarea name="YeuCauChinhSuaSauBaoVe" rows="4" class="form-control" required
                                placeholder="Ví dụ: Bổ sung sơ đồ thiết kế kiến trúc phần 3.2; Sửa định dạng danh mục tài liệu tham khảo theo chuẩn IEEE; Cập nhật lại kết quả thực nghiệm...">{{ $hoSo->YeuCauChinhSuaSauBaoVe }}</textarea>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Thời hạn nộp lại báo cáo hoàn thiện <span class="text-danger">*</span>:</label>
                                <input type="datetime-local" name="HanNopBaoCaoSauBaoVe" class="form-control" 
                                       value="{{ $hoSo->HanNopBaoCaoSauBaoVe ? \Carbon\Carbon::parse($hoSo->HanNopBaoCaoSauBaoVe)->format('Y-m-d\TH:i') : now()->addDays(7)->format('Y-m-d\T17:00') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Người phụ trách kiểm tra, xác nhận <span class="text-danger">*</span>:</label>
                                <select name="NguoiKiemTraChinhSua" class="form-select" required>
                                    <option value="{{ $deTai->giangVien->MaGV ?? '' }}">GVHD: {{ $deTai->giangVien->HoTen ?? 'Chưa rõ' }}</option>
                                    @foreach($hoiDong->thanhViens as $tv)
                                        @if($tv->MaGV !== ($deTai->giangVien->MaGV ?? ''))
                                            <option value="{{ $tv->MaGV }}">{{ $tv->VaiTro ?? 'Thành viên HĐ' }}: {{ $tv->giangVien->HoTen ?? $tv->MaGV }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">
                            <i class="fa-solid fa-paper-plane me-1"></i> Lưu &amp; Phát Thông Báo Đến Nhóm
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @empty
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center text-muted">
        <h6>Hội đồng này chưa có đề tài bảo vệ nào được phân công.</h6>
    </div>
    @endforelse

    {{-- MODAL XEM CHI TIẾT TỪNG PHIẾU CHẤM GỐC --}}
    @foreach($allPhieuChams as $phieu)
    @php
        $rubricP = ($phieu->LoaiKhoaLuan === 'KLKS') ? $rubricKLKS : $rubricKLCN;
        $chiTietP = $phieu->ChiTietDiem ?? [];
    @endphp
    <div class="modal fade" id="modalXemPhieu_{{ $phieu->MaGV }}_{{ $phieu->MaSV }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow rounded-3">
                <div class="modal-header bg-light border-bottom py-3">
                    <h6 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-file-invoice me-2 text-primary"></i> Phiếu Chấm Gốc — {{ $phieu->giangVien->HoTen ?? $phieu->MaGV }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="p-2 bg-light rounded-2 border mb-3 small">
                        <div><strong>Sinh viên:</strong> {{ $phieu->sinhVien->HoTen ?? $phieu->MaSV }} ({{ $phieu->MaSV }})</div>
                        <div><strong>Giám khảo:</strong> {{ $phieu->giangVien->HoTen ?? $phieu->MaGV }}</div>
                        <div><strong>Thời điểm ký:</strong> {{ $phieu->NgayXacNhan ? \Carbon\Carbon::parse($phieu->NgayXacNhan)->format('d/m/Y H:i') : 'Chưa ký xác nhận' }}</div>
                        <div><strong>Trạng thái:</strong> <span class="badge {{ $phieu->TrangThai === 'Đã hoàn thành' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $phieu->TrangThai }}</span></div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle text-center mb-0" style="font-size: 0.8rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;">STT</th>
                                    <th class="text-start">Tiêu chí đánh giá</th>
                                    <th style="width: 70px;">CLO</th>
                                    <th style="width: 80px;">Tối đa</th>
                                    <th style="width: 90px;">Điểm chấm</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rubricP as $cr)
                                <tr>
                                    <td>{{ $cr['stt'] }}</td>
                                    <td class="text-start">{{ $cr['noi_dung'] }}</td>
                                    <td><span class="badge bg-light text-secondary border">{{ $cr['clo'] }}</span></td>
                                    <td class="fw-bold">{{ number_format($cr['max'], 2) }}</td>
                                    <td class="fw-bold text-primary">
                                        {{ isset($chiTietP[$cr['id']]) ? number_format((float)$chiTietP[$cr['id']], 2) : '0.00' }}
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="table-primary fw-bold">
                                    <td colspan="4" class="text-end">TỔNG ĐIỂM:</td>
                                    <td class="fs-6 text-primary">{{ number_format((float)$phieu->Diem, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if(!empty($phieu->NhanXet))
                    <div class="mt-3 p-3 bg-light rounded-2 border small">
                        <strong>Nhận xét của Giám khảo:</strong>
                        <div class="mt-1 text-secondary fst-italic">{{ $phieu->NhanXet }}</div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer bg-light py-2 d-flex justify-content-between">
                    <form action="{{ route('truongkhoa.ketqua.mokhoaphieu') }}" method="POST" class="d-inline" onsubmit="return confirm('Trưởng khoa có chắc muốn MỞ KHÓA phiếu chấm này để Giám khảo chấm lại?');">
                        @csrf
                        <input type="hidden" name="MaPhieuChamDiem" value="{{ $phieu->MaPhieuChamDiem }}">
                        <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                            <i class="fa-solid fa-unlock me-1"></i> Mở Khóa Phiếu
                        </button>
                    </form>
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
