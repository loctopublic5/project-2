const QuickDeposit = {
    amount: 50000,
    // Cấu hình hạn mức đồng bộ với hệ thống
    CONFIG: {
        MIN: 10000,
        MAX: 50000000,
    },

    init: function() {
        console.log("QuickDeposit Module Starting...");
        this.bindEvents();

        const params = new URLSearchParams(window.location.search);
        let urlAmount = parseInt(params.get('amount'));
        
        if (urlAmount && urlAmount > 0) {
            // Kiểm tra giới hạn ngay cả với số tiền truyền từ URL (ví dụ từ trang thanh toán sang)
            if (urlAmount > this.CONFIG.MAX) urlAmount = this.CONFIG.MAX;
            
            this.amount = urlAmount;
            
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

        // 1. Click chọn nút số tiền cố định
        $body.off('click', '.btn-money').on('click', '.btn-money', function(e) {
            e.preventDefault();
            $('.btn-money').removeClass('active');
            $(this).addClass('active');
            $('#input-custom-amount').val(''); 
            
            self.amount = parseInt($(this).data('amount'));
            self.updateUI();
        });

        // 2. Nhập số tiền tay - Bổ sung logic chặn ký tự và giới hạn Max real-time
        $body.off('input', '#input-custom-amount').on('input', '#input-custom-amount', function() {
            // Chỉ cho phép nhập số
            let valStr = $(this).val().replace(/[^0-9]/g, '');
            let val = parseInt(valStr) || 0;

            if (val > self.CONFIG.MAX) {
                val = self.CONFIG.MAX;
                $(this).val(val);
                // Thông báo nhẹ bằng Toast nếu nhập quá 50tr
                console.warn("Đã đạt hạn mức nạp tối đa");
            } else {
                $(this).val(valStr);
            }

            $('.btn-money').removeClass('active');
            self.amount = val;
            self.updateUI();
        });

        // 3. Nút xác nhận
        $body.off('click', '#btn-submit-deposit').on('click', '#btn-submit-deposit', function(e) {
            e.preventDefault();
            self.handleDeposit();
        });
    },

    updateUI: function() {
        const formatted = new Intl.NumberFormat('vi-VN').format(this.amount) + 'đ';
        $('#final-amount').text(formatted);
        
        // Cảnh báo nếu số tiền đang nhỏ hơn mức tối thiểu (để user biết trước khi bấm nút)
        if (this.amount > 0 && this.amount < this.CONFIG.MIN) {
            $('#final-amount').css('color', '#e74c3c');
        } else {
            $('#final-amount').css('color', '#1fb5ad');
        }
    },

    handleDeposit: async function() {
        // Kiểm tra Tối thiểu
        if (this.amount < this.CONFIG.MIN) {
            return Swal.fire({
                title: 'Số tiền không đủ',
                text: `Số tiền nạp tối thiểu là ${new Intl.NumberFormat('vi-VN').format(this.CONFIG.MIN)}đ`,
                icon: 'warning',
                confirmButtonColor: '#E02222'
            });
        }

        // Kiểm tra Tối đa
        if (this.amount > this.CONFIG.MAX) {
            return Swal.fire({
                title: 'Vượt quá hạn mức',
                text: `Bạn không thể nạp quá ${new Intl.NumberFormat('vi-VN').format(this.CONFIG.MAX)}đ mỗi lần.`,
                icon: 'error',
                confirmButtonColor: '#E02222'
            });
        }

        Swal.fire({
            title: 'Đang khởi tạo giao dịch...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const res = await window.api.post('/api/v1/customer/wallet/deposit', {
                amount: this.amount,
                payment_method: $('input[name="payment_method"]:checked').val() || 'manual',
                description: "Nạp tiền nhanh để thanh toán đơn hàng"
            });

            if (res.data.status) {
                Swal.fire({
                    title: 'Nạp tiền thành công!',
                    text: `Số tiền ${new Intl.NumberFormat('vi-VN').format(this.amount)}đ đã được ghi nhận vào ví.`,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#1fb5ad',
                    confirmButtonText: '<i class="fa fa-shopping-cart"></i> Quay lại thanh toán',
                    cancelButtonText: '<i class="fa fa-user"></i> Xem ví của tôi',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '/checkout';
                    } else {
                        window.location.href = '/profile?tab=wallet';
                    }
                });
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Lỗi', err.response?.data?.message || 'Không thể nạp tiền. Vui lòng thử lại sau.', 'error');
        }
    }
};