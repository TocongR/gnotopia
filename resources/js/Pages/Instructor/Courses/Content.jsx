import ModuleCard from '@/Components/course/ModuleCard';
import StatusBadge from '@/Components/course/StatusBadge';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';

export default function Content({ course, modules }) {
    const { errors } = usePage().props;
    const addForm = useForm({ title: '' });

    // Errors that don't belong to a form field (stale reorder lists, blocked deletes).
    const problem = errors.ids || errors.items || errors.module;

    const addModule = (e) => {
        e.preventDefault();
        addForm.post(route('instructor.courses.modules.store', course.id), {
            preserveScroll: true,
            onSuccess: () => addForm.reset(),
        });
    };

    const moveModule = (index, delta) => {
        const target = index + delta;

        if (target < 0 || target >= modules.length) {
            return;
        }

        const ids = modules.map((module) => module.id);
        [ids[index], ids[target]] = [ids[target], ids[index]];

        router.put(route('instructor.courses.modules.reorder', course.id), { ids }, { preserveScroll: true });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <h2 className="text-xl font-semibold leading-tight text-stone-800">Content: {course.title}</h2>
                        <StatusBadge status={course.status} />
                    </div>
                    <Link href={route('instructor.courses.edit', course.id)} className="text-sm text-stone-600 hover:underline">
                        Back to course details
                    </Link>
                </div>
            }
        >
            <Head title={`Content: ${course.title}`} />

            <div className="py-12">
                <div className="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                    {problem && <div className="rounded-md bg-red-50 p-4 text-sm text-red-800">{problem}</div>}

                    {modules.length === 0 && (
                        <div className="rounded-lg bg-white p-6 text-stone-600 shadow-sm">
                            This course has no modules yet. Add the first one below.
                        </div>
                    )}

                    {modules.map((courseModule, index) => (
                        <ModuleCard
                            key={courseModule.id}
                            courseModule={courseModule}
                            index={index}
                            total={modules.length}
                            onMove={moveModule}
                        />
                    ))}

                    <form onSubmit={addModule} className="rounded-lg bg-white p-4 shadow-sm">
                        <div className="flex items-start gap-3">
                            <div className="flex-1">
                                <TextInput
                                    className="block w-full"
                                    placeholder="New module title"
                                    value={addForm.data.title}
                                    onChange={(e) => addForm.setData('title', e.target.value)}
                                />
                                <InputError message={addForm.errors.title} className="mt-2" />
                            </div>
                            <PrimaryButton disabled={addForm.processing}>Add module</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}