<?php
namespace App\Http\Controllers;

use App\Models\{HocKy, HoiDong, GiangVien};
use App\Services\{CloRoundReportService, CloReportExportService};
use Illuminate\Http\Request;

class CloRoundReportController extends Controller
{
    public function index(Request $request, CloRoundReportService $service)
    {
        $allowed = $service->allowedMajors($request->user());
        if (!$request->boolean('popup')) {
            $destination = match ($request->user()->vaiTro?->TenVaiTro) {
                'Giảng viên' => 'giangvien.hoidong.index',
                'Trưởng khoa' => 'truongkhoa.thongke_baocao.index',
                default => 'admin.thongke_baocao.index',
            };
            return redirect()->route($destination, ['clo_export' => 1] + $request->only(['MaHocKy', 'MaNganh', 'DotBaoVe', 'MaHoiDong', 'loai', 'format']));
        }
        $majors = $allowed->get();
        $semesters = HocKy::orderByDesc('created_at')->get();
        $teacher = $request->user()->vaiTro?->TenVaiTro === 'Giảng viên' ? GiangVien::getLoggedInGiangVien($request->user()) : null;
        $options = HoiDong::with(['thanhViens', 'hoSoBaoVes.nhom.thanhViens.sinhVien.lop'])->get()->flatMap(function ($hd) use ($majors, $teacher) {
            return $hd->hoSoBaoVes->flatMap(fn ($hs) => $hs->nhom?->thanhViens ?? collect())->map(fn ($member) => CloRoundReportService::studentMajor($member->sinhVien))->filter(fn ($id) => $majors->contains('MaNganh', $id))->unique()
                ->map(fn ($id) => ['semester' => $hd->MaHocKy, 'major' => $id, 'round' => $hd->DotBaoVe,
                    'council' => !$teacher || $hd->thanhViens->contains('MaGV', $teacher->MaGV) ? $hd->MaHoiDong : null,
                    'name' => !$teacher || $hd->thanhViens->contains('MaGV', $teacher->MaGV) ? $hd->TenHoiDong : null]);
        })->unique(fn ($row) => json_encode($row))->values();
        $layout = match ($request->user()->vaiTro?->TenVaiTro) { 'Trưởng khoa' => 'layouts.truongkhoa', 'Giảng viên' => 'layouts.giangvien', default => 'layouts.admin' };
        return view('clo_reports._selection', compact('majors', 'semesters', 'options', 'layout'));
    }

    public function export(Request $request, CloRoundReportService $service, CloReportExportService $exporter)
    {
        // Authorization is checked even when the selection is incomplete.
        $service->allowedMajors($request->user());
        $scope = $request->validate(['MaHocKy' => 'required|string|exists:HocKy,MaHocKy', 'MaNganh' => 'required|string|exists:Nganh,MaNganh',
            'DotBaoVe' => 'nullable|string|max:100', 'MaHoiDong' => 'nullable|string|exists:HoiDong,MaHoiDong', 'loai' => 'required|in:KLCN,KLKS', 'format' => 'required|in:xlsx,pdf']);
        $report = $service->prepare($request->user(), $scope['MaHocKy'], $scope['DotBaoVe'] ?? null, $scope['MaNganh'], $scope['loai'], $scope['MaHoiDong'] ?? null);
        $selection = $report['scope']['MaHoiDong'] ?: ($report['scope']['DotBaoVe'] ?: 'Toan_nganh');
        $name = 'Bao_cao_CLO_'.\Illuminate\Support\Str::slug($scope['MaNganh'].' '.$scope['MaHocKy'].' '.$selection.' '.$scope['loai'], '_');
        if ($scope['format'] === 'pdf') {
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.bao_cao_clo_theo_dot', ['report' => $report])->setPaper('a4', 'landscape')->stream($name.'.pdf');
        }
        $book = $exporter->exportRound($report);
        return response()->streamDownload(function () use ($book) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save('php://output');
        }, $name.'.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Cache-Control' => 'private, no-store']);
    }
}
