@extends('layouts.sinhvien')
@section('page_title', 'Nhóm Đồ Án Của Tôi')
@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
    <strong class="d-block mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Thông báo:</strong>
    <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- HEADER BANNER & ACTION -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 text-primary fw-bold"><i class="fa-solid fa-users-gear me-2"></i>Nhóm Đồ Án Của Tôi</h4>
        <p class="text-muted small mb-0">Quản lý nhóm, xem tiến độ, nộp sản phẩm và theo dõi nhận xét giảng viên</p>
    </div>
    <button type="button" class="btn btn-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#createGroupModal">
        <i class="fa-solid fa-plus me-2"></i>Tạo Nhóm Mới
    </button>
</div>

<!-- SECTION 1: LỜI MỜI THAM GIA NHÓM ĐANG CHỜ -->
@if(isset($loiMois) && $loiMois->count() > 0)
<div class="card border-warning mb-4 shadow-sm">
    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center fw-bold">
        <span><i class="fa-solid fa-envelope-open-text me-2"></i>Lời Mời Tham Gia Nhóm Đang Chờ ({{ $loiMois->count() }})</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tên Nhóm</th>
                        <th>Môn Học</th>
                        <th>Người Mời</th>
                        <th>Ngày Mời</th>
                        <th class="text-end px-4">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loiMois as $lm)
                    <tr>
                        <td class="fw-bold text-primary">{{ $lm->nhomDoAn->TenNhom ?? 'N/A' }}</td>
                        <td><span class="badge bg-info text-dark">{{ $lm->nhomDoAn->monHoc->TenMon ?? 'N/A' }}</span></td>
                        <td>{{ $lm->sinhVienMoi->HoTen ?? 'N/A' }}</td>
                        <td class="small text-muted">{{ date('d/m/Y H:i', strtotime($lm->NgayMoi)) }}</td>
                        <td class="text-end px-4">
                            <form action="{{ route('sinhvien.nhom.xacNhan', $lm->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 me-1">
                                    <i class="fa-solid fa-check me-1"></i>Đồng ý
                                </button>
                            </form>
                            <form action="{{ route('sinhvien.nhom.tuChoi', $lm->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                    <i class="fa-solid fa-xmark me-1"></i>Từ chối
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- SECTION 2: DANH SÁCH NHÓM CỦA SINH VIÊN -->
@if(isset($nhoms) && $nhoms->count() > 0)
    @foreach($nhoms as $nhom)
    <div class="card card-premium shadow-sm mb-4">
        <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 fw-bold text-primary">
                    <i class="fa-solid fa-users me-2"></i>{{ $nhom->TenNhom }}
                    <span class="badge bg-{{ $nhom->TrangThai == 'Đã có điểm' ? 'success' : ($nhom->TrangThai == 'Đang hoạt động' || $nhom->TrangThai == 'Đã nộp sản phẩm' ? 'info' : 'secondary') }} fs-7 ms-2">
                        <i class="fa-solid {{ $nhom->TrangThai == 'Đã có điểm' ? 'fa-award' : 'fa-circle-check' }} me-1"></i>{{ $nhom->TrangThai }}
                    </span>
                </h5>
                <div class="text-muted small">
                    @if($nhom->lopHocPhan)
                        <span class="badge bg-primary-subtle text-primary border border-primary me-2"><i class="fa-solid fa-graduation-cap me-1"></i>Lớp HP: {{ $nhom->lopHocPhan->TenLopHP }}</span>
                    @endif
                    <i class="fa-solid fa-book me-1"></i>Môn: <strong>{{ $nhom->monHoc->TenMon ?? '—' }}</strong> &nbsp;|&nbsp;
                    <i class="fa-solid fa-calendar me-1"></i>Học kỳ: <strong>{{ $nhom->hocKy->TenHocKy ?? '—' }}</strong>
                </div>
            </div>
            
            @if($nhom->TruongNhom == $sinhVien->MaSV)
                <span class="badge bg-warning text-dark px-3 py-2 fs-7"><i class="fa-solid fa-crown me-1"></i>Bạn là Trưởng Nhóm</span>
            @else
                <span class="badge bg-light text-secondary border px-3 py-2 fs-7"><i class="fa-solid fa-user me-1"></i>Thành viên</span>
            @endif
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                {{-- Cột trái: Thành viên & Đề tài --}}
                <div class="col-md-7">
                    <h6 class="fw-bold mb-3"><i class="fa-solid fa-user-group me-2 text-primary"></i>Danh Sách Thành Viên ({{ $nhom->thanhVienNhoms->count() }}/5)</h6>
                    <div class="table-responsive mb-4 border rounded">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>MSSV</th>
                                    <th>Họ và Tên</th>
                                    <th>Lớp</th>
                                    <th>Vai Trò</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nhom->thanhVienNhoms as $tv)
                                <tr>
                                    <td class="fw-semibold text-primary">{{ $tv->sinhVien->taiKhoan->TenDangNhap ?? '—' }}</td>
                                    <td>{{ $tv->sinhVien->HoTen ?? '—' }}</td>
                                    <td class="small text-muted">{{ $tv->sinhVien->lop->TenLop ?? '—' }}</td>
                                    <td>
                                        @if($tv->VaiTro == 'Trưởng nhóm' || $nhom->TruongNhom == $tv->MaSV)
                                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-crown me-1"></i>Trưởng nhóm</span>
                                        @else
                                            <span class="badge bg-secondary">Thành viên</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Thông tin Đề Tài đăng ký --}}
                    <div class="p-3 bg-light rounded border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small fw-bold"><i class="fa-solid fa-book-bookmark me-1 text-info"></i>ĐỀ TÀI ĐỒ ÁN:</span>
                            @if($nhom->dangKyDeTai)
                                <span class="badge bg-{{ $nhom->dangKyDeTai->TrangThai == 'Đã duyệt' ? 'success' : ($nhom->dangKyDeTai->TrangThai == 'Từ chối' ? 'danger' : 'warning text-dark') }}">
                                    {{ $nhom->dangKyDeTai->TrangThai }}
                                </span>
                            @endif
                        </div>
                        @if($nhom->dangKyDeTai && $nhom->dangKyDeTai->deTai)
                            <h6 class="fw-bold text-primary mb-1">{{ $nhom->dangKyDeTai->deTai->TenDeTai }}</h6>
                            <div class="small text-muted">
                                Giảng viên hướng dẫn: <strong>{{ $nhom->dangKyDeTai->deTai->giangVien->HoTen ?? '—' }}</strong>
                            </div>
                        @else
                            <p class="text-muted small mb-0">Chưa đăng ký đề tài nào. <a href="{{ route('sinhvien.dangky.index') }}" class="fw-bold text-primary">Đăng ký ngay</a></p>
                        @endif
                    </div>

                    {{-- Mời thành viên mới (Chỉ dành cho Trưởng nhóm) --}}
                    @if($nhom->TruongNhom == $sinhVien->MaSV && $nhom->thanhVienNhoms->count() < 5)
                    <div class="p-3 bg-white border rounded shadow-sm">
                        <h6 class="fw-bold small text-success mb-2"><i class="fa-solid fa-user-plus me-1"></i>Mời thành viên mới vào nhóm (Cùng Lớp Học Phần)</h6>
                        <form action="{{ route('sinhvien.nhom.moiThanhVien') }}" method="POST" class="row g-2">
                            @csrf
                            <input type="hidden" name="MaNhom" value="{{ $nhom->MaNhom }}">
                            <div class="col position-relative">
                                <input type="text" id="inputSearchSV_{{ $nhom->MaNhom }}" name="TenDangNhap_Them" class="form-control form-control-sm" placeholder="Nhập MSSV (vd: sv02) hoặc Họ tên..." autocomplete="off" required>
                                <div id="autocompleteResults_{{ $nhom->MaNhom }}" class="list-group position-absolute w-100 shadow d-none" style="z-index: 1000; max-height: 180px; overflow-y: auto;"></div>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-success px-3">
                                    <i class="fa-solid fa-paper-plane me-1"></i>Gửi lời mời
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>

                {{-- Cột phải: Tiến độ, Sản phẩm & Điểm số --}}
                <div class="col-md-5">
                    {{-- Điểm số tổng kết nếu có --}}
                    @if($nhom->chamDiem)
                    <div class="card border-0 shadow-sm mb-3 bg-success-subtle border-start border-success border-4 rounded-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-success mb-0">
                                    <i class="fa-solid fa-award me-1"></i>KẾT QUẢ ĐIỂM SỐ ĐỒ ÁN
                                </h6>
                                <span class="badge bg-success fs-6 fw-bold">Điểm tổng: {{ number_format($nhom->chamDiem->DiemTong ?? 0, 1) }} / 10</span>
                            </div>
                            <div class="row g-2 text-center my-2">
                                <div class="col-6">
                                    <div class="bg-white p-2 rounded border">
                                        <small class="text-muted d-block">Điểm Báo Cáo</small>
                                        <strong class="text-dark fs-5">{{ number_format($nhom->chamDiem->DiemBaoCao ?? 0, 1) }}</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white p-2 rounded border">
                                        <small class="text-muted d-block">Điểm Bảo Vệ</small>
                                        <strong class="text-dark fs-5">{{ number_format($nhom->chamDiem->DiemBaoVe ?? 0, 1) }}</strong>
                                    </div>
                                </div>
                            </div>
                            @if($nhom->chamDiem->NhanXet)
                                <div class="small text-muted mt-2 border-top pt-2">
                                    <i class="fa-solid fa-comment-dots me-1 text-success"></i><strong>Nhận xét:</strong> 
                                    <i>"{{ $nhom->chamDiem->NhanXet }}"</i>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Danh sách báo cáo tiến độ --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0"><i class="fa-solid fa-list-check me-1 text-info"></i>Báo Cáo Tiến Độ ({{ $nhom->baoCaos->count() }})</h6>
                            <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-xs btn-outline-primary py-0 px-2 small">Nộp báo cáo</a>
                        </div>
                        @if($nhom->baoCaos->isEmpty())
                            <p class="text-muted small border rounded p-2 text-center mb-0">Chưa có báo cáo tiến độ nào.</p>
                        @else
                            <div class="list-group list-group-flush border rounded" style="max-height: 180px; overflow-y: auto;">
                                @foreach($nhom->baoCaos as $bc)
                                <div class="list-group-item p-2 small">
                                    <div class="d-flex justify-content-between fw-semibold">
                                        <span>Báo cáo Lần {{ $bc->LanBaoCao }}</span>
                                        <span class="text-muted">{{ \Carbon\Carbon::parse($bc->NgayNop)->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="text-muted text-truncate">{{ $bc->NoiDung }}</div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Danh sách sản phẩm đồ án --}}
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0"><i class="fa-solid fa-box-open me-1 text-warning"></i>Sản Phẩm Nộp ({{ $nhom->sanPhams->count() }})</h6>
                            <a href="{{ route('sinhvien.sanpham.index') }}" class="btn btn-xs btn-outline-warning text-dark py-0 px-2 small">Nộp sản phẩm</a>
                        </div>
                        @if($nhom->sanPhams->isEmpty())
                            <p class="text-muted small border rounded p-2 text-center mb-0">Chưa nộp sản phẩm đồ án.</p>
                        @else
                            <div class="list-group list-group-flush border rounded">
                                @foreach($nhom->sanPhams as $sp)
                                <div class="list-group-item p-2 small d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold text-truncate" style="max-width: 200px;">{{ $sp->TenSanPham }}</span>
                                    <span class="badge bg-success">Đã nộp {{ \Carbon\Carbon::parse($sp->NgayNop)->format('d/m/Y') }}</span>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@else
    <div class="card card-premium shadow-sm text-center py-5">
        <div class="card-body">
            <i class="fa-solid fa-users-slash fa-3x text-muted opacity-25 mb-3"></i>
            <h5 class="fw-bold text-secondary">Bạn chưa tham gia nhóm đồ án nào</h5>
            <p class="text-muted mb-4">Hãy nhấn nút bên dưới để tạo nhóm mới cho môn học đồ án thuộc học kỳ hiện tại!</p>
            <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                <i class="fa-solid fa-plus me-2"></i>Tạo Nhóm Ngay
            </button>
        </div>
    </div>
