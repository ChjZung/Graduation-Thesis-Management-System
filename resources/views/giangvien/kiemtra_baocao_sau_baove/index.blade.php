@extends('layouts.giangvien')

@section('page_title', 'Kiểm Tra Báo Cáo Chỉnh Sửa Sau Bảo Vệ')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-file-circle-check me-2 text-primary"></i>
                Kiểm Tra &amp; Xác Nhận Báo Cáo Chỉnh Sửa Sau Bảo Vệ
            </h4>
            <div class="text-muted small">
                Danh sách các nhóm khóa luận được Hội đồng yêu cầu chỉnh sửa báo cáo do Thầy/Cô hướng dẫn hoặc được phân công kiểm tra.
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('giangvien.kiemtra_baocao_sau_baove.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 rounded-3" style="border-left: 4px solid #0072ce !important;">
                    <div class="card-body">
                        <div class="text-muted small fw-semibold text-uppercase">Tổng Nhóm Cần Sửa</div>
                        <h3 class="fw-bold my-1 text-primary">{{ $counts['total'] }}</h3>
                        <div class="small text-muted">Có yêu cầu chỉnh sửa</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('giangvien.kiemtra_baocao_sau_baove.index', ['status' => 'cho_kiem_tra']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 rounded-3" style="border-left: 4px solid #ffc107 !important;">
                    <div class="card-body">
                        <div class="text-muted small fw-semibold text-uppercase">Đã Nộp - Chờ Kiểm Tra</div>
                        <h3 class="fw-bold my-1 text-warning">{{ $counts['cho_kiem_tra'] }}</h3>
                        <div class="small text-muted">Cần đánh giá bản nộp mới</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('giangvien.kiemtra_baocao_sau_baove.index', ['status' => 'yeu_cau_sua']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 rounded-3" style="border-left: 4px solid #dc3545 !important;">
                    <div class="card-body">
                        <div class="text-muted small fw-semibold text-uppercase">Yêu Cầu Sửa Lại</div>
                        <h3 class="fw-bold my-1 text-danger">{{ $counts['yeu_cau_sua'] }}</h3>
                        <div class="small text-muted">Chưa đạt, chờ nộp lại</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('giangvien.kiemtra_baocao_sau_baove.index', ['status' => 'da_hoan_thanh']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 rounded-3" style="border-left: 4px solid #198754 !important;">
                    <div class="card-body">
                        <div class="text-muted small fw-semibold text-uppercase">Đã Xác Nhận Đạt</div>
                        <h3 class="fw-bold my-1 text-success">{{ $counts['da_hoan_thanh'] }}</h3>
                        <div class="small text-muted">Hoàn thành lưu trữ Khoa</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4 rounded-3 bg-white">
        <div class="card-body">
            <form method="GET" action="{{ route('giangvien.kiemtra_baocao_sau_baove.index') }}" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Tìm theo mã hồ sơ, tên nhóm hoặc tên đề tài..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="cho_kiem_tra" {{ request('status') === 'cho_kiem_tra' ? 'selected' : '' }}>Đã nộp — Chờ kiểm tra</option>
                        <option value="yeu_cau_sua" {{ request('status') === 'yeu_cau_sua' ? 'selected' : '' }}>Yêu cầu sửa lại</option>
                        <option value="da_hoan_thanh" {{ request('status') === 'da_hoan_thanh' ? 'selected' : '' }}>Đã xác nhận hoàn thành</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-3 px-3 fw-semibold w-100">
                        <i class="fa-solid fa-filter me-1"></i> Lọc
                    </button>
                    <a href="{{ route('giangvien.kiemtra_baocao_sau_baove.index') }}" class="btn btn-outline-secondary rounded-3 px-3" title="Đặt lại">
                        <i class="fa-solid fa-rotate"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh Sách Hồ Sơ -->
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-list-check text-primary me-2"></i>
                Danh Sách Hồ Sơ Cần Kiểm Tra ({{ $hoSos->total() }} hồ sơ)
            </h6>
            <div class="small text-muted">Trang {{ $hoSos->currentPage() }}/{{ $hoSos->lastPage() }}</div>
        </div>
        <div class="card-body p-0">
            @if($hoSos->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                    <h6>Không có hồ sơ nào cần kiểm tra báo cáo sau bảo vệ.</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">STT</th>
                                <th style="width: 120px;">Mã hồ sơ</th>
                                <th style="min-width: 250px;">Đề tài &amp; Nhóm sinh viên</th>
                                <th style="width: 140px;">Hội đồng</th>
                                <th style="width: 140px;">Thời hạn nộp</th>
                                <th style="width: 110px;" class="text-center">Số lần nộp</th>
                                <th style="width: 150px;" class="text-center">Trạng thái</th>
                                <th style="width: 130px;" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hoSos as $idx => $hs)
                            @php
                                $nhom = $hs->nhom;
                                $deTai = $hs->deTai;
                                $hanNop = $hs->HanNopBaoCaoSauBaoVe ? \Carbon\Carbon::parse($hs->HanNopBaoCaoSauBaoVe) : null;
                                $isQuaHan = $hanNop && $hanNop->isPast() && $hs->TrangThaiChinhSuaSauBaoVe !== 'DaHoanThanh';
                                $soLanNop = $hs->baoCaoSauBaoVes->count();
                            @endphp
                            <tr>
                                <td class="text-center text-muted">{{ $hoSos->firstItem() + $idx }}</td>
                                <td class="fw-bold font-monospace text-primary">{{ $hs->MaHoSo }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $deTai->TenDeTai ?? 'Chưa xác định' }}</div>
                                    <div class="small text-muted mt-1">
                                        <span>Nhóm: <strong>{{ $nhom->TenNhom ?? $hs->MaNhom }}</strong></span> &bull; 
                                        <span>Trưởng nhóm: {{ $nhom->truongNhom->HoTen ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $hs->hoiDong->TenHoiDong ?? 'Hội đồng' }}</div>
                                    <div class="text-muted small">{{ $hs->PhongBaoVe ?? 'Phòng B.304' }}</div>
                                </td>
                                <td>
                                    @if($hanNop)
                                        <div class="small fw-semibold {{ $isQuaHan ? 'text-danger' : 'text-dark' }}">
                                            {{ $hanNop->format('d/m/Y H:i') }}
                                        </div>
                                        @if($isQuaHan)
                                            <span class="badge bg-danger rounded-pill" style="font-size: 0.68rem;">Quá hạn</span>
                                        @endif
                                    @else
                                        <span class="text-muted small">--</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                        {{ $soLanNop }} lần
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($hs->TrangThaiChinhSuaSauBaoVe === 'DaHoanThanh')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Đã Đạt Yêu Cầu</span>
                                    @elseif($hs->TrangThaiChinhSuaSauBaoVe === 'DaNop')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1">Đã Nộp - Chờ Duyệt</span>
                                    @elseif($hs->TrangThaiChinhSuaSauBaoVe === 'YeuCauSuaTiep')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">Yêu Cầu Sửa Lại</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">Chờ Sinh Viên Nộp</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('giangvien.kiemtra_baocao_sau_baove.show', $hs->MaHoSo) }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i> Kiểm Tra
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $hoSos->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
