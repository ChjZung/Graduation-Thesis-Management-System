<?php

namespace App\Http\Controllers;

use App\Models\HoSoBaoVe;
use App\Models\BaoCaoSauBaoVe;
use App\Models\GiangVien;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\Nganh;
use App\Models\HocKy;
use App\Models\HoiDong;
use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaoCaoSauChamDiemController extends Controller
{
    /**
     * Xác định vai trò, phạm vi dữ liệu (scope), layout và tiền tố route của người dùng đang đăng nhập
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
                'maBoMon'     => null,
                'layout'      => 'layouts.admin',
                'routePrefix' => 'admin.baocao_sau_chamdiem',
            ];
        }

        if ($roleName === 'Trưởng khoa') {
            $maKhoa = $this->resolveMaKhoa($user);
            return [
                'role'        => 'truongkhoa',
                'roleTitle'   => 'Trưởng Khoa',
                'maKhoa'      => $maKhoa,
                'maBoMon'     => null,
                'layout'      => 'layouts.truongkhoa',
                'routePrefix' => 'truongkhoa.baocao_sau_chamdiem',
            ];
        }

        if ($roleName === 'Trưởng bộ môn') {
            $maBoMon = $this->resolveMaBoMon($user);
            return [
                'role'        => 'truongbomon',
                'roleTitle'   => 'Trưởng Bộ Môn',
                'maKhoa'      => null,
                'maBoMon'     => $maBoMon,
                'layout'      => 'layouts.truongbomon',
                'routePrefix' => 'truongbomon.baocao_sau_chamdiem',
            ];
        }

        abort(403, 'Bạn không có quyền truy cập chức năng này.');
    }

    /**
     * Xác định mã khoa cho Trưởng khoa
     */
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

    /**
     * Xác định mã bộ môn cho Trưởng bộ môn
     */
    private function resolveMaBoMon(TaiKhoan $user): ?string
    {
        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv && $gv->MaBoMon) {
            return $gv->MaBoMon;
        }

        if (preg_match('/^TBM_(?:BM_)?([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
            $bm = BoMon::where('MaBoMon', $m[1])->orWhere('MaBoMon', 'BM_' . $m[1])->first();
            if ($bm) return $bm->MaBoMon;
        }

        foreach (BoMon::all() as $itemBm) {
            $code = $itemBm->MaBoMon;
            if (str_contains($user->TenDangNhap, '_' . $code . '_') || str_contains($user->TenDangNhap, '_' . $code)) {
                return $itemBm->MaBoMon;
            }
        }

        if (!empty($gv?->HoTen)) {
            $found = BoMon::where('TruongBoMon', 'like', '%' . $gv->HoTen . '%')->first();
            if ($found) return $found->MaBoMon;
        }

        return BoMon::first()?->MaBoMon ?? 'CNPM';
    }

    /**
     * Danh sách báo cáo sau chấm điểm (dành cho Giáo vụ, Trưởng khoa, Trưởng bộ môn)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $scope = $this->getScope($user);

        // Khởi tạo truy vấn hồ sơ đã bảo vệ hoặc có yêu cầu chỉnh sửa sau bảo vệ
        $query = HoSoBaoVe::with([
            'nhom.truongNhom',
            'nhom.thanhViens.sinhVien.lop',
            'nhom.hocPhan',
            'nhom.hocKy',
            'deTai.giangVien.boMon.khoa',
            'hoiDong.khoa',
            'hoiDong.nganh',
            'baoCaoSauBaoVes.sinhVienNop',
            'baoCaoSauBaoVeMoiNhat',
        ])
        ->where(function ($q) {
            $q->whereNotNull('YeuCauChinhSuaSauBaoVe')
              ->orWhereNotNull('TrangThaiChinhSuaSauBaoVe')
              ->orWhereHas('baoCaoSauBaoVes')
              ->orWhereNotNull('FileBanChinhSua')
              ->orWhereNotNull('MaHoiDong');
        });

        // 1. Giới hạn phạm vi theo vai trò (Backend Scope Enforcement)
        if ($scope['role'] === 'truongkhoa') {
            $maKhoa = $scope['maKhoa'];
            $query->where(function ($q) use ($maKhoa) {
                $q->whereHas('deTai.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa))
                  ->orWhereHas('hoiDong', fn($hd) => $hd->where('MaKhoa', $maKhoa));
            });
        } elseif ($scope['role'] === 'truongbomon') {
            $bm = $scope['maBoMon'];
            $bmAlt = str_starts_with($bm, 'BM_') ? substr($bm, 3) : 'BM_' . $bm;
            $query->whereHas('deTai.giangVien', function ($gv) use ($bm, $bmAlt) {
                $gv->whereIn('MaBoMon', [$bm, $bmAlt]);
            });
        }

        // 2. Bộ lọc học kỳ
        if ($request->filled('MaHocKy')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('hoiDong', fn($hd) => $hd->where('MaHocKy', $request->MaHocKy))
                  ->orWhereHas('nhom', fn($nh) => $nh->where('MaHocKy', $request->MaHocKy))
                  ->orWhereHas('deTai', fn($dt) => $dt->where('MaHocKy', $request->MaHocKy));
            });
        }

        // 3. Bộ lọc Khoa (chỉ áp dụng cho Giáo vụ; Trưởng khoa/TBM đã cố định scope)
        if ($scope['role'] === 'admin' && $request->filled('MaKhoa')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('deTai.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $request->MaKhoa))
                  ->orWhereHas('hoiDong', fn($hd) => $hd->where('MaKhoa', $request->MaKhoa));
            });
        }

        // 4. Bộ lọc Bộ môn (áp dụng cho Giáo vụ và Trưởng khoa)
        if (in_array($scope['role'], ['admin', 'truongkhoa']) && $request->filled('MaBoMon')) {
            $bmFilter = $request->MaBoMon;
            $bmFilterAlt = str_starts_with($bmFilter, 'BM_') ? substr($bmFilter, 3) : 'BM_' . $bmFilter;
            $query->whereHas('deTai.giangVien', function ($gv) use ($bmFilter, $bmFilterAlt) {
                $gv->whereIn('MaBoMon', [$bmFilter, $bmFilterAlt]);
            });
        }

        // 5. Bộ lọc Ngành
        if ($request->filled('MaNganh')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('hoiDong', fn($hd) => $hd->where('MaNganh', $request->MaNganh))
                  ->orWhereHas('nhom.thanhViens.sinhVien.lop', fn($l) => $l->where('MaNganh', $request->MaNganh));
            });
        }

        // 6. Bộ lọc Hội đồng
        if ($request->filled('MaHoiDong')) {
            $query->where('MaHoiDong', $request->MaHoiDong);
        }

        // 7. Bộ lọc Trạng thái nộp lại
        if ($request->filled('status')) {
            $st = $request->status;
            if ($st === 'da_hoan_thanh' || $st === 'dat') {
                $query->where('TrangThaiChinhSuaSauBaoVe', 'DaHoanThanh');
            } elseif ($st === 'da_nop' || $st === 'cho_kiem_tra') {
                $query->where('TrangThaiChinhSuaSauBaoVe', 'DaNop');
            } elseif ($st === 'yeu_cau_sua' || $st === 'chua_dat') {
                $query->where('TrangThaiChinhSuaSauBaoVe', 'YeuCauSuaTiep');
            } elseif ($st === 'chua_nop') {
                $query->where(function ($q) {
                    $q->whereNull('TrangThaiChinhSuaSauBaoVe')
                      ->orWhere('TrangThaiChinhSuaSauBaoVe', 'ChoSinhVienNop')
                      ->orWhereDoesntHave('baoCaoSauBaoVes');
                });
            }
        }

        // 8. Ô tìm kiếm: MSSV, Họ tên, Tên nhóm, Tên đề tài, Tên GVHD
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('MaHoSo', 'like', "%{$s}%")
                  ->orWhereHas('nhom', fn($nq) => $nq->where('TenNhom', 'like', "%{$s}%"))
                  ->orWhereHas('deTai', fn($dq) => $dq->where('TenDeTai', 'like', "%{$s}%"))
                  ->orWhereHas('deTai.giangVien', fn($gq) => $gq->where('HoTen', 'like', "%{$s}%"))
                  ->orWhereHas('nhom.thanhViens.sinhVien', function ($sq) use ($s) {
                      $sq->where('MaSV', 'like', "%{$s}%")
                         ->orWhere('HoTen', 'like', "%{$s}%");
                  });
            });
        }

        // Thống kê nhanh theo bộ đếm
        $countBase = clone $query;
        $counts = [
            'total'          => (clone $countBase)->count(),
            'cho_kiem_tra'   => (clone $countBase)->where('TrangThaiChinhSuaSauBaoVe', 'DaNop')->count(),
            'yeu_cau_sua'    => (clone $countBase)->where('TrangThaiChinhSuaSauBaoVe', 'YeuCauSuaTiep')->count(),
            'da_hoan_thanh'  => (clone $countBase)->where('TrangThaiChinhSuaSauBaoVe', 'DaHoanThanh')->count(),
            'chua_nop'       => (clone $countBase)->where(function($q) {
                $q->whereNull('TrangThaiChinhSuaSauBaoVe')
                  ->orWhere('TrangThaiChinhSuaSauBaoVe', 'ChoSinhVienNop')
                  ->orWhereDoesntHave('baoCaoSauBaoVes');
            })->count(),
        ];

        $hoSos = $query->orderBy('updated_at', 'desc')->paginate(5)->withQueryString();

        // Danh mục dropdown phục vụ bộ lọc
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $khoas = Khoa::orderBy('TenKhoa')->get();

        $boMonsQuery = BoMon::query();
        if ($scope['role'] === 'truongkhoa') {
            $boMonsQuery->where('MaKhoa', $scope['maKhoa']);
        } elseif ($scope['role'] === 'truongbomon') {
            $bm = $scope['maBoMon'];
            $bmAlt = str_starts_with($bm, 'BM_') ? substr($bm, 3) : 'BM_' . $bm;
            $boMonsQuery->whereIn('MaBoMon', [$bm, $bmAlt]);
        }
        $boMons = $boMonsQuery->orderBy('TenBoMon')->get();

        $nganhsQuery = Nganh::query();
        if ($scope['role'] === 'truongkhoa') {
            $nganhsQuery->where('MaKhoa', $scope['maKhoa']);
        }
        $nganhs = $nganhsQuery->orderBy('TenNganh')->get();

        $hoiDongsQuery = HoiDong::query();
        if ($scope['role'] === 'truongkhoa') {
            $hoiDongsQuery->where('MaKhoa', $scope['maKhoa']);
        }
        if ($request->filled('MaHocKy')) {
            $hoiDongsQuery->where('MaHocKy', $request->MaHocKy);
        }
        $hoiDongs = $hoiDongsQuery->orderBy('TenHoiDong')->get();

        return view('baocao_sau_chamdiem.index', compact(
            'scope',
            'hoSos',
            'counts',
            'hocKies',
            'khoas',
            'boMons',
            'nganhs',
            'hoiDongs'
        ));
    }

    /**
     * Chi tiết hồ sơ báo cáo sau chấm điểm (Chế độ chỉ đọc cho Giáo vụ, Trưởng khoa, Trưởng bộ môn)
     */
    public function show(Request $request, string $id)
    {
        $user = Auth::user();
        $scope = $this->getScope($user);

        $hoSo = HoSoBaoVe::with([
            'nhom.truongNhom',
            'nhom.thanhViens.sinhVien.lop',
            'nhom.hocPhan',
            'nhom.hocKy',
            'deTai.giangVien.boMon.khoa',
            'hoiDong.khoa',
            'hoiDong.nganh',
            'baoCaoSauBaoVes.sinhVienNop',
            'baoCaoSauBaoVeMoiNhat',
        ])->findOrFail($id);

        // Kiểm tra phân quyền phạm vi truy cập (Scope check - Chống IDOR)
        if ($scope['role'] === 'truongkhoa') {
            $dtKhoa = $hoSo->deTai?->giangVien?->boMon?->MaKhoa ?? $hoSo->hoiDong?->MaKhoa;
            if (!$dtKhoa || $dtKhoa !== $scope['maKhoa']) {
                abort(403, 'Bạn không có quyền truy cập hồ sơ đề tài ngoài phạm vi Khoa của bạn.');
            }
        } elseif ($scope['role'] === 'truongbomon') {
            $dtBoMon = $hoSo->deTai?->giangVien?->MaBoMon;
            $bm = $scope['maBoMon'];
            $bmAlt = str_starts_with($bm, 'BM_') ? substr($bm, 3) : 'BM_' . $bm;
            if (!$dtBoMon || !in_array($dtBoMon, [$bm, $bmAlt])) {
                abort(403, 'Bạn không có quyền truy cập hồ sơ đề tài ngoài phạm vi Bộ môn của bạn.');
            }
        }

        $latestBaoCao = $hoSo->baoCaoSauBaoVeMoiNhat;
        $lichSuNop = $hoSo->baoCaoSauBaoVes()->orderBy('LanChinhSua', 'desc')->get();

        return view('baocao_sau_chamdiem.show', compact(
            'scope',
            'hoSo',
            'latestBaoCao',
            'lichSuNop'
        ));
    }
}
