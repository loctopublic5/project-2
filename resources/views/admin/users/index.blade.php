@extends('layouts.admin')

@section('title', 'Quản lý Khách hàng')

@section('content')
<div class="page-heading">
</div>

<div class="page-content">
    <div class="row">
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-3 py-4-5">
                    <div class="row">
                        <div class="col-md-4"><div class="stats-icon purple"><i class="bi bi-people-fill"></i></div></div>
                        <div class="col-md-8">
                            <h6 class="text-muted font-semibold">Tổng User</h6>
                            <h6 class="font-extrabold mb-0" id="stat-total">...</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6">
             <div class="card"><div class="card-body px-3 py-4-5"><div class="row"><div class="col-md-4"><div class="stats-icon green"><i class="bi bi-person-check-fill"></i></div></div><div class="col-md-8"><h6 class="text-muted font-semibold">Active</h6><h6 class="font-extrabold mb-0" id="stat-active">...</h6></div></div></div></div>
        </div>
        <div class="col-6 col-lg-3 col-md-6">
             <div class="card"><div class="card-body px-3 py-4-5"><div class="row"><div class="col-md-4"><div class="stats-icon red"><i class="bi bi-person-x-fill"></i></div></div><div class="col-md-8"><h6 class="text-muted font-semibold">Banned</h6><h6 class="font-extrabold mb-0" id="stat-banned">...</h6></div></div></div></div>
        </div>
        <div class="col-6 col-lg-3 col-md-6">
             <div class="card"><div class="card-body px-3 py-4-5"><div class="row"><div class="col-md-4"><div class="stats-icon blue"><i class="bi bi-person-plus-fill"></i></div></div><div class="col-md-8"><h6 class="text-muted font-semibold">Khách mới</h6><h6 class="font-extrabold mb-0" id="stat-new">...</h6></div></div></div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" id="filter-keyword" class="form-control" placeholder="Tìm tên, email, sđt...">
                </div>
                <div class="col-md-3">
                    <select id="filter-status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="banned">Banned</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="sort-spending" class="form-select">
                        <option value="">Sắp xếp mặc định</option>
                        <option value="desc">Chi tiêu cao nhất (VIP)</option>
                        <option value="asc">Chi tiêu thấp nhất</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" id="btn-filter">Lọc dữ liệu</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Khách hàng</th>
                            <th>Role</th>
                            <th class="text-end">Ví tiền</th>
                            <th class="text-end">LTV (Chi tiêu)</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="user-list-body"></tbody>
                </table>
            </div>
            <div id="pagination-links" class="mt-3 d-flex justify-content-end"></div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('admin_assets/js/pages/users.js') }}"></script>
@endpush
@endsection