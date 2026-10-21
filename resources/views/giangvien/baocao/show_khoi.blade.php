@extends('layouts.giangvien')

@section('page_title', 'Đối Chiếu AI & Đánh Giá Báo Cáo Nhỏ - ' . $khoi->TieuDe)

@section('content')
<div class="container-fluid px-0">
    <!-- Header & Navigation -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('giangvien.baocao.show', $baoCao->MaBaoCao) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
                <i class="fa-solid fa-arrow-left me-2"></i> Quay lại giai đoạn {{ $baoCao->LanBaoCao }}
            </a>
            <div>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <span>Đánh Giá Báo Cáo Nhỏ (Đợt {{ $khoi->LanNop }})</span>
                    <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i>Đối chiếu AI Map-Reduce
                    </span>
                </h4>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $khoi->TrangThai === 'Đạt' ? 'bg-success' : ($khoi->TrangThai === 'Chờ duyệt' ? 'bg-warning text-dark' : 'bg-info text-dark') }} rounded-pill px-3 py-2 fw-bold">
                Trạng thái: {{ $khoi->TrangThai }}
            </span>
        </div>
    </div>

    <!-- Metadata Đề tài & Sinh viên -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-secondary rounded-pill px-3 py-1 mb-1" style="font-size: 11.5px;">Giai đoạn {{ $khoi->LanBaoCao }}: {{ $baoCao->TieuDe }}</span>
                    <div class="fw-bold text-dark fs-6">{{ $deTai->TenDeTai }}</div>
                    <div class="text-muted small">
                        <span><i class="fa-solid fa-users me-2"></i>Nhóm: <strong>{{ $nhom->TenNhom ?? ('Nhóm ' . $nhom->MaNhom) }}</strong></span>
                        <span class="ms-3"><i class="fa-regular fa-clock me-2"></i>Nộp lúc: <strong>{{ \Carbon\Carbon::parse($khoi->NgayNop)->format('H:i - d/m/Y') }}</strong></span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                    <div class="badge bg-primary text-white fs-6 px-3 py-2 rounded-pill">
                        Tiến độ khai báo: {{ $khoi->PhanTramHoanThanh ?? 0 }}%
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $tomTatData = $khoi->TomTatJson ?? $khoi->tomTat?->TomTatJson;
        if (is_string($tomTatData)) $tomTatData = json_decode($tomTatData, true);
        $hasAi = is_array($tomTatData) && (!empty($tomTatData['_processing']) || !empty($tomTatData['overall_summary']) || !empty($tomTatData['data']['overall_summary']));
    @endphp

    <!-- KHUNG ĐỐI CHIẾU SONG SONG (2 CỘT): SINH VIÊN vs AI TRÍCH XUẤT -->
    <div class="row g-4 mb-4">
        <!-- CỘT 1: NỘI DUNG DO SINH VIÊN TRÌNH BÀY -->
        <div class="col-lg-6">
            <div class="card shadow-sm border h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-user-pen text-primary me-2"></i>Nội Dung Sinh Viên Đã Trình Bày
                    </h6>
                    <span class="badge bg-light text-dark border">Bản sinh viên nộp</span>
                </div>
                <div class="card-body p-4">
                    <!-- Tiêu đề & Tài liệu -->
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="text-muted small">Tiêu đề báo cáo:</div>
                        <div class="fw-bold text-dark fs-6">{{ $khoi->TieuDe }}</div>

                        <div class="d-flex flex-wrap gap-2 mt-2">
                            @include('partials.report_attachment', ['report' => $khoi])
                            @if($khoi->LinkCode)
                                <a href="{{ $khoi->LinkCode }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                    <i class="fa-brands fa-github me-2"></i>Xem Source Code
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- 1. Kết quả đạt được -->
                    <div class="mb-3">
                        <h6 class="fw-bold text-success mb-1" style="font-size: 13px;">
                            <i class="fa-solid fa-circle-check me-2"></i>1. Kết quả đã hoàn thành:
                        </h6>
                        <div class="p-3 bg-light rounded-2 border text-dark" style="font-size: 13px;">{!! \App\Helpers\HtmlHelper::clean($khoi->KetQuaDatDuoc ?: ($khoi->NoiDungBaoCao ?: 'Chưa cập nhật')) !!}</div>
                    </div>

                    <!-- 2. Khó khăn -->
                    @if($khoi->KhoKhan)
                    <div class="mb-3">
                        <h6 class="fw-bold text-warning-emphasis mb-1" style="font-size: 13px;">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>2. Khó khăn gặp phải:
                        </h6>
                        <div class="p-3 bg-light rounded-2 border text-dark" style="font-size: 13px;">{!! \App\Helpers\HtmlHelper::clean($khoi->KhoKhan) !!}</div>
                    </div>
                    @endif

                    <!-- 3. Hướng giải quyết -->
                    @if($khoi->HuongGiaiQuyet)
                    <div class="mb-3">
                        <h6 class="fw-bold text-info mb-1" style="font-size: 13px;">
                            <i class="fa-solid fa-lightbulb me-2"></i>3. Hướng giải quyết đề xuất:
                        </h6>
                        <div class="p-3 bg-light rounded-2 border text-dark" style="font-size: 13px;">{!! \App\Helpers\HtmlHelper::clean($khoi->HuongGiaiQuyet) !!}</div>
                    </div>
                    @endif

                    <!-- 4. Kế hoạch tiếp theo -->
                    @if($khoi->KeHoachTiepTheo)
                    <div class="mb-0">
                        <h6 class="fw-bold text-primary mb-1" style="font-size: 13px;">
                            <i class="fa-solid fa-calendar-check me-2"></i>4. Kế hoạch tuần tới:
                        </h6>
                        <div class="p-3 bg-light rounded-2 border text-dark" style="font-size: 13px;">{!! \App\Helpers\HtmlHelper::clean($khoi->KeHoachTiepTheo) !!}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- CỘT 2: NỘI DUNG AI MAP-REDUCE TRÍCH XUẤT (KÈM TRÍCH DẪN SỐ TRANG) -->
        <div class="col-lg-6">
            <div class="card shadow-sm border border-primary border-opacity-25 h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Trích Xuất Đối Chiếu Từ AI Map-Reduce
                    </h6>
                    <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1" style="font-size: 11.5px;">
                        Đối chiếu theo dẫn chứng tài liệu
                    </span>
                </div>
                <div class="card-body p-3">
                    @if($hasAi)
                        @include('partials.ai_summary_11_sections', ['pb' => $khoi])
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fa-solid fa-wand-magic-sparkles fa-2x mb-2 text-secondary"></i>
                            <p class="mb-0" style="font-size: 13px;">Chưa có dữ liệu trích xuất AI cho bản nộp này hoặc tệp chưa có văn bản đọc được.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- KHUNG ĐÁNH GIÁ CỦA GIẢNG VIÊN HƯỚNG DẪN -->
    <div class="card shadow-sm border">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-clipboard-check text-primary me-2"></i>Đánh Giá &amp; Nhận Xét Báo Cáo Nhỏ (Đợt {{ $khoi->LanNop }})
            </h6>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('giangvien.baocao.nhanxet_khoi', $khoi->MaKhoi) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                            Kết quả thẩm định: <span class="text-danger">*</span>
                        </label>
                        <select name="LoaiNhanXet" class="form-select fw-bold" required>
                            <option value="Đạt" {{ old('LoaiNhanXet', $khoi->TrangThai) === 'Đạt' ? 'selected' : '' }}>
                                ✅ Đạt (Công nhận kết quả đợt này)
                            </option>
                            <option value="Yêu cầu nộp lại" {{ old('LoaiNhanXet', $khoi->TrangThai) === 'Yêu cầu nộp lại' ? 'selected' : '' }}>
                                🔄 Yêu cầu chỉnh sửa / Bổ sung
                            </option>
                        </select>
                        <div class="form-text text-muted" style="font-size: 12px;">
                            Nếu chọn "Yêu cầu nộp lại", sinh viên sẽ nhận được thông báo chỉnh sửa.
                        </div>
                    </div>

                    <div class="col-md-8">
                        @include('partials.rich_text_editor', [
                            'name' => 'NoiDung',
                            'id' => 'NoiDung',
                            'value' => old('NoiDung', $khoi->NhanXetGV),
                            'label' => 'Nội dung nhận xét & Góp ý chuyên môn',
                            'required' => true,
                            'minHeight' => '120px',
                            'compact' => true,
                            'placeholder' => 'Ghi nhận xét cụ thể về kết quả thực hiện, đối chiếu với tài liệu đính kèm...'
                        ])
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
                    <a href="{{ route('giangvien.baocao.show', $baoCao->MaBaoCao) }}" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
                        Quay lại
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs d-inline-flex align-items-center">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Lưu Đánh Giá Báo Cáo Nhỏ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
