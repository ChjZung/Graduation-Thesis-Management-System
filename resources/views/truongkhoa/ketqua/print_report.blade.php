<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo Tổng Hợp Kết Quả Khóa Luận Tốt Nghiệp — {{ $khoa->TenKhoa ?? 'Khoa CNTT' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111;
            background: #f8fafc;
        }
        .page-container {
            max-width: 1050px;
            margin: 20px auto;
            background: #fff;
            padding: 40px 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-radius: 8px;
        }
        @media print {
            body {
                background: #fff;
                margin: 0;
                padding: 0;
            }
            .page-container {
                max-width: 100%;
                margin: 0;
                padding: 15mm 20mm;
                box-shadow: none;
                border-radius: 0;
            }
            .no-print {
                display: none !important;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
        }
        .huit-header-title {
            text-transform: uppercase;
            font-weight: bold;
            font-size: 14px;
            letter-spacing: 0.5px;
        }
        .report-title {
            text-transform: uppercase;
            font-weight: bold;
            font-size: 19px;
            color: #003366;
            margin-top: 25px;
            margin-bottom: 5px;
            text-align: center;
        }
        table.table-report th {
            background-color: #f1f5f9 !important;
            color: #0f172a;
            font-weight: bold;
            border: 1px solid #cbd5e1 !important;
            vertical-align: middle;
            font-size: 12.5px;
            text-align: center;
        }
        table.table-report td {
            border: 1px solid #cbd5e1 !important;
            vertical-align: middle;
            font-size: 12.5px;
        }
    </style>
</head>
<body>

<div class="no-print bg-white border-bottom py-3 shadow-xs sticky-top">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-file-invoice text-primary me-2"></i>Xem Trước Văn Bản Báo Cáo Tổng Hợp Điểm</h6>
            <div class="text-muted small">Khổ giấy chuẩn A4 &bull; Sẵn sàng in hoặc Lưu dưới dạng PDF</div>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                <i class="fa-solid fa-print me-2"></i> In Báo Cáo / Lưu PDF
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary rounded-pill px-3">
                Đóng
            </button>
        </div>
    </div>
</div>

<div class="page-container">
    <!-- Header Quốc Hiệu & Trường -->
    <div class="row align-items-start pb-3">
        <div class="col-6 text-center">
            <div class="huit-header-title">BỘ CÔNG THƯƠNG</div>
            <div class="huit-header-title fw-bold" style="color: #003366;">TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP.HCM</div>
            <div class="fw-bold" style="font-size: 13px;">KHOA {{ mb_strtoupper($khoa->TenKhoa ?? 'CÔNG NGHỆ THÔNG TIN') }}</div>
            <div style="width: 140px; border-bottom: 1px solid #000; margin: 4px auto 0 auto;"></div>
        </div>
        <div class="col-6 text-center">
            <div class="huit-header-title fw-bold">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
            <div class="fw-bold" style="font-size: 13px;">Độc lập - Tự do - Hạnh phúc</div>
            <div style="width: 160px; border-bottom: 1px solid #000; margin: 4px auto 0 auto;"></div>
            <div class="fst-italic text-muted mt-2" style="font-size: 12px;">
                TP. Hồ Chí Minh, ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}
            </div>
        </div>
    </div>

    <!-- Tựa Đề Báo Cáo -->
    <div class="report-title">
        BÁO CÁO TỔNG HỢP KẾT QUẢ ĐÁNH GIÁ KHÓA LUẬN TỐT NGHIỆP
    </div>
    <div class="text-center text-secondary small mb-4">
        Học kỳ: <strong>{{ $hocKy->TenHocKy ?? $hocKy->MaHocKy ?? 'Hiện tại' }}</strong> &bull; 
        Khoa: <strong>{{ $khoa->TenKhoa ?? 'Công nghệ Thông tin' }}</strong>
    </div>

    <!-- Tóm Tắt Số Liệu Đợt Bảo Vệ -->
    @php
        $totalSV = $kqs->count();
        $scores = $kqs->map(fn($k) => (float)($k->DiemChinhThuc ?? $k->DiemTongKet ?? $k->DiemHoiDongTB))->filter(fn($v) => $v > 0);
        $avgScore = $scores->isNotEmpty() ? round($scores->avg(), 2) : 0;
        $maxScore = $scores->isNotEmpty() ? round($scores->max(), 2) : 0;
        $minScore = $scores->isNotEmpty() ? round($scores->min(), 2) : 0;
        $countDat = $scores->filter(fn($v) => $v >= 5.0)->count();
        $pctDat = $scores->isNotEmpty() ? round(($countDat / $scores->count()) * 100, 1) : 0;
    @endphp

    <div class="p-3 bg-light rounded border mb-4">
        <div class="row g-2 text-center" style="font-size: 13px;">
            <div class="col-3">
                <span class="text-muted d-block">Tổng số sinh viên:</span>
                <strong class="fs-6 text-dark">{{ $totalSV }}</strong>
            </div>
            <div class="col-3">
                <span class="text-muted d-block">Số lượng Hội đồng:</span>
                <strong class="fs-6 text-dark">{{ $hoiDongs->count() }}</strong>
            </div>
            <div class="col-3">
                <span class="text-muted d-block">Điểm Trung Bình Đợt:</span>
                <strong class="fs-6 text-primary">{{ number_format($avgScore, 2) }}</strong>
            </div>
            <div class="col-3">
                <span class="text-muted d-block">Tỷ lệ Đạt yêu cầu:</span>
                <strong class="fs-6 text-success">{{ $pctDat }}%</strong> ({{ $countDat }}/{{ $scores->count() }})
            </div>
        </div>
    </div>

    <!-- Bảng Chi Tiết Kết Quả Sinh Viên -->
    <h6 class="fw-bold text-dark mb-2" style="font-size: 13.5px;">I. DANH SÁCH ĐIỂM ĐÁNH GIÁ CHÍNH THỨC TỪNG SINH VIÊN</h6>
    <table class="table table-bordered table-report mb-4">
        <thead>
            <tr>
                <th style="width: 40px;">STT</th>
                <th style="width: 100px;">MSSV</th>
                <th>Họ và Tên Sinh Viên</th>
                <th style="width: 100px;">Lớp</th>
                <th>Đề Tài Khóa Luận</th>
                <th style="width: 130px;">Hội Đồng</th>
                <th style="width: 80px;">Điểm Số</th>
                <th style="width: 65px;">Điểm Chữ</th>
                <th style="width: 85px;">Kết Quả</th>
                <th style="width: 95px;">Trạng Thái</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kqs as $idx => $kq)
                @php
                    $diem = (float)($kq->DiemChinhThuc ?? $kq->DiemTongKet ?? $kq->DiemHoiDongTB ?? 0);
                    $isPass = ($diem >= 5.0);
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-monospace text-center fw-bold">{{ $kq->MaSV }}</td>
                    <td><strong>{{ $kq->sinhVien->HoTen ?? 'N/A' }}</strong></td>
                    <td class="text-center">{{ $kq->sinhVien->lop->TenLop ?? ($kq->sinhVien->MaLop ?? 'N/A') }}</td>
                    <td style="overflow-wrap: anywhere; word-break: break-word;">{{ $kq->hoSoBaoVe?->deTai?->TenDeTai ?? 'N/A' }}</td>
                    <td style="font-size: 11.5px;">{{ $kq->hoSoBaoVe?->hoiDong?->TenHoiDong ?? 'N/A' }}</td>
                    <td class="text-center fw-bold text-primary">{{ number_format($diem, 2) }}</td>
                    <td class="text-center fw-bold">{{ $kq->diem_chu }}</td>
                    <td class="text-center {{ $isPass ? 'text-success fw-bold' : 'text-danger fw-bold' }}">
                        {{ $isPass ? 'Đạt' : 'Không đạt' }}
                    </td>
                    <td class="text-center" style="font-size: 11.5px;">
                        {{ $kq->TrangThai ?? 'Chưa xác định' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-4 text-muted">Không có dữ liệu sinh viên trong đợt này.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Chữ Ký Phê Duyệt -->
    <div class="row pt-4" style="page-break-inside: avoid;">
        <div class="col-4 text-center">
            <div class="fw-bold text-uppercase" style="font-size: 12.5px;">Người Lập Bảng</div>
            <div class="text-muted fst-italic" style="font-size: 11.5px;">(Ký và ghi rõ họ tên)</div>
            <div style="height: 75px;"></div>
            <div class="fw-bold">Giáo vụ Khoa</div>
        </div>
        <div class="col-4 text-center">
            <div class="fw-bold text-uppercase" style="font-size: 12.5px;">Trưởng Bộ Môn</div>
            <div class="text-muted fst-italic" style="font-size: 11.5px;">(Ký và ghi rõ họ tên)</div>
            <div style="height: 75px;"></div>
            <div class="fw-bold">Ban Chủ Nhiệm Bộ Môn</div>
        </div>
        <div class="col-4 text-center">
            <div class="fw-bold text-uppercase" style="font-size: 12.5px;">Trưởng Khoa Phê Duyệt</div>
            <div class="text-muted fst-italic" style="font-size: 11.5px;">(Ký, đóng dấu và ghi rõ họ tên)</div>
            <div style="height: 75px;"></div>
            <div class="fw-bold text-primary">{{ $khoa->TruongKhoa ?? 'Trưởng Khoa' }}</div>
        </div>
    </div>
</div>

</body>
</html>
