<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HoiDong;
use App\Models\HocKy;
use App\Services\HoiDongPdfService;
use Illuminate\Http\Request;

class HoiDongController extends Controller
{
    private function filteredCouncils(Request $request)
    {
        $query = HoiDong::with([
            'thanhViens.giangVien.boMon',
            'hoSoBaoVes.nhom.truongNhom',
            'hoSoBaoVes.nhom.thanhVienNhoms.sinhVien',
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

        return $query;
    }

    /**
     * Danh sách Hội đồng bảo vệ khóa luận (Giáo vụ: Theo dõi, Tra cứu & Xuất báo cáo)
     */
    public function index(Request $request)
    {
        $query = $this->filteredCouncils($request);
        $hoiDongs = $query->paginate(5)->withQueryString();
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
     * Xuất báo cáo tổng hợp Danh sách Hội đồng bảo vệ khóa luận (Excel .xlsx)
     */
    public function exportReport(Request $request)
    {
        $hoiDongs = $this->filteredCouncils($request)->get();
        $semester = $request->filled('MaHocKy') ? (HocKy::find($request->MaHocKy)?->TenDayDu ?? $request->MaHocKy) : 'Tất cả học kỳ';
        $round = $request->filled('DotBaoVe') ? $request->DotBaoVe : 'Tất cả đợt bảo vệ';
        $filename = 'Bao_cao_hoi_dong_'.\Illuminate\Support\Str::slug(($request->MaHocKy ?: 'tat_ca_hoc_ky').' '.$round, '_').'_'.now()->format('Ymd_His').'.xlsx';
        $filters = collect($request->only(['search', 'NgayBaoVe', 'TrangThai', 'TrangThaiCongBo']))->filter(fn ($value) => filled($value))->map(fn ($value, $key) => $key.': '.$value)->join('; ');
        $book = app(\App\Services\CouncilListExcelService::class)->build($hoiDongs, $semester, $round, $filters);
        return response()->streamDownload(function () use ($book) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Cache-Control' => 'private, no-store']);
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
