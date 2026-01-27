const QuickDeposit = {
    amount: 50000,

    init: function() {
        console.log("QuickDeposit Module Starting...");
        this.bindEvents();

        // Xử lý số tiền từ URL nếu có
        const params = new URLSearchParams(window.location.search);
        const urlAmount = parseInt(params.get('amount'));
        if (urlAmount && urlAmount >= 10000) {
            this.amount = urlAmount;
            // Nếu là số khác không nằm trong list button
            if (!$(`.btn-money[data-amount="${urlAmount}"]`).length) {
                $('#input-custom-amount').val(urlAmount);
            }
        }

        this.updateUI();
    },

    bindEvents: function() {
        const self = this;
        const $body = $('body');

        // 1. Lắng nghe click chọn số tiền (Dùng class .btn-money)
        $body.off('click', '.btn-money').on('click', '.btn-money', function(e) {
            e.preventDefault();
            console.log("Btn money clicked:", $(this).data('amount'));
            
            $('.btn-money').removeClass('active');
            $(this).addClass('active');
            $('#input-custom-amount').val(''); // Xóa ô nhập tay
            
            self.amount = parseInt($(this).data('amount'));
            self.updateUI();
        });

        // 2. Lắng nghe nhập tiền tay (Dùng ID #input-custom-amount)
        $body.off('input', '#input-custom-amount').on('input', '#input-custom-amount', function() {
            console.log("Custom amount inputting...");
            $('.btn-money').removeClass('active');
            self.amount = parseInt($(this).val()) || 0;
            self.updateUI();
        });

        // 3. Lắng nghe nút xác nhận (Dùng ID #btn-submit-deposit)
        $body.off('click', '#btn-submit-deposit').on('click', '#btn-submit-deposit', function(e) {
            e.preventDefault();
            console.log("Submit button clicked, current amount:", self.amount);
            self.handleDeposit();
        });
    },

    updateUI: function() {
        const formatted = new Intl.NumberFormat('vi-VN').format(this.amount) + 'đ';
        // Cập nhật vào ID #final-amount
        $('#final-amount').text(formatted);
        
        // Đồng bộ trạng thái active của nút
        $('.btn-money').removeClass('active');
        $(`.btn-money[data-amount="${this.amount}"]`).addClass('active');
    },

    handleDeposit: async function() {
        if (this.amount < 10000) {
            return Swal.fire('Thông báo', 'Số tiền nạp tối thiểu là 10.000đ', 'warning');
        }

        Swal.fire({
            title: 'Đang kết nối ngân hàng...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            // Sử dụng window.api (instance axios của bạn)
            const res = await window.api.post('/api/v1/customer/wallet/deposit', {
                amount: this.amount,
                payment_method: $('input[name="payment_method"]:checked').val(),
                description: "Nạp tiền nhanh đơn hàng"
            });

            if (res.data.status) {
                Swal.fire('Thành công', 'Hệ thống đang xử lý giao dịch của bạn', 'success')
                    .then(() => window.location.href = '/profile?tab=wallet');
            }
        } catch (err) {
            console.error("Deposit Error:", err);
            Swal.fire('Lỗi', err.response?.data?.message || 'Không thể tạo yêu cầu nạp tiền', 'error');
        }
    }
};