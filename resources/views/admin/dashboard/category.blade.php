@extends('layouts.admin')

@section('title', 'Quản lý Danh mục')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <p class="text-subtitle text-muted">Quản lý cây danh mục sản phẩm.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <button class="btn btn-primary" onclick="openCreateModal()">
                        <i class="bi bi-plus"></i> Thêm danh mục
                    </button>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="table-categories">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên danh mục</th>
                                <th>Slug</th>
                                <th>Danh mục cha</th>
                                <th>Trạng thái</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="category-list">
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Thêm mới danh mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="category-form">
                <div class="modal-body">
                    <input type="hidden" id="category_id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" onkeyup="generateSlug()" placeholder="Nhập tên danh mục">
                            <div class="invalid-feedback" id="error-name"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="slug" class="form-label">Slug (URL)</label>
                            <input type="text" class="form-control" id="slug" placeholder="tự-động-sinh">
                            <div class="invalid-feedback" id="error-slug"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="parent_id" class="form-label">Danh mục cha</label>
                            <select class="form-select" id="parent_id">
                                <option value="">-- Là danh mục gốc --</option>
                                </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng thái</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Hiển thị</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary ml-1" id="btn-save">
                        <i class="bi bi-check d-none d-sm-inline-block"></i>
                        <span id="btn-text">Lưu lại</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('admin_assets/js/pages/categories.js') }}?v={{ time() }}"></script>
@endpush