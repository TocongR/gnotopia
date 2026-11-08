import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';

const fieldClass =
    'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';

export default function CourseForm({ form, categories, onSubmit, submitLabel }) {
    const { data, setData, errors, processing } = form;

    return (
        <form onSubmit={onSubmit} className="space-y-6">
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

            <div>
                <InputLabel htmlFor="category_id" value="Category" />
                <select
                    id="category_id"
                    className={fieldClass}
                    value={data.category_id}
                    onChange={(e) => setData('category_id', e.target.value)}
                    required
                >
                    <option value="">Select a category</option>
                    {categories.map((category) => (
                        <option key={category.id} value={category.id}>
                            {category.name}
                        </option>
                    ))}
                </select>
                <InputError message={errors.category_id} className="mt-2" />
            </div>

            <div>
                <InputLabel htmlFor="description" value="Description" />
                <textarea
                    id="description"
                    rows={6}
                    className={fieldClass}
                    value={data.description ?? ''}
                    onChange={(e) => setData('description', e.target.value)}
                />
                <InputError message={errors.description} className="mt-2" />
            </div>

            <div>
                <InputLabel htmlFor="price" value="Price (PHP). Use 0 for a free course" />
                <TextInput
                    id="price"
                    type="number"
                    step="0.01"
                    min="0"
                    className="mt-1 block w-full"
                    value={data.price}
                    onChange={(e) => setData('price', e.target.value)}
                    required
                />
                <InputError message={errors.price} className="mt-2" />
            </div>

            <PrimaryButton disabled={processing}>{submitLabel}</PrimaryButton>
        </form>
    );
}