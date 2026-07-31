<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\DeTai;
use App\Models\DangKyDeTai;
use App\Models\NhomDoAn;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Models\PhanCongHuongDanLop;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DangKyDeTaiController extends Controller
{
    public function index(Request $request)
    {
        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->first();
        if (!$sinhVien) abort(403);

        // Tự động đóng các đề tài đã quá hạn đăng ký
        DeTai::where('TrangThai', 'Đang mở đăng ký')
            ->whereNotNull('HanDangKy')
            ->where('HanDangKy', '<', date('Y-m-d'))
            ->update(['TrangThai' => 'Đã đóng']);

        // 1. Lấy tất cả Lớp Học Phần mà sinh viên này đang tham gia (qua ghi danh hoặc qua Nhóm đồ án)
        $enrolledLhpIds = \App\Models\SinhVienLopHocPhan::where('MaSV', $sinhVien->MaSV)->pluck('MaLopHP');
        $groupLhpIds = NhomDoAn::where(function ($q) use ($sinhVien) {
            $q->where('TruongNhom', $sinhVien->MaSV)
              ->orWhereHas('thanhVienNhoms', function ($sub) use ($sinhVien) {
                  $sub->where('MaSV', $sinhVien->MaSV)->where('TrangThai', 'da_chap_nhan');
              });
        })->pluck('MaLopHP')->filter();

        $allMyLhpIds = $enrolledLhpIds->concat($groupLhpIds)->unique()->filter()->values();

        // Danh sách Lớp Học Phần sinh viên tham gia
        $myLopHocPhans = \App\Models\LopHocPhan::with(['monHoc', 'hocKy', 'giangVien'])
            ->whereIn('MaLopHP', $allMyLhpIds)
            ->orderBy('MaLopHP', 'desc')
            ->get();

        // Fallback: nếu chưa có Lớp HP nào được ghi danh, lấy tất cả Lớp HP đang mở
        if ($myLopHocPhans->isEmpty()) {
            $myLopHocPhans = \App\Models\LopHocPhan::with(['monHoc', 'hocKy', 'giangVien'])
                ->where('TrangThai', 'Đang mở')
                ->orderBy('MaLopHP', 'desc')
                ->get();
        }

        // 2. Xác định Lớp Học Phần được chọn (Mặc định chọn Lớp HP đầu tiên)
        $selectedMaLopHP = $request->query('MaLopHP');
        if (!$selectedMaLopHP && $myLopHocPhans->isNotEmpty()) {
            $selectedMaLopHP = $myLopHocPhans->first()->MaLopHP;
        }

        $currentLopHP = $selectedMaLopHP ? \App\Models\LopHocPhan::with(['monHoc', 'hocKy', 'giangVien'])->find($selectedMaLopHP) : null;

        // 3. Tìm Nhóm đồ án của sinh viên trong Lớp Học Phần được chọn này
        $nhom = null;
        if ($selectedMaLopHP) {
            $nhom = NhomDoAn::where('MaLopHP', $selectedMaLopHP)
                ->where(function ($q) use ($sinhVien) {
                    $q->where('TruongNhom', $sinhVien->MaSV)
                      ->orWhereHas('thanhVienNhoms', function ($sub) use ($sinhVien) {
                          $sub->where('MaSV', $sinhVien->MaSV)->where('TrangThai', 'da_chap_nhan');
                      });
                })
                ->first();
        }

        // 4. Lấy Đề tài CHỈ ĐƯỢC MỞ CHO LỚP HỌC PHẦN ĐƯỢC CHỌN NÀY
        $query = DeTai::where('TrangThai', 'Đang mở đăng ký')
            ->with(['giangVien', 'monHoc', 'lop', 'hocKy', 'lopHocPhan']);

        if ($selectedMaLopHP) {
            $query->where('MaLopHP', $selectedMaLopHP);
        } else {
            $query->where('MaLop', $sinhVien->MaLop);
        }

        if ($request->filled('search')) {
            $query->where('TenDeTai', 'LIKE', '%' . trim($request->search) . '%');
        }

        $detais = $query->orderBy('MaDeTai', 'desc')->paginate(10);

        // 5. Kiểm tra tình trạng đăng ký đề tài của nhóm trong Lớp Học Phần này
        $dangky = null;
        if ($nhom) {
            $dangky = DangKyDeTai::where('MaNhom', $nhom->MaNhom)
                ->with(['deTai.giangVien'])
                ->orderBy('MaDangKy', 'desc')
                ->first();
        }

        return view('sinhvien.dangky.index', compact(
            'detais', 'nhom', 'dangky', 'sinhVien', 'myLopHocPhans', 'selectedMaLopHP', 'currentLopHP'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaDeTai' => 'required|exists:de_tais,MaDeTai'
        ], [
            'MaDeTai.required' => 'Vui lòng chọn đề tài muốn đăng ký.'
        ]);

        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $deTai = DeTai::findOrFail($request->MaDeTai);

        // Tìm Nhóm của sinh viên thuộc đúng Lớp Học Phần của Đề tài được đăng ký
        $nhom = null;
        if ($deTai->MaLopHP) {
            $nhom = NhomDoAn::where('MaLopHP', $deTai->MaLopHP)
                ->where(function ($q) use ($sinhVien) {
                    $q->where('TruongNhom', $sinhVien->MaSV)
                      ->orWhereHas('thanhVienNhoms', function ($sub) use ($sinhVien) {
                          $sub->where('MaSV', $sinhVien->MaSV)->where('TrangThai', 'da_chap_nhan');
                      });
                })->first();
        }

        if (!$nhom) {
            $nhom = NhomDoAn::where('TruongNhom', $sinhVien->MaSV)->first();
        }

        if (!$nhom) {
            return redirect()->back()->withErrors('Bạn chưa tham gia nhóm nào thuộc Lớp Học Phần này! Vui lòng tạo hoặc tham gia nhóm trước.');
        }

        if ($nhom->TruongNhom != $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền đại diện đăng ký đề tài!');
        }

        // 1. Kiểm tra thành viên tối thiểu (tối thiểu 2 người)
        $soThanhVien = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)->count();
        if ($soThanhVien < 2) {
            return redirect()->back()->withErrors('Nhóm chưa đủ thành viên tối thiểu (cần tối thiểu 2 người) để đăng ký đề tài!');
        }

        // 2. Kiểm tra hạn đăng ký (HanDangKy)
        if ($deTai->HanDangKy && date('Y-m-d') > $deTai->HanDangKy) {
            $deTai->update(['TrangThai' => 'Đã đóng']);
            return redirect()->back()->withErrors("Đã quá hạn đăng ký cho đề tài này (Hạn chót: " . date('d/m/Y', strtotime($deTai->HanDangKy)) . "). Đề tài đã tự động đóng.");
        }

        // 3. Kiểm tra hai nhóm đăng ký cùng một đề tài
        $isRegistered = DangKyDeTai::where('MaDeTai', $deTai->MaDeTai)
            ->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])
            ->where('MaNhom', '!=', $nhom->MaNhom)
            ->exists();

        if ($isRegistered) {
            return redirect()->back()->withErrors('Đề tài này đã được một nhóm khác đăng ký.');
        }

        // 4. Kiểm tra nhóm trong Lớp Học Phần này đã đăng ký đề tài chưa
        $dangKyHienTai = DangKyDeTai::where('MaNhom', $nhom->MaNhom)->first();

        if ($dangKyHienTai) {
            if ($dangKyHienTai->TrangThai == 'Từ chối') {
                $dangKyHienTai->update([
                    'MaDeTai' => $deTai->MaDeTai,
                    'NgayDangKy' => date('Y-m-d'),
                    'TrangThai' => 'Chờ duyệt',
                    'LyDoTuChoi' => null
                ]);
                $nhom->update(['TrangThai' => 'Chờ duyệt đề tài']);

                AuditLog::log('dang_ky_lai_de_tai', 'DangKyDeTai', $dangKyHienTai->MaDangKy, ['MaNhom' => $nhom->MaNhom, 'MaDeTai' => $deTai->MaDeTai]);

                return redirect()->back()->with('success', 'Đăng ký lại đề tài thành công, vui lòng chờ giảng viên duyệt!');
            }
            return redirect()->back()->withErrors('Nhóm của bạn trong Lớp Học Phần này đã đăng ký một đề tài rồi!');
        }

        $dk = DangKyDeTai::create([
            'MaNhom' => $nhom->MaNhom,
            'MaDeTai' => $deTai->MaDeTai,
            'NgayDangKy' => date('Y-m-d'),
            'TrangThai' => 'Chờ duyệt'
        ]);

        $nhom->update(['TrangThai' => 'Chờ duyệt đề tài']);

        AuditLog::log('dang_ky_de_tai', 'DangKyDeTai', $dk->MaDangKy, ['MaNhom' => $nhom->MaNhom, 'MaDeTai' => $deTai->MaDeTai]);

        return redirect()->back()->with('success', 'Đăng ký đề tài thành công, vui lòng chờ giảng viên duyệt!');
    }

    public function destroy($id)
    {
        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $nhom = NhomDoAn::where('TruongNhom', $sinhVien->MaSV)->first();

        if (!$nhom) {
            return redirect()->back()->withErrors('Bạn phải là trưởng nhóm mới có quyền hủy đăng ký đề tài!');
        }

        $dangKy = DangKyDeTai::where('MaDangKy', $id)
            ->where('MaNhom', $nhom->MaNhom)
            ->firstOrFail();

        if ($dangKy->TrangThai === 'Đã duyệt') {
            return redirect()->back()->withErrors('Đề tài đã được duyệt chính thức, không thể tự hủy đăng ký! Vui lòng liên hệ giảng viên nếu muốn đổi đề tài.');
        }

        $dangKy->delete();
        $nhom->update(['TrangThai' => 'Đang hoạt động']);

        AuditLog::log('huy_dang_ky_de_tai', 'DangKyDeTai', $id, ['MaNhom' => $nhom->MaNhom]);

        return redirect()->back()->with('success', 'Đã hủy đăng ký đề tài thành công! Bạn có thể chọn đề tài mới.');
    }
}