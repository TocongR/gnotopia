import { formatPrice } from '@/lib/format';
import { Link } from '@inertiajs/react';

export default function CourseCard({ course }) {
    return (
        <Link
            href={route('catalog.show', course.slug)}
            className="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition hover:shadow-md"
        >
            <div className="flex h-28 items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 text-4xl font-bold text-white/80">
                {course.title.charAt(0).toUpperCase()}
            </div>

            <div className="flex flex-1 flex-col p-4">
                <span className="text-xs font-medium uppercase tracking-wide text-indigo-600">{course.category}</span>
                <h3 className="mt-1 font-semibold text-gray-900">{course.title}</h3>
                <p className="mt-1 text-sm text-gray-500">by {course.instructor}</p>
                <p className="mt-2 flex-1 text-sm text-gray-600">{course.excerpt}</p>

                <div className="mt-4 flex items-center justify-between text-sm">
                    <span className="text-gray-500">{course.modules_count} module(s)</span>
                    <span className="font-semibold text-gray-900">{formatPrice(course.price_cents)}</span>
                </div>
            </div>
        </Link>
    );
}