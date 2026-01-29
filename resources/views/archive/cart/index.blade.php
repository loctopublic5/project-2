@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('content')
<div class="container">
    <h2>Giỏ hàng</h2>

    @forelse($cart->items as $item)
        <div class="row">
            <div class="col-md-6">
                {{ $item->product->name }}
            </div>
            <div class="col-md-2">
                {{ $item->quantity }}
            </div>
            <div class="col-md-2">
                {{ number_format($item->price) }} đ
            </div>
        </div>
    @empty
        <p>Giỏ hàng trống</p>
    @endforelse
</div>
@endsection
