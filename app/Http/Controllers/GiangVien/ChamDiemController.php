<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\ChiTietDiemHoiDong;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\GiangVien;
use App\Models\KetQuaSinhVien;
use App\Models\ThanhVienHoiDong;
use App\Models\ThanhVienNhom;
use App\Models\HocKy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChamDiemController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $giangVien = GiangVien::where('MaTK', $user->MaTK)->firstOrFail();

        // Hội đồng mà GV này là thành viên
        $hoiDongs = HoiDong::whereHas('thanhViens', fn($q) => $q->where('MaGV', $giangVien->MaGV))
            ->with([
                'thanhViens.giangVien',
                'hoSoBaoVes.nhom.thanhViens' => fn($q) => $q->where('TrangThai', 'da_tham_gia')->with('sinhVien'),
                'hoSoBaoVes.nhom.deTai',
                'hoSoBaoVes.nhom.truongNhom',
            ])
            ->get();

        // Điểm GV đã chấm
        $diemDaCham = ChiTietDiemHoiDong::where('MaGV', $giangVien->MaGV)->get()
            ->keyBy(fn($d) => $d->MaHoiDong . '_' . $d->MaSV);

        return view('giangvien.chamdiem.index', compact('giangVien', 'hoiDongs', 'diemDaCham'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaHoiDong' => 'required|exists:hoi_dongs,MaHoiDong',
            'diems'     => 'required|array|min:1',
            'diems.*.MaSV' => 'required|exists:sinh_viens,MaSV',
            'diems.*.Diem' => 'required|numeric|min:0|max:10',
            'diems.*.NhanXet' => 'nullable|string|max:500',
        ], [
            'diems.*.Diem.required' => 'Vui lòng nhập điểm cho tất cả Sinh viên.',
            'diems.*.Diem.min'      => 'Điểm tối thiểu là 0.',
            'diems.*.Diem.max'      => 'Điểm tối đa là 10.',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::where('MaTK', $user->MaTK)->firstOrFail();

        DB::transaction(function () use ($request, $giangVien) {
            foreach ($request->diems as $diemData) {
                ChiTietDiemHoiDong::updateOrCreate(
                    [
                        'MaHoiDong' => $request->MaHoiDong,
                        'MaGV'      => $giangVien->MaGV,
                        'MaSV'      => $diemData['MaSV'],
                    ],
                    [
                        'Diem'     => $diemData['Diem'],
                        'NhanXet'  => $diemData['NhanXet'] ?? null,
                        'NgayCham' => now(),
                    ]
                );
            }

            // Kiểm tra đủ điểm để tổng hợp KetQuaSinhVien
            $this->tongHopKetQua($request->MaHoiDong);
        });

        return redirect()->route('giangvien.chamdiem.index')
            ->with('success', 'Đã lưu điểm chấm thành công!');
    }

    private function tongHopKetQua(string $maHoiDong): void
    {
        $hoiDong = HoiDong::with(['hoSoBaoVes.nhom.thanhViens' => fn($q) => $q->where('TrangThai', 'da_tham_gia')])->find($maHoiDong);
        if (!$hoiDong) return;

        $hocKy = HocKy::where('TrangThai', true)->first();
        if (!$hocKy) return;

        foreach ($hoiDong->hoSoBaoVes as $hoSo) {
            if (!$hoSo->nhom) continue;

            foreach ($hoSo->nhom->thanhViens as $tv) {
                $maSV = $tv->MaSV;

                // Điểm trung bình từ tất cả GV trong HĐ
                $allDiems = ChiTietDiemHoiDong::where('MaHoiDong', $maHoiDong)
                    ->where('MaSV', $maSV)->get();

                if ($allDiems->isEmpty()) continue;

                $diemHoiDongTB = round($allDiems->avg('Diem'), 2);

                // Điểm GVHD (lấy từ ChamDiem hoặc mặc định 8 nếu chưa có)
                $diemGVHD = \App\Models\ChamDiem::where('MaSV', $maSV)->value('DiemHuongDan') ?? 0;

                // Điểm Phản biện (GV có vai trò "Phản biện" trong HĐ)
                $maGVPB = ThanhVienHoiDong::where('MaHoiDong', $maHoiDong)
                    ->where('VaiTro', 'Phản biện')->value('MaGV');
                $diemPB = $maGVPB
                    ? ChiTietDiemHoiDong::where('MaHoiDong', $maHoiDong)
                        ->where('MaGV', $maGVPB)->where('MaSV', $maSV)->value('Diem') ?? 0
                    : 0;

                $diemTongKet = KetQuaSinhVien::tinhDiemTongKet(
                    (float)$diemGVHD, (float)$diemPB, (float)$diemHoiDongTB
                );

                KetQuaSinhVien::updateOrCreate(
                    ['MaSV' => $maSV, 'MaHocKy' => $hocKy->MaHocKy],
                    [
                        'MaKetQua'      => 'KQ' . str_pad(KetQuaSinhVien::count() + 1, 3, '0', STR_PAD_LEFT),
                        'DiemHuongDan'  => $diemGVHD,
                        'DiemPhanBien'  => $diemPB,
                        'DiemHoiDongTB' => $diemHoiDongTB,
                        'DiemTongKet'   => $diemTongKet,
                        'KetQua'        => KetQuaSinhVien::xepLoai($diemTongKet),
                        'NgayCham'      => now()->toDateString(),
                    ]
                );
            }
        }
    }
}
