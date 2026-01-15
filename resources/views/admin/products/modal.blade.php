<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modalTitle">Thêm mới sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <form id="productForm" enctype="multipart/form-data">
                    <input type="hidden" id="product_id" name="id">

                    <div class="row g-4">
                        <div class="col-md-8">
                            <div class="card mb-3 border"> 
                                <div class="card-body">
                                    <h6 class="card-title text-primary mb-3">Thông tin chung</h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required placeholder="Nhập tên sản phẩm">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Mã SKU <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="sku" name="sku" required placeholder="Mã duy nhất">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                            <select class="form-select" id="category_id" name="category_id" required>
                                                <option value="">Đang tải...</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Giá niêm yết</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="price" name="price" required>
                                                <span class="input-group-text">₫</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Giá khuyến mãi</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="sale_price" name="sale_price">
                                                <span class="input-group-text">₫</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Mô tả ngắn</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-title text-primary m-0">Thuộc tính (Size, Color...)</h6>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addAttributeRow()">
                                            <i class="bi bi-plus-circle"></i> Thêm dòng
                                        </button>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table mb-0">
                                            <thead>
                                                <tr>
                                                    <th style="width: 35%">Tên thuộc tính</th>
                                                    <th>Giá trị (cách nhau dấu phẩy)</th>
                                                    <th style="width: 50px"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="attribute-list">
                                                </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <h6 class="card-title text-primary mb-3">Hình ảnh & Kho</h6>
                                    
                                    <div class="mb-3 text-center">
                                        <div class="ratio ratio-1x1 border rounded mb-2 position-relative overflow-hidden group-hover-upload">
                                            <img id="image-preview" src="{{ asset('admin_assets/assets/compiled/jpg/1.jpg') }}" 
                                                 class="w-100 h-100 object-fit-cover" 
                                                 style="cursor: pointer;" onclick="document.getElementById('image').click()">
                                        </div>
                                        <input type="file" class="d-none" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                                        <small class="text-muted d-block">Nhấn vào ảnh để thay đổi</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Số lượng kho</label>
                                        <input type="number" class="form-control" id="stock_qty" name="stock_qty" value="0">
                                    </div>

                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input fs-5" type="checkbox" id="is_active" name="is_active" checked style="cursor: pointer">
                                        <label class="form-check-label pt-1 ms-2" for="is_active">Đang bán</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form> 
                
                <datalist id="attribute-suggestions">
                    <option value="Màu sắc">
                    <option value="Kích thước">
                    <option value="Chất liệu">
                </datalist>
            </div>

            <div class="modal-footer border-top">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                    <span class="d-none d-sm-block">Đóng</span>
                </button>
                <button type="button" class="btn btn-primary ml-1" onclick="saveProduct()">
                    <span class="d-none d-sm-block">Lưu dữ liệu</span>
                </button>
            </div>
        </div>
    </div>
</div>