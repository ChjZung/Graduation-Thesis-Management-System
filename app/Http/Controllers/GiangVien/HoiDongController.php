<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Models\HoiDong;
use App\Models\HocKy;
use App\Models\HoSoBaoVe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HoiDongController extends Controller
{
    /**
     * Màn hình "Hội đồng của tôi" dành riêng cho Giảng viên
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên tương ứng với tài khoản này.');
        }

        $baseQuery = HoiDong::whereHas('thanhViens', function ($q) use ($gv) {
            $q->where('MaGV', $gv->MaGV);
        });

        // 1. Thống kê tổng quan số lượng hội đồng tham gia
        $now = now();
        $today = $now->toDateString();

        $allMyHoiDongs = (clone $baseQuery)->with('thanhViens')->get();
        $counts = [
            'tong_cong'     => $allMyHoiDongs->count(),
            'sap_dien_ra'   => $allMyHoiDongs->filter(fn($hd) => $hd->TrangThai === 'Chưa diễn ra' || (!$hd->TrangThai && $hd->NgayBaoVe && $hd->NgayBaoVe->gt($today)))->count(),
            'dang_dien_ra'  => $allMyHoiDongs->filter(fn($hd) => $hd->TrangThai === 'Đang diễn ra' || (!$hd->TrangThai && $hd->NgayBaoVe && $hd->NgayBaoVe->toDateString() === $today))->count(),
            'da_hoan_thanh' => $allMyHoiDongs->filter(fn($hd) => in_array($hd->TrangThai, ['Đã kết thúc', 'Đã hoàn thành']) || (!$hd->TrangThai && $hd->NgayBaoVe && $hd->NgayBaoVe->lt($today)))->count(),
            'chu_tich'      => $allMyHoiDongs->filter(fn($hd) => $hd->thanhViens->where('MaGV', $gv->MaGV)->first()?->VaiTro === 'Chủ tịch')->count(),
            'thu_ky'        => $allMyHoiDongs->filter(fn($hd) => $hd->thanhViens->where('MaGV', $gv->MaGV)->first()?->VaiTro === 'Thư ký')->count(),
            'phan_bien'     => $allMyHoiDongs->filter(fn($hd) => $hd->thanhViens->where('MaGV', $gv->MaGV)->first()?->VaiTro === 'Phản biện')->count(),
            'uy_vien'       => $allMyHoiDongs->filter(fn($hd) => $hd->thanhViens->where('MaGV', $gv->MaGV)->first()?->VaiTro === 'Ủy viên')->count(),
        ];

        // 2. Query lọc danh sách
        $query = (clone $baseQuery)->with([
            'thanhViens.giangVien.boMon',
            'hoSoBaoVes.nhom.deTai.giangVien',
            'hoSoBaoVes.nhom.truongNhom',
            'hocKy',
            'khoa',
            'nganh',
        ]);

        // Lọc theo Học kỳ
        if ($request->filled('MaHocKy') && $request->MaHocKy !== 'ALL') {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        // Lọc theo Vai trò
        if ($request->filled('VaiTro') && $request->VaiTro !== 'ALL') {
            $query->whereHas('thanhViens', function ($q) use ($gv, $request) {
                $q->where('MaGV', $gv->MaGV)->where('VaiTro', $request->VaiTro);
            });
        }

        // Lọc theo Trạng thái
        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            $st = $request->TrangThai;
            if ($st === 'SapDienRa') {
                $query->where(function ($q) use ($today) {
                    $q->where('TrangThai', 'Chưa diễn ra')
                      ->orWhere(function ($sub) use ($today) {
                          $sub->whereNull('TrangThai')->where('NgayBaoVe', '>', $today);
                      });
                });
            } elseif ($st === 'DangDienRa') {
                $query->where(function ($q) use ($today) {
                    $q->where('TrangThai', 'Đang diễn ra')
                      ->orWhere(function ($sub) use ($today) {
                          $sub->whereNull('TrangThai')->whereDate('NgayBaoVe', $today);
                      });
                });
            } elseif ($st === 'DaHoanThanh') {
                $query->where(function ($q) use ($today) {
                    $q->whereIn('TrangThai', ['Đã kết thúc', 'Đã hoàn thành'])
                      ->orWhere(function ($sub) use ($today) {
                          $sub->whereNull('TrangThai')->where('NgayBaoVe', '<', $today);
                      });
                });
            }
        }

        // Tìm kiếm theo từ khóa
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('TenHoiDong', 'like', "%{$s}%")
                  ->orWhere('DiaDiem', 'like', "%{$s}%")
                  ->orWhere('SoQuyetDinh', 'like', "%{$s}%")
                  ->orWhereHas('hoSoBaoVes.nhom.deTai', fn($dq) => $dq->where('TenDeTai', 'like', "%{$s}%"));
            });
        }

        $hoiDongs = $query->orderBy('NgayBaoVe', 'desc')
            ->orderBy('ThoiGianBatDau', 'desc')
            ->paginate(10)
            ->withQueryString();

        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        return view('giangvien.hoidong.index', compact('gv', 'hoiDongs', 'counts', 'hocKies'));
    }

    /**
     * Màn hình chi tiết một Hội đồng mà Giảng viên tham gia
     */
    public function show($id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) abort(404);

        $hoiDong = HoiDong::with([
            'thanhViens.giangVien.boMon',
            'hoSoBaoVes.nhom.deTai.giangVien.boMon',
            'hoSoBaoVes.nhom.truongNhom',
            'hoSoBaoVes.nhom.thanhViens.sinhVien',
            'hoSoBaoVes.baoCaoSauBaoVes',
            'hoSoBaoVes.phieuChamDiems',
            'hocKy',
            'khoa',
            'nganh',
        ])->findOrFail($id);

        // BẢO MẬT & IDOR: Giảng viên bắt buộc phải là thành viên trong hội đồng này
        $myMemberRecord = $hoiDong->thanhViens->firstWhere('MaGV', $gv->MaGV);
        if (!$myMemberRecord) {
            abort(403, 'Bạn không được phân công tham gia Hội đồng này.');
        }

        $myRole = $myMemberRecord->VaiTro;
        $isThuKy = ($myRole === 'Thư ký' || mb_stripos($myRole, 'thư ký') !== false);
        $isChuTich = ($myRole === 'Chủ tịch' || mb_stripos($myRole, 'chủ tịch') !== false);

        // Sắp xếp thứ tự chức danh thành viên: Chủ tịch -> Thư ký -> Phản biện -> Ủy viên
        $roleOrder = ['Chủ tịch' => 1, 'Thư ký' => 2, 'Phản biện' => 3, 'Ủy viên' => 4];
        $sortedThanhViens = $hoiDong->thanhViens->sortBy(function ($tv) use ($roleOrder) {
            return $roleOrder[$tv->VaiTro] ?? 5;
        });

        return view('giangvien.hoidong.show', compact(
            'gv',
            'hoiDong',
            'myRole',
            'isThuKy',
            'isChuTich',
            'sortedThanhViens'
        ));
    }
}
