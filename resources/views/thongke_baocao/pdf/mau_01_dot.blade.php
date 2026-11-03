@extends('thongke_baocao.pdf.layout_pdf')

@section('title', 'Báo Cáo Tổng Hợp Kết Quả Khóa Luận Theo Đợt')
@section('pdf_title', 'BÁO CÁO TỔNG HỢP KẾT QUẢ KHÓA LUẬN TỐT NGHIỆP THEO ĐỢT')

@section('pdf_subtitle')
    Học kỳ: <strong>{{ $hocKy->TenHocKy ?? 'N/A' }}</strong> &bull; Năm học: <strong>{{ $hocKy->NamHoc ?? 'N/A' }}</strong>
@endsection

@section('pdf_content')
    @php $sttGlobal = 1; @endphp
    @foreach($danhSachHoiDong as $hdData)
    <div style="margin-bottom: 4px; font-weight: bold; font-size: 8pt; color: #003366;">
        HỘI ĐỒNG: {{ mb_strtoupper($hdData['hoiDong']->TenHoiDong) }} 
        <span style="font-weight: normal; font-size: 7.2pt; color: #444;">
            (Ngành: {{ $hdData['hoiDong']->nganh?->TenNganh ?? 'Chung' }} &bull; Phòng: {{ $hdData['hoiDong']->DiaDiem ?? 'HUIT' }})
        </span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">STT</th>
                <th style="width: 5.5%;">Mã ĐT</th>
                <th style="width: 24.5%;">Tên Đề Tài Khóa Luận</th>
                <th style="width: 5%;">Nhóm</th>
                <th style="width: 7.5%;">MSSV</th>
                <th style="width: 12%;">Họ Tên</th>
                <th style="width: 4.5%;" title="Điểm Phản biện">GVPB</th>
                <th style="width: 4%;" title="Điểm Chủ tịch">CT</th>
                <th style="width: 4%;" title="Điểm Thư ký">TK</th>
                <th style="width: 4.5%;" title="Điểm Ủy viên">UV</th>
                <th style="width: 5%;">Điểm 10</th>
                <th style="width: 4.5%;">Điểm 4</th>
                <th style="width: 3.5%;">Chữ</th>
                <th style="width: 7%;">Xếp Loại</th>
                <th style="width: 5.5%;">Kết Quả</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hdData['deTais'] as $dtItem)
                @foreach($dtItem['sinhViens'] as $sv)
                <tr>
                    <td class="text-center">{{ $sttGlobal++ }}</td>
                    <td class="text-center text-bold">{{ $sv['maDeTai'] }}</td>
                    <td style="line-height: 1.2;">{{ $sv['tenDeTai'] }}</td>
                    <td class="text-center">{{ $sv['tenNhom'] }}</td>
                    <td class="text-center">{{ $sv['maSV'] }}</td>
                    <td class="text-bold">{{ $sv['hoTen'] }}</td>
                    <td class="text-center">{{ $sv['diemGVPB'] !== null ? number_format($sv['diemGVPB'], 2) : '--' }}</td>
                    <td class="text-center">{{ $sv['diemCT'] !== null ? number_format($sv['diemCT'], 2) : '--' }}</td>
                    <td class="text-center">{{ $sv['diemTK'] !== null ? number_format($sv['diemTK'], 2) : '--' }}</td>
                    <td class="text-center">
                        @if(!empty($sv['diemUVs']))
                            @foreach($sv['diemUVs'] as $uvItem)
                                <span>{{ $uvItem['diem'] !== null ? number_format($uvItem['diem'], 2) : '--' }}</span>
                            @endforeach
                        @else
                            <span>--</span>
                        @endif
                    </td>
                    <td class="text-center text-bold">
                        {{ $sv['diemTongKet'] !== null ? number_format($sv['diemTongKet'], 2) : '--' }}
                    </td>
                    <td class="text-center">{{ $sv['diemHe4'] !== null ? number_format($sv['diemHe4'], 2) : '--' }}</td>
                    <td class="text-center text-bold">{{ $sv['diemChu'] ?: '--' }}</td>
                    <td class="text-center">{{ $sv['xepLoai'] }}</td>
                    <td class="text-center text-bold {{ $sv['ketQua'] === 'Đạt' ? 'text-success' : ($sv['ketQua'] === 'Không đạt' ? 'text-danger' : 'badge-pending') }}">
                        {{ $sv['ketQua'] }}
                    </td>
                </tr>
                @endforeach
            @endforeach
            <tr class="bg-subtotal">
                <td colspan="4" class="text-left">
                    Tổng kết Hội đồng {{ $hdData['hoiDong']->TenHoiDong }}: {{ $hdData['soDeTai'] }} đề tài ({{ $hdData['soSV'] }} SV)
                </td>
                <td colspan="7" class="text-right">
                    Đạt: <strong>{{ $hdData['soDat'] }}</strong> &bull; 
                    K.Đạt: <strong>{{ $hdData['soKhongDat'] }}</strong> 
                    @if($hdData['soChuaXong'] > 0)
                    &bull; Chưa xong: <span class="badge-pending">{{ $hdData['soChuaXong'] }}</span>
                    @endif
                </td>
                <td class="text-center text-bold">{{ number_format($hdData['diemTB'], 2) }}</td>
                <td colspan="4" class="text-center">
                    Tỷ lệ Đạt: <strong>{{ number_format($hdData['tyLeDat'], 2) }}%</strong>
                </td>
            </tr>
        </tbody>
    </table>
    @endforeach
@endsection
