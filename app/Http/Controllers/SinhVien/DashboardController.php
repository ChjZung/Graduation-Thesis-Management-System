<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Models\Nhom;
use App\Models\PhieuDangKy;
use App\Models\BaoCaoTienDo;
use App\Models\GiaiDoanThucHien;
use App\Models\DotNopBaoCao;
use App\Models\KhoiBaoCaoTienDo;
use App\Models\TienDoNghiepVuKhoaLuan;
use App\Models\LichGapHuongDan;
use App\Models\HocKy;
use App\Models\MocThoiGianKhoaLuan;
use App\Models\KeHoachKhoaLuan;
use App\Models\HoSoBaoVe;
use App\Models\KetQuaSinhVien;
use App\Models\ThongBao;
use App\Services\ThesisProgressService;
use App\Services\ThongBaoService;
use App\Services\HtmlSanitizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::with(['khoa', 'nganh', 'lop.nganh.khoa'])->where('MaTK', $user->MaTK)->first();

        if (!$sinhVien) {
            return redirect()->route('login')->withErrors('Không tìm thấy thông tin sinh viên.');
        }

        // 1. Học kỳ hiện tại
        $hocKyHienTai = HocKy::where('TrangThai', 'Đang diễn ra')
            ->orWhere('TrangThai', '1')
            ->orWhere('TrangThai', 'Đang mở')
            ->orderBy('MaHocKy', 'desc')
            ->first() ?? HocKy::latest('MaHocKy')->first();

        // 2. Kiểm tra điều kiện xét duyệt làm khóa luận
        $dkRecord = null;
        if ($hocKyHienTai) {
            $dkRecord = \App\Models\DanhSachSVDuDieuKien::where('MaSV', $sinhVien->MaSV)
                ->where('MaHocKy', $hocKyHienTai->MaHocKy)
                ->first();
        }
        $isDuDieuKien = $dkRecord ? ($dkRecord->TrangThai === 'Đủ điều kiện') : $sinhVien->isDuDieuKien();

        // 3. Lấy thông tin các nhóm & đề tài (hỗ trợ sinh viên tham gia nhiều nhóm / học phần)
        $hocKyMa = $hocKyHienTai?->MaHocKy;
        $allStudentMemberships = ThanhVienNhom::with([
            'nhom.hocPhan',
            'nhom.hocKy',
            'nhom.dangKyDeTai.deTai.giangVien',
            'nhom.thanhViens.sinhVien',
        ])
        ->where('MaSV', $sinhVien->MaSV)
        ->where('TrangThai', 'da_tham_gia')
        ->get();

        $selectedNhomId = $request->get('nhom_id');
        $activeMembership = null;
        if ($selectedNhomId) {
            $activeMembership = $allStudentMemberships->firstWhere('MaNhom', $selectedNhomId);
        }
        if (!$activeMembership && $allStudentMemberships->isNotEmpty()) {
            $activeMembership = $allStudentMemberships->first();
        }

        $thanhVienNhom = $activeMembership;

        $nhom = null;
        $phieuDangKy = null;
        $deTai = null;
        $gvhd = null;
        $truongNhom = null;
        $thanhViens = collect();
        $baoCaos = collect();
        $lichGaps = collect();
        $hoSoBaoVe = null;
        $stageBreakdown = [];

        if ($thanhVienNhom) {
            $nhom = Nhom::with([
                'thanhViens.sinhVien.lop',
                'deTai.giangVien.boMon',
                'hocPhan',
                'hocKy',
            ])->find($thanhVienNhom->MaNhom);

            if ($nhom) {
                $stageBreakdownData = ThesisProgressService::getStageProgressBreakdown($nhom);
                $stageBreakdown = $stageBreakdownData['stages'] ?? [];
                $nhom->stage_breakdown = $stageBreakdown;
                $nhom->progress_percent = $stageBreakdownData['overall_percent'] ?? 0;
                $thanhViens = $nhom->thanhViens;
                $truongNhom = $thanhViens->firstWhere('VaiTro', 'Nhóm trưởng')?->sinhVien 
                    ?? $thanhViens->firstWhere('VaiTro', 'Trưởng nhóm')?->sinhVien
                    ?? $thanhViens->first()?->sinhVien;

                $phieuDangKy = PhieuDangKy::with('deTai.giangVien.boMon')
                    ->where('MaNhom', $nhom->MaNhom)
                    ->latest()
                    ->first();

                $deTai = $nhom->deTai ?? $phieuDangKy?->deTai;
                $gvhd = $deTai?->giangVien;

                if ($deTai) {
                    $baoCaos = BaoCaoTienDo::with('tomTatBaoCao')
                        ->where('MaDeTai', $deTai->MaDeTai)
                        ->orderBy('LanBaoCao')
                        ->get();
                }

                $lichGaps = LichGapHuongDan::where('MaNhom', $nhom->MaNhom)
                    ->orderBy('ThoiGianBatDau', 'desc')
                    ->take(3)
                    ->get();

                $hoSoBaoVe = HoSoBaoVe::with(['hoiDong.thanhViens.giangVien'])
                    ->where('MaNhom', $nhom->MaNhom)
                    ->first();
            }
        }

        // 4. Kế hoạch khóa luận & Mốc thời gian
        $activePlan = KeHoachKhoaLuan::with(['mocThoiGians' => fn($q) => $q->orderBy('NgayBatDau')])
            ->where(function($q) use ($hocKyHienTai) {
                if ($hocKyHienTai) {
                    $q->where('MaHocKy', $hocKyHienTai->MaHocKy);
                }
            })
            ->whereIn('TrangThai', ['ĐANG THỰC HIỆN', 'ĐÃ CÔNG BỐ', '1'])
            ->first() ?? KeHoachKhoaLuan::with(['mocThoiGians' => fn($q) => $q->orderBy('NgayBatDau')])->latest()->first();

        $allMocs = $activePlan ? $activePlan->mocThoiGians : collect();
        $today = now()->format('Y-m-d');
        
        $nextMoc = null;
        $daysRemaining = null;

        if ($nhom) {
            $nextMoc = $allMocs->first(function($m) use ($today) {
                return $m->NgayKetThuc >= $today;
            }) ?? $allMocs->last();

            if ($nextMoc && $nextMoc->NgayKetThuc) {
                $endCarbon = Carbon::parse($nextMoc->NgayKetThuc)->endOfDay();
                $daysRemaining = (int) ceil(now()->diffInDays($endCarbon, false));
            }
        }

        // 5. Kết quả khóa luận (nếu có)
        $ketQua = KetQuaSinhVien::where('MaSV', $sinhVien->MaSV)->first();

        // 6. TÍNH TOÁN 6 GIAI ĐOẠN TIẾN ĐỘ NGHIỆP VỤ HỌC PHẦN (ThesisProgressService)
        $thesisProgressService = app(ThesisProgressService::class);
        $tienDoNghiepVu = null;
        $dieuKienNghiepVu = [];
        $giaiDoanHienTaiNghiepVu = 1;

        if ($nhom) {
            $eval = $thesisProgressService->tuDongDanhGiaGiaiDoanNghiepVu($nhom);
            $tienDoNghiepVu = $eval['tien_do'];
            $dieuKienNghiepVu = $eval['dieu_kien'];
            $giaiDoanHienTaiNghiepVu = $tienDoNghiepVu->GiaiDoanHienTai ?? $eval['suggested_stage'];
        }

        // 7. TIẾN ĐỘ BÁO CÁO CHUYÊN MÔN VỚI GIẢNG VIÊN HƯỚNG DẪN (GiaiDoanThucHien & DotNopBaoCao)
        $giaiDoansGV = collect();
        $allDotNops = collect();

        if ($deTai) {
            $giaiDoansGV = GiaiDoanThucHien::where('MaDeTai', $deTai->MaDeTai)
                ->with(['dotNops' => function($q) {
                    $q->with('phienBanMoiNhat')->orderBy('ThuTu');
                }])
                ->orderBy('ThuTu')
                ->get();
            $allDotNops = $giaiDoansGV->flatMap->dotNops;
        }

        // 8. TÍNH TOÁN TIẾN ĐỘ PHẦN TRĂM THỰC TẾ ĐÁNG TIN CẬY
        $soBaoCaoDat = 0;
        $tongDotNop = $allDotNops->count();
        if ($tongDotNop > 0) {
            $soBaoCaoDat = $allDotNops->filter(function($d) {
                return $d->phienBanMoiNhat?->TrangThai === 'Đạt' || $d->TrangThai === 'HOAN_THANH';
            })->count();
        } else {
            $soBaoCaoDat = $baoCaos->where('TrangThai', 'Đạt')->count();
            $tongDotNop = 5;
        }

        $tienDoPhanTram = 0;
        if ($nhom) {
            if ($ketQua && $ketQua->DiemTongKet > 0) {
                $tienDoPhanTram = 100;
            } elseif ($hoSoBaoVe && in_array($hoSoBaoVe->TrangThai, ['Đã bảo vệ', 'Đạt yêu cầu'])) {
                $tienDoPhanTram = 95;
            } elseif ($hoSoBaoVe && in_array($hoSoBaoVe->TrangThai, ['Đủ điều kiện bảo vệ', 'Đã phân công'])) {
                $tienDoPhanTram = 85;
            } elseif ($hoSoBaoVe) {
                $tienDoPhanTram = 75;
            } elseif ($deTai) {
                // Đề tài đã duyệt: Tối thiểu 25% + (tiến độ các đợt nộp báo cáo tối đa 45%)
                $ratioBaoCao = $tongDotNop > 0 ? ($soBaoCaoDat / $tongDotNop) : 0;
                $tienDoPhanTram = min(70, round(25 + ($ratioBaoCao * 45)));
            } elseif ($phieuDangKy) {
                $tienDoPhanTram = 20;
            } else {
                $tienDoPhanTram = 10;
            }
        }

        // Nhận xét mới nhất từ GVHD
        $latestFeedback = null;
        if ($allDotNops->isNotEmpty()) {
            $latestWithNote = $allDotNops->filter(fn($d) => $d->phienBanMoiNhat && !empty($d->phienBanMoiNhat->NhanXetGV))
                ->sortByDesc(fn($d) => $d->phienBanMoiNhat->NgayNhanXet ?? $d->phienBanMoiNhat->updated_at)
                ->first();
            if ($latestWithNote) {
                $latestFeedback = (object)[
                    'TieuDe' => $latestWithNote->TenDotNop,
                    'TrangThai' => $latestWithNote->phienBanMoiNhat->TrangThai,
                    'NhanXet' => $latestWithNote->phienBanMoiNhat->NhanXetGV,
                    'DiemSo' => null,
                    'LanBaoCao' => $latestWithNote->ThuTu ?? 1,
                    'updated_at' => $latestWithNote->phienBanMoiNhat->NgayNhanXet ?? $latestWithNote->phienBanMoiNhat->updated_at,
                ];
            }
        }
        if (!$latestFeedback) {
            $latestFeedback = $baoCaos->whereNotNull('NhanXet')->sortByDesc('updated_at')->first()
                ?? $baoCaos->sortByDesc('LanBaoCao')->first();
        }

        // 9. Lời mời gia nhập nhóm đang chờ
        $fullNhomIds = ThanhVienNhom::where('TrangThai', 'da_tham_gia')
            ->select('MaNhom')
            ->groupBy('MaNhom')
            ->havingRaw('COUNT(*) >= 3')
            ->pluck('MaNhom');

        if ($fullNhomIds->isNotEmpty()) {
            ThanhVienNhom::whereIn('MaNhom', $fullNhomIds)
                ->whereIn('TrangThai', ['cho_xac_nhan', 'xin_gia_nhap'])
                ->delete();
        }

        $loiMois = ThanhVienNhom::with(['nhom.truongNhom.taiKhoan', 'nhom.hocPhan', 'nhom.hocKy'])
            ->where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'cho_xac_nhan')
            ->whereHas('nhom', function($q) use ($hocKyMa) {
                if ($hocKyMa) {
                    $q->where('MaHocKy', $hocKyMa)->orWhereNull('MaHocKy');
                }
                $q->whereHas('thanhViens', function($sq) {
                    $sq->where('TrangThai', 'da_tham_gia');
                }, '<', 3);
            })
            ->get();

        // 10. THÔNG BÁO HỆ THỐNG & ĐẾM CHƯA ĐỌC
        $readIds = ThongBaoService::getReadIds($user?->MaTK);
        $userNhomCodes = $nhom ? [$nhom->MaNhom] : [];

        $announcementsQuery = ThongBao::whereIn('TrangThai', ['Đã phát hành', 'ĐÃ GỬI', 'Đã gửi', 'da_gui', 'ACTIVE', 'Đã tạo', 'DaPhatHanh'])
            ->where(function($q) use ($user, $userNhomCodes) {
                $q->whereIn('DoiTuongNhan', ['Sinh viên', 'Toàn thể', 'Tất cả', 'Toàn trường', 'Toàn thể sinh viên', 'Toàn thể GV và SV', ''])
                  ->orWhereNull('DoiTuongNhan')
                  ->orWhere('DoiTuongNhan', $user->MaTK)
                  ->orWhere('DoiTuongNhan', 'like', "%{$user->MaTK}%")
                  ->orWhere('MaThongBao', 'like', 'TB_KH_%')
                  ->orWhere('MaThongBao', 'like', 'TB_DK_%');

                if (!empty($userNhomCodes)) {
                    $q->orWhere('DoiTuongNhan', 'like', '%Tất cả các nhóm%');
                    foreach ($userNhomCodes as $code) {
                        $q->orWhere('DoiTuongNhan', 'like', "%{$code}%");
                    }
                }
            })
            ->orderBy('created_at', 'desc');

        $latestAnnouncements = (clone $announcementsQuery)->take(5)->get();
        $latestAnnouncements->transform(function($tb) use ($readIds) {
            $tb->DaDoc = in_array($tb->MaThongBao, $readIds);
            $tb->Loai = $tb->LoaiThongBao ?? 'Thông báo';
            $tb->icon = match($tb->LoaiThongBao) {
                'Kế hoạch' => 'fa-calendar-check text-info',
                'Khóa luận' => 'fa-graduation-cap text-primary',
                'Báo cáo'   => 'fa-file-lines text-warning',
                'Hội đồng'  => 'fa-users text-danger',
                default     => 'fa-bullhorn text-primary',
            };
            return $tb;
        });

        $unreadNotificationsCount = (clone $announcementsQuery)
            ->whereNotIn('MaThongBao', $readIds)
            ->count();

        // 11. KHU VỰC "VIỆC CẦN LÀM" (ACTION ITEMS THEO MỨC ĐỘ ƯU TIÊN)
        $actionItems = collect();
        $now = now();

        // 11.1. Chưa đủ điều kiện khóa luận
        if (!$isDuDieuKien) {
            $actionItems->push([
                'priority' => 1,
                'level' => 'danger',
                'badge' => 'Cần lưu ý',
                'title' => 'Chưa đủ điều kiện tham gia khóa luận tốt nghiệp',
                'desc' => 'Số tín chỉ tích lũy hoặc điểm trung bình chưa đạt tiêu chuẩn theo quy định xét duyệt của Khoa.',
                'deadline' => null,
                'action_label' => 'Xem điều kiện',
                'action_url' => route('sinhvien.dashboard'),
                'action_class' => 'btn-outline-danger',
            ]);
        }

        // 11.2. Chưa có nhóm
        if (!$nhom) {
            $actionItems->push([
                'priority' => 1,
                'level' => 'danger',
                'badge' => 'Khẩn cấp',
                'title' => 'Chưa tham gia nhóm khóa luận tốt nghiệp',
                'desc' => 'Bạn cần tạo nhóm mới hoặc gửi yêu cầu gia nhập nhóm để bắt đầu thực hiện học phần khóa luận.',
                'deadline' => null,
                'action_label' => 'Tạo / Vào nhóm ngay',
                'action_url' => route('sinhvien.nhom.index'),
                'action_class' => 'btn-primary',
            ]);
        }

        // 11.3. Có lời mời nhóm đang chờ xác nhận
        if (isset($loiMois) && $loiMois->isNotEmpty()) {
            $actionItems->push([
                'priority' => 2,
                'level' => 'warning',
                'badge' => 'Cần phản hồi',
                'title' => 'Bạn có ' . $loiMois->count() . ' lời mời tham gia nhóm khóa luận',
                'desc' => 'Lời mời từ ' . ($loiMois->first()->nhom->truongNhom->HoTen ?? 'Bạn học') . ' (Nhóm: ' . ($loiMois->first()->nhom->TenNhom ?? '') . ').',
                'deadline' => null,
                'action_label' => 'Xem & Phản hồi',
                'action_url' => route('sinhvien.nhom.index'),
                'action_class' => 'btn-warning text-dark',
            ]);
        }

        // 11.4. Có nhóm nhưng chưa có đề tài
        if ($nhom && !$deTai) {
            if ($phieuDangKy && in_array($phieuDangKy->TrangThai, ['Chờ duyệt', 'cho_duyet'])) {
                $actionItems->push([
                    'priority' => 3,
                    'level' => 'info',
                    'badge' => 'Đang chờ duyệt',
                    'title' => 'Đăng ký đề tài đang chờ Bộ môn / Khoa xét duyệt',
                    'desc' => 'Phiếu đăng ký đã được gửi thành công. Vui lòng theo dõi kết quả phê duyệt chính thức.',
                    'deadline' => null,
                    'action_label' => 'Xem phiếu đăng ký',
                    'action_url' => route('sinhvien.dangky.index'),
                    'action_class' => 'btn-outline-primary',
                ]);
            } else {
                $actionItems->push([
                    'priority' => 1,
                    'level' => 'danger',
                    'badge' => 'Khẩn cấp',
                    'title' => 'Nhóm chưa đăng ký đề tài khóa luận',
                    'desc' => 'Vui lòng truy cập danh mục đề tài được công bố và nộp phiếu đăng ký đề tài cho nhóm.',
                    'deadline' => null,
                    'action_label' => 'Đăng ký đề tài ngay',
                    'action_url' => route('sinhvien.dangky.index'),
                    'action_class' => 'btn-primary',
                ]);
            }
        }

        // 11.5. Đã duyệt đề tài nhưng chưa nộp đề cương chi tiết
        if ($nhom && $deTai && empty($deTai->FileDeCuong)) {
            $hasDeCuong = DB::table('tailieunop')
                ->where('MaDeTai', $deTai->MaDeTai)
                ->where(function($q) {
                    $q->where('LoaiTaiLieu', 'like', '%decuong%')
                      ->orWhere('TenTaiLieu', 'like', '%đề cương%');
                })->exists();

            if (!$hasDeCuong) {
                $actionItems->push([
                    'priority' => 2,
                    'level' => 'warning',
                    'badge' => 'Giai đoạn 3',
                    'title' => 'Hoàn thiện và nộp hồ sơ đề cương chi tiết',
                    'desc' => 'Soạn thảo đề cương khóa luận dưới sự hướng dẫn của GVHD để chuẩn bị thẩm định / phản biện đề cương.',
                    'deadline' => null,
                    'action_label' => 'Chi tiết đề tài & đề cương',
                    'action_url' => route('sinhvien.dangky.index'),
                    'action_class' => 'btn-warning text-dark',
                ]);
            }
        }

        // 11.6. Kiểm tra các đợt nộp báo cáo chuyên môn GVHD
        if (!empty($allDotNops) && $allDotNops->isNotEmpty()) {
            foreach ($allDotNops as $dot) {
                $latest = $dot->phienBanMoiNhat;
                $isOverdue = $dot->HanNop && $now->gt($dot->HanNop);

                // Báo cáo bị yêu cầu nộp lại / chỉnh sửa
                if ($latest && in_array($latest->TrangThai, ['Yêu cầu nộp lại', 'Cần bổ sung'])) {
                    $actionItems->push([
                        'priority' => 1,
                        'level' => 'danger',
                        'badge' => 'Cần chỉnh sửa',
                        'title' => 'Chỉnh sửa & nộp lại: ' . $dot->TenDotNop,
                        'desc' => 'GVHD yêu cầu nộp lại v' . $latest->LanNop . ($latest->NhanXetGV ? ': "' . Str::limit($latest->NhanXetGV, 80) . '"' : '.'),
                        'deadline' => $dot->HanNop ? $dot->HanNop->format('H:i d/m/Y') : null,
                        'action_label' => 'Nộp lại báo cáo',
                        'action_url' => route('sinhvien.baocao.dot_nop.show', $dot->MaDotNop),
                        'action_class' => 'btn-danger',
                    ]);
                }
                // Chưa nộp và đã quá hạn
                elseif (!$latest && $isOverdue) {
                    $actionItems->push([
                        'priority' => 1,
                        'level' => 'danger',
                        'badge' => 'Quá hạn',
                        'title' => 'Quá hạn nộp: ' . $dot->TenDotNop,
                        'desc' => 'Đợt báo cáo đã hết hạn nộp vào ' . $dot->HanNop->format('d/m/Y') . '. Vui lòng nộp bổ sung và liên hệ GVHD.',
                        'deadline' => $dot->HanNop ? $dot->HanNop->format('H:i d/m/Y') : null,
                        'action_label' => 'Nộp bù ngay',
                        'action_url' => route('sinhvien.baocao.dot_nop.show', $dot->MaDotNop),
                        'action_class' => 'btn-danger',
                    ]);
                }
                // Đang mở nộp và chưa nộp
                elseif (!$latest && $dot->isMoNop() && $dot->HanNop) {
                    $daysLeft = (int) ceil($now->diffInDays($dot->HanNop, false));
                    $isUrgent = $daysLeft <= 3;
                    $actionItems->push([
                        'priority' => $isUrgent ? 1 : 2,
                        'level' => $isUrgent ? 'danger' : 'warning',
                        'badge' => $isUrgent ? 'Hạn chót ' . $daysLeft . ' ngày' : 'Còn ' . $daysLeft . ' ngày',
                        'title' => 'Đến hạn nộp: ' . $dot->TenDotNop,
                        'desc' => 'Đợt nộp báo cáo đang mở tiếp nhận. Hãy hoàn thành và nộp tài liệu đúng thời hạn.',
                        'deadline' => $dot->HanNop->format('H:i d/m/Y'),
                        'action_label' => 'Nộp báo cáo ngay',
                        'action_url' => route('sinhvien.baocao.dot_nop.show', $dot->MaDotNop),
                        'action_class' => $isUrgent ? 'btn-danger' : 'btn-warning text-dark',
                    ]);
                }
                // Đã nộp, đang chờ GVHD chấm
                elseif ($latest && in_array($latest->TrangThai, ['Chờ duyệt', 'cho_duyet'])) {
                    $actionItems->push([
                        'priority' => 3,
                        'level' => 'info',
                        'badge' => 'Chờ duyệt',
                        'title' => 'Chờ đánh giá: ' . $dot->TenDotNop,
                        'desc' => 'Đã nộp phiên bản v' . $latest->LanNop . ($latest->NgayNop ? ' vào ngày ' . $latest->NgayNop->format('d/m/Y') : '') . '. Đang chờ GVHD phản hồi.',
                        'deadline' => null,
                        'action_label' => 'Xem bài nộp',
                        'action_url' => route('sinhvien.baocao.dot_nop.show', $dot->MaDotNop),
                        'action_class' => 'btn-outline-secondary',
                    ]);
                }
            }
        }

        // 11.7. Lịch gặp GVHD sắp diễn ra
        foreach ($lichGaps as $lg) {
            if ($lg->ThoiGianBatDau && Carbon::parse($lg->ThoiGianBatDau)->isFuture()) {
                $dt = Carbon::parse($lg->ThoiGianBatDau);
                if ($dt->diffInDays($now) <= 7) {
                    $actionItems->push([
                        'priority' => 2,
                        'level' => 'warning',
                        'badge' => 'Sắp diễn ra',
                        'title' => 'Lịch trao đổi với GVHD: ' . ($lg->NoiDung ?? 'Hướng dẫn khóa luận'),
                        'desc' => 'Thời gian: ' . $dt->format('H:i d/m/Y') . ' tại ' . ($lg->DiaDiem ?? 'Văn phòng bộ môn') . '.',
                        'deadline' => $dt->format('H:i d/m/Y'),
                        'action_label' => 'Xem lịch hẹn',
                        'action_url' => route('sinhvien.calendar'),
                        'action_class' => 'btn-outline-primary',
                    ]);
                }
            }
        }

        // Sắp xếp actionItems theo mức độ ưu tiên
        $actionItems = $actionItems->sortBy('priority')->values();

        // 12. HOẠT ĐỘNG GẦN ĐÂY THỰC TẾ CỦA SINH VIÊN
        $recentActivities = collect();

        if ($phieuDangKy) {
            if (in_array($phieuDangKy->TrangThai, ['Đã duyệt', 'da_duyet'])) {
                $recentActivities->push([
                    'icon' => 'fa-circle-check text-success',
                    'bg_class' => 'bg-success-subtle text-success',
                    'title' => 'Đề tài đã được phê duyệt',
                    'description' => 'Phiếu đăng ký đề tài ' . ($deTai?->MaDeTai ?? '') . ' của nhóm đã được duyệt chính thức.',
                    'time' => $phieuDangKy->NgayDuyet ?? $phieuDangKy->updated_at ?? $phieuDangKy->created_at,
                    'link' => route('sinhvien.dangky.index'),
                    'type' => 'DANG_KY',
                ]);
            } else {
                $recentActivities->push([
                    'icon' => 'fa-clock text-warning',
                    'bg_class' => 'bg-warning-subtle text-warning',
                    'title' => 'Gửi phiếu đăng ký đề tài',
                    'description' => 'Phiếu đăng ký ' . ($deTai?->MaDeTai ?? '') . ' đang chờ phê duyệt từ Khoa/Bộ môn.',
                    'time' => $phieuDangKy->created_at,
                    'link' => route('sinhvien.dangky.index'),
                    'type' => 'DANG_KY',
                ]);
            }
        }

        if ($nhom) {
            $recentActivities->push([
                'icon' => 'fa-users text-primary',
                'bg_class' => 'bg-primary-subtle text-primary',
                'title' => 'Tham gia nhóm khóa luận',
                'description' => 'Tham gia nhóm "' . $nhom->TenNhom . '" (' . $thanhViens->count() . ' thành viên).',
                'time' => $nhom->created_at,
                'link' => route('sinhvien.nhom.index'),
                'type' => 'NHOM',
            ]);
        }

        if ($allDotNops->isNotEmpty()) {
            foreach ($allDotNops as $dot) {
                $latestPb = $dot->phienBanMoiNhat;
                if ($latestPb) {
                    $recentActivities->push([
                        'icon' => 'fa-file-arrow-up text-info',
                        'bg_class' => 'bg-info-subtle text-info',
                        'title' => 'Nộp báo cáo: ' . $dot->TenDotNop,
                        'description' => 'Đã nộp phiên bản v' . $latestPb->LanNop . ($latestPb->TenFile ? ' (' . $latestPb->TenFile . ')' : ''),
                        'time' => $latestPb->NgayNop ?? $latestPb->created_at,
                        'link' => route('sinhvien.baocao.dot_nop.show', $dot->MaDotNop),
                        'type' => 'NOP_BAI',
                    ]);

                    if ($latestPb->NgayNhanXet || $latestPb->NhanXetGV) {
                        $isPass = $latestPb->TrangThai === 'Đạt';
                        $recentActivities->push([
                            'icon' => $isPass ? 'fa-comment-check text-success' : 'fa-comment-exclamation text-warning',
                            'bg_class' => $isPass ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning',
                            'title' => 'GVHD nhận xét: ' . $dot->TenDotNop,
                            'description' => 'Trạng thái: ' . $latestPb->TrangThai . ($latestPb->NhanXetGV ? ' - "' . Str::limit($latestPb->NhanXetGV, 60) . '"' : ''),
                            'time' => $latestPb->NgayNhanXet ?? $latestPb->updated_at,
                            'link' => route('sinhvien.baocao.dot_nop.show', $dot->MaDotNop),
                            'type' => 'NHAN_XET',
                        ]);
                    }
                }
            }
        } elseif ($baoCaos->isNotEmpty()) {
            foreach ($baoCaos as $bc) {
                if ($bc->NhanXet) {
                    $recentActivities->push([
                        'icon' => 'fa-comment text-primary',
                        'bg_class' => 'bg-primary-subtle text-primary',
                        'title' => 'GVHD nhận xét đợt ' . $bc->LanBaoCao,
                        'description' => '"' . Str::limit($bc->NhanXet, 60) . '"',
                        'time' => $bc->updated_at ?? $bc->NgayNop,
                        'link' => route('sinhvien.baocao.index'),
                        'type' => 'NHAN_XET',
                    ]);
                }
            }
        }

        foreach ($lichGaps as $lg) {
            $recentActivities->push([
                'icon' => 'fa-calendar-check text-primary',
                'bg_class' => 'bg-primary-subtle text-primary',
                'title' => 'Lịch trao đổi với GVHD',
                'description' => ($lg->NoiDung ?? 'Hướng dẫn khóa luận') . ' tại ' . ($lg->DiaDiem ?? 'Bộ môn'),
                'time' => $lg->ThoiGianBatDau ?? $lg->created_at,
                'link' => route('sinhvien.calendar'),
                'type' => 'LICH_GAP',
            ]);
        }

        if ($hoSoBaoVe) {
            $recentActivities->push([
                'icon' => 'fa-award text-success',
                'bg_class' => 'bg-success-subtle text-success',
                'title' => 'Hồ sơ bảo vệ khóa luận',
                'description' => 'Trạng thái hồ sơ: ' . ($hoSoBaoVe->TrangThai ?? 'Đang xử lý'),
                'time' => $hoSoBaoVe->updated_at ?? $hoSoBaoVe->created_at,
                'link' => route('sinhvien.hoso.index'),
                'type' => 'HO_SO',
            ]);
        }

        $recentActivities = $recentActivities->filter(fn($a) => !empty($a['time']))
            ->sortByDesc(fn($a) => Carbon::parse($a['time'])->timestamp)
            ->values()
            ->take(5);

        // 13. DỮ LIỆU 5 THẺ THỐNG KÊ TỔNG QUAN (KPI)
        $tenGiaiDoanShort = TienDoNghiepVuKhoaLuan::DANH_SACH_GIAI_DOAN[$giaiDoanHienTaiNghiepVu]['short'] ?? 'Đang thực hiện';
        
        $kpiDeTai = [
            'title' => 'Đề tài của tôi',
            'value' => $deTai ? $deTai->MaDeTai : ($nhom ? 'Chờ đăng ký' : 'Chưa có'),
            'badge' => $deTai ? ($deTai->TrangThai ?? 'Đã duyệt') : ($nhom ? 'Chờ đăng ký' : 'Chưa có nhóm'),
            'badge_class' => $deTai ? 'bg-primary-subtle text-primary' : ($nhom ? 'bg-warning-subtle text-warning-emphasis' : 'badge-neutral'),
            'icon' => 'fa-solid fa-book-bookmark',
            'link' => route('sinhvien.dangky.index'),
        ];

        $kpiTienDo = [
            'title' => 'Tiến độ khóa luận',
            'value' => $nhom ? ($tienDoPhanTram . '%') : '--',
            'badge' => $nhom ? ("GĐ {$giaiDoanHienTaiNghiepVu}: " . $tenGiaiDoanShort) : 'Chưa có nhóm',
            'badge_class' => $nhom ? 'bg-primary-subtle text-primary' : 'badge-neutral',
            'icon' => 'fa-solid fa-chart-line',
            'link' => '#progress-section',
        ];

        $kpiBaoCao = [
            'title' => 'Báo cáo tiến độ',
            'value' => $allDotNops->count() > 0 ? ($soBaoCaoDat . '/' . $allDotNops->count()) : ($soBaoCaoDat . '/5 đợt'),
            'badge' => ($soBaoCaoDat > 0 && $soBaoCaoDat == $tongDotNop) ? 'Hoàn thành các đợt' : 'Đợt nộp chuyên môn',
            'badge_class' => ($soBaoCaoDat > 0 && $soBaoCaoDat == $tongDotNop) ? 'bg-success-subtle text-success' : 'badge-neutral',
            'icon' => 'fa-solid fa-clipboard-list',
            'link' => route('sinhvien.baocao.index'),
        ];

        $urgentTasksCount = $actionItems->where('priority', 1)->count();
        $kpiCongViec = [
            'title' => 'Công việc cần làm',
            'value' => $actionItems->count() . ' việc',
            'badge' => $urgentTasksCount > 0 ? ($urgentTasksCount . ' việc khẩn cấp') : ($actionItems->isEmpty() ? 'Đã hoàn tất' : 'Đang xử lý'),
            'badge_class' => $urgentTasksCount > 0 ? 'bg-danger-subtle text-danger' : ($actionItems->isEmpty() ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning-emphasis'),
            'icon' => 'fa-solid fa-list-check',
            'link' => '#action-items-section',
        ];

        $kpiThongBao = [
            'title' => 'Thông báo mới',
            'value' => $unreadNotificationsCount . ' tin',
            'badge' => $unreadNotificationsCount > 0 ? 'Chưa đọc' : 'Đã xem hết',
            'badge_class' => $unreadNotificationsCount > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success',
            'icon' => 'fa-solid fa-bell',
            'link' => route('sinhvien.thongbao.index'),
        ];

        return view('sinhvien.dashboard', compact(
            'sinhVien',
            'isDuDieuKien',
            'dkRecord',
            'hocKyHienTai',
            'nhom',
            'phieuDangKy',
            'deTai',
            'gvhd',
            'truongNhom',
            'thanhViens',
            'tienDoNghiepVu',
            'dieuKienNghiepVu',
            'giaiDoanHienTaiNghiepVu',
            'giaiDoansGV',
            'allDotNops',
            'baoCaos',
            'soBaoCaoDat',
            'tongDotNop',
            'tienDoPhanTram',
            'actionItems',
            'kpiDeTai',
            'kpiTienDo',
            'kpiBaoCao',
            'kpiCongViec',
            'kpiThongBao',
            'latestAnnouncements',
            'unreadNotificationsCount',
            'recentActivities',
            'lichGaps',
            'hoSoBaoVe',
            'ketQua',
            'activePlan',
            'nextMoc',
            'daysRemaining',
            'latestFeedback',
            'loiMois',
            'allStudentMemberships',
            'stageBreakdown'
        ));
    }
}
