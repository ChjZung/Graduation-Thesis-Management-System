<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KeHoachKhoaLuan;
use App\Models\MocThoiGianKhoaLuan;
use App\Models\HocKy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KeHoachKhoaLuanController extends Controller
{
    public function index()
    {
        $keHoachs = KeHoachKhoaLuan::with('hocKy', 'mocThoiGians')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.kehoach.index', compact('keHoachs'));
    }

    public function create()
    {
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        return view('admin.kehoach.create', compact('hocKies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenKeHoach' => 'required|string|max:150',
            'MaHocKy' => 'required|exists:hoc_kies,MaHocKy',
            'NoiDung' => 'nullable|string',
            'mocs' => 'required|array|min:5',
            'mocs.*.TenMoc' => 'required|string|max:100',
            'mocs.*.NgayBatDau' => 'required|date',
            'mocs.*.NgayKetThuc' => 'required|date|after_or_equal:mocs.*.NgayBatDau',
        ], [
            'TenKeHoach.required' => 'Vui lòng nhập tên kế hoạch khóa luận.',
            'MaHocKy.required' => 'Vui lòng chọn học kỳ.',
            'mocs.required' => 'Vui lòng nhập đầy đủ 5 mốc thời gian báo cáo.',
            'mocs.*.NgayKetThuc.after_or_equal' => 'Ngày kết thúc của mốc phải lớn hơn hoặc bằng ngày bắt đầu.',
        ]);

        DB::transaction(function () use ($request) {
            $maKH = 'KH_' . Str::upper(Str::random(6));
            $gvu = \App\Models\GiaoVu::first();
            $maGVu = $gvu ? $gvu->MaGVu : 'GVU01';

            $keHoach = KeHoachKhoaLuan::create([
                'MaKeHoach' => $maKH,
                'MaHocKy' => $request->MaHocKy,
                'MaGVu' => $maGVu,
                'TenKeHoach' => $request->TenKeHoach,
                'NoiDung' => $request->NoiDung,
                'TrangThai' => 'Đã công bố',
                'NgayTao' => now(),
                'NgayCongBo' => now(),
            ]);

            foreach ($request->mocs as $index => $mocData) {
                $maMoc = 'MOC_' . ($index + 1) . '_' . Str::upper(Str::random(4));
                MocThoiGianKhoaLuan::create([
                    'MaMoc' => $maMoc,
                    'MaKeHoach' => $maKH,
                    'TenMoc' => $mocData['TenMoc'],
                    'NgayBatDau' => $mocData['NgayBatDau'],
                    'NgayKetThuc' => $mocData['NgayKetThuc'],
                    'MoTa' => $mocData['MoTa'] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.kehoach.index')->with('success', 'Tạo Kế hoạch Khóa luận & 5 mốc báo cáo thành công!');
    }

    public function show($id)
    {
        $keHoach = KeHoachKhoaLuan::with('hocKy', 'mocThoiGians')->findOrFail($id);
        return view('admin.kehoach.show', compact('keHoach'));
    }

    public function edit($id)
    {
        $keHoach = KeHoachKhoaLuan::with('mocThoiGians')->findOrFail($id);
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        return view('admin.kehoach.edit', compact('keHoach', 'hocKies'));
    }

    public function update(Request $request, $id)
    {
        $keHoach = KeHoachKhoaLuan::findOrFail($id);

        $request->validate([
            'TenKeHoach' => 'required|string|max:150',
            'MaHocKy' => 'required|exists:hoc_kies,MaHocKy',
            'NoiDung' => 'nullable|string',
        ]);

        $keHoach->update($request->only(['TenKeHoach', 'MaHocKy', 'NoiDung']));

        if ($request->has('mocs')) {
            foreach ($request->mocs as $maMoc => $mocData) {
                MocThoiGianKhoaLuan::where('MaMoc', $maMoc)->update([
                    'NgayBatDau' => $mocData['NgayBatDau'],
                    'NgayKetThuc' => $mocData['NgayKetThuc'],
                    'MoTa' => $mocData['MoTa'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.kehoach.index')->with('success', 'Cập nhật Kế hoạch Khóa luận thành công!');
    }

    public function destroy($id)
    {
        $keHoach = KeHoachKhoaLuan::findOrFail($id);
        try {
            DB::transaction(function () use ($keHoach) {
                MocThoiGianKhoaLuan::where('MaKeHoach', $keHoach->MaKeHoach)->delete();
                $keHoach->delete();
            });
            return redirect()->route('admin.kehoach.index')->with('success', 'Xóa Kế hoạch Khóa luận thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors('Không thể xóa kế hoạch này do vướng dữ liệu liên quan.');
        }
    }

    /**
     * Giao diện Calendar chung cho Admin / Giáo vụ
     */
    public function calendar()
    {
        $mocs = MocThoiGianKhoaLuan::with('keHoach')->get();
        $events = [];

        $colors = [
            '#0d6efd', // Mốc 1: Phân tích Nghiệp vụ
            '#0dcaf0', // Mốc 2: Phân tích Hệ thống
            '#ffc107', // Mốc 3: Thiết kế CSDL
            '#fd7e14', // Mốc 4: Triển khai Code
            '#198754', // Mốc 5: Hoàn thành
            '#dc3545', // Hạn Turnitin / Bảo vệ
        ];

        foreach ($mocs as $i => $moc) {
            $events[] = [
                'title' => $moc->TenMoc . ' (' . ($moc->keHoach->TenKeHoach ?? '') . ')',
                'start' => $moc->NgayBatDau,
                'end' => date('Y-m-d', strtotime($moc->NgayKetThuc . ' +1 day')),
                'color' => $colors[$i % count($colors)],
                'description' => $moc->MoTa ?? '',
            ];
        }

        return view('calendar.index', [
            'layout' => 'layouts.admin',
            'events' => $events,
            'roleTitle' => 'Giáo vụ Khoa',
        ]);
    }
}
