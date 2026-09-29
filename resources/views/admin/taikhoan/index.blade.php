@extends('layouts.admin')

@section('page_title', 'Quản lý tài khoản')

@push('styles')
<style>
    /* Academic Theme Tabs */
    .account-tabs {
        border-bottom: 2px solid #e2e8f0;
        gap: 0.5rem;
    }
    .account-tab-btn {
        border: none;
        background: transparent;
        color: #64748b;
        font-weight: 600;
        font-size: 0.95rem;
        padding: 0.75rem 1.25rem;
        border-radius: 8px 8px 0 0;
        position: relative;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .account-tab-btn:hover {
        color: #0f172a;
        background: #f1f5f9;
    }
    .account-tab-btn.active {
        color: #002855;
        background: #ffffff;
        border-bottom: 3px solid #002855;
        font-weight: 700;
    }
    .account-tab-btn .badge-pill-count {
        font-size: 0.75rem;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        background: #e2e8f0;
        color: #475569;
    }
    .account-tab-btn.active .badge-pill-count {
        background: #002855;
        color: #ffffff;
    }
    .badge-code {
        font-family: 'Consolas', 'Courier New', monospace;
        font-size: 0.8rem;
        padding: 3px 8px;
        border-radius: 6px;
        background: #f8fafc;
        color: #0f172a;
        font-weight: 600;
        border: 1px solid #cbd5e1;
    }
    .form-preview-username {
        background: #f8fafc;
        border: 1px dashed #0284c7;
        color: #0284c7;
        font-family: 'Consolas', monospace;
        font-weight: 700;
        font-size: 0.95rem;
        padding: 8px 12px;
        border-radius: 6px;
    }
    .filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
    }
    .table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        border-bottom: 1px solid #e2e8f0;
    }
    .btn-create-canbo {
        background: #002855;
        color: #ffffff;
        font-weight: 600;
        border-radius: 8px;
        padding: 0.5rem 1.25rem;
        box-shadow: 0 2px 4px rgba(0, 40, 85, 0.15);
        transition: all 0.2s ease;
    }
    .btn-create-canbo:hover {
        background: #001f42;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 40, 85, 0.2);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    <!-- ═══ 1. HEADER ═══ -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-users-gear text-primary"></i> Quản lý tài khoản
            </h4>
            <div class="text-muted small">
                Quản lý tài khoản người dùng trong hệ thống.
            </div>
        </div>
        <div>
            <!-- Nút duy nhất góc phải: + Thêm tài khoản cán bộ -->
            <button type="button" class="btn btn-create-canbo d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createCanBoModal">
                <i class="fa-solid fa-plus"></i>
                <span>Thêm tài khoản cán bộ</span>
            </button>
        </div>
    </div>

    <!-- ═══ 2. CHỌN LOẠI TÀI KHOẢN (4 TABS) ═══ -->
    <div class="d-flex account-tabs mb-4 overflow-auto">
        <a href="{{ route('admin.taikhoan.index', ['tab' => 'sinhvien']) }}" 
           class="account-tab-btn {{ $activeTab === 'sinhvien' ? 'active' : '' }}">
            <i class="fa-solid fa-user-graduate"></i>
            <span>Sinh viên</span>
            <span class="badge-pill-count">{{ number_format($counts['sinhvien']) }}</span>
        </a>

        <a href="{{ route('admin.taikhoan.index', ['tab' => 'giangvien']) }}" 
           class="account-tab-btn {{ $activeTab === 'giangvien' ? 'active' : '' }}">
            <i class="fa-solid fa-chalkboard-user"></i>
            <span>Giảng viên</span>
            <span class="badge-pill-count">{{ number_format($counts['giangvien']) }}</span>
        </a>

        <a href="{{ route('admin.taikhoan.index', ['tab' => 'truongbomon']) }}" 
           class="account-tab-btn {{ $activeTab === 'truongbomon' ? 'active' : '' }}">
            <i class="fa-solid fa-user-tie"></i>
            <span>Trưởng bộ môn</span>
            <span class="badge-pill-count">{{ number_format($counts['truongbomon']) }}</span>
        </a>

        <a href="{{ route('admin.taikhoan.index', ['tab' => 'truongkhoa']) }}" 
           class="account-tab-btn {{ $activeTab === 'truongkhoa' ? 'active' : '' }}">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>Trưởng khoa</span>
            <span class="badge-pill-count">{{ number_format($counts['truongkhoa']) }}</span>
        </a>
    </div>

    <!-- ═══ THANH TÌM KIẾM & BỘ LỌC THEO TAB ═══ -->
    <div class="card filter-card mb-4 shadow-xs">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.taikhoan.index') }}" id="filterForm">
                <input type="hidden" name="tab" value="{{ $activeTab }}">

                <div class="row g-2 align-items-end">
                    <!-- Ô tìm kiếm (chung cho mọi tab) -->
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Tìm kiếm từ khóa</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" 
                                   placeholder="{{ $activeTab === 'sinhvien' ? 'Mã SV, họ tên, username...' : 'Mã cán bộ, họ tên, username...' }}" 
                                   value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- ── TAB 1: SINH VIÊN FILTERS ── -->
                    @if($activeTab === 'sinhvien')
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-bold text-muted mb-1">Khoa</label>
                            <select name="ma_khoa" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- Tất cả Khoa --</option>
                                @foreach($khoas as $k)
                                    <option value="{{ $k->MaKhoa }}" {{ request('ma_khoa') == $k->MaKhoa ? 'selected' : '' }}>
                                        {{ $k->TenKhoa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-bold text-muted mb-1">Ngành</label>
                            <select name="ma_nganh" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- Tất cả Ngành --</option>
                                @foreach($nganhs as $ng)
                                    <option value="{{ $ng->MaNganh }}" {{ request('ma_nganh') == $ng->MaNganh ? 'selected' : '' }}>
                                        {{ $ng->TenNganh }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-bold text-muted mb-1">Lớp</label>
                            <select name="ma_lop" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- Tất cả Lớp --</option>
                                @foreach($lops as $lp)
                                    <option value="{{ $lp->MaLop }}" {{ request('ma_lop') == $lp->MaLop ? 'selected' : '' }}>
                                        {{ $lp->TenLop }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 col-md-1">
                            <label class="form-label small fw-bold text-muted mb-1">Khóa</label>
                            <select name="khoa_hoc" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- Khóa --</option>
                                @foreach($khoaHocs as $kh)
                                    <option value="{{ $kh }}" {{ request('khoa_hoc') == $kh ? 'selected' : '' }}>{{ $kh }}</option>
                                @endforeach
                            </select>
                        </div>

                    <!-- ── TAB 2: GIẢNG VIÊN FILTERS ── -->
                    @elseif($activeTab === 'giangvien')
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold text-muted mb-1">Khoa</label>
                            <select name="ma_khoa" id="filterKhoa" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- Tất cả Khoa --</option>
                                @foreach($khoas as $k)
                                    <option value="{{ $k->MaKhoa }}" {{ request('ma_khoa') == $k->MaKhoa ? 'selected' : '' }}>
                                        {{ $k->TenKhoa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold text-muted mb-1">Bộ môn</label>
                            <select name="ma_bomon" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- Tất cả Bộ môn --</option>
                                @foreach($boMons as $bm)
                                    @if(!request('ma_khoa') || $bm->MaKhoa == request('ma_khoa'))
                                        <option value="{{ $bm->MaBoMon }}" {{ request('ma_bomon') == $bm->MaBoMon ? 'selected' : '' }}>
                                            {{ $bm->TenBoMon }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                    <!-- ── TAB 3: TRƯỞNG BỘ MÔN FILTERS ── -->
                    @elseif($activeTab === 'truongbomon')
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold text-muted mb-1">Khoa</label>
                            <select name="ma_khoa" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- Tất cả Khoa --</option>
                                @foreach($khoas as $k)
                                    <option value="{{ $k->MaKhoa }}" {{ request('ma_khoa') == $k->MaKhoa ? 'selected' : '' }}>
                                        {{ $k->TenKhoa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold text-muted mb-1">Bộ môn</label>
                            <select name="ma_bomon" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- Tất cả Bộ môn --</option>
                                @foreach($boMons as $bm)
                                    @if(!request('ma_khoa') || $bm->MaKhoa == request('ma_khoa'))
                                        <option value="{{ $bm->MaBoMon }}" {{ request('ma_bomon') == $bm->MaBoMon ? 'selected' : '' }}>
                                            {{ $bm->TenBoMon }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                    <!-- ── TAB 4: TRƯỞNG KHOA FILTERS (Không có bộ môn) ── -->
                    @elseif($activeTab === 'truongkhoa')
                        <div class="col-6 col-md-5">
                            <label class="form-label small fw-bold text-muted mb-1">Khoa</label>
                            <select name="ma_khoa" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- Tất cả Khoa --</option>
                                @foreach($khoas as $k)
                                    <option value="{{ $k->MaKhoa }}" {{ request('ma_khoa') == $k->MaKhoa ? 'selected' : '' }}>
                                        {{ $k->TenKhoa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Trạng thái (chung) -->
                    <div class="col-6 col-md-1">
                        <label class="form-label small fw-bold text-muted mb-1">Trạng thái</label>
                        <select name="trang_thai" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Tất cả</option>
                            <option value="1" {{ request('trang_thai') === '1' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="0" {{ request('trang_thai') === '0' ? 'selected' : '' }}>Đã khóa</option>
                        </select>
                    </div>

                    <!-- Nút Lọc & Reset -->
                    <div class="col-12 col-md-1 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold" title="Lọc dữ liệu">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                        <a href="{{ route('admin.taikhoan.index', ['tab' => $activeTab]) }}" class="btn btn-sm btn-light border px-2" title="Đặt lại bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══ BẢNG DANH SÁCH THEO TAB ═══ -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <!-- ── TAB 1: SINH VIÊN COLUMNS ── -->
                    @if($activeTab === 'sinhvien')
                        <tr>
                            <th class="ps-4 py-3" width="14%">USERNAME</th>
                            <th class="py-3" width="12%">MÃ SINH VIÊN</th>
                            <th class="py-3" width="18%">HỌ TÊN</th>
                            <th class="py-3" width="14%">KHOA</th>
                            <th class="py-3" width="14%">NGÀNH</th>
                            <th class="py-3" width="10%">LỚP</th>
                            <th class="py-3" width="8%">KHÓA</th>
                            <th class="text-center py-3" width="10%">TRẠNG THÁI</th>
                            <th class="pe-4 py-3 text-center" width="8%">THAO TÁC</th>
                        </tr>

                    <!-- ── TAB 2: GIẢNG VIÊN COLUMNS ── -->
                    @elseif($activeTab === 'giangvien')
                        <tr>
                            <th class="ps-4 py-3" width="16%">USERNAME</th>
                            <th class="py-3" width="10%">MÃ CÁN BỘ</th>
                            <th class="py-3" width="18%">HỌ TÊN</th>
                            <th class="py-3" width="16%">EMAIL</th>
                            <th class="py-3" width="13%">KHOA</th>
                            <th class="py-3" width="13%">BỘ MÔN</th>
                            <th class="text-center py-3" width="9%">TRẠNG THÁI</th>
                            <th class="text-center py-3" width="10%">NGÀY TẠO</th>
                            <th class="pe-4 py-3 text-center" width="8%">THAO TÁC</th>
                        </tr>

                    <!-- ── TAB 3: TRƯỞNG BỘ MÔN COLUMNS ── -->
                    @elseif($activeTab === 'truongbomon')
                        <tr>
                            <th class="ps-4 py-3" width="18%">USERNAME</th>
                            <th class="py-3" width="11%">MÃ CÁN BỘ</th>
                            <th class="py-3" width="18%">HỌ TÊN</th>
                            <th class="py-3" width="15%">KHOA</th>
                            <th class="py-3" width="15%">BỘ MÔN</th>
                            <th class="text-center py-3" width="11%">VAI TRÒ</th>
                            <th class="text-center py-3" width="10%">TRẠNG THÁI</th>
                            <th class="pe-4 py-3 text-center" width="8%">THAO TÁC</th>
                        </tr>

                    <!-- ── TAB 4: TRƯỞNG KHOA COLUMNS (Không có Bộ môn) ── -->
                    @elseif($activeTab === 'truongkhoa')
                        <tr>
                            <th class="ps-4 py-3" width="20%">USERNAME</th>
                            <th class="py-3" width="13%">MÃ CÁN BỘ</th>
                            <th class="py-3" width="22%">HỌ TÊN</th>
                            <th class="py-3" width="18%">KHOA</th>
                            <th class="text-center py-3" width="13%">VAI TRÒ</th>
                            <th class="text-center py-3" width="10%">TRẠNG THÁI</th>
                            <th class="pe-4 py-3 text-center" width="8%">THAO TÁC</th>
                        </tr>
                    @endif
                </thead>

                <tbody class="border-top-0">
                    @forelse($taiKhoans as $tk)
                        @php
                            $sv = $tk->sinhVien;
                            $gv = $tk->giangVien;
                            $isActive = (bool)$tk->TrangThai;

                            // Trích xuất mã khoa từ username nếu chưa gán quan hệ
                            $uKhoa = '';
                            $uBoMon = '';
                            if (preg_match('/^(?:TK|TBM|GV)_([A-Z0-9]+)_([A-Z0-9]+)_/i', $tk->TenDangNhap, $m)) {
                                $uKhoa = $m[1];
                                $uBoMon = $m[2];
                            } elseif (preg_match('/^TK_([A-Z0-9]+)_/i', $tk->TenDangNhap, $m)) {
                                $uKhoa = $m[1];
                            }
                        @endphp

                        <!-- ── ROW: TAB SINH VIÊN ── -->
                        @if($activeTab === 'sinhvien')
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="badge-code">{{ $tk->TenDangNhap }}</span>
                            </td>
                            <td class="py-3 fw-bold text-dark">
                                {{ $sv->MaSV ?? $tk->TenDangNhap }}
                            </td>
                            <td class="py-3 fw-semibold text-dark">
                                {{ $sv->HoTen ?? 'Chưa cập nhật' }}
                            </td>
                            <td class="py-3 text-muted">
                                {{ $sv->lop?->nganh?->khoa?->TenKhoa ?? $sv->khoa?->TenKhoa ?? $sv->MaKhoa ?? '---' }}
                            </td>
                            <td class="py-3 text-muted">
                                {{ $sv->lop?->nganh?->TenNganh ?? $sv->nganh?->TenNganh ?? $sv->MaNganh ?? '---' }}
                            </td>
                            <td class="py-3">
                                <span class="badge bg-light text-secondary border">{{ $sv->lop?->TenLop ?? $sv->MaLop ?? '---' }}</span>
                            </td>
                            <td class="py-3 small text-muted">
                                {{ $sv->KhoaHoc ?? '---' }}
                            </td>
                            <td class="text-center py-3">
                                @if($isActive)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                        <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem;"></i> Hoạt động
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                                        <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem;"></i> Đã khóa
                                    </span>
                                @endif
                            </td>
                            <td class="pe-4 text-center py-3">
                                <div class="d-flex justify-content-center gap-1">
                                    <form action="{{ route('admin.taikhoan.resetPassword', $tk->MaTK) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Đặt lại mật khẩu cho sinh viên {{ $sv->HoTen ?? $tk->TenDangNhap }} về 123456?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light border rounded-pill px-2 text-warning" title="Đặt lại mật khẩu (123456)">
                                            <i class="fa-solid fa-key"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.taikhoan.toggleLock', $tk->MaTK) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('{{ $isActive ? 'Khóa tài khoản này?' : 'Mở khóa tài khoản này?' }}');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light border rounded-pill px-2 {{ $isActive ? 'text-danger' : 'text-success' }}" 
                                                title="{{ $isActive ? 'Khóa tài khoản' : 'Mở khóa' }}">
                                            <i class="fa-solid {{ $isActive ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- ── ROW: TAB GIẢNG VIÊN ── -->
                        @elseif($activeTab === 'giangvien')
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="badge-code">{{ $tk->TenDangNhap }}</span>
                            </td>
                            <td class="py-3 fw-bold text-dark">
                                {{ $gv->MaGV ?? $tk->MaTK }}
                            </td>
                            <td class="py-3">
                                <div class="fw-semibold text-dark">{{ $gv->HoTen ?? 'Chưa cập nhật' }}</div>
                                @if($gv && $gv->HocVi)
                                    <span class="badge bg-light text-muted border small" style="font-size: 0.7rem;">{{ $gv->HocVi }}</span>
                                @endif
                            </td>
                            <td class="py-3 small text-muted">
                                {{ $gv->Email ?? '---' }}
                            </td>
                            <td class="py-3 text-muted">
                                {{ $gv->boMon?->khoa?->TenKhoa ?? ($uKhoa ? 'Khoa ' . $uKhoa : '---') }}
                            </td>
                            <td class="py-3 text-muted">
                                {{ $gv->boMon?->TenBoMon ?? ($uBoMon ? 'BM ' . $uBoMon : '---') }}
                            </td>
                            <td class="text-center py-3">
                                @if($isActive)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                        <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem;"></i> Hoạt động
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                                        <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem;"></i> Đã khóa
                                    </span>
                                @endif
                            </td>
                            <td class="text-center py-3 small text-muted">
                                {{ $tk->created_at ? $tk->created_at->format('d/m/Y') : '---' }}
                            </td>
                            <td class="pe-4 text-center py-3">
                                <div class="d-flex justify-content-center gap-1">
                                    <form action="{{ route('admin.taikhoan.resetPassword', $tk->MaTK) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Đặt lại mật khẩu cho giảng viên {{ $gv->HoTen ?? $tk->TenDangNhap }} về 123456?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light border rounded-pill px-2 text-warning" title="Đặt lại mật khẩu (123456)">
                                            <i class="fa-solid fa-key"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.taikhoan.toggleLock', $tk->MaTK) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('{{ $isActive ? 'Khóa tài khoản này?' : 'Mở khóa tài khoản này?' }}');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light border rounded-pill px-2 {{ $isActive ? 'text-danger' : 'text-success' }}" 
                                                title="{{ $isActive ? 'Khóa tài khoản' : 'Mở khóa' }}">
                                            <i class="fa-solid {{ $isActive ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- ── ROW: TAB TRƯỞNG BỘ MÔN ── -->
                        @elseif($activeTab === 'truongbomon')
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="badge-code">{{ $tk->TenDangNhap }}</span>
                            </td>
                            <td class="py-3 fw-bold text-dark">
                                {{ $gv->MaGV ?? $tk->MaTK }}
                            </td>
                            <td class="py-3 fw-semibold text-dark">
                                {{ $gv->HoTen ?? 'Chưa cập nhật' }}
                            </td>
                            <td class="py-3 text-muted">
                                {{ $gv->boMon?->khoa?->TenKhoa ?? ($uKhoa ? 'Khoa ' . $uKhoa : '---') }}
                            </td>
                            <td class="py-3 text-muted">
                                {{ $gv->boMon?->TenBoMon ?? ($uBoMon ? 'BM ' . $uBoMon : '---') }}
                            </td>
                            <td class="text-center py-3">
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold">
                                    <i class="fa-solid fa-user-tie me-1"></i> Trưởng bộ môn
                                </span>
                            </td>
                            <td class="text-center py-3">
                                @if($isActive)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                        <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem;"></i> Hoạt động
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                                        <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem;"></i> Đã khóa
                                    </span>
                                @endif
                            </td>
                            <td class="pe-4 text-center py-3">
                                <div class="d-flex justify-content-center gap-1">
                                    <form action="{{ route('admin.taikhoan.resetPassword', $tk->MaTK) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Đặt lại mật khẩu tài khoản Trưởng bộ môn {{ $gv->HoTen ?? $tk->TenDangNhap }} về 123456?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light border rounded-pill px-2 text-warning" title="Đặt lại mật khẩu (123456)">
                                            <i class="fa-solid fa-key"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.taikhoan.toggleLock', $tk->MaTK) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('{{ $isActive ? 'Khóa tài khoản này?' : 'Mở khóa tài khoản này?' }}');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light border rounded-pill px-2 {{ $isActive ? 'text-danger' : 'text-success' }}" 
                                                title="{{ $isActive ? 'Khóa tài khoản' : 'Mở khóa' }}">
                                            <i class="fa-solid {{ $isActive ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- ── ROW: TAB TRƯỞNG KHOA (Không có cột Bộ môn) ── -->
                        @elseif($activeTab === 'truongkhoa')
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="badge-code">{{ $tk->TenDangNhap }}</span>
                            </td>
                            <td class="py-3 fw-bold text-dark">
                                {{ $gv->MaGV ?? $tk->MaTK }}
                            </td>
                            <td class="py-3 fw-semibold text-dark">
                                {{ $gv->HoTen ?? 'Chưa cập nhật' }}
                            </td>
                            <td class="py-3 text-muted">
                                {{ $gv->boMon?->khoa?->TenKhoa ?? ($uKhoa ? 'Khoa ' . $uKhoa : '---') }}
                            </td>
                            <td class="text-center py-3">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-bold">
                                    <i class="fa-solid fa-graduation-cap me-1"></i> Trưởng khoa
                                </span>
                            </td>
                            <td class="text-center py-3">
                                @if($isActive)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                        <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem;"></i> Hoạt động
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                                        <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem;"></i> Đã khóa
                                    </span>
                                @endif
                            </td>
                            <td class="pe-4 text-center py-3">
                                <div class="d-flex justify-content-center gap-1">
                                    <form action="{{ route('admin.taikhoan.resetPassword', $tk->MaTK) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Đặt lại mật khẩu tài khoản Trưởng khoa {{ $gv->HoTen ?? $tk->TenDangNhap }} về 123456?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light border rounded-pill px-2 text-warning" title="Đặt lại mật khẩu (123456)">
                                            <i class="fa-solid fa-key"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.taikhoan.toggleLock', $tk->MaTK) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('{{ $isActive ? 'Khóa tài khoản này?' : 'Mở khóa tài khoản này?' }}');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light border rounded-pill px-2 {{ $isActive ? 'text-danger' : 'text-success' }}" 
                                                title="{{ $isActive ? 'Khóa tài khoản' : 'Mở khóa' }}">
                                            <i class="fa-solid {{ $isActive ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endif

                    @empty
                        <tr>
                            <td colspan="{{ $activeTab === 'truongkhoa' ? 7 : ($activeTab === 'giangvien' ? 9 : 9) }}" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa-solid fa-user-slash fa-3x text-secondary mb-3 opacity-50"></i>
                                    <h6 class="fw-bold">Không tìm thấy tài khoản nào</h6>
                                    <p class="small mb-3">Vui lòng thử điều chỉnh lại từ khóa tìm kiếm hoặc bộ lọc.</p>
                                    <a href="{{ route('admin.taikhoan.index', ['tab' => $activeTab]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-4">
                                        Đặt lại bộ lọc
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($taiKhoans->hasPages())
        <div class="p-3 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <span class="small text-muted">
                Hiển thị <strong>{{ $taiKhoans->firstItem() }} - {{ $taiKhoans->lastItem() }}</strong> trên tổng số <strong>{{ $taiKhoans->total() }}</strong> tài khoản
            </span>
            {{ $taiKhoans->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>

<!-- ═══ MODAL THÊM TÀI KHOẢN CÁN BỘ (SECTION 7, 8, 9, 10) ═══ -->
<div class="modal fade" id="createCanBoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background: #002855; color: #ffffff;">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="fa-solid fa-user-plus text-warning"></i> Thêm tài khoản cán bộ
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('admin.taikhoan.store_canbo') }}" id="formCreateCanBo">
                @csrf
                <div class="modal-body p-4">

                    <!-- Chọn 1 trong 3 loại cán bộ -->
                    <!-- Chọn 1 trong 3 loại cán bộ -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark small text-uppercase mb-2">Chọn loại tài khoản cán bộ <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-2">
                            <input type="radio" class="btn-check" name="loai_can_bo" id="btnLoaiTBM" value="TRUONG_BO_MON" checked onchange="onLoaiCanBoChange()">
                            <label class="btn btn-outline-warning text-dark px-3 py-2 fw-semibold rounded-3 flex-fill text-center" for="btnLoaiTBM">
                                <i class="fa-solid fa-user-tie me-1"></i> Trưởng bộ môn
                            </label>

                            <input type="radio" class="btn-check" name="loai_can_bo" id="btnLoaiTK" value="TRUONG_KHOA" onchange="onLoaiCanBoChange()">
                            <label class="btn btn-outline-danger px-3 py-2 fw-semibold rounded-3 flex-fill text-center" for="btnLoaiTK">
                                <i class="fa-solid fa-graduation-cap me-1"></i> Trưởng khoa
                            </label>

                            <input type="radio" class="btn-check" name="loai_can_bo" id="btnLoaiGV" value="GIANG_VIEN" onchange="onLoaiCanBoChange()">
                            <label class="btn btn-outline-primary px-3 py-2 fw-semibold rounded-3 flex-fill text-center" for="btnLoaiGV">
                                <i class="fa-solid fa-chalkboard-user me-1"></i> Giảng viên
                            </label>
                        </div>
                    </div>

                    <!-- Hộp hiển thị Preview Username tự động -->
                    <div class="mb-4 p-3 rounded-3" style="background: #f1f5f9; border: 1px solid #cbd5e1;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="small text-muted fw-bold text-uppercase">Tên đăng nhập hệ thống tự sinh:</span>
                                <div class="form-preview-username mt-1" id="previewUsername">TBM_[KHOA]_[BM]_[MACB]</div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1.5" id="badgeRoleName">Vai trò: Trưởng bộ môn (VT04)</span>
                            </div>
                        </div>
                        <div class="small text-muted mt-2" id="ruleNote">
                            <i class="fa-solid fa-circle-info me-1 text-primary"></i> Quy tắc: <code>TBM_{MA_KHOA}_{MA_BO_MON}_{MA_CAN_BO}</code>
                        </div>
                    </div>

                    <!-- ── PHẦN 1: CHỌN GIẢNG VIÊN TỪ DANH SÁCH (Áp dụng cho TBM & TK) ── -->
                    <div class="mb-3" id="divSelectGiangVien">
                        <label class="form-label small fw-bold text-dark">
                            <i class="fa-solid fa-user-check text-primary me-1"></i> Chọn giảng viên từ danh sách <span class="text-danger">*</span>
                        </label>
                        <select id="modalSelectGiangVien" class="form-select" onchange="onSelectGiangVienChange()">
                            <option value="">-- Chọn giảng viên để bổ nhiệm vai trò --</option>
                            @foreach($giangViens as $gv)
                                <option value="{{ $gv->MaGV }}"
                                        data-hoten="{{ $gv->HoTen }}"
                                        data-email="{{ $gv->Email }}"
                                        data-sdt="{{ $gv->SoDienThoai }}"
                                        data-hocvi="{{ $gv->HocVi }}"
                                        data-makhoa="{{ $gv->boMon?->MaKhoa }}"
                                        data-tenkhoa="{{ $gv->boMon?->khoa?->TenKhoa }}"
                                        data-mabomon="{{ $gv->MaBoMon }}"
                                        data-tenbomon="{{ $gv->boMon?->TenBoMon }}"
                                        data-username="{{ $gv->taiKhoan?->TenDangNhap }}"
                                        data-hastk="{{ $gv->MaTK ? '1' : '0' }}">
                                    {{ $gv->HoTen }} ({{ $gv->MaGV }}) &bull; {{ $gv->boMon?->TenBoMon ?? 'Chưa phân BM' }} [Khoa {{ $gv->boMon?->khoa?->TenKhoa ?? 'Chưa rõ' }}]
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small text-muted">
                            <i class="fa-solid fa-circle-info text-info me-1"></i> Thông tin cán bộ sẽ được lấy tự động từ danh sách giảng viên để bổ nhiệm chức vụ.
                        </div>

                        <!-- Card tóm tắt hồ sơ Giảng viên đã chọn -->
                        <div id="cardGvSelectedInfo" class="mt-3 p-3 bg-white border rounded-3 shadow-xs d-none">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fa-solid fa-user-tie"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0" id="infoGvHoTen">TS. Nguyễn Văn A</h6>
                                        <span class="small text-muted" id="infoGvMa">Mã CB: GV001</span>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark border" id="infoGvTrangThaiTK">Đã có tài khoản</span>
                            </div>
                            <div class="row g-2 small text-secondary">
                                <div class="col-6"><i class="fa-solid fa-envelope me-1 text-primary"></i> <span id="infoGvEmail">email@huit.edu.vn</span></div>
                                <div class="col-6"><i class="fa-solid fa-phone me-1 text-primary"></i> <span id="infoGvSdt">0901234567</span></div>
                                <div class="col-6"><i class="fa-solid fa-building-columns me-1 text-primary"></i> Khoa: <strong class="text-dark" id="infoGvKhoa">CNTT</strong></div>
                                <div class="col-6" id="infoGvDivBoMon"><i class="fa-solid fa-layer-group me-1 text-primary"></i> Bộ môn: <strong class="text-dark" id="infoGvBoMon">CNPM</strong></div>
                            </div>
                        </div>
                    </div>

                    <!-- ── PHẦN 2: THÔNG TIN CHI TIẾT (Nhập mới khi tạo Giảng viên hoặc chứa giá trị gửi lên server) ── -->
                    <div id="divManualInputs">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Mã cán bộ <span class="text-danger">*</span></label>
                                <input type="text" name="ma_can_bo" id="inputMaCanBo" class="form-control" 
                                       placeholder="Ví dụ: GV001, CB010..." required oninput="updateUsernamePreview()">
                                <div class="form-text small text-muted">Dùng chữ cái và số, viết liền (VD: GV001).</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Họ và tên cán bộ <span class="text-danger">*</span></label>
                                <input type="text" name="ho_ten" id="inputHoTen" class="form-control" placeholder="Ví dụ: TS. Nguyễn Văn A" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Email liên hệ <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="inputEmail" class="form-control" placeholder="canbo@huit.edu.vn" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Số điện thoại</label>
                                <input type="text" name="so_dien_thoai" id="inputSdt" class="form-control" placeholder="0901234567">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Trường Học vị -->
                            <div class="col-md-4" id="divHocVi">
                                <label class="form-label small fw-bold text-dark">Học vị</label>
                                <select name="hoc_vi" id="inputHocVi" class="form-select">
                                    <option value="Thạc sĩ">Thạc sĩ</option>
                                    <option value="Tiến sĩ" selected>Tiến sĩ</option>
                                    <option value="Phó Giáo sư">Phó Giáo sư</option>
                                    <option value="Giáo sư">Giáo sư</option>
                                    <option value="Kỹ sư">Kỹ sư</option>
                                    <option value="Cử nhân">Cử nhân</option>
                                </select>
                            </div>

                            <!-- Trường Khoa -->
                            <div class="col-md-4" id="divKhoa">
                                <label class="form-label small fw-bold text-dark">Khoa <span class="text-danger">*</span></label>
                                <select name="ma_khoa" id="modalSelectKhoa" class="form-select" required onchange="onModalKhoaChange()">
                                    <option value="">-- Chọn Khoa --</option>
                                    @foreach($khoas as $k)
                                        <option value="{{ $k->MaKhoa }}" data-ten="{{ $k->TenKhoa }}">{{ $k->TenKhoa }} ({{ $k->MaKhoa }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Trường Bộ môn (chỉ có ở GV và TBM, KHÔNG CÓ Ở TRƯỞNG KHOA) -->
                            <div class="col-md-4" id="divBoMon">
                                <label class="form-label small fw-bold text-dark">Bộ môn <span class="text-danger">*</span></label>
                                <select name="ma_bomon" id="modalSelectBoMon" class="form-select" onchange="updateUsernamePreview()">
                                    <option value="">-- Chọn Bộ môn --</option>
                                    @foreach($boMons as $bm)
                                        <option value="{{ $bm->MaBoMon }}" data-khoa="{{ $bm->MaKhoa }}">{{ $bm->TenBoMon }} ({{ $bm->MaBoMon }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3 p-3 bg-light rounded-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Mật khẩu đăng nhập</label>
                            <input type="text" name="mat_khau" class="form-control" value="123456" placeholder="Mặc định: 123456">
                            <div class="form-text small text-muted">Mặc định là <code>123456</code> (nếu giảng viên đã có tài khoản, để trống để giữ nguyên MK).</div>
                        </div>

                        <div class="col-md-6 d-flex align-items-center mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="bat_buoc_doi_mat_khau" id="chkMustChangePassword" value="1" checked>
                                <label class="form-check-label small fw-semibold text-dark" for="chkMustChangePassword">
                                    Bắt buộc đổi mật khẩu ở lần đăng nhập đầu tiên
                                </label>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top py-2 px-4">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Lưu &amp; Cấp Vai Trò
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Quản lý Modal Tạo Tài Khoản Cán Bộ
function onLoaiCanBoChange() {
    const isGV = document.getElementById('btnLoaiGV').checked;
    const isTBM = document.getElementById('btnLoaiTBM').checked;
    const isTK = document.getElementById('btnLoaiTK').checked;

    const divSelectGV = document.getElementById('divSelectGiangVien');
    const divManual = document.getElementById('divManualInputs');
    const divHocVi = document.getElementById('divHocVi');
    const divBoMon = document.getElementById('divBoMon');
    const selectBoMon = document.getElementById('modalSelectBoMon');
    const badgeRole = document.getElementById('badgeRoleName');
    const ruleNote = document.getElementById('ruleNote');
    const selectGV = document.getElementById('modalSelectGiangVien');
    const cardGvInfo = document.getElementById('cardGvSelectedInfo');

    if (isTK) {
        // TRƯỞNG KHOA: Lấy thông tin từ danh sách giảng viên
        divSelectGV.style.display = 'block';
        divManual.style.display = 'none';
        selectBoMon.removeAttribute('required');
        badgeRole.className = 'badge bg-danger rounded-pill px-3 py-1.5';
        badgeRole.textContent = 'Vai trò: Trưởng khoa (VT05)';
        ruleNote.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> <strong>Business rule:</strong> Chọn giảng viên từ danh sách. Một Khoa chỉ có một Trưởng khoa đang hoạt động. Quy tắc Username: <code>TK_{MA_KHOA}_{MA_CAN_BO}</code>';
        onSelectGiangVienChange();

    } else if (isTBM) {
        // TRƯỞNG BỘ MÔN: Lấy thông tin từ danh sách giảng viên
        divSelectGV.style.display = 'block';
        divManual.style.display = 'none';
        selectBoMon.setAttribute('required', 'required');
        badgeRole.className = 'badge bg-warning text-dark rounded-pill px-3 py-1.5';
        badgeRole.textContent = 'Vai trò: Trưởng bộ môn (VT04)';
        ruleNote.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> <strong>Business rule:</strong> Chọn giảng viên từ danh sách. Một Bộ môn chỉ có một Trưởng bộ môn đang hoạt động. Quy tắc Username: <code>TBM_{MA_KHOA}_{MA_BO_MON}_{MA_CAN_BO}</code>';
        onSelectGiangVienChange();

    } else {
        // GIẢNG VIÊN: Nhập giảng viên mới hoặc có thể chọn
        divSelectGV.style.display = 'none';
        cardGvInfo.classList.add('d-none');
        divManual.style.display = 'block';
        divHocVi.style.display = 'block';
        divBoMon.style.display = 'block';
        selectBoMon.setAttribute('required', 'required');
        badgeRole.className = 'badge bg-primary rounded-pill px-3 py-1.5';
        badgeRole.textContent = 'Vai trò: Giảng viên (VT02)';
        ruleNote.innerHTML = '<i class="fa-solid fa-circle-info text-primary me-1"></i> Quy tắc Username: <code>GV_{MA_KHOA}_{MA_BO_MON}_{MA_CAN_BO}</code>';
        updateUsernamePreview();
    }
}

function onSelectGiangVienChange() {
    const isGV = document.getElementById('btnLoaiGV').checked;
    if (isGV) return;

    const selectGV = document.getElementById('modalSelectGiangVien');
    const cardGvInfo = document.getElementById('cardGvSelectedInfo');
    const opt = selectGV.selectedOptions[0];

    if (!opt || !opt.value) {
        cardGvInfo.classList.add('d-none');
        document.getElementById('inputMaCanBo').value = '';
        updateUsernamePreview();
        return;
    }

    // Hiển thị thông tin giảng viên được chọn
    cardGvInfo.classList.remove('d-none');
    document.getElementById('infoGvHoTen').textContent = opt.dataset.hoten || 'Chưa rõ';
    document.getElementById('infoGvMa').textContent = 'Mã CB: ' + opt.value;
    document.getElementById('infoGvEmail').textContent = opt.dataset.email || 'Chưa cập nhật email';
    document.getElementById('infoGvSdt').textContent = opt.dataset.sdt || 'Chưa cập nhật SĐT';
    document.getElementById('infoGvKhoa').textContent = (opt.dataset.tenkhoa || 'Chưa rõ') + (opt.dataset.makhoa ? ' (' + opt.dataset.makhoa + ')' : '');
    document.getElementById('infoGvBoMon').textContent = (opt.dataset.tenbomon || 'Chưa gán') + (opt.dataset.mabomon ? ' (' + opt.dataset.mabomon + ')' : '');

    const badgeTk = document.getElementById('infoGvTrangThaiTK');
    if (opt.dataset.hastk === '1') {
        badgeTk.textContent = 'Đã có TK: ' + (opt.dataset.username || '');
        badgeTk.className = 'badge bg-info-subtle text-info border';
    } else {
        badgeTk.textContent = 'Chưa có tài khoản';
        badgeTk.className = 'badge bg-secondary-subtle text-secondary border';
    }

    // Điền vào các trường ẩn để gửi lên controller
    document.getElementById('inputMaCanBo').value = opt.value;
    document.getElementById('inputHoTen').value = opt.dataset.hoten || '';
    document.getElementById('inputEmail').value = opt.dataset.email || '';
    document.getElementById('inputSdt').value = opt.dataset.sdt || '';
    if (opt.dataset.makhoa) {
        document.getElementById('modalSelectKhoa').value = opt.dataset.makhoa;
    }
    if (opt.dataset.mabomon) {
        document.getElementById('modalSelectBoMon').value = opt.dataset.mabomon;
    }

    updateUsernamePreview();
}

function onModalKhoaChange() {
    const selectKhoa = document.getElementById('modalSelectKhoa');
    const selectBoMon = document.getElementById('modalSelectBoMon');
    const selectedKhoa = selectKhoa.value;

    // Filter Bộ môn thuộc Khoa đã chọn
    Array.from(selectBoMon.options).forEach(opt => {
        if (!opt.value) return;
        const bmKhoa = opt.getAttribute('data-khoa');
        if (!selectedKhoa || bmKhoa === selectedKhoa) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
        }
    });

    const currentBM = selectBoMon.selectedOptions[0];
    if (currentBM && currentBM.getAttribute('data-khoa') !== selectedKhoa && selectedKhoa !== '') {
        selectBoMon.value = '';
    }

    updateUsernamePreview();
}

function updateUsernamePreview() {
    const isGV = document.getElementById('btnLoaiGV').checked;
    const isTBM = document.getElementById('btnLoaiTBM').checked;
    const isTK = document.getElementById('btnLoaiTK').checked;

    let maCanBo = (document.getElementById('inputMaCanBo').value || 'MACB').trim().toUpperCase().replace(/[^A-Z0-9_]/g, '');
    let maKhoa = (document.getElementById('modalSelectKhoa').value || 'KHOA').trim().toUpperCase();
    let maBoMon = (document.getElementById('modalSelectBoMon').value || 'BM').trim().toUpperCase();

    if (!isGV) {
        const selectGV = document.getElementById('modalSelectGiangVien');
        const opt = selectGV.selectedOptions[0];
        if (opt && opt.value) {
            maCanBo = opt.value;
            if (opt.dataset.makhoa) maKhoa = opt.dataset.makhoa;
            if (opt.dataset.mabomon) maBoMon = opt.dataset.mabomon;
        }
    }

    let username = '';
    if (isTK) {
        username = `TK_${maKhoa}_${maCanBo}`;
    } else if (isTBM) {
        username = `TBM_${maKhoa}_${maBoMon}_${maCanBo}`;
    } else {
        username = `GV_${maKhoa}_${maBoMon}_${maCanBo}`;
    }

    document.getElementById('previewUsername').textContent = username;
}

// Khởi chạy khi load trang
document.addEventListener('DOMContentLoaded', function () {
    onLoaiCanBoChange();
});
</script>
@endpush
