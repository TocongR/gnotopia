import InputError from '@/Components/InputError';
import { Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

const smallButton =
    'rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-50 disabled:opacity-30 disabled:hover:bg-transparent';

export default function ModuleCard({ courseModule, index, total, onMove }) {
    const [editing, setEditing] = useState(false);
    const form = useForm({ title: courseModule.title });

    const rename = (e) => {
        e.preventDefault();
        form.put(route('instructor.modules.update', courseModule.id), {
            preserveScroll: true,
            onSuccess: () => setEditing(false),
        });
    };

    const removeModule = () => {
        if (window.confirm('Delete this module and all of its lessons and quizzes?')) {
            router.delete(route('instructor.modules.destroy', courseModule.id), { preserveScroll: true });
        }
    };

    const moveItem = (itemIndex, delta) => {
        const target = itemIndex + delta;

        if (target < 0 || target >= courseModule.items.length) {
            return;
        }

        const items = courseModule.items.map(({ type, id }) => ({ type, id }));
        [items[itemIndex], items[target]] = [items[target], items[itemIndex]];

        router.put(route('instructor.modules.items.reorder', courseModule.id), { items }, { preserveScroll: true });
    };

    const removeLesson = (id) => {
        if (window.confirm('Delete this lesson?')) {
            router.delete(route('instructor.lessons.destroy', id), { preserveScroll: true });
        }
    };

    return (
        <div className="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div className="flex flex-wrap items-center justify-between gap-3 bg-gray-50 px-4 py-3">
                {editing ? (
                    <form onSubmit={rename} className="flex flex-1 items-center gap-2">
                        <input
                            className="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value={form.data.title}
                            onChange={(e) => form.setData('title', e.target.value)}
                            autoFocus
                        />
                        <button type="submit" className={smallButton} disabled={form.processing}>
                            Save
                        </button>
                        <button
                            type="button"
                            className={smallButton}
                            onClick={() => {
                                form.reset();
                                form.clearErrors();
                                setEditing(false);
                            }}
                        >
                            Cancel
                        </button>
                    </form>
                ) : (
                    <h3 className="font-semibold text-gray-900">
                        {index + 1}. {courseModule.title}
                    </h3>
                )}

                <div className="flex items-center gap-1">
                    <button type="button" aria-label="Move module up" className={smallButton} disabled={index === 0} onClick={() => onMove(index, -1)}>
                        ↑
                    </button>
                    <button type="button" aria-label="Move module down" className={smallButton} disabled={index === total - 1} onClick={() => onMove(index, 1)}>
                        ↓
                    </button>
                    {!editing && (
                        <button type="button" className={smallButton} onClick={() => setEditing(true)}>
                            Rename
                        </button>
                    )}
                    <button type="button" className={`${smallButton} text-red-600`} onClick={removeModule}>
                        Delete
                    </button>
                </div>
            </div>

            {editing && <InputError message={form.errors.title} className="px-4 pt-2" />}

            {courseModule.items.length === 0 ? (
                <p className="px-4 py-4 text-sm text-gray-500">No lessons yet.</p>
            ) : (
                <ul className="divide-y divide-gray-100">
                    {courseModule.items.map((item, itemIndex) => (
                        <li key={item.key} className="flex items-center justify-between gap-3 px-4 py-3">
                            <div className="min-w-0">
                                <span className="mr-2 rounded bg-gray-100 px-2 py-0.5 text-xs capitalize text-gray-700">
                                    {item.kind}
                                </span>
                                <span className="font-medium text-gray-900">{item.title}</span>
                            </div>

                            <div className="flex shrink-0 items-center gap-1">
                                <button type="button" aria-label="Move up" className={smallButton} disabled={itemIndex === 0} onClick={() => moveItem(itemIndex, -1)}>
                                    ↑
                                </button>
                                <button
                                    type="button"
                                    aria-label="Move down"
                                    className={smallButton}
                                    disabled={itemIndex === courseModule.items.length - 1}
                                    onClick={() => moveItem(itemIndex, 1)}
                                >
                                    ↓
                                </button>

                                {item.type === 'lesson' ? (
                                    <>
                                        <Link href={route('instructor.lessons.edit', item.id)} className={smallButton}>
                                            Edit
                                        </Link>
                                        <button type="button" className={`${smallButton} text-red-600`} onClick={() => removeLesson(item.id)}>
                                            Delete
                                        </button>
                                    </>
                                ) : (
                                    <span className="px-2 text-xs text-gray-400">Quiz editor comes in step 4C</span>
                                )}
                            </div>
                        </li>
                    ))}
                </ul>
            )}

            <div className="border-t border-gray-100 px-4 py-3">
                <Link
                    href={route('instructor.modules.lessons.create', courseModule.id)}
                    className="text-sm font-medium text-indigo-600 hover:underline"
                >
                    + Add lesson
                </Link>
            </div>
        </div>
    );
}