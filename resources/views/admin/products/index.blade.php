@extends('layouts.admin')

@section('title', 'Quản lý Sản phẩm')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
            </div>
            </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="row g-3">
                    <div class="col-md-auto">
                        <button class="btn btn-primary w-100" onclick="openCreateModal()">
                            <i class="bi bi-plus-circle-fill"></i> Thêm mới
                        </button>
                    </div>

                    <div class="col-md-3">
                        <select class="form-select" id="filter-category" onchange="loadProducts(1)">
                            <option value="">-- Tất cả danh mục --</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select class="form-select" id="filter-status" onchange="loadProducts(1)">
                            <option value="">-- Trạng thái --</option>
                            <option value="1">Đang bán</option>
                            <option value="0">Tạm ẩn</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select class="form-select" id="sort-by" onchange="loadProducts(1)">
                            <option value="latest">Mới nhất</option>
                            <option value="price_asc">Giá tăng dần</option>
                            <option value="price_desc">Giá giảm dần</option>
                        </select>
                    </div>

                    <div class="col-md">
                        <div class="input-group">
                            <input type="text" id="search-input" class="form-control" placeholder="Tên sp, SKU...">
                            <button class="btn btn-primary" onclick="loadProducts(1)">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="table-products">
                        <thead>
                            <tr>
                                <th>Ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá bán</th>
                                <th>Kho</th>
                                <th>Trạng thái</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="product-list-body">
                            </tbody>
                    </table>
                </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap">
                        <div class="text-muted small" id="pagination-info">
                            Đang tải dữ liệu...
                        </div>
                    <div id="pagination-links">
                </div>
            </div>
            </div>
        </div>
    </section>
</div>

@include('admin.products.modal') 
{{-- (Nếu bạn tách modal ra file riêng, hoặc giữ nguyên modal code ở dưới file này như cũ) --}}

@endsection

@push('scripts')
    <script src="{{ asset('admin_assets/js/pages/products.js') }}"></script>
@endpush