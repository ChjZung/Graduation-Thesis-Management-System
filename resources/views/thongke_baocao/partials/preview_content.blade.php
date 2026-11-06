<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 bg-white p-2.5 border rounded-3">
    <div class="d-flex align-items-center gap-2 flex-wrap small">
        <span class="badge bg-primary text-uppercase">{{ $mauBaoCao ?? 'MAU_01' }}</span>
        <span class="text-muted">|</span>
        <span class="fw-semibold">{{ $data['hocKy']->TenDayDu ?? 'Học kỳ' }}</span>
        @if(!empty($data['filters']['DotBaoVe']))
            <span>&bull; Đợt: <strong>{{ $data['filters']['DotBaoVe'] }}</strong></span>
        @endif
        @if(!empty($data['filters']['MaKhoa']))
            <span>&bull; Khoa: <strong>{{ $data['filters']['MaKhoa'] }}</strong></span>
        @endif
        @if(!empty($data['filters']['MaNganh']))
            <span>&bull; Ngành: <strong>{{ $data['filters']['MaNganh'] }}</strong></span>
        @endif
        @if(!empty($data['filters']['MaHoiDong']))
            <span>&bull; HĐ: <strong>{{ $data['filters']['MaHoiDong'] }}</strong></span>
        @endif
        <span class="badge bg-light text-dark border ms-1">{{ count($data['danhSachTatCaSV']) }} SV</span>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary py-1" onclick="document.getElementById('tab-preview-settings-btn').click();">
            <i class="fa-solid fa-sliders me-1"></i> Tùy chỉnh
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary py-1" onclick="window.print();">
            <i class="fa-solid fa-print me-1"></i> In
        </button>
    </div>
</div>

