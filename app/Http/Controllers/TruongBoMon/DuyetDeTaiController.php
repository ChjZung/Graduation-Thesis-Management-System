<?php

namespace App\Http\Controllers\TruongBoMon;

use App\Http\Controllers\Controller;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\PhanCongPhanBien;
use App\Helpers\IdGenerator;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DuyetDeTaiController extends Controller
{
    private function getBoMon()
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv && $gv->MaBoMon) {
            $bm = BoMon::where('MaBoMon', $gv->MaBoMon)->first();
            if ($bm) return $bm;
        }

        // Dự phòng: parse mã bộ môn từ Tên đăng nhập (ví dụ: TBM_BM_CNPM_GV00000001 -> BM_CNPM hoặc CNPM)
        if ($user && preg_match('/^TBM_(?:BM_)?([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
            $bm = BoMon::where('MaBoMon', $m[1])->orWhere('MaBoMon', 'BM_' . $m[1])->first();
            if ($bm) return $bm;
        }

        return BoMon::where('TruongBoMon', 'like', '%' . ($gv->HoTen ?? '') . '%')->first() ?? BoMon::first();
    }

    public function index(Request $request)
    {
        $boMon = $this->getBoMon();
        $maBoMon = $boMon ? $boMon->MaBoMon : 'BM_CNPM';

        $giangViens = GiangVien::where('MaBoMon', $maBoMon)->orderBy('HoTen')->get();
        $gvIds = $giangViens->pluck('MaGV');

        // Danh sách học kỳ
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $currentHocKy = HocKy::where('TrangThai', 'Đang diễn ra')->orderBy('MaHocKy', 'desc')->first() ?? $hocKies->first();
        $selectedHocKy = $request->input('MaHocKy', $currentHocKy?->MaHocKy ?? '');

        // Query cơ sở tính toán counts & filters: đề tài do GV thuộc bộ môn đề xuất HOẶC môn học thuộc bộ môn
        $baseScopeClosure = function($q) use ($gvIds, $maBoMon) {
            $q->whereIn('MaGV', $gvIds)
              ->orWhereHas('hocPhanRef', fn($hp) => $hp->where('MaBoMon', $maBoMon));
        };

        // Dropdowns bộ lọc
        $linhVucs = DeTai::where($baseScopeClosure)
            ->whereNotNull('LinhVuc')
            ->where('LinhVuc', '!=', '')
            ->distinct()
            ->pluck('LinhVuc');

        $hocPhans = DeTai::where($baseScopeClosure)
            ->whereNotNull('HocPhan')
            ->where('HocPhan', '!=', '')
            ->distinct()
            ->pluck('HocPhan');

        // Query cơ sở tính toán counts
        $baseCountQuery = DeTai::where($baseScopeClosure);
        if ($selectedHocKy && $selectedHocKy !== 'ALL') {
            $baseCountQuery->where('MaHocKy', $selectedHocKy);
        }
        if ($request->filled('MaGV')) {
            $baseCountQuery->where('MaGV', $request->MaGV);
        }
        if ($request->filled('LinhVuc')) {
            $baseCountQuery->where('LinhVuc', $request->LinhVuc);
        }
        if ($request->filled('HocPhan')) {
            $baseCountQuery->where('HocPhan', $request->HocPhan);
        }

        $choDuyetBM   = (clone $baseCountQuery)->where('TrangThai', 'Chờ duyệt cấp Bộ môn')->count();
        $dangPB       = (clone $baseCountQuery)->whereIn('TrangThai', ['Đang phản biện đề cương', 'Đã nộp đề cương - Chờ phân công PB'])->count();
        $daPB         = (clone $baseCountQuery)->whereIn('TrangThai', ['Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương'])->count();
        $choDuyetKhoa = (clone $baseCountQuery)->where('TrangThai', 'Chờ duyệt cấp Khoa')->count();
        $tkDaDuyet    = (clone $baseCountQuery)->whereIn('TrangThai', ['Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương'])->count();
        $daCongBo     = (clone $baseCountQuery)->where('TrangThai', 'Đã công bố')->count();
        $daDangKy     = (clone $baseCountQuery)->where('TrangThai', 'Đã đăng ký')->count();
        $hoanThanh    = (clone $baseCountQuery)->where('TrangThai', 'Hoàn thành')->count();
        $yeuCauSua    = (clone $baseCountQuery)->whereIn('TrangThai', ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương'])->count();
        $tuChoi       = (clone $baseCountQuery)->whereIn('TrangThai', ['Từ chối', 'Không đạt phản biện'])->count();
        $total        = (clone $baseCountQuery)->count();

        $countDangXetDuyet = (clone $baseCountQuery)->whereIn('TrangThai', [
            'Chờ duyệt cấp Bộ môn', 'Đang phản biện đề cương', 'Chờ duyệt cấp Khoa',
            'Đã nộp đề cương - Chờ phân công PB', 'Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương'
        ])->count();
        $countDaCongBoTab  = (clone $baseCountQuery)->whereIn('TrangThai', [
            'Đã công bố', 'Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã đăng ký', 'Hoàn thành'
        ])->count();
        $countTuChoiCanSua = (clone $baseCountQuery)->whereIn('TrangThai', [
            'Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương', 'Từ chối', 'Không đạt phản biện'
        ])->count();

        $counts = [
            'dang_xet_duyet'      => $countDangXetDuyet,
            'da_cong_bo_tab'      => $countDaCongBoTab,
            'tu_choi_can_sua'     => $countTuChoiCanSua,
            'cho_duyet_bm'        => $choDuyetBM,
            'dang_phan_bien'      => $dangPB,
            'da_phan_bien'        => $daPB,
            'can_duyet'           => ($choDuyetBM + $daPB),
            'cho_duyet_khoa'      => $choDuyetKhoa,
            'truong_khoa_da_duyet'=> $tkDaDuyet,
            'da_duyet_khoa'       => $tkDaDuyet,
            'da_cong_bo'          => $daCongBo,
            'da_dang_ky'          => $daDangKy,
            'hoan_thanh'          => $hoanThanh,
            'cho_duyet'           => ($choDuyetBM + $dangPB + $daPB),
            'yeu_cau_sua'         => $yeuCauSua,
            'tu_choi'             => $tuChoi,
            'total'               => $total,
        ];

        // -------------------------------------------------------------
        // TÍNH TOÁN DỮ LIỆU THỐNG KÊ HỌC THUẬT (TAB THỐNG KÊ BỘ MÔN)
        // -------------------------------------------------------------
        $allScopedDeTais = (clone $baseCountQuery)->with([
            'giangVien',
            'phieuDangKys.nhom.thanhVienNhoms'
        ])->get();

        $statTotal = $allScopedDeTais->count();
        $statDaCongBo = 0;
        $statDangDangKy = 0;
        $statDaDuNhom = 0;
        $statDangChoDuyet = 0;
        $statDaHoanThanh = 0;

        $tongMoDangKy = 0;
        $coNhomDangKy = 0;
        $conCho = 0;
        $daDuSV = 0;
        $chuaCoSVDangKy = 0;

        $statusChartCounts = [
            'Đã công bố' => 0,
            'Đang đăng ký / Đủ nhóm' => 0,
            'Đang chờ duyệt' => 0,
            'Hoàn thành' => 0,
            'Khác' => 0,
        ];
        $giangVienChartCounts = [];
        $linhVucChartCounts = [];
        $quyMoNhomChartCounts = [
            '1 sinh viên' => 0,
            '2 sinh viên' => 0,
            '3 sinh viên' => 0,
        ];

        foreach ($allScopedDeTais as $dt) {
            $maxSV = $dt->SoLuongSinhVienToiDa ?? 3;
            $regApproved = $dt->phieuDangKys ? $dt->phieuDangKys->where('TrangThai', 'Đã duyệt') : collect();
            $cntSV = 0;
            foreach ($regApproved as $p) {
                if ($p->nhom && $p->nhom->thanhVienNhoms) {
                    $cntSV += $p->nhom->thanhVienNhoms->count();
                } else {
                    $cntSV += 1;
                }
            }

            if ($dt->TrangThai === 'Hoàn thành') {
                $statDaHoanThanh++;
                $statusChartCounts['Hoàn thành']++;
            } elseif (in_array($dt->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Đang phản biện đề cương', 'Đã phản biện - Chờ duyệt BM', 'Yêu cầu chỉnh sửa'])) {
                $statDangChoDuyet++;
                $statusChartCounts['Đang chờ duyệt']++;
            } elseif ($dt->TrangThai === 'Từ chối') {
                $statusChartCounts['Khác']++;
            } elseif (in_array($dt->TrangThai, ['Đã công bố', 'Đã đăng ký', 'Trưởng khoa đã duyệt', 'Chờ duyệt cấp Khoa'])) {
                $tongMoDangKy++;
                if ($cntSV > 0) {
                    $coNhomDangKy++;
                    if ($cntSV >= $maxSV) {
                        $statDaDuNhom++;
                        $daDuSV++;
                    } else {
                        $statDangDangKy++;
                        $conCho++;
                    }
                    $statusChartCounts['Đang đăng ký / Đủ nhóm']++;
                } else {
                    $chuaCoSVDangKy++;
                    $conCho++;
                    $statDaCongBo++;
                    $statusChartCounts['Đã công bố']++;
                }
            } else {
                $statusChartCounts['Khác']++;
            }

            $gvName = $dt->giangVien->HoTen ?? 'Chưa phân GV';
            $giangVienChartCounts[$gvName] = ($giangVienChartCounts[$gvName] ?? 0) + 1;

            $lvName = $dt->LinhVuc ?: 'Lĩnh vực khác';
            $linhVucChartCounts[$lvName] = ($linhVucChartCounts[$lvName] ?? 0) + 1;

            if ($maxSV == 1) {
                $quyMoNhomChartCounts['1 sinh viên']++;
            } elseif ($maxSV == 2) {
                $quyMoNhomChartCounts['2 sinh viên']++;
            } else {
                $quyMoNhomChartCounts['3 sinh viên']++;
            }
        }

        arsort($linhVucChartCounts);
        $topLinhVucChartCounts = array_slice($linhVucChartCounts, 0, 8, true);
        arsort($giangVienChartCounts);

        $statsKPIs = [
            'total'          => $statTotal,
            'da_cong_bo'     => $statDaCongBo,
            'dang_dang_ky'   => $statDangDangKy,
            'da_du_nhom'     => $statDaDuNhom,
            'dang_cho_duyet' => $statDangChoDuyet,
            'da_hoan_thanh'  => $statDaHoanThanh,
        ];

        $dangKyStats = [
            'tong_cong_bo' => $tongMoDangKy,
            'co_nhom_dk'   => $coNhomDangKy,
            'con_cho'      => $conCho,
            'da_du_sv'     => $daDuSV,
            'chua_co_sv'   => $chuaCoSVDangKy,
        ];

        // -------------------------------------------------------------
        // QUERY DANH SÁCH ĐỀ TÀI PHÂN TRANG (CHUẨN 5 ĐỀ TÀI/TRANG)
        // -------------------------------------------------------------
        $query = DeTai::with([
            'giangVien.boMon', 
            'nganh', 
            'hocKy', 
            'phieuDangKys.nhom.thanhVienNhoms.sinhVien.lop', 
            'phanCongPhanBiens.giangVien'
        ])->where($baseScopeClosure);

        if ($selectedHocKy && $selectedHocKy !== 'ALL') {
            $query->where('MaHocKy', $selectedHocKy);
        }

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            if ($request->TrangThai === 'dang_xet_duyet' || $request->TrangThai === 'can_duyet') {
                $query->whereIn('TrangThai', [
                    'Chờ duyệt cấp Bộ môn', 'Đang phản biện đề cương', 'Chờ duyệt cấp Khoa',
                    'Đã nộp đề cương - Chờ phân công PB', 'Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương'
                ]);
            } elseif ($request->TrangThai === 'da_cong_bo') {
                $query->whereIn('TrangThai', [
                    'Đã công bố', 'Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã đăng ký', 'Hoàn thành'
                ]);
            } elseif ($request->TrangThai === 'tu_choi_can_sua') {
                $query->whereIn('TrangThai', [
                    'Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương', 'Từ chối', 'Không đạt phản biện'
                ]);
            } elseif ($request->TrangThai === 'dang_phan_bien' || $request->TrangThai === 'Đang phản biện đề cương') {
                $query->whereIn('TrangThai', ['Đang phản biện đề cương', 'Đã nộp đề cương - Chờ phân công PB']);
            } elseif ($request->TrangThai === 'da_phan_bien' || $request->TrangThai === 'Đã phản biện - Chờ duyệt BM') {
                $query->whereIn('TrangThai', ['Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương']);
            } elseif ($request->TrangThai === 'Trưởng khoa đã duyệt') {
                $query->whereIn('TrangThai', ['Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương']);
            } elseif ($request->TrangThai === 'Yêu cầu chỉnh sửa') {
                $query->whereIn('TrangThai', ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương']);
            } elseif ($request->TrangThai === 'Từ chối') {
                $query->whereIn('TrangThai', ['Từ chối', 'Không đạt phản biện']);
            } else {
                $query->where('TrangThai', $request->TrangThai);
            }
        }

        if ($request->filled('MaGV')) {
            $query->where('MaGV', $request->MaGV);
        }

        if ($request->filled('LinhVuc')) {
            $query->where('LinhVuc', $request->LinhVuc);
        }

        if ($request->filled('HocPhan')) {
            $query->where('HocPhan', $request->HocPhan);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $query->where(function ($q) use ($s, $sClean) {
                $q->where('TenDeTai', 'like', "%{$s}%")
                  ->orWhere('MaDeTai', 'like', "%{$s}%")
                  ->orWhere('LinhVuc', 'like', "%{$s}%");
                if (!empty($sClean)) {
                    $q->orWhere('MaDeTai', 'like', "%{$sClean}%");
                }
                $q->orWhereHas('giangVien', function($g) use ($s, $sClean) {
                    $g->where('HoTen', 'like', "%{$s}%");
                    if (!empty($sClean)) {
                        $g->orWhere('MaGV', 'like', "%{$sClean}%");
                    }
                });
            });
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(5)->withQueryString();

        return view('truongbomon.duyet_detai.index', compact(
            'boMon',
            'detais',
            'counts',
            'statsKPIs',
            'statusChartCounts',
            'giangVienChartCounts',
            'topLinhVucChartCounts',
            'quyMoNhomChartCounts',
            'dangKyStats',
            'hocKies',
            'currentHocKy',
            'selectedHocKy',
            'giangViens',
            'linhVucs',
            'hocPhans'
        ));
    }

    public function export(Request $request)
    {
        $boMon = $this->getBoMon();
        $maBoMon = $boMon ? $boMon->MaBoMon : 'CNPM';
        $gvIds = GiangVien::where('MaBoMon', $maBoMon)->pluck('MaGV');

        $query = DeTai::with(['giangVien.boMon', 'nganh', 'hocKy'])
            ->whereIn('MaGV', $gvIds);

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            $query->where('TrangThai', $request->TrangThai);
        }
        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }
        if ($request->filled('HocPhan')) {
            $query->where('HocPhan', $request->HocPhan);
        }
        if ($request->filled('MaGV')) {
            $query->where('MaGV', $request->MaGV);
        }

        $detais = $query->orderBy('MaDeTai')->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=Danh_Sach_De_Tai_BM_" . $maBoMon . "_" . date('Ymd_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Mã ĐT', 'Tên Đề Tài', 'Môn / Học Phần', 'Lĩnh Vực', 'Ngành', 'Giảng Viên Hướng Dẫn', 'Bộ Môn', 'Số SV Tối Đa', 'Học Kỳ', 'Trạng Thái', 'Có Đề Cương', 'Ngày Duyệt BM', 'Ngày Duyệt Khoa', 'Ngày Công Bố'];

        $callback = function() use ($detais, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach ($detais as $dt) {
                fputcsv($file, [
                    $dt->MaDeTai,
                    $dt->TenDeTai,
                    $dt->HocPhan ?? 'Khóa luận tốt nghiệp',
                    $dt->LinhVuc ?? '',
                    $dt->nganh->TenNganh ?? 'Toàn ngành',
                    $dt->giangVien->HoTen ?? '',
                    $dt->giangVien->boMon->TenBoMon ?? '',
                    $dt->SoLuongSinhVienToiDa ?? 3,
                    $dt->hocKy->TenHocKy ?? '',
                    $dt->TrangThai,
                    $dt->FileDeCuong ? 'Có' : 'Chưa có',
                    $dt->NgayDuyetBM ?? '',
                    $dt->NgayDuyetKhoa ?? '',
                    $dt->NgayCongBo ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show($id)
    {
        $boMon = $this->getBoMon();
        $detai = DeTai::with(['giangVien.boMon', 'hocKy', 'nganh', 'phanCongPhanBiens.giangVien'])
            ->findOrFail($id);

        $user = Auth::user();
        $currentGv = GiangVien::getLoggedInGiangVien($user);

        $maBoMon = $detai->giangVien?->MaBoMon ?? ($boMon?->MaBoMon ?? 'CNPM');
        $allGiangViens = GiangVien::with('boMon')
            ->where('MaBoMon', $maBoMon)
            ->where('MaGV', '!=', $detai->MaGV)
            ->when($currentGv, fn($q) => $q->where('MaGV', '!=', $currentGv->MaGV))
            ->orderBy('HoTen')
            ->get();
        $phanBienHienTai = $detai->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');

        return view('truongbomon.duyet_detai.show', compact('boMon', 'detai', 'allGiangViens', 'phanBienHienTai'));
    }

    public function phanCongPhanBien(Request $request, $id)
    {
        $request->validate([
            'MaGVPhanBien' => 'required|exists:GiangVien,MaGV',
        ], [
            'MaGVPhanBien.required' => 'Vui lòng chọn Giảng viên phản biện đề cương.',
        ]);

        $detai = DeTai::findOrFail($id);

        if (!$detai->FileDeCuong) {
            return redirect()->back()->withErrors('Đề tài chưa có file Đề cương chi tiết. Không thể phân công phản biện ở giai đoạn này!');
        }

        // Trưởng bộ môn không được tự phân công phản biện cho bản thân
        $user = Auth::user();
        $currentGv = GiangVien::getLoggedInGiangVien($user);
        if ($currentGv && $request->MaGVPhanBien === $currentGv->MaGV) {
            return redirect()->back()->withErrors('Trưởng bộ môn không được tự phân công phản biện đề cương cho chính bản thân! Vui lòng phân công cho giảng viên khác trong bộ môn.');
        }

        // Quy tắc BR07: GV đề xuất đề tài không được làm GV phản biện chính đề tài đó
        if ($detai->MaGV === $request->MaGVPhanBien) {
            return redirect()->back()->withErrors('Giảng viên đề xuất đề tài không được làm Giảng viên phản biện cho đề tài này!');
        }

        // Giảng viên phản biện phải thuộc cùng Bộ môn với đề tài
        $gvPB = GiangVien::find($request->MaGVPhanBien);
        if ($detai->giangVien && $gvPB && $gvPB->MaBoMon !== $detai->giangVien->MaBoMon) {
            return redirect()->back()->withErrors('Giảng viên phản biện phải thuộc cùng Bộ môn với đề tài (' . ($detai->giangVien->boMon->TenBoMon ?? '') . ')!');
        }

        DB::transaction(function () use ($request, $detai) {
            $maPC = IdGenerator::nextPhanCong();

            PhanCongPhanBien::updateOrCreate(
                [
                    'MaDeTai' => $detai->MaDeTai,
                    'VaiTro'  => 'Phản biện đề cương',
                ],
                [
                    'MaPhanCong'   => $maPC,
                    'MaGV'         => $request->MaGVPhanBien,
                    'NgayPhanCong' => now()->toDateString(),
                    'TrangThai'    => 'Đang phản biện',
                    'NhanXet'      => null,
                    'KetQua'       => null,
                    'NgayDanhGia'  => null,
                ]
            );

            $detai->update([
                'TrangThai' => 'Đang phản biện đề cương',
            ]);
        });

        // Gửi thông báo đến GV phản biện
        $gvPB = GiangVien::find($request->MaGVPhanBien);
        if ($gvPB && $gvPB->MaTK) {
            ThongBaoService::guiDen(
                $gvPB->MaTK,
                '📋 Phân công phản biện đề cương đề tài',
                "Bạn vừa được Trưởng bộ môn phân công phản biện đề cương cho đề tài: '{$detai->TenDeTai}'. Vui lòng xem và gửi nhận xét đánh giá.",
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã phân công Giảng viên phản biện đề cương cho đề tài '{$detai->TenDeTai}' thành công!");
    }

    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);

        $detai = DeTai::findOrFail($id);

        if (in_array($detai->TrangThai, ['Đã công bố', 'Trưởng khoa đã duyệt']) && !empty($detai->NgayDuyetKhoa)) {
            return redirect()->back()->withErrors('Đề tài đã được Trưởng khoa phê duyệt chính thức, quyết định đã hoàn tất.');
        }

        $detai->update([
            'TrangThai'    => 'Chờ duyệt cấp Khoa',
            'NgayDuyetBM'  => now(),
            'NguoiDuyetBM' => $gv ? $gv->MaGV : ($user->TenDangNhap ?? 'TBM'),
            'LyDoTuChoi'   => null,
        ]);

        // Gửi thông báo cho GV đề xuất
        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '✅ Đề tài đã được Trưởng bộ môn phê duyệt',
                "Đề tài '{$detai->TenDeTai}' đã được Bộ môn phê duyệt và chuyển tiếp lên Trưởng khoa xem xét.",
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã duyệt đề tài '{$detai->TenDeTai}' ở cấp Bộ môn thành công! Hồ sơ đã được chuyển tiếp lên Trưởng khoa phê duyệt.");
    }

    public function duyetVaCongBo(Request $request, $id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        $maGVTBM = $gv ? $gv->MaGV : ($user->TenDangNhap ?? 'TBM');

        $detai = DeTai::findOrFail($id);

        $phanBien = $detai->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
        if (!$phanBien || $phanBien->KetQua !== 'Đạt') {
            return redirect()->back()->withErrors('Đề cương chưa có kết quả phản biện ĐẠT. Không thể duyệt công bố đề tài.');
        }

        DB::transaction(function () use ($detai, $maGVTBM) {
            $detai->update([
                'TrangThai'    => 'Đã công bố',
                'NgayCongBo'   => now(),
                'NgayDuyetBM'  => now(),
                'NguoiDuyetBM' => $maGVTBM,
            ]);
        });

        // Gửi thông báo đến GV đề xuất
        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '🎉 Đề tài đã được Trưởng bộ môn duyệt đề cương và CÔNG BỐ',
                "Đề tài '{$detai->TenDeTai}' đã hoàn tất phản biện đạt yêu cầu và được Trưởng bộ môn phê duyệt chính thức CÔNG BỐ cho sinh viên đăng ký!",
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã phê duyệt đề cương và CÔNG BỐ đề tài '{$detai->TenDeTai}' thành công! Sinh viên hiện đã có thể xem và đăng ký đề tài này.");
    }

    public function requestEdit(Request $request, $id)
    {
        $request->validate([
            'YeuCauSua' => 'required|string|max:1000',
        ], [
            'YeuCauSua.required' => 'Vui lòng nhập nội dung yêu cầu Giảng viên chỉnh sửa.',
        ]);

        $detai = DeTai::findOrFail($id);

        if (in_array($detai->TrangThai, ['Đã công bố', 'Trưởng khoa đã duyệt']) && !empty($detai->NgayDuyetKhoa)) {
            return redirect()->back()->withErrors('Đề tài đã được Trưởng khoa phê duyệt chính thức. Không thể yêu cầu chỉnh sửa đề tài này.');
        }

        $detai->update([
            'TrangThai'   => 'Yêu cầu chỉnh sửa',
            'LyDoTuChoi'  => trim($request->YeuCauSua),
        ]);

        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '⚠️ Yêu cầu chỉnh sửa đề tài từ Trưởng bộ môn',
                "Đề tài '{$detai->TenDeTai}' có yêu cầu chỉnh sửa từ Trưởng bộ môn: " . trim($request->YeuCauSua),
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã gửi yêu cầu chỉnh sửa đề tài tới Giảng viên đề xuất.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'LyDoTuChoi' => 'required|string|max:1000',
        ], [
            'LyDoTuChoi.required' => 'Vui lòng nhập lý do từ chối đề tài.',
        ]);

        $detai = DeTai::findOrFail($id);

        if (in_array($detai->TrangThai, ['Đã công bố', 'Trưởng khoa đã duyệt']) && !empty($detai->NgayDuyetKhoa)) {
            return redirect()->back()->withErrors('Đề tài đã được Trưởng khoa phê duyệt chính thức. Không thể từ chối đề tài này.');
        }

        $detai->update([
            'TrangThai'  => 'Từ chối',
            'LyDoTuChoi' => trim($request->LyDoTuChoi),
        ]);

        $detai->loadMissing('giangVien.taiKhoan');
        $gv = $detai->giangVien;
        $maTK = $gv?->MaTK ?? $gv?->taiKhoan?->MaTK;
        if (!$maTK && $gv) {
            $maTK = \App\Models\TaiKhoan::where('TenDangNhap', $gv->MaGV)->orWhere('Email', $gv->Email)->value('MaTK');
        }
        if ($maTK) {
            ThongBaoService::guiDen(
                $maTK,
                '❌ Đề tài bị Trưởng bộ môn từ chối',
                "Đề tài '{$detai->TenDeTai}' đã bị Trưởng bộ môn từ chối. Lý do: " . trim($request->LyDoTuChoi),
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã từ chối đề tài '{$detai->TenDeTai}'.");
    }
}
