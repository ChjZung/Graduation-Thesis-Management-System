@extends('layouts.admin')
@section('title', 'Tổng Quan Hệ Thống')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-primary-custom"><i class="fa-solid fa-chart-line me-2"></i>Tổng Quan Hệ Thống</h4>
</div>

<div class="row g-4 mb-4">
    <!-- Card Sinh Viên -->
    <div class="col-md-3">
        <div class="card card-premium bg-primary text-white h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <h6 class="text-uppercase mb-1 opacity-75">Sinh Viên</h6>
                    <h2 class="mb-0 fw-bold">{{ $soSinhVien }}</h2>
                </div>
                <div class="fs-1 opacity-50"><i class="fa-solid fa-user-graduate"></i></div>
            </div>
        </div>
    </div>
    
    <!-- Card Giảng Viên -->
    <div class="col-md-3">
        <div class="card card-premium bg-success text-white h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <h6 class="text-uppercase mb-1 opacity-75">Giảng Viên</h6>
                    <h2 class="mb-0 fw-bold">{{ $soGiangVien }}</h2>
                </div>
                <div class="fs-1 opacity-50"><i class="fa-solid fa-chalkboard-user"></i></div>
            </div>
        </div>
    </div>
    
    <!-- Card Đề Tài -->
    <div class="col-md-3">
        <div class="card card-premium bg-warning text-dark h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <h6 class="text-uppercase mb-1 opacity-75">Đề Tài</h6>
                    <h2 class="mb-0 fw-bold">{{ $soDeTai }}</h2>
                </div>
                <div class="fs-1 opacity-50"><i class="fa-solid fa-book-open"></i></div>
            </div>
        </div>
    </div>
    
    <!-- Card Nhóm -->
    <div class="col-md-3">
        <div class="card card-premium bg-danger text-white h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <h6 class="text-uppercase mb-1 opacity-75">Nhóm Đồ Án</h6>
                    <h2 class="mb-0 fw-bold">{{ $soNhom }}</h2>
                </div>
                <div class="fs-1 opacity-50"><i class="fa-solid fa-users"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-premium">
            <div class="card-header-premium">
                <i class="fa-solid fa-chart-pie me-2"></i>Thống Kê Tiến Độ Đồ Án
            </div>
            <div class="card-body d-flex justify-content-center">
                <div style="width: 100%; max-width: 400px;">
                    <canvas id="trangThaiChart"></canvas>
                </div>
            </div>
            @if(empty($chartLabels))
                <div class="text-center text-muted pb-3">Chưa có dữ liệu nhóm đồ án.</div>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-premium h-100">
            <div class="card-header-premium">
                <i class="fa-solid fa-bullhorn me-2"></i>Lối Tắt Nhanh
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="{{ route('sinhvien.index') }}" class="btn btn-outline-primary text-start p-3 rounded-3">
                        <i class="fa-solid fa-user-plus me-2"></i> Quản lý Sinh Viên
                    </a>
                    <a href="{{ route('giangvien.index') }}" class="btn btn-outline-success text-start p-3 rounded-3">
                        <i class="fa-solid fa-user-tie me-2"></i> Quản lý Giảng Viên
                    </a>
                    <a href="{{ route('hocky.index') }}" class="btn btn-outline-warning text-start p-3 rounded-3">
                        <i class="fa-solid fa-calendar-days me-2"></i> Quản lý Học Kỳ
                    </a>
                    <a href="{{ route('thongbao.index') }}" class="btn btn-outline-danger text-start p-3 rounded-3">
                        <i class="fa-solid fa-envelope me-2"></i> Quản Lý Thông Báo
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const labels = {!! json_encode($chartLabels) !!};
        const data = {!! json_encode($chartData) !!};
        
        if (labels.length > 0) {
            const ctx = document.getElementById('trangThaiChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            '#6c757d', // Đang tạo
                            '#0dcaf0', // Đã có đề tài
                            '#ffc107', // Đang hướng dẫn
                            '#0d6efd', // Đã nộp sản phẩm
                            '#198754', // Đã có điểm
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
