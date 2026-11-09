import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import RichTextEditor from '@/Components/ui/RichTextEditor';
import { Link } from '@inertiajs/react';

const types = [
    { value: 'article', label: 'Article', hint: 'Written content' },
    { value: 'video', label: 'Video', hint: 'YouTube or Vimeo link' },
];

export default function LessonForm({ form, onSubmit, submitLabel, cancelHref }) {
    const { data, setData, errors, processing } = form;

    return (
        <form onSubmit={onSubmit} className="space-y-6">
            <div>
                <InputLabel value="Lesson type" />
                <div className="mt-2 grid grid-cols-2 gap-3">
                    {types.map((type) => (
                        <label
                            key={type.value}
                            className={`cursor-pointer rounded-md border p-3 text-sm ${
                                data.type === type.value ? 'border-indigo-600 bg-indigo-50' : 'border-gray-300'
                            }`}
                        >
                            <input
                                type="radio"
                                name="type"
                                value={type.value}
                                checked={data.type === type.value}
                                onChange={(e) => setData('type', e.target.value)}
                                className="sr-only"
                            />
                            <span className="block font-semibold text-gray-900">{type.label}</span>
                            <span className="block text-gray-600">{type.hint}</span>
                        </label>
                    ))}
                </div>
                <InputError message={errors.type} className="mt-2" />
            </div>

            <div>
                <InputLabel htmlFor="title" value="Title" />
                <TextInput
                    id="title"
                    className="mt-1 block w-full"
                    value={data.title}
                    onChange={(e) => setData('title', e.target.value)}
                    required
                />
                <InputError message={errors.title} className="mt-2" />
            </div>

            {data.type === 'video' ? (
                <div>
                    <InputLabel htmlFor="video_url" value="Video link (YouTube or Vimeo)" />
                    <TextInput
                        id="video_url"
                        type="url"
                        className="mt-1 block w-full"
                        placeholder="https://www.youtube.com/watch?v=..."
                        value={data.video_url}
                        onChange={(e) => setData('video_url', e.target.value)}
                    />
                    <InputError message={errors.video_url} className="mt-2" />
                </div>
            ) : (
                <div>
                    <InputLabel value="Content" />
                    <RichTextEditor value={data.body} onChange={(html) => setData('body', html)} />
                    <InputError message={errors.body} className="mt-2" />
                </div>
            )}

            <div className="flex items-center gap-4">
                <PrimaryButton disabled={processing}>{submitLabel}</PrimaryButton>
                <Link href={cancelHref} className="text-sm text-gray-600 hover:underline">
                    Cancel
                </Link>
            </div>
        </form>
    );
}