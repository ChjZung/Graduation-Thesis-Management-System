<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KeHoachKhoaLuan;
use App\Models\MocThoiGianKhoaLuan;
use App\Models\HocKy;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Services\PlanPhaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KeHoachKhoaLuanController extends Controller
{
    public function index(Request $request)
    {
        $query = KeHoachKhoaLuan::with(['hocKy', 'khoa', 'boMon', 'mocThoiGians']);

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai);
        }

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('TenKeHoach', 'LIKE', "%{$search}%")
                  ->orWhere('MaKeHoach', 'LIKE', "%{$search}%");
            });
        }

        $keHoachs = $query->orderBy('created_at', 'desc')->paginate(10);
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        return view('admin.kehoach.index', compact('keHoachs', 'hocKies'));
    }

    public function create()
    {
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $khoas = Khoa::orderBy('TenKhoa')->get();
        $boMons = BoMon::orderBy('TenBoMon')->get();
        $defaultPhases = PlanPhaseService::getDefaultPhases('2026-09-01');

        return view('admin.kehoach.create', compact('hocKies', 'khoas', 'boMons', 'defaultPhases'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenKeHoach'  => 'required|string|max:200',
            'MaHocKy'     => 'required|exists:hoc_kies,MaHocKy',
            'NamHoc'      => 'required|string',
            'NgayBatDau'  => 'required|date',
            'NgayKetThuc' => 'required|date|after:NgayBatDau',
            'mocs'        => 'required|array|min:1',
            'mocs.*.TenMoc'     => 'required|string|max:200',
            'mocs.*.NgayBatDau'  => 'required|date',
            'mocs.*.NgayKetThuc' => 'required|date|after_or_equal:mocs.*.NgayBatDau',
        ], [
            'TenKeHoach.required'   => 'Vui lòng nhập tên kế hoạch.',
            'MaHocKy.required'      => 'Vui lòng chọn học kỳ.',
            'NgayKetThuc.after'     => 'Ngày kết thúc của kế hoạch phải diễn ra sau ngày bắt đầu.',
            'mocs.*.NgayKetThuc.after_or_equal' => 'Ngày kết thúc của từng mốc phải lớn hơn hoặc bằng ngày bắt đầu.',
        ]);

        // Kiểm tra kế hoạch trùng lặp phạm vi trong cùng học kỳ
        $exists = KeHoachKhoaLuan::where('MaHocKy', $request->MaHocKy)
            ->where('NamHoc', $request->NamHoc)
            ->where('MaKhoa', $request->MaKhoa)
            ->where('TrangThai', '!=', 'HỦY')
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->withErrors('Đã tồn tại kế hoạch khóa luận trong cùng Học kỳ & Phạm vi nghiệp vụ.');
        }

        // Kiểm tra các mốc phải nằm trong khoảng ngày bắt đầu và kết thúc của kế hoạch
        foreach ($request->mocs as $idx => $m) {
            if ($m['NgayBatDau'] < $request->NgayBatDau || $m['NgayKetThuc'] > $request->NgayKetThuc) {
                return redirect()->back()->withInput()->withErrors("Mốc thứ " . ($idx + 1) . " ('{$m['TenMoc']}') phải nằm trong khoảng thời gian từ {$request->NgayBatDau} đến {$request->NgayKetThuc} của kế hoạch.");
            }
        }

        DB::transaction(function () use ($request) {
            $maKH = 'KH_' . Str::upper(Str::random(6));
            $user = auth()->user();

            $keHoach = KeHoachKhoaLuan::create([
                'MaKeHoach'   => $maKH,
                'MaKhoa'      => $request->MaKhoa ?: null,
                'MaBoMon'     => $request->MaBoMon ?: null,
                'MaHocKy'     => $request->MaHocKy,
                'NamHoc'      => $request->NamHoc,
                'MaGVu'       => $user ? $user->MaTK : 'GVU01',
                'NguoiLap'    => $user ? $user->MaTK : 'GVU01',
                'TenKeHoach'  => $request->TenKeHoach,
                'NoiDung'     => $request->NoiDung,
                'NgayBatDau'  => $request->NgayBatDau,
                'NgayKetThuc' => $request->NgayKetThuc,
                'TrangThai'   => $request->action === 'publish' ? 'ĐÃ CÔNG BỐ' : 'NHÁP',
                'NgayTao'     => now(),
                'NgayCongBo'  => $request->action === 'publish' ? now() : null,
            ]);

            foreach ($request->mocs as $index => $mocData) {
                $maMoc = 'MOC_' . ($index + 1) . '_' . Str::upper(Str::random(4));
                MocThoiGianKhoaLuan::create([
                    'MaMoc'           => $maMoc,
                    'MaKeHoach'       => $maKH,
                    'LoaiGiaiDoan'    => $mocData['LoaiGiaiDoan'] ?? 'THUC_HIEN',
                    'ThuTu'           => $index + 1,
                    'TenMoc'          => $mocData['TenMoc'],
                    'DoiTuongThucHien'=> $mocData['DoiTuongThucHien'] ?? 'Tất cả',
                    'NgayBatDau'      => $mocData['NgayBatDau'],
                    'NgayKetThuc'     => $mocData['NgayKetThuc'],
                    'MoTa'            => $mocData['MoTa'] ?? null,
                    'BatBuoc'         => isset($mocData['BatBuoc']) ? (bool)$mocData['BatBuoc'] : true,
                ]);
            }
        });

        return redirect()->route('admin.kehoach.index')->with('success', 'Tạo Kế hoạch Khóa luận mới thành công!');
    }

    public function show($id)
    {
        $keHoach = KeHoachKhoaLuan::with(['hocKy', 'khoa', 'boMon', 'mocThoiGians'])->findOrFail($id);
        $currentPhase = PlanPhaseService::getCurrentPhase($keHoach);

        return view('admin.kehoach.show', compact('keHoach', 'currentPhase'));
    }

    public function edit($id)
    {
        $keHoach = KeHoachKhoaLuan::with('mocThoiGians')->findOrFail($id);

        if (in_array($keHoach->TrangThai, ['ĐÃ CÔNG BỐ', 'ĐANG THỰC HIỆN', 'HOÀN THÀNH'])) {
            return redirect()->back()->withErrors('Không cho phép chỉnh sửa kế hoạch đã công bố hoặc đang thực hiện do đã phát sinh dữ liệu.');
        }

        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $khoas = Khoa::orderBy('TenKhoa')->get();
        $boMons = BoMon::orderBy('TenBoMon')->get();

        return view('admin.kehoach.edit', compact('keHoach', 'hocKies', 'khoas', 'boMons'));
    }

    public function update(Request $request, $id)
    {
        $keHoach = KeHoachKhoaLuan::findOrFail($id);

        if (in_array($keHoach->TrangThai, ['ĐÃ CÔNG BỐ', 'ĐANG THỰC HIỆN', 'HOÀN THÀNH'])) {
            return redirect()->back()->withErrors('Không thể cập nhật kế hoạch đã công bố.');
        }

        $request->validate([
            'TenKeHoach'  => 'required|string|max:200',
            'MaHocKy'     => 'required|exists:hoc_kies,MaHocKy',
            'NgayBatDau'  => 'required|date',
            'NgayKetThuc' => 'required|date|after:NgayBatDau',
        ]);

        $keHoach->update($request->only([
            'TenKeHoach', 'MaKhoa', 'MaBoMon', 'MaHocKy', 'NamHoc',
            'NoiDung', 'NgayBatDau', 'NgayKetThuc'
        ]));

        if ($request->has('mocs')) {
            foreach ($request->mocs as $maMoc => $mocData) {
                MocThoiGianKhoaLuan::where('MaMoc', $maMoc)->update([
                    'TenMoc'          => $mocData['TenMoc'],
                    'NgayBatDau'      => $mocData['NgayBatDau'],
                    'NgayKetThuc'     => $mocData['NgayKetThuc'],
                    'DoiTuongThucHien'=> $mocData['DoiTuongThucHien'] ?? 'Tất cả',
                    'MoTa'            => $mocData['MoTa'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.kehoach.index')->with('success', 'Cập nhật Kế hoạch Khóa luận thành công!');
    }

    public function updateStatus(Request $request, $id)
    {
        $keHoach = KeHoachKhoaLuan::findOrFail($id);
        $status = $request->input('TrangThai');

        $allowedStatuses = ['NHÁP', 'CHỜ DUYỆT', 'ĐÃ DUYỆT', 'ĐÃ CÔNG BỐ', 'ĐANG THỰC HIỆN', 'HOÀN THÀNH', 'HỦY'];
        if (!in_array($status, $allowedStatuses)) {
            return redirect()->back()->withErrors('Trạng thái không hợp lệ.');
        }

        $keHoach->TrangThai = $status;
        if ($status === 'ĐÃ CÔNG BỐ' && empty($keHoach->NgayCongBo)) {
            $keHoach->NgayCongBo = now();
        }
        $keHoach->save();

        return redirect()->back()->with('success', "Đã chuyển trạng thái kế hoạch thành: {$status}");
    }

    public function destroy($id)
    {
        $keHoach = KeHoachKhoaLuan::findOrFail($id);

        if (in_array($keHoach->TrangThai, ['ĐÃ CÔNG BỐ', 'ĐANG THỰC HIỆN', 'HOÀN THÀNH'])) {
            return redirect()->back()->withErrors('Không cho phép xóa kế hoạch đã công bố hoặc đã thực hiện.');
        }

        try {
            DB::transaction(function () use ($keHoach) {
                MocThoiGianKhoaLuan::where('MaKeHoach', $keHoach->MaKeHoach)->delete();
                $keHoach->delete();
            });
            return redirect()->route('admin.kehoach.index')->with('success', 'Xóa Kế hoạch Khóa luận thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors('Không thể xóa kế hoạch này do đã vướng dữ liệu liên quan.');
        }
    }
}
