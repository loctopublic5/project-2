@php

    $image = $product->images->first();

    $imageUrl = $image
        ? asset('storage/' . $image->path)
        : asset('assets/pages/img/products/default.jpg');
@endphp

<div class="modal fade" id="productQuickView-{{ $product->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal">&times;</button>

                <div class="row">

                    {{-- IMAGE --}}
                    <div class="col-md-6 col-sm-6">
                        <img src="{{ $imageUrl }}"
                             class="img-responsive"
                             alt="{{ $product->name }}">
                    </div>

                    {{-- INFO --}}
                    <div class="col-md-6 col-sm-6">
                        <h2>{{ $product->name }}</h2>

                        <div class="price-availability-block clearfix">
                            <div class="price">
                                @if($product->sale_price)
                                    <strong>{{ number_format($product->sale_price) }} đ</strong>
                                    <em>{{ number_format($product->price) }} đ</em>
                                @else
                                    <strong>{{ number_format($product->price) }} đ</strong>
                                @endif
                            </div>
                            <div class="availability">
                                Availability: <strong>In Stock</strong>
                            </div>
                        </div>

                        <div class="description">
                            {{ $product->description ?? 'Đang cập nhật mô tả sản phẩm.' }}
                        </div>

                        <div class="product-page-cart">
                            <div class="product-quantity">
                                <input type="number" value="1" min="1" class="form-control input-sm">
                            </div>
                            <button class="btn btn-primary add-to-cart">
                                Add to cart
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
