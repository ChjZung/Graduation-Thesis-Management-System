-- Mở khóa và đồng bộ mật khẩu tài khoản Trưởng bộ môn về 123456
-- Thực hiện theo yêu cầu mở khóa tài khoản TBM_BM_ATTT_GV00000005

UPDATE TaiKhoan 
SET TrangThai = 1,
    SoLanDangNhapSai = 0,
    NgayKhoa = NULL,
    BatBuocDoiMatKhau = 0,
    MatKhau = '$2y$12$c3orH8pP1fYsng7.lDmhC.35kkTwekmMrpcUwpZoy7/UXMCofCN9e' -- Mật khẩu: 123456
WHERE TenDangNhap IN (
    'TBM_BM_ATTT_GV00000005',
    'TBM_BM_HTTT_GV00000003',
    'TBM_BM_KHMT_GV00000006'
);
