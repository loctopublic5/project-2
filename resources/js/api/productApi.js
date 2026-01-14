import axiosClient from "./axiosClient";

const productApi = {
    /**
     * 1. Lấy danh sách sản phẩm mới (New Arrivals)
     * Dùng để hiển thị ở trang chủ
     */
    getNewArrivals(params) {
        const url = "/products/new-arrivals";
        return axiosClient.get(url, { params });
    },

    /**
     * 2. Lấy tất cả sản phẩm
     * Phục vụ trang "DANH SÁCH SẢN PHẨM"
     * params có thể chứa: page, limit, sort...
     */
    getAll(params) {
        const url = "/products";
        return axiosClient.get(url, { params });
    },

    /**
     * 3. Lấy chi tiết một sản phẩm theo ID hoặc Slug
     * Gọi khi người dùng nhấn vào nút "CHI TIẾT"
     */
    getById(id) {
        const url = `/products/${id}`;
        return axiosClient.get(url);
    },

    /**
     * 4. Lấy sản phẩm theo danh mục (Category)
     */
    getByCategory(categoryId, params) {
        const url = `/categories/${categoryId}/products`;
        return axiosClient.get(url, { params });
    },

    /**
     * 5. Tìm kiếm sản phẩm (Bổ sung mới)
     * Dùng cho ô Search trên Header Metronic
     */
    search(query) {
        const url = "/products/search";
        return axiosClient.get(url, { params: { q: query } });
    },

    /**
     * 6. Lấy sản phẩm liên quan (Bổ sung mới)
     * Dùng ở cuối trang chi tiết sản phẩm
     */
    getRelated(id) {
        const url = `/products/${id}/related`;
        return axiosClient.get(url);
    },
};

export default productApi;
