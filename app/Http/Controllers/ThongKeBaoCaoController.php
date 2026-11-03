<?php

namespace App\Http\Controllers;

use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\Nganh;
use App\Models\HocKy;
use App\Models\HoiDong;
use App\Models\TaiKhoan;
use App\Models\GiangVien;
use App\Models\AuditLog;
use App\Services\ThongKeService;
use App\Services\ThongKeExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ThongKeBaoCaoController extends Controller
{
    public function __construct(
        protected ThongKeService $thongKeService,
        protected ThongKeExportService $exportService
    ) {}

    /**
     * Xác định vai trò, phạm vi dữ liệu, layout và route prefix
     */
    private function getScope(TaiKhoan $user): array
    {
        $user->loadMissing('vaiTro');
        $roleName = $user->vaiTro->TenVaiTro ?? '';

        if (in_array($roleName, ['Admin', 'Giáo vụ'])) {
            return [
                'role'        => 'admin',
                'roleTitle'   => 'Giáo Vụ / Quản Trị Viên',
                'maKhoa'      => null,
                'layout'      => 'layouts.admin',
                'routePrefix' => 'admin.thongke_baocao',
            ];
        }

        if ($roleName === 'Trưởng khoa') {
            $maKhoa = $this->resolveMaKhoa($user);
            return [
                'role'        => 'truongkhoa',
                'roleTitle'   => 'Trưởng Khoa',
                'maKhoa'      => $maKhoa,
                'layout'      => 'layouts.truongkhoa',
                'routePrefix' => 'truongkhoa.thongke_baocao',
            ];
        }

        abort(403, 'Bạn không có quyền truy cập mục Thống kê & Báo cáo.');
    }

    private function resolveMaKhoa(TaiKhoan $user): ?string
    {
        if (preg_match('/^TK_([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
            $k = Khoa::where('MaKhoa', $m[1])->first();
            if ($k) return $k->MaKhoa;
        }

        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv) {
            $gv->loadMissing('boMon.khoa');
            if ($gv->boMon?->MaKhoa) {
                return $gv->boMon->MaKhoa;
            }
            $byName = Khoa::where('TruongKhoa', 'like', '%' . ($gv->HoTen ?? '') . '%')->first();
            if ($byName) return $byName->MaKhoa;
        }

        return Khoa::first()?->MaKhoa ?? 'CNTT';
    }

    private function sanitizeFilters(array $filters): array
    {
        foreach ($filters as $k => $v) {
            if ($v === '' || $v === 'all' || $v === 'null' || $v === 'undefined') {
                $filters[$k] = null;
            }
        }

        // Chuẩn hóa tên tham số giữa snake_case và PascalCase
        if (empty($filters['MaHocKy']) && !empty($filters['ma_hoc_ky'])) {
            $filters['MaHocKy'] = $filters['ma_hoc_ky'];
        }
        if (empty($filters['NamHoc']) && !empty($filters['nam_hoc'])) {
            $filters['NamHoc'] = $filters['nam_hoc'];
        }
        if (empty($filters['DotBaoVe']) && !empty($filters['dot_bao_ve'])) {
            $filters['DotBaoVe'] = $filters['dot_bao_ve'];
        }
        if (empty($filters['MaKhoa']) && !empty($filters['ma_khoa'])) {
            $filters['MaKhoa'] = $filters['ma_khoa'];
        }
        if (empty($filters['MaBoMon']) && !empty($filters['ma_bo_mon'])) {
            $filters['MaBoMon'] = $filters['ma_bo_mon'];
        }
        if (empty($filters['MaNganh']) && !empty($filters['ma_nganh'])) {
            $filters['MaNganh'] = $filters['ma_nganh'];
        }
        if (empty($filters['MaHocPhan']) && !empty($filters['ma_hoc_phan'])) {
            $filters['MaHocPhan'] = $filters['ma_hoc_phan'];
        }
        if (empty($filters['MaHoiDong']) && !empty($filters['ma_hoi_dong'])) {
            $filters['MaHoiDong'] = $filters['ma_hoi_dong'];
        }
        if (empty($filters['MaGV']) && !empty($filters['ma_gv'])) {
            $filters['MaGV'] = $filters['ma_gv'];
        }

        if (empty($filters['mau_bao_cao'])) {
            $filters['mau_bao_cao'] = 'mau_01';
        }

        if (empty($filters['MaHocKy'])) {
            $activeHk = HocKy::where('TrangThai', 'Đang diễn ra')->first() 
                     ?? HocKy::where('TrangThai', true)->first() 
                     ?? HocKy::orderBy('MaHocKy', 'desc')->first();
            $filters['MaHocKy'] = $activeHk?->MaHocKy;
            if (empty($filters['NamHoc']) && $activeHk) {
                $filters['NamHoc'] = $activeHk->NamHoc;
            }
        }

        return $filters;
    }

    /**
     * Màn hình chính Thống Kê & Báo Cáo Khóa Luận Tốt Nghiệp
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $scope = $this->getScope($user);

        // Chuyển hướng các link cũ ?tab= về URL gốc
        if ($request->has('tab')) {
            return redirect()->route($scope['routePrefix'] . '.index', $request->except('tab'), 301);
        }

        $filters = $this->sanitizeFilters($request->all());
        $danhMuc = $this->thongKeService->getDanhSachDanhMuc($scope);

        // Tổng hợp dữ liệu thống kê, biểu đồ và mẫu báo cáo
        $data = $this->thongKeService->thongKeTongHop($filters, $scope);
        $hasData = ($data['kpi']['tongSVDaPhanCongHD'] > 0 || $data['kpi']['tongSVThamGia'] > 0);

        if ($request->ajax() || $request->has('ajax') || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'status'     => 'success',
                'kpi'        => $data['kpi'],
                'charts'     => $data['charts'],
                'filters'    => $filters,
                'hasData'    => $hasData,
                'mauBaoCao'  => $data['mauBaoCao'] ?? ($filters['mau_bao_cao'] ?? 'mau_01'),
                'html_kpi'   => view('thongke_baocao.partials.kpi_cards', compact('data'))->render(),
                'html_table' => view('thongke_baocao.partials.data_table', compact('data'))->render(),
            ]);
        }

        return view('thongke_baocao.index', compact(
            'scope',
            'danhMuc',
            'data',
            'filters',
            'hasData'
        ));
    }

    /**
     * Trích xuất và chuẩn hoá cấu hình tuỳ chọn báo cáo
     */
    private function parseReportConfig(Request $request): array
    {
        return [
            'kho_giay'           => $request->query('kho_giay', null),
            'show_kpi'           => filter_var($request->query('show_kpi', true), FILTER_VALIDATE_BOOLEAN),
            'show_table'         => filter_var($request->query('show_table', true), FILTER_VALIDATE_BOOLEAN),
            'show_detail'        => filter_var($request->query('show_detail', true), FILTER_VALIDATE_BOOLEAN),
            'show_charts'        => filter_var($request->query('show_charts', true), FILTER_VALIDATE_BOOLEAN),
            'show_council_info'  => filter_var($request->query('show_council_info', true), FILTER_VALIDATE_BOOLEAN),
            'show_score_info'    => filter_var($request->query('show_score_info', true), FILTER_VALIDATE_BOOLEAN),
            'show_result_status' => filter_var($request->query('show_result_status', true), FILTER_VALIDATE_BOOLEAN),
            'show_export_date'   => filter_var($request->query('show_export_date', true), FILTER_VALIDATE_BOOLEAN),
            'show_reporter'      => filter_var($request->query('show_reporter', true), FILTER_VALIDATE_BOOLEAN),
            'show_conclusion'    => filter_var($request->query('show_conclusion', true), FILTER_VALIDATE_BOOLEAN),
            'show_signatures'    => filter_var($request->query('show_signatures', true), FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * Tạo tên file PDF thông minh dựa theo loại báo cáo và bộ lọc
     * Ví dụ: BaoCao_KetQuaKhoaLuan_Dot1_CNTT_20261102.pdf
     */
    private function buildExportFileName(string $mauBaoCao, array $data, array $filters): string
    {
        $prefix = match ($mauBaoCao) {
            'mau_02' => 'BaoCao_KetQuaTheoNganh',
            'mau_03' => 'BaoCao_SinhVien_Dat',
            'mau_04' => 'BaoCao_SinhVien_ChuaDat',
            'mau_05' => 'BaoCao_SinhVien_DangCham',
            'mau_06' => 'BaoCao_TheoHoiDong',
            'mau_07' => 'BaoCao_PhanBoDiem',
            'mau_08' => 'BaoCao_TinhHinhToChuc',
            'mau_09' => 'BaoCao_TheoBoMon',
            'mau_10' => 'BaoCao_TheoHocPhan',
            default  => 'BaoCao_KetQuaKhoaLuan',
        };

        $parts = [$prefix];

        if (!empty($filters['DotBaoVe'])) {
            $parts[] = Str::slug($filters['DotBaoVe'], '_');
        } elseif (!empty($data['hocKy']->TenHocKy)) {
            $parts[] = Str::slug($data['hocKy']->TenHocKy, '_');
        }

        if (!empty($filters['MaNganh'])) {
            $parts[] = $filters['MaNganh'];
        } elseif (!empty($filters['MaKhoa'])) {
            $parts[] = $filters['MaKhoa'];
        }

        $parts[] = date('Ymd');
        $fileName = implode('_', array_filter($parts)) . '.pdf';
        return preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileName);
    }

    /**
     * Xem trước báo cáo trước khi xuất PDF (Live Preview)
     */
    public function previewReport(Request $request)
    {
        $user = Auth::user();
        $scope = $this->getScope($user);
        $filters = $this->sanitizeFilters($request->all());

        $data = $this->thongKeService->thongKeTongHop($filters, $scope);

        $mauBaoCao = $filters['mau_bao_cao'] ?? 'mau_01';
        $config = $this->parseReportConfig($request);

        $html = view('thongke_baocao.partials.preview_content', compact('data', 'mauBaoCao', 'config'))->render();

        return response()->json([
            'status'     => 'success',
            'html'       => $html,
            'soSV'       => count($data['danhSachTatCaSV']),
            'soDeTai'    => count(array_unique(array_column($data['danhSachTatCaSV'], 'maDeTai'))),
            'soHoiDong'  => count($data['danhSachHoiDong']),
            'fileName'   => $this->buildExportFileName($mauBaoCao, $data, $filters),
        ]);
    }

    /**
     * Xuất PDF báo cáo theo biểu mẫu lựa chọn (Mẫu 01 -> Mẫu 10)
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $scope = $this->getScope($user);
        $filters = $this->sanitizeFilters($request->all());

        $data = $this->thongKeService->thongKeTongHop($filters, $scope);

        $mauBaoCao = $filters['mau_bao_cao'] ?? 'mau_01';
        $config = $this->parseReportConfig($request);

        $pdf = $this->exportService->xuatPdfBaoCao($mauBaoCao, $data, $config);

        $now = ThongKeService::getNow();
        $fileName = $this->buildExportFileName($mauBaoCao, $data, $filters);

        AuditLog::log('XUAT_PDF_BAOCAO', 'ThongKe', null, [
            'user'         => $user->TenDangNhap,
            'role'         => $scope['role'],
            'mauBaoCao'    => $mauBaoCao,
            'config'       => $config,
            'filters'      => $filters,
            'fileName'     => $fileName,
            'time'         => $now->toDateTimeString(),
        ]);

        return $pdf->download($fileName);
    }

    /**
     * Đếm nhanh số lượng phục vụ live count
     */
    public function previewCount(Request $request)
    {
        $user = Auth::user();
        $scope = $this->getScope($user);
        $filters = $this->sanitizeFilters($request->all());

        $counts = $this->thongKeService->demSoLuongXuat($filters, $scope);

        return response()->json([
            'status' => 'success',
            'counts' => $counts,
        ]);
    }

    /**
     * Backward-compatible export methods
     */
    public function exportPdfDot(Request $request)
    {
        $request->merge(['mau_bao_cao' => 'mau_01']);
        return $this->exportPdf($request);
    }

    public function exportPdfKhoaNganh(Request $request)
    {
        $request->merge(['mau_bao_cao' => 'mau_02']);
        return $this->exportPdf($request);
    }

    public function exportExcelKhoaNganh(Request $request)
    {
        $user = Auth::user();
        $scope = $this->getScope($user);
        $filters = $this->sanitizeFilters($request->all());
        $data = $this->thongKeService->thongKeTongHop($filters, $scope);
        return redirect()->route($scope['routePrefix'] . '.index')->with('info', 'Chức năng xuất PDF được khuyến nghị sử dụng.');
    }

    public function exportPdfNganhHoiDong(Request $request)
    {
        $request->merge(['mau_bao_cao' => 'mau_06']);
        return $this->exportPdf($request);
    }
}
