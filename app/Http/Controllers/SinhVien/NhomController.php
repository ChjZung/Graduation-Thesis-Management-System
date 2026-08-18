<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\SinhVien;
use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NhomController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sinhVien = SinhVien::with('lop')->where('MaTK', $user->MaTK)->first();

        if (!$sinhVien) {
            $firstLop = \App\Models\Lop::first();
            $maSV = 'SV_' . Str::upper(Str::random(5));
            $sinhVien = SinhVien::create([
                'MaSV' => $maSV,
                'MaTK' => $user->MaTK,
                'MaLop' => $firstLop->MaLop ?? 'L01',
                'HoTen' => $user->TenDangNhap,
                'Email' => $user->TenDangNhap . '@st.huit.edu.vn',
                'TrangThai' => 'Đang học'
            ]);
        }


        // 1. Kiểm tra nhóm mà sinh viên đang tham gia chính thức ('da_tham_gia')
        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->first();

        $nhomCurrent = null;

        if ($thanhVienRecord) {
            $nhomCurrent = Nhom::with(['deTai.giangVien', 'truongNhom'])
                ->where('MaNhom', $thanhVienRecord->MaNhom)
                ->first();

            if ($nhomCurrent) {
                // Nạp danh sách thành viên chính thức
                $nhomCurrent->thanhViens = ThanhVienNhom::with('sinhVien.lop')
                    ->where('MaNhom', $nhomCurrent->MaNhom)
                    ->where('TrangThai', 'da_tham_gia')
                    ->get();
            }
        }

        // 2. Lời mời gia nhập nhóm đang chờ xác nhận ('cho_xac_nhan')
        $loiMois = ThanhVienNhom::with(['nhom.truongNhom'])
            ->where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'cho_xac_nhan')
            ->get();

        return view('sinhvien.nhom.index', compact('sinhVien', 'nhomCurrent', 'loiMois'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenNhom' => 'required|string|max:100',
        ], [
            'TenNhom.required' => 'Vui lòng nhập tên nhóm khóa luận.',
        ]);

        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();

        // Kiểm tra SV đã ở trong nhóm nào chính thức chưa
        $alreadyInGroup = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->exists();

        if ($alreadyInGroup) {
            return redirect()->back()->withErrors('Bạn đã thuộc một nhóm khóa luận rồi!');
        }

        DB::transaction(function () use ($request, $sinhVien) {
            $count = Nhom::count() + 1;
            $maNhom = 'N' . sprintf('%02d', $count);
            while (Nhom::where('MaNhom', $maNhom)->exists()) {
                $count++;
                $maNhom = 'N' . sprintf('%02d', $count);
            }


            Nhom::create([
                'MaNhom' => $maNhom,
                'TenNhom' => $request->TenNhom,
                'MaTruongNhom' => $sinhVien->MaSV,
                'TrangThai' => 'Đang hoạt động',
                'NgayTao' => now(),
            ]);

            ThanhVienNhom::create([
                'MaNhom' => $maNhom,
                'MaSV' => $sinhVien->MaSV,
                'VaiTro' => 'Trưởng nhóm',
                'TrangThai' => 'da_tham_gia',
                'NgayThamGia' => now(),
            ]);
        });

        return redirect()->route('sinhvien.nhom.index')->with('success', "Tạo nhóm '{$request->TenNhom}' thành công!");
    }

    public function moiThanhVien(Request $request)
    {
        $request->validate([
            'MaNhom' => 'required|exists:nhoms,MaNhom',
            'MSSV_Them' => 'required|string',
        ], [
            'MSSV_Them.required' => 'Vui lòng nhập Tên đăng nhập / MSSV của thành viên.',
        ]);

        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $nhom = Nhom::findOrFail($request->MaNhom);

        if ($nhom->MaTruongNhom != $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền gửi lời mời thành viên!');
        }

        // Kiểm tra giới hạn tối đa 3 sinh viên/nhóm
        $count = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)
            ->where('TrangThai', 'da_tham_gia')
            ->count();

        if ($count >= 3) {
            return redirect()->back()->withErrors('Nhóm khóa luận đã đạt số lượng tối đa 3 thành viên theo quy định!');
        }

        $tkThem = TaiKhoan::where('TenDangNhap', trim($request->MSSV_Them))->first();
        if (!$tkThem) {
            return redirect()->back()->withErrors('Không tìm thấy sinh viên nào có tên đăng nhập/MSSV này.');
        }

        $svThem = SinhVien::where('MaTK', $tkThem->MaTK)->first();
        if (!$svThem) {
            return redirect()->back()->withErrors('Không tìm thấy sinh viên tương ứng.');
        }

        if ($svThem->MaSV === $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Bạn không thể tự mời chính mình!');
        }

        // Kiểm tra xem SV được mời đã ở trong nhóm nào chính thức chưa
        $alreadyInGroup = ThanhVienNhom::where('MaSV', $svThem->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->exists();

        if ($alreadyInGroup) {
            return redirect()->back()->withErrors("Sinh viên {$svThem->HoTen} đã thuộc một nhóm khóa luận khác!");
        }

        // Kiểm tra lời mời trùng lặp
        $existingInvite = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)
            ->where('MaSV', $svThem->MaSV)
            ->where('TrangThai', 'cho_xac_nhan')
            ->exists();

        if ($existingInvite) {
            return redirect()->back()->withErrors('Lời mời đến sinh viên này đã được gửi trước đó.');
        }

        ThanhVienNhom::create([
            'MaNhom' => $nhom->MaNhom,
            'MaSV' => $svThem->MaSV,
            'VaiTro' => 'Thành viên',
            'TrangThai' => 'cho_xac_nhan',
            'NgayThamGia' => null,
        ]);

        return redirect()->back()->with('success', "Đã gửi lời mời gia nhập nhóm đến sinh viên {$svThem->HoTen}!");
    }

    public function xacNhanLoiMoi($maNhom)
    {
        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $thanhVien = ThanhVienNhom::where('MaNhom', $maNhom)
            ->where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'cho_xac_nhan')
            ->firstOrFail();

        $count = ThanhVienNhom::where('MaNhom', $maNhom)
            ->where('TrangThai', 'da_tham_gia')
            ->count();

        if ($count >= 3) {
            $thanhVien->delete();
            return redirect()->back()->withErrors('Không thể gia nhập: Nhóm đã đủ 3 thành viên!');
        }

        $thanhVien->update([
            'TrangThai' => 'da_tham_gia',
            'NgayThamGia' => now(),
        ]);

        return redirect()->route('sinhvien.nhom.index')->with('success', 'Bạn đã tham gia nhóm thành công!');
    }

    public function tuChoiLoiMoi($maNhom)
    {
        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $thanhVien = ThanhVienNhom::where('MaNhom', $maNhom)
            ->where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'cho_xac_nhan')
            ->firstOrFail();

        $thanhVien->delete();

        return redirect()->back()->with('success', 'Đã từ chối lời mời gia nhập nhóm.');
    }
}