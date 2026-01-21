@extends('layouts.app')

@section('title', 'Checkout | Metronic Shop UI')

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
  <link rel="shortcut icon" href="favicon.ico">

  <!-- Fonts START -->
  <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|PT+Sans+Narrow|Source+Sans+Pro:200,300,400,600,700,900&amp;subset=all" rel="stylesheet" type="text/css"> 
  <!-- Fonts END -->

  <!-- Page level plugin styles START -->
  <link href="assets/plugins/fancybox/source/jquery.fancybox.css" rel="stylesheet">
  <link href="assets/plugins/owl.carousel/assets/owl.carousel.css" rel="stylesheet">
  <link href="assets/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css">
  <!-- Page level plugin styles END -->

  <!-- Theme styles START -->
  <link href="assets/pages/css/components.css" rel="stylesheet">
  <link href="assets/corporate/css/style.css" rel="stylesheet">
  <link href="assets/pages/css/style-shop.css" rel="stylesheet" type="text/css">
  <link href="assets/corporate/css/style-responsive.css" rel="stylesheet">
  <link href="assets/corporate/css/themes/red.css" rel="stylesheet" id="style-color">
  <link href="assets/corporate/css/custom.css" rel="stylesheet">
  <!-- Theme styles END -->
@endsection
<!-- Head END -->

@section('content')
<!-- Body BEGIN -->
    <!-- BEGIN STYLE CUSTOMIZER -->
    <div class="color-panel hidden-sm">
      <div class="color-mode-icons icon-color"></div>
      <div class="color-mode-icons icon-color-close"></div>
      <div class="color-mode">
        <p>THEME COLOR</p>
        <ul class="inline">
          <li class="color-red current color-default" data-style="red"></li>
          <li class="color-blue" data-style="blue"></li>
          <li class="color-green" data-style="green"></li>
          <li class="color-orange" data-style="orange"></li>
          <li class="color-gray" data-style="gray"></li>
          <li class="color-turquoise" data-style="turquoise"></li>
        </ul>
      </div>
    </div>
    <!-- END BEGIN STYLE CUSTOMIZER --> 
<!---------------------------------------------------------------------------------------------------------------------------------------->

    <div class="main">
      <div class="container">
        <ul class="breadcrumb">
            <li><a href="index.html">Home</a></li>
            <li><a href="">Store</a></li>
            <li class="active">Checkout</li>
        </ul>
        <!-- BEGIN SIDEBAR & CONTENT -->
        <div class="row margin-bottom-40">
          <!-- BEGIN CONTENT -->
          <div class="col-md-12 col-sm-12">
            <h1>Checkout</h1>
            <!-- BEGIN CHECKOUT PAGE -->
            <div class="panel-group checkout-page accordion scrollable" id="checkout-page">


<!---------------------------------------------------------------------------------------------------------------------------------------->
<!-- BEGIN CHECKOUT -->
<div id="checkout" class="panel panel-default">
    <div class="panel-heading">
        <h2 class="panel-title">
            <a data-toggle="collapse" data-parent="#checkout-page" href="#checkout-content" class="accordion-toggle">
                Step 1: Checkout Options
            </a>
        </h2>
    </div>
    <div id="checkout-content" class="panel-collapse collapse in">
        <div class="panel-body row">
            <div class="col-md-6 col-sm-6">
                <h3>Khách hàng mới</h3>
                <p>Tùy chọn thanh toán:</p>
                <div class="radio-list">
                    <label><input type="radio" name="account" value="register" checked> Đăng ký tài khoản</label>
                </div>
                <p>Tạo tài khoản giúp bạn quản lý đơn hàng tốt hơn và nhận nhiều ưu đãi.</p>
                <button class="btn btn-primary" type="button" onclick="Checkout.proceedToStep2()">Tiếp tục</button>
            </div>
            <div class="col-md-6 col-sm-6">
                <h3>Khách hàng cũ</h3>
                <p>Tôi đã có tài khoản.</p>
                <form id="form-login-checkout">
                    <div class="form-group">
                        <label for="email-login">E-Mail <span class="require">*</span></label>
                        <input type="email" id="email-login" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="password-login">Mật khẩu <span class="require">*</span></label>
                        <input type="password" id="password-login" class="form-control">
                    </div>
                    <div class="padding-top-20">                  
                        <button class="btn btn-primary" type="submit" id="btn-login-checkout">Đăng nhập</button>
                    </div>
                </form>
            </div> 
        </div>
    </div>
