<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KeHoachKhoaLuan;
use App\Models\MocThoiGianKhoaLuan;
use App\Models\HocKy;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\QuyDinhKhoaLuan;
use App\Models\ThongBao;
use App\Services\PlanPhaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class KeHoachKhoaLuanController extends Controller
{
    public function index(Request $request)
    {
        $query = KeHoachKhoaLuan::with(['hocKy', 'mocThoiGians']);

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai);
        }

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $cleanSearch = preg_replace('/[^A-Za-z0-9]/', '', $search);
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('TenKeHoach', 'LIKE', "%{$search}%")
                  ->orWhere('MaKeHoach', 'LIKE', "%{$search}%");
                if (!empty($cleanSearch)) {
                    $q->orWhere('MaKeHoach', 'LIKE', "%{$cleanSearch}%");
                }
            });
        }

        $keHoachs = $query->orderBy('created_at', 'desc')->paginate(10);
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        return view('admin.kehoach.index', compact('keHoachs', 'hocKies'));
    }

    public function create()
    {
        return redirect()->route('admin.kehoach.importDocument')
            ->with('info', 'Hệ thống yêu cầu tải lên file công văn thông báo kế hoạch (PDF/Word) để bóc tách và tạo kế hoạch tự động.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenKeHoach'  => 'required|string|max:200',
            'MaHocKy'     => 'required|exists:HocKy,MaHocKy',
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
            'NgayBatDau.required'   => 'Vui lòng nhập ngày bắt đầu của kế hoạch.',
            'NgayKetThuc.required'  => 'Vui lòng nhập ngày kết thúc của kế hoạch.',
            'NgayKetThuc.after'     => 'Ngày kết thúc của kế hoạch phải diễn ra sau ngày bắt đầu.',
            'mocs.*.TenMoc.required'     => 'Tên mốc quy trình không được để trống.',
            'mocs.*.NgayBatDau.required'  => 'Không được để trống ngày bắt đầu của bất kỳ mốc thời gian nào.',
            'mocs.*.NgayKetThuc.required' => 'Không được để trống ngày kết thúc của bất kỳ mốc thời gian nào.',
            'mocs.*.NgayKetThuc.after_or_equal' => 'Ngày kết thúc của từng mốc phải lớn hơn hoặc bằng ngày bắt đầu.',
        ]);

        // Kiểm tra kế hoạch trùng lặp trong cùng học kỳ
        $exists = KeHoachKhoaLuan::where('MaHocKy', $request->MaHocKy)
            ->where('TrangThai', '!=', 'HỦY')
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->withErrors('Đã tồn tại kế hoạch khóa luận trong cùng Học kỳ.');
        }

        // Kiểm tra chặt chẽ: Không được để trống mốc thời gian và phải nằm trong khoảng kế hoạch
        foreach ($request->mocs as $idx => $m) {
            if (empty($m['NgayBatDau']) || empty($m['NgayKetThuc'])) {
                return redirect()->back()->withInput()->withErrors("Mốc thứ " . ($idx + 1) . " ('" . ($m['TenMoc'] ?? '') . "') không được để trống ngày bắt đầu hoặc ngày kết thúc.");
            }
            if ($m['NgayBatDau'] < $request->NgayBatDau || $m['NgayKetThuc'] > $request->NgayKetThuc) {
                return redirect()->back()->withInput()->withErrors("Mốc thứ " . ($idx + 1) . " ('{$m['TenMoc']}') phải nằm trong khoảng thời gian từ {$request->NgayBatDau} đến {$request->NgayKetThuc} của kế hoạch.");
            }
        }

        DB::transaction(function () use ($request) {
            $maKH = 'KH_' . Str::upper(Str::random(6));
            $user = auth()->user();

            $giaoVu = \App\Models\GiaoVu::where('MaGVu', $user ? $user->MaTK : '')->first() ?: \App\Models\GiaoVu::first();
            $maGVu = $giaoVu ? $giaoVu->MaGVu : null;

            $keHoach = KeHoachKhoaLuan::create([
                'MakeHoach'   => $maKH,
                'MaKeHoach'   => $maKH,
                'MaHocKy'     => $request->MaHocKy,
                'MaGVu'       => $maGVu,
                'TenKeHoach'  => trim($request->TenKeHoach),
                'NoiDung'     => $request->NoiDung,
                'TrangThai'   => $request->action === 'publish' ? 'ĐÃ CÔNG BỐ' : 'NHÁP',
                'NgayTao'     => now(),
                'NgayCongBo'  => $request->action === 'publish' ? now() : null,
                'NgayBatDau'  => $request->NgayBatDau,
                'NgayKetThuc' => $request->NgayKetThuc,
            ]);

            foreach ($request->mocs as $index => $mocData) {
                $maMoc = 'MOC_' . ($index + 1) . '_' . Str::upper(Str::random(4));
                MocThoiGianKhoaLuan::create([
                    'MaMoc'           => $maMoc,
                    'MakeHoach'       => $maKH,
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

            // Lưu các Quy định & Tiêu chuẩn khóa luận (theo form chỉnh sửa hoặc mặc định)
            $submittedRegulations = $request->input('regulations', []);
            if (empty($submittedRegulations)) {
                $submittedRegulations = PlanPhaseService::getDefaultRegulations();
            }

            foreach ($submittedRegulations as $rIdx => $reg) {
                if (empty($reg['TenQuyDinh']) || empty($reg['GiaTri'])) continue;

                $cleanSuffix = preg_replace('/[^A-Za-z0-9]/', '', $maKH);
                $maQD = 'QD_' . substr($cleanSuffix, -4) . '_' . str_pad($rIdx + 1, 2, '0', STR_PAD_LEFT);

                QuyDinhKhoaLuan::create([
                    'MaQuyDinh'  => substr($maQD, 0, 20),
                    'TenQuyDinh' => trim($reg['TenQuyDinh']),
                    'GiaTri'     => trim($reg['GiaTri']),
                    'MoTa'       => isset($reg['MoTa']) ? trim($reg['MoTa']) : null,
                    'MakeHoach'  => $maKH,
                ]);
            }
        });

        return redirect()->route('admin.kehoach.index')->with('success', 'Tạo Kế hoạch Khóa luận mới và lưu đầy đủ quy định thành công!');
    }

    public function show($id)
    {
        $keHoach = KeHoachKhoaLuan::with(['hocKy', 'giaoVu', 'mocThoiGians', 'quyDinhs', 'bieuMaus'])->findOrFail($id);
        $currentPhase = PlanPhaseService::getCurrentPhase($keHoach);

        return view('admin.kehoach.show', compact('keHoach', 'currentPhase'));
    }

    public function edit($id)
    {
        $keHoach = KeHoachKhoaLuan::with('mocThoiGians')->findOrFail($id);

        if ($keHoach->TrangThai === 'HOÀN THÀNH') {
            return redirect()->back()->withErrors('Không cho phép chỉnh sửa kế hoạch đã hoàn thành.');
        }

        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $khoas = Khoa::orderBy('TenKhoa')->get();
        $boMons = BoMon::orderBy('TenBoMon')->get();

        return view('admin.kehoach.edit', compact('keHoach', 'hocKies', 'khoas', 'boMons') + ['Khoa' => $khoas]);
    }

    public function update(Request $request, $id)
    {
        $keHoach = KeHoachKhoaLuan::findOrFail($id);

        if ($keHoach->TrangThai === 'HOÀN THÀNH') {
            return redirect()->back()->withErrors('Không thể cập nhật kế hoạch đã hoàn thành.');
        }

        $request->validate([
            'TenKeHoach'  => 'required|string|max:200',
            'MaHocKy'     => 'required|exists:HocKy,MaHocKy',
        ]);

        $keHoach->update($request->only([
            'TenKeHoach', 'MaKhoa', 'MaBoMon', 'MaHocKy', 'NamHoc',
            'NoiDung', 'NgayBatDau', 'NgayKetThuc'
        ]));

        if ($request->has('mocs')) {
            foreach ($request->mocs as $maMoc => $mocData) {
                $fieldsToUpdate = [
                    'TenMoc'      => $mocData['TenMoc'],
                    'NgayBatDau'  => $mocData['NgayBatDau'],
                    'NgayKetThuc' => $mocData['NgayKetThuc'],
                    'MoTa'        => $mocData['MoTa'] ?? null,
                ];
                if (Schema::hasColumn('MocThoiGianKhoaLuan', 'DoiTuongThucHien')) {
                    $fieldsToUpdate['DoiTuongThucHien'] = $mocData['DoiTuongThucHien'] ?? 'Tất cả';
                }
                MocThoiGianKhoaLuan::where('MaMoc', $maMoc)->update($fieldsToUpdate);
            }
        }

        return redirect()->route('admin.kehoach.index')->with('success', 'Cập nhật Kế hoạch Khóa luận thành công!');
    }

    public function updateStatus(Request $request, $id)
    {
        $keHoach = KeHoachKhoaLuan::with(['hocKy'])->findOrFail($id);
        $status = $request->input('TrangThai');

        $allowedStatuses = ['NHÁP', 'CHỜ DUYỆT', 'ĐÃ DUYỆT', 'ĐÃ CÔNG BỐ', 'ĐANG THỰC HIỆN', 'HOÀN THÀNH', 'HỦY'];
        if (!in_array($status, $allowedStatuses)) {
            return redirect()->back()->withErrors('Trạng thái không hợp lệ.');
        }

        $keHoach->TrangThai = $status;
        $keHoach->save();

        if (in_array($status, ['ĐÃ CÔNG BỐ', 'ĐANG THỰC HIỆN'])) {
            $this->sendBroadcastNotification($keHoach);
            return redirect()->back()->with('success', "Đã chuyển trạng thái sang {$status} và gửi thông báo kèm file công văn tới toàn bộ hệ thống.");
        }

        return redirect()->back()->with('success', "Đã chuyển trạng thái kế hoạch thành: {$status}");
    }

    public function publish($id)
    {
        $keHoach = KeHoachKhoaLuan::with(['hocKy', 'mocThoiGians'])->findOrFail($id);
        $keHoach->TrangThai = 'ĐÃ CÔNG BỐ';
        $keHoach->save();

        $this->sendBroadcastNotification($keHoach);

        return redirect()->back()->with('success', '🎉 Đã công bố Kế hoạch khóa luận thành công! Thông báo kèm file công văn đính kèm đã được phát hành cho toàn thể hệ thống.');
    }

    private function sendBroadcastNotification(KeHoachKhoaLuan $keHoach): void
    {
        $headerName = $keHoach->hocKy->TenHocKy ?? $keHoach->TenKeHoach;
        $title = "[KHOA CNTT] Thông Báo Công Bố Kế Hoạch Khóa Luận Tốt Nghiệp - {$headerName}";

        $fileUrl = null;
        if (!empty($keHoach->FileDinhKem)) {
            $fileUrl = asset('storage/' . $keHoach->FileDinhKem);
        }

        $noiDung = "Khoa Công nghệ Thông tin chính thức công bố {$keHoach->TenKeHoach} (" . ($keHoach->NoiDung ?? '') . ").\n\n"
            . "Đề nghị toàn thể Giảng viên và Sinh viên theo dõi chi tiết các mốc thời gian quy trình và thực hiện theo đúng kế hoạch."
            . ($fileUrl ? "\n\n📄 File công văn đính kèm: " . $fileUrl : "");

        $maGVu = $keHoach->MaGVu ?? (Auth::user()?->giaoVu?->MaGVu ?? 'GVU01');

        $maTB = 'TB_' . Str::upper(Str::random(7));
        while (ThongBao::where('MaThongBao', $maTB)->exists()) {
            $maTB = 'TB_' . Str::upper(Str::random(7));
        }

        ThongBao::create([
            'MaThongBao'   => $maTB,
            'TieuDe'       => $title,
            'NoiDung'      => $noiDung,
            'LoaiThongBao' => 'Kế hoạch',
            'DoiTuongNhan' => 'Toàn thể',
            'NgayTao'      => now(),
            'TrangThai'    => 'Đã phát hành',
            'FileDinhKem'  => $keHoach->FileDinhKem ? ('storage/' . $keHoach->FileDinhKem) : null,
            'MaGVu'        => $maGVu,
        ]);
    }

    public function destroy($id)
    {
        $keHoach = KeHoachKhoaLuan::findOrFail($id);

        if ($keHoach->TrangThai === 'HOÀN THÀNH') {
            return redirect()->back()->withErrors('Không cho phép xóa kế hoạch đã hoàn thành.');
        }

        try {
            DB::transaction(function () use ($keHoach) {
                MocThoiGianKhoaLuan::where('MakeHoach', $keHoach->MakeHoach)->delete();
                QuyDinhKhoaLuan::where('MakeHoach', $keHoach->MakeHoach)->delete();
                $keHoach->delete();
            });
            return redirect()->route('admin.kehoach.index')->with('success', 'Xóa Kế hoạch Khóa luận thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors('Không thể xóa kế hoạch này: ' . $e->getMessage());
        }
    }
}
