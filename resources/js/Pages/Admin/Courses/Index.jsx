import StatusBadge from '@/Components/course/StatusBadge';
import Pagination from '@/Components/ui/Pagination';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { buttonPrimary } from '@/lib/buttons';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

// Build a query object that leaves out empty values, so URLs stay clean.
const query = (values) => Object.fromEntries(Object.entries(values).filter(([, value]) => value));

const smallButton = 'rounded border border-stone-300 px-2 py-1 text-xs text-stone-700 hover:bg-stone-50';

export default function Index({ courses, statuses, filters }) {
    const [q, setQ] = useState(filters.q);

    const search = (e) => {
        e.preventDefault();
        router.get(route('admin.courses.index'), query({ q, status: filters.status }), { preserveState: true });
    };

    const filterStatus = (status) =>
        router.get(route('admin.courses.index'), query({ q: filters.q, status }), { preserveState: true });

    const toggleSuspension = (course) => {
        if (course.status === 'suspended') {
            router.delete(route('admin.courses.restore', course.id), { preserveScroll: true });
        } else if (
            window.confirm(
                'Suspend this course? It disappears from the catalog and enrolled students lose access until it is restored.',
            )
        ) {
            router.post(route('admin.courses.suspend', course.id), {}, { preserveScroll: true });
        }
    };

    const chip = (active) =>
        `rounded-full border px-4 py-1.5 text-sm capitalize ${
            active ? 'border-brand-800 bg-brand-800 text-white' : 'border-stone-300 bg-white text-stone-700 hover:bg-stone-100'
        }`;

    return (
        <AuthenticatedLayout header={<h2 className="font-display text-xl font-semibold text-stone-900">Courses</h2>}>
            <Head title="Courses" />

            <div className="py-10">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    <form onSubmit={search} className="flex max-w-xl gap-2">
                        <input
                            type="search"
                            value={q}
                            onChange={(e) => setQ(e.target.value)}
                            placeholder="Search courses"
                            className="w-full rounded-md border-stone-300 shadow-sm focus:border-brand-600 focus:ring-brand-600"
                        />
                        <button type="submit" className={buttonPrimary}>
                            Search
                        </button>
                    </form>

                    <div className="flex flex-wrap gap-2">
                        <button type="button" className={chip(!filters.status)} onClick={() => filterStatus('')}>
                            All
                        </button>
                        {statuses.map((status) => (
                            <button
                                key={status}
                                type="button"
                                className={chip(filters.status === status)}
                                onClick={() => filterStatus(status)}
                            >
                                {status}
                            </button>
                        ))}
                    </div>

                    {courses.data.length === 0 ? (
                        <p className="rounded-md border border-stone-200 bg-white p-6 text-stone-600">
                            No courses match your search.
                        </p>
                    ) : (
                        <div className="overflow-x-auto rounded-md border border-stone-200 bg-white">
                            <table className="min-w-full divide-y divide-stone-200 text-sm">
                                <thead className="bg-stone-50 text-left text-xs uppercase text-stone-500">
                                    <tr>
                                        <th className="px-4 py-3">Course</th>
                                        <th className="px-4 py-3">Instructor</th>
                                        <th className="px-4 py-3">Category</th>
                                        <th className="px-4 py-3">Status</th>
                                        <th className="px-4 py-3">Students</th>
                                        <th className="px-4 py-3" />
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-stone-200">
                                    {courses.data.map((course) => (
                                        <tr key={course.id}>
                                            <td className="px-4 py-3 font-medium text-stone-900">{course.title}</td>
                                            <td className="px-4 py-3">
                                                <p className="text-stone-900">{course.instructor.name}</p>
                                                <p className="text-stone-500">{course.instructor.email}</p>
                                            </td>
                                            <td className="px-4 py-3 text-stone-600">{course.category}</td>
                                            <td className="px-4 py-3">
                                                <StatusBadge status={course.status} />
                                            </td>
                                            <td className="px-4 py-3 text-stone-600">{course.enrollments_count}</td>
                                            <td className="px-4 py-3 text-right">
                                                <button
                                                    type="button"
                                                    className={`${smallButton} ${course.status === 'suspended' ? '' : 'text-red-700'}`}
                                                    onClick={() => toggleSuspension(course)}
                                                >
                                                    {course.status === 'suspended' ? 'Restore' : 'Suspend'}
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}

                    <Pagination links={courses.links} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}