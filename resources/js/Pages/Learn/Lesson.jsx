import CourseSidebar from '@/Components/learn/CourseSidebar';
import LessonViewer from '@/Components/learn/LessonViewer';
import ProgressBar from '@/Components/ui/ProgressBar';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

const primary =
    'inline-flex items-center rounded-md bg-brand-800 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700';
const secondary =
    'inline-flex items-center rounded-md border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 hover:bg-stone-50';

export default function Lesson({ course, modules, lesson, progress, prev, next }) {
    const lessonUrl = (target) => route('student.learn.lesson', [course.slug, target.id]);

    const markComplete = () =>
        router.post(route('student.lessons.complete', lesson.id), {}, { preserveScroll: true });

    const markIncomplete = () =>
        router.delete(route('student.lessons.uncomplete', lesson.id), { preserveScroll: true });

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between gap-4">
                    <h2 className="font-display text-xl font-semibold text-stone-900">{course.title}</h2>
                    <Link href={route('student.dashboard')} className="text-sm text-stone-600 hover:underline">
                        My learning
                    </Link>
                </div>
            }
        >
            <Head title={`${lesson.title} · ${course.title}`} />

            <div className="py-8">
                <div className="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-4 lg:px-8">
                    <div className="lg:col-span-3">
                        <h1 className="font-display text-3xl font-semibold text-stone-900">{lesson.title}</h1>

                        <div className="mt-6">
                            <LessonViewer lesson={lesson} />
                        </div>

                        <div className="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-stone-200 pt-6">
                            <div className="flex items-center gap-3">
                                {lesson.completed ? (
                                    <>
                                        <span className="text-sm font-medium text-brand-800">✓ Completed</span>
                                        <button
                                            type="button"
                                            onClick={markIncomplete}
                                            className="text-sm text-stone-500 hover:underline"
                                        >
                                            Mark as incomplete
                                        </button>
                                    </>
                                ) : (
                                    <button type="button" onClick={markComplete} className={primary}>
                                        Mark as complete
                                    </button>
                                )}
                            </div>

                            <div className="flex items-center gap-2">
                                {prev && (
                                    <Link href={lessonUrl(prev)} className={secondary}>
                                        ← Previous
                                    </Link>
                                )}
                                {next && (
                                    <Link href={lessonUrl(next)} className={lesson.completed ? primary : secondary}>
                                        Next →
                                    </Link>
                                )}
                            </div>
                        </div>

                        {progress.percent === 100 && (
                            <p className="mt-6 rounded-md bg-brand-50 p-4 text-sm text-brand-900">
                                You have completed every lesson in this course. Quizzes unlock in the next update.
                            </p>
                        )}
                    </div>

                    <aside className="space-y-4 lg:order-first lg:col-span-1">
                        <div className="rounded-md border border-stone-200 bg-white p-4">
                            <p className="text-sm font-medium text-stone-900">
                                {progress.done} of {progress.total} lessons complete
                            </p>
                            <div className="mt-2">
                                <ProgressBar percent={progress.percent} label="Course progress" />
                            </div>
                        </div>

                        <CourseSidebar course={course} modules={modules} currentLessonId={lesson.id} />
                    </aside>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}