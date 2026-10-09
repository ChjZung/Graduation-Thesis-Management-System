<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Models\Nhom;
use App\Models\ThongBao;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ThongBaoController extends Controller
{
    /**
     * Danh sách thông báo của Giảng viên:
     * - Các thông báo GV gửi cho các nhóm mình hướng dẫn
     * - Các thông báo từ Nhà trường/Giáo vụ gửi đến Giảng viên
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);

        if (!$gv) {
            return redirect()->back()->withErrors('Không tìm thấy thông tin Giảng viên liên kết với tài khoản này.');
        }

        // Lấy danh sách nhóm GV đang hướng dẫn
        $nhoms = Nhom::with(['deTai', 'truongNhom'])
            ->where(function($query) use ($gv) {
                $query->whereHas('dangKyDeTai', function($q) use ($gv) {
                    $q->where('TrangThai', 'Đã duyệt')
                      ->whereHas('deTai', function($dtQ) use ($gv) {
                          $dtQ->where('MaGV', $gv->MaGV);
                      });
                })->orWhereHas('deTai', function($q) use ($gv) {
                    $q->where('MaGV', $gv->MaGV);
                });
            })->get();

        $tab = $request->input('tab', 'da_gui'); // 'da_gui' hoặc 'nhan_duoc'

        if ($tab === 'da_gui') {
            $query = ThongBao::where('MaGV', $gv->MaGV)->orderBy('created_at', 'desc');
        } else {
            $query = ThongBao::where(function($q) use ($user) {
                $q->whereIn('DoiTuongNhan', ['Giảng viên', 'Tất cả', 'Toàn thể'])
                  ->orWhere('DoiTuongNhan', $user->MaTK)
                  ->orWhere('DoiTuongNhan', 'like', "%{$user->MaTK}%");
            })->where(function($q) use ($gv) {
                $q->whereNull('MaGV')->orWhere('MaGV', '!=', $gv->MaGV);
            })->orderBy('created_at', 'desc');
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('TieuDe', 'like', "%{$s}%")
                  ->orWhere('NoiDung', 'like', "%{$s}%")
                  ->orWhere('DoiTuongNhan', 'like', "%{$s}%");
            });
        }

        if ($request->filled('LoaiThongBao')) {
            $query->where('LoaiThongBao', $request->LoaiThongBao);
        }

        $thongbaos = $query->paginate(10)->withQueryString();

        $stats = [
            'da_gui'      => ThongBao::where('MaGV', $gv->MaGV)->count(),
            'nhan_duoc'   => ThongBao::whereIn('DoiTuongNhan', ['Giảng viên', 'Tất cả', 'Toàn thể'])->count(),
            'tong_so_nhom'=> $nhoms->count(),
        ];

        return view('giangvien.thongbao.index', compact('thongbaos', 'nhoms', 'stats', 'tab', 'gv'));
    }

    /**
     * Màn hình soạn thông báo gửi cho nhóm hướng dẫn
     */
    public function create()
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);

        if (!$gv) {
            return redirect()->back()->withErrors('Không tìm thấy thông tin Giảng viên.');
        }

        $nhoms = Nhom::with(['deTai', 'truongNhom', 'thanhViens.sinhVien'])
            ->where(function($query) use ($gv) {
                $query->whereHas('dangKyDeTai', function($q) use ($gv) {
                    $q->where('TrangThai', 'Đã duyệt')
                      ->whereHas('deTai', function($dtQ) use ($gv) {
                          $dtQ->where('MaGV', $gv->MaGV);
                      });
                })->orWhereHas('deTai', function($q) use ($gv) {
                    $q->where('MaGV', $gv->MaGV);
                });
            })->get();

        return view('giangvien.thongbao.create', compact('nhoms', 'gv'));
    }

    /**
     * Lưu và gửi thông báo đến các nhóm
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);

        if (!$gv) {
            return redirect()->back()->withErrors('Không tìm thấy thông tin Giảng viên.');
        }

        $request->validate([
            'TieuDe'       => 'required|string|max:200',
            'NoiDung'      => 'required|string',
            'LoaiThongBao' => 'required|string',
            'DoiTuong'     => 'required|string',
            'FileDinhKem'  => 'nullable|file|mimes:pdf,docx,doc,zip,rar,png,jpg,jpeg|max:10240',
        ], [
            'TieuDe.required'       => 'Vui lòng nhập tiêu đề thông báo.',
            'NoiDung.required'      => 'Vui lòng nhập nội dung thông báo.',
            'LoaiThongBao.required' => 'Vui lòng chọn loại thông báo.',
            'DoiTuong.required'     => 'Vui lòng chọn nhóm nhận thông báo.',
            'FileDinhKem.max'       => 'Tệp đính kèm không được vượt quá 10MB.',
        ]);

        $filePath = null;
        if ($request->hasFile('FileDinhKem')) {
            $file = $request->file('FileDinhKem');
            $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $path = $file->storeAs('public/announcements', $filename);
            $filePath = ltrim(Storage::url($path), '/');
        }

        // Lấy danh sách nhóm hợp lệ của GV
        $allNhoms = Nhom::with(['deTai', 'thanhViens.sinhVien.taiKhoan'])
            ->where(function($query) use ($gv) {
                $query->whereHas('dangKyDeTai', function($q) use ($gv) {
                    $q->where('TrangThai', 'Đã duyệt')
                      ->whereHas('deTai', function($dtQ) use ($gv) {
                          $dtQ->where('MaGV', $gv->MaGV);
                      });
                })->orWhereHas('deTai', function($q) use ($gv) {
                    $q->where('MaGV', $gv->MaGV);
                });
            })->get();

        $selectedTarget = $request->DoiTuong;
        $targetNhoms = [];
        $doiTuongText = '';

        if ($selectedTarget === 'ALL') {
            $targetNhoms = $allNhoms;
            $doiTuongText = 'Tất cả các nhóm hướng dẫn';
        } else {
            $foundNhom = $allNhoms->firstWhere('MaNhom', $selectedTarget);
            if ($foundNhom) {
                $targetNhoms = collect([$foundNhom]);
                $doiTuongText = "Nhóm {$foundNhom->MaNhom} (" . ($foundNhom->TenNhom ?? '') . ")";
            } else {
                $doiTuongText = "Nhóm {$selectedTarget}";
            }
        }

        $maTB = 'TB_' . Str::upper(Str::random(7));
        while (ThongBao::where('MaThongBao', $maTB)->exists()) {
            $maTB = 'TB_' . Str::upper(Str::random(7));
        }

        // 1. Tạo thông báo
        $thongBao = ThongBao::create([
            'MaThongBao'   => $maTB,
            'TieuDe'       => trim($request->TieuDe),
            'NoiDung'      => trim($request->NoiDung),
            'LoaiThongBao' => $request->LoaiThongBao,
            'DoiTuongNhan' => $doiTuongText,
            'NgayTao'      => now(),
            'TrangThai'    => 'Đã phát hành',
            'MaGVu'        => null,
            'MaGV'         => $gv->MaGV,
            'FileDinhKem'  => $filePath,
        ]);

        // 2. Gửi thông báo đến sinh viên từng nhóm
        $soSVDaNhan = 0;
        foreach ($targetNhoms as $nhom) {
            ThongBaoService::guiDenNhom(
                $nhom->MaNhom,
                "📢 [GVHD {$gv->HoTen}] {$request->TieuDe}",
                $request->NoiDung,
                $request->LoaiThongBao,
                $filePath
            );
            $soSVDaNhan += $nhom->thanhViens->count();
        }

        return redirect()->route('giangvien.thongbao.index')
            ->with('success', "Đã gửi thông báo thành công đến {$doiTuongText} ({$soSVDaNhan} sinh viên nhận)!");
    }

    /**
     * Chi tiết thông báo
     */
    public function show($id)
    {
        $thongBao = ThongBao::with(['giangVien', 'giaoVu'])->where('MaThongBao', $id)->firstOrFail();

        $fileUrl = $thongBao->FileDinhKem;
        if (!empty($fileUrl)) {
            $fileUrl = ltrim($fileUrl, '/');
            if (!str_starts_with($fileUrl, 'storage/') && !str_starts_with($fileUrl, 'http')) {
                $fileUrl = 'storage/' . $fileUrl;
            }
        }
        if (empty($fileUrl) || !file_exists(public_path($fileUrl))) {
            $latestPlan = \App\Models\KeHoachKhoaLuan::whereNotNull('FileDinhKem')->latest()->first();
            if ($latestPlan && !empty($latestPlan->FileDinhKem)) {
                $candidate = 'storage/' . ltrim($latestPlan->FileDinhKem, '/');
                if (file_exists(public_path($candidate))) {
                    $fileUrl = $candidate;
                }
            }
        }

        $fileType = 'other';
        if (!empty($fileUrl)) {
            $ext = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                $fileType = 'pdf';
            } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'])) {
                $fileType = 'image';
            }
        }

        return view('thongbao.show', array_merge(
            compact('thongBao', 'fileUrl', 'fileType'),
            [
                'layout'  => 'layouts.giangvien',
                'backUrl' => route('giangvien.thongbao.index')
            ]
        ));
    }
}
