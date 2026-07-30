@extends('layouts.admin')
@section('page_title', 'Thêm Môn Học')
@section('content')
<div class="card card-premium w-50 mx-auto"><div class="card-body p-4">
    <form action="{{ route('monhoc.store') }}" method="POST">
        @csrf
        <div class="mb-3"><label>Tên Môn</label><input type="text" name="TenMon" class="form-control" required></div>
        <div class="mb-3"><label>Bộ Môn</label>
            <select name="MaBoMon" class="form-select" required>
                <option value="">-- Chọn Bộ môn --</option>
                @foreach($bomons as $b) <option value="{{ $b->MaBoMon }}">{{ $b->TenBoMon }}</option> @endforeach
            </select>
        </div>
        <div class="mb-3"><label>Số Tín Chỉ</label><input type="number" name="SoTinChi" class="form-control" required></div>
        <div class="text-end"><button type="submit" class="btn btn-primary-custom">Lưu</button></div>
    </form>
</div></div>
@endsection