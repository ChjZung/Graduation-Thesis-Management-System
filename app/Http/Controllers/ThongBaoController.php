<?php

namespace App\Http\Controllers;

use App\Models\ThongBao;
use App\Models\NguoiNhanThongBao;
use App\Models\KeHoachKhoaLuan;
use App\Models\TaiKhoan;
use App\Services\FileUploadService;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ThongBaoController extends Controller
{
    /**
     * Danh sách Thông báo Hệ thống kèm Bộ lọc & Phân trang
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';

        $query = ThongBao::orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('TieuDe', 'like', "%{$s}%")
                  ->orWhere('NoiDung', 'like', "%{$s}%")
                  ->orWhere('MaThongBao', 'like', "%{$s}%");
            });
        }

        if ($request->filled('LoaiThongBao')) {
            $query->where('LoaiThongBao', $request->LoaiThongBao);
        }

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai);
        }

        $thongbaos = $query->paginate(10)->withQueryString();

        $stats = [
            'total_thongbao' => ThongBao::count(),
            'active_count' => ThongBao::whereIn('TrangThai', ['ĐÃ GỬI', 'ACTIVE'])->count(),
            'target_all_count' => ThongBao::where('DoiTuongNhan', 'Tất cả')->count(),
            'draft_schedule_count' => ThongBao::whereIn('TrangThai', ['NHÁP', 'ĐÃ LÊN LỊCH'])->count(),
        ];

        $layout = ($role === 'Admin' || $role === 'Giáo vụ') ? 'layouts.admin' : (($role === 'Giảng viên') ? 'layouts.giangvien' : 'layouts.sinhvien');

        return view('thongbao.index', compact('layout', 'thongbaos', 'stats'));
    }

    /**
     * Giao diện Tạo mới Thông báo
     */
    public function create()
    {
        $keHoachs = KeHoachKhoaLuan::orderBy('created_at', 'desc')->get();
        return view('thongbao.create', compact('keHoachs'));
    }

    /**
     * Xử lý Lưu Thông báo Mới (Nháp / Lên lịch / Gửi ngay)
     */
    public function store(Request $request)
    {
        $request->validate([
            'TieuDe'       => 'required|max:255',
            'NoiDung'      => 'required',
            'LoaiThongBao' => 'required',
            'DoiTuongNhan' => 'required',
            'FileDinhKem'  => 'nullable|file|mimes:pdf,docx,doc,zip,rar,png,jpg,jpeg|max:10240',
        ], [
            'TieuDe.required'       => 'Vui lòng nhập tiêu đề thông báo.',
            'NoiDung.required'      => 'Vui lòng nhập nội dung thông báo.',
            'LoaiThongBao.required' => 'Vui lòng chọn loại thông báo.',
            'DoiTuongNhan.required' => 'Vui lòng chọn đối tượng nhận.',
            'FileDinhKem.mimes'      => 'File đính kèm chỉ hỗ trợ định dạng PDF, DOCX, ZIP, RAR, HÌNH ẢNH.',
        ]);

        $filePath = null;
        if ($request->hasFile('FileDinhKem')) {
            $filePath = FileUploadService::uploadFile($request->file('FileDinhKem'), 'announcements');
        }

        $maTB = 'TB_' . Str::upper(Str::random(7));
        while (ThongBao::where('MaThongBao', $maTB)->exists()) {
            $maTB = 'TB_' . Str::upper(Str::random(7));
        }

        $actionType = $request->input('action_type', 'send_now');
        $trangThai = 'NHÁP';
        $ngayGui = null;

        if ($actionType === 'send_now') {
            $trangThai = 'ĐÃ GỬI';
            $ngayGui = now();
        } elseif ($actionType === 'schedule') {
            $trangThai = 'ĐÃ LÊN LỊCH';
            $ngayGui = $request->input('NgayGuiScheduled', now());
        }

        $thongBao = ThongBao::create([
            'MaThongBao'   => $maTB,
            'MaGVu'        => Auth::user()->MaTK ?? 'ADMIN',
            'TieuDe'       => $request->TieuDe,
            'NoiDung'      => $request->NoiDung,
            'LoaiThongBao' => $request->LoaiThongBao,
            'DoiTuongNhan' => $request->DoiTuongNhan,
            'FileDinhKem'  => $filePath,
            'MaKeHoach'    => $request->MaKeHoach,
            'NgayTao'      => now(),
            'NgayGui'      => $ngayGui,
            'TrangThai'    => $trangThai,
        ]);

        // Nếu phát hành ngay $\rightarrow$ Phân phối notification tới người nhận
        if ($trangThai === 'ĐÃ GỬI') {
            $this->dispatchRecipients($thongBao);
        }

        return redirect()->route('thongbao.index')->with('success', 'Đã lưu thông báo thành công!');
    }

    /**
     * Trang Xem Chi Tiết Thông Báo & Lịch Sử Người Nhận
     */
    public function show($id)
    {
        $thongBao = ThongBao::where('MaThongBao', $id)->firstOrFail();

        $target = $thongBao->DoiTuongNhan;
        if ($target === 'Sinh viên') {
            $totalSent = \App\Models\SinhVien::count();
        } elseif ($target === 'Giảng viên') {
            $totalSent = \App\Models\GiangVien::count();
        } else {
            $totalSent = \App\Models\TaiKhoan::count();
        }
        $readCount = 0;
        $unreadCount = $totalSent;

        return view('thongbao.show', compact('thongBao', 'totalSent', 'readCount', 'unreadCount'));
    }

    /**
     * Trang Chỉnh Sửa Thông Báo (Dành cho Nháp / Chưa gửi)
     */
    public function edit($id)
    {
        $thongBao = ThongBao::where('MaThongBao', $id)->firstOrFail();

        if (in_array($thongBao->TrangThai, ['ĐÃ GỬI', 'ĐÃ HỦY'])) {
            return redirect()->route('thongbao.show', $id)
                ->withErrors('Thông báo đã gửi hoặc đã hủy không thể chỉnh sửa trực tiếp nội dung để bảo toàn lịch sử.');
        }

        $keHoachs = KeHoachKhoaLuan::orderBy('created_at', 'desc')->get();
        return view('thongbao.edit', compact('thongBao', 'keHoachs'));
    }

    /**
     * Xử lý Cập Nhật Thông Báo
     */
    public function update(Request $request, $id)
    {
        $thongBao = ThongBao::where('MaThongBao', $id)->firstOrFail();

        if (in_array($thongBao->TrangThai, ['ĐÃ GỬI', 'ĐÃ HỦY'])) {
            return redirect()->route('thongbao.show', $id)
                ->withErrors('Thông báo đã phát hành không được chỉnh sửa.');
        }

        $request->validate([
            'TieuDe'       => 'required|max:255',
            'NoiDung'      => 'required',
            'LoaiThongBao' => 'required',
            'DoiTuongNhan' => 'required',
            'FileDinhKem'  => 'nullable|file|mimes:pdf,docx,doc,zip,rar,png,jpg,jpeg|max:10240',
        ]);

        $filePath = $thongBao->FileDinhKem;
        if ($request->hasFile('FileDinhKem')) {
            $filePath = FileUploadService::uploadFile($request->file('FileDinhKem'), 'announcements');
        }

        $actionType = $request->input('action_type', 'save_draft');
        $trangThai = $thongBao->TrangThai;
        $ngayGui = $thongBao->NgayGui;

        if ($actionType === 'send_now') {
            $trangThai = 'ĐÃ GỬI';
            $ngayGui = now();
        } elseif ($actionType === 'schedule') {
            $trangThai = 'ĐÃ LÊN LỊCH';
            $ngayGui = $request->input('NgayGuiScheduled', now());
        }

        $thongBao->update([
            'TieuDe'       => $request->TieuDe,
            'NoiDung'      => $request->NoiDung,
            'LoaiThongBao' => $request->LoaiThongBao,
            'DoiTuongNhan' => $request->DoiTuongNhan,
            'FileDinhKem'  => $filePath,
            'MaKeHoach'    => $request->MaKeHoach,
            'NgayGui'      => $ngayGui,
            'TrangThai'    => $trangThai,
        ]);

        if ($trangThai === 'ĐÃ GỬI') {
            $this->dispatchRecipients($thongBao);
        }

        return redirect()->route('thongbao.show', $thongBao->MaThongBao)->with('success', 'Đã cập nhật thông báo!');
    }

    /**
     * Phát Hành Ngay (Send Now Action)
     */
    public function sendNow($id)
    {
        $thongBao = ThongBao::where('MaThongBao', $id)->firstOrFail();

        if ($thongBao->TrangThai === 'ĐÃ GỬI') {
            return redirect()->back()->with('info', 'Thông báo này đã được phát hành trước đó.');
        }

        $thongBao->update([
            'TrangThai' => 'ĐÃ GỬI',
            'NgayGui'   => now(),
        ]);

        $this->dispatchRecipients($thongBao);

        return redirect()->route('thongbao.show', $id)->with('success', '🎉 Đã phát hành thông báo thành công!');
    }

    /**
     * Xóa Nháp / Hủy / Soft Delete Thông Báo
     */
    public function destroy($id)
    {
        $thongBao = ThongBao::where('MaThongBao', $id)->firstOrFail();

        if ($thongBao->TrangThai === 'NHÁP' || $thongBao->TrangThai === 'ĐÃ LÊN LỊCH') {
            $thongBao->delete(); // Soft Delete
            return redirect()->route('thongbao.index')->with('success', 'Đã xóa thông báo nháp!');
        }

        // Với thông báo đã gửi: Đổi trạng thái sang ĐÃ HỦY để giữ lịch sử người nhận
        $thongBao->update(['TrangThai' => 'ĐÃ HỦY']);
        return redirect()->route('thongbao.index')->with('success', 'Đã thu hồi / chuyển trạng thái HỦY thông báo!');
    }

    /**
     * Phân phối Notification tới từng tài khoản người nhận
     */
    private function dispatchRecipients(ThongBao $thongBao): void
    {
        $target = $thongBao->DoiTuongNhan;
        $query = TaiKhoan::query();

        if ($target === 'Sinh viên') {
            $query->whereHas('vaiTro', fn($q) => $q->where('TenVaiTro', 'Sinh viên'));
        } elseif ($target === 'Giảng viên') {
            $query->whereHas('vaiTro', fn($q) => $q->where('TenVaiTro', 'Giảng viên'));
        }

        $taiKhoans = $query->get();
        $fileUrl = $thongBao->FileDinhKem ? asset($thongBao->FileDinhKem) : null;

        if (class_exists(\App\Models\NguoiNhanThongBao::class) && \Illuminate\Support\Facades\Schema::hasTable('nguoi_nhan_thong_baos')) {
            foreach ($taiKhoans as $tk) {
                \App\Models\NguoiNhanThongBao::updateOrCreate(
                    [
                        'MaThongBao' => $thongBao->MaThongBao,
                        'MaTK'       => $tk->MaTK,
                    ],
                    [
                        'TieuDe'     => $thongBao->TieuDe,
                        'NoiDung'    => $thongBao->NoiDung,
                        'Loai'       => $thongBao->LoaiThongBao,
                        'DuongDan'   => $fileUrl,
                        'DaDoc'      => false,
                        'NgayDoc'    => null,
                    ]
                );
            }
        }
    }
}
