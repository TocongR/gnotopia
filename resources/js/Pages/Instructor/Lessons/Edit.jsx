import LessonForm from '@/Components/course/LessonForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Edit({ course, module: parentModule, lesson }) {
    const form = useForm({
        title: lesson.title,
        type: lesson.type,
        video_url: lesson.video_url ?? '',
        body: lesson.body ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        form.put(route('instructor.lessons.update', lesson.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Edit lesson in "{parentModule.title}"
                </h2>
            }
        >
            <Head title={`Edit: ${lesson.title}`} />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <LessonForm
                            form={form}
                            onSubmit={submit}
                            submitLabel="Save lesson"
                            cancelHref={route('instructor.courses.content', course.id)}
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}