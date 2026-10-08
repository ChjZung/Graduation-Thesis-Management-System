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
    public function index(Request $request)
    {
        $query = HoiDong::with(['thanhViens.giangVien', 'hoSoBaoVes.nhom'])
            ->orderBy('ThoiGianBatDau', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('TenHoiDong', 'like', "%{$s}%")
                  ->orWhere('MaHoiDong', 'like', "%{$s}%")
                  ->orWhere('DiaDiem', 'like', "%{$s}%");
            });
        }

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai);
        }

        $hoiDongs = $query->paginate(10)->withQueryString();

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

    public function themThanhVien(Request $request, $id)
    {
        $request->validate([
            'MaGV'   => 'required|exists:GiangVien,MaGV',
            'VaiTro' => 'required|in:Chủ tịch,Thư ký,Ủy viên,Thành viên,Phản biện',
        ], [
            'MaGV.required'   => 'Vui lòng chọn giảng viên.',
            'VaiTro.required' => 'Vui lòng chọn vai trò trong Hội đồng.',
        ]);

        $hoiDong = HoiDong::findOrFail($id);

        $exists = ThanhVienHoiDong::where('MaHoiDong', $id)->where('MaGV', $request->MaGV)->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'Giảng viên này đã có trong danh sách thành viên Hội đồng!');
        }

        ThanhVienHoiDong::create([
            'MaHoiDong' => $hoiDong->MaHoiDong,
            'MaGV'      => $request->MaGV,
            'VaiTro'    => $request->VaiTro,
        ]);

        return redirect()->back()->with('success', 'Đã thêm thành viên vào Hội đồng thành công!');
    }

    public function doiVaiTroThanhVien(Request $request, $id, $maGV)
    {
        $request->validate([
            'VaiTro' => 'required|in:Chủ tịch,Thư ký,Ủy viên,Thành viên,Phản biện',
        ]);

        $tv = ThanhVienHoiDong::where('MaHoiDong', $id)->where('MaGV', $maGV)->firstOrFail();
        $tv->update(['VaiTro' => $request->VaiTro]);

        return redirect()->back()->with('success', 'Đã cập nhật vai trò thành viên thành công!');
    }

    public function xoaThanhVien($id, $maGV)
    {
        $hoiDong = HoiDong::findOrFail($id);

        // Kiểm tra xem giảng viên đã có phiếu chấm điểm nào trong hội đồng này chưa
        $daChamDiem = \App\Models\PhieuChamDiem::where('MaHoiDong', $id)->where('MaGV', $maGV)->exists();
        if ($daChamDiem) {
            return redirect()->back()->with('error', 'Không thể xóa thành viên này vì đã có phiếu chấm điểm được ghi nhận trong Hội đồng!');
        }

        $deleted = ThanhVienHoiDong::where('MaHoiDong', $id)->where('MaGV', $maGV)->delete();
        if ($deleted) {
            return redirect()->back()->with('success', 'Đã xóa thành viên khỏi Hội đồng thành công!');
        }

        return redirect()->back()->with('error', 'Không tìm thấy thành viên cần xóa.');
    }
}
