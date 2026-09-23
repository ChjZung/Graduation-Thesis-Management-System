@extends('layouts.admin')
@section('page_title', 'Chi Tiết Hội Đồng — ' . $hoiDong->TenHoiDong)
@section('content')



<div class="row g-4">
    <!-- Cột trái: Thông tin Hội đồng & Thành viên -->
    <div class="col-lg-5">
        <!-- Thông tin cơ bản -->
        <div class="card card-premium shadow-sm border-0 mb-4">
            <div class="card-header-premium d-flex justify-content-between align-items-center py-3">
                <span class="fw-bold"><i class="fa-solid fa-landmark me-2 text-primary"></i>{{ $hoiDong->TenHoiDong }}</span>
                @php
                    $badgeClass = match($hoiDong->TrangThai) {
                        'Đang diễn ra' => 'bg-success',
                        'Đã kết thúc'  => 'bg-secondary',
                        default        => 'bg-warning text-dark',
                    };
                @endphp
                <span class="badge {{ $badgeClass }} rounded-pill px-3 py-1">{{ $hoiDong->TrangThai }}</span>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-3">
                    <tr>
                        <td class="text-muted fw-semibold" width="35%">Mã HĐ:</td>
                        <td><span class="badge bg-secondary font-monospace">{{ $hoiDong->MaHoiDong }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Thời gian:</td>
                        <td>
                            <div><i class="fa-regular fa-clock text-success me-1"></i>{{ \Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('d/m/Y H:i') }}</div>
                            <div class="small text-muted"><i class="fa-solid fa-arrow-right-long text-muted me-1"></i>{{ \Carbon\Carbon::parse($hoiDong->ThoiGianKetThuc)->format('d/m/Y H:i') }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Địa điểm:</td>
                        <td><strong>{{ $hoiDong->DiaDiem ?? 'Chưa xác định' }}</strong></td>
                    </tr>
                    @if($hoiDong->GhiChu)
                    <tr>
                        <td class="text-muted fw-semibold">Ghi chú:</td>
                        <td class="small">{{ $hoiDong->GhiChu }}</td>
                    </tr>
                    @endif
                </table>

                <!-- Cập nhật trạng thái Hội đồng -->
                <form action="{{ route('admin.hoidong.updateTrangThai', $hoiDong->MaHoiDong) }}" method="POST" class="d-flex gap-2 pt-2 border-top">
                    @csrf
                    <select name="TrangThai" class="form-select form-select-sm">
                        <option value="Chưa diễn ra" {{ $hoiDong->TrangThai === 'Chưa diễn ra' ? 'selected' : '' }}>Chưa diễn ra</option>
                        <option value="Đang diễn ra" {{ $hoiDong->TrangThai === 'Đang diễn ra' ? 'selected' : '' }}>Đang diễn ra</option>
                        <option value="Đã kết thúc" {{ $hoiDong->TrangThai === 'Đã kết thúc' ? 'selected' : '' }}>Đã kết thúc</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 flex-shrink-0">Cập nhật</button>
                </form>
            </div>
        </div>

        <!-- Thành viên Hội đồng -->
        <div class="card card-premium shadow-sm border-0">
            <div class="card-header-premium py-3">
                <i class="fa-solid fa-users me-2 text-primary"></i>Thành Viên Hội Đồng ({{ $hoiDong->thanhViens->count() }})
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Giảng Viên</th>
                                <th class="text-center" width="35%">Vai Trò</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hoiDong->thanhViens as $tv)
                            <tr>
                                <td class="py-2">
                                    <div class="fw-bold">{{ $tv->giangVien->HoTen ?? '—' }}</div>
                                    <div class="small text-muted">{{ $tv->giangVien->HocVi ?? '' }} | {{ $tv->giangVien->boMon->TenBoMon ?? '' }}</div>
                                </td>
                                <td class="text-center">
                                    @php
                                        $vc = match($tv->VaiTro) {
                                            'Chủ tịch' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                            'Thư ký'   => 'bg-info-subtle text-info border border-info-subtle',
                                            'Phản biện'=> 'bg-warning-subtle text-warning border border-warning-subtle',
                                            default    => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                        };
                                    @endphp
                                    <span class="badge {{ $vc }} rounded-pill px-3 py-1">{{ $tv->VaiTro }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">Chưa có thành viên nào.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Cột phải: Danh sách nhóm sinh viên bảo vệ -->
    <div class="col-lg-7">
        <!-- Card: Thêm nhóm bảo vệ mới vào Hội đồng -->
        @php
            $nhomChuaPhanCong = $nhomDuDieuKien->filter(function($h) use ($hoiDong) {
                return $h->MaHoiDong !== $hoiDong->MaHoiDong;
            });
        @endphp

        @if($nhomChuaPhanCong->isNotEmpty())
        <div class="card card-premium shadow-sm border-0 mb-4 bg-light-subtle">
            <div class="card-header-premium py-2 bg-primary-subtle text-primary">
                <i class="fa-solid fa-user-plus me-2"></i>Thêm Nhóm Bảo Vệ Vào Hội Đồng Này
            </div>
            <div class="card-body p-3">
                <form action="{{ route('admin.hoidong.phanCongNhom', $hoiDong->MaHoiDong) }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Chọn Nhóm (Đủ điều kiện)</label>
                            <select name="MaNhom" class="form-select form-select-sm" required>
                                <option value="">— Chọn nhóm sinh viên —</option>
                                @foreach($nhomChuaPhanCong as $item)
                                <option value="{{ $item->MaNhom }}">
                                    {{ $item->nhom->TenNhom ?? $item->MaNhom }} — {{ Str::limit($item->nhom->deTai->TenDeTai ?? '', 35) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Giảng Viên Phản Biện</label>
                            <select name="MaGVPhanBien" class="form-select form-select-sm">
                                <option value="">— Chọn GV Phản biện —</option>
                                @foreach($giangViens as $gv)
                                <option value="{{ $gv->MaGV }}">{{ $gv->HoTen }} ({{ $gv->HocVi ?? '' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Thời gian bảo vệ</label>
                            <input type="datetime-local" name="ThoiGianBaoVe" class="form-control form-control-sm"
                                value="{{ $hoiDong->ThoiGianBatDau ? \Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('Y-m-d\TH:i') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Phòng bảo vệ</label>
                            <input type="text" name="PhongBaoVe" class="form-control form-control-sm" placeholder="VD: Phòng A101" value="{{ $hoiDong->DiaDiem }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100 fw-bold">
                                <i class="fa-solid fa-plus me-1"></i>Thêm Nhóm
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- Card: Các nhóm đang trong Hội đồng -->
        <div class="card card-premium shadow-sm border-0">
            <div class="card-header-premium d-flex justify-content-between align-items-center py-3">
                <span class="fw-bold"><i class="fa-solid fa-folder-open me-2 text-primary"></i>Danh Sách Nhóm Bảo Vệ Trong Hội Đồng ({{ $hoiDong->hoSoBaoVes->count() }})</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="35%">Nhóm / Đề Tài</th>
                                <th width="15%" class="text-center">Turnitin</th>
                                <th width="35%">GV Phản Biện & Lịch</th>
                                <th width="15%" class="text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hoiDong->hoSoBaoVes as $hoSo)
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary-custom">{{ $hoSo->nhom->TenNhom ?? '' }}</div>
                                    <div class="small text-dark fw-semibold">{{ Str::limit($hoSo->nhom->deTai->TenDeTai ?? '', 40) }}</div>
                                    <div class="small text-muted">Trưởng nhóm: {{ $hoSo->nhom->truongNhom->HoTen ?? '—' }}</div>
                                </td>
                                <td class="text-center">
                                    @php $pct = (float)$hoSo->TyLeTrungLap; @endphp
                                    <span class="fw-bold fs-6 {{ $pct <= 20 ? 'text-success' : 'text-danger' }}">{{ number_format($pct, 2) }}%</span>
                                    @if($hoSo->MinhChungDaoVan)
                                    <div>
                                        <a href="{{ asset('storage/' . $hoSo->MinhChungDaoVan) }}" target="_blank" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-0" style="font-size: 0.68rem;">
                                            Minh chứng
                                        </a>
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.hoidong.phanCongNhom', $hoiDong->MaHoiDong) }}" method="POST" class="d-flex flex-column gap-1">
                                        @csrf
                                        <input type="hidden" name="MaNhom" value="{{ $hoSo->MaNhom }}">
                                        <select name="MaGVPhanBien" class="form-select form-select-sm" style="font-size: 0.8rem;">
                                            <option value="">— GVPB: Chưa chọn —</option>
                                            @foreach($giangViens as $gv)
                                            <option value="{{ $gv->MaGV }}" {{ $hoSo->MaGVPhanBien === $gv->MaGV ? 'selected' : '' }}>
                                                {{ $gv->HoTen }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <div class="d-flex gap-1">
                                            <input type="text" name="PhongBaoVe" class="form-control form-control-sm" placeholder="Phòng" value="{{ $hoSo->PhongBaoVe }}" style="font-size: 0.75rem;">
                                            <button type="submit" class="btn btn-xs btn-primary rounded-pill flex-shrink-0" style="font-size: 0.75rem; padding: 2px 10px;">Lưu</button>
                                        </div>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.hoidong.huyPhanCongNhom', [$hoiDong->MaHoiDong, $hoSo->MaHoSo]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1" style="font-size: 0.75rem;" onclick="return confirm('Bạn có chắc chắn muốn hủy phân công nhóm này khỏi hội đồng?')">
                                            <i class="fa-solid fa-trash-can me-1"></i>Hủy
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <i class="fa-solid fa-folder-open fa-2x text-secondary mb-2 d-block"></i>
                                    Chưa có nhóm sinh viên nào được phân công vào Hội đồng này.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('admin.hoidong.index') }}" class="btn btn-light rounded-pill px-4 shadow-sm">
        <i class="fa-solid fa-arrow-left me-2"></i>Quay lại danh sách Hội đồng
    </a>
</div>
@endsection

