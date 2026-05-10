const styles = {
    buka: 'bg-emerald-100 text-emerald-700',
    istirahat: 'bg-amber-100 text-amber-700',
    selesai: 'bg-slate-200 text-slate-700',
    menunggu: 'bg-sky-100 text-sky-700',
    dipanggil: 'bg-amber-100 text-amber-700',
    diperiksa: 'bg-violet-100 text-violet-700',
    dilewati: 'bg-orange-100 text-orange-700',
    batal: 'bg-rose-100 text-rose-700',
};

export default function StatusBadge({ status, className = '' }) {
    return (
        <span
            className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize ${styles[status] || 'bg-slate-100 text-slate-700'} ${className}`}
        >
            {status}
        </span>
    );
}
