<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8">
<title>Quyết định và danh sách hội đồng bảo vệ theo đợt</title>
<style>
@page { margin: 18mm 14mm; }
body { font-family: 'DejaVu Serif', serif; font-size: 10pt; line-height: 1.4; color: #111; }
.center { text-align: center; } .bold { font-weight: bold; } .italic { font-style: italic; }
.heading { width: 100%; border-collapse: collapse; margin-bottom: 22px; }
.heading td { width: 50%; text-align: center; vertical-align: top; font-size: 9pt; }
h1 { font-size: 14pt; text-align: center; margin: 24px 0 8px; }
h2 { font-size: 11pt; margin: 18px 0 7px; page-break-after: avoid; }
p { text-align: justify; margin: 9px 0; } .appendix { page-break-before: always; }
.data { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 8.5pt; margin: 8px 0 18px; }
.data td, .data th { border: 1px solid #111; padding: 5px; vertical-align: middle; overflow-wrap: break-word; }
.data th { text-align: center; } thead { display: table-header-group; } tr { page-break-inside: avoid; }
.sub { font-size: 9pt; text-align: center; margin-bottom: 14px; }
.meta { font-size: 9pt; margin: 4px 0 8px; }
.council-group { page-break-inside: avoid; }
.signature { width: 100%; margin-top: 25px; } .signature td { vertical-align: top; }
</style></head><body>
@php($council = $first['hoiDong'])
<table class="heading"><tr><td>BỘ CÔNG THƯƠNG<br><strong>TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG<br>THÀNH PHỐ HỒ CHÍ MINH</strong><br>__________<br>Số: {{ $first['soQuyetDinh'] }}</td>
<td><strong>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM<br>Độc lập - Tự do - Hạnh phúc</strong><br>________________<br><em>Thành phố Hồ Chí Minh, ngày {{ $first['ngayBanHanh']?->format('d') }} tháng {{ $first['ngayBanHanh']?->format('m') }} năm {{ $first['ngayBanHanh']?->format('Y') }}</em></td></tr></table>
<h1>QUYẾT ĐỊNH</h1>
<div class="center bold">Về việc thành lập các Hội đồng chấm khóa luận tốt nghiệp<br>{{ $first['khoa']->TenKhoa }} · {{ $first['hocKy']->TenDayDu ?? $first['hocKy']->TenHocKy }} · {{ $council->DotBaoVe }}</div>
<p class="center bold" style="margin-top: 24px;">{{ mb_strtoupper($first['chucVuNguoiKy']) }}<br>TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG THÀNH PHỐ HỒ CHÍ MINH</p>
<p class="italic">Căn cứ kế hoạch tổ chức bảo vệ khóa luận tốt nghiệp của {{ $first['khoa']->TenKhoa }} trong {{ $first['hocKy']->TenDayDu ?? $first['hocKy']->TenHocKy }};</p>
<p class="italic">Theo đề nghị của Trưởng khoa và danh sách phân công hội đồng bảo vệ khóa luận tốt nghiệp.</p>
<p class="center bold">QUYẾT ĐỊNH:</p>
<p><strong>Điều 1.</strong> Thành lập {{ $items->count() }} Hội đồng chấm khóa luận tốt nghiệp thuộc {{ $first['khoa']->TenKhoa }}, {{ $council->DotBaoVe }}, {{ $first['hocKy']->TenDayDu ?? $first['hocKy']->TenHocKy }}. Danh sách thành viên các Hội đồng được trình bày tại Phụ lục 1 kèm theo.</p>
<p><strong>Điều 2.</strong> Các Hội đồng thực hiện nhiệm vụ tổ chức chấm khóa luận tốt nghiệp theo kế hoạch và quy định của Nhà trường. Danh sách sinh viên, đề tài và giảng viên hướng dẫn được trình bày theo từng Hội đồng tại Phụ lục 2 kèm theo.</p>
<p><strong>Điều 3.</strong> Các đơn vị và cá nhân có liên quan chịu trách nhiệm thực hiện Quyết định này.</p>
<table class="signature"><tr><td style="width: 50%; font-size: 9pt;"><strong><em>Nơi nhận:</em></strong><br>- Các đơn vị, cá nhân liên quan;<br>- Lưu: Khoa.</td><td class="center"><strong>{{ mb_strtoupper($first['chucVuNguoiKy']) }}</strong><br><br><br><br><strong>{{ $first['nguoiKy'] }}</strong></td></tr></table>
<div class="appendix">
<div class="center bold">PHỤ LỤC 1<br>DANH SÁCH HỘI ĐỒNG CHẤM KHÓA LUẬN TỐT NGHIỆP<br>{{ mb_strtoupper($first['khoa']->TenKhoa) }}</div>
<div class="sub">{{ $first['hocKy']->TenDayDu ?? $first['hocKy']->TenHocKy }} · {{ $council->DotBaoVe }}<br><em>(Kèm theo Quyết định số {{ $first['soQuyetDinh'] }} ngày {{ $first['ngayBanHanh']?->format('d/m/Y') }})</em></div>
@foreach($items as $item)
<div class="council-group">
<h2>{{ $item['hoiDong']->TenHoiDong }} ({{ $item['hoiDong']->MaHoiDong }})</h2>
<div class="meta">Ngành: {{ $item['nganh']->TenNganh ?? 'Chưa xác định' }}<br>Thời gian: {{ $item['ngayBaoVe']?->format('d/m/Y') }} · {{ $item['hoiDong']->ThoiGianBatDau ? \Carbon\Carbon::parse($item['hoiDong']->ThoiGianBatDau)->format('H:i') : '' }}–{{ $item['hoiDong']->ThoiGianKetThuc ? \Carbon\Carbon::parse($item['hoiDong']->ThoiGianKetThuc)->format('H:i') : '' }} · Địa điểm: {{ $item['hoiDong']->DiaDiem }}</div>
<table class="data"><thead><tr><th style="width: 6%;">STT</th><th style="width: 27%;">Họ và tên</th><th style="width: 19%;">Học hàm, học vị</th><th style="width: 26%;">Bộ môn / Đơn vị</th><th style="width: 22%;">Chức danh trong Hội đồng</th></tr></thead><tbody>
@foreach($item['thanhViens'] as $member)
<tr><td class="center">{{ $loop->iteration }}</td><td>{{ $member->giangVien?->HoTen }}</td><td>{{ trim(($member->giangVien?->HocHam ?? '').' '.($member->giangVien?->HocVi ?? '')) }}</td><td>{{ $member->giangVien?->boMon?->TenBoMon ?? 'Chưa cập nhật' }}</td><td class="center">{{ $member->short_vai_tro }}</td></tr>
@endforeach
</tbody></table>
</div>
@endforeach
</div>
<div class="appendix">
<div class="center bold">PHỤ LỤC 2<br>DANH SÁCH SINH VIÊN BẢO VỆ KHÓA LUẬN TỐT NGHIỆP<br>{{ mb_strtoupper($first['khoa']->TenKhoa) }}</div>
<div class="sub">{{ $first['hocKy']->TenDayDu ?? $first['hocKy']->TenHocKy }} · {{ $council->DotBaoVe }}<br><em>(Kèm theo Quyết định số {{ $first['soQuyetDinh'] }} ngày {{ $first['ngayBanHanh']?->format('d/m/Y') }})</em></div>
@foreach($items as $item)
<div class="council-group">
<h2>{{ $item['hoiDong']->TenHoiDong }} ({{ $item['hoiDong']->MaHoiDong }})</h2>
<table class="data"><thead><tr><th style="width: 6%;">STT</th><th style="width: 19%;">Mã số sinh viên</th><th style="width: 23%;">Họ và tên</th><th style="width: 32%;">Tên đề tài khóa luận</th><th style="width: 20%;">Người hướng dẫn</th></tr></thead><tbody>
@forelse($item['danhSachSinhVien'] as $student)
<tr><td class="center">{{ $loop->iteration }}</td><td>{{ $student['mssv'] }}</td><td>{{ $student['ho_ten'] }}</td><td>{{ $student['ten_de_tai'] }}</td><td>{{ $student['gvhd'] }}</td></tr>
@empty
<tr><td colspan="5">Chưa có dữ liệu thành viên sinh viên trong các nhóm đã phân công.</td></tr>
@endforelse
</tbody></table>
</div>
@endforeach
</div>
</body></html>
