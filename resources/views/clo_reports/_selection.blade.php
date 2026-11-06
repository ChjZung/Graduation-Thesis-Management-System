<p class="text-muted small mb-3">Chọn hội đồng để xuất riêng hội đồng đó. Để trống hội đồng để xuất theo đợt; để trống cả hai để xuất toàn ngành trong học kỳ.</p>
    <form method="GET" action="{{ route('clo.reports.export') }}" class="row g-3" id="cloRoundExportForm" data-clo-round-form data-council-member="{{ auth()->user()->vaiTro?->TenVaiTro === 'Giảng viên' ? '1' : '0' }}" data-options="{{ json_encode($options) }}" data-selected-round="{{ old('DotBaoVe', request('DotBaoVe', '')) }}" data-selected-council="{{ old('MaHoiDong', request('MaHoiDong', '')) }}">
        <div class="col-12 col-md-6"><label class="form-label" for="cloSemester">Học kỳ <span class="text-danger">*</span></label>
            <select name="MaHocKy" id="cloSemester" class="form-select" required><option value="">Chọn học kỳ</option>
                @foreach($semesters as $semester)<option value="{{ $semester->MaHocKy }}" @selected(old('MaHocKy', request('MaHocKy')) === $semester->MaHocKy)>{{ $semester->TenDayDu }}</option>@endforeach
            </select>
        </div>
        <div class="col-12 col-md-6"><label class="form-label" for="cloMajor">Ngành đào tạo <span class="text-danger">*</span></label>
            <select name="MaNganh" id="cloMajor" class="form-select" required><option value="">Chọn ngành</option>
                @foreach($majors as $major)<option value="{{ $major->MaNganh }}" @selected(old('MaNganh', request('MaNganh')) === $major->MaNganh)>{{ $major->TenNganh }}</option>@endforeach
            </select>
        </div>
        <div class="col-12 col-md-6"><label class="form-label" for="cloRound">Đợt bảo vệ <small class="text-muted">(không bắt buộc)</small></label><select name="DotBaoVe" id="cloRound" class="form-select" disabled><option value="">Tất cả đợt trong học kỳ</option></select></div>
        <div class="col-12 col-md-6"><label class="form-label" for="cloCouncil">Hội đồng <small class="text-muted">(không bắt buộc)</small></label><select name="MaHoiDong" id="cloCouncil" class="form-select" disabled><option value="">Không chọn hội đồng riêng</option></select><div class="form-text">Giảng viên chỉ được chọn hội đồng mình tham gia.</div></div>
        <div class="col-12 col-md-6"><label class="form-label" for="cloKind">Mẫu báo cáo</label><select name="loai" id="cloKind" class="form-select"><option value="KLCN">Khóa luận Cử nhân</option><option value="KLKS" @selected(old('loai', request('loai')) === 'KLKS')>Khóa luận Kỹ sư</option></select></div>
        <div class="col-12 col-md-6"><label class="form-label" for="cloFormat">Định dạng</label><select name="format" id="cloFormat" class="form-select"><option value="xlsx_school" @selected(request('format') === 'xlsx_school')>Excel mẫu trường · 11 sheet</option><option value="pdf" @selected(request('format') === 'pdf')>PDF</option></select></div>
        <div class="col-12"><div class="alert alert-light border mb-0 small" data-scope-summary role="status"></div></div>
        <div class="col-12 d-flex flex-wrap gap-2 align-items-center"><button type="submit" class="btn btn-primary" id="cloExportButton" disabled>Xuất báo cáo tổng hợp CĐR/CLO</button>@if(in_array(auth()->user()->vaiTro?->TenVaiTro, ['Admin', 'Trưởng khoa'], true))
            <button type="submit" class="btn btn-outline-primary" data-clo-preview disabled>Xem báo cáo và đối chiếu CLO</button>
        @endif<span class="small text-muted">Chỉ xuất chính thức khi các sinh viên trong phạm vi đã có đủ phiếu chấm hoàn thành.</span></div>
    </form>
