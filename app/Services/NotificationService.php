<?php

namespace App\Services;

use App\Models\ThongBao;
use App\Models\SinhVien;
use App\Models\NhomDoAn;
use App\Models\DeTai;
use App\Models\BaoCaoTienDo;
use App\Models\ChamDiem;
use App\Models\SanPham;

class NotificationService
{
    /**
     * Gửi thông báo đến 1 tài khoản
     */
    public function guiThongBao(int $maTK, string $tieuDe, string $noiDung, ?string $loai = null, ?string $duongDan = null): ThongBao
    {
        return ThongBao::create([
            'MaTK' => $maTK,
            'TieuDe' => $tieuDe,
            'NoiDung' => $noiDung,
            'LoaiThongBao' => $loai ?? 'HeThong',
            'DuongDan' => $duongDan,
            'DaDoc' => false,
            'NgayTao' => date('Y-m-d')
        ]);
    }

    /**
     * Gửi thông báo tới toàn bộ thành viên trong nhóm
     */
    public function guiThongBaoNhom(NhomDoAn $nhom, string $tieuDe, string $noiDung, ?string $loai = null, ?string $duongDan = null): void
    {
        $nhom->loadMissing('thanhVienNhoms.sinhVien');
        foreach ($nhom->thanhVienNhoms as $tv) {
            if ($tv->sinhVien && $tv->sinhVien->MaTK) {
                $this->guiThongBao($tv->sinhVien->MaTK, $tieuDe, $noiDung, $loai, $duongDan);
            }
        }
    }

    /**
     * Lời mời tham gia nhóm
     */
    public function guiLoiMoiNhom(SinhVien $duocMoi, NhomDoAn $nhom, SinhVien $nguoiMoi): void
    {
        $this->guiThongBao(
            $duocMoi->MaTK,
            "Lời mời vào nhóm đồ án",
            "Sinh viên {$nguoiMoi->HoTen} đã mời bạn tham gia nhóm '{$nhom->TenNhom}'.",
            "LoiMoiNhom",
            route('sinhvien.nhom.index')
        );
    }

    /**
     * Chấp nhận lời mời tham gia nhóm -> gửi thông báo cho Trưởng nhóm
     */
    public function guiChapNhanLoiMoi(NhomDoAn $nhom, SinhVien $thanhVienMoi): void
    {
        $truongNhom = SinhVien::find($nhom->TruongNhom);
        if ($truongNhom && $truongNhom->MaTK) {
            $this->guiThongBao(
                $truongNhom->MaTK,
                "Lời mời đã được chấp nhận",
                "Sinh viên {$thanhVienMoi->HoTen} đã đồng ý gia nhập nhóm '{$nhom->TenNhom}'.",
                "LoiMoiChapNhan",
                route('sinhvien.nhom.index')
            );
        }
    }

    /**
     * Từ chối lời mời tham gia nhóm -> gửi thông báo cho Trưởng nhóm
     */
    public function guiTuChoiLoiMoi(NhomDoAn $nhom, SinhVien $sinhVienTuChoi): void
    {
        $truongNhom = SinhVien::find($nhom->TruongNhom);
        if ($truongNhom && $truongNhom->MaTK) {
            $this->guiThongBao(
                $truongNhom->MaTK,
                "Lời mời bị từ chối",
                "Sinh viên {$sinhVienTuChoi->HoTen} đã từ chối lời mời vào nhóm '{$nhom->TenNhom}'.",
                "LoiMoiTuChoi",
                route('sinhvien.nhom.index')
            );
        }
    }

    /**
     * Đề tài được duyệt
     */
    public function guiDeTaiDuocDuyet(NhomDoAn $nhom, DeTai $deTai): void
    {
        $this->guiThongBaoNhom(
            $nhom,
            "Đề tài đã được duyệt!",
            "Chúc mừng! Đề tài '{$deTai->TenDeTai}' của nhóm '{$nhom->TenNhom}' đã được giảng viên duyệt.",
            "DeTaiDuyet",
            route('sinhvien.dangky.index')
        );
    }

    /**
     * Đề tài bị từ chối
     */
    public function guiDeTaiBiTuChoi(NhomDoAn $nhom, DeTai $deTai, string $lyDo): void
    {
        $this->guiThongBaoNhom(
            $nhom,
            "Đề tài bị từ chối",
            "Đăng ký đề tài '{$deTai->TenDeTai}' của nhóm '{$nhom->TenNhom}' đã bị từ chối. Lý do: {$lyDo}",
            "DeTaiTuChoi",
            route('sinhvien.dangky.index')
        );
    }

    /**
     * Báo cáo có nhận xét mới
     */
    public function guiNhanXetMoi(NhomDoAn $nhom, BaoCaoTienDo $baocao): void
    {
        $this->guiThongBaoNhom(
            $nhom,
            "Nhận xét mới cho Báo cáo lần {$baocao->LanBaoCao}",
            "Giảng viên đã để lại nhận xét cho báo cáo tiến độ lần {$baocao->LanBaoCao} của nhóm bạn.",
            "NhanXet",
            route('sinhvien.baocao.index')
        );
    }

    /**
     * Có điểm mới
     */
    public function guiDiemMoi(NhomDoAn $nhom, ChamDiem $chamDiem): void
    {
        $this->guiThongBaoNhom(
            $nhom,
            "Điểm đồ án đã được cập nhật",
            "Giảng viên đã cập nhật điểm tổng kết cho nhóm '{$nhom->TenNhom}': {$chamDiem->DiemTong} điểm.",
            "Diem",
            route('sinhvien.nhom.index')
        );
    }

    /**
     * Nhóm nộp báo cáo mới -> thông báo cho giảng viên
     */
    public function guiBaoCaoMoiChoGV(NhomDoAn $nhom, int $maTK_GV, BaoCaoTienDo $baocao): void
    {
        $this->guiThongBao(
            $maTK_GV,
            "Nhóm {$nhom->TenNhom} đã nộp báo cáo",
            "Nhóm '{$nhom->TenNhom}' vừa nộp báo cáo tiến độ lần {$baocao->LanBaoCao}.",
            "BaoCaoMoi",
            route('giangvien.baocao.index')
        );
    }

    /**
     * Nhóm nộp sản phẩm mới -> thông báo cho giảng viên
     */
    public function guiSanPhamMoiChoGV(NhomDoAn $nhom, int $maTK_GV, SanPham $sanPham): void
    {
        $this->guiThongBao(
            $maTK_GV,
            "Nhóm {$nhom->TenNhom} đã nộp sản phẩm",
            "Nhóm '{$nhom->TenNhom}' vừa nộp sản phẩm/source code: '{$sanPham->TenSanPham}'.",
            "SanPhamMoi",
            route('giangvien.sanpham.index')
        );
    }
}
