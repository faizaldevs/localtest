// Currency formatting helper function
export function formatCurrency(value) {
    if (!value) return 'Rs. 0.00';
    return 'Rs. ' + Number(value).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
