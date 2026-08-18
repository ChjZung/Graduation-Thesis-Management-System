@extends('layouts.giangvien')
@section('page_title', 'Chấm Điểm Hội Đồng')
@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3">
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

@if($hoiDongs->isEmpty())
<div class="card card-premium">
    <div class="card-body text-center py-5">
        <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Bạn chưa được phân công vào Hội đồng nào</h5>
        <p class="text-muted">Khi Giáo vụ thành lập Hội đồng và thêm bạn vào, danh sách sẽ hiện ở đây.</p>
    </div>
</div>
@endif

@foreach($hoiDongs as $hoiDong)
<div class="card card-premium mb-4">
    <!-- Header HĐ -->
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <div>
            <i class="fa-solid fa-landmark me-2 text-primary"></i>
            <strong>{{ $hoiDong->TenHoiDong }}</strong>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <small class="text-muted">
                <i class="fa-solid fa-clock me-1"></i>{{ \Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('d/m/Y H:i') }}
                @if($hoiDong->DiaDiem) | 📍{{ $hoiDong->DiaDiem }} @endif
            </small>
            @php
                $badgeClass = match($hoiDong->TrangThai) {
                    'Đang diễn ra' => 'bg-success',
                    'Đã kết thúc'  => 'bg-secondary',
                    default        => 'bg-warning text-dark',
                };
            @endphp
            <span class="badge {{ $badgeClass }} rounded-pill">{{ $hoiDong->TrangThai }}</span>
        </div>
    </div>

    <!-- Vai trò của GV trong HĐ này -->
    @php
        $tvHD = $hoiDong->thanhViens->firstWhere('MaGV', $giangVien->MaGV);
        $vaiTro = $tvHD?->VaiTro ?? 'Thành viên';
    @endphp
    <div class="px-3 pt-2">
        <small class="text-muted">Vai trò của bạn: <strong class="text-primary">{{ $vaiTro }}</strong></small>
    </div>

    <!-- Danh sách nhóm bảo vệ trong HĐ này -->
    @foreach($hoiDong->hoSoBaoVes as $hoSo)
    @php
        $thanhViensNhom = $hoSo->nhom->thanhViens ?? collect();
    @endphp
    <div class="card-body">
        <div class="fw-bold mb-2 text-primary-custom">
            <i class="fa-solid fa-users me-2"></i>{{ $hoSo->nhom->TenNhom ?? '' }}
            <span class="text-muted fw-normal small ms-2">— {{ $hoSo->nhom->deTai->TenDeTai ?? '' }}</span>
        </div>

        <form action="{{ route('giangvien.chamdiem.store') }}" method="POST">
            @csrf
            <input type="hidden" name="MaHoiDong" value="{{ $hoiDong->MaHoiDong }}">

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-2">
                    <thead>
                        <tr>
                            <th width="30%">Sinh Viên</th>
                            <th width="15%" class="text-center">Điểm (0–10) <span class="text-danger">*</span></th>
                            <th width="40%">Nhận Xét</th>
                            <th width="15%" class="text-center">Đã Chấm</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($thanhViensNhom as $idx => $tv)
                        @php
                            $key = $hoiDong->MaHoiDong . '_' . $tv->MaSV;
                            $daDat = $diemDaCham[$key] ?? null;
                        @endphp
                        <tr>
                            <td>
                                <input type="hidden" name="diems[{{ $idx }}][MaSV]" value="{{ $tv->MaSV }}">
                                <div class="fw-bold">{{ $tv->sinhVien->HoTen ?? $tv->MaSV }}</div>
                                <div class="small text-muted">MSSV: {{ $tv->MaSV }}
                                    @if($tv->VaiTro === 'Trưởng nhóm') 👑 @endif
                                </div>
                            </td>
                            <td class="text-center">
                                <input type="number" name="diems[{{ $idx }}][Diem]" class="form-control form-control-sm text-center"
                                    step="0.5" min="0" max="10"
                                    value="{{ $daDat?->Diem ?? '' }}"
                                    placeholder="0–10" required>
                            </td>
                            <td>
                                <input type="text" name="diems[{{ $idx }}][NhanXet]" class="form-control form-control-sm"
                                    value="{{ $daDat?->NhanXet ?? '' }}"
                                    placeholder="Nhận xét ngắn gọn...">
                            </td>
                            <td class="text-center">
                                @if($daDat)
                                    <span class="badge bg-success rounded-pill">✅ {{ $daDat->Diem }}</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill">Chưa</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Lưu Điểm Chấm
                </button>
                <small class="text-muted my-auto">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Điểm tổng kết = GVHD×30% + Phản biện×30% + HĐ×40%
                </small>
            </div>
        </form>
    </div>

    @if(!$loop->last) <hr class="mx-3"> @endif
    @endforeach
</div>
@endforeach

@endsection
