<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\BaoCaoTienDo;
use App\Models\TomTatBaoCao;
use App\Models\Nhom;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Services\AiSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BaoCaoController extends Controller
{
    // Danh sách 5 mốc cố định
    const MOCS = [
        1 => ['ten' => 'Mốc 1: Đề cương & Phân tích', 'loai' => 'pdf', 'mo_ta' => 'Nộp file PDF đề cương nghiên cứu'],
        2 => ['ten' => 'Mốc 2: Nghiên cứu & Thiết kế', 'loai' => 'pdf', 'mo_ta' => 'Nộp file PDF báo cáo thiết kế hệ thống'],
        3 => ['ten' => 'Mốc 3: Lập trình & Kiểm thử', 'loai' => 'pdf', 'mo_ta' => 'Nộp file PDF báo cáo tiến độ lập trình'],
        4 => ['ten' => 'Mốc 4: Hoàn thiện & Code', 'loai' => 'git', 'mo_ta' => 'Nộp link GitHub/GitLab (repository cuối)'],
        5 => ['ten' => 'Mốc 5: Báo cáo & Bảo vệ', 'loai' => 'pdf_git', 'mo_ta' => 'Nộp file PDF báo cáo hoàn chỉnh + link repository'],
    ];

    public function index()
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->first();

        if (!$sinhVien) {
            return redirect()->route('sinhvien.nhom.index')
                ->with('error', 'Bạn chưa có hồ sơ sinh viên. Vui lòng liên hệ Giáo vụ.');
        }

        // Kiểm tra có nhóm & đề tài đã duyệt chưa
        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')->first();

        if (!$thanhVienRecord) {
            return view('sinhvien.baocao.index', [
                'error'    => 'Bạn chưa có nhóm. Vui lòng tạo hoặc gia nhập nhóm trước.',
                'sinhVien' => $sinhVien,
                'nhom'     => null,
                'mocs'     => self::MOCS,
                'baoCaos'  => collect(),
                'mocHienTai' => 1,
            ]);
        }

        $nhom = Nhom::with(['deTai.giangVien'])->find($thanhVienRecord->MaNhom);

        if (!$nhom || !$nhom->MaDeTai) {
            return view('sinhvien.baocao.index', [
                'error'    => 'Nhóm của bạn chưa đăng ký đề tài hoặc đề tài chưa được duyệt.',
                'sinhVien' => $sinhVien,
                'nhom'     => $nhom,
                'mocs'     => self::MOCS,
                'baoCaos'  => collect(),
                'mocHienTai' => 1,
            ]);
        }

        // Lấy tất cả báo cáo của nhóm
        $baoCaos = BaoCaoTienDo::with('tomTat')
            ->where('MaNhom', $nhom->MaNhom)
            ->orderBy('LanBaoCao')
            ->get()
            ->keyBy('LanBaoCao');

        // Xác định mốc hiện tại (mốc tiếp theo có thể nộp)
        $mocHienTai = 1;
        for ($i = 1; $i <= 5; $i++) {
            if (!isset($baoCaos[$i])) {
                $mocHienTai = $i;
                break;
            }
            // Nếu mốc i chưa "Đạt" thì chưa được mở mốc i+1
            if ($baoCaos[$i]->TrangThai !== 'Đạt') {
                $mocHienTai = $i;
                break;
            }
            $mocHienTai = $i + 1;
        }
        if ($mocHienTai > 5) $mocHienTai = 5; // đã xong cả 5

        return view('sinhvien.baocao.index', compact(
            'sinhVien', 'nhom', 'baoCaos', 'mocHienTai'
        ) + ['mocs' => self::MOCS, 'error' => null]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();

        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')->firstOrFail();
        $nhom = Nhom::findOrFail($thanhVienRecord->MaNhom);

        $lan = (int) $request->input('LanBaoCao');
        $mocInfo = self::MOCS[$lan] ?? null;

        if (!$mocInfo) {
            return back()->with('error', 'Mốc báo cáo không hợp lệ.');
        }

        // Validate theo loại mốc
        $rules = ['LanBaoCao' => 'required|integer|min:1|max:5'];
        $messages = [];

        if (in_array($mocInfo['loai'], ['pdf', 'pdf_git'])) {
            $rules['FileBaoCao'] = 'required|file|mimes:pdf|max:20480';
            $messages['FileBaoCao.required'] = 'Vui lòng đính kèm file PDF.';
            $messages['FileBaoCao.mimes'] = 'Chỉ chấp nhận file PDF.';
            $messages['FileBaoCao.max'] = 'File PDF tối đa 20MB.';
        }
        if (in_array($mocInfo['loai'], ['git', 'pdf_git'])) {
            $rules['LinkCode'] = 'required|url';
            $messages['LinkCode.required'] = 'Vui lòng nhập link GitHub/GitLab.';
            $messages['LinkCode.url'] = 'Link Code phải là URL hợp lệ (bắt đầu https://).';
        }

        $request->validate($rules, $messages);

        // Kiểm tra thứ tự: phải có mốc trước đã "Đạt"
        if ($lan > 1) {
            $mocTruoc = BaoCaoTienDo::where('MaNhom', $nhom->MaNhom)
                ->where('LanBaoCao', $lan - 1)
                ->where('TrangThai', 'Đạt')
                ->first();
            if (!$mocTruoc) {
                return back()->with('error', "Bạn cần hoàn thành Mốc " . ($lan - 1) . " và được Giảng viên đánh giá \"Đạt\" trước khi nộp Mốc {$lan}.");
            }
        }

        // Không cho nộp lại nếu đã có bài "Chờ duyệt" hoặc "Đạt"
        $existing = BaoCaoTienDo::where('MaNhom', $nhom->MaNhom)
            ->where('LanBaoCao', $lan)
            ->whereIn('TrangThai', ['Chờ duyệt', 'Đạt'])
            ->first();
        if ($existing) {
            return back()->with('error', "Mốc {$lan} đã có bài nộp đang chờ xử lý hoặc đã đạt.");
        }

        DB::transaction(function () use ($request, $nhom, $lan, $mocInfo) {
            // Upload file PDF nếu có
            $tenFile = null;
            $duongDanFile = null;
            if ($request->hasFile('FileBaoCao')) {
                $file = $request->file('FileBaoCao');
                $tenFile = $file->getClientOriginalName();
                $duongDanFile = $file->store("baocao/{$nhom->MaNhom}/moc{$lan}", 'public');
            }

            // Tạo mã báo cáo
            $maBaoCao = 'BC' . str_pad(
                BaoCaoTienDo::count() + 1,
                3, '0', STR_PAD_LEFT
            );

            $baoCao = BaoCaoTienDo::create([
                'MaBaoCao'      => $maBaoCao,
                'MaNhom'        => $nhom->MaNhom,
                'LanBaoCao'     => $lan,
                'TieuDe'        => $mocInfo['ten'],
                'NoiDungBaoCao' => $request->input('NoiDungBaoCao'),
                'NgayNop'       => now()->toDateString(),
                'TenFile'       => $tenFile,
                'DuongDanFile'  => $duongDanFile,
                'LinkCode'      => $request->input('LinkCode'),
                'TrangThai'     => 'Chờ duyệt',
            ]);

            // Sinh bản tóm tắt AI ngay sau khi nộp
            $aiService = new \App\Services\AiSummaryService();
            $aiData = $aiService->generate($baoCao);

            $maTomTat = 'TT' . str_pad(
                \App\Models\TomTatBaoCao::count() + 1,
                3, '0', STR_PAD_LEFT
            );

            TomTatBaoCao::create(array_merge(
                ['MaTomTat' => $maTomTat, 'MaBaoCao' => $baoCao->MaBaoCao],
                $aiData
            ));
        });

        return redirect()->route('sinhvien.baocao.index')
            ->with('success', "Nộp báo cáo Mốc {$lan} thành công! Hệ thống đã tự động sinh bản tóm tắt AI để Giảng viên xem xét.");
    }
}
