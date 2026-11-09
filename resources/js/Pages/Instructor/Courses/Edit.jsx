import CourseForm from '@/Components/course/CourseForm';
import StatusBadge from '@/Components/course/StatusBadge';
import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';

export default function Edit({ course, categories }) {
    const { errors: pageErrors } = usePage().props;

    const form = useForm({
        title: course.title,
        description: course.description ?? '',
        category_id: course.category_id,
        price: course.price,
    });

    const submit = (e) => {
        e.preventDefault();
        form.put(route('instructor.courses.update', course.id), { preserveScroll: true });
    };

    const publish = () =>
        router.post(route('instructor.courses.publish', course.id), {}, { preserveScroll: true });

    const unpublish = () =>
        router.delete(route('instructor.courses.unpublish', course.id), { preserveScroll: true });

    const destroy = () => {
        if (window.confirm('Delete this course and everything in it? This cannot be undone.')) {
            router.delete(route('instructor.courses.destroy', course.id));
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">Edit Course</h2>
                    <Link href={route('instructor.courses.index')} className="text-sm text-gray-600 hover:underline">
                        Back to my courses
                    </Link>
                </div>
            }
        >
            <Head title={`Edit: ${course.title}`} />

            <div className="py-12">
                <div className="mx-auto grid max-w-7xl gap-6 sm:px-6 lg:grid-cols-3 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-2">
                        <CourseForm
                            form={form}
                            categories={categories}
                            onSubmit={submit}
                            submitLabel="Save changes"
                        />
                    </div>

                    <div className="space-y-6">
                        <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 className="font-semibold text-gray-900">Status</h3>
                            <div className="mt-2 flex items-center gap-2">
                                <StatusBadge status={course.status} />
                                <span className="text-sm text-gray-600">{course.modules_count} module(s)</span>
                            </div>

                            <div className="mt-4">
                                {course.status === 'published' ? (
                                    <SecondaryButton onClick={unpublish}>Unpublish</SecondaryButton>
                                ) : (
                                    <PrimaryButton onClick={publish} disabled={course.status === 'suspended'}>
                                        Publish
                                    </PrimaryButton>
                                )}
                                <InputError message={pageErrors.status} className="mt-2" />
                            </div>
                        </div>

                        <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 className="font-semibold text-gray-900">Content</h3>
                            <p className="mt-2 text-sm text-gray-600">{course.modules_count} module(s)</p>
                            <Link
                                href={route('instructor.courses.content', course.id)}
                                className="mt-3 inline-block text-sm font-medium text-indigo-600 hover:underline"
                            >
                                Manage modules and lessons →
                            </Link>
                        </div>

                        <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 className="font-semibold text-gray-900">Danger zone</h3>
                            <div className="mt-4">
                                <DangerButton onClick={destroy}>Delete course</DangerButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}