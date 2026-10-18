<?php

namespace App\Http\Controllers\TruongKhoa;

use App\Http\Controllers\Controller;
use App\Models\HoiDong;
use App\Models\ThanhVienHoiDong;
use App\Models\GiangVien;
use App\Models\HoSoBaoVe;
use App\Models\PhanCongPhanBien;
use App\Models\Khoa;
use App\Helpers\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HoiDongController extends Controller
{
    /**
     * Xác định Khoa do Trưởng khoa phụ trách
     */
    private function getKhoa()
    {
        $user = Auth::user();
        if ($user && preg_match('/^TK_([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
            $khoa = Khoa::where('MaKhoa', $m[1])->first();
            if ($khoa) return $khoa;
        }

        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv) {
            $gv->loadMissing('boMon.khoa');
            if ($gv->boMon && $gv->boMon->khoa) {
                return $gv->boMon->khoa;
            }
        }

        if (!empty($gv?->HoTen)) {
            $byName = Khoa::where('TruongKhoa', 'like', '%' . $gv->HoTen . '%')->first();
            if ($byName) return $byName;
        }

        return Khoa::first() ?? new Khoa(['MaKhoa' => 'CNTT', 'TenKhoa' => 'Khoa Công nghệ Thông tin']);
    }

    /**
     * Danh sách Hội đồng bảo vệ khóa luận thuộc Khoa
     */
    public function index(Request $request)
    {
        $khoa = $this->getKhoa();
        $maKhoa = $khoa->MaKhoa;

        $query = HoiDong::with(['thanhViens.giangVien.boMon', 'hoSoBaoVes.nhom.deTai.giangVien'])
            ->where(function($q) use ($maKhoa) {
                // Hội đồng có nhóm thuộc Khoa hoặc có thành viên thuộc Khoa
                $q->whereHas('hoSoBaoVes.nhom.deTai.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa))
                  ->orWhereHas('thanhViens.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa))
                  ->orDoesntHave('hoSoBaoVes'); // Các hội đồng mới lập chưa gán nhóm
            })
            ->orderBy('ThoiGianBatDau', 'desc');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $query->where(function($q) use ($s, $sClean) {
                $q->where('TenHoiDong', 'like', "%{$s}%")
                  ->orWhere('MaHoiDong', 'like', "%{$s}%")
                  ->orWhere('DiaDiem', 'like', "%{$s}%");
                if (!empty($sClean)) {
                    $q->orWhere('MaHoiDong', 'like', "%{$sClean}%");
                }
            });
        }

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai);
        }

        $hoiDongs = $query->paginate(10)->withQueryString();

        return view('truongkhoa.hoidong.index', compact('hoiDongs', 'khoa'));
    }

    /**
     * Màn hình thành lập Hội đồng mới
     */
    public function create()
    {
        $khoa = $this->getKhoa();
        $giangViens = GiangVien::with('boMon')
            ->whereHas('boMon', fn($bm) => $bm->where('MaKhoa', $khoa->MaKhoa))
            ->orderBy('HoTen')
            ->get();

        if ($giangViens->isEmpty()) {
            $giangViens = GiangVien::with('boMon')->orderBy('HoTen')->get();
        }

        return view('truongkhoa.hoidong.create', compact('giangViens', 'khoa'));
    }

    /**
     * Lưu quyết định thành lập Hội đồng mới
     */
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

        return redirect()->route('truongkhoa.hoidong.index')
            ->with('success', 'Trưởng khoa đã ban hành quyết định thành lập Hội đồng thành công!');
    }

    /**
     * Chi tiết Hội đồng & Phân công các nhóm vào Hội đồng bảo vệ
     */
    public function show($id)
    {
        $khoa = $this->getKhoa();
        $hoiDong = HoiDong::with([
            'thanhViens.giangVien.boMon',
            'hoSoBaoVes.nhom.truongNhom',
            'hoSoBaoVes.nhom.deTai.giangVien.boMon',
            'hoSoBaoVes.phanCongPhanBien.giangVien.boMon',
            'hoSoBaoVes.tepHoSoBaoVes',
        ])->findOrFail($id);

        $giangViens = GiangVien::with('boMon')
            ->whereHas('boMon', fn($bm) => $bm->where('MaKhoa', $khoa->MaKhoa))
            ->orderBy('HoTen')
            ->get();

        if ($giangViens->isEmpty()) {
            $giangViens = GiangVien::with('boMon')->orderBy('HoTen')->get();
        }

        // Nhóm đủ điều kiện chưa được phân công hoặc đang ở hội đồng này
        $nhomDuDieuKien = HoSoBaoVe::with(['nhom.truongNhom', 'nhom.deTai.giangVien.boMon'])
            ->where(function ($q) use ($id) {
                $q->whereNull('MaHoiDong')
                  ->orWhere('MaHoiDong', $id);
            })
            ->where('TrangThai', 'Đủ điều kiện bảo vệ')
            ->whereHas('nhom.deTai.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $khoa->MaKhoa))
            ->get();

        return view('truongkhoa.hoidong.show', compact('hoiDong', 'giangViens', 'nhomDuDieuKien', 'khoa'));
    }

    /**
     * Cập nhật trạng thái Hội đồng
     */
    public function updateTrangThai(Request $request, $id)
    {
        $request->validate([
            'TrangThai' => 'required|in:Chưa diễn ra,Đang diễn ra,Đã kết thúc,Đã hủy',
        ]);

        $hoiDong = HoiDong::findOrFail($id);
        $hoiDong->update(['TrangThai' => $request->TrangThai]);

        return redirect()->back()->with('success', "Cập nhật trạng thái Hội đồng thành '{$request->TrangThai}' thành công!");
    }

    /**
     * Phân công nhóm sinh viên vào Hội đồng bảo vệ
     */
    public function phanCongNhom(Request $request, $id)
    {
        $request->validate([
            'MaNhom'        => 'required|exists:HoSoBaoVe,MaNhom',
            'ThoiGianBaoVe' => 'nullable|date',
            'PhongBaoVe'    => 'nullable|string|max:100',
            'MaGVPhanBien'  => 'nullable|exists:GiangVien,MaGV',
        ]);

        $hoiDong = HoiDong::findOrFail($id);
        $hoSo = HoSoBaoVe::where('MaNhom', $request->MaNhom)->first();

        if (!$hoSo) {
            return redirect()->back()->with('error', 'Nhóm này chưa nộp hồ sơ bảo vệ.');
        }

        // Kiểm tra ràng buộc cùng Bộ môn đối với Giảng viên phản biện
        if ($request->filled('MaGVPhanBien')) {
            $hoSo->loadMissing('nhom.deTai.giangVien', 'nhom.deTai.hocPhanRef');
            $detai = $hoSo->nhom?->deTai;
            $gvPB = GiangVien::find($request->MaGVPhanBien);
            if ($detai && $gvPB) {
                $dtBm = str_replace('BM_', '', $detai->giangVien?->MaBoMon ?? $detai->hocPhanRef?->MaBoMon ?? '');
                $gvBm = str_replace('BM_', '', $gvPB->MaBoMon ?? '');
                if ($dtBm && $gvBm && $dtBm !== $gvBm) {
                    return redirect()->back()->withErrors("Giảng viên phản biện ({$gvPB->HoTen}) phải thuộc cùng Bộ môn với đề tài!");
                }
            }
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

    /**
     * Hủy phân công nhóm khỏi Hội đồng
     */
    public function huyPhanCongNhom($id, $maHoSo)
    {
        $hoSo = HoSoBaoVe::where('MaHoiDong', $id)->where('MaHoSo', $maHoSo)->firstOrFail();
        $hoSo->update([
            'MaHoiDong' => null,
            'TrangThai' => 'Đủ điều kiện bảo vệ',
        ]);
        return redirect()->back()->with('success', 'Đã hủy phân công nhóm khỏi hội đồng!');
    }

    /**
     * Bổ sung thành viên vào Hội đồng
     */
    public function themThanhVien(Request $request, $id)
    {
        $request->validate([
            'MaGV'   => 'required|exists:GiangVien,MaGV',
            'VaiTro' => 'required|in:Chủ tịch,Thư ký,Thành viên,Phản biện',
        ]);

        $hoiDong = HoiDong::findOrFail($id);

        $exists = ThanhVienHoiDong::where('MaHoiDong', $id)->where('MaGV', $request->MaGV)->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'Giảng viên này đã có trong Hội đồng.');
        }

        ThanhVienHoiDong::create([
            'MaHoiDong' => $hoiDong->MaHoiDong,
            'MaGV'      => $request->MaGV,
            'VaiTro'    => $request->VaiTro,
        ]);

        return redirect()->back()->with('success', 'Đã thêm thành viên vào Hội đồng thành công!');
    }

    /**
     * Đổi vai trò thành viên Hội đồng
     */
    public function doiVaiTroThanhVien(Request $request, $id, $maGV)
    {
        $request->validate([
            'VaiTro' => 'required|in:Chủ tịch,Thư ký,Thành viên,Phản biện',
        ]);

        $tv = ThanhVienHoiDong::where('MaHoiDong', $id)->where('MaGV', $maGV)->firstOrFail();
        $tv->update(['VaiTro' => $request->VaiTro]);

        return redirect()->back()->with('success', 'Đã cập nhật vai trò thành viên thành công!');
    }

    /**
     * Xóa thành viên khỏi Hội đồng
     */
    public function xoaThanhVien($id, $maGV)
    {
        $tv = ThanhVienHoiDong::where('MaHoiDong', $id)->where('MaGV', $maGV)->firstOrFail();
        $tv->delete();

        return redirect()->back()->with('success', 'Đã xóa thành viên khỏi Hội đồng thành công!');
    }
}
