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

        // Lấy các phân công phản biện đề cương của GV này
        $query = PhanCongPhanBien::with(['deTai.giangVien.boMon', 'deTai.hocKy', 'deTai.nganh'])
            ->where('MaGV', $gv->MaGV)
            ->where('VaiTro', 'Phản biện đề cương');

        if ($request->filled('KetQua')) {
            if ($request->KetQua === 'chua_danh_gia') {
                $query->whereNull('KetQua');
            } else {
                $query->where('KetQua', $request->KetQua);
            }
        }

        $phanCongs = $query->orderBy('created_at', 'desc')->paginate(10);

        $counts = [
            'chua_danh_gia' => PhanCongPhanBien::where('MaGV', $gv->MaGV)->where('VaiTro', 'Phản biện đề cương')->whereNull('KetQua')->count(),
            'dat'           => PhanCongPhanBien::where('MaGV', $gv->MaGV)->where('VaiTro', 'Phản biện đề cương')->where('KetQua', 'Đạt')->count(),
            'yeu_cau_sua'   => PhanCongPhanBien::where('MaGV', $gv->MaGV)->where('VaiTro', 'Phản biện đề cương')->where('KetQua', 'Yêu cầu chỉnh sửa')->count(),
            'khong_dat'     => PhanCongPhanBien::where('MaGV', $gv->MaGV)->where('VaiTro', 'Phản biện đề cương')->where('KetQua', 'Không đạt')->count(),
            'total'         => PhanCongPhanBien::where('MaGV', $gv->MaGV)->where('VaiTro', 'Phản biện đề cương')->count(),
        ];

        return view('giangvien.phanbien.index', compact('gv', 'phanCongs', 'counts'));
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
                    'TrangThai'  => 'Yêu cầu chỉnh sửa',
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

        return redirect()->route('giangvien.phanbien.index')
            ->with('success', "Đã gửi kết quả phản biện đề cương thành công!");
    }
}
