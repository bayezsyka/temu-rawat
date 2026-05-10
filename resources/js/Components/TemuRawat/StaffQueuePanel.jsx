import FlashMessage from '@/Components/TemuRawat/FlashMessage';
import InitialCheckForm from '@/Components/TemuRawat/InitialCheckForm';
import StatusBadge from '@/Components/TemuRawat/StatusBadge';
import useRealtimeReload from '@/hooks/useRealtimeReload';
import { router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

const actions = [
    ['panggil', 'Panggil'],
    ['mulai_periksa', 'Mulai periksa'],
    ['selesai', 'Selesai'],
    ['lewati', 'Lewati'],
    ['batalkan', 'Batalkan'],
];

export default function StaffQueuePanel({ session, queues }) {
    const { flash } = usePage().props;
    const [selectedId, setSelectedId] = useState(session?.current_queue?.id || queues[0]?.id || null);

    useEffect(() => {
        if (!queues.some((queue) => queue.id === selectedId)) {
            setSelectedId(session?.current_queue?.id || queues[0]?.id || null);
        }
    }, [queues, selectedId, session?.current_queue?.id]);

    useRealtimeReload({
        publicChannels: ['practice-overview', session && `practice-session.${session.id}`],
        privateChannels: ['staff-panel'],
        only: ['session', 'queues', 'flash'],
    });

    const selectedQueue = queues.find((queue) => queue.id === selectedId) || null;

    const runAction = (action) => {
        if (!selectedQueue) {
            return;
        }

        router.patch(route('panel.queues.status', selectedQueue.id), { action }, { preserveScroll: true });
    };

    return (
        <div className="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <div className="space-y-5">
                <div className="rounded-[2rem] border border-white/80 bg-white/95 p-6 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-teal-600">Panel Antrian</p>
                            <h1 className="mt-2 text-3xl font-extrabold text-slate-900">Daftar antrian hari ini</h1>
                        </div>
                        {session ? <StatusBadge status={session.status} /> : null}
                    </div>

                    <div className="mt-6">
                        <FlashMessage flash={flash} />
                    </div>

                    <div className="mt-6 space-y-3">
                        {queues.length ? (
                            queues.map((queue) => (
                                <button
                                    key={queue.id}
                                    type="button"
                                    onClick={() => setSelectedId(queue.id)}
                                    className={`w-full rounded-[1.5rem] border p-4 text-left transition ${
                                        selectedId === queue.id
                                            ? 'border-teal-500 bg-teal-50'
                                            : 'border-slate-200 bg-white hover:border-teal-200 hover:bg-slate-50'
                                    }`}
                                >
                                    <div className="flex items-center justify-between gap-3">
                                        <div>
                                            <p className="text-xl font-bold text-slate-900">{queue.kode_antrian}</p>
                                            <p className="text-sm text-slate-600">{queue.patient.nama}</p>
                                        </div>
                                        <StatusBadge status={queue.status} />
                                    </div>
                                    <p className="mt-3 line-clamp-2 text-sm text-slate-500">
                                        {queue.keluhan || 'Belum ada keluhan.'}
                                    </p>
                                </button>
                            ))
                        ) : (
                            <div className="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                                Belum ada antrian hari ini.
                            </div>
                        )}
                    </div>
                </div>
            </div>

            <div className="space-y-5">
                <div className="rounded-[2rem] border border-white/80 bg-white/95 p-6 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
                    <div className="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Pasien aktif</p>
                            <h2 className="mt-2 text-2xl font-extrabold text-slate-900">
                                {selectedQueue ? selectedQueue.patient.nama : 'Belum dipilih'}
                            </h2>
                        </div>
                        {selectedQueue ? <StatusBadge status={selectedQueue.status} /> : null}
                    </div>

                    {selectedQueue ? (
                        <>
                            <div className="mt-6 grid gap-4 md:grid-cols-2">
                                <Detail label="Nomor antrian" value={selectedQueue.kode_antrian} />
                                <Detail label="WhatsApp" value={selectedQueue.patient.nomor_whatsapp} />
                                <Detail label="Usia" value={selectedQueue.patient.usia || '-'} />
                                <Detail label="Jenis kelamin" value={selectedQueue.patient.jenis_kelamin || '-'} />
                                <Detail label="Status kunjungan" value={selectedQueue.status_kunjungan} />
                                <Detail label="Metode daftar" value={selectedQueue.metode_daftar} />
                            </div>

                            <div className="mt-4 rounded-[1.5rem] bg-slate-50 p-4">
                                <p className="text-sm font-semibold text-slate-700">Keluhan</p>
                                <p className="mt-2 text-sm text-slate-600">{selectedQueue.keluhan || 'Belum ada keluhan.'}</p>
                            </div>

                            <div className="mt-5 flex flex-wrap gap-3">
                                {actions.map(([value, label]) => (
                                    <button
                                        key={value}
                                        type="button"
                                        onClick={() => runAction(value)}
                                        className="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-teal-300 hover:text-teal-700"
                                    >
                                        {label}
                                    </button>
                                ))}
                            </div>
                        </>
                    ) : null}
                </div>

                <div className="rounded-[2rem] border border-white/80 bg-white/95 p-6 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
                    <p className="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Pemeriksaan awal</p>
                    <div className="mt-4">
                        <InitialCheckForm queue={selectedQueue} />
                    </div>
                </div>
            </div>
        </div>
    );
}

function Detail({ label, value }) {
    return (
        <div className="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{label}</p>
            <p className="mt-2 text-base font-semibold text-slate-900">{value}</p>
        </div>
    );
}
