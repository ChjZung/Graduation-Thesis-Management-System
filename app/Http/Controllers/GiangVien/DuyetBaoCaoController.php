<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\BaoCaoTienDo;
use App\Models\GiangVien;
use App\Models\NhanXet;
use App\Models\Nhom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DuyetBaoCaoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $giangVien = GiangVien::where('MaTK', $user->MaTK)->firstOrFail();

        // Lấy danh sách nhóm do GV này hướng dẫn
        $nhoms = Nhom::whereHas('dangKyDeTai', function ($q) use ($giangVien) {
                $q->where('MaGVHuongDan', $giangVien->MaGV)
                  ->where('TrangThai', 'Đã duyệt');
            })
            ->with([
                'deTai',
                'truongNhom',
                'thanhViens' => fn($q) => $q->where('TrangThai', 'da_tham_gia')->with('sinhVien'),
                'baoCaos' => fn($q) => $q->with(['tomTat', 'nhanXets.giangVien'])->orderBy('LanBaoCao'),
            ])
            ->get();

        return view('giangvien.baocao.index', compact('giangVien', 'nhoms'));
    }

    public function storeNhanXet(Request $request, $maBaoCao)
    {
        $request->validate([
            'NoiDung'   => 'required|string|max:2000',
            'LoaiNhanXet' => 'required|in:Đạt,Yêu cầu nộp lại',
        ], [
            'NoiDung.required'       => 'Vui lòng nhập nhận xét.',
            'LoaiNhanXet.required'   => 'Vui lòng chọn kết quả đánh giá.',
            'LoaiNhanXet.in'         => 'Kết quả phải là "Đạt" hoặc "Yêu cầu nộp lại".',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::where('MaTK', $user->MaTK)->firstOrFail();
        $baoCao = BaoCaoTienDo::findOrFail($maBaoCao);

        DB::transaction(function () use ($request, $baoCao, $giangVien) {
            // Tạo nhận xét
            $maNhanXet = 'NX' . str_pad(NhanXet::count() + 1, 3, '0', STR_PAD_LEFT);
            NhanXet::create([
                'MaNhanXet'   => $maNhanXet,
                'MaBaoCao'    => $baoCao->MaBaoCao,
                'MaGV'        => $giangVien->MaGV,
                'NoiDung'     => $request->NoiDung,
                'LoaiNhanXet' => $request->LoaiNhanXet,
                'NgayNhanXet' => now(),
                'TrangThai'   => 'Đã nhận xét',
            ]);

            // Cập nhật trạng thái báo cáo
            $baoCao->update(['TrangThai' => $request->LoaiNhanXet]);
        });

        $msg = $request->LoaiNhanXet === 'Đạt'
            ? "✅ Đã đánh giá \"Đạt\" cho Mốc {$baoCao->LanBaoCao}. Sinh viên có thể nộp Mốc tiếp theo!"
            : "🔄 Đã yêu cầu nhóm nộp lại Mốc {$baoCao->LanBaoCao}.";

        return redirect()->route('giangvien.baocao.index')->with('success', $msg);
    }
}
