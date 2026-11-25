<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Helpers\HtmlHelper;
use App\Models\BaoCaoSauBaoVe;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HoSoBaoVe;
use App\Models\HoiDong;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\Nhom;
use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\ThanhVienNhom;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;

class BaoCaoSauBaoVeEvaluationTest extends TestCase
{
    use DatabaseTransactions;

    protected $lecturer;
    protected $lecturerUser;
    protected $unauthorizedUser;
    protected $student;
    protected $studentUser;
    protected $hoSo;
    protected $baoCao;
    protected $pdfStoragePath;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Khoa, BoMon
        $khoa = Khoa::first() ?? Khoa::create(['MaKhoa' => 'CNTT', 'TenKhoa' => 'Khoa Công nghệ Thông tin']);
        $boMon = BoMon::first() ?? BoMon::create(['MaBoMon' => 'BM_CNPM', 'TenBoMon' => 'Bộ môn CNPM', 'MaKhoa' => $khoa->MaKhoa]);

        // 2. Giảng viên kiểm tra
        $this->lecturerUser = TaiKhoan::create([
            'MaTK' => 'TK_GV_EVAL_' . bin2hex(random_bytes(4)),
            'TenDangNhap' => 'gveval_' . bin2hex(random_bytes(4)),
            'MatKhau' => bcrypt('123456'),
            'MaVaiTro' => 'VT02', // Giảng viên
            'TrangThai' => true,
        ]);

        $this->lecturer = GiangVien::create([
            'MaGV' => 'GV_EVAL_' . bin2hex(random_bytes(4)),
            'HoTen' => 'TS. Nguyễn Đánh Giá Test',
            'Email' => 'gveval_' . bin2hex(random_bytes(4)) . '@huit.edu.vn',
            'MaTK' => $this->lecturerUser->MaTK,
            'MaBoMon' => $boMon->MaBoMon,
        ]);

        // Giảng viên ngoài (không có quyền)
        $this->unauthorizedUser = TaiKhoan::create([
            'MaTK' => 'TK_GV_OTHER_' . bin2hex(random_bytes(4)),
            'TenDangNhap' => 'gvother_' . bin2hex(random_bytes(4)),
            'MatKhau' => bcrypt('123456'),
            'MaVaiTro' => 'VT02',
            'TrangThai' => true,
        ]);
        GiangVien::create([
            'MaGV' => 'GV_OTHER_' . bin2hex(random_bytes(4)),
            'HoTen' => 'ThS. Người Ngoài Không Quyền',
            'Email' => 'gvother_' . bin2hex(random_bytes(4)) . '@huit.edu.vn',
            'MaTK' => $this->unauthorizedUser->MaTK,
            'MaBoMon' => $boMon->MaBoMon,
        ]);

        // 3. Sinh viên
        $this->studentUser = TaiKhoan::create([
            'MaTK' => 'TK_SV_TEST_' . bin2hex(random_bytes(4)),
            'TenDangNhap' => 'svtest_' . bin2hex(random_bytes(4)),
            'MatKhau' => bcrypt('123456'),
            'MaVaiTro' => 'VT03', // Sinh viên
            'TrangThai' => true,
        ]);

        $this->student = SinhVien::create([
            'MaSV' => 'SV_EVAL_' . bin2hex(random_bytes(4)),
            'HoTen' => 'Trần Sinh Viên Đánh Giá',
            'Email' => 'sveval_' . bin2hex(random_bytes(4)) . '@st.huit.edu.vn',
            'MaTK' => $this->studentUser->MaTK,
            'MaKhoa' => $khoa->MaKhoa,
        ]);

        // 4. Nhóm & Đề tài
        $hocKy = \App\Models\HocKy::first();
        $maHocKy = $hocKy ? $hocKy->MaHocKy : 'HK2627_1';

        $deTai = DeTai::create([
            'MaDeTai' => 'DT_EVAL_' . bin2hex(random_bytes(4)),
            'TenDeTai' => 'Hệ thống Quản lý Báo cáo Tốt nghiệp Test',
            'MaGV' => $this->lecturer->MaGV,
            'MaHocKy' => $maHocKy,
            'TrangThai' => 'Đã duyệt',
        ]);

        $nhom = Nhom::create([
            'MaNhom' => 'NHOM_EVAL_' . bin2hex(random_bytes(4)),
            'TenNhom' => 'Nhóm Test Đánh Giá',
            'MaDeTai' => $deTai->MaDeTai,
            'MaTruongNhom' => $this->student->MaSV,
            'TrangThai' => 'Đang thực hiện',
        ]);

        ThanhVienNhom::create([
            'MaNhom' => $nhom->MaNhom,
            'MaSV' => $this->student->MaSV,
            'VaiTro' => 'Trưởng nhóm',
            'TrangThai' => 'da_tham_gia',
        ]);

        // 5. Hội đồng
        $hoiDong = HoiDong::create([
            'MaHoiDong' => 'HD_EVAL_' . bin2hex(random_bytes(4)),
            'TenHoiDong' => 'Hội đồng Test Báo Cáo Chỉnh Sửa',
            'MaKhoa' => $khoa->MaKhoa,
            'DiaDiem' => 'Phòng B.304',
            'ThoiGianBatDau' => now()->subDay(),
            'TrangThai' => 'Đang diễn ra',
        ]);

