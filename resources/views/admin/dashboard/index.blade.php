@extends('layouts.admin')

@section('title', 'Thống kê tổng quan')

@section('content')
<div class="page-heading">
    <h3>Dashboard Analytics</h3>
</div>

<div class="page-content">
    <section class="row">
        <div class="col-12 col-lg-9">
            <div class="row">
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4"><div class="stats-icon purple mb-2"><i class="iconly-boldShow"></i></div></div>
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
                                <div class="col-md-4"><div class="stats-icon blue mb-2"><i class="iconly-boldBuy"></i></div></div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Đơn hôm nay</h6>
                                    <h6 class="font-extrabold mb-0" id="stat-orders">...</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4"><div class="stats-icon green mb-2"><i class="iconly-boldProfile"></i></div></div>
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
                                <div class="col-md-4"><div class="stats-icon red mb-2"><i class="iconly-boldTime-Circle"></i></div></div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Chờ xử lý</h6>
                                    <h6 class="font-extrabold mb-0" id="stat-pending">...</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Biểu đồ doanh thu (7 ngày)</h4>
                        </div>
                        <div class="card-body">
                            <div id="revenue-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h4>Sắp hết hàng</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Tên</th>
                                    <th>SL</th>
                                    <th>Giá</th>
                                </tr>
                            </thead>
                            <tbody id="low-stock-list">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('admin_assets/assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <script src="{{ asset('admin_assets/js/pages/dashboard.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lấy URL từ Laravel route (chuẩn chỉ)
            // Route này bạn đã khai báo trong api.php: ->name('admin.dashboard.api')
            const apiUrl = "{{ route('admin.dashboard.api') }}"; 

            console.log("🏁 Blade: Bắt đầu gọi initDashboard...");
            
            // Kích hoạt hàm
            if (typeof initDashboard === 'function') {
                initDashboard(apiUrl);
            } else {
                console.error("❌ Lỗi: Không tìm thấy hàm initDashboard. Kiểm tra lại đường dẫn file JS.");
            }
        });
    </script>
@endpush