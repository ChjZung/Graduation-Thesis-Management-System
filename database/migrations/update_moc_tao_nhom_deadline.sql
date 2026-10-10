-- File Migration: Đồng bộ thời hạn kết thúc tạo nhóm HK1 2026-2027 theo Thông báo số 27/TB-KCNTT
-- Hạn chót tạo nhóm: 23:59 ngày 10/08/2026
UPDATE `mocthoigiankhoaluan` 
SET `NgayKetThuc` = '2026-08-10' 
WHERE `MakeHoach` = 'KH_2026_R0JW' 
  AND (`TenMoc` LIKE '%tạo nhóm%' OR `MoTa` LIKE '%[TAO_NHOM]%');
