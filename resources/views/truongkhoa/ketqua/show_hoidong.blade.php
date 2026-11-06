@extends('layouts.truongkhoa')

@section('page_title', 'Chi Tiết Hội Đồng Bảo Vệ — ' . $hoiDong->TenHoiDong)

@push('styles')
<style>
/* ════ BẢNG ĐỐI CHIẾU ĐIỂM GIÁM KHẢO (TRƯỞNG KHOA) ════ */
.table-doichieu-diem {
    width: 100%;
    margin-bottom: 0;
}

.table-doichieu-diem thead th {
    background-color: #003366 !important;
    color: #ffffff !important;
    vertical-align: middle !important;
    border-bottom: 2px solid #002244 !important;
    font-weight: 600 !important;
    letter-spacing: 0.2px;
}

/* Tên giám khảo: Không cắt chữ, cho phép xuống tối đa 2 dòng */
.table-doichieu-diem thead th .gk-head-name {
    color: #ffffff !important;
    font-size: 0.82rem;
    font-weight: 600;
    white-space: normal !important;
    line-height: 1.25;
    overflow-wrap: anywhere !important;
    word-break: normal;
}

/* Vai trò giám khảo: Màu sáng tương phản cao trên nền navy (>10:1 WCAG AA), không gạch chân */
.table-doichieu-diem thead th .gk-head-role {
    color: rgba(255, 255, 255, 0.85) !important;
    font-size: 0.72rem !important;
    font-weight: 500;
    text-decoration: none !important;
    display: block;
    margin-top: 2px;
    letter-spacing: 0.2px;
}

