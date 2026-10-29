<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Models\HoSoBaoVe;
use App\Models\BaoCaoSauBaoVe;
use App\Services\ThongBaoService;
use App\Helpers\HtmlHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KiemTraBaoCaoSauBaoVeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        // Tìm tất cả hồ sơ mà giảng viên là GVHD hoặc được Trưởng khoa phân công kiểm tra báo cáo sau bảo vệ
        $query = HoSoBaoVe::with([
            'nhom.truongNhom',
            'nhom.thanhViens.sinhVien',
            'nhom.hocPhan',
            'deTai.giangVien',
            'hoiDong',
            'baoCaoSauBaoVes.sinhVienNop',
            'baoCaoSauBaoVeMoiNhat',
        ])
        ->where(function ($q) {
            $q->whereNotNull('YeuCauChinhSuaSauBaoVe')
              ->orWhereNotNull('TrangThaiChinhSuaSauBaoVe')
              ->orWhereHas('baoCaoSauBaoVes');
        })
        ->where(function ($q) use ($gv) {
            $q->where('NguoiKiemTraChinhSua', $gv->MaGV)
              ->orWhere('NguoiKiemTraChinhSua', 'like', "%{$gv->HoTen}%")
              ->orWhereHas('deTai', fn($d) => $d->where('MaGV', $gv->MaGV));
        });

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

        $hoSos = $query->orderBy('updated_at', 'desc')->paginate(12)->withQueryString();

        // Thống kê
        $base = HoSoBaoVe::where(function ($q) {
                $q->whereNotNull('YeuCauChinhSuaSauBaoVe')
                  ->orWhereNotNull('TrangThaiChinhSuaSauBaoVe')
                  ->orWhereHas('baoCaoSauBaoVes');
            })
            ->where(function ($q) use ($gv) {
                $q->where('NguoiKiemTraChinhSua', $gv->MaGV)
                  ->orWhere('NguoiKiemTraChinhSua', 'like', "%{$gv->HoTen}%")
                  ->orWhereHas('deTai', fn($d) => $d->where('MaGV', $gv->MaGV));
            });

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

        $isAuthorized = ($hoSo->NguoiKiemTraChinhSua === $gv->MaGV)
            || str_contains($hoSo->NguoiKiemTraChinhSua ?? '', $gv->HoTen)
            || ($hoSo->deTai && $hoSo->deTai->MaGV === $gv->MaGV);

        if (!$isAuthorized) {
            abort(403, 'Bạn không được phân công kiểm tra báo cáo sau bảo vệ của nhóm này.');
        }

        $latestBaoCao = $hoSo->baoCaoSauBaoVeMoiNhat;
        $lichSuNop = $hoSo->baoCaoSauBaoVes;

        return view('giangvien.kiemtra_baocao_sau_baove.show', compact('gv', 'hoSo', 'latestBaoCao', 'lichSuNop'));
    }

    public function danhGia(Request $request, $id)
    {
        $request->validate([
            'HanhDong'   => 'required|in:dat,yeu_cau_sua_tiep',
            'NhanXetGV'  => 'required|string|max:3000',
            'HanNopMoi'  => 'nullable|date|after:now',
        ], [
            'NhanXetGV.required' => 'Vui lòng nhập ý kiến nhận xét chi tiết về bản báo cáo chỉnh sửa của nhóm.',
            'HanNopMoi.after'    => 'Thời hạn nộp lại mới phải sau thời điểm hiện tại.',
        ]);

        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) abort(404);

        $hoSo = HoSoBaoVe::with(['nhom.truongNhom', 'nhom.deTai.giangVien', 'hoiDong', 'baoCaoSauBaoVeMoiNhat'])->findOrFail($id);

        $isAuthorized = ($hoSo->NguoiKiemTraChinhSua === $gv->MaGV)
            || str_contains($hoSo->NguoiKiemTraChinhSua ?? '', $gv->HoTen)
            || ($hoSo->deTai && $hoSo->deTai->MaGV === $gv->MaGV);

        if (!$isAuthorized) {
            abort(403, 'Bạn không có quyền đánh giá hồ sơ này.');
        }

        $latestBaoCao = $hoSo->baoCaoSauBaoVeMoiNhat;
        if (!$latestBaoCao) {
            return redirect()->back()->with('error', 'Nhóm sinh viên chưa nộp tệp báo cáo chỉnh sửa nào.');
        }

        $isDat = ($request->HanhDong === 'dat');
        $cleanedComment = HtmlHelper::toPlainText($request->NhanXetGV);

        // Cập nhật bản ghi BaoCaoSauBaoVe hiện tại
        $latestBaoCao->update([
            'TrangThai'    => $isDat ? 'Đạt' : 'Yêu cầu sửa tiếp',
            'NhanXetGV'    => $cleanedComment,
            'NgayKiemTra'  => now(),
            'NguoiKiemTra' => $gv->HoTen,
        ]);

        if ($isDat) {
            $hoSo->update([
                'TrangThaiChinhSuaSauBaoVe'   => 'DaHoanThanh',
                'TrangThaiKetQua'             => 'DaHoanThanh',
                'NgayXacNhanHoanThanhSauBaoVe'=> now(),
            ]);

            // Thông báo cho nhóm sinh viên
            ThongBaoService::guiDenNhom(
                $hoSo->MaNhom,
                'Báo cáo chỉnh sửa sau bảo vệ ĐÃ ĐƯỢC XÁC NHẬN ĐẠT',
                "Thầy/Cô {$gv->HoTen} đã kiểm tra bản chỉnh sửa sau bảo vệ của nhóm và chính thức XÁC NHẬN ĐẠT YÊU CẦU. Khóa luận tốt nghiệp của nhóm đã hoàn tất quy trình lưu trữ tại Khoa.",
                'Hoàn thành'
            );

            // Thông báo cho Trưởng khoa
            if ($hoSo->hoiDong?->MaKhoa) {
                $tk = GiangVien::whereHas('boMon.khoa', fn($q) => $q->where('MaKhoa', $hoSo->hoiDong->MaKhoa))
                    ->whereHas('taiKhoan.vaiTro', fn($v) => $v->where('TenVaiTro', 'like', '%Trưởng khoa%'))
                    ->first();
                if ($tk) {
                    ThongBaoService::gui(
                        $tk->MaGV,
                        'Nhóm khóa luận đã hoàn thành báo cáo sau bảo vệ',
                        "Thầy/Cô {$gv->HoTen} đã kiểm tra và xác nhận ĐẠT cho báo cáo hoàn thiện sau bảo vệ của nhóm {$hoSo->nhom?->TenNhom} (Đề tài: {$hoSo->nhom?->deTai?->TenDeTai}).",
                        'Báo cáo sau bảo vệ'
                    );
                }
            }

            return redirect()->route('giangvien.kiemtra_baocao_sau_baove.show', $hoSo->MaHoSo)
                ->with('success', 'Đã xác nhận báo cáo chỉnh sửa ĐẠT YÊU CẦU thành công! Hồ sơ khóa luận đã chuyển sang trạng thái Đã hoàn thành.');
        } else {
            $updateData = [
                'TrangThaiChinhSuaSauBaoVe' => 'YeuCauSuaTiep',
            ];
            if ($request->filled('HanNopMoi')) {
                $updateData['HanNopBaoCaoSauBaoVe'] = Carbon::parse($request->HanNopMoi);
            }
            $hoSo->update($updateData);

            $hanStr = $request->filled('HanNopMoi') ? (' Hạn nộp mới: ' . Carbon::parse($request->HanNopMoi)->format('d/m/Y H:i') . '.') : '';

            // Thông báo cho nhóm sinh viên
            ThongBaoService::guiDenNhom(
                $hoSo->MaNhom,
                'Yêu cầu chỉnh sửa lại báo cáo sau bảo vệ',
                "Thầy/Cô {$gv->HoTen} đã kiểm tra bản nộp lại của nhóm và yêu cầu TIẾP TỤC CHỈNH SỬA. Nhận xét: " . strip_tags($cleanedComment) . ".{$hanStr} Vui lòng kiểm tra và nộp lại bản sửa đổi.",
                'Yêu cầu sửa lại'
            );

            return redirect()->route('giangvien.kiemtra_baocao_sau_baove.show', $hoSo->MaHoSo)
                ->with('warning', 'Đã gửi yêu cầu chỉnh sửa tiếp đến nhóm sinh viên thành công!');
        }
    }
}
