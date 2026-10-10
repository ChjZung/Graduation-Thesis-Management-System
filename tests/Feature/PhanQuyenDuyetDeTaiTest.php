<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\DeTai;

class PhanQuyenDuyetDeTaiTest extends TestCase
{
    /**
     * TBM không được xem/thao tác đề tài ngoài Bộ môn mình phụ trách (phải trả về 403).
     */
    public function test_tbm_cannot_access_detai_of_another_bomon()
    {
        $userHTTT = TaiKhoan::where('TenDangNhap', 'TBM_BM_HTTT_GV00000003')->first();
        $this->assertNotNull($userHTTT, 'Tài khoản TBM_BM_HTTT_GV00000003 phải tồn tại.');

        $detaiCNPM = DeTai::where('MaDeTai', 'DT01')->first();
        $this->assertNotNull($detaiCNPM, 'Đề tài DT01 (thuộc BM_CNPM) phải tồn tại.');

        // TBM của BM_HTTT truy cập chi tiết đề tài DT01 của BM_CNPM
        $response = $this->actingAs($userHTTT)->get('/truongbomon/duyet-detai/' . $detaiCNPM->MaDeTai);
        $response->assertStatus(403);
    }

    /**
     * TBM được xem chi tiết đề tài thuộc đúng Bộ môn mình phụ trách.
     */
    public function test_tbm_can_access_detai_of_own_bomon()
    {
        $userCNPM = TaiKhoan::where('TenDangNhap', 'TBM_BM_CNPM_GV00000001')->first();
        $this->assertNotNull($userCNPM, 'Tài khoản TBM_BM_CNPM_GV00000001 phải tồn tại.');

        $detaiCNPM = DeTai::where('MaDeTai', 'DT01')->first();
        $this->assertNotNull($detaiCNPM, 'Đề tài DT01 (thuộc BM_CNPM) phải tồn tại.');

        $response = $this->actingAs($userCNPM)->get('/truongbomon/duyet-detai/' . $detaiCNPM->MaDeTai);
        $response->assertStatus(200);
    }

    /**
     * TK không được xem/thao tác đề tài ngoài Khoa mình phụ trách (phải trả về 403).
     */
    public function test_tk_cannot_access_detai_of_another_khoa()
    {
        $userTKATTT = TaiKhoan::where('TenDangNhap', 'TK_ATTT_GV00000004')->first();
        $this->assertNotNull($userTKATTT, 'Tài khoản TK_ATTT_GV00000004 phải tồn tại.');

        $detaiCNTT = DeTai::where('MaDeTai', 'DT01')->first(); // DT01 thuộc BM_CNPM -> Khoa CNTT
        $this->assertNotNull($detaiCNTT, 'Đề tài DT01 (Khoa CNTT) phải tồn tại.');

        // TK của Khoa ATTT truy cập đề tài của Khoa CNTT
        $response = $this->actingAs($userTKATTT)->get('/truongkhoa/duyet-detai/' . $detaiCNTT->MaDeTai);
        $response->assertStatus(403);
    }

    /**
     * TK được xem chi tiết đề tài thuộc đúng Khoa mình phụ trách.
     */
    public function test_tk_can_access_detai_of_own_khoa()
    {
        $userTKCNTT = TaiKhoan::where('TenDangNhap', 'TK_CNTT_GV00000002')->first();
        $this->assertNotNull($userTKCNTT, 'Tài khoản TK_CNTT_GV00000002 phải tồn tại.');

        $detaiCNTT = DeTai::where('MaDeTai', 'DT01')->first();
        $this->assertNotNull($detaiCNTT, 'Đề tài DT01 (Khoa CNTT) phải tồn tại.');

        $response = $this->actingAs($userTKCNTT)->get('/truongkhoa/duyet-detai/' . $detaiCNTT->MaDeTai);
        $response->assertStatus(200);
    }

