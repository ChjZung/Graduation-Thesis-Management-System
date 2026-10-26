<?php

namespace App\Http\Controllers\TruongKhoa;

use App\Http\Controllers\Controller;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\PhieuChamDiem;
use App\Models\KetQuaSinhVien;
use App\Services\GradeCalculationService;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KetQuaController extends Controller
{
    public function __construct(private GradeCalculationService $gradeService) {}

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
            $byName = Khoa::where('TruongKhoa', 'like', '%' . ($gv->HoTen ?? '') . '%')->first();
            if ($byName) return $byName;
        }

        return Khoa::first();
    }

    public function index(Request $request)
    {
        $khoa = $this->getKhoa();
        if (!$khoa) {
            abort(403, 'Tài khoản Trưởng khoa chưa được liên kết với Khoa nào.');
        }
        $maKhoa = $khoa->MaKhoa;

        $viewMode = $request->query('view', 'hoidong'); // 'hoidong' hoặc 'toankhoa'

        $boMons = BoMon::where('MaKhoa', $maKhoa)->orderBy('TenBoMon')->get();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $activeHocKy = HocKy::where('TrangThai', true)->first() ?? $hocKies->first();

        // 1. Dữ liệu Tab Tổng Hợp Theo Hội Đồng
        $hoiDongsQuery = HoiDong::where(function ($q) use ($maKhoa) {
                $q->where('MaKhoa', $maKhoa)
                  ->orWhereNull('MaKhoa')
                  ->orWhere('MaKhoa', '')
                  ->orWhereHas('hoSoBaoVes.nhom.deTai.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa))
                  ->orWhereHas('thanhViens.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa));
            })
            ->with([
                'thanhViens.giangVien',
                'hoSoBaoVes.nhom.deTai',
                'hoSoBaoVes.nhom.thanhViens.sinhVien',
            ])
            ->orderBy('NgayBaoVe', 'desc')
            ->orderBy('MaHoiDong', 'desc');

        if ($request->filled('MaHocKy')) {
            $hoiDongsQuery->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('search_hd')) {
            $s = trim($request->search_hd);
            $hoiDongsQuery->where(function ($q) use ($s) {
                $q->where('TenHoiDong', 'like', "%{$s}%")
                  ->orWhere('DiaDiem', 'like', "%{$s}%")
                  ->orWhere('MaHoiDong', 'like', "%{$s}%");
            });
        }

        $hoiDongs = $hoiDongsQuery->get();

        // Tiến độ chấm điểm của từng Hội đồng
        $tienDoHoiDongs = [];
        foreach ($hoiDongs as $hd) {
            $tienDoHoiDongs[$hd->MaHoiDong] = $this->gradeService->kiemTraTienDoChamHoiDong($hd->MaHoiDong);
        }

        // 2. Dữ liệu Tab Toàn Khoa (Sinh viên)
        $query = KetQuaSinhVien::with([
            'sinhVien.lop.nganh',
            'hocKy',
            'hoSoBaoVe.nhom',
            'hoSoBaoVe.deTai.giangVien.boMon',
            'hoSoBaoVe.hoiDong'
        ])->where(function ($sub) use ($maKhoa) {
            $sub->whereHas('sinhVien', fn($q) => $q->where('MaKhoa', $maKhoa))
                ->orWhereHas('hoSoBaoVe.deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));
        });

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('MaBoMon')) {
            $maBM = $request->MaBoMon;
            $query->whereHas('hoSoBaoVe.deTai.giangVien', fn($q) => $q->where('MaBoMon', $maBM));
        }

        if ($request->filled('xep_loai')) {
            $xl = $request->xep_loai;
            if ($xl === 'XuatSac') {
                $query->where('DiemTongKet', '>=', 9.0);
            } elseif ($xl === 'Gioi') {
                $query->whereBetween('DiemTongKet', [8.0, 8.99]);
            } elseif ($xl === 'Kha') {
                $query->whereBetween('DiemTongKet', [7.0, 7.99]);
            } elseif ($xl === 'TrungBinh') {
                $query->whereBetween('DiemTongKet', [5.0, 6.99]);
            } elseif ($xl === 'KhongDat') {
                $query->where('DiemTongKet', '<', 5.0);
            }
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('MaSV', 'LIKE', "%{$s}%")
                  ->orWhereHas('sinhVien', fn($sv) => $sv->where('HoTen', 'LIKE', "%{$s}%"))
                  ->orWhereHas('hoSoBaoVe.deTai', fn($dt) => $dt->where('TenDeTai', 'LIKE', "%{$s}%"));
            });
        }

        $ketQuas = $query->orderBy('DiemTongKet', 'desc')->paginate(15)->withQueryString();

        // Thống kê xếp loại toàn khoa
        $baseQuery = KetQuaSinhVien::where(function ($sub) use ($maKhoa) {
            $sub->whereHas('sinhVien', fn($q) => $q->where('MaKhoa', $maKhoa))
                ->orWhereHas('hoSoBaoVe.deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));
        });

        if ($request->filled('MaHocKy')) {
            $baseQuery->where('MaHocKy', $request->MaHocKy);
        }

        $stats = [
            'total'        => (clone $baseQuery)->count(),
            'xuat_sac'     => (clone $baseQuery)->where('DiemTongKet', '>=', 9.0)->count(),
            'gioi'         => (clone $baseQuery)->whereBetween('DiemTongKet', [8.0, 8.99])->count(),
            'kha'          => (clone $baseQuery)->whereBetween('DiemTongKet', [7.0, 7.99])->count(),
            'trung_binh'   => (clone $baseQuery)->whereBetween('DiemTongKet', [5.0, 6.99])->count(),
            'khong_dat'    => (clone $baseQuery)->where('DiemTongKet', '<', 5.0)->count(),
        ];

        return view('truongkhoa.ketqua.index', compact(
            'khoa',
            'viewMode',
            'hoiDongs',
            'tienDoHoiDongs',
            'ketQuas',
            'boMons',
            'hocKies',
            'stats'
        ));
    }

    /**
     * Màn hình Chi tiết Tổng hợp Điểm, Đối chiếu Điểm & Công bố Hội đồng
     */
    public function showHoiDong(string $id, Request $request)
    {
        $khoa = $this->getKhoa();
        $tienDo = $this->gradeService->kiemTraTienDoChamHoiDong($id);

        if (!$tienDo['exists']) {
            abort(404, 'Không tìm thấy Hội đồng bảo vệ.');
        }

        $hoiDong = $tienDo['hoiDong'];

        // Danh sách giảng viên trong khoa để phân công kiểm tra báo cáo chỉnh sửa (nếu cần)
        $giangViensKhoa = GiangVien::whereHas('boMon', fn($q) => $q->where('MaKhoa', $khoa->MaKhoa))->orderBy('HoTen')->get();

        // Lấy tất cả các phiếu chấm gốc để modal xem trước hiển thị chi tiết
        $allPhieuChams = PhieuChamDiem::with(['giangVien', 'sinhVien'])
            ->where('MaHoiDong', $id)
            ->get();

        $rubricKLCN = PhieuChamDiem::getRubricKLCN();
        $rubricKLKS = PhieuChamDiem::getRubricKLKS();

        return view('truongkhoa.ketqua.show_hoidong', compact(
            'khoa',
            'hoiDong',
            'tienDo',
            'giangViensKhoa',
            'allPhieuChams',
            'rubricKLCN',
            'rubricKLKS'
        ));
    }

    /**
     * Trưởng khoa xác nhận kết quả bảo vệ cho một nhóm/hồ sơ
     */
    public function xacNhanKetQua(Request $request)
    {
        $request->validate([
            'MaHoSo'           => 'required|exists:HoSoBaoVe,MaHoSo',
            'dieu_chinh_diem'  => 'nullable|array',
            'ghi_chu'          => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $nguoiXacNhan = $user->HoTen ?? ($user->TenDangNhap ?? 'Trưởng khoa');

        $this->gradeService->xacNhanKetQua(
            $request->MaHoSo,
            $nguoiXacNhan,
            $request->dieu_chinh_diem,
            $request->ghi_chu
        );

        return redirect()->back()->with('success', 'Đã xác nhận kết quả bảo vệ của nhóm thành công! Điểm chính thức đã được chốt và lưu vết kiểm toán.');
    }

    /**
     * Trưởng khoa công bố kết quả chính thức của toàn Hội đồng
     */
    public function congBoKetQua(Request $request)
    {
        $request->validate([
            'MaHoiDong' => 'required|exists:HoiDong,MaHoiDong',
        ]);

        $tienDo = $this->gradeService->kiemTraTienDoChamHoiDong($request->MaHoiDong);
        if (!$tienDo['exists'] || !$tienDo['hoiDongDuPhieu']) {
            return redirect()->back()->with('error', 'Hội đồng chưa đủ 100% phiếu chấm hợp lệ từ các giám khảo! Chưa thể công bố kết quả.');
        }

        $user = Auth::user();
        $nguoiCongBo = $user->HoTen ?? ($user->TenDangNhap ?? 'Trưởng khoa');

        $this->gradeService->congBoKetQua($request->MaHoiDong, $nguoiCongBo);

        return redirect()->back()->with('success', 'Đã CÔNG BỐ KẾT QUẢ bảo vệ thành công! Dữ liệu điểm đã được khóa và hệ thống đã gửi thông báo đến toàn bộ sinh viên, giảng viên hướng dẫn.');
    }

    /**
     * Gửi thông báo nhắc nhở giảng viên nộp phiếu chấm điểm
     */
    public function nhacNhoChamDiem(Request $request)
    {
        $request->validate([
            'MaHoiDong' => 'required|exists:HoiDong,MaHoiDong',
            'MaHoSo'    => 'nullable|exists:HoSoBaoVe,MaHoSo',
        ]);

        $sent = $this->gradeService->guiNhacNhoChamDiem($request->MaHoiDong, $request->MaHoSo);

        return redirect()->back()->with('success', "Đã gửi thông báo nhắc nhở đến {$sent} lượt giảng viên chưa hoàn thành phiếu chấm điểm!");
    }

    /**
     * Trưởng khoa ghi nhận yêu cầu chỉnh sửa báo cáo sau bảo vệ
     */
    public function yeuCauChinhSua(Request $request)
    {
        $request->validate([
            'MaHoSo'                    => 'required|exists:HoSoBaoVe,MaHoSo',
            'YeuCauChinhSuaSauBaoVe'    => 'required|string|max:3000',
            'HanNopBaoCaoSauBaoVe'      => 'required|date|after:now',
            'NguoiKiemTraChinhSua'      => 'required|string|max:100',
        ], [
            'YeuCauChinhSuaSauBaoVe.required' => 'Vui lòng nhập nội dung cần chỉnh sửa theo kết luận của Hội đồng.',
            'HanNopBaoCaoSauBaoVe.required'   => 'Vui lòng thiết lập thời hạn hoàn thành nộp lại báo cáo.',
            'HanNopBaoCaoSauBaoVe.after'      => 'Thời hạn nộp lại phải sau thời điểm hiện tại.',
            'NguoiKiemTraChinhSua.required'   => 'Vui lòng chỉ định người kiểm tra, xác nhận báo cáo sau chỉnh sửa.',
        ]);

        $hoSo = HoSoBaoVe::with(['nhom.deTai.giangVien', 'hoiDong'])->findOrFail($request->MaHoSo);

        $hoSo->YeuCauChinhSuaSauBaoVe = $request->YeuCauChinhSuaSauBaoVe;
        $hoSo->HanNopBaoCaoSauBaoVe = $request->HanNopBaoCaoSauBaoVe;
        $hoSo->NguoiKiemTraChinhSua = $request->NguoiKiemTraChinhSua;
        $hoSo->TrangThaiChinhSuaSauBaoVe = 'ChoSinhVienNop';
        $hoSo->TrangThaiKetQua = 'CoYeuCauChinhSua';
        $hoSo->save();

        // Gửi thông báo đến nhóm sinh viên
        ThongBaoService::guiDenNhom(
            $hoSo->MaNhom,
            'Yêu cầu chỉnh sửa báo cáo sau bảo vệ khóa luận',
            "Hội đồng {$hoSo->hoiDong?->TenHoiDong} yêu cầu nhóm chỉnh sửa, hoàn thiện báo cáo khóa luận. Hạn chót: " . \Carbon\Carbon::parse($request->HanNopBaoCaoSauBaoVe)->format('d/m/Y H:i') . ". Vui lòng vào mục 'Hoàn thiện báo cáo sau bảo vệ' để xem chi tiết và nộp lại.",
            'Yêu cầu chỉnh sửa'
        );

        // Gửi thông báo đến người kiểm tra
        ThongBaoService::gui(
            $request->NguoiKiemTraChinhSua,
            'Phân công kiểm tra báo cáo sau bảo vệ',
            "Thầy/Cô được Trưởng khoa phân công kiểm tra, xác nhận báo cáo chỉnh sửa sau bảo vệ của nhóm {$hoSo->nhom?->TenNhom} (Đề tài: {$hoSo->nhom?->deTai?->TenDeTai}).",
            'Phân công'
        );

        return redirect()->back()->with('success', 'Đã ghi nhận yêu cầu chỉnh sửa báo cáo sau bảo vệ và phát thông báo đến nhóm sinh viên cùng giảng viên phụ trách kiểm tra!');
    }

    /**
     * Trưởng khoa mở khóa phiếu chấm điểm theo yêu cầu của giám khảo
     */
    public function moKhoaPhieu(Request $request)
    {
        $request->validate([
            'MaPhieuChamDiem' => 'required|exists:PhieuChamDiem,MaPhieuChamDiem',
            'LyDoMoKhoa'      => 'nullable|string|max:500',
        ]);

        $phieu = PhieuChamDiem::with(['giangVien', 'hoiDong'])->findOrFail($request->MaPhieuChamDiem);
        $phieu->TrangThai = 'Lưu nháp';
        $phieu->NgayXacNhan = null;
        $phieu->GhiChuMoKhoa = $request->LyDoMoKhoa ?? 'Trưởng khoa cho phép mở khóa để điều chỉnh điểm.';
        $phieu->save();

        // Cập nhật lại tổng hợp điểm hội đồng
        $this->gradeService->tongHopKetQuaHoiDong($phieu->MaHoiDong);

        // Gửi thông báo cho giám khảo
        ThongBaoService::gui(
            $phieu->MaGV,
            'Phiếu chấm điểm đã được mở khóa',
            "Trưởng khoa đã mở khóa phiếu chấm điểm của Thầy/Cô cho sinh viên {$phieu->MaSV} (HĐ: {$phieu->hoiDong?->TenHoiDong}). Thầy/Cô có thể vào chỉnh sửa và Ký xác nhận lại.",
            'Mở khóa'
        );

        return redirect()->back()->with('success', 'Đã mở khóa phiếu chấm điểm thành công! Giám khảo có thể chỉnh sửa lại phiếu này.');
    }

    public function exportExcel(Request $request)
    {
        $khoa = $this->getKhoa();
        $maKhoa = $khoa ? $khoa->MaKhoa : 'CNTT';

        $query = KetQuaSinhVien::with([
            'sinhVien.lop.nganh',
            'hocKy',
            'hoSoBaoVe.nhom',
            'hoSoBaoVe.deTai.giangVien.boMon',
            'hoSoBaoVe.hoiDong'
        ])->where(function ($sub) use ($maKhoa) {
            $sub->whereHas('sinhVien', fn($q) => $q->where('MaKhoa', $maKhoa))
                ->orWhereHas('hoSoBaoVe.deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));
        });

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('MaBoMon')) {
            $maBM = $request->MaBoMon;
            $query->whereHas('hoSoBaoVe.deTai.giangVien', fn($q) => $q->where('MaBoMon', $maBM));
        }

        if ($request->filled('xep_loai')) {
            $xl = $request->xep_loai;
            if ($xl === 'XuatSac') {
                $query->where('DiemTongKet', '>=', 9.0);
            } elseif ($xl === 'Gioi') {
                $query->whereBetween('DiemTongKet', [8.0, 8.99]);
            } elseif ($xl === 'Kha') {
                $query->whereBetween('DiemTongKet', [7.0, 7.99]);
            } elseif ($xl === 'TrungBinh') {
                $query->whereBetween('DiemTongKet', [5.0, 6.99]);
            } elseif ($xl === 'KhongDat') {
                $query->where('DiemTongKet', '<', 5.0);
            }
        }

        $list = $query->orderBy('DiemTongKet', 'desc')->get();

        $filename = 'KetQua_KhoaLuan_' . $maKhoa . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($list) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, [
                'STT',
                'Mã SV',
                'Họ và Tên',
                'Lớp',
                'Bộ môn',
                'Đề tài khóa luận',
                'Loại Khóa Luận',
                'Điểm TB Sơ Bộ',
                'Điểm Chính Thức',
                'Trạng Thái',
                'Xếp Loại',
                'Người Xác Nhận',
                'Ngày Công Bố',
            ]);

            foreach ($list as $index => $row) {
                $diemTB = (float)($row->DiemHoiDongTB ?? 0);
                $diemCT = (float)($row->DiemChinhThuc ?? $row->DiemTongKet ?? $diemTB);
                fputcsv($handle, [
                    $index + 1,
                    $row->MaSV,
                    $row->sinhVien->HoTen ?? 'N/A',
                    $row->sinhVien->lop->TenLop ?? ($row->sinhVien->MaLop ?? 'N/A'),
                    $row->hoSoBaoVe->deTai->giangVien->boMon->TenBoMon ?? 'N/A',
                    $row->hoSoBaoVe->deTai->TenDeTai ?? 'N/A',
                    $row->LoaiKhoaLuan ?? 'KLCN',
                    number_format($diemTB, 2),
                    number_format($diemCT, 2),
                    $row->TrangThai ?? 'Chưa xác định',
                    $row->KetQua ?? ($row->xep_loai ?? 'N/A'),
                    $row->NguoiXacNhan ?? '',
                    $row->NgayCongBo ? \Carbon\Carbon::parse($row->NgayCongBo)->format('d/m/Y H:i') : '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
