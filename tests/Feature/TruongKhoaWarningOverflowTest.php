<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\Khoa;
use App\Models\HocKy;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TruongKhoaWarningOverflowTest extends TestCase
{
    use DatabaseTransactions;

    protected $userTruongKhoa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userTruongKhoa = TaiKhoan::where('TenDangNhap', 'like', 'TK_CNTT_%')->first()
            ?? TaiKhoan::where('MaVaiTro', 'VT05')->first();
    }

    public function test_canh_bao_1_de_tai_tach_the_rieng_chong_tran_va_khong_dung_monospace()
    {
        $itemW = [
            'ma_ho_so'           => 'HSBV_TEST01',
            'ten_de_tai'         => 'Ứng dụng phân tích dữ liệu mạng xã hội cảnh báo sớm thông tin sai lệch bằng Deep Learning',
            'ten_nhom'           => 'Nhóm Test 04 - Social FakeNews',
            'ten_hoi_dong'       => 'Hội đồng 01 — Ngành Khoa học Dữ liệu',
            'ma_hoi_dong'        => 'HD01',
            'gv_chua_hoan_thanh' => [
                [
                    'MaGV'      => 'GV001',
                    'HoTen'     => 'ThS. Nguyễn Hoàng Anh',
                    'VaiTro'    => 'Ủy viên',
                    'TrangThai' => 'Lưu nháp / Chưa ký',
                ],
                [
                    'MaGV'      => 'GV002',
                    'HoTen'     => 'TS. Lê Hoàng Cường',
                    'VaiTro'    => 'Phản biện',
                    'TrangThai' => 'Chưa chấm',
                ],
            ],
            'ly_do'              => 'Còn 2 giám khảo chưa hoàn thành nộp phiếu',
        ];

        $rendered = view('truongkhoa.ketqua.partials._warning_topic_card', ['itemW' => $itemW])->render();

        // 1. Kiểm tra Dòng 1: Tên đề tài, cho phép xuống dòng, chống tràn
        $this->assertStringContainsString('Ứng dụng phân tích dữ liệu mạng xã hội', $rendered);
        $this->assertStringContainsString('overflow-wrap: anywhere', $rendered);
        $this->assertStringContainsString('word-break: break-word', $rendered);
        $this->assertStringContainsString('white-space: normal', $rendered);

        // 2. Kiểm tra Dòng 2: Nhóm & Hội đồng
        $this->assertStringContainsString('Nhóm Test 04 - Social FakeNews', $rendered);
        $this->assertStringContainsString('Hội đồng 01 — Ngành Khoa học Dữ liệu', $rendered);

        // 3. Kiểm tra Dòng 3: Danh sách giám khảo chưa nộp phiếu tách thành badge riêng
        $this->assertStringContainsString('Còn 2 giám khảo chưa nộp phiếu:', $rendered);
        $this->assertStringContainsString('ThS. Nguyễn Hoàng Anh (Lưu nháp)', $rendered);
        $this->assertStringContainsString('TS. Lê Hoàng Cường (Chưa chấm)', $rendered);
        $this->assertStringContainsString('gap: 6px', $rendered);
    }

    public function test_canh_bao_chuoi_dai_khong_dau_cach_chong_tran_thanh_cong()
    {
        $nonSpacedString = 'GV00000023GV00000023GV00000023GV00000023GV00000023GV00000023GV00000023';
        $nonSpacedCouncil = 'TK_CNTT_GV00TK_CNTT_GV00TK_CNTT_GV00TK_CNTT_GV00';

        $itemW = [
            'ma_ho_so'           => 'HSBV_NONSPACE',
            'ten_de_tai'         => $nonSpacedString,
            'ten_nhom'           => 'Nhóm NonSpaced',
            'ten_hoi_dong'       => $nonSpacedCouncil,
            'ma_hoi_dong'        => 'HD99',
            'gv_chua_hoan_thanh' => [
                [
                    'MaGV'      => 'GV999',
                    'HoTen'     => 'TS_GV999_NAME_NON_SPACED_STRING_TESTING',
                    'VaiTro'    => 'Chủ tịch',
                    'TrangThai' => 'Chưa chấm',
                ]
            ],
            'ly_do'              => 'Còn 1 giám khảo chưa hoàn thành nộp phiếu',
        ];

        $rendered = view('truongkhoa.ketqua.partials._warning_topic_card', ['itemW' => $itemW])->render();

        // Đảm bảo không bị vỡ giao diện với chuỗi không dấu cách
        $this->assertStringContainsString($nonSpacedString, $rendered);
        $this->assertStringContainsString($nonSpacedCouncil, $rendered);
        $this->assertStringContainsString('overflow-wrap: anywhere', $rendered);
    }

    public function test_danh_sach_5_de_tai_hien_thi_3_item_va_thu_gon_xem_them_2()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        // Tạo 5 mock đề tài thiếu điểm
        $warnings = [];
        for ($i = 1; $i <= 5; $i++) {
            $warnings[] = [
                'ma_ho_so'           => "HSBV_0{$i}",
                'ten_de_tai'         => "Đề tài thử nghiệm số {$i}",
                'ten_nhom'           => "Nhóm {$i}",
                'ten_hoi_dong'       => "Hội đồng 0{$i}",
                'ma_hoi_dong'        => "HD0{$i}",
                'gv_chua_hoan_thanh' => [
                    [
                        'MaGV'      => "GV00{$i}",
                        'HoTen'     => "Giảng viên {$i}",
                        'VaiTro'    => 'Ủy viên',
                        'TrangThai' => 'Chưa chấm',
                    ]
                ],
                'ly_do'              => 'Chưa hoàn thành',
            ];
        }

        $khoa = Khoa::first() ?? new Khoa(['TenKhoa' => 'Khoa CNTT', 'MaKhoa' => 'CNTT']);
        $hocKies = HocKy::all();
        $hoiDongs = collect();
        $tienDoHoiDongs = [];
        $ketQuas = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        $recentLogs = collect();

        $stats = [
            'tong_sinh_vien'        => 0,
            'tong_de_tai'           => 5,
            'so_hoi_dong'           => 5,
            'de_tai_da_cham'        => 0,
            'de_tai_chua_cham'      => 5,
            'ty_le_hoan_thanh'      => 0,
            'de_tai_da_cong_bo'     => 0,
            'de_tai_chua_cong_bo'   => 5,
            'diem_trung_binh'       => 0,
            'diem_cao_nhat'         => 0,
            'diem_thap_nhat'        => 0,
            'so_dat'                => 0,
            'so_khong_dat'          => 0,
            'ty_le_dat'             => 0,
            'chart_distribution'    => ['A'=>0,'B_plus'=>0,'B'=>0,'C_plus'=>0,'C'=>0,'D_plus'=>0,'D'=>0,'F'=>0],
            'chart_councils'        => [],
            'de_tai_thieu_diem'     => $warnings,
        ];

        $html = view('truongkhoa.ketqua.index', [
            'khoa'            => $khoa,
            'hocKies'         => $hocKies,
            'currentMaHocKy'  => 'HK2627_1',
            'allHoiDongs'     => collect(),
            'hoiDongs'        => $hoiDongs,
            'tienDoHoiDongs'  => $tienDoHoiDongs,
            'ketQuas'         => $ketQuas,
            'stats'           => $stats,
            'recentLogs'      => $recentLogs,
            'viewMode'        => 'toankhoa',
            'boMons'          => collect(),
        ])->render();

        // 1. Tiêu đề hiển thị đúng số lượng 5 đề tài
        $this->assertStringContainsString('Phát hiện 5 đề tài chưa đủ 100% phiếu chấm hợp lệ', $html);

        // 2. Badge chặn công bố tự động có flex-shrink: 0 và white-space: nowrap
        $this->assertStringContainsString('Chặn công bố tự động', $html);
        $this->assertStringContainsString('white-space: nowrap', $html);
        $this->assertStringContainsString('flex-shrink-0', $html);

        // 3. Có container thu gọn collapseMoreWarningItems và nút Xem thêm (2)
        $this->assertStringContainsString('id="collapseMoreWarningItems"', $html);
        $this->assertStringContainsString('id="btnToggleWarningMore"', $html);
        $this->assertStringContainsString('Xem thêm (2)', $html);

        // 4. Có CSS chống tràn cho toàn khối
        $this->assertStringContainsString('warning-de-tai-container', $html);
        $this->assertStringContainsString('overflow-wrap: anywhere', $html);
    }

    public function test_bieu_do_hoi_dong_co_nhan_chong_xoay_cheo_va_tooltip_day_du()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        $response = $this->actingAs($this->userTruongKhoa)->get(route('truongkhoa.ketqua.index'));
        $response->assertStatus(200);

        $content = $response->getContent();

        // Kiểm tra biểu đồ có cấu hình maxRotation: 0, minRotation: 0 để nhãn nằm ngang
        $this->assertStringContainsString('maxRotation: 0', $content);
        $this->assertStringContainsString('minRotation: 0', $content);
        // Kiểm tra tooltip lấy fullName
        $this->assertStringContainsString('fullName', $content);
    }
}