</div>
              <!-- END CHECKOUT -->

              <!-- BEGIN PAYMENT ADDRESS -->
<div id="payment-address-content" class="panel-collapse collapse">
    <div class="panel-body row">
        <form id="form-register-checkout">
            <div class="col-md-6 col-sm-6">
                <h3>Your Personal Details</h3>
                <div class="form-group">
                    <label for="firstname">First Name <span class="require">*</span></label>
                    <input type="text" id="firstname" class="form-control" placeholder="Nguyễn" required>
                </div>
                <div class="form-group">
                    <label for="lastname">Last Name <span class="require">*</span></label>
                    <input type="text" id="lastname" class="form-control" placeholder="Văn A" required>
                </div>
                <div class="form-group">
                    <label for="email-reg">E-Mail <span class="require">*</span></label>
                    <input type="email" id="email-reg" class="form-control" placeholder="email@example.com" required>
                </div>
                <div class="form-group">
                    <label for="telephone">Telephone <span class="require">*</span></label>
                    <input type="text" id="telephone" class="form-control" placeholder="09xxxxxxx" required>
                </div>
            </div>

            <div class="col-md-6 col-sm-6">
                <h3>Your Password</h3>
                <div class="form-group">
                    <label for="password-reg">Password <span class="require">*</span></label>
                    <input type="password" id="password-reg" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password-confirm">Password Confirm <span class="require">*</span></label>
                    <input type="password" id="password-confirm" class="form-control" required>
                </div>
                
                <div style="margin-top: 20px;" class="hidden-xs"></div>
                <p class="text-muted"><i class="fa fa-info-circle"></i> Đảm bảo mật khẩu của bạn có ít nhất 6 ký tự để bảo mật tốt nhất.</p>
            </div>

            <div class="col-md-12">
                <hr>
                <div class="pull-right" style="margin-left: 15px;">
                    <button class="btn btn-primary" type="submit" id="btn-register-checkout">Continue</button>
                </div>
                <div class="checkbox pull-right">
                    <label>
                        <input type="checkbox" id="agree-reg" checked> I have read and agree to the <a href="javascript:;">Privacy Policy</a>
                    </label>
                </div>
            </div>
        </form>
    </div>
</div>
              <!-- END PAYMENT ADDRESS -->

              <!-- BEGIN SHIPPING ADDRESS -->
              <div id="shipping-address" class="panel panel-default">
                <div class="panel-heading">
                  <h2 class="panel-title">
                    <a data-toggle="collapse" data-parent="#checkout-page" href="#shipping-address-content" class="accordion-toggle">
    Step 2: Delivery Details
</a>
                  </h2>
                </div>
                <div id="shipping-address-content" class="panel-collapse collapse">
    <div class="panel-body row">
        <div class="col-md-12" id="address-list-container">
            <h3>Địa chỉ nhận hàng của bạn</h3>
            <div id="address-items" class="margin-bottom-20">
                <p class="text-muted">Đang tải danh sách địa chỉ...</p>
            </div>
            <button class="btn btn-default btn-sm" onclick="Checkout.Address.toggleNewAddressForm()">
                <i class="fa fa-plus"></i> Thêm địa chỉ mới
            </button>
        </div>

        <div class="col-md-12" id="new-address-form-wrapper" style="display: none; margin-top: 20px;">
            <hr>
            <h3>Thêm địa chỉ mới</h3>
            <form id="form-add-address">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="recipient_name">Tên người nhận <span class="require">*</span></label>
                            <input type="text" id="recipient_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="address_phone">Số điện thoại <span class="require">*</span></label>
                            <input type="text" id="address_phone" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="address_detail">Địa chỉ chi tiết (Số nhà, tên đường...) <span class="require">*</span></label>
            <input type="text" id="address_detail" class="form-control" placeholder="Ví dụ: 123 Đường ABC, Phường 1" required>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Tỉnh/Thành phố <span class="require">*</span></label>
            <select id="province_id" class="form-control input-sm" required>
                <option value="">-- Chọn Tỉnh --</option>
                <option value="1">Hà Nội</option>
                <option value="2">TP. Hồ Chí Minh</option>
                </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Quận/Huyện <span class="require">*</span></label>
            <select id="district_id" class="form-control input-sm" required>
                <option value="">-- Chọn Huyện --</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Phường/Xã <span class="require">*</span></label>
            <select id="ward_id" class="form-control input-sm" required>
                <option value="">-- Chọn Xã --</option>
            </select>
        </div>
    </div>
