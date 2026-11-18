import LogoMark from '@/Components/LogoMark';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-stone-50 px-4 py-10">
            <Link href={route('home')} className="flex items-center gap-3">
                <LogoMark className="h-11 w-11" />
                <span className="font-display text-3xl font-extrabold text-brand-900">Gnotopia</span>
            </Link>

            <div className="mt-8 w-full max-w-md rounded-2xl border border-stone-200 bg-white p-6 sm:p-8">
                {children}
            </div>

            <Link href={route('catalog.index')} className="mt-6 text-sm font-medium text-stone-600 hover:underline">
                Browse courses without an account
            </Link>
        </div>
    );
}