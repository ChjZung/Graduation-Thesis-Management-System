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
        if (empty($filePath)) {
            abort(404, 'Đề tài này hiện chưa có file đề cương đính kèm.');
        }

        $fileName = basename($filePath) ?: ('De_Cuong_' . $detai->MaDeTai . '.' . pathinfo($filePath, PATHINFO_EXTENSION));
        return $this->serveFile($filePath, $fileName, request()->boolean('download'));
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
                return $this->serveFile($cleanPath, basename($cleanPath), $request->boolean('download'));
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
