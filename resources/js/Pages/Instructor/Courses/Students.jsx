import StatusBadge from '@/Components/course/StatusBadge';
import ProgressBar from '@/Components/ui/ProgressBar';
import Pagination from '@/Components/ui/Pagination';
import StatCard from '@/Components/ui/StatCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Students({ course, students, totals }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <h2 className="font-display text-xl font-semibold text-stone-900">Students: {course.title}</h2>
                        <StatusBadge status={course.status} />
                    </div>
                    <Link href={route('instructor.courses.edit', course.id)} className="text-sm text-stone-600 hover:underline">
                        Back to course
                    </Link>
                </div>
            }
        >
            <Head title={`Students: ${course.title}`} />

            <div className="py-10">
                <div className="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <StatCard label="Enrolled" value={totals.enrolled} />
                        <StatCard label="Completed" value={totals.completed} />
                    </div>

                    {students.data.length === 0 ? (
                        <p className="rounded-md border border-stone-200 bg-white p-6 text-stone-600">
                            No students have enrolled in this course yet.
                        </p>
                    ) : (
                        <div className="overflow-x-auto rounded-md border border-stone-200 bg-white">
                            <table className="min-w-full divide-y divide-stone-200 text-sm">
                                <thead className="bg-stone-50 text-left text-xs uppercase text-stone-500">
                                    <tr>
                                        <th className="px-4 py-3">Student</th>
                                        <th className="px-4 py-3">Enrolled</th>
                                        <th className="w-64 px-4 py-3">Progress</th>
                                        <th className="px-4 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-stone-200">
                                    {students.data.map((student) => (
                                        <tr key={student.id}>
                                            <td className="px-4 py-3 font-medium text-stone-900">{student.name}</td>
                                            <td className="px-4 py-3 text-stone-600">{student.enrolled_at}</td>
                                            <td className="px-4 py-3">
                                                <ProgressBar
                                                    percent={student.progress.percent}
                                                    label={`${student.name} progress`}
                                                />
                                                <p className="mt-1 text-xs text-stone-500">
                                                    {student.progress.done} of {student.progress.total} steps
                                                </p>
                                            </td>
                                            <td className="px-4 py-3">
                                                {student.completed_at ? (
                                                    <span className="rounded bg-brand-100 px-2 py-0.5 text-xs font-medium text-brand-800">
                                                        Completed {student.completed_at}
                                                    </span>
                                                ) : (
                                                    <span className="text-stone-600">In progress</span>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}

                    <Pagination links={students.links} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}