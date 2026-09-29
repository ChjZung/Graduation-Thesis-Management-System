<?php

namespace App\Http\Controllers;

use App\Models\DeTai;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\HocKy;
use App\Models\GiangVien;
use App\Models\PhieuDangKy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ThongKeDeTaiController extends Controller
{
    /**
     * Xác định phạm vi (Scope) dữ liệu theo vai trò người dùng đăng nhập.
     * Chặn cứng các hành vi cố tình inject/bypass URL.
     */
    private function resolveUserScope(Request $request): array
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Vui lòng đăng nhập để tiếp tục.');
        }

        $vaiTro = $user->vaiTro?->TenVaiTro ?? '';
        $maVaiTro = $user->MaVaiTro ?? '';

        // Sinh viên không có quyền truy cập
        if ($maVaiTro === 'VT03' || str_contains(mb_strtolower($vaiTro), 'sinh viên')) {
            abort(403, 'Bạn không có quyền truy cập trang thống kê đề tài khóa luận.');
        }

        $scope = [
            'role_code'   => $maVaiTro,
            'role_name'   => $vaiTro,
            'is_admin'    => false,
            'is_tk'       => false,
            'is_tbm'      => false,
            'is_gv'       => false,
            'locked_khoa' => false,
            'locked_bm'   => false,
            'force_khoa'  => null,
            'force_bm'    => null,
            'force_gv'    => null,
        ];

        // 1. Giáo vụ (VT01)
        if ($maVaiTro === 'VT01' || str_contains(mb_strtolower($vaiTro), 'giáo vụ')) {
            $scope['is_admin'] = true;
            return $scope;
        }

        // Tìm hồ sơ Giảng viên liên kết với tài khoản
        $gv = GiangVien::with('boMon.khoa')->where('MaTK', $user->MaTK)->first();
        if (!$gv) {
            // Thử tìm theo MaGV = TenDangNhap
            $gv = GiangVien::with('boMon.khoa')->where('MaGV', $user->TenDangNhap)->first();
        }

        // 2. Trưởng khoa (VT05)
        if ($maVaiTro === 'VT05' || str_contains(mb_strtolower($vaiTro), 'trưởng khoa')) {
            $scope['is_tk'] = true;
            $scope['locked_khoa'] = true;

            // Xác định Mã Khoa của Trưởng khoa
            $maKhoa = $gv?->boMon?->MaKhoa;
            if (!$maKhoa) {
                // Thử tìm trong bảng Khoa có TruongKhoa trùng họ tên hoặc MaKhoa từ TenDangNhap
                if (preg_match('/^TK_([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
                    $maKhoa = $m[1];
                } else {
                    $maKhoa = Khoa::where('TruongKhoa', $gv?->HoTen)->value('MaKhoa') ?? 'CNTT';
                }
            }
            $scope['force_khoa'] = $maKhoa;
            return $scope;
        }

        // 3. Trưởng bộ môn (VT04)
        if ($maVaiTro === 'VT04' || str_contains(mb_strtolower($vaiTro), 'trưởng bộ môn')) {
            $scope['is_tbm'] = true;
            $scope['locked_khoa'] = true;
            $scope['locked_bm'] = true;

            $maBM = $gv?->MaBoMon;
            if (!$maBM && preg_match('/^TBM_([A-Z0-9]+)_([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
                $maBM = $m[2];
            }
            if (!$maBM) {
                $maBM = BoMon::where('TruongBoMon', $gv?->HoTen)->value('MaBoMon') ?? 'CNPM';
            }
            $bm = BoMon::where('MaBoMon', $maBM)->first();
            $scope['force_bm'] = $maBM;
            $scope['force_khoa'] = $bm?->MaKhoa ?? 'CNTT';
            return $scope;
        }

        // 4. Giảng viên thông thường (VT02)
        if ($maVaiTro === 'VT02' || str_contains(mb_strtolower($vaiTro), 'giảng viên')) {
            $scope['is_gv'] = true;
            $scope['locked_khoa'] = true;
            $scope['locked_bm'] = true;
            $scope['force_gv'] = $gv?->MaGV ?? $user->TenDangNhap;
            $scope['force_bm'] = $gv?->MaBoMon;
            $scope['force_khoa'] = $gv?->boMon?->MaKhoa;
            return $scope;
        }

        abort(403, 'Vai trò của bạn không được phép xem trang thống kê này.');
    }

    /**
     * Build Query lọc đề tài dựa trên scope và request input.
     */
    private function buildScopedQuery(Request $request, array $scope)
    {
        $query = DeTai::with([
            'giangVien.boMon.khoa',
            'hocKy',
            'phieuDangKys.nhom.thanhVienNhoms'
        ]);

        // Áp dụng ép buộc phạm vi (Scope enforcement)
        if ($scope['force_gv']) {
            $query->where('MaGV', $scope['force_gv']);
        } elseif ($scope['force_bm']) {
            $query->whereHas('giangVien', fn($q) => $q->where('MaBoMon', $scope['force_bm']));
        } elseif ($scope['force_khoa']) {
            $query->whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $scope['force_khoa']));
        }

        // Bộ lọc Học kỳ (mặc định lấy HK đang diễn ra hoặc mới nhất nếu chưa chọn)
        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        // Bộ lọc Khoa (chỉ áp dụng nếu không bị khóa và request có gửi)
        if (!$scope['locked_khoa'] && $request->filled('MaKhoa')) {
            $query->whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $request->MaKhoa));
        }

        // Bộ lọc Bộ môn (chỉ áp dụng nếu không bị khóa và request có gửi)
        if (!$scope['locked_bm'] && $request->filled('MaBoMon')) {
            $query->whereHas('giangVien', fn($q) => $q->where('MaBoMon', $request->MaBoMon));
        }

        // Bộ lọc Trạng thái đề tài
        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai);
        }

        // Bộ lọc Lĩnh vực
        if ($request->filled('LinhVuc')) {
            $query->where('LinhVuc', $request->LinhVuc);
        }

        // Tìm kiếm từ khóa (Mã đề tài, Tên đề tài, Giảng viên)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('TenDeTai', 'like', "%{$search}%")
                  ->orWhere('MaDeTai', 'like', "%{$search}%")
                  ->orWhereHas('giangVien', fn($g) => $g->where('HoTen', 'like', "%{$search}%"));
            });
        }

        return $query;
    }

    /**
     * Dashboard Thống kê Đề tài Khóa luận Tốt nghiệp
     */
    public function index(Request $request)
    {
        $scope = $this->resolveUserScope($request);

        if ($scope['is_admin']) {
            return redirect()->route('admin.duyet_detai.index', array_merge($request->query(), ['tab' => 'thongke']));
        }

        // Danh mục dropdown
        $hocKys = HocKy::orderBy('MaHocKy', 'desc')->get();
        $selectedHocKy = $request->input('MaHocKy');
        if (!$selectedHocKy && $hocKys->isNotEmpty()) {
            // Mặc định chọn học kỳ đang diễn ra hoặc học kỳ đầu tiên
            $currentHK = $hocKys->firstWhere('TrangThai', '1') ?? $hocKys->first();
            $selectedHocKy = $currentHK?->MaHocKy;
            $request->merge(['MaHocKy' => $selectedHocKy]);
        }

        // Danh sách Khoa khả dụng
        if ($scope['is_admin']) {
            $khoas = Khoa::orderBy('TenKhoa')->get();
        } else {
            $khoas = Khoa::where('MaKhoa', $scope['force_khoa'])->get();
        }

        // Danh sách Bộ môn khả dụng
        $currentKhoa = $scope['force_khoa'] ?? $request->input('MaKhoa');
        if ($scope['force_bm']) {
            $boMons = BoMon::where('MaBoMon', $scope['force_bm'])->get();
        } elseif ($currentKhoa) {
            $boMons = BoMon::where('MaKhoa', $currentKhoa)->orderBy('TenBoMon')->get();
        } else {
            $boMons = BoMon::orderBy('TenBoMon')->get();
        }

        // Danh sách Lĩnh vực duy nhất
        $linhVucs = DeTai::whereNotNull('LinhVuc')->where('LinhVuc', '!=', '')
            ->distinct()->pluck('LinhVuc')->sort()->values();

        // Danh sách Trạng thái chuẩn
        $trangThais = [
            'Chờ duyệt cấp Bộ môn',
            'Đang phản biện đề cương',
            'Chờ duyệt cấp Khoa',
            'Trưởng khoa đã duyệt',
            'Đã công bố',
            'Đã đăng ký',
            'Hoàn thành',
            'Yêu cầu chỉnh sửa',
            'Từ chối'
        ];

        // Lấy tất cả đề tài theo filter để tính toán KPI & Biểu đồ
        $allScopedQuery = $this->buildScopedQuery($request, $scope);
        $allDeTais = (clone $allScopedQuery)->get();

        // ------------------------------------------------------------------
        // TÍNH TOÁN 6 KPI CARDS
        // ------------------------------------------------------------------
        $totalDeTai = $allDeTais->count();
        $daCongBo = 0;
        $dangDangKy = 0;
        $daDuNhom = 0;
        $dangChoDuyet = 0;
        $daHoanThanh = 0;

        // Tình hình đăng ký (Section E)
        $tongMoDangKy = 0;
        $coNhomDangKy = 0;
        $conCho = 0;
        $daDuSV = 0;
        $chuaCoSVDangKy = 0;

        // Dữ liệu cho các biểu đồ
        $statusCounts = [
            'Đã công bố' => 0,
            'Đang đăng ký / Đủ nhóm' => 0,
            'Đang chờ duyệt' => 0,
            'Đã hoàn thành' => 0,
            'Từ chối / Chỉnh sửa' => 0,
        ];

        $boMonCounts = [];
        $linhVucCounts = [];
        $quyMoNhomCounts = [
            '1 sinh viên' => 0,
            '2 sinh viên' => 0,
            '3 sinh viên' => 0,
        ];

        foreach ($allDeTais as $dt) {
            // Tính số SV đã đăng ký
            $approvedRegistrations = $dt->phieuDangKys->where('TrangThai', 'Đã duyệt');
            $allRegistrations = $dt->phieuDangKys->whereIn('TrangThai', ['Đã duyệt', 'Chờ duyệt']);
            
            $numRegisteredSV = 0;
            foreach ($approvedRegistrations as $pdk) {
                if ($pdk->nhom) {
                    $numRegisteredSV += $pdk->nhom->thanhVienNhoms->count();
                }
            }
            // Nếu chưa có approved thì tính cả pending cho trạng thái đang đăng ký
            $numPendingSV = 0;
            foreach ($allRegistrations as $pdk) {
                if ($pdk->nhom) {
                    $numPendingSV += $pdk->nhom->thanhVienNhoms->count();
                }
            }

            $maxSV = $dt->SoLuongSinhVienToiDa ?? 3;
            $dt->so_sv_dang_ky = $numRegisteredSV;

            // 1. Phân loại KPI
            $tt = trim($dt->TrangThai);

            if ($tt === 'Hoàn thành') {
                $daHoanThanh++;
                $statusCounts['Đã hoàn thành']++;
            } elseif (in_array($tt, ['Chờ duyệt cấp Bộ môn', 'Đang phản biện đề cương', 'Chờ duyệt cấp Khoa'])) {
                $dangChoDuyet++;
                $statusCounts['Đang chờ duyệt']++;
            } elseif (in_array($tt, ['Từ chối', 'Yêu cầu chỉnh sửa'])) {
                $statusCounts['Từ chối / Chỉnh sửa']++;
            } else {
                // Các trạng thái công bố / đã duyệt / đã đăng ký
                $daCongBo++;
                $tongMoDangKy++;

                if ($numRegisteredSV >= $maxSV) {
                    $daDuNhom++;
                    $daDuSV++;
                    $coNhomDangKy++;
                    $statusCounts['Đang đăng ký / Đủ nhóm']++;
                } elseif ($numPendingSV > 0) {
                    $dangDangKy++;
                    $conCho++;
                    $coNhomDangKy++;
                    $statusCounts['Đang đăng ký / Đủ nhóm']++;
                } else {
                    $chuaCoSVDangKy++;
                    $conCho++;
                    $statusCounts['Đã công bố']++;
                }
            }

            // 2. Thống kê theo Bộ môn
            $bmName = $dt->giangVien?->boMon?->TenBoMon ?? 'Chưa phân bộ môn';
            $boMonCounts[$bmName] = ($boMonCounts[$bmName] ?? 0) + 1;

            // 3. Thống kê theo Lĩnh vực
            $lvName = $dt->LinhVuc ?: 'Lĩnh vực khác';
            $linhVucCounts[$lvName] = ($linhVucCounts[$lvName] ?? 0) + 1;

            // 4. Thống kê theo Quy mô nhóm
            if ($maxSV == 1) {
                $quyMoNhomCounts['1 sinh viên']++;
            } elseif ($maxSV == 2) {
                $quyMoNhomCounts['2 sinh viên']++;
            } else {
                $quyMoNhomCounts['3 sinh viên']++;
            }
        }

        // Sắp xếp Lĩnh vực giảm dần và lấy top 8
        arsort($linhVucCounts);
        $topLinhVucCounts = array_slice($linhVucCounts, 0, 8, true);

        arsort($boMonCounts);

        $kpis = [
            'total'          => $totalDeTai,
            'da_cong_bo'     => $daCongBo,
            'dang_dang_ky'   => $dangDangKy,
            'da_du_nhom'     => $daDuNhom,
            'dang_cho_duyet' => $dangChoDuyet,
            'da_hoan_thanh'  => $daHoanThanh,
        ];

        $dangKyStats = [
            'tong_cong_bo' => $tongMoDangKy,
            'co_nhom_dk'   => $coNhomDangKy,
            'con_cho'      => $conCho,
            'da_du_sv'     => $daDuSV,
            'chua_co_sv'   => $chuaCoSVDangKy,
        ];

        // ------------------------------------------------------------------
        // PHÂN TRANG DANH SÁCH ĐỀ TÀI CHI TIẾT
        // ------------------------------------------------------------------
        $pagedQuery = $this->buildScopedQuery($request, $scope);

        // Xử lý sắp xếp
        $sortBy = $request->input('sort_by', 'updated_at');
        $sortDir = $request->input('sort_dir', 'desc');

        if (in_array($sortBy, ['TenDeTai', 'MaDeTai', 'TrangThai', 'updated_at'])) {
            $pagedQuery->orderBy($sortBy, $sortDir);
        } else {
            $pagedQuery->orderBy('updated_at', 'desc');
        }

        $deTais = $pagedQuery->paginate(5)->withQueryString();

        // Gán số SV đăng ký cho trang hiện tại
        foreach ($deTais as $dt) {
            $approved = $dt->phieuDangKys->where('TrangThai', 'Đã duyệt');
            $cnt = 0;
            foreach ($approved as $p) {
                if ($p->nhom) {
                    $cnt += $p->nhom->thanhVienNhoms->count();
                }
            }
            $dt->so_sv_dang_ky = $cnt;
        }

        return view('thongke.detai', compact(
            'scope',
            'hocKys',
            'khoas',
            'boMons',
            'linhVucs',
            'trangThais',
            'kpis',
            'dangKyStats',
            'statusCounts',
            'boMonCounts',
            'topLinhVucCounts',
            'quyMoNhomCounts',
            'deTais'
        ));
    }

    /**
     * Xuất dữ liệu Thống kê Đề tài ra file CSV (UTF-8 BOM hỗ trợ chuẩn Excel)
     */
    public function exportExcel(Request $request)
    {
        $scope = $this->resolveUserScope($request);
        $query = $this->buildScopedQuery($request, $scope);
        $deTais = $query->orderBy('MaDeTai')->get();

        $fileName = 'Thong_Ke_De_Tai_Khoa_Luan_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($deTais) {
            $file = fopen('php://output', 'w');
            // Ghi UTF-8 BOM
            fwrite($file, "\xEF\xBB\xBF");

            // Header hàng
            fputcsv($file, [
                'STT',
                'Mã Đề Tài',
                'Tên Đề Tài',
                'Giảng Viên Hướng Dẫn',
                'Khoa',
                'Bộ Môn',
                'Lĩnh Vực',
                'Số SV Đã Đăng Ký',
                'Số SV Tối Đa',
                'Tình Trạng Chỗ',
                'Trạng Thái',
                'Học Kỳ',
                'Ngày Cập Nhật'
            ]);

            $stt = 1;
            foreach ($deTais as $dt) {
                $approved = $dt->phieuDangKys->where('TrangThai', 'Đã duyệt');
                $registeredCount = 0;
                foreach ($approved as $p) {
                    if ($p->nhom) {
                        $registeredCount += $p->nhom->thanhVienNhoms->count();
                    }
                }
                $maxSV = $dt->SoLuongSinhVienToiDa ?? 3;
                $tinhTrang = ($registeredCount >= $maxSV) ? 'Đã đủ' : (($registeredCount > 0) ? 'Còn chỗ' : 'Chưa có SV');

                fputcsv($file, [
                    $stt++,
                    $dt->MaDeTai,
                    $dt->TenDeTai,
                    $dt->giangVien?->HoTen ?? 'Chưa phân công',
                    $dt->giangVien?->boMon?->khoa?->TenKhoa ?? '',
                    $dt->giangVien?->boMon?->TenBoMon ?? '',
                    $dt->LinhVuc ?? 'Chưa xác định',
                    $registeredCount,
                    $maxSV,
                    $tinhTrang,
                    $dt->TrangThai,
                    $dt->hocKy?->TenHocKy ?? $dt->MaHocKy,
                    $dt->updated_at ? $dt->updated_at->format('d/m/Y H:i') : ''
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * AJAX: Lấy danh sách bộ môn theo khoa (hỗ trợ cascading dropdown)
     */
    public function getBoMonsByKhoa($maKhoa)
    {
        $boMons = BoMon::where('MaKhoa', $maKhoa)
            ->orderBy('TenBoMon')
            ->get(['MaBoMon', 'TenBoMon']);

        return response()->json($boMons);
    }
}
