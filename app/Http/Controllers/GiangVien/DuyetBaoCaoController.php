<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\BaoCaoTienDo;
use App\Models\GiangVien;
use App\Models\Nhom;
use App\Models\PhieuDangKy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DuyetBaoCaoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $giangVien = GiangVien::where('MaTK', $user->MaTK)->firstOrFail();

        $nhoms = Nhom::whereHas('phieuDangKys', function ($q) use ($giangVien) {
                $q->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])
                  ->whereHas('deTai', function ($d) use ($giangVien) {
                      $d->where('MaGV', $giangVien->MaGV);
                  });
            })
            ->with([
                'deTai.giangVien',
                'thanhViens.sinhVien',
                'baoCaos.tomTatBaoCao',
            ])
            ->get();

        // Thống kê 4 thẻ KPI theo chuẩn Figma
        $tongSoNhom = $nhoms->count();
        $baocaoChoDuyet = $nhoms->flatMap->baoCaos->where('TrangThai', 'Chờ duyệt')->count();
        $baocaoDaDat = $nhoms->flatMap->baoCaos->where('TrangThai', 'Đạt')->count();
        $nhomCanNhacNho = $nhoms->filter(function($n) {
            return $n->baoCaos->isEmpty() || $n->baoCaos->where('TrangThai', 'Yêu cầu nộp lại')->isNotEmpty();
        })->count();

        return view('giangvien.baocao.index', compact(
            'giangVien', 'nhoms', 'tongSoNhom', 'baocaoChoDuyet', 'baocaoDaDat', 'nhomCanNhacNho'
        ));
    }

    public function show($id)
    {
        $user = Auth::user();
        $giangVien = GiangVien::where('MaTK', $user->MaTK)->firstOrFail();

        $baoCao = BaoCaoTienDo::with([
            'deTai.giangVien',
            'tomTatBaoCao',
        ])->findOrFail($id);

        if (!$baoCao->deTai || $baoCao->deTai->MaGV !== $giangVien->MaGV) {
            abort(403, 'Bạn không phải Giảng viên hướng dẫn của đề tài này.');
        }

        $phieuDangKy = PhieuDangKy::with([
            'nhom.thanhViens.sinhVien',
        ])->where('MaDeTai', $baoCao->MaDeTai)
          ->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])
          ->first();

        $nhom = $phieuDangKy?->nhom;
        $deTai = $baoCao->deTai;
        $truongNhom = $nhom?->thanhViens->firstWhere('VaiTro', 'Nhóm trưởng')?->sinhVien 
            ?? $nhom?->thanhViens->firstWhere('VaiTro', 'Trưởng nhóm')?->sinhVien
            ?? $nhom?->thanhViens->first()?->sinhVien;

        $thanhViens = $nhom?->thanhViens ?? collect();
        $tomTat = $baoCao->tomTatBaoCao;

        // Lấy tất cả 5 mốc của đề tài này để hiển thị trên bảng lộ trình chi tiết
        $allBaoCaos = BaoCaoTienDo::where('MaDeTai', $baoCao->MaDeTai)
            ->orderBy('LanBaoCao')
            ->get()
            ->keyBy('LanBaoCao');

        // Kiểm tra xem Mốc tiếp theo đã nộp chưa để khóa kết quả
        $lanTiepTheo = $baoCao->LanBaoCao + 1;
        $daNopMocSau = BaoCaoTienDo::where('MaDeTai', $baoCao->MaDeTai)
            ->where('LanBaoCao', $lanTiepTheo)
            ->exists();

        return view('giangvien.baocao.show', compact(
            'giangVien',
            'baoCao',
            'nhom',
            'deTai',
            'truongNhom',
            'thanhViens',
            'tomTat',
            'allBaoCaos',
            'daNopMocSau'
        ));
    }

    public function storeNhanXet(Request $request, $maBaoCao)
    {
        $request->validate([
            'NoiDung'     => 'required|string|max:2000',
            'LoaiNhanXet' => 'required|in:Đạt,Yêu cầu nộp lại',
        ], [
            'NoiDung.required'     => 'Vui lòng nhập nhận xét.',
            'LoaiNhanXet.required' => 'Vui lòng chọn kết quả đánh giá.',
            'LoaiNhanXet.in'       => 'Kết quả phải là "Đạt" hoặc "Yêu cầu nộp lại".',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::where('MaTK', $user->MaTK)->firstOrFail();
        $baoCao = BaoCaoTienDo::with('deTai')->findOrFail($maBaoCao);

        // Policy: Chỉ GV hướng dẫn của đề tài mới được đánh giá
        if (!$baoCao->deTai || $baoCao->deTai->MaGV !== $giangVien->MaGV) {
            abort(403, 'Bạn không phải Giảng viên hướng dẫn của nhóm này và không có quyền nhận xét báo cáo.');
        }

        // Nếu nhóm sinh viên đã nộp Mốc tiếp theo thì khóa, không cho đổi về 'Yêu cầu nộp lại'
        $lanTiepTheo = $baoCao->LanBaoCao + 1;
        $daNopMocSau = BaoCaoTienDo::where('MaDeTai', $baoCao->MaDeTai)
            ->where('LanBaoCao', $lanTiepTheo)
            ->exists();

        if ($daNopMocSau && $request->LoaiNhanXet === 'Yêu cầu nộp lại') {
            return back()->with('error', "Không thể chuyển trạng thái về \"Yêu cầu nộp lại\" do nhóm sinh viên đã nộp bài Mốc {$lanTiepTheo}.");
        }

        $isFirstReview = ($baoCao->TrangThai === 'Chờ duyệt');

        $baoCao->update([
            'TrangThai' => $request->LoaiNhanXet,
            'NhanXet'   => $request->NoiDung,
        ]);

        if ($isFirstReview) {
            $msg = $request->LoaiNhanXet === 'Đạt'
                ? "Đã đánh giá \"Đạt\" cho Mốc {$baoCao->LanBaoCao}. Sinh viên đã được mở khóa nộp Mốc tiếp theo!"
                : "Đã gửi đánh giá \"Yêu cầu nộp lại\" cho Mốc {$baoCao->LanBaoCao}.";
        } else {
            $msg = "Đã cập nhật lại nhận xét và đánh giá cho Mốc {$baoCao->LanBaoCao}.";
        }

        return redirect()->route('giangvien.baocao.show', $baoCao->MaBaoCao)->with('success', $msg);
    }
}
