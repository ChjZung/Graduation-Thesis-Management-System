@extends('layouts.admin')
@section('title', 'Phân Công Hướng Dẫn & Phụ Trách Lớp')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 text-primary-custom"><i class="fa-solid fa-users-gear me-2"></i>Phân Công Hướng Dẫn & Phụ Trách Lớp</h4>
        <small class="text-muted">Quản lý phân công Giảng viên cho cả Lớp Hành Chính và Lớp Học Phần (Lớp Tín Chỉ)</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.import.template', 'phancong') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="fa-solid fa-file-arrow-down me-1"></i>File mẫu .xlsx
        </a>
        <button class="btn btn-outline-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fa-solid fa-file-excel me-1"></i>Import Excel
        </button>
        <button class="btn btn-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-plus me-2"></i>Thêm Phân Công
        </button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('import_result'))
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-circle-info me-2"></i>{!! session('import_result') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@php
    $activeTab = request('tab') == 'hp' || request()->has('page_hp') ? 'hp' : 'hc';
@endphp

<div class="card card-premium">
    <div class="card-header bg-white border-bottom p-3">
        <ul class="nav nav-pills card-header-pills" id="phanCongTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'hc' ? 'active' : '' }} rounded-pill px-4 me-2" id="hc-tab" data-bs-toggle="tab" data-bs-target="#hc-pane" type="button">
                    <i class="fa-solid fa-building-user me-2"></i>Lớp Hành Chính ({{ $phancongs->total() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'hp' ? 'active' : '' }} rounded-pill px-4" id="hp-tab" data-bs-toggle="tab" data-bs-target="#hp-pane" type="button">
                    <i class="fa-solid fa-graduation-cap me-2"></i>Lớp Học Phần (Lớp Tín Chỉ) ({{ $lophocphans->total() }})
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content" id="phanCongTabContent">
            {{-- TAB 1: PHÂN CÔNG LỚP HÀNH CHÍNH --}}
            <div class="tab-pane fade {{ $activeTab == 'hc' ? 'show active' : '' }}" id="hc-pane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">#ID</th>
                                <th>Giảng Viên Hướng Dẫn / Chủ Nhiệm</th>
                                <th>Lớp Hành Chính</th>
                                <th>Học Kỳ Phụ Trách</th>
                                <th>Ngày Phân Công</th>
                                <th class="text-end px-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($phancongs as $pc)
                            <tr>
                                <td class="px-4 fw-bold text-muted">#{{ $pc->MaPhanCong }}</td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $pc->giangVien->HoTen ?? 'N/A' }}</span><br>
                                    <small class="text-muted"><i class="fa-solid fa-chalkboard-user me-1"></i>{{ $pc->giangVien->boMon->TenBoMon ?? 'Bộ môn N/A' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border px-3 py-2 rounded-pill fw-bold">
                                        <i class="fa-solid fa-users-rectangle me-1"></i>{{ $pc->lop->TenLop ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ $pc->hocKy->TenHocKy ?? 'N/A' }} ({{ $pc->hocKy->NamHoc ?? '' }})</td>
                                <td>{{ date('d/m/Y', strtotime($pc->NgayPhanCong)) }}</td>
                                <td class="text-end px-4">
                                    <form action="{{ route('phancong.destroy', $pc->MaPhanCong) }}" method="POST" class="d-inline form-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-circle btn-delete" title="Xóa phân công"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                                    Chưa có dữ liệu phân công Lớp Hành chính nào.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($phancongs->hasPages())
                <div class="p-3 border-top">
                    {{ $phancongs->appends(request()->except('page_hc'))->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>

            {{-- TAB 2: PHÂN CÔNG LỚP HỌC PHẦN (LỚP TÍN CHỈ) --}}
            <div class="tab-pane fade {{ $activeTab == 'hp' ? 'show active' : '' }}" id="hp-pane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Mã Lớp HP</th>
                                <th>Tên Lớp Học Phần</th>
                                <th>Môn Học</th>
                                <th>Học Kỳ</th>
                                <th>Giảng Viên Phụ Trách</th>
                                <th>Sĩ Số</th>
                                <th class="text-end px-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lophocphans as $lhp)
                            <tr>
                                <td class="px-4 fw-bold text-primary">#{{ $lhp->MaLopHP }}</td>
                                <td class="fw-bold text-dark">{{ $lhp->TenLopHP }}</td>
                                <td>{{ $lhp->monHoc->TenMon ?? 'N/A' }}</td>
                                <td>{{ $lhp->hocKy->TenHocKy ?? 'N/A' }} ({{ $lhp->hocKy->NamHoc ?? '' }})</td>
                                <td>
                                    @if($lhp->giangVien)
                                        <span class="fw-bold text-dark">{{ $lhp->giangVien->HoTen }}</span><br>
                                        <small class="text-muted"><i class="fa-solid fa-chalkboard-user me-1"></i>{{ $lhp->giangVien->boMon->TenBoMon ?? 'Bộ môn N/A' }}</small>
                                    @else
                                        <span class="badge bg-warning-subtle text-dark border"><i class="fa-solid fa-triangle-exclamation me-1"></i>Chưa phân công</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $lhp->sinhVienLopHocPhans->count() }} / {{ $lhp->SiSoToiDa }} SV
                                    </span>
                                </td>
                                <td class="text-end px-4">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-assign-hp" 
                                            data-lhp-id="{{ $lhp->MaLopHP }}" 
                                            data-lhp-name="{{ $lhp->TenLopHP }}"
                                            data-gv-id="{{ $lhp->MaGV }}">
                                        <i class="fa-solid fa-user-pen me-1"></i>{{ $lhp->MaGV ? 'Đổi GV' : 'Phân công GV' }}
                                    </button>
                                    @if($lhp->MaGV)
                                    <form action="{{ route('admin.phancong.unassign_lhp', $lhp->MaLopHP) }}" method="POST" class="d-inline form-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-circle btn-delete" title="Hủy phân công"><i class="fa-solid fa-user-slash"></i></button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                                    Chưa có Lớp Học Phần nào trong hệ thống.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($lophocphans->hasPages())
                <div class="p-3 border-top">
                    {{ $lophocphans->appends(request()->except('page_hp'))->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm Phân Công -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('phancong.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-user-plus me-2"></i>Thêm / Cập Nhật Phân Công</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Loại Phân Công</label>
                        <div class="d-flex gap-3 bg-light p-2 rounded border">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="LoaiPhanCong" id="loaiHC" value="lop_hanh_chinh" checked>
                                <label class="form-check-input-label fw-semibold cursor-pointer" for="loaiHC">
                                    Lớp Hành Chính
                                </label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="LoaiPhanCong" id="loaiHP" value="lop_hoc_phan">
                                <label class="form-check-input-label fw-semibold cursor-pointer" for="loaiHP">
                                    Lớp Học Phần (Lớp Tín Chỉ)
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Nhóm Lớp Hành Chính --}}
                    <div id="groupLopHC">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Chọn Lớp Hành Chính</label>
                            <select name="MaLop" id="selectMaLop" class="form-select">
                                <option value="">-- Chọn lớp hành chính --</option>
                                @foreach($lops as $lop)
                                <option value="{{ $lop->MaLop }}">{{ $lop->TenLop }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Học Kỳ Phụ Trách</label>
                            <select name="MaHocKy" id="selectMaHocKy" class="form-select">
                                <option value="">-- Chọn học kỳ --</option>
                                @foreach($hockys as $hk)
                                <option value="{{ $hk->MaHocKy }}">{{ $hk->TenHocKy }} ({{ $hk->NamHoc }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Nhóm Lớp Học Phần --}}
                    <div id="groupLopHP" class="d-none">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Chọn Lớp Học Phần</label>
                            <select name="MaLopHP" id="selectMaLopHP" class="form-select">
                                <option value="">-- Chọn lớp học phần --</option>
                                @foreach($lophocphans as $lhp)
                                <option value="{{ $lhp->MaLopHP }}">{{ $lhp->TenLopHP }} ({{ $lhp->monHoc->TenMon ?? '' }} - {{ $lhp->hocKy->TenHocKy ?? '' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Giảng Viên Phụ Trách / Hướng Dẫn</label>
                        <select name="MaGV" id="selectMaGV" class="form-select" required>
                            <option value="">-- Chọn giảng viên --</option>
                            @foreach($giangviens as $gv)
                            <option value="{{ $gv->MaGV }}">{{ $gv->HoTen }} ({{ $gv->boMon->TenBoMon ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4"><i class="fa-solid fa-floppy-disk me-1"></i>Lưu Phân Công</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.phancong.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-file-excel me-2"></i>Import Phân Công Lớp (Hành Chính & Học Phần)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Chọn file Excel (.xlsx, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls" required>
                    </div>
                    <div class="alert alert-light border small text-muted mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i> Tải <a href="{{ route('admin.import.template', 'phancong') }}" class="fw-bold">File mẫu .xlsx</a> hỗ trợ nhập phân công cho cả Lớp Hành Chính và Lớp Học Phần.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4"><i class="fa-solid fa-upload me-1"></i>Tải Lên & Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const loaiHC = document.getElementById('loaiHC');
        const loaiHP = document.getElementById('loaiHP');
        const groupLopHC = document.getElementById('groupLopHC');
        const groupLopHP = document.getElementById('groupLopHP');
        const selectMaLop = document.getElementById('selectMaLop');
        const selectMaHocKy = document.getElementById('selectMaHocKy');
        const selectMaLopHP = document.getElementById('selectMaLopHP');

        function toggleLoai() {
            if (loaiHP.checked) {
                groupLopHC.classList.add('d-none');
                groupLopHP.classList.remove('d-none');
                selectMaLop.removeAttribute('required');
                selectMaHocKy.removeAttribute('required');
                selectMaLopHP.setAttribute('required', 'required');
            } else {
                groupLopHC.classList.remove('d-none');
                groupLopHP.classList.add('d-none');
                selectMaLop.setAttribute('required', 'required');
                selectMaHocKy.setAttribute('required', 'required');
                selectMaLopHP.removeAttribute('required');
            }
        }

        loaiHC.addEventListener('change', toggleLoai);
        loaiHP.addEventListener('change', toggleLoai);
        toggleLoai();

        // Nút phân công nhanh Lớp HP
        document.querySelectorAll('.btn-assign-hp').forEach(button => {
            button.addEventListener('click', function() {
                const lhpId = this.getAttribute('data-lhp-id');
                const gvId = this.getAttribute('data-gv-id');
                
                loaiHP.checked = true;
                toggleLoai();
                
                if (selectMaLopHP) selectMaLopHP.value = lhpId;
                if (selectMaGV) selectMaGV.value = gvId || '';
                
                const addModal = new bootstrap.Modal(document.getElementById('addModal'));
                addModal.show();
            });
        });

        // Confirmation dialog for delete
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                let form = this.closest('form');
                Swal.fire({
                    title: 'Xóa phân công?',
                    text: "Bạn không thể hoàn tác hành động này!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Xóa ngay',
                    cancelButtonText: 'Hủy',
                    background: '#fff',
                    borderRadius: '1rem',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                })
            });
        });
    });
</script>
@endsection
