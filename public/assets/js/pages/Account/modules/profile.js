const UserProfileModule = {
    isLoaded: false,
    formatCurrency: (amount) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount || 0),

    init: function() {
        console.log("UserProfileModule: Initializing...");
        if (this.isLoaded) {
            console.log("UserProfileModule: Already loaded, skipping init.");
            return; 
        } 
        this.loadUserDetail();
        this.initAvatarEvents();

        this.isLoaded = true;
    },
    // 1. Khởi tạo sự kiện (Gọi hàm này trong UserProfileModule.init)
    initAvatarEvents: function() {
        const self = this;
        $('#avatar-input').on('change', function() {
            const file = this.files[0];
            if (file) {
                // Client-side Preview (Cập nhật tức thì để trải nghiệm mượt)
                const reader = new FileReader();
                reader.onload = (e) => $('#detail-avatar').attr('src', e.target.result);
                reader.readAsDataURL(file);

                // Tiến hành upload ngay
                self.uploadAvatar(file);
            }
        });
    },

    // 2. Logic Xem ảnh to bằng SweetAlert2
    viewFullAvatar: function() {
        const currentSrc = $('#detail-avatar').attr('src');
        Swal.fire({
            imageUrl: currentSrc,
            imageAlt: 'Avatar',
            showConfirmButton: false,
            background: 'transparent',
            backdrop: `rgba(0,0,0,0.8)`,
            closeButtonHtml: '&times;',
            showCloseButton: true
        });
    },

    // 3. Logic Upload (Dựa trên Product Module)
    uploadAvatar: async function(file) {
    // 1. Lấy ID từ localStorage (đã có sẵn trong logic của bạn)
    const storedUser = JSON.parse(localStorage.getItem('admin_user'));
    const userId = storedUser ? storedUser.id : null;

    if (!userId) {
        Swal.fire('Lỗi', 'Không tìm thấy ID người dùng', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('avatar', file);
    formData.append('_method', 'PUT'); 

    if (this.isUploading) return;
        this.isUploading = true;

    try {
        // Hiển thị loading
        $('.avatar-overlay').css('opacity', '1').html('<i class="fa fa-spinner fa-spin" style="color:white; font-size:30px;"></i>');

        const config = { headers: { 'Content-Type': 'multipart/form-data' } };
        
        // 2. Nối userId vào URL để khớp với Route {id}
        const response = await window.api.post(`/api/v1/customer/profile/avatar/${userId}`, formData, config);

        if (response.data.status) {
            const newUrl = response.data.data.avatar_url;
            $('.global-user-avatar').attr('src', newUrl);
            
            // Cập nhật lại cache local
            storedUser.avatar_url = newUrl;
            localStorage.setItem('admin_user', JSON.stringify(storedUser));

            Swal.fire({ icon: 'success', title: 'Thành công', text: 'Đã cập nhật ảnh đại diện', timer: 1500, showConfirmButton: false });
        }
    } catch (error) {
        console.error("Avatar Upload Error:", error);
        Swal.fire('Lỗi', 'Không thể tải ảnh lên', 'error');
    } finally {
        this.isUploading = false;
        this.resetAvatarOverlay();
    }
},

    resetAvatarOverlay: function() {
        const overlayHtml = `
            <div class="overlay-content">
                <button type="button" onclick="UserProfileModule.viewFullAvatar()" class="btn-avatar-action" title="Xem ảnh">
                    <i class="fa fa-search-plus"></i>
                </button>
                <button type="button" onclick="$('#avatar-input').click()" class="btn-avatar-action" title="Đổi ảnh">
                    <i class="fa fa-camera"></i>
                </button>
            </div>`;
        $('.avatar-overlay').html(overlayHtml).css('opacity', '');
        $('#avatar-input').val(''); // Giải phóng bộ nhớ input file
    },
    openEditModal: function() {
    // Lấy dữ liệu hiện tại từ UI đổ vào form modal
    $('#edit-full-name').val($('#detail-name').text());
    $('#edit-email').val($('#detail-email').text());
    $('#edit-phone').val($('#detail-phone').text() === 'Chưa cập nhật' ? '' : $('#detail-phone').text());
    
    $('#modal-edit-profile').modal('show');
},

saveBasicInfo: async function() {
    const storedUser = JSON.parse(localStorage.getItem('admin_user'));
    const userId = storedUser ? storedUser.id : null;

    if (!userId) return Swal.fire('Lỗi', 'Không tìm thấy ID người dùng', 'error');

    // Thu thập dữ liệu từ form
    const updateData = {
        full_name: $('#edit-full-name').val(),
        email: $('#edit-email').val(),
        phone: $('#edit-phone').val(),
    };

    try {
        // Hiển thị loading trên nút bấm hoặc dùng Swal
        Swal.showLoading();

        // Gửi request PUT đến API update-info/{id}
        const response = await window.api.put(`/api/v1/customer/profile/update-info/${userId}`, updateData);

        if (response.data.status) {
            const updatedUser = response.data.data;

            // 1. Cập nhật lại UI hiển thị
            this.fillBasicInfo(updatedUser);

            // 2. Cập nhật lại localStorage để đồng bộ toàn trang
            storedUser.full_name = updatedUser.full_name;
            storedUser.email = updatedUser.email;
            localStorage.setItem('admin_user', JSON.stringify(storedUser));

            // 3. Đóng modal và thông báo
            $('#modal-edit-profile').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Thành công',
                text: 'Thông tin đã được cập nhật!',
                timer: 1500,
                showConfirmButton: false
            });
        }
    } catch (error) {
        console.error("Update Profile Error:", error);
        let msg = 'Không thể cập nhật thông tin';
        if (error.response && error.response.data.errors) {
            // Lấy lỗi đầu tiên từ validation của Laravel
            msg = Object.values(error.response.data.errors)[0][0];
        }
        Swal.fire('Thất bại', msg, 'error');
    }
},

