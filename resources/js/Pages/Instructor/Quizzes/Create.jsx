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
                <h2 className="text-xl font-semibold leading-tight text-stone-800">
                    New quiz in "{parentModule.title}"
                </h2>
            }
        >
            <Head title="New Quiz" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
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