</div>
                <div class="checkbox">
                    <label><input type="checkbox" id="is_default"> Đặt làm địa chỉ mặc định</label>
                </div>
                <div class="margin-top-10">
        <button type="button" class="btn btn-primary" id="btn-save-address" onclick="Checkout.Address.saveAddressManual()">
        Lưu địa chỉ
    </button>
    
    <button type="button" class="btn btn-default" onclick="Checkout.Address.toggleNewAddressForm(false)">
        Hủy
    </button>
    </div>
            </form>
        </div>

        <div class="col-md-12 margin-top-20">
            <hr>
            <button class="btn btn-primary pull-right" type="button" id="btn-continue-shipping" onclick="Checkout.Address.confirmAddress()">
                Tiếp tục phương thức vận chuyển
            </button>
        </div>
    </div>
</div>

<style>
    /* Tổng thể khung địa chỉ */
    .address-item { 
        position: relative;
        border: 1px solid #ddd; 
        padding: 15px; 
        margin-bottom: 12px; 
        cursor: pointer; 
        border-radius: 4px; 
        transition: all 0.2s ease-in-out;
        background: #fff;
        opacity: 1 !important; /* Đảm bảo không bị mờ */
    }

    /* Hiệu ứng khi di chuột qua */
    .address-item:hover { 
        border-color: #999;
        background: #f9f9f9;
    }

    /* TRẠNG THÁI ACTIVE (KHI ĐƯỢC CHỌN) */
    .address-item.active { 
        border-color: #e84d1c !important; 
        border-width: 2px;
        background: #fffcfb !important; 
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    /* ÉP CHỮ ĐEN ĐẬM KHI ACTIVE */
    .address-item.active strong,
    .address-item.active p,
    .address-item.active small,
    .address-item.active i {
        color: #000 !important; /* Đen tuyền */
        text-shadow: none !important; /* Loại bỏ bóng mờ hoàn toàn */
        opacity: 1 !important;
    }

    .address-item.active strong {
        font-size: 15px;
        font-weight: 700; /* Đậm nhất cho tên người nhận */
    }

    .address-item.active p {
        font-weight: 500; /* Đậm vừa cho địa chỉ */
        line-height: 1.5;
    }

    /* Icon checkmark ở góc phải để nhận diện tốt hơn */
    .address-item.active::after {
        content: "\f058"; /* fa-check-circle */
        font-family: 'FontAwesome';
        position: absolute;
        top: 15px;
        right: 15px;
        color: #e84d1c;
        font-size: 20px;
    }

    /* Badge mặc định */
    .address-item .badge { 
        background-color: #e84d1c; 
        font-weight: 600;
        padding: 3px 8px;
    }
</style>
              <!-- END SHIPPING ADDRESS -->

              <!-- BEGIN PAYMENT METHOD -->
<div id="payment-method" class="panel panel-default">
    <div class="panel-heading">
        <h2 class="panel-title">
            <a data-toggle="collapse" data-parent="#checkout-page" href="#payment-method-content" class="accordion-toggle">
                Step 3: Payment Method
            </a>
        </h2>
    </div>
    <div id="payment-method-content" class="panel-collapse collapse">
        <div class="panel-body row">
            <div class="col-md-12">
                <p>Vui lòng chọn phương thức thanh toán ưu tiên cho đơn hàng này.</p>
                <div class="radio-list">
                    <label>
                        <input type="radio" name="payment_method" value="cod" checked> Thanh toán khi giao hàng (COD)
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="wallet"> Thanh toán qua Ví điện tử
                    </label>
                </div>

                <div id="wallet-info-container" style="display: none; margin-top: 15px; padding: 15px; background: #f9f9f9; border-left: 4px solid #e84d1c;">
                    <h4>Thông tin ví của bạn</h4>
                    <div id="wallet-status-content">
                        <i class="fa fa-spinner fa-spin"></i> Đang kiểm tra số dư...
                    </div>
                </div>

                <div class="form-group margin-top-20">
                    <label for="delivery-payment-method">Ghi chú về đơn hàng</label>
                    <textarea id="delivery-payment-method" rows="3" class="form-control"></textarea>
                </div>

                <div class="checkbox pull-right">
                    <label>
                        <input type="checkbox" id="agree-terms"> Tôi đã đọc và đồng ý với <a href="javascript:;">Điều khoản & Điều kiện</a>
                    </label>
                </div>

                <div id="recharge-section" class="margin-top-15" style="display:none;">
                    <div class="alert alert-warning">
                        <p><i class="fa fa-info-circle"></i> Bạn không đủ số dư để thực hiện thanh toán này.</p>
                        <div class="margin-top-10">
                            <a href="/customer/wallet/deposit?redirect_to=checkout" class="btn btn-danger btn-sm">
                                <i class="fa fa-plus"></i> Nạp tiền ngay
                            </a>
                        </div>
                    </div>
                </div>

                <div class="margin-top-20">
                    <button class="btn btn-primary pull-right" type="button" id="btn-confirm-payment" onclick="Checkout.Payment.confirmPayment()">
                        Tiếp tục
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
              <!-- END PAYMENT METHOD -->

<div id="confirm" class="panel panel-default">
    <div class="panel-heading">
        <h2 class="panel-title">
            <a data-toggle="collapse" data-parent="#checkout-page" href="#confirm-content" class="accordion-toggle">
                Step 4: Confirm Order
            </a>
        </h2>
    </div>
    <div id="confirm-content" class="panel-collapse collapse">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="table-wrapper-responsive">
                        <table id="table-confirm-order" class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th class="checkout-image">HÌNH ẢNH</th>
                                    <th class="checkout-description">MÔ TẢ</th>
                                    <th class="checkout-model">SKU</th>
                                    <th class="checkout-quantity">SỐ LƯỢNG</th>
                                    <th class="checkout-price">GIÁ</th>
                                    <th class="checkout-total">THÀNH TIỀN</th>
                                </tr>
                            </thead>
                            <tbody>
                                </tbody>
                        </table>
                    </div>

                    <div class="checkout-total-block clearfix">
                        <ul id="checkout-final-summary" class="list-unstyled">
                            </ul>
                    </div>

                    <div class="clearfix" style="clear: both; margin-bottom: 20px;"></div>
                    <hr>

                    <div class="confirm-order-actions text-right">
                        <button type="button" class="btn btn-default" style="margin-right: 10px;" onclick="window.location.reload()">
                            HỦY BỎ
                        </button>
                        <button class="btn btn-primary" type="button" id="button-confirm" onclick="Checkout.OrderReview.placeOrder()">
                            XÁC NHẬN ĐẶT HÀNG
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="steps-block steps-block-red">
    </div>

<!-- END REVIEW & CONFIRM -->
<!---------------------------------------------------------------------------------------------------------------------------------------->
@endsection

@push('extra_scripts')
    <!-- BEGIN CORE PLUGINS(REQUIRED FOR ALL PAGES) -->
    <script src="assets/plugins/jquery-migrate.min.js" type="text/javascript"></script>    
    <script src="assets/corporate/scripts/back-to-top.js" type="text/javascript"></script>
    <script src="assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <!-- END CORE PLUGINS -->

    <!-- BEGIN PAGE LEVEL JAVASCRIPTS (REQUIRED ONLY FOR CURRENT PAGE) -->
    <script src="assets/plugins/fancybox/source/jquery.fancybox.pack.js" type="text/javascript"></script><!-- pop up -->
    <script src="assets/plugins/owl.carousel/owl.carousel.min.js" type="text/javascript"></script><!-- slider for products -->
    <script src='assets/plugins/zoom/jquery.zoom.min.js' type="text/javascript"></script><!-- product zoom -->
    <script src="assets/plugins/bootstrap-touchspin/bootstrap.touchspin.js" type="text/javascript"></script><!-- Quantity -->
    <script src="assets/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
    <script src="assets/corporate/scripts/layout.js" type="text/javascript"></script>


    <!-- JS Core) -->
    <script src="assets/js/pages/checkout/checkout-core.js" type="text/javascript"></script>
    <script src="assets/js/pages/checkout/checkout-auth.js" type="text/javascript"></script>
    <script src="assets/js/pages/checkout/checkout-address.js" type="text/javascript"></script>
    <script src="assets/js/pages/checkout/checkout-payment.js" type="text/javascript"></script>
    <script src="assets/js/pages/checkout/checkout-review.js" type="text/javascript"></script>

    <script type="text/javascript">
    jQuery(document).ready(function() {
        Layout.init();    
        Layout.initOWL();
        Layout.initTwitter();
        Layout.initImageZoom();
        Layout.initTouchspin();
        Layout.initUniform(); 
        
        // Kích hoạt logic Checkout của chúng ta
        if (typeof Checkout !== 'undefined') {
            Checkout.init();
        }
    });
</script>
@endpush