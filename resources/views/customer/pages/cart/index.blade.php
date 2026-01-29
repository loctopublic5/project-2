@extends('layouts.app')

@section('title', 'Shopping cart | Metronic Shop UI')


@section('meta_tags')
  <meta content="Metronic Shop UI description" name="description">
  <meta content="Metronic Shop UI keywords" name="keywords">
  <meta content="keenthemes" name="author">
  <meta property="og:site_name" content="-CUSTOMER VALUE-">
  <meta property="og:title" content="-CUSTOMER VALUE-">
  <meta property="og:description" content="-CUSTOMER VALUE-">
  <meta property="og:type" content="website">
  <meta property="og:image" content="-CUSTOMER VALUE-"><!-- link to image for socio -->
  <meta property="og:url" content="-CUSTOMER VALUE-">
@endsection

@section('extra_plugins')
  <!-- Page level plugin styles START -->
  <link href="assets/plugins/fancybox/source/jquery.fancybox.css" rel="stylesheet">
  <link href="assets/plugins/owl.carousel/assets/owl.carousel.css" rel="stylesheet">
  <link href="assets/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css">
  <link href="https://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css"><!-- for slider-range -->
  <link href="assets/plugins/rateit/src/rateit.css" rel="stylesheet" type="text/css">
  <!-- Page level plugin styles END -->
@endsection
<!-- Head END -->

<!-- Body BEGIN -->
@section('content')
<div class="main">
    <div class="container">
        <div class="row margin-bottom-40">
            <div class="col-md-12 col-sm-12">
                <h1>Shopping cart</h1>
                <div class="goods-page">
                    <div class="goods-data clearfix">
                        <div class="table-wrapper-responsive">
                            <table summary="Shopping cart" id="cart-table">
                                <thead>
                                    <tr>
                                        <th class="goods-page-image">Image</th>
                                        <th class="goods-page-description">Description</th>
                                        <th class="goods-page-ref-no">Sku</th>
                                        <th class="goods-page-quantity">Quantity</th>
                                        <th class="goods-page-price">Unit price</th>
                                        <th class="goods-page-total" colspan="2">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="cart-items-container">
                                    </tbody>
                            </table>
                        </div>

                        <div class="shopping-total">
                            <ul>
                                <li><em>Sub total</em> <strong class="price"><span id="sub-total">$0.00</span></strong></li>
                                <li><em>Shipping cost</em> <strong class="price"><span id="shipping-fee">$0.00</span></strong></li>
                                <li class="shopping-total-price"><em>Total</em> <strong class="price"><span id="final-total">$0.00</span></strong></li>
                            </ul>
                        </div>
                    </div>
                    <a href="{{ url('/') }}" class="btn btn-default">
                        Continue shopping <i class="fa fa-shopping-cart"></i>
                    </a>

                    <a href="{{ url('/checkout') }}" class="btn btn-primary" id="btn-checkout">
                        Checkout <i class="fa fa-check"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="product-pop-up" style="display: none; width: 700px;">
    <div class="product-page product-pop-up" style="min-height: auto; padding: 15px;">
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-3">
                <div class="product-main-image">
                    <img id="modal-product-image" src="" style="width: 100%; height: 420px; object-fit: cover; object-position: top;">
                </div>
                <div class="product-other-images" id="modal-product-gallery">
                    </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-9">
                <h1 id="modal-product-name">Loading...</h1>
                <div class="price-availability-block clearfix">
                    <div class="price">
                        <strong id="modal-product-price"></strong>
                        <em id="modal-product-old-price"></em>
                    </div>
                    <div class="availability">
                        Availability: <strong id="modal-product-status"></strong>
                    </div>
                </div>
                <div class="description">
                    <p id="modal-product-desc"></p>
                </div>
                <div class="product-page-options" id="modal-product-attributes">
                    </div>
                <div class="product-page-cart">
                    <div class="product-quantity">
                        <input id="modal-product-quantity" type="text" value="1" readonly class="form-control input-sm">
                    </div>
                    <button class="btn btn-primary" id="btn-modal-update-cart" data-item-id="">Update Cart</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
    <!-- END BODY -->
@push('extra_scripts')
    <script src="https://code.jquery.com/ui/1.10.3/jquery-ui.js" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/fancybox/source/jquery.fancybox.pack.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/owl.carousel/owl.carousel.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/zoom/jquery.zoom.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/bootstrap-touchspin/bootstrap.touchspin.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/uniform/jquery.uniform.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/rateit/src/jquery.rateit.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/corporate/scripts/layout.js') }}" type="text/javascript"></script>

    <script src="{{ asset('assets/js/pages/cart/shoppingCart.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            // 1. Khởi tạo Giỏ hàng trước để lấy dữ liệu ngay lập tức
            if (typeof ShoppingCart !== 'undefined') {
                console.log("🚀 Gọi ShoppingCart.init()...");
                ShoppingCart.init();
            }

            // 2. Khởi tạo Giao diện (Bọc try-catch để tránh lỗi UI làm chết code dữ liệu)
            try {
                Layout.init();    
                Layout.initOWL();
                Layout.initTwitter();
                Layout.initImageZoom();
                Layout.initTouchspin();
                Layout.initUniform();
                
                // Chỉ chạy slider nếu thư viện jQuery UI được nạp thành công
                if (typeof jQuery.ui !== 'undefined' && typeof jQuery.ui.slider !== 'undefined') {
                    Layout.initSliderRange();
                }
            } catch (e) {
                console.warn("⚠️ Cảnh báo UI:", e.message);
            }
        });
    </script>
@endpush