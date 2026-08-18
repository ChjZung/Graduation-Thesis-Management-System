@extends('layouts.giangvien')

@section('page_title', 'Duyệt Tiến Độ Báo Cáo')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3">
    <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($nhoms->isEmpty())
<div class="card card-premium">
    <div class="card-body text-center py-5">
        <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Chưa có nhóm nào đăng ký đề tài của bạn</h5>
        <p class="text-muted">Khi sinh viên đăng ký và được duyệt, danh sách nhóm sẽ hiện ở đây.</p>
    </div>
</div>
@else

@foreach($nhoms as $nhom)
@php
    $baoCaos = $nhom->baoCaos->keyBy('LanBaoCao');
    $mocs = [
        1 => 'Mốc 1: Đề cương',
        2 => 'Mốc 2: Thiết kế',
        3 => 'Mốc 3: Lập trình',
        4 => 'Mốc 4: Code',
        5 => 'Mốc 5: Báo cáo',
    ];
@endphp

<div class="card card-premium mb-4">
    <!-- Header nhóm -->
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <div>
            <i class="fa-solid fa-users me-2 text-primary"></i>
            <strong>{{ $nhom->TenNhom }}</strong>
            <span class="text-muted ms-2" style="font-size: 0.85rem;">— {{ $nhom->deTai->TenDeTai ?? 'Chưa có đề tài' }}</span>
        </div>
        <div>
            @foreach($nhom->thanhViens as $tv)
                <span class="badge bg-light text-dark border me-1">
                    {{ $tv->sinhVien->HoTen ?? '' }}
                    @if($tv->VaiTro === 'Trưởng nhóm') 👑 @endif
                </span>
            @endforeach
        </div>
    </div>

    <!-- Bảng tiến độ 5 mốc -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0 align-middle">
                <thead>
                    <tr>
                        <th width="15%">Mốc</th>
                        <th width="12%" class="text-center">Trạng Thái</th>
                        <th width="12%" class="text-center">Ngày Nộp</th>
                        <th width="20%" class="text-center">File / Link</th>
                        <th width="41%" class="text-center">Tóm Tắt AI & Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mocs as $soMoc => $tenMoc)
                    @php $bc = $baoCaos[$soMoc] ?? null; @endphp
                    <tr class="{{ $bc && $bc->TrangThai === 'Đạt' ? 'table-success' : ($bc && $bc->TrangThai === 'Chờ duyệt' ? 'table-warning' : '') }}">
                        <td>
                            <span class="fw-bold">{{ $tenMoc }}</span>
                        </td>
                        <td class="text-center">
                            @if(!$bc)
                                <span class="badge bg-secondary rounded-pill">Chưa nộp</span>
                            @elseif($bc->TrangThai === 'Đạt')
                                <span class="badge bg-success rounded-pill">✅ Đạt</span>
                            @elseif($bc->TrangThai === 'Chờ duyệt')
                                <span class="badge bg-warning text-dark rounded-pill">⏳ Chờ duyệt</span>
                            @elseif($bc->TrangThai === 'Yêu cầu nộp lại')
                                <span class="badge bg-info text-dark rounded-pill">🔄 Nộp lại</span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ $bc ? $bc->NgayNop : '—' }}
                        </td>
                        <td class="text-center">
                            @if($bc)
                                @if($bc->TenFile)
                                    <a href="{{ asset('storage/' . $bc->DuongDanFile) }}" target="_blank"
                                       class="btn btn-sm btn-outline-danger rounded-pill mb-1">
                                        <i class="fa-solid fa-file-pdf me-1"></i>PDF
                                    </a>
                                @endif
                                @if($bc->LinkCode)
                                    <a href="{{ $bc->LinkCode }}" target="_blank"
                                       class="btn btn-sm btn-outline-dark rounded-pill mb-1">
                                        <i class="fa-brands fa-github me-1"></i>Code
                                    </a>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($bc)
                                <!-- AI Summary Widget -->
                                @if($bc->tomTat)
                                @php $tt = $bc->tomTat; @endphp
                                <div class="p-2 rounded-3 mb-2" style="background: linear-gradient(135deg, rgba(102,126,234,0.08), rgba(118,75,162,0.08)); border: 1px solid rgba(102,126,234,0.2);">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span style="font-size: 0.75rem; font-weight: 600; color: #667eea;">
                                            <i class="fa-solid fa-robot me-1"></i>Tóm Tắt AI
                                        </span>
                                        <span class="badge rounded-pill" style="background: rgba(102,126,234,0.15); color: #667eea; font-size: 0.7rem;">
                                            {{ number_format($tt->DoTinCayAI, 0) }}% tin cậy
                                        </span>
                                    </div>
                                    <div style="font-size: 0.78rem; line-height: 1.5;">
                                        <div class="text-success fw-semibold mb-1">✅ {{ Str::limit($tt->CongViecDaHoanThanh, 100) }}</div>
                                        <div class="text-warning fw-semibold">⚠️ {{ Str::limit($tt->KhoKhan, 80) }}</div>
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm rounded-pill mt-1 w-100"
                                        data-bs-toggle="modal" data-bs-target="#aiDetailModal{{ $bc->MaBaoCao }}">
                                        Xem đầy đủ
                                    </button>
                                </div>
                                @endif

                                <!-- Nhận xét đã có -->
                                @foreach($bc->nhanXets as $nx)
                                <div class="text-muted mb-1" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-comment me-1"></i><em>{{ Str::limit($nx->NoiDung, 60) }}</em>
                                    <span class="badge {{ $nx->LoaiNhanXet === 'Đạt' ? 'bg-success' : 'bg-info text-dark' }} ms-1" style="font-size: 0.65rem;">{{ $nx->LoaiNhanXet }}</span>
                                </div>
                                @endforeach

                                <!-- Form nhận xét nếu đang chờ duyệt -->
                                @if($bc->TrangThai === 'Chờ duyệt')
                                <button class="btn btn-sm btn-success rounded-pill w-100 fw-bold"
                                    data-bs-toggle="modal" data-bs-target="#nhanXetModal{{ $bc->MaBaoCao }}">
                                    <i class="fa-solid fa-pen-to-square me-1"></i>Nhận Xét & Đánh Giá
                                </button>
                                @endif
                            @else
                                <span class="text-muted small">Sinh viên chưa nộp</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modals: Nhận xét + AI chi tiết -->
