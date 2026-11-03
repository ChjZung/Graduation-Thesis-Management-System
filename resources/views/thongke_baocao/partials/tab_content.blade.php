@if(!empty($dataDot['canhBaoChuaHoanTat']))
    <div class="alert alert-warning border rounded-3 mb-3 py-2 px-3 d-flex align-items-center gap-2 small">
        <i class="fa-solid fa-triangle-exclamation text-warning"></i>
        <div>{{ $dataDot['canhBaoChuaHoanTat'] }} (Gắn nhãn <strong>Chưa có kết quả</strong>, tách riêng khỏi tỷ lệ Đạt).</div>
    </div>
@endif

@if(!empty($dataDot))
    <div class="mb-3 text-muted small">
        Đợt: <strong class="text-dark">{{ $dataDot['hocKy']->TenHocKy ?? 'N/A' }}</strong> &bull; 
        <strong>{{ $dataDot['tongSoHoiDong'] }}</strong> hội đồng &bull; 
        <strong>{{ $dataDot['tongSoDeTai'] }}</strong> đề tài &bull; 
        <strong>{{ $dataDot['tongSoSV'] }}</strong> sinh viên
    </div>

    <div class="row g-2 mb-4">
        <div class="col-6 col-lg">
            <div class="card border shadow-none h-100 bg-white">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Hội đồng / Đề tài</div>
                    <h4 class="fw-bold mt-1 mb-0 text-dark">{{ $dataDot['tongSoHoiDong'] }} / {{ $dataDot['tongSoDeTai'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border shadow-none h-100 bg-white">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Đạt ({{ number_format($dataDot['tyLeDat'], 1) }}%)</div>
                    <h4 class="fw-bold mt-1 mb-0 text-success">{{ $dataDot['tongDat'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border shadow-none h-100 bg-white">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Không đạt</div>
                    <h4 class="fw-bold mt-1 mb-0 text-danger">{{ $dataDot['tongKhongDat'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border shadow-none h-100 bg-white">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Chưa có KQ</div>
                    <h4 class="fw-bold mt-1 mb-0 text-warning">{{ $dataDot['tongChuaXong'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border shadow-none h-100 bg-white">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Điểm TB</div>
                    <h4 class="fw-bold mt-1 mb-0 text-primary">{{ number_format($dataDot['diemTB'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    @if(empty($dataDot['danhSachHoiDong']) || count($dataDot['danhSachHoiDong']) === 0)
        <div class="card border shadow-none rounded-3 bg-white text-center py-5">
            <p class="text-muted small mb-0">Không có dữ liệu phù hợp với bộ lọc.</p>
        </div>
    @else
        @foreach($dataDot['danhSachHoiDong'] as $hdData)
        <div class="card border shadow-none rounded-3 mb-3 bg-white">
            <div class="card-header bg-white py-2.5 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
                <div>
                    <h6 class="fw-bold mb-0 text-dark small text-uppercase">
                        {{ $hdData['hoiDong']->TenHoiDong }}
                    </h6>
                    <div class="small text-muted mt-0.5">
                        Ngành: {{ $hdData['hoiDong']->nganh?->TenNganh ?? 'Chung' }} &bull; 
                        Phòng: {{ $hdData['hoiDong']->DiaDiem ?? 'HUIT' }} &bull; 
                        CT: {{ $hdData['ct']->giangVien?->HoTen ?? '-' }}, 
                        TK: {{ $hdData['tk']->giangVien?->HoTen ?? '-' }}, 
                        UV: {{ $hdData['uvs']->pluck('giangVien.HoTen')->filter()->join(', ') ?: '-' }}
                    </div>
                </div>
                <span class="badge {{ $hdData['isCongBo'] ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                    {{ $hdData['isCongBo'] ? 'Đã công bố' : 'Dự thảo' }}
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 table-reports small">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">STT</th>
                                <th style="width: 90px;">MSSV</th>
                                <th style="width: 140px;">Họ và tên</th>
                                <th style="width: 75px;">Lớp</th>
                                <th style="width: 55px;">Nhóm</th>
                                <th style="min-width: 200px;">Đề tài</th>
                                <th style="width: 130px;">GVHD</th>
                                <th class="text-center">CT</th>
                                <th class="text-center">TK</th>
                                @foreach($hdData['uvs'] as $uvIdx => $uvItem)
                                    <th class="text-center" title="{{ $uvItem->giangVien?->HoTen }}">UV{{ $uvIdx + 1 }}</th>
                                @endforeach
                                <th class="text-center">Điểm HĐ</th>
                                <th class="text-center">GVHD</th>
                                <th class="text-center">GVPB</th>
                                <th class="text-center">Tổng</th>
                                <th class="text-center">Chữ</th>
                                <th class="text-center">Xếp loại</th>
                                <th class="text-center">Kết quả</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $sttHD = 1; @endphp
                            @foreach($hdData['deTais'] as $dt)
                                @foreach($dt['sinhViens'] as $svRow)
                                <tr>
                                    <td class="text-center text-muted">{{ $sttHD++ }}</td>
                                    <td class="fw-semibold">{{ $svRow['maSV'] }}</td>
                                    <td class="fw-semibold text-dark text-nowrap">{{ $svRow['hoTen'] }}</td>
                                    <td class="text-muted">{{ $svRow['tenLop'] ?? $svRow['lop'] ?? '' }}</td>
                                    <td>{{ $dt['nhom']->TenNhom ?? '' }}</td>
                                    <td>
                                        <div class="line-clamp-2" title="{{ $dt['deTai']->TenDeTai ?? '' }}">
                                            {{ $dt['deTai']->TenDeTai ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="text-nowrap">{{ $dt['gvhd']?->HoTen ?? '-' }}</td>
                                    <td class="text-center">{{ ($svRow['diemCT'] ?? null) !== null ? number_format($svRow['diemCT'], 2) : '-' }}</td>
                                    <td class="text-center">{{ ($svRow['diemTK'] ?? null) !== null ? number_format($svRow['diemTK'], 2) : '-' }}</td>
                                    @foreach(($svRow['diemUVs'] ?? []) as $uScore)
                                        <td class="text-center">{{ ($uScore['diem'] ?? null) !== null ? number_format($uScore['diem'], 2) : '-' }}</td>
                                    @endforeach
                                    <td class="text-center fw-semibold bg-light">{{ ($svRow['diemHD'] ?? null) !== null ? number_format($svRow['diemHD'], 2) : '-' }}</td>
                                    <td class="text-center">{{ ($svRow['diemGVHD'] ?? null) !== null ? number_format($svRow['diemGVHD'], 2) : '-' }}</td>
                                    <td class="text-center">{{ ($svRow['diemGVPB'] ?? null) !== null ? number_format($svRow['diemGVPB'], 2) : '-' }}</td>
                                    <td class="text-center fw-bold text-primary bg-light">{{ ($svRow['diemTongKet'] ?? null) !== null ? number_format($svRow['diemTongKet'], 2) : '-' }}</td>
                                    <td class="text-center fw-semibold">{{ $svRow['diemChu'] ?? '-' }}</td>
                                    <td class="text-center">{{ $svRow['xepLoai'] ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($svRow['isDuPhieu'])
                                            <span class="badge {{ $svRow['isDat'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                {{ $svRow['ketQua'] }}
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning">Chưa có KQ</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-semibold">
                                <td colspan="7">
                                    Tổng: {{ $hdData['soDeTai'] }} đề tài ({{ $hdData['soSV'] }} SV) &bull; 
                                    Đạt: <span class="text-success">{{ $hdData['soDat'] }}</span> &bull; 
                                    Không đạt: <span class="text-danger">{{ $hdData['soKhongDat'] }}</span>
                                    @if($hdData['soChuaXong'] > 0)
                                        &bull; Chưa xong: <span class="text-warning">{{ $hdData['soChuaXong'] }}</span>
                                    @endif
                                </td>
                                <td colspan="{{ 6 + count($hdData['uvs']) }}" class="text-end text-muted">Điểm TB:</td>
                                <td class="text-center text-primary">{{ number_format($hdData['diemTB'], 2) }}</td>
                                <td colspan="3" class="text-center text-success">Đạt: {{ number_format($hdData['tyLeDat'], 1) }}%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    @endif
@endif