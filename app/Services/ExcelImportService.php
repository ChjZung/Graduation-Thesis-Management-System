<?php

namespace App\Services;

use App\Models\BoMon;
use App\Models\Nganh;
use App\Models\Lop;
use App\Models\MonHoc;
use App\Models\GiangVien;
use App\Models\SinhVien;
use App\Models\DeTai;
use App\Models\HocKy;
use App\Models\PhanCongHuongDanLop;
use App\Models\NhomDoAn;
use App\Models\ThanhVienNhom;
use App\Models\TaiKhoan;
use App\Models\LopHocPhan;
use App\Models\SinhVienLopHocPhan;
use App\Models\VaiTro;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Exception;

class ExcelImportService
{
    /**
     * Read an Excel (.xlsx/.xls) or CSV file into array of associative rows.
     *
     * Strategy (in order of priority):
     *   1. CSV / TXT  → PhpSpreadsheet Csv reader (no ZipArchive needed)
     *   2. XLSX / XLS → PhpSpreadsheet IOFactory (requires php_zip / ZipArchive)
     *   3. Fallback   → native PHP fgetcsv() — works for CSV regardless of server config
     */
    private function parseFile($file): array
    {
        if (!$file || !$file->isValid()) {
            throw new Exception("File tải lên không hợp lệ hoặc không tồn tại.");
        }

        $filePath  = $file->getRealPath();
        $origName  = $file->getClientOriginalName();
        $ext       = strtolower($file->getClientOriginalExtension());

        $spreadsheet = null;

        // --- Strategy 1: CSV / TXT via PhpSpreadsheet (no ZipArchive) ---
        if (in_array($ext, ['csv', 'txt'])) {
            try {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                $reader->setInputEncoding(\PhpOffice\PhpSpreadsheet\Reader\Csv::guessEncoding($filePath));
                $reader->setDelimiter(',');
                $spreadsheet = $reader->load($filePath);
            } catch (\Throwable $e) {
                // fall through to native CSV
            }
        }

        // --- Strategy 2: XLSX / XLS via PhpSpreadsheet IOFactory ---
        if ($spreadsheet === null && in_array($ext, ['xlsx', 'xls'])) {
            try {
                $spreadsheet = IOFactory::load($filePath);
            } catch (\Throwable $e) {
                // ZipArchive or other error — fall through to native CSV fallback
                \Illuminate\Support\Facades\Log::warning("PhpSpreadsheet failed for {$origName}: " . $e->getMessage() . ". Retrying as CSV.");
            }
        }

        $knownHeaders = [
            'MSSV', 'HoTen', 'TenLop', 'Email', 'SoDienThoai', 'TenDeTai', 'TenBoMon', 'TenNganh', 'TenMon', 'TenDangNhap',
            'TenHocKy', 'MaGV', 'MaLop', 'MaLopHP', 'TenLopHP', 'MaMon', 'MaHocKy', 'TenHoiDong', 'MaHoiDong',
            'MaSV_TruongNhom', 'TruongNhom', 'VaiTroHoiDong', 'SoTinChi', 'SiSoToiDa', 'MoTa', 'YeuCau',
            'HanDangKy', 'HanBaoCao', 'HanNopSanPham'
        ];

        // --- Strategy 3: Native PHP fgetcsv fallback (no extensions needed) ---
        if ($spreadsheet !== null) {
            // Parse using PhpSpreadsheet result
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray(null, true, true, true);

            if (empty($rows)) {
                return [];
            }

            $headers = null;
            $parsedData = [];

            foreach ($rows as $lineNum => $row) {
                $rowValues = array_map(fn($v) => trim((string)$v), $row);

                // Nếu chưa tìm thấy Header Row, kiểm tra xem dòng này có phải Header Row không
                if ($headers === null) {
                    $isHeader = false;
                    foreach ($rowValues as $val) {
                        if (in_array($val, $knownHeaders)) {
                            $isHeader = true;
                            break;
                        }
                    }

                    // Nếu không có từ khóa mẫu, kiểm tra nếu dòng có >= 2 ô dữ liệu và không chứa '💡' / 'Lưu ý' / 'Note'
                    if (!$isHeader) {
                        $firstCell = reset($rowValues);
                        if (!empty($firstCell) && !str_contains($firstCell, '💡') && !str_starts_with(mb_strtolower($firstCell), 'lưu ý') && !str_starts_with(strtolower($firstCell), 'note')) {
                            $nonEmptyCount = count(array_filter($rowValues, fn($v) => $v !== ''));
                            if ($nonEmptyCount >= 2) {
                                $isHeader = true;
                            }
                        }
                    }

                    if ($isHeader) {
                        $headers = [];
                        foreach ($row as $col => $val) {
                            $headers[$col] = trim((string)$val);
                        }
                    }
                    continue;
                }

                // Đã tìm thấy Header Row, đọc các dòng dữ liệu bên dưới
                $dataRow = ['_row_num' => $lineNum];
                $hasData = false;
                foreach ($row as $col => $val) {
                    $headerName = $headers[$col] ?? '';
                    if (!empty($headerName)) {
                        $cleanedVal = trim((string)$val);
                        $dataRow[$headerName] = $cleanedVal;
                        if ($cleanedVal !== '') {
                            $hasData = true;
                        }
                    }
                }
                if ($hasData) {
                    $parsedData[] = $dataRow;
                }
            }

            if ($headers === null) {
                throw new Exception("File {$origName} không chứa dòng tiêu đề hợp lệ.");
            }

            return $parsedData;
        }

        // --- Native fgetcsv (works for CSV/text files without any PHP extension) ---
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new Exception("Không thể mở file {$origName}. Vui lòng kiểm tra quyền đọc file.");
        }

        // Detect & strip BOM (UTF-8: EF BB BF)
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers     = null;
        $parsedData  = [];
        $lineNum     = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $lineNum++;
            // Skip blank lines
            if (count($row) === 1 && ($row[0] === null || trim($row[0]) === '')) {
                continue;
            }

            $rowValues = array_map(fn($v) => trim((string)$v), $row);

