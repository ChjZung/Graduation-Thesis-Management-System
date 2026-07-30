<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DangKyDeTai extends Model
{
    protected $table = 'dang_ky_de_tais';
    protected $primaryKey = 'MaDangKy';
    protected $fillable = ['MaNhom', 'MaDeTai', 'NgayDangKy', 'TrangThai', 'NgayDuyet', 'LyDoTuChoi'];

    public function nhomDoAn() { return $this->belongsTo(NhomDoAn::class, 'MaNhom', 'MaNhom'); }
    public function deTai() { return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai'); }
}