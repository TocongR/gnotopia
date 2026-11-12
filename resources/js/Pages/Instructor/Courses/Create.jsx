import CourseForm from '@/Components/course/CourseForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ categories }) {
    const form = useForm({
        title: '',
        description: '',
        category_id: '',
        price: '0',
    });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('instructor.courses.store'));
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-stone-800">New Course</h2>}
        >
            <Head title="New Course" />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <CourseForm
                            form={form}
                            categories={categories}
                            onSubmit={submit}
                            submitLabel="Create course"
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}