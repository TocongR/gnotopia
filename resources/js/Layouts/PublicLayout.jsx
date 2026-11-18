import LogoMark from '@/Components/LogoMark';
import SiteFooter from '@/Components/layout/SiteFooter';
import { Link, usePage } from '@inertiajs/react';

export default function PublicLayout({ children }) {
    const { auth } = usePage().props;

    return (
        <div className="flex min-h-screen flex-col bg-stone-50 text-stone-900">
            <header className="sticky top-0 z-30 px-4 pt-4">
                <div className="mx-auto flex max-w-3xl items-center justify-between rounded-full border border-stone-200 bg-white px-2 py-2">
                    <Link
                        href={route('catalog.index')}
                        className="rounded-full px-4 py-2 text-sm font-semibold text-stone-800 hover:bg-stone-100"
                    >
                        Courses
                    </Link>

                    <Link href={route('home')} aria-label="Gnotopia home" className="flex items-center gap-2">
                        <LogoMark className="h-8 w-8" />
                        <span className="hidden font-display text-lg font-bold text-brand-900 sm:inline">
                            Gnotopia
                        </span>
                    </Link>

                    <nav className="flex items-center gap-1">
                        {auth.user ? (
                            <Link
                                href={route('dashboard')}
                                className="rounded-full bg-brand-800 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={route('login')}
                                    className="hidden rounded-full px-4 py-2 text-sm font-semibold text-stone-800 hover:bg-stone-100 sm:inline-flex"
                                >
                                    Log in
                                </Link>
                                <Link
                                    href={route('register')}
                                    className="rounded-full bg-brand-800 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700"
                                >
                                    Register
                                </Link>
                            </>
                        )}
                    </nav>
                </div>
            </header>

            <main className="flex-1">{children}</main>

            <SiteFooter />
        </div>
    );
}