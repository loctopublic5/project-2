Checkout.Payment = (function () {
    var selectedMethod = 'cod';

    return {
        init: function () {
    // Sử dụng event delegation để bám chắc sự kiện
    $(document).off('change', 'input[name="payment_method"]').on('change', 'input[name="payment_method"]', (e) => {
        const method = $(e.target).val();
        if (method === 'wallet') {
            this.validateWalletPayment();
        } else {
            $('#wallet-info-container').slideUp();
            Checkout.data.wallet_valid = true; // COD luôn coi là valid để pass qua check
        }
    });

    // Sự kiện cho nút confirm của Step 3
    $(document).off('click', '#btn-confirm-payment').on('click', '#btn-confirm-payment', () => {
        this.confirmPayment();
    });
},

    validateWalletPayment: async function () {
    const $content = $('#wallet-status-content');
    const $container = $('#wallet-info-container');
    const MAX_SHIPPING_FEE = 35000; // Phí ship dự phòng tối đa
    
    $container.slideDown();
    $content.html('<i class="fa fa-spinner fa-spin"></i> Đang kiểm tra ví...');

    try {
        const [cartRes, walletRes] = await Promise.all([
            window.api.get('/api/v1/customer/cart'),
            window.api.get('/api/v1/customer/wallet')
        ]);

        // 1. KIỂM TRA LỖI TỪ BACKEND (Trường hợp chưa kích hoạt ví)
        if (walletRes.data.status === false) {
            Checkout.data.wallet_valid = false;
            
            let html = `
                <div class="alert alert-warning" style="margin-bottom: 0; border-left: 4px solid #f39c12;">
                    <h4 style="margin-top: 0; color: #e67e22;"><i class="fa fa-info-circle"></i> Thông báo kích hoạt</h4>
                    <p>${walletRes.data.message}</p>
                    <div class="margin-top-10">
                        <a href="/customer/wallet/activate" class="btn btn-sm btn-warning">
                            <i class="fa fa-magic"></i> Kích hoạt ví ngay
                        </a>
                    </div>
                </div>`;
            
            $content.html(html);
            return; // Dừng xử lý các bước dưới
        }

        // 2. NẾU VÍ ĐÃ KÍCH HOẠT (Logic cũ)
        const cartTotal = cartRes.data.data.summary.final_total;
        const balance = walletRes.data.data.balance;
        
        // GIÁ TRỊ ƯỚC TÍNH (An toàn): Tổng giỏ hàng + phí ship cao nhất
        const estimatedTotal = cartTotal + MAX_SHIPPING_FEE;
        
        Checkout.data.total_amount = cartTotal; // Lưu giá gốc để hiển thị tạm

        let html = `<p>Số dư ví hiện tại: <strong>${Checkout.formatPrice(balance)}</strong></p>`;

        if (balance < estimatedTotal) {
            // Tính số tiền cần nạp = (Giá ước tính - Số dư hiện có)
            const shortage = estimatedTotal - balance;
            
            // Làm tròn lên hàng nghìn cho đẹp
            const suggestedDeposit = Math.ceil(shortage / 1000) * 1000;

            html += `
                <div class="alert alert-warning margin-top-10" style="border-left: 4px solid #d9534f;">
                    <i class="fa fa-info-circle"></i> Số dư ví hiện không đủ để thanh toán (bao gồm cả phí vận chuyển dự tính).
                    <br>Bạn cần nạp thêm khoảng <strong>${Checkout.formatPrice(suggestedDeposit)}</strong>.
                    <br><a href="/deposit?amount=${suggestedDeposit}" class="btn btn-xs btn-danger margin-top-10">
                        Nạp tiền ngay
                    </a>
                </div>`;
            Checkout.data.wallet_valid = false;
        } else {
            html += `<p class="text-success"><i class="fa fa-check"></i> Đủ điều kiện thanh toán (đã bao gồm dự phòng phí ship).</p>`;
            Checkout.data.wallet_valid = true;
        }

        $content.html(html);

    } catch (error) {
        console.error("Lỗi API Ví:", error);
        $content.html('<div class="alert alert-danger">Không thể kiểm tra số dư ví.</div>');
    }
},

        confirmPayment: function () {
            // Kiểm tra điều khoản
            if (!$('#agree-terms').is(':checked')) {
                return Swal.fire('Chú ý', 'Vui lòng đồng ý với điều khoản dịch vụ.', 'warning');
            }
            const selectedMethod = $('input[name="payment_method"]:checked').val();

            // Kiểm tra nếu dùng ví mà không đủ tiền
            if (selectedMethod === 'wallet' && !Checkout.data.wallet_valid) {
        return Swal.fire({
            icon: 'error',
            title: 'Chưa thể thanh toán',
            text: 'Vui lòng kích hoạt ví hoặc nạp thêm tiền trước khi tiếp tục.',
            confirmButtonText: 'Đã hiểu'
        });
    }

            // Lưu thông tin và chuyển sang Step Review
            Checkout.data.payment_method = selectedMethod;
            Checkout.markStepComplete('#payment-method-content');
            Checkout.goToStep('#confirm-content');
            
            if (Checkout.OrderReview) Checkout.OrderReview.initReview();
        }
    };
})();