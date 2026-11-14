import InputError from '@/Components/InputError';
import LearnShell from '@/Components/learn/LearnShell';
import StepNav from '@/Components/learn/StepNav';
import { buttonPrimary } from '@/lib/buttons';
import { Link, useForm } from '@inertiajs/react';

export default function Quiz({ course, modules, progress, quiz, attempts, prev, next }) {
    const form = useForm({ answers: {} });

    const answered = Object.keys(form.data.answers).length;
    const complete = answered === quiz.questions.length;

    const choose = (questionId, optionId) =>
        form.setData('answers', { ...form.data.answers, [questionId]: optionId });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('student.quizzes.attempts.store', [course.slug, quiz.id]));
    };

    return (
        <LearnShell
            course={course}
            modules={modules}
            progress={progress}
            currentKey={`quiz-${quiz.id}`}
            title={quiz.title}
        >
            <h1 className="font-display text-3xl font-semibold text-stone-900">{quiz.title}</h1>
            <p className="mt-2 text-sm text-stone-600">
                {quiz.questions.length} question(s) · You need {quiz.pass_percent}% to pass. You can retake it as often as
                you like.
            </p>

            {quiz.passed && (
                <p className="mt-4 rounded-md bg-brand-50 p-3 text-sm font-medium text-brand-900">
                    ✓ You have already passed this quiz.
                </p>
            )}

            <form onSubmit={submit} className="mt-6 space-y-6">
                {quiz.questions.map((question, index) => (
                    <fieldset key={question.id} className="rounded-md border border-stone-200 bg-white p-5">
                        <legend className="px-1 font-medium text-stone-900">
                            {index + 1}. {question.body}
                        </legend>

                        <div className="mt-2 space-y-2">
                            {question.options.map((option) => (
                                <label
                                    key={option.id}
                                    className={`flex cursor-pointer items-start gap-3 rounded-md border px-3 py-2 text-sm ${
                                        form.data.answers[question.id] === option.id
                                            ? 'border-brand-600 bg-brand-50'
                                            : 'border-stone-200 hover:bg-stone-50'
                                    }`}
                                >
                                    <input
                                        type="radio"
                                        name={`question-${question.id}`}
                                        value={option.id}
                                        checked={form.data.answers[question.id] === option.id}
                                        onChange={() => choose(question.id, option.id)}
                                        className="mt-0.5 text-brand-700 focus:ring-brand-600"
                                    />
                                    <span className="text-stone-800">{option.body}</span>
                                </label>
                            ))}
                        </div>
                    </fieldset>
                ))}

                <InputError message={form.errors.answers} />

                <div className="flex flex-wrap items-center gap-4">
                    <button type="submit" disabled={!complete || form.processing} className={buttonPrimary}>
                        Submit answers
                    </button>
                    <span className="text-sm text-stone-500">
                        {answered} of {quiz.questions.length} answered
                    </span>
                </div>
            </form>

            {attempts.length > 0 && (
                <section className="mt-10">
                    <h2 className="font-display text-xl font-semibold text-stone-900">Your previous attempts</h2>
                    <ul className="mt-3 divide-y divide-stone-200 rounded-md border border-stone-200 bg-white">
                        {attempts.map((attempt) => (
                            <li key={attempt.id} className="flex items-center justify-between gap-4 px-4 py-3 text-sm">
                                <span className="text-stone-700">
                                    {attempt.score_percent}% · {attempt.passed ? 'Passed' : 'Not passed'}
                                </span>
                                <span className="flex items-center gap-4">
                                    <span className="text-stone-500">{attempt.taken_at}</span>
                                    <Link href={attempt.url} className="font-medium text-brand-700 hover:underline">
                                        Review
                                    </Link>
                                </span>
                            </li>
                        ))}
                    </ul>
                </section>
            )}

            <div className="mt-8 flex justify-end border-t border-stone-200 pt-6">
                <StepNav prev={prev} next={next} emphasizeNext={quiz.passed} />
            </div>
        </LearnShell>
    );
}