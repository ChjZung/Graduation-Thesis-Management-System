<?php
namespace App\Http\Controllers\GiangVien;
use App\Http\Controllers\Controller;
use App\Models\DangKyDeTai;
use App\Models\DeTai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DuyetDeTaiController extends Controller
{
    public function index() {
        $maTK = Auth::user()->MaTK;
        // Lấy các đăng ký thuộc về đề tài của Giảng viên này
        $dangkys = DangKyDeTai::whereHas('deTai', function($query) use ($maTK) {
            $query->where('MaTK', $maTK);
        })->with(['nhomDoAn', 'deTai'])->paginate(10);
        return view('giangvien.duyet.index', compact('dangkys'));
    }
    
    public function update(Request $request, $id) {
        $dangky = DangKyDeTai::findOrFail($id);
        // Xác minh quyền
        if ($dangky->deTai->MaTK != Auth::user()->MaTK) {
            abort(403);
        }
        
        $trangThai = $request->input('TrangThai');
        $lyDoTuChoi = $request->input('LyDoTuChoi');

        if (in_array($trangThai, ['Đã duyệt', 'Từ chối'])) {
            $dangky->update([
                'TrangThai' => $trangThai,
                'NgayDuyet' => date('Y-m-d'),
                'LyDoTuChoi' => $trangThai == 'Từ chối' ? $lyDoTuChoi : null,
            ]);
            
            $nhom = $dangky->nhomDoAn;
            $notiService = new \App\Services\NotificationService();

            if ($trangThai == 'Đã duyệt') {
                $nhom->update(['TrangThai' => 'Đã có đề tài']);
                
                $gv = \App\Models\GiangVien::where('MaTK', Auth::user()->MaTK)->first();
                if ($gv) {
                    \App\Models\HuongDan::firstOrCreate([
                        'MaNhom' => $dangky->MaNhom,
                        'MaGV' => $gv->MaGV,
                        'MaDeTai' => $dangky->MaDeTai
                    ], [
                        'NgayPhanCong' => date('Y-m-d'),
                        'TrangThai' => 'Đang hướng dẫn'
                    ]);
                }

                $notiService->guiDeTaiDuocDuyet($nhom, $dangky->deTai);
                \App\Models\AuditLog::log('duyet_de_tai', 'DangKyDeTai', $dangky->MaDangKy, ['MaNhom' => $nhom->MaNhom, 'MaDeTai' => $dangky->MaDeTai]);
            } else {
                $notiService->guiDeTaiBiTuChoi($nhom, $dangky->deTai, $lyDoTuChoi ?? 'Không đạt yêu cầu');
                \App\Models\AuditLog::log('tu_choi_de_tai', 'DangKyDeTai', $dangky->MaDangKy, ['MaNhom' => $nhom->MaNhom, 'LyDo' => $lyDoTuChoi]);
            }
        }
        
        return redirect()->back()->with('success', 'Cập nhật trạng thái thành công!');
    }
}