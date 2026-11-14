import StatusBadge from '@/Components/course/StatusBadge';
import StatCard from '@/Components/ui/StatCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { buttonPrimary } from '@/lib/buttons';
import { Head, Link } from '@inertiajs/react';

export default function Instructor({ stats, courses, recent }) {
    return (
        <AuthenticatedLayout
            header={<h2 className="font-display text-xl font-semibold text-stone-900">Instructor dashboard</h2>}
        >
            <Head title="Instructor dashboard" />

            <div className="py-10">
                <div className="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <StatCard label="Courses" value={stats.courses} />
                        <StatCard label="Published" value={stats.published} />
                        <StatCard label="Enrollments" value={stats.enrollments} />
                        <StatCard label="Completions" value={stats.completions} />
                    </div>

                    {courses.length === 0 ? (
                        <div className="rounded-md border border-stone-200 bg-white p-8 text-center">
                            <p className="text-stone-700">You have not created any courses yet.</p>
                            <Link href={route('instructor.courses.create')} className={`${buttonPrimary} mt-4`}>
                                Create your first course
                            </Link>
                        </div>
                    ) : (
                        <div className="grid gap-8 lg:grid-cols-3">
                            <section className="lg:col-span-2">
                                <div className="mb-3 flex items-end justify-between">
                                    <h3 className="font-display text-lg font-semibold text-stone-900">Your courses</h3>
                                    <Link
                                        href={route('instructor.courses.index')}
                                        className="text-sm font-medium text-brand-700 hover:underline"
                                    >
                                        Manage courses →
                                    </Link>
                                </div>

                                <div className="overflow-x-auto rounded-md border border-stone-200 bg-white">
                                    <table className="min-w-full divide-y divide-stone-200 text-sm">
                                        <thead className="bg-stone-50 text-left text-xs uppercase text-stone-500">
                                            <tr>
                                                <th className="px-4 py-3">Course</th>
                                                <th className="px-4 py-3">Status</th>
                                                <th className="px-4 py-3">Enrolled</th>
                                                <th className="px-4 py-3">Completed</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-stone-200">
                                            {courses.map((course) => (
                                                <tr key={course.id}>
                                                    <td className="px-4 py-3 font-medium">
                                                        <Link
                                                            href={route('instructor.courses.students', course.id)}
                                                            className="text-brand-700 hover:underline"
                                                        >
                                                            {course.title}
                                                        </Link>
                                                    </td>
                                                    <td className="px-4 py-3">
                                                        <StatusBadge status={course.status} />
                                                    </td>
                                                    <td className="px-4 py-3">{course.enrollments}</td>
                                                    <td className="px-4 py-3">{course.completions}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </section>

                            <section>
                                <h3 className="mb-3 font-display text-lg font-semibold text-stone-900">
                                    Recent enrollments
                                </h3>

                                {recent.length === 0 ? (
                                    <p className="rounded-md border border-stone-200 bg-white p-4 text-sm text-stone-600">
                                        No students have enrolled yet.
                                    </p>
                                ) : (
                                    <ul className="divide-y divide-stone-200 rounded-md border border-stone-200 bg-white">
                                        {recent.map((item) => (
                                            <li key={item.id} className="px-4 py-3 text-sm">
                                                <p className="font-medium text-stone-900">{item.student}</p>
                                                <p className="text-stone-600">{item.course}</p>
                                                <p className="mt-1 text-xs text-stone-500">
                                                    {item.completed ? 'Completed · ' : ''}
                                                    Enrolled {item.enrolled_at}
                                                </p>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </section>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}