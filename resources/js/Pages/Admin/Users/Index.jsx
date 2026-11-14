import Pagination from '@/Components/ui/Pagination';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { buttonPrimary } from '@/lib/buttons';
import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

// Build a query object that leaves out empty values, so URLs stay clean.
const query = (values) => Object.fromEntries(Object.entries(values).filter(([, value]) => value));

const smallButton = 'rounded border border-stone-300 px-2 py-1 text-xs text-stone-700 hover:bg-stone-50';

export default function Index({ users, roles, filters }) {
    const { errors } = usePage().props;
    const [q, setQ] = useState(filters.q);

    const search = (e) => {
        e.preventDefault();
        router.get(route('admin.users.index'), query({ q, role: filters.role }), { preserveState: true });
    };

    const filterRole = (role) =>
        router.get(route('admin.users.index'), query({ q: filters.q, role }), { preserveState: true });

    const changeRole = (user, role) => {
        if (role !== user.role && window.confirm(`Change ${user.name} from ${user.role} to ${role}?`)) {
            router.put(route('admin.users.role', user.id), { role }, { preserveScroll: true });
        }
    };

    const toggleSuspension = (user) => {
        if (user.suspended) {
            router.delete(route('admin.users.unsuspend', user.id), { preserveScroll: true });
        } else if (window.confirm(`Suspend ${user.name}? They will be signed out and cannot log in.`)) {
            router.post(route('admin.users.suspend', user.id), {}, { preserveScroll: true });
        }
    };

    const chip = (active) =>
        `rounded-full border px-4 py-1.5 text-sm capitalize ${
            active ? 'border-brand-800 bg-brand-800 text-white' : 'border-stone-300 bg-white text-stone-700 hover:bg-stone-100'
        }`;

    return (
        <AuthenticatedLayout header={<h2 className="font-display text-xl font-semibold text-stone-900">Users</h2>}>
            <Head title="Users" />

            <div className="py-10">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    {errors.role && <div className="rounded-md bg-red-50 p-4 text-sm text-red-800">{errors.role}</div>}

                    <form onSubmit={search} className="flex max-w-xl gap-2">
                        <input
                            type="search"
                            value={q}
                            onChange={(e) => setQ(e.target.value)}
                            placeholder="Search by name or email"
                            className="w-full rounded-md border-stone-300 shadow-sm focus:border-brand-600 focus:ring-brand-600"
                        />
                        <button type="submit" className={buttonPrimary}>
                            Search
                        </button>
                    </form>

                    <div className="flex flex-wrap gap-2">
                        <button type="button" className={chip(!filters.role)} onClick={() => filterRole('')}>
                            All
                        </button>
                        {roles.map((role) => (
                            <button
                                key={role}
                                type="button"
                                className={chip(filters.role === role)}
                                onClick={() => filterRole(role)}
                            >
                                {role}
                            </button>
                        ))}
                    </div>

                    {users.data.length === 0 ? (
                        <p className="rounded-md border border-stone-200 bg-white p-6 text-stone-600">
                            No users match your search.
                        </p>
                    ) : (
                        <div className="overflow-x-auto rounded-md border border-stone-200 bg-white">
                            <table className="min-w-full divide-y divide-stone-200 text-sm">
                                <thead className="bg-stone-50 text-left text-xs uppercase text-stone-500">
                                    <tr>
                                        <th className="px-4 py-3">User</th>
                                        <th className="px-4 py-3">Role</th>
                                        <th className="px-4 py-3">Activity</th>
                                        <th className="px-4 py-3">Status</th>
                                        <th className="px-4 py-3">Joined</th>
                                        <th className="px-4 py-3" />
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-stone-200">
                                    {users.data.map((user) => (
                                        <tr key={user.id}>
                                            <td className="px-4 py-3">
                                                <p className="font-medium text-stone-900">
                                                    {user.name}
                                                    {user.is_self && <span className="ml-2 text-xs text-stone-500">(you)</span>}
                                                </p>
                                                <p className="text-stone-500">{user.email}</p>
                                            </td>
                                            <td className="px-4 py-3">
                                                {user.is_self ? (
                                                    <span className="capitalize">{user.role}</span>
                                                ) : (
                                                    <select
                                                        aria-label={`Role for ${user.name}`}
                                                        value={user.role}
                                                        onChange={(e) => changeRole(user, e.target.value)}
                                                        className="rounded-md border-stone-300 py-1 text-sm capitalize focus:border-brand-600 focus:ring-brand-600"
                                                    >
                                                        {roles.map((role) => (
                                                            <option key={role} value={role}>
                                                                {role}
                                                            </option>
                                                        ))}
                                                    </select>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-stone-600">
                                                {user.courses_count} course(s) · {user.enrollments_count} enrollment(s)
                                            </td>
                                            <td className="px-4 py-3">
                                                {user.suspended ? (
                                                    <span className="rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                                                        Suspended
                                                    </span>
                                                ) : (
                                                    <span className="text-stone-600">Active</span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-stone-500">{user.joined}</td>
                                            <td className="px-4 py-3 text-right">
                                                {!user.is_self && (
                                                    <button
                                                        type="button"
                                                        className={`${smallButton} ${user.suspended ? '' : 'text-red-700'}`}
                                                        onClick={() => toggleSuspension(user)}
                                                    >
                                                        {user.suspended ? 'Unsuspend' : 'Suspend'}
                                                    </button>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}

                    <Pagination links={users.links} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}