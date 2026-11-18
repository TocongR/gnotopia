import LogoMark from '@/Components/LogoMark';
import { Link, usePage } from '@inertiajs/react';

export default function SiteFooter() {
    const { auth } = usePage().props;

    const linkClass = 'text-brand-100 hover:text-white hover:underline';

    return (
        <footer className="mt-20 overflow-hidden rounded-t-3xl bg-brand-900 text-brand-100">
            <div className="mx-auto max-w-7xl px-6 pt-14 lg:px-8">
                <div className="grid gap-10 sm:grid-cols-3">
                    <div>
                        <div className="flex items-center gap-2">
                            <LogoMark className="h-9 w-9" />
                            <span className="font-display text-xl font-bold text-white">Gnotopia</span>
                        </div>
                        <p className="mt-4 max-w-xs text-sm text-brand-200">
                            Courses with lessons, videos and quizzes, made by instructors.
                        </p>
                    </div>

                    <nav aria-label="Explore">
                        <h2 className="text-sm font-semibold uppercase tracking-wide text-brand-300">Explore</h2>
                        <ul className="mt-4 space-y-2 text-sm">
                            <li>
                                <Link href={route('catalog.index')} className={linkClass}>
                                    All courses
                                </Link>
                            </li>
                            <li>
                                <Link href={route('home')} className={linkClass}>
                                    Home
                                </Link>
                            </li>
                        </ul>
                    </nav>

                    <nav aria-label="Account">
                        <h2 className="text-sm font-semibold uppercase tracking-wide text-brand-300">Account</h2>
                        <ul className="mt-4 space-y-2 text-sm">
                            {auth.user ? (
                                <li>
                                    <Link href={route('dashboard')} className={linkClass}>
                                        Dashboard
                                    </Link>
                                </li>
                            ) : (
                                <>
                                    <li>
                                        <Link href={route('login')} className={linkClass}>
                                            Log in
                                        </Link>
                                    </li>
                                    <li>
                                        <Link href={route('register')} className={linkClass}>
                                            Create an account
                                        </Link>
                                    </li>
                                </>
                            )}
                        </ul>
                    </nav>
                </div>

                <p className="mt-12 text-sm text-brand-300">© {new Date().getFullYear()} Gnotopia</p>
            </div>

            <div
                aria-hidden="true"
                className="mt-4 translate-y-[18%] select-none whitespace-nowrap text-center font-display text-[22vw] font-extrabold leading-[0.8] text-brand-800"
            >
                Gnotopia
            </div>
        </footer>
    );
}