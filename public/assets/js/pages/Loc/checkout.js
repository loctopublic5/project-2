

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
            this.initLocationSelectors();

            // Lắng nghe sự kiện khi Step 3 được mở ra
            $('#shipping-address-content').on('shown.bs.collapse', () => {
                if ($('#address-items').find('.address-item').length === 0) {
                    this.loadAddresses();
                }
            });
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
            $('#checkout-content').collapse('hide');
            $('#payment-address-content').collapse('hide');
            
            // Mở Step Địa chỉ
            $('#shipping-address-content').collapse('show');
            
            // GỌI NGAY: Tải danh sách địa chỉ ngay khi chuyển step
            this.loadAddresses();

            // Decorate check icon
            $('a[href="#checkout-content"], a[href="#payment-address-content"]').find('.fa-check').remove();
            $('a[href="#checkout-content"]').append(' <i class="fa fa-check text-success"></i>');
            $('a[href="#payment-address-content"]').append(' <i class="fa fa-check text-success"></i>');
        },

        // 1. Tải danh sách địa chỉ từ API
        loadAddresses: async function () {
            const $container = $('#address-items');
            $container.html('<p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Đang lấy địa chỉ của bạn...</p>');

            try {
                const res = await window.api.get('/api/v1/customer/addresses');
                const addresses = res.data.data; 
                this.renderAddresses(addresses);
            } catch (e) {
                $container.html('<div class="alert alert-danger">Không thể tải địa chỉ. Vui lòng thử lại.</div>');
                console.error("Address Load Error:", e);
            }
        },

        // 2. Render danh sách địa chỉ
        renderAddresses: function (addresses) {
            const $container = $('#address-items');
            
            if (!addresses || addresses.length === 0) {
                $container.html('<div class="alert alert-info">Bạn chưa có địa chỉ giao hàng nào.</div>');
                this.toggleNewAddressForm(true); // Tự động mở form thêm mới nếu chưa có gì
                return;
            }

            let html = '';
            addresses.forEach(addr => {
                // Nếu là địa chỉ mặc định hoặc chưa có cái nào được chọn thì ưu tiên nó
                const isActive = addr.is_default ? 'active' : '';
                if (addr.is_default) selectedAddressId = addr.id;

                html += `
                <div class="address-item ${isActive}" data-id="${addr.id}" onclick="Checkout.selectAddress(${addr.id}, this)">
                    <div class="address-content">
                        <strong>${addr.recipient_name}</strong> 
                        ${addr.is_default ? '<span class="badge badge-success">Mặc định</span>' : ''}
                        <br><small><i class="fa fa-phone"></i> ${addr.phone}</small>
                        <p style="margin-top: 5px; margin-bottom:0; color: #666;">
                            <i class="fa fa-map-marker"></i> ${addr.full_address}
                        </p>
                    </div>
                </div>`;
            });
            $container.html(html);

            // Nếu có địa chỉ mặc định, tự động gán ID để tiếp tục
            if (!selectedAddressId && addresses.length > 0) {
                selectedAddressId = addresses[0].id;
            }
        },

        selectAddress: function (id, element) {
            selectedAddressId = id;
            $('.address-item').removeClass('active');
            $(element).addClass('active');
        },

        toggleNewAddressForm: function (forceShow = false) {
            const $wrapper = $('#new-address-form-wrapper');
            forceShow ? $wrapper.slideDown() : $wrapper.slideToggle();
        },

        // 3. Lưu địa chỉ mới (Store theo SaveAddressRequest)
        handleSaveAddress: function () {
    const _this = this;
    
    // Sử dụng $(document).off().on() để tránh việc gán trùng lặp event nếu init gọi nhiều lần
    $(document).off('submit', '#form-add-address').on('submit', '#form-add-address', async function (e) {
        e.preventDefault(); // CHẶN RELOAD TRANG
        e.stopPropagation();

        const $btn = $('#btn-save-address');
        
        // Lấy dữ liệu từ các input
        const data = {
            recipient_name: $('#recipient_name').val(),
            phone: $('#address_phone').val(),
            province_id: $('#province_id').val(),
            district_id: $('#district_id').val(),
            ward_id: $('#ward_id').val(),
            address_detail: $('#address_detail').val(),
            is_default: $('#is_default').is(':checked') ? 1 : 0
        };

        // Hiệu ứng loading nút
        const originalText = $btn.text();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang lưu...');

        try {
            // Gửi lên backend (SaveAddressRequest của bạn)
            await window.api.post('/api/v1/customer/addresses', data);
            
            Swal.fire({ 
                icon: 'success', 
                title: 'Thành công', 
                text: 'Đã thêm địa chỉ mới', 
                timer: 1500, 
                showConfirmButton: false 
            });

            // 1. Reset form
            $('#form-add-address')[0].reset();
            // 2. Ẩn form thêm mới
            _this.toggleNewAddressForm(false);
            // 3. Quan trọng: Tải lại danh sách địa chỉ ngay lập tức
            await _this.loadAddresses(); 

        } catch (error) {
            _this.handleAjaxError(error);
        } finally {
            $btn.prop('disabled', false).text(originalText);
        }
    });
},

