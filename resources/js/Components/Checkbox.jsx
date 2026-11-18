export default function Checkbox({ className = '', ...props }) {
    return (
        <input
            {...props}
            type="checkbox"
            className={`rounded border-stone-300 text-brand-800 focus:ring-brand-600 ${className}`}
        />
    );
}