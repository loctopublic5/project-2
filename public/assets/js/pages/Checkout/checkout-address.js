Checkout.Address = (function () {
    // Biến nội bộ trong module
    var selectedAddressId = null;

    // Helper: Mapping ID sang Tên (Tỉnh/Huyện/Xã)
    var LocationMapper = {
    cache: { p: {}, d: {}, w: {} },
    getName: async function(type, code) {
        if (!code || code == 0) return "";
        if (this.cache[type][code]) return this.cache[type][code];

        try {
            // Thêm timeout để không bắt user đợi quá lâu nếu API treo
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 3000);

            const res = await fetch(`https://provinces.open-api.vn/api/${type}/${code}`, { signal: controller.signal });
            clearTimeout(timeoutId);

            if (!res.ok) throw new Error("API Error");
            
            const data = await res.json();
            this.cache[type][code] = data.name;
            return data.name;
        } catch (e) {
            console.warn(`Lỗi lấy tên ${type} cho mã ${code}:`, e);
            // Thay vì trả về N/A ngay, hãy trả về chính cái ID để user vẫn biết đó là mã gì
            return `Mã ${code}`; 
        }
    }
};

    return {
        init: function () {
            // 1. Khởi tạo dropdown Tỉnh/Thành
            this.initLocationSelectors();

            // 2. Lắng nghe sự kiện click nút "Thêm địa chỉ mới" (để ẩn/hiện form)
            // Giả sử nút của bạn có ID là #btn-show-address-form hoặc tương đương
            $(document).on('click', '#btn-add-new-address', () => {
                this.toggleNewAddressForm();
            });

            // 3. Lắng nghe sự kiện nút "LƯU" địa chỉ
            $(document).on('click', '#btn-save-address', (e) => {
                e.preventDefault();
                this.saveAddressManual();
            });

            // 4. Lắng nghe sự kiện nút "TIẾP TỤC" (Xác nhận địa chỉ đã chọn)
            $(document).on('click', '#btn-confirm-address', () => {
                this.confirmAddress();
            });

            // 5. Tự động load địa chỉ khi mở Step 2
            $(document).on('shown.bs.collapse', '#shipping-address-content', () => {
                this.loadAddresses();
            });

            $(document).on('submit', '#form-add-address', (e) => {
                e.preventDefault();
                this.saveAddressManual();
            });
        },

        // Các hàm hỗ trợ ẩn hiện Form
        toggleNewAddressForm: function (forceShow = null) {
            const $wrapper = $('#new-address-form-wrapper');
            if (forceShow === true) $wrapper.slideDown();
            else if (forceShow === false) $wrapper.slideUp();
            else $wrapper.slideToggle();
        },
        // 1. Tải danh sách địa chỉ từ API
        loadAddresses: async function (activeId = null) {
            const $container = $('#address-items');
            $container.html('<p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Đang lấy địa chỉ...</p>');

            try {
                const res = await window.api.get('/api/v1/customer/addresses');
                const addresses = res.data.data;
                await this.renderAddresses(addresses, activeId);
            } catch (e) {
                $container.html('<div class="alert alert-danger">Không thể tải địa chỉ.</div>');
                Checkout.handleAjaxError(e);
            }
        },

        // 2. Render danh sách địa chỉ (Async để chờ Mapping tên)
        renderAddresses: async function (addresses, activeId = null) {
            const $container = $('#address-items');
            
            if (!addresses || addresses.length === 0) {
                $container.html('<div class="alert alert-info">Bạn chưa có địa chỉ giao hàng nào.</div>');
                this.toggleNewAddressForm(true);
                return;
            }

            // Xác định ID nào sẽ được active
            if (activeId) {
                selectedAddressId = activeId;
            } else {
                const defaultAddr = addresses.find(a => a.is_default);
                selectedAddressId = defaultAddr ? defaultAddr.id : addresses[0].id;
            }

            let html = '';
            for (const addr of addresses) {
                const isActive = (addr.id == selectedAddressId) ? 'active' : '';
                
                // Mapping ID sang Tên thực tế
                const pName = await LocationMapper.getName('p', addr.location.province_id);
                const dName = await LocationMapper.getName('d', addr.location.district_id);
                const wName = await LocationMapper.getName('w', addr.location.ward_id);

                html += `
                <div class="address-item ${isActive}" data-id="${addr.id}" 
                    onclick="Checkout.Address.selectAddress(${addr.id}, this)">
                    <div class="address-content">
                        <strong>${addr.recipient_name}</strong> 
                        ${addr.is_default ? '<span class="badge badge-success">Mặc định</span>' : ''}
                        <br><small><i class="fa fa-phone"></i> ${addr.phone}</small>
                        <p style="margin-top: 5px; margin-bottom:0; color: #333;">
                            <i class="fa fa-map-marker"></i> ${addr.location.detail}, ${wName}, ${dName}, ${pName}
                        </p>
                    </div>
                </div>`;
            }
            $container.html(html);
            
            // Cập nhật ID vào Core Data để các step sau sử dụng
            Checkout.data.selectedAddressId = selectedAddressId;
        },

        selectAddress: function (id, element) {
            selectedAddressId = id;
            Checkout.data.selectedAddressId = id;
            $('.address-item').removeClass('active');
            $(element).addClass('active');
        },

        toggleNewAddressForm: function (forceShow = false) {
            const $wrapper = $('#new-address-form-wrapper');
            forceShow ? $wrapper.slideDown() : $wrapper.slideToggle();
        },

        // 3. Lưu địa chỉ mới (Dùng phương thức Manual để an toàn tuyệt đối)
        saveAddressManual: async function () {
            const $btn = $('#btn-save-address');
            const data = {
                recipient_name: $('#recipient_name').val().trim(),
                phone:          $('#address_phone').val().trim(),
                province_id:    $('#province_id').val(),
                district_id:    $('#district_id').val(),
                ward_id:        $('#ward_id').val(),
                address_detail: $('#address_detail').val().trim(),
                is_default:     $('#is_default').is(':checked') ? 1 : 0
            };

            if (!data.recipient_name || !data.phone || !data.address_detail || !data.province_id) {
                return Swal.fire({ icon: 'warning', title: 'Thiếu thông tin', text: 'Vui lòng điền đầy đủ các trường.' });
            }

            const originalText = $btn.text();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            try {
                const response = await window.api.post('/api/v1/customer/addresses', data);
                const newId = response.data.data ? response.data.data.id : null;

                await Swal.fire({ icon: 'success', title: 'Thành công', timer: 1000, showConfirmButton: false });

                $('#form-add-address')[0].reset();
                this.toggleNewAddressForm(false);
                await this.loadAddresses(newId); // Tải lại và chọn cái mới
            } catch (error) {
                Checkout.handleAjaxError(error);
            } finally {
                $btn.prop('disabled', false).text(originalText);
            }
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


        initLocationSelectors: function () {
            const $province = $('#province_id');
            const $district = $('#district_id');
            const $ward = $('#ward_id');

            fetch('https://provinces.open-api.vn/api/p/')
                .then(res => res.json())
                .then(data => {
                    data.forEach(p => $province.append(`<option value="${p.code}">${p.name}</option>`));
                });

            $province.on('change', function () {
                const pCode = $(this).val();
                $district.empty().append('<option value="">-- Quận/Huyện --</option>').prop('disabled', true);
                $ward.empty().append('<option value="">-- Phường/Xã --</option>').prop('disabled', true);
                if (pCode) {
                    fetch(`https://provinces.open-api.vn/api/p/${pCode}?depth=2`)
                        .then(res => res.json())
                        .then(data => {
                            data.districts.forEach(d => $district.append(`<option value="${d.code}">${d.name}</option>`));
                            $district.prop('disabled', false);
                        });
                }
            });

            $district.on('change', function () {
                const dCode = $(this).val();
                $ward.empty().append('<option value="">-- Phường/Xã --</option>').prop('disabled', true);
                if (dCode) {
                    fetch(`https://provinces.open-api.vn/api/d/${dCode}?depth=2`)
                        .then(res => res.json())
                        .then(data => {
                            data.wards.forEach(w => $ward.append(`<option value="${w.code}">${w.name}</option>`));
                            $ward.prop('disabled', false);
                        });
                }
            });
        }
    };
})();