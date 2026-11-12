const styles = {
    draft: 'bg-stone-100 text-stone-700',
    published: 'bg-brand-100 text-brand-800',
    suspended: 'bg-red-100 text-red-800',
};

export default function StatusBadge({ status }) {
    return (
        <span
            className={`inline-flex rounded px-2 py-0.5 text-xs font-medium capitalize ${styles[status] ?? styles.draft}`}
        >
            {status}
        </span>
    );
}