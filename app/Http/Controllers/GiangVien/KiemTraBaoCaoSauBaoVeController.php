<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Models\HoSoBaoVe;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KiemTraBaoCaoSauBaoVeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        $base = HoSoBaoVe::whereHas('baoCaoSauBaoVes')
            ->whereHas('deTai', fn($d) => $d->where('MaGV', $gv->MaGV));

        $query = (clone $base)->with([
            'nhom.truongNhom',
            'nhom.thanhViens.sinhVien',
            'nhom.hocPhan',
            'deTai.giangVien',
            'hoiDong',
            'baoCaoSauBaoVes.sinhVienNop',
            'baoCaoSauBaoVeMoiNhat',
        ]);

        // Lọc theo trạng thái
        $status = $request->query('status');
        if ($status === 'cho_kiem_tra') {
            $query->where('TrangThaiChinhSuaSauBaoVe', 'DaNop');
        } elseif ($status === 'yeu_cau_sua') {
            $query->where('TrangThaiChinhSuaSauBaoVe', 'YeuCauSuaTiep');
        } elseif ($status === 'da_hoan_thanh') {
            $query->where('TrangThaiChinhSuaSauBaoVe', 'DaHoanThanh');
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('MaHoSo', 'like', "%{$s}%")
                  ->orWhereHas('nhom', fn($nq) => $nq->where('TenNhom', 'like', "%{$s}%"))
                  ->orWhereHas('deTai', fn($dq) => $dq->where('TenDeTai', 'like', "%{$s}%"));
            });
        }

        $hoSos = $query->orderBy('updated_at', 'desc')->paginate(5)->withQueryString();

        $counts = [
            'total'          => (clone $base)->count(),
            'cho_kiem_tra'   => (clone $base)->where('TrangThaiChinhSuaSauBaoVe', 'DaNop')->count(),
            'yeu_cau_sua'    => (clone $base)->where('TrangThaiChinhSuaSauBaoVe', 'YeuCauSuaTiep')->count(),
            'da_hoan_thanh'  => (clone $base)->where('TrangThaiChinhSuaSauBaoVe', 'DaHoanThanh')->count(),
        ];

        return view('giangvien.kiemtra_baocao_sau_baove.index', compact('gv', 'hoSos', 'counts'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) abort(404);

        $hoSo = HoSoBaoVe::with([
            'nhom.truongNhom',
            'nhom.thanhViens.sinhVien',
            'nhom.hocPhan',
            'deTai.giangVien',
            'hoiDong',
            'baoCaoSauBaoVes.sinhVienNop',
            'baoCaoSauBaoVeMoiNhat',
        ])->findOrFail($id);

        abort_unless($hoSo->deTai?->MaGV === $gv->MaGV, 403);
        abort_unless($hoSo->baoCaoSauBaoVeMoiNhat, 404);

        $latestBaoCao = $hoSo->baoCaoSauBaoVeMoiNhat;
        $lichSuNop = $hoSo->baoCaoSauBaoVes;

        return view('giangvien.kiemtra_baocao_sau_baove.show', compact('gv', 'hoSo', 'latestBaoCao', 'lichSuNop'));
    }

    public function xacNhan(Request $request, string $id)
    {
        $gv = GiangVien::getLoggedInGiangVien($request->user());
        abort_unless($gv, 403);

        $data = $request->validate(['bao_cao_id' => 'required|integer']);

        DB::transaction(function () use ($id, $gv, $data) {
            $hoSo = HoSoBaoVe::lockForUpdate()->findOrFail($id);
            abort_unless($hoSo->deTai?->MaGV === $gv->MaGV, 403);

            $baoCao = $hoSo->baoCaoSauBaoVeMoiNhat()->firstOrFail();
            abort_unless((int) $baoCao->id === (int) $data['bao_cao_id'], 409, 'Bản nộp đã thay đổi. Vui lòng tải lại trang.');

            if ($baoCao->TrangThai === 'Đạt') {
                return;
            }

            abort_unless(in_array($baoCao->TrangThai, ['Đã nộp', 'Yêu cầu sửa tiếp'], true), 409);
            abort_if(blank($baoCao->DuongDanFile), 409, 'Bản nộp chưa có tệp báo cáo.');

            $ngayXacNhan = now();
            $baoCao->update([
                'TrangThai' => 'Đạt',
                'NgayKiemTra' => $ngayXacNhan,
                'NguoiKiemTra' => $gv->HoTen,
            ]);
            $hoSo->update([
                'TrangThaiChinhSuaSauBaoVe' => 'DaHoanThanh',
                'TrangThaiKetQua' => 'DaHoanThanh',
                'NguoiKiemTraChinhSua' => $gv->MaGV,
                'NgayXacNhanHoanThanhSauBaoVe' => $ngayXacNhan,
            ]);

            $noiDung = "Giảng viên hướng dẫn đã xác nhận báo cáo hoàn thiện của nhóm {$hoSo->nhom?->TenNhom}.";
            ThongBaoService::guiDenNhom($hoSo->MaNhom, 'Đã xác nhận báo cáo sau bảo vệ', $noiDung, 'Báo cáo sau bảo vệ');
            ThongBaoService::thongBaoCanBoQuanLy($hoSo, 'GVHD đã xác nhận báo cáo sau bảo vệ', $noiDung);
        });

        return back()->with('success', 'Đã xác nhận báo cáo sau bảo vệ.');
    }
}
