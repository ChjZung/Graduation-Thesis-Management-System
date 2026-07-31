<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\BaoCaoTienDo;
use App\Models\NhanXet;
use App\Models\HuongDan;
use App\Models\NhomDoAn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DuyetBaoCaoController extends Controller
{
    public function index() {
        $gv = \App\Models\GiangVien::where('MaTK', Auth::user()->MaTK)->first();
        if (!$gv) abort(403);

        // Lấy danh sách các nhóm do giảng viên này hướng dẫn hoặc thuộc Lớp Học Phần của giảng viên
        $nhomIds1 = HuongDan::where('MaGV', $gv->MaGV)->pluck('MaNhom')->toArray();
        $nhomIds2 = NhomDoAn::whereHas('lopHocPhan', function($q) use ($gv) {
            $q->where('MaGV', $gv->MaGV);
        })->pluck('MaNhom')->toArray();

        $allNhomIds = array_unique(array_merge($nhomIds1, $nhomIds2));

        $baocaos = BaoCaoTienDo::whereIn('MaNhom', $allNhomIds)
                               ->with(['nhomDoAn', 'nhanXets'])
                               ->orderBy('NgayNop', 'desc')
                               ->paginate(10);
                               
        return view('giangvien.baocao.index', compact('baocaos'));
    }

    public function storeNhanXet(Request $request, $maBaoCao) {
        $request->validate(['NoiDung' => 'required']);
        
        $gv = \App\Models\GiangVien::where('MaTK', Auth::user()->MaTK)->first();
        $baocao = BaoCaoTienDo::findOrFail($maBaoCao);

        NhanXet::create([
            'MaBaoCao' => $baocao->MaBaoCao,
            'MaGV' => $gv->MaGV,
            'NoiDung' => $request->NoiDung,
            'NgayNhanXet' => date('Y-m-d')
        ]);

        $baocao->update(['TrangThai' => 'Đã nhận xét']);

        // Gửi thông báo đến nhóm
        $notiService = new \App\Services\NotificationService();
        $notiService->guiNhanXetMoi($baocao->nhomDoAn, $baocao);

        \App\Models\AuditLog::log('nhan_xet_bao_cao', 'BaoCaoTienDo', $baocao->MaBaoCao, ['MaNhom' => $baocao->MaNhom]);

        return redirect()->back()->with('success', 'Thêm nhận xét thành công!');
    }
}
