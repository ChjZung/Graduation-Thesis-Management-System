<div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-2 mb-4" id="kpiCardsContainer">
    <div class="col">
        <div class="card border shadow-none rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Tổng SV tham gia</div>
                <h4 class="fw-bold mt-1 mb-0 text-dark" id="kpiTongSVThamGia">{{ number_format($data['kpi']['tongSVThamGia']) }}</h4>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card border shadow-none rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Tổng đề tài</div>
                <h4 class="fw-bold mt-1 mb-0 text-dark" id="kpiTongDeTai">{{ number_format($data['kpi']['tongDeTai']) }}</h4>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card border shadow-none rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Tổng hội đồng</div>
                <h4 class="fw-bold mt-1 mb-0 text-dark" id="kpiTongHoiDong">{{ number_format($data['kpi']['tongHoiDong']) }}</h4>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card border shadow-none rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Đã phân hội đồng</div>
                <h4 class="fw-bold mt-1 mb-0 text-dark" id="kpiTongSVDaPhanCong">{{ number_format($data['kpi']['tongSVDaPhanCongHD']) }}</h4>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card border shadow-none rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Chưa phân hội đồng</div>
                <h4 class="fw-bold mt-1 mb-0 text-warning" id="kpiTongSVChuaPhanCong">{{ number_format($data['kpi']['tongSVChuaPhanCongHD']) }}</h4>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card border shadow-none rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Hoàn thành chấm</div>
                <h4 class="fw-bold mt-1 mb-0 text-dark" id="kpiTongSVHoanThanhCham">{{ number_format($data['kpi']['tongSVHoanThanhCham']) }}</h4>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card border shadow-none rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Sinh viên đạt</div>
                <h4 class="fw-bold mt-1 mb-0 text-success" id="kpiTongSVDat">{{ number_format($data['kpi']['tongSVDat']) }}</h4>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card border shadow-none rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Chưa đạt</div>
                <h4 class="fw-bold mt-1 mb-0 text-danger" id="kpiTongSVChuaDat">{{ number_format($data['kpi']['tongSVChuaDat']) }}</h4>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card border shadow-none rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Chưa có kết quả</div>
                <h4 class="fw-bold mt-1 mb-0 text-warning" id="kpiTongSVChuaCoKQ">{{ number_format($data['kpi']['tongSVChuaCoKQ']) }}</h4>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card border shadow-none rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Tỷ lệ đạt / Điểm TB</div>
                <h4 class="fw-bold mt-1 mb-0 text-primary" style="font-size: 1.2rem;" id="kpiTyLeVaDiemTB">
                    {{ number_format($data['kpi']['tyLeDat'], 1) }}% 
                    <span class="fs-6 text-muted fw-normal">| {{ number_format($data['kpi']['diemTB'], 2) }}</span>
                </h4>
            </div>
        </div>
    </div>
</div>