const AddressModule = {
    init: function() {
        this.loadAddresses();
        this.initFormEvent();
    },

    initLocationSelectors: async function() {
    const $p = $('select[name="province_id"]');
    const $d = $('select[name="district_id"]');
    const $w = $('select[name="ward_id"]');

    // 1. Load Tỉnh
    try {
        const res = await fetch('https://provinces.open-api.vn/api/p/');
        const provinces = await res.json();
        $p.html('<option value="">-- Chọn Tỉnh/Thành --</option>' + 
            provinces.map(i => `<option value="${i.code}">${i.name}</option>`).join(''));
    } catch (e) { console.error("Lỗi tải tỉnh thành"); }

    // 2. Sự kiện khi chọn Tỉnh -> Load Huyện
    $p.on('change', async function() {
        const pCode = $(this).val();
        $d.html('<option value="">Đang tải...</option>');
        $w.html('<option value="">-- Chọn Xã/Phường --</option>');
        if(!pCode) return;

        const res = await fetch(`https://provinces.open-api.vn/api/p/${pCode}?depth=2`);
        const data = await res.json();
        $d.html('<option value="">-- Chọn Quận/Huyện --</option>' + 
            data.districts.map(i => `<option value="${i.code}">${i.name}</option>`).join(''));
    });

    // 3. Sự kiện khi chọn Huyện -> Load Xã
    $d.on('change', async function() {
        const dCode = $(this).val();
        $w.html('<option value="">Đang tải...</option>');
        if(!dCode) return;

        const res = await fetch(`https://provinces.open-api.vn/api/d/${dCode}?depth=2`);
        const data = await res.json();
        $w.html('<option value="">-- Chọn Xã/Phường --</option>' + 
            data.wards.map(i => `<option value="${i.code}">${i.name}</option>`).join(''));
    });
},

    loadAddresses: async function() {
        const grid = document.getElementById('address-list-grid');
        grid.innerHTML = '<div class="col-md-12 text-center"><i class="fa fa-refresh fa-spin"></i> Đang tải...</div>';

        try {
            const res = await window.api.get('/api/v1/customer/addresses');
            const addresses = res.data.data;

            if (addresses.length === 0) {
                grid.innerHTML = '<div class="col-md-12 text-center">Bạn chưa có địa chỉ nào. Hãy thêm mới!</div>';
                return;
            }

grid.innerHTML = addresses.map(addr => `
    <div class="col-md-6 col-sm-12">
        <div class="address-card ${addr.is_default ? 'default' : ''}">
            ${addr.is_default ? '<span class="badge-default-addr">Mặc định</span>' : ''}
            
            <div class="address-header">
                <span class="name">${addr.recipient_name}</span>
            </div>
            
            <div class="address-body">
                <p><i class="fa fa-phone"></i> ${addr.phone}</p>
                <p><i class="fa fa-map-marker"></i> ${addr.location.detail}</p>
                <p class="text-muted">
                    <i class="fa fa-map"></i>
                    <span>
                        <span class="loc-w-${addr.id}">...</span>, 
                        <span class="loc-d-${addr.id}">...</span>, 
                        <span class="loc-p-${addr.id}">...</span>
                    </span>
                </p>
            </div>
            
            <div class="address-footer">
                ${!addr.is_default ? `<button onclick="AddressModule.setDefault('${addr.id}')" class="btn btn-link btn-xs">Thiết lập mặc định</button>` : '<span></span>'}
                <div>
                    <button onclick="AddressModule.showEditModal('${addr.id}')" class="btn btn-default btn-xs shadow-sm"><i class="fa fa-edit"></i> Sửa</button>
                    <button onclick="AddressModule.deleteAddress('${addr.id}')" class="btn btn-danger btn-xs shadow-sm"><i class="fa fa-trash"></i> Xóa</button>
                </div>
            </div>
        </div>
    </div>
`).join('');
addresses.forEach(async (addr) => {
    const pName = await LocationMapper.getName('p', addr.location.province_id);
    const dName = await LocationMapper.getName('d', addr.location.district_id);
    const wName = await LocationMapper.getName('w', addr.location.ward_id);
    
    $(`.loc-p-${addr.id}`).text(pName);
    $(`.loc-d-${addr.id}`).text(dName);
    $(`.loc-w-${addr.id}`).text(wName);
});
        } catch (err) {
            grid.innerHTML = '<div class="text-danger">Lỗi tải dữ liệu.</div>';
        }
    },

    showAddModal: function() {
        $('#address-form')[0].reset();
        $('#address-id').val('');
        $('#address-modal-title').text('Thêm địa chỉ nhận hàng');
        $('#addressModal').modal('show');
    },

    showEditModal: async function(id) {
    Swal.showLoading();
    const res = await window.api.get(`/api/v1/customer/addresses/${id}`);
    const addr = res.data.data;

    // Phải load xong Tỉnh, rồi chọn Tỉnh, rồi load Huyện...
    await this.initLocationSelectors(); // Đảm bảo list Tỉnh đã có
    
    $('select[name="province_id"]').val(addr.location.province_id).trigger('change');
    
    // Đợi 1 chút để API Huyện kịp load
    setTimeout(async () => {
        $('select[name="district_id"]').val(addr.location.district_id).trigger('change');
        
        setTimeout(() => {
            $('select[name="ward_id"]').val(addr.location.ward_id);
            Swal.close();
        }, 600);
    }, 600);

    // Điền các thông tin còn lại...
    $('#address-id').val(addr.id);
    $('[name="recipient_name"]').val(addr.recipient_name);
    $('[name="phone"]').val(addr.phone);
    $('[name="address_detail"]').val(addr.location.detail);
    $('[name="is_default"]').prop('checked', addr.is_default);
    
    $('#addressModal').modal('show');
},

// Cập nhật đoạn logic xử lý trong AddressModule.initFormEvent
initFormEvent: function() {
    $('#address-form').on('submit', async (e) => {
        e.preventDefault();
        const id = $('#address-id').val();
        
        // Thu thập dữ liệu từ Form
        const formData = {
            recipient_name: $('[name="recipient_name"]').val(),
            phone: $('[name="phone"]').val(),
            province_id: parseInt($('[name="province_id"]').val()),
            district_id: parseInt($('[name="district_id"]').val()),
            ward_id: parseInt($('[name="ward_id"]').val()),
            address_detail: $('[name="address_detail"]').val(),
            // Service của bạn xử lý boolean rất tốt, nên ta gửi chuẩn boolean
            is_default: $('[name="is_default"]').is(':checked')
        };

        try {
            Swal.showLoading();
            if (id) {
                await window.api.put(`/api/v1/customer/addresses/${id}`, formData);
            } else {
                await window.api.post('/api/v1/customer/addresses', formData);
            }

            Swal.fire({
                icon: 'success',
                title: 'Thành công',
                text: id ? 'Cập nhật địa chỉ thành công' : 'Thêm địa chỉ mới thành công',
                timer: 1500,
                showConfirmButton: false
            });
            
            $('#addressModal').modal('hide');
            this.loadAddresses(); // Tải lại danh sách để thấy sự thay đổi thứ tự (mặc định lên đầu)
        } catch (err) {
            // Hiển thị lỗi từ SaveAddressRequest (422) hoặc Exception (400)
            const errorMsg = err.response?.data?.message || 'Không thể lưu địa chỉ';
            Swal.fire('Lỗi', errorMsg, 'error');
        }
    });
},

    setDefault: async function(id) {
        try {
            await window.api.patch(`/api/v1/customer/addresses/${id}/default`);
            this.loadAddresses();
        } catch (err) {
            Swal.fire('Thất bại', 'Không thể đặt mặc định', 'error');
        }
    },

    deleteAddress: function(id) {
        Swal.fire({
            title: 'Xóa địa chỉ này?',
            text: "Hành động này không thể hoàn tác!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Xóa ngay'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    await window.api.delete(`/api/v1/customer/addresses/${id}`);
                    this.loadAddresses();
                    Swal.fire('Đã xóa!', 'Địa chỉ đã được gỡ bỏ.', 'success');
                } catch (err) {
                    Swal.fire('Lỗi', 'Không thể xóa địa chỉ này', 'error');
                }
            }
        });
    }
};