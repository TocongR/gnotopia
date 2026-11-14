import StatCard from '@/Components/ui/StatCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { buttonPrimary, buttonSecondary } from '@/lib/buttons';
import { Head, Link } from '@inertiajs/react';

function Group({ title, children }) {
    return (
        <section>
            <h3 className="mb-3 font-display text-lg font-semibold text-stone-900">{title}</h3>
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">{children}</div>
        </section>
    );
}

export default function Admin({ users, courses, enrollments, completions }) {
    return (
        <AuthenticatedLayout
            header={<h2 className="font-display text-xl font-semibold text-stone-900">Admin dashboard</h2>}
        >
            <Head title="Admin dashboard" />

            <div className="py-10">
                <div className="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                    <Group title="People">
                        <StatCard label="Students" value={users.students} />
                        <StatCard label="Instructors" value={users.instructors} />
                        <StatCard label="Admins" value={users.admins} />
                        <StatCard label="Suspended accounts" value={users.suspended} />
                    </Group>

                    <Group title="Courses">
                        <StatCard label="Published" value={courses.published} />
                        <StatCard label="Drafts" value={courses.draft} />
                        <StatCard label="Suspended" value={courses.suspended} />
                    </Group>

                    <Group title="Learning">
                        <StatCard label="Enrollments" value={enrollments} />
                        <StatCard label="Completions" value={completions} />
                    </Group>

                    <div className="flex gap-3">
                        <Link href={route('admin.users.index')} className={buttonPrimary}>
                            Manage users
                        </Link>
                        <Link href={route('admin.courses.index')} className={buttonSecondary}>
                            Manage courses
                        </Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}