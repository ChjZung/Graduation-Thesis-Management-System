<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ThanhVienNhom extends Model
{
    protected $table = 'thanh_vien_nhoms';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = ['MaNhom', 'MaSV', 'VaiTro', 'TrangThai'];

    public function nhomDoAn() { return $this->belongsTo(NhomDoAn::class, 'MaNhom', 'MaNhom'); }
    public function sinhVien() { return $this->belongsTo(SinhVien::class, 'MaSV', 'MaSV'); }
    public function scopeActive($query) { return $query->where('TrangThai', 'da_tham_gia'); }
}