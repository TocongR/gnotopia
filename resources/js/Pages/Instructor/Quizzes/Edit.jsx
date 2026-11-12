import QuizForm from '@/Components/course/QuizForm';
import DangerButton from '@/Components/DangerButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';

export default function Edit({ course, module: parentModule, quiz }) {
    const form = useForm({
        title: quiz.title,
        pass_percent: quiz.pass_percent,
        questions: quiz.questions,
    });

    const submit = (e) => {
        e.preventDefault();
        form.put(route('instructor.quizzes.update', quiz.id));
    };

    const destroy = () => {
        if (window.confirm('Delete this quiz and all of its questions?')) {
            router.delete(route('instructor.quizzes.destroy', quiz.id));
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-stone-800">
                        Edit quiz in "{parentModule.title}"
                    </h2>
                    <DangerButton onClick={destroy}>Delete quiz</DangerButton>
                </div>
            }
        >
            <Head title={`Edit: ${quiz.title}`} />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <QuizForm
                            form={form}
                            onSubmit={submit}
                            submitLabel="Save quiz"
                            cancelHref={route('instructor.courses.content', course.id)}
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}