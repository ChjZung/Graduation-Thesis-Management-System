<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\DeTai;
use App\Models\DangKyDeTai;
use App\Models\Nhom;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DangKyDeTaiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();

        // 1. Kiểm tra Nhóm của sinh viên
        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->first();
        $nhom = null;
        $dangKyCurrent = null;

        if ($thanhVienRecord) {
            $nhom = Nhom::where('MaNhom', $thanhVienRecord->MaNhom)->first();
            if ($nhom) {
                $dangKyCurrent = DangKyDeTai::with('deTai.giangVien')
                    ->where('MaNhom', $nhom->MaNhom)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
        }

        // 2. Lấy danh sách Đề tài ĐÃ ĐƯỢC GIÁO VỤ PHÊ DUYỆT ('Đã duyệt')
        $query = DeTai::with('giangVien')->where('TrangThai', 'Đã duyệt');

        if ($request->filled('search')) {
            $query->where('TenDeTai', 'LIKE', '%' . trim($request->search) . '%');
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('sinhvien.dangky.index', compact('detais', 'nhom', 'dangKyCurrent', 'sinhVien'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaDeTai' => 'required|exists:de_tais,MaDeTai',
        ], [
            'MaDeTai.required' => 'Vui lòng chọn đề tài muốn đăng ký.',
        ]);

        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();
        $deTai = DeTai::findOrFail($request->MaDeTai);

        // 1. Kiểm tra sinh viên có thuộc nhóm nào không
        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->first();

        if (!$thanhVienRecord) {
            return redirect()->back()->withErrors('Bạn chưa có nhóm khóa luận! Vui lòng tạo nhóm hoặc gia nhập nhóm trước khi đăng ký đề tài.');
        }

        $nhom = Nhom::where('MaNhom', $thanhVienRecord->MaNhom)->firstOrFail();

        // 2. Kiểm tra chỉ Trưởng nhóm được đại diện đăng ký
        if ($nhom->MaTruongNhom != $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền đại diện đăng ký đề tài khóa luận!');
        }

        // 3. Kiểm tra xem đề tài đã được nhóm khác đăng ký được duyệt chưa
        $alreadyTaken = DangKyDeTai::where('MaDeTai', $deTai->MaDeTai)
            ->where('TrangThai', 'Đã duyệt')
            ->exists();

        if ($alreadyTaken) {
            return redirect()->back()->withErrors('Đề tài này đã được một nhóm khác đăng ký và duyệt chính thức!');
        }

        // 4. Kiểm tra nhóm đã đăng ký đề tài nào chưa
        $existingRegistration = DangKyDeTai::where('MaNhom', $nhom->MaNhom)
            ->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])
            ->first();

        if ($existingRegistration) {
            return redirect()->back()->withErrors('Nhóm của bạn đã gửi đơn đăng ký đề tài rồi! Vui lòng chờ phê duyệt hoặc hủy đơn cũ.');
        }

        $maDK = 'DK_' . Str::upper(Str::random(6));

        DangKyDeTai::create([
            'MaDangKy' => $maDK,
            'MaNhom' => $nhom->MaNhom,
            'MaDeTai' => $deTai->MaDeTai,
            'MaGVHuongDan' => $deTai->MaGV,
            'NgayDangKy' => now(),
            'TrangThai' => 'Chờ duyệt',
        ]);

        $nhom->update(['MaDeTai' => $deTai->MaDeTai]);

        return redirect()->back()->with('success', "Đăng ký đề tài '{$deTai->TenDeTai}' thành công! Đơn đăng ký đang chờ Giáo vụ Khoa phê duyệt.");
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();

        $dangKy = DangKyDeTai::with('nhom')->findOrFail($id);

        if ($dangKy->nhom->MaTruongNhom != $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền hủy đơn đăng ký đề tài!');
        }

        if ($dangKy->TrangThai === 'Đã duyệt') {
            return redirect()->back()->withErrors('Đơn đăng ký đề tài đã được duyệt chính thức. Bạn không thể tự hủy đơn!');
        }

        $dangKy->nhom->update(['MaDeTai' => null]);
        $dangKy->delete();

        return redirect()->back()->with('success', 'Đã hủy đơn đăng ký đề tài thành công.');
    }
}