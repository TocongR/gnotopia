import CourseCard from '@/Components/course/CourseCard';
import Pagination from '@/Components/ui/Pagination';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

// Build a query object that leaves out empty values, so URLs stay clean.
const query = (values) => Object.fromEntries(Object.entries(values).filter(([, value]) => value));

export default function Index({ courses, categories, filters }) {
    const [q, setQ] = useState(filters.q);

    const search = (e) => {
        e.preventDefault();
        router.get(route('catalog.index'), query({ q, category: filters.category }), { preserveState: true });
    };

    const hasFilters = filters.q || filters.category;

    return (
        <PublicLayout>
            <Head title="Browse courses" />

            <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                <h1 className="text-3xl font-bold text-stone-900">Browse courses</h1>

                <form onSubmit={search} className="mt-6 flex max-w-xl gap-2">
                    <input
                        type="search"
                        value={q}
                        onChange={(e) => setQ(e.target.value)}
                        placeholder="Search courses"
                        className="w-full rounded-md border-stone-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                    />
                    <button
                        type="submit"
                        className="rounded-md bg-brand-800 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700"
                    >
                        Search
                    </button>
                </form>

                <div className="mt-4 flex flex-wrap gap-2">
                    <Link
                        href={route('catalog.index', query({ q: filters.q }))}
                        className={`rounded-full border px-4 py-1.5 text-sm ${
                            !filters.category
                                ? 'border-brand-600 bg-brand-600 text-white'
                                : 'border-stone-300 bg-white text-stone-700 hover:bg-stone-100'
                        }`}
                    >
                        All
                    </Link>
                    {categories.map((category) => (
                        <Link
                            key={category.id}
                            href={route('catalog.index', query({ q: filters.q, category: category.slug }))}
                            className={`rounded-full border px-4 py-1.5 text-sm ${
                                filters.category === category.slug
                                    ? 'border-brand-600 bg-brand-600 text-white'
                                    : 'border-stone-300 bg-white text-stone-700 hover:bg-stone-100'
                            }`}
                        >
                            {category.name}
                        </Link>
                    ))}
                </div>

                <div className="mt-8">
                    {courses.data.length === 0 ? (
                        <div className="rounded-lg bg-white p-8 text-center text-stone-600 shadow-sm">
                            <p>No courses match your search.</p>
                            {hasFilters && (
                                <Link
                                    href={route('catalog.index')}
                                    className="mt-3 inline-block text-sm font-medium text-brand-600 hover:underline"
                                >
                                    Clear filters
                                </Link>
                            )}
                        </div>
                    ) : (
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {courses.data.map((course) => (
                                <CourseCard key={course.id} course={course} />
                            ))}
                        </div>
                    )}

                    <Pagination links={courses.links} />
                </div>
            </div>
        </PublicLayout>
    );
}