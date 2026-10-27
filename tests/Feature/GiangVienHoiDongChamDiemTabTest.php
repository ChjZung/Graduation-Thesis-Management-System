<?php

namespace Tests\Feature;

use App\Models\GiangVien;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\TaiKhoan;
use App\Models\ThanhVienHoiDong;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GiangVienHoiDongChamDiemTabTest extends TestCase
{
    use DatabaseTransactions;

    protected function getLecturerUser(): array
    {
        $dossier = HoSoBaoVe::whereNotNull('MaHoiDong')->first();
        if ($dossier) {
            $tv = ThanhVienHoiDong::where('MaHoiDong', $dossier->MaHoiDong)->first();
            if ($tv) {
                $gv = GiangVien::find($tv->MaGV);
                $user = TaiKhoan::where('MaTK', $gv?->MaTK)->first();
                if ($user) {
                    return [$gv, $user, $dossier];
                }
            }
        }

        $gv = GiangVien::whereHas('taiKhoan')->firstOrFail();
        $user = TaiKhoan::where('MaTK', $gv->MaTK)->firstOrFail();
        return [$gv, $user, null];
    }

    public function test_route_chamdiem_index_redirects_to_hoidong_with_tab_chamdiem(): void
    {
        [$gv, $user] = $this->getLecturerUser();

        $response = $this->actingAs($user)->get(route('giangvien.chamdiem.index'));

        $response->assertStatus(302);
        $response->assertRedirect(route('giangvien.hoidong.index', ['tab' => 'chamdiem']));
    }

    public function test_route_chamdiem_index_preserves_query_parameters_on_redirect(): void
    {
        [$gv, $user] = $this->getLecturerUser();

        $response = $this->actingAs($user)->get(route('giangvien.chamdiem.index', [
            'hd'   => 'HD01',
            'hoso' => 'HS01',
            'loai' => 'KLCN',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect(route('giangvien.hoidong.index', [
            'hd'   => 'HD01',
            'hoso' => 'HS01',
            'loai' => 'KLCN',
            'tab'  => 'chamdiem',
        ]));
    }

    public function test_giangvien_hoidong_page_renders_unified_tabs_and_list(): void
    {
        [$gv, $user] = $this->getLecturerUser();

        $response = $this->actingAs($user)->get(route('giangvien.hoidong.index'));

        $response->assertOk();
        $response->assertSee('nav-tabs-huit', false);
        $response->assertSee('id="hoiDongUnifiedTabs"', false);
        $response->assertSee('id="tab-btn-danhsach"', false);
        $response->assertSee('id="tab-btn-chamdiem"', false);
        $response->assertSee('id="pane-danhsach"', false);
        $response->assertSee('id="pane-chamdiem"', false);
        $response->assertSee('switchHoiDongTab(\'chamdiem\')', false);

        // Sidebar không có mục "Chấm điểm hội đồng" trong menu Đánh Giá & Báo Cáo
        // Menu item Hội đồng của tôi phải tồn tại
        $response->assertSee('Hội đồng của tôi');
        $content = $response->getContent();
        $this->assertDoesNotMatchRegularExpression(
            '#<a[^>]*sidebar-link[^>]*href=["\'][^"\']*giangvien/chamdiem["\'][^>]*>#i',
            $content,
            'Sidebar của giảng viên không được chứa liên kết menu Chấm điểm hội đồng.'
        );
    }

    public function test_giangvien_hoidong_page_activates_chamdiem_tab_when_tab_param_provided(): void
    {
        [$gv, $user, $dossier] = $this->getLecturerUser();

        $url = route('giangvien.hoidong.index', ['tab' => 'chamdiem']);
        if ($dossier) {
            $url = route('giangvien.hoidong.index', [
                'tab'  => 'chamdiem',
                'hd'   => $dossier->MaHoiDong,
                'hoso' => $dossier->MaHoSo,
            ]);
        }

        $response = $this->actingAs($user)->get($url);

        $response->assertOk();
        $response->assertSee('id="pane-chamdiem"', false);
        $response->assertSee('tab-pane fade show active" id="pane-chamdiem"', false);
    }
}
