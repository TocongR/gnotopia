import { Link } from '@inertiajs/react';

const clean = (label) => label.replace('&laquo;', '«').replace('&raquo;', '»');

export default function Pagination({ links }) {
    // Laravel always includes "Previous" and "Next"; 3 links or fewer means a single page.
    if (!links || links.length <= 3) {
        return null;
    }

    return (
        <nav className="mt-6 flex flex-wrap gap-1">
            {links.map((link, index) =>
                link.url ? (
                    <Link
                        key={index}
                        href={link.url}
                        preserveScroll
                        className={`rounded border px-3 py-1 text-sm ${
                            link.active
                                ? 'border-brand-600 bg-brand-600 text-white'
                                : 'border-stone-300 bg-white text-stone-700 hover:bg-stone-50'
                        }`}
                    >
                        {clean(link.label)}
                    </Link>
                ) : (
                    <span key={index} className="rounded border border-stone-200 px-3 py-1 text-sm text-stone-400">
                        {clean(link.label)}
                    </span>
                ),
            )}
        </nav>
    );
}