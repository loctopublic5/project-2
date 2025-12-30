<!DOCTYPE html>
<html>
<head>
    <title>Danh sách sản phẩm</title>
</head>
<body>

<h1>Danh sách sản phẩm</h1>

@if($products->count())
    <ul>
        @foreach($products as $product)
            <li>
                <strong>{{ $product->name }}</strong><br>
                Giá: {{ number_format($product->price) }} đ<br>
                Danh mục: {{ $product->category->name ?? 'N/A' }}
            </li>
            <hr>
        @endforeach
    </ul>
@else
    <p>Không có sản phẩm</p>
@endif

</body>
</html>
