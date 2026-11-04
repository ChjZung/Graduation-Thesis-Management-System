<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\DeTai;
use App\Models\GiangVien;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DeTaiNopDeCuongFileFormatTest extends TestCase
{
    use DatabaseTransactions;

    public function test_giang_vien_can_submit_outline_with_docx_file()
    {
        Storage::fake('public');

        $detai = DeTai::where('MaDeTai', 'DT32')->first();
        $this->assertNotNull($detai, 'Khong tim thay de tai DT32');

        $gv = GiangVien::where('MaGV', $detai->MaGV)->first();
        $this->assertNotNull($gv, 'Khong tim thay GiangVien cua DT32');

        $user = TaiKhoan::where('MaTK', $gv->MaTK)->first();
        $this->assertNotNull($user, 'Khong tim thay TaiKhoan cua GiangVien');

        // Sử dụng template docx thật của hệ thống (file mà user đã download và upload trong screenshot)
        $templatePath = public_path('templates/Mau_De_Cuong_Khoa_Luan_Cu_Nhan.docx');
        $uploadedDocx = new UploadedFile(
            $templatePath,
            'Mau_De_Cuong_Khoa_Luan_Cu_Nhan_HUIT (1).docx',
            'application/octet-stream',
            null,
            true
        );

        $response = $this->actingAs($user)->post(route('giangvien.detai.nopDeCuong', $detai->MaDeTai), [
            'FileDeCuong' => $uploadedDocx,
        ]);

        // Phải redirect thành công và KHÔNG có lỗi validation về định dạng FileDeCuong
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('TaiLieuNop', [
            'MaDeTai' => 'DT32',
            'LoaiTaiLieu' => 'Đề cương chi tiết',
            'TenTaiLieu' => 'Mau_De_Cuong_Khoa_Luan_Cu_Nhan_HUIT (1).docx',
        ]);
    }
}
