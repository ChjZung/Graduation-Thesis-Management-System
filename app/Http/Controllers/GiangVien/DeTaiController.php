<?php
namespace App\Http\Controllers\GiangVien;
use App\Http\Controllers\Controller;
use App\Http\Traits\HandlesExcelImport;
use App\Models\DeTai;
use App\Models\MonHoc;
use App\Models\HocKy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeTaiController extends Controller
{
    use HandlesExcelImport;
    public function index(Request $request) {
        $maTK = Auth::user()->MaTK;
        $gv = \App\Models\GiangVien::where('MaTK', $maTK)->firstOrFail();
        
        $phanCongs = \App\Models\PhanCongHuongDanLop::with(['lop', 'hocKy'])->where('MaGV', $gv->MaGV)->get();
        $lops = $phanCongs->pluck('lop')->filter()->unique('MaLop');
        $hockys = \App\Models\HocKy::all();
        $monhocs = \App\Models\MonHoc::all();

        $query = DeTai::with(['monHoc', 'lop', 'hocKy'])
                        ->where('MaTK', $maTK);

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('MaLop')) {
            $query->where('MaLop', $request->MaLop);
        }

        if ($request->filled('MaMon')) {
            $query->where('MaMon', $request->MaMon);
        }

        if ($request->filled('search')) {
            $query->where('TenDeTai', 'LIKE', '%' . trim($request->search) . '%');
        }

        $detais = $query->orderBy('MaDeTai', 'desc')->paginate(10);

        return view('giangvien.detai.index', compact('detais', 'lops', 'hockys', 'monhocs'));
    }

    public function create() {
        $gv = \App\Models\GiangVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $phanCongs = \App\Models\PhanCongHuongDanLop::with('lop')->where('MaGV', $gv->MaGV)->get();
        $lops = $phanCongs->pluck('lop')->filter()->unique('MaLop');

        $monhocs = MonHoc::all();
        $hockys = HocKy::all();
        return view('giangvien.detai.create', compact('monhocs', 'hockys', 'lops'));
    }

    public function store(Request $request) {
        $gv = \App\Models\GiangVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();

        $request->validate([
            'TenDeTai' => 'required|string|max:200',
            'MaMon' => 'required|exists:mon_hocs,MaMon',
            'MaLop' => 'required|exists:lops,MaLop',
            'MaHocKy' => 'required|exists:hoc_kies,MaHocKy',
            'HanDangKy' => 'nullable|date',
            'HanBaoCao' => 'nullable|date|after_or_equal:HanDangKy',
            'HanNopSanPham' => 'nullable|date|after_or_equal:HanBaoCao',
        ], [
            'TenDeTai.required' => 'Vui lòng nhập tên đề tài.',
            'MaLop.required' => 'Vui lòng chọn lớp phụ trách.',
            'HanBaoCao.after_or_equal' => 'Hạn báo cáo phải sau hoặc bằng hạn đăng ký.',
            'HanNopSanPham.after_or_equal' => 'Hạn nộp sản phẩm phải sau hoặc bằng hạn báo cáo.',
        ]);

        // Kiểm tra phân công giảng viên đối với lớp
        $isAssigned = \App\Models\PhanCongHuongDanLop::where('MaGV', $gv->MaGV)
                                                     ->where('MaLop', $request->MaLop)
                                                     ->exists();
        if (!$isAssigned) {
            return redirect()->back()->withErrors('Bạn chưa được phân công phụ trách lớp này!')->withInput();
        }

        // Kiểm tra trùng tên đề tài trong lớp
        $exists = DeTai::where('TenDeTai', $request->TenDeTai)->where('MaLop', $request->MaLop)->exists();
        if ($exists) {
            return redirect()->back()->withErrors("Đề tài '{$request->TenDeTai}' đã tồn tại trong lớp này!")->withInput();
        }

        $dt = DeTai::create([
            'MaTK' => Auth::user()->MaTK,
            'MaMon' => $request->MaMon,
            'MaLop' => $request->MaLop,
            'MaHocKy' => $request->MaHocKy,
            'TenDeTai' => $request->TenDeTai,
            'MoTa' => $request->MoTa,
            'YeuCau' => $request->YeuCau,
            'HanDangKy' => $request->HanDangKy,
            'HanBaoCao' => $request->HanBaoCao,
            'HanNopSanPham' => $request->HanNopSanPham,
            'TrangThai' => 'Đang mở đăng ký',
            'NgayTao' => date('Y-m-d')
        ]);

        \App\Models\AuditLog::log('tao_de_tai', 'DeTai', $dt->MaDeTai, ['TenDeTai' => $dt->TenDeTai]);

        return redirect()->route('giangvien.detai.index')->with('success', 'Thêm đề tài thành công!');
    }

    public function edit($id) {
        $detai = DeTai::where('MaDeTai', $id)->where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $gv = \App\Models\GiangVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $phanCongs = \App\Models\PhanCongHuongDanLop::with('lop')->where('MaGV', $gv->MaGV)->get();
        $lops = $phanCongs->pluck('lop')->filter()->unique('MaLop');

        $monhocs = MonHoc::all();
        $hockys = HocKy::all();
        return view('giangvien.detai.edit', compact('detai', 'monhocs', 'hockys', 'lops'));
    }

    public function update(Request $request, $id) {
        $detai = DeTai::where('MaDeTai', $id)->where('MaTK', Auth::user()->MaTK)->firstOrFail();
        
        $request->validate([
            'TenDeTai' => 'required|string|max:200',
            'MaMon' => 'required|exists:mon_hocs,MaMon',
            'MaLop' => 'required|exists:lops,MaLop',
            'MaHocKy' => 'required|exists:hoc_kies,MaHocKy',
            'HanDangKy' => 'nullable|date',
            'HanBaoCao' => 'nullable|date|after_or_equal:HanDangKy',
            'HanNopSanPham' => 'nullable|date|after_or_equal:HanBaoCao',
        ]);

        $detai->update($request->only(['TenDeTai', 'MoTa', 'YeuCau', 'MaMon', 'MaLop', 'MaHocKy', 'TrangThai', 'HanDangKy', 'HanBaoCao', 'HanNopSanPham']));
        
        \App\Models\AuditLog::log('cap_nhat_de_tai', 'DeTai', $id, ['TenDeTai' => $request->TenDeTai]);

        return redirect()->route('giangvien.detai.index')->with('success', 'Cập nhật đề tài thành công!');
    }

    public function destroy($id) {
        $detai = DeTai::where('MaDeTai', $id)->where('MaTK', Auth::user()->MaTK)->firstOrFail();

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($detai, $id) {
                // Xóa các bản ghi đăng ký đề tài và phân công hướng dẫn liên quan
                \App\Models\DangKyDeTai::where('MaDeTai', $id)->delete();
                \App\Models\HuongDan::where('MaDeTai', $id)->delete();

                // Xóa tệp tài liệu đính kèm nếu có
                if (!empty($detai->FileTaiLieu) && \Illuminate\Support\Facades\Storage::disk('public')->exists($detai->FileTaiLieu)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($detai->FileTaiLieu);
                }

                \App\Models\AuditLog::log('xoa_de_tai', 'DeTai', $id, ['TenDeTai' => $detai->TenDeTai]);
                $detai->delete();
            });

            return redirect()->route('giangvien.detai.index')->with('success', 'Xóa đề tài thành công!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Xóa đề tài lỗi: ' . $e->getMessage());
            return redirect()->back()->withErrors('Không thể xóa đề tài này do đang vướng ràng buộc dữ liệu liên quan.');
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importDeTai', [Auth::user()->MaTK], 'Đề Tài');
    }

    public function uploadTaiLieu(\App\Http\Requests\UploadTaiLieuRequest $request, $id)
    {
        $detai = DeTai::where('MaDeTai', $id)->where('MaTK', Auth::user()->MaTK)->firstOrFail();

        if ($request->hasFile('file_tai_lieu')) {
            $file = $request->file('file_tai_lieu');
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = 'detai_' . $detai->MaDeTai . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;

            // Xóa file cũ nếu có
            if (!empty($detai->FileTaiLieu) && \Illuminate\Support\Facades\Storage::disk('public')->exists($detai->FileTaiLieu)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($detai->FileTaiLieu);
            }

            $path = $file->storeAs('tai_lieu_de_tai', $filename, 'public');
            $detai->update(['FileTaiLieu' => $path]);

            \App\Models\AuditLog::log('upload_tai_lieu_de_tai', 'DeTai', $id, ['path' => $path]);

            return redirect()->back()->with('success', 'Tải lên tài liệu đính kèm đề tài thành công!');
        }

        return redirect()->back()->withErrors('Vui lòng chọn tệp hợp lệ.');
    }

    public function downloadTaiLieu($id)
    {
        $detai = DeTai::where('MaDeTai', $id)->firstOrFail();

        if (empty($detai->FileTaiLieu) || !\Illuminate\Support\Facades\Storage::disk('public')->exists($detai->FileTaiLieu)) {
            return redirect()->back()->withErrors('Tài liệu đính kèm không tồn tại hoặc đã bị xóa.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($detai->FileTaiLieu);
    }

    public function deleteTaiLieu($id)
    {
        $detai = DeTai::where('MaDeTai', $id)->where('MaTK', Auth::user()->MaTK)->firstOrFail();

        if (!empty($detai->FileTaiLieu) && \Illuminate\Support\Facades\Storage::disk('public')->exists($detai->FileTaiLieu)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($detai->FileTaiLieu);
        }

        $detai->update(['FileTaiLieu' => null]);
        return redirect()->back()->with('success', 'Xóa tài liệu đính kèm thành công!');
    }
}