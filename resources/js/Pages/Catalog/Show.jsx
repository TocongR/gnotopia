import PublicLayout from '@/Layouts/PublicLayout';
import { formatPrice } from '@/lib/format';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

const kindLabels = { article: 'Article', video: 'Video', quiz: 'Quiz' };

const primaryLink =
    'block rounded-md bg-brand-800 px-4 py-2 text-center text-sm font-medium text-white hover:bg-brand-700';

function EnrollPanel({ course, viewer, loggedIn }) {
    const form = useForm({});

    if (!loggedIn) {
        return (
            <div className="mt-4 space-y-2">
                <Link href={route('register')} className={primaryLink}>
                    Create an account to enroll
                </Link>
                <Link href={route('login')} className="block text-center text-sm text-stone-600 hover:underline">
                    Already have an account? Log in
                </Link>
            </div>
        );
    }

    if (!viewer.is_student) {
        return (
            <p className="mt-4 rounded-md bg-stone-100 p-3 text-sm text-stone-600">
                Enrolling is available to student accounts.
            </p>
        );
    }

    if (viewer.enrolled) {
        return (
            <Link href={route('student.learn.show', course.slug)} className={`${primaryLink} mt-4`}>
                Continue learning
            </Link>
        );
    }

    if (viewer.can_enroll) {
        return (
            <button
                type="button"
                disabled={form.processing}
                onClick={() => form.post(route('student.courses.enroll', course.slug))}
                className={`${primaryLink} mt-4 w-full disabled:opacity-50`}
            >
                Enroll for free
            </button>
        );
    }

    return (
        <p className="mt-4 rounded-md bg-stone-100 p-3 text-sm text-stone-600">
            Paid courses cannot be purchased yet. Payments open in a later update.
        </p>
    );
}

export default function Show({ course, viewer }) {
    const { auth } = usePage().props;

    return (
        <PublicLayout>
            <Head title={course.title} />

            <div className="bg-brand-900 py-10 text-white">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Link
                        href={route('catalog.index', { category: course.category.slug })}
                        className="text-sm font-medium uppercase tracking-wide text-brand-200 hover:underline"
                    >
                        {course.category.name}
                    </Link>
                    <h1 className="mt-2 font-display text-3xl font-semibold">{course.title}</h1>
                    <p className="mt-2 text-brand-100">
                        by {course.instructor.name} · Updated {course.updated_at}
                    </p>
                </div>
            </div>

            <div className="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-3 lg:px-8">
                <div className="space-y-10 lg:col-span-2">
                    <section>
                        <h2 className="font-display text-xl font-semibold text-stone-900">About this course</h2>
                        <p className="mt-3 whitespace-pre-line text-stone-700">
                            {course.description || 'No description yet.'}
                        </p>
                    </section>

                    <section>
                        <h2 className="font-display text-xl font-semibold text-stone-900">Course content</h2>
                        <p className="mt-1 text-sm text-stone-500">
                            {course.modules.length} module(s) · {course.lessons_count} lesson(s) · {course.quizzes_count}{' '}
                            quiz(zes)
                        </p>

                        <div className="mt-4 space-y-3">
                            {course.modules.map((courseModule, index) => (
                                <details
                                    key={courseModule.id}
                                    className="rounded-md border border-stone-200 bg-white"
                                    open={index === 0}
                                >
                                    <summary className="cursor-pointer px-4 py-3 font-semibold text-stone-900">
                                        {index + 1}. {courseModule.title}
                                    </summary>
                                    <ul className="divide-y divide-stone-100 border-t border-stone-100">
                                        {courseModule.items.map((item) => (
                                            <li key={item.key} className="flex items-center gap-3 px-4 py-2 text-sm">
                                                <span className="w-16 shrink-0 rounded bg-stone-100 px-2 py-0.5 text-center text-xs text-stone-700">
                                                    {kindLabels[item.kind] ?? item.kind}
                                                </span>
                                                <span className="text-stone-800">{item.title}</span>
                                            </li>
                                        ))}
                                    </ul>
                                </details>
                            ))}
                        </div>
                    </section>
                </div>

                <aside>
                    <div className="sticky top-6 rounded-md border border-stone-200 bg-white p-6">
                        <p className="font-display text-3xl font-semibold text-stone-900">
                            {formatPrice(course.price_cents)}
                        </p>

                        <EnrollPanel course={course} viewer={viewer} loggedIn={Boolean(auth.user)} />
                    </div>
                </aside>
            </div>
        </PublicLayout>
    );
}