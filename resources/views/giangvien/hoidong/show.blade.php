@extends('layouts.giangvien')

@section('page_title', 'Chi Tiết Hội Đồng: ' . $hoiDong->TenHoiDong)

@section('content')
@php
    $reasonCheckShow = '';
    $canGradeShow = $hoiDong->canGrade($gv, null, $reasonCheckShow);
@endphp
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('giangvien.hoidong.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1">
                    <i class="fa-solid fa-arrow-left me-1"></i> Danh Sách Hội Đồng
                </a>
                <span class="badge bg-light text-secondary border font-monospace">Mã HĐ: {{ $hoiDong->MaHoiDong }}</span>
            </div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark, #003366);">
                <i class="fa-solid fa-landmark me-2 text-primary"></i>{{ $hoiDong->TenHoiDong }}
            </h4>
            <div class="text-muted small">
                Khoa: <strong>{{ $hoiDong->khoa->TenKhoa ?? 'Công nghệ Thông tin' }}</strong> &bull; 
                Học kỳ: <strong>{{ $hoiDong->hocKy->TenHocKy ?? 'Hiện tại' }}</strong> &bull; 
                Quyết định: <strong>{{ $hoiDong->SoQuyetDinh ?? 'Đang cập nhật' }}</strong>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if($hoiDong->FileQuyetDinh)
                <a href="{{ asset('storage/' . $hoiDong->FileQuyetDinh) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold shadow-xs">
                    <i class="fa-solid fa-file-pdf me-1"></i> Quyết Định Thành Lập HĐ (PDF)
                </a>
            @endif
            @if($canGradeShow)
                <a href="{{ route('giangvien.chamdiem.index', ['MaHoiDong' => $hoiDong->MaHoiDong]) }}" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm" style="background: #0072ce; border-color: #0072ce;">
                    <i class="fa-solid fa-calculator me-1"></i> Vào Phòng Chấm Điểm
                </a>
            @elseif($hoiDong->trang_thai_thoi_gian === 'SapDienRa')
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4 fw-semibold text-white-50 opacity-75 shadow-sm" disabled title="{{ $reasonCheckShow ?: 'Chưa đến thời gian chấm điểm' }}">
                    <i class="fa-solid fa-lock me-1"></i> Phòng Chấm Điểm
                </button>
            @else
                <a href="{{ route('giangvien.chamdiem.index', ['MaHoiDong' => $hoiDong->MaHoiDong]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-4 fw-semibold shadow-sm" title="Xem bảng điểm và biên bản hội đồng (Chỉ đọc)">
                    <i class="fa-solid fa-file-lines me-1"></i> Xem Điểm Hội Đồng
                </a>
            @endif
        </div>
    </div>

    <!-- Thông Tin Tổng Quan & Lịch Trình Bảo Vệ -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="fa-solid fa-calendar-day text-primary me-2"></i>Lịch Trình &amp; Địa Điểm Tổ Chức Bảo Vệ
                </h6>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 bg-light border border-slate-200">
                            <span class="text-muted small d-block mb-1"><i class="fa-solid fa-location-dot text-danger me-1"></i>Địa điểm / Phòng bảo vệ:</span>
                            <div class="fw-bold text-dark fs-6">{{ $hoiDong->DiaDiem ?? 'Phòng B.304 - Nhà B' }}</div>
                            <div class="small text-muted mt-1">Cơ sở chính HUIT: 140 Lê Trọng Tấn, Tây Thạnh, Tân Phú</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 bg-light border border-slate-200">
                            <span class="text-muted small d-block mb-1"><i class="fa-regular fa-clock text-primary me-1"></i>Thời gian tổ chức:</span>
                            <div class="fw-bold text-dark fs-6">
                                {{ $hoiDong->NgayBaoVe ? $hoiDong->NgayBaoVe->format('d/m/Y') : 'Chưa xếp ngày' }}
                            </div>
                            <div class="small text-muted mt-1">
                                Khung giờ: <strong>{{ $hoiDong->ThoiGianBatDau ? \Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('H:i') : '07:30' }}</strong> đến <strong>{{ $hoiDong->ThoiGianKetThuc ? \Carbon\Carbon::parse($hoiDong->ThoiGianKetThuc)->format('H:i') : '17:00' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 bg-light border border-slate-200">
                            <span class="text-muted small d-block mb-1"><i class="fa-solid fa-file-contract text-info me-1"></i>Văn bản quyết định thành lập:</span>
                            <div class="fw-bold text-dark">{{ $hoiDong->SoQuyetDinh ?? 'Số: .../QĐ-ĐHTP' }}</div>
                            <div class="small text-muted mt-1">
                                Ký ngày: {{ $hoiDong->NgayBanHanh ? $hoiDong->NgayBanHanh->format('d/m/Y') : '...' }} &bull; Người ký: {{ $hoiDong->NguoiKy ?? 'Hiệu trưởng / Trưởng khoa' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 bg-light border border-slate-200">
                            <span class="text-muted small d-block mb-1"><i class="fa-solid fa-shield-halved text-success me-1"></i>Trạng thái hội đồng:</span>
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
                                    @if($hoiDong->dem_nguoc_bat_dau)
                                        <div class="text-muted small mt-1" style="font-size: 0.72rem;">{{ $hoiDong->dem_nguoc_bat_dau }}</div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thẻ Vai Trò Của Thầy/Cô Trong Hội Đồng -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 d-flex flex-column justify-content-between" style="border-top: 5px solid {{ $isChuTich ? '#ffc107' : ($isThuKy ? '#0dcaf0' : '#0072ce') }} !important;">
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-2">Vai Trò Được Phân Công:</div>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        @if($isChuTich)
                            <div class="p-3 bg-warning-subtle text-warning-emphasis rounded-circle">
                                <i class="fa-solid fa-crown fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-warning-emphasis">CHỦ TỊCH</h4>
                                <div class="text-muted small">Điều hành phiên bảo vệ</div>
                            </div>
                        @elseif($isThuKy)
                            <div class="p-3 bg-info-subtle text-info-emphasis rounded-circle">
                                <i class="fa-solid fa-pen-nib fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-info-emphasis">THƯ KÝ</h4>
                                <div class="text-muted small">Chuẩn bị phiếu &amp; ghi biên bản</div>
                            </div>
                        @elseif($myRole === 'Phản biện')
                            <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                                <i class="fa-solid fa-user-check fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-primary">PHẢN BIỆN</h4>
                                <div class="text-muted small">Đánh giá &amp; phản biện đề tài</div>
                            </div>
                        @else
                            <div class="p-3 bg-secondary-subtle text-secondary rounded-circle">
                                <i class="fa-solid fa-user fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-secondary">ỦY VIÊN</h4>
                                <div class="text-muted small">Chấm điểm &amp; nhận xét câu hỏi</div>
                            </div>
                        @endif
                    </div>

                    <div class="p-3 bg-light rounded-3 border text-dark small mb-3">
                        @if($isThuKy)
                            <i class="fa-solid fa-circle-info text-info me-1"></i> <strong>Quy định vai trò Thư ký:</strong> Thầy/Cô chịu trách nhiệm xem đề cương đã duyệt của nhóm, thiết lập/chuẩn bị tiêu chí chức năng phiếu chấm, hỗ trợ xuất phiếu và tổng hợp biên bản bảo vệ.
                        @elseif($isChuTich)
                            <i class="fa-solid fa-circle-info text-warning me-1"></i> <strong>Quy định vai trò Chủ tịch:</strong> Điều hành quá trình báo cáo, mở câu hỏi chất vấn và kết luận kết quả bảo vệ của từng nhóm đề tài.
                        @else
                            <i class="fa-solid fa-circle-info text-primary me-1"></i> <strong>Quy định thành viên:</strong> Chấm điểm các tiêu chí (Hình thức báo cáo, Thuyết trình, Trả lời câu hỏi, Cài đặt chức năng), nhận xét và khóa phiếu điểm.
                        @endif
                    </div>
                </div>

                <div>
                    @if($canGradeShow)
                        <a href="{{ route('giangvien.chamdiem.index', ['MaHoiDong' => $hoiDong->MaHoiDong]) }}" class="btn btn-primary rounded-pill w-100 fw-bold py-2 shadow-xs" style="background: #0072ce; border-color: #0072ce;">
                            <i class="fa-solid fa-file-signature me-1"></i> Mở Bảng Điểm Hội Đồng
                        </a>
                    @elseif($hoiDong->trang_thai_thoi_gian === 'SapDienRa')
                        <button type="button" class="btn btn-secondary rounded-pill w-100 fw-semibold py-2 text-white-50 opacity-75" disabled title="{{ $reasonCheckShow ?: 'Chưa đến thời gian chấm điểm' }}">
                            <i class="fa-solid fa-lock me-1"></i> Mở Bảng Điểm Hội Đồng
                        </button>
                    @else
                        <a href="{{ route('giangvien.chamdiem.index', ['MaHoiDong' => $hoiDong->MaHoiDong]) }}" class="btn btn-outline-secondary rounded-pill w-100 fw-semibold py-2" title="Xem bảng điểm và biên bản hội đồng (Chỉ đọc)">
                            <i class="fa-solid fa-file-lines me-1"></i> Xem Bảng Điểm Hội Đồng
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Danh Sách Thành Viên Hội Đồng -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-users text-primary me-2"></i>Thành Viên Hội Đồng Đánh Giá ({{ $sortedThanhViens->count() }} Thầy/Cô)
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">STT</th>
                            <th style="width: 140px;">Vai Trò</th>
                            <th>Họ Và Tên Giảng Viên</th>
                            <th>Học Vị / Chức Vụ</th>
                            <th>Bộ Môn</th>
                            <th>Thông Tin Liên Hệ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sortedThanhViens as $idx => $tv)
                            @php
                                $isMe = ($tv->MaGV === $gv->MaGV);
                            @endphp
                            <tr class="{{ $isMe ? 'table-primary-subtle' : '' }}">
                                <td class="text-center font-monospace">{{ $idx + 1 }}</td>
                                <td>
                                    @if($tv->VaiTro === 'Chủ tịch')
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold">
                                            <i class="fa-solid fa-crown me-1"></i> Chủ Tịch
                                        </span>
                                    @elseif($tv->VaiTro === 'Thư ký')
                                        <span class="badge bg-info text-white rounded-pill px-3 py-1 fw-bold">
                                            <i class="fa-solid fa-pen-nib me-1"></i> Thư Ký
                                        </span>
                                    @elseif($tv->VaiTro === 'Phản biện')
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
                                    <div class="fw-bold text-dark">
                                        {{ $tv->giangVien->HoTen ?? $tv->MaGV }}
                                        @if($isMe)
                                            <span class="badge bg-primary ms-1" style="font-size: 0.65rem;">Thầy/Cô</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted font-monospace">Mã GV: {{ $tv->MaGV }}</div>
                                </td>
                                <td>{{ $tv->giangVien->HocVi ?? 'Thạc sĩ' }}</td>
                                <td>{{ $tv->giangVien->boMon->TenBoMon ?? 'Khoa CNTT' }}</td>
                                <td>
                                    @if($tv->giangVien?->Email)
                                        <div><i class="fa-regular fa-envelope me-1 text-muted"></i><code>{{ $tv->giangVien->Email }}</code></div>
                                    @endif
                                    @if($tv->giangVien?->SoDienThoai)
                                        <div class="small text-muted"><i class="fa-solid fa-phone me-1 text-muted"></i>{{ $tv->giangVien->SoDienThoai }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Danh Sách Các Nhóm Đề Tài Thuộc Hội Đồng -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-graduation-cap text-primary me-2"></i>Danh Sách Nhóm Đề Tài Báo Cáo Trước Hội Đồng ({{ $hoiDong->hoSoBaoVes->count() }} nhóm)
            </span>
            <div class="text-muted small">
                Chuẩn bị phiếu chấm dựa trên đề cương đã duyệt &bull; Chấm điểm độc lập từng sinh viên
            </div>
        </div>
        <div class="card-body p-0">
            @if($hoiDong->hoSoBaoVes->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                    <h6>Chưa có nhóm đề tài nào được phân công vào hội đồng này.</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">STT</th>
                                <th style="width: 220px;">Nhóm &amp; Sinh Viên</th>
                                <th>Đề Tài Khóa Luận</th>
                                <th style="width: 140px;">Ngành Đào Tạo</th>
                                <th style="width: 160px;">GV Hướng Dẫn</th>
                                <th style="width: 150px;" class="text-center">Chuẩn Bị Phiếu</th>
                                <th style="width: 200px;" class="text-end pe-4">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hoiDong->hoSoBaoVes as $sIdx => $hoSo)
                                @php
                                    $nhom = $hoSo->nhom;
                                    $deTai = $nhom?->deTai;
                                    $isPhieuReady = ($hoSo->TrangThaiChuanBiPhieu === 'DaChuanBi');
                                @endphp
                                <tr>
                                    <td class="text-center font-monospace">{{ $sIdx + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-primary">{{ $nhom->TenNhom ?? 'Nhóm SV' }}</div>
                                        <div class="small text-muted font-monospace">Hồ sơ: {{ $hoSo->MaHoSo }}</div>
                                        <div class="small text-muted mt-1">
                                            @foreach($nhom->thanhViens ?? [] as $tvn)
                                                <div>&bull; {{ $tvn->sinhVien->HoTen ?? $tvn->MaSV }} ({{ $tvn->MaSV }})</div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $deTai->TenDeTai ?? 'Chưa cập nhật tên đề tài' }}</div>
                                        @if($deTai && $deTai->TenDeTaiTiengAnh)
                                            <div class="text-muted fst-italic small">{{ $deTai->TenDeTaiTiengAnh }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                                            <i class="fa-solid fa-graduation-cap me-1"></i>{{ $hoSo->ten_nganh }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $deTai->giangVien->HoTen ?? 'Chưa rõ' }}</div>
                                        <div class="small text-muted">{{ $deTai->giangVien->boMon->TenBoMon ?? '' }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($isPhieuReady)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-circle-check me-1"></i> ĐÃ CHUẨN BỊ
                                            </span>
                                            <div class="text-muted" style="font-size: 0.7rem;">bởi Thư ký HĐ</div>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-hourglass-half me-1"></i> CHƯA CHUẨN BỊ
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        @php
                                            $reasonHs = '';
                                            $canGradeHs = $hoiDong->canGrade($gv, $hoSo, $reasonHs);
                                        @endphp
                                        <div class="d-flex justify-content-end gap-1 flex-wrap">
                                            @if($canGradeHs)
                                                <a href="{{ route('giangvien.chamdiem.index', ['MaHoiDong' => $hoiDong->MaHoiDong, 'MaHoSo' => $hoSo->MaHoSo]) }}" class="btn btn-xs btn-primary rounded-pill px-3 py-1 fw-bold shadow-xs">
                                                    <i class="fa-solid fa-calculator me-1"></i> Chấm điểm
                                                </a>
                                            @elseif($hoiDong->trang_thai_thoi_gian === 'SapDienRa')
                                                <button type="button" class="btn btn-xs btn-secondary rounded-pill px-3 py-1 fw-semibold text-white-50 opacity-75" disabled title="{{ $reasonHs ?: 'Chưa đến thời gian bảo vệ' }}">
                                                    <i class="fa-solid fa-lock me-1"></i> Chấm điểm
                                                </button>
                                            @else
                                                <a href="{{ route('giangvien.chamdiem.index', ['MaHoiDong' => $hoiDong->MaHoiDong, 'MaHoSo' => $hoSo->MaHoSo]) }}" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" title="Xem chi tiết phiếu điểm (Chỉ đọc)">
                                                    <i class="fa-solid fa-eye me-1"></i> Xem điểm
                                                </a>
                                            @endif
                                            <a href="{{ route('giangvien.chamdiem.xuatPdf', ['MaHoiDong' => $hoiDong->MaHoiDong, 'MaHoSo' => $hoSo->MaHoSo]) }}" target="_blank" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" title="Xuất phiếu điểm PDF">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </a>
                                        </div>
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
@endsection
