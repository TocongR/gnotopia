import QuizForm, { blankQuestion } from '@/Components/course/QuizForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ course, module: parentModule }) {
    const form = useForm({
        title: '',
        pass_percent: 70,
        questions: [blankQuestion()],
    });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('instructor.modules.quizzes.store', parentModule.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="font-display text-2xl font-extrabold tracking-tight text-stone-900">
                    New quiz in "{parentModule.title}"
                </h2>
            }
        >
            <Head title="New Quiz" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 border border-stone-200 sm:rounded-2xl">
                        <QuizForm
                            form={form}
                            onSubmit={submit}
                            submitLabel="Add quiz"
                            cancelHref={route('instructor.courses.content', course.id)}
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}