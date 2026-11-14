import { Link } from '@inertiajs/react';

export default function CourseSidebar({ modules, currentKey }) {
    return (
        <nav aria-label="Course content" className="rounded-md border border-stone-200 bg-white">
            {modules.map((courseModule, index) => (
                <div key={courseModule.id} className="border-b border-stone-200 last:border-b-0">
                    <h3 className="bg-stone-50 px-4 py-3 text-sm font-semibold text-stone-900">
                        {index + 1}. {courseModule.title}
                    </h3>

                    <ul>
                        {courseModule.items.map((item) => {
                            const current = item.key === currentKey;

                            return (
                                <li key={item.key}>
                                    <Link
                                        href={item.url}
                                        aria-current={current ? 'page' : undefined}
                                        className={`flex items-start gap-3 px-4 py-2 text-sm ${
                                            current
                                                ? 'bg-brand-100 font-medium text-brand-900'
                                                : 'text-stone-700 hover:bg-stone-50'
                                        }`}
                                    >
                                        <span
                                            aria-hidden="true"
                                            className={item.completed ? 'text-brand-700' : 'text-stone-400'}
                                        >
                                            {item.completed ? '✓' : item.type === 'quiz' ? '□' : '○'}
                                        </span>
                                        <span>
                                            {item.title}
                                            {item.type === 'quiz' && (
                                                <span className="ml-1 text-xs text-stone-500">(quiz)</span>
                                            )}
                                            {item.completed && <span className="sr-only"> (completed)</span>}
                                        </span>
                                    </Link>
                                </li>
                            );
                        })}
                    </ul>
                </div>
            ))}
        </nav>
    );
}