import ProgressBar from '@/Components/ui/ProgressBar';
import StatCard from '@/Components/ui/StatCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { buttonPrimary } from '@/lib/buttons';
import { Head, Link } from '@inertiajs/react';

export default function Student({ courses }) {
    const completed = courses.filter((course) => course.completed).length;

    const actionLabel = (course) => {
        if (course.completed) {
            return 'Review';
        }

        return course.progress.percent === 0 ? 'Start' : 'Continue';
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-display text-xl font-semibold text-stone-900">My learning</h2>}
        >
            <Head title="My learning" />

            <div className="py-10">
                <div className="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
                    {courses.length === 0 ? (
                        <div className="rounded-md border border-stone-200 bg-white p-8 text-center">
                            <p className="text-stone-700">You have not enrolled in any courses yet.</p>
                            <Link href={route('catalog.index')} className={`${buttonPrimary} mt-4`}>
                                Browse courses
                            </Link>
                        </div>
                    ) : (
                        <>
                            <div className="grid gap-4 sm:grid-cols-3">
                                <StatCard label="Enrolled" value={courses.length} />
                                <StatCard label="In progress" value={courses.length - completed} />
                                <StatCard label="Completed" value={completed} />
                            </div>

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

                                            <div className="flex items-center gap-3">
                                                {course.completed && (
                                                    <span className="rounded bg-brand-100 px-2 py-0.5 text-xs font-medium text-brand-800">
                                                        Completed {course.completed_at}
                                                    </span>
                                                )}

                                                {course.suspended ? (
                                                    <span className="rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                                                        Unavailable
                                                    </span>
                                                ) : (
                                                    <Link href={route('student.learn.show', course.slug)} className={buttonPrimary}>
                                                        {actionLabel(course)}
                                                    </Link>
                                                )}
                                            </div>
                                        </div>

                                        <div className="mt-4">
                                            <ProgressBar
                                                percent={course.progress.percent}
                                                label={`${course.title} progress`}
                                            />
                                            <p className="mt-1 text-xs text-stone-500">
                                                {course.progress.done} of {course.progress.total} steps complete
                                            </p>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}