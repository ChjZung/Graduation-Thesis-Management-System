<div class="admin-filter-bar mb-3">
    <form id="filterForm" method="GET" action="{{ route($scope['routePrefix'] . '.index') }}" class="d-flex flex-wrap align-items-center justify-content-between gap-2 w-100">

        <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1 filter-inputs-wrap" style="min-width: 0;">
            
            <div class="input-group input-group-sm search-filter-group" style="flex: 2 1 180px; min-width: 160px;">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" id="inputSearch" class="form-control border-start-0 ps-0" placeholder="MSSV, họ tên, đề tài..." value="{{ request('search') }}" autocomplete="off">
            </div>

            <select name="MaHocKy" id="selectHocKy" class="form-select form-select-sm filter-select text-truncate" style="flex: 1 1 150px; max-width: 220px;" required>
                @foreach($hocKies as $hk)
                    @php
                        $isCurrent = ($hk->TrangThai === 'Đang diễn ra' || $hk->TrangThai == true || $hk->TrangThai === 1);
                        $isSelected = (request('MaHocKy') == $hk->MaHocKy || (!request()->filled('MaHocKy') && $isCurrent));
                    @endphp
                    <option value="{{ $hk->MaHocKy }}" data-is-current="{{ $isCurrent ? '1' : '0' }}" {{ $isSelected ? 'selected' : '' }}>
                        {{ $hk->TenHocKy }}
                    </option>
                @endforeach
            </select>

            @if($scope['role'] === 'admin')
            <select name="MaKhoa" id="selectKhoa" class="form-select form-select-sm filter-select text-truncate" style="flex: 1 1 140px; max-width: 200px;">
                <option value="">Tất cả khoa</option>
                @foreach($khoas as $k)
                    <option value="{{ $k->MaKhoa }}" {{ request('MaKhoa') == $k->MaKhoa ? 'selected' : '' }}>{{ $k->TenKhoa }}</option>
                @endforeach
            </select>
            @else
            <input type="hidden" name="MaKhoa" id="selectKhoa" value="{{ $scope['maKhoa'] }}">
            @endif

            <select name="MaBoMon" id="selectBoMon" class="form-select form-select-sm filter-select filter-col-bomon text-truncate" style="flex: 1 1 140px; max-width: 200px;">
                <option value="">Tất cả bộ môn</option>
                @foreach($boMons as $bm)
                    <option value="{{ $bm->MaBoMon }}" data-khoa="{{ $bm->MaKhoa }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>{{ $bm->TenBoMon }}</option>
                @endforeach
            </select>

            <select name="MaNganh" id="selectNganh" class="form-select form-select-sm filter-select text-truncate" style="flex: 1 1 140px; max-width: 200px;">
                <option value="">Tất cả ngành</option>
                @foreach($nganhs as $ng)
                    <option value="{{ $ng->MaNganh }}" data-khoa="{{ $ng->MaKhoa }}" {{ request('MaNganh') == $ng->MaNganh ? 'selected' : '' }}>{{ $ng->TenNganh }}</option>
                @endforeach
            </select>

            <select name="MaHoiDong" id="selectHoiDong" class="form-select form-select-sm filter-select filter-col-hoidong text-truncate" style="flex: 1 1 150px; max-width: 220px;">
                <option value="">Tất cả hội đồng</option>
                @foreach($hoiDongs as $hd)
                    <option value="{{ $hd->MaHoiDong }}" data-khoa="{{ $hd->MaKhoa }}" data-nganh="{{ $hd->MaNganh }}" data-hocky="{{ $hd->MaHocKy }}" {{ request('MaHoiDong') == $hd->MaHoiDong ? 'selected' : '' }}>{{ $hd->TenHoiDong }}</option>
                @endforeach
            </select>

            <select name="ket_qua" id="selectKetQua" class="form-select form-select-sm filter-select text-truncate" style="flex: 1 1 130px; max-width: 180px;">
                <option value="">Tất cả kết quả</option>
                <option value="Dat" {{ request('ket_qua') === 'Dat' ? 'selected' : '' }}>Đạt</option>
                <option value="KhongDat" {{ request('ket_qua') === 'KhongDat' ? 'selected' : '' }}>Không đạt</option>
                <option value="ChuaXong" {{ request('ket_qua') === 'ChuaXong' ? 'selected' : '' }}>Chưa có KQ</option>
            </select>

            <div id="filterLoadingIndicator" class="d-none align-items-center text-primary small ms-1">
                <span class="spinner-border spinner-border-sm" role="status"></span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-auto">
            <button type="button" id="btnResetFilter" class="btn btn-sm btn-outline-secondary px-3">
                Đặt lại
            </button>

            <button type="button" class="btn btn-sm btn-primary px-3 fw-semibold" id="btnOpenExportModal" data-bs-toggle="modal" data-bs-target="#modalXuatPdf" {{ empty($hasData) ? 'disabled' : '' }}>
                Xuất PDF
            </button>
        </div>
    </form>
</div>