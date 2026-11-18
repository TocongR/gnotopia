import CourseCard from '@/Components/course/CourseCard';
import CategoryRibbon from '@/Components/home/CategoryRibbon';
import HeroLandscape from '@/Components/home/HeroLandscape';
import Icon from '@/Components/home/Icon';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link, usePage } from '@inertiajs/react';

const features = [
    {
        icon: 'list',
        title: 'Lessons in order',
        text: 'Modules and lessons follow the order the instructor set. Continue takes you back to where you stopped.',
    },
    {
        icon: 'check',
        title: 'Quizzes with a review',
        text: 'Each quiz is graded when you submit it, with every answer reviewed. Retake it as often as you like.',
    },
    {
        icon: 'chart',
        title: 'Progress you can see',
        text: 'A progress bar for every course, and a Completed mark once you finish every lesson and quiz.',
    },
    {
        icon: 'play',
        title: 'Free courses open fast',
        text: 'Free courses start with one click once you have a student account. No deadlines.',
    },
];

const teachSteps = [
    'Create the course and add its modules.',
    'Write lessons, add videos and build quizzes.',
    'Publish when it is ready, then follow how far each student gets.',
];

const faqs = [
    {
        q: 'Who is Gnotopia for?',
        a: 'People who want to learn from structured courses, and instructors who want to build and publish one.',
    },
    {
        q: 'Does it cost anything?',
        a: 'Creating an account is free, and free courses can be started right away. Paid courses are not available yet.',
    },
    {
        q: 'Can I learn at my own pace?',
        a: 'Yes. There are no deadlines. Your progress is saved, and Continue takes you to the next lesson or quiz you have not finished.',
    },
    {
        q: 'How do quizzes work?',
        a: 'A quiz is graded as soon as you submit it. You see which answers were right, and you can retake it as many times as you want. Passing counts toward finishing the course.',
    },
    {
        q: 'Do I get a certificate?',
        a: 'Not yet. Finishing a course is marked on your dashboard, and certificates are planned for a later update.',
    },
];

function FloatingPill({ className, children }) {
    return (
        <span
            aria-hidden="true"
            className={`absolute hidden rounded-full px-5 py-2 text-sm font-semibold md:inline-flex ${className}`}
        >
            {children}
        </span>
    );
}

