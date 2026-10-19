<?php

namespace App\Http\Controllers;

use App\Helpers\IdGenerator;
use App\Models\GiangVien;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Models\Nhom;
use App\Models\LichGapHuongDan;
use App\Models\DangKyLichHuongDan;
use App\Services\ThongBaoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LichHenHuongDanController extends Controller
{
    /**
     * GIẢNG VIÊN: Tạo mới khung giờ rảnh trong tuần
     */
    public function taoKhungGio(Request $request)
    {
        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $request->validate([
            'NgayGap'      => 'required|date|after_or_equal:today',
            'GioBatDau'    => 'required|date_format:H:i',
            'GioKetThuc'   => 'required|date_format:H:i',
            'HinhThuc'     => 'required|in:TrucTiep,TrucTuyen',
            'DiaDiem'      => 'nullable|string|max:200',
            'LinkCuocHop'  => 'nullable|url|max:500',
            'NoiDung'      => 'required|string|max:1000',
            'SoNhomToiDa'  => 'required|integer|min:1|max:10',
        ], [
            'NgayGap.after_or_equal' => 'Ngày gặp không được nằm trong quá khứ.',
            'GioBatDau.date_format'  => 'Giờ bắt đầu không đúng định dạng (HH:mm).',
            'GioKetThuc.date_format' => 'Giờ kết thúc không đúng định dạng (HH:mm).',
            'LinkCuocHop.url'        => 'Đường dẫn phòng họp trực tuyến phải là URL hợp lệ (bắt đầu bằng http:// hoặc https://).',
            'SoNhomToiDa.min'        => 'Số nhóm tối đa phải từ 1 nhóm trở lên.',
        ]);

        $ngayGap = $request->NgayGap;
        $start = Carbon::parse("{$ngayGap} {$request->GioBatDau}:00");
        $end = Carbon::parse("{$ngayGap} {$request->GioKetThuc}:00");

        // 1. Kiểm tra giờ kết thúc phải lớn hơn giờ bắt đầu
        if ($end->lte($start)) {
            return redirect()->back()->withInput()->with('error', 'Giờ kết thúc phải lớn hơn giờ bắt đầu.');
        }

        // 2. Không cho tạo lịch trong quá khứ so với thời điểm hiện tại
        if ($start->lt(now())) {
            return redirect()->back()->withInput()->with('error', 'Thời gian bắt đầu không được nhỏ hơn thời điểm hiện tại.');
        }

        // 3. Kiểm tra hình thức
        if ($request->HinhThuc === 'TrucTiep' && empty(trim($request->DiaDiem))) {
            return redirect()->back()->withInput()->with('error', 'Vui lòng nhập địa điểm gặp trực tiếp (phòng làm việc/văn phòng bộ môn).');
        }
        if ($request->HinhThuc === 'TrucTuyen' && empty(trim($request->LinkCuocHop))) {
            return redirect()->back()->withInput()->with('error', 'Vui lòng cung cấp liên kết họp trực tuyến (Google Meet, MS Teams, Zoom...).');
        }

        // 4. Kiểm soát chống trùng lặp / chồng lấn khung giờ của chính Giảng viên
        $overlap = LichGapHuongDan::where('MaGV', $giangVien->MaGV)
            ->where('TrangThai', '!=', 'DaHuy')
            ->where(function ($q) use ($start, $end) {
                $q->where('ThoiGianBatDau', '<', $end)
                  ->where('ThoiGianKetThuc', '>', $start);
            })->exists();

        if ($overlap) {
            return redirect()->back()->withInput()->with('error', 'Khung giờ này bị trùng lặp hoặc chồng lấn với một khung giờ khác Thầy/Cô đã tạo trước đó.');
        }

        // 5. Lưu vào CSDL
        $slot = DB::transaction(function () use ($request, $giangVien, $start, $end, $ngayGap) {
            $maLich = IdGenerator::nextLichGap();

            return LichGapHuongDan::create([
                'MaLichGap'       => $maLich,
                'ThoiGianBatDau'  => $start,
                'ThoiGianKetThuc' => $end,
                'DiaDiem'         => $request->HinhThuc === 'TrucTiep' ? trim($request->DiaDiem) : null,
                'HinhThuc'        => $request->HinhThuc,
                'LinkCuocHop'     => $request->HinhThuc === 'TrucTuyen' ? trim($request->LinkCuocHop) : null,
                'NoiDung'         => trim(strip_tags($request->NoiDung)),
                'SoNhomToiDa'     => (int) $request->SoNhomToiDa,
                'SoNhomDaDangKy'  => 0,
                'TrangThai'       => 'MoDangKy',
                'NgayTao'         => now()->toDateString(),
                'MaGV'            => $giangVien->MaGV,
                'MaNhom'          => null,
            ]);
        });

        // 6. Gửi thông báo đến các nhóm sinh viên đang được giảng viên hướng dẫn
        try {
            $nhoms = Nhom::whereHas('phieuDangKys', function ($q) use ($giangVien) {
                $q->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])
                  ->whereHas('deTai', fn ($d) => $d->where('MaGV', $giangVien->MaGV));
            })->get();

            $thoiGianStr = $start->format('H:i') . ' - ' . $end->format('H:i') . ' ngày ' . $start->format('d/m/Y');
            $hinhThucStr = ($request->HinhThuc === 'TrucTiep') ? "Trực tiếp ({$request->DiaDiem})" : "Trực tuyến";

            foreach ($nhoms as $nhom) {
                ThongBaoService::guiDenNhom(
                    $nhom->MaNhom,
                    'GVHD mở khung giờ hẹn hướng dẫn mới',
                    "Thầy/Cô {$giangVien->HoTen} vừa mở khung giờ hẹn hướng dẫn mới: {$thoiGianStr} [{$hinhThucStr}]. Nhóm hãy truy cập Lịch hẹn hướng dẫn để đăng ký nếu có nhu cầu.",
                    'Lịch hẹn'
                );
            }
        } catch (\Throwable $e) {
            // Không làm gián đoạn transaction nếu gửi thông báo thất bại
        }

        return redirect()->back()->with('success', 'Tạo khung giờ hướng dẫn thành công! Sinh viên đã có thể xem và đặt lịch.');
    }

    /**
     * GIẢNG VIÊN: Cập nhật thông tin khung giờ (chỉ khi chưa có ai đăng ký)
     */
    public function capNhatKhungGio(Request $request, $id)
    {
        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(403);
        }

        $slot = LichGapHuongDan::with('dangKyHieuLucs')->findOrFail($id);
        if ($slot->MaGV !== $giangVien->MaGV) {
            abort(403, 'Bạn không có quyền chỉnh sửa khung giờ của giảng viên khác.');
        }

        if ($slot->dangKyHieuLucs->isNotEmpty() || $slot->SoNhomDaDangKy > 0) {
            return redirect()->back()->with('error', 'Khung giờ này đã có nhóm sinh viên đăng ký, không thể thay đổi thông tin. Nếu có thay đổi, Thầy/Cô vui lòng hủy khung giờ kèm lý do để hệ thống thông báo cho các bên.');
        }

        $request->validate([
            'NgayGap'      => 'required|date|after_or_equal:today',
            'GioBatDau'    => 'required|date_format:H:i',
            'GioKetThuc'   => 'required|date_format:H:i',
            'HinhThuc'     => 'required|in:TrucTiep,TrucTuyen',
            'DiaDiem'      => 'nullable|string|max:200',
            'LinkCuocHop'  => 'nullable|url|max:500',
            'NoiDung'      => 'required|string|max:1000',
            'SoNhomToiDa'  => 'required|integer|min:1|max:10',
        ]);

        $start = Carbon::parse("{$request->NgayGap} {$request->GioBatDau}:00");
        $end = Carbon::parse("{$request->NgayGap} {$request->GioKetThuc}:00");

        if ($end->lte($start)) {
            return redirect()->back()->withInput()->with('error', 'Giờ kết thúc phải lớn hơn giờ bắt đầu.');
        }
        if ($start->lt(now())) {
            return redirect()->back()->withInput()->with('error', 'Thời gian bắt đầu không được nhỏ hơn thời điểm hiện tại.');
        }

        // Kiểm tra trùng lấn (loại trừ chính khung giờ đang sửa)
        $overlap = LichGapHuongDan::where('MaGV', $giangVien->MaGV)
            ->where('MaLichGap', '!=', $slot->MaLichGap)
            ->where('TrangThai', '!=', 'DaHuy')
            ->where(function ($q) use ($start, $end) {
                $q->where('ThoiGianBatDau', '<', $end)
                  ->where('ThoiGianKetThuc', '>', $start);
            })->exists();

        if ($overlap) {
            return redirect()->back()->withInput()->with('error', 'Khung giờ điều chỉnh bị trùng lặp với một khung giờ khác Thầy/Cô đã tạo.');
        }

        $slot->update([
            'ThoiGianBatDau'  => $start,
            'ThoiGianKetThuc' => $end,
            'DiaDiem'         => $request->HinhThuc === 'TrucTiep' ? trim($request->DiaDiem) : null,
            'HinhThuc'        => $request->HinhThuc,
            'LinkCuocHop'     => $request->HinhThuc === 'TrucTuyen' ? trim($request->LinkCuocHop) : null,
            'NoiDung'         => trim(strip_tags($request->NoiDung)),
            'SoNhomToiDa'     => (int) $request->SoNhomToiDa,
        ]);

        return redirect()->back()->with('success', 'Cập nhật thông tin khung giờ hướng dẫn thành công.');
    }

    /**
     * GIẢNG VIÊN: Đóng hoặc mở đăng ký khung giờ
     */
    public function chuyenTrangThai(Request $request, $id)
    {
        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(403);
        }

        $slot = LichGapHuongDan::findOrFail($id);
        if ($slot->MaGV !== $giangVien->MaGV) {
            abort(403);
        }

        if ($slot->TrangThai === 'DaHuy') {
            return redirect()->back()->with('error', 'Khung giờ đã hủy không thể mở lại.');
        }

        if ($slot->ThoiGianBatDau < now()) {
            return redirect()->back()->with('error', 'Khung giờ đã qua thời gian hẹn, không thể mở lại đăng ký.');
        }

        if ($slot->TrangThai === 'DongDangKy') {
            // Mở lại đăng ký (nếu chưa đầy chỗ)
            $newStatus = ($slot->so_cho_con_lai > 0) ? 'MoDangKy' : 'DaDuCho';
            $slot->update(['TrangThai' => $newStatus]);
            $msg = 'Đã mở lại đăng ký cho khung giờ này.';
        } else {
            // Đóng đăng ký
            $slot->update(['TrangThai' => 'DongDangKy']);
            $msg = 'Đã tạm đóng nhận đăng ký cho khung giờ này.';
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * GIẢNG VIÊN: Hủy toàn bộ khung giờ (kèm lý do và thông báo cho các bên)
     */
    public function huyKhungGio(Request $request, $id)
    {
        $request->validate([
            'LyDoHuy' => 'required|string|max:1000',
        ], [
            'LyDoHuy.required' => 'Vui lòng cung cấp lý do hủy khung giờ để thông báo đến sinh viên.',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(403);
        }

        $slot = LichGapHuongDan::with(['dangKys.nhom'])->findOrFail($id);
        if ($slot->MaGV !== $giangVien->MaGV) {
            abort(403);
        }

        if ($slot->TrangThai === 'DaHuy') {
            return redirect()->back()->with('error', 'Khung giờ này đã ở trạng thái hủy trước đó.');
        }

        $lyDoHuy = trim(strip_tags($request->LyDoHuy));

        DB::transaction(function () use ($slot, $lyDoHuy, $giangVien) {
            $slot->update([
                'TrangThai' => 'DaHuy',
                'LyDoHuy'   => $lyDoHuy,
            ]);

            // Cập nhật tất cả lượt đăng ký còn hiệu lực thành Đã hủy
            $activeBookings = $slot->dangKys()->where('TrangThai', 'DaXacNhan')->get();

            foreach ($activeBookings as $bk) {
                $bk->update([
                    'TrangThai'   => 'DaHuy',
                    'LyDoHuy'     => $lyDoHuy,
                    'NguoiHuy'    => 'GiangVien',
                    'ThoiGianHuy' => now(),
                ]);

                // Thông báo đến nhóm
                $thoiGianStr = $slot->ThoiGianBatDau->format('H:i d/m/Y');
                ThongBaoService::guiDenNhom(
                    $bk->MaNhom,
                    'GVHD đã hủy buổi hẹn hướng dẫn',
                    "Buổi hẹn hướng dẫn vào lúc {$thoiGianStr} đã bị hủy bởi Thầy/Cô {$giangVien->HoTen}. Lý do hủy: {$lyDoHuy}. Nhóm vui lòng kiểm tra và chọn khung giờ khác.",
                    'Lịch hẹn'
                );
            }
        });

        return redirect()->back()->with('success', 'Đã hủy khung giờ và gửi thông báo lý do hủy đến các nhóm sinh viên liên quan.');
    }

    /**
     * GIẢNG VIÊN: Hủy một lượt đặt lịch của nhóm cụ thể
     */
    public function huyLuotDatCuaNhom(Request $request, $id, $dangKyId)
    {
        $request->validate([
            'LyDoHuy' => 'required|string|max:1000',
        ], [
            'LyDoHuy.required' => 'Vui lòng cung cấp lý do hủy lịch hẹn của nhóm.',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(403);
        }

        $slot = LichGapHuongDan::findOrFail($id);
        if ($slot->MaGV !== $giangVien->MaGV) {
            abort(403);
        }

        $booking = DangKyLichHuongDan::where('id', $dangKyId)
            ->where('MaLichGap', $slot->MaLichGap)
            ->firstOrFail();

        if ($booking->TrangThai === 'DaHuy') {
            return redirect()->back()->with('error', 'Lượt đặt lịch này đã bị hủy trước đó.');
        }

        $lyDoHuy = trim(strip_tags($request->LyDoHuy));

        DB::transaction(function () use ($booking, $slot, $lyDoHuy, $giangVien) {
            $booking->update([
                'TrangThai'   => 'DaHuy',
                'LyDoHuy'     => $lyDoHuy,
                'NguoiHuy'    => 'GiangVien',
                'ThoiGianHuy' => now(),
            ]);

            // Cập nhật lại số nhóm đã đăng ký
            $activeCount = DangKyLichHuongDan::where('MaLichGap', $slot->MaLichGap)
                ->where('TrangThai', 'DaXacNhan')
                ->count();

            $updateData = [
                'SoNhomDaDangKy' => $activeCount,
            ];

            // Nếu khung giờ đang là DaDuCho hoặc DongDangKy mà còn chỗ trong tương lai -> mở lại MoDangKy
            if ($slot->TrangThai === 'DaDuCho' && $activeCount < $slot->SoNhomToiDa && $slot->ThoiGianBatDau > now()) {
                $updateData['TrangThai'] = 'MoDangKy';
            }

            if ($activeCount === 0) {
                $updateData['MaNhom'] = null;
            }

            $slot->update($updateData);

            // Gửi thông báo đến nhóm
            $thoiGianStr = $slot->ThoiGianBatDau->format('H:i d/m/Y');
            ThongBaoService::guiDenNhom(
                $booking->MaNhom,
                'Lịch hẹn hướng dẫn đã bị hủy bởi GVHD',
                "Lịch hẹn lúc {$thoiGianStr} của nhóm bạn với Thầy/Cô {$giangVien->HoTen} đã bị hủy. Lý do: {$lyDoHuy}. Nhóm có thể chọn khung giờ khác.",
                'Lịch hẹn'
            );
        });

        return redirect()->back()->with('success', 'Đã hủy lịch hẹn của nhóm và hoàn lại chỗ trống cho khung giờ.');
    }

    /**
     * SINH VIÊN: Đặt lịch theo khung giờ có sẵn của GVHD (Concurrency control & Transaction)
     */
    public function datLich(Request $request, $id)
    {
        $request->validate([
            'GhiChu' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $sinhVien = SinhVien::getLoggedInSinhVien($user);
        if (!$sinhVien) {
            abort(403, 'Không tìm thấy thông tin sinh viên.');
        }

        // Tìm nhóm hiện tại của sinh viên
        $tv = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->first();

        if (!$tv) {
            return redirect()->back()->with('error', 'Bạn chưa tham gia nhóm khóa luận nào nên không thể đặt lịch hẹn hướng dẫn.');
        }

        $nhom = Nhom::with(['deTai.giangVien', 'phieuDangKys.deTai.giangVien'])->find($tv->MaNhom);
        if (!$nhom) {
            return redirect()->back()->with('error', 'Không tìm thấy hồ sơ nhóm khóa luận.');
        }

        // Xác định Giảng viên hướng dẫn chính thức của nhóm
        $gvhd = $nhom->deTai?->giangVien 
            ?? $nhom->phieuDangKys()->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])->latest()->first()?->deTai?->giangVien;

        if (!$gvhd) {
            return redirect()->back()->with('error', 'Nhóm của bạn chưa có Đề tài và Giảng viên hướng dẫn chính thức được phê duyệt.');
        }

        // THỰC HIỆN ĐẶT LỊCH VỚI DB TRANSACTION VÀ PESSIMISTIC LOCKING
        try {
            DB::transaction(function () use ($request, $id, $sinhVien, $nhom, $gvhd) {
                // Khóa hàng khung giờ để chống race condition khi nhiều người bấm cùng lúc
                $slot = LichGapHuongDan::where('MaLichGap', $id)->lockForUpdate()->firstOrFail();

                // 1. Kiểm tra quyền: Khung giờ phải thuộc đúng GVHD của nhóm
                if ($slot->MaGV !== $gvhd->MaGV) {
                    throw new \Exception('Khung giờ này không thuộc Giảng viên hướng dẫn của nhóm bạn.');
                }

                // 2. Kiểm tra trạng thái khung giờ
                if ($slot->TrangThai !== 'MoDangKy') {
                    throw new \Exception('Khung giờ này hiện không ở trạng thái mở đăng ký hoặc đã bị đóng/hủy.');
                }

                // 3. Kiểm tra quá hạn
                if ($slot->ThoiGianBatDau < now()) {
                    throw new \Exception('Khung giờ này đã qua thời gian hẹn, không thể đăng ký.');
                }

                // 4. Kiểm tra số chỗ còn lại (Pessimistic Check)
                $activeCount = DangKyLichHuongDan::where('MaLichGap', $slot->MaLichGap)
                    ->where('TrangThai', 'DaXacNhan')
                    ->lockForUpdate()
                    ->count();

                if ($activeCount >= ($slot->SoNhomToiDa ?: 1)) {
                    $slot->update([
                        'TrangThai'       => 'DaDuCho',
                        'SoNhomDaDangKy'  => $activeCount,
                    ]);
                    throw new \Exception('Khung giờ này vừa có nhóm khác đăng ký và đã hết chỗ. Vui lòng chọn khung giờ khác.');
                }

                // 5. Kiểm tra nhóm đã đăng ký khung giờ này chưa
                $daDatSlotNay = DangKyLichHuongDan::where('MaLichGap', $slot->MaLichGap)
                    ->where('MaNhom', $nhom->MaNhom)
                    ->where('TrangThai', 'DaXacNhan')
                    ->exists();

                if ($daDatSlotNay) {
                    throw new \Exception('Nhóm của bạn đã đăng ký khung giờ này rồi, không thể đặt lặp lại.');
                }

                // 6. Kiểm tra nhóm có lịch hẹn khác trùng khoảng thời gian không
                $trungThoiGian = DangKyLichHuongDan::where('MaNhom', $nhom->MaNhom)
                    ->where('TrangThai', 'DaXacNhan')
                    ->whereHas('lichGap', function ($q) use ($slot) {
                        $q->where('ThoiGianBatDau', '<', $slot->ThoiGianKetThuc)
                          ->where('ThoiGianKetThuc', '>', $slot->ThoiGianBatDau);
                    })->exists();

                if ($trungThoiGian) {
                    throw new \Exception('Nhóm của bạn đã có một buổi hẹn khác trùng vào khoảng thời gian này.');
                }

                // 7. Tạo bản ghi đặt lịch
                DangKyLichHuongDan::create([
                    'MaLichGap'   => $slot->MaLichGap,
                    'MaNhom'      => $nhom->MaNhom,
                    'MaSV'        => $sinhVien->MaSV,
                    'NgayDangKy'  => now(),
                    'GhiChu'      => trim(strip_tags($request->GhiChu ?? '')),
                    'TrangThai'   => 'DaXacNhan',
                ]);

                // 8. Cập nhật slot
                $newCount = $activeCount + 1;
                $updateData = [
                    'SoNhomDaDangKy' => $newCount,
                ];

                if ($newCount >= ($slot->SoNhomToiDa ?: 1)) {
                    $updateData['TrangThai'] = 'DaDuCho';
                }

                if (empty($slot->MaNhom)) {
                    $updateData['MaNhom'] = $nhom->MaNhom;
                }

                $slot->update($updateData);

                // 9. Gửi thông báo đến GVHD
                $diaDiemStr = ($slot->HinhThuc === 'TrucTiep') ? $slot->DiaDiem : "Trực tuyến ({$slot->LinkCuocHop})";
                $thoiGianStr = $slot->ThoiGianBatDau->format('H:i') . ' - ' . $slot->ThoiGianKetThuc->format('H:i') . ' ngày ' . $slot->ThoiGianBatDau->format('d/m/Y');
                $noiDungTraoDoi = $request->GhiChu ? "Nội dung nhóm cần trao đổi: {$request->GhiChu}" : "Mục đích: {$slot->NoiDung}";

                ThongBaoService::gui(
                    $gvhd->MaGV,
                    "Nhóm {$nhom->TenNhom} đã đặt lịch hẹn hướng dẫn",
                    "Sinh viên {$sinhVien->HoTen} đại diện Nhóm {$nhom->TenNhom} (Đề tài: {$nhom->deTai?->TenDeTai}) vừa xác nhận đặt lịch hẹn hướng dẫn vào lúc {$thoiGianStr} [{$diaDiemStr}]. {$noiDungTraoDoi}.",
                    'Lịch hẹn'
                );

                // 10. Gửi thông báo cho toàn bộ thành viên nhóm
                ThongBaoService::guiDenNhom(
                    $nhom->MaNhom,
                    'Xác nhận đặt lịch hẹn hướng dẫn thành công',
                    "Bạn {$sinhVien->HoTen} đã đại diện nhóm đặt lịch hẹn với Thầy/Cô {$gvhd->HoTen} vào lúc {$thoiGianStr} [{$diaDiemStr}].",
                    'Lịch hẹn'
                );
            });
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Đặt lịch hẹn hướng dẫn thành công! Lịch hẹn đã được xác nhận và thông báo đến Giảng viên hướng dẫn.');
    }

    /**
     * SINH VIÊN: Hủy lịch đã đặt của nhóm
     */
    public function huyLichSinhVien(Request $request, $dangKyId)
    {
        $request->validate([
            'LyDoHuy' => 'required|string|max:1000',
        ], [
            'LyDoHuy.required' => 'Vui lòng cung cấp lý do hủy lịch hẹn để thông báo cho Giảng viên hướng dẫn.',
        ]);

        $user = Auth::user();
        $sinhVien = SinhVien::getLoggedInSinhVien($user);
        if (!$sinhVien) {
            abort(403);
        }

        $tv = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->first();

        if (!$tv) {
            return redirect()->back()->with('error', 'Bạn không thuộc nhóm nào.');
        }

        $booking = DangKyLichHuongDan::with(['lichGap.giangVien', 'nhom'])->findOrFail($dangKyId);

        if ($booking->MaNhom !== $tv->MaNhom) {
            abort(403, 'Bạn không thể hủy lịch hẹn của nhóm khác.');
        }

        if ($booking->TrangThai === 'DaHuy') {
            return redirect()->back()->with('error', 'Lịch hẹn này đã bị hủy trước đó.');
        }

        $slot = $booking->lichGap;
        if ($slot->ThoiGianBatDau < now()) {
            return redirect()->back()->with('error', 'Lịch hẹn này đã diễn ra trong quá khứ, không thể hủy.');
        }

        $lyDoHuy = trim(strip_tags($request->LyDoHuy));

        DB::transaction(function () use ($booking, $slot, $lyDoHuy, $sinhVien) {
            $booking->update([
                'TrangThai'   => 'DaHuy',
                'LyDoHuy'     => $lyDoHuy,
                'NguoiHuy'    => 'SinhVien',
                'ThoiGianHuy' => now(),
            ]);

            // Cập nhật lại số nhóm và trạng thái khung giờ
            $activeCount = DangKyLichHuongDan::where('MaLichGap', $slot->MaLichGap)
                ->where('TrangThai', 'DaXacNhan')
                ->count();

            $updateData = [
                'SoNhomDaDangKy' => $activeCount,
            ];

            // Nếu khung giờ đang DaDuCho mà được giải phóng chỗ -> mở lại MoDangKy
            if ($slot->TrangThai === 'DaDuCho' && $activeCount < ($slot->SoNhomToiDa ?: 1) && $slot->ThoiGianBatDau > now()) {
                $updateData['TrangThai'] = 'MoDangKy';
            }

            if ($activeCount === 0) {
                $updateData['MaNhom'] = null;
            }

            $slot->update($updateData);

            // Gửi thông báo đến GVHD
            $thoiGianStr = $slot->ThoiGianBatDau->format('H:i d/m/Y');
            ThongBaoService::gui(
                $slot->MaGV,
                "Nhóm {$booking->nhom?->TenNhom} đã hủy lịch hẹn hướng dẫn",
                "Sinh viên {$sinhVien->HoTen} đại diện Nhóm {$booking->nhom?->TenNhom} vừa hủy lịch hẹn lúc {$thoiGianStr}. Lý do hủy: {$lyDoHuy}. Khung giờ đã được mở lại cho các nhóm khác đăng ký.",
                'Lịch hẹn'
            );

            // Thông báo nội bộ cho nhóm
            ThongBaoService::guiDenNhom(
                $booking->MaNhom,
                'Lịch hẹn hướng dẫn đã bị hủy',
                "Bạn {$sinhVien->HoTen} đã hủy lịch hẹn hướng dẫn lúc {$thoiGianStr}. Lý do: {$lyDoHuy}.",
                'Lịch hẹn'
            );
        });

        return redirect()->back()->with('success', 'Đã hủy lịch hẹn thành công và thông báo đến Thầy/Cô hướng dẫn.');
    }
}
