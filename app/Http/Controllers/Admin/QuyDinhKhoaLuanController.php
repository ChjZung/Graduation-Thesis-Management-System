<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuyDinhKhoaLuan;
use App\Models\KeHoachKhoaLuan;
use App\Models\HocKy;
use App\Services\PlanPhaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuyDinhKhoaLuanController extends Controller
{
    public function index(Request $request)
    {
        $keHoachs = KeHoachKhoaLuan::with('hocKy')->orderBy('created_at', 'desc')->get();

        // Chỉ nạp 8 Quy định Chuẩn nếu đã có Kế hoạch Khóa luận nhưng kế hoạch đó chưa có quy định
        // TUYỆT ĐỐI không tự động tạo Kế hoạch mới khi người dùng chưa bấm tạo hoặc chưa xác nhận import!
        if ($keHoachs->isNotEmpty()) {
            $targetPlan = $keHoachs->first();
            $hasRules = QuyDinhKhoaLuan::where('MakeHoach', $targetPlan->MakeHoach)->exists();
            if (!$hasRules) {
                $defaultRules = PlanPhaseService::getDefaultRegulations();
                foreach ($defaultRules as $idx => $r) {
                    $cleanSuffix = preg_replace('/[^A-Za-z0-9]/', '', $targetPlan->MakeHoach);
                    $maQD = 'QD_' . substr($cleanSuffix, -4) . '_' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
                    QuyDinhKhoaLuan::create([
                        'MaQuyDinh'  => substr($maQD, 0, 20),
                        'TenQuyDinh' => $r['TenQuyDinh'],
                        'GiaTri'     => $r['GiaTri'],
                        'MoTa'       => $r['MoTa'],
                        'MakeHoach'  => $targetPlan->MakeHoach,
                    ]);
                }
            }
        }

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
        $soKeHoachApDung = QuyDinhKhoaLuan::distinct('MakeHoach')->count('MakeHoach');

        $stats = [
            'total_quydinh'   => $totalQD,
            'so_kehoach'      => $soKeHoachApDung,
            'tc_chuan'        => '115 Tín chỉ',
            'gpa_chuan'       => '2.0 GPA',
        ];

        $quyDinhs = $query->orderBy('created_at', 'asc')->paginate(12)->withQueryString();

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
        $defaultRules = PlanPhaseService::getDefaultRegulations();

        $count = 0;
        foreach ($defaultRules as $idx => $r) {
            $cleanSuffix = preg_replace('/[^A-Za-z0-9]/', '', $maKH);
            $maQD = 'QD_' . substr($cleanSuffix, -4) . '_' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT);

            $ruleData = [
                'MaQuyDinh'  => substr($maQD, 0, 20),
                'TenQuyDinh' => $r['TenQuyDinh'],
                'GiaTri'     => $r['GiaTri'],
                'MoTa'       => $r['MoTa'],
                'MakeHoach'  => $maKH,
            ];

            // Nếu đã tồn tại thì cập nhật, chưa có thì tạo mới
            $existing = QuyDinhKhoaLuan::where('MakeHoach', $maKH)
                                       ->where('TenQuyDinh', $r['TenQuyDinh'])
                                       ->first();
            if ($existing) {
                $existing->update([
                    'GiaTri' => $r['GiaTri'],
                    'MoTa'   => $r['MoTa'],
                ]);
            } else {
                QuyDinhKhoaLuan::create($ruleData);
                $count++;
            }
        }

        return redirect()->route('admin.quydinh.index', ['make_hoach' => $maKH])
                         ->with('success', "Đã đồng bộ thành công 8 quy định & tiêu chuẩn chuẩn cho Kế hoạch khóa luận!");
    }
}
