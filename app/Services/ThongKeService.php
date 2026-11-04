<?php

namespace App\Services;

use App\Models\HocKy;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\Nganh;
use App\Models\HocPhan;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\PhieuChamDiem;
use App\Models\KetQuaSinhVien;
use App\Models\DanhSachSVDuDieuKien;
use App\Models\PhanCongPhanBien;
use App\Models\AuditLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ThongKeService
{
    /**
     * Lấy thời điểm hiện tại của hệ thống (hỗ trợ giờ ảo nếu đang demo)
     */
    public static function getNow(): Carbon
    {
        return HoiDong::getSystemNow();
    }

    /**
     * Quy đổi điểm hệ 10 sang thang 4, điểm chữ, xếp loại và kết quả Đạt / Không đạt
     */
    public static function quyDoiDiem(?float $score): array
    {
        if ($score === null) {
            return [
                'diemHe10'   => null,
                'diemHe4'    => null,
                'diemChu'    => null,
                'xepLoai'    => 'Chưa đủ điểm',
                'ketQua'     => 'Chưa có kết quả',
                'isDat'      => false,
                'isHoanTat'  => false,
            ];
        }

        $d10 = round($score, 2);

        $d4 = match (true) {
            $d10 >= 8.5 => 4.0,
            $d10 >= 8.0 => 3.5,
            $d10 >= 7.0 => 3.0,
            $d10 >= 6.5 => 2.5,
            $d10 >= 5.5 => 2.0,
            $d10 >= 5.0 => 1.5,
            $d10 >= 4.0 => 1.0,
            default     => 0.0,
        };

        $chu = match (true) {
            $d10 >= 8.5 => 'A',
            $d10 >= 8.0 => 'B+',
            $d10 >= 7.0 => 'B',
            $d10 >= 6.5 => 'C+',
            $d10 >= 5.5 => 'C',
            $d10 >= 5.0 => 'D+',
            $d10 >= 4.0 => 'D',
            default     => 'F',
        };

        $xepLoai = match (true) {
            $d10 >= 9.0 => 'Xuất sắc',
            $d10 >= 8.0 => 'Giỏi',
            $d10 >= 7.0 => 'Khá',
            $d10 >= 5.5 => 'Trung bình',
            $d10 >= 4.0 => 'Trung bình yếu',
            default     => 'Không đạt',
        };

        $isDat = ($d10 >= 4.0 && $chu !== 'F');

        return [
            'diemHe10'   => $d10,
            'diemHe4'    => round($d4, 2),
            'diemChu'    => $chu,
            'xepLoai'    => $xepLoai,
            'ketQua'     => $isDat ? 'Đạt' : 'Không đạt',
            'isDat'      => $isDat,
            'isHoanTat'  => true,
        ];
    }

    /**
     * Lấy danh mục dữ liệu phục vụ bộ lọc liên kết và danh sách biểu mẫu
     */
    public function getDanhSachDanhMuc(array $scope = []): array
    {
        $namHocs = HocKy::whereNotNull('NamHoc')
            ->distinct()
            ->orderBy('NamHoc', 'desc')
            ->pluck('NamHoc')
            ->values()
            ->all();

        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        $dotBaoVes = HoiDong::whereNotNull('DotBaoVe')
            ->where('DotBaoVe', '!=', '')
            ->distinct()
            ->pluck('DotBaoVe')
            ->values()
            ->all();
        if (empty($dotBaoVes)) {
            $dotBaoVes = ['Đợt 1', 'Đợt 2'];
        }

        $khoasQuery = Khoa::query();
        if (($scope['role'] ?? '') === 'truongkhoa' && !empty($scope['maKhoa'])) {
            $khoasQuery->where('MaKhoa', $scope['maKhoa']);
        }
        $khoas = $khoasQuery->orderBy('TenKhoa')->get();

        $boMonsQuery = BoMon::query();
        if (($scope['role'] ?? '') === 'truongkhoa' && !empty($scope['maKhoa'])) {
            $boMonsQuery->where('MaKhoa', $scope['maKhoa']);
        }
        $boMons = $boMonsQuery->orderBy('TenBoMon')->get();

        $nganhsQuery = Nganh::query();
        if (($scope['role'] ?? '') === 'truongkhoa' && !empty($scope['maKhoa'])) {
            $nganhsQuery->where('MaKhoa', $scope['maKhoa']);
        }
        $nganhs = $nganhsQuery->orderBy('TenNganh')->get();

        $hocPhansQuery = HocPhan::query();
        if (($scope['role'] ?? '') === 'truongkhoa' && !empty($scope['maKhoa'])) {
            $hocPhansQuery->where('MaKhoa', $scope['maKhoa']);
        }
        $hocPhans = $hocPhansQuery->orderBy('TenHocPhan')->get();

        $hoiDongsQuery = HoiDong::query();
        if (($scope['role'] ?? '') === 'truongkhoa' && !empty($scope['maKhoa'])) {
            $hoiDongsQuery->where('MaKhoa', $scope['maKhoa']);
        }
        $hoiDongs = $hoiDongsQuery->orderBy('TenHoiDong')->get();

        $giangViensQuery = GiangVien::query();
        if (($scope['role'] ?? '') === 'truongkhoa' && !empty($scope['maKhoa'])) {
            $giangViensQuery->whereHas('boMon', fn($bm) => $bm->where('MaKhoa', $scope['maKhoa']));
        }
        $giangViens = $giangViensQuery->orderBy('HoTen')->get();

        // 10 Biểu mẫu báo cáo chuẩn quy định
        $mauBaoCaos = [
            'mau_01' => [
                'ma'            => 'mau_01',
                'soHieu'        => 'MẪU 01',
                'ten'           => 'Báo cáo tổng hợp kết quả khóa luận theo đợt',
                'moTa'          => 'Tổng hợp chi tiết theo từng hội đồng, điểm các giám khảo và tổng kết toàn đợt.',
                'loaiBaoCao'    => 'Báo cáo tổng hợp',
                'thongTinChinh' => [
                    'Tổng số sinh viên, đề tài & hội đồng',
                    'Chi tiết điểm giám khảo CT, TK, UV',
                    'Điểm tổng kết & kết quả Đạt / Chưa đạt'
                ],
                'icon'          => 'fa-calendar-week',
                'badge'         => 'Toàn đợt',
                'color'         => 'primary',
            ],
            'mau_02' => [
                'ma'            => 'mau_02',
                'soHieu'        => 'MẪU 02',
                'ten'           => 'Báo cáo thống kê kết quả khóa luận theo ngành',
                'moTa'          => 'Phân tích theo từng ngành, ma trận điểm (Max, Min, TB), tỷ lệ Đạt và phân bố điểm chữ A-F.',
                'loaiBaoCao'    => 'Thống kê theo ngành',
                'thongTinChinh' => [
                    'Bảng tổng hợp chỉ số từng ngành đào tạo',
                    'Điểm trung bình & tỷ lệ Đạt theo ngành',
                    'Danh sách chi tiết sinh viên theo ngành'
                ],
                'icon'          => 'fa-graduation-cap',
                'badge'         => 'Theo ngành',
                'color'         => 'success',
            ],
            'mau_03' => [
                'ma'            => 'mau_03',
                'soHieu'        => 'MẪU 03',
                'ten'           => 'Báo cáo danh sách sinh viên đạt khóa luận',
                'moTa'          => 'Danh sách sinh viên đủ điều kiện công nhận đạt khóa luận tốt nghiệp theo chuẩn quy chế.',
                'loaiBaoCao'    => 'Danh sách sinh viên đạt',
                'thongTinChinh' => [
                    'Sinh viên có điểm tổng kết ≥ 4.0 và đạt tất cả CLO',
                    'Điểm hệ 10, quy đổi hệ 4 và điểm chữ',
                    'Xếp loại học lực tốt nghiệp chính thức'
                ],
                'icon'          => 'fa-user-check',
                'badge'         => 'Đạt chuẩn',
                'color'         => 'success',
            ],
            'mau_04' => [
                'ma'            => 'mau_04',
                'soHieu'        => 'MẪU 04',
                'ten'           => 'Báo cáo danh sách sinh viên chưa đạt khóa luận',
                'moTa'          => 'Danh sách sinh viên có điểm số dưới 4.0 (F) hoặc chưa đạt yêu cầu cần xử lý bảo vệ lại.',
                'loaiBaoCao'    => 'Danh sách chưa đạt',
                'thongTinChinh' => [
                    'Sinh viên có điểm tổng kết < 4.0 hoặc có CLO chưa đạt',
                    'Thông tin đề tài, GV hướng dẫn & Hội đồng',
                    'Ghi chú tình trạng & phương án xử lý'
                ],
                'icon'          => 'fa-user-xmark',
                'badge'         => 'Chưa đạt',
                'color'         => 'danger',
            ],
            'mau_05' => [
                'ma'            => 'mau_05',
                'soHieu'        => 'MẪU 05',
                'ten'           => 'Báo cáo sinh viên chưa có kết quả (Đang chấm)',
                'moTa'          => 'Theo dõi tiến độ chấm điểm các đề tài chưa đủ phiếu đánh giá từ các thành viên hội đồng.',
                'loaiBaoCao'    => 'Tiến độ chấm điểm',
                'thongTinChinh' => [
                    'Sinh viên chưa hoàn tất đủ phiếu chấm',
                    'Chi tiết phiếu thiếu: GVHD, GVPB, CT, TK',
                    'Đôn đốc tiến độ nộp phiếu chấm điểm'
                ],
                'icon'          => 'fa-clock',
                'badge'         => 'Đang chấm',
                'color'         => 'warning',
            ],
            'mau_06' => [
                'ma'            => 'mau_06',
                'soHieu'        => 'MẪU 06',
                'ten'           => 'Báo cáo thống kê điểm số theo hội đồng',
                'moTa'          => 'Chi tiết từng hội đồng, danh sách giám khảo, bảng đối chiếu điểm và cảnh báo lệch điểm.',
                'loaiBaoCao'    => 'Quản lý hội đồng',
                'thongTinChinh' => [
                    'Thông tin Chủ tịch, Thư ký, UV hội đồng',
                    'Điểm trung bình và tỷ lệ đạt từng hội đồng',
                    'Cảnh báo sai lệch điểm giữa các giám khảo'
                ],
                'icon'          => 'fa-landmark',
                'badge'         => 'Hội đồng',
                'color'         => 'info',
            ],
            'mau_07' => [
                'ma'            => 'mau_07',
                'soHieu'        => 'MẪU 07',
                'ten'           => 'Báo cáo phân bố điểm khóa luận tốt nghiệp',
                'moTa'          => 'Thống kê chi tiết theo từng dải điểm (thang 10, thang 4, điểm chữ) và tỷ lệ % sinh viên.',
                'loaiBaoCao'    => 'Phân tích phổ điểm',
                'thongTinChinh' => [
                    'Phân bố theo 6 khoảng điểm thang 10',
                    'Bảng quy đổi thang chữ A, B+, B, C, D, F',
                    'Tỷ lệ phần trăm và biểu đồ phân bố'
                ],
                'icon'          => 'fa-chart-simple',
                'badge'         => 'Phân bố điểm',
                'color'         => 'secondary',
            ],
            'mau_08' => [
                'ma'            => 'mau_08',
                'soHieu'        => 'MẪU 08',
                'ten'           => 'Báo cáo tình hình tổ chức khóa luận tốt nghiệp',
                'moTa'          => 'Theo dõi số lượng theo đợt, phân công hội đồng, tiến độ chấm và tỷ lệ hoàn thành.',
                'loaiBaoCao'    => 'Báo cáo tổ chức',
                'thongTinChinh' => [
                    'Số lượng sinh viên đủ điều kiện làm KLTN',
                    'Tỷ lệ ghép sinh viên vào hội đồng bảo vệ',
                    'Tiến độ hoàn thành chấm điểm đợt'
                ],
                'icon'          => 'fa-diagram-project',
                'badge'         => 'Tổ chức',
                'color'         => 'primary',
            ],
            'mau_09' => [
                'ma'            => 'mau_09',
                'soHieu'        => 'MẪU 09',
                'ten'           => 'Báo cáo thống kê khóa luận theo bộ môn',
                'moTa'          => 'Tổng hợp số lượng đề tài, sinh viên, hội đồng và kết quả đào tạo theo từng bộ môn quản lý.',
                'loaiBaoCao'    => 'Thống kê bộ môn',
                'thongTinChinh' => [
                    'Số lượng đề tài & SV theo bộ môn phụ trách',
                    'Số lượng giảng viên hướng dẫn của bộ môn',
                    'Điểm trung bình và tỷ lệ đạt từng bộ môn'
                ],
                'icon'          => 'fa-sitemap',
                'badge'         => 'Bộ môn',
                'color'         => 'dark',
            ],
            'mau_10' => [
                'ma'            => 'mau_10',
                'soHieu'        => 'MẪU 10',
                'ten'           => 'Báo cáo thống kê khóa luận theo học phần',
                'moTa'          => 'Theo dõi tình hình thực hiện và kết quả điểm số theo từng mã học phần khóa luận tốt nghiệp.',
                'loaiBaoCao'    => 'Thống kê học phần',
                'thongTinChinh' => [
                    'Mã học phần, tên học phần & số tín chỉ',
                    'Số lượng sinh viên đăng ký học phần',
                    'Điểm trung bình và kết quả học phần'
                ],
                'icon'          => 'fa-book-bookmark',
                'badge'         => 'Học phần',
                'color'         => 'info',
            ],
        ];

        return compact(
            'namHocs',
            'hocKies',
            'dotBaoVes',
            'khoas',
            'boMons',
            'nganhs',
            'hocPhans',
            'hoiDongs',
            'giangViens',
            'mauBaoCaos'
        );
    }

    /**
     * BỘ MÁY THỐNG KÊ TỔNG HỢP TOÀN DIỆN (Core Aggregator Engine)
     * Thống kê KPI, biểu đồ phân tích và chuẩn bị dữ liệu cho cả 10 biểu mẫu báo cáo
     */
    public function thongKeTongHop(array $filters, array $scope = []): array
    {
        // 1. Xác định Học kỳ & Năm học
        $maHocKy = $filters['MaHocKy'] ?? $filters['ma_hoc_ky'] ?? null;
        $namHoc = $filters['NamHoc'] ?? $filters['nam_hoc'] ?? null;

        if (!$maHocKy && $namHoc) {
            $hkByNam = HocKy::where('NamHoc', $namHoc)->orderBy('MaHocKy', 'desc')->first();
            $maHocKy = $hkByNam?->MaHocKy;
        }

        if (!$maHocKy) {
            $hocKy = HocKy::where('TrangThai', 'Đang diễn ra')->first() 
                  ?? HocKy::where('TrangThai', true)->first() 
                  ?? HocKy::orderBy('MaHocKy', 'desc')->first();
            $maHocKy = $hocKy?->MaHocKy;
        }

        $hocKy = HocKy::find($maHocKy);
        if ($hocKy && empty($namHoc)) {
            $namHoc = $hocKy->NamHoc;
        }

        // 2. Thống kê cấp Đợt / Học kỳ: Tổng SV tham gia & Tổng Đề tài
        $tongSVDuDieuKien = DanhSachSVDuDieuKien::where('MaHocKy', $maHocKy)
            ->where('TrangThai', 'Đủ điều kiện')
            ->count();

        $tongDeTaiHocKy = DeTai::where('MaHocKy', $maHocKy)
            ->whereIn('TrangThai', ['Đã công bố', 'Hoàn thành', 'Đã duyệt đề cương'])
            ->count();

        // 3. Xây dựng truy vấn Hội đồng & Eager loading
        $hdQuery = HoiDong::with([
            'thanhViens.giangVien.boMon',
            'hoSoBaoVes.deTai.giangVien.boMon.khoa',
            'hoSoBaoVes.deTai.hocPhan',
            'hoSoBaoVes.nhom.thanhViens.sinhVien.lop.nganh.khoa',
            'khoa',
            'nganh',
            'hocKy',
        ])
        ->where('MaHocKy', $maHocKy);

        // Giới hạn Scope Trưởng khoa
        if (($scope['role'] ?? '') === 'truongkhoa' && !empty($scope['maKhoa'])) {
            $maKhoa = $scope['maKhoa'];
            $hdQuery->where(function ($q) use ($maKhoa) {
                $q->where('MaKhoa', $maKhoa)
                  ->orWhereHas('hoSoBaoVes.deTai.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa));
            });
        } elseif (!empty($filters['MaKhoa']) || !empty($filters['ma_khoa'])) {
            $maKhoa = $filters['MaKhoa'] ?? $filters['ma_khoa'];
            $hdQuery->where(function ($q) use ($maKhoa) {
                $q->where('MaKhoa', $maKhoa)
                  ->orWhereHas('hoSoBaoVes.deTai.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa));
            });
        }

        // Lọc Đợt khóa luận (DotBaoVe)
        $dotBaoVe = $filters['DotBaoVe'] ?? $filters['dot_bao_ve'] ?? null;
        if (!empty($dotBaoVe)) {
            $hdQuery->where('DotBaoVe', $dotBaoVe);
        }

        // Lọc Bộ môn
        $maBoMon = $filters['MaBoMon'] ?? $filters['ma_bo_mon'] ?? null;
        if (!empty($maBoMon)) {
            $bmAlt = str_starts_with($maBoMon, 'BM_') ? substr($maBoMon, 3) : 'BM_' . $maBoMon;
            $hdQuery->whereHas('hoSoBaoVes.deTai.giangVien', fn($gv) => $gv->whereIn('MaBoMon', [$maBoMon, $bmAlt]));
        }

        // Lọc Ngành
        $maNganh = $filters['MaNganh'] ?? $filters['ma_nganh'] ?? null;
        if (!empty($maNganh)) {
            if (is_array($maNganh)) {
                $hdQuery->whereIn('MaNganh', $maNganh);
            } else {
                $hdQuery->where('MaNganh', $maNganh);
            }
        }

        // Lọc Học phần
        $maHocPhan = $filters['MaHocPhan'] ?? $filters['ma_hoc_phan'] ?? null;
        if (!empty($maHocPhan)) {
            $hdQuery->whereHas('hoSoBaoVes.deTai', fn($dt) => $dt->where('MaHocPhan', $maHocPhan));
        }

        // Lọc Hội đồng
        $maHoiDong = $filters['MaHoiDong'] ?? $filters['ma_hoi_dong'] ?? null;
        if (!empty($maHoiDong)) {
            if (is_array($maHoiDong)) {
                $hdQuery->whereIn('MaHoiDong', $maHoiDong);
            } else {
                $hdQuery->where('MaHoiDong', $maHoiDong);
            }
        }

        // Lọc Giảng viên hướng dẫn
        $maGV = $filters['MaGV'] ?? $filters['ma_gv'] ?? null;
        if (!empty($maGV)) {
            $hdQuery->whereHas('hoSoBaoVes.deTai', fn($dt) => $dt->where('MaGV', $maGV));
        }

        $hoiDongs = $hdQuery->orderBy('MaNganh')->orderBy('MaHoiDong')->get();

        // 4. Lấy toàn bộ phiếu chấm & kết quả
        $allHoSoIds = $hoiDongs->flatMap(fn($h) => $h->hoSoBaoVes->pluck('MaHoSo'))->unique()->values()->all();
        $phieuChams = PhieuChamDiem::with('giangVien')->whereIn('MaHoSo', $allHoSoIds)->get();
        $ketQuas = KetQuaSinhVien::whereIn('MaHoSo', $allHoSoIds)->get()->keyBy(fn($k) => $k->MaHoSo . '_' . $k->MaSV);

        $allDeTaiIds = $hoiDongs->flatMap(fn($h) => $h->hoSoBaoVes->pluck('MaDeTai'))->unique()->values()->all();
        $phanBiens = PhanCongPhanBien::with('giangVien')
            ->whereIn('MaDeTai', $allDeTaiIds)
            ->get()
            ->groupBy('MaDeTai');

        // 5. Tổng hợp dữ liệu chi tiết
        $searchQuery = mb_strtolower(trim($filters['search'] ?? ''));
        $ketQuaFilter = $filters['ket_qua'] ?? $filters['trang_thai'] ?? null;
        $mauBaoCao = $filters['mau_bao_cao'] ?? 'mau_01';
        $hinhThuc = $filters['hinh_thuc'] ?? 'tong_hop';

        $danhSachHoiDong = [];
        $danhSachTatCaSV = [];
        $danhSachTatCaDeTai = [];

        $tongSVPhanCong = 0;
        $tongSVHoanThanhCham = 0;
        $tongSVChuaHoanThanhCham = 0;
        $tongSVDat = 0;
        $tongSVChuaDat = 0;
        $tongSVChuaCoKQ = 0;

        $allScoresValid = [];
        $phanBoDiemChu = ['A' => 0, 'B+' => 0, 'B' => 0, 'C+' => 0, 'C' => 0, 'D+' => 0, 'D' => 0, 'F' => 0];
        $phanBoKhoangDiem = [
            'duoi_4'  => 0, // < 4.0 (F)
            '4_5'     => 0, // 4.0 - 4.9 (D)
            '5_65'    => 0, // 5.0 - 6.4 (D+, C)
            '65_8'    => 0, // 6.5 - 7.9 (C+, B)
            '8_9'     => 0, // 8.0 - 8.9 (B+, A)
            '9_10'    => 0, // 9.0 - 10.0 (Xuất sắc)
        ];

        $statsTheoNganh = [];
        $statsTheoBoMon = [];
        $statsTheoHocPhan = [];
        $statsTheoHoiDong = [];

        $soHoiDongHoanThanh = 0;
        $soHoiDongChuaHoanThanh = 0;
        $hasChuaCongBo = false;

        foreach ($hoiDongs as $hd) {
            if ($hd->TrangThaiCongBo !== 'Đã công bố') {
                $hasChuaCongBo = true;
            }

            $thanhViens = $hd->thanhViens;
            $soThanhVienHD = $thanhViens->count();

            $ct = $thanhViens->first(fn($tv) => str_contains(mb_strtolower($tv->VaiTro ?? ''), 'chủ tịch'));
            $tk = $thanhViens->first(fn($tv) => str_contains(mb_strtolower($tv->VaiTro ?? ''), 'thư ký'));
            $uvs = $thanhViens->filter(fn($tv) => $tv !== $ct && $tv !== $tk)->values();

            $dsDeTaiHD = [];
            $soSVHD = 0;
            $soDatHD = 0;
            $soKhongDatHD = 0;
            $soChuaXongHD = 0;
            $tongDiemHD = 0.0;
            $soLuongDiemHD = 0;
            $hdIsFullCompleted = true;

            foreach ($hd->hoSoBaoVes as $hs) {
                $nhom = $hs->nhom;
                $deTai = $hs->deTai;
                $gvhd = $deTai?->giangVien;
                $gvpbList = $phanBiens->get($deTai?->MaDeTai ?? '', collect());
                $gvpb = $gvpbList->first()?->giangVien;

                $tenGVHD = self::formatLecturerName($gvhd);
                if ($tenGVHD === '—') {
                    $tenGVHD = 'Chưa phân công';
                }

                $gvpbGiangViens = $gvpbList->map(fn($pb) => $pb->giangVien ?? $pb->MaGV)->filter();
                $tenGVPB = self::formatLecturerName($gvpbGiangViens->isNotEmpty() ? $gvpbGiangViens : $gvpb);
                if ($tenGVPB === '—') {
                    $tenGVPB = 'Chưa phân công';
                }

                $phieusCuaHoSo = $phieuChams->where('MaHoSo', $hs->MaHoSo);
                $gvHoanThanhCount = $thanhViens->filter(function ($tv) use ($phieusCuaHoSo) {
                    $pGv = $phieusCuaHoSo->where('MaGV', $tv->MaGV);
                    return $pGv->isNotEmpty() && $pGv->every(fn($p) => $p->TrangThai === 'Đã hoàn thành');
                })->count();

                $isDuPhieu = ($soThanhVienHD > 0 && $gvHoanThanhCount >= $soThanhVienHD);
                if (!$isDuPhieu) {
                    $hdIsFullCompleted = false;
                }

                $thanhVienSVs = $nhom?->thanhViens ?? collect();
                $sinhViensData = [];

                foreach ($thanhVienSVs as $tvSv) {
                    $sv = $tvSv->sinhVien;
                    if (!$sv) continue;

                    // Điểm các giám khảo
                    $diemCT = $ct ? $phieusCuaHoSo->where('MaGV', $ct->MaGV)->where('MaSV', $sv->MaSV)->first()?->Diem : null;
                    $diemTK = $tk ? $phieusCuaHoSo->where('MaGV', $tk->MaGV)->where('MaSV', $sv->MaSV)->first()?->Diem : null;

                    $diemUVs = [];
                    $listScoresMem = [];
                    foreach ($uvs as $idxUv => $uv) {
                        $pUv = $phieusCuaHoSo->where('MaGV', $uv->MaGV)->where('MaSV', $sv->MaSV)->first();
                        $dVal = $pUv?->Diem !== null ? (float)$pUv->Diem : null;
                        $diemUVs[$idxUv] = [
                            'tenGV'  => $uv->giangVien?->HoTen ?? $uv->MaGV,
                            'vaiTro' => $uv->VaiTro ?? 'Ủy viên',
                            'diem'   => $dVal,
                        ];
                        if ($dVal !== null) $listScoresMem[] = $dVal;
                    }
                    if ($diemCT !== null) $listScoresMem[] = (float)$diemCT;
                    if ($diemTK !== null) $listScoresMem[] = (float)$diemTK;

                    $diffScore = !empty($listScoresMem) ? round(max($listScoresMem) - min($listScoresMem), 2) : 0.0;
                    $canhBaoLech = ($diffScore >= 1.5 && count($listScoresMem) >= 2);

                    // Điểm GVHD & GVPB
                    $kqItem = $ketQuas->get($hs->MaHoSo . '_' . $sv->MaSV);
                    $diemGVHD = $kqItem?->DiemHuongDan;
                    $diemGVPB = $kqItem?->DiemPhanBien;

                    if ($diemGVHD === null && $gvhd) {
                        $diemGVHD = $phieusCuaHoSo->where('MaGV', $gvhd->MaGV)->where('MaSV', $sv->MaSV)->first()?->Diem;
                    }
                    if ($diemGVPB === null && $gvpb) {
                        $diemGVPB = $phieusCuaHoSo->where('MaGV', $gvpb->MaGV)->where('MaSV', $sv->MaSV)->first()?->Diem;
                    }

                    // Điểm trung bình Hội đồng
                    $validScores = $phieusCuaHoSo->where('MaSV', $sv->MaSV)
                        ->where('TrangThai', 'Đã hoàn thành')
                        ->whereNotNull('Diem')
                        ->pluck('Diem')
                        ->map(fn($d) => (float)$d);
                    $diemHD = $validScores->isNotEmpty() ? round($validScores->avg(), 2) : null;

                    // Điểm tổng kết
                    if ($isDuPhieu) {
                        $finalScore = $kqItem?->DiemChinhThuc ?? $kqItem?->DiemTongKet ?? ($diemHD ?? 0.0);
                        $gradeData = self::quyDoiDiem((float)$finalScore);
                    } else {
                        $gradeData = self::quyDoiDiem(null);
                    }

                    $outcome = ['passed' => null, 'failed_clos' => []];
                    if ($isDuPhieu) {
                        $hs->setRelation('hoiDong', $hd);
                        $scoreResult = new KetQuaSinhVien(['MaSV' => $sv->MaSV, 'DiemChinhThuc' => $finalScore]);
                        $outcome = app(StudentOutcomeService::class)->evaluate($scoreResult, $hs, $phieusCuaHoSo);
                        $gradeData['isDat'] = $outcome['passed'] === true;
                        $gradeData['ketQua'] = $outcome['passed'] === null ? 'Chưa đủ dữ liệu CLO' : ($gradeData['isDat'] ? 'Đạt' : 'Không đạt');
                    }
                    $isStudentComplete = $isDuPhieu && $outcome['passed'] !== null;

                    $maNgSV = $sv->lop?->MaNganh ?? $sv->MaNganh ?? $hd->MaNganh ?? 'CHUNG';
                    $tenNgSV = $sv->lop?->nganh?->TenNganh ?? $hd->nganh?->TenNganh ?? 'Chung';
                    $maBMSV = $deTai?->giangVien?->MaBoMon ?? $hd->khoa?->MaKhoa ?? 'BM';
                    $tenBMSV = $deTai?->giangVien?->boMon?->TenBoMon ?? 'Bộ môn chung';
                    $maHPSV = $deTai?->MaHocPhan ?? 'HP_KLTN';
                    $tenHPSV = $deTai?->hocPhan?->TenHocPhan ?? $deTai?->HocPhan ?? 'Khóa luận tốt nghiệp';

                    $svItem = [
                        'sinhVien'          => $sv,
                        'maSV'              => $sv->MaSoSinhVien ?? $sv->MaSV,
                        'hoTen'             => $sv->HoTen,
                        'lop'               => $sv->lop?->TenLop ?? '',
                        'tenLop'            => $sv->lop?->TenLop ?? '',
                        'maNganh'           => $maNgSV,
                        'tenNganh'          => $tenNgSV,
                        'maBoMon'           => $maBMSV,
                        'tenBoMon'          => $tenBMSV,
                        'maHocPhan'         => $maHPSV,
                        'tenHocPhan'        => $tenHPSV,
                        'maHoiDong'         => $hd->MaHoiDong,
                        'tenHoiDong'        => $hd->TenHoiDong,
                        'nhom'              => $nhom,
                        'tenNhom'           => $nhom?->TenNhom ?? '',
                        'deTai'             => $deTai,
                        'maDeTai'           => $deTai?->MaDeTai ?? $hs->MaHoSo,
                        'tenDeTai'          => $deTai?->TenDeTai ?? 'Chưa xác định',
                        'gvhd'              => $tenGVHD,
                        'tenGVHD'           => $tenGVHD,
                        'gvhdObj'           => $gvhd,
                        'gvpb'              => $tenGVPB,
                        'tenGVPB'           => $tenGVPB,
                        'gvpbObj'           => $gvpb,
                        'diemGVHD'          => $diemGVHD !== null ? round((float)$diemGVHD, 2) : null,
                        'diemGVPB'          => $diemGVPB !== null ? round((float)$diemGVPB, 2) : null,
                        'diemCT'            => $diemCT !== null ? round((float)$diemCT, 2) : null,
                        'diemTK'            => $diemTK !== null ? round((float)$diemTK, 2) : null,
                        'diemUVs'           => $diemUVs,
                        'diffScore'         => $diffScore,
                        'canhBaoLech'       => $canhBaoLech,
                        'diemHD'            => $diemHD,
                        'diemTongKet'       => $isDuPhieu ? $gradeData['diemHe10'] : null,
                        'diemHe4'           => $isDuPhieu ? $gradeData['diemHe4'] : null,
                        'diemChu'           => $isDuPhieu ? $gradeData['diemChu'] : null,
                        'xepLoai'           => $gradeData['xepLoai'],
                        'ketQua'            => $gradeData['ketQua'],
                        'isDat'             => $isStudentComplete && $gradeData['isDat'],
                        'isDuPhieu'         => $isStudentComplete,
                        'failedClos'        => $outcome['failed_clos'],
                    ];

                    $sinhViensData[] = $svItem;
                }

                // Lọc theo tìm kiếm từ khóa
                if ($searchQuery !== '') {
                    $sinhViensData = array_values(array_filter($sinhViensData, function ($sv) use ($searchQuery, $deTai, $nhom, $hd) {
                        return str_contains(mb_strtolower($sv['maSV']), $searchQuery)
                            || str_contains(mb_strtolower($sv['hoTen']), $searchQuery)
                            || str_contains(mb_strtolower($sv['tenLop']), $searchQuery)
                            || str_contains(mb_strtolower($deTai?->TenDeTai ?? ''), $searchQuery)
                            || str_contains(mb_strtolower($nhom?->TenNhom ?? ''), $searchQuery)
                            || str_contains(mb_strtolower($hd->TenHoiDong ?? ''), $searchQuery);
                    }));
                }

                // Lọc theo kết quả
                if (!empty($ketQuaFilter) && !in_array($ketQuaFilter, ['all', 'ca_hai'])) {
                    $sinhViensData = array_values(array_filter($sinhViensData, function ($sv) use ($ketQuaFilter) {
                        if ($ketQuaFilter === 'Dat' || $ketQuaFilter === 'dat') return $sv['isDuPhieu'] && $sv['isDat'];
                        if ($ketQuaFilter === 'KhongDat' || $ketQuaFilter === 'khong_dat') return $sv['isDuPhieu'] && !$sv['isDat'];
                        if ($ketQuaFilter === 'ChuaXong' || $ketQuaFilter === 'chua_xong') return !$sv['isDuPhieu'];
                        return true;
                    }));
                }

                if (empty($sinhViensData)) {
                    continue;
                }

                $dsDeTaiHD[] = [
                    'hoSo'         => $hs,
                    'deTai'        => $deTai,
                    'nhom'         => $nhom,
                    'gvhd'         => $tenGVHD,
                    'tenGVHD'      => $tenGVHD,
                    'gvhdObj'      => $gvhd,
                    'gvpb'         => $tenGVPB,
                    'tenGVPB'      => $tenGVPB,
                    'gvpbObj'      => $gvpb,
                    'isDuPhieu'    => $isDuPhieu,
                    'sinhViens'    => $sinhViensData,
                ];

                if (!in_array($deTai?->MaDeTai, $danhSachTatCaDeTai)) {
                    $danhSachTatCaDeTai[] = $deTai?->MaDeTai;
                }
            }

            if (empty($dsDeTaiHD)) {
                continue;
            }

            // Tính số liệu cho Hội đồng
            foreach ($dsDeTaiHD as $dtItem) {
                foreach ($dtItem['sinhViens'] as $sv) {
                    $soSVHD++;
                    $tongSVPhanCong++;
                    $danhSachTatCaSV[] = $sv;

                    $maNg = $sv['maNganh'];
                    if (!isset($statsTheoNganh[$maNg])) {
                        $statsTheoNganh[$maNg] = [
                            'maNganh'    => $maNg,
                            'tenNganh'   => $sv['tenNganh'],
                            'soSV'       => 0,
                            'soDat'      => 0,
                            'soKhongDat' => 0,
                            'soChuaXong' => 0,
                            'scores'     => [],
                        ];
                    }
                    $statsTheoNganh[$maNg]['soSV']++;

                    $maBm = $sv['maBoMon'];
                    if (!isset($statsTheoBoMon[$maBm])) {
                        $statsTheoBoMon[$maBm] = [
                            'maBoMon'    => $maBm,
                            'tenBoMon'   => $sv['tenBoMon'],
                            'soSV'       => 0,
                            'soDat'      => 0,
                            'soKhongDat' => 0,
                            'soChuaXong' => 0,
                            'scores'     => [],
                        ];
                    }
                    $statsTheoBoMon[$maBm]['soSV']++;

                    $maHp = $sv['maHocPhan'];
                    if (!isset($statsTheoHocPhan[$maHp])) {
                        $statsTheoHocPhan[$maHp] = [
                            'maHocPhan'  => $maHp,
                            'tenHocPhan' => $sv['tenHocPhan'],
                            'soSV'       => 0,
                            'soDat'      => 0,
                            'soKhongDat' => 0,
                            'soChuaXong' => 0,
                            'scores'     => [],
                        ];
                    }
                    $statsTheoHocPhan[$maHp]['soSV']++;

                    if ($sv['isDuPhieu'] && $sv['diemTongKet'] !== null) {
                        $tongSVHoanThanhCham++;
                        $d10 = (float)$sv['diemTongKet'];
                        $soLuongDiemHD++;
                        $tongDiemHD += $d10;
                        $allScoresValid[] = $d10;

                        $statsTheoNganh[$maNg]['scores'][] = $d10;
                        $statsTheoBoMon[$maBm]['scores'][] = $d10;
                        $statsTheoHocPhan[$maHp]['scores'][] = $d10;

                        // Phân bố điểm chữ
                        $chu = $sv['diemChu'];
                        if (isset($phanBoDiemChu[$chu])) {
                            $phanBoDiemChu[$chu]++;
                        }

                        // Phân bố dải điểm
                        if ($d10 < 4.0) $phanBoKhoangDiem['duoi_4']++;
                        elseif ($d10 < 5.0) $phanBoKhoangDiem['4_5']++;
                        elseif ($d10 < 6.5) $phanBoKhoangDiem['5_65']++;
                        elseif ($d10 < 8.0) $phanBoKhoangDiem['65_8']++;
                        elseif ($d10 < 9.0) $phanBoKhoangDiem['8_9']++;
                        else $phanBoKhoangDiem['9_10']++;

                        if ($sv['isDat']) {
                            $soDatHD++;
                            $tongSVDat++;
                            $statsTheoNganh[$maNg]['soDat']++;
                            $statsTheoBoMon[$maBm]['soDat']++;
                            $statsTheoHocPhan[$maHp]['soDat']++;
                        } else {
                            $soKhongDatHD++;
                            $tongSVChuaDat++;
                            $statsTheoNganh[$maNg]['soKhongDat']++;
                            $statsTheoBoMon[$maBm]['soKhongDat']++;
                            $statsTheoHocPhan[$maHp]['soKhongDat']++;
                        }
                    } else {
                        $soChuaXongHD++;
                        $tongSVChuaHoanThanhCham++;
                        $tongSVChuaCoKQ++;
                        $statsTheoNganh[$maNg]['soChuaXong']++;
                        $statsTheoBoMon[$maBm]['soChuaXong']++;
                        $statsTheoHocPhan[$maHp]['soChuaXong']++;
                    }
                }
            }

            if ($hdIsFullCompleted && $soSVHD > 0 && $soChuaXongHD === 0) {
                $soHoiDongHoanThanh++;
            } else {
                $soHoiDongChuaHoanThanh++;
            }

            $tyLeDatHD = ($soDatHD + $soKhongDatHD) > 0 ? round(($soDatHD / ($soDatHD + $soKhongDatHD)) * 100, 2) : 0.0;
            $diemTBHD = $soLuongDiemHD > 0 ? round($tongDiemHD / $soLuongDiemHD, 2) : 0.0;

            $danhSachHoiDong[] = [
                'hoiDong'        => $hd,
                'thanhViens'     => $thanhViens,
                'ct'             => $ct,
                'tk'             => $tk,
                'uvs'            => $uvs,
                'deTais'         => $dsDeTaiHD,
                'soDeTai'        => count($dsDeTaiHD),
                'soSV'           => $soSVHD,
                'soDat'          => $soDatHD,
                'soKhongDat'     => $soKhongDatHD,
                'soChuaXong'     => $soChuaXongHD,
                'tyLeDat'        => $tyLeDatHD,
                'diemTB'         => $diemTBHD,
                'isCongBo'       => ($hd->TrangThaiCongBo === 'Đã công bố'),
                'isHoanThanh'    => ($soSVHD > 0 && $soChuaXongHD === 0),
            ];

            $statsTheoHoiDong[] = [
                'maHoiDong'    => $hd->MaHoiDong,
                'tenHoiDong'   => $hd->TenHoiDong,
                'nganh'        => $hd->nganh?->TenNganh ?? 'Chung',
                'diaDiem'      => $hd->DiaDiem ?? 'HUIT',
                'chuTich'      => $ct->giangVien?->HoTen ?? 'Chưa rõ',
                'thuKy'        => $tk->giangVien?->HoTen ?? 'Chưa rõ',
                'soSV'         => $soSVHD,
                'daPhanCong'   => $soSVHD,
                'daHoanThanh'  => ($soSVHD - $soChuaXongHD),
                'chuaHoanThanh'=> $soChuaXongHD,
                'soDat'        => $soDatHD,
                'soKhongDat'   => $soKhongDatHD,
                'diemTB'       => $diemTBHD,
                'tyLeDat'      => $tyLeDatHD,
                'isHoanThanh'  => ($soSVHD > 0 && $soChuaXongHD === 0),
            ];
        }

        // 6. Tính toán 10 KPI tổng quan cốt lõi
        $tongSVThamGia = $tongSVDuDieuKien > 0 ? $tongSVDuDieuKien : $tongSVPhanCong;
        $tongSVChuaPhanCong = max(0, $tongSVThamGia - $tongSVPhanCong);
        $tongDeTai = $tongDeTaiHocKy > 0 ? $tongDeTaiHocKy : count($danhSachTatCaDeTai);
        $tongHoiDong = count($hoiDongs);

        $tyLeDatToanDot = ($tongSVDat + $tongSVChuaDat) > 0 ? round(($tongSVDat / ($tongSVDat + $tongSVChuaDat)) * 100, 2) : 0.0;
        $diemTBToanDot = !empty($allScoresValid) ? round(array_sum($allScoresValid) / count($allScoresValid), 2) : 0.0;
        $diemCaoNhat = !empty($allScoresValid) ? max($allScoresValid) : 0.0;
        $diemThapNhat = !empty($allScoresValid) ? min($allScoresValid) : 0.0;

        $kpi = [
            'tongSVThamGia'           => $tongSVThamGia,
            'tongDeTai'               => $tongDeTai,
            'tongHoiDong'             => $tongHoiDong,
            'tongSVDaPhanCongHD'      => $tongSVPhanCong,
            'tongSVChuaPhanCongHD'    => $tongSVChuaPhanCong,
            'tongSVHoanThanhCham'     => $tongSVHoanThanhCham,
            'tongSVChuaHoanThanhCham' => $tongSVChuaHoanThanhCham,
            'tongSVDat'               => $tongSVDat,
            'tongSVChuaDat'           => $tongSVChuaDat,
            'tongSVChuaCoKQ'          => $tongSVChuaCoKQ,
            'tyLeDat'                 => $tyLeDatToanDot,
            'diemTB'                  => $diemTBToanDot,
            'diemCaoNhat'             => $diemCaoNhat,
            'diemThapNhat'            => $diemThapNhat,
            'soHoiDongHoanThanh'      => $soHoiDongHoanThanh,
            'soHoiDongChuaHoanThanh'  => $soHoiDongChuaHoanThanh,
        ];

        // 7. Tạo Payload biểu đồ phân tích (Chart.js ready)
        // Biểu đồ 1: Tỷ lệ đạt / chưa đạt / chưa kết quả
        $chartTyLeDat = [
            'labels' => ['Đạt', 'Chưa đạt', 'Chưa có kết quả'],
            'data'   => [$tongSVDat, $tongSVChuaDat, $tongSVChuaCoKQ],
            'colors' => ['#10b981', '#ef4444', '#f59e0b'],
        ];

        // Biểu đồ 2: Số lượng sinh viên theo ngành
        $nganhLabels = [];
        $nganhSVCounts = [];
        $nganhDatCounts = [];
        $nganhChuaDatCounts = [];
        $nganhDiemTB = [];
        foreach ($statsTheoNganh as $stNg) {
            $nganhLabels[] = $stNg['tenNganh'];
            $nganhSVCounts[] = $stNg['soSV'];
            $nganhDatCounts[] = $stNg['soDat'];
            $nganhChuaDatCounts[] = $stNg['soKhongDat'];
            $nganhDiemTB[] = !empty($stNg['scores']) ? round(array_sum($stNg['scores']) / count($stNg['scores']), 2) : 0.0;
        }

        $chartSVTheoNganh = [
            'labels' => $nganhLabels,
            'tongSV' => $nganhSVCounts,
            'dat'    => $nganhDatCounts,
            'chuaDat'=> $nganhChuaDatCounts,
        ];

        // Biểu đồ 3: Điểm trung bình theo ngành
        $chartDiemTheoNganh = [
            'labels' => $nganhLabels,
            'diemTB' => $nganhDiemTB,
        ];

        // Biểu đồ 4: Thống kê theo hội đồng (Đã phân công vs Hoàn thành)
        $chartTheoHoiDong = [
            'labels'      => array_column($statsTheoHoiDong, 'tenHoiDong'),
            'daPhanCong'  => array_column($statsTheoHoiDong, 'daPhanCong'),
            'daHoanThanh' => array_column($statsTheoHoiDong, 'daHoanThanh'),
        ];

        // Biểu đồ 5: Phân bố điểm
        $chartPhanBoDiem = [
            'labels'  => ['Dưới 4.0 (F)', '4.0 - 4.9 (D)', '5.0 - 6.4 (D+, C)', '6.5 - 7.9 (C+, B)', '8.0 - 8.9 (B+)', '9.0 - 10.0 (A)'],
            'data'    => array_values($phanBoKhoangDiem),
            'chuA_F'  => $phanBoDiemChu,
        ];

        // Biểu đồ 6: Tình hình hoàn thành chấm điểm
        $chartTienDoChamDiem = [
            'labels' => ['Đã chấm đủ phiếu', 'Đang chấm / Chưa đủ phiếu'],
            'data'   => [$tongSVHoanThanhCham, $tongSVChuaHoanThanhCham],
            'colors' => ['#0072ce', '#f59e0b'],
        ];

        $charts = compact(
            'chartTyLeDat',
            'chartSVTheoNganh',
            'chartDiemTheoNganh',
            'chartTheoHoiDong',
            'chartPhanBoDiem',
            'chartTienDoChamDiem'
        );

        // 8. Chuẩn bị tập dữ liệu theo Biểu mẫu báo cáo được chọn
        $duLieuMauBaoCao = $this->buildDuLieuChoMauBaoCao($mauBaoCao, [
            'danhSachHoiDong'   => $danhSachHoiDong,
            'danhSachTatCaSV'   => $danhSachTatCaSV,
            'statsTheoNganh'    => $statsTheoNganh,
            'statsTheoBoMon'    => $statsTheoBoMon,
            'statsTheoHocPhan'  => $statsTheoHocPhan,
            'statsTheoHoiDong'  => $statsTheoHoiDong,
            'phanBoKhoangDiem'  => $phanBoKhoangDiem,
            'phanBoDiemChu'     => $phanBoDiemChu,
            'kpi'               => $kpi,
            'hocKy'             => $hocKy,
            'filters'           => $filters,
        ]);

        return [
            'hocKy'             => $hocKy,
            'filters'           => $filters,
            'kpi'               => $kpi,
            'charts'            => $charts,
            'mauBaoCao'         => $mauBaoCao,
            'hinhThuc'          => $hinhThuc,
            'duLieuMauBaoCao'   => $duLieuMauBaoCao,
            'danhSachHoiDong'   => $danhSachHoiDong,
            'danhSachTatCaSV'   => $danhSachTatCaSV,
            'statsTheoNganh'    => $statsTheoNganh,
            'statsTheoBoMon'    => $statsTheoBoMon,
            'statsTheoHocPhan'  => $statsTheoHocPhan,
            'statsTheoHoiDong'  => $statsTheoHoiDong,
            'thoiDiemXuat'      => self::getNow(),
            'hasChuaCongBo'     => $hasChuaCongBo,
            'canhBaoChuaHoanTat'=> $tongSVChuaCoKQ > 0 ? "Có {$tongSVChuaCoKQ} sinh viên chưa hoàn tất chấm điểm đủ các phiếu." : null,
        ];
    }

    /**
     * Tách nhỏ và định hình dữ liệu phù hợp với từng Mẫu báo cáo
     */
    private function buildDuLieuChoMauBaoCao(string $mau, array $context): array
    {
        $allSV = $context['danhSachTatCaSV'];

        return match ($mau) {
            'mau_02' => [
                'type'       => 'nganh',
                'nhomNganh'  => $context['statsTheoNganh'],
                'danhSachSV' => $allSV,
            ],
            'mau_03' => [
                'type'       => 'sv_dat',
                'danhSachSV' => array_values(array_filter($allSV, fn($sv) => $sv['isDuPhieu'] && $sv['isDat'])),
            ],
            'mau_04' => [
                'type'       => 'sv_chua_dat',
                'danhSachSV' => array_values(array_filter($allSV, fn($sv) => $sv['isDuPhieu'] && !$sv['isDat'])),
            ],
            'mau_05' => [
                'type'       => 'chua_co_kq',
                'danhSachSV' => array_values(array_filter($allSV, fn($sv) => !$sv['isDuPhieu'])),
            ],
            'mau_06' => [
                'type'       => 'hoi_dong',
                'hoiDongs'   => $context['danhSachHoiDong'],
            ],
            'mau_07' => [
                'type'       => 'phan_bo_diem',
                'khoangDiem' => $context['phanBoKhoangDiem'],
                'diemChu'    => $context['phanBoDiemChu'],
                'danhSachSV' => $allSV,
            ],
            'mau_08' => [
                'type'       => 'to_chuc',
                'hoiDongs'   => $context['danhSachHoiDong'],
                'nhomNganh'  => $context['statsTheoNganh'],
            ],
            'mau_09' => [
                'type'       => 'bo_mon',
                'nhomBoMon'  => $context['statsTheoBoMon'],
                'danhSachSV' => $allSV,
            ],
            'mau_10' => [
                'type'       => 'hoc_phan',
                'nhomHocPhan'=> $context['statsTheoHocPhan'],
                'danhSachSV' => $allSV,
            ],
            default => [
                'type'       => 'dot',
                'hoiDongs'   => $context['danhSachHoiDong'],
                'danhSachSV' => $allSV,
            ],
        };
    }

    /**
     * Backward-compatibility wrapper methods for existing calls
     */
    public function thongKeTheoDot(array $filters, array $scope = []): array
    {
        $res = $this->thongKeTongHop(array_merge($filters, ['mau_bao_cao' => 'mau_01']), $scope);
        return [
            'hocKy'              => $res['hocKy'],
            'filters'            => $filters,
            'danhSachHoiDong'    => $res['danhSachHoiDong'],
            'tongSoHoiDong'      => $res['kpi']['tongHoiDong'],
            'tongSoDeTai'        => $res['kpi']['tongDeTai'],
            'tongSoSV'           => $res['kpi']['tongSVDaPhanCongHD'],
            'tongDat'            => $res['kpi']['tongSVDat'],
            'tongKhongDat'       => $res['kpi']['tongSVChuaDat'],
            'tongChuaXong'       => $res['kpi']['tongSVChuaCoKQ'],
            'tyLeDat'            => $res['kpi']['tyLeDat'],
            'diemTB'             => $res['kpi']['diemTB'],
            'hasChuaCongBo'      => $res['hasChuaCongBo'],
            'thoiDiemXuat'       => $res['thoiDiemXuat'],
            'canhBaoChuaHoanTat' => $res['canhBaoChuaHoanTat'],
        ];
    }

    public function getDuLieuBaoCaoNganh(array $filters, array $scope = []): array
    {
        $res = $this->thongKeTongHop(array_merge($filters, ['mau_bao_cao' => 'mau_02']), $scope);
        return [
            'hocKy'             => $res['hocKy'],
            'filters'           => $filters,
            'danhSachNganh'     => $res['statsTheoNganh'],
            'tongSoNganh'       => count($res['statsTheoNganh']),
            'tongSoHoiDong'     => $res['kpi']['tongHoiDong'],
            'tongSoDeTai'       => $res['kpi']['tongDeTai'],
            'tongSoSV'          => $res['kpi']['tongSVDaPhanCongHD'],
            'tongDat'           => $res['kpi']['tongSVDat'],
            'tongKhongDat'      => $res['kpi']['tongSVChuaDat'],
            'tongChuaXong'      => $res['kpi']['tongSVChuaCoKQ'],
            'tyLeDat'           => $res['kpi']['tyLeDat'],
            'diemTB'            => $res['kpi']['diemTB'],
            'diemCaoNhat'       => $res['kpi']['diemCaoNhat'],
            'diemThapNhat'      => $res['kpi']['diemThapNhat'],
            'phanBoToanBo'      => $res['charts']['chartPhanBoDiem']['chuA_F'],
            'thoiDiemXuat'      => $res['thoiDiemXuat'],
            'hasChuaCongBo'     => $res['hasChuaCongBo'],
            'canhBaoChuaHoanTat'=> $res['canhBaoChuaHoanTat'],
        ];
    }

    public function getDuLieuBaoCaoTrangThai(array $filters, array $scope = []): array
    {
        $res = $this->thongKeTongHop(array_merge($filters, ['mau_bao_cao' => 'mau_03']), $scope);
        return [
            'hocKy'             => $res['hocKy'],
            'filters'           => $filters,
            'danhSachNganh'     => $res['statsTheoNganh'],
            'tongSoNganh'       => count($res['statsTheoNganh']),
            'tongSoHoiDong'     => $res['kpi']['tongHoiDong'],
            'tongSoDeTai'       => $res['kpi']['tongDeTai'],
            'tongSoSV'          => $res['kpi']['tongSVDaPhanCongHD'],
            'tongDat'           => $res['kpi']['tongSVDat'],
            'tongKhongDat'      => $res['kpi']['tongSVChuaDat'],
            'tongChuaXong'      => $res['kpi']['tongSVChuaCoKQ'],
            'tyLeDat'           => $res['kpi']['tyLeDat'],
            'thoiDiemXuat'      => $res['thoiDiemXuat'],
            'hasChuaCongBo'     => $res['hasChuaCongBo'],
            'canhBaoChuaHoanTat'=> $res['canhBaoChuaHoanTat'],
        ];
    }

    public function demSoLuongXuat(array $filters, array $scope = []): array
    {
        $res = $this->thongKeTongHop($filters, $scope);
        return [
            'soHoiDong' => count($res['danhSachHoiDong']),
            'soDeTai'   => count(array_unique(array_column($res['danhSachTatCaSV'], 'maDeTai'))),
            'soSV'      => count($res['danhSachTatCaSV']),
        ];
    }

    /**
     * Chuẩn hoá hiển thị tên giảng viên (hỗ trợ Model, Object, Array, Collection, JSON string, string thô)
     * Trả về tên kèm học hàm/học vị nếu có (ví dụ: "TS. Nguyễn Văn A"), hoặc danh sách tên ngăn cách bởi dấu phẩy.
     */
    public static function formatLecturerName(mixed $lecturer): string
    {
        if (is_null($lecturer)) {
            return '—';
        }

        // Nếu là string JSON: decode thử
        if (is_string($lecturer)) {
            $trimmed = trim($lecturer);
            if ($trimmed === '' || $trimmed === 'null' || $trimmed === 'undefined' || $trimmed === '[]' || $trimmed === '{}') {
                return '—';
            }
            if ((str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) ||
                (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']'))) {
                $decoded = json_decode($trimmed, true);
                if (json_last_error() === JSON_ERROR_NONE && !is_null($decoded)) {
                    return self::formatLecturerName($decoded);
                }
            }
            return $trimmed;
        }

        // Nếu là Collection hoặc Array
        if ($lecturer instanceof \Illuminate\Support\Collection || is_array($lecturer)) {
            $items = is_array($lecturer) ? $lecturer : $lecturer->all();
            if (empty($items)) {
                return '—';
            }

            // Kiểm tra nếu là associative array đại diện cho 1 giảng viên
            if (isset($items['HoTen']) || isset($items['hoTen']) || isset($items['fullName']) || isset($items['name']) || isset($items['MaGV'])) {
                return self::extractSingleLecturerName($items);
            }

            // Nếu là danh sách nhiều giảng viên
            $names = [];
            foreach ($items as $item) {
                $formatted = self::formatLecturerName($item);
                if ($formatted !== '—' && !in_array($formatted, $names)) {
                    $names[] = $formatted;
                }
            }
            return !empty($names) ? implode(', ', $names) : '—';
        }

        // Nếu là Model hoặc Object
        if (is_object($lecturer)) {
            return self::extractSingleLecturerName($lecturer);
        }

        return (string)$lecturer;
    }

    /**
     * Trích xuất tên của một giảng viên từ Object hoặc Array
     */
    private static function extractSingleLecturerName(object|array $item): string
    {
        if (is_object($item)) {
            if (isset($item->danh_xung_ho_ten) && !empty($item->danh_xung_ho_ten)) {
                return trim($item->danh_xung_ho_ten);
            }
            if (isset($item->HoTen) && !empty($item->HoTen)) {
                $prefix = self::buildAcademicPrefix($item->HocHam ?? null, $item->HocVi ?? null);
                return trim($prefix . ' ' . $item->HoTen);
            }
            if (isset($item->hoTen) && !empty($item->hoTen)) {
                return trim($item->hoTen);
            }
            if (isset($item->fullName) && !empty($item->fullName)) {
                return trim($item->fullName);
            }
            if (isset($item->name) && !empty($item->name)) {
                return trim($item->name);
            }
            if (isset($item->TenGV) && !empty($item->TenGV)) {
                return trim($item->TenGV);
            }
            if (isset($item->MaGV) && !empty($item->MaGV)) {
                return trim($item->MaGV);
            }
        } elseif (is_array($item)) {
            if (!empty($item['danh_xung_ho_ten'])) {
                return trim($item['danh_xung_ho_ten']);
            }
            if (!empty($item['HoTen'])) {
                $prefix = self::buildAcademicPrefix($item['HocHam'] ?? null, $item['HocVi'] ?? null);
                return trim($prefix . ' ' . $item['HoTen']);
            }
            if (!empty($item['hoTen'])) {
                return trim($item['hoTen']);
            }
            if (!empty($item['fullName'])) {
                return trim($item['fullName']);
            }
            if (!empty($item['name'])) {
                return trim($item['name']);
            }
            if (!empty($item['TenGV'])) {
                return trim($item['TenGV']);
            }
            if (!empty($item['MaGV'])) {
                return trim($item['MaGV']);
            }
        }

        return '—';
    }

    /**
     * Tạo tiền tố học hàm / học vị chuẩn
     */
    private static function buildAcademicPrefix(?string $hh, ?string $hv): string
    {
        $hh = trim((string)$hh);
        $hv = trim((string)$hv);
        $prefix = '';

        if (mb_stripos($hh, 'giáo sư') !== false && mb_stripos($hh, 'phó') === false) {
            $prefix = 'GS.';
        } elseif (mb_stripos($hh, 'phó giáo sư') !== false || mb_stripos($hh, 'pgs') !== false) {
            $prefix = 'PGS.';
        }

        if (mb_stripos($hv, 'tiến sĩ') !== false || mb_stripos($hv, 'ts') !== false || mb_stripos($hv, 'tskh') !== false) {
            $prefix .= ($prefix ? ' ' : '') . 'TS.';
        } elseif (mb_stripos($hv, 'thạc sĩ') !== false || mb_stripos($hv, 'ths') !== false) {
            $prefix .= ($prefix ? ' ' : '') . 'ThS.';
        }

        return $prefix;
    }
}
