import CoverArt from '@/Components/course/CoverArt';
import { formatPrice } from '@/lib/format';
import { Link } from '@inertiajs/react';

export default function CourseCard({ course }) {
    return (
        <Link
            href={route('catalog.show', course.slug)}
            className="group flex flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white transition-colors hover:border-brand-700"
        >
            <div className="h-36 overflow-hidden">
                <CoverArt seed={course.id} />
            </div>

            <div className="flex flex-1 flex-col p-5">
                <span className="w-fit rounded-full bg-brand-100 px-3 py-1 text-xs font-semibold text-brand-900">
                    {course.category}
                </span>
                <h3 className="mt-3 font-display text-lg font-bold leading-snug text-stone-900 group-hover:text-brand-800">
                    {course.title}
                </h3>
                <p className="mt-1 text-sm text-stone-500">{course.instructor}</p>
                <p className="mt-3 flex-1 text-sm text-stone-600">{course.excerpt}</p>

                <div className="mt-5 flex items-center justify-between border-t border-stone-100 pt-3 text-sm">
                    <span className="text-stone-500">{course.modules_count} module(s)</span>
                    <span className="font-semibold text-stone-900">{formatPrice(course.price_cents)}</span>
                </div>
            </div>
        </Link>
    );
}