            if ($headers === null) {
                $isHeader = false;
                foreach ($rowValues as $val) {
                    if (in_array($val, $knownHeaders)) {
                        $isHeader = true;
                        break;
                    }
                }

                if (!$isHeader) {
                    $firstCell = reset($rowValues);
                    if (!empty($firstCell) && !str_contains($firstCell, '💡') && !str_starts_with(mb_strtolower($firstCell), 'lưu ý') && !str_starts_with(strtolower($firstCell), 'note')) {
                        $nonEmptyCount = count(array_filter($rowValues, fn($v) => $v !== ''));
                        if ($nonEmptyCount >= 2) {
                            $isHeader = true;
                        }
                    }
                }

                if ($isHeader) {
                    $headers = array_map(fn($h) => trim($h), $row);
                }
                continue;
            }

            $dataRow  = ['_row_num' => $lineNum];
            $hasData  = false;

            foreach ($row as $idx => $val) {
                $headerName = $headers[$idx] ?? '';
                if (!empty($headerName)) {
                    $cleanedVal = trim((string)$val);
                    $dataRow[$headerName] = $cleanedVal;
                    if ($cleanedVal !== '') {
                        $hasData = true;
                    }
                }
            }

            if ($hasData) {
                $parsedData[] = $dataRow;
            }
        }

        fclose($handle);

        if ($headers === null) {
            throw new Exception("File {$origName} không có dòng tiêu đề hợp lệ. Vui lòng dùng file mẫu để nhập đúng định dạng.");
        }

        return $parsedData;
    }

    /**
     * Generate a CSV error log file for failed rows.
     * Uses native PHP fputcsv — NO ZipArchive / PhpSpreadsheet dependency.
     */
    private function generateErrorFile(array $errors): ?string
    {
        if (empty($errors)) return null;

        try {
            $fileName     = 'import_error_' . time() . '_' . rand(1000, 9999) . '.csv';
            $relativePath = 'import-errors/' . $fileName;
            $fullPath     = storage_path('app/public/' . $relativePath);

            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            $handle = fopen($fullPath, 'w');
            if ($handle === false) return null;

            // UTF-8 BOM — Excel sẽ mở đúng tiếng Việt
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Dòng số', 'Lý do lỗi', 'Chi tiết bản ghi']);

            foreach ($errors as $err) {
                fputcsv($handle, [
                    $err['row']    ?? '',
                    $err['reason'] ?? '',
                    json_encode($err['data'] ?? [], JSON_UNESCAPED_UNICODE),
                ]);
            }

            fclose($handle);

            return route('import.error.download', ['filename' => $fileName]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('generateErrorFile failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper validation for Email
     */
    private function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Helper validation for 10-digit Phone Number
     */
    private function isValidPhone(string $phone): bool
    {
        return preg_match('/^0[0-9]{9}$/', $phone) === 1;
    }

    private function resolveBoMonId($val): ?int
    {
        if (empty($val)) return 1;
        if (is_numeric($val) && BoMon::where('MaBoMon', $val)->exists()) return (int)$val;
        return BoMon::where('TenBoMon', 'LIKE', '%' . trim($val) . '%')->value('MaBoMon') ?? 1;
    }

    private function resolveNganhId($val): ?int
    {
        if (empty($val)) return 1;
        if (is_numeric($val) && Nganh::where('MaNganh', $val)->exists()) return (int)$val;
        return Nganh::where('TenNganh', 'LIKE', '%' . trim($val) . '%')->value('MaNganh') ?? 1;
    }

    private function resolveLopId($val): ?int
    {
        if (empty($val)) return null;
        if (is_numeric($val) && Lop::where('MaLop', $val)->exists()) return (int)$val;
        return Lop::where('TenLop', 'LIKE', '%' . trim($val) . '%')->value('MaLop');
    }

    private function resolveMonHocId($val): ?int
    {
        if (empty($val)) return null;
        if (is_numeric($val) && MonHoc::where('MaMon', $val)->exists()) return (int)$val;
        return MonHoc::where('TenMon', 'LIKE', '%' . trim($val) . '%')->value('MaMon');
    }

    private function resolveHocKyId($val): ?int
    {
        if (empty($val)) return null;
        if (is_numeric($val) && HocKy::where('MaHocKy', $val)->exists()) return (int)$val;
        return HocKy::where('TenHocKy', 'LIKE', '%' . trim($val) . '%')->value('MaHocKy');
    }

    private function resolveGiangVienId($val): ?int
    {
        if (empty($val)) return null;
        if (is_numeric($val) && GiangVien::where('MaGV', $val)->exists()) return (int)$val;
        $tk = TaiKhoan::where('TenDangNhap', trim($val))->first();
        if ($tk) {
            $gv = GiangVien::where('MaTK', $tk->MaTK)->first();
            if ($gv) return $gv->MaGV;
        }
        return GiangVien::where('HoTen', 'LIKE', '%' . trim($val) . '%')->value('MaGV');
    }

    private function resolveSinhVienId($val): ?int
    {
        if (empty($val)) return null;
        if (is_numeric($val) && SinhVien::where('MaSV', $val)->exists()) return (int)$val;
        $tk = TaiKhoan::where('TenDangNhap', trim($val))->first();
        if ($tk) {
            $sv = SinhVien::where('MaTK', $tk->MaTK)->first();
            if ($sv) return $sv->MaSV;
        }
        return SinhVien::where('HoTen', 'LIKE', '%' . trim($val) . '%')->value('MaSV');
    }

    private function resolveLopHocPhanId($val): ?int
    {
        if (empty($val)) return null;
        if (is_numeric($val) && \App\Models\LopHocPhan::where('MaLopHP', $val)->exists()) return (int)$val;
        return \App\Models\LopHocPhan::where('TenLopHP', 'LIKE', '%' . trim($val) . '%')->value('MaLopHP');
    }

    // ==========================================
    // 1. IMPORT NGÀNH
    // ==========================================
    public function importNganh($file): array
    {
        $rows = $this->parseFile($file);
        $success = 0;
        $errors = [];
        $seenNganh = [];

        foreach ($rows as $row) {
            $rNum = $row['_row_num'];
            $tenNganh = $row['TenNganh'] ?? '';
            $maBoMon = $row['MaBoMon'] ?? 1;

            if (empty($tenNganh)) {
                $errors[] = ['row' => $rNum, 'reason' => 'Tên Ngành không được để trống', 'data' => $row];
                continue;
            }

            if (in_array(mb_strtolower($tenNganh), $seenNganh)) {
                $errors[] = ['row' => $rNum, 'reason' => "Tên Ngành '{$tenNganh}' bị trùng lặp trong file Excel", 'data' => $row];
                continue;
            }
            $seenNganh[] = mb_strtolower($tenNganh);

            if (Nganh::where('TenNganh', $tenNganh)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Ngành '{$tenNganh}' đã tồn tại trong cơ sở dữ liệu", 'data' => $row];
                continue;
            }

            try {
                Nganh::create([
                    'TenNganh' => $tenNganh,
                    'MoTa'     => $row['MoTa'] ?? '',
                    'MaBoMon'  => $maBoMon
                ]);
                $success++;
            } catch (Exception $e) {
                $errors[] = ['row' => $rNum, 'reason' => $e->getMessage(), 'data' => $row];
            }
        }

        AuditLog::log('import_nganh', 'Nganh', null, ['success' => $success, 'errors' => count($errors)]);
        return [
            'total_count' => count($rows),
            'success_count' => $success,
            'error_count' => count($errors),
            'errors' => $errors,
            'error_file' => $this->generateErrorFile($errors)
        ];
    }

    // ==========================================
    // 2. IMPORT LỚP
    // ==========================================
    public function importLop($file): array
    {
        $rows = $this->parseFile($file);
        $success = 0;
        $errors = [];
        $seenLop = [];

        foreach ($rows as $row) {
            $rNum = $row['_row_num'];
            $tenLop = $row['TenLop'] ?? '';
            $maNganh = $row['MaNganh'] ?? 1;

            if (empty($tenLop)) {
                $errors[] = ['row' => $rNum, 'reason' => 'Tên Lớp không được để trống', 'data' => $row];
                continue;
            }

            if (in_array(mb_strtolower($tenLop), $seenLop)) {
                $errors[] = ['row' => $rNum, 'reason' => "Tên Lớp '{$tenLop}' bị trùng lặp trong file Excel", 'data' => $row];
                continue;
            }
            $seenLop[] = mb_strtolower($tenLop);

            if (Lop::where('TenLop', $tenLop)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Lớp '{$tenLop}' đã tồn tại trong cơ sở dữ liệu", 'data' => $row];
                continue;
            }

            try {
                Lop::create([
                    'TenLop' => $tenLop,
                    'MaNganh' => $maNganh,
                    'KhoaHoc' => $row['KhoaHoc'] ?? '2021-2025'
                ]);
                $success++;
            } catch (Exception $e) {
                $errors[] = ['row' => $rNum, 'reason' => $e->getMessage(), 'data' => $row];
            }
        }

        AuditLog::log('import_lop', 'Lop', null, ['success' => $success, 'errors' => count($errors)]);
        return [
            'total_count' => count($rows),
            'success_count' => $success,
            'error_count' => count($errors),
            'errors' => $errors,
            'error_file' => $this->generateErrorFile($errors)
        ];
    }

    // ==========================================
    // 3. IMPORT MÔN HỌC
    // ==========================================
    public function importMonHoc($file): array
    {
        $rows = $this->parseFile($file);
        $success = 0;
        $errors = [];
        $seenMon = [];

        foreach ($rows as $row) {
            $rNum = $row['_row_num'];
            $tenMon = $row['TenMon'] ?? '';
            $maBoMon = $row['MaBoMon'] ?? 1;

            if (empty($tenMon)) {
                $errors[] = ['row' => $rNum, 'reason' => 'Tên Môn không được để trống', 'data' => $row];
                continue;
            }

            if (in_array(mb_strtolower($tenMon), $seenMon)) {
                $errors[] = ['row' => $rNum, 'reason' => "Tên môn '{$tenMon}' bị trùng lặp trong file Excel", 'data' => $row];
                continue;
            }
            $seenMon[] = mb_strtolower($tenMon);

            if (MonHoc::where('TenMon', $tenMon)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Môn học '{$tenMon}' đã tồn tại trong cơ sở dữ liệu", 'data' => $row];
                continue;
            }

            try {
                MonHoc::create([
                    'TenMon' => $tenMon,
                    'SoTinChi' => $row['SoTinChi'] ?? 3,
                    'MaBoMon' => $maBoMon
                ]);
                $success++;
            } catch (Exception $e) {
                $errors[] = ['row' => $rNum, 'reason' => $e->getMessage(), 'data' => $row];
            }
        }

        AuditLog::log('import_mon_hoc', 'MonHoc', null, ['success' => $success, 'errors' => count($errors)]);
        return [
            'total_count' => count($rows),
            'success_count' => $success,
            'error_count' => count($errors),
            'errors' => $errors,
            'error_file' => $this->generateErrorFile($errors)
        ];
    }

    // ==========================================
    // 4. IMPORT GIẢNG VIÊN
    // ==========================================
    public function importGiangVien($file): array
    {
        $rows = $this->parseFile($file);
        $success = 0;
        $errors = [];
        $seenUsernames = [];
        $seenEmails = [];
        $seenPhones = [];

        foreach ($rows as $row) {
            $rNum = $row['_row_num'];
            $username = trim($row['TenDangNhap'] ?? '');
            $hoTen = trim($row['HoTen'] ?? '');
            $email = trim($row['Email'] ?? '');
            $phone = trim($row['SoDienThoai'] ?? '');

            if (empty($username) || empty($hoTen)) {
                $errors[] = ['row' => $rNum, 'reason' => 'Tên đăng nhập / Mã GV và Họ tên không được để trống', 'data' => $row];
                continue;
            }

            if (in_array(mb_strtolower($username), $seenUsernames)) {
                $errors[] = ['row' => $rNum, 'reason' => "Mã giảng viên / Tên đăng nhập '{$username}' bị trùng lặp trong file Excel", 'data' => $row];
                continue;
            }
            $seenUsernames[] = mb_strtolower($username);

            if (!empty($email)) {
                if (!$this->isValidEmail($email)) {
                    $errors[] = ['row' => $rNum, 'reason' => "Email '{$email}' không đúng định dạng", 'data' => $row];
                    continue;
                }
                if (in_array(mb_strtolower($email), $seenEmails)) {
                    $errors[] = ['row' => $rNum, 'reason' => "Email '{$email}' bị trùng lặp trong file Excel", 'data' => $row];
                    continue;
                }
                $seenEmails[] = mb_strtolower($email);
            }

            if (!empty($phone)) {
                if (!$this->isValidPhone($phone)) {
                    $errors[] = ['row' => $rNum, 'reason' => "Số điện thoại '{$phone}' không đúng 10 chữ số (ví dụ: 0912345678)", 'data' => $row];
                    continue;
                }
                if (in_array($phone, $seenPhones)) {
                    $errors[] = ['row' => $rNum, 'reason' => "Số điện thoại '{$phone}' bị trùng lặp trong file Excel", 'data' => $row];
                    continue;
                }
                $seenPhones[] = $phone;
            }

            if (TaiKhoan::where('TenDangNhap', $username)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Mã giảng viên / Tên đăng nhập '{$username}' đã tồn tại trong hệ thống", 'data' => $row];
                continue;
            }

            if (!empty($email) && GiangVien::where('Email', $email)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Email '{$email}' đã trùng lặp trong cơ sở dữ liệu", 'data' => $row];
                continue;
            }

            try {
                DB::transaction(function () use ($username, $hoTen, $email, $phone, $row) {
                    $tk = TaiKhoan::create([
                        'TenDangNhap' => $username,
                        'MatKhau' => Hash::make('123456'),
                        'MaVaiTro' => 2, // Giảng viên
                        'TrangThai' => true
                    ]);

                    GiangVien::create([
                        'MaTK' => $tk->MaTK,
                        'MaBoMon' => $row['MaBoMon'] ?? 1,
                        'HoTen' => $hoTen,
                        'Email' => !empty($email) ? $email : ($username . '@fe.edu.vn'),
                        'SoDienThoai' => !empty($phone) ? $phone : ('090' . rand(1000000, 9999999)),
                        'HocVi' => $row['HocVi'] ?? 'Thạc sĩ'
                    ]);
                });
                $success++;
            } catch (Exception $e) {
                $errors[] = ['row' => $rNum, 'reason' => $e->getMessage(), 'data' => $row];
            }
        }

        AuditLog::log('import_giang_vien', 'GiangVien', null, ['success' => $success, 'errors' => count($errors)]);
        return [
            'total_count' => count($rows),
            'success_count' => $success,
            'error_count' => count($errors),
            'errors' => $errors,
            'error_file' => $this->generateErrorFile($errors)
        ];
    }

    // ==========================================
    // 5. IMPORT SINH VIÊN
    // ==========================================
    public function importSinhVien($file): array
    {
        $rows = $this->parseFile($file);
        $success = 0;
        $errors = [];
        $seenMSSVs = [];
        $seenEmails = [];
        $seenPhones = [];

        foreach ($rows as $row) {
            $rNum = $row['_row_num'];
            $username = trim($row['TenDangNhap'] ?? '');
            $hoTen = trim($row['HoTen'] ?? '');
            $email = trim($row['Email'] ?? '');
            $phone = trim($row['SoDienThoai'] ?? '');

            if (empty($username) || empty($hoTen)) {
                $errors[] = ['row' => $rNum, 'reason' => 'Tên đăng nhập (MSSV) và Họ tên không được để trống', 'data' => $row];
                continue;
            }

            if (in_array(mb_strtolower($username), $seenMSSVs)) {
                $errors[] = ['row' => $rNum, 'reason' => "MSSV '{$username}' bị trùng lặp trong file Excel", 'data' => $row];
                continue;
            }
            $seenMSSVs[] = mb_strtolower($username);

            if (!empty($email)) {
                if (!$this->isValidEmail($email)) {
                    $errors[] = ['row' => $rNum, 'reason' => "Email '{$email}' không hợp lệ", 'data' => $row];
                    continue;
                }
                if (in_array(mb_strtolower($email), $seenEmails)) {
                    $errors[] = ['row' => $rNum, 'reason' => "Email '{$email}' bị trùng lặp trong file Excel", 'data' => $row];
                    continue;
                }
                $seenEmails[] = mb_strtolower($email);
            }

            if (!empty($phone)) {
                if (!$this->isValidPhone($phone)) {
                    $errors[] = ['row' => $rNum, 'reason' => "Số điện thoại '{$phone}' không đúng 10 chữ số (bắt đầu bằng 0)", 'data' => $row];
                    continue;
                }
                if (in_array($phone, $seenPhones)) {
                    $errors[] = ['row' => $rNum, 'reason' => "Số điện thoại '{$phone}' bị trùng lặp trong file Excel", 'data' => $row];
                    continue;
                }
                $seenPhones[] = $phone;
            }

            if (TaiKhoan::where('TenDangNhap', $username)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "MSSV / Tài khoản '{$username}' đã tồn tại trong cơ sở dữ liệu", 'data' => $row];
                continue;
            }

            if (!empty($email) && SinhVien::where('Email', $email)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Email '{$email}' đã trùng lặp trong cơ sở dữ liệu", 'data' => $row];
                continue;
            }

            try {
                DB::transaction(function () use ($username, $hoTen, $email, $phone, $row) {
                    $tk = TaiKhoan::create([
                        'TenDangNhap' => $username,
                        'MatKhau' => Hash::make('123456'),
                        'MaVaiTro' => 3, // Sinh viên
                        'TrangThai' => true
                    ]);

                    SinhVien::create([
                        'MaTK' => $tk->MaTK,
                        'MaLop' => $row['MaLop'] ?? 1,
                        'HoTen' => $hoTen,
                        'Email' => !empty($email) ? $email : ($username . '@st.fe.edu.vn'),
                        'SoDienThoai' => !empty($phone) ? $phone : ('098' . rand(1000000, 9999999))
                    ]);
                });
                $success++;
            } catch (Exception $e) {
                $errors[] = ['row' => $rNum, 'reason' => $e->getMessage(), 'data' => $row];
            }
        }

        AuditLog::log('import_sinh_vien', 'SinhVien', null, ['success' => $success, 'errors' => count($errors)]);
        return [
            'total_count' => count($rows),
            'success_count' => $success,
            'error_count' => count($errors),
            'errors' => $errors,
            'error_file' => $this->generateErrorFile($errors)
        ];
    }

    // ==========================================
    // 6. IMPORT ĐỀ TÀI (GIẢNG VIÊN THỰC HIỆN)
    // ==========================================
    public function importDeTai($file, int $maTK, ?int $maLopPhanCong = null): array
    {
        $rows = $this->parseFile($file);
        $success = 0;
        $errors = [];
        $seenDeTai = [];

        foreach ($rows as $row) {
            $rNum = $row['_row_num'];
            $tenDeTai = trim($row['TenDeTai'] ?? '');

            // Thử resolve Lớp Học Phần
            $maLopHP = $this->resolveLopHocPhanId($row['MaLopHP'] ?? $row['TenLopHP'] ?? null);
            $lopHP = $maLopHP ? \App\Models\LopHocPhan::find($maLopHP) : null;

            $maMon = $lopHP ? $lopHP->MaMon : $this->resolveMonHocId($row['MaMon'] ?? $row['TenMon'] ?? 1);
            $maHocKy = $lopHP ? $lopHP->MaHocKy : $this->resolveHocKyId($row['MaHocKy'] ?? $row['TenHocKy'] ?? 1);
            $maLop = $this->resolveLopId($row['MaLop'] ?? $row['TenLop'] ?? $maLopPhanCong ?? null);

            if (empty($tenDeTai)) {
                $errors[] = ['row' => $rNum, 'reason' => 'Tên đề tài không được để trống', 'data' => $row];
                continue;
            }

            $uniqueKey = mb_strtolower($tenDeTai) . '_' . ($maLopHP ?? $maLop ?? 0);
            if (in_array($uniqueKey, $seenDeTai)) {
                $errors[] = ['row' => $rNum, 'reason' => "Tên đề tài '{$tenDeTai}' bị trùng lặp trong file Excel", 'data' => $row];
                continue;
            }
            $seenDeTai[] = $uniqueKey;

            // Kiểm tra trùng tên đề tài
            $queryExists = DeTai::where('TenDeTai', $tenDeTai);
            if ($maLopHP) {
                $queryExists->where('MaLopHP', $maLopHP);
            } elseif ($maLop) {
                $queryExists->where('MaLop', $maLop);
            }
            if ($queryExists->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Đề tài '{$tenDeTai}' đã bị trùng lặp trong lớp/lớp học phần", 'data' => $row];
                continue;
            }

            try {
                DeTai::create([
                    'MaTK' => $maTK,
                    'MaMon' => $maMon ?? 1,
                    'MaLop' => $maLop,
                    'MaLopHP' => $maLopHP,
                    'MaHocKy' => $maHocKy ?? 1,
                    'TenDeTai' => $tenDeTai,
                    'MoTa' => $row['MoTa'] ?? '',
                    'YeuCau' => $row['YeuCau'] ?? '',
                    'HanDangKy' => $row['HanDangKy'] ?? null,
                    'HanBaoCao' => $row['HanBaoCao'] ?? null,
                    'HanNopSanPham' => $row['HanNopSanPham'] ?? null,
                    'TrangThai' => 'Đang mở đăng ký',
                    'NgayTao' => now()->toDateString()
                ]);
                $success++;
            } catch (Exception $e) {
                $errors[] = ['row' => $rNum, 'reason' => $e->getMessage(), 'data' => $row];
            }
        }

        AuditLog::log('import_de_tai', 'DeTai', null, ['success' => $success, 'errors' => count($errors)]);
        return [
            'total_count' => count($rows),
            'success_count' => $success,
            'error_count' => count($errors),
            'errors' => $errors,
            'error_file' => $this->generateErrorFile($errors)
        ];
    }

    // ==========================================
    // 7. IMPORT BỘ MÔN
    // ==========================================
    public function importBoMon($file): array
    {
        $rows = $this->parseFile($file);
        $success = 0;
        $errors = [];
        $seenBoMon = [];

        foreach ($rows as $row) {
            $rNum = $row['_row_num'];
            $tenBoMon = trim($row['TenBoMon'] ?? '');

            if (empty($tenBoMon)) {
                $errors[] = ['row' => $rNum, 'reason' => 'Tên Bộ Môn không được để trống', 'data' => $row];
                continue;
            }

            if (in_array(mb_strtolower($tenBoMon), $seenBoMon)) {
                $errors[] = ['row' => $rNum, 'reason' => "Tên Bộ Môn '{$tenBoMon}' bị trùng lặp trong file Excel", 'data' => $row];
                continue;
            }
            $seenBoMon[] = mb_strtolower($tenBoMon);

            if (BoMon::where('TenBoMon', $tenBoMon)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Bộ Môn '{$tenBoMon}' đã tồn tại trong cơ sở dữ liệu", 'data' => $row];
                continue;
            }

            try {
                BoMon::create([
                    'TenBoMon' => $tenBoMon,
                    'MoTa' => $row['MoTa'] ?? ''
                ]);
                $success++;
            } catch (Exception $e) {
                $errors[] = ['row' => $rNum, 'reason' => $e->getMessage(), 'data' => $row];
            }
        }

        AuditLog::log('import_bo_mon', 'BoMon', null, ['success' => $success, 'errors' => count($errors)]);
        return [
            'total_count' => count($rows),
            'success_count' => $success,
            'error_count' => count($errors),
            'errors' => $errors,
            'error_file' => $this->generateErrorFile($errors)
        ];
    }

    // ==========================================
    // 8. IMPORT HỌC KỲ
    // ==========================================
    public function importHocKy($file): array
    {
        $rows = $this->parseFile($file);
        $success = 0;
        $errors = [];
        $seenHocKy = [];

        foreach ($rows as $row) {
            $rNum = $row['_row_num'];
            $tenHocKy = trim($row['TenHocKy'] ?? '');
            $namHoc = trim($row['NamHoc'] ?? '');

            if (empty($tenHocKy) || empty($namHoc)) {
                $errors[] = ['row' => $rNum, 'reason' => 'Tên Học Kỳ và Năm Học không được để trống', 'data' => $row];
                continue;
            }

            $key = mb_strtolower($tenHocKy) . '_' . mb_strtolower($namHoc);
            if (in_array($key, $seenHocKy)) {
                $errors[] = ['row' => $rNum, 'reason' => "Học kỳ '{$tenHocKy}' - '{$namHoc}' bị trùng lặp trong file Excel", 'data' => $row];
                continue;
            }
            $seenHocKy[] = $key;

            if (HocKy::where('TenHocKy', $tenHocKy)->where('NamHoc', $namHoc)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Học kỳ '{$tenHocKy}' năm học '{$namHoc}' đã tồn tại trong cơ sở dữ liệu", 'data' => $row];
                continue;
            }

            try {
                HocKy::create([
                    'TenHocKy' => $tenHocKy,
                    'NamHoc' => $namHoc,
                    'NgayBatDau' => !empty($row['NgayBatDau']) ? $row['NgayBatDau'] : null,
                    'NgayKetThuc' => !empty($row['NgayKetThuc']) ? $row['NgayKetThuc'] : null,
                ]);
                $success++;
            } catch (Exception $e) {
                $errors[] = ['row' => $rNum, 'reason' => $e->getMessage(), 'data' => $row];
            }
        }

        AuditLog::log('import_hoc_ky', 'HocKy', null, ['success' => $success, 'errors' => count($errors)]);
        return [
            'total_count' => count($rows),
            'success_count' => $success,
            'error_count' => count($errors),
            'errors' => $errors,
            'error_file' => $this->generateErrorFile($errors)
        ];
    }

    // ==========================================
    // 9. IMPORT PHÂN CÔNG HƯỚNG DẪN LỚP
    // ==========================================
    public function importPhanCong($file): array
    {
        $rows = $this->parseFile($file);
        $success = 0;
        $errors = [];

        foreach ($rows as $row) {
            $rNum = $row['_row_num'] ?? 2;
            $loai = mb_strtolower(trim($row['LoaiPhanCong'] ?? $row['Loai'] ?? ''));
            $gvVal = trim($row['MaGV'] ?? $row['GiangVien'] ?? $row['TenGiangVien'] ?? '');
            $lopVal = trim($row['TenLop_Hoac_TenLopHP'] ?? $row['MaLop'] ?? $row['TenLop'] ?? $row['TenLopHP'] ?? $row['MaLopHP'] ?? '');
            $hkVal = trim($row['MaHocKy'] ?? $row['TenHocKy'] ?? '');

            if (empty($gvVal) || empty($lopVal)) {
                $errors[] = ['row' => $rNum, 'reason' => 'Giảng viên và Lớp (Hành chính hoặc Học phần) không được để trống', 'data' => $row];
                continue;
            }

            // Resolve GiangVien
            $gv = GiangVien::where('MaGV', $gvVal)
                ->orWhere('HoTen', $gvVal)
                ->orWhereHas('taiKhoan', function ($q) use ($gvVal) {
                    $q->where('TenDangNhap', $gvVal);
                })->first();

            if (!$gv) {
                $errors[] = ['row' => $rNum, 'reason' => "Không tìm thấy Giảng viên '{$gvVal}' trong hệ thống", 'data' => $row];
                continue;
            }

            // Determine if Lớp Học Phần or Lớp Hành Chính
            $isLhp = (str_contains($loai, 'học phần') || str_contains($loai, 'tín chỉ') || str_contains($loai, 'hp')) 
                || (!str_contains($loai, 'hành chính') && LopHocPhan::where('TenLopHP', $lopVal)->orWhere('MaLopHP', $lopVal)->exists());

            if ($isLhp) {
                $lhp = LopHocPhan::where('TenLopHP', $lopVal)->orWhere('MaLopHP', $lopVal)->first();
                if (!$lhp) {
                    $errors[] = ['row' => $rNum, 'reason' => "Không tìm thấy Lớp Học Phần '{$lopVal}'", 'data' => $row];
                    continue;
                }
                $lhp->update(['MaGV' => $gv->MaGV]);
                $success++;
            } else {
                $lop = Lop::where('TenLop', $lopVal)->orWhere('MaLop', $lopVal)->first();
                if (!$lop) {
                    $errors[] = ['row' => $rNum, 'reason' => "Không tìm thấy Lớp Hành Chính '{$lopVal}'", 'data' => $row];
                    continue;
                }

                $hk = HocKy::where('TenHocKy', $hkVal)->orWhere('MaHocKy', $hkVal)->first() ?? HocKy::first();
                if (!$hk) {
                    $errors[] = ['row' => $rNum, 'reason' => "Không tìm thấy Học kỳ '{$hkVal}'", 'data' => $row];
                    continue;
                }

                $existing = PhanCongHuongDanLop::where('MaLop', $lop->MaLop)->where('MaHocKy', $hk->MaHocKy)->first();
                if ($existing) {
                    $existing->update(['MaGV' => $gv->MaGV]);
                    $success++;
                } else {
                    PhanCongHuongDanLop::create([
                        'MaGV' => $gv->MaGV,
                        'MaLop' => $lop->MaLop,
                        'MaHocKy' => $hk->MaHocKy,
                        'NgayPhanCong' => !empty($row['NgayPhanCong']) ? $row['NgayPhanCong'] : date('Y-m-d')
                    ]);
                    $success++;
                }
            }
        }

        AuditLog::log('import_phan_cong', 'PhanCong', null, ['success' => $success, 'errors' => count($errors)]);
        return [
            'total_count' => count($rows),
            'success_count' => $success,
            'error_count' => count($errors),
            'errors' => $errors,
            'error_file' => $this->generateErrorFile($errors)
        ];
    }

    // ==========================================
    // 10. IMPORT NHÓM ĐỒ ÁN
    // ==========================================
    public function importNhomDoAn($file): array
    {
        $rows = $this->parseFile($file);
        $success = 0;
        $errors = [];
        $seenGroup = [];

        foreach ($rows as $row) {
            $rNum = $row['_row_num'];
            $tenNhom = trim($row['TenNhom'] ?? '');
            $maMon = $row['MaMon'] ?? null;
            $maLop = $row['MaLop'] ?? null;
            $maHocKy = $row['MaHocKy'] ?? null;
            $maSVLeader = $row['MaSV_TruongNhom'] ?? $row['TruongNhom'] ?? null;

            if (empty($tenNhom) || empty($maMon) || empty($maHocKy)) {
                $errors[] = ['row' => $rNum, 'reason' => 'Tên nhóm, Mã môn, Mã học kỳ không được để trống', 'data' => $row];
                continue;
            }

            if (!MonHoc::where('MaMon', $maMon)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Mã môn học {$maMon} không tồn tại", 'data' => $row];
                continue;
            }

            if (!HocKy::where('MaHocKy', $maHocKy)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Mã học kỳ {$maHocKy} không tồn tại", 'data' => $row];
                continue;
            }

            if (!empty($maLop) && !Lop::where('MaLop', $maLop)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Mã lớp {$maLop} không tồn tại", 'data' => $row];
                continue;
            }

            if (!empty($maSVLeader) && !SinhVien::where('MaSV', $maSVLeader)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Mã sinh viên trưởng nhóm {$maSVLeader} không tồn tại", 'data' => $row];
                continue;
            }

            $key = mb_strtolower($tenNhom) . '_' . $maMon . '_' . $maHocKy;
            if (in_array($key, $seenGroup)) {
                $errors[] = ['row' => $rNum, 'reason' => "Nhóm '{$tenNhom}' bị trùng lặp trong file Excel", 'data' => $row];
                continue;
            }
            $seenGroup[] = $key;

            if (NhomDoAn::where('TenNhom', $tenNhom)->where('MaMon', $maMon)->where('MaHocKy', $maHocKy)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Nhóm '{$tenNhom}' đã tồn tại trong CSDL cho môn học và học kỳ này", 'data' => $row];
                continue;
            }

            try {
                DB::transaction(function () use ($tenNhom, $maMon, $maLop, $maHocKy, $maSVLeader) {
                    $nhom = NhomDoAn::create([
                        'TenNhom' => $tenNhom,
                        'MaMon' => $maMon,
                        'MaLop' => $maLop,
                        'MaHocKy' => $maHocKy,
                        'MaCode' => strtoupper(substr(md5(uniqid()), 0, 8)),
                        'NgayTao' => now()->toDateString()
                    ]);

                    if (!empty($maSVLeader)) {
                        ThanhVienNhom::create([
                            'MaNhom' => $nhom->MaNhom,
                            'MaSV' => $maSVLeader,
                            'VaiTro' => 'Trưởng nhóm',
                            'TrangThai' => 'da_chap_nhan'
                        ]);
                    }
                });
                $success++;
            } catch (Exception $e) {
                $errors[] = ['row' => $rNum, 'reason' => $e->getMessage(), 'data' => $row];
            }
        }

        AuditLog::log('import_nhom_do_an', 'NhomDoAn', null, ['success' => $success, 'errors' => count($errors)]);
        return [
            'total_count' => count($rows),
            'success_count' => $success,
            'error_count' => count($errors),
            'errors' => $errors,
            'error_file' => $this->generateErrorFile($errors)
        ];
    }

    // ==========================================
    // 11. IMPORT LỚP HỌC PHẦN (LỚP TÍN CHỈ)
    // ==========================================
    public function importLopHocPhan($file): array
    {
        $rows = $this->parseFile($file);
        $success = 0;
        $errors = [];
        $seenLHP = [];

        foreach ($rows as $row) {
            $rNum = $row['_row_num'];
            $tenLopHP = trim($row['TenLopHP'] ?? '');
            $maMon = $this->resolveMonHocId($row['MaMon'] ?? $row['TenMon'] ?? null);
            $maHocKy = $this->resolveHocKyId($row['MaHocKy'] ?? $row['TenHocKy'] ?? null);
            $maGV = $this->resolveGiangVienId($row['MaGV'] ?? null);
            $siSo = !empty($row['SiSoToiDa']) ? (int)$row['SiSoToiDa'] : 40;

            if (empty($tenLopHP)) {
                $errors[] = ['row' => $rNum, 'reason' => 'Tên Lớp Học Phần không được để trống', 'data' => $row];
                continue;
            }

            if (!$maMon) {
                $errors[] = ['row' => $rNum, 'reason' => 'Môn học không tồn tại hoặc chưa chọn', 'data' => $row];
                continue;
            }

            if (!$maHocKy) {
                $errors[] = ['row' => $rNum, 'reason' => 'Học kỳ không tồn tại hoặc chưa chọn', 'data' => $row];
                continue;
            }

            if (in_array(mb_strtolower($tenLopHP), $seenLHP)) {
                $errors[] = ['row' => $rNum, 'reason' => "Tên Lớp Học Phần '{$tenLopHP}' bị trùng lặp trong file Excel", 'data' => $row];
                continue;
            }
            $seenLHP[] = mb_strtolower($tenLopHP);

            if (\App\Models\LopHocPhan::where('TenLopHP', $tenLopHP)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Lớp Học Phần '{$tenLopHP}' đã tồn tại trong CSDL", 'data' => $row];
                continue;
            }

            try {
                \App\Models\LopHocPhan::create([
                    'TenLopHP' => $tenLopHP,
                    'MaMon' => $maMon,
                    'MaHocKy' => $maHocKy,
                    'MaGV' => $maGV,
                    'SiSoToiDa' => $siSo,
                    'TrangThai' => 'Đang mở',
                    'MoTa' => $row['MoTa'] ?? ''
                ]);
                $success++;
            } catch (Exception $e) {
                $errors[] = ['row' => $rNum, 'reason' => $e->getMessage(), 'data' => $row];
            }
        }

        AuditLog::log('import_lop_hoc_phan', 'LopHocPhan', null, ['success' => $success, 'errors' => count($errors)]);
        return [
            'total_count' => count($rows),
            'success_count' => $success,
            'error_count' => count($errors),
            'errors' => $errors,
            'error_file' => $this->generateErrorFile($errors)
        ];
    }

    /**
     * Import Sinh Viên vào Lớp Học Phần từ file Excel
     */
    public function importSinhVienLopHocPhan($file, $targetMaLopHP = null)
    {
        $rows = $this->parseFile($file);
        $success = 0;
        $errors = [];

        // Role ID cho Sinh viên
        $roleSV = VaiTro::where('TenVaiTro', 'Sinh viên')->first();
        $roleId = $roleSV->MaVaiTro ?? 3;

        foreach ($rows as $index => $row) {
            $rNum = $index + 2;

            $mssv = trim($row['MSSV'] ?? $row['TenDangNhap'] ?? $row['MaSV'] ?? '');
            if (empty($mssv)) {
                continue;
            }

            // Tìm sinh viên theo MSSV hoặc Tên đăng nhập
            $sv = SinhVien::where('MaSV', $mssv)
                ->orWhereHas('taiKhoan', function ($q) use ($mssv) {
                    $q->where('TenDangNhap', $mssv);
                })->first();

            // Nếu sinh viên chưa có trong CSDL, tự động tạo tài khoản & hồ sơ sinh viên
            if (!$sv) {
                $maLop = null;
                $tenLop = trim($row['TenLop'] ?? $row['MaLop'] ?? '');
                if (!empty($tenLop)) {
                    $lopObj = Lop::where('TenLop', $tenLop)->orWhere('MaLop', $tenLop)->first();
                    if (!$lopObj) {
                        $nganhFirst = Nganh::first();
                        $lopObj = Lop::create([
                            'TenLop' => $tenLop,
                            'MaNganh' => $nganhFirst->MaNganh ?? 1,
                            'KhoaHoc' => date('Y') . '-' . (date('Y') + 4)
                        ]);
                    }
                    $maLop = $lopObj->MaLop;
                }

                try {
                    $tkObj = TaiKhoan::create([
                        'TenDangNhap' => $mssv,
                        'MatKhau' => Hash::make('123456'),
                        'MaVaiTro' => $roleId,
                        'TrangThai' => true
                    ]);

                    $hoTenStr = trim($row['HoTen'] ?? "Sinh Viên {$mssv}");
                    $emailStr = trim($row['Email'] ?? "{$mssv}@st.edu.vn");
                    $sdtStr   = trim($row['SoDienThoai'] ?? '');

                    $sv = SinhVien::create([
                        'MaTK' => $tkObj->MaTK,
                        'MaLop' => $maLop,
                        'HoTen' => $hoTenStr,
                        'Email' => $emailStr,
                        'SoDienThoai' => $sdtStr
                    ]);
                } catch (Exception $e) {
                    $errors[] = ['row' => $rNum, 'reason' => "Tạo sinh viên mới '{$mssv}' thất bại: " . $e->getMessage(), 'data' => $row];
                    continue;
                }
            }

            // Tìm Lớp Học Phần
            $lhp = null;
            if ($targetMaLopHP) {
                $lhp = LopHocPhan::find($targetMaLopHP);
            } else {
                $tenLhp = trim($row['TenLopHP'] ?? $row['MaLopHP'] ?? '');
                if (!empty($tenLhp)) {
                    $lhp = LopHocPhan::where('TenLopHP', $tenLhp)->orWhere('MaLopHP', $tenLhp)->first();
                }
            }

            if (!$lhp) {
                $errors[] = ['row' => $rNum, 'reason' => "Không tìm thấy Lớp Học Phần tương ứng", 'data' => $row];
                continue;
            }

            // Kiểm tra xem sinh viên đã thuộc Lớp HP nào của môn này trong học kỳ này chưa
            $existing = SinhVienLopHocPhan::where('MaSV', $sv->MaSV)
                ->where('MaMon', $lhp->MaMon)
                ->where('MaHocKy', $lhp->MaHocKy)
                ->first();

            if ($existing) {
                if ($existing->MaLopHP == $lhp->MaLopHP) {
                    // Đã có trong lớp HP này rồi thì bỏ qua không báo lỗi
                    continue;
                }
                $errors[] = ['row' => $rNum, 'reason' => "Sinh viên '{$mssv}' đã thuộc Lớp HP khác của môn này trong cùng học kỳ!", 'data' => $row];
                continue;
            }

            // Kiểm tra giới hạn sĩ số tối đa
            $currentCount = SinhVienLopHocPhan::where('MaLopHP', $lhp->MaLopHP)->count();
            if ($currentCount >= $lhp->SiSoToiDa) {
                $errors[] = ['row' => $rNum, 'reason' => "Lớp Học Phần '{$lhp->TenLopHP}' đã đạt sĩ số tối đa ({$lhp->SiSoToiDa} SV)!", 'data' => $row];
                continue;
            }

            try {
                SinhVienLopHocPhan::create([
                    'MaSV' => $sv->MaSV,
                    'MaLopHP' => $lhp->MaLopHP,
                    'MaMon' => $lhp->MaMon,
                    'MaHocKy' => $lhp->MaHocKy,
                    'NgayDangKy' => now(),
                ]);
                $success++;
            } catch (Exception $e) {
                $errors[] = ['row' => $rNum, 'reason' => $e->getMessage(), 'data' => $row];
            }
        }

        AuditLog::log('import_sinhvien_lophocphan', 'SinhVienLopHocPhan', null, ['success' => $success, 'errors' => count($errors)]);

        return [
            'total_count' => count($rows),
            'success_count' => $success,
            'error_count' => count($errors),
            'errors' => $errors,
            'error_file' => $this->generateErrorFile($errors)
        ];
    }
}



