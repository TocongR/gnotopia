const styles = {
    draft: 'bg-gray-100 text-gray-700',
    published: 'bg-green-100 text-green-800',
    suspended: 'bg-red-100 text-red-800',
};

export default function StatusBadge({ status }) {
    return (
        <span
            className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium capitalize ${styles[status] ?? styles.draft}`}
        >
            {status}
        </span>
    );
}