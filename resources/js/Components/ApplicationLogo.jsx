export default function ApplicationLogo({ className = '' }) {
    return (
        <div className={`flex items-center gap-3 ${className}`}>
            <div className="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-600 text-sm font-extrabold text-white shadow-lg shadow-teal-200">
                TR
            </div>
            <div className="hidden sm:block">
                <div className="text-sm font-extrabold uppercase tracking-[0.22em] text-teal-700">
                    Temu Rawat
                </div>
                <div className="text-xs text-slate-500">
                    Antrian praktik dokter
                </div>
            </div>
        </div>
    );
}
