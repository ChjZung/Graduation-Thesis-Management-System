<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\PhieuChamDiem;
use App\Models\HoiDong;
use App\Models\GiangVien;
use App\Models\ThanhVienHoiDong;
use App\Models\HoSoBaoVe;
use App\Models\HocKy;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChamDiemController extends Controller
{
    public function __construct(private GradeCalculationService $gradeService) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        // Lấy tất cả Hội đồng mà giảng viên này tham gia
        $hoiDongs = HoiDong::whereHas('thanhViens', fn($q) => $q->where('MaGV', $giangVien->MaGV))
            ->with([
                'thanhViens.giangVien',
                'hoSoBaoVes.nhom.thanhViens.sinhVien.lop',
                'hoSoBaoVes.nhom.deTai.giangVien',
                'hoSoBaoVes.nhom.truongNhom',
            ])
            ->get();

        $activeHoiDong = null;
        if ($hoiDongs->isNotEmpty()) {
            if ($request->filled('hd')) {
                $activeHoiDong = $hoiDongs->firstWhere('MaHoiDong', $request->hd) ?? $hoiDongs->first();
            } else {
                $activeHoiDong = $hoiDongs->first();
            }
        }

        // Lấy nhóm / hồ sơ bảo vệ đang chọn
        $activeHoSo = null;
        if ($activeHoiDong && $activeHoiDong->hoSoBaoVes->isNotEmpty()) {
            if ($request->filled('hoso')) {
                $activeHoSo = $activeHoiDong->hoSoBaoVes->firstWhere('MaHoSo', $request->hoso) ?? $activeHoiDong->hoSoBaoVes->first();
            } else {
                $activeHoSo = $activeHoiDong->hoSoBaoVes->first();
            }
        }

        // Xác định loại khóa luận: Cử nhân (KLCN) hoặc Kỹ sư (KLKS)
        $tenDeTai = mb_strtolower($activeHoSo?->nhom?->deTai?->TenDeTai ?? '');
        $maHP = mb_strtolower($activeHoSo?->nhom?->deTai?->MaHocPhan ?? '');
        $isEngineers = str_contains($tenDeTai, 'kỹ sư') || str_contains($tenDeTai, 'ky su') || str_contains($maHP, 'klks') || str_contains($maHP, '0101102534');
        
        $defaultLoai = $isEngineers ? 'KLKS' : 'KLCN';
        $loaiKhoaLuan = $request->query('loai', $defaultLoai);

        // Lấy điểm đã chấm của giảng viên này cho hội đồng đang chọn
        $diemDaCham = [];
        if ($activeHoiDong) {
            $diemDaCham = PhieuChamDiem::where('MaHoiDong', $activeHoiDong->MaHoiDong)
                ->where('MaGV', $giangVien->MaGV)
                ->get()
                ->keyBy('MaSV');
        }

        $rubricKLCN = PhieuChamDiem::getRubricKLCN();
        $rubricKLKS = PhieuChamDiem::getRubricKLKS();

        return view('giangvien.chamdiem.index', compact(
            'giangVien',
            'hoiDongs',
            'activeHoiDong',
            'activeHoSo',
            'loaiKhoaLuan',
            'diemDaCham',
            'rubricKLCN',
            'rubricKLKS'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaHoiDong'     => 'required|exists:HoiDong,MaHoiDong',
            'MaHoSo'        => 'required|exists:HoSoBaoVe,MaHoSo',
            'LoaiKhoaLuan'  => 'required|in:KLCN,KLKS',
            'diems'         => 'required|array|min:1',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        // Kiểm tra quyền: Chỉ Giảng viên thuộc Hội đồng mới được chấm điểm
        $isMember = ThanhVienHoiDong::where('MaHoiDong', $request->MaHoiDong)
            ->where('MaGV', $giangVien->MaGV)
            ->exists();

        if (!$isMember) {
            abort(403, 'Bạn không thuộc Hội đồng bảo vệ này và không có quyền nhập điểm.');
        }

        $hoSo = HoSoBaoVe::with('nhom.deTai')->findOrFail($request->MaHoSo);
        $deTai = $hoSo->nhom?->deTai;
        $maDeTai = $deTai?->MaDeTai ?? ($hoSo->MaDeTai ?? 'DT_CHUA_RO');

        $activeHocKy = HocKy::where('TrangThai', true)->first() ?? HocKy::latest()->first();
        $maHocKy = $hoSo->MaHocKy ?? ($activeHocKy?->MaHocKy ?? 'HK_DEF');

        DB::transaction(function () use ($request, $giangVien, $hoSo, $maDeTai, $maHocKy) {
            foreach ($request->diems as $key => $diemData) {
                $maSV = is_numeric($key) ? ($diemData['MaSV'] ?? null) : $key;
                if (!$maSV) continue;

                $diemSo = floatval($diemData['Diem'] ?? 0);
                $nhanXet = $diemData['NhanXet'] ?? null;
                $chiTiet = isset($diemData['ChiTietDiem']) ? (is_array($diemData['ChiTietDiem']) ? $diemData['ChiTietDiem'] : json_decode($diemData['ChiTietDiem'], true)) : null;

                // Tạo mã phiếu chấm duy nhất
                $hashId = substr(md5($request->MaHoiDong . '_' . $giangVien->MaGV . '_' . $maSV), 0, 8);
                $maPhieu = 'PCD_' . strtoupper($hashId);

                PhieuChamDiem::updateOrCreate(
                    [
                        'MaHoiDong' => $request->MaHoiDong,
                        'MaGV'      => $giangVien->MaGV,
                        'MaSV'      => $maSV,
                    ],
                    [
                        'MaPhieuChamDiem' => $maPhieu,
                        'Diem'            => min(10.0, max(0.0, $diemSo)),
                        'LoaiKhoaLuan'    => $request->LoaiKhoaLuan,
                        'ChiTietDiem'     => $chiTiet,
                        'NhanXet'         => $nhanXet,
                        'MaDeTai'         => $maDeTai,
                        'MaHoSo'          => $hoSo->MaHoSo,
                        'MaHocKy'         => $maHocKy,
                        'NgayCham'        => now(),
                    ]
                );
            }

            // Tổng hợp kết quả điểm Hội đồng tự động (100% từ điểm các thành viên HĐ)
            $this->gradeService->tongHopKetQuaHoiDong($request->MaHoiDong);
        });

        return redirect()->route('giangvien.chamdiem.index', [
            'hd'   => $request->MaHoiDong,
            'hoso' => $request->MaHoSo,
            'loai' => $request->LoaiKhoaLuan,
        ])->with('success', 'Đã lưu phiếu chấm điểm của Giám khảo thành công! Điểm Hội đồng đã được cập nhật.');
    }
}
