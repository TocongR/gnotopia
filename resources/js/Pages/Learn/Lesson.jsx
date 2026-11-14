import LearnShell from '@/Components/learn/LearnShell';
import LessonViewer from '@/Components/learn/LessonViewer';
import StepNav from '@/Components/learn/StepNav';
import { buttonPrimary } from '@/lib/buttons';
import { router } from '@inertiajs/react';

export default function Lesson({ course, modules, lesson, progress, prev, next }) {
    const markComplete = () =>
        router.post(route('student.lessons.complete', lesson.id), {}, { preserveScroll: true });

    const markIncomplete = () =>
        router.delete(route('student.lessons.uncomplete', lesson.id), { preserveScroll: true });

    return (
        <LearnShell
            course={course}
            modules={modules}
            progress={progress}
            currentKey={`lesson-${lesson.id}`}
            title={lesson.title}
        >
            <h1 className="font-display text-3xl font-semibold text-stone-900">{lesson.title}</h1>

            <div className="mt-6">
                <LessonViewer lesson={lesson} />
            </div>

            <div className="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-stone-200 pt-6">
                <div className="flex items-center gap-3">
                    {lesson.completed ? (
                        <>
                            <span className="text-sm font-medium text-brand-800">✓ Completed</span>
                            <button type="button" onClick={markIncomplete} className="text-sm text-stone-500 hover:underline">
                                Mark as incomplete
                            </button>
                        </>
                    ) : (
                        <button type="button" onClick={markComplete} className={buttonPrimary}>
                            Mark as complete
                        </button>
                    )}
                </div>

                <StepNav prev={prev} next={next} emphasizeNext={lesson.completed} />
            </div>

            {progress.percent === 100 && (
                <p className="mt-6 rounded-md bg-brand-50 p-4 text-sm text-brand-900">
                    You have finished every lesson and quiz in this course.
                </p>
            )}
        </LearnShell>
    );
}