<div class="a4-preview-paper {{ ($config['kho_giay'] ?? 'landscape') === 'portrait' ? 'a4-portrait' : 'a4-landscape' }} mx-auto bg-white p-4 p-md-5 border shadow-sm">
    
    {{-- Header Quốc hiệu - Tiêu ngữ & Đơn vị đào tạo --}}
    <table class="w-100 mb-3" style="border: none; font-size: 0.82rem;">
        <tr>
            <td style="width: 45%; vertical-align: top; text-align: center; border: none; line-height: 1.35;">
                <div class="text-uppercase" style="font-size: 0.74rem;">BỘ CÔNG THƯƠNG</div>
                <div class="fw-bold text-uppercase" style="font-size: 0.8rem;">TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH</div>
                <div class="fw-semibold text-uppercase" style="font-size: 0.75rem;">
                    {{ !empty($data['filters']['MaKhoa']) ? 'KHOA ' . $data['filters']['MaKhoa'] : 'HỘI ĐỒNG KHÓA LUẬN TỐT NGHIỆP' }}
                </div>
                <div style="width: 70px; height: 1px; background: #000; margin: 3px auto 0;"></div>
            </td>
            <td style="width: 55%; vertical-align: top; text-align: center; border: none; line-height: 1.35;">
                <div class="fw-bold text-uppercase" style="font-size: 0.8rem;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
                <div class="fw-semibold" style="font-size: 0.8rem;">Độc lập - Tự do - Hạnh phúc</div>
                <div style="width: 110px; height: 1px; background: #000; margin: 3px auto 0;"></div>
                @if($config['show_export_date'] ?? true)
                    <div class="mt-2 fst-italic" style="font-size: 0.75rem;">
                        TP. Hồ Chí Minh, ngày {{ $data['thoiDiemXuat']?->format('d') ?? date('d') }} 
                        tháng {{ $data['thoiDiemXuat']?->format('m') ?? date('m') }} 
                        năm {{ $data['thoiDiemXuat']?->format('Y') ?? date('Y') }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Tiêu đề Báo cáo theo đúng Biểu mẫu đang chọn --}}
    <div class="text-center my-3">
        <h5 class="fw-bold text-uppercase text-dark mb-1" style="font-size: 1.05rem; color: #003366 !important;">
            @switch($mauBaoCao)
                @case('mau_02') BÁO CÁO THỐNG KÊ KẾT QUẢ KHÓA LUẬN THEO NGÀNH ĐÀO TẠO @break
                @case('mau_03') DANH SÁCH SINH VIÊN ĐẠT KHÓA LUẬN TỐT NGHIỆP @break
                @case('mau_04') DANH SÁCH SINH VIÊN CHƯA ĐẠT KHÓA LUẬN TỐT NGHIỆP @break
                @case('mau_05') DANH SÁCH SINH VIÊN CHƯA CÓ KẾT QUẢ ĐÁNH GIÁ (ĐANG CHẤM) @break
                @case('mau_06') BÁO CÁO THỐNG KÊ ĐIỂM SỐ CHI TIẾT THEO HỘI ĐỒNG BẢO VỆ @break
                @case('mau_07') BÁO CÁO PHÂN BỐ ĐIỂM SỐ KHÓA LUẬN TỐT NGHIỆP @break
                @case('mau_08') BÁO CÁO TÌNH HÌNH TỔ CHỨC KHÓA LUẬN TỐT NGHIỆP @break
                @case('mau_09') BÁO CÁO THỐNG KÊ KHÓA LUẬN THEO BỘ MÔN @break
                @case('mau_10') BÁO CÁO THỐNG KÊ KHÓA LUẬN THEO HỌC PHẦN KHÓA LUẬN @break
                @case('mau_11') BẢNG TỔNG HỢP ĐIỂM VÀ CHUẨN ĐẦU RA KHÓA LUẬN @break
                @default BÁO CÁO TỔNG HỢP KẾT QUẢ KHÓA LUẬN TỐT NGHIỆP THEO ĐỢT &amp; HỘI ĐỒNG
            @endswitch
        </h5>
        <div class="small text-secondary">
            Học kỳ: <strong>{{ $data['hocKy']->TenHocKy ?? 'N/A' }}</strong> &bull; Năm học: <strong>{{ $data['hocKy']->NamHoc ?? 'N/A' }}</strong>
            @if(!empty($data['filters']['DotBaoVe'])) &bull; Đợt: <strong>{{ $data['filters']['DotBaoVe'] }}</strong> @endif
            @if(!empty($data['filters']['MaNganh'])) &bull; Ngành: <strong>{{ $data['filters']['MaNganh'] }}</strong> @endif
            @if(!empty($data['filters']['MaBoMon'])) &bull; Bộ môn: <strong>{{ $data['filters']['MaBoMon'] }}</strong> @endif
            @if(!empty($data['filters']['MaHoiDong'])) &bull; Hội đồng: <strong>{{ $data['filters']['MaHoiDong'] }}</strong> @endif
        </div>
    </div>

    {{-- Cảnh báo chưa đủ điểm / Chưa hoàn tất chấm điểm --}}
    {{-- Thẻ KPI Tóm tắt --}}
    @if($config['show_kpi'] ?? true)
        <div class="p-2 mb-3 border bg-light" style="font-size: 0.76rem;">
            <div class="row g-2 text-center">
                <div class="col">Tổng SV: <strong>{{ number_format($data['kpi']['tongSVThamGia'] ?? 0) }}</strong></div>
                <div class="col border-start">Đề tài: <strong>{{ number_format($data['kpi']['tongDeTai'] ?? 0) }}</strong></div>
                <div class="col border-start">Hội đồng: <strong>{{ number_format($data['kpi']['tongHoiDong'] ?? 0) }}</strong></div>
                <div class="col border-start">Đạt: <strong class="text-success">{{ number_format($data['kpi']['tongSVDat'] ?? 0) }}</strong></div>
                <div class="col border-start">Chưa đạt: <strong class="text-danger">{{ number_format($data['kpi']['tongSVChuaDat'] ?? 0) }}</strong></div>
                <div class="col border-start">Chưa có KQ: <strong>{{ number_format($data['kpi']['tongSVChuaCoKQ'] ?? 0) }}</strong></div>
                <div class="col border-start">Điểm TB: <strong class="text-primary">{{ number_format($data['kpi']['diemTB'] ?? 0, 2) }}</strong></div>
                <div class="col border-start">Tỷ lệ Đạt: <strong class="text-success">{{ number_format($data['kpi']['tyLeDat'] ?? 0, 1) }}%</strong></div>
            </div>
        </div>
    @endif

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- NỘI DUNG BẢNG VĂN BẢN ĐÚNG THEO MẪU BÁO CÁO ĐANG CHỌN (1 -> 10)     --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    @switch($mauBaoCao)
        @case('mau_11')
            @include('thongke_baocao.partials.diem_clo_table', ['rows' => $data['danhSachTatCaSV']])
            @break

        {{-- ───────────────────────────────────────────────────────────── --}}
        {{-- MẪU 02: BÁO CÁO THỐNG KÊ KẾT QUẢ THEO NGÀNH ĐÀO TẠO          --}}
        {{-- ───────────────────────────────────────────────────────────── --}}
        @case('mau_02')
            <div class="mb-3">
                <div class="fw-bold text-dark mb-1" style="font-size: 0.8rem; color: #003366;">1. BẢNG TỔNG HỢP SỐ LIỆU THEO TỪNG NGÀNH:</div>
                <table class="table table-sm table-bordered align-middle text-center mb-0" style="font-size: 0.74rem;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35px;">STT</th>
                            <th style="width: 90px;">Mã Ngành</th>
                            <th class="text-start">Ngành Đào Tạo</th>
                            <th style="width: 65px;">Tổng SV</th>
                            <th style="width: 60px;">Đạt</th>
                            <th style="width: 65px;">Chưa Đạt</th>
                            <th style="width: 70px;">Chưa Có KQ</th>
                            <th style="width: 75px;">Tỷ Lệ Đạt</th>
                            <th style="width: 65px;">Điểm TB</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $sttNg = 1; @endphp
                        @forelse($data['statsTheoNganh'] as $stNg)
                            @php
                                $soDat = $stNg['soDat'] ?? 0;
                                $soKhongDat = $stNg['soKhongDat'] ?? 0;
                                $tyLe = ($soDat + $soKhongDat) > 0 ? round(($soDat / ($soDat + $soKhongDat)) * 100, 2) : 0;
                                $scores = $stNg['scores'] ?? [];
                                $tb = !empty($scores) ? round(array_sum($scores) / count($scores), 2) : 0;
                            @endphp
                            <tr>
                                <td>{{ $sttNg++ }}</td>
                                <td class="fw-semibold">{{ $stNg['maNganh'] ?? '-' }}</td>
                                <td class="text-start fw-bold">{{ $stNg['tenNganh'] ?? '-' }}</td>
                                <td>{{ $stNg['soSV'] ?? 0 }}</td>
                                <td class="text-success fw-bold">{{ $soDat }}</td>
                                <td class="text-danger">{{ $soKhongDat }}</td>
                                <td>{{ $stNg['soChuaXong'] ?? 0 }}</td>
                                <td class="text-success fw-bold">{{ number_format($tyLe, 2) }}%</td>
                                <td class="text-primary fw-bold">{{ number_format($tb, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-muted py-3">Không có dữ liệu ngành phù hợp.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-start">TỔNG CỘNG TOÀN KHÓA:</td>
                            <td>{{ number_format($data['kpi']['tongSVThamGia'] ?? 0) }}</td>
                            <td class="text-success">{{ number_format($data['kpi']['tongSVDat'] ?? 0) }}</td>
                            <td class="text-danger">{{ number_format($data['kpi']['tongSVChuaDat'] ?? 0) }}</td>
                            <td>{{ number_format($data['kpi']['tongSVChuaCoKQ'] ?? 0) }}</td>
                            <td class="text-success">{{ number_format($data['kpi']['tyLeDat'] ?? 0, 1) }}%</td>
                            <td class="text-primary">{{ number_format($data['kpi']['diemTB'] ?? 0, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($config['show_detail'] ?? true)
                <div class="mb-3">
                    <div class="fw-bold text-dark mb-1" style="font-size: 0.8rem; color: #003366;">2. DANH SÁCH CHI TIẾT SINH VIÊN THEO NGÀNH:</div>
                    <table class="table table-sm table-bordered align-middle text-center mb-0" style="font-size: 0.72rem;">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th style="width: 35px;">STT</th>
                                <th>MSSV</th>
                                <th class="text-start">Họ và Tên</th>
                                <th>Lớp</th>
                                <th class="text-start">Ngành</th>
                                <th class="text-start">Tên Đề Tài</th>
                                <th>GVHD</th>
                                <th>Điểm 10</th>
                                <th>Chữ</th>
                                <th>Xếp Loại</th>
                                <th>Kết Quả</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['danhSachTatCaSV'] as $index => $sv)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-semibold">{{ $sv['maSV'] ?? '-' }}</td>
                                    <td class="text-start text-nowrap fw-semibold">{{ $sv['hoTen'] ?? '-' }}</td>
                                    <td class="text-nowrap">{{ $sv['tenLop'] ?? '-' }}</td>
                                    <td class="text-start text-nowrap">{{ $sv['tenNganh'] ?? '-' }}</td>
                                    <td class="text-start" style="max-width: 260px;">{{ $sv['tenDeTai'] ?? '-' }}</td>
                                    <td>{{ \App\Services\ThongKeService::formatLecturerName($sv['tenGVHD'] ?? $sv['gvhd'] ?? null) }}</td>
                                    <td class="fw-bold {{ ($sv['isDat'] ?? false) ? 'text-success' : 'text-danger' }}">
                                        {{ $sv['diemTongKet'] !== null ? number_format($sv['diemTongKet'], 2) : '--' }}
                                    </td>
                                    <td class="fw-bold">{{ $sv['diemChu'] ?? '--' }}</td>
                                    <td>{{ $sv['xepLoai'] ?? '--' }}</td>
                                    <td>
                                        @if($sv['isDuPhieu'] ?? false)
                                            <span class="{{ ($sv['isDat'] ?? false) ? 'text-success fw-bold' : 'text-danger fw-bold' }}">
                                                {{ ($sv['isDat'] ?? false) ? 'Đạt' : 'Chưa đạt' }}
                                            </span>
                                        @else
                                            <span class="text-muted">Chưa có KQ</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-muted py-3">Không có sinh viên nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
            @break

        {{-- ───────────────────────────────────────────────────────────── --}}
        {{-- MẪU 03: DANH SÁCH SINH VIÊN ĐẠT KHÓA LUẬN TỐT NGHIỆP          --}}
        {{-- ───────────────────────────────────────────────────────────── --}}
        @case('mau_03')
            @php
                $dsSVDat = array_values(array_filter($data['danhSachTatCaSV'], fn($s) => ($s['isDuPhieu'] ?? false) && ($s['isDat'] ?? false)));
            @endphp
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold text-dark" style="font-size: 0.8rem; color: #003366;">
                        DANH SÁCH SINH VIÊN CÔNG NHẬN ĐẠT KHÓA LUẬN:
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                        Tổng số: {{ count($dsSVDat) }} sinh viên (Đạt {{ number_format($data['kpi']['tyLeDat'] ?? 0, 1) }}%)
                    </span>
                </div>

                <table class="table table-sm table-bordered align-middle text-center mb-0" style="font-size: 0.72rem;">
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th style="width: 35px;">STT</th>
                            <th>MSSV</th>
                            <th class="text-start">Họ và Tên</th>
                            <th>Lớp</th>
                            <th class="text-start">Ngành</th>
                            <th class="text-start">Tên Đề Tài Khóa Luận</th>
                            <th>GVHD</th>
                            <th>Hội Đồng</th>
                            <th>Điểm 10</th>
                            <th>Điểm 4</th>
                            <th>Chữ</th>
                            <th>Xếp Loại</th>
                            <th>Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dsSVDat as $index => $sv)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $sv['maSV'] ?? '-' }}</td>
                                <td class="text-start text-nowrap fw-semibold">{{ $sv['hoTen'] ?? '-' }}</td>
                                <td class="text-nowrap">{{ $sv['tenLop'] ?? '-' }}</td>
                                <td class="text-start text-nowrap">{{ $sv['tenNganh'] ?? '-' }}</td>
                                <td class="text-start" style="max-width: 250px;">{{ $sv['tenDeTai'] ?? '-' }}</td>
                                <td>{{ \App\Services\ThongKeService::formatLecturerName($sv['tenGVHD'] ?? $sv['gvhd'] ?? null) }}</td>
                                <td class="text-nowrap">{{ $sv['tenHoiDong'] ?? '-' }}</td>
                                <td class="fw-bold text-success">{{ number_format($sv['diemTongKet'], 2) }}</td>
                                <td>{{ $sv['diemHe4'] !== null ? number_format($sv['diemHe4'], 2) : '--' }}</td>
                                <td class="fw-bold">{{ $sv['diemChu'] ?? '--' }}</td>
                                <td>{{ $sv['xepLoai'] ?? '--' }}</td>
                                <td class="fw-bold text-success">Đạt</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-muted py-3">Không có sinh viên nào đạt theo tiêu chí đã chọn.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(!empty($dsSVDat))
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="8" class="text-start">TỔNG CỘNG SINH VIÊN ĐẠT KHÓA LUẬN:</td>
                            <td class="text-success">{{ number_format($data['kpi']['diemTB'] ?? 0, 2) }}</td>
                            <td colspan="4" class="text-success">{{ count($dsSVDat) }} SV (100%)</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @break

        {{-- ───────────────────────────────────────────────────────────── --}}
        {{-- MẪU 04: DANH SÁCH SINH VIÊN CHƯA ĐẠT KHÓA LUẬN                --}}
        {{-- ───────────────────────────────────────────────────────────── --}}
        @case('mau_04')
            @php
                $dsSVChuaDat = array_values(array_filter($data['danhSachTatCaSV'], fn($s) => ($s['isDuPhieu'] ?? false) && !($s['isDat'] ?? false)));
            @endphp
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold text-dark" style="font-size: 0.8rem; color: #003366;">
                        DANH SÁCH SINH VIÊN CHƯA ĐẠT KHÓA LUẬN (ĐIỂM &lt; 4.0):
                    </span>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                        Tổng số: {{ count($dsSVChuaDat) }} sinh viên
                    </span>
                </div>

                <table class="table table-sm table-bordered align-middle text-center mb-0" style="font-size: 0.72rem;">
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th style="width: 35px;">STT</th>
                            <th>MSSV</th>
                            <th class="text-start">Họ và Tên</th>
                            <th>Lớp</th>
                            <th class="text-start">Ngành</th>
                            <th class="text-start">Tên Đề Tài Khóa Luận</th>
                            <th>GVHD</th>
                            <th>Hội Đồng</th>
                            <th>Điểm 10</th>
                            <th>Chữ</th>
                            <th>Xếp Loại</th>
                            <th>Ghi Chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dsSVChuaDat as $index => $sv)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $sv['maSV'] ?? '-' }}</td>
                                <td class="text-start text-nowrap fw-semibold">{{ $sv['hoTen'] ?? '-' }}</td>
                                <td class="text-nowrap">{{ $sv['tenLop'] ?? '-' }}</td>
                                <td class="text-start text-nowrap">{{ $sv['tenNganh'] ?? '-' }}</td>
                                <td class="text-start" style="max-width: 250px;">{{ $sv['tenDeTai'] ?? '-' }}</td>
                                <td>{{ \App\Services\ThongKeService::formatLecturerName($sv['tenGVHD'] ?? $sv['gvhd'] ?? null) }}</td>
                                <td class="text-nowrap">{{ $sv['tenHoiDong'] ?? '-' }}</td>
                                <td class="fw-bold text-danger">{{ number_format($sv['diemTongKet'], 2) }}</td>
                                <td class="fw-bold text-danger">{{ $sv['diemChu'] ?? 'F' }}</td>
                                <td>{{ $sv['xepLoai'] ?? 'Kém' }}</td>
                                <td class="text-danger fw-semibold">Chưa đạt (điểm hoặc CLO)</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-muted py-3">Không có sinh viên nào chưa đạt theo tiêu chí đã chọn.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @break

        {{-- ───────────────────────────────────────────────────────────── --}}
        {{-- MẪU 05: DANH SÁCH SINH VIÊN CHƯA CÓ KẾT QUẢ ĐÁNH GIÁ (ĐANG CHẤM)--}}
        {{-- ───────────────────────────────────────────────────────────── --}}
        @case('mau_05')
            @php
                $dsChuaKQ = array_values(array_filter($data['danhSachTatCaSV'], fn($s) => !($s['isDuPhieu'] ?? false)));
            @endphp
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold text-dark" style="font-size: 0.8rem; color: #003366;">
                        DANH SÁCH SINH VIÊN CHƯA HOÀN TẤT ĐỦ CÁC PHIẾU ĐÁNH GIÁ:
                    </span>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                        Tổng số: {{ count($dsChuaKQ) }} sinh viên đang chờ chấm
                    </span>
                </div>

                <table class="table table-sm table-bordered align-middle text-center mb-0" style="font-size: 0.72rem;">
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th style="width: 35px;">STT</th>
                            <th>MSSV</th>
                            <th class="text-start">Họ và Tên</th>
                            <th>Lớp</th>
                            <th class="text-start">Ngành</th>
                            <th class="text-start">Tên Đề Tài Khóa Luận</th>
                            <th>GVHD</th>
                            <th>Hội Đồng</th>
                            <th>Điểm GVHD</th>
                            <th>Điểm GVPB</th>
                            <th>Điểm HĐ</th>
                            <th>Tình Trạng Phiếu Chấm</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dsChuaKQ as $index => $sv)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $sv['maSV'] ?? '-' }}</td>
                                <td class="text-start text-nowrap fw-semibold">{{ $sv['hoTen'] ?? '-' }}</td>
                                <td class="text-nowrap">{{ $sv['tenLop'] ?? '-' }}</td>
                                <td class="text-start text-nowrap">{{ $sv['tenNganh'] ?? '-' }}</td>
                                <td class="text-start" style="max-width: 250px;">{{ $sv['tenDeTai'] ?? '-' }}</td>
                                <td>{{ \App\Services\ThongKeService::formatLecturerName($sv['tenGVHD'] ?? $sv['gvhd'] ?? null) }}</td>
                                <td class="text-nowrap">{{ $sv['tenHoiDong'] ?? '-' }}</td>
                                <td>{{ $sv['diemGVHD'] !== null ? number_format($sv['diemGVHD'], 2) : 'Chưa có' }}</td>
                                <td>{{ $sv['diemGVPB'] !== null ? number_format($sv['diemGVPB'], 2) : 'Chưa có' }}</td>
                                <td>{{ $sv['diemHD'] !== null ? number_format($sv['diemHD'], 2) : 'Chưa có' }}</td>
                                <td>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                        {{ !empty($sv['missingScores']) ? 'Thiếu: ' . implode(', ', $sv['missingScores']) : 'Đang chấm' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-muted py-3">Tất cả sinh viên đều đã hoàn tất chấm điểm đủ các phiếu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @break

        {{-- ───────────────────────────────────────────────────────────── --}}
        {{-- MẪU 06: BÁO CÁO THỐNG KÊ ĐIỂM SỐ THEO HỘI ĐỒNG BẢO VỆ         --}}
        {{-- ───────────────────────────────────────────────────────────── --}}
        @case('mau_06')
            @php $sttGlobal = 1; @endphp
            @forelse($data['danhSachHoiDong'] as $hdData)
                <div class="mb-3">
                    <div class="p-2 mb-1 bg-light border fw-bold text-dark d-flex justify-content-between align-items-center flex-wrap gap-2" style="font-size: 0.78rem;">
                        <div>
                            <span class="text-primary">HỘI ĐỒNG: {{ mb_strtoupper($hdData['hoiDong']->TenHoiDong ?? 'HỘI ĐỒNG') }}</span>
                            <span class="text-muted fw-normal ms-2">
                                &bull; Ngành: <strong>{{ $hdData['hoiDong']->nganh?->TenNganh ?? 'Chung' }}</strong>
                                &bull; Phòng: <strong>{{ $hdData['hoiDong']->DiaDiem ?? 'HUIT' }}</strong>
                            </span>
                        </div>
                        <div class="small fw-normal text-secondary">
                            CT: <strong>{{ $hdData['ct']->giangVien?->HoTen ?? 'Chưa rõ' }}</strong> &bull;
                            TK: <strong>{{ $hdData['tk']->giangVien?->HoTen ?? 'Chưa rõ' }}</strong> &bull;
                            UV: <strong>{{ $hdData['uvs']->pluck('giangVien.HoTen')->filter()->join(', ') ?: 'Không có' }}</strong>
                        </div>
                    </div>

                    <table class="table table-sm table-bordered align-middle text-center mb-0" style="font-size: 0.72rem;">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th style="width: 35px;">STT</th>
                                <th>MSSV</th>
                                <th class="text-start">Họ và Tên</th>
                                <th>Lớp</th>
                                <th class="text-start">Tên Đề Tài Khóa Luận</th>
                                <th>Điểm CT</th>
                                <th>Điểm TK</th>
                                @foreach($hdData['uvs'] as $uIdx => $u)
                                    <th>UV{{ $uIdx + 1 }}</th>
                                @endforeach
                                <th>Độ Lệch</th>
                                <th>Điểm HĐ</th>
                                <th>Điểm Tổng</th>
                                <th>Chữ</th>
                                <th>Kết Quả</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $hasSvInHd = false; @endphp
                            @foreach($hdData['deTais'] as $dtItem)
                                @foreach($dtItem['sinhViens'] as $sv)
                                    @php $hasSvInHd = true; @endphp
                                    <tr>
                                        <td>{{ $sttGlobal++ }}</td>
                                        <td class="fw-semibold">{{ $sv['maSV'] ?? '-' }}</td>
                                        <td class="text-start text-nowrap fw-semibold">{{ $sv['hoTen'] ?? '-' }}</td>
                                        <td class="text-nowrap">{{ $sv['tenLop'] ?? '-' }}</td>
                                        <td class="text-start" style="max-width: 250px;">{{ $sv['tenDeTai'] ?? '-' }}</td>
                                        <td>{{ $sv['diemCT'] !== null ? number_format($sv['diemCT'], 2) : '--' }}</td>
                                        <td>{{ $sv['diemTK'] !== null ? number_format($sv['diemTK'], 2) : '--' }}</td>
                                        @foreach($sv['diemUVs'] as $uScore)
                                            <td>{{ $uScore['diem'] !== null ? number_format($uScore['diem'], 2) : '--' }}</td>
                                        @endforeach
                                        <td class="{{ ($sv['canhBaoLech'] ?? false) ? 'text-danger fw-bold' : '' }}">
                                            {{ number_format($sv['diffScore'] ?? 0, 2) }}
                                            @if($sv['canhBaoLech'] ?? false) * @endif
                                        </td>
                                        <td class="fw-semibold">{{ $sv['diemHD'] !== null ? number_format($sv['diemHD'], 2) : '--' }}</td>
                                        <td class="fw-bold text-primary">{{ $sv['diemTongKet'] !== null ? number_format($sv['diemTongKet'], 2) : '--' }}</td>
                                        <td class="fw-bold">{{ $sv['diemChu'] ?: '--' }}</td>
                                        <td>
                                            @if($sv['isDuPhieu'] ?? false)
                                                <span class="{{ ($sv['isDat'] ?? false) ? 'text-success fw-bold' : 'text-danger fw-bold' }}">
                                                    {{ ($sv['isDat'] ?? false) ? 'Đạt' : 'Chưa đạt' }}
                                                </span>
                                            @else
                                                <span class="text-muted">Chưa có KQ</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            @if(!$hasSvInHd)
                                <tr>
                                    <td colspan="{{ 11 + count($hdData['uvs']) }}" class="text-muted py-2">Hội đồng này chưa có đề tài được phân công.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="text-center py-4 text-muted">Không có hội đồng nào phù hợp với bộ lọc.</div>
            @endforelse
            @break

        {{-- ───────────────────────────────────────────────────────────── --}}
        {{-- MẪU 07: BÁO CÁO PHÂN BỐ ĐIỂM SỐ KHÓA LUẬN TỐT NGHIỆP          --}}
        {{-- ───────────────────────────────────────────────────────────── --}}
        @case('mau_07')
            @php
                $khoang = $data['charts']['chartPhanBoDiem']['khoangThang10'] ?? [];
                $diemChu = $data['charts']['chartPhanBoDiem']['chuA_F'] ?? [];
                $tongScored = $data['kpi']['tongSVHoanThanhCham'] ?: (count($data['danhSachTatCaSV']) ?: 1);
            @endphp
            <div class="mb-3">
                <div class="fw-bold text-dark mb-1" style="font-size: 0.8rem; color: #003366;">1. BẢNG PHÂN BỐ THEO KHOẢNG ĐIỂM HỆ 10:</div>
                <table class="table table-sm table-bordered align-middle text-center mb-0" style="font-size: 0.74rem;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35px;">STT</th>
                            <th>Khoảng Điểm (Thang 10)</th>
                            <th style="width: 150px;">Xếp Loại Tương Ứng</th>
                            <th style="width: 120px;">Số Lượng Sinh Viên</th>
                            <th style="width: 110px;">Tỷ Lệ (%)</th>
                            <th style="width: 110px;">Đánh Giá</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Dưới 4.0 điểm</td>
                            <td>Kém (F)</td>
                            <td class="fw-bold text-danger">{{ $khoang['duoi_4'] ?? 0 }}</td>
                            <td class="text-danger">{{ number_format((($khoang['duoi_4'] ?? 0) / $tongScored) * 100, 1) }}%</td>
                            <td class="text-danger fw-bold">Không đạt</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Từ 4.0 đến dưới 5.0 điểm</td>
                            <td>Yếu (D)</td>
                            <td class="fw-bold">{{ $khoang['4_5'] ?? 0 }}</td>
                            <td>{{ number_format((($khoang['4_5'] ?? 0) / $tongScored) * 100, 1) }}%</td>
                            <td class="text-success">Đạt</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Từ 5.0 đến dưới 6.5 điểm</td>
                            <td>Trung bình (D+, C)</td>
                            <td class="fw-bold">{{ $khoang['5_65'] ?? 0 }}</td>
                            <td>{{ number_format((($khoang['5_65'] ?? 0) / $tongScored) * 100, 1) }}%</td>
                            <td class="text-success">Đạt</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Từ 6.5 đến dưới 7.0 điểm</td>
                            <td>Trung bình khá (C+)</td>
                            <td class="fw-bold">{{ $khoang['65_7'] ?? 0 }}</td>
                            <td>{{ number_format((($khoang['65_7'] ?? 0) / $tongScored) * 100, 1) }}%</td>
                            <td class="text-success">Đạt</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Từ 7.0 đến dưới 8.5 điểm</td>
                            <td>Khá, Khá giỏi (B, B+)</td>
                            <td class="fw-bold">{{ $khoang['7_85'] ?? 0 }}</td>
                            <td>{{ number_format((($khoang['7_85'] ?? 0) / $tongScored) * 100, 1) }}%</td>
                            <td class="text-success">Đạt</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Từ 8.5 đến 10.0 điểm</td>
                            <td>Giỏi, Xuất sắc (A, A+)</td>
                            <td class="fw-bold text-success">{{ $khoang['85_10'] ?? 0 }}</td>
                            <td class="text-success fw-bold">{{ number_format((($khoang['85_10'] ?? 0) / $tongScored) * 100, 1) }}%</td>
                            <td class="text-success fw-bold">Đạt</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mb-3">
                <div class="fw-bold text-dark mb-1" style="font-size: 0.8rem; color: #003366;">2. BẢNG PHÂN BỐ THEO THANG ĐIỂM CHỮ:</div>
                <table class="table table-sm table-bordered align-middle text-center mb-0" style="font-size: 0.74rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Điểm Chữ</th>
                            <th>A+ / A</th>
                            <th>B+</th>
                            <th>B</th>
                            <th>C+</th>
                            <th>C</th>
                            <th>D+</th>
                            <th>D</th>
                            <th>F</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold">Thang Điểm 4</td>
                            <td>4.0 / 3.7</td>
                            <td>3.5</td>
                            <td>3.0</td>
                            <td>2.5</td>
                            <td>2.0</td>
                            <td>1.5</td>
                            <td>1.0</td>
                            <td>0.0</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Số Lượng SV</td>
                            <td class="fw-bold text-success">{{ $diemChu['A'] ?? 0 }}</td>
                            <td>{{ $diemChu['B+'] ?? 0 }}</td>
                            <td>{{ $diemChu['B'] ?? 0 }}</td>
                            <td>{{ $diemChu['C+'] ?? 0 }}</td>
                            <td>{{ $diemChu['C'] ?? 0 }}</td>
                            <td>{{ $diemChu['D+'] ?? 0 }}</td>
                            <td>{{ $diemChu['D'] ?? 0 }}</td>
                            <td class="fw-bold text-danger">{{ $diemChu['F'] ?? 0 }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @break

        {{-- ───────────────────────────────────────────────────────────── --}}
        {{-- MẪU 08: BÁO CÁO TÌNH HÌNH TỔ CHỨC KHÓA LUẬN TỐT NGHIỆP        --}}
        {{-- ───────────────────────────────────────────────────────────── --}}
        @case('mau_08')
            <div class="mb-3">
                <div class="fw-bold text-dark mb-1" style="font-size: 0.8rem; color: #003366;">1. TỔNG QUAN CÔNG TÁC TỔ CHỨC KHÓA LUẬN:</div>
                <div class="row g-2 text-center mb-2">
                    <div class="col-3">
                        <div class="p-2 border bg-light">
                            <div class="small text-muted">Tổng số Hội đồng</div>
                            <div class="fs-6 fw-bold text-primary">{{ number_format($data['kpi']['tongHoiDong'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border bg-light">
                            <div class="small text-muted">Tổng số Đề tài</div>
                            <div class="fs-6 fw-bold text-primary">{{ number_format($data['kpi']['tongDeTai'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border bg-light">
                            <div class="small text-muted">Tổng Sinh viên tham gia</div>
                            <div class="fs-6 fw-bold text-primary">{{ number_format($data['kpi']['tongSVThamGia'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border bg-light">
                            <div class="small text-muted">Tổng CBGV tham gia HĐ</div>
                            <div class="fs-6 fw-bold text-primary">{{ number_format($data['kpi']['tongCBGVHoiDong'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>

                <div class="fw-bold text-dark mb-1 mt-3" style="font-size: 0.8rem; color: #003366;">2. DANH SÁCH CHI TIẾT CÁC HỘI ĐỒNG BẢO VỆ:</div>
                <table class="table table-sm table-bordered align-middle text-center mb-0" style="font-size: 0.72rem;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35px;">STT</th>
                            <th>Mã HĐ</th>
                            <th class="text-start">Tên Hội Đồng</th>
                            <th>Ngành</th>
                            <th>Phòng</th>
                            <th>Thời Gian</th>
                            <th>Số ĐT</th>
                            <th>Số SV</th>
                            <th>Chủ Tịch</th>
                            <th>Thư Ký</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['danhSachHoiDong'] as $idx => $hdData)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $hdData['hoiDong']->MaHoiDong ?? '-' }}</td>
                                <td class="text-start fw-bold">{{ $hdData['hoiDong']->TenHoiDong ?? '-' }}</td>
                                <td>{{ $hdData['hoiDong']->nganh?->TenNganh ?? 'Chung' }}</td>
                                <td>{{ $hdData['hoiDong']->DiaDiem ?? 'HUIT' }}</td>
                                <td>{{ $hdData['hoiDong']->NgayBaoVe ? \Carbon\Carbon::parse($hdData['hoiDong']->NgayBaoVe)->format('d/m/Y') : '--' }}</td>
                                <td class="fw-semibold">{{ count($hdData['deTais']) }}</td>
                                <td class="fw-semibold">
                                    {{ array_sum(array_map(fn($d) => count($d['sinhViens']), $hdData['deTais'])) }}
                                </td>
                                <td>{{ $hdData['ct']->giangVien?->HoTen ?? 'Chưa rõ' }}</td>
                                <td>{{ $hdData['tk']->giangVien?->HoTen ?? 'Chưa rõ' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-muted py-3">Không có hội đồng nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @break

        {{-- ───────────────────────────────────────────────────────────── --}}
        {{-- MẪU 09: BÁO CÁO THỐNG KÊ KHÓA LUẬN THEO BỘ MÔN                 --}}
        {{-- ───────────────────────────────────────────────────────────── --}}
        @case('mau_09')
            <div class="mb-3">
                <div class="fw-bold text-dark mb-1" style="font-size: 0.8rem; color: #003366;">1. BẢNG TỔNG HỢP SỐ LIỆU THEO BỘ MÔN:</div>
                <table class="table table-sm table-bordered align-middle text-center mb-0" style="font-size: 0.74rem;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35px;">STT</th>
                            <th style="width: 80px;">Mã BM</th>
                            <th class="text-start">Tên Bộ Môn</th>
                            <th style="width: 60px;">Số ĐT</th>
                            <th style="width: 60px;">Số SV</th>
                            <th style="width: 60px;">Đạt</th>
                            <th style="width: 65px;">Chưa Đạt</th>
                            <th style="width: 70px;">Chưa Có KQ</th>
                            <th style="width: 75px;">Tỷ Lệ Đạt</th>
                            <th style="width: 65px;">Điểm TB</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['statsTheoBoMon'] as $idx => $stBm)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $stBm['maBoMon'] ?? '-' }}</td>
                                <td class="text-start fw-bold">{{ $stBm['tenBoMon'] ?? '-' }}</td>
                                <td>{{ $stBm['soDeTai'] ?? 0 }}</td>
                                <td>{{ $stBm['soSV'] ?? 0 }}</td>
                                <td class="text-success fw-bold">{{ $stBm['soDat'] ?? 0 }}</td>
                                <td class="text-danger">{{ $stBm['soKhongDat'] ?? 0 }}</td>
                                <td>{{ $stBm['soChuaXong'] ?? 0 }}</td>
                                <td class="text-success fw-bold">{{ number_format($stBm['tyLeDat'] ?? 0, 1) }}%</td>
                                <td class="text-primary fw-bold">{{ number_format($stBm['diemTB'] ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-muted py-3">Không có dữ liệu bộ môn phù hợp.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($config['show_detail'] ?? true)
            @include('thongke_baocao.partials.filtered_councils', ['rows' => $data['danhSachTatCaSV'], 'groupMode' => 'department'])
            @endif
            @break

        {{-- ───────────────────────────────────────────────────────────── --}}
        {{-- MẪU 10: BÁO CÁO THỐNG KÊ KHÓA LUẬN THEO HỌC PHẦN              --}}
        {{-- ───────────────────────────────────────────────────────────── --}}
        @case('mau_10')
            <div class="mb-3">
                <div class="fw-bold text-dark mb-1" style="font-size: 0.8rem; color: #003366;">1. BẢNG TỔNG HỢP THEO HỌC PHẦN KHÓA LUẬN:</div>
                <table class="table table-sm table-bordered align-middle text-center mb-0" style="font-size: 0.74rem;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35px;">STT</th>
                            <th style="width: 90px;">Mã HP</th>
                            <th class="text-start">Tên Học Phần</th>
                            <th style="width: 50px;">Số TC</th>
                            <th style="width: 60px;">Số ĐT</th>
                            <th style="width: 60px;">Số SV</th>
                            <th style="width: 60px;">Đạt</th>
                            <th style="width: 65px;">Chưa Đạt</th>
                            <th style="width: 75px;">Tỷ Lệ Đạt</th>
                            <th style="width: 65px;">Điểm TB</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['statsTheoHocPhan'] as $idx => $stHp)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $stHp['maHocPhan'] ?? '-' }}</td>
                                <td class="text-start fw-bold">{{ $stHp['tenHocPhan'] ?? '-' }}</td>
                                <td>{{ $stHp['soTinChi'] ?? 10 }}</td>
                                <td>{{ $stHp['soDeTai'] ?? 0 }}</td>
                                <td>{{ $stHp['soSV'] ?? 0 }}</td>
                                <td class="text-success fw-bold">{{ $stHp['soDat'] ?? 0 }}</td>
                                <td class="text-danger">{{ $stHp['soKhongDat'] ?? 0 }}</td>
                                <td class="text-success fw-bold">{{ number_format($stHp['tyLeDat'] ?? 0, 1) }}%</td>
                                <td class="text-primary fw-bold">{{ number_format($stHp['diemTB'] ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-muted py-3">Không có dữ liệu học phần phù hợp.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($config['show_detail'] ?? true)
            @include('thongke_baocao.partials.filtered_councils', ['rows' => $data['danhSachTatCaSV'], 'groupMode' => 'course'])
            @endif
            @break

        {{-- ───────────────────────────────────────────────────────────── --}}
        {{-- MẪU 01 (MẶC ĐỊNH): BÁO CÁO THEO ĐỢT & HỘI ĐỒNG                --}}
        {{-- ───────────────────────────────────────────────────────────── --}}
        @default
            @if($config['show_table'] ?? true)
                <div class="mb-3">
                    <div class="fw-bold text-dark mb-1" style="font-size: 0.8rem; color: #003366;">I. TỔNG HỢP KẾT QUẢ ĐỢT BẢO VỆ:</div>
                    <table class="table table-sm table-bordered align-middle text-center mb-0" style="font-size: 0.74rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">STT</th>
                                <th class="text-start">Chỉ tiêu thống kê</th>
                                <th style="width: 120px;">Số lượng</th>
                                <th style="width: 120px;">Tỷ lệ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td class="text-start">Sinh viên tham gia khóa luận</td>
                                <td class="fw-semibold">{{ number_format($data['kpi']['tongSVThamGia'] ?? 0) }}</td>
                                <td>100%</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td class="text-start">Sinh viên đã phân công hội đồng</td>
                                <td class="fw-semibold">{{ number_format($data['kpi']['tongSVDaPhanCongHD'] ?? 0) }}</td>
                                <td>{{ ($data['kpi']['tongSVThamGia'] ?? 0) > 0 ? number_format((($data['kpi']['tongSVDaPhanCongHD'] ?? 0) / $data['kpi']['tongSVThamGia']) * 100, 1) : 0 }}%</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td class="text-start">Sinh viên đạt (điểm và tất cả CLO)</td>
                                <td class="fw-semibold text-success">{{ number_format($data['kpi']['tongSVDat'] ?? 0) }}</td>
                                <td class="text-success fw-bold">{{ number_format($data['kpi']['tyLeDat'] ?? 0, 1) }}%</td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td class="text-start">Sinh viên chưa đạt (điểm hoặc CLO)</td>
                                <td class="fw-semibold text-danger">{{ number_format($data['kpi']['tongSVChuaDat'] ?? 0) }}</td>
                                <td class="text-danger">{{ (($data['kpi']['tongSVDat'] ?? 0) + ($data['kpi']['tongSVChuaDat'] ?? 0)) > 0 ? number_format((($data['kpi']['tongSVChuaDat'] ?? 0) / (($data['kpi']['tongSVDat'] ?? 0) + ($data['kpi']['tongSVChuaDat'] ?? 0))) * 100, 1) : 0 }}%</td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td class="text-start">Chưa có kết quả</td>
                                <td class="fw-semibold">{{ number_format($data['kpi']['tongSVChuaCoKQ'] ?? 0) }}</td>
                                <td>{{ ($data['kpi']['tongSVDaPhanCongHD'] ?? 0) > 0 ? number_format((($data['kpi']['tongSVChuaCoKQ'] ?? 0) / $data['kpi']['tongSVDaPhanCongHD']) * 100, 1) : 0 }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif

            @if($config['show_detail'] ?? true)
                <div class="mb-3">
                    <div class="fw-bold text-dark mb-1" style="font-size: 0.8rem; color: #003366;">II. DANH SÁCH CHI TIẾT THEO TỪNG HỘI ĐỒNG:</div>
                    @php $sttGlobal = 1; @endphp
                    @forelse($data['danhSachHoiDong'] as $hdData)
                        <div class="mb-3">
                            <div class="p-2 mb-1 bg-light border fw-bold text-dark d-flex justify-content-between align-items-center flex-wrap gap-2" style="font-size: 0.76rem;">
                                <span>HỘI ĐỒNG: {{ mb_strtoupper($hdData['hoiDong']->TenHoiDong ?? 'HỘI ĐỒNG') }}</span>
                                <span class="small fw-normal text-muted">
                                    Ngành: {{ $hdData['hoiDong']->nganh?->TenNganh ?? 'Chung' }} &bull; Phòng: {{ $hdData['hoiDong']->DiaDiem ?? 'HUIT' }}
                                </span>
                            </div>

                            <table class="table table-sm table-bordered align-middle text-center mb-0" style="font-size: 0.72rem;">
                                <thead class="table-light text-nowrap">
                                    <tr>
                                        <th style="width: 32px;">STT</th>
                                        <th>Mã ĐT</th>
                                        <th class="text-start">Tên Đề Tài</th>
                                        <th>Nhóm</th>
                                        <th>MSSV</th>
                                        <th class="text-start">Họ và Tên</th>
                                        <th>GVPB</th>
                                        <th>CT</th>
                                        <th>TK</th>
                                        <th>UV</th>
                                        <th>Điểm 10</th>
                                        <th>Điểm 4</th>
                                        <th>Chữ</th>
                                        <th>Xếp Loại</th>
                                        <th>Kết Quả</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $hasSvInCouncil = false; @endphp
                                    @foreach($hdData['deTais'] as $dtItem)
                                        @foreach($dtItem['sinhViens'] as $sv)
                                            @php $hasSvInCouncil = true; @endphp
                                            <tr>
                                                <td>{{ $sttGlobal++ }}</td>
                                                <td class="fw-semibold">{{ $sv['maDeTai'] ?? '-' }}</td>
                                                <td class="text-start" style="max-width: 220px;">{{ $sv['tenDeTai'] ?? '-' }}</td>
                                                <td>{{ $sv['tenNhom'] ?? '-' }}</td>
                                                <td class="fw-semibold">{{ $sv['maSV'] ?? '-' }}</td>
                                                <td class="text-start text-nowrap fw-semibold">{{ $sv['hoTen'] ?? '-' }}</td>
                                                <td>{{ $sv['diemGVPB'] !== null ? number_format($sv['diemGVPB'], 2) : '--' }}</td>
                                                <td>{{ $sv['diemCT'] !== null ? number_format($sv['diemCT'], 2) : '--' }}</td>
                                                <td>{{ $sv['diemTK'] !== null ? number_format($sv['diemTK'], 2) : '--' }}</td>
                                                <td>
                                                    @if(!empty($sv['diemUVs']))
                                                        @foreach($sv['diemUVs'] as $uvItem)
                                                            <span>{{ $uvItem['diem'] !== null ? number_format($uvItem['diem'], 2) : '--' }}</span>
                                                        @endforeach
                                                    @else
                                                        --
                                                    @endif
                                                </td>
                                                <td class="fw-bold {{ ($sv['isDat'] ?? false) ? 'text-success' : 'text-danger' }}">
                                                    {{ $sv['diemTongKet'] !== null ? number_format($sv['diemTongKet'], 2) : '--' }}
                                                </td>
                                                <td>{{ $sv['diemHe4'] !== null ? number_format($sv['diemHe4'], 2) : '--' }}</td>
                                                <td class="fw-bold">{{ $sv['diemChu'] ?? '--' }}</td>
                                                <td>{{ $sv['xepLoai'] ?? '--' }}</td>
                                                <td>
                                                    @if($sv['isDuPhieu'] ?? false)
                                                        <span class="{{ ($sv['isDat'] ?? false) ? 'text-success fw-bold' : 'text-danger fw-bold' }}">
                                                            {{ ($sv['isDat'] ?? false) ? 'Đạt' : 'Chưa đạt' }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">Chưa có KQ</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                    @if(!$hasSvInCouncil)
                                        <tr>
                                            <td colspan="16" class="text-muted py-2">Hội đồng chưa có đề tài được phân công.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @empty
                        <table class="table table-sm table-bordered align-middle text-center mb-0" style="font-size: 0.72rem;">
                            <tbody>
                                <tr>
                                    <td colspan="16" class="text-muted py-3">Không có dữ liệu đề tài / hội đồng theo bộ lọc.</td>
                                </tr>
                            </tbody>
                        </table>
                    @endforelse
                </div>
            @endif
    @endswitch

    {{-- Nhận xét & Kết luận --}}
    @if($config['show_conclusion'] ?? true)
        <div class="mb-3 p-2 bg-light border rounded" style="font-size: 0.76rem;">
            <strong>Nhận xét chung:</strong> Đợt khóa luận {{ $data['hocKy']->TenHocKy ?? '' }} (Năm học {{ $data['hocKy']->NamHoc ?? '' }}) có tỷ lệ sinh viên đạt <strong>{{ number_format($data['kpi']['tyLeDat'] ?? 0, 1) }}%</strong>, điểm trung bình đạt <strong>{{ number_format($data['kpi']['diemTB'] ?? 0, 2) }}/10</strong>.
        </div>
    @endif

    {{-- Chữ ký các bên liên quan --}}
    @if($config['show_signatures'] ?? true)
        <table class="w-100 mt-4 text-center" style="border: none; font-size: 0.78rem;">
            <tr>
                <td style="width: 33%; vertical-align: top; border: none;">
                    <div class="fw-bold">NGƯỜI LẬP BÁO CÁO</div>
                    <div class="text-muted fst-italic small">(Ký, ghi rõ họ tên)</div>
                    <div style="height: 60px;"></div>
                    <div class="fw-semibold">
                        {{ ($config['show_reporter'] ?? true) ? (auth()->user()->HoTen ?? '') : '' }}
                    </div>
                </td>
                <td style="width: 33%; vertical-align: top; border: none;">
                    <div class="fw-bold">TRƯỞNG BỘ MÔN</div>
                    <div class="text-muted fst-italic small">(Ký, ghi rõ họ tên)</div>
                    <div style="height: 60px;"></div>
                </td>
                <td style="width: 34%; vertical-align: top; border: none;">
                    <div class="fw-bold">BAN CHỦ NHIỆM KHOA</div>
                    <div class="text-muted fst-italic small">(Ký, đóng dấu)</div>
                    <div style="height: 60px;"></div>
                </td>
            </tr>
        </table>
    @endif
</div>
