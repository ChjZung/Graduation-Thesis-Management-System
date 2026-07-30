<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\ChamDiem;
use App\Models\HuongDan;
use App\Models\NhomDoAn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChamDiemController extends Controller
{
    public function index() {
        $gv = \App\Models\GiangVien::where('MaTK', Auth::user()->MaTK)->first();
        if (!$gv) abort(403);

        $nhomIds = HuongDan::where('MaGV', $gv->MaGV)->pluck('MaNhom');
        
        // Lấy các nhóm do Giảng viên này hướng dẫn đã nộp sản phẩm hoặc đã được chấm điểm
        $nhoms = NhomDoAn::whereIn('MaNhom', $nhomIds)
                         ->where(function($q) {
                             $q->where('TrangThai', 'Đã nộp sản phẩm')
                               ->orWhereHas('chamDiem');
                         })
                         ->with(['sanPhams', 'chamDiem'])
                         ->get();

        return view('giangvien.chamdiem.index', compact('nhoms'));
    }

    public function store(Request $request, $maNhom) {
        $request->validate([
            'DiemBaoCao' => 'required|numeric|min:0|max:10',
            'DiemBaoVe' => 'required|numeric|min:0|max:10',
            'NhanXet' => 'nullable|string'
        ]);

        $gv = \App\Models\GiangVien::where('MaTK', Auth::user()->MaTK)->first();

        // Tính điểm tổng trọng số 50 - 50
        $diemTong = ($request->DiemBaoCao * 0.5) + ($request->DiemBaoVe * 0.5);

        try {
            DB::beginTransaction();

            $cd = ChamDiem::updateOrCreate(
                ['MaNhom' => $maNhom, 'MaGV' => $gv->MaGV],
                [
                    'LoaiCham' => 'Cuối kỳ',
                    'DiemBaoCao' => $request->DiemBaoCao,
                    'DiemBaoVe' => $request->DiemBaoVe,
                    'DiemTong' => $diemTong,
                    'NhanXet' => $request->NhanXet,
                    'NgayCham' => date('Y-m-d')
                ]
            );

            $nhom = NhomDoAn::find($maNhom);
            $nhom->update(['TrangThai' => 'Đã có điểm']);

            DB::commit();

            // Gửi thông báo đến nhóm
            $notiService = new \App\Services\NotificationService();
            $notiService->guiDiemMoi($nhom, $cd);

            \App\Models\AuditLog::log('cham_diem', 'ChamDiem', $cd->id ?? null, ['MaNhom' => $maNhom, 'DiemTong' => $diemTong]);

            return redirect()->back()->with('success', 'Chấm điểm thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Lỗi khi chấm điểm: ' . $e->getMessage());
            return redirect()->back()->withErrors('Có lỗi khi chấm điểm, vui lòng thử lại.');
        }
    }
}
