const paths = {
    list: <path d="M4 6h16M4 12h16M4 18h10" />,
    check: (
        <>
            <circle cx="12" cy="12" r="9" />
            <path d="M8 12.5l2.7 2.7L16 9.5" />
        </>
    ),
    chart: <path d="M5 20v-8M12 20V5M19 20v-5" />,
    play: <path d="M8 5.5v13l10.5-6.5z" />,
};

export default function Icon({ name, className = '' }) {
    return (
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
            className={className}
            aria-hidden="true"
        >
            {paths[name]}
        </svg>
    );
}