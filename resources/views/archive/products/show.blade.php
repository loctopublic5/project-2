@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="container margin-top-40 margin-bottom-40">

    <div class="row">

        {{-- CỘT ẢNH --}}
        <div class="col-md-6">

            {{-- ẢNH CHÍNH --}}
            <div class="product-main-image">
                <img id="mainProductImage"
                     src="{{ asset('storage/' . $product->images->first()->path) }}"
                     class="img-responsive"
                     alt="{{ $product->name }}">
            </div>

            {{-- ẢNH NHỎ --}}
            <div class="product-thumbs margin-top-10">
                @foreach($product->images as $image)
                    <img
                        src="{{ asset('storage/' . $image->path) }}"
                        class="img-thumbnail product-thumb"
                        onclick="changeMainImage(this)">
                @endforeach
            </div>

        </div>

        {{-- CỘT THÔNG TIN --}}
        <div class="col-md-6">
            <h1>{{ $product->name }}</h1>

            <p class="price">
                {{ number_format($product->price) }} đ
            </p>

            <p>
                {{ $product->description }}
            </p>

            <button class="btn btn-primary">
                Add to cart
            </button>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    function changeMainImage(el) {
        document.getElementById('mainProductImage').src = el.src;
    }
</script>
@endsection
