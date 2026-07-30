<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\NhomDoAn;
use App\Models\ThanhVienNhom;
use App\Models\LoiMoiNhom;
use App\Models\SinhVien;
use App\Models\HocKy;
use App\Models\MonHoc;
use App\Models\DeTai;
use App\Models\AuditLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NhomController extends Controller
{
    public function index()
    {
        $sinhVien = SinhVien::with('lop')->where('MaTK', Auth::user()->MaTK)->firstOrFail();

        // 1. Danh sách tất cả các nhóm mà sinh viên đang tham gia
        $nhomIds = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->whereIn('TrangThai', ['da_chap_nhan', 'da_tham_gia'])
            ->pluck('MaNhom');

        $nhoms = NhomDoAn::whereIn('MaNhom', $nhomIds)
            ->with(['thanhVienNhoms.sinhVien.lop', 'chamDiem', 'monHoc', 'hocKy', 'dangKyDeTai.deTai.giangVien', 'sanPhams'])
            ->get();

        // Nạp tiến độ báo cáo cho từng nhóm
        foreach ($nhoms as $n) {
            $n->baoCaos = \App\Models\BaoCaoTienDo::with('nhanXets')
                ->where('MaNhom', $n->MaNhom)
                ->orderBy('LanBaoCao', 'desc')
                ->get();
        }

        // 2. Lời mời tham gia nhóm đang chờ xác nhận
        $loiMois = LoiMoiNhom::with(['nhomDoAn.monHoc', 'sinhVienMoi'])
                            ->where('MaSV_DuocMoi', $sinhVien->MaSV)
                            ->where('TrangThai', 'cho_xac_nhan')
                            ->get();

        $hockys = HocKy::all();
        $currentHocKy = HocKy::latest('MaHocKy')->first();
        $currentHocKyId = $currentHocKy->MaHocKy ?? 1;

        // 3. Môn học thuộc lớp sinh viên & chưa có nhóm trong học kỳ hiện tại
        $subjectIdsForClass = DeTai::where('MaLop', $sinhVien->MaLop)->pluck('MaMon')->toArray();
        $allClassMonHocs = empty($subjectIdsForClass) 
            ? MonHoc::all() 
            : MonHoc::whereIn('MaMon', array_unique($subjectIdsForClass))->get();

        // Môn học mà sinh viên ĐÃ CÓ NHÓM trong học kỳ này
        $joinedMonIds = NhomDoAn::whereIn('MaNhom', $nhomIds)
            ->where('MaHocKy', $currentHocKyId)
            ->pluck('MaMon')
            ->toArray();

        // Lọc môn học sinh viên CHƯA tham gia nhóm
        $availableMonHocs = $allClassMonHocs->reject(function ($mh) use ($joinedMonIds) {
            return in_array($mh->MaMon, $joinedMonIds);
        });

        return view('sinhvien.nhom.index', compact('nhoms', 'sinhVien', 'loiMois', 'hockys', 'availableMonHocs', 'currentHocKyId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenNhom' => 'required|string|max:100',
            'MaMon' => 'required|exists:mon_hocs,MaMon',
            'MaHocKy' => 'required|exists:hoc_kies,MaHocKy'
        ], [
            'TenNhom.required' => 'Vui lòng nhập tên nhóm đồ án.',
            'MaMon.required' => 'Vui lòng chọn môn học.',
            'MaHocKy.required' => 'Vui lòng chọn học kỳ.'
        ]);

        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();

        // Kiểm tra sinh viên đã thuộc nhóm nào trong môn học này ở học kỳ này chưa
        $alreadyInGroup = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->whereHas('nhomDoAn', function ($q) use ($request) {
                $q->where('MaMon', $request->MaMon)
                  ->where('MaHocKy', $request->MaHocKy);
            })->exists();

        if ($alreadyInGroup) {
            return redirect()->back()->withErrors('Bạn đã có nhóm đồ án cho môn học này trong học kỳ đã chọn. Mỗi môn học chỉ được tham gia duy nhất một nhóm!')->withInput();
        }

        try {
            DB::beginTransaction();

            $nhom = NhomDoAn::create([
                'TenNhom' => $request->TenNhom,
                'MaMon' => $request->MaMon,
                'MaHocKy' => $request->MaHocKy,
                'TruongNhom' => $sinhVien->MaSV,
                'TrangThai' => 'Đang hoạt động'
            ]);

            ThanhVienNhom::create([
                'MaNhom' => $nhom->MaNhom,
                'MaSV' => $sinhVien->MaSV,
                'VaiTro' => 'Trưởng nhóm',
                'TrangThai' => 'da_tham_gia'
            ]);

            DB::commit();

            AuditLog::log('tao_nhom', 'NhomDoAn', $nhom->MaNhom, ['TenNhom' => $nhom->TenNhom, 'MaMon' => $request->MaMon]);

            return redirect()->back()->with('success', "Tạo nhóm '{$nhom->TenNhom}' thành công!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Lỗi khi tạo nhóm: ' . $e->getMessage());
        }
    }

    /**
     * Autocomplete API: Tìm kiếm sinh viên CÙNG LỚP chưa có nhóm môn học này
     */
    public function searchSV(Request $request)
    {
        $term = trim($request->get('q', ''));
        $maNhom = $request->get('maNhom');
        $svCurrent = SinhVien::where('MaTK', Auth::user()->MaTK)->first();

        if (!$svCurrent || empty($term)) {
            return response()->json([]);
        }

        $nhom = $maNhom ? NhomDoAn::find($maNhom) : null;
        $maMon = $nhom ? $nhom->MaMon : null;
        $maHocKy = $nhom ? $nhom->MaHocKy : null;

        $query = SinhVien::with('taiKhoan')
            ->where('MaLop', $svCurrent->MaLop)
            ->where('MaSV', '!=', $svCurrent->MaSV)
            ->where(function ($q) use ($term) {
                $q->where('HoTen', 'LIKE', "%{$term}%")
                  ->orWhereHas('taiKhoan', function ($tkQ) use ($term) {
                      $tkQ->where('TenDangNhap', 'LIKE', "%{$term}%");
                  });
            });

        // Loại bỏ những sinh viên đã ở trong nhóm thuộc cùng môn học & học kỳ
        if ($maMon && $maHocKy) {
            $busySvIds = ThanhVienNhom::whereHas('nhomDoAn', function ($q) use ($maMon, $maHocKy) {
                $q->where('MaMon', $maMon)->where('MaHocKy', $maHocKy);
            })->pluck('MaSV')->toArray();

            $query->whereNotIn('MaSV', $busySvIds);
        }

        // Loại bỏ sinh viên đã có lời mời chờ xác nhận vào nhóm này
        if ($maNhom) {
            $invitedSvIds = LoiMoiNhom::where('MaNhom', $maNhom)
                ->where('TrangThai', 'cho_xac_nhan')
                ->pluck('MaSV_DuocMoi')->toArray();

            $query->whereNotIn('MaSV', $invitedSvIds);
        }

        $results = $query->limit(10)->get()->map(function ($sv) {
            $mssv = $sv->taiKhoan->TenDangNhap ?? '';
            return [
                'id' => $sv->MaSV,
                'mssv' => $mssv,
                'name' => $sv->HoTen,
                'text' => "{$sv->HoTen} ({$mssv})"
            ];
        });

        return response()->json($results);
    }

    /**
     * Mời thành viên tham gia nhóm
     */
    public function moiThanhVien(Request $request)
    {
        $request->validate([
            'MaNhom' => 'required|exists:nhom_do_ans,MaNhom',
            'TenDangNhap_Them' => 'required|string'
        ], [
            'MaNhom.required' => 'Vui lòng chọn nhóm.',
            'TenDangNhap_Them.required' => 'Vui lòng nhập MSSV / Tên đăng nhập của sinh viên.'
        ]);

        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $nhom = NhomDoAn::findOrFail($request->MaNhom);

        if ($nhom->TruongNhom != $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Chỉ trưởng nhóm mới có quyền mời thành viên!');
        }

        // 1. Kiểm tra sĩ số nhóm (tối đa 5 thành viên)
        $count = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)->count();
        if ($count >= 5) {
            return redirect()->back()->withErrors('Nhóm đã đạt số lượng tối đa 5 thành viên!');
        }

        $taiKhoanThem = \App\Models\TaiKhoan::where('TenDangNhap', trim($request->TenDangNhap_Them))
                                            ->where('MaVaiTro', 3)
                                            ->first();

        if (!$taiKhoanThem) {
            return redirect()->back()->withErrors('Không tìm thấy tài khoản sinh viên với MSSV này.');
        }

        $svThem = SinhVien::where('MaTK', $taiKhoanThem->MaTK)->first();
        if (!$svThem) {
            return redirect()->back()->withErrors('Không tìm thấy dữ liệu sinh viên.');
        }

        // 2. Kiểm tra không tự mời chính mình
        if ($svThem->MaSV == $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Bạn không thể tự mời chính mình!');
        }

        // 3. Kiểm tra phải cùng lớp
        if ($svThem->MaLop != $sinhVien->MaLop) {
            return redirect()->back()->withErrors('Bạn chỉ có thể mời sinh viên học CÙNG LỚP với bạn!');
        }

        // 4. Kiểm tra sinh viên đã ở trong nhóm môn học này chưa
        $inOtherGroup = ThanhVienNhom::where('MaSV', $svThem->MaSV)
            ->whereHas('nhomDoAn', function ($q) use ($nhom) {
                $q->where('MaMon', $nhom->MaMon)
                  ->where('MaHocKy', $nhom->MaHocKy);
            })->exists();

        if ($inOtherGroup) {
            return redirect()->back()->withErrors('Sinh viên này đã tham gia một nhóm khác cho môn học này!');
        }

        // 5. Kiểm tra lời mời trùng lặp
        $existingInvite = LoiMoiNhom::where('MaNhom', $nhom->MaNhom)
            ->where('MaSV_DuocMoi', $svThem->MaSV)
            ->where('TrangThai', 'cho_xac_nhan')
            ->exists();

        if ($existingInvite) {
            return redirect()->back()->withErrors('Lời mời đã được gửi trước đó và đang chờ xác nhận.');
        }

        LoiMoiNhom::create([
            'MaNhom' => $nhom->MaNhom,
            'MaSV_Moi' => $sinhVien->MaSV,
            'MaSV_DuocMoi' => $svThem->MaSV,
            'TrangThai' => 'cho_xac_nhan',
            'NgayMoi' => now()
        ]);

        $notiService = new NotificationService();
        $notiService->guiLoiMoiNhom($svThem, $nhom, $sinhVien);

        AuditLog::log('moi_thanh_vien', 'LoiMoiNhom', $nhom->MaNhom, ['MaSV_DuocMoi' => $svThem->MaSV]);

        return redirect()->back()->with('success', "Đã gửi lời mời tham gia nhóm '{$nhom->TenNhom}' đến sinh viên {$svThem->HoTen}!");
    }

    /**
     * Đồng ý gia nhập nhóm
     */
    public function xacNhanLoiMoi($id)
    {
        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $loiMoi = LoiMoiNhom::with('nhomDoAn')->where('id', $id)->where('MaSV_DuocMoi', $sinhVien->MaSV)->firstOrFail();

        if ($loiMoi->TrangThai != 'cho_xac_nhan') {
            return redirect()->back()->withErrors('Lời mời này đã được xử lý trước đó.');
        }

        $nhom = $loiMoi->nhomDoAn;

        // 1. Kiểm tra sĩ số nhóm
        $count = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)->count();
        if ($count >= 5) {
            $loiMoi->update(['TrangThai' => 'da_tu_choi', 'NgayPhanHoi' => now()]);
            return redirect()->back()->withErrors('Không thể gia nhập: Nhóm đã đủ 5 thành viên tối đa!');
        }

        // 2. Kiểm tra nếu sinh viên đã lỡ gia nhập nhóm khác trong cùng môn học
        $alreadyInGroup = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->whereHas('nhomDoAn', function ($q) use ($nhom) {
                $q->where('MaMon', $nhom->MaMon)
                  ->where('MaHocKy', $nhom->MaHocKy);
            })->exists();

        if ($alreadyInGroup) {
            $loiMoi->update(['TrangThai' => 'da_tu_choi', 'NgayPhanHoi' => now()]);
            return redirect()->back()->withErrors('Bạn đã ở trong một nhóm khác của môn học này rồi!');
        }

        DB::transaction(function () use ($loiMoi, $nhom, $sinhVien) {
            ThanhVienNhom::create([
                'MaNhom' => $nhom->MaNhom,
                'MaSV' => $sinhVien->MaSV,
                'VaiTro' => 'Thành viên',
                'TrangThai' => 'da_tham_gia'
            ]);

            $loiMoi->update([
                'TrangThai' => 'da_chap_nhan',
                'NgayPhanHoi' => now()
            ]);
        });

        // Thông báo cho Trưởng nhóm
        $notiService = new NotificationService();
        $notiService->guiChapNhanLoiMoi($nhom, $sinhVien);

        AuditLog::log('chap_nhan_loi_moi', 'LoiMoiNhom', $loiMoi->id, ['MaNhom' => $nhom->MaNhom]);

        return redirect()->back()->with('success', "Bạn đã tham gia nhóm '{$nhom->TenNhom}' thành công!");
    }

    /**
     * Từ chối lời mời
     */
    public function tuChoiLoiMoi($id)
    {
        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $loiMoi = LoiMoiNhom::with('nhomDoAn')->where('id', $id)->where('MaSV_DuocMoi', $sinhVien->MaSV)->firstOrFail();

        $loiMoi->update([
            'TrangThai' => 'da_tu_choi',
            'NgayPhanHoi' => now()
        ]);

        if ($loiMoi->nhomDoAn) {
            $notiService = new NotificationService();
            $notiService->guiTuChoiLoiMoi($loiMoi->nhomDoAn, $sinhVien);
        }

        AuditLog::log('tu_choi_loi_moi', 'LoiMoiNhom', $loiMoi->id, ['MaNhom' => $loiMoi->MaNhom]);

        return redirect()->back()->with('success', 'Đã từ chối lời mời tham gia nhóm.');
    }
}