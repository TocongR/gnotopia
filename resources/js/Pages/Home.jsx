import CourseCard from '@/Components/course/CourseCard';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Home({ courses, categories }) {
    const [q, setQ] = useState('');

    const search = (e) => {
        e.preventDefault();
        router.get(route('catalog.index'), q ? { q } : {});
    };

    return (
        <PublicLayout>
            <Head title="Learn something new" />

            <section className="bg-gradient-to-br from-indigo-600 to-purple-700 py-16 text-white">
                <div className="mx-auto max-w-3xl px-4 text-center">
                    <h1 className="text-4xl font-bold">Learn from real instructors. Earn real certificates.</h1>
                    <p className="mt-4 text-lg text-indigo-100">
                        Browse courses, learn at your own pace, and prove what you know.
                    </p>

                    <form onSubmit={search} className="mx-auto mt-8 flex max-w-xl gap-2">
                        <input
                            type="search"
                            value={q}
                            onChange={(e) => setQ(e.target.value)}
                            placeholder="What do you want to learn?"
                            className="w-full rounded-md border-0 text-gray-900 shadow-sm focus:ring-2 focus:ring-white"
                        />
                        <button
                            type="submit"
                            className="rounded-md bg-gray-900 px-5 py-2 text-sm font-semibold text-white hover:bg-gray-800"
                        >
                            Search
                        </button>
                    </form>
                </div>
            </section>

            <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                {categories.length > 0 && (
                    <div className="mb-10 flex flex-wrap gap-2">
                        {categories.map((category) => (
                            <Link
                                key={category.id}
                                href={route('catalog.index', { category: category.slug })}
                                className="rounded-full border border-gray-300 bg-white px-4 py-1.5 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                {category.name}
                            </Link>
                        ))}
                    </div>
                )}

                <div className="mb-6 flex items-end justify-between">
                    <h2 className="text-2xl font-bold text-gray-900">Latest courses</h2>
                    <Link href={route('catalog.index')} className="text-sm font-medium text-indigo-600 hover:underline">
                        View all →
                    </Link>
                </div>

                {courses.length === 0 ? (
                    <p className="text-gray-600">No courses have been published yet. Check back soon.</p>
                ) : (
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {courses.map((course) => (
                            <CourseCard key={course.id} course={course} />
                        ))}
                    </div>
                )}
            </div>
        </PublicLayout>
    );
}