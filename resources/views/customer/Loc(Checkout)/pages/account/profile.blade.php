@extends('layouts.app')

@section('title', 'My Account | Metronic Shop UI')

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
  <!-- Page level plugin styles END -->

  <!-- Theme styles START -->
  <link href="assets/pages/css/components.css" rel="stylesheet">
  <link href="assets/corporate/css/style.css" rel="stylesheet">
  <link href="assets/pages/css/style-shop.css" rel="stylesheet" type="text/css">
  <link href="assets/corporate/css/style-responsive.css" rel="stylesheet">
  <link href="assets/corporate/css/themes/red.css" rel="stylesheet" id="style-color">
  <link href="assets/corporate/css/custom.css" rel="stylesheet">
  <!-- Theme styles END -->
  <style>
        /* Tùy chỉnh để Dashboard hiện đại hơn trong nền Metronic */
        .stat-card { padding: 20px; border: 1px solid #eee; margin-bottom: 20px; border-radius: 4px; transition: all 0.3s; }
        .stat-card:hover { border-color: #E02222; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .stat-card i { font-size: 24px; float: right; color: #E02222; opacity: 0.3; }
        .stat-card .number { font-size: 20px; font-weight: 700; display: block; margin-top: 5px; }
        .stat-card .title { font-size: 12px; text-transform: uppercase; color: #777; }
        .sidebar-menu li.active a { color: #E02222 !important; font-weight: bold; background: #f9f9f9; }
        .sidebar-menu li i { margin-right: 8px; }
        .label-sm {
    padding: 3px 8px;
    font-size: 10px;
    text-transform: uppercase;
    font-weight: 600;
}
.label-info { background-color: #44b6ae; } /* Màu Teal đặc trưng Metronic */
.label-warning { background-color: #f2784b; } /* Màu Orange */

/* Metronic Badge Colors */
    .status-badge {
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        border-radius: 2px !important;
        display: inline-block;
        min-width: 90px;
        text-align: center;
    }
    .badge-pending { background-color: #F1C40F; color: #fff; }   /* Vàng - Chờ xử lý */
    .badge-confirmed { background-color: #3498DB; color: #fff; } /* Xanh dương - Đã duyệt */
    .badge-shipping { background-color: #8E44AD; color: #fff; }  /* Tím - Đang giao */
    .badge-completed { background-color: #2ECC71; color: #fff; } /* Xanh lá - Hoàn thành */
    .badge-cancelled { background-color: #E74C3C; color: #fff; } /* Đỏ - Đã hủy */
    .badge-unpaid { border: 1px solid #E74C3C; color: #E74C3C; background: transparent; }
    .badge-paid { border: 1px solid #2ECC71; color: #2ECC71; background: transparent; }
    .account-tab-content {
    min-height: 400px;
}
/* Layout cho các ô chọn tiền */
.deposit-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-top: 10px;
}
.amount-item {
    border: 1px solid #ddd;
    padding: 12px 5px;
    text-align: center;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.2s;
}
.amount-item:hover { border-color: #e02222; color: #e02222; }
.amount-item.active {
    background: #fff5f5;
    border-color: #e02222;
    color: #e02222;
    position: relative;
}
.input-blank {
    border: none;
    width: 100%;
    text-align: center;
    outline: none;
    background: transparent;
}

/* Giao diện phương thức thanh toán */
.payment-methods { display: flex; flex-direction: column; gap: 10px; }
.method-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border: 1px solid #eee;
    border-radius: 8px;
    cursor: pointer;
    position: relative;
}
.method-item input[type="radio"] { display: none; }
.method-content { display: flex; align-items: center; gap: 15px; flex: 1; }
.method-content i { font-size: 20px; width: 25px; }
.check-icon { color: #e02222; display: none; }
.method-item input:checked + .method-content + .check-icon { display: block; }
.method-item has-input:checked { border-color: #e02222; }
</style>
@endsection
<!-- Head END -->

@section('content')
<!-- Body BEGIN -->
<div class="main">
    <div class="container">
        <ul class="breadcrumb">
            <li><a href="/">Trang chủ</a></li>
            <li class="active">Tài khoản</li>
        </ul>
        
        <div class="row margin-bottom-40">
            <div class="sidebar col-md-3 col-sm-3">
                <ul class="list-group margin-bottom-25 sidebar-menu">
                    <li class="list-group-item clearfix active" data-tab="dashboard">
                        <a href="javascript:void(0);" onclick="AppAccount.switchTab('dashboard')">
                            <i class="fa fa-th-large"></i> Dashboard
                        </a>
                    </li>
                    <li class="list-group-item clearfix" data-tab="wallet">
                        <a href="javascript:void(0);" onclick="AppAccount.switchTab('wallet')">
                            <i class="fa fa-google-wallet"></i> Ví điện tử
                        </a>
                    </li>
                    <li class="list-group-item clearfix" data-tab="orders">
                        <a href="javascript:void(0);" onclick="AppAccount.switchTab('orders')">
                            <i class="fa fa-shopping-cart"></i> Đơn mua của tôi
                        </a>
                    </li>
                    <li class="list-group-item clearfix" data-tab="profile">
                        <a href="javascript:void(0);" onclick="AppAccount.switchTab('profile')">
                            <i class="fa fa-user"></i> Thông tin tài khoản
                        </a>
                    </li>
                    <li class="list-group-item clearfix">
                        <a href="javascript:void(0);" onclick="AppAccount.logout()">
                            <i class="fa fa-sign-out"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-md-9 col-sm-9" id="account-render-area">
                
                <div id="tab-dashboard" class="account-tab-content">
                    <h1>Chào mừng trở lại!</h1>
                    <div class="content-page">
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="stat-card">
                                    <i class="fa fa-money"></i>
                                    <span class="title">Số dư ví</span>
                                    <span class="number" id="db-balance">0đ</span>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <div class="stat-card">
                                    <i class="fa fa-truck"></i>
                                    <span class="title">Đơn đang chờ</span>
                                    <span class="number" id="db-pending">0</span>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <div class="stat-card">
                                    <i class="fa fa-star"></i>
                                    <span class="title">Hạng thành viên</span>
                                    <span class="number" id="db-rank">Bạc</span>
                                </div>
                            </div>
                        </div>

                        <div class="margin-top-20">
                            <h3>Đơn hàng gần đây</h3>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Mã đơn</th>
                                            <th>Ngày đặt</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody id="db-recent-orders">
                                        <tr><td colspan="5" class="text-center">Đang tải dữ liệu...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-wallet" class="account-tab-content" style="display: none;">
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="wallet-balance-card text-center" style="background: linear-gradient(135deg, #e02222 0%, #f1c40f 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 20px;">
                                <h4 style="margin:0; opacity: 0.9;">Số dư ví hiện tại</h4>
                                <h2 id="wallet-balance-big" style="font-weight: bold; margin: 10px 0;">0 đ</h2>
                                <button class="btn btn-default" onclick="$('#depositModal').modal('show')">
                                    <i class="fa fa-plus-circle"></i> NẠP TIỀN NGAY
                                </button>
                            </div>
                            <div class="well">
                                <h4>Hướng dẫn</h4>
                                <p><small>- Số dư ví dùng để thanh toán nhanh.</small></p>
                                <p><small>- Tiền nạp tối thiểu là 10.000 đ.</small></p>
                            </div>
                        </div>
                        <div class="col-md-8 col-sm-12">
                            <div class="portlet light">
                                <div class="portlet-title">
                                    <div class="caption"><span class="bold uppercase">Lịch sử giao dịch</span></div>
                                </div>
                                <div class="table-scrollable">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Mã GD</th>
                                                <th>Nội dung</th>
                                                <th>Số tiền</th>
                                                <th>Thời gian</th>
                                                <th>Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody id="wallet-history-table"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-orders" class="account-tab-content" style="display: none;">
                    <h3>Đơn mua của tôi</h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Ngày đặt</th>
                                    <th>Sản phẩm</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thanh toán</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="all-orders-table">
                                </tbody>
                        </table>
                    </div>
                </div>

            </div> </div>
    </div>
</div>

<div class="modal fade" id="orderDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Chi tiết đơn hàng <span id="md-order-code" class="text-primary"></span></h4>
            </div>
            <div class="modal-body" id="order-modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="depositModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document"> <div class="modal-content border-radius-10">
            <div class="modal-header bg-light">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title bold text-uppercase"><i class="fa fa-university"></i> Nạp tiền vào ví</h4>
            </div>
            <div class="modal-body">
                <div class="section-deposit mb-4">
                    <label class="bold mb-10">1. Chọn số tiền nạp (đ)</label>
                    <div class="deposit-grid">
                        <div class="amount-item active" onclick="WalletModule.selectAmount(50000, this)">50.000</div>
                        <div class="amount-item" onclick="WalletModule.selectAmount(100000, this)">100.000</div>
                        <div class="amount-item" onclick="WalletModule.selectAmount(200000, this)">200.000</div>
                        <div class="amount-item" onclick="WalletModule.selectAmount(500000, this)">500.000</div>
                        <div class="amount-item" onclick="WalletModule.selectAmount(1000000, this)">1.000.000</div>
                        <div class="amount-item custom-input">
                            <input type="number" id="deposit-amount" class="input-blank" placeholder="Số khác..." oninput="WalletModule.clearSelection()">
                        </div>
                    </div>
                </div>

                <hr>

                <div class="section-payment mt-20">
                    <label class="bold mb-10">2. Phương thức thanh toán</label>
                    <div class="payment-methods">
                        <label class="method-item">
                            <input type="radio" name="payment_method" value="bank" checked>
                            <div class="method-content">
                                <i class="fa fa-bank text-primary"></i>
                                <span>Chuyển khoản Ngân hàng (Auto)</span>
                            </div>
                            <i class="fa fa-check-circle check-icon"></i>
                        </label>
                        <label class="method-item">
                            <input type="radio" name="payment_method" value="momo">
                            <div class="method-content">
                                <i class="fa fa-qrcode text-danger"></i>
                                <span>Ví MoMo / QR Code</span>
                            </div>
                            <i class="fa fa-check-circle check-icon"></i>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <div class="text-left pull-left">
                    <small class="text-muted">Tổng nạp:</small>
                    <h3 id="display-total-amount" class="margin-0 bold text-danger">50,000đ</h3>
                </div>
                <button type="button" class="btn btn-primary btn-lg mt-10" style="min-width: 150px;" onclick="WalletModule.handleDeposit()">
                    XÁC NHẬN NẠP <i class="fa fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('extra_scripts')
    <!-- BEGIN PAGE LEVEL JAVASCRIPTS (REQUIRED ONLY FOR CURRENT PAGE) -->
    <script src="assets/plugins/fancybox/source/jquery.fancybox.pack.js" type="text/javascript"></script><!-- pop up -->
    <script src="assets/plugins/owl.carousel/owl.carousel.min.js" type="text/javascript"></script><!-- slider for products -->

    <script src="{{ asset('assets/js/pages/Account/utils/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/pages/Account/modules/orders.js') }}"></script>
    <script src="{{ asset('assets/js/pages/Account/modules/wallet.js') }}"></script>
    <script src="{{ asset('assets/js/pages/Account/modules/profile.js') }}"></script>
    <script src="{{ asset('assets/js/pages/Account/account-core.js') }}"></script>
    
    <script type="text/javascript">
        jQuery(document).ready(function() {
            Layout.init();    
            Layout.initOWL();
            Layout.initTwitter();

            AppAccount.init();
        });
    </script>
    <!-- END PAGE LEVEL JAVASCRIPTS -->
@endpush