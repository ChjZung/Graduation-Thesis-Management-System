@extends('layouts.giangvien')

@section('page_title', 'Duyệt Tiến Độ Báo Cáo')

@section('content')



<!-- Page Header -->
<div class="mb-4">
    <h3 class="fw-bold mb-1 text-primary-custom">
        <i class="fa-solid fa-list-check me-2 text-primary"></i>Duyệt Tiến Độ Báo Cáo
    </h3>
    <div class="text-muted small">
        Theo dõi tiến độ thực hiện và đánh giá các mốc báo cáo định kỳ của các nhóm sinh viên hướng dẫn
    </div>
</div>

<!-- 4 Thẻ KPI Thống Kê Theo Mockup Figma (congviechuongdan) -->
<div class="row g-3 mb-4">
    <!-- Card 1: Tổng số nhóm hướng dẫn -->
    <div class="col-xl-3 col-sm-6">
        <div class="card card-premium border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Số nhóm hướng dẫn</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-users fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h3 class="fw-bold text-dark mb-0">{{ $tongSoNhom }}</h3>
                    <span class="small text-muted">nhóm</span>
                </div>
                <div class="mt-2 text-muted small" style="font-size: 0.75rem;">
                    Đã đăng ký đề tài của bạn
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Báo cáo chờ nhận xét -->
    <div class="col-xl-3 col-sm-6">
        <div class="card card-premium border-0 shadow-sm h-100 {{ $baocaoChoDuyet > 0 ? 'border-start border-warning border-4' : '' }}">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Báo cáo chờ nhận xét</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-hourglass-half fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h3 class="fw-bold text-warning mb-0">{{ $baocaoChoDuyet }}</h3>
                    <span class="small text-muted">báo cáo</span>
                </div>
                <div class="mt-2 small" style="font-size: 0.75rem;">
                    @if($baocaoChoDuyet > 0)
                        <span class="text-warning fw-bold"><i class="fa-solid fa-bell me-1"></i>Cần đánh giá sớm</span>
                    @else
                        <span class="text-success"><i class="fa-solid fa-check me-1"></i>Đã duyệt hết bài nộp</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Báo cáo đã đạt -->
    <div class="col-xl-3 col-sm-6">
        <div class="card card-premium border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Báo cáo đã đạt</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-circle-check fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h3 class="fw-bold text-success mb-0">{{ $baocaoDaDat }}</h3>
                    <span class="small text-muted">mốc</span>
                </div>
                <div class="mt-2 text-muted small" style="font-size: 0.75rem;">
                    Đạt yêu cầu chuyên môn
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Nhóm cần nhắc nhở -->
    <div class="col-xl-3 col-sm-6">
        <div class="card card-premium border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Nhóm cần theo dõi</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h3 class="fw-bold text-danger mb-0">{{ $nhomCanNhacNho }}</h3>
                    <span class="small text-muted">nhóm</span>
                </div>
                <div class="mt-2 text-muted small" style="font-size: 0.75rem;">
                    Chưa nộp hoặc cần nộp lại
                </div>
            </div>
        </div>
    </div>
</div>

@if($nhoms->isEmpty())
<div class="card card-premium shadow-sm border-0">
    <div class="card-body text-center py-5">
        <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Chưa có nhóm nào đăng ký đề tài của bạn</h5>
        <p class="text-muted">Khi sinh viên đăng ký đề tài và được Giáo vụ duyệt chính thức, danh sách nhóm sẽ xuất hiện ở đây.</p>
    </div>
</div>
@else

@foreach($nhoms as $nhom)
@php
    $baoCaos = $nhom->baoCaos->keyBy('LanBaoCao');
    $mocs = [
        1 => 'Mốc 1: Đề cương & Phân tích',
        2 => 'Mốc 2: Nghiên cứu & Thiết kế',
        3 => 'Mốc 3: Lập trình & Kiểm thử',
        4 => 'Mốc 4: Hoàn thiện & Code Git',
        5 => 'Mốc 5: Báo cáo & Bảo vệ',
    ];
@endphp

