import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useRealtimeReload from '@/hooks/useRealtimeReload';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import Swal from 'sweetalert2';

const inputClass =
    'w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white focus:ring-0';

export default function PracticeSessions({ sessions, doctors }) {
    const { flash } = usePage().props;
    const form = useForm({
        doctor_id: doctors[0]?.id || '',
        status: 'buka',
    });

    useRealtimeReload({
        publicChannels: ['practice-overview', ...sessions.map((session) => `practice-session.${session.id}`)],
        privateChannels: ['staff-panel'],
        only: ['sessions', 'flash'],
    });

    const submit = (event) => {
        event.preventDefault();
        form.post(route('practice-sessions.store'));
    };

    const changeStatus = async (session, status) => {
        if (status === 'selesai') {
            const result = await Swal.fire({
                title: 'Tutup sesi praktik?',
                text: `Sesi ${session.doctor?.nama} akan ditandai selesai.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, tutup sesi',
                cancelButtonText: 'Batal',
            });

            if (!result.isConfirmed) return;
        }

        router.patch(route('practice-sessions.update', session.id), { status }, {
            preserveScroll: true,
            onSuccess: () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Sesi diperbarui',
                    timer: 1400,
                    showConfirmButton: false,
                });
            },
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-sm font-semibold uppercase tracking-[0.24em] text-teal-600">Admin Klinik</p>
                    <h2 className="mt-2 text-2xl font-black text-slate-900">Sesi praktik multi dokter</h2>
                </div>
            }
        >
            <Head title="Admin Sesi Praktik" />

            <div className="space-y-6">
                <form onSubmit={submit} className="rounded-[2rem] border border-white/80 bg-white/95 p-6 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
                    <div className="grid gap-4 lg:grid-cols-[1fr_220px_auto]">
                        <label className="space-y-2">
                            <span className="text-sm font-semibold text-slate-700">Pilih dokter</span>
                            <select value={form.data.doctor_id} onChange={(event) => form.setData('doctor_id', event.target.value)} className={inputClass}>
                                {doctors.map((doctor) => (
                                    <option key={doctor.id} value={doctor.id}>
                                        {doctor.nama} {doctor.spesialisasi ? `- ${doctor.spesialisasi}` : ''}
                                    </option>
                                ))}
                            </select>
                        </label>
                        <label className="space-y-2">
                            <span className="text-sm font-semibold text-slate-700">Status sesi</span>
                            <select value={form.data.status} onChange={(event) => form.setData('status', event.target.value)} className={inputClass}>
                                <option value="buka">Buka</option>
                                <option value="istirahat">Istirahat</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </label>
                        <div className="flex items-end">
                            <button type="submit" disabled={form.processing} className="rounded-3xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:opacity-70">
                                {form.processing ? 'Menyimpan...' : 'Buka / perbarui sesi'}
                            </button>
                        </div>
                    </div>
                    {flash?.success ? <p className="mt-4 text-sm text-emerald-600">{flash.success}</p> : null}
                </form>

                <div className="grid gap-5 xl:grid-cols-3">
                    {sessions.length ? sessions.map((session) => (
                        <article key={session.id} className="rounded-[2rem] border border-white/80 bg-white/95 p-6 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-teal-600">{session.status}</p>
                            <h3 className="mt-3 text-2xl font-black text-slate-900">{session.doctor?.nama}</h3>
                            <p className="mt-1 text-sm text-slate-600">{session.doctor?.spesialisasi || 'Dokter umum'}</p>

                            <div className="mt-5 grid gap-3 sm:grid-cols-2">
                                <Metric label="Menunggu" value={session.waiting_count} />
                                <Metric label="Nomor terakhir" value={session.nomor_terakhir} />
                                <Metric label="Sedang dilayani" value={session.current_queue?.kode_antrian || '--'} />
                                <Metric label="Berikutnya" value={session.next_queues[0]?.kode_antrian || '--'} />
                            </div>

                            <div className="mt-5 flex flex-wrap gap-3">
                                <ActionButton label="Buka" onClick={() => changeStatus(session, 'buka')} />
                                <ActionButton label="Istirahat" onClick={() => changeStatus(session, 'istirahat')} />
                                <ActionButton label="Selesai" onClick={() => changeStatus(session, 'selesai')} danger />
                            </div>
                        </article>
                    )) : (
                        <div className="rounded-[2rem] border border-dashed border-slate-300 bg-white/80 p-6 text-sm text-slate-500">
                            Belum ada sesi praktik hari ini.
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function Metric({ label, value }) {
    return (
        <div className="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{label}</p>
            <p className="mt-2 text-2xl font-black text-slate-900">{value}</p>
        </div>
    );
}

function ActionButton({ label, onClick, danger = false }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`rounded-3xl px-4 py-3 text-sm font-semibold transition ${danger ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'border border-slate-200 bg-white text-slate-700 hover:border-teal-300 hover:text-teal-700'}`}
        >
            {label}
        </button>
    );
}
