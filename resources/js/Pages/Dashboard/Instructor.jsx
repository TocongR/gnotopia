import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';

export default function Instructor() {
    const { auth } = usePage().props;

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Instructor Dashboard</h2>}
        >
            <Head title="Instructor Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        Welcome, {auth.user.name}. Your courses will appear here.
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}