export default function StatCard({ label, value }) {
    return (
        <div className="rounded-md border border-stone-200 bg-white p-4">
            <p className="text-sm text-stone-500">{label}</p>
            <p className="mt-1 font-display text-3xl font-semibold text-stone-900">{value}</p>
        </div>
    );
}