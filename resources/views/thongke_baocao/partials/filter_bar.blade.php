<div class="card border shadow-none rounded-3 mb-4 bg-white admin-filter-card" id="sectionFilterBar">
    <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark small text-uppercase">Bộ lọc dữ liệu</h6>
        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold" data-bs-toggle="collapse" data-bs-target="#advancedFiltersCollapse" aria-expanded="false" aria-controls="advancedFiltersCollapse" id="btnToggleAdvancedFilters">
            <span id="textToggleAdvanced">Lọc nâng cao</span>
            <i class="fa-solid fa-chevron-down ms-1 small" id="iconToggleAdvanced"></i>
        </button>
    </div>

    <div class="card-body p-3">
        <form id="filterForm" method="GET" action="{{ route($scope['routePrefix'] . '.index') }}">
            <input type="hidden" name="mau_bao_cao" id="filterMauBaoCao" value="{{ $filters['mau_bao_cao'] ?? 'mau_01' }}">

            <div class="row g-2 mb-2">
                <div class="col-12 col-sm-6 col-lg-4 col-xl">
                    <label for="filterDotBaoVe" class="form-label text-muted small fw-semibold mb-1">Đợt bảo vệ</label>
                    <select name="DotBaoVe" id="filterDotBaoVe" class="form-select form-select-sm filter-select">
                        <option value="">Tất cả đợt</option>
                        @foreach($danhMuc['dotBaoVes'] as $dot)
                            <option value="{{ $dot }}" {{ ($filters['DotBaoVe'] ?? '') == $dot ? 'selected' : '' }}>{{ $dot }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-lg-4 col-xl">
                    <label for="filterHocKy" class="form-label text-muted small fw-semibold mb-1">Học kỳ <span class="text-danger">*</span></label>
                    <select name="MaHocKy" id="filterHocKy" class="form-select form-select-sm filter-select" required>
                        @foreach($danhMuc['hocKies'] as $hk)
                            @php
                                $isCurrent = ($hk->TrangThai === 'Đang diễn ra' || $hk->TrangThai == true || $hk->TrangThai === 1);
                                $isSelected = (($filters['MaHocKy'] ?? '') == $hk->MaHocKy || (!isset($filters['MaHocKy']) && $isCurrent));
                            @endphp
                            <option value="{{ $hk->MaHocKy }}" data-nam-hoc="{{ $hk->NamHoc }}" data-is-current="{{ $isCurrent ? '1' : '0' }}" {{ $isSelected ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }} {{ $isCurrent ? '(Hiện hành)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-lg-4 col-xl">
                    <label for="filterNamHoc" class="form-label text-muted small fw-semibold mb-1">Năm học</label>
                    <select name="NamHoc" id="filterNamHoc" class="form-select form-select-sm filter-select">
                        <option value="">Tất cả năm học</option>
                        @foreach($danhMuc['namHocs'] as $nh)
                            <option value="{{ $nh }}" {{ ($filters['NamHoc'] ?? '') == $nh ? 'selected' : '' }}>{{ $nh }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-lg-6 col-xl">
                    <label for="filterNganh" class="form-label text-muted small fw-semibold mb-1">Ngành đào tạo</label>
                    <select name="MaNganh" id="filterNganh" class="form-select form-select-sm filter-select">
                        <option value="">Tất cả ngành</option>
                        @foreach($danhMuc['nganhs'] as $ng)
                            <option value="{{ $ng->MaNganh }}" data-khoa="{{ $ng->MaKhoa }}" {{ ($filters['MaNganh'] ?? '') == $ng->MaNganh ? 'selected' : '' }}>{{ $ng->TenNganh }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-lg-6 col-xl">
                    <label for="filterKetQua" class="form-label text-muted small fw-semibold mb-1">Kết quả</label>
                    <select name="ket_qua" id="filterKetQua" class="form-select form-select-sm filter-select">
                        <option value="">Tất cả</option>
                        <option value="Dat" {{ ($filters['ket_qua'] ?? '') === 'Dat' ? 'selected' : '' }}>Đạt (≥ 4.0)</option>
                        <option value="KhongDat" {{ ($filters['ket_qua'] ?? '') === 'KhongDat' ? 'selected' : '' }}>Chưa đạt (&lt; 4.0)</option>
                        <option value="ChuaXong" {{ ($filters['ket_qua'] ?? '') === 'ChuaXong' ? 'selected' : '' }}>Chưa có KQ</option>
                    </select>
                </div>
            </div>

            <div class="collapse {{ (!empty($filters['MaKhoa']) || !empty($filters['MaBoMon']) || !empty($filters['MaHocPhan']) || !empty($filters['MaHoiDong']) || !empty($filters['MaGV']) || !empty($filters['search'])) ? 'show' : '' }}" id="advancedFiltersCollapse">
                <div class="pt-2 border-top mt-2">
                    <div class="row g-2">
                        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
                            <label for="filterKhoa" class="form-label text-muted small fw-semibold mb-1">Khoa</label>
                            @if($scope['role'] === 'admin')
                                <select name="MaKhoa" id="filterKhoa" class="form-select form-select-sm filter-select">
                                    <option value="">Tất cả khoa</option>
                                    @foreach($danhMuc['khoas'] as $k)
                                        <option value="{{ $k->MaKhoa }}" {{ ($filters['MaKhoa'] ?? '') == $k->MaKhoa ? 'selected' : '' }}>{{ $k->TenKhoa }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" name="MaKhoa" id="filterKhoa" value="{{ $scope['maKhoa'] }}">
                                <input type="text" class="form-control form-control-sm bg-light text-muted" value="{{ $danhMuc['khoas']->firstWhere('MaKhoa', $scope['maKhoa'])?->TenKhoa ?? $scope['maKhoa'] }}" readonly>
                            @endif
                        </div>

                        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
                            <label for="filterBoMon" class="form-label text-muted small fw-semibold mb-1">Bộ môn</label>
                            <select name="MaBoMon" id="filterBoMon" class="form-select form-select-sm filter-select">
                                <option value="">Tất cả bộ môn</option>
                                @foreach($danhMuc['boMons'] as $bm)
                                    <option value="{{ $bm->MaBoMon }}" data-khoa="{{ $bm->MaKhoa }}" {{ ($filters['MaBoMon'] ?? '') == $bm->MaBoMon ? 'selected' : '' }}>{{ $bm->TenBoMon }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
                            <label for="filterHocPhan" class="form-label text-muted small fw-semibold mb-1">Học phần</label>
                            <select name="MaHocPhan" id="filterHocPhan" class="form-select form-select-sm filter-select">
                                <option value="">Tất cả học phần</option>
                                @foreach($danhMuc['hocPhans'] as $hp)
                                    <option value="{{ $hp->MaHocPhan }}" data-khoa="{{ $hp->MaKhoa }}" data-bomon="{{ $hp->MaBoMon }}" {{ ($filters['MaHocPhan'] ?? '') == $hp->MaHocPhan ? 'selected' : '' }}>
                                        {{ $hp->MaHocPhan }} - {{ $hp->TenHocPhan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
                            <label for="filterHoiDong" class="form-label text-muted small fw-semibold mb-1">Hội đồng</label>
                            <select name="MaHoiDong" id="filterHoiDong" class="form-select form-select-sm filter-select">
                                <option value="">Tất cả hội đồng</option>
                                @foreach($danhMuc['hoiDongs'] as $hd)
                                    <option value="{{ $hd->MaHoiDong }}" data-khoa="{{ $hd->MaKhoa }}" data-nganh="{{ $hd->MaNganh }}" data-hocky="{{ $hd->MaHocKy }}" {{ ($filters['MaHoiDong'] ?? '') == $hd->MaHoiDong ? 'selected' : '' }}>{{ $hd->TenHoiDong }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
                            <label for="filterGiangVien" class="form-label text-muted small fw-semibold mb-1">GV hướng dẫn</label>
                            <select name="MaGV" id="filterGiangVien" class="form-select form-select-sm filter-select">
                                <option value="">Tất cả GVHD</option>
                                @foreach($danhMuc['giangViens'] as $gv)
                                    <option value="{{ $gv->MaGV }}" data-khoa="{{ $gv->boMon?->MaKhoa }}" data-bomon="{{ $gv->MaBoMon }}" {{ ($filters['MaGV'] ?? '') == $gv->MaGV ? 'selected' : '' }}>{{ $gv->HoTen }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
                            <label for="filterSearch" class="form-label text-muted small fw-semibold mb-1">Tìm kiếm</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" id="filterSearch" class="form-control" placeholder="MSSV, tên, đề tài..." value="{{ $filters['search'] ?? '' }}" autocomplete="off">
                                @if(!empty($filters['search']))
                                    <button class="btn btn-outline-secondary" type="button" id="btnClearSearch">&times;</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-3 border-top mt-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="submit" id="btnFilterSubmit" class="btn btn-primary btn-sm px-3 fw-semibold">
                        Áp dụng lọc
                    </button>
                    <button type="button" id="btnResetFilter" class="btn btn-outline-secondary btn-sm px-3">
                        Đặt lại
                    </button>
                    <a href="#sectionTemplateSelector" class="btn btn-outline-secondary btn-sm px-3 text-decoration-none">
                        Chọn mẫu báo cáo
                    </a>
                    <div id="filterLoadingIndicator" class="d-none align-items-center gap-2 text-primary small ms-2">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        <span>Đang tải...</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 ms-auto">
                    <button type="button" id="btnOpenPreviewModal" class="btn btn-outline-primary btn-sm px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalPreviewReport">
                        Xem trước bản in
                    </button>
                    <button type="button" id="btnDirectExportPdf" class="btn btn-danger btn-sm px-3 fw-semibold" {{ empty($hasData) ? 'disabled' : '' }}>
                        Xuất PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>