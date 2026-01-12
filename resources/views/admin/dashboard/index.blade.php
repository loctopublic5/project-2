@extends('layouts.admin')

@section('title', 'Thống kê tổng quan')

@section('content')
<div class="page-heading d-flex justify-content-between">
    <h3>Thống kê tổng quan</h3>
    <button id="btn-refresh" class="btn btn-primary btn-sm">
        <i class="bi bi-arrow-clockwise"></i> Làm mới dữ liệu
    </button>
</div>

<div class="page-content">
    <section class="row">
        <div class="col-12 col-lg-9">
            <div class="row">
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="stats-icon purple mb-2"><i class="iconly-boldShow"></i></div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Doanh thu</h6>
                                    <h6 class="font-extrabold mb-0" id="stat-revenue">
                                        <span class="spinner-border spinner-border-sm"></span>
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="stats-icon blue mb-2"><i class="iconly-boldProfile"></i></div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Khách hàng</h6>
                                    <h6 class="font-extrabold mb-0" id="stat-customers">...</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="stats-icon green mb-2"><i class="iconly-boldAdd-User"></i></div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Đơn mới</h6>
                                    <h6 class="font-extrabold mb-0" id="stat-orders">...</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

{{-- PUSH LOGIC JS VÀO STACK --}}
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <script>
        // Hàm format tiền tệ VNĐ
        const formatCurrency = (amount) => {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        };

        // Hàm tải dữ liệu
        const loadDashboardData = () => {
            // 1. Gọi Route API bạn đã định nghĩa
            axios.get('/admin/api/dashboard-stats') 
                .then(response => {
                    // Cấu trúc response của Trait ApiResponse thường là:
                    // response.data => { status: true, message: "...", data: { ... } }
                    
                    const result = response.data;
                    
                    if(result.status || result.success) { // Tùy trait của bạn trả về key nào
                        const data = result.data; 

                        // 2. Cập nhật DOM
                        document.getElementById('stat-revenue').innerText = formatCurrency(data.revenue);
                        document.getElementById('stat-customers').innerText = data.total_customers;
                        document.getElementById('stat-orders').innerText = data.new_orders;
                    }
                })
                .catch(error => {
                    console.error("Lỗi tải dashboard:", error);
                    alert("Không thể tải dữ liệu thống kê!");
                });
        };

        // Hàm xóa cache (Gọi function refresh của bạn)
        const refreshData = () => {
            const btn = document.getElementById('btn-refresh');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang tải...';
            btn.disabled = true;

            axios.get('/admin/api/dashboard-refresh')
                .then(response => {
                    // Sau khi xóa cache xong thì load lại dữ liệu mới
                    loadDashboardData();
                    alert(response.data.message);
                })
                .catch(err => console.error(err))
                .finally(() => {
                    btn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Làm mới dữ liệu';
                    btn.disabled = false;
                });
        };

        // Chạy ngay khi trang load xong
        document.addEventListener('DOMContentLoaded', () => {
            loadDashboardData();

            // Bắt sự kiện click nút Refresh
            document.getElementById('btn-refresh').addEventListener('click', refreshData);
        });
    </script>
@endpush