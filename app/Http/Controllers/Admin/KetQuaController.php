<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HuongDan;

class KetQuaController extends Controller
{
    public function index()
    {
        // Lấy danh sách Hướng Dẫn (những nhóm đã có đề tài và giảng viên)
        // Kèm theo điểm số của nhóm đó
        $danhSach = HuongDan::with([
            'nhomDoAn.chamDiem',
            'nhomDoAn.sinhVienTruongNhom',
            'giangVien',
            'deTai'
        ])->paginate(15);

        return view('admin.ketqua.index', compact('danhSach'));
    }
}
