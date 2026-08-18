<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\HoSoBaoVe;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Models\Nhom;
use App\Models\BaoCaoTienDo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HoSoBaoVeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->first();

        if (!$sinhVien) {
            return redirect()->route('sinhvien.nhom.index')
                ->with('error', 'Bạn chưa có hồ sơ sinh viên.');
        }

        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')->first();

        $nhom = $thanhVienRecord ? Nhom::with(['deTai.giangVien'])->find($thanhVienRecord->MaNhom) : null;

        // Kiểm tra đã hoàn thành Mốc 5 chưa
        $moc5Dat = $nhom ? BaoCaoTienDo::where('MaNhom', $nhom->MaNhom)
            ->where('LanBaoCao', 5)->where('TrangThai', 'Đạt')->exists() : false;

        $hoSo = $nhom ? HoSoBaoVe::with(['hoiDong', 'giangVienPhanBien'])
            ->where('MaNhom', $nhom->MaNhom)->first() : null;


        return view('sinhvien.hoso_baove.index', compact('sinhVien', 'nhom', 'hoSo', 'moc5Dat'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TyLeTrungLap'   => 'required|numeric|min:0|max:100',
            'MinhChungDaoVan'=> 'nullable|file|mimes:pdf|max:20480',
        ], [
            'TyLeTrungLap.required' => 'Vui lòng nhập tỷ lệ trùng lặp từ Turnitin.',
            'TyLeTrungLap.numeric'  => 'Tỷ lệ trùng lặp phải là số.',
            'TyLeTrungLap.max'      => 'Tỷ lệ trùng lặp không thể vượt quá 100%.',
        ]);

        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();
        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')->firstOrFail();
        $nhom = Nhom::findOrFail($thanhVienRecord->MaNhom);

        // Chỉ Trưởng nhóm mới được nộp hồ sơ
        if ($nhom->MaTruongNhom !== $sinhVien->MaSV) {
            return back()->with('error', 'Chỉ Trưởng nhóm mới được nộp hồ sơ bảo vệ.');
        }

        // Kiểm tra đã có hồ sơ chưa
        if (HoSoBaoVe::where('MaNhom', $nhom->MaNhom)->exists()) {
            return back()->with('error', 'Nhóm đã nộp hồ sơ bảo vệ rồi.');
        }

        DB::transaction(function () use ($request, $nhom) {
            $duongDan = null;
            if ($request->hasFile('MinhChungDaoVan')) {
                $duongDan = $request->file('MinhChungDaoVan')
                    ->store("hoso/{$nhom->MaNhom}", 'public');
            }

            $count = HoSoBaoVe::count() + 1;
            HoSoBaoVe::create([
                'MaHoSo'         => 'HS' . str_pad($count, 2, '0', STR_PAD_LEFT),
                'MaNhom'         => $nhom->MaNhom,
                'TyLeTrungLap'   => $request->TyLeTrungLap,
                'MinhChungDaoVan'=> $duongDan,
                'TrangThai'      => 'Chờ xác nhận',
                'NgayNop'        => now()->toDateString(),
                'GhiChu'         => $request->GhiChu,
            ]);
        });

        return redirect()->route('sinhvien.hoso.index')
            ->with('success', 'Nộp hồ sơ bảo vệ thành công! Vui lòng chờ Giáo vụ xác nhận và phân công Hội đồng.');
    }
}
