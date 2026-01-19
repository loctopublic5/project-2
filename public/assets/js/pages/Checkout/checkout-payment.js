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
        const totalAmount = cartRes.data.data.summary.final_total;
        const balance = walletRes.data.data.balance;
        
        Checkout.data.total_amount = totalAmount;

        let html = `<p>Số dư ví: <strong>${Checkout.formatPrice(balance)}</strong></p>`;

        if (balance < totalAmount) {
            const shortage = totalAmount - balance;
            html += `
                <div class="alert alert-danger margin-top-10">
                    <i class="fa fa-exclamation-triangle"></i> Thiếu <strong>${Checkout.formatPrice(shortage)}</strong>.
                    <br><a href="/customer/wallet/recharge" class="btn btn-xs btn-danger margin-top-10">Nạp tiền & Tiếp tục</a>
                </div>`;
            Checkout.data.wallet_valid = false;
        } else {
            html += `<p class="text-success"><i class="fa fa-check"></i> Đủ điều kiện thanh toán.</p>`;
            Checkout.data.wallet_valid = true;
        }

        $content.html(html);

    } catch (error) {
        // Bắt lỗi hệ thống (404, 500, v.v.)
        console.error("Lỗi API Ví:", error);
        $content.html('<div class="alert alert-danger">Không thể kết nối hệ thống ví.</div>');
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