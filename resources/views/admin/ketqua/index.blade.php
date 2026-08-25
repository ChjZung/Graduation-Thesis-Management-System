@extends('layouts.admin')

@section('page_title', 'Bảng Điểm & Kết Quả Bảo Vệ Khóa Luận')

@section('content')
<div class="card card-premium mb-4">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <div>
            <span><i class="fa-solid fa-square-poll-vertical text-primary me-2"></i> Bảng Điểm &amp; Kết Quả Bảo Vệ Khóa Luận Tốt Nghiệp</span>
            <div class="small text-muted font-normal mt-1">Tổng hợp điểm số từ GVHD (30%), Điểm Phản Biện (30%) và Điểm Hội Đồng (40%).</div>
        </div>
        <a href="{{ route('admin.ketqua.export', ['MaHocKy' => request('MaHocKy')]) }}" class="btn btn-success btn-sm rounded-pill px-4 shadow-sm fw-bold">
            <i class="fa-solid fa-file-excel me-1"></i> Xuất Bảng Điểm Excel
        </a>
    </div>
    <div class="card-body p-0">
        <!-- Filter Bar -->
        <div class="p-3 bg-light border-bottom">
            <form method="GET" action="{{ route('admin.ketqua.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select name="MaHocKy" class="form-select form-select-sm">
                        <option value="">-- Tất cả Học Kỳ --</option>
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>{{ $hk->TenHocKy }} ({{ $hk->NamHoc }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 w-100"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
                    <a href="{{ route('admin.ketqua.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2" title="Xóa lọc"><i class="fa-solid fa-rotate"></i></a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="5%">STT</th>
                        <th width="12%">MSSV</th>
                        <th width="20%">Họ &amp; Tên Sinh Viên</th>
                        <th width="15%">Lớp Administrative</th>
                        <th width="10%" class="text-center">Đ.GVHD (30%)</th>
                        <th width="10%" class="text-center">Đ.Phản Biện (30%)</th>
                        <th width="10%" class="text-center">Đ.Hội Đồng (40%)</th>
                        <th width="9%" class="text-center">Tổng Kết</th>
                        <th width="9%" class="text-center">Xếp Loại</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ketQuas as $idx => $kq)
                    @php
                        $badgeClass = match($kq->KetQua) {
                            'Xuất sắc'   => 'bg-primary',
                            'Giỏi'       => 'bg-success',
                            'Khá'        => 'bg-info text-dark',
                            'Trung bình' => 'bg-warning text-dark',
                            default      => 'bg-danger',
                        };
                    @endphp
                    <tr>
                        <td class="fw-bold text-muted">{{ $ketQuas->firstItem() + $idx }}</td>
                        <td><span class="badge-code">{{ $kq->sinhVien->MaSoSinhVien ?? $kq->MaSV }}</span></td>
                        <td>
                            <div class="fw-bold text-primary">{{ $kq->sinhVien->HoTen ?? 'Chưa rõ' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $kq->sinhVien->lop->TenLop ?? 'N/A' }}</span>
                        </td>
                        <td class="text-center fw-bold text-dark">{{ number_format($kq->DiemHuongDan, 1) }}</td>
                        <td class="text-center fw-bold text-dark">{{ number_format($kq->DiemPhanBien, 1) }}</td>
                        <td class="text-center fw-bold text-dark">{{ number_format($kq->DiemHoiDongTB, 1) }}</td>
                        <td class="text-center">
                            <span class="fs-6 fw-bold text-primary">{{ number_format($kq->DiemTongKet, 2) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $badgeClass }} rounded-pill px-3 py-1">{{ $kq->KetQua }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state-box">
                                <i class="fa-solid fa-square-poll-vertical"></i>
                                <h6>Chưa có dữ liệu điểm bảo vệ nào</h6>
                                <p class="small text-muted mb-0">Điểm số sẽ tự động tổng hợp khi Hội đồng chấm điểm hoàn tất.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($ketQuas->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $ketQuas->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
