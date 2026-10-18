<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HoiDong;
use App\Models\HocKy;
use App\Services\HoiDongPdfService;
use Illuminate\Http\Request;

class HoiDongController extends Controller
{
    /**
     * Danh sách Hội đồng bảo vệ khóa luận (Giáo vụ: Theo dõi, Tra cứu & Xuất báo cáo)
     */
    public function index(Request $request)
    {
        $query = HoiDong::with([
            'thanhViens.giangVien.boMon',
            'hoSoBaoVes.nhom.truongNhom',
            'hoSoBaoVes.nhom.deTai.giangVien',
            'hocKy',
            'nganh',
        ])->orderBy('ThoiGianBatDau', 'desc');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('TenHoiDong', 'like', "%{$s}%")
                  ->orWhere('MaHoiDong', 'like', "%{$s}%")
                  ->orWhere('DiaDiem', 'like', "%{$s}%")
                  ->orWhere('SoQuyetDinh', 'like', "%{$s}%");
            });
        }

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('DotBaoVe')) {
            $query->where('DotBaoVe', 'like', "%{$request->DotBaoVe}%");
        }

        if ($request->filled('NgayBaoVe')) {
            $query->whereDate('NgayBaoVe', $request->NgayBaoVe);
        }

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai);
        }

        if ($request->filled('TrangThaiCongBo')) {
            $query->where('TrangThaiCongBo', $request->TrangThaiCongBo);
        }

        $hoiDongs = $query->paginate(12)->withQueryString();
        $hocKys = HocKy::orderBy('created_at', 'desc')->get();

        return view('admin.hoidong.index', compact('hoiDongs', 'hocKys'));
    }

    /**
     * Chi tiết Hội đồng (Giáo vụ: Xem chi tiết thành viên, đề tài, sinh viên và văn bản công bố)
     */
    public function show($id)
    {
        $hoiDong = HoiDong::with([
            'thanhViens.giangVien.boMon',
            'hoSoBaoVes.nhom.truongNhom',
            'hoSoBaoVes.nhom.thanhVienNhoms.sinhVien.lop',
            'hoSoBaoVes.nhom.deTai.giangVien.boMon',
            'hoSoBaoVes.phanCongPhanBien.giangVien.boMon',
            'hoSoBaoVes.tepHoSoBaoVes',
            'hocKy',
            'nganh',
            'khoa',
        ])->findOrFail($id);

        return view('admin.hoidong.show', compact('hoiDong'));
    }

    /**
     * Xem trước file PDF văn bản công bố Hội đồng
     */
    public function previewPdf($id)
    {
        $hoiDong = HoiDong::findOrFail($id);
        $pdf = HoiDongPdfService::generatePdf($hoiDong);
        return $pdf->stream("Quyet_Dinh_Hoi_Dong_{$hoiDong->MaHoiDong}.pdf");
    }

    /**
     * Tải xuống file PDF văn bản công bố Hội đồng
     */
    public function downloadPdf($id)
    {
        $hoiDong = HoiDong::findOrFail($id);
        $pdf = HoiDongPdfService::generatePdf($hoiDong);
        return $pdf->download("Quyet_Dinh_Hoi_Dong_{$hoiDong->MaHoiDong}.pdf");
    }

    /**
     * Xuất báo cáo tổng hợp Danh sách Hội đồng bảo vệ khóa luận (Excel / CSV)
     */
    public function exportReport(Request $request)
    {
        $query = HoiDong::with([
            'thanhViens.giangVien.boMon',
            'hoSoBaoVes.nhom.thanhVienNhoms.sinhVien',
            'hoSoBaoVes.nhom.deTai.giangVien',
            'hocKy',
            'nganh',
        ])->orderBy('ThoiGianBatDau', 'desc');

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }
        if ($request->filled('DotBaoVe')) {
            $query->where('DotBaoVe', 'like', "%{$request->DotBaoVe}%");
        }
        if ($request->filled('TrangThaiCongBo')) {
            $query->where('TrangThaiCongBo', $request->TrangThaiCongBo);
        }

        $hoiDongs = $query->get();

        $filename = "BAO_CAO_HOI_DONG_BAO_VE_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($hoiDongs) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($file, [
                'STT',
                'Mã Hội Đồng',
                'Tên Hội Đồng',
                'Số Quyết Định',
                'Học Kỳ',
                'Đợt Bảo Vệ',
                'Ngày Bảo Vệ',
                'Phòng Bảo Vệ',
                'Chủ Tịch HĐ',
                'Thư Ký HĐ',
                'Phản Biện HĐ',
                'Số Đề Tài',
                'Số Sinh Viên',
                'Trạng Thái Công Bố',
                'Trạng Thái Diễn Ra'
            ]);

            $stt = 1;
            foreach ($hoiDongs as $hd) {
                $chuTich = $hd->thanhViens->first(fn($tv) => str_contains($tv->VaiTro, 'Chủ tịch'))?->giangVien?->HoTen ?? 'Chưa gán';
                $thuKy = $hd->thanhViens->first(fn($tv) => str_contains($tv->VaiTro, 'Thư ký'))?->giangVien?->HoTen ?? 'Chưa gán';
                $phanBien = $hd->thanhViens->first(fn($tv) => str_contains($tv->VaiTro, 'Phản biện'))?->giangVien?->HoTen ?? 'Chưa gán';

                $soDeTai = $hd->hoSoBaoVes->count();
                $soSinhVien = $hd->hoSoBaoVes->sum(fn($hs) => $hs->nhom?->thanhVienNhoms?->count() ?? 0);

                fputcsv($file, [
                    $stt++,
                    $hd->MaHoiDong,
                    $hd->TenHoiDong,
                    $hd->SoQuyetDinh ?? 'Chưa có',
                    $hd->hocKy?->TenHocKy ?? $hd->MaHocKy,
                    $hd->DotBaoVe ?? 'Chính quy',
                    $hd->NgayBaoVe ? \Carbon\Carbon::parse($hd->NgayBaoVe)->format('d/m/Y') : '',
                    $hd->DiaDiem ?? '',
                    $chuTich,
                    $thuKy,
                    $phanBien,
                    $soDeTai,
                    $soSinhVien,
                    $hd->TrangThaiCongBo ?? 'Chưa công bố',
                    $hd->TrangThai ?? 'Chưa diễn ra',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==============================================================
    // CHẶN MỌI THAO TÁC GHI / SỬA / CÔNG BỐ TỪ GIÁO VỤ Ở PHÍA MÁY CHỦ
    // ==============================================================

    public function create()
    {
        abort(403, 'Tài khoản Giáo vụ chỉ có quyền theo dõi và tra cứu danh sách hội đồng. Việc lập hội đồng bảo vệ thuộc thẩm quyền của Trưởng khoa.');
    }

    public function store(Request $request)
    {
        abort(403, 'Tài khoản Giáo vụ không có quyền thành lập hội đồng. Nghiệp vụ này do Trưởng khoa thực hiện.');
    }

    public function updateTrangThai(Request $request, $id)
    {
        abort(403, 'Tài khoản Giáo vụ không có quyền thay đổi trạng thái hội đồng bảo vệ.');
    }

    public function phanCongNhom(Request $request, $id)
    {
        abort(403, 'Tài khoản Giáo vụ không có quyền phân công đề tài vào hội đồng. Nghiệp vụ này do Trưởng khoa thực hiện.');
    }

    public function huyPhanCongNhom($id, $maHoSo)
    {
        abort(403, 'Tài khoản Giáo vụ không có quyền loại bỏ đề tài khỏi hội đồng.');
    }

    public function themThanhVien(Request $request, $id)
    {
        abort(403, 'Tài khoản Giáo vụ không có quyền phân công giảng viên vào hội đồng.');
    }

    public function doiVaiTroThanhVien(Request $request, $id, $maGV)
    {
        abort(403, 'Tài khoản Giáo vụ không có quyền thay đổi vai trò thành viên hội đồng.');
    }

    public function xoaThanhVien($id, $maGV)
    {
        abort(403, 'Tài khoản Giáo vụ không có quyền xóa thành viên hội đồng.');
    }
}
