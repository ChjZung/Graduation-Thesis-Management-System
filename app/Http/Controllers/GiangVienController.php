<?php

namespace App\Http\Controllers;

use App\Helpers\IdGenerator;
use App\Http\Traits\HandlesExcelImport;
use App\Models\GiangVien;
use App\Models\BoMon;
use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GiangVienController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $query = GiangVien::with(['boMon.khoa', 'taiKhoan']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $query->where(function ($q) use ($s, $sClean) {
                $q->where('HoTen', 'LIKE', "%{$s}%")
                  ->orWhere('Email', 'LIKE', "%{$s}%")
                  ->orWhere('MaGV', 'LIKE', "%{$s}%")
                  ->orWhereHas('taiKhoan', fn($t) => $t->where('TenDangNhap', 'LIKE', "%{$s}%"));
                if (!empty($sClean)) {
                    $q->orWhere('MaGV', 'LIKE', "%{$sClean}%")
                      ->orWhereHas('taiKhoan', fn($t) => $t->where('TenDangNhap', 'LIKE', "%{$sClean}%"));
                }
            });
        }

        if ($request->filled('MaBoMon')) {
            $query->where('MaBoMon', $request->MaBoMon);
        }

        // Lấy danh sách tên Trưởng khoa và Trưởng bộ môn trong hệ thống
        $truongKhoaNames = \App\Models\Khoa::whereNotNull('TruongKhoa')->pluck('TruongKhoa')->filter()->toArray();
        $truongBoMonNames = BoMon::whereNotNull('TruongBoMon')->pluck('TruongBoMon')->filter()->toArray();
        $allChucVuNames = array_unique(array_merge($truongKhoaNames, $truongBoMonNames));

        // Bộ lọc theo Chức vụ / Vai trò
        if ($request->filled('ChucVu')) {
            $cv = $request->ChucVu;
            if ($cv === 'TK') {
                $query->where(function($q) use ($truongKhoaNames) {
                    $q->whereIn('HoTen', $truongKhoaNames)
                      ->orWhereHas('taiKhoan', fn($t) => $t->where('MaVaiTro', 'VT05')->orWhere('TenDangNhap', 'LIKE', 'TK_%'));
                });
            } elseif ($cv === 'TBM') {
                $query->where(function($q) use ($truongBoMonNames) {
                    $q->whereIn('HoTen', $truongBoMonNames)
                      ->orWhereHas('taiKhoan', fn($t) => $t->where('MaVaiTro', 'VT04')->orWhere('TenDangNhap', 'LIKE', 'TBM_%'));
                });
            } elseif ($cv === 'co_chuc_vu') {
                $query->where(function($q) use ($allChucVuNames) {
                    $q->whereIn('HoTen', $allChucVuNames)
                      ->orWhereHas('taiKhoan', fn($t) => $t->whereIn('MaVaiTro', ['VT04', 'VT05'])->orWhere('TenDangNhap', 'LIKE', 'TK_%')->orWhere('TenDangNhap', 'LIKE', 'TBM_%'));
                });
            } elseif ($cv === 'GV') {
                $query->whereNotIn('HoTen', $allChucVuNames)
                      ->whereDoesntHave('taiKhoan', fn($t) => $t->whereIn('MaVaiTro', ['VT04', 'VT05'])->orWhere('TenDangNhap', 'LIKE', 'TK_%')->orWhere('TenDangNhap', 'LIKE', 'TBM_%'));
            }
        }

        $currentHk = \App\Models\HocKy::where('TrangThai', 'Đang diễn ra')->first() ?? \App\Models\HocKy::orderBy('MaHocKy', 'desc')->first();
        $currentMaHocKy = $currentHk?->MaHocKy;

        $totalGV = GiangVien::count();
        $gvHuongDan = GiangVien::whereHas('deTais', function($q) use ($currentMaHocKy) {
            $q->whereIn('TrangThai', ['Đã công bố', 'Trưởng khoa đã duyệt', 'Đã duyệt', 'Đã đăng ký', 'Hoàn thành']);
            if ($currentMaHocKy) {
                $q->where('MaHocKy', $currentMaHocKy);
            }
        })->count();
        $gvHoiDong = GiangVien::has('thanhVienHoiDongs')->count();
        $gvActive = GiangVien::whereHas('taiKhoan', fn($tk) => $tk->where('TrangThai', true))->count();

        // Đếm số giảng viên đang giữ chức vụ
        $gvChucVuCount = GiangVien::where(function($q) use ($allChucVuNames) {
            $q->whereIn('HoTen', $allChucVuNames)
              ->orWhereHas('taiKhoan', fn($t) => $t->whereIn('MaVaiTro', ['VT04', 'VT05'])->orWhere('TenDangNhap', 'LIKE', 'TK_%')->orWhere('TenDangNhap', 'LIKE', 'TBM_%'));
        })->count();

        $stats = [
            'total'     => $totalGV,
            'huong_dan' => $gvHuongDan,
            'hoi_dong'  => $gvHoiDong,
            'active'    => $gvActive,
            'chuc_vu'   => $gvChucVuCount,
        ];

        $giangviens = $query->withCount([
            'deTais' => function($q) use ($currentMaHocKy) {
                $q->whereIn('TrangThai', ['Đã công bố', 'Trưởng khoa đã duyệt', 'Đã duyệt', 'Đã đăng ký', 'Hoàn thành']);
                if ($currentMaHocKy) {
                    $q->where('MaHocKy', $currentMaHocKy);
                }
            }
        ])->orderBy('MaGV')->paginate(10)->withQueryString();
        $bomons = BoMon::with('khoa')->orderBy('TenBoMon')->get();

        // Lấy danh sách các tài khoản chức vụ hiện có để đối chiếu nhanh
        $tkChucVus = TaiKhoan::whereIn('MaVaiTro', ['VT04', 'VT05'])
            ->orWhere('TenDangNhap', 'LIKE', 'TK_%')
            ->orWhere('TenDangNhap', 'LIKE', 'TBM_%')
            ->get();

        return view('admin.giangvien.index', compact(
            'giangviens',
            'bomons',
            'stats',
            'truongKhoaNames',
            'truongBoMonNames',
            'tkChucVus'
        ));
    }

    public function create()
    {
        $bomons = BoMon::with('khoa')->orderBy('TenBoMon')->get();
        return view('admin.giangvien.create', compact('bomons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenDangNhap' => [
                'required',
                'string',
                'size:10',
                'regex:/^[A-Za-z0-9_]{10}$/',
                'unique:TaiKhoan,TenDangNhap',
                'unique:GiangVien,MaGV'
            ],
            'HoTen'       => 'required|string|max:100',
            'HocVi'       => 'required|string|max:50',
            'MaBoMon'     => 'required|exists:BoMon,MaBoMon',
            'Email'       => 'required|email|max:100|unique:GiangVien,Email',
            'SoDienThoai' => [
                'nullable',
                'string',
                'regex:/^0[0-9]{9}$/',
                'unique:GiangVien,SoDienThoai'
            ]
        ], [
            'TenDangNhap.required' => 'Vui lòng nhập Mã giảng viên (MaGV).',
            'TenDangNhap.size'     => 'Mã giảng viên phải có đúng 10 ký tự (min=10, max=10, ví dụ: GV00000001).',
            'TenDangNhap.regex'    => 'Mã giảng viên chỉ gồm chữ và số, không chứa ký tự đặc biệt hay khoảng trắng.',
            'TenDangNhap.unique'   => 'Mã giảng viên / Tên đăng nhập đã tồn tại trong hệ thống.',
            'HoTen.required'       => 'Vui lòng nhập họ tên giảng viên.',
            'HocVi.required'       => 'Vui lòng chọn hoặc nhập học vị.',
            'MaBoMon.required'     => 'Vui lòng chọn bộ môn.',
            'Email.required'       => 'Vui lòng nhập email.',
            'Email.email'          => 'Định dạng email không hợp lệ.',
            'Email.unique'         => 'Email đã được sử dụng bởi giảng viên khác.',
            'SoDienThoai.unique'   => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex'    => 'Số điện thoại phải có đúng 10 chữ số (bắt đầu bằng 0, min=10, max=10, ví dụ: 0912345678).',
        ]);

        $maGV = trim($request->TenDangNhap);

        DB::transaction(function () use ($request, $maGV) {
            $tk = TaiKhoan::create([
                'MaTK'              => $maGV,
                'TenDangNhap'       => $maGV,
                'MatKhau'           => Hash::make('123456'),
                'MaVaiTro'          => 'VT02',
                'TrangThai'         => true,
                'TrangThaiMatKhau'  => 'INITIAL',
                'BatBuocDoiMatKhau' => true,
                'SoLanDangNhapSai'  => 0,
            ]);

            GiangVien::create([
                'MaGV'        => $maGV,
                'MaTK'        => $tk->MaTK,
                'MaBoMon'     => $request->MaBoMon,
                'HoTen'       => trim($request->HoTen),
                'Email'       => trim($request->Email),
                'SoDienThoai' => $request->SoDienThoai ? trim($request->SoDienThoai) : null,
                'HocVi'       => trim($request->HocVi),
                'TrangThai'   => 'Đang công tác',
            ]);
        });

        return redirect()->route('giangvien.index', ['tab' => 'danhsach'])->with('success', "Thêm giảng viên '{$request->HoTen}' (Mã GV: {$maGV}) thành công! (Mật khẩu mặc định: 123456)");
    }

    public function show($id)
    {
        $giangvien = GiangVien::with([
            'boMon.khoa',
            'taiKhoan',
            'deTais.hocKy',
            'deTais.phieuDangKys.nhom.sinhViens',
            'thanhVienHoiDongs.hoiDong.hocKy',
            'chiTieuHuongDans.hocKy',
        ])->withCount([
            'deTais' => fn($q) => $q->whereIn('TrangThai', ['Đã công bố', 'Trưởng khoa đã duyệt', 'Đã duyệt', 'Đã đăng ký', 'Hoàn thành']),
            'thanhVienHoiDongs'
        ])->findOrFail($id);

        $totalDeTai = $giangvien->de_tais_count;
        $totalHoiDong = $giangvien->thanh_vien_hoi_dongs_count;
        $nhomHuongDan = \App\Models\Nhom::whereHas('phieuDangKys', function($q) use ($id) {
            $q->where('TrangThai', 'Đã duyệt')
              ->whereHas('deTai', fn($dq) => $dq->where('MaGV', $id));
        })->count();

        $latestChiTieu = $giangvien->chiTieuHuongDans()->latest()->first();
        $chiTieuToiDa = $latestChiTieu ? $latestChiTieu->SoLuongToiDa : 5;

        $stats = [
            'total_detai'    => min((int)$totalDeTai, 5),
            'nhom_huong_dan' => min((int)$nhomHuongDan, 5),
            'chi_tieu_toida' => $chiTieuToiDa,
            'total_hoidong'  => $totalHoiDong,
        ];

        return view('admin.giangvien.show', compact('giangvien', 'stats'));
    }

    public function edit($id)
    {
        $giangvien = GiangVien::with('taiKhoan')->findOrFail($id);
        $bomons = BoMon::with('khoa')->orderBy('TenBoMon')->get();
        return view('admin.giangvien.edit', compact('giangvien', 'bomons'));
    }

    public function update(Request $request, $id)
    {
        $giangvien = GiangVien::with('taiKhoan')->findOrFail($id);

        $request->validate([
            'HoTen'       => 'required|string|max:100',
            'HocVi'       => 'required|string|max:50',
            'MaBoMon'     => 'required|exists:BoMon,MaBoMon',
            'Email'       => 'required|email|max:100|unique:GiangVien,Email,' . $id . ',MaGV',
            'SoDienThoai' => [
                'nullable',
                'string',
                'regex:/^0[0-9]{9}$/',
                'unique:GiangVien,SoDienThoai,' . $id . ',MaGV'
            ]
        ], [
            'HoTen.required'     => 'Vui lòng nhập họ tên.',
            'HocVi.required'     => 'Vui lòng nhập học vị.',
            'MaBoMon.required'   => 'Vui lòng chọn bộ môn.',
            'Email.required'     => 'Vui lòng nhập email.',
            'Email.email'        => 'Định dạng email không hợp lệ.',
            'Email.unique'       => 'Email đã được sử dụng.',
            'SoDienThoai.unique' => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex'  => 'Số điện thoại phải có đúng 10 chữ số (bắt đầu bằng số 0, min=10, max=10, ví dụ: 0912345678).',
        ]);

        $giangvien->update([
            'MaBoMon'     => $request->MaBoMon,
            'HoTen'       => trim($request->HoTen),
            'Email'       => trim($request->Email),
            'SoDienThoai' => $request->SoDienThoai ? trim($request->SoDienThoai) : null,
            'HocVi'       => trim($request->HocVi),
        ]);

        return redirect()->route('giangvien.index')->with('success', 'Cập nhật thông tin giảng viên thành công!');
    }

    public function destroy($id)
    {
        $giangvien = GiangVien::findOrFail($id);
        $maTK = $giangvien->MaTK;

        try {
            DB::transaction(function () use ($giangvien, $maTK) {
                $giangvien->delete();
                if ($maTK) {
                    TaiKhoan::destroy($maTK);
                }
            });
            return redirect()->route('giangvien.index')->with('success', "Xóa giảng viên '{$giangvien->HoTen}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa giảng viên '{$giangvien->HoTen}' do đang có đề tài, hướng dẫn hoặc hội đồng liên quan.");
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importGiangVien', [], 'Giảng viên');
    }

    /**
     * Bổ nhiệm Giảng viên làm Trưởng khoa hoặc Trưởng bộ môn
     * Quy tắc riêng biệt:
     * - Giảng viên thường: Mã GV (VD: GV00000001) - Role VT02
     * - Trưởng khoa: TK_{MaKhoa}_{MaGV} (Role VT05) - KHÔNG đè lên tài khoản GV
     * - Trưởng bộ môn: TBM_{MaBoMon}_{MaGV} (Role VT04) - KHÔNG đè lên tài khoản GV
     */
    public function boNhiem(Request $request, $id)
    {
        $gv = GiangVien::with('boMon.khoa', 'taiKhoan')->findOrFail($id);

        $request->validate([
            'chuc_vu' => 'required|in:TK,TBM,HUY',
            'ma_khoa' => 'required_if:chuc_vu,TK|nullable|exists:Khoa,MaKhoa',
            'ma_bomon' => 'required_if:chuc_vu,TBM|nullable|exists:BoMon,MaBoMon',
        ], [
            'chuc_vu.required' => 'Vui lòng chọn chức vụ bổ nhiệm hoặc chọn Hủy chức vụ.',
            'ma_khoa.required_if' => 'Vui lòng chọn Khoa bổ nhiệm.',
            'ma_bomon.required_if' => 'Vui lòng chọn Bộ môn bổ nhiệm.',
        ]);

        $chucVu = $request->chuc_vu;

        try {
            $msgSuccess = '';
            DB::transaction(function() use ($gv, $chucVu, $request, &$msgSuccess) {
                // 1. Luôn đảm bảo tài khoản GV gốc của giảng viên có vai trò VT02 (Giảng viên)
                $tkGV = TaiKhoan::where('TenDangNhap', $gv->MaGV)->first();
                if (!$tkGV) {
                    $tkGV = TaiKhoan::create([
                        'MaTK'              => $gv->MaGV,
                        'TenDangNhap'       => $gv->MaGV,
                        'MatKhau'           => Hash::make('123456'),
                        'MaVaiTro'          => 'VT02',
                        'TrangThai'         => true,
                        'TrangThaiMatKhau'  => 'INITIAL',
                        'BatBuocDoiMatKhau' => false,
                        'SoLanDangNhapSai'  => 0,
                    ]);
                    $gv->update(['MaTK' => $tkGV->MaTK]);
                } else {
                    $tkGV->update(['MaVaiTro' => 'VT02']);
                }

                // 2. Trường hợp Hủy chức vụ: Xóa các tài khoản chức vụ riêng biệt và gỡ tên khỏi Khoa/Bộ môn
                if ($chucVu === 'HUY') {
                    TaiKhoan::where('TenDangNhap', 'LIKE', "TK_%_{$gv->MaGV}")
                        ->orWhere('TenDangNhap', 'LIKE', "TBM_%_{$gv->MaGV}")
                        ->delete();

                    \App\Models\Khoa::where('TruongKhoa', $gv->HoTen)->update(['TruongKhoa' => null]);
                    BoMon::where('TruongBoMon', $gv->HoTen)->update(['TruongBoMon' => null]);

                    $msgSuccess = "Đã hủy chức vụ và thu hồi tài khoản chức năng của giảng viên '{$gv->HoTen}'. Tài khoản Giảng viên [{$gv->MaGV}] vẫn được giữ nguyên vai trò Giảng viên bình thường.";
                    return;
                }

                // 3. Trường hợp Bổ nhiệm Trưởng khoa (TK)
                if ($chucVu === 'TK') {
                    $maKhoa = $request->ma_khoa;
                    $usernameTK = 'TK_' . $maKhoa . '_' . $gv->MaGV;

                    // Xóa tài khoản TBM cũ nếu có
                    TaiKhoan::where('TenDangNhap', 'LIKE', "TBM_%_{$gv->MaGV}")->delete();
                    BoMon::where('TruongBoMon', $gv->HoTen)->update(['TruongBoMon' => null]);

                    // Tạo tài khoản chức vụ Trưởng khoa riêng biệt
                    TaiKhoan::updateOrCreate(
                        ['TenDangNhap' => $usernameTK],
                        [
                            'MaTK'              => $usernameTK,
                            'MatKhau'           => Hash::make('123456'),
                            'MaVaiTro'          => 'VT05',
                            'TrangThai'         => true,
                            'TrangThaiMatKhau'  => 'ACTIVE',
                            'BatBuocDoiMatKhau' => false,
                            'SoLanDangNhapSai'  => 0,
                        ]
                    );

                    \App\Models\Khoa::where('MaKhoa', $maKhoa)->update(['TruongKhoa' => $gv->HoTen]);

                    $msgSuccess = "Đã bổ nhiệm giảng viên '{$gv->HoTen}' làm Trưởng khoa! Cấp tài khoản chức vụ riêng: [{$usernameTK}] (Role: Trưởng khoa). Tài khoản giảng viên [{$gv->MaGV}] vẫn được giữ nguyên vai trò Giảng viên.";
                } else {
                    // 4. Trường hợp Bổ nhiệm Trưởng bộ môn (TBM)
                    $maBM = $request->ma_bomon;
                    $bm = BoMon::findOrFail($maBM);
                    $usernameTBM = 'TBM_' . $maBM . '_' . $gv->MaGV;

                    // Xóa tài khoản TK cũ nếu có
                    TaiKhoan::where('TenDangNhap', 'LIKE', "TK_%_{$gv->MaGV}")->delete();
                    \App\Models\Khoa::where('TruongKhoa', $gv->HoTen)->update(['TruongKhoa' => null]);

                    // Tạo tài khoản chức vụ Trưởng bộ môn riêng biệt
                    TaiKhoan::updateOrCreate(
                        ['TenDangNhap' => $usernameTBM],
                        [
                            'MaTK'              => $usernameTBM,
                            'MatKhau'           => Hash::make('123456'),
                            'MaVaiTro'          => 'VT04',
                            'TrangThai'         => true,
                            'TrangThaiMatKhau'  => 'ACTIVE',
                            'BatBuocDoiMatKhau' => false,
                            'SoLanDangNhapSai'  => 0,
                        ]
                    );

                    $bm->update(['TruongBoMon' => $gv->HoTen]);
                    $gv->update(['MaBoMon' => $maBM]);

                    $msgSuccess = "Đã bổ nhiệm giảng viên '{$gv->HoTen}' làm Trưởng bộ môn! Cấp tài khoản chức vụ riêng: [{$usernameTBM}] (Role: Trưởng bộ môn). Tài khoản giảng viên [{$gv->MaGV}] vẫn được giữ nguyên vai trò Giảng viên.";
                }
            });

            return redirect()->route('giangvien.index', ['tab' => 'taikhoan'])->with('success', $msgSuccess);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors("Lỗi khi xử lý chức vụ: " . $e->getMessage());
        }
    }

    /**
     * Hủy chức vụ Trưởng khoa / Trưởng bộ môn đã cấp cho giảng viên
     */
    public function huyBoNhiem($id)
    {
        $gv = GiangVien::with('taiKhoan')->findOrFail($id);

        try {
            DB::transaction(function () use ($gv) {
                // Xóa các tài khoản chức vụ riêng biệt
                TaiKhoan::where('TenDangNhap', 'LIKE', "TK_%_{$gv->MaGV}")
                    ->orWhere('TenDangNhap', 'LIKE', "TBM_%_{$gv->MaGV}")
                    ->delete();

                // Gỡ tên khỏi bảng Khoa và BoMon
                \App\Models\Khoa::where('TruongKhoa', $gv->HoTen)->update(['TruongKhoa' => null]);
                BoMon::where('TruongBoMon', $gv->HoTen)->update(['TruongBoMon' => null]);

                // Đảm bảo tài khoản giảng viên gốc vẫn là VT02
                TaiKhoan::where('TenDangNhap', $gv->MaGV)->update(['MaVaiTro' => 'VT02']);
            });

            return redirect()->route('giangvien.index', ['tab' => 'taikhoan'])->with(
                'success',
                "Đã hủy chức vụ đã cấp của giảng viên '{$gv->HoTen}' thành công! Tài khoản chức năng đã bị xóa, tài khoản Giảng viên [{$gv->MaGV}] vẫn hoạt động bình thường."
            );
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Lỗi khi hủy chức vụ: " . $e->getMessage());
        }
    }

    /**
     * Xóa tài khoản Portal đã cấp cho Giảng viên (giữ nguyên hồ sơ Giảng viên)
     */
    public function xoaTaiKhoanPortal($id)
    {
        $gv = GiangVien::with('taiKhoan')->findOrFail($id);

        try {
            DB::transaction(function () use ($gv) {
                // Xóa tài khoản chức vụ nếu có
                TaiKhoan::where('TenDangNhap', 'LIKE', "TK_%_{$gv->MaGV}")
                    ->orWhere('TenDangNhap', 'LIKE', "TBM_%_{$gv->MaGV}")
                    ->delete();

                // Xóa tài khoản GV gốc
                TaiKhoan::where('TenDangNhap', $gv->MaGV)->orWhere('MaTK', $gv->MaTK)->delete();

                // Gỡ tên chức vụ nếu có
                \App\Models\Khoa::where('TruongKhoa', $gv->HoTen)->update(['TruongKhoa' => null]);
                BoMon::where('TruongBoMon', $gv->HoTen)->update(['TruongBoMon' => null]);

                // Gỡ liên kết ở bảng giảng viên
                $gv->update(['MaTK' => null]);
            });

            return redirect()->route('giangvien.index', ['tab' => 'taikhoan'])->with(
                'success',
                "Đã xóa toàn bộ tài khoản Portal đã cấp cho giảng viên '{$gv->HoTen}' thành công! Hồ sơ giảng viên vẫn được giữ nguyên an toàn."
            );
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Lỗi khi xóa tài khoản portal: " . $e->getMessage());
        }
    }

    /**
     * Import danh sách tài khoản Trưởng khoa & Trưởng bộ môn từ file Excel/CSV
     */
    public function importTaiKhoanTBM_TK(Request $request)
    {
        return $this->runImport($request, 'importTaiKhoanTBM_TK', [], 'Tài khoản Trưởng khoa / Trưởng bộ môn');
    }

    /**
     * Xuất danh sách Giảng viên ra file Excel / CSV (hỗ trợ bộ lọc tìm kiếm & bộ môn)
     */
    public function exportExcel(Request $request)
    {
        $query = GiangVien::with(['boMon.khoa', 'taiKhoan.vaiTro'])->withCount('deTais');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('HoTen', 'LIKE', "%{$s}%")
                  ->orWhere('Email', 'LIKE', "%{$s}%")
                  ->orWhere('MaGV', 'LIKE', "%{$s}%")
                  ->orWhereHas('taiKhoan', fn($t) => $t->where('TenDangNhap', 'LIKE', "%{$s}%"));
            });
        }

        if ($request->filled('MaBoMon')) {
            $query->where('MaBoMon', $request->MaBoMon);
        }

        $giangviens = $query->orderBy('MaGV')->get();

        $filename = "DS_GIANG_VIEN_KHOA_CNTT_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-Type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($giangviens) {
            $file = fopen('php://output', 'w');
            // BOM UTF-8 để Microsoft Excel nhận diện tiếng Việt có dấu
            fputs($file, "\xEF\xBB\xBF");

            // Header hàng tiêu đề
            fputcsv($file, [
                'STT',
                'Mã Giảng Viên',
                'Họ và Tên',
                'Học Vị',
                'Học Hàm',
                'Giới Tính',
                'Email',
                'Số Điện Thoại',
                'Bộ Môn',
                'Khoa',
                'Vai Trò Portal',
                'Số Đề Tài Hướng Dẫn',
                'Trạng Thái Công Tác',
                'Tài Khoản Portal'
            ]);

            foreach ($giangviens as $idx => $gv) {
                $tk = $gv->taiKhoan;
                $vaiTro = $tk?->vaiTro?->TenVaiTro ?? 'Giảng viên';
                $trangThaiTK = ($tk && $tk->TrangThai) ? 'Đang hoạt động' : 'Chưa kích hoạt / Khóa';

                fputcsv($file, [
                    $idx + 1,
                    $gv->MaGV,
                    $gv->HoTen,
                    $gv->HocVi ?? 'Cử nhân / Kỹ sư',
                    $gv->HocHam ?? '—',
                    $gv->GioiTinh ?? '—',
                    $gv->Email ?? '—',
                    $gv->SoDienThoai ?? '—',
                    $gv->boMon->TenBoMon ?? 'Chưa phân bổ',
                    $gv->boMon->khoa->TenKhoa ?? 'Khoa Công nghệ Thông tin',
                    $vaiTro,
                    $gv->de_tais_count ?? 0,
                    $gv->TrangThai ?? 'Đang công tác',
                    $trangThaiTK
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}