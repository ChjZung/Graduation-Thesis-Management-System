<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo Thống Kê Tổng Quan Khóa Luận Tốt Nghiệp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #f8fafc;
            font-size: 14px;
        }
        .page-container {
            max-width: 900px;
            margin: 20px auto;
            background: #fff;
            padding: 40px 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-radius: 4px;
        }
        .table-print {
            border: 1px solid #000;
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        .table-print th, .table-print td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 13px;
        }
        .table-print th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
        }
        @media print {
            body {
                background: #fff;
            }
            .page-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print bg-dark text-white py-2 px-4 d-flex justify-content-between align-items-center shadow-sm">
    <div class="fw-bold">
        <i class="fa-solid fa-file-invoice me-2"></i> Bản Xem Trước In Báo Cáo Thống Kê Tổng Hợp Khóa Luận
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary btn-sm px-4 fw-bold d-inline-flex align-items-center" onclick="window.print()">
            <i class="fa-solid fa-print me-2"></i> In Báo Cáo / Lưu PDF
        </button>
        <button type="button" class="btn btn-outline-light btn-sm px-3" onclick="window.close()">
            Đóng
        </button>
    </div>
</div>

<div class="page-container">
    <!-- Header Công văn Trường -->
    <div class="row align-items-start mb-4">
        <div class="col-6 text-center">
            <div class="text-uppercase fw-bold" style="font-size: 13px;">BỘ CÔNG THƯƠNG</div>
            <div class="text-uppercase fw-bold text-decoration-underline" style="font-size: 13px;">TRƯỜNG ĐH CÔNG THƯƠNG TP.HCM</div>
            <div class="fw-bold mt-1" style="font-size: 13px;">KHOA CÔNG NGHỆ THÔNG TIN</div>
            <div class="small text-muted mt-1">Số: ...... /BC-CNTT-KLTN</div>
        </div>
        <div class="col-6 text-center">
            <div class="text-uppercase fw-bold" style="font-size: 13px;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
            <div class="fw-bold text-decoration-underline" style="font-size: 13px;">Độc lập - Tự do - Hạnh phúc</div>
            <div class="fst-italic mt-2" style="font-size: 12px;">TP. Hồ Chí Minh, ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}</div>
        </div>
    </div>

    <!-- Tiêu đề Báo Cáo -->
    <div class="text-center my-4">
        <h4 class="fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">BÁO CÁO THỐNG KÊ TỔNG HỢP</h4>
        <h5 class="fw-bold text-uppercase mb-1">CÔNG TÁC KHÓA LUẬN TỐT NGHIỆP</h5>
        <div class="fst-italic">Áp dụng: {{ $tenHocKy }}</div>
    </div>

    <!-- I. CHỈ SỐ TỔNG QUAN -->
    <div class="fw-bold mb-2 text-uppercase" style="font-size: 14px;">I. CHỈ SỐ TỔNG QUAN TOÀN HỆ THỐNG</div>
    <table class="table-print mb-4">
        <thead>
            <tr>
                <th style="width: 50px;">STT</th>
                <th>Hạng Mục Chỉ Số Quản Lý</th>
                <th style="width: 120px;">Số Lượng</th>
                <th style="width: 90px;">Đơn Vị</th>
                <th>Tình Trạng &amp; Đánh Giá</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Sinh viên đủ điều kiện làm khóa luận</td>
                <td class="text-center fw-bold">{{ $soSVDuDieuKien }}</td>
                <td class="text-center">Sinh viên</td>
                <td>Đã rà soát đủ điều kiện tín chỉ &amp; ĐTB tích lũy</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Sinh viên đã tham gia nhóm khóa luận</td>
                <td class="text-center fw-bold">{{ $soSVDaCoNhom }}</td>
                <td class="text-center">Sinh viên</td>
                <td>{{ $soSVDuDieuKien > 0 ? round(($soSVDaCoNhom / $soSVDuDieuKien) * 100) : 0 }}% sinh viên đủ điều kiện đã vào nhóm</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Tổng số nhóm sinh viên thành lập</td>
                <td class="text-center fw-bold">{{ $soNhomTong }}</td>
                <td class="text-center">Nhóm</td>
                <td>Trung bình 2-3 sinh viên / nhóm</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Tổng số đề tài khóa luận được đề xuất</td>
                <td class="text-center fw-bold">{{ $soDeTaiTong }}</td>
                <td class="text-center">Đề tài</td>
                <td>Đề xuất từ các bộ môn chuyên ngành</td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td>Đề tài đã hoàn thành xét duyệt 2 cấp (Công bố)</td>
                <td class="text-center fw-bold text-success">{{ $soDeTaiDaCongBo }}</td>
                <td class="text-center">Đề tài</td>
                <td>Trưởng bộ môn và Trưởng khoa đã phê duyệt</td>
            </tr>
            <tr>
                <td class="text-center">6</td>
                <td>Tổng số giảng viên tham gia công tác khóa luận</td>
                <td class="text-center fw-bold">{{ $soGiangVien }}</td>
                <td class="text-center">Cán bộ</td>
                <td>Đảm nhiệm hướng dẫn, phản biện và chấm điểm</td>
            </tr>
            <tr>
                <td class="text-center">7</td>
                <td>Hội đồng bảo vệ khóa luận dự kiến</td>
                <td class="text-center fw-bold">{{ $soHoiDong }}</td>
                <td class="text-center">Hội đồng</td>
                <td>Phục vụ công tác nghiệm thu bảo vệ đồ án</td>
            </tr>
        </tbody>
    </table>

    <!-- II. SỐ LIỆU THEO BỘ MÔN -->
    <div class="fw-bold mb-2 text-uppercase" style="font-size: 14px;">II. PHÂN BỔ SỐ LIỆU THEO BỘ MÔN CHUYÊN NGÀNH</div>
    <table class="table-print mb-4">
        <thead>
            <tr>
                <th style="width: 50px;">STT</th>
                <th style="width: 90px;">Mã BM</th>
                <th>Tên Bộ Môn</th>
                <th style="width: 80px;">Số GV</th>
                <th style="width: 90px;">Đề Tài Đề Xuất</th>
                <th style="width: 90px;">Đề Tài Đã Duyệt</th>
                <th style="width: 90px;">Nhóm ĐK</th>
                <th style="width: 80px;">Tỷ Lệ Duyệt</th>
            </tr>
        </thead>
        <tbody>
            @php
                $tongGV = 0; $tongDT = 0; $tongDTCongBo = 0; $tongNhom = 0;
            @endphp
            @foreach($thongKeBoMon as $idx => $bm)
            @php
                $tongGV += $bm['so_gv'];
                $tongDT += $bm['so_de_tai'];
                $tongDTCongBo += $bm['so_da_cong_bo'];
                $tongNhom += $bm['so_nhom'];
            @endphp
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-center fw-bold">{{ $bm['ma_bm'] }}</td>
                <td>{{ $bm['ten_bm'] }}</td>
                <td class="text-center">{{ $bm['so_gv'] }}</td>
                <td class="text-center">{{ $bm['so_de_tai'] }}</td>
                <td class="text-center fw-bold text-success">{{ $bm['so_da_cong_bo'] }}</td>
                <td class="text-center">{{ $bm['so_nhom'] }}</td>
                <td class="text-center">{{ $bm['ty_le_duyet'] }}%</td>
            </tr>
            @endforeach
            <tr class="fw-bold" style="background-color: #f8fafc;">
                <td colspan="3" class="text-center text-uppercase">Tổng Cộng</td>
                <td class="text-center">{{ $tongGV }}</td>
                <td class="text-center">{{ $tongDT }}</td>
                <td class="text-center text-success">{{ $tongDTCongBo }}</td>
                <td class="text-center">{{ $tongNhom }}</td>
                <td class="text-center">-</td>
            </tr>
        </tbody>
    </table>

    <!-- Chữ ký xác nhận -->
    <div class="row text-center mt-5" style="page-break-inside: avoid;">
        <div class="col-6">
            <div class="fw-bold text-uppercase">NGƯỜI LẬP BÁO CÁO</div>
            <div class="small text-muted fst-italic mb-5">(Ký và ghi rõ họ tên)</div>
            <div class="fw-bold mt-5">{{ Auth::user()->TenDangNhap ?? 'Quản trị viên' }}</div>
        </div>
        <div class="col-6">
            <div class="fw-bold text-uppercase">TRƯỞNG KHOA / BAN CHỦ NHIỆM</div>
            <div class="small text-muted fst-italic mb-5">(Ký, ghi rõ họ tên và đóng dấu)</div>
            <div class="fw-bold mt-5">TS. Nguyễn Văn A</div>
        </div>
    </div>
</div>

</body>
</html>
