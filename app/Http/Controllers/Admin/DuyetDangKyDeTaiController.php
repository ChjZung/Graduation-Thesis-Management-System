<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DangKyDeTai;
use App\Models\HocKy;
use App\Services\ChiTieuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DuyetDangKyDeTaiController extends Controller
{
    /**
     * Giáo vụ xem, tra cứu và theo dõi danh sách đăng ký đề tài của các nhóm sinh viên.
     * Lưu ý: Việc phê duyệt/từ chối đơn đăng ký do Giảng viên hướng dẫn (GVHD) thực hiện.
     */
    public function index(Request $request)
    {
        $currentHocKy = HocKy::where('TrangThai', 1)->first() ?? HocKy::orderBy('MaHocKy', 'desc')->first();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        $counts = [
            'cho_duyet' => DangKyDeTai::where('TrangThai', 'Chờ duyệt')->count(),
            'da_duyet'  => DangKyDeTai::where('TrangThai', 'Đã duyệt')->count(),
            'tu_choi'   => DangKyDeTai::where('TrangThai', 'Từ chối')->count(),
            'total'     => DangKyDeTai::count(),
        ];

        $query = DangKyDeTai::with([
            'nhom.truongNhom.lop',
            'nhom.sinhViens.lop',
            'deTai.giangVien.boMon',
            'deTai.hocKy',
        ]);

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            $query->where('TrangThai', $request->TrangThai);
        }

        if ($request->filled('MaHocKy')) {
            $maHK = $request->MaHocKy;
            $query->whereHas('deTai', function($dq) use ($maHK) {
                $dq->where('MaHocKy', $maHK);
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $cleanSearch = preg_replace('/[^A-Za-z0-9]/', '', $search);
            $query->where(function($q) use ($search, $cleanSearch) {
                $q->where('MaDangKy', 'LIKE', "%{$search}%")
                  ->orWhere('MaNhom', 'LIKE', "%{$search}%")
                  ->orWhere('MaDeTai', 'LIKE', "%{$search}%");
                if (!empty($cleanSearch)) {
                    $q->orWhere('MaDangKy', 'LIKE', "%{$cleanSearch}%")
                      ->orWhere('MaNhom', 'LIKE', "%{$cleanSearch}%")
                      ->orWhere('MaDeTai', 'LIKE', "%{$cleanSearch}%");
                }
                $q->orWhereHas('nhom', function($nq) use ($search, $cleanSearch) {
                    $nq->where('TenNhom', 'LIKE', "%{$search}%")
                       ->orWhereHas('sinhViens', function($sq) use ($search, $cleanSearch) {
                           $sq->where('HoTen', 'LIKE', "%{$search}%")
                              ->orWhere('MaSV', 'LIKE', "%{$search}%");
                           if (!empty($cleanSearch)) {
                               $sq->orWhere('MaSV', 'LIKE', "%{$cleanSearch}%");
                           }
                       });
                })
                ->orWhereHas('deTai', function($dq) use ($search, $cleanSearch) {
                    $dq->where('TenDeTai', 'LIKE', "%{$search}%");
                    if (!empty($cleanSearch)) {
                        $dq->orWhere('MaDeTai', 'LIKE', "%{$cleanSearch}%");
                    }
                    $dq->orWhereHas('giangVien', function($gq) use ($search, $cleanSearch) {
                        $gq->where('HoTen', 'LIKE', "%{$search}%");
                        if (!empty($cleanSearch)) {
                            $gq->orWhere('MaGV', 'LIKE', "%{$cleanSearch}%");
                        }
                    });
                });
            });
        }

        $dangKys = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Danh sách nhóm chưa có đề tài và đề tài còn trống để phục vụ ngoại lệ tại VPK
        $nhomChuaCoDeTai = \App\Models\Nhom::whereDoesntHave('phieuDangKys', fn($q) => $q->where('TrangThai', 'Đã duyệt'))
            ->with(['truongNhom', 'thanhViens.sinhVien'])
            ->get();

        $deTaiConTrong = \App\Models\DeTai::where('TrangThai', 'Đã công bố')
            ->whereDoesntHave('phieuDangKys', fn($q) => $q->where('TrangThai', 'Đã duyệt'))
            ->with(['giangVien.boMon'])
            ->get();

        $regState = \App\Services\PlanPhaseService::getRegistrationState();

        return view('admin.duyet_dangky.index', compact('dangKys', 'counts', 'hocKies', 'currentHocKy', 'nhomChuaCoDeTai', 'deTaiConTrong', 'regState'));
    }

    /**
     * Giáo vụ giải quyết ngoại lệ tại VPK: Gán trực tiếp đề tài cho nhóm sinh viên
     */
    public function assignTopicForNgoaiLe(Request $request)
    {
        $request->validate([
            'MaNhom'  => 'required|exists:Nhom,MaNhom',
            'MaDeTai' => 'required|exists:DeTai,MaDeTai',
        ], [
            'MaNhom.required'  => 'Vui lòng chọn nhóm sinh viên.',
            'MaDeTai.required' => 'Vui lòng chọn đề tài muốn gán.',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $nhom = \App\Models\Nhom::where('MaNhom', $request->MaNhom)->lockForUpdate()->firstOrFail();
                $deTai = \App\Models\DeTai::where('MaDeTai', $request->MaDeTai)->lockForUpdate()->firstOrFail();

                // Kiểm tra đề tài đã có nhóm khác chiếm chưa
                $isTaken = DangKyDeTai::where('MaDeTai', $deTai->MaDeTai)
                    ->where('TrangThai', 'Đã duyệt')
                    ->lockForUpdate()
                    ->exists();

                if ($isTaken) {
                    throw new \Exception('Đề tài này vừa được gán cho một nhóm khác. Vui lòng chọn đề tài khác!');
                }

                // Kiểm tra giảng viên hướng dẫn đã nhận đủ chỉ tiêu chưa
                $gvDeTai = $deTai->MaGV;
                if ($gvDeTai) {
                    $gvRecord = \App\Models\GiangVien::where('MaGV', $gvDeTai)->lockForUpdate()->first();
                    $chiTieuStats = ChiTieuService::getChiTieuStats($gvDeTai, $deTai->MaHocKy, true);

                    if ($chiTieuStats['is_full']) {
                        $tenGV = $gvRecord?->HoTen ?? $gvDeTai;
                        throw new \Exception("Giảng viên hướng dẫn ({$tenGV}) đã nhận đủ chỉ tiêu hướng dẫn ({$chiTieuStats['da_dung']}/{$chiTieuStats['tong']} nhóm) trong học kỳ này! Hết chỉ tiêu.");
                    }
                }

                // Tạo bản ghi duyệt chính thức
                $maDK = 'DK_VPK_' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(5));
                DangKyDeTai::updateOrCreate(
                    ['MaNhom' => $nhom->MaNhom],
                    [
                        'MaDangKy'     => $maDK,
                        'MaDeTai'      => $deTai->MaDeTai,
                        'MaGVHuongDan' => $deTai->MaGV,
                        'NgayDangKy'   => now(),
                        'TrangThai'    => 'Đã duyệt',
                        'NgayDuyet'    => now(),
                        'LyDoTuChoi'   => null,
                    ]
                );

                $nhom->update(['MaDeTai' => $deTai->MaDeTai]);

                return redirect()->back()->with('success', "Xử lý ngoại lệ thành công! Đã gán đề tài '{$deTai->TenDeTai}' cho nhóm '{$nhom->TenNhom}'.");
            });
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * Giáo vụ chốt và công bố danh sách chính thức cho Giảng viên & Sinh viên
     */
    public function publishOfficialList(Request $request)
    {
        $hocKy = HocKy::where('TrangThai', 1)->first() ?? HocKy::orderBy('MaHocKy', 'desc')->first();
        $tenHocKy = $hocKy ? $hocKy->TenHocKy : 'Học kỳ hiện tại';

        $soLuongNhom = DangKyDeTai::where('TrangThai', 'Đã duyệt')->count();

        // Tạo thông báo phát toàn trường
        $maTB = 'TB_' . date('Y') . '_' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(4));
        \App\Models\ThongBao::create([
            'MaThongBao'   => $maTB,
            'TieuDe'       => "THÔNG BÁO CHÍNH THỨC: Danh sách Sinh viên đăng ký đề tài & GVHD {$tenHocKy}",
            'NoiDung'      => "Khoa Công nghệ Thông tin thông báo chính thức danh sách {$soLuongNhom} nhóm sinh viên đã hoàn tất đăng ký đề tài và Giảng viên hướng dẫn (GVHD) trong {$tenHocKy}. Đề nghị các nhóm sinh viên chủ động liên hệ GVHD qua email trước ngày 17/08/2026 để trao đổi nội dung kế hoạch thực hiện đề tài. Nếu nhóm không liên hệ GVHD đúng hạn sẽ bị xem như không thực hiện khóa luận theo thông báo số 27/TB-KCNTT.",
            'LoaiThongBao' => 'Thông báo khóa luận',
            'DoiTuongNhan' => 'Toàn trường',
            'NgayTao'      => now()->toDateString(),
            'TrangThai'    => 'Đã phát hành',
            'MaGVu'        => 'GVU01',
        ]);

        return redirect()->back()->with('success', "🎉 Đã công bố chính thức danh sách đề tài & GVHD! Hệ thống đã phát thông báo hướng dẫn liên hệ GVHD đến toàn thể Sinh viên và Giảng viên.");
    }

    /**
     * Xuất danh sách đăng ký đề tài ra file CSV
     */
    public function export(Request $request)
    {
        $query = DangKyDeTai::with([
            'nhom.truongNhom.lop',
            'nhom.sinhViens',
            'deTai.giangVien.boMon',
            'deTai.hocKy',
        ]);

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            $query->where('TrangThai', $request->TrangThai);
        }

        if ($request->filled('MaHocKy')) {
            $maHK = $request->MaHocKy;
            $query->whereHas('deTai', function($dq) use ($maHK) {
                $dq->where('MaHocKy', $maHK);
            });
        }

        $dangKys = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=Danh_Sach_Dang_Ky_De_Tai_" . date('Ymd_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Mã Đăng Ký', 'Mã Nhóm', 'Tên Nhóm', 'Trưởng Nhóm', 'Số Thành Viên', 
            'Mã Đề Tài', 'Tên Đề Tài', 'GV Hướng Dẫn', 'Bộ Môn', 
            'Trạng Thái GVHD', 'Ngày Đăng Ký', 'Ngày Duyệt', 'Lý Do Từ Chối'
        ];

        $callback = function() use ($dangKys, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($dangKys as $dk) {
                fputcsv($file, [
                    $dk->MaDangKy,
                    $dk->MaNhom,
                    $dk->nhom->TenNhom ?? $dk->MaNhom,
                    $dk->nhom->truongNhom->HoTen ?? 'Chưa rõ',
                    $dk->nhom->sinhViens->count() ?: 1,
                    $dk->MaDeTai,
                    $dk->deTai->TenDeTai ?? 'Chưa rõ',
                    $dk->deTai->giangVien->HoTen ?? 'Chưa rõ',
                    $dk->deTai->giangVien->boMon->TenBoMon ?? '',
                    $dk->TrangThai,
                    $dk->created_at ? $dk->created_at->format('d/m/Y H:i') : ($dk->NgayDangKy ?? ''),
                    $dk->NgayDuyet ?? '',
                    $dk->LyDoTuChoi ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
