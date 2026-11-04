<!-- ═══ BẢNG SỐ LIỆU CHI TIẾT THEO BIỂU MẪU ĐƯỢC CHỌN ═══ -->
<div id="dataTableWrapper" class="card border-0 shadow-sm rounded-4 bg-white mb-4">
    <!-- Header của bảng: Tên mẫu, số lượng và các nút tiện ích -->
    <div class="card-header bg-white border-bottom py-3 px-3 px-lg-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary px-2.5 py-1.5 fw-bold rounded-pill text-uppercase">
                {{ $data['mauBaoCao'] ?? 'mau_01' }}
            </span>
            <h5 class="fw-bold mb-0 text-dark">
                @switch($data['mauBaoCao'] ?? 'mau_01')
                    @case('mau_02') Báo Cáo Thống Kê Kết Quả Khóa Luận Theo Ngành Đào Tạo @break
                    @case('mau_03') Danh Sách Sinh Viên Đạt Khóa Luận Tốt Nghiệp @break
                    @case('mau_04') Danh Sách Sinh Viên Chưa Đạt Khóa Luận Tốt Nghiệp @break
                    @case('mau_05') Danh Sách Sinh Viên Chưa Có Kết Quả (Đang Chấm) @break
                    @case('mau_06') Báo Cáo Thống Kê Điểm Số Theo Hội Đồng Bảo Vệ @break
                    @case('mau_07') Báo Cáo Phân Bố Điểm Khóa Luận Tốt Nghiệp @break
                    @case('mau_08') Báo Cáo Tình Hình Tổ Chức Khóa Luận Tốt Nghiệp @break
                    @case('mau_09') Báo Cáo Thống Kê Khóa Luận Theo Bộ Môn @break
                    @case('mau_10') Báo Cáo Thống Kê Khóa Luận Theo Học Phần @break
                    @default Báo Cáo Tổng Hợp Kết Quả Khóa Luận Theo Đợt &amp; Hội Đồng
                @endswitch
            </h5>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">
                Tổng cộng: <strong class="text-primary">{{ count($data['danhSachTatCaSV']) }}</strong> sinh viên
            </span>
        </div>
    </div>

    <!-- Alert Cảnh báo đề tài chưa đủ điểm -->
    <div class="card-body p-0">
        @if(empty($data['danhSachTatCaSV']) && empty($data['danhSachHoiDong']))
            <!-- Trạng thái trống -->
            <div class="text-center py-5">
                <i class="fa-solid fa-folder-open fa-3x text-muted mb-3 d-block opacity-50"></i>
                <h6 class="fw-bold text-secondary mb-1">Không tìm thấy dữ liệu phù hợp</h6>
                <p class="text-muted small mb-3">Vui lòng điều chỉnh tiêu chí lọc (Học kỳ, Khoa, Ngành, Kết quả hoặc từ khóa tìm kiếm).</p>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('btnResetFilter').click();">
                    <i class="fa-solid fa-rotate-left me-1"></i> Đặt lại bộ lọc
                </button>
            </div>
        @else
            @switch($data['mauBaoCao'] ?? 'mau_01')

                {{-- ═════════════════════════════════════════════════════════════════ --}}
                {{-- MẪU 02: BÁO CÁO THỐNG KÊ KẾT QUẢ THEO NGÀNH                      --}}
                {{-- ═════════════════════════════════════════════════════════════════ --}}
                @case('mau_02')
                    <!-- Bảng tổng hợp theo ngành -->
                    <div class="p-3 border-bottom bg-light">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-table-list text-primary me-1"></i> Bảng tổng hợp số liệu theo ngành</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover align-middle mb-0 bg-white">
                                <thead class="table-primary text-nowrap">
                                    <tr class="text-center">
                                        <th style="width: 50px;">STT</th>
                                        <th>Mã Ngành</th>
                                        <th>Tên Ngành Đào Tạo</th>
                                        <th>Tổng SV</th>
                                        <th>Đạt</th>
                                        <th>Chưa Đạt</th>
                                        <th>Chưa Có KQ</th>
                                        <th>Tỷ Lệ Đạt (%)</th>
                                        <th>Điểm TB</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sttNg = 1; @endphp
                                    @foreach($data['statsTheoNganh'] as $stNg)
                                        @php
                                            $tongDaCham = $stNg['soDat'] + $stNg['soKhongDat'];
                                            $tyLe = $tongDaCham > 0 ? round(($stNg['soDat'] / $tongDaCham) * 100, 2) : 0;
                                            $diemTB = !empty($stNg['scores']) ? round(array_sum($stNg['scores']) / count($stNg['scores']), 2) : 0;
                                        @endphp
                                        <tr class="text-center">
                                            <td>{{ $sttNg++ }}</td>
                                            <td class="fw-semibold text-secondary">{{ $stNg['maNganh'] }}</td>
                                            <td class="text-start fw-bold text-dark">{{ $stNg['tenNganh'] }}</td>
                                            <td class="fw-bold">{{ $stNg['soSV'] }}</td>
                                            <td class="text-success fw-bold">{{ $stNg['soDat'] }}</td>
                                            <td class="text-danger fw-bold">{{ $stNg['soKhongDat'] }}</td>
                                            <td class="text-warning fw-bold">{{ $stNg['soChuaXong'] }}</td>
                                            <td class="fw-bold text-primary">{{ number_format($tyLe, 2) }}%</td>
                                            <td class="fw-bold">{{ number_format($diemTB, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Bảng danh sách chi tiết sinh viên -->
                    <div class="p-3">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-users text-primary me-1"></i> Danh sách sinh viên chi tiết theo ngành</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-bordered align-middle mb-0">
                                <thead class="table-light text-nowrap">
                                    <tr class="text-center">
                                        <th style="width: 45px;">STT</th>
                                        <th>MSSV</th>
                                        <th>Họ Và Tên</th>
                                        <th>Lớp</th>
                                        <th>Ngành</th>
                                        <th>Tên Đề Tài</th>
                                        <th>GVHD</th>
                                        <th>Hội Đồng</th>
                                        <th>Điểm 10</th>
                                        <th>Điểm 4</th>
                                        <th>Điểm Chữ</th>
                                        <th>Xếp Loại</th>
                                        <th>Kết Quả</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['danhSachTatCaSV'] as $index => $sv)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td class="fw-bold text-center text-primary">{{ $sv['maSV'] }}</td>
                                            <td class="fw-semibold text-dark text-nowrap">{{ $sv['hoTen'] }}</td>
                                            <td class="text-center small">{{ $sv['tenLop'] }}</td>
                                            <td class="small">{{ $sv['tenNganh'] }}</td>
                                            <td class="small" style="max-width: 250px;">{{ $sv['tenDeTai'] }}</td>
                                            <td class="small">{{ \App\Services\ThongKeService::formatLecturerName($sv['tenGVHD'] ?? $sv['gvhd'] ?? null) }}</td>
                                            <td class="small">{{ $sv['tenHoiDong'] }}</td>
                                            <td class="text-center fw-bold">{{ $sv['diemTongKet'] !== null ? number_format($sv['diemTongKet'], 2) : '-' }}</td>
                                            <td class="text-center">{{ $sv['diemHe4'] !== null ? number_format($sv['diemHe4'], 2) : '-' }}</td>
                                            <td class="text-center fw-bold">{{ $sv['diemChu'] ?? '-' }}</td>
                                            <td class="text-center small">{{ $sv['xepLoai'] ?? '-' }}</td>
                                            <td class="text-center">
                                                @if($sv['isDuPhieu'])
                                                    @if($sv['isDat'])
                                                        <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Đạt</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">Không đạt</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill">Chưa có kết quả</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @break

                {{-- ═════════════════════════════════════════════════════════════════ --}}
                {{-- MẪU 03: DANH SÁCH SINH VIÊN ĐẠT KHÓA LUẬN                         --}}
                {{-- ═════════════════════════════════════════════════════════════════ --}}
                @case('mau_03')
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-sm align-middle mb-0">
                            <thead class="table-success text-nowrap text-center">
                                <tr>
                                    <th style="width: 50px;">STT</th>
                                    <th>MSSV</th>
                                    <th>Họ Và Tên</th>
                                    <th>Lớp</th>
                                    <th>Ngành Đào Tạo</th>
                                    <th>Đề Tài Khóa Luận</th>
                                    <th>GV Hướng Dẫn</th>
                                    <th>Hội Đồng</th>
                                    <th>Điểm Hệ 10</th>
                                    <th>Điểm Hệ 4</th>
                                    <th>Điểm Chữ</th>
                                    <th>Xếp Loại</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sttDat = 1; @endphp
                                @forelse($data['duLieuMauBaoCao']['danhSachSV'] ?? [] as $sv)
                                    <tr>
                                        <td class="text-center">{{ $sttDat++ }}</td>
                                        <td class="text-center fw-bold text-success">{{ $sv['maSV'] }}</td>
                                        <td class="fw-semibold text-dark text-nowrap">{{ $sv['hoTen'] }}</td>
                                        <td class="text-center small">{{ $sv['tenLop'] }}</td>
                                        <td class="small">{{ $sv['tenNganh'] }}</td>
                                        <td class="small" style="max-width: 280px;">{{ $sv['tenDeTai'] }}</td>
                                        <td class="small">{{ \App\Services\ThongKeService::formatLecturerName($sv['tenGVHD'] ?? $sv['gvhd'] ?? null) }}</td>
                                        <td class="small">{{ $sv['tenHoiDong'] }}</td>
                                        <td class="text-center fw-bold text-success fs-6">{{ number_format($sv['diemTongKet'], 2) }}</td>
                                        <td class="text-center fw-semibold">{{ number_format($sv['diemHe4'], 2) }}</td>
                                        <td class="text-center fw-bold text-primary">{{ $sv['diemChu'] }}</td>
                                        <td class="text-center fw-semibold small text-success">{{ $sv['xepLoai'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center py-4 text-muted">Không có sinh viên nào đạt trong tiêu chí lọc.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @break

                {{-- ═════════════════════════════════════════════════════════════════ --}}
                {{-- MẪU 04: DANH SÁCH SINH VIÊN CHƯA ĐẠT                             --}}
                {{-- ═════════════════════════════════════════════════════════════════ --}}
                @case('mau_04')
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-sm align-middle mb-0">
                            <thead class="table-danger text-nowrap text-center">
                                <tr>
                                    <th style="width: 50px;">STT</th>
                                    <th>MSSV</th>
                                    <th>Họ Và Tên</th>
                                    <th>Lớp</th>
                                    <th>Ngành Đào Tạo</th>
                                    <th>Đề Tài Khóa Luận</th>
                                    <th>GV Hướng Dẫn</th>
                                    <th>Hội Đồng</th>
                                    <th>Điểm Hệ 10</th>
                                    <th>Điểm Chữ</th>
                                    <th>Lý Do / Ghi Chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sttRot = 1; @endphp
                                @forelse($data['duLieuMauBaoCao']['danhSachSV'] ?? [] as $sv)
                                    <tr>
                                        <td class="text-center">{{ $sttRot++ }}</td>
                                        <td class="text-center fw-bold text-danger">{{ $sv['maSV'] }}</td>
                                        <td class="fw-semibold text-dark text-nowrap">{{ $sv['hoTen'] }}</td>
                                        <td class="text-center small">{{ $sv['tenLop'] }}</td>
                                        <td class="small">{{ $sv['tenNganh'] }}</td>
                                        <td class="small" style="max-width: 280px;">{{ $sv['tenDeTai'] }}</td>
                                        <td class="small">{{ \App\Services\ThongKeService::formatLecturerName($sv['tenGVHD'] ?? $sv['gvhd'] ?? null) }}</td>
                                        <td class="small">{{ $sv['tenHoiDong'] }}</td>
                                        <td class="text-center fw-bold text-danger fs-6">{{ number_format($sv['diemTongKet'], 2) }}</td>
                                        <td class="text-center fw-bold text-danger">{{ $sv['diemChu'] }}</td>
                                        <td class="small text-danger">Điểm tổng kết dưới ngưỡng hoặc có CLO chưa đạt</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4 text-success fw-bold">
                                            <i class="fa-solid fa-circle-check me-1"></i> Xuất sắc! Không có sinh viên nào bị rớt (chưa đạt) trong đợt này.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @break

                {{-- ═════════════════════════════════════════════════════════════════ --}}
                {{-- MẪU 05: SINH VIÊN CHƯA CÓ KẾT QUẢ (ĐANG CHẤM)                    --}}
                {{-- ═════════════════════════════════════════════════════════════════ --}}
                @case('mau_05')
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-sm align-middle mb-0">
                            <thead class="table-warning text-nowrap text-center">
                                <tr>
                                    <th style="width: 50px;">STT</th>
                                    <th>MSSV</th>
                                    <th>Họ Và Tên</th>
                                    <th>Lớp</th>
                                    <th>Ngành</th>
                                    <th>Đề Tài Khóa Luận</th>
                                    <th>Hội Đồng</th>
                                    <th>GVHD</th>
                                    <th>GVPB</th>
                                    <th>Chủ Tịch</th>
                                    <th>Thư Ký</th>
                                    <th>Tình Trạng Thiếu Phiếu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sttChua = 1; @endphp
                                @forelse($data['duLieuMauBaoCao']['danhSachSV'] ?? [] as $sv)
                                    <tr>
                                        <td class="text-center">{{ $sttChua++ }}</td>
                                        <td class="text-center fw-bold text-warning">{{ $sv['maSV'] }}</td>
                                        <td class="fw-semibold text-dark text-nowrap">{{ $sv['hoTen'] }}</td>
                                        <td class="text-center small">{{ $sv['tenLop'] }}</td>
                                        <td class="small">{{ $sv['tenNganh'] }}</td>
                                        <td class="small" style="max-width: 250px;">{{ $sv['tenDeTai'] }}</td>
                                        <td class="small text-nowrap">{{ $sv['tenHoiDong'] }}</td>
                                        <td class="text-center small">
                                            {!! $sv['diemGVHD'] !== null ? '<span class="text-success fw-bold">'.number_format($sv['diemGVHD'],1).'</span>' : '<span class="badge bg-danger-subtle text-danger">Chưa chấm</span>' !!}
                                        </td>
                                        <td class="text-center small">
                                            {!! $sv['diemGVPB'] !== null ? '<span class="text-success fw-bold">'.number_format($sv['diemGVPB'],1).'</span>' : '<span class="badge bg-danger-subtle text-danger">Chưa chấm</span>' !!}
                                        </td>
                                        <td class="text-center small">
                                            {!! $sv['diemCT'] !== null ? '<span class="text-success fw-bold">'.number_format($sv['diemCT'],1).'</span>' : '<span class="badge bg-secondary-subtle text-secondary">Chưa</span>' !!}
                                        </td>
                                        <td class="text-center small">
                                            {!! $sv['diemTK'] !== null ? '<span class="text-success fw-bold">'.number_format($sv['diemTK'],1).'</span>' : '<span class="badge bg-secondary-subtle text-secondary">Chưa</span>' !!}
                                        </td>
                                        <td class="small text-danger fw-semibold">
                                            @php
                                                $thieu = [];
                                                if ($sv['diemGVHD'] === null) $thieu[] = 'Phiếu GVHD';
                                                if ($sv['diemGVPB'] === null) $thieu[] = 'Phiếu GVPB';
                                                if ($sv['diemCT'] === null) $thieu[] = 'Chủ tịch';
                                                if ($sv['diemTK'] === null) $thieu[] = 'Thư ký';
                                            @endphp
                                            {{ implode(', ', $thieu) ?: 'Chưa hoàn tất' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center py-4 text-success fw-bold">
                                            <i class="fa-solid fa-check-circle me-1"></i> Tất cả sinh viên đã hoàn thành đầy đủ phiếu chấm! Không có hồ sơ tồn đọng.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @break

                {{-- ═════════════════════════════════════════════════════════════════ --}}
                {{-- MẪU 06: BÁO CÁO THỐNG KÊ THEO HỘI ĐỒNG                           --}}
                {{-- ═════════════════════════════════════════════════════════════════ --}}
                @case('mau_06')
                    <div class="p-3">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-sm align-middle mb-0">
                                <thead class="table-info text-nowrap text-center">
                                    <tr>
                                        <th style="width: 50px;">STT</th>
                                        <th>Tên Hội Đồng</th>
                                        <th>Ngành / Khoa</th>
                                        <th>Chủ Tịch &amp; Thư Ký</th>
                                        <th>Địa Điểm</th>
                                        <th>Số SV</th>
                                        <th>Đã Chấm Xong</th>
                                        <th>Số Đạt</th>
                                        <th>Không Đạt</th>
                                        <th>Điểm TB</th>
                                        <th>Tình Trạng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sttHd = 1; @endphp
                                    @foreach($data['statsTheoHoiDong'] as $stHd)
                                        <tr class="text-center">
                                            <td>{{ $sttHd++ }}</td>
                                            <td class="text-start fw-bold text-dark">{{ $stHd['tenHoiDong'] }}</td>
                                            <td class="small">{{ $stHd['nganh'] }}</td>
                                            <td class="small text-start">
                                                <div><strong>CT:</strong> {{ $stHd['chuTich'] }}</div>
                                                <div><strong>TK:</strong> {{ $stHd['thuKy'] }}</div>
                                            </td>
                                            <td class="small">{{ $stHd['diaDiem'] }}</td>
                                            <td class="fw-bold">{{ $stHd['daPhanCong'] }}</td>
                                            <td class="fw-bold text-primary">{{ $stHd['daHoanThanh'] }}</td>
                                            <td class="fw-bold text-success">{{ $stHd['soDat'] }}</td>
                                            <td class="fw-bold text-danger">{{ $stHd['soKhongDat'] }}</td>
                                            <td class="fw-bold fs-6">{{ number_format($stHd['diemTB'], 2) }}</td>
                                            <td>
                                                @if($stHd['isHoanThanh'])
                                                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1">Hoàn thành</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning rounded-pill px-2 py-1">Đang chấm</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @break

                {{-- ═════════════════════════════════════════════════════════════════ --}}
                {{-- MẪU 07: PHÂN BỐ ĐIỂM KHÓA LUẬN                                   --}}
                {{-- ═════════════════════════════════════════════════════════════════ --}}
                @case('mau_07')
                    <div class="p-3">
                        <div class="row g-3 mb-4">
                            <!-- Bảng 6 khoảng điểm thang 10 -->
                            <div class="col-md-7">
                                <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-chart-column text-primary me-1"></i> Phổ điểm theo 6 khoảng điểm thang 10</h6>
                                <table class="table table-sm table-bordered table-hover align-middle mb-0 bg-white">
                                    <thead class="table-light text-nowrap text-center">
                                        <tr>
                                            <th>Khoảng Điểm (Thang 10)</th>
                                            <th>Thang Chữ Tương Ứng</th>
                                            <th>Số Sinh Viên</th>
                                            <th>Tỷ Lệ (%)</th>
                                            <th>Đánh Giá</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $kbMap = [
                                                'duoi4'   => ['label' => 'Dưới 4.0', 'chu' => 'F', 'eval' => 'Chưa đạt (Kém)', 'color' => 'danger'],
                                                'tu4den49'=> ['label' => '4.0 - 4.9', 'chu' => 'D', 'eval' => 'Trung bình yếu', 'color' => 'warning'],
                                                'tu5den64'=> ['label' => '5.0 - 6.4', 'chu' => 'D+, C', 'eval' => 'Trung bình', 'color' => 'info'],
                                                'tu65den79'=>['label' => '6.5 - 7.9', 'chu' => 'C+, B', 'eval' => 'Khá', 'color' => 'primary'],
                                                'tu8den89'=> ['label' => '8.0 - 8.9', 'chu' => 'B+', 'eval' => 'Giỏi', 'color' => 'success'],
                                                'tu9den10'=> ['label' => '9.0 - 10.0', 'chu' => 'A', 'eval' => 'Xuất sắc', 'color' => 'success'],
                                            ];
                                            $tongDiemSV = array_sum($data['duLieuMauBaoCao']['khoangDiem']);
                                        @endphp
                                        @foreach($data['duLieuMauBaoCao']['khoangDiem'] as $kKey => $kCount)
                                            @php
                                                $pct = $tongDiemSV > 0 ? round(($kCount / $tongDiemSV) * 100, 2) : 0;
                                                $info = $kbMap[$kKey] ?? ['label' => $kKey, 'chu' => '', 'eval' => '', 'color' => 'secondary'];
                                            @endphp
                                            <tr class="text-center">
                                                <td class="fw-bold">{{ $info['label'] }}</td>
                                                <td><span class="badge bg-light text-dark border">{{ $info['chu'] }}</span></td>
                                                <td class="fw-bold fs-6">{{ $kCount }}</td>
                                                <td class="text-primary fw-semibold">{{ number_format($pct, 2) }}%</td>
                                                <td><span class="badge bg-{{ $info['color'] }}-subtle text-{{ $info['color'] }} px-2 py-1">{{ $info['eval'] }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Bảng phân bố thang điểm chữ A..F -->
                            <div class="col-md-5">
                                <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-font text-primary me-1"></i> Phân bố theo thang chữ (A → F)</h6>
                                <table class="table table-sm table-bordered table-hover align-middle mb-0 bg-white">
                                    <thead class="table-light text-nowrap text-center">
                                        <tr>
                                            <th>Điểm Chữ</th>
                                            <th>Thang 4</th>
                                            <th>Số Lượng</th>
                                            <th>Tỷ Lệ (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $chuThang4 = ['A' => 4.0, 'B+' => 3.5, 'B' => 3.0, 'C+' => 2.5, 'C' => 2.0, 'D+' => 1.5, 'D' => 1.0, 'F' => 0.0];
                                        @endphp
                                        @foreach($data['duLieuMauBaoCao']['diemChu'] as $chu => $count)
                                            @php $pctC = $tongDiemSV > 0 ? round(($count / $tongDiemSV) * 100, 2) : 0; @endphp
                                            <tr class="text-center">
                                                <td class="fw-bold text-primary">{{ $chu }}</td>
                                                <td>{{ number_format($chuThang4[$chu] ?? 0, 1) }}</td>
                                                <td class="fw-bold">{{ $count }}</td>
                                                <td class="text-muted">{{ number_format($pctC, 2) }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @break

                {{-- ═════════════════════════════════════════════════════════════════ --}}
                {{-- MẪU 08: BÁO CÁO TÌNH HÌNH TỔ CHỨC KHÓA LUẬN                      --}}
                {{-- ═════════════════════════════════════════════════════════════════ --}}
                @case('mau_08')
                    <div class="p-3">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-sm align-middle mb-0">
                                <thead class="table-primary text-nowrap text-center">
                                    <tr>
                                        <th style="width: 50px;">STT</th>
                                        <th>Ngành Đào Tạo</th>
                                        <th>Tổng SV Tham Gia</th>
                                        <th>SV Đã Vào Hội Đồng</th>
                                        <th>SV Chưa Vào HĐ</th>
                                        <th>Số Hội Đồng Lập Ra</th>
                                        <th>SV Hoàn Thành Chấm</th>
                                        <th>Tỷ Lệ Tổ Chức (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sttTc = 1; @endphp
                                    @foreach($data['statsTheoNganh'] as $stNg)
                                        @php
                                            $soHdNg = count(array_filter($data['statsTheoHoiDong'], fn($h) => $h['nganh'] === $stNg['tenNganh']));
                                            $pctTc = $stNg['soSV'] > 0 ? round(($stNg['soSV'] / max(1, $data['kpi']['tongSVThamGia'])) * 100, 1) : 0;
                                        @endphp
                                        <tr class="text-center">
                                            <td>{{ $sttTc++ }}</td>
                                            <td class="text-start fw-bold text-dark">{{ $stNg['tenNganh'] }}</td>
                                            <td class="fw-bold">{{ $stNg['soSV'] }}</td>
                                            <td class="text-primary fw-bold">{{ $stNg['soSV'] }}</td>
                                            <td class="text-muted">0</td>
                                            <td class="fw-bold">{{ $soHdNg > 0 ? $soHdNg : 1 }}</td>
                                            <td class="text-success fw-bold">{{ $stNg['soDat'] + $stNg['soKhongDat'] }}</td>
                                            <td class="fw-bold text-primary">{{ number_format($pctTc, 1) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @break

                {{-- ═════════════════════════════════════════════════════════════════ --}}
                {{-- MẪU 09: BÁO CÁO THỐNG KÊ THEO BỘ MÔN                             --}}
                {{-- ═════════════════════════════════════════════════════════════════ --}}
                @case('mau_09')
                    <div class="p-3">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-sm align-middle mb-0">
                                <thead class="table-dark text-nowrap text-center">
                                    <tr>
                                        <th style="width: 50px;">STT</th>
                                        <th>Mã Bộ Môn</th>
                                        <th>Tên Bộ Môn</th>
                                        <th>Số Sinh Viên</th>
                                        <th>Số Đạt</th>
                                        <th>Không Đạt</th>
                                        <th>Chưa Xong</th>
                                        <th>Tỷ Lệ Đạt (%)</th>
                                        <th>Điểm TB</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sttBm = 1; @endphp
                                    @foreach($data['statsTheoBoMon'] as $stBm)
                                        @php
                                            $tongDaCham = $stBm['soDat'] + $stBm['soKhongDat'];
                                            $tyLeBm = $tongDaCham > 0 ? round(($stBm['soDat'] / $tongDaCham) * 100, 2) : 0;
                                            $diemTBBm = !empty($stBm['scores']) ? round(array_sum($stBm['scores']) / count($stBm['scores']), 2) : 0;
                                        @endphp
                                        <tr class="text-center">
                                            <td>{{ $sttBm++ }}</td>
                                            <td class="fw-semibold text-secondary">{{ $stBm['maBoMon'] }}</td>
                                            <td class="text-start fw-bold text-dark">{{ $stBm['tenBoMon'] }}</td>
                                            <td class="fw-bold">{{ $stBm['soSV'] }}</td>
                                            <td class="text-success fw-bold">{{ $stBm['soDat'] }}</td>
                                            <td class="text-danger fw-bold">{{ $stBm['soKhongDat'] }}</td>
                                            <td class="text-warning fw-bold">{{ $stBm['soChuaXong'] }}</td>
                                            <td class="fw-bold text-primary">{{ number_format($tyLeBm, 2) }}%</td>
                                            <td class="fw-bold">{{ number_format($diemTBBm, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @break

                {{-- ═════════════════════════════════════════════════════════════════ --}}
                {{-- MẪU 10: BÁO CÁO THỐNG KÊ THEO HỌC PHẦN                           --}}
                {{-- ═════════════════════════════════════════════════════════════════ --}}
                @case('mau_10')
                    <div class="p-3">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-sm align-middle mb-0">
                                <thead class="table-info text-nowrap text-center">
                                    <tr>
                                        <th style="width: 50px;">STT</th>
                                        <th>Mã Học Phần</th>
                                        <th>Tên Học Phần Khóa Luận</th>
                                        <th>Số Sinh Viên</th>
                                        <th>Số Đạt</th>
                                        <th>Không Đạt</th>
                                        <th>Tỷ Lệ Đạt (%)</th>
                                        <th>Điểm TB</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sttHp = 1; @endphp
                                    @foreach($data['statsTheoHocPhan'] as $stHp)
                                        @php
                                            $tongDaChamHp = $stHp['soDat'] + $stHp['soKhongDat'];
                                            $tyLeHp = $tongDaChamHp > 0 ? round(($stHp['soDat'] / $tongDaChamHp) * 100, 2) : 0;
                                            $diemTBHp = !empty($stHp['scores']) ? round(array_sum($stHp['scores']) / count($stHp['scores']), 2) : 0;
                                        @endphp
                                        <tr class="text-center">
                                            <td>{{ $sttHp++ }}</td>
                                            <td class="fw-semibold text-secondary">{{ $stHp['maHocPhan'] }}</td>
                                            <td class="text-start fw-bold text-dark">{{ $stHp['tenHocPhan'] }}</td>
                                            <td class="fw-bold">{{ $stHp['soSV'] }}</td>
                                            <td class="text-success fw-bold">{{ $stHp['soDat'] }}</td>
                                            <td class="text-danger fw-bold">{{ $stHp['soKhongDat'] }}</td>
                                            <td class="fw-bold text-primary">{{ number_format($tyLeHp, 2) }}%</td>
                                            <td class="fw-bold">{{ number_format($diemTBHp, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @break

                {{-- ═════════════════════════════════════════════════════════════════ --}}
                {{-- MẪU 01 (MẶC ĐỊNH): BÁO CÁO TỔNG HỢP THEO ĐỢT & HỘI ĐỒNG          --}}
                {{-- ═════════════════════════════════════════════════════════════════ --}}
                @default
                    @if(empty($data['danhSachHoiDong']) || count($data['danhSachHoiDong']) === 0)
                        <div class="text-center py-5 text-muted small">Không có hội đồng nào phù hợp.</div>
                    @else
                        @foreach($data['danhSachHoiDong'] as $hdData)
                            <div class="border-bottom p-3">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark small text-uppercase">
                                            <i class="fa-solid fa-landmark text-primary me-1"></i>
                                            {{ $hdData['hoiDong']->TenHoiDong }}
                                        </h6>
                                        <div class="small text-muted">
                                            Ngành: {{ $hdData['hoiDong']->nganh?->TenNganh ?? 'Chung' }} &bull; 
                                            Phòng: {{ $hdData['hoiDong']->DiaDiem ?? 'HUIT' }} &bull; 
                                            CT: {{ $hdData['ct']->giangVien?->HoTen ?? '-' }} &bull;
                                            TK: {{ $hdData['tk']->giangVien?->HoTen ?? '-' }}
                                        </div>
                                    </div>
                                    <span class="badge {{ $hdData['isCongBo'] ? 'bg-success-subtle text-success' : 'bg-light text-secondary border' }}">
                                        {{ $hdData['isCongBo'] ? 'Đã công bố' : 'Chưa công bố' }}
                                    </span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover align-middle mb-0 bg-white">
                                        <thead class="table-light text-nowrap text-center">
                                            <tr>
                                                <th style="width: 45px;">STT</th>
                                                <th>MSSV</th>
                                                <th>Họ Và Tên</th>
                                                <th>Lớp</th>
                                                <th>Tên Đề Tài</th>
                                                <th>GVHD</th>
                                                <th>GVPB</th>
                                                <th>Điểm GVPB</th>
                                                <th>Điểm HĐ</th>
                                                <th>Điểm TK</th>
                                                <th>Điểm Chữ</th>
                                                <th>Kết Quả</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $sttSvHd = 1; @endphp
                                            @foreach($hdData['deTais'] as $dtItem)
                                                @foreach($dtItem['sinhViens'] as $sv)
                                                    <tr>
                                                        <td class="text-center">{{ $sttSvHd++ }}</td>
                                                        <td class="text-center fw-bold text-primary">{{ $sv['maSV'] }}</td>
                                                        <td class="fw-semibold text-dark text-nowrap">{{ $sv['hoTen'] }}</td>
                                                        <td class="text-center small">{{ $sv['tenLop'] }}</td>
                                                        <td class="small" style="max-width: 250px;">{{ $sv['tenDeTai'] }}</td>
                                                        <td class="small">{{ \App\Services\ThongKeService::formatLecturerName($sv['tenGVHD'] ?? $sv['gvhd'] ?? null) }}</td>
                                                        <td class="small">{{ \App\Services\ThongKeService::formatLecturerName($sv['tenGVPB'] ?? $sv['gvpb'] ?? null) }}</td>
                                                        <td class="text-center">{{ $sv['diemGVPB'] !== null ? number_format($sv['diemGVPB'], 2) : '-' }}</td>
                                                        <td class="text-center">{{ $sv['diemHD'] !== null ? number_format($sv['diemHD'], 2) : '-' }}</td>
                                                        <td class="text-center fw-bold text-primary fs-6">{{ $sv['diemTongKet'] !== null ? number_format($sv['diemTongKet'], 2) : '-' }}</td>
                                                        <td class="text-center fw-bold">{{ $sv['diemChu'] ?? '-' }}</td>
                                                        <td class="text-center">
                                                            @if($sv['isDuPhieu'])
                                                                @if($sv['isDat'])
                                                                    <span class="badge bg-success-subtle text-success px-2 py-0.5 rounded-pill">Đạt</span>
                                                                @else
                                                                    <span class="badge bg-danger-subtle text-danger px-2 py-0.5 rounded-pill">Chưa đạt</span>
                                                                @endif
                                                            @else
                                                                <span class="badge bg-warning-subtle text-warning px-2 py-0.5 rounded-pill">Chưa có kết quả</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @endif
            @endswitch
        @endif
    </div>
</div>
