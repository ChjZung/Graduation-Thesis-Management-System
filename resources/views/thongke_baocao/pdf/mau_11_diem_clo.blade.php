@extends('thongke_baocao.pdf.layout_pdf')

@section('title', 'Bảng tổng hợp điểm và chuẩn đầu ra khóa luận')
@section('pdf_title', 'BẢNG TỔNG HỢP ĐIỂM VÀ CHUẨN ĐẦU RA KHÓA LUẬN')

@section('pdf_subtitle')
    Học kỳ <strong>{{ $hocKy->TenHocKy ?? '—' }}</strong> · Năm học <strong>{{ $hocKy->NamHoc ?? '—' }}</strong>
    @if(!empty($filters['MaHocPhan'])) · Học phần <strong>{{ $filters['MaHocPhan'] }}</strong> @endif
@endsection

@section('pdf_content')
    @if($config['show_table'] ?? true)
        @include('thongke_baocao.partials.diem_clo_table', ['rows' => $danhSachTatCaSV])
    @endif
@endsection
