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

                <img
                    src="{{ $mainImage
                        ? asset('storage/' . $mainImage->path)
                        : asset('assets/pages/img/products/default.jpg') }}"
                    class="img-responsive"
                >

                {{-- GALLERY --}}
                <div class="row margin-top-10">
                    @foreach($product->images as $img)
                        <div class="col-xs-4">
                            <img
                                src="{{ asset('storage/' . $img->path) }}"
                                class="img-responsive"
                            >
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- INFO --}}
            <div class="col-md-6">
                <h1>{{ $product->name }}</h1>

                <div class="price-availability-block clearfix">
                    <div class="price">
                        @if($product->sale_price)
                            <strong>{{ number_format($product->sale_price) }} đ</strong>
                            <em>{{ number_format($product->price) }} đ</em>
                        @else
                            <strong>{{ number_format($product->price) }} đ</strong>
                        @endif
                    </div>
                </div>

                <div class="description">
                    {!! nl2br(e($product->description ?? 'Đang cập nhật mô tả')) !!}
                </div>

                <div class="product-page-cart">
                    <button class="btn btn-primary">
                        Thêm vào giỏ hàng
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
