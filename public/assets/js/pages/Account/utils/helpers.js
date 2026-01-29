const AppHelpers = {
    formatCurrency: (value) => {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
    },
    
    getStatusBadge: (status, type = 'order') => {
        if (!status) return '<span class="label label-default">N/A</span>';
        let badgeClass = 'status-badge ';
        if (type === 'order') {
            const map = { 'pending': 'badge-pending', 'confirmed': 'badge-confirmed', 'shipping': 'badge-shipping', 'completed': 'badge-completed', 'cancelled': 'badge-cancelled' };
            badgeClass += map[status.key] || 'label-default';
        } else {
            badgeClass += (status.key === 'paid') ? 'badge-paid' : 'badge-unpaid';
        }
        return `<span class="${badgeClass}">${status.label}</span>`;
    }
};