const WalletModule = {
    CONFIG: {
        MIN_DEPOSIT: 10000,
        MAX_DEPOSIT: 50000000, // 50 triệu đồng cho 1 lần nạp
    },

    init: function() {
        this.loadWalletInfo();
        // Bạn có thể đăng ký sự kiện click tại đây nếu không muốn dùng onclick trong HTML
    },

    initEventListeners: function() {
        // Lắng nghe sự kiện nhập liệu để valid real-time
        const amountInput = document.getElementById('deposit-amount');
        if (amountInput) {
            amountInput.addEventListener('input', (e) => {
                // Chỉ cho phép nhập số
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                
                const val = parseInt(e.target.value) || 0;
                if (val > this.CONFIG.MAX_DEPOSIT) {
                    e.target.value = this.CONFIG.MAX_DEPOSIT;
                    this.updateDisplayTotal(this.CONFIG.MAX_DEPOSIT);
                } else {
                    this.updateDisplayTotal(val);
                }
            });
        }
    },

    loadWalletInfo: async function() {
        try {
            const res = await window.api.get('/api/v1/customer/wallet');
            const { balance, history } = res.data.data;

            // Cập nhật số dư ở cả dashboard và tab ví bằng AppHelpers
            const fmtBalance = AppHelpers.formatCurrency(balance);
            if(document.getElementById('db-balance')) 
                document.getElementById('db-balance').innerText = fmtBalance;
            if(document.getElementById('wallet-balance-big')) 
                document.getElementById('wallet-balance-big').innerText = fmtBalance;

            // Render bảng lịch sử
            const historyTbody = document.getElementById('wallet-history-table');
            if (!historyTbody) return;

            if (!history.data || history.data.length === 0) {
                historyTbody.innerHTML = '<tr><td colspan="5" class="text-center">Chưa có giao dịch nào.</td></tr>';
                return;
            }

            historyTbody.innerHTML = history.data.map(trans => `
                <tr>
                    <td><small class="text-muted">${trans.code || trans.id}</small></td>
                    <td>${trans.description}</td>
                    <td style="color: ${trans.amount > 0 ? '#2ecc71' : '#e74c3c'}; font-weight: bold;">
                        ${trans.amount_fmt}
                    </td>
                    <td><small>${trans.created_at}</small></td>
                    <td><span class="status-badge badge-${trans.status}">${trans.status_label}</span></td>
                </tr>
            `).join('');

        } catch (err) {
            console.error("Wallet Load Error:", err);
        }
    },

    quickAmount: function(amount) {
        const input = document.getElementById('deposit-amount');
        if (input) input.value = amount;
    },

    handleDeposit: async function() {
        const amountInput = document.getElementById('deposit-amount');
        const amount = parseInt(amountInput.value);
        
        // 1. Kiểm tra giới hạn TỐI THIỂU
        if (isNaN(amount) || amount < this.CONFIG.MIN_DEPOSIT) {
            return Swal.fire({
                icon: 'warning',
                title: 'Số tiền quá nhỏ',
                text: `Số tiền nạp tối thiểu là ${AppHelpers.formatCurrency(this.CONFIG.MIN_DEPOSIT)}`,
                confirmButtonColor: '#E02222'
            });
        }

        // 2. Kiểm tra giới hạn TỐI ĐA (Quan trọng để bảo vệ Server)
        if (amount > this.CONFIG.MAX_DEPOSIT) {
            return Swal.fire({
                icon: 'error',
                title: 'Vượt hạn mức',
                text: `Mỗi giao dịch nạp tiền không được quá ${AppHelpers.formatCurrency(this.CONFIG.MAX_DEPOSIT)}. Vui lòng chia nhỏ giao dịch hoặc liên hệ hỗ trợ.`,
                confirmButtonColor: '#E02222'
            });
        }

        // 3. Xác nhận nạp tiền
        Swal.fire({
            title: 'Xác nhận nạp tiền?',
            text: `Hệ thống sẽ nạp ${AppHelpers.formatCurrency(amount)} vào ví của bạn.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#E02222',
            cancelButtonColor: '#777',
            confirmButtonText: 'Đồng ý nạp',
            cancelButtonText: 'Hủy',
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                // Hiển thị trạng thái loading
                Swal.fire({
                    title: 'Đang xử lý giao dịch...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                try {
                    const res = await window.api.post('/api/v1/customer/wallet/deposit', { amount });
                    
                    if (res.data.status) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: res.data.message || 'Giao dịch nạp tiền đã được ghi nhận.',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Đóng modal và reset input
                        $('#depositModal').modal('hide');
                        amountInput.value = 50000; 

                        // Tải lại dữ liệu ví
                        this.loadWalletInfo();
                    } else {
                        throw new Error(res.data.message);
                    }
                    
                } catch (err) {
                    // 3. Bắt lỗi từ Server (Ví dụ: Lỗi cổng thanh toán, lỗi hệ thống)
                    console.error("Deposit Error:", err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Giao dịch thất bại',
                        text: err.response?.data?.message || err.message || 'Không thể thực hiện giao dịch lúc này.',
                        confirmButtonColor: '#E02222'
                    });
                }
            }
        });
    },
    selectAmount: function(amount, element) {
        // Xóa class active ở tất cả các ô
        $('.amount-item').removeClass('active');
        // Thêm class active vào ô vừa chọn
        $(element).addClass('active');
        // Gán giá trị vào input ẩn
        document.getElementById('deposit-amount').value = amount;
        // Cập nhật text hiển thị tổng nạp
        this.updateDisplayTotal(amount);
    },

    clearSelection: function() {
        // Khi người dùng tự nhập số, bỏ chọn các ô cố định
        $('.amount-item').removeClass('active');
        $('.custom-input').addClass('active');
        const val = document.getElementById('deposit-amount').value;
        this.updateDisplayTotal(val || 0);
    },

    updateDisplayTotal: function(amount) {
        document.getElementById('display-total-amount').innerText = AppHelpers.formatCurrency(amount);
    }
};