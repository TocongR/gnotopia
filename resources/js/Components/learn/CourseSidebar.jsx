import { Link } from '@inertiajs/react';

export default function CourseSidebar({ course, modules, currentLessonId }) {
    return (
        <nav aria-label="Course content" className="rounded-md border border-stone-200 bg-white">
            {modules.map((courseModule, index) => (
                <div key={courseModule.id} className="border-b border-stone-200 last:border-b-0">
                    <h3 className="bg-stone-50 px-4 py-3 text-sm font-semibold text-stone-900">
                        {index + 1}. {courseModule.title}
                    </h3>

                    <ul>
                        {courseModule.items.map((item) =>
                            item.type === 'lesson' ? (
                                <li key={item.key}>
                                    <Link
                                        href={route('student.learn.lesson', [course.slug, item.id])}
                                        aria-current={item.id === currentLessonId ? 'page' : undefined}
                                        className={`flex items-start gap-3 px-4 py-2 text-sm ${
                                            item.id === currentLessonId
                                                ? 'bg-brand-100 font-medium text-brand-900'
                                                : 'text-stone-700 hover:bg-stone-50'
                                        }`}
                                    >
                                        <span
                                            aria-hidden="true"
                                            className={item.completed ? 'text-brand-700' : 'text-stone-400'}
                                        >
                                            {item.completed ? '✓' : '○'}
                                        </span>
                                        <span>
                                            {item.title}
                                            {item.completed && <span className="sr-only"> (completed)</span>}
                                        </span>
                                    </Link>
                                </li>
                            ) : (
                                <li key={item.key} className="flex items-start gap-3 px-4 py-2 text-sm text-stone-400">
                                    <span aria-hidden="true">□</span>
                                    <span>{item.title} · quiz, available soon</span>
                                </li>
                            ),
                        )}
                    </ul>
                </div>
            ))}
        </nav>
    );
}