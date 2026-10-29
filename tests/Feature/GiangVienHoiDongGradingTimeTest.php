<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\GiangVien;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\Nhom;
use App\Models\DeTai;
use App\Models\ThanhVienHoiDong;
use App\Models\PhieuChamDiem;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GiangVienHoiDongGradingTimeTest extends TestCase
{
    use DatabaseTransactions;

    protected $giangVien;
    protected $taiKhoan;
    protected $hoiDong;
    protected $hoSo;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Lấy hoặc tạo giảng viên test
        $this->giangVien = GiangVien::where('MaGV', 'GV00000002')->first()
            ?? GiangVien::whereHas('taiKhoan')->first();

        if ($this->giangVien) {
            $this->taiKhoan = $this->giangVien->taiKhoan;
        }

        // 2. Tìm hoặc chuẩn bị Hội đồng HD04 (có ngày bảo vệ 04/11/2026 07:30 - 17:00)
        $this->hoiDong = HoiDong::find('HD04') ?? HoiDong::first();
        if ($this->hoiDong) {
            $this->hoiDong->NgayBaoVe = '2026-11-04';
            $this->hoiDong->ThoiGianBatDau = '2026-11-04 07:30:00';
            $this->hoiDong->ThoiGianKetThuc = '2026-11-04 17:00:00';
            $this->hoiDong->TrangThaiCongBo = 'Chưa công bố';
            $this->hoiDong->save();

            // Đảm bảo giảng viên là thành viên hội đồng này
            ThanhVienHoiDong::firstOrCreate([
                'MaHoiDong' => $this->hoiDong->MaHoiDong,
                'MaGV'      => $this->giangVien->MaGV,
            ], [
                'VaiTro'    => 'Ủy viên',
            ]);

            $this->hoSo = $this->hoiDong->hoSoBaoVes->first();
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    public function test_hoidong_sap_dien_ra_khong_hien_nut_cham_diem_va_tab_cham_diem_hien_empty_state()
    {
        if (!$this->taiKhoan || !$this->hoiDong) {
            $this->markTestSkipped('Thiếu dữ liệu Giảng viên hoặc Hội đồng test.');
        }

        // Thời gian hiện tại là 28/10/2026 (trước ngày 04/11/2026)
        Carbon::setTestNow('2026-10-28 09:00:00');

        $this->assertEquals('SapDienRa', $this->hoiDong->trang_thai_thoi_gian);
        $this->assertFalse($this->hoiDong->isWithinGradingWindow());

        $reason = '';
        $this->assertFalse($this->hoiDong->canGrade($this->giangVien, null, $reason));
        $this->assertStringContainsString('Hội đồng chưa đến thời gian chấm điểm', $reason);

        // Truy cập view danh sách
        $response = $this->actingAs($this->taiKhoan)->get(route('giangvien.hoidong.index'));
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Badge "Sắp Diễn Ra" hiển thị
        $this->assertStringContainsString('Sắp Diễn Ra', $content);

        // 2. Nút chấm điểm của hội đồng sắp diễn ra bị disabled và có tooltip
        $this->assertStringContainsString('disabled', $content);
        $this->assertStringContainsString('Mở chấm điểm từ', $content);

        // 3. Tab Chấm điểm không có badge số đỏ (> 0)
        $this->assertStringNotContainsString('id="badgeChoChamDiem"', $content);

        // 4. Tab Chấm điểm hiển thị Empty State thông minh
        $this->assertStringContainsString('Hiện Chưa Có Hội Đồng Nào Đến Thời Gian Chấm Điểm', $content);
        $this->assertStringContainsString('Hội đồng gần nhất', $content);
    }

    public function test_truy_cap_truc_tiep_url_cham_diem_khi_chua_den_gio_bi_chuyen_huong_ve_danh_sach()
    {
        if (!$this->taiKhoan || !$this->hoiDong) {
            $this->markTestSkipped('Thiếu dữ liệu Giảng viên hoặc Hội đồng test.');
        }

        Carbon::setTestNow('2026-10-28 09:00:00');

        // Thử vào tab chấm điểm với hội đồng chưa đến giờ
        $response = $this->actingAs($this->taiKhoan)->get(route('giangvien.hoidong.index', [
            'tab' => 'chamdiem',
            'hd'  => $this->hoiDong->MaHoiDong,
        ]));

        // Phải bị chuyển hướng về tab danhsach kèm thông báo lỗi
        $response->assertRedirect(route('giangvien.hoidong.index', ['tab' => 'danhsach']));
        $response->assertSessionHas('error');

        // Thử gọi qua alias route /cham-diem?hd=HD04
        $resAlias = $this->actingAs($this->taiKhoan)->get('/giangvien/cham-diem?hd=' . $this->hoiDong->MaHoiDong);
        $resAlias->assertRedirect(route('giangvien.hoidong.index', ['tab' => 'danhsach']));
        $resAlias->assertSessionHas('error');
    }

    public function test_backend_chan_luu_nhap_va_nop_diem_khi_hoidong_sap_dien_ra()
    {
        if (!$this->taiKhoan || !$this->hoiDong || !$this->hoSo) {
            $this->markTestSkipped('Thiếu dữ liệu Giảng viên, Hội đồng hoặc Hồ sơ bảo vệ.');
        }

        Carbon::setTestNow('2026-10-28 09:00:00');

        $sv = $this->hoSo->nhom?->thanhViens->first()?->sinhVien;
        if (!$sv) {
            $this->markTestSkipped('Nhóm không có sinh viên.');
        }

        $payload = [
            'MaHoiDong'    => $this->hoiDong->MaHoiDong,
            'MaHoSo'       => $this->hoSo->MaHoSo,
            'LoaiKhoaLuan' => 'KLCN',
            'action_type'  => 'luu_nhap',
            'diems'        => [
                $sv->MaSV => [
                    'ChiTietDiem' => [
                        'tc1' => 0.5,
                        'tc2' => 0.5,
                        'tc3' => 0.5,
                        'tc4' => 0.5,
                        'tc5' => 0.5,
                        'tc6' => 0.5,
                        'tc7' => 1.5,
                    ],
                    'NhanXet'     => 'Nhận xét lưu nháp sớm.',
                ]
            ]
        ];

        // 1. Thử gửi AJAX request lưu nháp -> Phải trả về 403 Forbidden
        $resAjax = $this->actingAs($this->taiKhoan)
            ->postJson(route('giangvien.chamdiem.store'), $payload);

        $resAjax->assertStatus(403);
        $resAjax->assertJson(['success' => false]);
        $this->assertStringContainsString('Hội đồng chưa đến thời gian chấm điểm', $resAjax->json('message'));

        // 2. Thử gửi POST web thường xác nhận hoàn thành -> Phải redirect back kèm error
        $this->flushHeaders();
        $payload['action_type'] = 'xac_nhan_hoan_thanh';
        $resWeb = $this->actingAs($this->taiKhoan)
            ->from(route('giangvien.hoidong.index'))
            ->post(route('giangvien.chamdiem.store'), $payload);
        $resWeb->assertRedirect();
        $resWeb->assertSessionHas('error');
    }

    public function test_mo_cham_diem_thanh_cong_khi_hoidong_dang_dien_ra()
    {
        if (!$this->taiKhoan || !$this->hoiDong || !$this->hoSo) {
            $this->markTestSkipped('Thiếu dữ liệu.');
        }

        // Đặt giờ ảo vào giữa buổi bảo vệ (04/11/2026 10:00:00)
        Carbon::setTestNow('2026-11-04 10:00:00');

        $this->assertEquals('DangDienRa', $this->hoiDong->trang_thai_thoi_gian);
        $this->assertTrue($this->hoiDong->isWithinGradingWindow());

        $reason = '';
        $this->assertTrue($this->hoiDong->canGrade($this->giangVien, $this->hoSo, $reason));

        $sv = $this->hoSo->nhom?->thanhViens->first()?->sinhVien;
        if (!$sv) {
            $this->markTestSkipped('Nhóm không có sinh viên.');
        }

        // Vào được tab chấm điểm mà không bị redirect
        $resView = $this->actingAs($this->taiKhoan)->get(route('giangvien.hoidong.index', [
            'tab' => 'chamdiem',
            'hd'  => $this->hoiDong->MaHoiDong,
        ]));
        $resView->assertStatus(200);
        $this->assertStringContainsString('Xác Nhận &amp; Khóa Phiếu Chấm', $resView->getContent());

        // Lưu nháp điểm thành công
        $payload = [
            'MaHoiDong'    => $this->hoiDong->MaHoiDong,
            'MaHoSo'       => $this->hoSo->MaHoSo,
            'LoaiKhoaLuan' => 'KLCN',
            'action_type'  => 'luu_nhap',
            'diems'        => [
                $sv->MaSV => [
                    'ChiTietDiem' => [
                        'tc1' => 0.5,
                        'tc2' => 0.5,
                        'tc3' => 0.5,
                        'tc4' => 0.5,
                        'tc5' => 0.5,
                        'tc6' => 0.5,
                        'tc7' => 1.5,
                    ],
                    'NhanXet'     => 'Chấm điểm đang diễn ra.',
                ]
            ]
        ];

        $resPost = $this->actingAs($this->taiKhoan)->post(route('giangvien.chamdiem.store'), $payload);
        $resPost->assertSessionHas('success');

        $this->assertDatabaseHas('PhieuChamDiem', [
            'MaHoiDong' => $this->hoiDong->MaHoiDong,
            'MaHoSo'    => $this->hoSo->MaHoSo,
            'MaGV'      => $this->giangVien->MaGV,
            'MaSV'      => $sv->MaSV,
            'TrangThai' => 'Lưu nháp',
        ]);
    }

    public function test_cham_diem_khi_hoidong_da_ket_thuc_trong_han_7_ngay()
    {
        if (!$this->taiKhoan || !$this->hoiDong) {
            $this->markTestSkipped('Thiếu dữ liệu.');
        }

        // Ngày 06/11/2026 (sau 2 ngày kết thúc, trong hạn 7 ngày)
        Carbon::setTestNow('2026-11-06 14:00:00');

        $this->assertEquals('DaKetThuc', $this->hoiDong->trang_thai_thoi_gian);
        $this->assertTrue($this->hoiDong->isWithinGradingWindow());

        $reason = '';
        $this->assertTrue($this->hoiDong->canGrade($this->giangVien, null, $reason));
    }

    public function test_khoa_cham_diem_khi_qua_han_7_ngay()
    {
        if (!$this->taiKhoan || !$this->hoiDong || !$this->hoSo) {
            $this->markTestSkipped('Thiếu dữ liệu.');
        }

        // Ngày 15/11/2026 (quá hạn 7 ngày sau ngày 04/11/2026)
        Carbon::setTestNow('2026-11-15 10:00:00');

        $this->assertEquals('DaKetThuc', $this->hoiDong->trang_thai_thoi_gian);
        $this->assertFalse($this->hoiDong->isWithinGradingWindow());

        $reason = '';
        $this->assertFalse($this->hoiDong->canGrade($this->giangVien, $this->hoSo, $reason));
        $this->assertStringContainsString('Đã quá thời hạn chấm điểm Hội đồng', $reason);

        // Gửi request chấm điểm bị chặn 422
        $sv = $this->hoSo->nhom?->thanhViens->first()?->sinhVien;
        if (!$sv) return;

        $resAjax = $this->actingAs($this->taiKhoan)
            ->postJson(route('giangvien.chamdiem.store'), [
                'MaHoiDong'    => $this->hoiDong->MaHoiDong,
                'MaHoSo'       => $this->hoSo->MaHoSo,
                'LoaiKhoaLuan' => 'KLCN',
                'action_type'  => 'luu_nhap',
                'diems'        => [
                    $sv->MaSV => [
                        'ChiTietDiem' => ['tc1' => 0.5],
                    ]
                ]
            ]);

        $resAjax->assertStatus(403);
        $this->assertStringContainsString('Đã quá thời hạn chấm điểm', $resAjax->json('message'));
    }

    public function test_chan_giang_vien_khong_thuoc_hoi_dong()
    {
        if (!$this->hoiDong || !$this->hoSo) {
            $this->markTestSkipped('Thiếu dữ liệu.');
        }

        // Lấy giảng viên KHÔNG thuộc hội đồng HD04
        $otherGv = GiangVien::whereDoesntHave('thanhVienHoiDongs', function($q) {
            $q->where('MaHoiDong', $this->hoiDong->MaHoiDong);
        })->whereHas('taiKhoan')->first();

        if (!$otherGv || !$otherGv->taiKhoan) {
            $this->markTestSkipped('Không tìm thấy giảng viên ngoài hội đồng.');
        }

        // Cho dù là đang trong giờ bảo vệ
        Carbon::setTestNow('2026-11-04 10:00:00');

        $reason = '';
        $this->assertFalse($this->hoiDong->canGrade($otherGv, $this->hoSo, $reason));
        $this->assertStringContainsString('Bạn không thuộc Hội đồng bảo vệ này', $reason);

        $sv = $this->hoSo->nhom?->thanhViens->first()?->sinhVien;
        if (!$sv) return;

        $res = $this->actingAs($otherGv->taiKhoan)
            ->postJson(route('giangvien.chamdiem.store'), [
                'MaHoiDong'    => $this->hoiDong->MaHoiDong,
                'MaHoSo'       => $this->hoSo->MaHoSo,
                'LoaiKhoaLuan' => 'KLCN',
                'action_type'  => 'luu_nhap',
                'diems'        => [
                    $sv->MaSV => [
                        'ChiTietDiem' => ['tc1' => 0.5],
                    ]
                ]
            ]);

        $res->assertStatus(403);
        $this->assertStringContainsString('Bạn không thuộc Hội đồng bảo vệ này', $res->json('message'));
    }

    public function test_dong_nhat_trang_thai_va_quyen_cham_diem_giua_danh_sach_va_chi_tiet_hoidong()
    {
        // Sử dụng hội đồng HD03 (03/11/2026, 07:30 - 17:00)
        $hd03 = HoiDong::find('HD03') ?? $this->hoiDong;
        $hd03->NgayBaoVe = '2026-11-03';
        $hd03->ThoiGianBatDau = '2026-11-03 07:30:00';
        $hd03->ThoiGianKetThuc = '2026-11-03 17:00:00';
        $hd03->TrangThaiCongBo = 'Chưa công bố';
        $hd03->save();

        // Đảm bảo giảng viên test thuộc hội đồng HD03
        ThanhVienHoiDong::firstOrCreate([
            'MaHoiDong' => $hd03->MaHoiDong,
            'MaGV'      => $this->giangVien->MaGV,
        ], [
            'VaiTro'    => 'Ủy viên',
        ]);

        // KỊCH BẢN 1: 03/11/2026 06:00 (Sắp diễn ra)
        Carbon::setTestNow('2026-11-03 06:00:00');
        $this->assertEquals('SapDienRa', $hd03->trang_thai_thoi_gian);
        $this->assertEquals('Sắp Diễn Ra', $hd03->ten_trang_thai_thoi_gian);
        $this->assertFalse($hd03->canGrade($this->giangVien));

        $resList1 = $this->actingAs($this->taiKhoan)->get(route('giangvien.hoidong.index'));
        $this->assertStringContainsString('Sắp Diễn Ra', $resList1->getContent());

        $resShow1 = $this->actingAs($this->taiKhoan)->get(route('giangvien.hoidong.show', $hd03->MaHoiDong));
        $this->assertStringContainsString('Sắp Diễn Ra', $resShow1->getContent());
        $this->assertStringContainsString('disabled', $resShow1->getContent());

        // Gọi trực tiếp API chấm điểm khi chưa đến giờ -> Bị chặn 403
        $resApiBlock = $this->actingAs($this->taiKhoan)->postJson(route('giangvien.chamdiem.store'), [
            'MaHoiDong'    => $hd03->MaHoiDong,
            'MaHoSo'       => $this->hoSo->MaHoSo,
            'LoaiKhoaLuan' => 'KLCN',
            'action_type'  => 'luu_nhap',
            'diems'        => ['SVTEST' => ['ChiTietDiem' => ['tc1' => 0.5]]],
        ]);
        $resApiBlock->assertStatus(403);

        // KỊCH BẢN 2: 03/11/2026 16:00 (Đang diễn ra)
        Carbon::setTestNow('2026-11-03 16:00:00');
        $this->assertEquals('DangDienRa', $hd03->trang_thai_thoi_gian);
        $this->assertEquals('Đang Diễn Ra', $hd03->ten_trang_thai_thoi_gian);
        $this->assertTrue($hd03->canGrade($this->giangVien));

        $resList2 = $this->actingAs($this->taiKhoan)->get(route('giangvien.hoidong.index'));
        $contentList2 = $resList2->getContent();
        $this->assertStringContainsString('Đang Diễn Ra', $contentList2);
        // Ở trang danh sách, hội đồng đang diễn ra phải có nút "Chấm điểm"
        $this->assertStringContainsString('Chấm điểm', $contentList2);

        $resShow2 = $this->actingAs($this->taiKhoan)->get(route('giangvien.hoidong.show', $hd03->MaHoiDong));
        $contentShow2 = $resShow2->getContent();
        $this->assertStringContainsString('Đang Diễn Ra', $contentShow2);
        // Ở trang chi tiết, nút "Vào Phòng Chấm Điểm" và "Mở Bảng Điểm Hội Đồng" được bật
        $this->assertStringContainsString('Vào Phòng Chấm Điểm', $contentShow2);
        $this->assertStringContainsString('Mở Bảng Điểm Hội Đồng', $contentShow2);

        // KỊCH BẢN 3: 03/11/2026 18:00 (Đã kết thúc, còn hạn 7 ngày, chưa khóa)
        Carbon::setTestNow('2026-11-03 18:00:00');
        $this->assertEquals('DaKetThuc', $hd03->trang_thai_thoi_gian);
        $this->assertEquals('Đã Kết Thúc', $hd03->ten_trang_thai_thoi_gian);
        $this->assertTrue($hd03->canGrade($this->giangVien));

        $resShow3 = $this->actingAs($this->taiKhoan)->get(route('giangvien.hoidong.show', $hd03->MaHoiDong));
        $this->assertStringContainsString('Đã Kết Thúc', $resShow3->getContent());

        // KỊCH BẢN 4: 28/10/2026 (Giờ thật)
        Carbon::setTestNow('2026-10-28 17:00:00');
        $this->assertEquals('SapDienRa', $hd03->trang_thai_thoi_gian);
        $this->assertEquals('Sắp Diễn Ra', $hd03->ten_trang_thai_thoi_gian);
        $this->assertFalse($hd03->canGrade($this->giangVien));

        $resShow4 = $this->actingAs($this->taiKhoan)->get(route('giangvien.hoidong.show', $hd03->MaHoiDong));
        $this->assertStringContainsString('Sắp Diễn Ra', $resShow4->getContent());
    }
}
