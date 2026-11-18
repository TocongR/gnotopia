import LessonForm from '@/Components/course/LessonForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ course, module: parentModule }) {
    const form = useForm({ title: '', type: 'article', video_url: '', body: '' });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('instructor.modules.lessons.store', parentModule.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="font-display text-2xl font-extrabold tracking-tight text-stone-900">
                    New lesson in "{parentModule.title}"
                </h2>
            }
        >
            <Head title="New Lesson" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 border border-stone-200 sm:rounded-2xl">
                        <LessonForm
                            form={form}
                            onSubmit={submit}
                            submitLabel="Add lesson"
                            cancelHref={route('instructor.courses.content', course.id)}
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}