import { Link, usePage } from '@inertiajs/react';

export default function PublicLayout({ children }) {
    const { auth } = usePage().props;

    return (
        <div className="flex min-h-screen flex-col bg-stone-50 text-stone-900">
            <header className="border-b border-stone-200 bg-white">
                <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-8">
                        <Link href={route('home')} className="font-display text-2xl font-semibold tracking-tight text-brand-800">
                            Gnotopia
                        </Link>
                        <Link href={route('catalog.index')} className="text-sm font-medium text-stone-700 hover:text-brand-800">
                            Courses
                        </Link>
                    </div>

                    <nav className="flex items-center gap-5 text-sm">
                        {auth.user ? (
                            <Link
                                href={route('dashboard')}
                                className="rounded-md bg-brand-800 px-4 py-2 font-medium text-white hover:bg-brand-700"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link href={route('login')} className="font-medium text-stone-700 hover:text-brand-800">
                                    Log in
                                </Link>
                                <Link
                                    href={route('register')}
                                    className="rounded-md bg-brand-800 px-4 py-2 font-medium text-white hover:bg-brand-700"
                                >
                                    Register
                                </Link>
                            </>
                        )}
                    </nav>
                </div>
            </header>

            <main className="flex-1">{children}</main>

            <footer className="bg-brand-950 py-8 text-sm text-brand-200">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    © {new Date().getFullYear()} Gnotopia
                </div>
            </footer>
        </div>
    );
}