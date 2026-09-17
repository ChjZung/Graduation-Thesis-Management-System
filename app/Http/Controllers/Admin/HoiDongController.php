<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HoiDong;
use App\Models\ThanhVienHoiDong;
use App\Models\GiangVien;
use App\Models\HoSoBaoVe;
use App\Models\PhanCongPhanBien;
use App\Helpers\IdGenerator;
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
            'thanh_viens.*.MaGV'  => 'required|exists:GiangVien,MaGV',
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
            $maHoiDong = IdGenerator::nextHoiDong();

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
        $hoiDong = HoiDong::with([
            'thanhViens.giangVien',
            'hoSoBaoVes.nhom.truongNhom',
            'hoSoBaoVes.nhom.deTai',
            'hoSoBaoVes.phanCongPhanBien.giangVien',
            'hoSoBaoVes.tepHoSoBaoVes',
        ])->findOrFail($id);

        $giangViens = GiangVien::orderBy('HoTen')->get();

        // Nhóm đủ điều kiện chưa được phân công hoặc đang ở hội đồng này
        $nhomDuDieuKien = HoSoBaoVe::with(['nhom.truongNhom', 'nhom.deTai'])
            ->where(function ($q) use ($id) {
                $q->whereNull('MaHoiDong')
                  ->orWhere('MaHoiDong', $id);
            })
            ->whereIn('TrangThai', ['Đủ điều kiện bảo vệ', 'Đã phân công'])
            ->get();

        return view('admin.hoidong.show', compact('hoiDong', 'giangViens', 'nhomDuDieuKien'));
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
            'MaNhom'        => 'required|exists:Nhom,MaNhom',
            'MaGVPhanBien'  => 'nullable|exists:GiangVien,MaGV',
            'ThoiGianBaoVe' => 'nullable|date',
            'PhongBaoVe'    => 'nullable|string|max:100',
        ]);

        $hoiDong = HoiDong::findOrFail($id);
        $hoSo = HoSoBaoVe::where('MaNhom', $request->MaNhom)->first();

        if (!$hoSo) {
            return redirect()->back()->with('error', 'Nhóm này chưa nộp hồ sơ bảo vệ.');
        }

        DB::transaction(function () use ($request, $hoiDong, $hoSo) {
            $updateData = [
                'MaHoiDong' => $hoiDong->MaHoiDong,
                'TrangThai' => 'Đã phân công',
            ];
            if ($request->filled('ThoiGianBaoVe')) {
                $updateData['ThoiGianBaoVe'] = $request->ThoiGianBaoVe;
            } elseif (!$hoSo->ThoiGianBaoVe && $hoiDong->ThoiGianBatDau) {
                $updateData['ThoiGianBaoVe'] = $hoiDong->ThoiGianBatDau;
            }

            if ($request->filled('PhongBaoVe')) {
                $updateData['PhongBaoVe'] = $request->PhongBaoVe;
            } elseif (!$hoSo->PhongBaoVe && $hoiDong->DiaDiem) {
                $updateData['PhongBaoVe'] = $hoiDong->DiaDiem;
            }

            $hoSo->update($updateData);

            if ($request->filled('MaGVPhanBien') && $hoSo->MaDeTai) {
                $pc = PhanCongPhanBien::where('MaDeTai', $hoSo->MaDeTai)->first();
                if ($pc) {
                    $pc->update([
                        'MaGV'         => $request->MaGVPhanBien,
                        'NgayPhanCong' => now()->toDateString(),
                        'TrangThai'    => 'Đã phân công',
                    ]);
                } else {
                    PhanCongPhanBien::create([
                        'MaPhanCong'   => IdGenerator::nextPhanCong(),
                        'VaiTro'       => 'Phản biện',
                        'NgayPhanCong' => now()->toDateString(),
                        'TrangThai'    => 'Đã phân công',
                        'MaGV'         => $request->MaGVPhanBien,
                        'MaDeTai'      => $hoSo->MaDeTai,
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', "Đã phân công nhóm vào Hội đồng {$hoiDong->TenHoiDong} thành công!");
    }

    public function huyPhanCongNhom($id, $maHoSo)
    {
        $hoSo = HoSoBaoVe::where('MaHoiDong', $id)->where('MaHoSo', $maHoSo)->firstOrFail();
        $hoSo->update([
            'MaHoiDong' => null,
            'TrangThai' => 'Đủ điều kiện bảo vệ',
        ]);
        return redirect()->back()->with('success', 'Đã hủy phân công nhóm khỏi hội đồng!');
    }
}
