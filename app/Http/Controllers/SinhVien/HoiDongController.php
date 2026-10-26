<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Models\Nhom;
use App\Models\HoSoBaoVe;
use App\Models\HoiDong;
use App\Models\KetQuaSinhVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HoiDongController extends Controller
{
    /**
     * Màn hình "Hội đồng bảo vệ của tôi" dành riêng cho Sinh viên
     * Bảo mật IDOR: Sinh viên chỉ được xem Hội đồng thuộc nhóm của mình
     */
    public function index()
    {
        $user = Auth::user();
        $sinhVien = SinhVien::getLoggedInSinhVien($user);
        if (!$sinhVien) {
            return redirect()->route('login');
        }

        // Tìm nhóm chính thức của sinh viên
        $tv = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->first();

        $nhom = $tv ? Nhom::with([
            'truongNhom',
            'thanhViens.sinhVien.lop',
            'deTai.giangVien.boMon',
            'hocPhan',
            'hocKy'
        ])->find($tv->MaNhom) : null;

        $hoSo = $nhom ? HoSoBaoVe::with([
            'hoiDong.thanhViens.giangVien.boMon',
            'hoiDong.hocKy',
            'hoiDong.khoa',
            'hoiDong.nganh',
            'baoCaoSauBaoVes',
        ])->where('MaNhom', $nhom->MaNhom)->first() : null;

        $hoiDong = $hoSo?->hoiDong;

        // Thành viên hội đồng được công khai cho sinh viên (Sắp xếp: Chủ tịch -> Thư ký -> Phản biện -> Ủy viên)
        $roleOrder = ['Chủ tịch' => 1, 'Thư ký' => 2, 'Phản biện' => 3, 'Ủy viên' => 4];
        $publicThanhViens = collect();
        if ($hoiDong) {
            $publicThanhViens = $hoiDong->thanhViens->sortBy(function ($t) use ($roleOrder) {
                return $roleOrder[$t->VaiTro] ?? 5;
            });
        }

        // Kết quả điểm của sinh viên hiện tại (nếu đã công bố)
        $ketQua = KetQuaSinhVien::where('MaSV', $sinhVien->MaSV)->latest()->first();
        $daCongBoKetQua = false;
        if ($ketQua && $ketQua->TrangThai === 'Đã công bố') {
            $daCongBoKetQua = true;
        } elseif ($hoSo && in_array($hoSo->TrangThaiKetQua, ['DaCongBo', 'CoYeuCauChinhSua', 'DaNopChinhSua', 'DaHoanThanh'])) {
            $daCongBoKetQua = true;
        }

        $isLeader = false;
        if ($nhom) {
            $isLeader = ($nhom->truongNhom?->MaSV === $sinhVien->MaSV)
                || ($nhom->MaTruongNhom === $sinhVien->MaSV)
                || ($tv && mb_stripos($tv->VaiTro, 'trưởng') !== false);
        }

        return view('sinhvien.hoidong.index', compact(
            'sinhVien',
            'nhom',
            'hoSo',
            'hoiDong',
            'publicThanhViens',
            'ketQua',
            'daCongBoKetQua',
            'isLeader'
        ));
    }
}