.table-doichieu-diem thead th .gk-head-role * {
    color: rgba(255, 255, 255, 0.85) !important;
    text-decoration: none !important;
}
</style>
@endpush

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
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalLichSuSuaDiem">
                <i class="fa-solid fa-receipt me-1 text-primary"></i> Lịch Sử Sửa Điểm ({{ $lichSuSuaDiems->count() }})
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalCauHinhSuaDiem">
                <i class="fa-regular fa-clock me-1"></i> Hạn Sửa Điểm
            </button>
            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-semibold shadow-xs text-dark" data-bs-toggle="modal" data-bs-target="#modalMoKhoaSuaDiem">
                <i class="fa-solid fa-lock-open me-1 text-danger"></i> Mở Khóa Sai Sót
            </button>

            @php
                $allPublished = $hoiDong->hoSoBaoVes->isNotEmpty() && $hoiDong->hoSoBaoVes->every(fn($h) => in_array($h->TrangThaiKetQua, ['DaCongBo', 'DaHoanThanh', 'CoYeuCauChinhSua']));
            @endphp

            @if($allPublished)
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-bold">
                    <i class="fa-solid fa-circle-check me-1"></i> Đã Công Bố Kết Quả
                </span>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalThuHoiHd">
                    <i class="fa-solid fa-undo me-1"></i> Thu Hồi Công Bố
                </button>
            @elseif($tienDo['hoiDongDuPhieu'])
            <form action="{{ route('truongkhoa.ketqua.congbo_hoidong') }}" method="POST" onsubmit="return confirm('Trưởng khoa có chắc chắn muốn CÔNG BỐ KẾT QUẢ CHÍNH THỨC cho Hội đồng này?\n\nĐiểm số sẽ được khóa bảo vệ toàn vẹn và gửi thông báo đến các sinh viên, giảng viên.');">
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
                    @foreach($hoiDong->sorted_thanh_viens as $idx => $tv)
                    <div class="col-md-4 col-lg-3">
                        <div class="p-2 bg-white rounded-2 border d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark small">{{ $tv->giangVien->HoTen ?? $tv->MaGV }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $tv->short_vai_tro }}</div>
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
        $ketQuas = $hoSo->ketQuaSinhViens->keyBy('MaSV');
        $daXacNhan = $nhom && $nhom->thanhViens->isNotEmpty()
            && $nhom->thanhViens->every(fn($tv) => $ketQuas->get($tv->MaSV)?->daXacNhanKetQua() === true);

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

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @php
                        $dtDaCongBo = in_array($hoSo->TrangThaiKetQua, ['DaCongBo', 'CoYeuCauChinhSua', 'DaHoanThanh']);
                    @endphp

                    @if(!$duPhieu)
                    <form action="{{ route('truongkhoa.ketqua.nhacnho') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="MaHoiDong" value="{{ $hoiDong->MaHoiDong }}">
                        <input type="hidden" name="MaHoSo" value="{{ $hoSo->MaHoSo }}">
                        <button type="submit" class="btn btn-xs btn-outline-warning rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem;">
                            <i class="fa-regular fa-bell me-1"></i> Nhắc Nhở
                        </button>
                    </form>
                    @elseif($dtDaCongBo)
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalThuHoiDt_{{ $hoSo->MaHoSo }}">
                        <i class="fa-solid fa-undo me-1"></i> Thu Hồi Điểm
                    </button>
                    @endif

                    @if($daXacNhan)
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" disabled aria-disabled="true">
                        <i class="fa-solid fa-check me-1"></i> Đã Xác Nhận Kết Quả
                    </button>
                    @else
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalXacNhan_{{ $hoSo->MaHoSo }}">
                        <i class="fa-solid fa-signature me-1"></i> Xác Nhận Kết Quả
                    </button>
                    @endif

                    @unless($hoSo->baoCaoSauBaoVes()->exists())
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalYeuCauSua_{{ $hoSo->MaHoSo }}">
                        <i class="fa-solid fa-file-pen me-1"></i> Yêu Cầu Chỉnh Sửa
                    </button>
                    @endunless
                </div>
            </div>

            <!-- Tình trạng nộp phiếu của các giám khảo cho đề tài này -->
            <div class="mt-2 pt-2 border-top d-flex flex-wrap align-items-center gap-2" style="font-size: 0.78rem;">
                <span class="text-muted fw-bold">Giám khảo:</span>
                @foreach($item['gvHoanThanh'] as $gv)
                    <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2">
                        <i class="fa-solid fa-check me-1"></i> {{ $gv['HoTen'] }}
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
                <table class="table table-bordered table-hover align-middle mb-0 table-doichieu-diem" style="font-size: 0.84rem;">
                    <thead>
                        <tr>
                            <th style="width: 45px; min-width: 45px;" class="text-center align-middle text-white">STT</th>
                            <th style="min-width: 190px;" class="align-middle text-white">Sinh viên thực hiện</th>
                            @foreach($hoiDong->sorted_thanh_viens as $sIdx => $tv)
                            @php
                                $tenGk = $tv->giangVien->HoTen ?? $tv->MaGV;
                                $vaiTroGk = $tv->short_vai_tro ?? 'Giám khảo';
                            @endphp
                            <th class="text-center align-middle text-white" style="min-width: 155px; width: 160px;" title="{{ $tenGk }} ({{ $vaiTroGk }})">
                                <div class="gk-head-name" title="{{ $tenGk }} ({{ $vaiTroGk }})">
                                    {{ $tenGk }}
                                </div>
                                <div class="gk-head-role">
                                    {{ $vaiTroGk }}
                                </div>
                            </th>
                            @endforeach
                            <th class="text-center align-middle text-white" style="min-width: 115px; width: 115px;">Độ lệch điểm</th>
                            <th class="text-center align-middle text-white" style="min-width: 115px; width: 115px;">Điểm TB Sơ Bộ</th>
                            <th class="text-center align-middle text-white" style="min-width: 125px; width: 125px;">Điểm Chính Thức</th>
                            <th class="text-center align-middle text-white" style="min-width: 115px; width: 115px;">Trạng Thái</th>
                            <th class="text-center align-middle text-white" style="min-width: 100px; width: 100px;">Xếp Loại</th>
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
                            @foreach($hoiDong->sorted_thanh_viens as $tv)
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
                    <div>{!! \App\Helpers\HtmlHelper::renderRichText($hoSo->YeuCauChinhSuaSauBaoVe) !!}</div>
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
            <form action="{{ route('truongkhoa.ketqua.xacnhan') }}" method="POST" onsubmit="if(this.dataset.submitting) return false; this.dataset.submitting = '1'; const button = this.querySelector('button[type=submit]'); button.disabled = true; button.textContent = 'Đang xác nhận…';">
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
                                placeholder="Ví dụ: Bổ sung sơ đồ thiết kế kiến trúc phần 3.2; Sửa định dạng danh mục tài liệu tham khảo theo chuẩn IEEE; Cập nhật lại kết quả thực nghiệm...">{{ \App\Helpers\HtmlHelper::toPlainText($hoSo->YeuCauChinhSuaSauBaoVe) }}</textarea>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Thời hạn nộp lại báo cáo hoàn thiện <span class="text-danger">*</span>:</label>
                                <input type="datetime-local" name="HanNopBaoCaoSauBaoVe" class="form-control" 
                                       value="{{ $hoSo->hanNopHoanThienBaoCao()?->format('Y-m-d\TH:i') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Người phụ trách kiểm tra, xác nhận <span class="text-danger">*</span>:</label>
                                <select name="NguoiKiemTraChinhSua" class="form-select" required>
                                    <option value="{{ $deTai->giangVien->MaGV ?? '' }}">GVHD: {{ $deTai->giangVien->HoTen ?? 'Chưa rõ' }}</option>
                                    @foreach($hoiDong->sorted_thanh_viens as $tv)
                                        @if($tv->MaGV !== ($deTai->giangVien->MaGV ?? ''))
                                            <option value="{{ $tv->MaGV }}">{{ $tv->short_vai_tro }}: {{ $tv->giangVien->HoTen ?? $tv->MaGV }}</option>
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
        $customChucNangP = $phieu->hoSoBaoVe?->TieuChiChucNang;
        $rubricP = ($phieu->LoaiKhoaLuan === 'KLKS') 
            ? \App\Models\PhieuChamDiem::getRubricKLKS($customChucNangP) 
            : \App\Models\PhieuChamDiem::getRubricKLCN($customChucNangP);
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
                                    <th style="width: 45px; min-width: 45px;" class="text-center align-middle">STT</th>
                                    <th class="text-start align-middle" style="width: 58%; min-width: 320px;">Tiêu chí đánh giá</th>
                                    <th style="width: 70px; min-width: 70px;" class="text-center align-middle">CLO</th>
                                    <th style="width: 75px; min-width: 75px;" class="text-center align-middle">Tối đa</th>
                                    <th style="width: 85px; min-width: 85px;" class="text-center align-middle">Điểm chấm</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rubricP as $cr)
                                <tr>
                                    <td class="text-center align-top py-2 fw-semibold text-secondary">{{ $cr['stt'] }}</td>
                                    <td class="text-start align-top py-2" style="white-space: normal !important; overflow-wrap: anywhere !important; word-break: normal;">
                                        @include('partials.tieu_chi_renderer', ['cr' => $cr])
                                    </td>
                                    <td class="text-center align-top py-2"><span class="badge bg-light text-secondary border">{{ $cr['clo'] }}</span></td>
                                    <td class="text-center align-top py-2 fw-bold">{{ number_format($cr['max'], 2) }}</td>
                                    <td class="text-center align-top py-2 fw-bold text-primary">
                                        {{ isset($chiTietP[$cr['id']]) ? number_format((float)$chiTietP[$cr['id']], 2) : '0.00' }}
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="table-primary fw-bold">
                                    <td colspan="4" class="text-end">TỔNG ĐIỂM:</td>
                                    <td class="fs-6 text-primary text-center">{{ number_format((float)$phieu->Diem, 2) }}</td>
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
                <div class="modal-footer bg-light py-2 d-flex justify-content-end">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Modal Thu Hồi Hội Đồng -->
    <div class="modal fade" id="modalThuHoiHd" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                <div class="modal-header bg-danger text-white py-3">
                    <h5 class="modal-title fw-bold fs-6 mb-0">Thu Hồi Quyết Định Công Bố Hội Đồng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('truongkhoa.ketqua.thuhoi_hoidong') }}" method="POST">
                    @csrf
                    <input type="hidden" name="MaHoiDong" value="{{ $hoiDong->MaHoiDong }}">
                    <div class="modal-body p-4">
                        <div class="alert alert-danger rounded-3 small p-3 mb-3">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            Điểm của tất cả các đề tài thuộc Hội đồng này sẽ được chuyển về trạng thái <em>Chưa công bố</em>.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Lý do thu hồi công bố <span class="text-danger">*</span>:</label>
                            <textarea name="ly_do" class="form-control rounded-3" rows="3" placeholder="Nhập lý do thu hồi quyết định công bố của Hội đồng..." required minlength="5"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer py-2 bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Xác Nhận Thu Hồi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals Thu Hồi Từng Đề Tài -->
    @foreach($tienDo['danhSachHoSo'] as $item)
        @php $h = $item['hoSo']; @endphp
        <div class="modal fade" id="modalThuHoiDt_{{ $h->MaHoSo }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                    <div class="modal-header bg-danger text-white py-3">
                        <h5 class="modal-title fw-bold fs-6 mb-0">Thu Hồi Công Bố Đề Tài</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('truongkhoa.ketqua.thuhoi_detai') }}" method="POST">
                        @csrf
                        <input type="hidden" name="MaHoSo" value="{{ $h->MaHoSo }}">
                        <div class="modal-body p-4">
                            <div class="alert alert-danger rounded-3 small p-3 mb-3">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                Kết quả đề tài <strong>{{ $h->nhom?->deTai?->TenDeTai ?? $h->MaHoSo }}</strong> sẽ được chuyển về trạng thái <em>Chưa công bố</em>.
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small">Lý do thu hồi <span class="text-danger">*</span>:</label>
                                <textarea name="ly_do" class="form-control rounded-3" rows="3" placeholder="Nhập lý do thu hồi kết quả đề tài..." required minlength="5"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer py-2 bg-light">
                            <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Xác Nhận Thu Hồi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Modal Cấu hình thời hạn chỉnh sửa điểm -->
    <div class="modal fade" id="modalCauHinhSuaDiem" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header py-3 bg-light">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-regular fa-clock me-2 text-primary"></i> Thiết Lập Thời Hạn Chỉnh Sửa Điểm
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('truongkhoa.hoidong.cauhinh_suadiem', $hoiDong->MaHoiDong) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="small text-muted mb-3">
                            <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                            Giảng viên chỉ được phép chỉnh sửa điểm trong khoảng thời gian này. Khi hết hạn, hệ thống sẽ <strong>tự động khóa chức năng sửa điểm</strong> của cả 3 giảng viên.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Thời Gian Bắt Đầu Cho Phép Sửa:</label>
                            <input type="datetime-local" name="ThoiGianBatDauSuaDiem" class="form-control"
                                   value="{{ $hoiDong->ThoiGianBatDauSuaDiem ? $hoiDong->ThoiGianBatDauSuaDiem->format('Y-m-d\TH:i') : '' }}">
                            <div class="form-text small text-muted">Bỏ trống nếu cho phép sửa ngay.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Thời Gian Kết Thúc (Hạn Chót):</label>
                            <input type="datetime-local" name="ThoiGianKetThucSuaDiem" class="form-control"
                                   value="{{ $hoiDong->ThoiGianKetThucSuaDiem ? $hoiDong->ThoiGianKetThucSuaDiem->format('Y-m-d\TH:i') : '' }}">
                            <div class="form-text small text-muted">Sau thời điểm này, chức năng sửa điểm sẽ tự động bị khóa hoàn toàn.</div>
                        </div>
                    </div>
                    <div class="modal-footer py-2 bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Lưu Cấu Hình Thời Hạn</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Mở khóa sửa điểm xử lý sai sót -->
    <div class="modal fade" id="modalMoKhoaSuaDiem" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header py-3 bg-light">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="fa-solid fa-lock-open me-2"></i> Mở Khóa Sửa Điểm (Xử Lý Sai Sót)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('truongkhoa.ketqua.mokhoaphieu') }}" method="POST">
                    @csrf
                    <input type="hidden" name="MaHoiDong" value="{{ $hoiDong->MaHoiDong }}">
                    <div class="modal-body p-4">
                        <div class="alert alert-warning border-warning-subtle small mb-3">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            <strong>Lưu ý nghiệp vụ:</strong> Chỉ mở khóa khi có sai sót cần đính chính. Theo quy định, <strong>bắt buộc phải ghi nhận rõ lý do mở khóa</strong> và hành động này sẽ được ghi vết vào Lịch sử kiểm toán không thể xóa.
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">
                                Lý Do Mở Khóa <span class="text-danger">*</span> (Tối thiểu 5 ký tự):
                            </label>
                            <textarea name="ly_do" class="form-control" rows="3" required minlength="5"
                                      placeholder="Nhập lý do cụ thể vì sao mở khóa sửa điểm (VD: Giám khảo chấm nhầm điểm tiêu chí 3 của SV Nguyễn Văn A, cần chỉnh sửa lại theo biên bản...)">{{ old('ly_do', $hoiDong->LyDoMoKhoaSuaDiem) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Gia Hạn Chót Sửa Mới (Tùy chọn):</label>
                            <input type="datetime-local" name="han_sua_moi" class="form-control"
                                   value="{{ $hoiDong->ThoiGianKetThucSuaDiem ? $hoiDong->ThoiGianKetThucSuaDiem->format('Y-m-d\TH:i') : '' }}">
                            <div class="form-text small text-muted">Thời hạn mới để giảng viên hoàn tất sửa điểm trước khi tự động khóa lại.</div>
                        </div>
                    </div>
                    <div class="modal-footer py-2 bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold shadow-xs">
                            <i class="fa-solid fa-unlock me-1"></i> Xác Nhận Mở Khóa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Lịch sử chỉnh sửa điểm (Audit Log) -->
    <div class="modal fade" id="modalLichSuSuaDiem" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header py-3 bg-light border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-primary fs-5"></i>
                        <div>
                            <h5 class="modal-title fw-bold text-dark fs-6 mb-0">
                                Lịch Sử Chỉnh Sửa Điểm &amp; Kiểm Toán (Audit Log) — {{ $hoiDong->TenHoiDong }}
                            </h5>
                            <div class="small text-muted">Dữ liệu bất biến, ghi vết mọi thay đổi điểm tiêu chí/CLO và hành động mở khóa.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    @if($lichSuSuaDiems->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-receipt fa-3x mb-3 text-secondary opacity-50"></i>
                            <h6>Chưa có lịch sử chỉnh sửa điểm nào được ghi nhận cho Hội đồng này.</h6>
                        </div>
                    @else
                        <div class="table-responsive rounded-3 border">
                            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 45px;">STT</th>
                                        <th style="min-width: 150px;">Giảng Viên Thực Hiện</th>
                                        <th style="min-width: 110px;">Sinh Viên</th>
                                        <th style="min-width: 130px;">CLO / Tiêu Chí</th>
                                        <th class="text-center" style="width: 90px;">Điểm Cũ</th>
                                        <th class="text-center" style="width: 90px;">Điểm Mới</th>
                                        <th style="min-width: 140px;">Thời Gian</th>
                                        <th style="min-width: 200px;">Ghi Chú / Lý Do</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lichSuSuaDiems as $idx => $ls)
                                        <tr>
                                            <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $ls->NguoiThucHien }}</div>
                                                <div class="small text-muted font-monospace">{{ $ls->MaGV }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border font-monospace">{{ $ls->MaSV }}</span>
                                            </td>
                                            <td>
                                                @if($ls->MaCLO === 'UNLOCK')
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                                        <i class="fa-solid fa-lock-open me-1"></i> MỞ KHÓA SỬA ĐIỂM
                                                    </span>
                                                @else
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill me-1">
                                                        {{ $ls->MaCLO ?? 'CLO' }}
                                                    </span>
                                                    <span class="small text-secondary">{{ Str::limit($ls->TenTieuChi ?? $ls->MaTieuChi, 35) }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center font-monospace">
                                                @if(!is_null($ls->DiemCu))
                                                    <span class="text-muted text-decoration-line-through">{{ number_format($ls->DiemCu, 2) }}</span>
                                                @else
                                                    <span class="text-muted">&mdash;</span>
                                                @endif
                                            </td>
                                            <td class="text-center font-monospace">
                                                @if(!is_null($ls->DiemMoi))
                                                    <span class="fw-bold text-success">{{ number_format($ls->DiemMoi, 2) }}</span>
                                                @else
                                                    <span class="text-muted">&mdash;</span>
                                                @endif
                                            </td>
                                            <td class="small text-muted">
                                                <div>{{ $ls->ThoiGian ? \Carbon\Carbon::parse($ls->ThoiGian)->format('d/m/Y') : ($ls->created_at ? $ls->created_at->format('d/m/Y') : '') }}</div>
                                                <div class="fw-bold text-dark">{{ $ls->ThoiGian ? \Carbon\Carbon::parse($ls->ThoiGian)->format('H:i:s') : ($ls->created_at ? $ls->created_at->format('H:i:s') : '') }}</div>
                                            </td>
                                            <td class="small" style="overflow-wrap: anywhere; word-break: break-word;">
                                                <div class="{{ $ls->MaCLO === 'UNLOCK' ? 'text-danger fw-semibold' : 'text-secondary' }}">
                                                    {{ $ls->LyDo ?? 'Cập nhật điểm phiếu chấm' }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer py-2 bg-light">
                    <span class="small text-muted me-auto">
                        <i class="fa-solid fa-shield-halved text-success me-1"></i> Báo cáo kiểm toán bất biến &bull; Không cho phép sửa xóa dữ liệu lịch sử
                    </span>
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
