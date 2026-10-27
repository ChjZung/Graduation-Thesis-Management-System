<div class="row g-3">
    @forelse($hoiDongs as $hd)
    @php
        $td = $tienDoHoiDongs[$hd->MaHoiDong] ?? [];
        $duPhieu = $td['hoiDongDuPhieu'] ?? false;
        $soDeTai = $td['tongSoDeTai'] ?? 0;
        $soDeTaiDu = $td['soDeTaiDuPhieu'] ?? 0;
        $totalMembers = $td['totalMembers'] ?? 0;

        // Kiểm tra xem hội đồng đã công bố chưa
        $daCongBo = false;
        $coHoSo = $hd->hoSoBaoVes->isNotEmpty();
        if ($coHoSo) {
            $daCongBo = $hd->hoSoBaoVes->every(fn($h) => in_array($h->TrangThaiKetQua, ['DaCongBo', 'DaHoanThanh', 'CoYeuCauChinhSua']));
        }
    @endphp
    <div class="col-lg-6 col-xl-6">
        <div class="card border-0 shadow-sm h-100 rounded-3 position-relative" style="border-top: 4px solid {{ $daCongBo ? '#198754' : ($duPhieu ? '#0072ce' : '#ffc107') }} !important;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-light text-secondary border px-2 py-1 mb-1 font-monospace">{{ $hd->MaHoiDong }}</span>
                        <h5 class="fw-bold text-dark mb-1">{{ $hd->TenHoiDong }}</h5>
                        <div class="small text-muted">
                            <i class="fa-solid fa-location-dot me-1 text-danger"></i> {{ $hd->DiaDiem ?? 'Phòng B.304' }} &bull; 
                            <i class="fa-regular fa-calendar-days me-1 text-primary"></i> {{ \Carbon\Carbon::parse($hd->NgayBaoVe ?? now())->format('d/m/Y') }}
                        </div>
                    </div>
                    <div>
                        @if($daCongBo)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold">
                                <i class="fa-solid fa-circle-check me-1"></i> Đã Công Bố Kết Quả
                            </span>
                        @elseif($duPhieu)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                                <i class="fa-solid fa-check-double me-1"></i> Đã Đủ Phiếu Hợp Lệ
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2 fw-semibold">
                                <i class="fa-solid fa-hourglass-half me-1"></i> Chưa Đủ Phiếu Chấm
                            </span>
                        @endif
                    </div>
                </div>

                <hr class="my-3 opacity-25">

                <div class="row g-2 mb-3 small">
                    <div class="col-6">
                        <span class="text-muted">Số lượng giám khảo:</span>
                        <strong class="text-dark ms-1">{{ $totalMembers }} Giảng viên</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted">Đề tài bảo vệ:</span>
                        <strong class="text-dark ms-1">{{ $soDeTai }} nhóm</strong>
                    </div>
                    <div class="col-12">
                        <span class="text-muted">Tiến độ nộp phiếu:</span>
                        <strong class="{{ $soDeTaiDu === $soDeTai && $soDeTai > 0 ? 'text-success' : 'text-danger' }} ms-1">
                            {{ $soDeTaiDu }} / {{ $soDeTai }} đề tài đã có đủ phiếu giám khảo
                        </strong>
                    </div>
                </div>

                <!-- Danh sách các thành viên hội đồng -->
                <div class="p-2 bg-light-subtle rounded-2 border mb-3">
                    <div class="small fw-bold text-secondary mb-1">Thành phần Hội đồng:</div>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($hd->thanhViens as $tv)
                        <span class="badge bg-white text-dark border small fw-normal py-1 px-2">
                            <strong>{{ $tv->giangVien->HoTen ?? $tv->MaGV }}</strong> ({{ $tv->VaiTro ?? 'Thành viên' }})
                        </span>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top">
                    <a href="{{ route('truongkhoa.ketqua.hoidong', $hd->MaHoiDong) }}" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fa-solid fa-table-cells me-2"></i> Xem Chi Tiết &amp; Đối Chiếu Điểm
                    </a>

                    @if(!$duPhieu)
                    <form action="{{ route('truongkhoa.ketqua.nhacnho') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="MaHoiDong" value="{{ $hd->MaHoiDong }}">
                        <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold">
                            <i class="fa-regular fa-bell me-1"></i> Nhắc Giám Khảo Chưa Chấm
                        </button>
                    </form>
                    @elseif(!$daCongBo)
                    <form action="{{ route('truongkhoa.ketqua.congbo') }}" method="POST" class="d-inline" onsubmit="return confirm('Trưởng khoa có chắc chắn muốn CÔNG BỐ KẾT QUẢ chính thức cho Hội đồng {{ $hd->TenHoiDong }}?\n\nĐiểm số sẽ được khóa và gửi thông báo đến tất cả sinh viên và giảng viên.');">
                        @csrf
                        <input type="hidden" name="MaHoiDong" value="{{ $hd->MaHoiDong }}">
                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-xs">
                            <i class="fa-solid fa-bullhorn me-1"></i> Công Bố Kết Quả &amp; Chuyển Lưu Trữ
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted bg-white">
            <i class="fa-solid fa-clipboard-list fa-3x mb-3 text-secondary opacity-50"></i>
            <h6 class="fw-bold text-secondary mb-1">Không tìm thấy kết quả phù hợp</h6>
            <p class="small text-muted mb-0">Vui lòng thử thay đổi từ khóa tìm kiếm (tên hội đồng, phòng, mã HĐ) hoặc chọn lại học kỳ.</p>
        </div>
    </div>
    @endforelse
</div>
