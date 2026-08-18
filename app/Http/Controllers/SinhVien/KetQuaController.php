<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\KetQuaSinhVien;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Models\Nhom;
use Illuminate\Support\Facades\Auth;

class KetQuaController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->first();

        $ketQua = null;
        $nhom = null;

        if ($sinhVien) {
            $ketQua = KetQuaSinhVien::with('hocKy')
                ->where('MaSV', $sinhVien->MaSV)
                ->latest()->first();

            $tv = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
                ->where('TrangThai', 'da_tham_gia')->first();
            $nhom = $tv ? Nhom::with(['deTai.giangVien'])->find($tv->MaNhom) : null;
        }

        return view('sinhvien.ketqua.index', compact('sinhVien', 'ketQua', 'nhom'));
    }
}
