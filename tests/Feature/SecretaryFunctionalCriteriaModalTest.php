<?php

namespace Tests\Feature;

use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\PhieuChamDiem;
use App\Models\TaiKhoan;
use App\Models\ThanhVienHoiDong;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SecretaryFunctionalCriteriaModalTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test 1: Kiểm tra hằng số trần điểm và thuật toán phân bổ cent (tổng đúng bằng trần)
     */
    public function test_max_score_cap_constants_and_distribution(): void
    {
        // 1. Kiểm tra hằng số trần điểm
        $this->assertEquals(3.50, PhieuChamDiem::MAX_DIEM_CHUC_NANG['KLCN']);
        $this->assertEquals(4.00, PhieuChamDiem::MAX_DIEM_CHUC_NANG['KLKS']);
        $this->assertEquals(3.50, PhieuChamDiem::getMaxDiemChucNang('KLCN'));
        $this->assertEquals(4.00, PhieuChamDiem::getMaxDiemChucNang('KLKS'));

        // 2. Phân bổ cho 1 yêu cầu duy nhất
        $scores1 = PhieuChamDiem::distributeScores(3.50, 1);
        $this->assertEquals([3.50], $scores1);

        // 3. Phân bổ cho 3 yêu cầu (3.5 / 3 = 1.17, 1.17, 1.16)
        $scores3 = PhieuChamDiem::distributeScores(3.50, 3);
        $this->assertCount(3, $scores3);
        $this->assertEquals(3.50, round(array_sum($scores3), 2));
        $this->assertEquals([1.17, 1.17, 1.16], $scores3);

        // 4. Phân bổ cho 7 yêu cầu (3.5 / 7 = 0.50 x 7)
        $scores7 = PhieuChamDiem::distributeScores(3.50, 7);
        $this->assertCount(7, $scores7);
        $this->assertEquals(3.50, round(array_sum($scores7), 2));
        foreach ($scores7 as $s) {
            $this->assertEquals(0.50, $s);
        }

        // 5. Phân bổ cho 49 yêu cầu (Khắc phục hoàn toàn lỗi tràn 24.50đ ở dữ liệu cũ)
        $scores49 = PhieuChamDiem::distributeScores(3.50, 49);
        $this->assertCount(49, $scores49);
        $this->assertEquals(3.50, round(array_sum($scores49), 2));

        // 6. Phân bổ cho KLKS (Trần 4.00đ)
        $scoresKs8 = PhieuChamDiem::distributeScores(4.00, 8);
        $this->assertCount(8, $scoresKs8);
        $this->assertEquals(4.00, round(array_sum($scoresKs8), 2));

        $scoresKs49 = PhieuChamDiem::distributeScores(4.00, 49);
        $this->assertCount(49, $scoresKs49);
        $this->assertEquals(4.00, round(array_sum($scoresKs49), 2));
    }

    /**
     * Test 2: Tự động nhận diện cú pháp: Thụt lề vs Không thụt lề, Bullet markers, Nhóm rỗng
     */
    public function test_smart_recognition_rules_for_text(): void
    {
        // Kịch bản A: Thụt lề (Tab / spaces)
        $indentedText = "Chức năng 1: Quản lý đơn hàng\n  Tiếp nhận đơn hàng\n  Thanh toán trực tuyến\nChức năng 2: Quản lý sản phẩm\n  Cập nhật tồn kho";
        $parsedA = PhieuChamDiem::parseFunctionalRequirements($indentedText, 'KLCN');
        $nonBasicA = collect($parsedA)->where('is_co_ban', false);
        $this->assertCount(3, $nonBasicA);
        $this->assertEquals(3.50, round($nonBasicA->sum('max'), 2));

        // Kịch bản B: Danh sách gạch đầu dòng phẳng (người dùng không thụt lề)
        // Hệ thống tự coi mỗi dòng là một yêu cầu (không báo lỗi nhóm rỗng)
        $flatListText = "Đăng nhập hệ thống\nQuản trị danh mục hàng hóa\nThống kê doanh thu theo tháng\nXuất báo cáo PDF";
        $parsedB = PhieuChamDiem::parseFunctionalRequirements($flatListText, 'KLCN');
        $this->assertNotEmpty($parsedB);
        $nonBasicB = collect($parsedB)->where('is_co_ban', false);
        $this->assertGreaterThanOrEqual(3, $nonBasicB->count());
        $this->assertEquals(3.50, round($nonBasicB->sum('max'), 2));

        // Kịch bản C: Dán từ Word/Excel với số thứ tự và gạch đầu dòng
        $wordPastedText = "1. Phân hệ bán hàng:\n  - Tạo đơn hàng mới\n  - Quản lý giỏ hàng\n2. Phân hệ vận chuyển:\n  * Cập nhật trạng thái giao hàng\n  * Tra cứu mã vận đơn";
        $parsedC = PhieuChamDiem::parseFunctionalRequirements($wordPastedText, 'KLKS');
        $this->assertCount(4, $parsedC);
        $this->assertEquals(4.00, round(collect($parsedC)->sum('max'), 2));

        // Tự động bỏ ký hiệu đầu dòng (+, -, *, Yêu cầu 1:)
        $this->assertEquals('Tạo đơn hàng mới', $parsedC[0]['label']);
        $this->assertEquals('Quản lý giỏ hàng', $parsedC[1]['label']);
    }

    /**
     * Test 3: Thư ký Hội đồng cập nhật tiêu chí chức năng thành công qua POST
     */
    public function test_secretary_can_update_functional_criteria(): void
    {
        $hoSo = HoSoBaoVe::firstOrFail();
        $hoiDong = HoiDong::findOrFail($hoSo->MaHoiDong);

        // Tìm hoặc tạo Thư ký hội đồng
        $secretaryTv = ThanhVienHoiDong::where('MaHoiDong', $hoiDong->MaHoiDong)
            ->whereIn('VaiTro', ['Thư ký', 'Ủy viên thư ký'])
            ->first();

        if (!$secretaryTv) {
            $gv = GiangVien::whereNotIn('MaGV', $hoiDong->thanhViens->pluck('MaGV'))->firstOrFail();
            $secretaryTv = ThanhVienHoiDong::create([
                'MaHoiDong' => $hoiDong->MaHoiDong,
                'MaGV'      => $gv->MaGV,
                'VaiTro'    => 'Ủy viên thư ký',
            ]);
        }

        $secretaryGv = GiangVien::findOrFail($secretaryTv->MaGV);
        $user = TaiKhoan::where('MaGV', $secretaryGv->MaGV)->firstOrFail();

        $newCriteriaText = "Chức năng 1: Quản trị thanh toán\n  Tích hợp ví điện tử\n  Lập hóa đơn điện tử";

        $response = $this->actingAs($user)->post(route('giangvien.chamdiem.capNhatChucNang'), [
            'MaHoiDong'       => $hoiDong->MaHoiDong,
            'MaHoSo'          => $hoSo->MaHoSo,
            'TieuChiChucNang' => $newCriteriaText,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $hoSo->refresh();
        $this->assertEquals($newCriteriaText, $hoSo->TieuChiChucNang);
        $this->assertEquals('DaChuanBi', $hoSo->TrangThaiChuanBiPhieu);
        $this->assertEquals($secretaryGv->MaGV, $hoSo->NguoiChuanBiPhieu);
    }

    /**
     * Test 4: Giảng viên không phải Thư ký bị chặn không cho cập nhật tiêu chí
     */
    public function test_non_secretary_cannot_update_criteria(): void
    {
        $hoSo = HoSoBaoVe::firstOrFail();
        $hoiDong = HoiDong::findOrFail($hoSo->MaHoiDong);

        $memberTv = ThanhVienHoiDong::where('MaHoiDong', $hoiDong->MaHoiDong)
            ->whereNotIn('VaiTro', ['Thư ký', 'Ủy viên thư ký'])
            ->first();

        if ($memberTv) {
            $memberGv = GiangVien::findOrFail($memberTv->MaGV);
            $user = TaiKhoan::where('MaGV', $memberGv->MaGV)->first();

            if ($user && !$user->isTruongKhoa() && !$user->isGiaoVu() && !$user->isAdmin()) {
                $response = $this->actingAs($user)->post(route('giangvien.chamdiem.capNhatChucNang'), [
                    'MaHoiDong'       => $hoiDong->MaHoiDong,
                    'MaHoSo'          => $hoSo->MaHoSo,
                    'TieuChiChucNang' => 'Nội dung test',
                ]);

                $response->assertSessionHas('error');
            }
        }
    }

    /**
     * Test 5: Kiểm tra giao diện modal tối giản đúng tiêu chuẩn yêu cầu
     */
    public function test_simplified_modal_view_rendering(): void
    {
        $hoSo = HoSoBaoVe::firstOrFail();
        $hoiDong = HoiDong::findOrFail($hoSo->MaHoiDong);

        // Đặt thời gian vào giữa buổi bảo vệ của hội đồng
        Carbon::setTestNow('2026-12-15 09:00:00');
        $hoiDong->NgayBaoVe = '2026-12-15';
        $hoiDong->ThoiGianBatDau = '2026-12-15 08:00:00';
        $hoiDong->ThoiGianKetThuc = '2026-12-15 11:30:00';
        $hoiDong->TrangThaiCongBo = 'Đã công bố';
        $hoiDong->save();

        // Đảm bảo có thư ký
        $secretaryTv = ThanhVienHoiDong::where('MaHoiDong', $hoiDong->MaHoiDong)
            ->whereIn('VaiTro', ['Thư ký', 'Ủy viên thư ký'])
            ->first();

        if (!$secretaryTv) {
            $gv = GiangVien::whereNotIn('MaGV', $hoiDong->thanhViens->pluck('MaGV'))->firstOrFail();
            $secretaryTv = ThanhVienHoiDong::create([
                'MaHoiDong' => $hoiDong->MaHoiDong,
                'MaGV'      => $gv->MaGV,
                'VaiTro'    => 'Ủy viên thư ký',
            ]);
        }

        $secretaryGv = GiangVien::findOrFail($secretaryTv->MaGV);
        $user = TaiKhoan::where('MaGV', $secretaryGv->MaGV)->firstOrFail();

        $response = $this->actingAs($user)->get(route('giangvien.hoidong.index', [
            'tab'  => 'chamdiem',
            'hd'   => $hoiDong->MaHoiDong,
            'hoso' => $hoSo->MaHoSo,
        ]));

        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Header modal KHÔNG chứa chuỗi phụ đề cũ làm mờ
        $this->assertStringNotContainsString('Thư ký chuẩn bị mẫu phiếu chấm', $content);

        // 2. Tiêu đề trang trọng
        $this->assertStringContainsString('Thiết Lập Tiêu Chí Phiếu Chấm &amp; Yêu Cầu Chức Năng (Dành Cho Thư Ký Hội Đồng)', $content);

        // 3. Đã BỎ HOÀN TOÀN 2 tab và các nút phức tạp
        $this->assertStringNotContainsString('Biểu Mẫu Cây', $content);
        $this->assertStringNotContainsString('Văn Bản Nhanh', $content);
        $this->assertStringNotContainsString('+ Nhóm Chức Năng', $content);
        $this->assertStringNotContainsString('+ Yêu Cầu Con', $content);
        $this->assertStringNotContainsString('Nhập Từ File', $content);
        $this->assertStringNotContainsString('btn-toolbar-unified', $content);
        $this->assertStringNotContainsString('containerTreeForm', $content);
        $this->assertStringNotContainsString('containerFastText', $content);

        // 4. Giữ lại MỘT textarea lớn + 3 nút cùng kiểu + liên kết "Xóa hết"
        $this->assertStringContainsString('textareaChucNang', $content);
        $this->assertStringContainsString('Nhập từ đề cương', $content);
        $this->assertStringContainsString('Mẫu Cử nhân', $content);
        $this->assertStringContainsString('Mẫu Kỹ sư', $content);
        $this->assertStringContainsString('Xóa hết', $content);
        $this->assertStringContainsString('previewSummaryText', $content);
        $this->assertStringContainsString('footerDraftNotice', $content);
        $this->assertStringContainsString('btnSubmitChucNang', $content);
    }
}
