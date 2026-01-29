@extends('layouts.app')

@section('title', 'Giỏ hàng của bạn')

@section('content')
<div class="main">
    <div class="container">
        <div class="row margin-bottom-40">
            <div class="col-md-12 col-sm-12">
                <h1>Giỏ hàng</h1>
                <div class="goods-page">
                    <div class="goods-data clearfix">
                        <div class="table-wrapper-responsive">
                            <table summary="Shopping cart">
                                <thead>
                                    <tr>
                                        <th class="goods-page-image">Hình ảnh</th>
                                        <th class="goods-page-description">Mô tả</th>
                                        <th class="goods-page-ref-no">Mã SP</th>
                                        <th class="goods-page-quantity">Số lượng</th>
                                        <th class="goods-page-price">Đơn giá</th>
                                        <th class="goods-page-total" colspan="2">Thành tiền</th>
                                    </tr>
                                </thead>
                                
                                <tbody id="js-cart-body">
                                    <tr>
                                        <td colspan="7" class="text-center" style="padding: 30px;">
                                            <i class="fa fa-spinner fa-spin"></i> Đang tải giỏ hàng...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="shopping-total">
                            <ul>
                                <li>
                                    <em>Tạm tính</em>
                                    <strong class="price" id="js-cart-subtotal">0 ₫</strong>
                                </li>
                                <li>
                                    <em>Phí vận chuyển</em>
                                    <strong class="price">0 ₫</strong> </li>
                                <li class="shopping-total-price">
                                    <em>Tổng cộng</em>
                                    <strong class="price" id="js-cart-total-main">0 ₫</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <a href="/" class="btn btn-default">Tiếp tục mua sắm <i class="fa fa-shopping-cart"></i></a>
                    <a href="{{ route('checkout') }}" class="btn btn-primary">Thanh toán <i class="fa fa-check"></i></a>
                </div>
            </div>
            </div>
    </div>
</div>

@endsection

@push('extra_scripts')
    <script src="assets/plugins/fancybox/source/jquery.fancybox.pack.js" type="text/javascript"></script>
    <script src="assets/plugins/owl.carousel/owl.carousel.min.js" type="text/javascript"></script>
    <script src='assets/plugins/zoom/jquery.zoom.min.js' type="text/javascript"></script>
    <script src="assets/plugins/bootstrap-touchspin/bootstrap.touchspin.js" type="text/javascript"></script>
    <script src="assets/corporate/scripts/layout.js" type="text/javascript"></script>

    <script src="{{ asset('assets/js/pages/Huy/cart.js') }}"></script> 
    <script src="{{ asset('assets/js/pages/Huy/products-list.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            Layout.init();    
            Layout.initOWL();
            Layout.initTouchspin();
        });
    </script>
@endpush