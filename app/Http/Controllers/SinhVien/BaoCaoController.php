<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\BaoCaoTienDo;
use App\Models\NhomDoAn;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Models\AuditLog;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaoCaoController extends Controller
{
    public function index(Request $request)
    {
        $sv = SinhVien::where('MaTK', Auth::user()->MaTK)->first();
        if (!$sv) abort(403);

        $nhomIds = ThanhVienNhom::where('MaSV', $sv->MaSV)->whereIn('TrangThai', ['da_chap_nhan', 'da_tham_gia'])->pluck('MaNhom')->toArray();
        if (empty($nhomIds)) {
            return redirect()->route('sinhvien.nhom.index')->withErrors('Bạn chưa tham gia nhóm nào.');
        }

        $selectedNhomId = $request->get('maNhom', $nhomIds[0] ?? null);
        $nhom = NhomDoAn::with(['dangKyDeTai.deTai', 'monHoc', 'hocKy'])->whereIn('MaNhom', $nhomIds)->where('MaNhom', $selectedNhomId)->first();
        if (!$nhom) {
            $nhom = NhomDoAn::with(['dangKyDeTai.deTai', 'monHoc', 'hocKy'])->find($nhomIds[0]);
        }

        $allNhoms = NhomDoAn::whereIn('MaNhom', $nhomIds)->get();
        $baocaos = BaoCaoTienDo::with('nhanXets')->where('MaNhom', $nhom->MaNhom)->orderBy('LanBaoCao', 'desc')->get();

        return view('sinhvien.baocao.index', compact('nhom', 'allNhoms', 'baocaos', 'sv'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'NoiDung' => 'required|string',
            'FileUpLoad' => 'nullable|file|max:20480',
            'FileBaoCao' => 'nullable|string',
            'MaNhom' => 'nullable|exists:nhom_do_ans,MaNhom'
        ], [
            'NoiDung.required' => 'Vui lòng nhập tóm tắt nội dung báo cáo tiến độ.'
        ]);

        $sv = SinhVien::where('MaTK', Auth::user()->MaTK)->first();

        // Đọc MaNhom từ form truyền lên (nếu không có, lấy nhóm đầu tiên)
        $maNhom = $request->input('MaNhom');
        if (!$maNhom) {
            $thanhVien = ThanhVienNhom::where('MaSV', $sv->MaSV)->whereIn('TrangThai', ['da_tham_gia', 'da_chap_nhan'])->first();
            $maNhom = $thanhVien->MaNhom ?? null;
        }

        if (!$maNhom) {
            return redirect()->back()->withErrors('Bạn chưa tham gia nhóm nào!');
        }

        // Kiểm tra sinh viên có thuộc nhóm này không
        $isMember = ThanhVienNhom::where('MaSV', $sv->MaSV)->where('MaNhom', $maNhom)->whereIn('TrangThai', ['da_tham_gia', 'da_chap_nhan'])->exists();
        if (!$isMember) {
            return redirect()->back()->withErrors('Bạn không thuộc nhóm này!');
        }

        $nhom = NhomDoAn::with('dangKyDeTai.deTai')->findOrFail($maNhom);

        if ($nhom->TruongNhom != $sv->MaSV) {
            return redirect()->back()->withErrors('Chỉ trưởng nhóm mới được phép nộp báo cáo!');
        }

        // 1. Kiểm tra đề tài được duyệt & hạn nộp báo cáo (HanBaoCao)
        $dangKy = $nhom->dangKyDeTai ?? null;
        if (!$dangKy || $dangKy->TrangThai != 'Đã duyệt') {
            return redirect()->back()->withErrors('Nhóm chưa có đề tài được duyệt! Vui lòng đăng ký và chờ duyệt đề tài trước khi nộp báo cáo.');
        }

        $deTai = $dangKy->deTai ?? null;
        if ($deTai && $deTai->HanBaoCao && date('Y-m-d') > $deTai->HanBaoCao) {
            return redirect()->back()->withErrors('Đã quá hạn nộp báo cáo tiến độ! (Hạn chót: ' . date('d/m/Y', strtotime($deTai->HanBaoCao)) . ')');
        }

        // 2. Upload file / link - Không cho nộp thiếu file/link
        $fileService = new FileUploadService();
        $fileOrLink = $fileService->handleUploadOrLink($request, 'FileUpLoad', 'FileBaoCao', 'baocao');

        if (!$fileOrLink) {
            return redirect()->back()->withErrors('Không cho nộp báo cáo thiếu file hoặc link đính kèm!');
        }

        // 3. Tính số lần báo cáo và kiểm tra giới hạn 5 lần
        $lanCuoi = BaoCaoTienDo::where('MaNhom', $nhom->MaNhom)->max('LanBaoCao');
        $lanBaoCao = $lanCuoi ? $lanCuoi + 1 : 1;

        if ($lanBaoCao > 5) {
            return redirect()->back()->withErrors('Nhóm đã đạt giới hạn tối đa 5 lần nộp báo cáo tiến độ!');
        }

        // 4. Kiểm tra trùng lần báo cáo
        if (BaoCaoTienDo::where('MaNhom', $nhom->MaNhom)->where('LanBaoCao', $lanBaoCao)->exists()) {
            return redirect()->back()->withErrors("Báo cáo lần {$lanBaoCao} đã tồn tại trong hệ thống!");
        }

        $bc = BaoCaoTienDo::create([
            'MaNhom' => $nhom->MaNhom,
            'LanBaoCao' => $lanBaoCao,
            'NoiDung' => $request->NoiDung,
            'FileBaoCao' => $fileOrLink,
            'TrangThai' => 'Chờ nhận xét',
            'NgayNop' => date('Y-m-d')
        ]);

        // Gửi thông báo cho Giảng viên hướng dẫn
        if ($deTai && $deTai->MaTK) {
            $notiService = new NotificationService();
            $notiService->guiBaoCaoMoiChoGV($nhom, $deTai->MaTK, $bc);
        }

        AuditLog::log('nop_bao_cao', 'BaoCaoTienDo', $bc->MaBaoCao, ['MaNhom' => $nhom->MaNhom, 'LanBaoCao' => $lanBaoCao]);

        return redirect()->route('sinhvien.baocao.index', ['maNhom' => $nhom->MaNhom])->with('success', "Nộp báo cáo tiến độ lần {$lanBaoCao} thành công!");
    }
}
