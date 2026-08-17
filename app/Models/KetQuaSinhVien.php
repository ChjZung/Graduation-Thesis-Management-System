<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KetQuaSinhVien extends Model
{
    use HasFactory;

    protected $table = 'ket_qua_sinh_viens';
    protected $primaryKey = 'MaKetQua';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaKetQua', 'MaSV', 'MaHocKy', 'DiemHuongDan', 'DiemPhanBien', 'DiemHoiDongTB', 'DiemTongKet', 'KetQua', 'NhanXetChung', 'NgayCham'
    ];

    public $timestamps = true;
}
