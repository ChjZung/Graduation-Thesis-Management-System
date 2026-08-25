<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\ChiTietDiemHoiDong;
use App\Models\HoiDong;
use App\Models\GiangVien;
use App\Models\ThanhVienHoiDong;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChamDiemController extends Controller
{
    public function __construct(private GradeCalculationService $gradeService) {}

    public function index()
    {
        $user = Auth::user();
        $giangVien = GiangVien::where('MaTK', $user->MaTK)->firstOrFail();

        $hoiDongs = HoiDong::whereHas('thanhViens', fn($q) => $q->where('MaGV', $giangVien->MaGV))
            ->with([
                'thanhViens.giangVien',
                'hoSoBaoVes.nhom.thanhViens' => fn($q) => $q->where('TrangThai', 'da_tham_gia')->with('sinhVien'),
                'hoSoBaoVes.nhom.deTai',
                'hoSoBaoVes.nhom.truongNhom',
            ])
            ->get();

        $diemDaCham = ChiTietDiemHoiDong::where('MaGV', $giangVien->MaGV)->get()
            ->keyBy(fn($d) => $d->MaHoiDong . '_' . $d->MaSV);

        return view('giangvien.chamdiem.index', compact('giangVien', 'hoiDongs', 'diemDaCham'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaHoiDong'       => 'required|exists:hoi_dongs,MaHoiDong',
            'diems'           => 'required|array|min:1',
            'diems.*.MaSV'    => 'required|exists:sinh_viens,MaSV',
            'diems.*.Diem'    => 'required|numeric|min:0|max:10',
            'diems.*.NhanXet' => 'nullable|string|max:500',
        ], [
            'diems.*.Diem.required' => 'Vui lòng nhập điểm cho tất cả Sinh viên.',
            'diems.*.Diem.min'      => 'Điểm tối thiểu là 0.',
            'diems.*.Diem.max'      => 'Điểm tối đa là 10.',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::where('MaTK', $user->MaTK)->firstOrFail();

        // BƯỚC 3 (Policy): Chỉ GV thuộc Hội đồng mới được chấm điểm
        $isMember = ThanhVienHoiDong::where('MaHoiDong', $request->MaHoiDong)
            ->where('MaGV', $giangVien->MaGV)
            ->exists();

        if (!$isMember) {
            abort(403, 'Bạn không thuộc Hội đồng bảo vệ này và không có quyền nhập điểm.');
        }

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

            // BƯỚC 6: GradeCalculationService thay cho logic inline
            $this->gradeService->tongHopKetQuaHoiDong($request->MaHoiDong);
        });

        return redirect()->route('giangvien.chamdiem.index')
            ->with('success', 'Đã lưu điểm chấm thành công!');
    }
}
