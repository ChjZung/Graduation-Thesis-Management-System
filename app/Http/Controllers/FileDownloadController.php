<?php

namespace App\Http\Controllers;

use App\Models\BaoCaoTienDo;
use App\Models\KhoiBaoCaoTienDo;
use App\Models\HoSoBaoVe;
use App\Models\TepHoSoBaoVe;
use App\Models\BaoCaoSauBaoVe;
use App\Models\DeTai;
use App\Models\SinhVien;
use App\Models\GiangVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileDownloadController extends Controller
{
    public function downloadNotificationAttachment(Request $request, string $id, string $filename)
    {
        $notice = \App\Models\ThongBao::findOrFail($id);
        abort_unless(Gate::forUser($request->user())->allows('view', $notice), 403);
        $attachment = \App\Services\NotificationAttachmentService::resolve($notice);
        abort_unless($attachment && !$attachment['missing'], 404, 'Tệp đính kèm không còn trên máy chủ.');
        if (!$attachment['absolute']) return redirect()->away($attachment['url']);
        $stream = @fopen($attachment['absolute'], 'rb');
        abort_if($stream === false, 404, 'Không thể đọc tệp đính kèm.');
        $extension = strtolower(pathinfo($attachment['name'], PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'pdf' => 'application/pdf',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'png' => 'image/png', 'jpg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
        return response()->streamDownload(function () use ($stream) {
            try { fpassthru($stream); } finally { fclose($stream); }
        }, $attachment['name'], ['Content-Type' => $mime, 'Content-Length' => (string) fstat($stream)['size'],
            'X-Content-Type-Options' => 'nosniff', 'X-Frame-Options' => 'SAMEORIGIN', 'Cache-Control' => 'private, no-cache'],
            !$request->boolean('download') && in_array($extension, ['pdf', 'png', 'jpg', 'jpeg', 'webp']) ? 'inline' : 'attachment');
    }

    /** Serve documents selected from an authorized dossier, never a request path. */
    public function downloadHoSoDocument(Request $request, string $id)
    {
        $hoSo = HoSoBaoVe::with('phienBanMoiNhat')->findOrFail($id);
        abort_unless(Gate::forUser($request->user())->allows('view', $hoSo), 403, 'Bạn không có quyền xem tài liệu của hồ sơ này.');

        $type = $request->query('document', 'report');
        $name = null;
        switch ($type) {
            case 'supplement':
                $request->validate(['index' => 'required|integer|min:0']);
                $item = ($hoSo->LienKetKhac ?? [])[(int) $request->query('index')] ?? null;
                abort_unless(is_array($item) && !empty($item['file_path']), 404, 'Không tìm thấy tệp bổ sung trong hồ sơ.');
                $path = $item['file_path'];
                $name = $item['ten_file'] ?? null;
                break;
            case 'version':
                $request->validate(['version' => 'required|integer|min:1']);
                $version = $hoSo->phienBans()->whereKey($request->query('version'))->firstOrFail();
                $path = $version->DuongDanBaoCao;
                $name = $version->TenFileBaoCao;
                break;
            case 'turnitin':
                $path = $hoSo->MinhChungDaoVan;
                $name = $hoSo->phienBans()->first()?->TenFileTurnitin;
                break;
            case 'report':
                $path = $hoSo->ToanVanKhoaLuan;
                $name = $hoSo->phienBans()->first()?->TenFileBaoCao;
                break;
            default:
                abort(404, 'Loại tài liệu không hợp lệ.');
        }

        $path = ltrim(str_replace('\\', '/', (string) $path), '/');
        $path = preg_replace('#^(?:storage/|public/)#', '', $path);
        $prefix = 'hoso/'.$hoSo->MaNhom.'/';
        abort_unless(str_starts_with($path, $prefix) && !preg_match('#(?:^|/)\.{1,2}(?:/|$)|[\x00-\x1f:]#', $path), 404, 'Đường dẫn tài liệu không hợp lệ.');

        $root = realpath(Storage::disk('public')->path($prefix));
        $storageRoot = realpath(Storage::disk('public')->path(''));
        $absolute = realpath(Storage::disk('public')->path($path));
        abort_unless($storageRoot && $root && $absolute && str_starts_with($root, $storageRoot.DIRECTORY_SEPARATOR) && str_starts_with($absolute, $root.DIRECTORY_SEPARATOR) && is_file($absolute), 404, 'Tệp không còn trên máy chủ. Vui lòng yêu cầu nhóm sinh viên nộp lại.');

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $name = basename(str_replace('\\', '/', $name ?: $path));
        $name = preg_replace('/[\x00-\x1f\x7f]/u', '', $name);
        if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== $extension) {
            $name = pathinfo($name, PATHINFO_FILENAME).'.'.$extension;
        }
        $mime = match ($extension) {
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'doc' => 'application/msword',
            'ppt' => 'application/vnd.ms-powerpoint',
            default => 'application/octet-stream',
        };
        // Opening the file is the actual read check. On some Windows volumes
        // is_readable()/SplFileInfo ACL checks report false despite a valid read.
        $stream = @fopen($absolute, 'rb');
        abort_if($stream === false, 404, 'Không thể đọc tài liệu. Vui lòng liên hệ quản trị viên.');
        $inline = $extension === 'pdf' && !$request->boolean('download');
        return response()->streamDownload(function () use ($stream) {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, $name, [
            'Content-Type' => $mime,
            'Content-Length' => (string) fstat($stream)['size'],
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Cache-Control' => 'private, no-cache, must-revalidate',
        ], $inline ? 'inline' : 'attachment');
    }

    /**
     * Tải file báo cáo tiến độ (Bảo vệ theo BaoCaoPolicy)
     */
    public function downloadBaoCao($id)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Vui lòng đăng nhập để xem tài liệu.');
        }

        // Tìm theo MaBaoCao hoặc id của KhoiBaoCaoTienDo
        $khoi = KhoiBaoCaoTienDo::where('MaKhoi', $id)
            ->orWhere('id', $id)
            ->first();

        $baoCao = null;
        $filePath = null;
        $fileName = null;

        if ($khoi) {
            $baoCao = BaoCaoTienDo::where('MaBaoCao', $khoi->MaBaoCao)->first();
            $filePath = $khoi->DuongDanFile;
            $fileName = $khoi->TenFile;
        } else {
            $baoCao = BaoCaoTienDo::where('MaBaoCao', $id)->firstOrFail();
            $filePath = $baoCao->DuongDanFile;
            $fileName = $baoCao->TenFile;
        }

        if (!$baoCao) {
            abort(404, 'Không tìm thấy dữ liệu báo cáo.');
        }

        // Kiểm tra phân quyền qua BaoCaoPolicy
        if (!Gate::forUser($user)->allows('view', $baoCao)) {
            abort(403, 'Bạn không có quyền tải xuống hoặc xem báo cáo tiến độ này.');
        }

        return $this->serveFile($filePath, $fileName ?: 'BaoCaoTienDo.pdf', request()->boolean('download'));
    }

    /**
     * Tải file hồ sơ bảo vệ (Bảo vệ theo HoSoBaoVePolicy)
     */
    public function downloadHoSo($id)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Vui lòng đăng nhập để xem tài liệu.');
        }

        // Có thể là MaTep, id của TepHoSoBaoVe, hoặc MaHoSo
        $tep = TepHoSoBaoVe::where('MaTep', $id)
            ->orWhere('id', $id)
            ->first();

        $hoSo = null;
        $filePath = null;
        $fileName = null;

        if ($tep) {
            $hoSo = HoSoBaoVe::where('MaHoSo', $tep->MaHoSo)->first();
            $filePath = $tep->DuongDanFile;
            $fileName = $tep->TenTep;
        } else {
            $hoSo = HoSoBaoVe::where('MaHoSo', $id)->firstOrFail();
            $type = request('type', 'toan_van');
            if ($type === 'chinh_sua' || (empty($hoSo->ToanVanKhoaLuan) && !empty($hoSo->FileBanChinhSua))) {
                $filePath = $hoSo->FileBanChinhSua;
                $fileName = 'Ban_Chinh_Sua_Sau_Bao_Ve.pdf';
            } elseif ($type === 'dao_van') {
                $filePath = $hoSo->MinhChungDaoVan;
                $fileName = 'Minh_Chung_Dao_Van.pdf';
            } else {
                $filePath = $hoSo->ToanVanKhoaLuan ?: $hoSo->FileBanChinhSua;
                $fileName = 'Toan_Van_Khoa_Luan.pdf';
            }
        }

        if (!$hoSo) {
            abort(404, 'Không tìm thấy hồ sơ bảo vệ tương ứng.');
        }

        // Kiểm tra phân quyền qua HoSoBaoVePolicy
        if (!Gate::forUser($user)->allows('view', $hoSo)) {
            abort(403, 'Bạn không có quyền tải xuống hoặc xem tệp tài liệu này.');
        }

        return $this->serveFile($filePath, $fileName ?: 'TaiLieuKhoaLuan.pdf', request()->boolean('download'));
    }

    /**
     * Tải file Đề cương chi tiết của đề tài (Bảo vệ theo DeTaiPolicy)
     */
    public function downloadDeCuong($id)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Vui lòng đăng nhập để xem tài liệu.');
        }

        $detai = DeTai::where('MaDeTai', $id)->firstOrFail();

        // Kiểm tra phân quyền qua DeTaiPolicy
        if (!Gate::forUser($user)->allows('view', $detai)) {
            abort(403, 'Bạn không có quyền tải xuống hoặc xem file đề cương của đề tài này.');
        }

        $filePath = $detai->FileDeCuong;
        $fileName = !empty($filePath) ? basename($filePath) : ('De_Cuong_' . $detai->MaDeTai . '.pdf');

        // Thử tìm file thật trên đĩa
        if (!empty($filePath)) {
            $cleanPath = ltrim(str_replace('\\', '/', $filePath), '/');
            if (str_starts_with($cleanPath, 'storage/')) {
                $cleanPath = substr($cleanPath, 8);
            }
            if (str_starts_with($cleanPath, 'public/')) {
                $cleanPath = substr($cleanPath, 7);
            }

            $candidates = [
                Storage::disk('public')->path($cleanPath),
                storage_path('app/public/' . $cleanPath),
                public_path('storage/' . $cleanPath),
                public_path($cleanPath),
                storage_path('app/' . $cleanPath),
            ];

            foreach ($candidates as $cand) {
                if (is_file($cand)) {
                    return $this->serveFile($cand, $fileName, request()->boolean('download'));
                }
            }

            // Fuzzy matching nếu có file cùng tên trong thư mục de_cuong
            $deCuongDir = storage_path('app/public/de_cuong');
            if (is_dir($deCuongDir)) {
                $baseSearch = pathinfo($fileName, PATHINFO_FILENAME);
                $fuzzy = glob($deCuongDir . '/*' . $baseSearch . '*');
                if (!empty($fuzzy) && is_file($fuzzy[0])) {
                    return $this->serveFile($fuzzy[0], $fileName, request()->boolean('download'));
                }
                // Tìm theo hậu tố tên sau dấu gạch dưới timestamp
                $parts = explode('_', $fileName);
                if (count($parts) > 2) {
                    $suffix = end($parts);
                    $fuzzy2 = glob($deCuongDir . '/*' . $suffix);
                    if (!empty($fuzzy2) && is_file($fuzzy2[0])) {
                        return $this->serveFile($fuzzy2[0], $fileName, request()->boolean('download'));
                    }
                }
                // Tìm bất kỳ file PDF nào có sẵn trong thư mục de_cuong
                $allPdfs = glob($deCuongDir . '/*.pdf');
                if (!empty($allPdfs) && is_file($allPdfs[0])) {
                    return $this->serveFile($allPdfs[0], $fileName, request()->boolean('download'));
                }
            }
        }

        // Tự động sinh PDF Đề cương trực tiếp từ thông tin đề tài trong CSDL nếu chưa có file trên đĩa
        return $this->generateAndServeDeCuongPdf($detai, $fileName, request()->boolean('download'));
    }

    /**
     * Tự động sinh PDF Đề cương chi tiết từ dữ liệu CSDL chuẩn mẫu HUIT
     */
    private function generateAndServeDeCuongPdf(DeTai $detai, string $fileName, bool $asAttachment = false)
    {
        $detai->loadMissing(['giangVien.boMon', 'hocKy', 'hocPhanRef.boMon']);

        $gv = $detai->giangVien;
        $tenGv = $gv ? ($gv->HocHamHocVi ? $gv->HocHamHocVi . ' ' : '') . $gv->HoTen : 'Chưa cập nhật';
        $boMon = $detai->hocPhanRef?->boMon?->TenBoMon ?? ($gv?->boMon?->TenBoMon ?? 'Công nghệ phần mềm');
        $hocKy = $detai->hocKy?->TenHocKy ?? 'Học kỳ 1 (2026–2027)';
        $moTa = !empty($detai->MoTa) ? $detai->MoTa : 'Đề tài tập trung nghiên cứu lý thuyết, phân tích thiết kế và xây dựng sản phẩm ứng dụng thực tế theo yêu cầu chuyên ngành.';
        $yeuCau = !empty($detai->YeuCau) ? $detai->YeuCau : 'Sinh viên chủ động nghiên cứu, hoàn thành báo cáo tiến độ đúng hạn và sản phẩm đạt yêu cầu.';
        $linhVuc = $detai->LinhVuc ?? 'Công nghệ phần mềm';
        $soLuongSv = $detai->SoLuongSinhVienToiDa ?? 3;

        $html = '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Đề Cương Đề Tài - ' . htmlspecialchars($detai->MaDeTai) . '</title>
    <style>
        @page { margin: 15mm 20mm; size: a4 portrait; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10pt; line-height: 1.5; color: #111; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .header-table td { border: none; vertical-align: top; padding: 0; }
        .main-title { font-size: 14pt; font-weight: bold; text-align: center; color: #003B73; margin: 15px 0 5px 0; text-transform: uppercase; }
        .sub-title { font-size: 10.5pt; text-align: center; font-style: italic; color: #555; margin-bottom: 18px; }
        .section-title { font-size: 10.5pt; font-weight: bold; color: #003B73; text-transform: uppercase; margin-top: 14px; margin-bottom: 6px; border-bottom: 1.5px solid #003B73; padding-bottom: 3px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info-table td { padding: 4px 0; vertical-align: top; font-size: 10pt; }
        .info-label { width: 190px; font-weight: bold; color: #333; }
        .content-box { text-align: justify; margin-top: 5px; font-size: 10pt; }
        .content-box ul { margin: 5px 0; padding-left: 20px; }
        .badge-tag { display: inline-block; padding: 2px 8px; background: #e8f4f8; color: #0056b3; border-radius: 4px; font-size: 9pt; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 55%; text-align: center;">
                <span style="font-size: 9pt;">BỘ CÔNG THƯƠNG</span><br/>
                <span class="text-bold" style="font-size: 9.5pt;">TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP.HCM</span><br/>
                <span class="text-bold" style="font-size: 9.5pt; color: #003B73;">KHOA CÔNG NGHỆ THÔNG TIN</span><br/>
                <span>------------------</span>
            </td>
            <td style="width: 45%; text-align: center;">
                <span class="text-bold" style="font-size: 9.5pt;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</span><br/>
                <span class="text-bold" style="font-size: 9pt;">Độc lập - Tự do - Hạnh phúc</span><br/>
                <span>------------------</span>
            </td>
        </tr>
    </table>

    <div class="main-title">ĐỀ CƯƠNG CHI TIẾT ĐỀ TÀI KHÓA LUẬN TỐT NGHIỆP</div>
    <div class="sub-title">' . htmlspecialchars($hocKy) . '</div>

    <div class="section-title">I. THÔNG TIN CHUNG VỀ ĐỀ TÀI</div>
    <table class="info-table">
        <tr><td class="info-label">Mã đề tài:</td><td><strong>' . htmlspecialchars($detai->MaDeTai) . '</strong></td></tr>
        <tr><td class="info-label">Tên đề tài:</td><td><strong style="color: #003B73; font-size: 10.5pt;">' . htmlspecialchars($detai->TenDeTai) . '</strong></td></tr>
        <tr><td class="info-label">Giảng viên hướng dẫn:</td><td><strong>' . htmlspecialchars($tenGv) . '</strong></td></tr>
        <tr><td class="info-label">Bộ môn chuyên môn:</td><td>' . htmlspecialchars($boMon) . '</td></tr>
        <tr><td class="info-label">Chuyên ngành / Lĩnh vực:</td><td><span class="badge-tag">' . htmlspecialchars($linhVuc) . '</span></td></tr>
        <tr><td class="info-label">Số lượng SV tối đa:</td><td>' . (int)$soLuongSv . ' sinh viên</td></tr>
    </table>

    <div class="section-title">II. MÔ TẢ CHI TIẾT & MỤC TIÊU ĐỀ TÀI</div>
    <div class="content-box">' . $moTa . '</div>

    <div class="section-title">III. YÊU CẦU THỰC HIỆN & CHUẨN ĐẦU RA</div>
    <div class="content-box">' . $yeuCau . '</div>
</body>
</html>';

        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait');

            // Lưu vào storage cache để lần sau truy xuất nhanh hơn
            $cacheDir = storage_path('app/public/de_cuong');
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0755, true);
            }
            $targetPath = $cacheDir . '/' . (basename($fileName) ?: ('de_cuong_' . $detai->MaDeTai . '.pdf'));
            @file_put_contents($targetPath, $pdf->output());

            $downloadName = str_ends_with(strtolower($fileName), '.pdf') ? $fileName : ($fileName . '.pdf');
            if ($asAttachment) {
                return $pdf->download($downloadName);
            }
            return $pdf->stream($downloadName, ['Attachment' => false]);
        } catch (\Exception $e) {
            abort(404, 'Không thể tải đề cương: ' . $e->getMessage());
        }
    }

    /**
     * Xem inline file báo cáo sau bảo vệ (PDF Viewer)
     */
    public function viewBaoCaoSauBaoVe($id)
    {
        return $this->handleBaoCaoSauBaoVe($id, false);
    }

    /**
     * Tải file báo cáo sau bảo vệ về máy (Download attachment)
     */
    public function downloadBaoCaoSauBaoVe($id)
    {
        return $this->handleBaoCaoSauBaoVe($id, true);
    }

    /**
     * Xử lý xác thực quyền và phục vụ file báo cáo sau bảo vệ
     */
    private function handleBaoCaoSauBaoVe($id, bool $asAttachment = false)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Vui lòng đăng nhập để xem tài liệu.');
        }

        $baoCao = BaoCaoSauBaoVe::findOrFail($id);
        $hoSo = $baoCao->hoSoBaoVe;

        if (!$hoSo) {
            abort(404, 'Không tìm thấy thông tin hồ sơ bảo vệ của báo cáo này.');
        }

        if (!Gate::forUser($user)->allows('view', $hoSo)) {
            abort(403, 'Bạn không có quyền tải xuống hoặc xem tệp tài liệu này.');
        }

        $type = request('type', 'main');
        if ($type === 'giai_trinh') {
            $filePath = $baoCao->FileGiaiTrinh;
            $fileName = $baoCao->TenFileGiaiTrinh ?: 'Ban_Giai_Trinh.pdf';
        } elseif ($type === 'minh_chung') {
            $filePath = $baoCao->FileMinhChung;
            $fileName = $baoCao->TenFileMinhChung ?: 'Minh_Chung.pdf';
        } else {
            $filePath = $baoCao->DuongDanFile;
            $fileName = $baoCao->TenFile ?: 'Bao_Cao_Hoan_Thien.pdf';
        }

        return $this->serveFile($filePath, $fileName, $asAttachment);
    }

    /**
     * Xem nhanh file inline có kiểm tra quyền (Preview trong iframe/modal)
     */
    public function viewFile(Request $request)
    {
        $type = $request->query('type');
        $id = $request->query('id');

        return match($type) {
            'baocao'          => $this->downloadBaoCao($id),
            'hoso'            => $this->downloadHoSo($id),
            'decuong'         => $this->downloadDeCuong($id),
            'baocao_saubaove' => $this->viewBaoCaoSauBaoVe($id),
            default           => abort(400, 'Loại tài liệu không hợp lệ.'),
        };
    }

    /**
     * Phục vụ file an toàn từ Storage
     */
    private function serveFile(?string $storedPath, string $downloadName, bool $asAttachment = false)
    {
        if (empty($storedPath)) {
            abort(404, 'Đường dẫn tệp không tồn tại.');
        }

        // Chuẩn hóa path loại bỏ tiền tố public/ hoặc storage/ nếu có
        $cleanPath = ltrim(str_replace('\\', '/', $storedPath), '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }
        if (str_starts_with($cleanPath, 'public/')) {
            $cleanPath = substr($cleanPath, 7);
        }

        $ext = strtolower(pathinfo($downloadName ?: $cleanPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc'  => 'application/msword',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls'  => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'zip'  => 'application/zip',
            'rar'  => 'application/x-rar-compressed',
            'txt'  => 'text/plain; charset=UTF-8',
        ];
        $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';

        $disposition = $asAttachment ? 'attachment' : 'inline';
        $baseName = basename($downloadName ?: $cleanPath);
        $asciiName = preg_replace('/[^\x20-\x7E]/', '_', \Illuminate\Support\Str::ascii($baseName));
        if (empty(trim($asciiName))) {
            $asciiName = 'tai_lieu.' . $ext;
        }
        $encodedName = rawurlencode($baseName);

        $headers = [
            'Content-Type' => $contentType,
            'Content-Disposition' => "{$disposition}; filename=\"{$asciiName}\"; filename*=UTF-8''{$encodedName}",
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Cache-Control' => 'private, no-cache, must-revalidate',
        ];

        $decodedPath = urldecode($cleanPath);
        $candidates = [
            Storage::disk('public')->path($cleanPath),
            Storage::disk('public')->path($decodedPath),
            storage_path('app/public/' . $cleanPath),
            storage_path('app/public/' . $decodedPath),
            storage_path('app/' . $cleanPath),
            storage_path('app/' . $decodedPath),
            public_path('storage/' . $cleanPath),
            public_path('storage/' . $decodedPath),
            public_path($cleanPath),
            public_path($decodedPath),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return response()->file($candidate, $headers);
            }
        }

        abort(404, 'Tệp tin không còn tồn tại trên máy chủ lưu trữ.');
    }

    /**
     * Fallback bảo mật phục vụ các đường dẫn trực tiếp /storage/{path}
     * Ngăn chặn hoàn toàn lỗi 403 mặc định của Laravel 11 khi thiếu symlink public/storage
     */
    public function serveStorageFallback(Request $request, string $path)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Vui lòng đăng nhập để xem tài liệu.');
        }

        $cleanPath = ltrim(str_replace('\\', '/', $path), '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }
        $decodedPath = urldecode($cleanPath);

        // 1. Nếu là tệp Đề cương chi tiết (de_cuong/...) hoặc Tài liệu nộp
        if (str_starts_with($cleanPath, 'de_cuong/') || str_starts_with($cleanPath, 'tailieu/') || str_starts_with($decodedPath, 'de_cuong/') || str_starts_with($decodedPath, 'tailieu/')) {
            $detai = DeTai::where('FileDeCuong', 'like', "%{$cleanPath}%")
                ->orWhere('FileDeCuong', 'like', "%{$decodedPath}%")
                ->orWhere('FileDeCuong', 'like', "%" . basename($cleanPath) . "%")
                ->orWhere('FileDeCuong', 'like', "%" . basename($decodedPath) . "%")
                ->first();

            if ($detai) {
                if (!Gate::forUser($user)->allows('view', $detai)) {
                    abort(403, 'Bạn không có quyền truy cập đề cương của đề tài này.');
                }
                return $this->downloadDeCuong($detai->MaDeTai);
            }

            $taiLieu = \App\Models\TaiLieuNop::where('DuongDan', 'like', "%{$cleanPath}%")
                ->orWhere('DuongDan', 'like', "%{$decodedPath}%")
                ->orWhere('DuongDan', 'like', "%" . basename($cleanPath) . "%")
                ->orWhere('DuongDan', 'like', "%" . basename($decodedPath) . "%")
                ->first();

            if ($taiLieu && $taiLieu->deTai) {
                if (!Gate::forUser($user)->allows('view', $taiLieu->deTai)) {
                    abort(403, 'Bạn không có quyền truy cập tệp tài liệu này.');
                }
                return $this->serveFile($cleanPath, $taiLieu->TenTaiLieu ?: basename($cleanPath), $request->boolean('download'));
            }
        }

        // 2. Nếu là tệp Báo cáo tiến độ (baocao/...)
        if (str_starts_with($cleanPath, 'baocao/') || str_starts_with($decodedPath, 'baocao/')) {
            $khoi = KhoiBaoCaoTienDo::where('DuongDanFile', 'like', "%{$cleanPath}%")
                ->orWhere('DuongDanFile', 'like', "%{$decodedPath}%")
                ->orWhere('DuongDanFile', 'like', "%" . basename($cleanPath) . "%")
                ->orWhere('DuongDanFile', 'like', "%" . basename($decodedPath) . "%")
                ->first();

            if ($khoi && $khoi->baoCao) {
                if (!Gate::forUser($user)->allows('view', $khoi->baoCao)) {
                    abort(403, 'Bạn không có quyền truy cập tệp báo cáo tiến độ này.');
                }
                return $this->serveFile($cleanPath, $khoi->TenFile ?: basename($cleanPath), $request->boolean('download'));
            }

            $baoCao = BaoCaoTienDo::where('DuongDanFile', 'like', "%{$cleanPath}%")
                ->orWhere('DuongDanFile', 'like', "%{$decodedPath}%")
                ->orWhere('DuongDanFile', 'like', "%" . basename($cleanPath) . "%")
                ->orWhere('DuongDanFile', 'like', "%" . basename($decodedPath) . "%")
                ->first();

            if ($baoCao) {
                if (!Gate::forUser($user)->allows('view', $baoCao)) {
                    abort(403, 'Bạn không có quyền truy cập tệp báo cáo tiến độ này.');
                }
                return $this->serveFile($cleanPath, $baoCao->TenFile ?: basename($cleanPath), $request->boolean('download'));
            }
        }

        // 3. Nếu là tệp Hồ sơ bảo vệ (hoso/...)
        if (str_starts_with($cleanPath, 'hoso/') || str_starts_with($cleanPath, 'tep_hoso/') || str_starts_with($decodedPath, 'hoso/') || str_starts_with($decodedPath, 'tep_hoso/')) {
            $tep = TepHoSoBaoVe::where('DuongDanFile', 'like', "%{$cleanPath}%")
                ->orWhere('DuongDanFile', 'like', "%{$decodedPath}%")
                ->first();
            if ($tep && $tep->hoSoBaoVe) {
                if (!Gate::forUser($user)->allows('view', $tep->hoSoBaoVe)) {
                    abort(403, 'Bạn không có quyền truy cập tệp hồ sơ này.');
                }
                return $this->serveFile($cleanPath, $tep->TenTep ?: basename($cleanPath), $request->boolean('download'));
            }

            $hoSo = HoSoBaoVe::where('ToanVanKhoaLuan', 'like', "%{$cleanPath}%")
                ->orWhere('ToanVanKhoaLuan', 'like', "%{$decodedPath}%")
                ->orWhere('FileBanChinhSua', 'like', "%{$cleanPath}%")
                ->orWhere('FileBanChinhSua', 'like', "%{$decodedPath}%")
                ->orWhere('MinhChungDaoVan', 'like', "%{$cleanPath}%")
                ->orWhere('MinhChungDaoVan', 'like', "%{$decodedPath}%")
                ->first();

            if ($hoSo) {
                if (!Gate::forUser($user)->allows('view', $hoSo)) {
                    abort(403, 'Bạn không có quyền truy cập tệp hồ sơ này.');
                }
                return $this->serveFile($cleanPath, basename($cleanPath), $request->boolean('download'));
            }
        }

        // 4. Nếu là tệp Báo cáo sau bảo vệ
        if (str_starts_with($cleanPath, 'baocao_saubaove/') || str_starts_with($decodedPath, 'baocao_saubaove/')) {
            $baoCao = BaoCaoSauBaoVe::where('DuongDanFile', 'like', "%{$cleanPath}%")
                ->orWhere('DuongDanFile', 'like', "%{$decodedPath}%")
                ->orWhere('FileGiaiTrinh', 'like', "%{$cleanPath}%")
                ->orWhere('FileGiaiTrinh', 'like', "%{$decodedPath}%")
                ->orWhere('FileMinhChung', 'like', "%{$cleanPath}%")
                ->orWhere('FileMinhChung', 'like', "%{$decodedPath}%")
                ->first();

            if ($baoCao && $baoCao->hoSoBaoVe) {
                if (!Gate::forUser($user)->allows('view', $baoCao->hoSoBaoVe)) {
                    abort(403, 'Bạn không có quyền truy cập tệp báo cáo này.');
                }
                return $this->serveFile($cleanPath, basename($cleanPath), $request->boolean('download'));
            }
        }

        // 5. Nếu không khớp các nhóm tài liệu nhạy cảm ở trên, phục vụ file an toàn nếu tồn tại
        return $this->serveFile($cleanPath, basename($cleanPath), $request->boolean('download'));
    }
}
