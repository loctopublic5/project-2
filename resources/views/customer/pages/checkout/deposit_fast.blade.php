@extends('layouts.app')

@section('extra_plugins')
<style>
    .deposit-container-wrapper {
        background: #f4f6f9;
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .deposit-card {
        background: #fff;
        width: 100%;
        max-width: 500px;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .deposit-header {
        background: #e02222;
        padding: 25px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .icon-box {
        font-size: 30px;
        background: rgba(255,255,255,0.2);
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
    .header-text h3 { margin: 0; font-weight: 700; font-size: 20px; }
    .header-text p { margin: 0; opacity: 0.8; font-size: 13px; }

    .deposit-body { padding: 25px; }
    .section-label { display: block; font-weight: 600; margin-bottom: 15px; color: #333; }

    .money-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    .btn-money {
        border: 1px solid #e1e1e1;
        background: #fff;
        padding: 12px 5px;
        border-radius: 8px;
        font-weight: 600;
        transition: 0.3s;
        cursor: pointer;
    }
    .btn-money.active {
        border-color: #e02222;
        color: #e02222;
        background: #fff5f5;
    }
    .custom-money { grid-column: span 3; }
    .custom-money input {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #e1e1e1;
        text-align: center;
        font-weight: 600;
    }

    .payment-list { display: flex; flex-direction: column; gap: 10px; }
    .payment-option input { display: none; }
    .payment-box {
        border: 1.5px solid #e1e1e1;
        padding: 15px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        position: relative;
    }
    .payment-option input:checked + .payment-box {
        border-color: #e02222;
        background: #fffefb;
    }
    .badge {
        position: absolute;
        right: 10px;
        top: 10px;
        background: #ffc107;
        color: #000;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
    }

    .deposit-footer {
        padding: 20px 25px;
        background: #fafafa;
        border-top: 1px solid #eee;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .summary { display: flex; justify-content: space-between; align-items: center; }
    .summary strong { font-size: 24px; color: #e02222; }
    .btn-confirm {
        background: #e02222;
        color: #fff;
        border: none;
        padding: 15px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        width: 100%;
    }
    .btn-confirm:hover { background: #c11d1d; }
</style>
@endsection

@section('content')
<div class="deposit-container-wrapper">
    <div class="deposit-card">
        <div class="deposit-header">
            <div class="icon-box">
                <i class="fa fa-shield"></i>
            </div>
            <div class="header-text">
                <h3>Nạp Tiền Vào Ví</h3>
                <p>An toàn - Bảo mật - Tự động 24/7</p>
            </div>
        </div>

        <div class="deposit-body">
            <div class="form-group">
                <label class="section-label">1. Số tiền muốn nạp (VNĐ)</label>
                <div class="money-grid">
                    <button type="button" class="btn-money active" data-amount="50000">50,000</button>
                    <button type="button" class="btn-money" data-amount="100000">100,000</button>
                    <button type="button" class="btn-money" data-amount="200000">200,000</button>
                    <button type="button" class="btn-money" data-amount="500000">500,000</button>
                    <button type="button" class="btn-money" data-amount="1000000">1,000,000</button>
                    <div class="custom-money">
                        <input type="number" id="input-custom-amount" placeholder="Số tiền khác...">
                    </div>
                </div>
            </div>

            <div class="form-group mt-4">
                <label class="section-label">2. Hình thức thanh toán</label>
                <div class="payment-list">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="bank" checked>
                        <div class="payment-box">
                            <i class="fa fa-bank"></i>
                            <span>Chuyển khoản Ngân hàng</span>
                            <span class="badge">Khuyên dùng</span>
                        </div>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="momo">
                        <div class="payment-box">
                            <i class="fa fa-qrcode"></i>
                            <span>Ví MoMo / QR Code</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="deposit-footer">
            <div class="summary">
                <span>Tổng nạp:</span>
                <strong id="final-amount">50,000đ</strong>
            </div>
            <button id="btn-submit-deposit" class="btn-confirm">
                XÁC NHẬN THANH TOÁN <i class="fa fa-arrow-right"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@push('extra_scripts')
    <script src="{{ asset('assets/js/pages/Checkout/deposit-fast.js') }}?v={{ time() }}"></script>
    
    <script type="text/javascript">
        jQuery(document).ready(function() {
            // Khởi tạo các layout chung nếu cần (giống trang Account của bạn)
            if (typeof Layout !== 'undefined') {
                Layout.init();
            }
            
            // Khởi tạo module nạp tiền nhanh
            if (typeof QuickDeposit !== 'undefined') {
                QuickDeposit.init();
            }
        });
    </script>
@endpush