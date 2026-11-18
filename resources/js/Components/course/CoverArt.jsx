const palettes = [
    { bg: 'fill-brand-800', a: 'fill-sun-300', b: 'fill-brand-600' },
    { bg: 'fill-sun-300', a: 'fill-brand-800', b: 'fill-brand-600' },
    { bg: 'fill-brand-100', a: 'fill-brand-700', b: 'fill-sun-300' },
];

export default function CoverArt({ seed }) {
    const pattern = seed % 4;
    const p = palettes[Math.floor(seed / 4) % palettes.length];

    return (
        <svg
            viewBox="0 0 400 200"
            preserveAspectRatio="xMidYMid slice"
            className="h-full w-full"
            aria-hidden="true"
        >
            <rect width="400" height="200" className={p.bg} />

            {pattern === 0 && (
                <>
                    <circle cx="300" cy="100" r="110" className={p.b} />
                    <circle cx="300" cy="100" r="70" className={p.a} />
                    <circle cx="300" cy="100" r="30" className={p.bg} />
                </>
            )}

            {pattern === 1 && (
                <>
                    <rect x="70" y="40" width="60" height="180" rx="30" className={p.a} />
                    <rect x="170" y="-20" width="60" height="180" rx="30" className={p.b} />
                    <rect x="270" y="60" width="60" height="180" rx="30" className={p.a} />
                </>
            )}

            {pattern === 2 &&
                [0, 1, 2, 3].map((i) => (
                    <path
                        key={i}
                        d={`M${i * 100} 200 a50 90 0 0 1 100 0Z`}
                        className={i % 2 === 0 ? p.a : p.b}
                    />
                ))}

            {pattern === 3 &&
                [0, 1, 2, 3].flatMap((row) =>
                    [0, 1, 2, 3, 4, 5, 6, 7].map((col) => (
                        <circle
                            key={`${row}-${col}`}
                            cx={25 + col * 50}
                            cy={25 + row * 50}
                            r="10"
                            className={(row + col) % 2 === 0 ? p.a : p.b}
                        />
                    )),
                )}
        </svg>
    );
}