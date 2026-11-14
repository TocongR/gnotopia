import CourseSidebar from '@/Components/learn/CourseSidebar';
import ProgressBar from '@/Components/ui/ProgressBar';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function LearnShell({ course, modules, progress, currentKey, title, children }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between gap-4">
                    <h2 className="font-display text-xl font-semibold text-stone-900">{course.title}</h2>
                    <Link href={route('student.dashboard')} className="text-sm text-stone-600 hover:underline">
                        My learning
                    </Link>
                </div>
            }
        >
            <Head title={`${title} · ${course.title}`} />

            <div className="py-8">
                <div className="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-4 lg:px-8">
                    <div className="lg:col-span-3">
                        {progress.percent === 100 && (
                            <p role="status" className="mb-6 rounded-md bg-brand-50 p-4 text-sm font-medium text-brand-900">
                                ✓ You have completed this course.
                            </p>
                        )}
                        {children}
                    </div>

                    <aside className="space-y-4 lg:order-first lg:col-span-1">
                        <div className="rounded-md border border-stone-200 bg-white p-4">
                            <p className="text-sm font-medium text-stone-900">
                                {progress.done} of {progress.total} steps complete
                            </p>
                            <div className="mt-2">
                                <ProgressBar percent={progress.percent} label="Course progress" />
                            </div>
                        </div>

                        <CourseSidebar modules={modules} currentKey={currentKey} />
                    </aside>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}