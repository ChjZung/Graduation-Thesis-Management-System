<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\PhanCongPhanBien;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PhanBienDeCuongController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        // Danh sách học kỳ
        $hocKies = \App\Models\HocKy::orderBy('MaHocKy', 'desc')->get();
        $currentHocKy = \App\Models\HocKy::where('TrangThai', 'Đang diễn ra')->orderBy('MaHocKy', 'desc')->first() ?? $hocKies->first();
        $selectedHocKy = $request->input('MaHocKy', $currentHocKy?->MaHocKy ?? '');

        // Lấy các phân công phản biện đề cương của GV này
        $query = PhanCongPhanBien::with(['deTai.giangVien.boMon', 'deTai.hocKy', 'deTai.nganh'])
            ->where('MaGV', $gv->MaGV)
            ->where('VaiTro', 'Phản biện đề cương');

        if ($selectedHocKy && $selectedHocKy !== 'ALL') {
            $query->whereHas('deTai', fn($q) => $q->where('MaHocKy', $selectedHocKy));
        }

        if ($request->filled('KetQua')) {
            if ($request->KetQua === 'chua_danh_gia') {
                $query->whereNull('KetQua')
                      ->where(function($q) {
                          $q->whereNull('TrangThai')
                            ->orWhere('TrangThai', '!=', 'Đã nộp lại đề cương');
                      });
            } elseif ($request->KetQua === 'da_nop_lai') {
                $query->where(function($q) {
                    $q->where('KetQua', 'Đã nộp lại')
                      ->orWhere('TrangThai', 'Đã nộp lại đề cương')
                      ->orWhereHas('deTai', fn($dq) => $dq->where('TrangThai', 'Đã cập nhật đề cương - Chờ phản biện lại'));
                });
            } elseif ($request->KetQua === 'yeu_cau_sua' || $request->KetQua === 'Yêu cầu chỉnh sửa') {
                $query->where('KetQua', 'Yêu cầu chỉnh sửa')
                      ->where('TrangThai', '!=', 'Đã nộp lại đề cương')
                      ->whereDoesntHave('deTai', fn($dq) => $dq->where('TrangThai', 'Đã cập nhật đề cương - Chờ phản biện lại'));
            } else {
                $query->where('KetQua', $request->KetQua);
            }
        }

        $phanCongs = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Base query tính counts scoped theo Học kỳ
        $baseCountQuery = PhanCongPhanBien::where('MaGV', $gv->MaGV)->where('VaiTro', 'Phản biện đề cương');
        if ($selectedHocKy && $selectedHocKy !== 'ALL') {
            $baseCountQuery->whereHas('deTai', fn($q) => $q->where('MaHocKy', $selectedHocKy));
        }

        $counts = [
            'chua_danh_gia' => (clone $baseCountQuery)->whereNull('KetQua')->where('TrangThai', '!=', 'Đã nộp lại đề cương')->count(),
            'da_nop_lai'    => (clone $baseCountQuery)->where(function($q) {
                $q->where('KetQua', 'Đã nộp lại')
                  ->orWhere('TrangThai', 'Đã nộp lại đề cương')
                  ->orWhereHas('deTai', fn($dq) => $dq->where('TrangThai', 'Đã cập nhật đề cương - Chờ phản biện lại'));
            })->count(),
            'dat'           => (clone $baseCountQuery)->where('KetQua', 'Đạt')->count(),
            'yeu_cau_sua'   => (clone $baseCountQuery)->where('KetQua', 'Yêu cầu chỉnh sửa')->where('TrangThai', '!=', 'Đã nộp lại đề cương')->whereDoesntHave('deTai', fn($dq) => $dq->where('TrangThai', 'Đã cập nhật đề cương - Chờ phản biện lại'))->count(),
            'khong_dat'     => (clone $baseCountQuery)->where('KetQua', 'Không đạt')->count(),
            'total'         => (clone $baseCountQuery)->count(),
        ];

        return view('giangvien.phanbien.index', compact('gv', 'phanCongs', 'counts', 'hocKies', 'currentHocKy', 'selectedHocKy'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        $phanCong = PhanCongPhanBien::with(['deTai.giangVien.boMon', 'deTai.hocKy', 'deTai.nganh'])
            ->where('MaPhanCong', $id)
            ->where('MaGV', $gv->MaGV)
            ->firstOrFail();

        $detai = $phanCong->deTai;

        return view('giangvien.phanbien.show', compact('gv', 'phanCong', 'detai'));
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'KetQua'   => 'required|in:Đạt,Yêu cầu chỉnh sửa,Không đạt',
            'NhanXet'  => 'required|string|max:2000',
        ], [
            'KetQua.required'  => 'Vui lòng chọn kết luận đánh giá (Đạt / Yêu cầu chỉnh sửa / Không đạt).',
            'KetQua.in'        => 'Kết quả đánh giá không hợp lệ.',
            'NhanXet.required' => 'Vui lòng nhập nhận xét, ý kiến phản biện đề cương.',
        ]);

        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        $phanCong = PhanCongPhanBien::with('deTai.giangVien')
            ->where('MaPhanCong', $id)
            ->where('MaGV', $gv->MaGV)
            ->firstOrFail();

        $detai = $phanCong->deTai;

        DB::transaction(function () use ($request, $phanCong, $detai, $gv) {
            $phanCong->update([
                'KetQua'      => $request->KetQua,
                'NhanXet'     => trim($request->NhanXet),
                'NgayDanhGia' => now(),
                'TrangThai'   => 'Đã phản biện',
            ]);

            // Cập nhật trạng thái đề tài tương ứng
            if ($request->KetQua === 'Đạt') {
                $detai->update([
                    'TrangThai' => 'Đã phản biện - Chờ duyệt BM',
                ]);
            } elseif ($request->KetQua === 'Yêu cầu chỉnh sửa') {
                $detai->update([
                    'TrangThai'  => 'Yêu cầu chỉnh sửa đề cương',
                    'LyDoTuChoi' => 'Ý kiến phản biện đề cương: ' . trim($request->NhanXet),
                ]);
            } else { // Không đạt
                $detai->update([
                    'TrangThai'  => 'Không đạt phản biện',
                    'LyDoTuChoi' => 'Kết quả phản biện đề cương: Không đạt. ' . trim($request->NhanXet),
                ]);
            }
        });

        // Gửi thông báo cho GV đề xuất
        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '📝 Kết quả phản biện đề cương đề tài',
                "Đề tài '{$detai->TenDeTai}' đã có kết quả phản biện từ {$gv->HoTen}: '{$request->KetQua}'. Vui lòng kiểm tra nhận xét chi tiết.",
                'Đề tài'
            );
        }

        // Gửi thông báo cho Trưởng Bộ Môn để duyệt lần 2 & công bố
        $maBoMon = $detai->giangVien?->MaBoMon;
        $tbmUser = \App\Models\TaiKhoan::where(function($q) {
                $q->where('MaVaiTro', 'VT04')
                  ->orWhere('TenDangNhap', 'LIKE', 'TBM_%');
            })
            ->where(function($q) use ($maBoMon) {
                if ($maBoMon) {
                    $q->where('TenDangNhap', 'LIKE', '%' . $maBoMon . '%');
                }
            })->first();

        if ($tbmUser) {
            $tieuDe = $request->KetQua === 'Đạt' 
                ? '✅ Đề cương đã phản biện ĐẠT - Chờ duyệt công bố' 
                : '⚠️ Đề cương có kết quả phản biện: ' . $request->KetQua;
            $noiDung = "Đề tài '{$detai->TenDeTai}' đã có kết quả phản biện từ GV {$gv->HoTen}: '{$request->KetQua}'. Vui lòng vào xem và phê duyệt công bố.";

            ThongBaoService::guiDen($tbmUser->MaTK, $tieuDe, $noiDung, 'Đề tài');
        }

        return redirect()->route('giangvien.phanbien.index')
            ->with('success', "Đã gửi kết quả phản biện đề cương thành công!");
    }
}
