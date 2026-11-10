import PublicLayout from '@/Layouts/PublicLayout';
import { formatPrice } from '@/lib/format';
import { Head, Link, usePage } from '@inertiajs/react';

const kindLabels = { article: 'Article', video: 'Video', quiz: 'Quiz' };

export default function Show({ course }) {
    const { auth } = usePage().props;

    return (
        <PublicLayout>
            <Head title={course.title} />

            <div className="bg-gray-900 py-10 text-white">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Link
                        href={route('catalog.index', { category: course.category.slug })}
                        className="text-sm font-medium uppercase tracking-wide text-indigo-300 hover:underline"
                    >
                        {course.category.name}
                    </Link>
                    <h1 className="mt-2 text-3xl font-bold">{course.title}</h1>
                    <p className="mt-2 text-gray-300">
                        by {course.instructor.name} · Updated {course.updated_at}
                    </p>
                </div>
            </div>

            <div className="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-3 lg:px-8">
                <div className="space-y-10 lg:col-span-2">
                    <section>
                        <h2 className="text-xl font-bold text-gray-900">About this course</h2>
                        <p className="mt-3 whitespace-pre-line text-gray-700">
                            {course.description || 'No description yet.'}
                        </p>
                    </section>

                    <section>
                        <h2 className="text-xl font-bold text-gray-900">Course content</h2>
                        <p className="mt-1 text-sm text-gray-500">
                            {course.modules.length} module(s) · {course.lessons_count} lesson(s) · {course.quizzes_count}{' '}
                            quiz(zes)
                        </p>

                        <div className="mt-4 space-y-3">
                            {course.modules.map((courseModule, index) => (
                                <details
                                    key={courseModule.id}
                                    className="rounded-lg border border-gray-200 bg-white"
                                    open={index === 0}
                                >
                                    <summary className="cursor-pointer px-4 py-3 font-semibold text-gray-900">
                                        {index + 1}. {courseModule.title}
                                    </summary>
                                    <ul className="divide-y divide-gray-100 border-t border-gray-100">
                                        {courseModule.items.map((item) => (
                                            <li key={item.key} className="flex items-center gap-3 px-4 py-2 text-sm">
                                                <span className="w-16 shrink-0 rounded bg-gray-100 px-2 py-0.5 text-center text-xs text-gray-700">
                                                    {kindLabels[item.kind] ?? item.kind}
                                                </span>
                                                <span className="text-gray-800">{item.title}</span>
                                            </li>
                                        ))}
                                    </ul>
                                </details>
                            ))}
                        </div>
                    </section>
                </div>

                <aside>
                    <div className="sticky top-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <p className="text-3xl font-bold text-gray-900">{formatPrice(course.price_cents)}</p>

                        {/* Step 6 replaces this panel with a real Enroll button. */}
                        {auth.user ? (
                            <p className="mt-4 rounded-md bg-gray-100 p-3 text-sm text-gray-600">
                                Enrollment opens in the next update.
                            </p>
                        ) : (
                            <div className="mt-4 space-y-2">
                                <Link
                                    href={route('register')}
                                    className="block rounded-md bg-indigo-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-indigo-500"
                                >
                                    Create an account to enroll
                                </Link>
                                <Link
                                    href={route('login')}
                                    className="block text-center text-sm text-gray-600 hover:underline"
                                >
                                    Already have an account? Log in
                                </Link>
                            </div>
                        )}
                    </div>
                </aside>
            </div>
        </PublicLayout>
    );
}