        // 6. HoSoBaoVe
        $this->hoSo = HoSoBaoVe::create([
            'MaHoSo' => 'HS_EVAL_' . bin2hex(random_bytes(4)),
            'MaNhom' => $nhom->MaNhom,
            'MaDeTai' => $deTai->MaDeTai,
            'MaHoiDong' => $hoiDong->MaHoiDong,
            'TrangThai' => 'Đủ điều kiện bảo vệ',
            'TrangThaiChinhSuaSauBaoVe' => 'DaNop',
            'NguoiKiemTraChinhSua' => $this->lecturer->MaGV,
            'YeuCauChinhSuaSauBaoVe' => '<p>Sửa lỗi định dạng chương 3 và bổ sung kết quả kiểm thử.</p>',
        ]);

        // Tạo dummy file PDF trên storage
        $relativePdfPath = 'hoso/test_reports/test_report_' . bin2hex(random_bytes(4)) . '.pdf';
        $fullPdfPath = storage_path('app/public/' . $relativePdfPath);
        if (!is_dir(dirname($fullPdfPath))) {
            mkdir(dirname($fullPdfPath), 0777, true);
        }
        file_put_contents($fullPdfPath, "%PDF-1.4 test evaluation pdf content");
        $this->pdfStoragePath = $fullPdfPath;

        // 7. BaoCaoSauBaoVe
        $this->baoCao = BaoCaoSauBaoVe::create([
            'MaHoSo' => $this->hoSo->MaHoSo,
            'LanChinhSua' => 1,
            'DuongDanFile' => $relativePdfPath,
            'TenFile' => 'BaoCao_HoanThien_Test.pdf',
            'DungLuong' => 102400,
            'TomTatChinhSua' => '<p>44r534dg</p>',
            'NhanXetGV' => '<p>Báo cáo toàn văn đã được chỉnh sửa đúng theo ý kiến góp ý của Hội đồng bảo vệ. Hình thức và nội dung đạt chuẩn quy định lưu trữ Khoa.</p>',
            'NgayNop' => now(),
            'NguoiNop' => $this->student->MaSV,
            'TrangThai' => 'Đã nộp',
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->pdfStoragePath && file_exists($this->pdfStoragePath)) {
            @unlink($this->pdfStoragePath);
        }
        parent::tearDown();
    }

    /**
     * Test HtmlHelper::toPlainText correctly cleans HTML tags and entity encoded tags
     */
    public function test_html_helper_to_plain_text(): void
    {
        $this->assertSame('44r534dg', HtmlHelper::toPlainText('<p>44r534dg</p>'));
        $this->assertSame('44r534dg', HtmlHelper::toPlainText('&lt;p&gt;44r534dg&lt;/p&gt;'));

        $richText = "<p>Dòng 1</p><p>Dòng 2</p>";
        $expected = "Dòng 1\nDòng 2";
        $this->assertSame($expected, HtmlHelper::toPlainText($richText));

        $htmlWithEntities = "Đã sửa xong &amp; hoàn thiện &nbsp; các mục.";
        $this->assertSame("Đã sửa xong & hoàn thiện   các mục.", HtmlHelper::toPlainText($htmlWithEntities));
    }

    /**
     * Test HtmlHelper::renderRichText cleans XSS and handles entity-encoded tags
     */
    public function test_html_helper_render_rich_text(): void
    {
        // Malicious script tag removed
        $xss = '<p>An toàn</p><script>alert("XSS")</script>';
        $cleaned = HtmlHelper::renderRichText($xss);
        $this->assertStringNotContainsString('<script>', $cleaned);
        $this->assertStringContainsString('An toàn', $cleaned);

        // Malicious event handler removed
        $xssAttr = '<p onclick="alert(1)">Nội dung</p>';
        $cleanedAttr = HtmlHelper::renderRichText($xssAttr);
        $this->assertStringNotContainsString('onclick', $cleanedAttr);

        // Entity encoded html tags decoded and rendered as html
        $encoded = '&lt;p&gt;44r534dg&lt;/p&gt;';
        $rendered = HtmlHelper::renderRichText($encoded);
        $this->assertSame('<p>44r534dg</p>', $rendered);

        // Plain text with newlines converted to paragraphs / breaks
        $plain = "Đoạn 1\nĐoạn 2";
        $renderedPlain = HtmlHelper::renderRichText($plain);
        $this->assertStringContainsString('Đoạn 1', $renderedPlain);
        $this->assertStringContainsString('Đoạn 2', $renderedPlain);
    }

