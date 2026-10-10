-- Migration: Thêm cột DuLieuNhap vào bảng detai để lưu bản nháp chỉnh sửa đề tài (Mục A2)
-- Ngày tạo: 10/10/2026

ALTER TABLE `detai` 
ADD COLUMN `DuLieuNhap` LONGTEXT NULL COMMENT 'Lưu dữ liệu JSON bản nháp khi giảng viên sửa đề tài mà chưa nộp lại' 
AFTER `LyDoTuChoi`;
