import { usePage } from '@inertiajs/react';

export default function FlashMessages() {
    const { flash } = usePage().props;

    if (!flash?.success && !flash?.error) {
        return null;
    }

    return (
        <div className="mx-auto max-w-7xl space-y-3 px-4 pt-6 sm:px-6 lg:px-8">
            {flash.success && (
                <div
                    role="status"
                    className="rounded-2xl border border-brand-200 bg-brand-50 p-4 text-sm font-medium text-brand-900"
                >
                    {flash.success}
                </div>
            )}
            {flash.error && (
                <div role="alert" className="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    {flash.error}
                </div>
            )}
        </div>
    );
}