    public function test_only_advisor_can_confirm_and_release_the_submitted_report(): void
    {
        $admin = TaiKhoan::whereHas('vaiTro', fn($role) => $role->whereIn('TenVaiTro', ['Admin', 'Giáo vụ']))->firstOrFail();
        $admin->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $detail = route('admin.baocao_sau_chamdiem.show', $this->hoSo->MaHoSo);
        $list = route('admin.baocao_sau_chamdiem.index', ['search' => $this->hoSo->MaHoSo]);
        $deanRole = \App\Models\VaiTro::where('TenVaiTro', 'Trưởng khoa')->firstOrFail();
        $dean = TaiKhoan::create([
            'MaTK' => 'TK_DEAN_' . bin2hex(random_bytes(4)),
            'TenDangNhap' => 'TK_' . $this->hoSo->deTai->giangVien->boMon->MaKhoa . '_' . bin2hex(random_bytes(4)),
            'MatKhau' => bcrypt('123456'),
            'MaVaiTro' => $deanRole->MaVaiTro,
            'TrangThai' => true,
            'TrangThaiMatKhau' => 'ACTIVE',
            'BatBuocDoiMatKhau' => false,
        ]);
        $deanDetail = route('truongkhoa.baocao_sau_chamdiem.show', $this->hoSo->MaHoSo);
        $deanList = route('truongkhoa.baocao_sau_chamdiem.index', ['search' => $this->hoSo->MaHoSo]);
        $preview = route('baocao_sau_baove.pdf', $this->baoCao->id);
        $confirm = route('giangvien.kiemtra_baocao_sau_baove.xacnhan', $this->hoSo->MaHoSo);
        $data = ['bao_cao_id' => $this->baoCao->id];

        $this->actingAs($admin)->get($list)->assertOk()->assertDontSee($detail, false);
        $this->get($detail)->assertNotFound();
        $this->get($preview)->assertForbidden();
        $this->actingAs($dean)->get($deanList)->assertOk()->assertDontSee($deanDetail, false);
        $this->get($deanDetail)->assertNotFound();

        $this->hoSo->update(['NguoiKiemTraChinhSua' => $this->unauthorizedUser->giangVien->MaGV]);
        $this->actingAs($this->unauthorizedUser)->post($confirm, $data)->assertForbidden();
        $this->assertSame('Đã nộp', $this->baoCao->fresh()->TrangThai);

        $teacherPage = route('giangvien.kiemtra_baocao_sau_baove.show', $this->hoSo->MaHoSo);
        $this->actingAs($this->lecturerUser)->get($teacherPage)->assertOk()->assertSee($confirm, false);
        $this->post($confirm, ['bao_cao_id' => $this->baoCao->id + 1])->assertStatus(409);
        $this->from($teacherPage)->post($confirm, $data)->assertRedirect($teacherPage);
        $this->assertSame('Đạt', $this->baoCao->fresh()->TrangThai);
        $this->assertSame('DaHoanThanh', $this->hoSo->fresh()->TrangThaiChinhSuaSauBaoVe);

        $saved = $this->baoCao->fresh()->getAttributes();
        $this->post($confirm, $data)->assertRedirect();
        $this->assertSame($saved, $this->baoCao->fresh()->getAttributes());

        $this->actingAs($admin)->get($list)->assertOk()->assertSee($detail, false);
        $this->get($detail)->assertOk()->assertSee($this->baoCao->TenFile);
        $this->actingAs($dean)->get($deanList)->assertOk()->assertSee($deanDetail, false);
        $this->get($deanDetail)->assertOk()->assertSee($this->baoCao->TenFile);
        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($admin)
            ->allows('viewPostDefenseReport', [$this->hoSo->fresh(), $this->baoCao->fresh()]));
    }

    /**
     * Test inline PDF preview and download endpoints with authorization
     */
    public function test_pdf_view_and_download_endpoints(): void
    {
        // 1. Authorized lecturer can preview PDF inline
        $previewRes = $this->actingAs($this->lecturerUser)
            ->get(route('baocao_sau_baove.pdf', $this->baoCao->id));

        $previewRes->assertOk();
        $disposition = $previewRes->headers->get('Content-Disposition');
        $this->assertStringStartsWith('inline', $disposition);

        // 2. Authorized lecturer can download PDF attachment
        $downloadRes = $this->actingAs($this->lecturerUser)
            ->get(route('baocao_sau_baove.download', $this->baoCao->id));

        $downloadRes->assertOk();
        $downloadDisposition = $downloadRes->headers->get('Content-Disposition');
        $this->assertStringStartsWith('attachment', $downloadDisposition);

        // 3. Authorized student in the group can view the file
        $studentRes = $this->actingAs($this->studentUser)
            ->get(route('baocao_sau_baove.pdf', $this->baoCao->id));
        $studentRes->assertOk();

        // 4. Unauthorized user is forbidden (403)
        $unauthRes = $this->actingAs($this->unauthorizedUser)
            ->get(route('baocao_sau_baove.pdf', $this->baoCao->id));
        $unauthRes->assertForbidden();

        $unauthDownload = $this->actingAs($this->unauthorizedUser)
            ->get(route('baocao_sau_baove.download', $this->baoCao->id));
        $unauthDownload->assertForbidden();

        // 5. Non-existent report returns 404
        $notFoundRes = $this->actingAs($this->lecturerUser)
            ->get(route('baocao_sau_baove.pdf', 999999));
        $notFoundRes->assertNotFound();
    }
}
