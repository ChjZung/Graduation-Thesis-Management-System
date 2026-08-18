@extends('layouts.sinhvien')

@section('page_title', 'Nộp Báo Cáo Tiến Độ')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error') || isset($error) && $error)
<div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') ?? $error }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3">
    <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(isset($nhom) && $nhom)
<!-- Header Nhóm & Đề tài -->
<div class="card card-premium mb-4">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-users me-2 text-primary"></i>{{ $nhom->TenNhom }}</span>
        <span class="badge bg-success rounded-pill px-3">{{ $nhom->deTai->TrangThai ?? '' }}</span>
    </div>
    <div class="card-body py-3">
        <div class="row">
            <div class="col-md-8">
                <strong class="text-primary-custom">📘 Đề tài:</strong> {{ $nhom->deTai->TenDeTai ?? 'Chưa có đề tài' }}<br>
                <strong>👨‍🏫 GVHD:</strong> {{ $nhom->deTai->giangVien->HoTen ?? '' }}
                @if($nhom->deTai->giangVien->HocVi ?? false)
                    <span class="text-muted">({{ $nhom->deTai->giangVien->HocVi }})</span>
                @endif
            </div>
            <div class="col-md-4 text-end">
                <small class="text-muted">Mốc đang mở: <strong class="text-success fs-5">{{ $mocHienTai <= 5 ? $mocHienTai : '✅ Hoàn tất' }}</strong></small>
            </div>
        </div>
    </div>
</div>

<!-- Timeline 5 Mốc -->
<div class="row g-3 mb-4">
    @foreach($mocs as $soMoc => $info)
    @php
        $bc = $baoCaos[$soMoc] ?? null;
        $trangThai = $bc?->TrangThai ?? null;
        $coTheMo = ($soMoc == $mocHienTai) || ($bc && $trangThai === 'Yêu cầu nộp lại');
        $daDat = $trangThai === 'Đạt';
        $choDuyet = $trangThai === 'Chờ duyệt';
        $yeuCauNopLai = $trangThai === 'Yêu cầu nộp lại';
    @endphp
    <div class="col-md-{{ in_array($soMoc, [1,2,3]) ? '4' : '6' }}">
        <div class="card h-100 border-0 shadow-sm {{ $daDat ? 'border-success border-2' : ($coTheMo ? 'border-primary border-2' : '') }}"
             style="{{ $daDat ? 'border-left: 4px solid #28a745 !important;' : ($coTheMo ? 'border-left: 4px solid #0d6efd !important;' : 'opacity: 0.6;') }}">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold mb-0 {{ $daDat ? 'text-success' : ($coTheMo ? 'text-primary' : 'text-muted') }}">
                        @if($daDat) ✅
                        @elseif($choDuyet) ⏳
                        @elseif($yeuCauNopLai) 🔄
                        @elseif($coTheMo) 🔓
                        @else 🔒
                        @endif
                        Mốc {{ $soMoc }}
                    </h6>
                    @if($trangThai)
                        <span class="badge rounded-pill px-2
                            {{ $daDat ? 'bg-success' : ($choDuyet ? 'bg-warning text-dark' : ($yeuCauNopLai ? 'bg-info' : 'bg-secondary')) }}"
                            style="font-size: 0.7rem;">{{ $trangThai }}</span>
                    @endif
                </div>
                <div class="small fw-semibold mb-1">{{ $info['ten'] }}</div>
                <div class="text-muted" style="font-size: 0.78rem;">{{ $info['mo_ta'] }}</div>

                @if($bc)
                    <div class="mt-2 small text-muted">📅 Nộp: {{ $bc->NgayNop }}</div>
                    @if($bc->TenFile)
                        <div class="mt-1">
                            <a href="{{ asset('storage/' . $bc->DuongDanFile) }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill">
                                <i class="fa-solid fa-file-pdf me-1 text-danger"></i>Xem PDF
                            </a>
                        </div>
                    @endif
                    @if($bc->LinkCode)
                        <div class="mt-1">
                            <a href="{{ $bc->LinkCode }}" target="_blank" class="btn btn-outline-dark btn-sm rounded-pill">
                                <i class="fa-brands fa-github me-1"></i>Mở Code
                            </a>
                        </div>
                    @endif
                    @if($bc->tomTat)
                    <div class="mt-2">
                        <button class="btn btn-sm btn-outline-primary rounded-pill w-100"
                            data-bs-toggle="modal" data-bs-target="#aiModal{{ $soMoc }}">
                            <i class="fa-solid fa-robot me-1"></i>Xem Tóm Tắt AI
                        </button>
                    </div>
                    @endif

                    @if($bc->nhanXets && $bc->nhanXets->count())
                    <div class="mt-2 p-2 rounded" style="background: #f8f9fa; font-size: 0.78rem;">
                        <strong>Nhận xét GV:</strong> {{ $bc->nhanXets->last()->NoiDung }}
                    </div>
                    @endif
                @endif

                <!-- Nút Nộp bài nếu được phép -->
                @if($coTheMo && !$daDat && !$choDuyet)
                <div class="mt-3">
                    <button class="btn btn-primary btn-sm rounded-pill w-100 fw-bold"
                        data-bs-toggle="modal" data-bs-target="#nopModal{{ $soMoc }}">
                        <i class="fa-solid fa-upload me-1"></i>Nộp Bài Mốc {{ $soMoc }}
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Modals Nộp Bài -->
@foreach($mocs as $soMoc => $info)
@php
    $bc = $baoCaos[$soMoc] ?? null;
    $trangThai = $bc?->TrangThai ?? null;
    $coTheMo = ($soMoc == $mocHienTai) || ($bc && $trangThai === 'Yêu cầu nộp lại');
    $daDat = $trangThai === 'Đạt';
    $choDuyet = $trangThai === 'Chờ duyệt';
