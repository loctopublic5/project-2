$(document).ready(function() {

    // --- HÀM 1: ĐỆ QUY DANH MỤC THEO LEVEL ---
    // Giúp hiển thị Cha -> Con -> Cháu đẹp mắt trong Dropdown
    function buildCategoryDropdown(categories, level = 0) {
        let html = '';
        categories.forEach(cat => {
            // Sử dụng thực thể HTML để tạo khoảng trống thụt đầu dòng dựa trên cat.level
            const prefix = level > 0 ? '&nbsp;&nbsp;'.repeat(level) + '↳ ' : '';
            html += `<option value="${cat.id}">${prefix}${cat.name}</option>`;
            
            // Nếu có children (như trong CategoryResource), tiếp tục đệ quy
            if (cat.children && cat.children.length > 0) {
                html += buildCategoryDropdown(cat.children, level + 1);
            }
        });
        return html;
    }

    // --- HÀM 2: LOAD DANH MỤC TỪ API ---
    function loadCategoriesForSearch() {
        $.ajax({
            url: '/api/v1/categories',
            method: 'GET',
            success: function(res) {
                // res.data chứa mảng CategoryResource
                const options = buildCategoryDropdown(res.data);
                $('#search-category-id').append(options);
            }
        });
    }

    // --- HÀM 3: XỬ LÝ KHI SUBMIT FORM ---
    // Hàm này sẽ thu thập toàn bộ filter để gửi lên Backend
    $('#header-search-form').on('submit', function(e) {
        e.preventDefault();

        const filters = {
            category_id: $('#search-category-id').val(),
            keyword: $('#search-keyword').val(),
            sort_by: $('#search-sort-by').val(),
            limit: 20 // Mặc định hoặc lấy từ config
        };

        console.log("Đang gọi tìm kiếm với filters:", filters);

        // Ở đây bạn có thể dùng window.location.href để chuyển trang
        // Hoặc gọi Ajax để render lại danh sách sản phẩm (tùy mục đích sau này)
        const queryString = $.param(filters);
        window.location.href = '/search?' + queryString;
    });

    // Khởi tạo
    loadCategoriesForSearch();
});