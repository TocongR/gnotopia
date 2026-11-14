import LearnShell from '@/Components/learn/LearnShell';
import StepNav from '@/Components/learn/StepNav';
import { buttonPrimary, buttonSecondary } from '@/lib/buttons';
import { Link } from '@inertiajs/react';

export default function QuizResult({ course, modules, progress, quiz, attempt, review, prev, next }) {
    return (
        <LearnShell
            course={course}
            modules={modules}
            progress={progress}
            currentKey={`quiz-${quiz.id}`}
            title={`${quiz.title}: result`}
        >
            <h1 className="font-display text-3xl font-semibold text-stone-900">{quiz.title}</h1>

            <div
                role="status"
                className={`mt-6 rounded-md p-5 ${
                    attempt.passed ? 'bg-brand-50 text-brand-900' : 'bg-stone-100 text-stone-900'
                }`}
            >
                <p className="font-display text-2xl font-semibold">
                    {attempt.passed ? 'You passed' : 'Not passed yet'} · {attempt.score_percent}%
                </p>
                <p className="mt-1 text-sm">
                    {attempt.correct_count} of {attempt.total} correct. The passing score is {quiz.pass_percent}%.
                </p>
                <p className="mt-1 text-xs text-stone-500">{attempt.taken_at}</p>
            </div>

            <h2 className="mt-8 font-display text-xl font-semibold text-stone-900">Review your answers</h2>

            <ol className="mt-4 space-y-4">
                {review.map((item, index) => (
                    <li key={item.question_id} className="rounded-md border border-stone-200 bg-white p-5">
                        <p className="font-medium text-stone-900">
                            {index + 1}. {item.question}
                        </p>

                        <p className="mt-3 text-sm text-stone-700">
                            <span className="font-medium">Your answer: </span>
                            {item.selected}{' '}
                            <span className={item.is_correct ? 'font-medium text-brand-800' : 'font-medium text-red-700'}>
                                {item.is_correct ? '(correct)' : '(incorrect)'}
                            </span>
                        </p>

                        {!item.is_correct && item.correct && (
                            <p className="mt-1 text-sm text-stone-700">
                                <span className="font-medium">Correct answer: </span>
                                {item.correct}
                            </p>
                        )}
                    </li>
                ))}
            </ol>

            <div className="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-stone-200 pt-6">
                <Link href={quiz.url} className={attempt.passed ? buttonSecondary : buttonPrimary}>
                    Retake quiz
                </Link>

                <StepNav prev={prev} next={next} emphasizeNext={attempt.passed} />
            </div>
        </LearnShell>
    );
}