@foreach($nhom->baoCaos as $bc)

<!-- Modal Nhận Xét -->
@if($bc->TrangThai === 'Chờ duyệt')
<div class="modal fade" id="nhanXetModal{{ $bc->MaBaoCao }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('giangvien.baocao.nhanxet', $bc->MaBaoCao) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-pen-to-square me-2"></i>Đánh Giá — Mốc {{ $bc->LanBaoCao }} / {{ $nhom->TenNhom }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($bc->tomTat)
                    @php $tt = $bc->tomTat; @endphp
                    <div class="p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, rgba(102,126,234,0.06), rgba(118,75,162,0.06)); border: 1px solid rgba(102,126,234,0.2);">
                        <div class="fw-bold mb-2" style="color: #667eea;">
                            <i class="fa-solid fa-robot me-2"></i>Bản Tóm Tắt AI (tham khảo)
                        </div>
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="p-2 rounded" style="background: #f0fff4; font-size: 0.82rem;">
                                    <strong class="text-success">✅ Đã xong:</strong> {{ $tt->CongViecDaHoanThanh }}
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-2 rounded" style="background: #fffbeb; font-size: 0.82rem;">
                                    <strong class="text-warning">⚠️ Khó khăn:</strong> {{ $tt->KhoKhan }}
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-2 rounded" style="background: #eff6ff; font-size: 0.82rem;">
                                    <strong class="text-primary">📅 Kế hoạch:</strong> {{ $tt->KeHoachTuanToi }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nhận Xét Của Giảng Viên <span class="text-danger">*</span></label>
                        <textarea name="NoiDung" class="form-control" rows="4"
                            placeholder="Viết nhận xét chi tiết về bài nộp của sinh viên..." required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">Kết Quả Đánh Giá <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check flex-fill">
                                <input class="form-check-input" type="radio" name="LoaiNhanXet" value="Đạt" id="dat{{ $bc->MaBaoCao }}" required>
                                <label class="form-check-label fw-bold text-success" for="dat{{ $bc->MaBaoCao }}">
                                    ✅ Đạt — Mở khóa mốc tiếp theo
                                </label>
                            </div>
                            <div class="form-check flex-fill">
                                <input class="form-check-input" type="radio" name="LoaiNhanXet" value="Yêu cầu nộp lại" id="noplai{{ $bc->MaBaoCao }}" required>
                                <label class="form-check-label fw-bold text-info" for="noplai{{ $bc->MaBaoCao }}">
                                    🔄 Yêu cầu nộp lại
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill fw-bold px-4">
                        <i class="fa-solid fa-paper-plane me-1"></i>Gửi Đánh Giá
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Modal AI Chi Tiết -->
@if($bc->tomTat)
@php $tt = $bc->tomTat; @endphp
<div class="modal fade" id="aiDetailModal{{ $bc->MaBaoCao }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <h5 class="modal-title">
                    <i class="fa-solid fa-robot me-2"></i>Tóm Tắt AI — Mốc {{ $bc->LanBaoCao }} / {{ $nhom->TenNhom }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-end mb-3">
                    <span class="badge rounded-pill px-3" style="background: linear-gradient(135deg, #667eea, #764ba2); font-size: 0.82rem;">
                        Độ tin cậy: {{ number_format($tt->DoTinCayAI, 0) }}%
                    </span>
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="p-3 rounded-3 border-start border-success border-3" style="background: #f0fff4;">
                            <div class="fw-bold text-success mb-1">✅ Đã hoàn thành</div>
                            <div style="font-size: 0.88rem;">{{ $tt->CongViecDaHoanThanh }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded-3 border-start border-warning border-3" style="background: #fffbeb;">
                            <div class="fw-bold text-warning mb-1">⚠️ Khó khăn</div>
                            <div style="font-size: 0.88rem;">{{ $tt->KhoKhan }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded-3 border-start border-primary border-3" style="background: #eff6ff;">
                            <div class="fw-bold text-primary mb-1">📅 Kế hoạch tiếp theo</div>
                            <div style="font-size: 0.88rem;">{{ $tt->KeHoachTuanToi }}</div>
                        </div>
                    </div>
                </div>
                <div class="text-muted mt-3" style="font-size: 0.75rem;">
                    <i class="fa-solid fa-clock me-1"></i>Được tạo lúc: {{ \Carbon\Carbon::parse($tt->NgayTomTat)->format('d/m/Y H:i') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endif

@endforeach
@endforeach
@endif

@endsection
