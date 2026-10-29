<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhieuChamDiem extends Model
{
    use HasFactory;

    protected $table = 'PhieuChamDiem';
    protected $primaryKey = 'MaPhieuChamDiem';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaPhieuChamDiem',
        'Diem',
        'LoaiKhoaLuan',
        'NgayCham',
        'NhanXet',
        'ChiTietDiem',
        'MaDeTai',
        'MaHoiDong',
        'MaGV',
        'MaSV',
        'MaHoSo',
        'MaHocKy',
        'TrangThai',
        'DiemKhuyenKhich',
        'NgayXacNhan',
        'GhiChuMoKhoa',
        'ChuKy',
        'NgayKy',
    ];

    /**
     * Cấu hình trần điểm tập trung cho phần chức năng theo bài toán:
     * - KLCN (Cử nhân): 3.50đ (Cộng 0.5đ chức năng cơ bản = 4.00đ cho TC7)
     * - KLKS (Kỹ sư): 4.00đ (Toàn bộ phần chức năng ứng dụng TC7)
     */
    public const MAX_DIEM_CHUC_NANG = [
        'KLCN' => 3.50,
        'KLKS' => 4.00,
    ];

    /**
     * Lấy trần điểm tối đa cho phần yêu cầu chức năng bài toán theo loại khóa luận
     */
    public static function getMaxDiemChucNang(string $loaiKhoaLuan = 'KLCN'): float
    {
        return self::MAX_DIEM_CHUC_NANG[$loaiKhoaLuan] ?? 3.50;
    }

    /**
     * Tự động chia đều điểm trần cho các yêu cầu chức năng, phần dư phân bổ theo từng cent (0.01đ)
     * vào các yêu cầu đầu tiên để tổng chính xác tuyệt đối bằng điểm trần.
     */
    public static function distributeScores(float $target, int $count): array
    {
        if ($count <= 0) {
            return [];
        }
        $targetCents = (int) round($target * 100);
        $baseCents = intdiv($targetCents, $count);
        $remainder = $targetCents % $count;

        $scores = [];
        for ($i = 0; $i < $count; $i++) {
            $cents = $baseCents + ($i < $remainder ? 1 : 0);
            $scores[] = round($cents / 100, 2);
        }
        return $scores;
    }

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'Diem' => 'float',
            'DiemKhuyenKhich' => 'float',
            'NgayCham' => 'date',
            'NgayXacNhan' => 'datetime',
            'NgayKy' => 'datetime',
            'ChiTietDiem' => 'array',
        ];
    }

    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function hoiDong()
    {
        return $this->belongsTo(HoiDong::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class, 'MaSV', 'MaSV');
    }

    public function hoSoBaoVe()
    {
        return $this->belongsTo(HoSoBaoVe::class, 'MaHoSo', 'MaHoSo');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }

    /**
     * Bảng Tiêu chí chấm điểm Khóa luận Cử nhân (KLCN - Hướng ứng dụng)
     * Trích xuất nguyên bản từ biểu mẫu chính thức Khoa CNTT - ĐH Công Thương TP.HCM
     */
    public static function getRubricKLCN(?string $customChucNang = null): array
    {
        $chucNangContent = !empty(trim($customChucNang ?? ''))
            ? trim($customChucNang)
            : "  + Chức năng 01:\n    * Yêu cầu 1\n    * Yêu cầu 2\n    * …\n  + Chức năng 02:\n    * Yêu cầu 1\n    * Yêu cầu 2\n    * …\n  + Chức năng 03:\n    * Yêu cầu 1\n    * Yêu cầu 2\n    * …";

        return [
            [
                'id' => 'tc1',
                'stt' => 1,
                'noi_dung' => "Khảo sát nghiệp vụ, mô hình hóa nghiệp vụ\n- Xác định lý do, mục tiêu đề tài\n- Khảo sát hiện trạng: Cơ cấu tổ chức, Qui trình, biểu mẫu",
                'clo' => 'CLO1.1',
                'diem_toi_da_lines' => ['0.25', '0.5'],
                'max' => 0.75,
            ],
            [
                'id' => 'tc2',
                'stt' => 2,
                'noi_dung' => "Lập kế hoạch và phân công thực hiện công việc",
                'clo' => 'CLO6',
                'diem_toi_da_lines' => ['0.25'],
                'max' => 0.25,
            ],
            [
                'id' => 'tc3',
                'stt' => 3,
                'noi_dung' => "Phân tích hệ thống\n- Lập sơ đồ Use-Case nghiệp vụ và đặc tả.\n- Lập sơ đồ Use-Case hệ thống và đặc tả.\n- Sơ đồ lớp phân tích",
                'clo' => 'CLO1.2',
                'diem_toi_da_lines' => ['0.25', '0.25', '0.25'],
                'max' => 0.75,
            ],
            [
                'id' => 'tc4',
                'stt' => 4,
                'noi_dung' => "Thiết kế hệ thống\n- Sơ đồ lớp thiết kế\n- Mô hình dữ liệu",
                'clo' => 'CLO2.1',
                'diem_toi_da_lines' => ['0.25', '0.25'],
                'max' => 0.50,
            ],
            [
                'id' => 'tc5',
                'stt' => 5,
                'noi_dung' => "Thiết kế giao diện",
                'clo' => 'CLO2.2',
                'diem_toi_da_lines' => ['0.5'],
                'max' => 0.50,
            ],
            [
                'id' => 'tc6',
                'stt' => 6,
                'noi_dung' => "Xây dựng API hệ thống\n- Phân tích các chức năng trên các nền tảng",
                'clo' => 'CLO1.1',
                'diem_toi_da_lines' => ['0.25', '0.25'],
                'max' => 0.50,
            ],
            [
                'id' => 'tc7',
                'stt' => 7,
                'noi_dung' => "Xây dựng các chức năng cơ bản của ứng dụng: đăng nhập, đăng xuất, backup & restore,…\n- Cài đặt chức năng theo yêu cầu của bài toán\n" . $chucNangContent,
                'clo' => 'CLO3',
                'diem_toi_da_lines' => ['0.5', '3.5', '(chia nhỏ thanh điểm, tối đa 0.5 điểm)'],
                'max' => 4.00,
            ],
            [
                'id' => 'tc8',
                'stt' => 8,
                'noi_dung' => "Kiểm thử và triển khai hệ thống",
                'clo' => 'CLO4',
                'diem_toi_da_lines' => ['0.75'],
                'max' => 0.75,
            ],
            [
                'id' => 'tc9',
                'stt' => 9,
                'noi_dung' => "Nội dung kiến thức trình bày trong quyển báo cáo",
                'clo' => 'CLO5.1',
                'diem_toi_da_lines' => ['0.5'],
                'max' => 0.50,
            ],
            [
                'id' => 'tc10',
                'stt' => 10,
                'noi_dung' => "Hình thức, định dạng quyển báo cáo",
                'clo' => 'CLO5.1',
                'diem_toi_da_lines' => ['0.5'],
                'max' => 0.50,
            ],
            [
                'id' => 'tc11',
                'stt' => 11,
                'noi_dung' => "Thái độ, tác phong làm việc",
                'clo' => 'CLO6',
                'diem_toi_da_lines' => ['0.5'],
                'max' => 0.50,
            ],
            [
                'id' => 'tc12',
                'stt' => 12,
                'noi_dung' => "Phong cách báo cáo, Slide.",
                'clo' => 'CLO5.2',
                'diem_toi_da_lines' => ['0.5'],
                'max' => 0.50,
            ],
            [
                'id' => 'tc13',
                'stt' => 13,
                'noi_dung' => "Cộng điểm khuyến khích cho nhóm sinh viên tham gia thực hiện NCKH liên quan nội dung đề tài (tổng điểm chung không vượt quá 10 điểm)\n- Thi SV NCKH đạt giải cấp Khoa hoặc có bài báo đăng hội thảo cấp Khoa.\n- Thi SV NCKH đạt giải cấp Trường hoặc có bài báo đăng tạp chí khoa học cấp Trường trở lên",
                'clo' => 'CLO3',
                'diem_toi_da_lines' => ['1.0', '0.5', '1.0'],
                'max' => 1.00,
                'is_bonus' => true,
            ],
        ];
    }

    /**
     * Bảng Tiêu chí chấm điểm Khóa luận Kỹ sư (KLKS - Hướng ứng dụng)
     * Trích xuất nguyên bản từ biểu mẫu chính thức Khoa CNTT - ĐH Công Thương TP.HCM
     */
    public static function getRubricKLKS(?string $customChucNang = null): array
    {
        $chucNangContent = !empty(trim($customChucNang ?? ''))
            ? trim($customChucNang)
            : "- Xây dựng các chức năng cơ bản của ứng dụng:\n  + Đăng nhập, đăng xuất, đổi mật khẩu,\n  + Phân quyền ứng dụng (...)\n- Cài đặt chức năng nghiệp vụ trên nền tảng Web:\n  + Chức năng 1...\n  + Chức năng 2...\n- Cài đặt chức năng nghiệp vụ trên nền tảng Mobile:\n  + Chức năng 1...\n  + Chức năng 2...";

        return [
            [
                'id' => 'tc1',
                'stt' => 1,
                'noi_dung' => "Khảo sát nghiệp vụ, mô hình hóa nghiệp vụ\n- Xác định lý do, mục tiêu đề tài\n- Khảo sát hiện trạng: Cơ cấu tổ chức, Qui trình, biểu mẫu",
                'clo' => 'CLO1.1',
                'diem_toi_da_lines' => ['0.25', '0.25'],
                'max' => 0.50,
            ],
            [
                'id' => 'tc2',
                'stt' => 2,
                'noi_dung' => "Lập kế hoạch và phân công thực hiện công việc",
                'clo' => 'CLO6',
                'diem_toi_da_lines' => ['0.25'],
                'max' => 0.25,
            ],
            [
                'id' => 'tc3',
                'stt' => 3,
                'noi_dung' => "Phân tích hệ thống\n- Lập sơ đồ Use-Case nghiệp vụ và đặc tả.\n- Lập sơ đồ Use-Case hệ thống và đặc tả.\n- Sơ đồ lớp phân tích",
                'clo' => 'CLO1.2',
                'diem_toi_da_lines' => ['0.25', '0.25', '0.25'],
                'max' => 0.75,
            ],
            [
                'id' => 'tc4',
                'stt' => 4,
                'noi_dung' => "Thiết kế hệ thống\n- Sơ đồ lớp thiết kế\n- Mô hình dữ liệu",
                'clo' => 'CLO2.1',
                'diem_toi_da_lines' => ['0.25', '0.25'],
                'max' => 0.50,
            ],
            [
                'id' => 'tc5',
                'stt' => 5,
                'noi_dung' => "Thiết kế giao diện\n- Giao diện nền tảng 1\n- Giao diện nền tảng 2",
                'clo' => 'CLO2.2',
                'diem_toi_da_lines' => ['0.5', '0.5'],
                'max' => 1.00,
            ],
            [
                'id' => 'tc6',
                'stt' => 6,
                'noi_dung' => "Xây dựng API hệ thống\n- Phân tích các chức năng trên các nền tảng",
                'clo' => 'CLO1.1',
                'diem_toi_da_lines' => ['0.25', '0.25'],
                'max' => 0.50,
            ],
            [
                'id' => 'tc7',
                'stt' => 7,
                'noi_dung' => "Xây dựng ứng dụng:\n" . $chucNangContent,
                'clo' => 'CLO3',
                'diem_toi_da_lines' => ['4.0', '(chia nhỏ thanh điểm, tối đa 0.5 điểm)'],
                'max' => 4.00,
            ],
            [
                'id' => 'tc8',
                'stt' => 8,
                'noi_dung' => "Kiểm thử và triển khai hệ thống",
                'clo' => 'CLO4',
                'diem_toi_da_lines' => ['0.25'],
                'max' => 0.25,
            ],
            [
                'id' => 'tc9',
                'stt' => 9,
                'noi_dung' => "Ứng dụng thuật toán hoặc trí tuệ nhân tạo (AI) nhằm nâng cao hiệu quả của ứng dụng\n- ...",
                'clo' => 'CLO3',
                'diem_toi_da_lines' => ['1.25'],
                'max' => 1.25,
            ],
            [
                'id' => 'tc10',
                'stt' => 10,
                'noi_dung' => "Nội dung kiến thức trình bày trong quyển báo cáo",
                'clo' => 'CLO5.1',
                'diem_toi_da_lines' => ['0.25'],
                'max' => 0.25,
            ],
            [
                'id' => 'tc11',
                'stt' => 11,
                'noi_dung' => "Hình thức, định dạng quyển báo cáo",
                'clo' => 'CLO5.1',
                'diem_toi_da_lines' => ['0.25'],
                'max' => 0.25,
            ],
            [
                'id' => 'tc12',
                'stt' => 12,
                'noi_dung' => "Thái độ, tác phong làm việc",
                'clo' => 'CLO6',
                'diem_toi_da_lines' => ['0.25'],
                'max' => 0.25,
            ],
            [
                'id' => 'tc13',
                'stt' => 13,
                'noi_dung' => "Phong cách báo cáo, Slide.",
                'clo' => 'CLO5.2',
                'diem_toi_da_lines' => ['0.25'],
                'max' => 0.25,
            ],
            [
                'id' => 'tc14',
                'stt' => 14,
                'noi_dung' => "Cộng điểm khuyến khích cho nhóm sinh viên tham gia thực hiện NCKH liên quan nội dung đề tài (tổng điểm chung không vượt quá 10 điểm)\n- Thi SV NCKH đạt giải cấp Khoa hoặc có bài báo đăng hội thảo cấp Khoa.\n- Thi SV NCKH đạt giải cấp Trường hoặc có bài báo đăng tạp chí khoa học cấp Trường trở lên",
                'clo' => 'CLO3',
                'diem_toi_da_lines' => ['1.0', '0.5', '1.0'],
                'max' => 1.00,
                'is_bonus' => true,
            ],
        ];
    }

    /**
     * Phân tích danh sách các yêu cầu chức năng từ văn bản do Thư ký nhập hoặc từ rubric chuẩn,
     * tự động chia đều điểm trần theo quy định đào tạo (KLCN <= 3.50đ, KLKS <= 4.00đ).
     * Hỗ trợ nhận diện thông minh:
     * - Dòng không thụt lề = Nhóm chức năng (hoặc bắt đầu bằng Chức năng, CN, số La Mã, 1., I.)
     * - Dòng có thụt lề (Tab hoặc 2+ space) = Yêu cầu con (hoặc bắt đầu bằng -, *, •, a), 1.1)
     * - Nhóm không có yêu cầu con thì tự coi nhóm đó là một yêu cầu (không báo lỗi).
     */
    public static function parseFunctionalRequirements(?string $customText, string $loaiKhoaLuan = 'KLCN'): array
    {
        $requirements = [];
        $isKLKS = ($loaiKhoaLuan === 'KLKS');
        $targetScore = self::getMaxDiemChucNang($loaiKhoaLuan);

        if (!empty(trim($customText ?? ''))) {
            $lines = explode("\n", trim($customText));
            $groups = [];
            $currentGroupKey = null;

            foreach ($lines as $rawLine) {
                $trimmed = trim($rawLine);
                if ($trimmed === '') continue;

                // Kiểm tra thụt lề
                $isIndented = (bool) preg_match('/^(\t|\s{2,})/', $rawLine);
                $isExplicitGroup = (bool) preg_match('/^(Chức năng|CN|Module|Phần|\d+\.|\b[IVXLCDM]+\.)/iu', $trimmed);
                $isExplicitSub = (bool) preg_match('/^([\*\-\•\+]|[a-z]\)|\d+\.\d+)/iu', $trimmed);

                $isGroup = false;
                if ($isExplicitSub) {
                    $isGroup = false;
                } elseif ($isExplicitGroup) {
                    $isGroup = true;
                } elseif ($isIndented) {
                    $isGroup = false;
                } else {
                    // Dòng không thụt lề và không có ký hiệu con -> Là nhóm
                    $isGroup = true;
                }

                if ($isGroup) {
                    $cleanName = trim(preg_replace('/^[\+\-\*\•\#\s]+/', '', $trimmed));
                    $groups[] = [
                        'name'  => $cleanName,
                        'items' => [],
                    ];
                    $currentGroupKey = array_key_last($groups);
                } else {
                    $cleanItem = trim(preg_replace('/^([\*\-\•\+\#\s]+|[a-z]\)\s*|\d+\.\d+\s*|Yêu cầu\s*\d*:?\s*)/iu', '', $trimmed));
                    $cleanItem = trim(preg_replace('/\s*[\(\[]\s*[\d\.\,]+\s*(?:đ|điểm|pts?)\s*[\)\]]$/iu', '', $cleanItem));

                    if ($cleanItem !== '') {
                        if ($currentGroupKey === null) {
                            $defaultName = $isKLKS ? 'Chức năng nghiệp vụ' : 'Chức năng bài toán';
                            $groups[] = [
                                'name'  => $defaultName,
                                'items' => [],
                            ];
                            $currentGroupKey = array_key_last($groups);
                        }
                        $groups[$currentGroupKey]['items'][] = $cleanItem;
                    }
                }
            }

            // Nhóm không có yêu cầu con thì tự coi nhóm đó là một yêu cầu
            $parsedItems = [];
            foreach ($groups as $g) {
                $grpName = $g['name'];
                if (empty($g['items'])) {
                    $parsedItems[] = [
                        'group'     => $isKLKS ? 'Chức năng nghiệp vụ' : 'Chức năng bài toán',
                        'label'     => $grpName,
                        'is_co_ban' => (stripos($grpName, 'cơ bản') !== false),
                    ];
                } else {
                    foreach ($g['items'] as $label) {
                        $isCoBan = (stripos($grpName, 'cơ bản') !== false || (stripos($label, 'cơ bản') !== false && stripos($label, 'đăng nhập') !== false));
                        $parsedItems[] = [
                            'group'     => $grpName,
                            'label'     => $label,
                            'is_co_ban' => $isCoBan,
                        ];
                    }
                }
            }

            if (!empty($parsedItems)) {
                if ($isKLKS) {
                    // KLKS: Tổng toàn bộ yêu cầu = 4.00đ
                    $scores = self::distributeScores($targetScore, count($parsedItems));
                    $reqIndex = 1;
                    foreach ($parsedItems as $idx => $item) {
                        $requirements[] = [
                            'id'        => 'tc7_yc_' . $reqIndex,
                            'group'     => $item['group'],
                            'label'     => $item['label'],
                            'max'       => $scores[$idx] ?? 0.50,
                            'is_co_ban' => $item['is_co_ban'],
                        ];
                        $reqIndex++;
                    }
                } else {
                    // KLCN: Yêu cầu bài toán = 3.50đ, Chức năng cơ bản = 0.50đ (Tổng TC7 = 4.00đ)
                    $coBanItem = null;
                    $problemItems = [];

                    foreach ($parsedItems as $item) {
                        if ($item['is_co_ban'] && !$coBanItem) {
                            $coBanItem = $item;
                        } else {
                            $problemItems[] = $item;
                        }
                    }

                    // Đảm bảo luôn có 1 mục chức năng cơ bản chuẩn 0.50đ
                    $requirements[] = [
                        'id'        => 'tc7_co_ban',
                        'group'     => 'Chức năng cơ bản',
                        'label'     => $coBanItem ? $coBanItem['label'] : 'Đăng nhập, đăng xuất, phân quyền, backup & restore,...',
                        'max'       => 0.50,
                        'is_co_ban' => true,
                    ];

                    $scores = self::distributeScores($targetScore, count($problemItems));
                    $reqIndex = 1;
                    foreach ($problemItems as $idx => $item) {
                        $requirements[] = [
                            'id'        => 'tc7_yc_' . $reqIndex,
                            'group'     => $item['group'],
                            'label'     => $item['label'],
                            'max'       => $scores[$idx] ?? 0.50,
                            'is_co_ban' => false,
                        ];
                        $reqIndex++;
                    }
                }
            }
        }

        // Nếu chưa có hoặc không parse được dòng yêu cầu nào, dùng danh sách chuẩn của Khoa
        if (empty($requirements)) {
            if ($isKLKS) {
                $requirements = [
                    ['id' => 'tc7_co_ban',  'group' => 'Chức năng cơ bản', 'label' => 'Đăng nhập, đăng xuất, đổi mật khẩu, phân quyền', 'max' => 0.5, 'is_co_ban' => true],
                    ['id' => 'tc7_web_1',   'group' => 'Nền tảng Web',       'label' => 'Chức năng nghiệp vụ Web 1', 'max' => 0.5, 'is_co_ban' => false],
                    ['id' => 'tc7_web_2',   'group' => 'Nền tảng Web',       'label' => 'Chức năng nghiệp vụ Web 2', 'max' => 0.5, 'is_co_ban' => false],
                    ['id' => 'tc7_web_3',   'group' => 'Nền tảng Web',       'label' => 'Chức năng nghiệp vụ Web 3', 'max' => 0.5, 'is_co_ban' => false],
                    ['id' => 'tc7_mob_1',   'group' => 'Nền tảng Mobile',    'label' => 'Chức năng nghiệp vụ Mobile 1', 'max' => 0.5, 'is_co_ban' => false],
                    ['id' => 'tc7_mob_2',   'group' => 'Nền tảng Mobile',    'label' => 'Chức năng nghiệp vụ Mobile 2', 'max' => 0.5, 'is_co_ban' => false],
                    ['id' => 'tc7_mob_3',   'group' => 'Nền tảng Mobile',    'label' => 'Chức năng nghiệp vụ Mobile 3', 'max' => 0.5, 'is_co_ban' => false],
                    ['id' => 'tc7_mob_4',   'group' => 'Nền tảng Mobile',    'label' => 'Đồng bộ dữ liệu đa nền tảng', 'max' => 0.5, 'is_co_ban' => false],
                ];
            } else {
                $requirements = [
                    ['id' => 'tc7_co_ban', 'group' => 'Chức năng cơ bản', 'label' => 'Đăng nhập, đăng xuất, backup & restore,...', 'max' => 0.5, 'is_co_ban' => true],
                    ['id' => 'tc7_yc_1',   'group' => 'Chức năng 01',       'label' => 'Yêu cầu 1: Tiếp nhận và xử lý nghiệp vụ chính', 'max' => 0.5, 'is_co_ban' => false],
                    ['id' => 'tc7_yc_2',   'group' => 'Chức năng 01',       'label' => 'Yêu cầu 2: Kiểm tra dữ liệu và tích hợp hệ thống', 'max' => 0.5, 'is_co_ban' => false],
                    ['id' => 'tc7_yc_3',   'group' => 'Chức năng 02',       'label' => 'Yêu cầu 1: Quản lý danh mục và nghiệp vụ phụ trợ', 'max' => 0.5, 'is_co_ban' => false],
                    ['id' => 'tc7_yc_4',   'group' => 'Chức năng 02',       'label' => 'Yêu cầu 2: Báo cáo thống kê và xuất dữ liệu', 'max' => 0.5, 'is_co_ban' => false],
                    ['id' => 'tc7_yc_5',   'group' => 'Chức năng 03',       'label' => 'Yêu cầu 1: Phân quyền và quản lý tài khoản người dùng', 'max' => 0.5, 'is_co_ban' => false],
                    ['id' => 'tc7_yc_6',   'group' => 'Chức năng 03',       'label' => 'Yêu cầu 2: Thông báo và nhật ký thao tác hệ thống', 'max' => 0.5, 'is_co_ban' => false],
                    ['id' => 'tc7_yc_7',   'group' => 'Chức năng 03',       'label' => 'Yêu cầu 3: Tối ưu hiệu năng và bảo mật ứng dụng', 'max' => 0.5, 'is_co_ban' => false],
                ];
            }
        }

        return $requirements;
    }
}
