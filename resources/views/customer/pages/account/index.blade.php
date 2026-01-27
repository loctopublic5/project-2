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
  
  <link href="assets/css/profile.css" rel="stylesheet">
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
                    <li class="list-group-item clearfix" data-tab="addresses">
                        <a href="javascript:void(0);" onclick="AppAccount.switchTab('addresses')">
                            <i class="fa fa-map-marker"></i> Sổ địa chỉ
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
        <div class="stat-card clickable-card" data-target="wallet">
            <i class="fa fa-money"></i>
            <span class="title">Số dư ví</span>
            <span class="number" id="db-balance">0đ</span>
        </div>
    </div>
    <div class="col-md-4 col-sm-4">
        <div class="stat-card clickable-card" data-target="orders">
            <i class="fa fa-truck"></i>
            <span class="title">Đơn đang chờ</span>
            <span class="number" id="db-pending">0</span>
        </div>
    </div>
    <div class="col-md-4 col-sm-4">
        <div class="stat-card clickable-card" data-target="profile">
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
    <ul class="nav nav-tabs order-tabs" role="tablist">
        <li class="active"><a href="javascript:;" onclick="OrderModule.filter('')">Tất cả</a></li>
        <li><a href="javascript:;" onclick="OrderModule.filter('pending')">Chờ xác nhận</a></li>
        <li><a href="javascript:;" onclick="OrderModule.filter('shipping')">Đang giao</a></li>
        <li><a href="javascript:;" onclick="OrderModule.filter('completed')">Hoàn thành</a></li>
        <li><a href="javascript:;" onclick="OrderModule.filter('cancelled')">Đã hủy</a></li>
    </ul>

    <div id="order-history-list" class="margin-top-20">
        </div>
</div>

<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Đánh giá sản phẩm</h4>
            </div>
            <div class="modal-body" id="review-modal-body">
                </div>
        </div>
    </div>
</div>
<div id="tab-addresses" class="account-tab-content" style="display: none;">
    <div class="row margin-bottom-20">
        <div class="col-md-6">
            <h3>Sổ địa chỉ</h3>
        </div>
        <div class="col-md-6 text-right">
            <button class="btn btn-primary" onclick="AddressModule.showAddModal()">
                <i class="fa fa-plus"></i> Thêm địa chỉ mới
            </button>
        </div>
    </div>

    <div id="address-list-grid" class="row">
        </div>
</div>

<div class="modal fade" id="addressModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-radius-10">
            <form id="address-form">
                <input type="hidden" id="address-id"> <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title bold" id="address-modal-title">Thêm địa chỉ nhận hàng</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Họ tên người nhận <span class="text-danger">*</span></label>
                            <input type="text" name="recipient_name" class="form-control" required placeholder="VD: Nguyễn Văn A">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" required placeholder="VD: 0912345xxx">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Tỉnh/Thành phố</label>
                            <select name="province_id" class="form-control" required>
                                <option value="1">TP. Hồ Chí Minh</option> <option value="2">Hà Nội</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Quận/Huyện</label>
                            <select name="district_id" class="form-control" required>
                                <option value="101">Quận 1</option>
                                <option value="102">Quận 7</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Phường/Xã</label>
                            <select name="ward_id" class="form-control" required>
                                <option value="1001">Phường Bến Nghé</option>
                                <option value="1002">Phường Tân Phong</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ cụ thể <span class="text-danger">*</span></label>
                        <textarea name="address_detail" class="form-control" rows="2" required placeholder="Số nhà, tên đường..."></textarea>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="is_default"> Đặt làm địa chỉ mặc định
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu thông tin</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="tab-profile" class="account-tab-content" style="display: none;">
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <div class="portlet light profile-sidebar-portlet" style="border: 1px solid #eee; padding: 20px; background: #fff;">
                <div class="profile-userpic text-center">
    <div id="avatar-wrapper" class="position-relative d-inline-block">
        <img id="detail-avatar" src="{{ asset('admin_assets/assets/compiled/jpg/1.jpg') }}" 
            class="img-responsive global-user-avatar" alt="Avatar">
        
        <div class="avatar-overlay">
            <div class="overlay-content">
                <button type="button" onclick="UserProfileModule.viewFullAvatar()" class="btn-avatar-action" title="Xem ảnh">
                    <i class="fa fa-search-plus"></i>
                </button>
                <button type="button" onclick="$('#avatar-input').click()" class="btn-avatar-action" title="Đổi ảnh">
                    <i class="fa fa-camera"></i>
                </button>
            </div>
        </div>
        <input type="file" id="avatar-input" accept="image/*" style="display: none;">
    </div>
    <button type="button" class="btn btn-sm btn-primary" onclick="UserProfileModule.openEditModal()">
    <i class="fa fa-edit"></i> Chỉnh sửa thông tin
