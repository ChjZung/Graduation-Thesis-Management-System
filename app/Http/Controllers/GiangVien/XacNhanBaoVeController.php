<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Models\HoSoBaoVe;
use App\Models\HocKy;
use App\Models\PhienBanHoSoBaoVe;
use App\Services\ThongBaoService;
use App\Helpers\HtmlHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class XacNhanBaoVeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        // Lấy danh sách học kỳ để phục vụ bộ lọc
        $hocKys = HocKy::orderBy('NamHoc', 'desc')->orderBy('TenHocKy', 'desc')->get();

        // Query hồ sơ thuộc các đề tài do giảng viên này hướng dẫn
        $query = HoSoBaoVe::with([
            'nhom.truongNhom',
            'nhom.thanhViens.sinhVien',
            'nhom.hocPhan',
            'nhom.hocKy',
            'deTai',
            'tepHoSoBaoVes',
            'phienBans',
        ])->whereHas('deTai', fn($d) => $d->where('MaGV', $gv->MaGV));

        // Lọc theo trạng thái xác nhận
        $statusFilter = $request->query('xac_nhan');
        if ($statusFilter === 'cho_xac_nhan') {
            $query->where(function ($q) {
                $q->whereNull('XacNhanGVHD')
                  ->orWhereIn('XacNhanGVHD', ['Chưa duyệt', 'Chờ xác nhận'])
                  ->orWhereIn('TrangThai', ['Chờ GVHD kiểm tra', 'Đang được kiểm tra', 'Đã nộp lại - Chờ kiểm tra', 'Chờ thẩm định']);
            });
        } elseif ($statusFilter === 'yeu_cau_sua') {
            $query->where('TrangThai', 'Yêu cầu chỉnh sửa');
        } elseif ($statusFilter === 'da_xac_nhan') {
            $query->where(function ($q) {
                $q->where('XacNhanGVHD', 'Đã duyệt')
                  ->orWhereIn('TrangThai', ['Đã phê duyệt', 'Đủ điều kiện bảo vệ']);
            });
        } elseif ($statusFilter === 'khong_dat') {
            $query->where(function ($q) {
                $q->where('XacNhanGVHD', 'Không duyệt')
                  ->orWhere('TrangThai', 'Không duyệt');
            });
        }

        // Lọc theo học kỳ
        if ($request->filled('hoc_ky')) {
            $query->whereHas('nhom', function ($qn) use ($request) {
                $qn->where('MaHocKy', $request->hoc_ky);
            });
        }

        // Tìm kiếm từ khóa
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('MaHoSo', 'like', "%{$s}%")
                  ->orWhereHas('nhom', function ($qn) use ($s) {
                      $qn->where('TenNhom', 'like', "%{$s}%")
                         ->orWhere('MaNhom', 'like', "%{$s}%")
                         ->orWhereHas('thanhViens.sinhVien', function ($sq) use ($s) {
                             $sq->where('HoTen', 'like', "%{$s}%")
                                ->orWhere('MaSV', 'like', "%{$s}%");
                         });
                  })
                  ->orWhereHas('deTai', function ($qd) use ($s) {
                      $qd->where('TenDeTai', 'like', "%{$s}%");
                  });
            });
        }

        $hoSos = $query->orderBy('NgayNopGanNhat', 'desc')
                       ->orderBy('created_at', 'desc')
                       ->paginate(12)
                       ->withQueryString();

        // Thống kê số lượng theo nhóm trạng thái
        $baseQuery = fn() => HoSoBaoVe::whereHas('deTai', fn($d) => $d->where('MaGV', $gv->MaGV));

        $counts = [
            'total'        => $baseQuery()->count(),
            'cho_xac_nhan' => $baseQuery()->where(function ($q) {
                $q->whereNull('XacNhanGVHD')
                  ->orWhereIn('XacNhanGVHD', ['Chưa duyệt', 'Chờ xác nhận'])
                  ->orWhereIn('TrangThai', ['Chờ GVHD kiểm tra', 'Đang được kiểm tra', 'Đã nộp lại - Chờ kiểm tra', 'Chờ thẩm định']);
            })->count(),
            'yeu_cau_sua'  => $baseQuery()->where('TrangThai', 'Yêu cầu chỉnh sửa')->count(),
            'da_xac_nhan'  => $baseQuery()->where(function ($q) {
                $q->where('XacNhanGVHD', 'Đã duyệt')
                  ->orWhereIn('TrangThai', ['Đã phê duyệt', 'Đủ điều kiện bảo vệ']);
            })->count(),
            'khong_dat'    => $baseQuery()->where(function ($q) {
                $q->where('XacNhanGVHD', 'Không duyệt')
                  ->orWhere('TrangThai', 'Không duyệt');
            })->count(),
        ];

        return view('giangvien.xacnhan_baove.index', compact('gv', 'hoSos', 'counts', 'hocKys'));
    }

    /**
     * Màn hình chi tiết kiểm tra và đánh giá hồ sơ bảo vệ
     */
    public function show($id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        $hoSo = HoSoBaoVe::with([
            'nhom.truongNhom',
            'nhom.thanhViens.sinhVien',
            'nhom.hocPhan',
            'nhom.hocKy',
            'deTai.giangVien',
            'tepHoSoBaoVes',
            'phienBans.sinhVienNop',
            'phienBans.giangVienDanhGia',
            'hoiDong',
        ])->findOrFail($id);

        if (!$hoSo->deTai || $hoSo->deTai->MaGV !== $gv->MaGV) {
            abort(403, 'Bạn không phải là Giảng viên hướng dẫn của nhóm khóa luận này.');
        }

        // Nếu trạng thái đang là 'Chờ GVHD kiểm tra' hoặc 'Đã nộp lại - Chờ kiểm tra', chuyển sang 'Đang được kiểm tra'
        if (in_array($hoSo->TrangThai, ['Chờ GVHD kiểm tra', 'Đã nộp lại - Chờ kiểm tra', 'Chờ thẩm định'])) {
            $hoSo->update(['TrangThai' => 'Đang được kiểm tra']);
        }

        return view('giangvien.xacnhan_baove.show', compact('gv', 'hoSo'));
    }

    /**
     * GVHD Phê duyệt hoặc Từ chối phê duyệt hồ sơ bảo vệ
     */
    public function xacNhan(Request $request, $id)
    {
        $request->validate([
            'HanhDong' => 'required|in:dong_y,khong_dong_y',
            'NhanXet'  => 'nullable|string|max:1500',
        ]);

        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        $hoSo = HoSoBaoVe::with(['deTai', 'nhom', 'phienBans'])->findOrFail($id);

        if (!$hoSo->deTai || $hoSo->deTai->MaGV !== $gv->MaGV) {
            abort(403, 'Bạn không phải Giảng viên hướng dẫn của nhóm này.');
        }

        if (in_array($hoSo->TrangThai, ['Đã phê duyệt', 'Đủ điều kiện bảo vệ', 'Đã phân công', 'Đã phân công hội đồng', 'Đã lưu trữ']) || $hoSo->XacNhanGVHD === 'Đã duyệt') {
            return back()->with('error', 'Hồ sơ này đã được phê duyệt chính thức đủ điều kiện bảo vệ. Không thể thực hiện lại thao tác này.');
        }

        $isApprove = ($request->HanhDong === 'dong_y');

        if (!$isApprove && empty(trim($request->NhanXet ?? ''))) {
            return back()->with('error', 'Vui lòng nhập lý do cụ thể khi không đồng ý cho nhóm tham gia bảo vệ.');
        }

        $cleanedNhanXet = $request->filled('NhanXet') ? HtmlHelper::clean($request->NhanXet) : null;
        $trangThaiMoi = $isApprove ? 'Đã phê duyệt' : 'Không duyệt';
        $xacNhanGVHDMoi = $isApprove ? 'Đã duyệt' : 'Không duyệt';

        $hoSo->update([
            'XacNhanGVHD'     => $xacNhanGVHDMoi,
            'TrangThai'       => $trangThaiMoi,
            'NgayXacNhan'     => now()->toDateString(),
            'NguoiDuyet'      => $gv->MaGV,
            'NhanXetGVHD'     => $cleanedNhanXet ?: ($isApprove ? 'Đồng ý cho nhóm tham gia bảo vệ khóa luận trước Hội đồng.' : 'Không đồng ý cho bảo vệ.'),
            'ThanhPhanCanSua' => null,
            'HanNopLai'       => null,
        ]);

        // Cập nhật bản ghi snapshot phiên bản mới nhất
        $latestPhienBan = $hoSo->phienBans->first();
        if ($latestPhienBan) {
            $latestPhienBan->update([
                'TrangThai'       => $isApprove ? 'Đã phê duyệt' : 'Từ chối',
                'NhanXetGVHD'     => $cleanedNhanXet ?: ($isApprove ? 'Đồng ý cho nhóm tham gia bảo vệ.' : 'Từ chối phê duyệt.'),
                'NgayDanhGia'     => now(),
                'NguoiDanhGia'    => $gv->MaGV,
                'ThanhPhanCanSua' => null,
            ]);
        }

        // Gửi thông báo đến toàn nhóm sinh viên
        $tieuDeTB = $isApprove 
            ? '✅ GVHD đã phê duyệt hồ sơ - Đủ điều kiện bảo vệ!' 
            : '⚠️ GVHD không duyệt hồ sơ bảo vệ';

        $noiDungTB = $isApprove 
            ? "Giảng viên hướng dẫn {$gv->HoTen} đã kiểm tra toàn bộ hồ sơ bảo vệ (báo cáo, mã nguồn dự án, sản phẩm triển khai) và chính thức XÁC NHẬN NHÓM ĐỦ ĐIỀU KIỆN tham gia Hội đồng bảo vệ khóa luận tốt nghiệp." . ($cleanedNhanXet ? " Nhận xét: " . strip_tags($cleanedNhanXet) : '')
            : "Giảng viên hướng dẫn {$gv->HoTen} KHÔNG ĐỒNG Ý cho nhóm bảo vệ khóa luận. Lý do: " . strip_tags($cleanedNhanXet);

        ThongBaoService::guiDenNhom(
            $hoSo->MaNhom,
            $tieuDeTB,
            $noiDungTB,
            'Bảo vệ',
            route('sinhvien.hoso.index', ['nhom_id' => $hoSo->MaNhom])
        );

        $msg = $isApprove 
            ? "Đã phê duyệt hồ sơ nhóm '{$hoSo->nhom->TenNhom}' đủ điều kiện tham gia Hội đồng bảo vệ!" 
            : "Đã ghi nhận ý kiến không đồng ý cho bảo vệ đối với nhóm '{$hoSo->nhom->TenNhom}'.";

        return redirect()->route('giangvien.xacnhan_baove.show', $hoSo->MaHoSo)->with('success', $msg);
    }

    /**
     * GVHD Yêu cầu nhóm sinh viên chỉnh sửa, bổ sung các thành phần hồ sơ
     */
    public function yeuCauChinhSua(Request $request, $id)
    {
        $request->validate([
            'ThanhPhanCanSua'   => 'required|array|min:1',
            'ThanhPhanCanSua.*' => 'in:bao_cao,source_code,link_deploy,turnitin,tai_lieu_khac',
            'NhanXet'           => 'required|string|max:2000',
            'HanNopLai'         => 'nullable|date',
        ], [
            'ThanhPhanCanSua.required' => 'Vui lòng đánh dấu ít nhất một thành phần hồ sơ cần chỉnh sửa hoặc bổ sung.',
            'NhanXet.required'         => 'Vui lòng nhập nội dung nhận xét và yêu cầu chỉnh sửa chi tiết.',
        ]);

        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        $hoSo = HoSoBaoVe::with(['deTai', 'nhom', 'phienBans'])->findOrFail($id);

        if (!$hoSo->deTai || $hoSo->deTai->MaGV !== $gv->MaGV) {
            abort(403, 'Bạn không phải Giảng viên hướng dẫn của nhóm này.');
        }

        if (in_array($hoSo->TrangThai, ['Đã phê duyệt', 'Đủ điều kiện bảo vệ', 'Đã phân công', 'Đã phân công hội đồng', 'Đã lưu trữ']) || $hoSo->XacNhanGVHD === 'Đã duyệt') {
            return back()->with('error', 'Hồ sơ này đã được phê duyệt đủ điều kiện bảo vệ. Không thể yêu cầu chỉnh sửa.');
        }

        $cleanedNhanXet = HtmlHelper::clean($request->NhanXet);

        $hoSo->update([
            'TrangThai'       => 'Yêu cầu chỉnh sửa',
            'XacNhanGVHD'     => 'Yêu cầu chỉnh sửa',
            'NhanXetGVHD'     => $cleanedNhanXet,
            'ThanhPhanCanSua' => $request->ThanhPhanCanSua,
            'HanNopLai'       => $request->HanNopLai ? Carbon::parse($request->HanNopLai) : null,
            'NguoiDuyet'      => $gv->MaGV,
        ]);

        // Cập nhật snapshot phiên bản mới nhất
        $latestPhienBan = $hoSo->phienBans->first();
        if ($latestPhienBan) {
            $latestPhienBan->update([
                'TrangThai'       => 'Yêu cầu chỉnh sửa',
                'NhanXetGVHD'     => $cleanedNhanXet,
                'ThanhPhanCanSua' => $request->ThanhPhanCanSua,
                'HanNopLai'       => $request->HanNopLai ? Carbon::parse($request->HanNopLai) : null,
                'NgayDanhGia'     => now(),
                'NguoiDanhGia'    => $gv->MaGV,
            ]);
        }

        // Tên các thành phần tiếng Việt
        $labels = [
            'bao_cao'       => 'Báo cáo khóa luận toàn văn (PDF)',
            'source_code'   => 'Mã nguồn dự án (GitHub/GitLab)',
            'link_deploy'   => 'Đường dẫn triển khai sản phẩm / Demo',
            'turnitin'      => 'Minh chứng kiểm tra đạo văn Turnitin',
            'tai_lieu_khac' => 'Tài liệu hướng dẫn / Liên kết bổ sung',
        ];

        $thanhPhanTexts = array_map(fn($k) => $labels[$k] ?? $k, $request->ThanhPhanCanSua);
        $thanhPhanListStr = implode(', ', $thanhPhanTexts);

        // Gửi thông báo đến nhóm
        $hanStr = $request->HanNopLai ? (' | Hạn hoàn thành: ' . Carbon::parse($request->HanNopLai)->format('d/m/Y H:i')) : '';
        $noiDungTB = "Giảng viên hướng dẫn {$gv->HoTen} đã kiểm tra hồ sơ bảo vệ và yêu cầu nhóm chỉnh sửa/bổ sung các mục: {$thanhPhanListStr}. Ý kiến GVHD: " . strip_tags($cleanedNhanXet) . $hanStr . ". Vui lòng cập nhật và nộp lại hồ sơ trước thời hạn.";

        ThongBaoService::guiDenNhom(
            $hoSo->MaNhom,
            '📝 Yêu cầu chỉnh sửa & bổ sung hồ sơ bảo vệ khóa luận',
            $noiDungTB,
            'Bảo vệ',
            route('sinhvien.hoso.index', ['nhom_id' => $hoSo->MaNhom])
        );

        return redirect()->route('giangvien.xacnhan_baove.show', $hoSo->MaHoSo)
            ->with('warning', "Đã gửi yêu cầu chỉnh sửa hồ sơ bảo vệ đến nhóm '{$hoSo->nhom->TenNhom}' thành công!");
    }
}
