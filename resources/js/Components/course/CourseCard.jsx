import { formatPrice } from '@/lib/format';
import { Link } from '@inertiajs/react';

export default function CourseCard({ course }) {
    return (
        <Link
            href={route('catalog.show', course.slug)}
            className="group flex flex-col rounded-md border border-stone-200 bg-white p-5 transition-colors hover:border-brand-600"
        >
            <span className="text-xs font-semibold uppercase tracking-wide text-brand-700">{course.category}</span>
            <h3 className="mt-2 font-display text-xl font-semibold leading-snug text-stone-900 group-hover:text-brand-800">
                {course.title}
            </h3>
            <p className="mt-1 text-sm text-stone-500">{course.instructor}</p>
            <p className="mt-3 flex-1 text-sm text-stone-600">{course.excerpt}</p>

            <div className="mt-5 flex items-center justify-between border-t border-stone-100 pt-3 text-sm">
                <span className="text-stone-500">{course.modules_count} module(s)</span>
                <span className="font-semibold text-stone-900">{formatPrice(course.price_cents)}</span>
            </div>
        </Link>
    );
}