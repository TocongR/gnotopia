export default function LogoMark({ className = 'h-8 w-8' }) {
    return (
        <svg viewBox="0 0 40 40" className={className} aria-hidden="true">
            <rect width="40" height="40" rx="11" className="fill-brand-800" />
            <path
                d="M27 15.5A9 9 0 1 0 28.5 22H21"
                fill="none"
                strokeWidth="4"
                strokeLinecap="round"
                strokeLinejoin="round"
                className="stroke-sun-300"
            />
        </svg>
    );
}