confirmAddress: function () {
    if (!selectedAddressId) {
        Swal.fire({ 
            icon: 'warning', 
            title: 'Chú ý', 
            text: 'Vui lòng chọn một địa chỉ giao hàng hoặc thêm địa chỉ mới.' 
        });
        return;
    }

    // Logic chuyển step mượt mà của Metronic
    $('#shipping-address-content').collapse('hide');
    
    // Ở đây ta sẽ mở Step 3: Shipping Method (Hoặc Step 4 tùy cấu trúc của bạn)
    // Giả sử bước tiếp theo là #shipping-method-content
    $('#shipping-method-content').collapse('show');

    // Thêm icon check hoàn thành cho Step địa chỉ
    $('a[href="#shipping-address-content"]').find('.fa-check').remove();
    $('a[href="#shipping-address-content"]').append(' <i class="fa fa-check text-success"></i>');
    
    console.log("Đã xác nhận Address ID:", selectedAddressId);
},

        handleAjaxError: function(error) {
            let msg = "Đã có lỗi xảy ra.";
            if (error.response && error.response.status === 422) {
                msg = Object.values(error.response.data.errors)[0][0];
            } else if (error.response && error.response.data.message) {
                msg = error.response.data.message;
            }
            Swal.fire({ icon: 'error', title: 'Lỗi', text: msg });
        },
        
        // --- KHỞI TẠO DỮ LIỆU ĐỊA CHÍNH ---
        initLocationSelectors: function () {
    const $province = $('#province_id');
    const $district = $('#district_id');
    const $ward = $('#ward_id');

    // 1. Tải danh sách Tỉnh/Thành phố
    fetch('https://provinces.open-api.vn/api/p/')
        .then(response => response.json())
        .then(data => {
            data.forEach(p => {
                $province.append(`<option value="${p.code}">${p.name}</option>`);
            });
        })
        .catch(err => console.error("Lỗi tải Tỉnh/Thành:", err));

    // 2. Khi chọn Tỉnh -> Tải Quận/Huyện
    $province.on('change', function () {
        const pCode = $(this).val();
        $district.empty().append('<option value="">-- Chọn Quận/Huyện --</option>').prop('disabled', true);
        $ward.empty().append('<option value="">-- Chọn Phường/Xã --</option>').prop('disabled', true);

        if (pCode) {
            fetch(`https://provinces.open-api.vn/api/p/${pCode}?depth=2`)
                .then(response => response.json())
                .then(data => {
                    data.districts.forEach(d => {
                        $district.append(`<option value="${d.code}">${d.name}</option>`);
                    });
                    $district.prop('disabled', false);
                });
        }
    });

    // 3. Khi chọn Quận/Huyện -> Tải Phường/Xã
    $district.on('change', function () {
        const dCode = $(this).val();
        $ward.empty().append('<option value="">-- Chọn Phường/Xã --</option>').prop('disabled', true);

        if (dCode) {
            fetch(`https://provinces.open-api.vn/api/d/${dCode}?depth=2`)
                .then(response => response.json())
                .then(data => {
                    data.wards.forEach(w => {
                        $ward.append(`<option value="${w.code}">${w.name}</option>`);
                    });
                    $ward.prop('disabled', false);
                });
        }
    });
},
    saveAddressManual: async function () {
    const $btn = $('#btn-save-address');
    
    // 1. LẤY DỮ LIỆU
    const data = {
        recipient_name: $('#recipient_name').val().trim(),
        phone:          $('#address_phone').val().trim(),
        province_id:    $('#province_id').val(),
        district_id:    $('#district_id').val(),
        ward_id:        $('#ward_id').val(),
        address_detail: $('#address_detail').val().trim(),
        is_default:     $('#is_default').is(':checked') ? 1 : 0
    };

    // 2. VALIDATE THỦ CÔNG (Vì không dùng form submit)
    if (!data.recipient_name || !data.phone || !data.address_detail) {
        Swal.fire({ icon: 'warning', title: 'Thiếu thông tin', text: 'Vui lòng nhập tên, số điện thoại và địa chỉ chi tiết.' });
        return;
    }
    if (!data.province_id || !data.district_id || !data.ward_id) {
        Swal.fire({ icon: 'warning', title: 'Thiếu thông tin', text: 'Vui lòng chọn đầy đủ Tỉnh/Thành, Quận/Huyện, Phường/Xã.' });
        return;
    }

    // 3. XỬ LÝ GỬI ĐI
    const originalText = $btn.text();
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang lưu...');

    try {
        const response = await window.api.post('/api/v1/customer/addresses', data);
        
        // Lấy ID của địa chỉ vừa tạo (Giả sử BE trả về data của address mới tạo)
        // Nếu BE bạn trả về: { data: { id: 123, ... } }
        const newAddressId = response.data.data ? response.data.data.id : null;

        await Swal.fire({ 
            icon: 'success', 
            title: 'Thành công', 
            text: 'Đã thêm địa chỉ mới', 
            timer: 1000, 
            showConfirmButton: false 
        });

        // Reset form
        $('#recipient_name').val('');
        $('#address_phone').val('');
        $('#address_detail').val('');
        // Reset dropdown (nếu cần thiết)
        
        // Ẩn form
        this.toggleNewAddressForm(false);

        // 4. LOAD LẠI & TỰ ĐỘNG CHỌN
        // Gọi loadAddresses nhưng truyền thêm ID mới để nó biết đường mà "Active"
        await this.loadAddresses(newAddressId);

    } catch (error) {
        this.handleAjaxError(error);
    } finally {
        $btn.prop('disabled', false).text(originalText);
    }
},

        // --- XỬ LÝ NHẬP ĐỊA CHỈ SONG SONG ---
        // Nếu bạn dùng một dịch vụ như Google Maps Autocomplete hoặc API địa chính thông minh
        initQuickAddress: function() {
            $('#quick-address').on('input', function() {
                const query = $(this).val();
                if(query.length > 5) {
                    // Logic gợi ý địa chỉ ở đây
                    // Khi người dùng chọn 1 gợi ý, bạn tự động trigger change cho 3 dropdown trên
                }
            });
        }
    };

    var LocationMapper = {
    cache: { p: {}, d: {}, w: {} },

    getName: async function(type, code) {
        if (!code) return "";
        if (this.cache[type][code]) return this.cache[type][code];

        try {
            const res = await fetch(`https://provinces.open-api.vn/api/${type}/${code}`);
            const data = await res.json();
            this.cache[type][code] = data.name;
            return data.name;
        } catch (e) {
            return "N/A";
        }
    }
};
}();