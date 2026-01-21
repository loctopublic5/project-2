<!-- BEGIN TOP BAR -->
<div class="pre-header">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-sm-6 additional-shop-info">
                <ul class="list-unstyled list-inline">
                    <li><i class="fa fa-phone"></i><span>0987123666</span></li>
                    <li class="shop-currencies">
                        
                        <a href="javascript:;" class="current">VND</a>
                    </li>
                </ul>
            </div>

            <div class="col-md-6 col-sm-6 additional-nav">
                <ul class="list-unstyled list-inline pull-right">
                    <li><a href="{{ route('profile') }}">My Account</a></li>
                    <li><a href="{{ route('checkout') }}">Checkout</a></li>
                    <li><a href="{{ route('login') }}">Log In</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- END TOP BAR -->

<!-- BEGIN HEADER -->
<div class="header">
    <div class="container">
        <a class="site-logo" href="{{ route('home') }}">
            <img src="{{ asset('assets/corporate/img/logos/logo-shop-red.png') }}" alt="Shop">
        </a>

        <a href="javascript:;" class="mobi-toggler"><i class="fa fa-bars"></i></a>

        <!-- BEGIN CART -->
        <div class="top-cart-block">
            <div class="top-cart-info">
                <a href="javascript:;" class="top-cart-info-count">3 items</a>
                <a href="javascript:;" class="top-cart-info-value">$1260</a>
            </div>
            <i class="fa fa-shopping-cart"></i>

            <div class="top-cart-content-wrapper">
                <div class="top-cart-content">
                    <ul class="scroller" style="height: 250px;">
                        <li>
                            <a href="#"><img src="{{ asset('assets/pages/img/cart-img.jpg') }}" width="37" height="34"></a>
                            <span class="cart-content-count">x 1</span>
                            <strong><a href="#">Rolex Classic Watch</a></strong>
                            <em>$1230</em>
                        </li>
                    </ul>
                    <div class="text-right">
                        <a href="#" class="btn btn-default">View Cart</a>
                        <a href="#" class="btn btn-primary">Checkout</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- END CART -->

        <!-- BEGIN NAVIGATION -->
        <div class="header-navigation">
          <ul>
            <li><a href="/">Trang chủ</a></li>

            <li class="dropdown">
              <a class="dropdown-toggle" data-toggle="dropdown" data-target="#" href="javascript:;">
                Woman 
                
              </a>
                
              <!-- BEGIN DROPDOWN MENU -->
              <ul class="dropdown-menu">
                <li class="dropdown-submenu">
                  <a href="shop-product-list.html">Hi Tops <i class="fa fa-angle-right"></i></a>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="shop-product-list.html">Second Level Link</a></li>
                    <li><a href="shop-product-list.html">Second Level Link</a></li>
                    <li class="dropdown-submenu">
                      <a class="dropdown-toggle" data-toggle="dropdown" data-target="#" href="javascript:;">
                        Second Level Link 
                        <i class="fa fa-angle-right"></i>
                      </a>
                      <ul class="dropdown-menu">
                        <li><a href="shop-product-list.html">Third Level Link</a></li>
                        <li><a href="shop-product-list.html">Third Level Link</a></li>
                        <li><a href="shop-product-list.html">Third Level Link</a></li>
                      </ul>
                    </li>
                  </ul>
                </li>
                <li><a href="shop-product-list.html">Running Shoes</a></li>
                <li><a href="shop-product-list.html">Jackets and Coats</a></li>
              </ul>
              <!-- END DROPDOWN MENU -->
            </li>
            <li class="dropdown dropdown-megamenu">
              <a class="dropdown-toggle" data-toggle="dropdown" data-target="#" href="javascript:;">
                Man
                
              </a>
              <ul class="dropdown-menu">
                <li>
                  <div class="header-navigation-content">
                    <div class="row">
                      <div class="col-md-4 header-navigation-col">
                        <h4>Footwear</h4>
                        <ul>
                          <li><a href="shop-product-list.html">Astro Trainers</a></li>
                          <li><a href="shop-product-list.html">Basketball Shoes</a></li>
                          <li><a href="shop-product-list.html">Boots</a></li>
                          <li><a href="shop-product-list.html">Canvas Shoes</a></li>
                          <li><a href="shop-product-list.html">Football Boots</a></li>
                          <li><a href="shop-product-list.html">Golf Shoes</a></li>
                          <li><a href="shop-product-list.html">Hi Tops</a></li>
                          <li><a href="shop-product-list.html">Indoor and Court Trainers</a></li>
                        </ul>
                      </div>
                      <div class="col-md-4 header-navigation-col">
                        <h4>Clothing</h4>
                        <ul>
                          <li><a href="shop-product-list.html">Base Layer</a></li>
                          <li><a href="shop-product-list.html">Character</a></li>
                          <li><a href="shop-product-list.html">Chinos</a></li>
                          <li><a href="shop-product-list.html">Combats</a></li>
                          <li><a href="shop-product-list.html">Cricket Clothing</a></li>
                          <li><a href="shop-product-list.html">Fleeces</a></li>
                          <li><a href="shop-product-list.html">Gilets</a></li>
                          <li><a href="shop-product-list.html">Golf Tops</a></li>
                        </ul>
                      </div>
                      <div class="col-md-4 header-navigation-col">
                        <h4>Accessories</h4>
                        <ul>
                          <li><a href="shop-product-list.html">Belts</a></li>
                          <li><a href="shop-product-list.html">Caps</a></li>
                          <li><a href="shop-product-list.html">Gloves, Hats and Scarves</a></li>
                        </ul>

                        <h4>Clearance</h4>
                        <ul>
                          <li><a href="shop-product-list.html">Jackets</a></li>
                          <li><a href="shop-product-list.html">Bottoms</a></li>
                        </ul>
                      </div>
                      <div class="col-md-12 nav-brands">
                        <ul>
                          <li><a href="shop-product-list.html"><img title="esprit" alt="esprit" src="assets/pages/img/brands/esprit.jpg"></a></li>
                          <li><a href="shop-product-list.html"><img title="gap" alt="gap" src="assets/pages/img/brands/gap.jpg"></a></li>
                          <li><a href="shop-product-list.html"><img title="next" alt="next" src="assets/pages/img/brands/next.jpg"></a></li>
                          <li><a href="shop-product-list.html"><img title="puma" alt="puma" src="assets/pages/img/brands/puma.jpg"></a></li>
                          <li><a href="shop-product-list.html"><img title="zara" alt="zara" src="assets/pages/img/brands/zara.jpg"></a></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
            </li>
            
            <li class="dropdown dropdown100 nav-catalogue">
      <a class="dropdown-toggle" data-toggle="dropdown" data-target="#" href="javascript:;">New</a>
      <ul class="dropdown-menu">
        <li>
          <div class="header-navigation-content">
            <div class="row" id="js-new-arrivals">
               <div class="col-md-12"><p>Đang tải sản phẩm...</p></div>
            </div>
          </div>
        </li>
      </ul>
    </li>

            <!-- BEGIN TOP SEARCH -->
