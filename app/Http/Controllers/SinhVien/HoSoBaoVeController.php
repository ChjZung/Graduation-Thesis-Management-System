<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\HoSoBaoVe;
use App\Models\TepHoSoBaoVe;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Models\Nhom;
use App\Models\BaoCaoTienDo;
use App\Helpers\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HoSoBaoVeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->first();

        if (!$sinhVien) {
            return redirect()->route('sinhvien.nhom.index')
                ->with('error', 'Bạn chưa có hồ sơ sinh viên.');
        }

        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')->first();

        $nhom = $thanhVienRecord ? Nhom::with(['deTai.giangVien', 'thanhViens.sinhVien'])->find($thanhVienRecord->MaNhom) : null;

        // Kiểm tra đã hoàn thành Mốc 5 chưa (thông qua MaDeTai)
        $moc5Dat = ($nhom && $nhom->deTai) ? BaoCaoTienDo::where('MaDeTai', $nhom->deTai->MaDeTai)
            ->where('LanBaoCao', 5)->where('TrangThai', 'Đạt')->exists() : false;

        $hoSo = $nhom ? HoSoBaoVe::with(['hoiDong.thanhViens.giangVien', 'tepHoSoBaoVes', 'phanCongPhanBien.giangVien'])
            ->where('MaNhom', $nhom->MaNhom)->first() : null;

        return view('sinhvien.hoso_baove.index', compact('sinhVien', 'nhom', 'hoSo', 'moc5Dat'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TyLeTrungLap'     => 'required|numeric|min:0|max:100',
            'FileToanVan'      => 'required|file|mimes:pdf|max:30720',
            'MinhChungTurnitin'=> 'required|file|mimes:pdf|max:20480',
            'GhiChu'           => 'nullable|string|max:500',
        ], [
            'TyLeTrungLap.required'      => 'Vui lòng nhập tỷ lệ trùng lặp từ báo cáo Turnitin.',
            'TyLeTrungLap.numeric'       => 'Tỷ lệ trùng lặp phải là một con số hợp lệ.',
            'TyLeTrungLap.max'           => 'Tỷ lệ trùng lặp không thể vượt quá 100%.',
            'FileToanVan.required'       => 'Vui lòng đính kèm tệp Báo cáo toàn văn (PDF).',
            'FileToanVan.mimes'          => 'Tệp Báo cáo toàn văn phải là định dạng PDF.',
            'MinhChungTurnitin.required' => 'Vui lòng đính kèm tệp Báo cáo kiểm tra Turnitin (PDF).',
            'MinhChungTurnitin.mimes'    => 'Tệp Minh chứng Turnitin phải là định dạng PDF.',
        ]);

        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();
        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')->firstOrFail();
        $nhom = Nhom::with('deTai')->findOrFail($thanhVienRecord->MaNhom);

        if (!$nhom->deTai) {
            return back()->with('error', 'Nhóm của bạn chưa có đề tài được duyệt.');
        }

        // Kiểm tra điều kiện tiên quyết: phải hoàn thành Đạt Mốc 5
        $moc5Dat = BaoCaoTienDo::where('MaDeTai', $nhom->deTai->MaDeTai)
            ->where('LanBaoCao', 5)
            ->where('TrangThai', 'Đạt')
            ->exists();

        if (!$moc5Dat) {
            return back()->with('error', 'Nhóm của bạn cần hoàn thành và được Giảng viên đánh giá "Đạt" ở Mốc 5 trước khi nộp hồ sơ bảo vệ.');
        }

        // Kiểm tra nếu đã nộp hồ sơ và đang trong tiến trình thì không cho nộp đè
        $hoSoHienTai = HoSoBaoVe::where('MaNhom', $nhom->MaNhom)->first();
        if ($hoSoHienTai && in_array($hoSoHienTai->TrangThai, ['Đủ điều kiện bảo vệ', 'Đã phân công', 'Chờ thẩm định'])) {
            return back()->with('error', 'Nhóm đã nộp hồ sơ bảo vệ và đang trong quá trình thẩm định/phân công.');
        }

        DB::transaction(function () use ($request, $nhom, $hoSoHienTai) {
            $maHoSo = $hoSoHienTai ? $hoSoHienTai->MaHoSo : IdGenerator::nextHoSoBaoVe();

            $tyLe = number_format((float)$request->TyLeTrungLap, 2, '.', '');
            $ghiChuText = "Tỷ lệ đạo văn Turnitin: {$tyLe}%" . ($request->GhiChu ? ' | ' . $request->GhiChu : '');

            if ($hoSoHienTai) {
                $hoSoHienTai->update([
                    'NgayNop'    => now()->toDateString(),
                    'TrangThai'  => 'Chờ thẩm định',
                    'GhiChu'     => $ghiChuText,
                ]);
                $hoSo = $hoSoHienTai;
            } else {
                $hoSo = HoSoBaoVe::create([
                    'MaHoSo'     => $maHoSo,
                    'NgayLap'    => now()->toDateString(),
                    'NgayNop'    => now()->toDateString(),
                    'TrangThai'  => 'Chờ thẩm định',
                    'GhiChu'     => $ghiChuText,
                    'MaNhom'     => $nhom->MaNhom,
                    'MaDeTai'    => $nhom->deTai->MaDeTai,
                ]);
            }

            // Lưu tệp Báo cáo toàn văn
            if ($request->hasFile('FileToanVan')) {
                $fileTV = $request->file('FileToanVan');
                $pathTV = $fileTV->store("hoso/{$nhom->MaNhom}", 'public');

                $tepTV = $hoSo->tepHoSoBaoVes()->where('LoaiTep', 'Khóa luận toàn văn')->first();
                if ($tepTV) {
                    $tepTV->update([
                        'TenTep'       => $fileTV->getClientOriginalName(),
                        'DuongDanFile' => $pathTV,
                        'NgayNop'      => now()->toDateString(),
                    ]);
                } else {
                    TepHoSoBaoVe::create([
                        'MaTep'        => IdGenerator::nextTepHoSoBaoVe(),
                        'TenTep'       => $fileTV->getClientOriginalName(),
                        'LoaiTep'      => 'Khóa luận toàn văn',
                        'DuongDanFile' => $pathTV,
                        'PhienBan'     => '1.0',
                        'NgayNop'      => now()->toDateString(),
                        'TrangThai'    => 'Đã nộp',
                        'MaHoSo'       => $hoSo->MaHoSo,
                    ]);
                }
            }

            // Lưu tệp Minh chứng Turnitin
            if ($request->hasFile('MinhChungTurnitin')) {
                $fileTI = $request->file('MinhChungTurnitin');
                $pathTI = $fileTI->store("hoso/{$nhom->MaNhom}", 'public');

                $tepTI = $hoSo->tepHoSoBaoVes()->where('LoaiTep', 'Minh chứng đạo văn')->first();
                if ($tepTI) {
                    $tepTI->update([
                        'TenTep'       => $fileTI->getClientOriginalName(),
                        'DuongDanFile' => $pathTI,
                        'NgayNop'      => now()->toDateString(),
                    ]);
                } else {
                    TepHoSoBaoVe::create([
                        'MaTep'        => IdGenerator::nextTepHoSoBaoVe(),
                        'TenTep'       => $fileTI->getClientOriginalName(),
                        'LoaiTep'      => 'Minh chứng đạo văn',
                        'DuongDanFile' => $pathTI,
                        'PhienBan'     => '1.0',
                        'NgayNop'      => now()->toDateString(),
                        'TrangThai'    => 'Đã nộp',
                        'MaHoSo'       => $hoSo->MaHoSo,
                    ]);
                }
            }
        });

        return redirect()->route('sinhvien.hoso.index')
            ->with('success', 'Nộp hồ sơ bảo vệ thành công! Giáo vụ Khoa sẽ thẩm định tính hợp lệ của tỷ lệ Turnitin và sắp xếp Hội đồng bảo vệ.');
    }
}
