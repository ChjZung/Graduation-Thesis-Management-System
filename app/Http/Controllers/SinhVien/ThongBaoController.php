<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\ThongBao;
use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThongBaoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sv = \App\Models\SinhVien::where('MaTK', $user->MaTK)->first();

        if (!$sv) {
            $thongbaos = collect();
            return view('sinhvien.thongbao.index', compact('thongbaos'));
        }

        // 1. Admin accounts
        $adminTKs = TaiKhoan::where('MaVaiTro', 1)->pluck('MaTK')->toArray();

        // 2. Student's registered Lớp Học Phần IDs
        $studentLhpIds = \App\Models\SinhVienLopHocPhan::where('MaSV', $sv->MaSV)->pluck('MaLopHP')->toArray();

        // 3. Lecturers assigned to student's Lớp Hành chính or Lớp Học Phần
        $lecturerGvIdsFromLh = \App\Models\PhanCongHuongDanLop::where('MaLop', $sv->MaLop)->pluck('MaGV')->toArray();
        $lecturerGvIdsFromLhp = \App\Models\LopHocPhan::whereIn('MaLopHP', $studentLhpIds)->whereNotNull('MaGV')->pluck('MaGV')->toArray();
        $allGvIds = array_unique(array_merge($lecturerGvIdsFromLh, $lecturerGvIdsFromLhp));
        $lecturerTKs = \App\Models\GiangVien::whereIn('MaGV', $allGvIds)->pluck('MaTK')->toArray();

        $thongbaos = ThongBao::where(function($query) use ($adminTKs, $sv, $studentLhpIds, $lecturerTKs) {
                                $query->whereIn('MaTK', $adminTKs);
                                if ($sv->MaLop) {
                                    $query->orWhere('MaLop', $sv->MaLop);
                                }
                                if (!empty($studentLhpIds)) {
                                    $query->orWhereIn('MaLopHP', $studentLhpIds);
                                }
                                if (!empty($lecturerTKs)) {
                                    $query->orWhere(function($subQ) use ($lecturerTKs) {
                                        $subQ->whereIn('MaTK', $lecturerTKs)
                                             ->whereNull('MaLop')
                                             ->whereNull('MaLopHP');
                                    });
                                }
                            })
                            ->with(['taiKhoan.giangVien', 'lop', 'lopHocPhan'])
                            ->orderBy('MaThongBao', 'desc')
                            ->paginate(15);

        return view('sinhvien.thongbao.index', compact('thongbaos'));
    }

    public function markRead($id)
    {
        $tb = ThongBao::find($id);
        if ($tb) {
            $tb->update(['DaDoc' => true]);
        }
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        $user = Auth::user();
        $sv = \App\Models\SinhVien::where('MaTK', $user->MaTK)->first();

        if ($sv) {
            $adminTKs = TaiKhoan::where('MaVaiTro', 1)->pluck('MaTK')->toArray();
            $studentLhpIds = \App\Models\SinhVienLopHocPhan::where('MaSV', $sv->MaSV)->pluck('MaLopHP')->toArray();

            ThongBao::where(function($query) use ($adminTKs, $sv, $studentLhpIds) {
                $query->whereIn('MaTK', $adminTKs);
                if ($sv->MaLop) {
                    $query->orWhere('MaLop', $sv->MaLop);
                }
                if (!empty($studentLhpIds)) {
                    $query->orWhereIn('MaLopHP', $studentLhpIds);
                }
            })->update(['DaDoc' => true]);
        }

        return redirect()->back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc!');
    }
}
