<h1>Danh sách sản phẩm</h1>

@can('products.create')
    <button>+ Thêm sản phẩm mới</button>
@else
    <p style="color: grey">Bạn không có quyền tạo sản phẩm.</p>
@endcan