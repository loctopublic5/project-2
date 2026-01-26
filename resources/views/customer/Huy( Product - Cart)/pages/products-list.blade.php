@extends('layouts.app')

@section('title', 'Category | Metronic Shop UI')

@section('meta_tags')
  <meta content="Metronic Shop UI description" name="description">
  <meta content="Metronic Shop UI keywords" name="keywords">
  <meta content="keenthemes" name="author">

  <meta property="og:site_name" content="-CUSTOMER VALUE-">
  <meta property="og:title" content="-CUSTOMER VALUE-">
  <meta property="og:description" content="-CUSTOMER VALUE-">
  <meta property="og:type" content="website">
  <meta property="og:image" content="-CUSTOMER VALUE-">
  <meta property="og:url" content="-CUSTOMER VALUE-">
@endsection

@section('extra_plugins')
  <link rel="shortcut icon" href="favicon.ico">

  <!-- Fonts START -->
  <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|PT+Sans+Narrow|Source+Sans+Pro:200,300,400,600,700,900&amp;subset=all" rel="stylesheet" type="text/css"> 
  <!-- Fonts END -->

  <!-- Global styles START -->          
  <link href="assets/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">
  <link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Global styles END --> 
   
  <!-- Page level plugin styles START -->
  <link href="assets/plugins/fancybox/source/jquery.fancybox.css" rel="stylesheet">
  <link href="assets/plugins/owl.carousel/assets/owl.carousel.css" rel="stylesheet">
  <link href="assets/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css">
  <link href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css"><!-- for slider-range -->
  <link href="assets/plugins/rateit/src/rateit.css" rel="stylesheet" type="text/css">
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
<div class="ecommerce">
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
</div>
    <!-- END BEGIN STYLE CUSTOMIZER --> 

    <div class="title-wrapper">
      <div class="container"><div class="container-inner">
        <h1><span>MEN</span> CATEGORY</h1>
        <em>Over 4000 Items are available here</em>
      </div></div>
    </div>

    <div class="main">
      <div class="container">
        <ul class="breadcrumb">
            <li><a href="index.html">Home</a></li>
            <li><a href="">Store</a></li>
            <li class="active">Men category</li>
        </ul>
        <!-- BEGIN SIDEBAR & CONTENT -->
        <div class="row margin-bottom-40">
          <!-- BEGIN SIDEBAR -->
<div class="sidebar col-md-3 col-sm-5">
    <ul class="list-group margin-bottom-25 sidebar-menu" id="sidebar-categories">
        <li class="list-group-item">Đang tải danh mục...</li>
    </ul>

    <div class="sidebar-products clearfix" id="bestseller-container">
        <h2>Bestsellers</h2>
        </div>
</div>
          <!-- END SIDEBAR -->
          <!-- BEGIN CONTENT -->
          <div class="col-md-9 col-sm-7">
            <div class="row list-view-sorting clearfix">
              <div class="col-md-2 col-sm-2 list-view">
                <a href="javascript:;"><i class="fa fa-th-large"></i></a>
                <a href="javascript:;"><i class="fa fa-th-list"></i></a>
              </div>
              <div class="col-md-10 col-sm-10">
                <div class="pull-right">
                  <label class="control-label">Show:</label>
                  <select class="form-control input-sm">
                    <option value="#?limit=24" selected="selected">24</option>
                    <option value="#?limit=25">25</option>
                    <option value="#?limit=50">50</option>
                    <option value="#?limit=75">75</option>
                    <option value="#?limit=100">100</option>
                  </select>
                </div>
                <div class="pull-right">
                  <label class="control-label">Sort&nbsp;By:</label>
                  <select class="form-control input-sm">
                    <option value="#?sort=p.sort_order&amp;order=ASC" selected="selected">Default</option>
                    <option value="#?sort=pd.name&amp;order=ASC">Name (A - Z)</option>
                    <option value="#?sort=pd.name&amp;order=DESC">Name (Z - A)</option>
                    <option value="#?sort=p.price&amp;order=ASC">Price (Low &gt; High)</option>
                    <option value="#?sort=p.price&amp;order=DESC">Price (High &gt; Low)</option>
                    <option value="#?sort=rating&amp;order=DESC">Rating (Highest)</option>
                    <option value="#?sort=rating&amp;order=ASC">Rating (Lowest)</option>
                    <option value="#?sort=p.model&amp;order=ASC">Model (A - Z)</option>
                    <option value="#?sort=p.model&amp;order=DESC">Model (Z - A)</option>
                  </select>
                </div>
              </div>
            </div>
            <!-- BEGIN PRODUCT LIST -->

<div class="row product-list" id="real-product-container">
    <!-- JS sẽ render sản phẩm ở đây --> 
</div>
<div class="row">
    <div class="col-md-4 col-sm-4 items-info" id="pagination-info" style="padding-top: 15px;">
        </div>
    <div class="col-md-8 col-sm-8">
        <ul class="pagination pull-right" id="product-pagination">
            </ul>
    </div>
