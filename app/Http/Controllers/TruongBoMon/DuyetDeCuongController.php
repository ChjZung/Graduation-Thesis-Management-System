<?php

namespace App\Http\Controllers\TruongBoMon;

use App\Http\Controllers\Controller;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\PhanCongPhanBien;
use App\Models\ChiTietDuyetDeTai;
use App\Helpers\IdGenerator;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DuyetDeCuongController extends Controller
{
    private function getBoMon()
    {
        $user = Auth::user();
        if (!$user) return null;

        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv && $gv->MaBoMon) {
            $bm = BoMon::where('MaBoMon', $gv->MaBoMon)->orWhere('MaBoMon', 'BM_' . $gv->MaBoMon)->first();
            if ($bm) return $bm;
        }

        $allBoMons = BoMon::all();
        foreach ($allBoMons as $itemBm) {
            $code = $itemBm->MaBoMon;
            if (str_contains($user->TenDangNhap, '_' . $code . '_') || str_contains($user->TenDangNhap, '_' . $code) || str_contains($user->TenDangNhap, 'BM_' . $code)) {
                return $itemBm;
            }
        }

        if (!empty($gv?->HoTen)) {
            $found = BoMon::where('TruongBoMon', 'like', '%' . $gv->HoTen . '%')->first();
            if ($found) return $found;
        }

        return null;
    }

    /**
     * Ràng buộc phân quyền P0: Trưởng bộ môn chỉ thao tác trên đề tài thuộc Bộ môn mình phụ trách
     */
    private function assertDeTaiBelongsToBoMon(DeTai $detai, $boMon)
    {
        if (!$boMon) {
            abort(403, 'Bạn chưa được phân công phụ trách Bộ môn nào để thực hiện thao tác này.');
        }
        $detai->loadMissing('giangVien.boMon', 'hocPhanRef');
        $detaiMaBoMon = $detai->giangVien?->MaBoMon ?? $detai->hocPhanRef?->MaBoMon;
        $cleanDtBm = str_replace('BM_', '', $detaiMaBoMon ?? '');
        $cleanCurBm = str_replace('BM_', '', $boMon->MaBoMon ?? '');

        if (!$cleanDtBm || $cleanDtBm !== $cleanCurBm) {
            abort(403, "Bạn không có quyền thao tác trên đề tài không thuộc Bộ môn '{$boMon->TenBoMon}'.");
        }
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

        // Base query các đề tài có file đề cương hoặc bước phản biện / duyệt đề cương
        $baseQuery = DeTai::with(['giangVien.boMon', 'nganh', 'hocKy', 'phanCongPhanBiens.giangVien', 'chiTietDuyets'])
            ->where(function($q) use ($gvIds, $maBoMon) {
                $q->whereIn('MaGV', $gvIds)
                  ->orWhereHas('hocPhanRef', fn($hp) => $hp->where('MaBoMon', $maBoMon));
            })
            ->where(function($q) {
                $q->where(fn($file) => $file->whereNotNull('FileDeCuong')->where('FileDeCuong', '!=', ''))
                  ->orWhereIn('TrangThai', [
                      'Đã nộp đề cương - Chờ phân công PB',
                      'Đang phản biện đề cương',
                      'Đã cập nhật đề cương - Chờ phản biện lại',
                      'Đã phản biện - Chờ duyệt BM',
                      'Đã phản biện - Chờ TBM duyệt đề cương',
                      'Yêu cầu chỉnh sửa đề cương',
                      'Không đạt phản biện'
                  ]);
            });

        if ($selectedHocKy && $selectedHocKy !== 'ALL') {
            $baseQuery->where('MaHocKy', $selectedHocKy);
        }

        // Lọc theo Giảng viên đề xuất
        if ($request->filled('MaGV')) {
            $baseQuery->where('MaGV', $request->MaGV);
        }

        // Tìm kiếm
        if ($request->filled('search')) {
            $s = trim($request->search);
            $baseQuery->where(function ($q) use ($s) {
                $q->where('TenDeTai', 'like', "%{$s}%")
                  ->orWhere('MaDeTai', 'like', "%{$s}%")
                  ->orWhereHas('giangVien', fn($g) => $g->where('HoTen', 'like', "%{$s}%"));
            });
        }

        // Tab lọc
        $statusTab = $request->input('tab', 'cho_duyet');
        // Không tính hồ sơ có đường dẫn cũ nhưng tệp đã bị thiếu sau khi clone/di chuyển.
        $approvedIds = (clone $baseQuery)->withoutEagerLoads()->daDuyetDeCuong()
            ->get(['MaDeTai', 'FileDeCuong'])->filter(fn($topic) => $topic->coTepDeCuong())->pluck('MaDeTai');
        $query = clone $baseQuery;

        if ($statusTab === 'cho_duyet') {
            // Đề tài đã phản biện xong (Đạt) đang chờ TBM duyệt công bố (loại trừ các đề tài đang yêu cầu sửa)
            $query->where(function($q) {
                $q->whereIn('TrangThai', ['Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương'])
                  ->orWhere(function($subQ) {
                      $subQ->whereNotIn('TrangThai', [
                          'Đã công bố',
                          'Yêu cầu chỉnh sửa đề cương',
                          'Yêu cầu chỉnh sửa',
                          'Đang phản biện đề cương',
                          'Đã nộp đề cương - Chờ phân công PB',
                          'Đã cập nhật đề cương - Chờ phản biện lại',
                          'Không đạt phản biện',
                          'Từ chối'
                      ])->whereHas('phanCongPhanBiens', fn($pq) => $pq->where('VaiTro', 'Phản biện đề cương')->where('KetQua', 'Đạt'));
                  });
            })->where('TrangThai', '!=', 'Đã công bố');
        } elseif ($statusTab === 'dang_phan_bien') {
            $query->whereIn('TrangThai', ['Đang phản biện đề cương', 'Đã cập nhật đề cương - Chờ phản biện lại']);
        } elseif ($statusTab === 'yeu_cau_sua') {
            $query->whereIn('TrangThai', ['Yêu cầu chỉnh sửa đề cương']);
        } elseif ($statusTab === 'da_cong_bo') {
            $query->whereIn('MaDeTai', $approvedIds);
        } elseif ($statusTab === 'ALL') {
            // Hiển thị tất cả đề tài trong giai đoạn đề cương
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Counts
        $counts = [
            'cho_duyet' => (clone $baseQuery)->where(function($q) {
                $q->whereIn('TrangThai', ['Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương'])
                  ->orWhere(function($subQ) {
                      $subQ->whereNotIn('TrangThai', [
                          'Đã công bố',
                          'Yêu cầu chỉnh sửa đề cương',
                          'Yêu cầu chỉnh sửa',
                          'Đang phản biện đề cương',
                          'Đã nộp đề cương - Chờ phân công PB',
                          'Đã cập nhật đề cương - Chờ phản biện lại',
                          'Không đạt phản biện',
                          'Từ chối'
                      ])->whereHas('phanCongPhanBiens', fn($pq) => $pq->where('VaiTro', 'Phản biện đề cương')->where('KetQua', 'Đạt'));
                  });
            })->where('TrangThai', '!=', 'Đã công bố')->count(),

            'dang_phan_bien' => (clone $baseQuery)->whereIn('TrangThai', ['Đang phản biện đề cương', 'Đã cập nhật đề cương - Chờ phản biện lại'])->count(),

            'yeu_cau_sua' => (clone $baseQuery)->whereIn('TrangThai', ['Yêu cầu chỉnh sửa đề cương'])->count(),

            'da_cong_bo' => $approvedIds->count(),

            'total' => (clone $baseQuery)->count(),
        ];

        return view('truongbomon.duyet_decuong.index', compact('boMon', 'detais', 'counts', 'hocKies', 'giangViens', 'selectedHocKy', 'statusTab'));
    }

    public function duyetVaCongBo(Request $request, $id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        $maGVTBM = $gv ? $gv->MaGV : ($user->TenDangNhap ?? 'TBM');

        $boMon = $this->getBoMon();
        $detai = DeTai::findOrFail($id);
        $this->assertDeTaiBelongsToBoMon($detai, $boMon);

        // Khóa duyệt nếu đề tài đang bị yêu cầu chỉnh sửa đề cương hoặc từ chối
        if (in_array($detai->TrangThai, ['Yêu cầu chỉnh sửa đề cương', 'Yêu cầu chỉnh sửa', 'Không đạt phản biện', 'Từ chối'])) {
            return redirect()->back()->withErrors("Đề cương đang ở trạng thái '{$detai->TrangThai}'. Nút duyệt đã bị khóa, vui lòng chờ Giảng viên nộp lại bản đề cương hoàn thiện đạt yêu cầu trước khi phê duyệt.");
        }

        if (!$detai->coTepDeCuong()) {
            return redirect()->back()->withErrors('Chưa có tệp đề cương hợp lệ. Giảng viên hướng dẫn cần nộp đề cương trước khi phê duyệt.');
        }
        if ($detai->khoaNopDeCuong()) {
            return redirect()->back()->withErrors('Đề cương đã được phê duyệt hoặc đề tài đã hoàn thành.');
        }

        $phanBien = $detai->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
        if (!$phanBien || $phanBien->KetQua !== 'Đạt') {
            return redirect()->back()->withErrors('Đề cương chưa có kết quả phản biện ĐẠT. Không thể phê duyệt công bố đề tài.');
        }

        $oldStatus = $detai->TrangThai;

        DB::transaction(function () use ($detai, $maGVTBM, $gv, $oldStatus) {
            $detai->update([
                'TrangThai'    => 'Đã công bố',
                'NgayCongBo'   => now(),
                'NgayDuyetBM'  => now(),
                'NguoiDuyetBM' => $maGVTBM,
            ]);

            $maDuyet = IdGenerator::nextChiTietDuyetDeTai() ?? ('CTD' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT));
            ChiTietDuyetDeTai::create([
                'MaDuyet'       => $maDuyet,
                'MaDeTai'       => $detai->MaDeTai,
                'MaGV'          => $gv ? $gv->MaGV : null,
                'NguoiThucHien' => ($gv ? $gv->HoTen : 'Trưởng Bộ Môn') . ' - Trưởng Bộ Môn',
                'HanhDong'      => 'Phê duyệt đề cương & Công bố đề tài',
                'TrangThaiCu'   => $oldStatus,
                'TrangThai'     => 'Đã công bố',
                'LyDo'          => 'Trưởng bộ môn phê duyệt kết quả phản biện đề cương đạt yêu cầu và chính thức công bố đề tài.',
                'NgayDuyet'     => now(),
            ]);
        });

        // Gửi thông báo đến GV đề xuất
        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '🎉 Đề tài đã được Trưởng bộ môn duyệt đề cương và CÔNG BỐ',
                "Đề tài '{$detai->TenDeTai}' đã hoàn tất phản biện đề cương đạt yêu cầu và được Trưởng bộ môn phê duyệt chính thức CÔNG BỐ cho sinh viên đăng ký!",
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã phê duyệt đề cương và CÔNG BỐ đề tài '{$detai->TenDeTai}' thành công! Sinh viên hiện đã có thể xem và đăng ký đề tài này.");
    }

    public function requestEdit(Request $request, $id)
    {
        $request->validate([
            'YeuCauSua' => 'required|string',
        ], [
            'YeuCauSua.required' => 'Vui lòng nhập nội dung yêu cầu Giảng viên chỉnh sửa đề cương.',
        ]);

        $cleanedYeuCauSua = \App\Helpers\HtmlHelper::clean($request->YeuCauSua);
        $plainYeuCauSua = trim(strip_tags(html_entity_decode($cleanedYeuCauSua, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        if (mb_strlen($plainYeuCauSua) < 5) {
            return redirect()->back()->withInput()->withErrors(['YeuCauSua' => 'Vui lòng nhập nội dung yêu cầu chỉnh sửa chi tiết hơn (tối thiểu 5 ký tự).']);
        }

        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        $maGVTBM = $gv ? $gv->MaGV : ($user->TenDangNhap ?? 'TBM');

        $boMon = $this->getBoMon();
        $detai = DeTai::findOrFail($id);
        $this->assertDeTaiBelongsToBoMon($detai, $boMon);

        $oldStatus = $detai->TrangThai;

        DB::transaction(function () use ($detai, $cleanedYeuCauSua, $maGVTBM, $gv, $oldStatus) {
            $detai->update([
                'TrangThai'    => 'Yêu cầu chỉnh sửa đề cương',
                'LyDoTuChoi'   => $cleanedYeuCauSua,
                'NguoiDuyetBM' => $maGVTBM,
            ]);

            // Cập nhật trạng thái phân công phản biện để khóa duyệt và chờ giảng viên nộp lại
            PhanCongPhanBien::where('MaDeTai', $detai->MaDeTai)
                ->where('VaiTro', 'Phản biện đề cương')
                ->update([
                    'TrangThai' => 'Yêu cầu chỉnh sửa đề cương',
                ]);

            $maDuyet = IdGenerator::nextChiTietDuyetDeTai() ?? ('CTD' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT));
            ChiTietDuyetDeTai::create([
                'MaDuyet'       => $maDuyet,
                'MaDeTai'       => $detai->MaDeTai,
                'MaGV'          => $gv ? $gv->MaGV : null,
                'NguoiThucHien' => ($gv ? $gv->HoTen : 'Trưởng Bộ Môn') . ' - Trưởng Bộ Môn',
                'HanhDong'      => 'Yêu cầu chỉnh sửa đề cương',
                'TrangThaiCu'   => $oldStatus,
                'TrangThai'     => 'Yêu cầu chỉnh sửa đề cương',
                'LyDo'          => $cleanedYeuCauSua,
                'NgayDuyet'     => now(),
            ]);
        });

        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '⚠️ Yêu cầu chỉnh sửa đề cương từ Trưởng bộ môn',
                "Trưởng bộ môn đã xem xét đề cương đề tài '{$detai->TenDeTai}' và yêu cầu chỉnh sửa: '{$plainYeuCauSua}'. Vui lòng nộp lại file đề cương hoàn thiện.",
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã gửi yêu cầu chỉnh sửa đề cương đề tài '{$detai->TenDeTai}' tới Giảng viên hướng dẫn thành công!");
    }
}
