<div class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-sm-6 padding-top-10">
                2026 © Retail E-commerce System. Developed by Dev A.
            </div>
            <div class="col-md-6 col-sm-6">
                <ul class="list-unstyled list-inline pull-right">
                    <li><img src="{{ asset('assets/corporate/img/payments/western-union.jpg') }}" alt="We accept Western Union"></li>
                    <li><img src="{{ asset('assets/corporate/img/payments/mastercard.jpg') }}" alt="We accept MasterCard"></li>
                    <li><img src="{{ asset('assets/corporate/img/payments/visa.jpg') }}" alt="We accept Visa"></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/plugins/jquery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/jquery-migrate.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>      
<script src="{{ asset('assets/corporate/scripts/back-to-top.js') }}"></script>
<script src="{{ asset('assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>

<script src="{{ asset('assets/plugins/fancybox/source/jquery.fancybox.pack.js') }}"></script>
<script src="{{ asset('assets/plugins/owl.carousel/owl.carousel.min.js') }}"></script>
<script src="{{ asset('assets/corporate/scripts/layout.js') }}"></script>

<script type="text/javascript">
    jQuery(document).ready(function() {
        Layout.init();    
        Layout.initOWL();
        Layout.initImageZoom(); // Sửa lỗi zoom not a function
        Layout.initTouchspin();
    });
</script>