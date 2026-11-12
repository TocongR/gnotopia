import { usePage } from '@inertiajs/react';

export default function FlashMessages() {
    const { flash } = usePage().props;

    if (!flash?.success && !flash?.error) {
        return null;
    }

    return (
        <div className="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
            {flash.success && (
                <div className="rounded-md bg-brand-50 p-4 text-sm text-brand-800">{flash.success}</div>
            )}
            {flash.error && (
                <div className="rounded-md bg-red-50 p-4 text-sm text-red-800">{flash.error}</div>
            )}
        </div>
    );
}