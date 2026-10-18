<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SinhVien;
use App\Models\GiangVien;
use App\Models\DeTai;
use App\Models\Nhom;
use App\Models\TaiKhoan;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\DangKyDeTai;
use App\Models\DanhSachSVDuDieuKien;
use App\Models\BoMon;
use App\Models\HocKy;
use App\Models\KeHoachKhoaLuan;
use App\Models\ThanhVienNhom;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DashboardController extends Controller
{
    /**
     * Bảng Điều Hành & Thống Kê Tổng Quan Hệ Thống Khóa Luận Tốt Nghiệp (Dành cho Admin / Giáo vụ)
     */
    public function index(Request $request)
    {
        // ── 1. Danh sách Học kỳ (Xếp năm học mới nhất lên trước) ──
        $hocKys = HocKy::orderBy('NamHoc', 'desc')->orderBy('TenHocKy', 'desc')->get();

        // Học kỳ đang hoạt động có dữ liệu mới nhất
        $activeHocKy = KeHoachKhoaLuan::latest('created_at')->value('MaHocKy')
            ?? DeTai::latest('created_at')->value('MaHocKy')
            ?? $hocKys->first()?->MaHocKy
            ?? 'all';

        $selectedHocKy = $request->input('hoc_ky', $activeHocKy);

        // ── 2. Kế hoạch khóa luận hiện hành ──
        $keHoachHienHanh = null;
        if ($selectedHocKy !== 'all') {
            $keHoachHienHanh = KeHoachKhoaLuan::where('MaHocKy', $selectedHocKy)->latest('created_at')->first();
        }
        if (!$keHoachHienHanh) {
            $keHoachHienHanh = KeHoachKhoaLuan::latest('created_at')->first();
        }

        // ── 3. Thống kê Sinh viên theo Học kỳ ──
        $svDuDieuKienQuery = DanhSachSVDuDieuKien::where('TrangThai', 'Đủ điều kiện');
        if ($selectedHocKy !== 'all') {
            $svDuDieuKienQuery->where('MaHocKy', $selectedHocKy);
        }
        $soSVDuDieuKien = $svDuDieuKienQuery->count();

        // Sinh viên đã tham gia nhóm khóa luận
        $svDaCoNhomQuery = ThanhVienNhom::where('TrangThai', 'da_tham_gia');
        if ($selectedHocKy !== 'all') {
            $svDaCoNhomQuery->whereHas('nhom', fn($q) => $q->where('MaHocKy', $selectedHocKy));
        }
        $soSVDaCoNhom = (clone $svDaCoNhomQuery)->distinct('MaSV')->count('MaSV');
        $soSVChuaCoNhom = max(0, $soSVDuDieuKien - $soSVDaCoNhom);

        // ── 4. Thống kê Giảng viên ──
        $soGiangVien = GiangVien::count();
        $soGVDangHuongDan = DeTai::whereHas('nhoms')->distinct('MaGV')->count('MaGV');
        $soGVDeXuatDeTai = DeTai::distinct('MaGV')->count('MaGV');

        // ── 5. Thống kê Đề tài theo Trạng thái ──
        $deTaiQuery = DeTai::query();
        if ($selectedHocKy !== 'all') {
            $deTaiQuery->where(function($q) use ($selectedHocKy) {
                $q->where('MaHocKy', $selectedHocKy)->orWhereNull('MaHocKy');
            });
        }

        $soDeTaiTong = (clone $deTaiQuery)->count();
        $soDeTaiDaCongBo = (clone $deTaiQuery)->where('TrangThai', 'Đã công bố')->count();
        $soDeTaiChoDuyetBM = (clone $deTaiQuery)->where('TrangThai', 'Chờ duyệt cấp Bộ môn')->count();
        $soDeTaiChoDuyetKhoa = (clone $deTaiQuery)->where('TrangThai', 'Chờ duyệt cấp Khoa')->count();
        $soDeTaiPhanBien = (clone $deTaiQuery)->whereIn('TrangThai', ['Đang phản biện đề cương', 'Đã phản biện - Chờ duyệt BM'])->count();
        $soDeTaiChinhSua = (clone $deTaiQuery)->whereIn('TrangThai', ['Yêu cầu chỉnh sửa', 'Bị từ chối', 'Từ chối'])->count();

        // ── 6. Thống kê Nhóm Sinh viên & Đăng ký ──
        $nhomQuery = Nhom::query();
        if ($selectedHocKy !== 'all') {
            $nhomQuery->where('MaHocKy', $selectedHocKy);
        }

        $soNhomTong = (clone $nhomQuery)->count();
        $soNhomCoDeTai = (clone $nhomQuery)->has('deTai')->count();
        $soNhomChuaCoDeTai = (clone $nhomQuery)->doesntHave('deTai')->count();

        $dkDaDuyet  = DangKyDeTai::where('TrangThai', 'Đã duyệt')->count();
        $dkChoDuyet = DangKyDeTai::where('TrangThai', 'Chờ duyệt')->count();
        $dkTuChoi   = DangKyDeTai::where('TrangThai', 'Từ chối')->count();

        // ── 7. Thống kê chi tiết theo Bộ môn ──
        $boMons = BoMon::orderBy('TenBoMon')->get();
        $thongKeBoMon = [];
        foreach ($boMons as $bm) {
            $slGV = GiangVien::where('MaBoMon', $bm->MaBoMon)->count();
            
            $dtBmQuery = DeTai::whereHas('giangVien', fn($q) => $q->where('MaBoMon', $bm->MaBoMon));
            if ($selectedHocKy !== 'all') {
                $dtBmQuery->where(fn($q) => $q->where('MaHocKy', $selectedHocKy)->orWhereNull('MaHocKy'));
            }
            $slDeTai = (clone $dtBmQuery)->count();
            $slDaCongBo = (clone $dtBmQuery)->where('TrangThai', 'Đã công bố')->count();

            $nhomBmQuery = Nhom::whereHas('deTai.giangVien', fn($q) => $q->where('MaBoMon', $bm->MaBoMon));
            if ($selectedHocKy !== 'all') {
                $nhomBmQuery->where('MaHocKy', $selectedHocKy);
            }
            $slNhom = $nhomBmQuery->count();
            $tyLeDuyet = $slDeTai > 0 ? round(($slDaCongBo / $slDeTai) * 100) : 0;

            $thongKeBoMon[] = [
                'ma_bm'         => $bm->MaBoMon,
                'ten_bm'        => $bm->TenBoMon,
                'so_gv'         => $slGV,
                'so_de_tai'     => $slDeTai,
                'so_da_cong_bo' => $slDaCongBo,
                'so_nhom'       => $slNhom,
                'ty_le_duyet'   => $tyLeDuyet,
            ];
        }

        // ── 8. Hồ sơ bảo vệ & Hội đồng ──
        $hoSoCho      = HoSoBaoVe::where('TrangThai', 'Chờ xác nhận')->count();
        $hoSoPhanCong = HoSoBaoVe::where('TrangThai', 'Đã phân công')->count();
        $hoSoTong     = HoSoBaoVe::count();
        $hoSoHopLe    = HoSoBaoVe::whereIn('TrangThai', ['Đủ điều kiện bảo vệ', 'Đã phân công'])->count();
        $soHoiDong    = HoiDong::count();

        // ── 9. Danh sách Nhóm mới nhất ──
        $nhomMoiNhat = (clone $nhomQuery)
            ->with(['truongNhom.lop', 'sinhViens.lop', 'deTai.giangVien', 'deTai.boMon'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // ── 10. Đề tài cần xử lý khẩn cấp (Chờ duyệt / Cần chỉnh sửa) ──
        $deTaiCanXuLy = (clone $deTaiQuery)
            ->with(['giangVien', 'boMon'])
            ->whereIn('TrangThai', ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt cấp Khoa', 'Yêu cầu chỉnh sửa'])
            ->orderBy('updated_at', 'desc')
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact(
            'hocKys', 'selectedHocKy', 'keHoachHienHanh',
            'soSVDuDieuKien', 'soSVDaCoNhom', 'soSVChuaCoNhom',
            'soGiangVien', 'soGVDangHuongDan', 'soGVDeXuatDeTai',
            'soDeTaiTong', 'soDeTaiDaCongBo', 'soDeTaiChoDuyetBM', 'soDeTaiChoDuyetKhoa', 'soDeTaiPhanBien', 'soDeTaiChinhSua',
            'soNhomTong', 'soNhomCoDeTai', 'soNhomChuaCoDeTai',
            'dkDaDuyet', 'dkChoDuyet', 'dkTuChoi',
            'thongKeBoMon',
            'hoSoCho', 'hoSoPhanCong', 'hoSoHopLe', 'hoSoTong', 'soHoiDong',
            'nhomMoiNhat', 'deTaiCanXuLy'
        ));
    }

    /**
     * Xuất Báo Cáo Thống Kê Tổng Hợp Khóa Luận (Excel)
     */
    public function exportTongHop(Request $request)
    {
        $selectedHocKy = $request->input('hoc_ky', 'all');
        $tenHocKy = $selectedHocKy === 'all' ? 'Tất cả các học kỳ' : (HocKy::where('MaHocKy', $selectedHocKy)->value('TenHocKy') ?? $selectedHocKy);

        $spreadsheet = new Spreadsheet();
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Tổng Quan Khóa Luận');

        // Header thông tin trường
        $sheet1->mergeCells('A1:G1');
        $sheet1->setCellValue('A1', 'TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH (HUIT)');
        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));

        $sheet1->mergeCells('A2:G2');
        $sheet1->setCellValue('A2', 'KHOA CÔNG NGHỆ THÔNG TIN - HỆ THỐNG QUẢN LÝ KHÓA LUẬN TỐT NGHIỆP');
        $sheet1->getStyle('A2')->getFont()->setBold(true)->setSize(11);

        $sheet1->mergeCells('A4:G4');
        $sheet1->setCellValue('A4', 'BÁO CÁO THỐNG KÊ TỔNG HỢP CÔNG TÁC KHÓA LUẬN TỐT NGHIỆP');
        $sheet1->getStyle('A4')->getFont()->setBold(true)->setSize(15)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF003B73'));
        $sheet1->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet1->mergeCells('A5:G5');
        $sheet1->setCellValue('A5', "Học kỳ: {$tenHocKy} | Ngày xuất báo cáo: " . date('d/m/Y H:i'));
        $sheet1->getStyle('A5')->getFont()->setItalic(true)->setSize(10);
        $sheet1->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Bảng số liệu tổng hợp các chỉ số chính
        $sheet1->setCellValue('A7', 'I. CHỈ SỐ TOÀN DIỆN HỆ THỐNG');
        $sheet1->getStyle('A7')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));

        $kpis = [
            ['STT', 'Hạng Mục Chỉ Số Thống Kê', 'Số Lượng', 'Đơn Vị', 'Ghi Chú'],
            [1, 'Tổng số sinh viên đủ điều kiện làm khóa luận', DanhSachSVDuDieuKien::where('TrangThai', 'Đủ điều kiện')->count(), 'Sinh viên', 'Đạt chuẩn tín chỉ & ĐTB tích lũy'],
            [2, 'Số sinh viên đã tham gia nhóm khóa luận', ThanhVienNhom::where('TrangThai', 'da_tham_gia')->distinct('MaSV')->count('MaSV'), 'Sinh viên', 'Đang hoạt động trong nhóm'],
            [3, 'Tổng số nhóm sinh viên đã thành lập', Nhom::count(), 'Nhóm', 'Quy mô 2-3 SV/nhóm'],
            [4, 'Số nhóm đã được duyệt đề tài chính thức', Nhom::has('deTai')->count(), 'Nhóm', 'Đã ghép đề tài và có GVHD'],
            [5, 'Tổng số đề tài khóa luận được đề xuất', DeTai::count(), 'Đề tài', 'Đề xuất từ giảng viên'],
            [6, 'Số đề tài đã hoàn thành phê duyệt 2 cấp (Đã công bố)', DeTai::where('TrangThai', 'Đã công bố')->count(), 'Đề tài', 'TBM và Trưởng khoa duyệt'],
            [7, 'Số đề tài đang trong quy trình xét duyệt', DeTai::whereIn('TrangThai', ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt cấp Khoa', 'Đang phản biện đề cương'])->count(), 'Đề tài', 'Đang thẩm định chuyên môn'],
            [8, 'Tổng số giảng viên tham gia hướng dẫn / phản biện', GiangVien::count(), 'Cán bộ', 'Khoa Công nghệ thông tin'],
            [9, 'Tổng số hội đồng bảo vệ khóa luận', HoiDong::count(), 'Hội đồng', 'Giai đoạn nghiệm thu bảo vệ'],
        ];

        $rowIdx = 8;
        foreach ($kpis as $row) {
            $sheet1->fromArray($row, null, "A{$rowIdx}");
            if ($rowIdx === 8) {
                $sheet1->getStyle("A{$rowIdx}:E{$rowIdx}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
                $sheet1->getStyle("A{$rowIdx}:E{$rowIdx}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A8A');
            }
            $rowIdx++;
        }

        // Bảng số liệu chi tiết theo từng Bộ môn
        $rowIdx += 2;
        $sheet1->setCellValue("A{$rowIdx}", 'II. THỐNG KÊ CHI TIẾT THEO TỪNG BỘ MÔN CHUYÊN NGÀNH');
        $sheet1->getStyle("A{$rowIdx}")->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));
        $rowIdx++;

        $bmHeaders = ['STT', 'Mã Bộ Môn', 'Tên Bộ Môn Chuyên Ngành', 'Số Giảng Viên', 'Đề Tài Đề Xuất', 'Đề Tài Đã Công Bố', 'Nhóm Thực Hiện', 'Tỷ Lệ Duyệt (%)'];
        $sheet1->fromArray($bmHeaders, null, "A{$rowIdx}");
        $sheet1->getStyle("A{$rowIdx}:H{$rowIdx}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet1->getStyle("A{$rowIdx}:H{$rowIdx}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF003B73');
        $rowIdx++;

        $boMons = BoMon::orderBy('TenBoMon')->get();
        $stt = 1;
        $startBmRow = $rowIdx;
        foreach ($boMons as $bm) {
            $slGV = GiangVien::where('MaBoMon', $bm->MaBoMon)->count();
            $slDeTai = DeTai::whereHas('giangVien', fn($q) => $q->where('MaBoMon', $bm->MaBoMon))->count();
            $slDaCongBo = DeTai::whereHas('giangVien', fn($q) => $q->where('MaBoMon', $bm->MaBoMon))->where('TrangThai', 'Đã công bố')->count();
            $slNhom = Nhom::whereHas('deTai.giangVien', fn($q) => $q->where('MaBoMon', $bm->MaBoMon))->count();
            $tyLe = $slDeTai > 0 ? round(($slDaCongBo / $slDeTai) * 100) : 0;

            $sheet1->fromArray([$stt++, $bm->MaBoMon, $bm->TenBoMon, $slGV, $slDeTai, $slDaCongBo, $slNhom, "{$tyLe}%"], null, "A{$rowIdx}");
            $rowIdx++;
        }

        // Dòng tổng cộng
        $sheet1->setCellValue("A{$rowIdx}", 'TỔNG CỘNG');
        $sheet1->mergeCells("A{$rowIdx}:C{$rowIdx}");
        $sheet1->setCellValue("D{$rowIdx}", "=SUM(D{$startBmRow}:D" . ($rowIdx - 1) . ")");
        $sheet1->setCellValue("E{$rowIdx}", "=SUM(E{$startBmRow}:E" . ($rowIdx - 1) . ")");
        $sheet1->setCellValue("F{$rowIdx}", "=SUM(F{$startBmRow}:F" . ($rowIdx - 1) . ")");
        $sheet1->setCellValue("G{$rowIdx}", "=SUM(G{$startBmRow}:G" . ($rowIdx - 1) . ")");
        $sheet1->setCellValue("H{$rowIdx}", "-");
        $sheet1->getStyle("A{$rowIdx}:H{$rowIdx}")->getFont()->setBold(true);
        $sheet1->getStyle("A{$rowIdx}:H{$rowIdx}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        // Kẻ viền bảng
        $styleBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCBD5E1'],
                ],
            ],
        ];
        $sheet1->getStyle("A8:E17")->applyFromArray($styleBorder);
        $sheet1->getStyle("A" . ($startBmRow - 1) . ":H{$rowIdx}")->applyFromArray($styleBorder);

        foreach (range('A', 'H') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // Chữ ký xác nhận ở cuối
        $rowIdx += 3;
        $sheet1->setCellValue("B{$rowIdx}", "NGƯỜI LẬP BÁO CÁO\n(Ký và ghi rõ họ tên)");
        $sheet1->setCellValue("F{$rowIdx}", "TRƯỞNG KHOA / BAN CHỦ NHIỆM\n(Ký và đóng dấu)");
        $sheet1->getStyle("B{$rowIdx}:F{$rowIdx}")->getFont()->setBold(true);
        $sheet1->getStyle("B{$rowIdx}:F{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $fileName = 'Bao_Cao_Tong_Hop_Khoa_Luan_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Xuất Danh Sách Phân Công & Đăng Ký Đề Tài (Excel)
     */
    public function exportPhanCong(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Phân Công Đề Tài');

        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH (HUIT) - KHOA CNTT');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));

        $sheet->mergeCells('A3:I3');
        $sheet->setCellValue('A3', 'DANH SÁCH PHÂN CÔNG HƯỚNG DẪN & ĐĂNG KÝ ĐỀ TÀI KHÓA LUẬN');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF003B73'));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['STT', 'Mã Nhóm', 'Tên Nhóm', 'Mã SV', 'Họ Và Tên Sinh Viên', 'Lớp', 'Mã Đề Tài', 'Tên Đề Tài Khóa Luận', 'Giảng Viên Hướng Dẫn', 'Bộ Môn'];
        $sheet->fromArray($headers, null, 'A5');
        $sheet->getStyle('A5:J5')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle('A5:J5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A8A');

        $nhoms = Nhom::with(['sinhViens.lop', 'deTai.giangVien', 'deTai.boMon'])->orderBy('MaNhom')->get();
        $rowIdx = 6;
        $stt = 1;

        foreach ($nhoms as $nhom) {
            $deTai = $nhom->deTai;
            $tenDeTai = $deTai?->TenDeTai ?? '(Chưa đăng ký)';
            $maDeTai = $deTai?->MaDeTai ?? '-';
            $gvhd = $deTai?->giangVien?->HoTen ?? '-';
            $tenBm = $deTai?->boMon?->TenBoMon ?? '-';

            $sinhViens = $nhom->sinhViens;
            if ($sinhViens->isEmpty()) {
                $sheet->fromArray([$stt++, $nhom->MaNhom, $nhom->TenNhom, '-', 'Chưa có thành viên', '-', $maDeTai, $tenDeTai, $gvhd, $tenBm], null, "A{$rowIdx}");
                $rowIdx++;
            } else {
                foreach ($sinhViens as $sv) {
                    $sheet->fromArray([$stt++, $nhom->MaNhom, $nhom->TenNhom, $sv->MaSV, $sv->HoTen, $sv->lop?->TenLop ?? $sv->MaLop, $maDeTai, $tenDeTai, $gvhd, $tenBm], null, "A{$rowIdx}");
                    $rowIdx++;
                }
            }
        }

        $sheet->getStyle("A5:J" . ($rowIdx - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Danh_Sach_Phan_Cong_De_Tai_' . date('Ymd_His') . '.xlsx';
        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Xuất Danh Sách Sinh Viên Đủ Điều Kiện & Tình Trạng Nhóm (Excel)
     */
    public function exportSinhVien(Request $request)
    {
        $selectedHocKy = $request->input('hoc_ky', 'all');
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sinh Viên Đủ Điều Kiện');

        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'DANH SÁCH SINH VIÊN ĐỦ ĐIỀU KIỆN KHÓA LUẬN TỐT NGHIỆP');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['STT', 'Mã SV', 'Họ Và Tên', 'Lớp', 'Khoa', 'Tình Trạng Điều Kiện', 'Trạng Thái Nhóm', 'Mã Nhóm'];
        $sheet->fromArray($headers, null, 'A3');
        $sheet->getStyle('A3:H3')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle('A3:H3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF003B73');

        $query = DanhSachSVDuDieuKien::with(['sinhVien.lop', 'sinhVien.thanhVienNhoms.nhom']);
        if ($selectedHocKy !== 'all') {
            $query->where('MaHocKy', $selectedHocKy);
        }
        $list = $query->orderBy('MaSV')->get();

        $rowIdx = 4;
        $stt = 1;

        foreach ($list as $item) {
            $sv = $item->sinhVien;
            $tvn = $sv?->thanhVienNhoms?->firstWhere('TrangThai', 'da_tham_gia');
            $maNhom = $tvn?->MaNhom ?? '-';
            $ttNhom = $tvn ? 'Đã có nhóm' : 'Chưa có nhóm';

            $sheet->fromArray([
                $stt++,
                $item->MaSV,
                $sv?->HoTen ?? '-',
                $sv?->lop?->TenLop ?? $sv?->MaLop ?? '-',
                $sv?->MaKhoa ?? 'CNTT',
                $item->TrangThai,
                $ttNhom,
                $maNhom
            ], null, "A{$rowIdx}");
            $rowIdx++;
        }

        $sheet->getStyle("A3:H" . ($rowIdx - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Danh_Sach_SV_Du_Dieu_Kien_' . date('Ymd_His') . '.xlsx';
        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Trang Xem & In Báo Cáo Thống Kê Tổng Thể (Printable Report chuẩn A4)
     */
    public function printReport(Request $request)
    {
        $selectedHocKy = $request->input('hoc_ky', 'all');
        $tenHocKy = $selectedHocKy === 'all' ? 'Tất cả các học kỳ' : (HocKy::where('MaHocKy', $selectedHocKy)->value('TenHocKy') ?? $selectedHocKy);

        $soSVDuDieuKien = DanhSachSVDuDieuKien::where('TrangThai', 'Đủ điều kiện')->count();
        $soSVDaCoNhom   = ThanhVienNhom::where('TrangThai', 'da_tham_gia')->distinct('MaSV')->count('MaSV');
        $soGiangVien    = GiangVien::count();
        $soDeTaiTong    = DeTai::count();
        $soDeTaiDaCongBo= DeTai::where('TrangThai', 'Đã công bố')->count();
        $soNhomTong     = Nhom::count();
        $soHoiDong      = HoiDong::count();

        $boMons = BoMon::orderBy('TenBoMon')->get();
        $thongKeBoMon = [];
        foreach ($boMons as $bm) {
            $slGV = GiangVien::where('MaBoMon', $bm->MaBoMon)->count();
            $slDeTai = DeTai::whereHas('giangVien', fn($q) => $q->where('MaBoMon', $bm->MaBoMon))->count();
            $slDaCongBo = DeTai::whereHas('giangVien', fn($q) => $q->where('MaBoMon', $bm->MaBoMon))->where('TrangThai', 'Đã công bố')->count();
            $slNhom = Nhom::whereHas('deTai.giangVien', fn($q) => $q->where('MaBoMon', $bm->MaBoMon))->count();
            $tyLe = $slDeTai > 0 ? round(($slDaCongBo / $slDeTai) * 100) : 0;

            $thongKeBoMon[] = [
                'ma_bm'         => $bm->MaBoMon,
                'ten_bm'        => $bm->TenBoMon,
                'so_gv'         => $slGV,
                'so_de_tai'     => $slDeTai,
                'so_da_cong_bo' => $slDaCongBo,
                'so_nhom'       => $slNhom,
                'ty_le_duyet'   => $tyLe,
            ];
        }

        return view('admin.dashboard_print', compact(
            'tenHocKy', 'soSVDuDieuKien', 'soSVDaCoNhom',
            'soGiangVien', 'soDeTaiTong', 'soDeTaiDaCongBo', 'soNhomTong', 'soHoiDong',
            'thongKeBoMon'
        ));
    }

    /**
     * Khóa / Mở khóa tài khoản
     */
    public function toggleLockAccount($id)
    {
        $tk = TaiKhoan::findOrFail($id);
        if ($tk->MaVaiTro == 1 && $tk->MaTK == auth()->user()->MaTK) {
            return redirect()->back()->withErrors('Không thể tự khóa tài khoản Admin đang sử dụng!');
        }
        $tk->TrangThai = !$tk->TrangThai;
        $tk->save();
        $msg = $tk->TrangThai ? 'Mở khóa tài khoản thành công!' : 'Đã khóa tài khoản thành công!';
        return redirect()->back()->with('success', $msg);
    }
}
