import StatusBadge from '@/Components/course/StatusBadge';
import Pagination from '@/Components/ui/Pagination';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatPrice } from '@/lib/format';
import { Head, Link } from '@inertiajs/react';

export default function Index({ courses }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">My Courses</h2>
                    <Link
                        href={route('instructor.courses.create')}
                        className="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
                    >
                        New course
                    </Link>
                </div>
            }
        >
            <Head title="My Courses" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        {courses.data.length === 0 ? (
                            <p className="p-6 text-gray-600">You haven't created any courses yet.</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead className="bg-gray-50 text-left text-xs uppercase text-gray-500">
                                        <tr>
                                            <th className="px-6 py-3">Title</th>
                                            <th className="px-6 py-3">Category</th>
                                            <th className="px-6 py-3">Modules</th>
                                            <th className="px-6 py-3">Price</th>
                                            <th className="px-6 py-3">Status</th>
                                            <th className="px-6 py-3">Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200">
                                        {courses.data.map((course) => (
                                            <tr key={course.id}>
                                                <td className="px-6 py-4 font-medium">
                                                    <Link
                                                        href={route('instructor.courses.edit', course.id)}
                                                        className="text-indigo-600 hover:underline"
                                                    >
                                                        {course.title}
                                                    </Link>
                                                </td>
                                                <td className="px-6 py-4">{course.category}</td>
                                                <td className="px-6 py-4">{course.modules_count}</td>
                                                <td className="px-6 py-4">{formatPrice(course.price_cents)}</td>
                                                <td className="px-6 py-4">
                                                    <StatusBadge status={course.status} />
                                                </td>
                                                <td className="px-6 py-4 text-gray-500">{course.updated_at}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>

                    <Pagination links={courses.links} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}