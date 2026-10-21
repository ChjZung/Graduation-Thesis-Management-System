<?php

namespace App\Services;

use App\Models\DeTai;
use App\Models\Nhom;
use App\Models\SinhVien;
use App\Models\GiangVien;
use App\Models\GiaiDoanThucHien;
use App\Models\DotNopBaoCao;
use App\Models\KhoiBaoCaoTienDo;
use App\Models\BaoCaoTienDo;
use App\Models\TienDoNghiepVuKhoaLuan;
use App\Models\LichSuChuyenGiaiDoan;
use App\Models\PhieuDangKy;
use App\Models\PhanCongPhanBien;
use App\Models\HoSoBaoVe;
use App\Helpers\IdGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ThesisProgressService
{
    /**
     * 6 Giai đoạn kỹ thuật chuẩn mẫu cho Giảng viên hướng dẫn (có thể tùy chỉnh thoải mái)
     */
    public const GIAI_DOAN_KY_THUAT_MAU = [
        1 => [
            'ten' => 'Giai đoạn 1: Lập kế hoạch & Khảo sát hiện trạng',
            'mo_ta' => 'Xác định mục tiêu, phạm vi nghiên cứu, khảo sát các hệ thống tương tự và lập kế hoạch chi tiết.',
            'cong_viec' => 'Khảo sát thực tế, phỏng vấn thu thập nghiệp vụ, tổng hợp tài liệu tham khảo, lập timeline đề tài.',
            'dau_ra' => 'Báo cáo khảo sát hiện trạng, tài liệu đặc tả mục tiêu đề tài, kế hoạch phân chia công việc.',
            'yeu_cau' => 'Nắm rõ nghiệp vụ bài toán, phân công công việc cụ thể cho từng thành viên trong nhóm.',
            'dot_nops' => [
                [
                    'ten' => 'Đợt 1.1: Báo cáo khảo sát hiện trạng & Xác định bài toán',
                    'mo_ta' => 'Nộp báo cáo khảo sát nghiệp vụ thực tế và mô tả phạm vi giải pháp.',
                    'yeu_cau' => 'Phân tích ít nhất 2 giải pháp tương tự và làm rõ sự cần thiết của đề tài.',
                    'dinh_dang' => 'pdf',
                    'source_code' => false,
                ]
            ]
        ],
        2 => [
            'ten' => 'Giai đoạn 2: Phân tích yêu cầu hệ thống',
            'mo_ta' => 'Thu thập, phân tích và mô hình hóa toàn bộ yêu cầu chức năng và phi chức năng của hệ thống.',
            'cong_viec' => 'Xác định tác nhân (Actors), lập danh sách Usecase/User Stories, vẽ sơ đồ Use Case, sơ đồ Activity.',
            'dau_ra' => 'Tài liệu đặc tả yêu cầu phần mềm (SRS), sơ đồ ca sử dụng và luồng nghiệp vụ.',
            'yeu_cau' => 'Bao quát đầy đủ các phân hệ chức năng đã đăng ký trong đề cương khóa luận.',
            'dot_nops' => [
                [
                    'ten' => 'Đợt 2.1: Đặc tả Usecase & Luồng nghiệp vụ',
                    'mo_ta' => 'Nộp file tài liệu phân tích yêu cầu SRS và biểu đồ Use Case chi tiết.',
                    'yeu_cau' => 'Mô tả kịch bản chính và kịch bản ngoại lệ cho các chức năng trọng yếu.',
                    'dinh_dang' => 'pdf',
                    'source_code' => false,
                ]
            ]
        ],
        3 => [
            'ten' => 'Giai đoạn 3: Thiết kế hệ thống & CSDL',
            'mo_ta' => 'Thiết kế kiến trúc hệ thống, lược đồ cơ sở dữ liệu và thiết kế giao diện người dùng (UI/UX).',
            'cong_viec' => 'Thiết kế kiến trúc giải pháp, thiết kế ERD, chuẩn hóa CSDL (3NF), thiết kế wireframe/prototype UI.',
            'dau_ra' => 'Lược đồ CSDL chi tiết (Data Dictionary), sơ đồ lớp (Class Diagram), link Figma thiết kế giao diện.',
            'yeu_cau' => 'CSDL chuẩn hóa không dư thừa dữ liệu, giao diện đáp ứng tiêu chuẩn trải nghiệm người dùng.',
            'dot_nops' => [
                [
                    'ten' => 'Đợt 3.1: Thiết kế Cơ sở dữ liệu & Kiến trúc hệ thống',
                    'mo_ta' => 'Nộp báo cáo thiết kế CSDL (ERD, từ điển dữ liệu) và cấu trúc kiến trúc phần mềm.',
                    'yeu_cau' => 'Có script tạo bảng hoặc Migration CSDL, có sơ đồ kiến trúc module.',
                    'dinh_dang' => 'pdf',
                    'source_code' => false,
                ]
            ]
        ],
        4 => [
            'ten' => 'Giai đoạn 4: Xây dựng & Tích hợp hệ thống',
            'mo_ta' => 'Hiện thực hóa mã nguồn các chức năng theo thiết kế, tích hợp các module và dịch vụ.',
            'cong_viec' => 'Lập trình Backend API, lập trình Frontend UI, kết nối CSDL, cấu hình bảo mật và phân quyền.',
            'dau_ra' => 'Mã nguồn hệ thống chạy được trên môi trường thử nghiệm (Staging/Local), repository Git có commit đều đặn.',
            'yeu_cau' => 'Mã nguồn rõ ràng, clean code, tuân thủ kiến trúc đã thiết kế, có tài liệu hướng dẫn cài đặt.',
            'dot_nops' => [
                [
                    'ten' => 'Đợt 4.1: Báo cáo hoàn thành các chức năng cốt lõi (Module Core)',
                    'mo_ta' => 'Nộp báo cáo tiến độ lập trình các chức năng chính.',
                    'yeu_cau' => 'Mô tả các chức năng đã hoàn thành và có hướng dẫn chạy thử.',
                    'dinh_dang' => 'pdf',
                    'source_code' => false,
                ],
                [
                    'ten' => 'Đợt 4.2: Báo cáo tích hợp toàn diện & Hoàn thiện các module phụ trợ',
                    'mo_ta' => 'Nộp báo cáo tích hợp hệ thống, phân quyền người dùng và kiểm thử tích hợp.',
                    'yeu_cau' => 'Hệ thống vận hành trơn tru giữa Frontend và Backend.',
                    'dinh_dang' => 'pdf',
                    'source_code' => false,
                ]
            ]
        ],
        5 => [
            'ten' => 'Giai đoạn 5: Kiểm thử & Đánh giá kết quả',
            'mo_ta' => 'Kiểm thử toàn diện hệ thống, phát hiện và khắc phục lỗi, đánh giá hiệu năng và bảo mật.',
            'cong_viec' => 'Viết kịch bản kiểm thử (Test Cases), kiểm thử chức năng, kiểm thử giao diện, đo đạc độ trễ và tải.',
            'dau_ra' => 'Biên bản kiểm thử (Test Report), nhật ký sửa lỗi (Bug Log), báo cáo đánh giá kết quả nghiệm thu.',
            'yeu_cau' => 'Không còn lỗi nghiêm trọng (Critical bugs), hệ thống hoạt động ổn định.',
            'dot_nops' => [
                [
                    'ten' => 'Đợt 5.1: Báo cáo kiểm thử phần mềm & Khắc phục lỗi',
                    'mo_ta' => 'Nộp Test Report chi tiết và kết quả khắc phục các lỗi phát hiện.',
                    'yeu_cau' => 'Báo cáo tổng hợp số lượng ca kiểm thử đạt/không đạt và kết quả khắc phục lỗi.',
                    'dinh_dang' => 'pdf',
                    'source_code' => false,
                ]
            ]
        ],
        6 => [
            'ten' => 'Giai đoạn 6: Hoàn thiện sản phẩm & Viết báo cáo khóa luận',
            'mo_ta' => 'Viết toàn văn báo cáo khóa luận tốt nghiệp, đóng gói sản phẩm và chuẩn bị tài liệu nghiệm thu.',
            'cong_viec' => 'Soạn thảo toàn văn thuyết minh khóa luận theo quy định mẫu của Khoa, quay video demo sản phẩm.',
            'dau_ra' => 'Bản thảo toàn văn khóa luận (PDF), video demo hoặc link deploy sản phẩm, slide thuyết trình.',
            'yeu_cau' => 'Trình bày đúng quy cách chuẩn của Nhà trường, nội dung trung thực, có trích dẫn nguồn đầy đủ.',
            'dot_nops' => [
                [
                    'ten' => 'Đợt 6.1: Nộp toàn văn dự thảo Khóa luận & Demo sản phẩm',
                    'mo_ta' => 'Nộp bản toàn văn khóa luận hoàn chỉnh để GVHD xem xét và duyệt bảo vệ.',
                    'yeu_cau' => 'Nộp file PDF toàn văn và tài liệu minh chứng sản phẩm hoạt động.',
                    'dinh_dang' => 'pdf',
                    'source_code' => false,
                ]
            ]
        ],
    ];

    // =========================================================================
    // PHẦN 1: DÀNH CHO GIÁO VỤ - QUẢN LÝ 6 GIAI ĐOẠN NGHIỆP VỤ KHÓA LUẬN
    // =========================================================================

    /**
     * Khởi tạo hoặc lấy bản ghi Tiến độ nghiệp vụ của Nhóm
     */
    public function getOrCreateTienDoNghiepVu(Nhom $nhom): TienDoNghiepVuKhoaLuan
    {
        $tienDo = TienDoNghiepVuKhoaLuan::where('MaNhom', $nhom->MaNhom)->first();
        if (!$tienDo) {
            $deTai = $nhom->deTai;
            $tienDo = TienDoNghiepVuKhoaLuan::create([
                'MaNhom' => $nhom->MaNhom,
                'MaDeTai' => $deTai?->MaDeTai,
                'MaHocKy' => $nhom->MaHocKy ?? $deTai?->MaHocKy,
                'GiaiDoanHienTai' => 1,
                'TrangThaiGiaiDoan' => 'DANG_THUC_HIEN',
                'NgayBatDau' => now(),
            ]);

            // Tự động đánh giá các điều kiện thực tế
            $this->tuDongDanhGiaGiaiDoanNghiepVu($nhom, $tienDo);
        }

        return $tienDo;
    }

    /**
     * Tự động kiểm tra và đánh giá các điều kiện của 6 giai đoạn nghiệp vụ
     */
    public function tuDongDanhGiaGiaiDoanNghiepVu(Nhom $nhom, ?TienDoNghiepVuKhoaLuan $tienDo = null): array
    {
        if (!$tienDo) {
            $tienDo = $this->getOrCreateTienDoNghiepVu($nhom);
        }

        $deTai = $nhom->deTai;
        $phieuDK = PhieuDangKy::where('MaNhom', $nhom->MaNhom)->latest()->first();

        // Đánh giá chi tiết từng giai đoạn:
        $dieuKien = [];

        // ── GĐ 1: Đề xuất đề tài ──
        $gd1Dat = false;
        $gd1Msg = 'Chưa có đề tài được phê duyệt';
        if ($deTai) {
            if (in_array($deTai->TrangThai, ['Đã công bố', 'da_cong_bo', 'Đã duyệt', 'da_duyet'])) {
                $gd1Dat = true;
                $gd1Msg = "Đề tài {$deTai->MaDeTai} đã được phê duyệt và công bố";
            } else {
                $gd1Msg = "Đề tài đang ở trạng thái: {$deTai->TrangThai}";
            }
        }
        $dieuKien[1] = [
            'dat' => $gd1Dat,
            'mo_ta' => $gd1Msg,
            'de_tai' => $deTai?->TenDeTai,
            'gvhd' => $deTai?->giangVien?->HoTen,
        ];

        // ── GĐ 2: Đăng ký đề tài ──
        $gd2Dat = false;
        $gd2Msg = 'Nhóm chưa có phiếu đăng ký đề tài được duyệt';
        if ($phieuDK && in_array($phieuDK->TrangThai, ['Đã duyệt', 'da_duyet'])) {
            $gd2Dat = true;
            $gd2Msg = "Phiếu đăng ký đã được duyệt ngày " . ($phieuDK->NgayDuyet ? Carbon::parse($phieuDK->NgayDuyet)->format('d/m/Y') : 'trước đó');
        } elseif ($phieuDK) {
            $gd2Msg = "Phiếu đăng ký đang ở trạng thái: {$phieuDK->TrangThai}";
        }
        $dieuKien[2] = [
            'dat' => $gd2Dat,
            'mo_ta' => $gd2Msg,
            'so_tv' => $nhom->thanhViens()->count(),
            'truong_nhom' => $nhom->truongNhom?->HoTen,
        ];

        // ── GĐ 3: Thực hiện đề cương ──
        $gd3Dat = false;
        $gd3Msg = 'Chưa nộp file đề cương chi tiết';
        $hasDeCuongFile = $deTai?->coTepDeCuong() ?? false;

        if ($hasDeCuongFile) {
            $gd3Dat = true;
            $gd3Msg = 'Đã nộp hồ sơ đề cương chi tiết';
        }
        $dieuKien[3] = [
            'dat' => $gd3Dat,
            'mo_ta' => $gd3Msg,
            'file' => $hasDeCuongFile ? $deTai->FileDeCuong : null,
        ];

        // ── GĐ 4: Phê duyệt đề cương ──
        $gd4Dat = false;
        $gd4Msg = 'Chưa có kết quả phản biện phê duyệt đề cương';
        $phanBien = $hasDeCuongFile ? PhanCongPhanBien::where('MaDeTai', $deTai->MaDeTai)
            ->where('VaiTro', 'Phản biện đề cương')->first() : null;
        if ($deTai?->daDuyetDeCuong()) {
            $gd4Dat = true;
            $gd4Msg = 'Bộ môn đã phê duyệt đề cương';
        } elseif ($phanBien && in_array($phanBien->KetQua, ['Đạt', 'dat', 'Thông qua'])) {
            $gd4Msg = 'Phản biện đã đánh giá Đạt, chờ Trưởng bộ môn phê duyệt đề cương';
        } elseif ($phanBien) {
            $gd4Msg = "Phản biện: " . ($phanBien->KetQua ?: 'Đang đánh giá') . " (" . ($phanBien->NhanXet ?: 'Chưa có nhận xét') . ")";
        }
        $dieuKien[4] = [
            'dat' => $gd4Dat,
            'mo_ta' => $gd4Msg,
            'gv_phan_bien' => $phanBien?->giangVien?->HoTen,
            'ket_qua' => $phanBien?->KetQua,
        ];

        // ── GĐ 5: Thực hiện khóa luận (Liên kết với dữ liệu Giảng viên) ──
        $gd5Dat = false;
        $gd5Msg = 'Đang trong quá trình thực hiện theo kế hoạch GVHD';
        $tongGiaiDoanGV = 0;
        $giaiDoanGVHoanThanh = 0;
        $tongDotNop = 0;
        $dotNopHoanThanh = 0;

        if ($deTai) {
            $giaiDoans = GiaiDoanThucHien::where('MaDeTai', $deTai->MaDeTai)->with('dotNops')->get();
            $tongGiaiDoanGV = $giaiDoans->count();
            $giaiDoanGVHoanThanh = $giaiDoans->where('TrangThai', 'HOAN_THANH')->count();

            $allDots = $giaiDoans->flatMap->dotNops;
            $tongDotNop = $allDots->count();
            $dotNopHoanThanh = $allDots->where('TrangThai', 'HOAN_THANH')->count();

            // Fallback nếu dùng hệ thống cũ (bảng baocaotiendo)
            if ($tongDotNop === 0) {
                $oldBaoCaos = BaoCaoTienDo::where('MaDeTai', $deTai->MaDeTai)->get();
                $tongDotNop = $oldBaoCaos->count();
                $dotNopHoanThanh = $oldBaoCaos->where('TrangThai', 'Đạt')->count();
            }

            // Giai đoạn 5 chỉ hoàn thành để chuyển sang Giai đoạn 6 (Bảo vệ) khi:
            // Hoàn thành các đợt nộp VÀ GVHD xác nhận cho phép nộp hồ sơ bảo vệ
            $daXacNhanBaoVe = ($tienDo && $tienDo->GiaiDoanHienTai >= 6);
            if ($tongDotNop > 0 && $dotNopHoanThanh >= $tongDotNop && $daXacNhanBaoVe) {
                $gd5Dat = true;
                $gd5Msg = "Hoàn thành toàn bộ {$dotNopHoanThanh}/{$tongDotNop} đợt nộp và GVHD đã xác nhận cho nộp hồ sơ bảo vệ";
            } elseif ($tongDotNop > 0 && $dotNopHoanThanh >= $tongDotNop) {
                $gd5Dat = false;
                $gd5Msg = "Đã hoàn thành {$dotNopHoanThanh}/{$tongDotNop} đợt nộp báo cáo. Đang chờ GVHD xác nhận cho phép bảo vệ.";
            } else {
                $gd5Msg = "Đã hoàn thành {$dotNopHoanThanh}/{$tongDotNop} đợt nộp báo cáo";
            }
        }
        $dieuKien[5] = [
            'dat' => $gd5Dat,
            'mo_ta' => $gd5Msg,
            'tong_gd_gv' => $tongGiaiDoanGV,
            'gd_gv_hoan_thanh' => $giaiDoanGVHoanThanh,
            'tong_dot_nop' => $tongDotNop,
            'dot_nop_hoan_thanh' => $dotNopHoanThanh,
        ];

        // ── GĐ 6: Hoàn thiện & Bảo vệ ──
        $gd6Dat = false;
        $gd6Msg = 'Chưa hoàn tất thủ tục bảo vệ';
        $hoSo = HoSoBaoVe::where('MaNhom', $nhom->MaNhom)->orWhere('MaDeTai', $deTai?->MaDeTai)->first();
        if ($hoSo && $hoSo->FileBanChinhSua) {
            $gd6Dat = true;
            $gd6Msg = 'Đã bảo vệ và nộp bản khóa luận hoàn chỉnh sau chỉnh sửa';
        } elseif ($hoSo && $hoSo->ThoiGianBaoVe) {
            $gd6Msg = 'Đã có lịch bảo vệ trước Hội đồng vào ' . Carbon::parse($hoSo->ThoiGianBaoVe)->format('d/m/Y H:i');
        } elseif ($hoSo && $hoSo->XacNhanGVHD) {
            $gd6Msg = 'GVHD đã ký duyệt đồng ý cho nhóm bảo vệ';
        }
        $dieuKien[6] = [
            'dat' => $gd6Dat,
            'mo_ta' => $gd6Msg,
            'xac_nhan_gvhd' => (bool)$hoSo?->XacNhanGVHD,
            'lich_bao_ve' => $hoSo?->ThoiGianBaoVe,
            'phong_bao_ve' => $hoSo?->PhongBaoVe,
            'file_hoan_chinh' => $hoSo?->FileBanChinhSua,
        ];

        // Xác định giai đoạn tự động gợi ý
        $suggestedStage = 1;
        for ($s = 1; $s <= 6; $s++) {
            if (!$dieuKien[$s]['dat']) {
                $suggestedStage = $s;
                break;
            }
            $suggestedStage = min(6, $s + 1);
        }

        // Cập nhật lưu json điều kiện vào bảng
        $tienDo->DieuKienDat = $dieuKien;
        // Chỉ tự động nâng giai đoạn nếu giai đoạn hiện tại nhỏ hơn gợi ý và đang ở trạng thái DANG_THUC_HIEN
        if ($tienDo->GiaiDoanHienTai < $suggestedStage && $tienDo->TrangThaiGiaiDoan !== 'CHO_XU_LY') {
            $gdTruoc = $tienDo->GiaiDoanHienTai;
            $tienDo->GiaiDoanHienTai = $suggestedStage;
            $tienDo->save();

            // Tự động ghi nhật ký hệ thống ghi nhận chuyển giai đoạn dựa trên điều kiện thực tế của các bên liên quan
            LichSuChuyenGiaiDoan::create([
                'MaNhom' => $nhom->MaNhom,
                'GiaiDoanTruoc' => $gdTruoc,
                'GiaiDoanSau' => $suggestedStage,
                'TrangThaiTruoc' => 'DANG_THUC_HIEN',
                'TrangThaiSau' => 'DANG_THUC_HIEN',
                'HanhDong' => 'Hệ thống tự động đồng bộ giai đoạn',
                'NguoiThucHien' => 'Hệ thống tự động',
                'LyDo' => "Đã đáp ứng đủ điều kiện hoàn thành Giai đoạn {$gdTruoc}: " . ($dieuKien[$gdTruoc]['mo_ta'] ?? ''),
            ]);
        }
        $tienDo->save();

        return [
            'tien_do' => $tienDo,
            'dieu_kien' => $dieuKien,
            'suggested_stage' => $suggestedStage,
        ];
    }

    /**
     * Tính toán chi tiết phần trăm, thanh tiến độ, trạng thái và thời hạn của từng giai đoạn (1..6)
     */
    public static function getStageProgressBreakdown(Nhom $nhom, ?array $dieuKien = null, ?int $currentStage = null): array
    {
        if ($dieuKien === null) {
            $eval = app(self::class)->tuDongDanhGiaGiaiDoanNghiepVu($nhom);
            $dieuKien = $eval['dieu_kien'];
            $currentStage = $eval['tien_do']->GiaiDoanHienTai;
        }

        $deTai = $nhom->deTai;
        $tienDo = $nhom->tienDoNghiepVu ?? TienDoNghiepVuKhoaLuan::where('MaNhom', $nhom->MaNhom)->first();
        $currStage = $currentStage ?? ($tienDo?->GiaiDoanHienTai ?? 1);
        $stageStatusCode = $tienDo?->TrangThaiGiaiDoan ?? 'DANG_THUC_HIEN';

        // Lấy các mốc kế hoạch thời gian nếu có
        $hocKyMa = $nhom->MaHocKy ?? $deTai?->MaHocKy;
        $planMilestones = \App\Models\MocThoiGianKhoaLuan::whereHas('keHoach', function($q) use ($hocKyMa) {
            if ($hocKyMa) $q->where('MaHocKy', $hocKyMa);
        })->get()->keyBy('LoaiGiaiDoan');

        $breakdown = [];
        $stageDefs = TienDoNghiepVuKhoaLuan::DANH_SACH_GIAI_DOAN;

        for ($st = 1; $st <= 6; $st++) {
            $isCompleted = !empty($dieuKien[$st]['dat']);
            $isCurrent = ($st === $currStage);
            $isFuture = ($st > $currStage);

            $percent = 0;
            $statusCode = 'CHUA_BAT_DAU';
            $statusLabel = 'Chưa đến thời gian thực hiện';
            $badgeClass = 'bg-secondary text-white';
            $color = '#94a3b8';
            $deadline = null;
            $lastUpdated = null;
            $description = $dieuKien[$st]['mo_ta'] ?? ($stageDefs[$st]['mo_ta'] ?? '');

            switch ($st) {
                case 1: // Đề xuất đề tài
                    $deadline = $planMilestones->get('DE_XUAT_DE_TAI')?->NgayKetThuc 
                        ?? ($deTai?->NgayDeXuat ? Carbon::parse($deTai->NgayDeXuat)->format('d/m/Y') : 'Theo kế hoạch Khoa');
                    if ($isCompleted) {
                        $percent = 100;
                        $statusCode = 'HOAN_THANH';
                        $statusLabel = 'Đã hoàn thành';
                        $badgeClass = 'bg-success text-white';
                        $color = '#10b981';
                        $lastUpdated = $deTai?->NgayCongBo ?? $deTai?->NgayDuyetKhoa ?? $deTai?->NgayDuyetBM;
                    } elseif ($deTai) {
                        $percent = 50;
                        $statusCode = 'CHO_XU_LY';
                        $statusLabel = 'Chờ duyệt đề tài';
                        $badgeClass = 'bg-warning text-dark';
                        $color = '#f59e0b';
                        $lastUpdated = $deTai->NgayDeXuat;
                    } else {
                        $percent = 0;
                        $statusCode = 'CHUA_BAT_DAU';
                        $statusLabel = 'Chưa có đề tài';
                        $badgeClass = 'bg-secondary text-white';
                        $color = '#94a3b8';
                    }
                    break;

                case 2: // Đăng ký đề tài
                    $deadline = $planMilestones->get('DANG_KY_DE_TAI')?->NgayKetThuc 
                        ?? ($planMilestones->get('TAO_NHOM')?->NgayKetThuc ?? 'Theo kế hoạch Khoa');
                    $phieuDK = $nhom->phieuDangKys()->latest()->first();
                    if ($isCompleted) {
                        $percent = 100;
                        $statusCode = 'HOAN_THANH';
                        $statusLabel = 'Đã hoàn thành';
                        $badgeClass = 'bg-success text-white';
                        $color = '#10b981';
                        $lastUpdated = $phieuDK?->NgayDuyet ?? $phieuDK?->created_at;
                    } elseif ($phieuDK && in_array($phieuDK->TrangThai, ['ChoDuyet', 'Chờ duyệt', 'cho_duyet'])) {
                        $percent = 60;
                        $statusCode = 'CHO_XU_LY';
                        $statusLabel = 'Chờ duyệt đăng ký';
                        $badgeClass = 'bg-warning text-dark';
                        $color = '#f59e0b';
                        $lastUpdated = $phieuDK->created_at;
                    } elseif ($isCurrent) {
                        $percent = 30;
                        $statusCode = 'DANG_THUC_HIEN';
                        $statusLabel = 'Đang lập nhóm & đăng ký';
                        $badgeClass = 'bg-primary text-white';
                        $color = '#0072ce';
                        $lastUpdated = $nhom->NgayTao;
                    } else {
                        $percent = 0;
                        $statusCode = 'CHUA_BAT_DAU';
                        $statusLabel = 'Chưa đến thời gian thực hiện';
                    }
                    break;

                case 3: // Thực hiện đề cương
                    $deadline = $planMilestones->get('NOP_DE_CUONG')?->NgayKetThuc 
                        ?? ($planMilestones->get('THUC_HIEN_DE_CUONG')?->NgayKetThuc ?? 'Theo kế hoạch Khoa');
                    if ($isCompleted) {
                        $percent = 100;
                        $statusCode = 'HOAN_THANH';
                        $statusLabel = 'Đã hoàn thành';
                        $badgeClass = 'bg-success text-white';
                        $color = '#10b981';
                        $lastUpdated = $deTai?->updated_at;
                    } elseif ($isCurrent) {
                        $percent = 0;
                        $statusCode = ($stageStatusCode === 'QUA_HAN') ? 'QUA_HAN' : 'DANG_THUC_HIEN';
                        $statusLabel = ($statusCode === 'QUA_HAN') ? 'Quá hạn nộp đề cương' : 'Chưa nộp đề cương';
                        $badgeClass = ($statusCode === 'QUA_HAN') ? 'bg-danger text-white' : 'bg-primary text-white';
                        $color = ($statusCode === 'QUA_HAN') ? '#ef4444' : '#0072ce';
                    } else {
                        $percent = 0;
                        $statusCode = 'CHUA_BAT_DAU';
                        $statusLabel = 'Chưa đến thời gian thực hiện';
                    }
                    break;

                case 4: // Phê duyệt đề cương
                    $deadline = $planMilestones->get('PHE_DUYET_DE_CUONG')?->NgayKetThuc ?? 'Theo kế hoạch Khoa';
                    $phanBien = $deTai?->coTepDeCuong() ? \App\Models\PhanCongPhanBien::where('MaDeTai', $deTai->MaDeTai)
                        ->where('VaiTro', 'Phản biện đề cương')->first() : null;
                    if ($isCompleted) {
                        $percent = 100;
                        $statusCode = 'HOAN_THANH';
                        $statusLabel = 'Đã hoàn thành';
                        $badgeClass = 'bg-success text-white';
                        $color = '#10b981';
                        $lastUpdated = $phanBien?->updated_at ?? $deTai?->updated_at;
                    } elseif ($phanBien) {
                        $percent = 50;
                        $statusCode = 'CHO_XU_LY';
                        $statusLabel = in_array($phanBien->KetQua, ['Đạt', 'dat', 'Thông qua'])
                            ? 'Chờ Trưởng bộ môn phê duyệt' : 'Cán bộ đang phản biện';
                        $badgeClass = 'bg-warning text-dark';
                        $color = '#f59e0b';
                        $lastUpdated = $phanBien->updated_at;
                    } elseif ($isCurrent) {
                        $percent = 0;
                        $statusCode = 'CHO_XU_LY';
                        $statusLabel = 'Chờ phân công phản biện';
                        $badgeClass = 'bg-warning text-dark';
                        $color = '#f59e0b';
                    } else {
                        $percent = 0;
                        $statusCode = 'CHUA_BAT_DAU';
                        $statusLabel = 'Chưa đến thời gian thực hiện';
                    }
                    break;

                case 5: // Thực hiện khóa luận (báo cáo tiến độ)
                    $tongDotNop = (int)($dieuKien[5]['tong_dot_nop'] ?? 0);
                    $dotNopHoanThanh = (int)($dieuKien[5]['dot_nop_hoan_thanh'] ?? 0);
                    $lastBaoCao = $nhom->baoCaos()->latest()->first();
                    $lastUpdated = $lastBaoCao?->NgayNop ?? $lastBaoCao?->updated_at;

                    // Lấy hạn nộp gần nhất
                    $latestDot = $deTai ? \App\Models\DotNopBaoCao::where('MaDeTai', $deTai->MaDeTai)
                        ->where('TrangThai', '!=', 'HOAN_THANH')
                        ->orderBy('HanNop')
                        ->first() : null;
                    $deadline = $latestDot?->HanNop ? Carbon::parse($latestDot->HanNop)->format('d/m/Y H:i') : 'Theo kế hoạch GVHD';

                    if ($isCompleted) {
                        $percent = 100;
                        $statusCode = 'HOAN_THANH';
                        $statusLabel = 'Đã hoàn thành';
                        $badgeClass = 'bg-success text-white';
                        $color = '#10b981';
                    } elseif ($isCurrent) {
                        if ($tongDotNop > 0) {
                            $percent = (int) round(($dotNopHoanThanh / $tongDotNop) * 100);
                        } else {
                            $percent = 0;
                        }

                        if ($stageStatusCode === 'QUA_HAN' || ($latestDot && $latestDot->HanNop && Carbon::parse($latestDot->HanNop)->isPast())) {
                            $statusCode = 'QUA_HAN';
                            $statusLabel = 'Quá hạn nộp báo cáo';
                            $badgeClass = 'bg-danger text-white';
                            $color = '#ef4444';
                        } else {
                            $statusCode = 'DANG_THUC_HIEN';
                            $statusLabel = "Đang thực hiện ({$dotNopHoanThanh}/{$tongDotNop} đợt)";
                            $badgeClass = 'bg-primary text-white';
                            $color = '#0072ce';
                        }
                    } else {
                        $percent = 0;
                        $statusCode = 'CHUA_BAT_DAU';
                        $statusLabel = 'Chưa đến thời gian thực hiện';
                    }
                    break;

                case 6: // Hoàn thiện & Bảo vệ
                    $hoSo = \App\Models\HoSoBaoVe::where('MaNhom', $nhom->MaNhom)->orWhere('MaDeTai', $deTai?->MaDeTai)->first();
                    $deadline = $hoSo?->ThoiGianBaoVe ? Carbon::parse($hoSo->ThoiGianBaoVe)->format('d/m/Y H:i') : ($planMilestones->get('BAO_VE_KHOA_LUAN')?->NgayKetThuc ?? 'Theo kế hoạch Hội đồng');
                    if ($isCompleted) {
                        $percent = 100;
                        $statusCode = 'HOAN_THANH';
                        $statusLabel = 'Đã hoàn tất bảo vệ';
                        $badgeClass = 'bg-success text-white';
                        $color = '#10b981';
                        $lastUpdated = $hoSo?->updated_at;
                    } elseif ($hoSo && $hoSo->FileBanChinhSua) {
                        $percent = 100;
                        $statusCode = 'HOAN_THANH';
                        $statusLabel = 'Đã nộp bản hoàn chỉnh';
                        $badgeClass = 'bg-success text-white';
                        $color = '#10b981';
                    } elseif ($hoSo && $hoSo->ThoiGianBaoVe) {
                        $percent = 60;
                        $statusCode = 'CHO_XU_LY';
                        $statusLabel = 'Đã có lịch bảo vệ';
                        $badgeClass = 'bg-info text-white';
                        $color = '#0ea5e9';
                    } elseif ($hoSo && $hoSo->XacNhanGVHD) {
                        $percent = 40;
                        $statusCode = 'DANG_THUC_HIEN';
                        $statusLabel = 'GVHD đã đồng ý cho bảo vệ';
                        $badgeClass = 'bg-primary text-white';
                        $color = '#0072ce';
                    } elseif ($isCurrent) {
                        $percent = 0;
                        $statusCode = 'DANG_THUC_HIEN';
                        $statusLabel = 'Chuẩn bị hồ sơ bảo vệ';
                        $badgeClass = 'bg-primary text-white';
                        $color = '#0072ce';
                    } else {
                        $percent = 0;
                        $statusCode = 'CHUA_BAT_DAU';
                        $statusLabel = 'Chưa đến thời gian thực hiện';
                    }
                    break;
            }

            // Chuẩn hóa định dạng ngày
            if ($deadline && is_string($deadline) && preg_match('/^\d{4}-\d{2}-\d{2}/', $deadline)) {
                $deadline = Carbon::parse($deadline)->format('d/m/Y');
            }
            if ($lastUpdated && ($lastUpdated instanceof \DateTimeInterface || preg_match('/^\d{4}-\d{2}-\d{2}/', $lastUpdated))) {
                $lastUpdated = Carbon::parse($lastUpdated)->format('d/m/Y H:i');
            }

            $breakdown[$st] = [
                'stage_number' => $st,
                'name'         => $stageDefs[$st]['ten'],
                'short'        => $stageDefs[$st]['short'],
                'icon'         => $stageDefs[$st]['icon'],
                'percent'      => $percent,
                'status_code'  => $statusCode,
                'status_label' => $statusLabel,
                'badge_class'  => $badgeClass,
                'color'        => $color,
                'deadline'     => $deadline ?: 'Theo quy định',
                'last_updated' => $lastUpdated,
                'description'  => $description,
                'is_completed' => $isCompleted,
                'is_current'   => $isCurrent,
                'is_future'    => $isFuture,
            ];
        }

        // Tính % tiến độ tổng thể khóa luận có căn cứ
        $completedStagesCount = 0;
        $totalPercentSum = 0;
        foreach ($breakdown as $item) {
            if ($item['is_completed']) $completedStagesCount++;
            $totalPercentSum += $item['percent'];
        }
        $overallPercent = (int) round($totalPercentSum / 6);

        return [
            'stages' => $breakdown,
            'overall_percent' => $overallPercent,
            'completed_stages_count' => $completedStagesCount,
            'current_stage_number' => $currStage,
            'current_stage_label' => $stageDefs[$currStage]['ten'] ?? "Giai đoạn {$currStage}",
            'status_code' => $stageStatusCode,
            'status_label' => match($stageStatusCode) {
                'HOAN_THANH' => 'Đã hoàn thành',
                'QUA_HAN' => 'Quá hạn',
                'CHO_XU_LY' => 'Chờ xử lý',
                default => 'Đang thực hiện',
            },
        ];
    }

    /**
     * Giáo vụ thực hiện chuyển giai đoạn nghiệp vụ hoặc cập nhật trạng thái
     */
    public function chuyenGiaiDoanNghiepVu(
        Nhom $nhom,
        int $giaiDoanMoi,
        string $trangThaiMoi,
        string $nguoiThucHien,
        ?string $lyDo = null
    ): TienDoNghiepVuKhoaLuan {
        return DB::transaction(function () use ($nhom, $giaiDoanMoi, $trangThaiMoi, $nguoiThucHien, $lyDo) {
            $tienDo = $this->getOrCreateTienDoNghiepVu($nhom);
            $gdTruoc = $tienDo->GiaiDoanHienTai;
            $ttTruoc = $tienDo->TrangThaiGiaiDoan;

            $tienDo->GiaiDoanHienTai = $giaiDoanMoi;
            $tienDo->TrangThaiGiaiDoan = $trangThaiMoi;
            if ($giaiDoanMoi === 6 && $trangThaiMoi === 'HOAN_THANH') {
                $tienDo->NgayHoanThanh = now();
            }
            if ($lyDo) {
                $tienDo->GhiChu = $lyDo;
            }
            $tienDo->save();

            // Ghi nhận lịch sử chuyển giai đoạn nghiệp vụ
            LichSuChuyenGiaiDoan::create([
                'MaNhom' => $nhom->MaNhom,
                'GiaiDoanTruoc' => $gdTruoc,
                'GiaiDoanSau' => $giaiDoanMoi,
                'TrangThaiTruoc' => $ttTruoc,
                'TrangThaiSau' => $trangThaiMoi,
                'HanhDong' => ($gdTruoc !== $giaiDoanMoi) ? 'Chuyển giai đoạn nghiệp vụ' : 'Cập nhật trạng thái nghiệp vụ',
                'NguoiThucHien' => $nguoiThucHien,
                'LyDo' => $lyDo ?: "Giáo vụ {$nguoiThucHien} cập nhật giai đoạn {$giaiDoanMoi} ({$trangThaiMoi})",
            ]);

            return $tienDo;
        });
    }

    // =========================================================================
    // PHẦN 2: DÀNH CHO GIẢNG VIÊN HƯỚNG DẪN - QUẢN LÝ TIẾN ĐỘ THỰC HIỆN CHI TIẾT
    // =========================================================================

    /**
     * Khởi tạo template 6 giai đoạn kỹ thuật & các đợt nộp mẫu cho Đề tài nếu chưa có
     */
    public function khoiTaoGiaiDoanMauChoDeTai(DeTai $deTai): int
    {
        return DB::transaction(function () use ($deTai) {
            $existingCount = GiaiDoanThucHien::where('MaDeTai', $deTai->MaDeTai)->count();
            if ($existingCount > 0) {
                return $existingCount;
            }

            $now = Carbon::now();
            $index = 1;

            foreach (self::GIAI_DOAN_KY_THUAT_MAU as $stNum => $meta) {
                $startDate = (clone $now)->addWeeks(($stNum - 1) * 2)->startOfDay();
                $endDate = (clone $startDate)->addWeeks(2)->endOfDay();

                $giaiDoan = GiaiDoanThucHien::create([
                    'MaDeTai' => $deTai->MaDeTai,
                    'TenGiaiDoan' => $meta['ten'],
                    'MoTa' => $meta['mo_ta'],
                    'NoiDungCongViec' => $meta['cong_viec'],
                    'KetQuaDauRa' => $meta['dau_ra'],
                    'NgayBatDau' => $startDate->toDateString(),
                    'NgayKetThuc' => $endDate->toDateString(),
                    'YeuCauCanDat' => $meta['yeu_cau'],
                    'ThuTu' => $index,
                    'TrangThai' => ($index === 1) ? 'DANG_THUC_HIEN' : 'CHUA_BAT_DAU',
                ]);

                // Tạo các đợt nộp trong giai đoạn
                $dotIndex = 1;
                foreach ($meta['dot_nops'] as $dotMeta) {
                    $openDate = clone $startDate;
                    $deadline = clone $endDate;

                    DotNopBaoCao::create([
                        'MaGiaiDoan' => $giaiDoan->MaGiaiDoan,
                        'MaDeTai' => $deTai->MaDeTai,
                        'TenDotNop' => $dotMeta['ten'],
                        'MoTa' => $dotMeta['mo_ta'],
                        'NoiDungYeuCau' => $dotMeta['yeu_cau'],
                        'NgayMoNop' => $openDate,
                        'HanNop' => $deadline,
                        'DinhDangChoPhep' => $dotMeta['dinh_dang'],
                        'YeuCauSourceCode' => $dotMeta['source_code'],
                        'SoLanNopToiDa' => null,
                        'TrangThai' => ($index === 1) ? 'DANG_MO' : 'TAM_DONG',
                        'ThuTu' => $dotIndex++,
                    ]);
                }

                $index++;
            }

            return count(self::GIAI_DOAN_KY_THUAT_MAU);
        });
    }

    /**
     * Giảng viên thêm giai đoạn mới
     */
    public function taoGiaiDoan(DeTai $deTai, array $data): GiaiDoanThucHien
    {
        $maxThuTu = GiaiDoanThucHien::where('MaDeTai', $deTai->MaDeTai)->max('ThuTu') ?? 0;
        return GiaiDoanThucHien::create([
            'MaDeTai' => $deTai->MaDeTai,
            'TenGiaiDoan' => $data['TenGiaiDoan'],
            'MoTa' => $data['MoTa'] ?? null,
            'NoiDungCongViec' => $data['NoiDungCongViec'] ?? null,
            'KetQuaDauRa' => $data['KetQuaDauRa'] ?? null,
            'NgayBatDau' => $data['NgayBatDau'] ?? null,
            'NgayKetThuc' => $data['NgayKetThuc'] ?? null,
            'YeuCauCanDat' => $data['YeuCauCanDat'] ?? null,
            'ThuTu' => $data['ThuTu'] ?? ($maxThuTu + 1),
            'TrangThai' => $data['TrangThai'] ?? 'CHUA_BAT_DAU',
        ]);
    }

    /**
     * Giảng viên cập nhật giai đoạn
     */
    public function capNhatGiaiDoan(GiaiDoanThucHien $giaiDoan, array $data): bool
    {
        return $giaiDoan->update([
            'TenGiaiDoan' => $data['TenGiaiDoan'] ?? $giaiDoan->TenGiaiDoan,
            'MoTa' => $data['MoTa'] ?? $giaiDoan->MoTa,
            'NoiDungCongViec' => $data['NoiDungCongViec'] ?? $giaiDoan->NoiDungCongViec,
            'KetQuaDauRa' => $data['KetQuaDauRa'] ?? $giaiDoan->KetQuaDauRa,
            'NgayBatDau' => $data['NgayBatDau'] ?? $giaiDoan->NgayBatDau,
            'NgayKetThuc' => $data['NgayKetThuc'] ?? $giaiDoan->NgayKetThuc,
            'YeuCauCanDat' => $data['YeuCauCanDat'] ?? $giaiDoan->YeuCauCanDat,
            'ThuTu' => $data['ThuTu'] ?? $giaiDoan->ThuTu,
            'TrangThai' => $data['TrangThai'] ?? $giaiDoan->TrangThai,
        ]);
    }

    /**
     * Giảng viên xóa giai đoạn (chỉ khi chưa có đợt nộp nào có dữ liệu báo cáo)
     */
    public function xoaGiaiDoan(GiaiDoanThucHien $giaiDoan): bool
    {
        $hasSubmissions = KhoiBaoCaoTienDo::where('MaGiaiDoan', $giaiDoan->MaGiaiDoan)->exists();
        if ($hasSubmissions) {
            throw new \Exception('Không thể xóa giai đoạn đã có báo cáo nộp từ sinh viên. Vui lòng chuyển trạng thái hoặc lưu trữ.');
        }

        return DB::transaction(function () use ($giaiDoan) {
            $giaiDoan->dotNops()->delete();
            return $giaiDoan->delete();
        });
    }

    /**
     * Giảng viên tạo đợt nộp báo cáo mới trong một giai đoạn
     */
    public function taoDotNop(GiaiDoanThucHien $giaiDoan, array $data): DotNopBaoCao
    {
        $maxThuTu = DotNopBaoCao::where('MaGiaiDoan', $giaiDoan->MaGiaiDoan)->max('ThuTu') ?? 0;
        return DotNopBaoCao::create([
            'MaGiaiDoan' => $giaiDoan->MaGiaiDoan,
            'MaDeTai' => $giaiDoan->MaDeTai,
            'TenDotNop' => $data['TenDotNop'],
            'MoTa' => $data['MoTa'] ?? null,
            'NoiDungYeuCau' => $data['NoiDungYeuCau'] ?? null,
            'NgayMoNop' => $data['NgayMoNop'] ?? null,
            'HanNop' => $data['HanNop'] ?? null,
            'DinhDangChoPhep' => $data['DinhDangChoPhep'] ?? 'pdf',
            'YeuCauSourceCode' => filter_var($data['YeuCauSourceCode'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'SoLanNopToiDa' => !empty($data['SoLanNopToiDa']) ? (int)$data['SoLanNopToiDa'] : null,
            'TrangThai' => $data['TrangThai'] ?? 'DANG_MO',
            'ThuTu' => $data['ThuTu'] ?? ($maxThuTu + 1),
        ]);
    }

    /**
     * Giảng viên nhận xét và đánh giá một phiên bản báo cáo của đợt nộp
     */
    public function danhGiaPhienBanBaoCao(
        DotNopBaoCao $dotNop,
        KhoiBaoCaoTienDo $phienBan,
        string $hanhDong, // 'DAT' hoặc 'YEU_CAU_NOP_LAI'
        string $nhanXet,
        GiangVien $giangVien
    ): KhoiBaoCaoTienDo {
        return DB::transaction(function () use ($dotNop, $phienBan, $hanhDong, $nhanXet, $giangVien) {
            $isDat = ($hanhDong === 'DAT');

            $phienBan->TrangThai = $isDat ? 'Đạt' : 'Yêu cầu nộp lại';
            $phienBan->NhanXetGV = $nhanXet;
            $phienBan->NguoiNhanXet = $giangVien->HoTen;
            $phienBan->NgayNhanXet = now();
            if ($isDat) {
                $phienBan->PhanTramHoanThanh = 100;
            }
            $phienBan->save();

            // Cập nhật trạng thái của đợt nộp
            if ($isDat) {
                $dotNop->TrangThai = 'HOAN_THANH';
                $dotNop->save();

                // Cập nhật trạng thái giai đoạn
                $dotNop->giaiDoan?->capNhatTrangThai();
            } else {
                // Mở lại cho nộp nếu chưa đóng
                if ($dotNop->TrangThai !== 'DA_DONG') {
                    $dotNop->TrangThai = 'DANG_MO';
                    $dotNop->save();
                }
            }

            // Đồng bộ trạng thái vào bản ghi BaoCaoTienDo cha
            if ($phienBan->MaBaoCao) {
                BaoCaoTienDo::where('MaBaoCao', $phienBan->MaBaoCao)->update([
                    'TrangThai' => $isDat ? 'Đạt' : 'Yêu cầu nộp lại',
                    'NhanXet' => $nhanXet,
                    'NguoiXacNhan' => $giangVien->HoTen,
                    'NgayXacNhan' => now(),
                    'PhanTramHoanThanh' => $isDat ? 100 : $phienBan->PhanTramHoanThanh,
                ]);
            }

            return $phienBan;
        });
    }

    // =========================================================================
    // PHẦN 3: DÀNH CHO SINH VIÊN - NỘP BÁO CÁO NHIỀU PHIÊN BẢN (VERSIONING)
    // =========================================================================

    /**
     * Sinh viên nộp báo cáo tạo phiên bản mới (LanNop = N + 1)
     */
    public function nopBaoCaoPhienBanMoi(
        DotNopBaoCao $dotNop,
        SinhVien $sinhVien,
        array $data,
        $uploadedFile = null
    ): KhoiBaoCaoTienDo {
        return DB::transaction(function () use ($dotNop, $sinhVien, $data, $uploadedFile) {
            // Xác định số lần nộp hiện tại (Version)
            $latestVersion = KhoiBaoCaoTienDo::where('MaDotNop', $dotNop->MaDotNop)->max('LanNop') ?? 0;
            $newVersion = $latestVersion + 1;

            // Kiểm tra số lần nộp tối đa nếu có cấu hình
            if ($dotNop->SoLanNopToiDa && $newVersion > $dotNop->SoLanNopToiDa) {
                throw new \Exception("Đợt báo cáo này giới hạn tối đa {$dotNop->SoLanNopToiDa} lần nộp. Bạn không thể nộp thêm.");
            }

            // Xử lý upload file PDF
            $tenFile = null;
            $duongDanFile = null;
            if ($uploadedFile) {
                $fileName = 'baocao_dot' . $dotNop->MaDotNop . '_v' . $newVersion . '_' . time() . '.' . $uploadedFile->getClientOriginalExtension();
                $path = $uploadedFile->storeAs('baocao', $fileName, 'public');
                $tenFile = $uploadedFile->getClientOriginalName();
                $duongDanFile = $path;
            } elseif ($latestVersion > 0) {
                // Kế thừa file từ phiên bản trước nếu không upload file mới
                $prev = KhoiBaoCaoTienDo::where('MaDotNop', $dotNop->MaDotNop)->where('LanNop', $latestVersion)->first();
                if ($prev) {
                    $tenFile = $prev->TenFile;
                    $duongDanFile = $prev->DuongDanFile;
                }
            }

            $userCompletionPct = !empty($data['PhanTramHoanThanh']) ? (int)$data['PhanTramHoanThanh'] : null;

            // Đảm bảo tồn tại bản ghi BaoCaoTienDo cha cho đợt nộp này (tương thích FK và các màn hình thống kê)
            $maBaoCao = 'BC_DOT_' . $dotNop->MaDotNop;
            $baoCaoCha = BaoCaoTienDo::firstOrCreate(
                ['MaBaoCao' => $maBaoCao],
                [
                    'LanBaoCao' => $dotNop->ThuTu,
                    'TieuDe' => $dotNop->TenDotNop,
                    'MaDeTai' => $dotNop->MaDeTai,
                    'TrangThai' => 'Chờ duyệt',
                    'NgayNop' => now(),
                    'PhanTramHoanThanh' => $userCompletionPct ?? 50,
                    'TenFile' => $tenFile,
                    'DuongDanFile' => $duongDanFile,
                ]
            );

            // Đồng bộ cập nhật thông tin mới nhất vào BaoCaoTienDo cha
            $baoCaoCha->update([
                'TieuDe' => $dotNop->TenDotNop,
                'TrangThai' => 'Chờ duyệt',
                'NgayNop' => now(),
                'PhanTramHoanThanh' => $userCompletionPct ?? (int)$baoCaoCha->PhanTramHoanThanh,
                'TenFile' => $tenFile ?? $baoCaoCha->TenFile,
                'DuongDanFile' => $duongDanFile ?? $baoCaoCha->DuongDanFile,
            ]);

            // Sinh mã khối báo cáo độc nhất
            $maKhoi = 'KHOI_D' . $dotNop->MaDotNop . '_V' . $newVersion . '_' . substr(md5(uniqid()), 0, 6);

            $khoi = KhoiBaoCaoTienDo::create([
                'MaKhoi' => $maKhoi,
                'MaBaoCao' => $maBaoCao,
                'MaDotNop' => $dotNop->MaDotNop,
                'MaGiaiDoan' => $dotNop->MaGiaiDoan,
                'MaDeTai' => $dotNop->MaDeTai,
                'LanBaoCao' => $dotNop->ThuTu,
                'LanNop' => $newVersion,
                'TieuDe' => $data['TieuDe'] ?? ($dotNop->TenDotNop . ' (Phiên bản ' . $newVersion . ')'),
                'NoiDungBaoCao' => $data['NoiDungBaoCao'] ?? '',
                'KetQuaDatDuoc' => $data['KetQuaDatDuoc'] ?? '',
                'KhoKhan' => $data['KhoKhan'] ?? '',
                'CongViecChuaHoanThanh' => $data['CongViecChuaHoanThanh'] ?? '',
                'HuongGiaiQuyet' => $data['HuongGiaiQuyet'] ?? '',
                'KeHoachTiepTheo' => $data['KeHoachTiepTheo'] ?? '',
                'PhanTramHoanThanh' => $userCompletionPct ?? 50,
                'TenFile' => $tenFile,
                'DuongDanFile' => $duongDanFile,
                'LinkCode' => $data['LinkCode'] ?? null,
                'TrangThai' => 'Chờ duyệt',
                'NgayNop' => now(),
            ]);

            // Cập nhật giai đoạn sang ĐANG_THUC_HIEN nếu đang CHUA_BAT_DAU
            $giaiDoan = $dotNop->giaiDoan;
            if ($giaiDoan && $giaiDoan->TrangThai === 'CHUA_BAT_DAU') {
                $giaiDoan->TrangThai = 'DANG_THUC_HIEN';
                $giaiDoan->save();
            }

            // Persist a database job with the submission; no API/network work in this transaction.
            if ($duongDanFile && file_exists(storage_path('app/public/' . $duongDanFile))) {
                app(AiReportProcessingService::class)->enqueue($khoi);
            }

            return $khoi;
        });
    }
}
