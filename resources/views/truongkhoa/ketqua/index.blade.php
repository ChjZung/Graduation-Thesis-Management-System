@extends('layouts.truongkhoa')

@section('page_title', 'Tổng Hợp & Công Bố Kết Quả Khóa Luận')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-award me-2 text-primary"></i>
                Quản Lý Kết Quả Bảo Vệ &amp; Bảng Điểm Khóa Luận — {{ $khoa->TenKhoa ?? 'Khoa CNTT' }}
            </h4>
            <div class="text-muted small">
                Theo dõi tiến độ chấm điểm các hội đồng, đối chiếu độ lệch điểm giám khảo, xác nhận và công bố điểm chính thức.
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('truongkhoa.ketqua.export', request()->all()) }}" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-semibold shadow-xs">
                <i class="fa-solid fa-file-excel me-2"></i> Xuất Bảng Điểm (Excel / CSV)
            </a>
        </div>
    </div>

    <!-- ═══ SUMMARY KPI CARDS ═══ -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #0072ce !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Tổng Sinh Viên Có Điểm</div>
                    <h3 class="fw-bold my-1 text-primary">{{ $stats['total'] }}</h3>
                    <div class="small text-muted">Đã ghi nhận dữ liệu điểm</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #198754 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Xuất Sắc (>= 9.0)</div>
                    <h3 class="fw-bold my-1 text-success">{{ $stats['xuat_sac'] }}</h3>
                    <div class="small text-muted">
                        {{ $stats['total'] > 0 ? round(($stats['xuat_sac'] / $stats['total']) * 100, 1) : 0 }}% tổng số
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #0d6efd !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Giỏi (8.0 - 8.9)</div>
                    <h3 class="fw-bold my-1 text-primary">{{ $stats['gioi'] }}</h3>
                    <div class="small text-muted">
                        {{ $stats['total'] > 0 ? round(($stats['gioi'] / $stats['total']) * 100, 1) : 0 }}% tổng số
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #ffc107 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Khá (7.0 - 7.9)</div>
                    <h3 class="fw-bold my-1 text-warning">{{ $stats['kha'] }}</h3>
                    <div class="small text-muted">
                        {{ $stats['total'] > 0 ? round(($stats['kha'] / $stats['total']) * 100, 1) : 0 }}% tổng số
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #dc3545 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Trung Bình / Chưa Đạt</div>
                    <h3 class="fw-bold my-1 text-danger">{{ $stats['trung_binh'] + $stats['khong_dat'] }}</h3>
                    <div class="small text-muted">TB: {{ $stats['trung_binh'] }} | Không đạt: {{ $stats['khong_dat'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ NAV TABS CHUYỂN ĐỔI CHẾ ĐỘ XEM ═══ -->
    <div class="d-flex border-bottom mb-4">
        <a href="{{ route('truongkhoa.ketqua.index', ['view' => 'hoidong']) }}" 
           class="px-4 py-2 text-decoration-none fw-bold {{ $viewMode === 'hoidong' ? 'border-bottom border-primary border-3 text-primary' : 'text-secondary' }}">
            <i class="fa-solid fa-users-viewfinder me-2"></i> Tổng Hợp &amp; Công Bố Theo Hội Đồng ({{ $hoiDongs->count() }})
        </a>
        <a href="{{ route('truongkhoa.ketqua.index', ['view' => 'toankhoa']) }}" 
           class="px-4 py-2 text-decoration-none fw-bold {{ $viewMode === 'toankhoa' ? 'border-bottom border-primary border-3 text-primary' : 'text-secondary' }}">
            <i class="fa-solid fa-list-ol me-2"></i> Danh Sách Bảng Điểm Toàn Khoa ({{ $ketQuas->total() }})
        </a>
    </div>

    @if($viewMode === 'hoidong')
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <!-- TAB 1: TỔNG HỢP & ĐỐI CHIẾU ĐIỂM THEO HỘI ĐỒNG BẢO VỆ                  -->
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body">
            <form method="GET" action="{{ route('truongkhoa.ketqua.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="view" value="hoidong">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" name="search_hd" class="form-control border-start-0" 
                               placeholder="Tìm theo tên hội đồng, phòng bảo vệ, mã hội đồng..." value="{{ request('search_hd') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="MaHocKy" class="form-select">
                        <option value="">-- Tất cả học kỳ --</option>
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-3 px-3 fw-semibold w-100">
                        <i class="fa-solid fa-filter me-2"></i> Lọc Hội Đồng
                    </button>
                    <a href="{{ route('truongkhoa.ketqua.index', ['view' => 'hoidong']) }}" class="btn btn-outline-secondary rounded-3 px-3" title="Đặt lại">
                        <i class="fa-solid fa-rotate"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        @forelse($hoiDongs as $hd)
        @php
            $td = $tienDoHoiDongs[$hd->MaHoiDong] ?? [];
            $duPhieu = $td['hoiDongDuPhieu'] ?? false;
            $soDeTai = $td['tongSoDeTai'] ?? 0;
            $soDeTaiDu = $td['soDeTaiDuPhieu'] ?? 0;
            $totalMembers = $td['totalMembers'] ?? 0;

            // Kiểm tra xem hội đồng đã công bố chưa
            $daCongBo = false;
            $coHoSo = $hd->hoSoBaoVes->isNotEmpty();
            if ($coHoSo) {
                $daCongBo = $hd->hoSoBaoVes->every(fn($h) => in_array($h->TrangThaiKetQua, ['DaCongBo', 'DaHoanThanh', 'CoYeuCauChinhSua']));
            }
        @endphp
        <div class="col-lg-6 col-xl-6">
            <div class="card border-0 shadow-sm h-100 rounded-3 position-relative" style="border-top: 4px solid {{ $daCongBo ? '#198754' : ($duPhieu ? '#0072ce' : '#ffc107') }} !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-light text-secondary border px-2 py-1 mb-1 font-monospace">{{ $hd->MaHoiDong }}</span>
                            <h5 class="fw-bold text-dark mb-1">{{ $hd->TenHoiDong }}</h5>
                            <div class="small text-muted">
                                <i class="fa-solid fa-location-dot me-1 text-danger"></i> {{ $hd->DiaDiem ?? 'Phòng B.304' }} &bull; 
                                <i class="fa-regular fa-calendar-days me-1 text-primary"></i> {{ \Carbon\Carbon::parse($hd->NgayBaoVe ?? now())->format('d/m/Y') }}
                            </div>
                        </div>
                        <div>
                            @if($daCongBo)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold">
                                    <i class="fa-solid fa-circle-check me-1"></i> Đã Công Bố Kết Quả
                                </span>
                            @elseif($duPhieu)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                                    <i class="fa-solid fa-check-double me-1"></i> Đã Đủ Phiếu Hợp Lệ
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2 fw-semibold">
                                    <i class="fa-solid fa-hourglass-half me-1"></i> Chưa Đủ Phiếu Chấm
                                </span>
                            @endif
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    <div class="row g-2 mb-3 small">
                        <div class="col-6">
                            <span class="text-muted">Số lượng giám khảo:</span>
                            <strong class="text-dark ms-1">{{ $totalMembers }} Giảng viên</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Đề tài bảo vệ:</span>
                            <strong class="text-dark ms-1">{{ $soDeTai }} nhóm</strong>
                        </div>
                        <div class="col-12">
                            <span class="text-muted">Tiến độ nộp phiếu:</span>
                            <strong class="{{ $soDeTaiDu === $soDeTai && $soDeTai > 0 ? 'text-success' : 'text-danger' }} ms-1">
                                {{ $soDeTaiDu }} / {{ $soDeTai }} đề tài đã có đủ phiếu giám khảo
                            </strong>
                        </div>
                    </div>

                    <!-- Danh sách các thành viên hội đồng -->
                    <div class="p-2 bg-light-subtle rounded-2 border mb-3">
                        <div class="small fw-bold text-secondary mb-1">Thành phần Hội đồng:</div>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($hd->thanhViens as $tv)
                            <span class="badge bg-white text-dark border small fw-normal py-1 px-2">
                                <strong>{{ $tv->giangVien->HoTen ?? $tv->MaGV }}</strong> ({{ $tv->VaiTro ?? 'Thành viên' }})
                            </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top">
                        <a href="{{ route('truongkhoa.ketqua.hoidong', $hd->MaHoiDong) }}" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs">
                            <i class="fa-solid fa-table-cells me-2"></i> Xem Chi Tiết &amp; Đối Chiếu Điểm
                        </a>

                        @if(!$duPhieu)
                        <form action="{{ route('truongkhoa.ketqua.nhacnho') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="MaHoiDong" value="{{ $hd->MaHoiDong }}">
                            <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold">
                                <i class="fa-regular fa-bell me-1"></i> Nhắc Giám Khảo Chưa Chấm
                            </button>
                        </form>
                        @elseif(!$daCongBo)
                        <form action="{{ route('truongkhoa.ketqua.congbo') }}" method="POST" class="d-inline" onsubmit="return confirm('Trưởng khoa có chắc chắn muốn CÔNG BỐ KẾT QUẢ chính thức cho Hội đồng {{ $hd->TenHoiDong }}?\n\nĐiểm số sẽ được khóa và gửi thông báo đến tất cả sinh viên và giảng viên.');">
                            @csrf
                            <input type="hidden" name="MaHoiDong" value="{{ $hd->MaHoiDong }}">
                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-xs">
                                <i class="fa-solid fa-bullhorn me-1"></i> Công Bố Kết Quả
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 p-5 text-center text-muted">
                <i class="fa-solid fa-clipboard-list fa-3x mb-3 text-secondary opacity-50"></i>
                <h6>Không tìm thấy Hội đồng bảo vệ nào phù hợp với bộ lọc hiện tại.</h6>
            </div>
        </div>
        @endforelse
    </div>

    @else
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <!-- TAB 2: BẢNG ĐIỂM SINH VIÊN TOÀN KHOA                                   -->
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body">
            <form method="GET" action="{{ route('truongkhoa.ketqua.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="view" value="toankhoa">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Tìm kiếm mã SV, họ tên hoặc đề tài..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="MaBoMon" class="form-select">
                        <option value="">-- Tất cả Bộ môn --</option>
                        @foreach($boMons as $bm)
                            <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                                {{ $bm->TenBoMon }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="MaHocKy" class="form-select">
                        <option value="">-- Tất cả học kỳ --</option>
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="xep_loai" class="form-select">
                        <option value="">-- Tất cả xếp loại --</option>
                        <option value="XuatSac" {{ request('xep_loai') == 'XuatSac' ? 'selected' : '' }}>Xuất sắc (>= 9.0)</option>
                        <option value="Gioi" {{ request('xep_loai') == 'Gioi' ? 'selected' : '' }}>Giỏi (8.0 - 8.9)</option>
                        <option value="Kha" {{ request('xep_loai') == 'Kha' ? 'selected' : '' }}>Khá (7.0 - 7.9)</option>
                        <option value="TrungBinh" {{ request('xep_loai') == 'TrungBinh' ? 'selected' : '' }}>Trung bình (5.0 - 6.9)</option>
                        <option value="KhongDat" {{ request('xep_loai') == 'KhongDat' ? 'selected' : '' }}>Không đạt (&lt; 5.0)</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-3 px-3 fw-semibold w-100">
                        <i class="fa-solid fa-filter me-2"></i> Lọc
                    </button>
                    <a href="{{ route('truongkhoa.ketqua.index', ['view' => 'toankhoa']) }}" class="btn btn-outline-secondary rounded-3 px-3" title="Đặt lại">
                        <i class="fa-solid fa-rotate"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-table-list text-primary me-2"></i>
                Danh Sách Điểm Khóa Luận Sinh Viên ({{ $ketQuas->total() }} sinh viên)
            </h6>
            <div class="small text-muted">Trang {{ $ketQuas->currentPage() }}/{{ $ketQuas->lastPage() }}</div>
        </div>
        <div class="card-body p-0">
            @if($ketQuas->isEmpty())
                <div class="text-center py-5">
                    <i class="fa-solid fa-award text-muted fa-3x mb-3 opacity-50"></i>
                    <h6 class="text-muted">Chưa có kết quả điểm khóa luận nào được ghi nhận cho bộ lọc này!</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">STT</th>
                                <th style="width: 110px;">Mã SV</th>
                                <th>Họ và Tên</th>
                                <th>Lớp & Bộ Môn</th>
                                <th>Đề Tài Khóa Luận</th>
                                <th class="text-center">Loại KL</th>
                                <th class="text-center">Điểm TB Sơ Bộ</th>
                                <th class="text-center text-primary">Điểm Chính Thức</th>
                                <th class="text-center">Trạng Thái</th>
                                <th class="text-center">Xếp Loại</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ketQuas as $idx => $kq)
                                <tr>
                                    <td class="text-center text-muted small">{{ $ketQuas->firstItem() + $idx }}</td>
                                    <td class="fw-bold text-danger">{{ $kq->MaSV }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $kq->sinhVien->HoTen ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ $kq->sinhVien->Email ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark">{{ $kq->sinhVien->lop->TenLop ?? ($kq->sinhVien->MaLop ?? 'N/A') }}</div>
                                        <div class="small text-muted">
                                            {{ $kq->hoSoBaoVe->deTai->giangVien->boMon->TenBoMon ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark" style="max-width: 250px;">
                                            {{ $kq->hoSoBaoVe->deTai->TenDeTai ?? 'Chưa xác định' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-secondary border font-monospace">
                                            {{ $kq->LoaiKhoaLuan ?? 'KLCN' }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-semibold text-dark">
                                        {{ number_format((float)($kq->DiemHoiDongTB ?? 0), 2) }}
                                    </td>
                                    <td class="text-center fw-bold fs-6 text-primary">
                                        {{ number_format((float)($kq->DiemChinhThuc ?? $kq->DiemTongKet ?? $kq->DiemHoiDongTB ?? 0), 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if($kq->TrangThai === 'Đã công bố')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">Đã công bố</span>
                                        @elseif($kq->TrangThai === 'Đã xác nhận')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1">Đã xác nhận</span>
                                        @elseif($kq->TrangThai === 'Đã đủ phiếu hợp lệ')
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1">Đã đủ phiếu</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1">Chưa đủ phiếu</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $diem = (float)($kq->DiemChinhThuc ?? $kq->DiemTongKet ?? 0);
                                        @endphp
                                        @if($diem >= 9.0)
                                            <span class="badge bg-success px-2 py-1">Xuất sắc</span>
                                        @elseif($diem >= 8.0)
                                            <span class="badge bg-primary px-2 py-1">Giỏi</span>
                                        @elseif($diem >= 7.0)
                                            <span class="badge bg-warning text-dark px-2 py-1">Khá</span>
                                        @elseif($diem >= 5.0)
                                            <span class="badge bg-secondary px-2 py-1">Trung bình</span>
                                        @else
                                            <span class="badge bg-danger px-2 py-1">Không đạt</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $ketQuas->links() }}
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
