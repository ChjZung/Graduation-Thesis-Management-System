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
            'TenDeTai', 'TenBoMon', 'TenNganh', 'TenLop', 'TenMon', 'TenDangNhap',
            'TenHocKy', 'MaGV', 'MaLop', 'MaMon', 'MaHocKy', 'TenHoiDong', 'MaHoiDong',
            'MaSV_TruongNhom', 'TruongNhom', 'VaiTroHoiDong', 'HoTen', 'Email', 'SoDienThoai'
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
                    'MaBoMon' => $maBoMon
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
            $maMon = $row['MaMon'] ?? 1;
            $maLop = $row['MaLop'] ?? $maLopPhanCong ?? 1;

            if (empty($tenDeTai)) {
                $errors[] = ['row' => $rNum, 'reason' => 'Tên đề tài không được để trống', 'data' => $row];
                continue;
            }

            $uniqueKey = mb_strtolower($tenDeTai) . '_' . $maLop;
            if (in_array($uniqueKey, $seenDeTai)) {
                $errors[] = ['row' => $rNum, 'reason' => "Tên đề tài '{$tenDeTai}' bị trùng lặp trong file Excel cho lớp này", 'data' => $row];
                continue;
            }
            $seenDeTai[] = $uniqueKey;

            // Kiểm tra trùng tên đề tài trong cùng lớp và môn
            if (DeTai::where('TenDeTai', $tenDeTai)->where('MaLop', $maLop)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Đề tài '{$tenDeTai}' đã bị trùng lặp trong lớp", 'data' => $row];
                continue;
            }

            try {
                DeTai::create([
                    'MaTK' => $maTK,
                    'MaMon' => $maMon,
                    'MaLop' => $maLop,
                    'MaHocKy' => $row['MaHocKy'] ?? 1,
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
        $seen = [];

        foreach ($rows as $row) {
            $rNum = $row['_row_num'];
            $maGV = $row['MaGV'] ?? null;
            $maLop = $row['MaLop'] ?? null;
            $maHocKy = $row['MaHocKy'] ?? null;

            if (empty($maGV) || empty($maLop) || empty($maHocKy)) {
                $errors[] = ['row' => $rNum, 'reason' => 'MaGV, MaLop, MaHocKy không được để trống', 'data' => $row];
                continue;
            }

            if (!GiangVien::where('MaGV', $maGV)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Mã Giảng viên {$maGV} không tồn tại", 'data' => $row];
                continue;
            }

            if (!Lop::where('MaLop', $maLop)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Mã Lớp {$maLop} không tồn tại", 'data' => $row];
                continue;
            }

            if (!HocKy::where('MaHocKy', $maHocKy)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Mã Học Kỳ {$maHocKy} không tồn tại", 'data' => $row];
                continue;
            }

            $key = "{$maGV}_{$maLop}_{$maHocKy}";
            if (in_array($key, $seen)) {
                $errors[] = ['row' => $rNum, 'reason' => "Bản ghi phân công bị trùng lặp trong file Excel", 'data' => $row];
                continue;
            }
            $seen[] = $key;

            if (PhanCongHuongDanLop::where('MaGV', $maGV)->where('MaLop', $maLop)->where('MaHocKy', $maHocKy)->exists()) {
                $errors[] = ['row' => $rNum, 'reason' => "Phân công này đã tồn tại trong cơ sở dữ liệu", 'data' => $row];
                continue;
            }

            try {
                PhanCongHuongDanLop::create([
                    'MaGV' => $maGV,
                    'MaLop' => $maLop,
                    'MaHocKy' => $maHocKy,
                    'NgayPhanCong' => !empty($row['NgayPhanCong']) ? $row['NgayPhanCong'] : date('Y-m-d')
                ]);
                $success++;
            } catch (Exception $e) {
                $errors[] = ['row' => $rNum, 'reason' => $e->getMessage(), 'data' => $row];
            }
        }

        AuditLog::log('import_phan_cong', 'PhanCongHuongDanLop', null, ['success' => $success, 'errors' => count($errors)]);
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
}



