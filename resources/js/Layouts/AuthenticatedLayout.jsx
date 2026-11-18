import Dropdown from '@/Components/Dropdown';
import FlashMessages from '@/Components/FlashMessages';
import LogoMark from '@/Components/LogoMark';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

// One place that says what each role sees in the top bar.
const navByRole = {
    student: [
        { label: 'My learning', href: 'student.dashboard', active: ['student.*'] },
        { label: 'Browse courses', href: 'catalog.index', active: ['catalog.*'] },
    ],
    instructor: [
        { label: 'Dashboard', href: 'instructor.dashboard', active: ['instructor.dashboard'] },
        {
            label: 'My courses',
            href: 'instructor.courses.index',
            active: ['instructor.courses.*', 'instructor.modules.*', 'instructor.lessons.*', 'instructor.quizzes.*'],
        },
    ],
    admin: [
        { label: 'Dashboard', href: 'admin.dashboard', active: ['admin.dashboard'] },
        { label: 'Users', href: 'admin.users.index', active: ['admin.users.*'] },
        { label: 'Courses', href: 'admin.courses.index', active: ['admin.courses.*'] },
    ],
};

const isActive = (item) => item.active.some((pattern) => route().current(pattern));

function Chevron() {
    return (
        <svg viewBox="0 0 20 20" className="h-4 w-4 text-stone-500" fill="currentColor" aria-hidden="true">
            <path d="M5.3 7.3a1 1 0 0 1 1.4 0L10 10.6l3.3-3.3a1 1 0 1 1 1.4 1.4l-4 4a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 0-1.4Z" />
        </svg>
    );
}

export default function AuthenticatedLayout({ header, children }) {
    const { user } = usePage().props.auth;
    const [menuOpen, setMenuOpen] = useState(false);

    const links = navByRole[user.role] ?? [];
    const roleLabel = user.role.charAt(0).toUpperCase() + user.role.slice(1);

    const pill = (active) =>
        `rounded-full px-4 py-2 text-sm font-semibold ${
            active ? 'bg-brand-100 text-brand-900' : 'text-stone-700 hover:bg-stone-100'
        }`;

    return (
        <div className="min-h-screen bg-stone-50 text-stone-900">
            <header className="sticky top-0 z-30 border-b border-stone-200 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-6">
                        <Link href={route('dashboard')} className="flex items-center gap-2">
                            <LogoMark className="h-8 w-8" />
                            <span className="font-display text-lg font-extrabold text-brand-900">Gnotopia</span>
                        </Link>

                        <nav aria-label="Main" className="hidden items-center gap-1 md:flex">
                            {links.map((item) => (
                                <Link
                                    key={item.href}
                                    href={route(item.href)}
                                    aria-current={isActive(item) ? 'page' : undefined}
                                    className={pill(isActive(item))}
                                >
                                    {item.label}
                                </Link>
                            ))}
                        </nav>
                    </div>

                    <div className="hidden md:block">
                        <Dropdown>
                            <Dropdown.Trigger>
                                <span
                                    aria-hidden="true"
                                    className="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-sm font-bold text-brand-900"
                                >
                                    {user.name.charAt(0).toUpperCase()}
                                </span>
                                <span className="text-sm font-semibold text-stone-800">{user.name}</span>
                                <Chevron />
                            </Dropdown.Trigger>

                            <Dropdown.Content>
                                <div className="px-3 py-2">
                                    <p className="truncate text-sm font-semibold text-stone-900">{user.name}</p>
                                    <p className="truncate text-xs text-stone-500">{user.email}</p>
                                    <p className="mt-1 text-xs font-semibold text-brand-800">{roleLabel}</p>
                                </div>
                                <div className="my-1 border-t border-stone-100" />
                                <Dropdown.Link href={route('profile.edit')}>Profile</Dropdown.Link>
                                <Dropdown.Link href={route('logout')} method="post" as="button">
                                    Log out
                                </Dropdown.Link>
                            </Dropdown.Content>
                        </Dropdown>
                    </div>

                    <button
                        type="button"
                        aria-label="Toggle menu"
                        aria-expanded={menuOpen}
                        onClick={() => setMenuOpen((value) => !value)}
                        className="inline-flex h-10 w-10 items-center justify-center rounded-full border border-stone-200 text-stone-700 hover:bg-stone-50 md:hidden"
                    >
                        <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true">
                            {menuOpen ? <path d="M6 6l12 12M18 6L6 18" /> : <path d="M4 7h16M4 12h16M4 17h16" />}
                        </svg>
                    </button>
                </div>

                {menuOpen && (
                    <div className="border-t border-stone-200 bg-white px-4 py-4 md:hidden">
                        <nav aria-label="Main" className="flex flex-col gap-1">
                            {links.map((item) => (
                                <Link
                                    key={item.href}
                                    href={route(item.href)}
                                    aria-current={isActive(item) ? 'page' : undefined}
                                    className={pill(isActive(item))}
                                >
                                    {item.label}
                                </Link>
                            ))}
                        </nav>

                        <div className="mt-4 border-t border-stone-100 pt-4">
                            <p className="text-sm font-semibold text-stone-900">{user.name}</p>
                            <p className="text-xs text-stone-500">
                                {user.email} · {roleLabel}
                            </p>

                            <div className="mt-3 flex flex-col gap-1">
                                <Link href={route('profile.edit')} className={pill(route().current('profile.*'))}>
                                    Profile
                                </Link>
                                <Link
                                    href={route('logout')}
                                    method="post"
                                    as="button"
                                    className="rounded-full px-4 py-2 text-left text-sm font-semibold text-stone-700 hover:bg-stone-100"
                                >
                                    Log out
                                </Link>
                            </div>
                        </div>
                    </div>
                )}
            </header>

            {header && <div className="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">{header}</div>}

            <FlashMessages />

            <main>{children}</main>
        </div>
    );
}