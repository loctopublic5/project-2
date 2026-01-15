
@extends('layouts.app')

@section('title', $product->name)

@section('content')

<div class="main">
    <div class="container">

        <div class="row margin-bottom-40">

            {{-- IMAGE --}}
            <div class="col-md-6">

                @php
                    $mainImage = $product->images->first();
                @endphp

                {{-- ẢNH CHÍNH --}}
                <img
                    id="main-product-image"
                    src="{{ $mainImage
                        ? asset('storage/' . $mainImage->path)
                        : asset('assets/pages/img/products/default.jpg') }}"
                    class="img-responsive"
                    style="border:1px solid #eee; padding:5px; width:100%;"
                    alt="{{ $product->name }}"
                >

                {{-- ẢNH NHỎ --}}
                @if($product->images->count() > 1)
                    <div class="row margin-top-10">
                        @foreach($product->images as $img)
                            <div class="col-xs-3">
                                <img
                                    src="{{ asset('storage/' . $img->path) }}"
                                    class="img-responsive thumb-image"
                                    style="
                                        cursor:pointer;
                                        border:1px solid #ddd;
                                        padding:3px;
                                        margin-bottom:10px;
                                    "
                                    onclick="changeMainImage('{{ asset('storage/' . $img->path) }}')"
                                    alt="Thumbnail"
                                >
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- INFO --}}
            <div class="col-md-6">
                <h1>{{ $product->name }}</h1>

                <div class="price-availability-block clearfix">
                    <div class="price">
                        @if($product->sale_price)
                            <strong>{{ number_format($product->sale_price) }} đ</strong>
                            <em style="text-decoration: line-through;">
                                {{ number_format($product->price) }} đ
                            </em>
                        @else
                            <strong>{{ number_format($product->price) }} đ</strong>
                        @endif
                    </div>
                </div>

                <div class="description margin-bottom-20">
                    {!! nl2br(e($product->description ?? 'Đang cập nhật mô tả')) !!}
                </div>

                <div class="product-page-cart">
                    <button class="btn btn-primary btn-lg">
                        <form action="{{ route('cart.add') }}" method="POST">
    @csrf

    <input type="hidden" name="product_id" value="{{ $product->id }}">

    {{-- SIZE --}}
    @if(!empty($product->attributes['size']))
        <select name="options[size]" class="form-control input-sm">
            @foreach($product->attributes['size'] as $size)
                <option value="{{ $size }}">{{ $size }}</option>
            @endforeach
        </select>
    @endif

    {{-- COLOR --}}
    @if(!empty($product->attributes['color']))
        <select name="options[color]" class="form-control input-sm">
            @foreach($product->attributes['color'] as $color)
                <option value="{{ $color }}">{{ $color }}</option>
            @endforeach
        </select>
    @endif

    {{-- QUANTITY --}}
    <input type="number"
           name="quantity"
           value="1"
           min="1"
           class="form-control input-sm"
           style="width:80px; margin:10px 0">

    <button type="submit" class="btn btn-primary">
        Add to cart
    </button>
</form>

                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- JS ĐỔI ẢNH --}}
<script>
    function changeMainImage(src) {
        document.getElementById('main-product-image').src = src;
    }
</script>
@endsection
