export default function ProgressBar({ percent, label = 'Progress' }) {
    return (
        <div
            className="h-2 overflow-hidden rounded-full bg-stone-200"
            role="progressbar"
            aria-label={label}
            aria-valuemin={0}
            aria-valuemax={100}
            aria-valuenow={percent}
        >
            <div className="h-full bg-brand-600" style={{ width: `${percent}%` }} />
        </div>
    );
}