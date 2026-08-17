import os

models = {
    'VaiTro': ('vai_tros', 'MaVaiTro', ['MaVaiTro', 'TenVaiTro']),
    'TaiKhoan': ('tai_khoans', 'MaTK', ['MaTK', 'MaVaiTro', 'TenDangNhap', 'MatKhau', 'TrangThai', 'SoLanDangNhapSai', 'BatBuocDoiMatKhau', 'LanDangNhapCuoi', 'NgayKhoa', 'remember_token']),
    'Khoa': ('khoas', 'MaKhoa', ['MaKhoa', 'TenKhoa']),
    'BoMon': ('bo_mons', 'MaBoMon', ['MaBoMon', 'TenBoMon', 'MaKhoa']),
    'Nganh': ('nganhs', 'MaNganh', ['MaNganh', 'TenNganh', 'MaKhoa']),
    'Lop': ('lops', 'MaLop', ['MaLop', 'TenLop', 'MaNganh', 'KhoaHoc']),
    'HocKy': ('hoc_kies', 'MaHocKy', ['MaHocKy', 'TenHocKy', 'NamHoc', 'NgayBatDau', 'NgayKetThuc', 'TrangThai']),
    'GiaoVu': ('giao_vus', 'MaGVu', ['MaGVu', 'MaTK', 'MaKhoa', 'HoTen', 'Email', 'SoDienThoai', 'ChucVu']),
    'GiangVien': ('giang_viens', 'MaGV', ['MaGV', 'MaTK', 'MaBoMon', 'MaSoCanBo', 'HoTen', 'NgaySinh', 'GioiTinh', 'Email', 'SoDienThoai', 'HocHam', 'HocVi', 'ChuyenNganh', 'TrangThai']),
    'SinhVien': ('sinh_viens', 'MaSV', ['MaSV', 'MaTK', 'MaLop', 'MaSoSinhVien', 'HoTen', 'NgaySinh', 'GioiTinh', 'Email', 'SoDienThoai', 'NgayNhapHoc', 'KhoaHoc', 'SoTinChiTichLuy', 'DiemTichLuy', 'TrangThai']),
    'DanhSachSVDuDieuKien': ('danh_sach_sv_du_dieu_kiens', 'MaSV', ['MaSV', 'MaHocKy', 'NgayXetDuyet', 'TrangThai', 'GhiChu']),
    'ChiTieuHuongDan': ('chi_tieu_huong_dans', 'MaGV', ['MaGV', 'MaHocKy', 'SoNhomToiDa', 'NgayPhanBo']),
    'KeHoachKhoaLuan': ('ke_hoach_khoa_luans', 'MaKeHoach', ['MaKeHoach', 'MaHocKy', 'MaGVu', 'TenKeHoach', 'NoiDung', 'TrangThai', 'NgayTao', 'NgayCongBo']),
    'MocThoiGianKhoaLuan': ('moc_thoi_gian_khoa_luans', 'MaMoc', ['MaMoc', 'MaKeHoach', 'TenMoc', 'NgayBatDau', 'NgayKetThuc', 'MoTa']),
    'QuyDinhKhoaLuan': ('quy_dinh_khoa_luans', 'MaQuyDinh', ['MaQuyDinh', 'MaKeHoach', 'TenQuyDinh', 'GiaTri', 'MoTa']),
    'BieuMau': ('bieu_maus', 'MaBieuMau', ['MaBieuMau', 'MaKeHoach', 'TenBieuMau', 'DuongDanFile']),
    'DeTai': ('de_tais', 'MaDeTai', ['MaDeTai', 'MaGV', 'TenDeTai', 'MoTa', 'YeuCau', 'LinhVuc', 'SoLuongSinhVienToiDa', 'MaHocKy', 'TrangThai', 'NgayDeXuat', 'NgayDuyet', 'LyDoTuChoi']),
    'Nhom': ('nhoms', 'MaNhom', ['MaNhom', 'MaDeTai', 'MaTruongNhom', 'TenNhom', 'TrangThai', 'NgayTao']),
    'ThanhVienNhom': ('thanh_vien_nhoms', 'MaNhom', ['MaNhom', 'MaSV', 'VaiTro', 'NgayThamGia']),
    'DangKyDeTai': ('dang_ky_de_tais', 'MaDangKy', ['MaDangKy', 'MaNhom', 'MaDeTai', 'MaGVHuongDan', 'NgayDangKy', 'TrangThai', 'NgayDuyet', 'GhiChu', 'LyDoTuChoi']),
    'BaoCaoTienDo': ('bao_cao_tien_dos', 'MaBaoCao', ['MaBaoCao', 'MaNhom', 'LanBaoCao', 'TieuDe', 'NoiDungBaoCao', 'NgayNop', 'TenFile', 'DuongDanFile', 'TrangThai']),
    'TomTatBaoCao': ('tom_tat_bao_caos', 'MaTomTat', ['MaTomTat', 'MaBaoCao', 'CongViecDaHoanThanh', 'KhoKhan', 'KeHoachTuanToi', 'NoiDungAI', 'DoTinCayAI', 'NgayTomTat', 'TrangThai']),
    'NhanXet': ('nhan_xets', 'MaNhanXet', ['MaNhanXet', 'MaBaoCao', 'MaGV', 'NoiDung', 'LoaiNhanXet', 'NgayNhanXet', 'TrangThai']),
    'TienDo': ('tien_dos', 'MaTienDo', ['MaTienDo', 'MaNhom', 'MaMoc', 'TyLeHoanThanh', 'TrangThai', 'NgayCapNhat', 'GhiChu']),
    'HoiDong': ('hoi_dongs', 'MaHoiDong', ['MaHoiDong', 'TenHoiDong', 'ThoiGianBatDau', 'ThoiGianKetThuc', 'DiaDiem', 'TrangThai', 'GhiChu']),
    'ThanhVienHoiDong': ('thanh_vien_hoi_dongs', 'MaHoiDong', ['MaHoiDong', 'MaGV', 'VaiTro']),
    'HoSoBaoVe': ('ho_so_bao_ves', 'MaHoSo', ['MaHoSo', 'MaNhom', 'MaHoiDong', 'MaGVPhanBien', 'XacNhanGVHD', 'NgayXacNhanGVHD', 'TyLeTrungLap', 'MinhChungDaoVan', 'TrangThai', 'NgayNop', 'NgayXacNhan', 'NguoiXacNhan', 'GhiChu']),
    'ChiTietDiemHoiDong': ('chi_tiet_diem_hoi_dongs', 'MaHoiDong', ['MaHoiDong', 'MaGV', 'MaSV', 'Diem', 'NhanXet', 'NgayCham']),
    'KetQuaSinhVien': ('ket_qua_sinh_viens', 'MaKetQua', ['MaKetQua', 'MaSV', 'MaHocKy', 'DiemHuongDan', 'DiemPhanBien', 'DiemHoiDongTB', 'DiemTongKet', 'KetQua', 'NhanXetChung', 'NgayCham']),
    'ThongBao': ('thong_baos', 'MaThongBao', ['MaThongBao', 'MaGVu', 'TieuDe', 'NoiDung', 'LoaiThongBao', 'DoiTuongNhan', 'NgayTao', 'TrangThai']),
    'NguoiNhanThongBao': ('nguoi_nhan_thong_baos', 'MaThongBao', ['MaThongBao', 'MaTK', 'DaDoc', 'NgayDoc'])
}

models_dir = r"C:\laragon\www\KhoaLuanCuNhan\app\Models"

for name, (table, pk, fillable) in models.items():
    fillable_str = ", ".join([f"'{f}'" for f in fillable])
    content = f"""<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;

class {name} extends Model
{{
    use HasFactory;

    protected $table = '{table}';
    protected $primaryKey = '{pk}';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        {fillable_str}
    ];

    public $timestamps = true;
}}
"""
    filepath = os.path.join(models_dir, f"{name}.php")
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

print(f"Generated {len(models)} Eloquent models successfully!")
