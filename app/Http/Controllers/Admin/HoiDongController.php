<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HoiDong;
use App\Models\ThanhVienHoiDong;
use App\Models\GiangVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HoiDongController extends Controller
{
    public function index()
    {
        $hoiDongs = HoiDong::with(['thanhViens.giangVien', 'hoSoBaoVes.nhom'])
            ->orderBy('ThoiGianBatDau', 'desc')
            ->paginate(10);

        return view('admin.hoidong.index', compact('hoiDongs'));
    }

    public function create()
    {
        $giangViens = GiangVien::orderBy('HoTen')->get();
        return view('admin.hoidong.create', compact('giangViens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenHoiDong'     => 'required|string|max:200',
            'ThoiGianBatDau' => 'required|date',
            'ThoiGianKetThuc'=> 'required|date|after:ThoiGianBatDau',
            'DiaDiem'        => 'nullable|string|max:200',
            'GhiChu'         => 'nullable|string|max:255',
            'thanh_viens'    => 'required|array|min:3',
            'thanh_viens.*.MaGV'  => 'required|exists:giang_viens,MaGV',
            'thanh_viens.*.VaiTro'=> 'required|in:Chủ tịch,Thư ký,Thành viên,Phản biện',
        ], [
            'TenHoiDong.required'         => 'Vui lòng nhập tên Hội đồng.',
            'ThoiGianBatDau.required'     => 'Vui lòng chọn thời gian bắt đầu.',
            'ThoiGianKetThuc.required'    => 'Vui lòng chọn thời gian kết thúc.',
            'ThoiGianKetThuc.after'       => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'thanh_viens.required'        => 'Vui lòng thêm ít nhất 3 thành viên Hội đồng.',
            'thanh_viens.min'             => 'Hội đồng cần ít nhất 3 Giảng viên.',
        ]);

        DB::transaction(function () use ($request) {
            $count = HoiDong::count() + 1;
            $maHoiDong = 'HD' . str_pad($count, 2, '0', STR_PAD_LEFT);

            $hoiDong = HoiDong::create([
                'MaHoiDong'       => $maHoiDong,
                'TenHoiDong'      => $request->TenHoiDong,
                'ThoiGianBatDau'  => $request->ThoiGianBatDau,
                'ThoiGianKetThuc' => $request->ThoiGianKetThuc,
                'DiaDiem'         => $request->DiaDiem,
                'TrangThai'       => 'Chưa diễn ra',
                'GhiChu'          => $request->GhiChu,
            ]);

            foreach ($request->thanh_viens as $tv) {
                ThanhVienHoiDong::create([
                    'MaHoiDong' => $hoiDong->MaHoiDong,
                    'MaGV'      => $tv['MaGV'],
                    'VaiTro'    => $tv['VaiTro'],
                ]);
            }
        });

        return redirect()->route('admin.hoidong.index')
            ->with('success', 'Thành lập Hội đồng thành công!');
    }

    public function show($id)
    {
        $hoiDong = HoiDong::with(['thanhViens.giangVien', 'hoSoBaoVes.nhom.truongNhom'])
            ->findOrFail($id);
        $giangViens = GiangVien::orderBy('HoTen')->get();
        return view('admin.hoidong.show', compact('hoiDong', 'giangViens'));
    }

    public function updateTrangThai(Request $request, $id)
    {
        $hoiDong = HoiDong::findOrFail($id);
        $request->validate(['TrangThai' => 'required|in:Chưa diễn ra,Đang diễn ra,Đã kết thúc']);
        $hoiDong->update(['TrangThai' => $request->TrangThai]);
        return redirect()->back()->with('success', 'Cập nhật trạng thái Hội đồng thành công!');
    }

    public function phanCongNhom(Request $request, $id)
    {
        $request->validate([
            'MaNhom'       => 'required|exists:nhoms,MaNhom',
            'MaGVPhanBien' => 'nullable|exists:giang_viens,MaGV',
        ]);

        $hoiDong = HoiDong::findOrFail($id);

        \App\Models\HoSoBaoVe::where('MaNhom', $request->MaNhom)
            ->update([
                'MaHoiDong'    => $hoiDong->MaHoiDong,
                'MaGVPhanBien' => $request->MaGVPhanBien,
                'TrangThai'    => 'Đã phân công',
            ]);

        return redirect()->back()->with('success', "Đã phân công Nhóm vào Hội đồng {$hoiDong->TenHoiDong}!");
    }

}
