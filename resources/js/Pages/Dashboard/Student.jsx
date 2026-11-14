import ProgressBar from '@/Components/ui/ProgressBar';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

const linkButton =
    'inline-flex items-center rounded-md bg-brand-800 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700';

export default function Student({ courses }) {
    return (
        <AuthenticatedLayout
            header={<h2 className="font-display text-xl font-semibold text-stone-900">My learning</h2>}
        >
            <Head title="My learning" />

            <div className="py-10">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    {courses.length === 0 ? (
                        <div className="rounded-md border border-stone-200 bg-white p-8 text-center">
                            <p className="text-stone-700">You have not enrolled in any courses yet.</p>
                            <Link href={route('catalog.index')} className={`${linkButton} mt-4`}>
                                Browse courses
                            </Link>
                        </div>
                    ) : (
                        <ul className="space-y-4">
                            {courses.map((course) => (
                                <li key={course.id} className="rounded-md border border-stone-200 bg-white p-5">
                                    <div className="flex flex-wrap items-start justify-between gap-4">
                                        <div>
                                            <p className="text-xs font-semibold uppercase tracking-wide text-brand-700">
                                                {course.category}
                                            </p>
                                            <h3 className="mt-1 font-display text-xl font-semibold text-stone-900">
                                                {course.title}
                                            </h3>
                                            <p className="text-sm text-stone-500">{course.instructor}</p>
                                        </div>

                                        {course.suspended ? (
                                            <span className="rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                                                Unavailable
                                            </span>
                                        ) : (
                                            <Link href={route('student.learn.show', course.slug)} className={linkButton}>
                                                {course.progress.percent === 0
                                                    ? 'Start'
                                                    : course.progress.percent === 100
                                                      ? 'Review'
                                                      : 'Continue'}
                                            </Link>
                                        )}
                                    </div>

                                    <div className="mt-4">
                                        <ProgressBar percent={course.progress.percent} label={`${course.title} progress`} />
                                        <p className="mt-1 text-xs text-stone-500">
                                            {course.progress.done} of {course.progress.total} steps complete
                                        </p>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}