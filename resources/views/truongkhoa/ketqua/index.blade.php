@extends('layouts.truongkhoa')

@section('page_title', 'Tổng Hợp & Công Bố Kết Quả Khóa Luận')

@section('content')
<style>
/* Ẩn triệt để nút x mặc định của webkit/trình duyệt */
input::-webkit-search-decoration,
input::-webkit-search-cancel-button,
input::-webkit-search-results-button,
input::-webkit-search-results-decoration {
    -webkit-appearance: none;
    appearance: none;
    display: none !important;
}
</style>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-award me-2 text-primary"></i>
                Quản Lý Kết Quả Bảo Vệ &amp; Bảng Điểm Khóa Luận — {{ $khoa->TenKhoa ?? 'Khoa CNTT' }}
            </h4>
            <div class="text-muted small">
                Theo dõi tiến độ chấm điểm các hội đồng, đối chiếu độ lệch điểm giám khảo, xác nhận và công bố điểm chính thức.
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('truongkhoa.ketqua.export', request()->all()) }}" id="btn-export-excel" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-semibold shadow-xs">
                <i class="fa-solid fa-file-excel me-2"></i> Xuất Bảng Điểm (Excel / CSV)
            </a>
        </div>
    </div>

    <!-- ═══ SUMMARY KPI CARDS ═══ -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #0072ce !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Tổng Sinh Viên Có Điểm</div>
                    <h3 class="fw-bold my-1 text-primary" id="stat-total">{{ $stats['total'] }}</h3>
                    <div class="small text-muted">Đã ghi nhận dữ liệu điểm</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #198754 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Xuất Sắc (>= 9.0)</div>
                    <h3 class="fw-bold my-1 text-success" id="stat-xuat-sac">{{ $stats['xuat_sac'] }}</h3>
                    <div class="small text-muted" id="stat-xuat-sac-pct">
                        {{ $stats['total'] > 0 ? round(($stats['xuat_sac'] / $stats['total']) * 100, 1) : 0 }}% tổng số
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #0d6efd !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Giỏi (8.0 - 8.9)</div>
                    <h3 class="fw-bold my-1 text-primary" id="stat-gioi">{{ $stats['gioi'] }}</h3>
                    <div class="small text-muted" id="stat-gioi-pct">
                        {{ $stats['total'] > 0 ? round(($stats['gioi'] / $stats['total']) * 100, 1) : 0 }}% tổng số
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #ffc107 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Khá (7.0 - 7.9)</div>
                    <h3 class="fw-bold my-1 text-warning" id="stat-kha">{{ $stats['kha'] }}</h3>
                    <div class="small text-muted" id="stat-kha-pct">
                        {{ $stats['total'] > 0 ? round(($stats['kha'] / $stats['total']) * 100, 1) : 0 }}% tổng số
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #dc3545 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Trung Bình / Chưa Đạt</div>
                    <h3 class="fw-bold my-1 text-danger" id="stat-trung-binh-khong-dat">{{ $stats['trung_binh'] + $stats['khong_dat'] }}</h3>
                    <div class="small text-muted" id="stat-tb-kd-detail">TB: {{ $stats['trung_binh'] }} | Không đạt: {{ $stats['khong_dat'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ NAV TABS CHUYỂN ĐỔI CHẾ ĐỘ XEM ═══ -->
    <div class="d-flex border-bottom mb-4">
        <a href="{{ route('truongkhoa.ketqua.index', array_filter(['view' => 'hoidong', 'MaHocKy' => request('MaHocKy')])) }}" 
           class="px-4 py-2 text-decoration-none fw-bold {{ $viewMode === 'hoidong' ? 'border-bottom border-primary border-3 text-primary' : 'text-secondary' }}">
            <i class="fa-solid fa-users-viewfinder me-2"></i> Tổng Hợp &amp; Công Bố Theo Hội Đồng <span id="tab-counter-hoidong">({{ $hoiDongs->count() }})</span>
        </a>
        <a href="{{ route('truongkhoa.ketqua.index', array_filter(['view' => 'toankhoa', 'MaHocKy' => request('MaHocKy')])) }}" 
           class="px-4 py-2 text-decoration-none fw-bold {{ $viewMode === 'toankhoa' ? 'border-bottom border-primary border-3 text-primary' : 'text-secondary' }}">
            <i class="fa-solid fa-list-ol me-2"></i> Danh Sách Bảng Điểm Toàn Khoa <span id="tab-counter-toankhoa">({{ $ketQuas->total() }})</span>
        </a>
    </div>

    @if($viewMode === 'hoidong')
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <!-- TAB 1: TỔNG HỢP & ĐỐI CHIẾU ĐIỂM THEO HỘI ĐỒNG BẢO VỆ                  -->
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <form id="filter-form" method="GET" action="{{ route('truongkhoa.ketqua.index') }}" class="d-flex flex-wrap gap-2 align-items-center w-100">
                <input type="hidden" name="view" value="hoidong">

                <!-- Ô tìm kiếm (Wrapper tương đối, chừa chỗ cho icon kính lúp, không có nút xóa x) -->
                <div class="position-relative flex-grow-1 min-w-0" style="min-width: 260px;">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary" style="pointer-events: none; z-index: 4;"></i>
                    <input type="text" 
                           name="search_hd" 
                           id="input-search"
                           class="form-control rounded-3 shadow-xs" 
                           style="padding-left: 2.75rem !important; padding-right: 0.75rem !important; min-height: 42px !important;"
                           placeholder="Tìm theo tên hội đồng, phòng bảo vệ, mã hội đồng..." 
                           value="{{ request('search_hd') }}"
                           autocomplete="off">
                </div>

                <!-- Dropdown Học kỳ -->
                <select name="MaHocKy" id="select-hocky" class="form-select rounded-3 py-2 shadow-xs flex-grow-1 flex-md-grow-0" style="min-width: 180px; max-width: 250px; min-height: 42px !important;">
                    <option value="">-- Tất cả học kỳ --</option>
                    @foreach($hocKies as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }}
                        </option>
                    @endforeach
                </select>

                <!-- Nút Lọc tùy chọn (Submit trực tiếp không debounce) -->
                <button type="submit" id="btn-submit-filter" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold shadow-xs d-inline-flex align-items-center justify-content-center text-nowrap" style="min-height: 42px !important;">
                    <i class="fa-solid fa-filter me-2"></i> Lọc Hội Đồng
                </button>
            </form>
        </div>
    </div>

    <!-- Vùng kết quả Tab 1 (Cập nhật mượt mà qua AJAX) -->
    <div id="ketqua-result-container">
        @include('truongkhoa.ketqua.partials._hoidong_list')
    </div>

    @else
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <!-- TAB 2: BẢNG ĐIỂM SINH VIÊN TOÀN KHOA                                   -->
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <form id="filter-form" method="GET" action="{{ route('truongkhoa.ketqua.index') }}" class="d-flex flex-wrap gap-2 align-items-center w-100">
                <input type="hidden" name="view" value="toankhoa">

                <!-- Ô tìm kiếm (Wrapper tương đối, chừa chỗ cho icon kính lúp, không có nút xóa x) -->
                <div class="position-relative flex-grow-1 min-w-0" style="min-width: 240px;">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary" style="pointer-events: none; z-index: 4;"></i>
                    <input type="text" 
                           name="search" 
                           id="input-search"
                           class="form-control rounded-3 shadow-xs" 
                           style="padding-left: 2.75rem !important; padding-right: 0.75rem !important; min-height: 42px !important;"
                           placeholder="Tìm kiếm mã SV, họ tên hoặc đề tài..." 
                           value="{{ request('search') }}"
                           autocomplete="off">
                </div>

                <!-- Dropdown Bộ môn -->
                <select name="MaBoMon" class="form-select rounded-3 py-2 shadow-xs flex-grow-1 flex-md-grow-0" style="min-width: 170px; max-width: 230px; min-height: 42px !important;">
                    <option value="">-- Tất cả Bộ môn --</option>
                    @foreach($boMons as $bm)
                        <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                            {{ $bm->TenBoMon }}
                        </option>
                    @endforeach
                </select>

                <!-- Dropdown Học kỳ -->
                <select name="MaHocKy" id="select-hocky" class="form-select rounded-3 py-2 shadow-xs flex-grow-1 flex-md-grow-0" style="min-width: 160px; max-width: 210px; min-height: 42px !important;">
                    <option value="">-- Tất cả học kỳ --</option>
                    @foreach($hocKies as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }}
                        </option>
                    @endforeach
                </select>

                <!-- Dropdown Xếp loại -->
                <select name="xep_loai" class="form-select rounded-3 py-2 shadow-xs flex-grow-1 flex-md-grow-0" style="min-width: 160px; max-width: 200px; min-height: 42px !important;">
                    <option value="">-- Tất cả xếp loại --</option>
                    <option value="XuatSac" {{ request('xep_loai') == 'XuatSac' ? 'selected' : '' }}>Xuất sắc (>= 9.0)</option>
                    <option value="Gioi" {{ request('xep_loai') == 'Gioi' ? 'selected' : '' }}>Giỏi (8.0 - 8.9)</option>
                    <option value="Kha" {{ request('xep_loai') == 'Kha' ? 'selected' : '' }}>Khá (7.0 - 7.9)</option>
                    <option value="TrungBinh" {{ request('xep_loai') == 'TrungBinh' ? 'selected' : '' }}>Trung bình (5.0 - 6.9)</option>
                    <option value="KhongDat" {{ request('xep_loai') == 'KhongDat' ? 'selected' : '' }}>Không đạt (&lt; 5.0)</option>
                </select>

                <!-- Nút Lọc tùy chọn (Submit trực tiếp không debounce) -->
                <button type="submit" id="btn-submit-filter" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold shadow-xs d-inline-flex align-items-center justify-content-center text-nowrap" style="min-height: 42px !important;">
                    <i class="fa-solid fa-filter me-2"></i> Lọc
                </button>
            </form>
        </div>
    </div>

    <!-- Vùng kết quả Tab 2 (Cập nhật mượt mà qua AJAX) -->
    <div id="ketqua-result-container">
        @include('truongkhoa.ketqua.partials._toankhoa_table')
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filter-form');
    if (!filterForm) return;

    const searchInput = document.getElementById('input-search');
    const resultContainer = document.getElementById('ketqua-result-container');
    const selects = filterForm.querySelectorAll('select');
    const exportBtn = document.getElementById('btn-export-excel');

    let debounceTimer = null;
    let isComposing = false; // Quản lý trạng thái bộ gõ tiếng Việt (Telex / VNI)
    let currentAbortController = null;
    let lastQueryString = new URLSearchParams(new FormData(filterForm)).toString();

    // 1. Lắng nghe ô tìm kiếm (Enter để lọc ngay, IME tiếng Việt, input debounce)
    if (searchInput) {
        // Nhấn Enter để lọc ngay lập tức (bỏ qua debounce)
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(debounceTimer);
                triggerFetch(true);
            }
        });

        // Quản lý IME tiếng Việt (chống mất dấu khi gõ Telex / VNI)
        searchInput.addEventListener('compositionstart', function () {
            isComposing = true;
        });

        searchInput.addEventListener('compositionend', function () {
            isComposing = false;
            scheduleFetch(400);
        });

        searchInput.addEventListener('input', function (e) {
            if (isComposing || e.isComposing) return;
            scheduleFetch(400);
        });
    }
        });
    }

    // 3. Lắng nghe Dropdown change (Lọc ngay lập tức, không debounce)
    selects.forEach(function (select) {
        select.addEventListener('change', function () {
            triggerFetch(true);
        });
    });

    // 4. Submit form trực tiếp qua nút "Lọc"
    filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(debounceTimer);
        triggerFetch(true);
    });

    // 5. Hàm hẹn giờ debounce
    function scheduleFetch(delay) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            triggerFetch(true);
        }, delay);
    }

    // 6. Thực hiện Fetch AJAX cập nhật vùng kết quả
    function triggerFetch(resetPage = true, customUrl = null) {
        clearTimeout(debounceTimer);

        let url;
        if (customUrl) {
            url = new URL(customUrl, window.location.origin);
        } else {
            url = new URL(filterForm.action, window.location.origin);
            const formData = new FormData(filterForm);
            for (let [key, val] of formData.entries()) {
                val = val.trim();
                if (val !== '') {
                    url.searchParams.set(key, val);
                }
            }
            if (resetPage) {
                url.searchParams.delete('page');
            }
        }

        const currentQuery = url.searchParams.toString();
        // Không gửi lại request nếu bộ lọc hoàn toàn không thay đổi (ngoại trừ khi bấm link phân trang)
        if (!customUrl && currentQuery === lastQueryString) {
            return;
        }
        lastQueryString = currentQuery;

        // Cập nhật URL trình duyệt qua history.replaceState mà không làm reload trang
        window.history.replaceState({ path: url.toString() }, '', url.toString());

        // Hủy request cũ nếu còn đang dở dang
        if (currentAbortController) {
            currentAbortController.abort();
        }
        currentAbortController = new AbortController();

        // Hiệu ứng mờ nhẹ vùng kết quả khi đang tải (không nhảy giật layout)
        if (resultContainer) {
            resultContainer.style.opacity = '0.55';
            resultContainer.style.pointerEvents = 'none';
            resultContainer.style.transition = 'opacity 0.2s ease';
        }

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            signal: currentAbortController.signal
        })
        .then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function (data) {
            if (resultContainer && data.html !== undefined) {
                resultContainer.innerHTML = data.html;
            }

            // Cập nhật số đếm trên Header Tab
            if (data.total !== undefined) {
                const viewMode = data.viewMode || 'hoidong';
                if (viewMode === 'hoidong') {
                    const counter = document.getElementById('tab-counter-hoidong');
                    if (counter) counter.textContent = `(${data.total})`;
                } else {
                    const counter = document.getElementById('tab-counter-toankhoa');
                    if (counter) counter.textContent = `(${data.total})`;
                }
            }

            // Cập nhật các thẻ KPI thống kê trên cùng nếu có dữ liệu mới
            if (data.stats) {
                updateStats(data.stats);
            }

            // Cập nhật link xuất Excel đồng bộ bộ lọc
            if (exportBtn) {
                const exportUrl = new URL(exportBtn.href, window.location.origin);
                for (let [k, v] of url.searchParams.entries()) {
                    exportUrl.searchParams.set(k, v);
                }
                exportBtn.href = exportUrl.toString();
            }
        })
        .catch(function (err) {
            if (err.name === 'AbortError') {
                return; // Bỏ qua vì đã có request mới hơn
            }
            console.error('Fetch filter error:', err);
            if (resultContainer) {
                resultContainer.innerHTML = `
                    <div class="col-12 py-3">
                        <div class="alert alert-danger rounded-3 d-flex align-items-center mb-0">
                            <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
                            <div>Đã xảy ra lỗi khi tải dữ liệu (${err.message}). Vui lòng bấm <strong>Lọc</strong> để thử lại.</div>
                        </div>
                    </div>
                `;
            }
        })
        .finally(function () {
            if (resultContainer) {
                resultContainer.style.opacity = '1';
                resultContainer.style.pointerEvents = 'auto';
            }
        });
    }

    // Cập nhật số liệu các card thống kê KPI
    function updateStats(stats) {
        const total = stats.total || 0;
        const totalEl = document.getElementById('stat-total');
        if (totalEl) totalEl.textContent = total;

        const xuatSacEl = document.getElementById('stat-xuat-sac');
        if (xuatSacEl) xuatSacEl.textContent = stats.xuat_sac || 0;
        const xuatSacPctEl = document.getElementById('stat-xuat-sac-pct');
        if (xuatSacPctEl) {
            xuatSacPctEl.textContent = (total > 0 ? ((stats.xuat_sac / total) * 100).toFixed(1) : 0) + '% tổng số';
        }

        const gioiEl = document.getElementById('stat-gioi');
        if (gioiEl) gioiEl.textContent = stats.gioi || 0;
        const gioiPctEl = document.getElementById('stat-gioi-pct');
        if (gioiPctEl) {
            gioiPctEl.textContent = (total > 0 ? ((stats.gioi / total) * 100).toFixed(1) : 0) + '% tổng số';
        }

        const khaEl = document.getElementById('stat-kha');
        if (khaEl) khaEl.textContent = stats.kha || 0;
        const khaPctEl = document.getElementById('stat-kha-pct');
        if (khaPctEl) {
            khaPctEl.textContent = (total > 0 ? ((stats.kha / total) * 100).toFixed(1) : 0) + '% tổng số';
        }

        const tbKdEl = document.getElementById('stat-trung-binh-khong-dat');
        if (tbKdEl) tbKdEl.textContent = (stats.trung_binh || 0) + (stats.khong_dat || 0);
        const tbKdDetailEl = document.getElementById('stat-tb-kd-detail');
        if (tbKdDetailEl) {
            tbKdDetailEl.textContent = `TB: ${stats.trung_binh || 0} | Không đạt: ${stats.khong_dat || 0}`;
        }
    }

    // 7. Bắt sự kiện click phân trang AJAX
    if (resultContainer) {
        resultContainer.addEventListener('click', function (e) {
            const paginationLink = e.target.closest('.ajax-pagination-wrapper a, .pagination a');
            if (paginationLink && paginationLink.href) {
                e.preventDefault();
                triggerFetch(false, paginationLink.href);
            }
        });
    }

    // 8. Lắng nghe nút Back / Forward trình duyệt để tải lại state tương ứng
    window.addEventListener('popstate', function () {
        window.location.reload();
    });
});
</script>
@endpush