@endphp
@if($coTheMo && !$daDat && !$choDuyet)
<div class="modal fade" id="nopModal{{ $soMoc }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('sinhvien.baocao.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="LanBaoCao" value="{{ $soMoc }}">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-upload me-2"></i>Nộp Báo Cáo — {{ $info['ten'] }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info rounded-3 py-2 mb-3" style="font-size: 0.85rem;">
                        <i class="fa-solid fa-circle-info me-2"></i><strong>Yêu cầu:</strong> {{ $info['mo_ta'] }}
                    </div>

                    <!-- File PDF -->
                    @if(in_array($info['loai'], ['pdf', 'pdf_git']))
                    <div class="mb-3">
                        <label class="form-label fw-bold">File Báo Cáo (PDF) <span class="text-danger">*</span></label>
                        <input type="file" name="FileBaoCao" class="form-control" accept=".pdf" required>
                        <div class="form-text text-muted">Tối đa 20MB. Chỉ chấp nhận định dạng PDF.</div>
                    </div>
                    @endif

                    <!-- Link Git -->
                    @if(in_array($info['loai'], ['git', 'pdf_git']))
                    <div class="mb-3">
                        <label class="form-label fw-bold">Link Repository (GitHub/GitLab) <span class="text-danger">*</span></label>
                        <input type="url" name="LinkCode" class="form-control"
                            placeholder="https://github.com/username/repo-name"
                            {{ in_array($info['loai'], ['git', 'pdf_git']) ? 'required' : '' }}>
                        <div class="form-text text-muted">Đảm bảo repository ở chế độ Public.</div>
                    </div>
                    @endif

                    <!-- Ghi chú -->
                    <div class="mb-1">
                        <label class="form-label fw-bold">Ghi Chú Thêm <span class="text-muted">(tùy chọn)</span></label>
                        <textarea name="NoiDungBaoCao" class="form-control" rows="3"
                            placeholder="Mô tả ngắn về công việc đã thực hiện, khó khăn gặp phải..."></textarea>
                        <div class="form-text text-muted">
                            <i class="fa-solid fa-robot me-1 text-primary"></i>
                            Hệ thống sẽ tự động sinh <strong>Bản Tóm Tắt AI</strong> dựa trên thông tin bài nộp.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-bold px-4">
                        <i class="fa-solid fa-paper-plane me-1"></i>Xác Nhận Nộp Bài
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Modal Tóm Tắt AI -->
@if(isset($baoCaos[$soMoc]) && $baoCaos[$soMoc]->tomTat)
@php $tomTat = $baoCaos[$soMoc]->tomTat; @endphp
<div class="modal fade" id="aiModal{{ $soMoc }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color:white;">
                <h5 class="modal-title">
                    <i class="fa-solid fa-robot me-2"></i>Tóm Tắt AI — Mốc {{ $soMoc }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-end mb-2">
                    <span class="badge rounded-pill px-3"
                        style="background: linear-gradient(135deg, #667eea, #764ba2); font-size: 0.8rem;">
                        <i class="fa-solid fa-gauge me-1"></i>Độ tin cậy: {{ number_format($tomTat->DoTinCayAI, 0) }}%
                    </span>
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="p-3 rounded-3 border-start border-success border-3" style="background: #f0fff4;">
                            <div class="fw-bold text-success mb-1"><i class="fa-solid fa-check-circle me-2"></i>✅ Đã hoàn thành</div>
                            <div style="font-size: 0.88rem;">{{ $tomTat->CongViecDaHoanThanh }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded-3 border-start border-warning border-3" style="background: #fffbeb;">
                            <div class="fw-bold text-warning mb-1"><i class="fa-solid fa-triangle-exclamation me-2"></i>⚠️ Khó khăn</div>
                            <div style="font-size: 0.88rem;">{{ $tomTat->KhoKhan }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded-3 border-start border-primary border-3" style="background: #eff6ff;">
                            <div class="fw-bold text-primary mb-1"><i class="fa-solid fa-calendar-check me-2"></i>📅 Kế hoạch tiếp theo</div>
                            <div style="font-size: 0.88rem;">{{ $tomTat->KeHoachTuanToi }}</div>
                        </div>
                    </div>
                </div>
                <div class="text-muted mt-3" style="font-size: 0.75rem;">
                    <i class="fa-solid fa-clock me-1"></i>Được tạo lúc: {{ \Carbon\Carbon::parse($tomTat->NgayTomTat)->format('d/m/Y H:i') }}
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

@else
<!-- Chưa có nhóm / đề tài -->
<div class="card card-premium">
    <div class="card-body text-center py-5">
        <i class="fa-solid fa-lock fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Chưa thể nộp báo cáo</h5>
        <p class="text-muted mb-4">{{ $error ?? 'Bạn cần có nhóm và đề tài được duyệt trước khi nộp báo cáo.' }}</p>
        <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fa-solid fa-users me-2"></i>Đến Nhóm của tôi
        </a>
    </div>
</div>
@endif

@endsection
