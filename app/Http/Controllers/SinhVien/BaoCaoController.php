<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\BaoCaoTienDo;
use App\Models\MocThoiGianKhoaLuan;
use App\Models\KeHoachKhoaLuan;
use App\Models\Nhom;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Helpers\IdGenerator;
use App\Jobs\GenerateAiSummaryJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BaoCaoController extends Controller
{
    // Danh sách 6 giai đoạn tiến độ khóa luận
    const MOCS = [
        1 => ['ten' => 'GĐ 1: Nhận đề cương & Thiết kế CSDL', 'loai' => 'pdf', 'mo_ta' => 'Nộp file PDF đề cương chi tiết đề tài và sơ đồ thiết kế cơ sở dữ liệu.'],
        2 => ['ten' => 'GĐ 2: Thiết kế hệ thống & Xây dựng chức năng', 'loai' => 'pdf_git', 'mo_ta' => 'Nộp tài liệu thiết kế kiến trúc hệ thống, giao diện và link GitHub/GitLab các chức năng đã xây dựng.'],
        3 => ['ten' => 'GĐ 3: Kiểm thử hoàn thiện & Báo cáo dự thảo', 'loai' => 'pdf_git', 'mo_ta' => 'Nộp kết quả kiểm thử (Test report), dự thảo toàn văn khóa luận và hoàn thiện source code.'],
        4 => ['ten' => 'GĐ 4: Hoàn thiện hồ sơ bảo vệ', 'loai' => 'pdf', 'mo_ta' => 'Nộp kết quả kiểm tra đạo văn Turnitin và hồ sơ xin bảo vệ đã có ý kiến của GVHD.'],
        5 => ['ten' => 'GĐ 5: Tiến hành bảo vệ trước Hội đồng', 'loai' => 'pdf', 'mo_ta' => 'Nộp slide thuyết trình và tóm tắt đề tài phục vụ phiên bảo vệ trước Hội đồng chấm.'],
        6 => ['ten' => 'GĐ 6: Nhận kết quả & Nộp lại kết quả hoàn chỉnh', 'loai' => 'pdf_git', 'mo_ta' => 'Nộp bản khóa luận hoàn chỉnh đã chỉnh sửa theo kết luận và góp ý của Hội đồng chấm.'],
    ];

    public function index()
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->first();

        if (!$sinhVien) {
            return redirect()->route('sinhvien.nhom.index')
                ->with('error', 'Bạn chưa có hồ sơ sinh viên. Vui lòng liên hệ Giáo vụ.');
        }

        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->first();

        if (!$thanhVienRecord) {
            return view('sinhvien.baocao.index', [
                'error'      => 'Bạn chưa có nhóm. Vui lòng tạo hoặc gia nhập nhóm trước.',
                'sinhVien'   => $sinhVien,
                'nhom'       => null,
                'mocs'       => self::MOCS,
                'baoCaos'    => collect(),
                'mocHienTai' => 1,
                'mocDeadlines' => [],
            ]);
        }

        $nhom = Nhom::with(['deTai.giangVien'])->find($thanhVienRecord->MaNhom);

        if (!$nhom || !$nhom->deTai) {
            return view('sinhvien.baocao.index', [
                'error'      => 'Nhóm của bạn chưa đăng ký đề tài hoặc đề tài chưa được duyệt.',
                'sinhVien'   => $sinhVien,
                'nhom'       => $nhom,
                'mocs'       => self::MOCS,
                'baoCaos'    => collect(),
                'mocHienTai' => 1,
                'mocDeadlines' => [],
            ]);
        }

        $baoCaos = BaoCaoTienDo::with('tomTatBaoCao')
            ->where('MaDeTai', $nhom->deTai->MaDeTai)
            ->orderBy('LanBaoCao')
            ->get()
            ->keyBy('LanBaoCao');

        $mocHienTai = 1;
        for ($i = 1; $i <= 6; $i++) {
            if (!isset($baoCaos[$i])) {
                $mocHienTai = $i;
                break;
            }
            if ($baoCaos[$i]->TrangThai !== 'Đạt') {
                $mocHienTai = $i;
                break;
            }
            $mocHienTai = $i + 1;
        }
        if ($mocHienTai > 6) $mocHienTai = 6;

        // Lấy deadline các mốc từ KeHoach để hiển thị cảnh báo
        $mocDeadlines = $this->getMocDeadlines();

        return view('sinhvien.baocao.index', compact(
            'sinhVien', 'nhom', 'baoCaos', 'mocHienTai', 'mocDeadlines'
        ) + ['mocs' => self::MOCS, 'error' => null]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();

        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->firstOrFail();
        $nhom = Nhom::with('deTai')->findOrFail($thanhVienRecord->MaNhom);
        $deTai = $nhom->deTai;

        if (!$deTai) {
            return back()->with('error', 'Nhóm của bạn chưa có đề tài được duyệt.');
        }

        $lan = (int) $request->input('LanBaoCao');
        $mocInfo = self::MOCS[$lan] ?? null;

        if (!$mocInfo) {
            return back()->with('error', 'Giai đoạn báo cáo không hợp lệ.');
        }

        // Validate theo loại mốc
        $rules = ['LanBaoCao' => 'required|integer|min:1|max:6'];
        $messages = [];

        $existing = BaoCaoTienDo::where('MaDeTai', $deTai->MaDeTai)
            ->where('LanBaoCao', $lan)
            ->first();

        if (in_array($mocInfo['loai'], ['pdf', 'pdf_git'])) {
            // Nếu đã có file trước đó và đang chỉnh sửa thì không bắt buộc upload lại file mới
            $fileRule = ($existing && $existing->DuongDanFile) ? 'nullable|file|mimes:pdf|max:20480' : 'required|file|mimes:pdf|max:20480';
            $rules['FileBaoCao'] = $fileRule;
            $messages['FileBaoCao.required'] = 'Vui lòng đính kèm file PDF.';
            $messages['FileBaoCao.mimes'] = 'Chỉ chấp nhận file PDF.';
            $messages['FileBaoCao.max'] = 'File PDF tối đa 20MB.';
        }
        if (in_array($mocInfo['loai'], ['git', 'pdf_git'])) {
            $rules['LinkCode'] = 'nullable|url';
            $messages['LinkCode.url'] = 'Link Code phải là URL hợp lệ (bắt đầu https://).';
        }

        $request->validate($rules, $messages);

        // Kiểm tra thứ tự mốc: Phải hoàn thành mốc trước mới được nộp mốc sau
        if ($lan > 1) {
            $mocTruoc = BaoCaoTienDo::where('MaDeTai', $deTai->MaDeTai)
                ->where('LanBaoCao', $lan - 1)
                ->where('TrangThai', 'Đạt')
                ->first();
            if (!$mocTruoc) {
                return back()->with('error', "Bạn cần hoàn thành Giai đoạn " . ($lan - 1) . " và được Giảng viên đánh giá \"Đạt\" trước khi nộp Giai đoạn {$lan}.");
            }
        }

        if ($existing && $existing->TrangThai === 'Đạt') {
            return back()->with('error', "Giai đoạn {$lan} đã được Giảng viên đánh giá Đạt, không cần nộp lại.");
        }

        $maBaoCao = null;

        DB::transaction(function () use ($request, $nhom, $deTai, $lan, $mocInfo, $existing, &$maBaoCao) {
            $tenFile = $existing?->TenFile;
            $duongDanFile = $existing?->DuongDanFile;
            if ($request->hasFile('FileBaoCao')) {
                $file = $request->file('FileBaoCao');
                $tenFile = $file->getClientOriginalName();
                $duongDanFile = $file->store("baocao/{$nhom->MaNhom}/moc{$lan}", 'public');
            }

            $tieuDe = $request->input('TieuDe') ?: $mocInfo['ten'];
            $noiDung = $request->input('NoiDungBaoCao') . ($request->input('LinkCode') ? "\nLink Code: " . $request->input('LinkCode') : '');

            if ($existing) {
                // Xóa tóm tắt AI cũ nếu có
                $existing->tomTatBaoCao()?->delete();

                $existing->update([
                    'TieuDe'        => $tieuDe,
                    'NoiDungBaoCao' => $noiDung,
                    'NgayNop'       => now()->toDateString(),
                    'TenFile'       => $tenFile,
                    'DuongDanFile'  => $duongDanFile,
                    'TrangThai'     => 'Chờ duyệt',
                    'NhanXet'       => null,
                ]);
                $maBaoCao = $existing->MaBaoCao;
            } else {
                $maBaoCao = IdGenerator::nextBaoCao();
                BaoCaoTienDo::create([
                    'MaBaoCao'      => $maBaoCao,
                    'MaDeTai'       => $deTai->MaDeTai,
                    'LanBaoCao'     => $lan,
                    'TieuDe'        => $tieuDe,
                    'NoiDungBaoCao' => $noiDung,
                    'NgayNop'       => now()->toDateString(),
                    'TenFile'       => $tenFile,
                    'DuongDanFile'  => $duongDanFile,
                    'TrangThai'     => 'Chờ duyệt',
                ]);
            }
        });

        // BƯỚC 5: Dispatch Queue Job bất đồng bộ
        GenerateAiSummaryJob::dispatch($maBaoCao);

        return redirect()->route('sinhvien.baocao.index')
            ->with('success', "Nộp và lưu thông tin tiến độ GĐ {$lan} thành công! Hệ thống đã kích hoạt trợ lý AI phân tích và tóm tắt nội dung báo cáo.");
    }

    /**
     * BƯỚC 4: Kiểm tra deadline nộp bài theo MocThoiGian.
     * Trả về null nếu còn hạn, trả về chuỗi lỗi nếu quá hạn.
     */
    private function checkDeadline(int $lan): ?string
    {
        // Lấy kế hoạch khóa luận đang hiện hành
        $keHoach = KeHoachKhoaLuan::where('TrangThai', 'Đang thực hiện')->first();
        if (!$keHoach) return null; // Không có kế hoạch → bỏ qua kiểm tra

        $moc = MocThoiGianKhoaLuan::where('MaKeHoach', $keHoach->MaKeHoach)
            ->where(function($q) use ($lan) {
                $q->where('TenMoc', 'LIKE', "%Mốc {$lan}%")
                  ->orWhere('TenMoc', 'LIKE', "%Moc {$lan}%")
                  ->orWhere('TenMoc', 'LIKE', "%GĐ {$lan}%")
                  ->orWhere('TenMoc', 'LIKE', "%Giai đoạn {$lan}%");
            })
            ->first();

        if (!$moc) return null; // Chưa cấu hình mốc → bỏ qua

        $ngayKetThuc = Carbon::parse($moc->NgayKetThuc)->endOfDay();

        if (now()->greaterThan($ngayKetThuc)) {
            return "Đã quá hạn nộp Giai đoạn {$lan}! Hạn cuối là " . $ngayKetThuc->format('d/m/Y H:i') . ". Vui lòng liên hệ Giáo vụ Khoa nếu cần gia hạn.";
        }

        return null;
    }

    /**
     * Lấy map deadline các mốc để hiển thị trên UI.
     */
    private function getMocDeadlines(): array
    {
        $keHoach = KeHoachKhoaLuan::where('TrangThai', 'Đang thực hiện')->first();
        if (!$keHoach) return [];

        $mocs = MocThoiGianKhoaLuan::where('MaKeHoach', $keHoach->MaKeHoach)->get();
        $map = [];
        foreach ($mocs as $moc) {
            foreach (range(1, 6) as $lan) {
                if (str_contains($moc->TenMoc, "Mốc {$lan}") || str_contains($moc->TenMoc, "GĐ {$lan}") || str_contains($moc->TenMoc, "Giai đoạn {$lan}")) {
                    $map[$lan] = $moc->NgayKetThuc;
                }
            }
        }
        return $map;
    }
}
