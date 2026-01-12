<!-- BEGIN TOP BAR -->
<div class="pre-header">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-sm-6 additional-shop-info">
                <ul class="list-unstyled list-inline">
                    <li><i class="fa fa-phone"></i><span>+1 456 6717</span></li>
                    <li class="shop-currencies">
                        <a href="javascript:;">€</a>
                        <a href="javascript:;">£</a>
                        <a href="javascript:;" class="current">$</a>
                    </li>
                    <li class="langs-block">
                        <a href="javascript:;" class="current">English</a>
                    </li>
                </ul>
            </div>

            <div class="col-md-6 col-sm-6 additional-nav">
                <ul class="list-unstyled list-inline pull-right">
                    <li><a href="{{ route('profile') }}">My Account</a></li>
                    <li><a href="#">My Wishlist</a></li>
                    <li><a href="#">Checkout</a></li>
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
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('products') }}">Products</a></li>
                <li><a href="#">Kids</a></li>

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:;">Pages</a>
                    <ul class="dropdown-menu">
                        <li class="active"><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="{{ route('products') }}">Product List</a></li>
                        <li><a href="#">Checkout</a></li>
                        <li><a href="{{ route('login') }}">Login</a></li>
                    </ul>
                </li>

                <li class="menu-search">
                    <span class="sep"></span>
                    <i class="fa fa-search search-btn"></i>
                    <div class="search-box">
                        <form>
                            <div class="input-group">
                                <input type="text" placeholder="Search" class="form-control">
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="submit">Search</button>
                                </span>
                            </div>
                        </form>
                    </div>
                </li>
            </ul>
        </div>
        <!-- END NAVIGATION -->
    </div>
</div>
<!-- Header END -->