<div class="card card-premium shadow-sm border-0 mb-4">
    <!-- Header nhóm -->
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                <i class="fa-solid fa-users text-primary"></i>
                <span>{{ $nhom->TenNhom }}</span>
                <span class="badge bg-light text-muted border" style="font-size: 0.72rem;">Mã: {{ $nhom->MaNhom }}</span>
            </div>
            <div class="small text-muted mt-1">
                <strong>Đề tài:</strong> {{ $nhom->deTai->TenDeTai ?? 'Chưa có đề tài' }}
            </div>
        </div>
        <div class="d-flex flex-wrap gap-1">
            @foreach($nhom->thanhViens as $tv)
                <span class="badge bg-light text-dark border px-2 py-1 small">
                    {{ $tv->sinhVien->HoTen ?? $tv->MaSV }}
                    @if(str_contains(mb_strtolower($tv->VaiTro), 'trưởng'))
                        👑 <span class="text-primary fw-bold">(Trưởng nhóm)</span>
                    @endif
                </span>
            @endforeach
        </div>
    </div>

    <!-- Bảng tiến độ 5 mốc của nhóm -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-secondary">
                    <tr>
                        <th class="ps-4" width="20%">Mốc tiến độ</th>
                        <th width="12%" class="text-center">Trạng thái</th>
                        <th width="12%" class="text-center">Ngày nộp</th>
                        <th width="16%" class="text-center">Tài liệu / Code</th>
                        <th width="40%">Tóm tắt AI & Nhận xét của GV</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mocs as $soMoc => $tenMoc)
                    @php 
                        $bc = $baoCaos[$soMoc] ?? null; 
                        $isPending = ($bc && $bc->TrangThai === 'Chờ duyệt');
                        $isDone = ($bc && $bc->TrangThai === 'Đạt');
                        $isResubmit = ($bc && $bc->TrangThai === 'Yêu cầu nộp lại');
                    @endphp
                    <tr class="{{ $isPending ? 'table-warning bg-opacity-25' : '' }}">
                        <td class="ps-4">
                            <span class="fw-bold small text-dark">{{ $tenMoc }}</span>
                            @if($bc && $bc->TieuDe)
                                <div class="text-muted" style="font-size: 0.75rem;">{{ Str::limit($bc->TieuDe, 40) }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!$bc)
                                <span class="badge bg-secondary-subtle text-secondary border rounded-pill small">Chưa nộp</span>
                            @elseif($isDone)
                                <span class="badge bg-success rounded-pill small">✅ Đạt</span>
                            @elseif($isPending)
                                <span class="badge bg-warning text-dark rounded-pill small fw-bold">⏳ Chờ duyệt</span>
                            @elseif($isResubmit)
                                <span class="badge bg-info text-dark rounded-pill small">🔄 Yêu cầu nộp lại</span>
                            @endif
                        </td>
                        <td class="text-center small text-muted">
                            {{ $bc ? \Carbon\Carbon::parse($bc->NgayNop)->format('d/m/Y') : '—' }}
                        </td>
                        <td class="text-center">
                            @if($bc)
                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                    @if($bc->TenFile)
                                        <a href="{{ asset('storage/' . $bc->DuongDanFile) }}" target="_blank"
                                           class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 small">
                                            <i class="fa-solid fa-file-pdf me-1"></i>PDF
                                        </a>
                                    @endif
                                    @if($bc->LinkCode)
                                        <a href="{{ $bc->LinkCode }}" target="_blank"
                                           class="btn btn-xs btn-outline-dark rounded-pill px-2 py-1 small">
                                            <i class="fa-brands fa-github me-1"></i>Code
                                        </a>
                                    @endif
                                    <a href="{{ route('giangvien.baocao.show', $bc->MaBaoCao) }}" 
                                       class="btn btn-xs btn-primary rounded-pill px-2 py-1 small d-inline-flex align-items-center gap-1 shadow-xs"
                                       style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;">
                                        <i class="fa-solid fa-wand-magic-sparkles text-warning"></i>
                                        <span class="fw-bold">{{ $isPending ? 'Duyệt (AI)' : 'Xem chi tiết' }}</span>
                                    </a>
                                </div>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="pe-3">
                            @if($bc)
                                <!-- AI Summary Widget nếu có -->
                                @if($bc->tomTatBaoCao)
                                @php $tt = $bc->tomTatBaoCao; @endphp
                                <div class="p-2 rounded-2 mb-2" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span style="font-size: 0.74rem; font-weight: 700; color: #4f46e5;">
                                            <i class="fa-solid fa-wand-magic-sparkles text-warning me-1"></i>Tóm tắt AI ({{ number_format($tt->DoTinCayAI, 0) }}% tin cậy)
                                        </span>
                                    </div>
                                    <div style="font-size: 0.76rem; line-height: 1.45;">
                                        @if($tt->CongViecDaHoanThanh)
                                            <div class="text-success mb-1"><strong>• Xong:</strong> {{ Str::limit($tt->CongViecDaHoanThanh, 90) }}</div>
                                        @endif
                                        @if($tt->KhoKhan)
                                            <div class="text-warning-emphasis"><strong>• Khó khăn:</strong> {{ Str::limit($tt->KhoKhan, 80) }}</div>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                <!-- Nhận xét hiện tại của GV nếu đã có -->
                                @if($bc->NhanXet)
                                <div class="small text-dark mb-1" style="font-size: 0.78rem;">
                                    <i class="fa-solid fa-comment-dots text-primary me-1"></i>
                                    <em>"{{ Str::limit($bc->NhanXet, 90) }}"</em>
                                </div>
                                @endif

                                <!-- Nút duyệt nhanh chuyển đến show view -->
                                @if($isPending)
                                <div class="mt-1">
                                    <a href="{{ route('giangvien.baocao.show', $bc->MaBaoCao) }}" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-xs">
                                        <i class="fa-solid fa-pen-to-square me-1"></i>Đánh giá & Duyệt mốc này
                                    </a>
                                </div>
                                @endif
                            @else
                                <span class="text-muted small">Sinh viên chưa nộp bài</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endforeach

@endif

@endsection
