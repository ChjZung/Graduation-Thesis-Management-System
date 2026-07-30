@extends('layouts.admin')
@section('page_title', 'Sửa Môn Học')
@section('content')
<div class="card card-premium w-50 mx-auto"><div class="card-body p-4">
    <form action="{{ route('monhoc.update', $monhoc->MaMon) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3"><label>Tên Môn</label><input type="text" name="TenMon" class="form-control" value="{{ $monhoc->TenMon }}" required></div>
        <div class="mb-3"><label>Bộ Môn</label>
            <select name="MaBoMon" class="form-select" required>
                @foreach($bomons as $b) <option value="{{ $b->MaBoMon }}" {{ $monhoc->MaBoMon == $b->MaBoMon ? 'selected' : '' }}>{{ $b->TenBoMon }}</option> @endforeach
            </select>
        </div>
        <div class="mb-3"><label>Số Tín Chỉ</label><input type="number" name="SoTinChi" class="form-control" value="{{ $monhoc->SoTinChi }}" required></div>
        <div class="text-end"><button type="submit" class="btn btn-primary-custom">Cập Nhật</button></div>
    </form>
</div></div>
@endsection