</div>
<style>
    /* 1. Thiết lập lưới 3 cột chuẩn */
    #real-product-container {
        display: block !important;
        clear: both;
    }

    #real-product-container .col-md-4 {
        width: 33.333% !important;
        float: left !important;
        padding: 10px !important;
        margin-bottom: 20px;
    }

    /* 2. ÉP CHIỀU CAO TỔNG THỂ CỦA CARD SẢN PHẨM */
    .product-item {
        padding: 10px;
        background: #fff;
        border: 1px solid #eee;
        /* Ép tất cả các khung sản phẩm cao bằng nhau (500px) */
        height: 440px !important; 
        position: relative;
        display: flex;
        flex-direction: column;
    }

    /* 3. KHUNG ẢNH: Đã fix đẹp */
    .product-item .pi-img-wrapper {
        height: 320px !important;
        width: 100%;
        overflow: hidden;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f9f9f9;
    }

    .product-item .pi-img-wrapper img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important; /* Lấp đầy khung không để khoảng trắng */
    }

    /* 4. TÊN SẢN PHẨM: Ép chiều cao cố định cho 2 dòng */
    .product-item h3 {
        font-size: 15px !important;
        height: 44px !important; /* Đủ cho 2 dòng chữ */
        margin-bottom: 10px !important;
        overflow: hidden;
        line-height: 22px !important;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    /* 5. PHẦN GIÁ VÀ NÚT BẤM: Đẩy xuống đáy khung */
    .product-item .pi-price {
        color: #e84d1c;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 15px !important;
    }


    /* Ép nút Add to Cart nằm ở hàng cuối cùng của khung 500px */
    .product-item .btn-addcart {
        margin-top: auto; /* Tự động đẩy xuống dưới cùng */
        align-self: flex-start;
        padding: 6px 15px;
        text-transform: uppercase;
    }

    /* 6. Fix lỗi float sau mỗi 3 ảnh */
    #real-product-container .col-md-4:nth-child(3n+1) {
        clear: both !important;
    }
    
</style>

<!-- END PRODUCT LIST -->


    <!-- BEGIN fast view of a product -->
    <div id="product-pop-up" style="display: none; width: 700px;">
            <div class="product-page product-pop-up"style="min-height: auto; padding: 15px;">
              <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-3">
                  <div class="product-main-image">
                    <img src="assets/pages/img/products/model7.jpg" 
                         style="width: 100%; height: 420px; object-fit: cover; object-position: top; ">
                  </div>
                  <div class="product-other-images">
    <a href="javascript:;" class="active change-main-image" data-image="assets/pages/img/products/model3.jpg">
        <img alt="Thumb" src="assets/pages/img/products/model3.jpg">
    </a>
    <a href="javascript:;" class="change-main-image" data-image="assets/pages/img/products/model4.jpg">
        <img alt="Thumb" src="assets/pages/img/products/model4.jpg">
    </a>
    <a href="javascript:;" class="change-main-image" data-image="assets/pages/img/products/model5.jpg">
        <img alt="Thumb" src="assets/pages/img/products/model5.jpg">
    </a>
</div>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-9">
                  <h1>Cool green dress with red bell</h1>
                  <div class="price-availability-block clearfix">
                    <div class="price">
                      <strong><span>$</span>47.00</strong>
                      <em>$<span>62.00</span></em>
                    </div>
                    <div class="availability">
                      Availability: <strong>In Stock</strong>
                    </div>
                  </div>
                  <div class="description">
                    <p>Lorem ipsum dolor ut sit ame dolore  adipiscing elit, sed nonumy nibh sed euismod laoreet dolore magna aliquarm erat volutpat 
Nostrud duis molestie at dolore.</p>
                  </div>
                  <div class="product-page-options">
                    <div class="pull-left">
                      <label class="control-label">Size:</label>
                      <select class="form-control input-sm">
                        <option>L</option>
                        <option>M</option>
                        <option>XL</option>
                      </select>
                    </div>
                    <div class="pull-left">
                      <label class="control-label">Color:</label>
                      <select class="form-control input-sm">
                        <option>Red</option>
                        <option>Blue</option>
                        <option>Black</option>
                      </select>
                    </div>
                  </div>
                  <div class="product-page-cart">
                    <div class="product-quantity">
                        <input id="product-quantity" type="text" value="1" readonly name="product-quantity" class="form-control input-sm">
                    </div>
                    <button class="btn btn-primary" type="submit">Add to cart</button>
                  </div>
                </div>

                <div class="sticker sticker-sale"></div>
              </div>
            </div>
    </div>
    </div>
   </div>
   </div> 
   </div>

    <!-- END fast view of a product -->
@endsection

@push('extra_scripts') 

    <!-- BEGIN PAGE LEVEL JAVASCRIPTS (REQUIRED ONLY FOR CURRENT PAGE) -->
    <script src="assets/plugins/fancybox/source/jquery.fancybox.pack.js" type="text/javascript"></script><!-- pop up -->
    <script src="assets/plugins/owl.carousel/owl.carousel.min.js" type="text/javascript"></script><!-- slider for products -->
    <script src='assets/plugins/zoom/jquery.zoom.min.js' type="text/javascript"></script><!-- product zoom -->
    <script src="assets/plugins/bootstrap-touchspin/bootstrap.touchspin.js" type="text/javascript"></script><!-- Quantity -->
    <script src="assets/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
    <script src="assets/plugins/rateit/src/jquery.rateit.js" type="text/javascript"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js" type="text/javascript"></script> <!-- for slider-range -->


    <script src="{{ asset('assets/js/pages/Huy/products-list.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    jQuery(document).ready(function() {
        // 1. Khởi tạo layout chung
        if (typeof Layout !== 'undefined') {
            Layout.init();
            Layout.initUniform();
        }


        if (typeof ProductList !== 'undefined') { 

            window.productApp = new ProductList(); 
            window.productApp.init().catch(err => console.error("Lỗi init:", err));
        } 
    });
</script>
    <!-- END PAGE LEVEL JAVASCRIPTS -->
@endpush