<?php

namespace App\Http\Controllers;

use App\Models\BaoCaoTienDo;
use App\Models\KhoiBaoCaoTienDo;
use App\Models\HoSoBaoVe;
use App\Models\TepHoSoBaoVe;
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

        return $this->serveFile($filePath, $fileName ?: 'BaoCaoTienDo.pdf');
    }

    /**
     * Tải file hồ sơ bảo vệ (Bảo vệ theo HoSoBaoVePolicy)
     */
    public function downloadHoSo($id)
    {
        $user = Auth::user();

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

        return $this->serveFile($filePath, $fileName ?: 'TaiLieuKhoaLuan.pdf');
    }

    /**
     * Tải file Đề cương chi tiết của đề tài (Bảo vệ theo DeTaiPolicy)
     */
    public function downloadDeCuong($id)
    {
        $user = Auth::user();
        $detai = DeTai::where('MaDeTai', $id)->firstOrFail();

        // Kiểm tra phân quyền qua DeTaiPolicy
        if (!Gate::forUser($user)->allows('view', $detai)) {
            abort(403, 'Bạn không có quyền tải xuống file đề cương của đề tài này.');
        }

        $filePath = $detai->FileDeCuong;
        if (empty($filePath)) {
            abort(404, 'Đề tài này hiện chưa có file đề cương đính kèm.');
        }

        $fileName = 'De_Cuong_' . $detai->MaDeTai . '.' . pathinfo($filePath, PATHINFO_EXTENSION);
        return $this->serveFile($filePath, $fileName);
    }

    /**
     * Xem nhanh file inline có kiểm tra quyền (Preview trong iframe/modal)
     */
    public function viewFile(Request $request)
    {
        $type = $request->query('type');
        $id = $request->query('id');

        return match($type) {
            'baocao'  => $this->downloadBaoCao($id),
            'hoso'    => $this->downloadHoSo($id),
            'decuong' => $this->downloadDeCuong($id),
            default   => abort(400, 'Loại tài liệu không hợp lệ.'),
        };
    }

    /**
     * Phục vụ file an toàn từ Storage
     */
    private function serveFile(?string $storedPath, string $downloadName)
    {
        if (empty($storedPath)) {
            abort(404, 'Đường dẫn tệp không tồn tại.');
        }

        // Chuẩn hóa path loại bỏ tiền tố public/ hoặc storage/ nếu có
        $cleanPath = ltrim($storedPath, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($cleanPath)) {
            // Thử kiểm tra đường dẫn trực tiếp trong storage/app
            $directPath = storage_path('app/' . $cleanPath);
            if (file_exists($directPath)) {
                return response()->file($directPath, [
                    'Content-Disposition' => 'inline; filename="' . basename($downloadName) . '"',
                ]);
            }

            // Thử đường dẫn trong public/
            $publicPath = public_path($cleanPath);
            if (file_exists($publicPath)) {
                return response()->file($publicPath, [
                    'Content-Disposition' => 'inline; filename="' . basename($downloadName) . '"',
                ]);
            }

            abort(404, 'Tệp tin không còn tồn tại trên máy chủ lưu trữ.');
        }

        $fullPath = $disk->path($cleanPath);
        return response()->file($fullPath, [
            'Content-Disposition' => 'inline; filename="' . basename($downloadName) . '"',
        ]);
    }
}