requestPasswordReset: async function() {
    const storedUser = JSON.parse(localStorage.getItem('admin_user'));
    const userId = storedUser ? storedUser.id : null;

    if (!userId) return Swal.fire('Lỗi', 'Không tìm thấy ID người dùng', 'error');

    const result = await Swal.fire({
        title: 'Xác nhận đổi mật khẩu?',
        text: "Hệ thống sẽ chuyển bạn đến trang yêu cầu đặt lại mật khẩu với email đã được điền sẵn.",
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#435ebe',
        cancelButtonColor: '#cdcdcd',
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Hủy'
    });

    if (result.isConfirmed) {
        try {
            Swal.showLoading();
            // Gọi API trigger để lấy URL redirect
            const response = await window.api.post('/api/v1/customer/profile/trigger-reset-password', {
                user_id: userId
            });

            if (response.data.status) {
                // Chuyển hướng người dùng sang trang forgot-password kèm email
                window.location.href = response.data.data.redirect_url;
            }
        } catch (error) {
            console.error("Reset Password Error:", error);
            Swal.fire('Lỗi', 'Không thể kết nối với hệ thống khôi phục mật khẩu.', 'error');
        }
    }
},

    loadUserDetail: async function() {
        const pathSegments = window.location.pathname.split('/').filter(s => s);
        const lastSegment = pathSegments[pathSegments.length - 1];
        let userId = !isNaN(lastSegment) ? lastSegment : null;

        if (!userId) {
            const storedUser = localStorage.getItem('admin_user');
            if (storedUser) {
                try {
                    const userObj = JSON.parse(storedUser);
                    userId = userObj.id;
                    console.log("UserProfileModule: Lấy ID từ localStorage:", userId);
                } catch (e) { console.error("Lỗi parse localStorage"); }
            }
        }

        if (!userId) {
            $('#detail-name').text("Vui lòng đăng nhập");
            return;
        }

        try {
            const endpoint = `/api/v1/customer/user/${userId}`;
            console.log("UserProfileModule: Fetching...", endpoint);
            const res = await window.api.get(endpoint);
            const user = res.data.data || res.data;

            this.fillBasicInfo(user);
            this.fillVipInfo(user.vip_info);
            this.renderAddressTable(user.addresses || []);
            this.renderOrderTable(user.recent_orders || []);

            this.isLoaded = true;
        } catch (error) {
            console.error("UserProfileModule Error:", error);
            $('#detail-name').text("Lỗi kết nối API");
        }
    },

    fillBasicInfo: function(user) {
        $('#detail-name').text(user.full_name || '---');
        $('#detail-email').text(user.email);
        $('#detail-phone').text(user.phone || 'Chưa cập nhật');
        $('#detail-joined').text(user.joined_at);
        
        const statusHtml = user.is_active 
            ? '<span class="label label-sm label-success">Đang hoạt động</span>'
            : '<span class="label label-sm label-danger">Bị khóa</span>';
        $('#detail-status').html(statusHtml);

        if(user.avatar_url) $('#detail-avatar').attr('src', user.avatar_url);
    },

    fillVipInfo: function(vip) {
        if (!vip) return;
        $('#detail-wallet').text(this.formatCurrency(vip.wallet_balance));
        const rank = vip.rank || 'Member';
        $('#detail-rank')
            .removeClass()
            .addClass(`rank-badge rank-${rank.toLowerCase()}`)
            .text(rank);
    },

    renderAddressTable: function(addresses) {
        const $tbody = $('#address-list');
        if (addresses.length === 0) {
            $tbody.html('<tr><td colspan="4" class="text-center text-muted">Chưa có địa chỉ nào</td></tr>');
            return;
        }

        $tbody.html(addresses.map(addr => `
            <tr>
                <td class="bold">${addr.recipient_name} ${addr.is_default ? '<span class="badge-default">Mặc định</span>' : ''}</td>
                <td>${addr.phone}</td>
                <td>
                    <small class="d-block">${addr.address_detail}</small>
                    <small class="text-muted loc-full-${addr.id}">Đang tải vị trí...</small>
                </td>
                <td><span class="label label-sm ${addr.is_active ? 'label-info' : 'label-default'}">${addr.is_active ? 'Sử dụng' : 'Khóa'}</span></td>
            </tr>
        `).join(''));

        // Cập nhật logic lấy vị trí theo đúng JSON trả về
        addresses.forEach(async (addr) => {
            // Check đúng key province_id từ dữ liệu bạn gửi
            if (addr.province_id && typeof LocationMapper !== 'undefined') {
                try {
                    const pName = await LocationMapper.getName('p', addr.province_id);
                    const dName = await LocationMapper.getName('d', addr.district_id);
                    const wName = await LocationMapper.getName('w', addr.ward_id);
                    $(`.loc-full-${addr.id}`).text(`${wName}, ${dName}, ${pName}`);
                } catch (e) {
                    $(`.loc-full-${addr.id}`).text('Không rõ vị trí');
                }
            } else {
                $(`.loc-full-${addr.id}`).text('');
            }
        });
    },

    renderOrderTable: function(orders) {
        const $tbody = $('#order-list');
        if (orders.length === 0) {
            $tbody.html('<tr><td colspan="4" class="text-center">Chưa có đơn hàng nào</td></tr>');
            return;
        }

        $tbody.html(orders.map(order => {
            const statusLabels = { 'completed': 'Hoàn thành', 'pending': 'Chờ xử lý', 'shipping': 'Đang giao', 'cancelled': 'Đã hủy' };
            return `
                <tr>
                    <td><a href="/customer/orders/${order.id}" class="bold">#${order.code}</a></td>
                    <td>${order.created_at.split('T')[0]}</td>
                    <td class="bold text-danger">${this.formatCurrency(order.total_amount)}</td>
                    <td><span class="status-pill status-${order.status}">${statusLabels[order.status] || order.status}</span></td>
                </tr>
            `;
        }).join(''));
    }
};
