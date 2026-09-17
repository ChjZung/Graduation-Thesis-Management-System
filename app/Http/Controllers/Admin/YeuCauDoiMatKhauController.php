<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaiKhoan;
use App\Models\VaiTro;
use App\Models\YeuCauDoiMatKhau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class YeuCauDoiMatKhauController extends Controller
{
    public function index(Request $request)
    {
        // 1. Danh sách yêu cầu đổi mật khẩu từ sinh viên/giảng viên (Quên mật khẩu)
        $yeuCauChoDuyet = YeuCauDoiMatKhau::where('TrangThai', 'Chờ duyệt')
            ->orderBy('NgayGui', 'desc')
            ->get();

        $yeuCauDaXuLy = YeuCauDoiMatKhau::where('TrangThai', '!=', 'Chờ duyệt')
            ->orderBy('NgayDuyet', 'desc')
            ->take(10)
            ->get();

        // 2. Danh sách toàn bộ tài khoản để quản lý khóa/mở khóa
        $query = TaiKhoan::with('vaiTro');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('TenDangNhap', 'LIKE', "%{$s}%")
                  ->orWhere('MaTK', 'LIKE', "%{$s}%");
            });
        }

        if ($request->filled('MaVaiTro')) {
            $query->where('MaVaiTro', $request->MaVaiTro);
        }

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai == '1');
        }

        $taiKhoans = $query->orderBy('MaTK')->paginate(15);
        $vaiTros = VaiTro::all();

        return view('admin.yeucau_matkhau.index', compact(
            'yeuCauChoDuyet',
            'yeuCauDaXuLy',
            'taiKhoans',
            'vaiTros'
        ));
    }

    public function approve($id)
    {
        // Kiểm tra xem $id là MaYeuCau hay MaTK
        $yeuCau = YeuCauDoiMatKhau::find($id);

        if ($yeuCau) {
            $tk = TaiKhoan::where('TenDangNhap', $yeuCau->TenDangNhap)->first();
            if ($tk) {
                $tk->update([
                    'MatKhau'           => Hash::make('123456'),
                    'TrangThaiMatKhau'  => 'INITIAL',
                    'BatBuocDoiMatKhau' => true,
                    'SoLanDangNhapSai'  => 0,
                    'TrangThai'         => true,
                ]);
            }

            $yeuCau->update([
                'TrangThai'   => 'Đã duyệt',
                'NgayDuyet'   => now(),
                'NguoiDuyet'  => Auth::user()->TenDangNhap ?? 'admin',
            ]);

            return redirect()->back()->with('success', "Đã duyệt yêu cầu của {$yeuCau->HoTen} ({$yeuCau->TenDangNhap}) và reset mật khẩu về '123456' thành công! Người dùng sẽ được yêu cầu đổi mật khẩu mới ở lần đăng nhập tiếp theo.");
        }

        // Trường hợp reset trực tiếp từ danh sách tài khoản
        $tk = TaiKhoan::findOrFail($id);
        $tk->update([
            'MatKhau'           => Hash::make('123456'),
            'TrangThaiMatKhau'  => 'INITIAL',
            'BatBuocDoiMatKhau' => true,
            'SoLanDangNhapSai'  => 0,
            'TrangThai'         => true,
        ]);

        return redirect()->back()->with('success', "Đã reset mật khẩu tài khoản '{$tk->TenDangNhap}' về '123456' thành công! Người dùng sẽ được yêu cầu thiết lập mật khẩu cá nhân mới khi đăng nhập.");
    }

    public function reject($id)
    {
        // Nếu là yêu cầu đổi mật khẩu
        $yeuCau = YeuCauDoiMatKhau::find($id);
        if ($yeuCau) {
            $yeuCau->update([
                'TrangThai'   => 'Từ chối',
                'NgayDuyet'   => now(),
                'NguoiDuyet'  => Auth::user()->TenDangNhap ?? 'admin',
            ]);
            return redirect()->back()->with('success', "Đã từ chối yêu cầu cấp lại mật khẩu của {$yeuCau->HoTen} ({$yeuCau->TenDangNhap}).");
        }

        // Trường hợp khóa / mở khóa tài khoản
        $tk = TaiKhoan::findOrFail($id);

        if ($tk->MaTK === Auth::user()->MaTK) {
            return redirect()->back()->withErrors('Không thể tự khóa tài khoản Admin đang đăng nhập!');
        }

        $newStatus = !$tk->TrangThai;
        $tk->update([
            'TrangThai'        => $newStatus,
            'SoLanDangNhapSai' => $newStatus ? 0 : $tk->SoLanDangNhapSai, // Reset lần sai nếu mở khóa
            'NgayKhoa'         => $newStatus ? null : now(),
        ]);

        $msg = $newStatus ? "Đã mở khóa tài khoản '{$tk->TenDangNhap}'." : "Đã khóa tài khoản '{$tk->TenDangNhap}'.";
        return redirect()->back()->with('success', $msg);
    }
}
