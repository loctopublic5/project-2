const QuickDeposit = {
    amount: 50000,

    init: function() {
        console.log("QuickDeposit Module Starting...");
        this.bindEvents();

        // 1. Lấy số tiền thiếu từ URL
        const params = new URLSearchParams(window.location.search);
        const urlAmount = parseInt(params.get('amount'));
        
        if (urlAmount && urlAmount > 0) {
            // Làm tròn lên hàng nghìn (ví dụ 10.200 -> 11.000) nếu cần, hoặc để nguyên
            this.amount = urlAmount;
            
            // Nếu số tiền này không có trong danh sách nút chọn sẵn, bơm vào ô input
            const $existBtn = $(`.btn-money[data-amount="${urlAmount}"]`);
            if ($existBtn.length) {
                $('.btn-money').removeClass('active');
                $existBtn.addClass('active');
            } else {
                $('.btn-money').removeClass('active');
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
            title: 'Đang khởi tạo giao dịch...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const res = await window.api.post('/api/v1/customer/wallet/deposit', {
                amount: this.amount,
                payment_method: $('input[name="payment_method"]:checked').val(),
                description: "Nạp tiền nhanh để thanh toán đơn hàng"
            });

            if (res.data.status) {
                // SỰ KIỆN MỚI: Cho người dùng lựa chọn sau khi nạp thành công
                Swal.fire({
                    title: 'Nạp tiền thành công!',
                    text: `Số tiền ${new Intl.NumberFormat('vi-VN').format(this.amount)}đ đã được ghi nhận. Bạn muốn làm gì tiếp theo?`,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#1fb5ad',
                    confirmButtonText: '<i class="fa fa-shopping-cart"></i> Quay lại thanh toán',
                    cancelButtonText: '<i class="fa fa-user"></i> Xem ví của tôi',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Quay lại trang checkout
                        window.location.href = '/checkout';
                    } else {
                        // Về trang profile và mở tab wallet
                        window.location.href = '/profile?tab=wallet';
                    }
                });
            }
        } catch (err) {
            Swal.fire('Lỗi', err.response?.data?.message || 'Không thể nạp tiền', 'error');
        }
    }
};