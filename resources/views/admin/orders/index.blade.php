@extends('layouts.admin')

@section('title', 'Quản lý Đơn hàng')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Quản lý Đơn hàng</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.view') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Đơn hàng</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header border-bottom">
                <ul class="nav nav-tabs card-header-tabs" id="orderStatusTabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" onclick="filterStatus('')" data-status="">Tất cả</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="#" onclick="filterStatus('pending')" data-status="pending">Chờ xử lý</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-info" href="#" onclick="filterStatus('confirmed')" data-status="confirmed">Đã duyệt</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-primary" href="#" onclick="filterStatus('shipping')" data-status="shipping">Đang giao</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-success" href="#" onclick="filterStatus('completed')" data-status="completed">Hoàn thành</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="#" onclick="filterStatus('cancelled')" data-status="cancelled">Đã hủy</a>
                    </li>
                </ul>
            </div>
            
            <div class="card-body mt-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="search-input" class="form-control" placeholder="Mã đơn, SĐT, Tên khách...">
                            <button class="btn btn-primary" onclick="loadOrders(1)">Tìm</button>
                        </div>
                    </div>
                    <div class="col-md-auto ms-auto">
                        <button class="btn btn-outline-secondary" onclick="loadOrders(1)"><i class="bi bi-arrow-clockwise"></i> Làm mới</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Thanh toán</th>
                                <th>Trạng thái</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="order-list-body">
                            </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
                    <div id="pagination-info" class="text-muted small"></div>
                    
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-primary justify-content-end mb-0" id="pagination-links">
                            </ul>
                    </nav>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="orderDetailModal" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">Chi tiết đơn hàng: <span id="modal-order-code">...</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card mb-0 border shadow-none h-100">
                             <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead class="border-bottom">
                                            <tr>
                                                <th class="ps-4">Sản phẩm</th>
                                                <th class="text-center">SL</th>
                                                <th class="text-end">Đơn giá</th>
                                                <th class="text-end pe-4">Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody id="modal-order-items"></tbody>
                                        <tfoot class="border-top">
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold pt-3">Tổng tiền hàng:</td>
                                                <td class="text-end pt-3 pe-4" id="modal-subtotal">0 ₫</td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold">Phí vận chuyển:</td>
                                                <td class="text-end pe-4" id="modal-shipping">0 ₫</td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold text-primary h5 mb-0 pb-3">TỔNG CỘNG:</td>
                                                <td class="text-end fw-bold text-primary h5 mb-0 pb-3 pe-4" id="modal-total">0 ₫</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border shadow-none mb-3">
                            <div class="card-body">
                                <h6 class="card-title text-primary mb-3"><i class="bi bi-person-circle"></i> Khách hàng</h6>
                                <p class="mb-1 fw-bold fs-6" id="modal-customer-name">...</p>
                                <p class="mb-0 text-muted"><i class="bi bi-telephone"></i> <span id="modal-customer-phone">...</span></p>
                            </div>
                        </div>
                        <div class="card border shadow-none mb-3">
                            <div class="card-body">
                                <h6 class="card-title text-primary mb-3"><i class="bi bi-geo-alt-fill"></i> Giao hàng tới</h6>
                                <p class="mb-0 small text-break lh-sm" id="modal-shipping-address">...</p>
                            </div>
                        </div>
                        <div class="card border shadow-none mb-0">
                            <div class="card-body">
                                <h6 class="card-title text-primary mb-3"><i class="bi bi-credit-card-2-front-fill"></i> Thanh toán</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Phương thức:</span><span class="fw-bold" id="modal-payment-method">...</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Trạng thái:</span><span id="modal-payment-status">...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="cancel-reason-area" class="alert alert-light-danger border-danger mt-3 d-none">
                    <strong><i class="bi bi-x-circle"></i> Lý do hủy đơn:</strong> <span id="cancel-reason-text"></span>
                </div>
            </div>

            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <div id="modal-actions"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('admin_assets/js/pages/orders.js') }}"></script>
@endpush