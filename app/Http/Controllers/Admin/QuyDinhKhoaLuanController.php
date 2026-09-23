<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuyDinhKhoaLuan;
use App\Models\KeHoachKhoaLuan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuyDinhKhoaLuanController extends Controller
{
    public function index(Request $request)
    {
        $query = QuyDinhKhoaLuan::with('keHoachKhoaLuan.hocKy');

        if ($request->filled('make_hoach')) {
            $query->where('MakeHoach', $request->make_hoach);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('TenQuyDinh', 'LIKE', "%{$s}%")
                  ->orWhere('MaQuyDinh', 'LIKE', "%{$s}%")
                  ->orWhere('GiaTri', 'LIKE', "%{$s}%");
            });
        }

        $totalQD = QuyDinhKhoaLuan::count();
        $keHoachs = KeHoachKhoaLuan::with('hocKy')->orderBy('created_at', 'desc')->get();
        $soKeHoachApDung = QuyDinhKhoaLuan::distinct('MakeHoach')->count('MakeHoach');

        $stats = [
            'total_quydinh'   => $totalQD,
            'so_kehoach'      => $soKeHoachApDung,
            'tc_chuan'        => '115 Tín chỉ',
            'gpa_chuan'       => '2.0 GPA',
        ];

        $quyDinhs = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.quydinh.index', compact('quyDinhs', 'keHoachs', 'stats'));
    }

    public function create()
    {
        $keHoachs = KeHoachKhoaLuan::with('hocKy')->orderBy('created_at', 'desc')->get();
        return view('admin.quydinh.create', compact('keHoachs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenQuyDinh' => 'required|string|max:200',
            'GiaTri'     => 'required|string|max:255',
            'MakeHoach'  => 'required|exists:KeHoachKhoaLuan,MakeHoach',
            'MoTa'       => 'nullable|string',
        ], [
            'TenQuyDinh.required' => 'Vui lòng nhập tên quy định.',
            'GiaTri.required'     => 'Vui lòng nhập giá trị tham số quy định.',
            'MakeHoach.required'  => 'Vui lòng chọn Kế hoạch khóa luận áp dụng.',
        ]);

        $maQD = $request->filled('MaQuyDinh') ? strtoupper(trim($request->MaQuyDinh)) : 'QD_' . Str::upper(Str::random(6));

        QuyDinhKhoaLuan::create([
            'MaQuyDinh'  => $maQD,
            'TenQuyDinh' => trim($request->TenQuyDinh),
            'GiaTri'     => trim($request->GiaTri),
            'MoTa'       => $request->MoTa ? trim($request->MoTa) : null,
            'MakeHoach'  => $request->MakeHoach,
        ]);

        return redirect()->route('admin.quydinh.index', ['make_hoach' => $request->MakeHoach])
                         ->with('success', "Thêm quy định '{$request->TenQuyDinh}' thành công!");
    }

    public function show($id)
    {
        $quyDinh = QuyDinhKhoaLuan::with('keHoachKhoaLuan.hocKy')->findOrFail($id);
        return view('admin.quydinh.show', compact('quyDinh'));
    }

    public function edit($id)
    {
        $quyDinh = QuyDinhKhoaLuan::findOrFail($id);
        $keHoachs = KeHoachKhoaLuan::with('hocKy')->orderBy('created_at', 'desc')->get();
        return view('admin.quydinh.edit', compact('quyDinh', 'keHoachs'));
    }

    public function update(Request $request, $id)
    {
        $quyDinh = QuyDinhKhoaLuan::findOrFail($id);

        $request->validate([
            'TenQuyDinh' => 'required|string|max:200',
            'GiaTri'     => 'required|string|max:255',
            'MakeHoach'  => 'required|exists:KeHoachKhoaLuan,MakeHoach',
            'MoTa'       => 'nullable|string',
        ]);

        $quyDinh->update([
            'TenQuyDinh' => trim($request->TenQuyDinh),
            'GiaTri'     => trim($request->GiaTri),
            'MoTa'       => $request->MoTa ? trim($request->MoTa) : null,
            'MakeHoach'  => $request->MakeHoach,
        ]);

        return redirect()->route('admin.quydinh.index', ['make_hoach' => $request->MakeHoach])
                         ->with('success', "Cập nhật quy định '{$quyDinh->TenQuyDinh}' thành công!");
    }

    public function destroy($id)
    {
        $quyDinh = QuyDinhKhoaLuan::findOrFail($id);
        $makeHoach = $quyDinh->MakeHoach;
        $quyDinh->delete();

        return redirect()->route('admin.quydinh.index', ['make_hoach' => $makeHoach])
                         ->with('success', "Đã xóa quy định '{$quyDinh->TenQuyDinh}' thành công!");
    }

    /**
     * Khởi tạo bộ quy định mẫu chuẩn HUIT cho 1 Kế hoạch khóa luận
     */
    public function initDefaults(Request $request)
    {
        $request->validate([
            'MakeHoach' => 'required|exists:KeHoachKhoaLuan,MakeHoach',
        ], [
            'MakeHoach.required' => 'Vui lòng chọn Kế hoạch khóa luận cần áp dụng.',
        ]);

        $maKH = $request->MakeHoach;

        $defaultRules = [
            [
                'MaQuyDinh'  => 'QD_TIN_CHI_' . Str::upper(Str::random(4)),
                'TenQuyDinh' => 'Số tín chỉ tích lũy tối thiểu làm KLTN',
                'GiaTri'     => '115 Tín chỉ',
                'MoTa'       => 'Sinh viên phải tích lũy tối thiểu 115 tín chỉ và không nợ các môn điều kiện tiên quyết.',
                'MakeHoach'  => $maKH,
            ],
            [
                'MaQuyDinh'  => 'QD_GPA_' . Str::upper(Str::random(4)),
                'TenQuyDinh' => 'Điểm trung bình tích lũy tối thiểu (GPA)',
                'GiaTri'     => '2.0 GPA',
                'MoTa'       => 'Điểm trung bình tích lũy thang điểm 4.0 đạt từ 2.0 trở lên tại thời điểm xét duyệt.',
                'MakeHoach'  => $maKH,
            ],
            [
                'MaQuyDinh'  => 'QD_MAX_SV_' . Str::upper(Str::random(4)),
                'TenQuyDinh' => 'Số lượng sinh viên tối đa trong một nhóm',
                'GiaTri'     => '2 Sinh viên',
                'MoTa'       => 'Mỗi nhóm khóa luận tối đa 2 sinh viên (trừ trường hợp đặc biệt được Trưởng khoa phê duyệt).',
                'MakeHoach'  => $maKH,
            ],
            [
                'MaQuyDinh'  => 'QD_MAX_DETAI_' . Str::upper(Str::random(4)),
                'TenQuyDinh' => 'Định mức đề tài tối đa một giảng viên hướng dẫn',
                'GiaTri'     => '5 Đề tài',
                'MoTa'       => 'Mỗi giảng viên hướng dẫn tối đa 5 đề tài/nhóm trong một học kỳ để đảm bảo chất lượng.',
                'MakeHoach'  => $maKH,
            ],
            [
                'MaQuyDinh'  => 'QD_TURNITIN_' . Str::upper(Str::random(4)),
                'TenQuyDinh' => 'Ngưỡng trùng lặp kiểm tra Turnitin tối đa',
                'GiaTri'     => '<= 20%',
                'MoTa'       => 'Báo cáo toàn văn quét qua hệ thống Turnitin có độ trùng lặp không được vượt quá 20%.',
                'MakeHoach'  => $maKH,
            ],
            [
                'MaQuyDinh'  => 'QD_DIEM_DAT_' . Str::upper(Str::random(4)),
                'TenQuyDinh' => 'Điểm tổng kết tối thiểu để đạt Khóa luận',
                'GiaTri'     => '>= 5.0 Điểm',
                'MoTa'       => 'Điểm tổng kết bảo vệ theo trọng số (GVHD 30%, GVPB 30%, HĐ 40%) phải đạt từ 5.0 trở lên.',
                'MakeHoach'  => $maKH,
            ],
        ];

        $count = 0;
        foreach ($defaultRules as $rule) {
            // Không tạo trùng tên trong cùng kế hoạch
            $exists = QuyDinhKhoaLuan::where('MakeHoach', $maKH)
                                     ->where('TenQuyDinh', $rule['TenQuyDinh'])
                                     ->exists();
            if (!$exists) {
                QuyDinhKhoaLuan::create($rule);
                $count++;
            }
        }

        return redirect()->route('admin.quydinh.index', ['make_hoach' => $maKH])
                         ->with('success', "Đã khởi tạo thành công {$count} quy định chuẩn HUIT cho Kế hoạch!");
    }
}
