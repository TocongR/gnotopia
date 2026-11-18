export default function SecondaryButton({ type = 'button', className = '', disabled, children, ...props }) {
    return (
        <button
            {...props}
            type={type}
            disabled={disabled}
            className={`inline-flex items-center justify-center rounded-full border border-stone-300 bg-white px-5 py-2 text-sm font-semibold text-stone-800 transition-colors hover:bg-stone-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 disabled:opacity-50 ${className}`}
        >
            {children}
        </button>
    );
}