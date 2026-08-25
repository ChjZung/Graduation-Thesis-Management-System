<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DangKyDeTai;
use App\Models\Nhom;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DuyetDangKyDeTaiController extends Controller
{
    public function index(Request $request)
    {
        $query = DangKyDeTai::with(['nhom.truongNhom', 'nhom.thanhViens.sinhVien', 'deTai.giangVien']);

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai);
        } else {
            $query->where('TrangThai', 'Chờ duyệt');
        }

        $dangKys = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.duyet_dangky.index', compact('dangKys'));
    }

    public function approve($id)
    {
        $dangKy = DangKyDeTai::with(['nhom', 'deTai'])->findOrFail($id);

        if ($dangKy->TrangThai !== 'Chờ duyệt') {
            return redirect()->back()->withErrors('Đơn đăng ký này đã được xử lý trước đó.');
        }

        $tenNhom = $dangKy->nhom->TenNhom ?? $dangKy->MaNhom;
        $tenDeTai = $dangKy->deTai->TenDeTai ?? 'Đề tài khóa luận';

        DB::transaction(function () use ($dangKy, $tenNhom) {
            // Duyệt đơn được chọn
            $dangKy->update([
                'TrangThai'   => 'Đã duyệt',
                'NgayDuyet'   => now(),
                'LyDoTuChoi'  => null,
            ]);

            if ($dangKy->nhom) {
                $dangKy->nhom->update([
                    'MaDeTai'   => $dangKy->MaDeTai,
                    'TrangThai' => 'Đã duyệt',
                ]);
            }

            // Tự động Từ chối TẤT CẢ các đơn khác cùng đề tài
            $otherDangKys = DangKyDeTai::where('MaDeTai', $dangKy->MaDeTai)
                ->where('MaDangKy', '!=', $dangKy->MaDangKy)
                ->where('TrangThai', 'Chờ duyệt')
                ->get();

            foreach ($otherDangKys as $other) {
                $other->update([
                    'TrangThai'  => 'Từ chối',
                    'LyDoTuChoi' => "Đề tài đã được phân công cho nhóm {$tenNhom}.",
                ]);

                if ($other->nhom) {
                    $other->nhom->update([
                        'MaDeTai'   => null,
                        'TrangThai' => 'Đang hoạt động',
                    ]);
                }
            }
        });

        // Gửi thông báo cho nhóm được duyệt
        ThongBaoService::guiDenNhom(
            $dangKy->MaNhom,
            '✅ Đăng ký đề tài được duyệt!',
            "Nhóm bạn đã được duyệt làm đề tài: " . $tenDeTai,
            'Đăng ký'
        );

        return redirect()->back()->with('success', "Đã phê duyệt đơn đăng ký đề tài cho Nhóm '{$tenNhom}' thành công! Các đơn trùng đề tài đã bị từ chối tự động.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'LyDoTuChoi' => 'required|string|max:500',
        ], [
            'LyDoTuChoi.required' => 'Vui lòng nhập lý do từ chối đơn đăng ký.',
        ]);

        $dangKy = DangKyDeTai::with(['nhom', 'deTai'])->findOrFail($id);
        $tenNhom = $dangKy->nhom->TenNhom ?? $dangKy->MaNhom;

        DB::transaction(function () use ($dangKy, $request) {
            $dangKy->update([
                'TrangThai'  => 'Từ chối',
                'LyDoTuChoi' => $request->LyDoTuChoi,
            ]);

            if ($dangKy->nhom) {
                $dangKy->nhom->update([
                    'MaDeTai'   => null,
                    'TrangThai' => 'Đang hoạt động',
                ]);
            }
        });

        return redirect()->back()->with('success', "Đã từ chối đơn đăng ký đề tài của Nhóm '{$tenNhom}'.");
    }
}
