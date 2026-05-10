import FlashMessage from '@/Components/TemuRawat/FlashMessage';
import StatusBadge from '@/Components/TemuRawat/StatusBadge';
import { useForm, usePage } from '@inertiajs/react';

const statuses = ['buka', 'istirahat', 'selesai'];
const inputClass =
    'w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white focus:ring-0';

export default function PracticeSessionControl({ session }) {
    const { flash } = usePage().props;
    const form = useForm({
        nama_dokter: session?.nama_dokter || '',
        status: session?.status || 'buka',
        nomor_awal: session?.nomor_terakhir ? session.nomor_terakhir + 1 : 1,
    });

    const submit = (event) => {
        event.preventDefault();
        form.put(route('practice-sessions.upsert'), { preserveScroll: true });
    };

    const quickStatus = (status) => {
        form.setData('status', status);
        form.put(route('practice-sessions.upsert'), { preserveScroll: true });
    };

    return (
        <div className="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <form onSubmit={submit} className="space-y-5 rounded-[2rem] border border-white/80 bg-white/95 p-6 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-teal-600">Sesi Praktik</p>
                        <h1 className="mt-2 text-3xl font-extrabold text-slate-900">Kontrol sesi hari ini</h1>
                    </div>
                    {session ? <StatusBadge status={session.status} /> : null}
                </div>

                <FlashMessage flash={flash} />

                <label className="block space-y-2">
                    <span className="text-sm font-semibold text-slate-700">Nama dokter</span>
                    <input value={form.data.nama_dokter} onChange={(e) => form.setData('nama_dokter', e.target.value)} className={inputClass} />
                </label>

                <div className="grid gap-4 md:grid-cols-2">
                    <label className="block space-y-2">
                        <span className="text-sm font-semibold text-slate-700">Status praktik</span>
                        <select value={form.data.status} onChange={(e) => form.setData('status', e.target.value)} className={inputClass}>
                            {statuses.map((status) => (
                                <option key={status} value={status}>
                                    {status}
                                </option>
                            ))}
                        </select>
                    </label>
                    <label className="block space-y-2">
                        <span className="text-sm font-semibold text-slate-700">Nomor awal</span>
                        <input type="number" min="1" value={form.data.nomor_awal} onChange={(e) => form.setData('nomor_awal', e.target.value)} className={inputClass} />
                    </label>
                </div>

                <button
                    type="submit"
                    disabled={form.processing}
                    className="rounded-2xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:opacity-70"
                >
                    {form.processing ? 'Menyimpan...' : 'Simpan sesi praktik'}
                </button>
            </form>

            <div className="space-y-5 rounded-[2rem] border border-white/80 bg-white/95 p-6 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
                <div>
                    <p className="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Aksi cepat</p>
                    <div className="mt-4 flex flex-wrap gap-3">
                        {statuses.map((status) => (
                            <button
                                key={status}
                                type="button"
                                onClick={() => quickStatus(status)}
                                className="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-teal-300 hover:text-teal-700"
                            >
                                Ubah ke {status}
                            </button>
                        ))}
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <Summary label="Status saat ini" value={session?.status || 'Belum dibuka'} />
                    <Summary label="Nomor terakhir" value={session ? `A-${String(session.nomor_terakhir).padStart(3, '0')}` : '-'} />
                    <Summary label="Sedang dilayani" value={session?.current_queue?.kode_antrian || 'Belum ada'} />
                    <Summary label="Pasien menunggu" value={String(session?.waiting_count || 0)} />
                </div>
            </div>
        </div>
    );
}

function Summary({ label, value }) {
    return (
        <div className="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{label}</p>
            <p className="mt-2 text-2xl font-extrabold text-slate-900">{value}</p>
        </div>
    );
}
