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
            <li><a href="">Home</a></li>
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
    /* 1. GRID SẢN PHẨM NGOÀI DANH SÁCH */
    #real-product-container { display: flex; flex-wrap: wrap; margin: 0 -10px; }
    .product-grid-item { padding: 10px; width: 33.333%; }
    .product-item {
        padding: 15px; background: #fff; border: 1px solid #eee;
        height: 100%; display: flex; flex-direction: column;
        transition: all 0.3s; position: relative;
    }
    .product-item:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-color: #e84d1c; }
    .pi-img-wrapper { height: 250px; overflow: hidden; position: relative; }
    .pi-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
    .product-item h3 { font-size: 16px; height: 40px; overflow: hidden; margin: 15px 0 10px; }
    .pi-price { color: #e84d1c; font-size: 18px; font-weight: bold; margin-bottom: 10px; }

    .sticker-out-of-stock {
        position: absolute; top: 10px; left: 10px; background: rgba(0, 0, 0, 0.7);
        color: #fff; padding: 4px 10px; font-size: 11px; font-weight: bold;
        text-transform: uppercase; z-index: 2; border-radius: 2px;
    }

    /* 2. PHÓNG TO MODAL & XỬ LÝ TRÀN DATA */
    #product-pop-up {
        padding: 25px; background: #fff; border-radius: 4px;
        max-width: 900px; /* Tăng chiều rộng để data thở */
    }
    
    .product-pop-up .row {
        display: flex; /* Dùng flex để hai cột bằng chiều cao nhau */
        flex-wrap: wrap;
    }

    .product-pop-up .product-main-image {
        border: 1px solid #f4f4f4; margin-bottom: 15px;
        height: 450px; /* Tăng chiều cao ảnh */
        display: flex; align-items: center; justify-content: center;
    }
    .product-pop-up .product-main-image img {
        max-height: 100%; width: auto; object-fit: contain;
    }

    /* Cột chi tiết bên phải */
    .product-details-container {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding-left: 10px;
    }

    #modal-product-name {
        font-size: 26px; font-weight: 700; margin: 0 0 10px 0;
        color: #333; line-height: 1.2;
    }

    .price-availability-block {
        margin: 10px 0; padding: 10px 0;
        border-top: 1px dashed #eee; border-bottom: 1px dashed #eee;
    }
    #modal-product-price { font-size: 28px; color: #e84d1c; font-weight: bold; }
    #modal-product-old-price { text-decoration: line-through; color: #999; margin-left: 10px; font-size: 16px; }

    /* Xử lý phần mô tả không bị dính thanh cuộn */
    #modal-product-desc {
        margin: 10px 0; line-height: 1.5; color: #666; font-size: 14px;
        /* Không set max-height cố định để nó tự giãn theo chữ */
    }

    .product-page-options-wrapper { margin-top: 10px; }
    .option-row { margin-bottom: 15px; }
    .option-row label {
        display: block; font-weight: bold; font-size: 12px;
        color: #333; margin-bottom: 5px; text-transform: uppercase;
    }
    .option-row select { width: 100%; height: 38px; border: 1px solid #ccc; }

    /* Khu vực nút bấm nằm dưới cùng */
    .product-page-cart {
        margin-top: auto; /* Đẩy xuống đáy cột */
        padding-top: 20px; border-top: 1px solid #f4f4f4;
        display: flex; align-items: flex-end; gap: 15px;
    }
    .qty-wrapper { width: 120px; }
    .qty-wrapper label { font-size: 11px; font-weight: bold; margin-bottom: 5px; display: block; }

    .btn-add-cart-lg {
        flex: 1; height: 42px; background: #e84d1c !important;
        color: #fff; border: none; font-weight: bold; text-transform: uppercase;
    }
    .btn-add-cart-lg:hover { background: #c13b13 !important; }

    /* Gallery Thumbs */
    #modal-product-gallery { display: flex; gap: 8px; flex-wrap: wrap; }
    .thumb-item {
        width: 60px; height: 60px; border: 1px solid #ddd;
        cursor: pointer; padding: 2px;
    }
    .thumb-item img { width: 100%; height: 100%; object-fit: cover; }
    .thumb-item.active { border-color: #e84d1c; }

    /* Fancybox Fix */
    .fancybox-skin { padding: 0 !important; }

    /* Tùy chỉnh thanh cuộn cho danh sách review */
#modal-reviews-list::-webkit-scrollbar {
    width: 5px;
}
#modal-reviews-list::-webkit-scrollbar-thumb {
    background: #eee;
    border-radius: 10px;
}
#modal-reviews-list::-webkit-scrollbar-thumb:hover {
    background: #ddd;
}

.review-item:last-child {
    border-bottom: none !important;
}

/* Sticker và giao diện chung của Siêu Modal */
.product-reviews-section {
    background: #fcfcfc;
    padding: 15px;
    border-radius: 4px;
    margin-top: 25px;
}
</style>

<!-- END PRODUCT LIST -->


    <!-- BEGIN fast view of a product -->
<div id="product-pop-up" style="display: none;">
    <div class="product-page product-pop-up">
        <div class="row">
            <div class="col-md-6 col-sm-6">
                <div class="product-main-image">
                    <img id="modal-product-image" src="" class="img-responsive">
                </div>
                <div id="modal-product-gallery"></div>
            </div>
            
            <div class="col-md-6 col-sm-6">
                <div class="product-details-container">
                    <h1 id="modal-product-name"></h1>
                    
                    <div class="price-availability-block clearfix">
                        <div class="price">
                            <strong id="modal-product-price"></strong>
                            <em id="modal-product-old-price" style="display:none;"></em>
                        </div>
                        <div class="availability">
                            Trạng thái: <strong id="modal-product-status"></strong>
                        </div>
                    </div>

                    <div id="modal-product-desc"></div>

                    <div class="product-page-options-wrapper">
                        <div id="modal-product-attributes"></div>
                    </div>

<div class="product-page-cart">
    <div class="qty-wrapper">
        <label>Số lượng</label>
        <div class="product-quantity">
            <input id="modal-product-quantity" 
                    type="number" 
                    value="1" 
                    min="1" 
                    max="10" 
                    class="form-control input-sm">
        </div>
    </div>
                        <button class="btn btn-primary btn-add-cart-lg" id="btn-modal-add-to-cart">
                            <i class="fa fa-shopping-cart"></i> Add To Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="product-reviews-section" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
    <h4 class="bold" style="text-transform: uppercase; font-size: 14px; margin-bottom: 20px;">
        Đánh giá từ khách hàng (<span id="modal-review-count">0</span>)
    </h4>
    <div id="modal-reviews-list" style="max-height: 300px; overflow-y: auto;">
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
        // Kiểm tra dứt điểm để không bao giờ khởi tạo 2 lần
        if (typeof window.App === 'undefined') {
            window.App = new ProductList();
            window.App.init();
        }
    });
</script>
@endpush