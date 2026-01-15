@extends('layouts.app')

@section('title', 'Products')

@section('content')

<div class="title-wrapper">
    <div class="container">
        <div class="container-inner">
            <h1>PRODUCTS</h1>
            <em>Browse our latest products</em>
        </div>
    </div>
</div>

<div class="main">
    <div class="container">

        {{-- BREADCRUMB --}}
        <ul class="breadcrumb">
            <li><a href="{{ route('home') }}">Home</a></li>
            <li class="active">Products</li>
        </ul>

        <div class="row margin-bottom-40">

            {{-- SIDEBAR --}}
            @include('components.sidebar')

            {{-- PRODUCT LIST --}}
            <div class="col-md-9 col-sm-7">

                <div class="row product-list">

                    @forelse ($products as $product)

                        @php
                            // LẤY ẢNH ĐẦU TIÊN CỦA SẢN PHẨM
                            $image = $product->images->first();
                        @endphp

                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <div class="product-item">
                                <div class="pi-img-wrapper">

                                    {{-- ẢNH SẢN PHẨM --}}
                                    <img
                                        src="{{ $image
                                            ? asset('storage/' . $image->path)
                                            : asset('assets/pages/img/products/default.jpg') }}"
                                        class="img-responsive"
                                        alt="{{ $product->name }}"
                                    >

                                    <div>
                                        {{-- ZOOM ẢNH (GIỮ NGUYÊN) --}}
                                        <a href="{{ $image
                                                ? asset('storage/' . $image->path)
                                                : asset('assets/pages/img/products/default.jpg') }}"
                                            class="btn btn-default fancybox-button"
                                            rel="fancybox-button">
                                                Zoom
                                        </a>


                                        <a href="#"
                                           class="btn btn-default"
                                           data-toggle="modal"
                                           data-target="#productQuickView-{{ $product->id }}">
                                            View
                                        </a>
                                    </div>
                                </div>

                                <h3>
                                    {{-- CLICK TÊN → VẪN SANG TRANG DETAIL --}}
                                    <a href="{{ route('products.show', $product->slug) }}">
                                        {{ $product->name }}
                                    </a>
                                </h3>

                                {{-- GIÁ --}}
                                <div class="pi-price">
                                    @if($product->sale_price)
                                        {{ number_format($product->sale_price) }} đ
                                        <span style="text-decoration: line-through; color:#999">
                                            {{ number_format($product->price) }} đ
                                        </span>
                                    @else
                                        {{ number_format($product->price) }} đ
                                    @endif
                                </div>

                                <a href="#" class="btn btn-default add2cart">
                                    Add to cart
                                </a>
                            </div>
                        </div>


                        @include('components.product-quick-view', ['product' => $product])


                    @empty
                        <div class="col-md-12 text-center">
                            <p>Không có sản phẩm nào.</p>
                        </div>
                    @endforelse

                </div>

                {{-- PAGINATION --}}
                <div class="row">
                    <div class="col-md-12 text-center">
                        {{ $products->links() }}
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
