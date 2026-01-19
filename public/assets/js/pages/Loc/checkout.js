var Checkout = function () {

    return {
        init: function () {
            
            $('#checkout').on('change', '#checkout-content input[name="account"]', function() {

              var title = '';

              if ($(this).attr('value') == 'register') {
                title = 'Step 2: Account &amp; Billing Details';
              } else {
                title = 'Step 2: Billing Details';
              }    

              $('#payment-address .accordion-toggle').html(title);
            });

        }
    };

}();
//----------------------------------------- Code của minh-----------------------------------

/**
 * File: public/assets/js/checkout.js
 * Nhiệm vụ: Xử lý Step 1 dựa trên Laravel AuthResource & LoginRequest
 */
var Checkout = function () {
    return {
        init: function () {
            // 1. Kiểm tra trạng thái đăng nhập ngay lập tức
            this.checkAuth(); 
            
            // 2. Khởi tạo các listener cho form
            this.handleLogin();
            this.handleRegister();
        },

        // --- LOGIC KIỂM TRA TOKEN TỰ ĐỘNG ---
        checkAuth: async function () {
            const token = localStorage.getItem('admin_token');
            
            if (token && window.api) {
                console.log("Phát hiện token, đang kiểm tra quyền truy cập...");
                try {
                    // Gọi một API bất kỳ yêu cầu auth để verify token
                    // Ở đây tôi dùng API lấy thông tin giỏ hàng vì nó nhẹ và thực tế
                    await window.api.get('/api/v1/customer/cart');
                    
                    console.log("Token hợp lệ. Tự động chuyển đến Step 3.");
                    this.skipToStep3(true); // Truyền true để đánh dấu là auto-pass
                } catch (error) {
                    console.warn("Token không hợp lệ hoặc hết hạn. Yêu cầu đăng nhập lại.");
                    localStorage.removeItem('admin_token');
                    localStorage.removeItem('admin_user');
                }
            }
        },

        // --- XỬ LÝ ĐĂNG NHẬP (STEP 1) ---
        handleLogin: function () {
            const _this = this;
            $('#form-login-checkout').on('submit', async function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                const $btn = $('#btn-login-checkout');
                const data = {
                    email: $('#email-login').val().trim(),
                    password: $('#password-login').val()
                };

                $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

                try {
                    const response = await window.api.post('/api/v1/auth/login', data);
                    const result = response.data.data;
                    
                    localStorage.setItem('admin_token', result.authorization.token);
                    localStorage.setItem('admin_user', JSON.stringify(result.user_info));

                    await Swal.fire({ 
                        icon: 'success', 
                        title: 'Chào mừng ' + result.user_info.name, 
                        timer: 1000, 
                        showConfirmButton: false 
                    });

                    _this.skipToStep3();
                } catch (error) {
                    _this.handleAjaxError(error);
                } finally {
                    $btn.html('Đăng nhập').prop('disabled', false);
                }
            });
        },

        // --- XỬ LÝ ĐĂNG KÝ (STEP 2) ---
        handleRegister: function () {
            const _this = this;
            $('#form-register-checkout').on('submit', async function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                const $btn = $('#btn-register-checkout');
                const data = {
                    full_name: ($('#firstname').val() + ' ' + $('#lastname').val()).trim(),
                    email: $('#email-reg').val().trim(),
                    phone: $('#telephone').val().trim(),
                    password: $('#password-reg').val(),
                    password_confirmation: $('#password-confirm').val()
                };

                $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

                try {
                    const response = await window.api.post('/api/v1/auth/register', data);
                    const result = response.data.data;

                    localStorage.setItem('admin_token', result.authorization.token);
                    localStorage.setItem('admin_user', JSON.stringify(result.user_info));

                    await Swal.fire({ icon: 'success', title: 'Đăng ký thành công!', timer: 1000, showConfirmButton: false });

                    _this.skipToStep3();
                } catch (error) {
                    _this.handleAjaxError(error);
                } finally {
                    $btn.html('Continue').prop('disabled', false);
                }
            });
        },

        proceedToStep2: function () {
            $('#checkout-content').collapse('hide');
            $('#payment-address-content').collapse('show');
        },

        // Nhảy thẳng tới Step 3 (Shipping Address)
        skipToStep3: function (isAuto = false) {
            // Đóng 2 step đầu
            $('#checkout-content').collapse('hide');
            $('#payment-address-content').collapse('hide');
            
            // Mở Step 3
            $('#shipping-address-content').collapse('show');
            
            // Decorate: Thêm icon check hoàn thành cho các bước trước
            // Xóa icon cũ nếu có để tránh trùng lặp
            $('a[href="#checkout-content"] .fa-check, a[href="#payment-address-content"] .fa-check').remove();
            
            $('a[href="#checkout-content"]').append(' <i class="fa fa-check text-success"></i>');
            $('a[href="#payment-address-content"]').append(' <i class="fa fa-check text-success"></i>');

            if (isAuto) {
                console.log("Hệ thống đã tự động hoàn tất xác thực.");
            }
        },

        handleAjaxError: function(error) {
            let msg = "Đã có lỗi xảy ra.";
            if (error.response && error.response.status === 422) {
                msg = Object.values(error.response.data.errors)[0][0];
            } else if (error.response && error.response.data.message) {
                msg = error.response.data.message;
            }
            Swal.fire({ icon: 'error', title: 'Lỗi', text: msg });
        }
    };
}();