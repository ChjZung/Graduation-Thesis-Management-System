<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Kiểm thử toàn bộ RESTful API Endpoints của hệ thống.
 * Đáp ứng tiêu chí: Kiểm thử PHPUnit (1.0 điểm - CLO2)
 */
class ApiTest extends TestCase
{
    // =========================================================
    // ĐỀ TÀI API TESTS
    // =========================================================

    /**
     * Test API lấy danh sách đề tài trả về JSON chuẩn.
     */
    public function test_api_get_detais_returns_json()
    {
        $response = $this->getJson('/api/detais');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'count',
                     'data'
                 ]);
    }

    /**
     * Test API hỗ trợ tìm kiếm đề tài theo tên.
     */
    public function test_api_search_detais_by_name()
    {
        $response = $this->getJson('/api/detais?search=quản+lý');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'count', 'data']);
    }

    /**
     * Test API trả về lỗi 404 khi đề tài không tồn tại.
     */
    public function test_api_get_detai_detail_returns_404_for_invalid_id()
    {
        $response = $this->getJson('/api/detais/99999');

        $response->assertStatus(404)
                 ->assertJson(['status' => 'error']);
    }

    /**
     * Test API tạo đề tài thất bại khi thiếu dữ liệu bắt buộc (Validation 422).
     */
    public function test_api_create_detai_fails_with_missing_data()
    {
        $response = $this->postJson('/api/detais', []);

        $response->assertStatus(422)
                 ->assertJson(['status' => 'error'])
                 ->assertJsonStructure(['errors']);
    }

    /**
     * Test API cập nhật đề tài thất bại khi ID không tồn tại.
     */
    public function test_api_update_detai_returns_404_for_invalid_id()
    {
        $response = $this->putJson('/api/detais/99999', [
            'TenDeTai' => 'Test Update'
        ]);

        $response->assertStatus(404)
                 ->assertJson(['status' => 'error']);
    }

    /**
     * Test API xóa đề tài thất bại khi ID không tồn tại.
     */
    public function test_api_delete_detai_returns_404_for_invalid_id()
    {
        $response = $this->deleteJson('/api/detais/99999');

        $response->assertStatus(404)
                 ->assertJson(['status' => 'error']);
    }

    // =========================================================
    // NHÓM ĐỒ ÁN API TESTS
    // =========================================================

    /**
     * Test API lấy danh sách nhóm đồ án.
     */
    public function test_api_get_nhoms_returns_json()
    {
        $response = $this->getJson('/api/nhoms');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'count', 'data']);
    }

    /**
     * Test API lấy chi tiết nhóm đồ án không tồn tại.
     */
    public function test_api_get_nhom_detail_returns_404_for_invalid_id()
    {
        $response = $this->getJson('/api/nhoms/99999');

        $response->assertStatus(404)
                 ->assertJson(['status' => 'error']);
    }

    // =========================================================
    // DANH MỤC API TESTS
    // =========================================================

    /**
     * Test API lấy danh sách sinh viên.
     */
    public function test_api_get_sinhviens_returns_json()
    {
        $response = $this->getJson('/api/sinhviens');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'count', 'data']);
    }

    /**
     * Test API lấy danh sách giảng viên.
     */
    public function test_api_get_giangviens_returns_json()
    {
        $response = $this->getJson('/api/giangviens');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'count', 'data']);
    }

    /**
     * Test API lấy danh sách lớp.
     */
    public function test_api_get_lops_returns_json()
    {
        $response = $this->getJson('/api/lops');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'count', 'data']);
    }

    /**
     * Test API lấy danh sách học kỳ.
     */
    public function test_api_get_hockys_returns_json()
    {
        $response = $this->getJson('/api/hockys');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'count', 'data']);
    }

    /**
     * Test API lấy danh sách bộ môn.
     */
    public function test_api_get_bomons_returns_json()
    {
        $response = $this->getJson('/api/bomons');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'count', 'data']);
    }

    /**
     * Test API lấy danh sách ngành.
     */
    public function test_api_get_nganhs_returns_json()
    {
        $response = $this->getJson('/api/nganhs');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'count', 'data']);
    }

    /**
     * Test API lấy danh sách thông báo.
     */
    public function test_api_get_thongbaos_returns_json()
    {
        $response = $this->getJson('/api/thongbaos');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'count', 'data']);
    }

    // =========================================================
    // THỐNG KÊ API TESTS
    // =========================================================

    /**
     * Test API thống kê tổng quan hệ thống.
     */
    public function test_api_get_thongke_returns_statistics()
    {
        $response = $this->getJson('/api/thongke');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'so_sinh_vien',
                         'so_giang_vien',
                         'so_de_tai',
                         'so_nhom',
                         'so_lop',
                         'so_hoc_ky',
                         'so_bo_mon',
                         'so_nganh',
                     ]
                 ]);
    }
}

