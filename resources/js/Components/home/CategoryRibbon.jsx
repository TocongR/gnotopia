import { Link } from '@inertiajs/react';

const styles = [
    'bg-brand-800 text-white',
    'bg-sun-300 text-brand-950',
    'bg-brand-100 text-brand-900',
    'border border-stone-300 bg-white text-stone-800',
];

function Group({ categories, hidden = false }) {
    // Repeat the list so one group is always wider than the screen.
    const rounds = Math.max(3, Math.ceil(12 / categories.length));

    const items = Array.from({ length: rounds }).flatMap((_, round) =>
        categories.map((category, index) => ({
            category,
            key: `${round}-${category.id}`,
            style: styles[(round * categories.length + index) % styles.length],
        })),
    );

    return (
        <ul className="flex shrink-0 items-center gap-4 pr-4" aria-hidden={hidden || undefined}>
            {items.map(({ category, key, style }) => (
                <li key={key}>
                    <Link
                        href={route('catalog.index', { category: category.slug })}
                        tabIndex={hidden ? -1 : undefined}
                        className={`inline-flex rounded-full px-7 py-3 text-lg font-semibold ${style}`}
                    >
                        {category.name}
                    </Link>
                </li>
            ))}
        </ul>
    );
}

export default function CategoryRibbon({ categories }) {
    if (categories.length === 0) {
        return null;
    }

    return (
        <section aria-label="Course categories" className="overflow-hidden py-6">
            <div className="marquee-track">
                <Group categories={categories} />
                <Group categories={categories} hidden />
            </div>
        </section>
    );
}