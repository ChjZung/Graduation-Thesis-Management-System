<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauDoiMatKhau extends Model
{
    protected $table = 'yeu_cau_doi_mat_khaus';
    protected $primaryKey = 'MaYeuCau';

    protected $fillable = [
        'TenDangNhap',
        'Email',
        'Role',
        'TrangThai',
        'NgayGui'
    ];
}