</button>
</div>
                <div class="profile-usertitle text-center margin-top-20">
                    <div class="profile-usertitle-name" id="detail-name" style="font-size: 20px; font-weight: 600;"> Đang tải... </div>
                    <div class="profile-usertitle-job"> 
                        <span id="detail-rank" style="margin-top: 5px;"> Đang kiểm tra... </span> 
                    </div>
                </div>
                
                <hr>

                <div class="profile-userbuttons">
                    <div class="margin-bottom-10">
                        <i class="fa fa-envelope"></i> <span id="detail-email">---</span>
                    </div>
                    <div class="margin-bottom-10">
                        <i class="fa fa-phone"></i> <span id="detail-phone">---</span>
                    </div>
                    <div class="margin-bottom-10">
                        <i class="fa fa-calendar"></i> Tham gia: <b id="detail-joined">---</b>
                    </div>
                    <div id="detail-status" class="margin-top-10">
                        </div>
                </div>
            </div>

            <div class="well margin-top-20" style="background: #e02222; color: white; border: none;">
                <div class="row">
                    <div class="col-xs-3"><i class="fa fa-google-wallet" style="font-size: 40px;"></i></div>
                    <div class="col-xs-9 text-right">
                        <h4 style="margin:0; opacity: 0.8; font-size: 13px;">Số dư hiện tại</h4>
                        <h3 class="bold" id="detail-wallet" style="margin: 5px 0;">0 ₫</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 col-sm-12">
            <div class="portlet light" style="border: 1px solid #eee; padding: 15px; background: #fff; margin-bottom: 20px;">
                <div class="portlet-title" style="border-bottom: 1px solid #eee; margin-bottom: 15px;">
                    <div class="caption"><h4 class="bold uppercase" style="margin:0;">Sổ địa chỉ</h4></div>
                </div>
                <div class="table-scrollable">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Người nhận</th>
                                <th>SĐT</th>
                                <th>Địa chỉ</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody id="address-list">
                            <tr><td colspan="4" class="text-center">Đang tải dữ liệu...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="portlet light" style="border: 1px solid #eee; padding: 15px; background: #fff;">
                <div class="portlet-title" style="border-bottom: 1px solid #eee; margin-bottom: 15px;">
                    <div class="caption"><h4 class="bold uppercase" style="margin:0;">Đơn hàng gần đây</h4></div>
                </div>
                <div class="table-scrollable">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody id="order-list">
                            <tr><td colspan="4" class="text-center">Đang tải dữ liệu...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
            </div> 
        </div>
    </div>

<div class="modal fade" id="reviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title bold text-primary">Đánh giá sản phẩm</h4>
            </div>
            <div class="modal-body" id="review-modal-body" style="max-height: 450px; overflow-y: auto;">
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
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
<div class="modal fade" id="modal-edit-profile" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Cập nhật thông tin cá nhân</h4>
            </div>
            <div class="modal-body">
                <form id="form-edit-profile">
                    <div class="form-group">
                        <label>Họ và tên</label>
                        <input type="text" class="form-control" id="edit-full-name" name="full_name">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" id="edit-email" name="email">
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" class="form-control" id="edit-phone" name="phone">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="UserProfileModule.saveBasicInfo()">Lưu thay đổi</button>
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
    <script src="{{ asset('assets/js/pages/Account/modules/address.js') }}"></script>
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