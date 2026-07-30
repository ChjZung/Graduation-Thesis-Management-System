<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SinhVien;
use App\Models\GiangVien;
use App\Models\DeTai;
use App\Models\NhomDoAn;
use App\Models\TaiKhoan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $soSinhVien = Cache::remember('dashboard_soSinhVien', 60, function () { return SinhVien::count(); });
        $soGiangVien = Cache::remember('dashboard_soGiangVien', 60, function () { return GiangVien::count(); });
        $soDeTai = Cache::remember('dashboard_soDeTai', 60, function () { return DeTai::count(); });
        $soNhom = Cache::remember('dashboard_soNhom', 60, function () { return NhomDoAn::count(); });

        // Thống kê trạng thái nhóm
        $trangThaiNhom = Cache::remember('dashboard_trangThaiNhom', 60, function () {
            return NhomDoAn::select('TrangThai', DB::raw('count(*) as total'))
                                 ->groupBy('TrangThai')
                                 ->pluck('total', 'TrangThai')
                                 ->toArray();
        });

        // Chuẩn bị dữ liệu cho Chart.js
        $chartLabels = array_keys($trangThaiNhom);
        $chartData = array_values($trangThaiNhom);

        return view('admin.dashboard', compact(
            'soSinhVien', 'soGiangVien', 'soDeTai', 'soNhom',
            'chartLabels', 'chartData'
        ));
    }

    public function toggleLockAccount($id)
    {
        $tk = TaiKhoan::findOrFail($id);
        if ($tk->MaVaiTro == 1 && $tk->MaTK == auth()->user()->MaTK) {
            return redirect()->back()->withErrors('Không thể tự khóa tài khoản Admin đang sử dụng!');
        }

        $tk->TrangThai = !$tk->TrangThai;
        $tk->save();

        $msg = $tk->TrangThai ? 'Mở khóa tài khoản thành công!' : 'Đã khóa tài khoản thành công!';
        return redirect()->back()->with('success', $msg);
    }
}
