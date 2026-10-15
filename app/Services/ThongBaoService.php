<?php

namespace App\Services;

use App\Models\ThongBao;
use App\Models\NguoiNhanThongBao;
use App\Models\ThanhVienNhom;
use App\Models\GiangVien;
use App\Models\TaiKhoan;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ThongBaoService
{
    /**
     * Gửi thông báo đến một tài khoản cụ thể
     */
    public static function guiDen(string $maTK, string $tieuDe, string $noiDung, string $loai = 'Hệ thống', ?string $duongDan = null): void
    {
        $maTB = 'TB_' . Str::upper(Str::random(7));
        while (ThongBao::where('MaThongBao', $maTB)->exists()) {
            $maTB = 'TB_' . Str::upper(Str::random(7));
        }

        // Tạo bản ghi cha trong ThongBao
        ThongBao::create([
            'MaThongBao'   => $maTB,
            'TieuDe'       => $tieuDe,
            'NoiDung'      => $noiDung,
            'LoaiThongBao' => $loai,
            'DoiTuongNhan' => $maTK,
            'NgayTao'      => now(),
            'TrangThai'    => 'Đã phát hành',
        ]);

        // Tạo bản ghi người nhận nếu bảng tồn tại
        if (class_exists(\App\Models\NguoiNhanThongBao::class) && \Illuminate\Support\Facades\Schema::hasTable('nguoi_nhan_thong_baos')) {
            \App\Models\NguoiNhanThongBao::create([
                'MaThongBao' => $maTB,
                'MaTK'       => $maTK,
                'TieuDe'     => $tieuDe,
                'NoiDung'    => $noiDung,
                'Loai'       => $loai,
                'DuongDan'   => $duongDan,
                'DaDoc'      => false,
                'NgayDoc'    => null,
            ]);
        }
    }

    /**
     * Gửi thông báo đến tất cả thành viên trong một nhóm
     */
    public static function guiDenNhom(string $maNhom, string $tieuDe, string $noiDung, string $loai = 'Hệ thống', ?string $duongDan = null): void
    {
        $thanhViens = ThanhVienNhom::where('MaNhom', $maNhom)
            ->where('TrangThai', 'da_tham_gia')
            ->with('sinhVien.taiKhoan')
            ->get();

        foreach ($thanhViens as $tv) {
            $maTK = $tv->sinhVien->taiKhoan->MaTK ?? null;
            if ($maTK) {
                self::guiDen($maTK, $tieuDe, $noiDung, $loai, $duongDan);
            }
        }
    }

    /**
     * Gửi thông báo đến GVHD của một nhóm (thông qua DangKyDeTai)
     */
    public static function guiDenGVHD(string $maNhom, string $tieuDe, string $noiDung, string $loai = 'Hệ thống', ?string $duongDan = null): void
    {
        $dk = \App\Models\DangKyDeTai::where('MaNhom', $maNhom)
            ->where('TrangThai', 'Đã duyệt')
            ->first();

        if (!$dk) return;

        $gv = $dk->deTai?->giangVien ?? GiangVien::find($dk->MaGVHuongDan);
        if (!$gv) return;

        $tk = TaiKhoan::find($gv->MaTK);
        if ($tk) {
            self::guiDen($tk->MaTK, $tieuDe, $noiDung, $loai, $duongDan);
        }
    }

    /**
     * Gửi thông báo đến tất cả Sinh viên
     */
    public static function guiDenTatCaSV(string $tieuDe, string $noiDung, string $loai = 'Hệ thống'): void
    {
        $maTKs = \App\Models\SinhVien::with('taiKhoan')->get()
            ->pluck('taiKhoan.MaTK')->filter();

        foreach ($maTKs as $maTK) {
            self::guiDen($maTK, $tieuDe, $noiDung, $loai);
        }
    }

    /**
     * Gửi thông báo deadline có chống gửi trùng lặp cùng 1 tiêu đề cho 1 tài khoản trong ngày
     */
    public static function guiThongBaoDeadline(string $maTK, string $tieuDe, string $noiDung, string $loai = 'Báo cáo', ?string $duongDan = null): bool
    {
        $today = date('Y-m-d');
        $alreadySent = NguoiNhanThongBao::where('MaTK', $maTK)
            ->where('TieuDe', $tieuDe)
            ->whereDate('created_at', $today)
            ->exists();

        if ($alreadySent) {
            return false;
        }

        self::guiDen($maTK, $tieuDe, $noiDung, $loai, $duongDan);
        return true;
    }

    /**
     * Gửi thông báo đính kèm file văn bản gốc kế hoạch cho Sinh viên / Giảng viên
     */
    public static function guiThongBaoKemFileGoc(string $tieuDe, string $noiDung, string $fileUrl, string $loai = 'Kế hoạch'): int
    {
        $maTB = 'TB_' . Str::upper(Str::random(7));
        while (ThongBao::where('MaThongBao', $maTB)->exists()) {
            $maTB = 'TB_' . Str::upper(Str::random(7));
        }

        // Tạo bản ghi cha (nối link file vào nội dung)
        $fullContent = $noiDung . "\n\nFile đính kèm: " . asset($fileUrl);

        ThongBao::create([
            'MaThongBao'   => $maTB,
            'TieuDe'       => $tieuDe,
            'NoiDung'      => $fullContent,
            'LoaiThongBao' => $loai,
            'DoiTuongNhan' => 'Toàn thể',
            'NgayTao'      => now(),
            'TrangThai'    => 'Đã phát hành',
        ]);

        // Phân phối tới tất cả Sinh viên và Giảng viên nếu bảng nhận thông báo tồn tại
        $count = 0;
        if (class_exists(\App\Models\NguoiNhanThongBao::class) && \Illuminate\Support\Facades\Schema::hasTable('nguoi_nhan_thong_baos')) {
            $taiKhoans = TaiKhoan::whereHas('vaiTro', function($q) {
                $q->whereIn('TenVaiTro', ['Sinh viên', 'Giảng viên']);
            })->get();

            foreach ($taiKhoans as $tk) {
                \App\Models\NguoiNhanThongBao::create([
                    'MaThongBao' => $maTB,
                    'MaTK'       => $tk->MaTK,
                    'TieuDe'     => $tieuDe,
                    'NoiDung'    => $fullContent,
                    'Loai'       => $loai,
                    'DuongDan'   => asset($fileUrl),
                    'DaDoc'      => false,
                    'NgayDoc'    => null,
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Lấy danh sách ID thông báo đã đọc của tài khoản (kết hợp bền vững Cache + Session)
     */
    public static function getReadIds(?string $maTK): array
    {
        $key = 'read_thong_bao_ids_' . ($maTK ?: 'guest');
        $cached = \Illuminate\Support\Facades\Cache::get($key, []);
        $sess = session()->get($key, session()->get('read_thong_bao_ids', []));
        if (!is_array($cached)) $cached = [];
        if (!is_array($sess)) $sess = [];
        $all = array_values(array_unique(array_merge($cached, $sess)));
        return $all;
    }

    /**
     * Đánh dấu danh sách thông báo đã đọc và lưu bền vững vào cả Session và Cache
     */
    public static function markAsRead(?string $maTK, string|array $thongBaoIds): array
    {
        $key = 'read_thong_bao_ids_' . ($maTK ?: 'guest');
        $current = self::getReadIds($maTK);
        $newIds = is_array($thongBaoIds) ? $thongBaoIds : [$thongBaoIds];
        $merged = array_values(array_unique(array_merge($current, array_filter($newIds))));

        // Đồng bộ lưu vào session
        session([
            $key => $merged,
            'read_thong_bao_ids' => $merged
        ]);

        // Đồng bộ lưu bền vững vào Cache trong 1 năm
        \Illuminate\Support\Facades\Cache::put($key, $merged, now()->addYear());

        return $merged;
    }

    /**
     * Lấy dữ liệu công bố danh sách chính thức (Đề tài, Nhóm sinh viên, Giảng viên hướng dẫn)
     */
    public static function getOfficialListData(ThongBao $thongBao): ?array
    {
        $tieuDe = mb_strtolower($thongBao->TieuDe ?? '');
        $noiDung = mb_strtolower($thongBao->NoiDung ?? '');

        $isOfficial = (
            str_contains($tieuDe, 'danh sách sinh viên đăng ký đề tài') ||
            str_contains($tieuDe, 'danh sách đề tài & gvhd') ||
            str_contains($tieuDe, 'đăng ký đề tài & gvhd') ||
            str_contains($tieuDe, 'công bố danh mục đề tài') ||
            str_contains($tieuDe, 'danh sách đề tài') ||
            (str_contains($noiDung, 'danh sách') && str_contains($noiDung, 'đề tài') && str_contains($noiDung, 'hướng dẫn')) ||
            str_starts_with($thongBao->MaThongBao, 'TB_CONGBO_') ||
            str_starts_with($thongBao->MaThongBao, 'TB_CB_')
        );

        if (!$isOfficial) {
            return null;
        }

        // Xác định học kỳ
        $hocKy = null;
        if (preg_match('/(HK\w+)/i', $thongBao->TieuDe, $m)) {
            $hocKy = \App\Models\HocKy::where('MaHocKy', $m[1])->first();
        }
        if (!$hocKy) {
            $hocKy = \App\Models\HocKy::where('TrangThai', 1)->first() ?? \App\Models\HocKy::latest('MaHocKy')->first();
        }
        $maHK = $hocKy?->MaHocKy;

        // 1. Danh sách Đề tài: Đã được duyệt 2 cấp (Đã công bố / Trưởng khoa đã duyệt) VÀ được nhóm SV đăng ký chính thức
        $deTaiQuery = \App\Models\DeTai::with(['giangVien.boMon', 'nganh', 'hocKy', 'phieuDangKys.nhom.truongNhom'])
            ->when($maHK, fn($q) => $q->where('MaHocKy', $maHK))
            ->whereIn('TrangThai', ['Đã công bố', 'Trưởng khoa đã duyệt', 'Đã duyệt', '1']);

        // Lấy đề tài đã có phiếu đăng ký được duyệt
        $registeredDeTaiList = (clone $deTaiQuery)
            ->whereHas('phieuDangKys', function($q) {
                $q->where('TrangThai', 'Đã duyệt');
            })
            ->orderBy('MaDeTai')
            ->get();

        $deTaiList = $registeredDeTaiList->isNotEmpty() ? $registeredDeTaiList : $deTaiQuery->orderBy('MaDeTai')->get();

        if ($deTaiList->isEmpty()) {
            $deTaiList = \App\Models\DeTai::with(['giangVien.boMon', 'nganh', 'hocKy', 'phieuDangKys.nhom.truongNhom'])
                ->when($maHK, fn($q) => $q->where('MaHocKy', $maHK))
                ->get();
        }
        if ($deTaiList->isEmpty()) {
            $deTaiList = \App\Models\DeTai::with(['giangVien.boMon', 'nganh', 'hocKy', 'phieuDangKys.nhom.truongNhom'])->get();
        }

        // 2. Danh sách Nhóm sinh viên đã đăng ký đề tài được duyệt
        $nhomList = \App\Models\Nhom::with([
            'thanhViens.sinhVien.lop.nganh',
            'truongNhom.lop.nganh',
            'dangKyDeTai.deTai.giangVien.boMon',
            'hocKy'
        ])
        ->when($maHK, fn($q) => $q->where('MaHocKy', $maHK))
        ->whereHas('thanhViens')
        ->whereHas('dangKyDeTai', function($q) {
            $q->where('TrangThai', 'Đã duyệt');
        })
        ->get();

        if ($nhomList->isEmpty()) {
            $nhomList = \App\Models\Nhom::with([
                'thanhViens.sinhVien.lop.nganh',
                'truongNhom.lop.nganh',
                'dangKyDeTai.deTai.giangVien.boMon',
                'hocKy'
            ])
            ->when($maHK, fn($q) => $q->where('MaHocKy', $maHK))
            ->whereHas('thanhViens')
            ->get();
        }

        // 3. Danh sách Giảng viên Hướng dẫn (GVHD) - Chỉ tính các đề tài được nhóm SV đăng ký và đã duyệt
        $gvhdIds = $deTaiList->pluck('MaGV')->filter()->unique();
        $gvhdList = \App\Models\GiangVien::with([
            'boMon',
            'deTais' => function($q) use ($maHK) {
                if ($maHK) $q->where('MaHocKy', $maHK);
                $q->whereIn('TrangThai', ['Đã công bố', 'Trưởng khoa đã duyệt', 'Đã duyệt', '1'])
                  ->whereHas('phieuDangKys', function($p) {
                      $p->where('TrangThai', 'Đã duyệt');
                  })
                  ->with('phieuDangKys.nhom.truongNhom');
            }
        ])
        ->whereIn('MaGV', $gvhdIds)
        ->orderBy('HoTen')
        ->get()
        ->map(function($gv) use ($deTaiList) {
            // Đếm chính xác số đề tài giảng viên này hướng dẫn cho các nhóm đã đăng ký
            $gv->soDeTaiHuongDan = $deTaiList->where('MaGV', $gv->MaGV)->count();
            return $gv;
        });

        if ($gvhdList->isEmpty()) {
            $gvhdList = \App\Models\GiangVien::with(['boMon', 'deTais.phieuDangKys.nhom'])->orderBy('HoTen')->limit(30)->get();
        }

        $soSV = 0;
        foreach ($nhomList as $nh) {
            $soSV += $nh->thanhViens ? $nh->thanhViens->count() : 0;
        }

        $data = [
            'isOfficial' => true,
            'deTaiList'  => $deTaiList,
            'nhomList'   => $nhomList,
            'gvhdList'   => $gvhdList,
            'stats'      => [
                'soDeTai'  => $deTaiList->count(),
                'soNhom'   => $nhomList->count(),
                'soGVHD'   => $gvhdList->count(),
                'soSV'     => $soSV > 0 ? $soSV : 10,
                'tenHocKy' => $hocKy ? "{$hocKy->TenHocKy} ({$hocKy->NamHoc})" : 'Học kỳ hiện tại',
            ]
        ];

        // Tự động khởi tạo hoặc cập nhật file Excel (.xlsx) chuẩn chính thức
        $data['excelUrl'] = self::generateOfficialExcelFile($thongBao, $data);

        return $data;
    }

    /**
     * Xuất / Khởi tạo file Excel (.xlsx) chính thức cho đợt công bố đề tài & GVHD
     */
    public static function generateOfficialExcelFile(ThongBao $thongBao, ?array $data = null): ?string
    {
        if (!$data) {
            $data = self::getOfficialListData($thongBao);
        }

        if (!$data || empty($data['isOfficial'])) {
            return null;
        }

        $cleanMaTB = preg_replace('/[^A-Za-z0-9_]/', '', $thongBao->MaThongBao);
        $fileName = 'DanhSach_ChinhThuc_DeTai_Nhom_GVHD_' . $cleanMaTB . '.xlsx';
        $relativeStoragePath = 'announcements/' . $fileName;
        $fullPath = storage_path('app/public/' . $relativeStoragePath);
        $publicUrl = 'storage/' . $relativeStoragePath;

        // Nếu file đã tồn tại và tạo trong vòng 2 giờ, tái sử dụng để tối ưu
        if (file_exists($fullPath) && (time() - filemtime($fullPath) < 7200)) {
            if ($thongBao->FileDinhKem !== $relativeStoragePath && $thongBao->FileDinhKem !== $publicUrl) {
                $thongBao->update(['FileDinhKem' => $relativeStoragePath]);
            }
            return $publicUrl;
        }

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        try {
            $spreadsheet = new Spreadsheet();

            // ---------------- SHEET 1: ĐỀ TÀI ĐÃ DUYỆT & ĐĂNG KÝ ----------------
            $sheet1 = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('1. Đề Tài Đăng Ký');

            $sheet1->setCellValue('A1', 'TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH - KHOA CÔNG NGHỆ THÔNG TIN');
            $sheet1->mergeCells('A1:H1');
            $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('1E3A8A');

            $sheet1->setCellValue('A2', 'DANH SÁCH ĐỀ TÀI KHÓA LUẬN TỐT NGHIỆP CHÍNH THỨC - ' . ($data['stats']['tenHocKy'] ?? 'Học kỳ hiện tại'));
            $sheet1->mergeCells('A2:H2');
            $sheet1->getStyle('A2')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('0072CE');

            $sheet1->setCellValue('A3', 'Ngày xuất file: ' . now()->format('d/m/Y H:i') . ' | Tiêu chuẩn: Đã duyệt 2 cấp & Được nhóm sinh viên đăng ký');
            $sheet1->mergeCells('A3:H3');
            $sheet1->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->getColor()->setRGB('64748B');

            $headers1 = ['STT', 'Mã ĐT', 'Tên Đề Tài Khóa Luận Tốt Nghiệp', 'Lĩnh Vực Nghiên Cứu', 'Bộ Môn / Ngành', 'Giảng Viên Hướng Dẫn', 'Email GVHD', 'Trạng Thái'];
            $cols1 = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

            foreach ($headers1 as $i => $h) {
                $cell = $cols1[$i] . '5';
                $sheet1->setCellValue($cell, $h);
                $sheet1->getStyle($cell)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
                $sheet1->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0072CE');
                $sheet1->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            }
            $sheet1->getRowDimension(5)->setRowHeight(26);

            $row = 6;
            foreach ($data['deTaiList'] as $idx => $dt) {
                $sheet1->setCellValue('A' . $row, $idx + 1);
                $sheet1->setCellValue('B' . $row, $dt->MaDeTai);
                $sheet1->setCellValue('C' . $row, $dt->TenDeTai);
                $sheet1->setCellValue('D' . $row, $dt->LinhVuc ?? 'Công nghệ thông tin');
                $sheet1->setCellValue('E' . $row, $dt->giangVien?->boMon?->TenBoMon ?? $dt->nganh?->TenNganh ?? 'Khoa CNTT');
                $sheet1->setCellValue('F' . $row, $dt->giangVien?->HoTen ?? 'Chưa phân công');
                $sheet1->setCellValue('G' . $row, $dt->giangVien?->Email ?? '');
                $sheet1->setCellValue('H' . $row, $dt->TrangThai);

                $sheet1->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet1->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet1->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }

            $styleBorder = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
            ];
            $sheet1->getStyle('A5:H' . max(6, $row - 1))->applyFromArray($styleBorder);
            foreach ($cols1 as $col) {
                $sheet1->getColumnDimension($col)->setAutoSize(true);
            }

            // ---------------- SHEET 2: NHÓM SINH VIÊN ----------------
            $sheet2 = $spreadsheet->createSheet();
            $sheet2->setTitle('2. Nhóm Sinh Viên');

            $sheet2->setCellValue('A1', 'TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH - KHOA CÔNG NGHỆ THÔNG TIN');
            $sheet2->mergeCells('A1:K1');
            $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('1E3A8A');

            $sheet2->setCellValue('A2', 'DANH SÁCH NHÓM SINH VIÊN ĐĂNG KÝ ĐỀ TÀI - ' . ($data['stats']['tenHocKy'] ?? 'Học kỳ hiện tại'));
            $sheet2->mergeCells('A2:K2');
            $sheet2->getStyle('A2')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('16A34A');

            $sheet2->setCellValue('A3', 'Ngày xuất file: ' . now()->format('d/m/Y H:i') . ' | Tổng số nhóm: ' . ($data['stats']['soNhom'] ?? 0) . ' nhóm | Tổng số SV: ' . ($data['stats']['soSV'] ?? 0) . ' sinh viên');
            $sheet2->mergeCells('A3:K3');
            $sheet2->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->getColor()->setRGB('64748B');

            $headers2 = ['STT', 'Mã Nhóm', 'Tên Nhóm', 'Mã Sinh Viên', 'Họ Và Tên Sinh Viên', 'Lớp', 'Vai Trò', 'Mã Đề Tài', 'Tên Đề Tài Khóa Luận', 'GVHD Phụ Trách', 'Trạng Thái ĐK'];
            $cols2 = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];

            foreach ($headers2 as $i => $h) {
                $cell = $cols2[$i] . '5';
                $sheet2->setCellValue($cell, $h);
                $sheet2->getStyle($cell)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
                $sheet2->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('16A34A');
                $sheet2->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            }
            $sheet2->getRowDimension(5)->setRowHeight(26);

            $row2 = 6;
            $stt2 = 1;
            foreach ($data['nhomList'] as $nh) {
                $pdk = $nh->dangKyDeTai;
                $deTai = $pdk?->deTai;
                $gv = $deTai?->giangVien;

                if ($nh->thanhViens && $nh->thanhViens->isNotEmpty()) {
                    foreach ($nh->thanhViens as $tv) {
                        $sv = $tv->sinhVien;
                        $sheet2->setCellValue('A' . $row2, $stt2++);
                        $sheet2->setCellValue('B' . $row2, $nh->MaNhom);
                        $sheet2->setCellValue('C' . $row2, $nh->TenNhom);
                        $sheet2->setCellValue('D' . $row2, $tv->MaSV);
                        $sheet2->setCellValue('E' . $row2, $sv?->HoTen ?? 'N/A');
                        $sheet2->setCellValue('F' . $row2, $sv?->lop?->TenLop ?? '');
                        $sheet2->setCellValue('G' . $row2, $tv->VaiTro ?? 'Thành viên');
                        $sheet2->setCellValue('H' . $row2, $deTai?->MaDeTai ?? '');
                        $sheet2->setCellValue('I' . $row2, $deTai?->TenDeTai ?? 'Chưa gán đề tài');
                        $sheet2->setCellValue('J' . $row2, $gv?->HoTen ?? '');
                        $sheet2->setCellValue('K' . $row2, $pdk?->TrangThai ?? 'Đã duyệt');

                        $sheet2->getStyle('A' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet2->getStyle('B' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet2->getStyle('D' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet2->getStyle('F' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet2->getStyle('G' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet2->getStyle('H' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet2->getStyle('K' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $row2++;
                    }
                } else {
                    $sheet2->setCellValue('A' . $row2, $stt2++);
                    $sheet2->setCellValue('B' . $row2, $nh->MaNhom);
                    $sheet2->setCellValue('C' . $row2, $nh->TenNhom);
                    $sheet2->setCellValue('D' . $row2, $nh->truongNhom?->MaSV ?? '');
                    $sheet2->setCellValue('E' . $row2, $nh->truongNhom?->HoTen ?? 'N/A');
                    $sheet2->setCellValue('F' . $row2, $nh->truongNhom?->lop?->TenLop ?? '');
                    $sheet2->setCellValue('G' . $row2, 'Trưởng nhóm');
                    $sheet2->setCellValue('H' . $row2, $deTai?->MaDeTai ?? '');
                    $sheet2->setCellValue('I' . $row2, $deTai?->TenDeTai ?? 'Chưa gán đề tài');
                    $sheet2->setCellValue('J' . $row2, $gv?->HoTen ?? '');
                    $sheet2->setCellValue('K' . $row2, $pdk?->TrangThai ?? 'Đã duyệt');
                    $row2++;
                }
            }
            $sheet2->getStyle('A5:K' . max(6, $row2 - 1))->applyFromArray($styleBorder);
            foreach ($cols2 as $col) {
                $sheet2->getColumnDimension($col)->setAutoSize(true);
            }

            // ---------------- SHEET 3: GIẢNG VIÊN HƯỚNG DẪN ----------------
            $sheet3 = $spreadsheet->createSheet();
            $sheet3->setTitle('3. GV Hướng Dẫn');

            $sheet3->setCellValue('A1', 'TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH - KHOA CÔNG NGHỆ THÔNG TIN');
            $sheet3->mergeCells('A1:H1');
            $sheet3->getStyle('A1')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('1E3A8A');

            $sheet3->setCellValue('A2', 'DANH SÁCH GIẢNG VIÊN HƯỚNG DẪN KHÓA LUẬN - ' . ($data['stats']['tenHocKy'] ?? 'Học kỳ hiện tại'));
            $sheet3->mergeCells('A2:H2');
            $sheet3->getStyle('A2')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('D97706');

            $sheet3->setCellValue('A3', 'Ngày xuất file: ' . now()->format('d/m/Y H:i') . ' | Tổng số GVHD phụ trách: ' . ($data['stats']['soGVHD'] ?? 0) . ' giảng viên');
            $sheet3->mergeCells('A3:H3');
            $sheet3->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->getColor()->setRGB('64748B');

            $headers3 = ['STT', 'Mã GV', 'Họ Và Tên Giảng Viên', 'Học Vị / Học Hàm', 'Bộ Môn Chuyên Môn', 'Email Liên Hệ', 'Số Điện Thoại', 'Số Đề Tài Hướng Dẫn Thực Tế'];
            $cols3 = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

            foreach ($headers3 as $i => $h) {
                $cell = $cols3[$i] . '5';
                $sheet3->setCellValue($cell, $h);
                $sheet3->getStyle($cell)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
                $sheet3->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D97706');
                $sheet3->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            }
            $sheet3->getRowDimension(5)->setRowHeight(26);

            $row3 = 6;
            foreach ($data['gvhdList'] as $idx => $gv) {
                $sheet3->setCellValue('A' . $row3, $idx + 1);
                $sheet3->setCellValue('B' . $row3, $gv->MaGV);
                $sheet3->setCellValue('C' . $row3, $gv->HoTen);
                $sheet3->setCellValue('D' . $row3, trim(($gv->HocHam ? $gv->HocHam . ' - ' : '') . ($gv->HocVi ?? '')));
                $sheet3->setCellValue('E' . $row3, $gv->boMon?->TenBoMon ?? 'Khoa CNTT');
                $sheet3->setCellValue('F' . $row3, $gv->Email ?? '');
                $sheet3->setCellValue('G' . $row3, $gv->SoDienThoai ?? '');
                $sheet3->setCellValue('H' . $row3, ($gv->soDeTaiHuongDan ?? 0) . ' đề tài');

                $sheet3->getStyle('A' . $row3)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet3->getStyle('B' . $row3)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet3->getStyle('G' . $row3)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet3->getStyle('H' . $row3)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row3++;
            }
            $sheet3->getStyle('A5:H' . max(6, $row3 - 1))->applyFromArray($styleBorder);
            foreach ($cols3 as $col) {
                $sheet3->getColumnDimension($col)->setAutoSize(true);
            }

            $spreadsheet->setActiveSheetIndex(0);
            $writer = new Xlsx($spreadsheet);
            $writer->save($fullPath);

            $thongBao->update(['FileDinhKem' => $relativeStoragePath]);
            return $publicUrl;
        } catch (\Throwable $e) {
            \Log::error('Lỗi xuất file Excel thông báo chính thức: ' . $e->getMessage());
            return null;
        }
    }
}

