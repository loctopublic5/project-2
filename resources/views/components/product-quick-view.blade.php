@php
    $image = $product->images->first();

    $imageUrl = $image
        ? asset('storage/' . $image->path)
        : asset('assets/pages/img/products/default.jpg');

    // Lấy attributes an toàn
    $attributes = is_array($product->attributes) ? $product->attributes : [];
@endphp

<div class="modal fade" id="productQuickView-{{ $product->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal">&times;</button>

                <div class="row">

                        {{-- IMAGE --}}
                        <div class="col-md-6 col-sm-6">

                        {{-- ẢNH CHÍNH --}}
                        <img src="{{ $imageUrl }}"
                            class="img-responsive"
                            alt="{{ $product->name }}"
                            style="border:1px solid #ddd; margin-bottom:10px;">

                        {{-- ẢNH NHỎ --}}
                        @php
                        $images = $product->images()->get();
                    @endphp

                    @if($images->count() > 1)

                            <div class="row">
                                @foreach($product->images as $img)
                                    <div class="col-xs-4">
                                        <img src="{{ asset('storage/' . $img->path) }}"
                                            class="img-responsive"
                                            style="cursor:pointer;border:1px solid #eee;margin-bottom:5px;">
                                    </div>
                                @endforeach
                            </div>
                        @endif

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

                        {{-- ================= SIZE & COLOR ================= --}}
                        @if(!empty($attributes))
                            <div class="product-page-options clearfix">

                                {{-- SIZE --}}
                                @if(!empty($attributes['size']))
                                    <div class="product-page-options-item">
                                        <label class="control-label">Size:</label>
                                        <select class="form-control input-sm">
                                            @foreach($attributes['size'] as $size)
                                                <option value="{{ $size }}">{{ $size }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                {{-- COLOR --}}
                                @if(!empty($attributes['color']))
                                    <div class="product-page-options-item">
                                        <label class="control-label">Color:</label>
                                        <select class="form-control input-sm">
                                            @foreach($attributes['color'] as $color)
                                                <option value="{{ $color }}">{{ $color }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                            </div>
                        @endif
                        {{-- ================= END SIZE & COLOR ================= --}}

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
