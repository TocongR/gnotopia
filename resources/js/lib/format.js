export function formatPrice(cents) {
    if (cents === 0) {
        return 'Free';
    }

    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(cents / 100);
}