<li class="menu-search">
    <span class="sep"></span>
    <i class="fa fa-search search-btn"></i>
    <div class="search-box">
        <form id="header-search-form" action="/search" method="GET">
            <div class="input-group search-complex-wrapper">
                
                <select name="category_id" id="search-category-id" class="form-control select-filter">
                    <option value="">Tất cả danh mục</option>
                </select>

                <input type="text" name="keyword" id="search-keyword" placeholder="Bạn tìm gì..." class="form-control input-keyword">

                <select name="sort_by" id="search-sort-by" class="form-control select-sort">
                    <option value="latest">Mới nhất</option>
                    <option value="price_asc">Giá tăng dần</option>
                    <option value="price_desc">Giá giảm dần</option>
                </select>

                <span class="input-group-btn">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa fa-search"></i>
                    </a>
                </span>
            </div>
        </form>
    </div> 
</li>
            <!-- END TOP SEARCH -->
          </ul>
        </div>
        <!-- END NAVIGATION -->
      </div>
    </div>
    <!-- Header END -->
    <style>
/* Container bọc 3 ô */
.search-complex-wrapper {
    display: flex !important;
    width: 550px !important; /* Độ rộng đủ cho 3 thành phần */
    background: #fff;
    border-radius: 4px;
    overflow: hidden;
}

/* Các ô Select và Input */
.select-filter {
    width: 140px !important;
    border: none !important;
    border-right: 1px solid #ddd !important;
    background: #f9f9f9 !important;
    height: 38px !important;
}

.input-keyword {
    flex-grow: 1; /* Ô này sẽ tự giãn ra */
    border: none !important;
    height: 38px !important;
}

.select-sort {
    width: 120px !important;
    border: none !important;
    border-left: 1px solid #ddd !important;
    height: 38px !important;
    color: #666;
    font-size: 12px;
}

/* Nút tìm kiếm */
.search-complex-wrapper .btn-primary {
    height: 38px !important;
    padding: 0 15px !important;
    border-radius: 0 !important;
}

/* Hiệu ứng khi focus */
.search-complex-wrapper select:focus, 
.search-complex-wrapper input:focus {
    outline: none;
    box-shadow: none;
}
</style>
    </div>
</div>
<!-- Header END -->