@endif

<!-- MODAL TẠO NHÓM MỚI -->
<div class="modal fade" id="createGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('sinhvien.nhom.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-users me-2"></i>Tạo Nhóm Đồ Án Mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 p-3 bg-light rounded border border-primary-subtle">
                        <label class="form-label fw-bold text-primary"><i class="fa-solid fa-graduation-cap me-1"></i>Lớp Học Phần <span class="badge bg-primary text-white ms-1">Mới</span></label>
                        <select name="MaLopHP" id="select_MaLopHP" class="form-select border-primary" onchange="onLopHocPhanChange(this)">
                            <option value="">-- Chọn Lớp Học Phần (Khuyên dùng) --</option>
                            @if(isset($allLopHocPhans))
                                @foreach($allLopHocPhans as $lhp)
                                    <option value="{{ $lhp->MaLopHP }}" data-mamon="{{ $lhp->MaMon }}" data-mahocky="{{ $lhp->MaHocKy }}">
                                        {{ $lhp->TenLopHP }} ({{ $lhp->monHoc->TenMon ?? 'Môn' }} - {{ $lhp->hocKy->TenHocKy ?? 'Kỳ' }} - GV: {{ $lhp->giangVien->HoTen ?? 'Chưa gán' }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <div class="form-text text-muted small">Chọn Lớp Học Phần để tự động gán đúng Môn học, Học kỳ và Giảng viên hướng dẫn.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Học Kỳ <span class="text-danger">*</span></label>
                        <select name="MaHocKy" id="select_MaHocKy" class="form-select" required>
                            @foreach($hockys as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ $hk->MaHocKy == $currentHocKyId ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Môn Học Đồ Án <span class="text-danger">*</span></label>
                        @if(isset($availableMonHocs) && $availableMonHocs->isNotEmpty())
                            <select name="MaMon" id="select_MaMon" class="form-select" required>
                                <option value="">-- Chọn Môn Học --</option>
                                @foreach($availableMonHocs as $mh)
                                    <option value="{{ $mh->MaMon }}">{{ $mh->TenMon }} ({{ $mh->SoTinChi }} tín chỉ)</option>
                                @endforeach
                            </select>
                        @else
                            <div class="alert alert-warning mb-0 small">
                                <i class="fa-solid fa-circle-info me-1"></i>Bạn đã tham gia nhóm đồ án cho tất cả các môn học trong học kỳ hiện tại!
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên Nhóm <span class="text-danger">*</span></label>
                        <input type="text" name="TenNhom" class="form-control" placeholder="Ví dụ: Nhóm Web App 01" required max="100">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Hủy</button>
                    @if(isset($availableMonHocs) && $availableMonHocs->isNotEmpty())
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fa-solid fa-check me-1"></i>Tạo Nhóm
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- AUTOCOMPLETE JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(isset($nhoms))
    @foreach($nhoms as $n)
    (function() {
        const input = document.getElementById('inputSearchSV_{{ $n->MaNhom }}');
        const results = document.getElementById('autocompleteResults_{{ $n->MaNhom }}');
        if (!input || !results) return;

        input.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length < 1) {
                results.classList.add('d-none');
                return;
            }

            fetch(`{{ route('sinhvien.nhom.searchSV') }}?q=${encodeURIComponent(query)}&maNhom={{ $n->MaNhom }}`)
                .then(res => res.json())
                .then(data => {
                    results.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const a = document.createElement('a');
                            a.href = '#';
                            a.className = 'list-group-item list-group-item-action py-2 small';
                            a.innerHTML = `<strong>${item.name}</strong> <span class="text-muted">(${item.mssv})</span>`;
                            a.addEventListener('click', function(e) {
                                e.preventDefault();
                                input.value = item.mssv;
                                results.classList.add('d-none');
                            });
                            results.appendChild(a);
                        });
                        results.classList.remove('d-none');
                    } else {
                        results.innerHTML = '<div class="list-group-item text-muted small py-2">Không tìm thấy SV cùng lớp phù hợp...</div>';
                        results.classList.remove('d-none');
                    }
                });
        });

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !results.contains(e.target)) {
                results.classList.add('d-none');
            }
        });
    })();
    @endforeach
    @endif
});

function onLopHocPhanChange(selectEl) {
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    const maMon = selectedOption.getAttribute('data-mamon');
    const maHocKy = selectedOption.getAttribute('data-mahocky');

    if (maMon) {
        const monSelect = document.getElementById('select_MaMon');
        if (monSelect) monSelect.value = maMon;
    }
    if (maHocKy) {
        const hkSelect = document.getElementById('select_MaHocKy');
        if (hkSelect) hkSelect.value = maHocKy;
    }
}
</script>
@endsection