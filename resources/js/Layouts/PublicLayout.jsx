import { Link, usePage } from '@inertiajs/react';

export default function PublicLayout({ children }) {
    const { auth } = usePage().props;

    return (
        <div className="flex min-h-screen flex-col bg-gray-50">
            <header className="border-b border-gray-200 bg-white">
                <div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-6">
                        <Link href={route('home')} className="text-xl font-bold text-indigo-600">
                            Gnotopia
                        </Link>
                        <Link href={route('catalog.index')} className="text-sm font-medium text-gray-700 hover:text-gray-900">
                            Browse courses
                        </Link>
                    </div>

                    <nav className="flex items-center gap-4 text-sm">
                        {auth.user ? (
                            <Link
                                href={route('dashboard')}
                                className="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link href={route('login')} className="font-medium text-gray-700 hover:text-gray-900">
                                    Log in
                                </Link>
                                <Link
                                    href={route('register')}
                                    className="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-indigo-500"
                                >
                                    Register
                                </Link>
                            </>
                        )}
                    </nav>
                </div>
            </header>

            <main className="flex-1">{children}</main>

            <footer className="border-t border-gray-200 bg-white py-6 text-center text-sm text-gray-500">
                © {new Date().getFullYear()} Gnotopia
            </footer>
        </div>
    );
}