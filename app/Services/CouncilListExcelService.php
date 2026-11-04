<?php
namespace App\Services;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\{Coordinate, DataType};
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Border, Fill};
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class CouncilListExcelService
{
    public function build(Collection $councils, string $semester, string $round, string $filters = ''): Spreadsheet
    {
        $book = new Spreadsheet();
        $book->getProperties()->setTitle('Danh sách hội đồng bảo vệ khóa luận')->setCreator('HUIT');
        $sheet = $book->getActiveSheet()->setTitle('Danh sách hội đồng');
        $sheet->setShowGridlines(false);
        $book->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(11);
        $sheet->getDefaultRowDimension()->setRowHeight(23);
        $write = function (string $cell, $value) use ($sheet) {
            // Explicit strings preserve IDs and prevent user text from becoming formulas.
            $sheet->setCellValueExplicit($cell, $value, is_int($value) ? DataType::TYPE_NUMERIC : DataType::TYPE_STRING);
        };
        foreach ([1 => 'BỘ CÔNG THƯƠNG', 2 => 'TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG THÀNH PHỐ HỒ CHÍ MINH',
            3 => 'BÁO CÁO DANH SÁCH HỘI ĐỒNG BẢO VỆ KHÓA LUẬN TỐT NGHIỆP'] as $row => $title) {
            $sheet->mergeCells("A{$row}:O{$row}"); $write('A'.$row, $title);
            $sheet->getStyle('A'.$row)->getFont()->setBold(true);
        }
        $sheet->getStyle('A1:O7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle('A3')->getFont()->setSize(16)->getColor()->setRGB('003366');
        $sheet->getRowDimension(3)->setRowHeight(40);
        $sheet->mergeCells('A4:H4')->mergeCells('I4:O4');
        $write('A4', 'Học kỳ: '.$semester); $write('I4', 'Đợt bảo vệ: '.$round);
        $sheet->getRowDimension(4)->setRowHeight(32);
        $sheet->mergeCells('A5:H5')->mergeCells('I5:O5');
        $write('A5', 'Ngày lập: '.ThongKeService::getNow()->format('d/m/Y H:i')); $write('I5', 'Số hội đồng: '.$councils->count());
        $sheet->mergeCells('A6:O6'); $write('A6', 'Bộ lọc: '.($filters ?: 'Không có bộ lọc bổ sung'));
        $sheet->getStyle('A4:O6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EAF3FA');
        $sheet->getRowDimension(6)->setRowHeight(32);
        $headers = ['STT', 'Mã hội đồng', 'Tên hội đồng', 'Số quyết định', 'Học kỳ', 'Đợt bảo vệ', 'Ngày bảo vệ', 'Phòng bảo vệ', 'Chủ tịch', 'Thư ký', 'Phản biện', 'Số đề tài', 'Số sinh viên', 'Công bố quyết định', 'Trạng thái diễn ra'];
        foreach ($headers as $i => $header) $write(Coordinate::stringFromColumnIndex($i + 1).'8', $header);
        $sheet->getStyle('A8:O8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('003366');
        $sheet->getStyle('A8:O8')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getRowDimension(8)->setRowHeight(38);
        $totalTopics = 0; $totalStudents = 0;
        foreach ($councils->values() as $index => $hd) {
            $row = $index + 9;
            $teacher = fn($role) => $hd->thanhViens->first(fn($member) => \App\Enums\VaiTroHoiDong::normalize($member->VaiTro) === $role)?->giangVien?->HoTen ?? 'Chưa gán';
            $groups = $hd->hoSoBaoVes->unique('MaNhom');
            $students = $groups->flatMap(fn($hs) => $hs->nhom?->thanhVienNhoms ?? collect())->unique('MaSV');
            $totalTopics += $groups->count(); $totalStudents += $students->count();
            $cells = [$index + 1, $hd->MaHoiDong, $hd->TenHoiDong, $hd->SoQuyetDinh ?? '', $hd->hocKy?->TenDayDu ?? $hd->MaHocKy,
                $hd->DotBaoVe ?? '', $hd->NgayBaoVe ? \Carbon\Carbon::parse($hd->NgayBaoVe)->format('d/m/Y') : '', $hd->DiaDiem ?? '',
                $teacher('Chủ tịch'), $teacher('Thư ký'), $teacher('Phản biện'), $groups->count(), $students->count(), $hd->TrangThaiCongBo, $hd->TrangThai];
            foreach ($cells as $i => $value) $write(Coordinate::stringFromColumnIndex($i + 1).$row, $value);
            $lines = max(3, (int)ceil(mb_strlen($hd->TenHoiDong ?? '') / 27), (int)ceil(mb_strlen($hd->DiaDiem ?? '') / 22));
            $sheet->getRowDimension($row)->setRowHeight($lines * 15 + 12);
            if ($index % 2 === 0) $sheet->getStyle("A{$row}:O{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F6FC');
        }
        $last = 9 + $councils->count();
        $sheet->mergeCells("A{$last}:K{$last}"); $write('A'.$last, 'TỔNG CỘNG: '.$councils->count().' HỘI ĐỒNG');
        $write('L'.$last, $totalTopics); $write('M'.$last, $totalStudents);
        $sheet->getStyle("A{$last}:O{$last}")->getFont()->setBold(true);
        $sheet->getStyle("A{$last}:O{$last}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCEAF7');
        $sheet->getStyle("A8:O{$last}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C9:K{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("A8:O{$last}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('BBC9D8');
        foreach ([7, 17, 36, 22, 24, 17, 17, 30, 25, 25, 25, 10, 11, 21, 21] as $i => $width) $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))->setWidth($width);
        $sheet->freezePane('D9'); $sheet->setAutoFilter('A8:O'.max(8, $last - 1));
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setPaperSize(PageSetup::PAPERSIZE_A3)->setFitToWidth(1)->setFitToHeight(0)->setRowsToRepeatAtTopByStartAndEnd(8, 8)->setPrintArea('A1:O'.$last);
        $sheet->getPageMargins()->setTop(.3)->setBottom(.3)->setLeft(.25)->setRight(.25);
        $sheet->getHeaderFooter()->setOddFooter('&L HUIT — Danh sách hội đồng&R Trang &P / &N');
        return $book;
    }
}
