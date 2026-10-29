<div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; min-width: 0; max-width: 100%;">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-users-viewfinder text-primary me-2"></i>
            Danh Sách Hội Đồng &amp; Tiến Độ Chấm Điểm (<span id="span-total-hoidong">{{ $hoiDongs->count() }}</span> hội đồng)
        </h6>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border">
                <i class="fa-solid fa-info-circle me-1"></i> Bảng chuẩn dạng cột
            </span>
        </div>
    </div>
    <div class="card-body p-0" style="min-width: 0; overflow: hidden;">
        @if($hoiDongs->isEmpty())
            <div class="text-center py-5 px-3">
                <i class="fa-solid fa-folder-open text-muted fa-3x mb-3 opacity-50"></i>
                <h6 class="fw-bold text-secondary mb-1">Không tìm thấy kết quả phù hợp</h6>
                <p class="small text-muted mb-0">Vui lòng thay đổi từ khóa tìm kiếm hoặc kiểm tra lại các bộ lọc học kỳ.</p>
            </div>
        @else
            <div class="table-scroll-wrapper position-relative w-100" style="min-width: 0;">
                <div class="table-responsive custom-table-scroll" style="max-height: 70vh; overflow-x: scroll !important; overflow-y: auto !important; max-width: 100%;">
                    <table class="table table-hover align-middle mb-0" id="tableHoiDong" style="font-size: 13px; table-layout: fixed; width: max-content; min-width: 100%;">
                        <colgroup>
                            <col style="width: 50px; min-width: 50px;">
                            <col style="width: 220px; min-width: 220px;">
                            <col style="width: 150px; min-width: 150px;">
                            <col style="width: 160px; min-width: 160px;">
                            <col style="width: 150px; min-width: 150px;">
                            <col style="width: 210px; min-width: 210px;">
                            <col style="width: 110px; min-width: 110px;">
                            <col style="width: 180px; min-width: 180px;">
                            <col style="width: 150px; min-width: 150px;">
                            <col style="width: 140px; min-width: 140px;">
                        </colgroup>
                        <thead class="sticky-top">
                            <tr>
                                <th style="width: 50px; min-width: 50px; max-width: 50px;" class="text-center sticky-col-hd-stt">STT</th>
                                <th style="width: 220px; min-width: 220px; max-width: 220px;" class="sticky-col-hd-name">Tên Hội Đồng &amp; Mã</th>
                                <th style="width: 150px; min-width: 150px; max-width: 150px;">Chuyên Ngành / Bộ Môn</th>
                                <th style="width: 160px; min-width: 160px; max-width: 160px;">Chủ Tịch Hội Đồng</th>
                                <th style="width: 150px; min-width: 150px; max-width: 150px;">Thư Ký</th>
                                <th style="width: 210px; min-width: 210px; max-width: 210px;">Các Ủy Viên &amp; Phản Biện</th>
                                <th style="width: 110px; min-width: 110px; max-width: 110px;" class="text-center">Số Đề Tài</th>
                                <th style="width: 180px; min-width: 180px; max-width: 180px;" class="text-center">Ngày BV &amp; Địa Điểm</th>
                                <th style="width: 150px; min-width: 150px; max-width: 150px;" class="text-center">Trạng Thái</th>
                                <th style="width: 140px; min-width: 140px; max-width: 140px;" class="text-end pe-3">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hoiDongs as $idx => $hd)
                                @php
                                    $td = $tienDoHoiDongs[$hd->MaHoiDong] ?? [];
                                    $duPhieu = $td['hoiDongDuPhieu'] ?? false;
                                    $soDeTai = $td['tongSoDeTai'] ?? 0;
                                    $soDeTaiDu = $td['soDeTaiDuPhieu'] ?? 0;
                                    $totalMembers = $td['totalMembers'] ?? 0;

                                    $daCongBo = false;
                                    $coHoSo = $hd->hoSoBaoVes->isNotEmpty();
                                    if ($coHoSo) {
                                        $daCongBo = $hd->hoSoBaoVes->every(fn($h) => in_array($h->TrangThaiKetQua, ['DaCongBo', 'DaHoanThanh', 'CoYeuCauChinhSua']));
                                    }

                                    $chuTich = $hd->thanhViens->firstWhere('VaiTro', 'Chủ tịch');
                                    $thuKy = $hd->thanhViens->firstWhere('VaiTro', 'Thư ký');
                                    $uyViens = $hd->thanhViens->filter(fn($tv) => !in_array($tv->VaiTro, ['Chủ tịch', 'Thư ký']));
                                    $boMonHd = $hd->thanhViens->first()?->giangVien?->boMon?->TenBoMon ?? ($hd->khoa?->TenKhoa ?? 'Khoa CNTT');

                                    $rawDiaDiem = $hd->DiaDiem ?? '';
                                    $cleanDiaDiem = trim(preg_replace('/\s*\(.*?\)/u', '', $rawDiaDiem));
                                    if (empty($cleanDiaDiem)) {
                                        $cleanDiaDiem = 'Phòng BV';
                                    }
                                    $ngayBV = $hd->NgayBaoVe ? \Carbon\Carbon::parse($hd->NgayBaoVe)->format('d/m/Y') : '';
                                    $gioBV = ($hd->NgayBaoVe && \Carbon\Carbon::parse($hd->NgayBaoVe)->format('H:i') !== '00:00') ? \Carbon\Carbon::parse($hd->NgayBaoVe)->format('H:i') : '';
                                    $fullHdLocation = trim(($ngayBV ? $ngayBV . ' ' : '') . ($gioBV ? $gioBV . ' • ' : '') . $rawDiaDiem);
                                @endphp
                                <tr>
                                    <td class="text-center text-muted sticky-col-hd-stt">{{ $idx + 1 }}</td>
                                    <td class="sticky-col-hd-name">
                                        <div class="fw-bold text-dark line-clamp-2" 
                                             title="{{ $hd->TenHoiDong }}">
                                            {{ $hd->TenHoiDong }}
                                        </div>
                                        <span class="badge bg-light text-secondary border font-monospace mt-1" style="font-size: 11px; white-space: nowrap;">
                                            Mã: {{ $hd->MaHoiDong }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-secondary small line-clamp-2" title="{{ $boMonHd }}">
                                            {{ $boMonHd }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($chuTich)
                                            <div class="fw-bold text-danger line-clamp-1" title="{{ $chuTich->giangVien->HoTen ?? $chuTich->MaGV }}">
                                                <i class="fa-solid fa-crown text-warning me-1"></i>
                                                {{ $chuTich->giangVien->HoTen ?? $chuTich->MaGV }}
                                            </div>
                                            <div class="text-muted font-monospace line-clamp-1" style="font-size: 11px;">{{ $chuTich->giangVien->HocVi ?? 'Giảng viên' }}</div>
                                        @else
                                            <span class="text-muted fst-italic small">Chưa phân công</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($thuKy)
                                            <div class="fw-bold text-primary line-clamp-1" title="{{ $thuKy->giangVien->HoTen ?? $thuKy->MaGV }}">
                                                {{ $thuKy->giangVien->HoTen ?? $thuKy->MaGV }}
                                            </div>
                                            <div class="text-muted font-monospace line-clamp-1" style="font-size: 11px;">{{ $thuKy->giangVien->HocVi ?? 'Giảng viên' }}</div>
                                        @else
                                            <span class="text-muted fst-italic small">Chưa phân công</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($uyViens->isNotEmpty())
                                            <div class="small">
                                                @foreach($uyViens as $uv)
                                                    <div class="line-clamp-1 mb-1" style="font-size: 11.5px;" title="{{ $uv->giangVien->HoTen ?? $uv->MaGV }} ({{ $uv->VaiTro ?? 'Ủy viên' }})">
                                                        &bull; <strong>{{ $uv->giangVien->HoTen ?? $uv->MaGV }}</strong>
                                                        <span class="text-muted">({{ $uv->VaiTro ?? 'Ủy viên' }})</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted fst-italic small">Chưa có ủy viên</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        <span class="badge bg-light text-dark border px-2 py-1 font-monospace fw-bold fs-6">
                                            {{ $soDeTai }}
                                        </span>
                                        <div class="small mt-1 {{ $soDeTaiDu === $soDeTai && $soDeTai > 0 ? 'text-success fw-bold' : 'text-danger' }}" style="font-size: 11px; white-space: nowrap;">
                                            {{ $soDeTaiDu }}/{{ $soDeTai }} đủ phiếu
                                        </div>
                                    </td>
                                    <td class="text-center small" style="width: 180px; min-width: 180px; max-width: 180px; overflow: hidden;" title="{{ $fullHdLocation }}">
                                        @if($ngayBV)
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 100%;">
                                                <i class="fa-regular fa-calendar-days text-primary me-1"></i>{{ $ngayBV }}
                                            </div>
                                            <div class="text-muted text-truncate" style="font-size: 11.5px; max-width: 100%;">
                                                @if($gioBV)
                                                    <span class="fw-semibold text-secondary">{{ $gioBV }}</span> &bull;
                                                @endif
                                                <i class="fa-solid fa-location-dot me-1 text-danger"></i>{{ $cleanDiaDiem }}
                                            </div>
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        @if($daCongBo)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fw-bold" style="white-space: nowrap; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-circle-check me-1"></i> Đã công bố
                                            </span>
                                        @elseif($duPhieu)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 fw-bold" style="white-space: nowrap; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-check-double me-1"></i> Đã đủ phiếu
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fw-bold" style="white-space: nowrap; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-hourglass-half me-1"></i> Đang chấm (thiếu phiếu)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3" style="white-space: nowrap;">
                                        <div class="d-inline-flex gap-1 justify-content-end" style="white-space: nowrap;">
                                            <a href="{{ route('truongkhoa.ketqua.hoidong', $hd->MaHoiDong) }}" class="btn btn-xs btn-primary rounded-pill px-3 py-1 fw-bold shadow-xs" title="Xem chi tiết và đối chiếu điểm">
                                                <i class="fa-solid fa-table-cells me-1"></i> Chi tiết
                                            </a>

                                            @if(!$duPhieu)
                                                <form action="{{ route('truongkhoa.ketqua.nhacnho') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="MaHoiDong" value="{{ $hd->MaHoiDong }}">
                                                    <button type="submit" class="btn btn-xs btn-outline-warning rounded-pill px-2 py-1 fw-bold" title="Nhắc nhở giám khảo chưa chấm">
                                                        <i class="fa-regular fa-bell"></i>
                                                    </button>
                                                </form>
                                            @elseif(!$daCongBo)
                                                <button type="button" class="btn btn-xs btn-success rounded-pill px-2 py-1 fw-bold btn-congbo-hoidong" 
                                                    data-mahoidong="{{ $hd->MaHoiDong }}" data-tenhoidong="{{ $hd->TenHoiDong }}" title="Công bố kết quả Hội đồng">
                                                    <i class="fa-solid fa-bullhorn"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 fw-bold btn-thuhoi-hoidong" 
                                                    data-mahoidong="{{ $hd->MaHoiDong }}" data-tenhoidong="{{ $hd->TenHoiDong }}" title="Thu hồi công bố kết quả Hội đồng">
                                                    <i class="fa-solid fa-undo"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
