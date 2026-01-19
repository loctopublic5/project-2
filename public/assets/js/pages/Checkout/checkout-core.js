/**
 * Core Dispatcher: Quản lý luồng và các module con
 */
var Checkout = (function () {
    // Biến lưu trữ dữ liệu dùng chung giữa các Step
    var globalData = {
        selectedAddressId: null,
        shippingMethod: null,
        paymentMethod: null,
        totalAmount: 0
    };

    return {
        // Getter/Setter để các module con truy cập dữ liệu an toàn
        data: globalData,

        init: function () {
            console.log("Checkout System Starting...");
            
            // Khởi tạo các module con nếu chúng tồn tại
            if (this.Auth) this.Auth.init();
            if (this.Address) this.Address.init();
            if (this.Shipping) this.Shipping.init();
            if (this.Payment) this.Payment.init();
        },

        formatPrice: function (amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        },
        // Tiện ích: Chuyển bước mượt mà (Metronic Accordion)
        goToStep: function (stepContentId) {
            // Đóng tất cả các panel khác (tùy chọn)
            // $('.panel-collapse').collapse('hide'); 
            
            // Mở panel đích
            $(stepContentId).collapse('show');

            // Hiệu ứng cuộn trang lên đầu bước hiện tại cho UX tốt
            setTimeout(() => {
                $('html, body').animate({
                scrollTop: $(stepContentId).parent().offset().top - 80 // Trừ đi khoảng cách header nếu có
            }, 500);
            }, 300); // Đợi một chút để hiệu ứng mở accordion bắt đầu
    },

        // Tiện ích: Đánh dấu bước đã hoàn thành (Icon check)
        markStepComplete: function (stepHref) {
            var $link = $('a[href="' + stepHref + '"]');
            $link.find('.fa-check').remove(); // Tránh trùng lặp
            $link.append(' <i class="fa fa-check text-success"></i>');
        },

        // Xử lý lỗi Ajax tập trung
        handleAjaxError: function (error) {
            let msg = "Đã có lỗi xảy ra.";
            if (error.response) {
                if (error.response.status === 422) {
                    msg = Object.values(error.response.data.errors)[0][0];
                } else if (error.response.data.message) {
                    msg = error.response.data.message;
                }
            }
            Swal.fire({ icon: 'error', title: 'Lỗi', text: msg });
        }
    };
})();