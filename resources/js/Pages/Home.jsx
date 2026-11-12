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
            <Head title="Courses from real instructors" />

            <section className="bg-brand-900 text-white">
                <div className="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-5 lg:px-8 lg:py-20">
                    <div className="lg:col-span-3">
                        <h1 className="font-display text-4xl font-semibold leading-tight sm:text-5xl">
                            Structured courses from real instructors.
                        </h1>
                        <p className="mt-4 max-w-xl text-lg text-brand-100">
                            Pick a course, work through it at your own pace, and check what you learned with quizzes.
                        </p>

                        <form onSubmit={search} className="mt-8 flex max-w-xl gap-2">
                            <input
                                type="search"
                                value={q}
                                onChange={(e) => setQ(e.target.value)}
                                placeholder="Search courses"
                                className="w-full rounded-md border-0 text-stone-900 focus:ring-2 focus:ring-brand-300"
                            />
                            <button
                                type="submit"
                                className="rounded-md bg-brand-600 px-5 py-2 text-sm font-medium text-white hover:bg-brand-500"
                            >
                                Search
                            </button>
                        </form>
                    </div>

                    {categories.length > 0 && (
                        <div className="lg:col-span-2">
                            <h2 className="text-sm font-semibold uppercase tracking-wide text-brand-200">
                                Browse by category
                            </h2>
                            <ul className="mt-3 divide-y divide-brand-700 border-y border-brand-700">
                                {categories.map((category) => (
                                    <li key={category.id}>
                                        <Link
                                            href={route('catalog.index', { category: category.slug })}
                                            className="flex items-center justify-between py-3 text-brand-50 hover:text-white"
                                        >
                                            {category.name}
                                            <span aria-hidden="true">→</span>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </div>
            </section>

            <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="mb-6 flex items-end justify-between">
                    <h2 className="font-display text-2xl font-semibold text-stone-900">Latest courses</h2>
                    <Link href={route('catalog.index')} className="text-sm font-medium text-brand-700 hover:underline">
                        View all courses →
                    </Link>
                </div>

                {courses.length === 0 ? (
                    <p className="text-stone-600">No courses have been published yet. Check back soon.</p>
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