export default function Home({ courses, categories }) {
    const { auth } = usePage().props;

    return (
        <PublicLayout>
            <Head title="Courses with lessons, videos and quizzes" />

            {/* Hero */}
            <section className="px-4 pt-4 sm:px-6 lg:px-8">
                <div className="relative mx-auto min-h-[520px] max-w-7xl overflow-hidden rounded-3xl bg-brand-800 sm:min-h-[580px]">
                    <div
                        aria-hidden="true"
                        className="absolute right-[10%] top-[12%] h-16 w-16 rounded-full bg-sun-300 sm:h-24 sm:w-24"
                    />
                    <HeroLandscape />

                    <div className="relative z-10 mx-auto max-w-2xl px-6 pt-24 text-center sm:pt-28">
                        <h1 className="font-display text-5xl font-extrabold leading-[1.05] tracking-tight text-white sm:text-6xl">
                            Pick a course. Finish it.
                        </h1>
                        <p className="mx-auto mt-5 max-w-xl text-lg text-brand-100">
                            A better place to learn. Made for curious minds who want real skills, not just another unfinished tab.
                        </p>

                        <div className="mt-8 flex flex-wrap justify-center gap-3">
                            <Link
                                href={route('catalog.index')}
                                className="rounded-full bg-sun-300 px-7 py-3 text-sm font-bold text-brand-950 hover:bg-sun-400"
                            >
                                Browse courses
                            </Link>
                            <Link
                                href={auth.user ? route('dashboard') : route('register')}
                                className="rounded-full border border-white/40 px-7 py-3 text-sm font-bold text-white hover:bg-white/10"
                            >
                                {auth.user ? 'Go to dashboard' : 'Teach a course'}
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            {/* Courses */}
            <section className="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 className="font-display text-3xl font-extrabold tracking-tight text-stone-900 sm:text-4xl">
                            Courses you can start today
                        </h2>
                        <p className="mt-2 text-stone-600">Pick one, or browse by category below.</p>
                    </div>
                    <Link href={route('catalog.index')} className="text-sm font-semibold text-brand-700 hover:underline">
                        Browse all courses →
                    </Link>
                </div>

                {courses.length === 0 ? (
                    <p className="mt-8 rounded-2xl border border-stone-200 bg-white p-8 text-stone-600">
                        No courses have been published yet.
                    </p>
                ) : (
                    <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {courses.map((course) => (
                            <CourseCard key={course.id} course={course} />
                        ))}
                    </div>
                )}
            </section>

            <CategoryRibbon categories={categories} />

            {/* Statement */}
            <section className="relative mx-auto max-w-4xl px-6 py-24 text-center">
                <h2 className="font-display text-3xl font-extrabold leading-tight tracking-tight text-stone-900 sm:text-5xl">
                    Each course has lessons, videos and quizzes, laid out in order.
                </h2>

                <FloatingPill className="left-2 top-10 -rotate-6 bg-sun-300 text-brand-950">Lessons</FloatingPill>
                <FloatingPill className="right-4 top-6 rotate-3 bg-brand-800 text-white">Videos</FloatingPill>
                <FloatingPill className="bottom-10 left-10 rotate-3 bg-brand-100 text-brand-900">Quizzes</FloatingPill>
                <FloatingPill className="bottom-14 right-0 -rotate-3 border border-stone-300 bg-white text-stone-800">
                    Progress
                </FloatingPill>
            </section>

            {/* Features */}
            <section className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p className="text-center text-sm font-semibold uppercase tracking-wide text-brand-700">
                    What a course gives you
                </p>

                <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {features.map((feature) => (
                        <div key={feature.title} className="rounded-2xl border border-stone-200 bg-white p-6">
                            <span className="inline-flex h-11 w-11 items-center justify-center rounded-full bg-brand-100 text-brand-800">
                                <Icon name={feature.icon} className="h-5 w-5" />
                            </span>
                            <h3 className="mt-4 font-display text-lg font-bold text-stone-900">{feature.title}</h3>
                            <p className="mt-2 text-sm text-stone-600">{feature.text}</p>
                        </div>
                    ))}
                </div>

                <div className="mt-8 text-center">
                    <Link
                        href={route('catalog.index')}
                        className="inline-flex rounded-full bg-brand-800 px-7 py-3 text-sm font-bold text-white hover:bg-brand-700"
                    >
                        See the courses
                    </Link>
                </div>
            </section>

            {/* Teach */}
            <section className="mx-auto mt-24 max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="grid gap-10 rounded-3xl bg-brand-900 p-8 text-white sm:p-12 lg:grid-cols-2 lg:items-center">
                    <div>
                        <h2 className="font-display text-3xl font-extrabold tracking-tight sm:text-4xl">
                            Have something to teach?
                        </h2>
                        <p className="mt-4 max-w-md text-brand-100">
                            Build a course from modules, lessons, videos and quizzes. See who enrolled and how far they
                            have got.
                        </p>
                        {!auth.user && (
                            <Link
                                href={route('register')}
                                className="mt-8 inline-flex rounded-full bg-sun-300 px-7 py-3 text-sm font-bold text-brand-950 hover:bg-sun-400"
                            >
                                Register as an instructor
                            </Link>
                        )}
                    </div>

                    <ol className="space-y-3">
                        {teachSteps.map((step, index) => (
                            <li key={step} className="flex items-start gap-4 rounded-2xl bg-brand-800 p-5">
                                <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sun-300 text-sm font-bold text-brand-950">
                                    {index + 1}
                                </span>
                                <span className="pt-1 text-brand-50">{step}</span>
                            </li>
                        ))}
                    </ol>
                </div>
            </section>

            {/* FAQ */}
            <section className="mx-auto max-w-3xl px-4 pt-24 sm:px-6">
                <h2 className="text-center font-display text-3xl font-extrabold tracking-tight text-stone-900 sm:text-4xl">
                    Common questions
                </h2>

                <div className="mt-8 space-y-3">
                    {faqs.map((item) => (
                        <details
                            key={item.q}
                            className="group rounded-2xl border border-stone-200 bg-white px-5 py-4 open:border-brand-700"
                        >
                            <summary className="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-stone-900 [&::-webkit-details-marker]:hidden">
                                {item.q}
                                <span
                                    aria-hidden="true"
                                    className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-800 transition-transform group-open:rotate-45"
                                >
                                    +
                                </span>
                            </summary>
                            <p className="mt-3 text-stone-600">{item.a}</p>
                        </details>
                    ))}
                </div>
            </section>

            {/* Closing call to action */}
            <section className="mx-auto mt-24 max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="rounded-3xl bg-sun-300 px-6 py-16 text-center">
                    <h2 className="font-display text-3xl font-extrabold tracking-tight text-brand-950 sm:text-5xl">
                        Start with one course.
                    </h2>
                    <p className="mx-auto mt-4 max-w-md text-brand-900">
                        Make a free account and open your first course in a minute.
                    </p>

                    <div className="mt-8 flex flex-wrap justify-center gap-3">
                        <Link
                            href={auth.user ? route('dashboard') : route('register')}
                            className="rounded-full bg-brand-900 px-7 py-3 text-sm font-bold text-white hover:bg-brand-800"
                        >
                            {auth.user ? 'Go to dashboard' : 'Create an account'}
                        </Link>
                        <Link
                            href={route('catalog.index')}
                            className="rounded-full bg-white px-7 py-3 text-sm font-bold text-brand-950 hover:bg-stone-100"
                        >
                            Browse courses
                        </Link>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}