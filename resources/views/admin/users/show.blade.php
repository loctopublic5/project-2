@extends('layouts.admin')

@section('title', 'Hồ sơ Khách hàng 360')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Hồ sơ Khách hàng</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/users">Users</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="row">
        <div class="col-12 col-lg-4">
            
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-center align-items-center flex-column">
                        <div class="avatar avatar-2xl">
                            <img id="detail-avatar" src="{{ asset('admin_assets/assets/compiled/jpg/1.jpg') }}" alt="Avatar" style="object-fit: cover;">
                        </div>
                        
                        <h3 class="mt-3 text-center" id="detail-name">Loading...</h3>
                        
                        <p class="text-small badge bg-light-primary text-primary" id="detail-rank">Checking...</p>
                    </div>
                    
                    <hr>
                    <div class="info-list">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-envelope fs-4"></i>
                            <span class="ms-3 text-break" id="detail-email">---</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-phone fs-4"></i>
                            <span class="ms-3" id="detail-phone">---</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-calendar fs-4"></i>
                            <span class="ms-3">Tham gia: <b id="detail-joined">---</b></span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-shield-check fs-4"></i>
                            <span class="ms-3" id="detail-status">---</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row">
                        <div class="col-3"><i class="bi bi-wallet2 fs-1"></i></div>
                        <div class="col-9 text-end">
                            <h6 class="text-white-50">Số dư ví hiện tại</h6>
                            <h3 class="text-white font-bold" id="detail-wallet">0 ₫</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            
            <div class="card">
                <div class="card-header">
                    <h4>Sổ địa chỉ</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-lg">
                            <thead>
                                <tr>
                                    <th>Người nhận</th>
                                    <th>SĐT</th>
                                    <th>Địa chỉ</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody id="address-list">
                                <tr><td colspan="4" class="text-center text-muted">Đang tải...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>5 Đơn hàng gần nhất</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody id="order-list">
                                <tr><td colspan="4" class="text-center text-muted">Đang tải...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('admin_assets/js/pages/user-detail.js') }}"></script>
@endpush
@endsection