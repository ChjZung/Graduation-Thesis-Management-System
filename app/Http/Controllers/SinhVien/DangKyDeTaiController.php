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

        // Sinh viên chỉ được xem đề tài thuộc LỚP MÌNH (MaLop) và do Giảng viên được phân công cho lớp tạo
        $gvIds = PhanCongHuongDanLop::where('MaLop', $sinhVien->MaLop)->pluck('MaGV');
        $gvMaTKs = \App\Models\GiangVien::whereIn('MaGV', $gvIds)->pluck('MaTK');

        $query = DeTai::where('MaLop', $sinhVien->MaLop)
            ->whereIn('MaTK', $gvMaTKs)
            ->where('TrangThai', 'Đang mở đăng ký')
            ->with(['giangVien', 'monHoc', 'lop', 'hocKy']);

        if ($request->filled('MaMon')) {
            $query->where('MaMon', $request->MaMon);
        }

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('search')) {
            $query->where('TenDeTai', 'LIKE', '%' . trim($request->search) . '%');
        }

        $detais = $query->paginate(10);

        // Nhóm mà sinh viên là Trưởng nhóm (dùng để kiểm tra nút đăng ký)
        $nhom = NhomDoAn::where('TruongNhom', $sinhVien->MaSV)->first();

        // Đăng ký đề tài hiện tại của nhóm (null nếu chưa đăng ký hoặc không có nhóm)
        $dangky = null;
        if ($nhom) {
            $dangky = DangKyDeTai::where('MaNhom', $nhom->MaNhom)
                ->with(['deTai.giangVien'])
                ->orderBy('MaDangKy', 'desc')
                ->first();
        }

        $monhocs = \App\Models\MonHoc::all();
        $hockys = \App\Models\HocKy::all();

        return view('sinhvien.dangky.index', compact('detais', 'nhom', 'dangky', 'sinhVien', 'monhocs', 'hockys'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaDeTai' => 'required|exists:de_tais,MaDeTai'
        ], [
            'MaDeTai.required' => 'Vui lòng chọn đề tài muốn đăng ký.'
        ]);

        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $nhom = NhomDoAn::where('TruongNhom', $sinhVien->MaSV)->first();

        if (!$nhom) {
            return redirect()->back()->withErrors('Bạn phải là trưởng nhóm mới được đăng ký đề tài!');
        }

        // 1. Kiểm tra thành viên tối thiểu (tối thiểu 2 người)
        $soThanhVien = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)->count();
        if ($soThanhVien < 2) {
            return redirect()->back()->withErrors('Nhóm chưa đủ thành viên tối thiểu (cần tối thiểu 2 người) để đăng ký đề tài!');
        }

        $deTai = DeTai::findOrFail($request->MaDeTai);

        // 2. Kiểm tra hạn đăng ký (HanDangKy)
        if ($deTai->HanDangKy && date('Y-m-d') > $deTai->HanDangKy) {
            return redirect()->back()->withErrors("Đã quá hạn đăng ký cho đề tài này (Hạn chót: " . date('d/m/Y', strtotime($deTai->HanDangKy)) . ").");
        }

        // 3. Kiểm tra hai nhóm đăng ký cùng một đề tài
        $isRegistered = DangKyDeTai::where('MaDeTai', $deTai->MaDeTai)
            ->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])
            ->where('MaNhom', '!=', $nhom->MaNhom)
            ->exists();

        if ($isRegistered) {
            return redirect()->back()->withErrors('Đề tài đã được đăng ký.');
        }

        // 4. Kiểm tra nhóm đăng ký nhiều đề tài
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
            return redirect()->back()->withErrors('Nhóm của bạn đã đăng ký một đề tài khác rồi!');
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
}