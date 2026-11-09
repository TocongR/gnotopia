import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import { Link } from '@inertiajs/react';

const MIN_OPTIONS = 2;
const MAX_OPTIONS = 6;
const MAX_QUESTIONS = 50;

export const blankOption = () => ({ id: null, body: '', is_correct: false });
export const blankQuestion = () => ({ id: null, body: '', options: [blankOption(), blankOption()] });

const smallButton =
    'rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-50 disabled:opacity-30 disabled:hover:bg-transparent';

export default function QuizForm({ form, onSubmit, submitLabel, cancelHref }) {
    const { data, setData, errors, processing } = form;

    const setQuestions = (questions) => setData('questions', questions);

    const updateQuestion = (qIndex, changes) =>
        setQuestions(data.questions.map((q, i) => (i === qIndex ? { ...q, ...changes } : q)));

    const addQuestion = () => setQuestions([...data.questions, blankQuestion()]);

    const removeQuestion = (qIndex) => setQuestions(data.questions.filter((_, i) => i !== qIndex));

    const moveQuestion = (qIndex, delta) => {
        const target = qIndex + delta;

        if (target < 0 || target >= data.questions.length) {
            return;
        }

        const next = [...data.questions];
        [next[qIndex], next[target]] = [next[target], next[qIndex]];
        setQuestions(next);
    };

    const updateOption = (qIndex, oIndex, changes) =>
        updateQuestion(qIndex, {
            options: data.questions[qIndex].options.map((o, i) => (i === oIndex ? { ...o, ...changes } : o)),
        });

    // Single-answer quiz: marking one option unmarks the others.
    const markCorrect = (qIndex, oIndex) =>
        updateQuestion(qIndex, {
            options: data.questions[qIndex].options.map((o, i) => ({ ...o, is_correct: i === oIndex })),
        });

    const addOption = (qIndex) =>
        updateQuestion(qIndex, { options: [...data.questions[qIndex].options, blankOption()] });

    const removeOption = (qIndex, oIndex) =>
        updateQuestion(qIndex, { options: data.questions[qIndex].options.filter((_, i) => i !== oIndex) });

    return (
        <form onSubmit={onSubmit} className="space-y-6">
            <div className="grid gap-6 sm:grid-cols-3">
                <div className="sm:col-span-2">
                    <InputLabel htmlFor="title" value="Quiz title" />
                    <TextInput
                        id="title"
                        className="mt-1 block w-full"
                        value={data.title}
                        onChange={(e) => setData('title', e.target.value)}
                        required
                    />
                    <InputError message={errors.title} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="pass_percent" value="Passing score (%)" />
                    <TextInput
                        id="pass_percent"
                        type="number"
                        min="1"
                        max="100"
                        className="mt-1 block w-full"
                        value={data.pass_percent}
                        onChange={(e) => setData('pass_percent', e.target.value)}
                        required
                    />
                    <InputError message={errors.pass_percent} className="mt-2" />
                </div>
            </div>

            {errors.questions && (
                <div className="rounded-md bg-red-50 p-3 text-sm text-red-800">{errors.questions}</div>
            )}

            <div className="space-y-4">
                {data.questions.map((question, qIndex) => (
                    <div key={qIndex} className="rounded-lg border border-gray-200 p-4">
                        <div className="flex items-start justify-between gap-3">
                            <InputLabel htmlFor={`question-${qIndex}`} value={`Question ${qIndex + 1}`} />

                            <div className="flex items-center gap-1">
                                <button
                                    type="button"
                                    aria-label="Move question up"
                                    className={smallButton}
                                    disabled={qIndex === 0}
                                    onClick={() => moveQuestion(qIndex, -1)}
                                >
                                    ↑
                                </button>
                                <button
                                    type="button"
                                    aria-label="Move question down"
                                    className={smallButton}
                                    disabled={qIndex === data.questions.length - 1}
                                    onClick={() => moveQuestion(qIndex, 1)}
                                >
                                    ↓
                                </button>
                                <button
                                    type="button"
                                    className={`${smallButton} text-red-600`}
                                    disabled={data.questions.length === 1}
                                    onClick={() => removeQuestion(qIndex)}
                                >
                                    Remove
                                </button>
                            </div>
                        </div>

                        <textarea
                            id={`question-${qIndex}`}
                            rows={2}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value={question.body}
                            onChange={(e) => updateQuestion(qIndex, { body: e.target.value })}
                        />
                        <InputError message={errors[`questions.${qIndex}.body`]} className="mt-2" />

                        <p className="mt-4 text-xs text-gray-500">
                            Select the radio button next to the correct answer.
                        </p>

                        <div className="mt-2 space-y-2">
                            {question.options.map((option, oIndex) => (
                                <div key={oIndex}>
                                    <div className="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name={`correct-${qIndex}`}
                                            aria-label={`Option ${oIndex + 1} is the correct answer`}
                                            checked={option.is_correct}
                                            onChange={() => markCorrect(qIndex, oIndex)}
                                            className="text-indigo-600 focus:ring-indigo-500"
                                        />
                                        <TextInput
                                            className="block w-full"
                                            placeholder={`Option ${oIndex + 1}`}
                                            value={option.body}
                                            onChange={(e) => updateOption(qIndex, oIndex, { body: e.target.value })}
                                        />
                                        <button
                                            type="button"
                                            aria-label="Remove option"
                                            className={smallButton}
                                            disabled={question.options.length <= MIN_OPTIONS}
                                            onClick={() => removeOption(qIndex, oIndex)}
                                        >
                                            ✕
                                        </button>
                                    </div>
                                    <InputError
                                        message={errors[`questions.${qIndex}.options.${oIndex}.body`]}
                                        className="ms-6 mt-1"
                                    />
                                </div>
                            ))}
                        </div>

                        <InputError message={errors[`questions.${qIndex}.options`]} className="mt-2" />

                        <button
                            type="button"
                            className="mt-3 text-sm font-medium text-indigo-600 hover:underline disabled:opacity-40"
                            disabled={question.options.length >= MAX_OPTIONS}
                            onClick={() => addOption(qIndex)}
                        >
                            + Add option
                        </button>
                    </div>
                ))}
            </div>

            <SecondaryButton disabled={data.questions.length >= MAX_QUESTIONS} onClick={addQuestion}>
                + Add question
            </SecondaryButton>

            <div className="flex items-center gap-4 border-t border-gray-100 pt-6">
                <PrimaryButton disabled={processing}>{submitLabel}</PrimaryButton>
                <Link href={cancelHref} className="text-sm text-gray-600 hover:underline">
                    Cancel
                </Link>
            </div>
        </form>
    );
}