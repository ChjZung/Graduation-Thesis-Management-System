<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\Nhom;
use App\Models\DangKyDeTai;
use App\Models\ThanhVienNhom;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeTaiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $gv = GiangVien::where('MaTK', $user->MaTK)->first();

        if (!$gv) {
            return redirect()->back()->withErrors('Không tìm thấy thông tin Giảng viên liên kết với tài khoản này.');
        }

        $query = DeTai::with(['giangVien', 'dangKyDeTais.nhom.thanhViens.sinhVien'])->where('MaGV', $gv->MaGV);

        if ($request->filled('search')) {
            $query->where('TenDeTai', 'LIKE', '%' . trim($request->search) . '%');
        }

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai);
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(10);
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        // Danh sách các nhóm chưa có đề tài đã duyệt
        $NhomChuaCoDeTai = Nhom::with(['truongNhom', 'thanhViens.sinhVien'])
            ->whereDoesntHave('phieuDangKys', fn($q) => $q->where('TrangThai', 'Đã duyệt'))
            ->get();

        return view('giangvien.detai.index', compact('detais', 'hocKies', 'gv', 'NhomChuaCoDeTai'));
    }

    public function downloadTemplate()
    {
        $filePath = public_path('templates/Mau_De_Cuong_Khoa_Luan.docx');
        if (!file_exists($filePath)) {
            return redirect()->back()->withErrors('Không tìm thấy file biểu mẫu đề cương.');
        }
        return response()->download($filePath, 'Mau_De_Cuong_Chi_Tiet_KLTN_HUIT.docx');
    }

    public function create()
    {
        $user = Auth::user();
        $gv = GiangVien::where('MaTK', $user->MaTK)->first();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $nganhs = \App\Models\Nganh::orderBy('TenNganh')->get();

        return view('giangvien.detai.create', compact('hocKies', 'gv', 'nganhs'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $gv = GiangVien::where('MaTK', $user->MaTK)->firstOrFail();

        $request->validate([
            'TenDeTai' => 'required|string|max:300',
            'MaHocKy' => 'required|exists:HocKy,MaHocKy',
            'HocPhan' => 'nullable|string|max:150',
            'MaNganh' => 'nullable|exists:Nganh,MaNganh',
            'SoLuongSinhVienToiDa' => 'required|integer|min:1|max:3',
            'MoTa' => 'nullable|string',
            'YeuCau' => 'nullable|string',
            'LinhVuc' => 'nullable|string|max:150',
            'FileDeCuong' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ], [
            'TenDeTai.required' => 'Vui lòng nhập tên đề tài.',
            'MaHocKy.required' => 'Vui lòng chọn học kỳ áp dụng.',
            'SoLuongSinhVienToiDa.required' => 'Vui lòng nhập số sinh viên tối đa.',
            'SoLuongSinhVienToiDa.min' => 'Số sinh viên tối đa ít nhất là 1.',
            'SoLuongSinhVienToiDa.max' => 'Số sinh viên tối đa không vượt quá 3.',
            'FileDeCuong.mimes' => 'Đề cương phải có định dạng .pdf, .doc hoặc .docx.',
            'FileDeCuong.max' => 'Dung lượng file đề cương không được vượt quá 10MB.',
        ]);

        $fileDeCuongPath = null;
        if ($request->hasFile('FileDeCuong') && $request->file('FileDeCuong')->isValid()) {
            $file = $request->file('FileDeCuong');
            $filename = 'de_cuong_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('de_cuong', $filename, 'public');
            $fileDeCuongPath = 'storage/' . $path;
        }

        $count = DeTai::count() + 1;
        $maDT = 'DT' . sprintf('%02d', $count);
        while (DeTai::where('MaDeTai', $maDT)->exists()) {
            $count++;
            $maDT = 'DT' . sprintf('%02d', $count);
        }

        DeTai::create([
            'MaDeTai' => $maDT,
            'MaGV' => $gv->MaGV,
            'TenDeTai' => $request->TenDeTai,
            'MoTa' => $request->MoTa,
            'YeuCau' => $request->YeuCau,
            'LinhVuc' => $request->LinhVuc ?? 'Công Nghệ Thông Tin',
            'HocPhan' => $request->HocPhan ?? 'Khóa luận tốt nghiệp',
            'SoLuongSinhVienToiDa' => (int)$request->SoLuongSinhVienToiDa,
            'FileDeCuong' => $fileDeCuongPath,
            'MaNganh' => $request->MaNganh,
            'MaHocKy' => $request->MaHocKy,
            'TrangThai' => 'Chờ duyệt',
            'NgayDeXuat' => now(),
        ]);

        return redirect()->route('giangvien.detai.index')->with('success', 'Đề xuất đề tài mới thành công! Vui lòng chờ Giáo vụ Khoa phê duyệt.');
    }

    public function edit($id)
    {
        $user = Auth::user();
        $gv = GiangVien::where('MaTK', $user->MaTK)->firstOrFail();
        $detai = DeTai::where('MaDeTai', $id)->where('MaGV', $gv->MaGV)->firstOrFail();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $nganhs = \App\Models\Nganh::orderBy('TenNganh')->get();

        return view('giangvien.detai.edit', compact('detai', 'hocKies', 'nganhs'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $gv = GiangVien::where('MaTK', $user->MaTK)->firstOrFail();
        $detai = DeTai::where('MaDeTai', $id)->where('MaGV', $gv->MaGV)->firstOrFail();

        $request->validate([
            'TenDeTai' => 'required|string|max:300',
            'MaHocKy' => 'required|exists:HocKy,MaHocKy',
            'HocPhan' => 'nullable|string|max:150',
            'MaNganh' => 'nullable|exists:Nganh,MaNganh',
            'SoLuongSinhVienToiDa' => 'required|integer|min:1|max:3',
            'MoTa' => 'nullable|string',
            'YeuCau' => 'nullable|string',
            'LinhVuc' => 'nullable|string|max:150',
            'FileDeCuong' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $data = [
            'TenDeTai' => $request->TenDeTai,
            'MaHocKy' => $request->MaHocKy,
            'HocPhan' => $request->HocPhan ?? $detai->HocPhan ?? 'Khóa luận tốt nghiệp',
            'MaNganh' => $request->MaNganh,
            'SoLuongSinhVienToiDa' => (int)$request->SoLuongSinhVienToiDa,
            'MoTa' => $request->MoTa,
            'YeuCau' => $request->YeuCau,
            'LinhVuc' => $request->LinhVuc,
        ];

        if ($request->hasFile('FileDeCuong') && $request->file('FileDeCuong')->isValid()) {
            $file = $request->file('FileDeCuong');
            $filename = 'de_cuong_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('de_cuong', $filename, 'public');
            $data['FileDeCuong'] = 'storage/' . $path;
        }

        if (in_array($detai->TrangThai, ['Yêu cầu điều chỉnh', 'Từ chối'])) {
            $data['TrangThai'] = 'Chờ duyệt';
            $data['NgayDeXuat'] = now();
        }

        $detai->update($data);

        return redirect()->route('giangvien.detai.index')->with('success', 'Cập nhật đề tài thành công!' . ($detai->wasChanged('TrangThai') ? ' Đề tài đã được nộp lại cho Giáo vụ rà soát.' : ''));
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $gv = GiangVien::where('MaTK', $user->MaTK)->firstOrFail();
        $detai = DeTai::where('MaDeTai', $id)->where('MaGV', $gv->MaGV)->firstOrFail();

        try {
            $detai->delete();
            return redirect()->route('giangvien.detai.index')->with('success', 'Xóa đề tài thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors('Không thể xóa đề tài này do đã có sinh viên đăng ký.');
        }
    }

    public function ganNhom(Request $request, $id)
    {
        $request->validate([
            'MaNhom' => 'required|exists:Nhom,MaNhom',
        ], [
            'MaNhom.required' => 'Vui lòng chọn nhóm sinh viên cần gán.',
        ]);

        $user = Auth::user();
        $gv = GiangVien::where('MaTK', $user->MaTK)->firstOrFail();
        $detai = DeTai::where('MaDeTai', $id)->where('MaGV', $gv->MaGV)->firstOrFail();

        if ($detai->TrangThai !== 'Đã duyệt') {
            return redirect()->back()->withErrors('Chỉ đề tài đã được Giáo vụ phê duyệt mới có thể gán nhóm thực hiện!');
        }

        $nhom = Nhom::with('thanhViens')->findOrFail($request->MaNhom);

        // Kiểm tra nhóm phải đủ 3 thành viên chính thức
        $countMembers = $nhom->thanhViens->where('TrangThai', 'da_tham_gia')->count();
        if ($countMembers < 3) {
            return redirect()->back()->withErrors("Nhóm '{$nhom->TenNhom}' hiện chỉ có {$countMembers}/3 thành viên. Nhóm cần đủ 3 thành viên mới được gán đề tài!");
        }

        DB::transaction(function () use ($detai, $nhom, $gv) {
            // Hủy các đơn đăng ký cũ nếu có của nhóm hoặc đề tài này
            DangKyDeTai::where('MaDeTai', $detai->MaDeTai)->delete();
            DangKyDeTai::where('MaNhom', $nhom->MaNhom)->delete();

            $maDK = 'DK_' . Str::upper(Str::random(6));

            DangKyDeTai::create([
                'MaDangKy'     => $maDK,
                'MaNhom'       => $nhom->MaNhom,
                'MaDeTai'      => $detai->MaDeTai,
                'MaGVHuongDan' => $gv->MaGV,
                'NgayDangKy'   => now(),
                'NgayDuyet'    => now(),
                'TrangThai'    => 'Đã duyệt',
            ]);

            $nhom->update([
                'MaDeTai'   => $detai->MaDeTai,
                'TrangThai' => 'Đã duyệt',
            ]);
        });

        // Gửi thông báo cho nhóm
        ThongBaoService::guiDenNhom(
            $nhom->MaNhom,
            '✅ Giảng viên đã gán đề tài cho nhóm!',
            "Giảng viên {$gv->HoTen} đã trực tiếp gán nhóm của bạn vào đề tài: '{$detai->TenDeTai}'",
            'Đề tài'
        );

        return redirect()->route('giangvien.detai.index')
            ->with('success', "Đã gán thành công Nhóm '{$nhom->TenNhom}' vào đề tài '{$detai->TenDeTai}'!");
    }

    public function myTasks()
    {
        $user = Auth::user();
        $gv = GiangVien::where('MaTK', $user->MaTK)->first();
        if (!$gv) {
            return redirect()->route('giangvien.dashboard');
        }

        $Nhom = Nhom::with(['deTai', 'truongNhom', 'dangKyDeTai', 'baoCaos', 'hoSoBaoVe'])
            ->where(function($query) use ($gv) {
                $query->whereHas('dangKyDeTai', function($q) use ($gv) {
                    $q->where('MaGVHuongDan', $gv->MaGV)->where('TrangThai', 'Đã duyệt');
                })->orWhereHas('deTai', function($q) use ($gv) {
                    $q->where('MaGV', $gv->MaGV);
                });
            })->get();

        $stats = [
            'sap_den_han'  => 0,
            'qua_han'      => 0,
            'cho_nhan_xet' => 0,
        ];

        $today = \Carbon\Carbon::today();
        foreach ($Nhom as $nhom) {
            foreach ($nhom->baoCaos as $bc) {
                if (empty($bc->NhanXet)) {
                    $stats['cho_nhan_xet']++;
                }
            }
        }

        return view('giangvien.my_tasks', compact('Nhom', 'stats'));
    }
}