    /**
     * TK không được phê duyệt đề tài khi đề tài chưa qua bước TBM duyệt.
     */
    public function test_tk_cannot_approve_topic_before_tbm_approval()
    {
        $userTKCNTT = TaiKhoan::where('TenDangNhap', 'TK_CNTT_GV00000002')->first();

        // Tạo 1 đề tài thử nghiệm đang ở trạng thái 'Chờ duyệt cấp Bộ môn'
        $detai = DeTai::first();
        $origStatus = $detai->TrangThai;
        $origNgayBM = $detai->NgayDuyetBM;

        $detai->update([
            'TrangThai' => 'Chờ duyệt cấp Bộ môn',
            'NgayDuyetBM' => null,
            'NgayDuyetKhoa' => null,
        ]);

        try {
            $response = $this->actingAs($userTKCNTT)->post('/truongkhoa/duyet-detai/' . $detai->MaDeTai . '/duyet');
            // Phải bị redirect về kèm lỗi session
            $response->assertSessionHasErrors();
            $this->assertEquals('Chờ duyệt cấp Bộ môn', $detai->fresh()->TrangThai);
        } finally {
            // Khôi phục lại trạng thái ban đầu của đề tài
            $detai->update([
                'TrangThai' => $origStatus,
                'NgayDuyetBM' => $origNgayBM,
            ]);
        }
    }

    /**
     * Luồng duyệt đa cấp chuẩn: Giảng viên đề xuất -> TBM duyệt -> TK duyệt, ghi nhận lịch sử đầy đủ.
     */
    public function test_luong_duyet_da_cap_tbm_roi_toi_tk_va_ghi_lich_su()
    {
        $userTBM = TaiKhoan::where('TenDangNhap', 'TBM_BM_CNPM_GV00000001')->first();
        $userTK = TaiKhoan::where('TenDangNhap', 'TK_CNTT_GV00000002')->first();
        $this->assertNotNull($userTBM);
        $this->assertNotNull($userTK);

        // Lấy 1 đề tài thuộc BM_CNPM
        $detai = DeTai::whereHas('giangVien', fn($q) => $q->where('MaBoMon', 'BM_CNPM'))->first();
        $this->assertNotNull($detai);

        $origStatus = $detai->TrangThai;
        $origNgayBM = $detai->NgayDuyetBM;
        $origNgayKhoa = $detai->NgayDuyetKhoa;

        try {
            // Bước 1: Đề tài ở trạng thái 'Chờ duyệt cấp Bộ môn'
            $detai->update([
                'TrangThai'     => 'Chờ duyệt cấp Bộ môn',
                'NgayDuyetBM'   => null,
                'NgayDuyetKhoa' => null,
            ]);

            // Bước 2: TBM phê duyệt
            $resTBM = $this->actingAs($userTBM)->post('/truong-bo-mon/phe-duyet-de-tai/de-tai/' . $detai->MaDeTai . '/duyet', [
                'GhiChu' => 'TBM đồng ý thông qua đề tài.',
            ]);
            $resTBM->assertSessionHas('success');

            $detaiFresh = $detai->fresh();
            $this->assertEquals('Chờ duyệt cấp Khoa', $detaiFresh->TrangThai);
            $this->assertNotNull($detaiFresh->NgayDuyetBM);

            // Kiểm tra có log lịch sử của TBM
            $this->assertDatabaseHas('ChiTietDuyetDeTai', [
                'MaDeTai'   => $detai->MaDeTai,
                'HanhDong'  => 'Phê duyệt',
                'TrangThai' => 'Chờ duyệt cấp Khoa',
            ]);

            // Bước 3: TK phê duyệt sau khi TBM đã duyệt
            $resTK = $this->actingAs($userTK)->post('/truongkhoa/duyet-detai/' . $detai->MaDeTai . '/duyet');
            $resTK->assertSessionHas('success');

            $detaiTKDone = $detai->fresh();
            $this->assertEquals('Đã công bố', $detaiTKDone->TrangThai);
            $this->assertNotNull($detaiTKDone->NgayDuyetKhoa);

            // Kiểm tra có log lịch sử của TK
            $this->assertDatabaseHas('ChiTietDuyetDeTai', [
                'MaDeTai'       => $detai->MaDeTai,
                'HanhDong'      => 'Phê duyệt',
                'TrangThai'     => 'Đã công bố',
            ]);
        } finally {
            // Khôi phục trạng thái ban đầu
            $detai->update([
                'TrangThai'     => $origStatus,
                'NgayDuyetBM'   => $origNgayBM,
                'NgayDuyetKhoa' => $origNgayKhoa,
            ]);
        }
    }
}

