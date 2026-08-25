@extends('layouts.giangvien')

@section('page_title', 'Công Việc Hướng Dẫn & Tiến Độ Nhóm')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold text-primary-custom mb-1"><i class="fa-solid fa-list-check me-2"></i>Công Việc Hướng Dẫn & Tiến Độ Các Nhóm</h4>
    <p class="text-muted small mb-0">Hệ thống nhắc nhở công việc duyệt đề tài, nhận xét báo cáo tiến độ và xác nhận đủ điều kiện bảo vệ cho GVHD.</p>
</div>

<!-- Task Overview Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-premium border-start border-4 border-primary">
            <div class="card-body p-3 text-center">
                <div class="text-muted small fw-bold mb-1">Nhóm Đang Hướng Dẫn</div>
                <h3 class="fw-bold text-primary mb-0">{{ $nhoms->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-premium border-start border-4 border-warning">
            <div class="card-body p-3 text-center">
                <div class="text-muted small fw-bold mb-1">Báo Cáo Sắp Đến Hạn</div>
                <h3 class="fw-bold text-warning mb-0">{{ $stats['sap_den_han'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-premium border-start border-4 border-danger">
            <div class="card-body p-3 text-center">
                <div class="text-muted small fw-bold mb-1">Nhóm Quá Hạn Báo Cáo</div>
                <h3 class="fw-bold text-danger mb-0">{{ $stats['qua_han'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-premium border-start border-4 border-success">
            <div class="card-body p-3 text-center">
                <div class="text-muted small fw-bold mb-1">Báo Cáo Chờ Nhận Xét</div>
                <h3 class="fw-bold text-success mb-0">{{ $stats['cho_nhan_xet'] }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- List of Managed Groups and Phase Progress -->
<div class="card card-premium mb-4">
    <div class="card-header-premium">
        <span><i class="fa-solid fa-users text-primary me-2"></i> Danh Sách Nhóm Đồ Án Hướng Dẫn & Tiến Độ Nộp</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="12%">Mã Nhóm</th>
                        <th width="30%">Tên Đề Tài / Trưởng Nhóm</th>
                        <th width="20%">Báo Cáo Tiến Độ (1,2,3)</th>
                        <th width="18%">Hồ Sơ Bảo Vệ (Turnitin)</th>
                        <th width="20%" class="text-center">Thao Tác GVHD</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nhoms as $nhom)
                    <tr>
                        <td><span class="badge bg-light text-primary fw-bold border fs-6 px-2 py-1">{{ $nhom->MaNhom }}</span></td>
                        <td>
                            <div class="fw-bold text-primary-custom fs-6">{{ $nhom->deTai->TenDeTai ?? 'Chưa gán đề tài' }}</div>
                            <div class="small text-muted"><i class="fa-solid fa-user-tie me-1"></i>Trưởng nhóm: {{ $nhom->truongNhom->HoTen ?? 'Chưa xác định' }}</div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @for($l = 1; $l <= 3; $l++)
                                    @php $bc = $nhom->baoCaos->where('LanBaoCao', $l)->first(); @endphp
                                    @if($bc)
                                        <span class="badge bg-success rounded-pill" title="Lần {{ $l }}: Đã nộp {{ date('d/m', strtotime($bc->NgayNop)) }}">Lần {{ $l }} ✓</span>
                                    @else
                                        <span class="badge bg-light text-muted border rounded-pill" title="Lần {{ $l }}: Chưa nộp">Lần {{ $l }} -</span>
                                    @endif
                                @endfor
                            </div>
                        </td>
                        <td>
                            @if($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->TyLeTrungLap !== null)
                                <span class="badge bg-info text-white rounded-pill">Turnitin: {{ $nhom->hoSoBaoVe->TyLeTrungLap }}%</span>
                            @else
                                <span class="badge bg-light text-muted border rounded-pill">Chưa kiểm tra</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('giangvien.baocao.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Nhận xét
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Bạn chưa được phân công hướng dẫn nhóm đồ án nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
