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
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->first();

        if (!$sinhVien) {
            return redirect()->route('sinhvien.nhom.index')
                ->with('error', 'Hồ sơ sinh viên chưa được thiết lập. Vui lòng liên hệ Giáo vụ Khoa.');
        }

        // 1. Kiểm tra Nhóm của sinh viên (chỉ lấy nhóm đã tham gia chính thức)
        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->first();
        $nhom = null;
        $dangKyCurrent = null;
        $soThanhVien = 0;

        if ($thanhVienRecord) {
            $nhom = Nhom::with('thanhViens')->where('MaNhom', $thanhVienRecord->MaNhom)->first();
            if ($nhom) {
                $soThanhVien = $nhom->thanhViens->where('TrangThai', 'da_tham_gia')->count();
                $dangKyCurrent = DangKyDeTai::with('deTai.giangVien')
                    ->where('MaNhom', $nhom->MaNhom)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
        }

        // 2. Lấy danh sách Đề tài đã công bố chính thức theo Môn/Học phần
        $hocPhans = ['Khóa luận tốt nghiệp', 'Đồ án tốt nghiệp', 'Đồ án chuyên ngành'];
        $selectedHocPhan = $request->input('HocPhan');

        $query = DeTai::with(['giangVien.boMon', 'nganh'])->where('TrangThai', 'Đã công bố');

        if ($request->filled('HocPhan')) {
            $query->where('HocPhan', $request->HocPhan);
        }

        if ($request->filled('search')) {
            $query->where('TenDeTai', 'LIKE', '%' . trim($request->search) . '%');
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // 3. Lấy map các đề tài đã có nhóm đăng ký ('Chờ duyệt' hoặc 'Đã duyệt')
        $deTaiDaDangKys = DangKyDeTai::whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])
            ->with('nhom')
            ->get()
            ->keyBy('MaDeTai');

        return view('sinhvien.dangky.index', compact('detais', 'nhom', 'dangKyCurrent', 'sinhVien', 'soThanhVien', 'deTaiDaDangKys', 'hocPhans', 'selectedHocPhan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaDeTai' => 'required|exists:DeTai,MaDeTai',
        ], [
            'MaDeTai.required' => 'Vui lòng chọn đề tài muốn đăng ký.',
        ]);

        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();
        $deTai = DeTai::findOrFail($request->MaDeTai);

        // 0. Kiểm tra đề tài đã được công bố chính thức chưa
        if ($deTai->TrangThai !== 'Đã công bố') {
            return redirect()->back()->withErrors('Đề tài này chưa được Giáo vụ công bố chính thức cho sinh viên đăng ký!');
        }

        // 1. Kiểm tra sinh viên có thuộc nhóm chính thức không
        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->first();

        if (!$thanhVienRecord) {
            return redirect()->back()->withErrors('Bạn chưa có nhóm khóa luận! Vui lòng tạo nhóm hoặc gia nhập nhóm trước khi đăng ký đề tài.');
        }

        $nhom = Nhom::where('MaNhom', $thanhVienRecord->MaNhom)->firstOrFail();

        // 2. Chỉ Trưởng nhóm được đăng ký
        if ($nhom->MaTruongNhom != $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền đại diện đăng ký đề tài khóa luận!');
        }

        // 3. QUY ĐỊNH: Nhóm phải có từ 1 đến 3 thành viên chính thức và không vượt quá chỉ tiêu đề tài
        $countMembers = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)
            ->where('TrangThai', 'da_tham_gia')
            ->count();

        if ($countMembers < 1 || $countMembers > 3) {
            return redirect()->back()->withErrors("Quy định: Nhóm phải có từ 1 đến tối đa 3 thành viên để đăng ký đề tài! Hiện tại nhóm của bạn có {$countMembers} thành viên.");
        }

        $maxSV = $deTai->SoLuongSinhVienToiDa ?? 3;
        if ($countMembers > $maxSV) {
            return redirect()->back()->withErrors("Đề tài '{$deTai->TenDeTai}' chỉ tiếp nhận tối đa {$maxSV} sinh viên. Nhóm của bạn hiện có {$countMembers} thành viên!");
        }

        // 4. Kiểm tra đề tài đã được nhóm khác đăng ký chưa (Chờ duyệt hoặc Đã duyệt)
        $alreadyTaken = DangKyDeTai::where('MaDeTai', $deTai->MaDeTai)
            ->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])
            ->exists();

        if ($alreadyTaken) {
            return redirect()->back()->withErrors('Đề tài này đã có nhóm khác đăng ký. Vui lòng chọn đề tài khác!');
        }

        // 5. Kiểm tra nhóm đã đăng ký đề tài nào chưa
        $existingRegistration = DangKyDeTai::where('MaNhom', $nhom->MaNhom)
            ->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])
            ->first();

        if ($existingRegistration) {
            return redirect()->back()->withErrors('Nhóm của bạn đã gửi đơn đăng ký đề tài rồi! Vui lòng chờ phê duyệt hoặc hủy đơn cũ.');
        }

        $maDK = 'DK_' . Str::upper(Str::random(6));

        DangKyDeTai::create([
            'MaDangKy'     => $maDK,
            'MaNhom'       => $nhom->MaNhom,
            'MaDeTai'      => $deTai->MaDeTai,
            'MaGVHuongDan' => $deTai->MaGV,
            'NgayDangKy'   => now(),
            'TrangThai'    => 'Chờ duyệt',
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

    public function deXuatRieng(Request $request)
    {
        return redirect()->back()->withErrors('Quy định hiện tại: Sinh viên chỉ được đăng ký các đề tài đã công bố của Giảng viên / Khoa, không được phép tự đề xuất đề tài riêng.');
    }
}