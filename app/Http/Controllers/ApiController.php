<?php

namespace App\Http\Controllers;

use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\Lop;
use App\Models\MonHoc;
use App\Models\Nganh;
use App\Models\Nhom;
use App\Models\SinhVien;
use App\Models\ThongBao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * RESTful API Controller
 * 
 * Cung cấp các JSON Endpoints chuẩn REST cho hệ thống
 * Đáp ứng tiêu chí: API / JSON Endpoints (2.5 điểm)
 * 
 * Danh sách endpoints:
 *   GET  /api/detais          - Danh sách đề tài
 *   GET  /api/detais/{id}     - Chi tiết 1 đề tài
 *   GET  /api/nhoms           - Danh sách nhóm đồ án
 *   GET  /api/nhoms/{id}      - Chi tiết 1 nhóm đồ án
 *   GET  /api/sinhviens       - Danh sách sinh viên
 *   GET  /api/giangviens      - Danh sách giảng viên
 *   GET  /api/lops            - Danh sách lớp
 *   GET  /api/hockys          - Danh sách học kỳ
 *   GET  /api/bomons          - Danh sách bộ môn
 *   GET  /api/nganhs          - Danh sách ngành
 *   GET  /api/thongbaos       - Danh sách thông báo
 *   GET  /api/thongke         - Thống kê tổng quan hệ thống
 *   POST /api/detais          - Tạo đề tài mới
 *   PUT  /api/detais/{id}     - Cập nhật đề tài
 *   DELETE /api/detais/{id}   - Xóa đề tài
 */
class ApiController extends Controller
{
    // =========================================================
    // ĐỀ TÀI ENDPOINTS
    // =========================================================

    /**
     * GET /api/detais
     * Lấy danh sách tất cả đề tài kèm thông tin Giảng viên hướng dẫn.
     */
    public function getDeTais(Request $request)
    {
        $query = DeTai::with('giangVien:MaGV,HoTen,MaTK');

        // Hỗ trợ tìm kiếm theo tên đề tài
        if ($request->has('search')) {
            $query->where('TenDeTai', 'like', '%' . $request->search . '%');
        }

        // Hỗ trợ phân trang
        $perPage = $request->input('per_page', 0);
        if ($perPage > 0) {
            $detais = $query->paginate($perPage);
        } else {
            $detais = $query->get();
        }

        return response()->json([
            'status' => 'success',
            'count' => $detais instanceof \Illuminate\Pagination\LengthAwarePaginator ? $detais->total() : $detais->count(),
            'data' => $detais
        ], 200);
    }

    /**
     * GET /api/detais/{id}
     * Lấy chi tiết 1 đề tài theo MaDeTai.
     */
    public function getDeTaiDetail($id)
    {
        $detai = DeTai::with(['giangVien:MaGV,HoTen,MaTK'])
            ->find($id);

        if (!$detai) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy đề tài với mã ' . $id
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $detai
        ], 200);
    }

    /**
     * POST /api/detais
     * Tạo đề tài mới qua API.
     */
    public function storeDeTai(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'TenDeTai' => 'required|string|max:255',
            'MoTa' => 'nullable|string',
            'MaGV' => 'required|string|exists:giang_viens,MaGV',
            'MaHocKy' => 'required|string|exists:hoc_kies,MaHocKy',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors()
            ], 422);
        }

        $detai = DeTai::create($request->only(['TenDeTai', 'MoTa', 'MaGV', 'MaHocKy']));

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo đề tài thành công.',
            'data' => $detai
        ], 201);
    }

    /**
     * PUT /api/detais/{id}
     * Cập nhật đề tài.
     */
    public function updateDeTai(Request $request, $id)
    {
        $detai = DeTai::find($id);

        if (!$detai) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy đề tài với mã ' . $id
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'TenDeTai' => 'sometimes|required|string|max:255',
            'MoTa' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors()
            ], 422);
        }

        $detai->update($request->only(['TenDeTai', 'MoTa']));

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật đề tài thành công.',
            'data' => $detai->fresh()
        ], 200);
    }

    /**
     * DELETE /api/detais/{id}
     * Xóa đề tài.
     */
    public function destroyDeTai($id)
    {
        $detai = DeTai::find($id);

        if (!$detai) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy đề tài với mã ' . $id
            ], 404);
        }

        try {
            $detai->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Xóa đề tài thành công.'
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể xóa đề tài do có dữ liệu liên quan.'
            ], 409);
        }
    }

    // =========================================================
    // NHÓM ĐỒ ÁN ENDPOINTS
    // =========================================================

    /**
     * GET /api/nhoms
     * Lấy danh sách nhóm đồ án.
     */
    public function getNhoms()
    {
        $nhoms = Nhom::all();

        return response()->json([
            'status' => 'success',
            'count' => $nhoms->count(),
            'data' => $nhoms
        ], 200);
    }

    /**
     * GET /api/nhoms/{id}
     * Lấy chi tiết 1 nhóm đồ án.
     */
    public function getNhomDetail($id)
    {
        $nhom = Nhom::find($id);

        if (!$nhom) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy nhóm đồ án với mã ' . $id
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $nhom
        ], 200);
    }

    // =========================================================
    // DANH MỤC ENDPOINTS (Read-Only)
    // =========================================================

    /**
     * GET /api/sinhviens
     */
    public function getSinhViens()
    {
        $data = SinhVien::with('lop:MaLop,TenLop')->get();
        return response()->json(['status' => 'success', 'count' => $data->count(), 'data' => $data], 200);
    }

    /**
     * GET /api/giangviens
     */
    public function getGiangViens()
    {
        $data = GiangVien::with('boMon:MaBoMon,TenBoMon')->get();
        return response()->json(['status' => 'success', 'count' => $data->count(), 'data' => $data], 200);
    }

    /**
     * GET /api/lops
     */
    public function getLops()
    {
        $data = Lop::with('nganh:MaNganh,TenNganh')->get();
        return response()->json(['status' => 'success', 'count' => $data->count(), 'data' => $data], 200);
    }

    /**
     * GET /api/hockys
     */
    public function getHocKys()
    {
        $data = HocKy::all();
        return response()->json(['status' => 'success', 'count' => $data->count(), 'data' => $data], 200);
    }

    /**
     * GET /api/bomons
     */
    public function getBoMons()
    {
        $data = BoMon::all();
        return response()->json(['status' => 'success', 'count' => $data->count(), 'data' => $data], 200);
    }

    /**
     * GET /api/nganhs
     */
    public function getNganhs()
    {
        $data = Nganh::all();
        return response()->json(['status' => 'success', 'count' => $data->count(), 'data' => $data], 200);
    }

    /**
     * GET /api/thongbaos
     */
    public function getThongBaos()
    {
        $data = ThongBao::orderByDesc('created_at')->limit(50)->get();
        return response()->json(['status' => 'success', 'count' => $data->count(), 'data' => $data], 200);
    }

    // =========================================================
    // THỐNG KÊ ENDPOINT
    // =========================================================

    /**
     * GET /api/thongke
     * Thống kê tổng quan hệ thống.
     */
    public function getThongKe()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'so_sinh_vien' => SinhVien::count(),
                'so_giang_vien' => GiangVien::count(),
                'so_de_tai' => DeTai::count(),
                'so_nhom' => Nhom::count(),
                'so_lop' => Lop::count(),
                'so_hoc_ky' => HocKy::count(),
                'so_bo_mon' => BoMon::count(),
                'so_nganh' => Nganh::count(),
            ]
        ], 200);
    }
}

