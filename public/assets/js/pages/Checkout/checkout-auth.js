Checkout.Auth = (function () {
    return {
        init: function () {
            this.checkAuth();
            this.handleLogin();
            this.handleRegister();

            // Gán sự kiện cho nút "Continue" ở Step 1 (nếu dùng guest checkout hoặc chuyển step thủ công)
            $('#button-account').on('click', () => this.proceedToStep2());
        },

        // --- 1. KIỂM TRA TOKEN TỰ ĐỘNG ---
        checkAuth: async function () {
            const token = localStorage.getItem('admin_token');
            
            if (token && window.api) {
                console.log("Checking session...");
                try {
                    // Verify token với server
                    await window.api.get('/api/v1/customer/cart');
                    console.log("Session valid. Skipping to address step.");
                    
                    // Gọi hàm điều hướng của module này
                    this.skipToAddressStep(true);
                } catch (error) {
                    console.warn("Session expired.");
                    localStorage.removeItem('admin_token');
                    localStorage.removeItem('admin_user');
                }
            }
        },

        // --- 2. XỬ LÝ ĐĂNG NHẬP ---
        handleLogin: function () {
            const _this = this;
            $('#form-login-checkout').on('submit', async function (e) {
                e.preventDefault();
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

                    // Cập nhật token cho axios instance ngay lập tức
                    if(window.api) window.api.defaults.headers.common['Authorization'] = `Bearer ${result.authorization.token}`;

                    await Swal.fire({ 
                        icon: 'success', 
                        title: 'Chào mừng ' + result.user_info.name, 
                        timer: 1000, 
                        showConfirmButton: false 
                    });

                    _this.skipToAddressStep();
                } catch (error) {
                    Checkout.handleAjaxError(error); // Gọi hàm dùng chung từ Core
                } finally {
                    $btn.html('Đăng nhập').prop('disabled', false);
                }
            });
        },

        // --- 3. XỬ LÝ ĐĂNG KÝ ---
        handleRegister: function () {
            const _this = this;
            $('#form-register-checkout').on('submit', async function (e) {
                e.preventDefault();
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
                    
                    if(window.api) window.api.defaults.headers.common['Authorization'] = `Bearer ${result.authorization.token}`;

                    await Swal.fire({ icon: 'success', title: 'Đăng ký thành công!', timer: 1000, showConfirmButton: false });

                    _this.skipToAddressStep();
                } catch (error) {
                    Checkout.handleAjaxError(error);
                } finally {
                    $btn.html('Continue').prop('disabled', false);
                }
            });
        },

        // --- 4. ĐIỀU HƯỚNG ---
        proceedToStep2: function () {
            Checkout.goToStep('#payment-address-content');
        },

        skipToAddressStep: function (isAuto = false) {
            // Đóng các step trước
            $('#checkout-content, #payment-address-content').collapse('hide');
            
            // Đánh dấu hoàn thành cho các step trước bằng hàm của Core
            Checkout.markStepComplete('#checkout-content');
            Checkout.markStepComplete('#payment-address-content');

            // Mở Step Địa chỉ
            Checkout.goToStep('#shipping-address-content');
            
            // QUAN TRỌNG: Gọi module Address để tải dữ liệu
            if (Checkout.Address) {
                console.log("Auth success - Triggering Address Load");
                Checkout.Address.loadAddresses(); 
            }
        }
    };
})();