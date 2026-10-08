@extends('layouts.truongkhoa')

@section('page_title', 'Kết Quả Khóa Luận Toàn Khoa')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-award me-2 text-primary"></i>
                Bảng Điểm & Xếp Loại Khóa Luận Tốt Nghiệp – {{ $khoa->TenKhoa ?? 'Khoa CNTT' }}
            </h4>
            <p class="text-muted mb-0 small">
                Tra cứu kết quả điểm đánh giá của Giảng viên hướng dẫn, Phản biện và Hội đồng chấm bảo vệ toàn Khoa.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('truongkhoa.ketqua.export', request()->all()) }}" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-semibold">
                <i class="fa-solid fa-file-excel me-1"></i> Xuất Bảng Điểm (Excel / CSV)
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
                    <div class="small text-muted">Đã hoàn thành khóa luận</div>
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

    <!-- ═══ TOOLBAR BỘ LỌC ═══ -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body">
            <form method="GET" action="{{ route('truongkhoa.ketqua.index') }}" class="row g-2 align-items-center">
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
                    <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold text-nowrap w-100">
                        <i class="fa-solid fa-filter me-1"></i> Lọc
                    </button>
                    <a href="{{ route('truongkhoa.ketqua.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3" title="Đặt lại">
                        <i class="fa-solid fa-rotate"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══ BẢNG KẾT QUẢ KHÓA LUẬN ═══ -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-table-list text-danger me-2"></i>
                Danh Sách Điểm Khóa Luận Sinh Viên ({{ $ketQuas->total() }} sinh viên)
            </h6>
            <div class="small text-muted">Trang {{ $ketQuas->currentPage() }}/{{ $ketQuas->lastPage() }}</div>
        </div>
        <div class="card-body p-0">
            @if($ketQuas->isEmpty())
                <div class="text-center py-5">
                    <i class="fa-solid fa-award text-muted fa-3x mb-3"></i>
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
                                <th class="text-center">Điểm HĐ (100%)</th>
                                <th class="text-center text-primary">Điểm Tổng Kết</th>
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
                                        <div class="small fw-semibold text-dark" style="max-width: 280px;">
                                            {{ $kq->hoSoBaoVe->deTai->TenDeTai ?? 'Chưa xác định' }}
                                        </div>
                                    </td>
                                    <td class="text-center fw-semibold text-dark">
                                        {{ number_format((float)($kq->DiemHoiDongTB ?? $kq->DiemTongKet ?? 0), 2) }}
                                    </td>
                                    <td class="text-center fw-bold fs-6 text-primary">
                                        {{ number_format((float)($kq->DiemTongKet ?? $kq->DiemHoiDongTB ?? 0), 2) }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $diem = (float)($kq->DiemTongKet ?? 0);
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
            @endif
        </div>
        @if($ketQuas->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $ketQuas->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
