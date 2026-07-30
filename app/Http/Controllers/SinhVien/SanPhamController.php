<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use App\Models\NhomDoAn;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Models\AuditLog;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SanPhamController extends Controller
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
        $nhom = NhomDoAn::with(['dangKyDeTai.deTai', 'monHoc', 'hocKy', 'chamDiem'])->whereIn('MaNhom', $nhomIds)->where('MaNhom', $selectedNhomId)->first();
        if (!$nhom) {
            $nhom = NhomDoAn::with(['dangKyDeTai.deTai', 'monHoc', 'hocKy', 'chamDiem'])->find($nhomIds[0]);
        }

        $allNhoms = NhomDoAn::whereIn('MaNhom', $nhomIds)->get();
        $sanphams = SanPham::where('MaNhom', $nhom->MaNhom)->orderBy('NgayNop', 'desc')->get();

        return view('sinhvien.sanpham.index', compact('nhom', 'allNhoms', 'sanphams', 'sv'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenSanPham' => 'required|string|max:200',
            'FileUpLoad' => 'nullable|file|max:20480',
            'LinkFile' => 'nullable|string'
        ], [
            'TenSanPham.required' => 'Vui lòng nhập tên sản phẩm / source code.'
        ]);

        $sv = SinhVien::where('MaTK', Auth::user()->MaTK)->first();
        $thanhVien = ThanhVienNhom::where('MaSV', $sv->MaSV)->whereIn('TrangThai', ['da_tham_gia', 'da_chap_nhan'])->first();

        if (!$thanhVien) {
            return redirect()->back()->withErrors('Bạn chưa tham gia nhóm nào!');
        }

        $nhom = NhomDoAn::with('dangKyDeTai.deTai')->findOrFail($thanhVien->MaNhom);

        if ($nhom->TruongNhom != $sv->MaSV) {
            return redirect()->back()->withErrors('Chỉ trưởng nhóm mới được nộp sản phẩm!');
        }

        // 1. Kiểm tra đề tài đã duyệt chưa
        $dangKy = $nhom->dangKyDeTai;
        if (!$dangKy || $dangKy->TrangThai != 'Đã duyệt') {
            return redirect()->back()->withErrors('Không cho nộp sản phẩm khi đề tài chưa được duyệt!');
        }

        // 2. Kiểm tra hạn nộp sản phẩm (HanNopSanPham)
        $deTai = $dangKy->deTai;
        if ($deTai && $deTai->HanNopSanPham && date('Y-m-d') > $deTai->HanNopSanPham) {
            return redirect()->back()->withErrors('Đã quá hạn nộp sản phẩm cuối kỳ! (Hạn chót: ' . date('d/m/Y', strtotime($deTai->HanNopSanPham)) . ')');
        }

        $fileService = new FileUploadService();
        $fileOrLink = $fileService->handleUploadOrLink($request, 'FileUpLoad', 'LinkFile', 'sanpham');

        if (!$fileOrLink) {
            return redirect()->back()->withErrors('Vui lòng nhập Link hoặc Tải lên File đính kèm sản phẩm.');
        }

        $sp = SanPham::create([
            'MaNhom' => $nhom->MaNhom,
            'TenSanPham' => $request->TenSanPham,
            'LinkFile' => $fileOrLink,
            'NgayNop' => date('Y-m-d')
        ]);

        $nhom->update(['TrangThai' => 'Đã nộp sản phẩm']);

        // Gửi thông báo cho Giảng viên hướng dẫn
        if ($deTai && $deTai->MaTK) {
            $notiService = new NotificationService();
            $notiService->guiSanPhamMoiChoGV($nhom, $deTai->MaTK, $sp);
        }

        AuditLog::log('nop_san_pham', 'SanPham', $sp->MaSanPham ?? null, ['MaNhom' => $nhom->MaNhom, 'TenSanPham' => $sp->TenSanPham]);

        return redirect()->back()->with('success', 'Nộp sản phẩm đồ án thành công!');
    }

    /**
     * Cho phép cập nhật sản phẩm trước hạn nộp
     */
    public function update(Request $request, $id)
    {
        $sp = SanPham::findOrFail($id);
        $sv = SinhVien::where('MaTK', Auth::user()->MaTK)->first();

        $nhom = NhomDoAn::with('dangKyDeTai.deTai')->findOrFail($sp->MaNhom);
        if ($nhom->TruongNhom != $sv->MaSV) {
            return redirect()->back()->withErrors('Chỉ trưởng nhóm mới được cập nhật sản phẩm!');
        }

        $deTai = $nhom->dangKyDeTai->deTai ?? null;
        if ($deTai && $deTai->HanNopSanPham && date('Y-m-d') > $deTai->HanNopSanPham) {
            return redirect()->back()->withErrors('Đã hết hạn nộp sản phẩm, không thể cập nhật!');
        }

        $fileService = new FileUploadService();
        $fileOrLink = $fileService->handleUploadOrLink($request, 'FileUpLoad', 'LinkFile', 'sanpham');

        $dataUpdate = ['TenSanPham' => $request->TenSanPham ?? $sp->TenSanPham];
        if ($fileOrLink) {
            $dataUpdate['LinkFile'] = $fileOrLink;
        }

        $sp->update($dataUpdate);

        AuditLog::log('cap_nhat_san_pham', 'SanPham', $sp->MaSanPham ?? $sp->id, ['MaNhom' => $nhom->MaNhom]);

        return redirect()->back()->with('success', 'Cập nhật sản phẩm thành công!');
    }
}
