import { buttonPrimary, buttonSecondary } from '@/lib/buttons';
import { Link } from '@inertiajs/react';

export default function StepNav({ prev, next, emphasizeNext = false }) {
    return (
        <div className="flex items-center gap-2">
            {prev && (
                <Link href={prev.url} className={buttonSecondary}>
                    ← Previous
                </Link>
            )}
            {next && (
                <Link href={next.url} className={emphasizeNext ? buttonPrimary : buttonSecondary}>
                    Next →
                </Link>
            )}
        </div>
    );
}