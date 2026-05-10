import useRealtimeReload from '@/hooks/useRealtimeReload';
import { Head } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';

export default function DisplayIndex({ sessions }) {
    const sessionChannels = sessions.map((session) => `practice-session.${session.id}`);

    useRealtimeReload({
        publicChannels: ['practice-overview', ...sessionChannels],
        only: ['sessions'],
        pollInterval: 10000,
    });

    const visibleSessions = useMemo(
        () => sessions.filter((session) => session.status !== 'selesai'),
        [sessions],
    );

    return (
        <>
            <Head title="Display Antrian" />
            <div className="min-h-screen bg-[radial-gradient(circle_at_top,#12303b_0%,#0f172a_55%,#020617_100%)] px-4 py-8 text-white sm:px-6 lg:px-10">
                <div className="mx-auto max-w-7xl">
                    <p className="text-sm font-semibold uppercase tracking-[0.35em] text-teal-300">Temu Rawat</p>
                    <h1 className="mt-4 text-4xl font-black tracking-tight sm:text-5xl">Praktik Hari Ini</h1>

                    {visibleSessions.length ? (
                        <div className="mt-8 grid gap-5 xl:grid-cols-3">
                            {visibleSessions.map((session) => (
                                <article key={session.id} className="rounded-[2rem] border border-white/10 bg-white/10 p-6 backdrop-blur">
                                    <p className="text-sm font-semibold uppercase tracking-[0.2em] text-teal-200">{session.status}</p>
                                    <h2 className="mt-3 text-2xl font-black">{session.doctor?.nama || 'Dokter'}</h2>
                                    <p className="mt-1 text-sm text-white/70">{session.doctor?.spesialisasi || 'Layanan umum'}</p>

                                    <div className="mt-6 rounded-[1.5rem] bg-teal-400/90 p-5 text-slate-950">
                                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-slate-800">Sedang dilayani</p>
                                        <p className="mt-2 text-5xl font-black tracking-tight">{session.current_queue?.kode_antrian || '--'}</p>
                                    </div>

                                    <div className="mt-5 grid gap-4 lg:grid-cols-2">
                                        <div className="rounded-[1.5rem] bg-white/10 p-4">
                                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-white/60">Berikutnya</p>
                                            <p className="mt-3 text-lg font-bold leading-8">
                                                {session.next_queues.length
                                                    ? session.next_queues.map((queue) => queue.kode_antrian).join(', ')
                                                    : 'Belum ada'}
                                            </p>
                                        </div>
                                        <div className="rounded-[1.5rem] bg-white/10 p-4">
                                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-white/60">Menunggu</p>
                                            <p className="mt-3 text-4xl font-black">{session.waiting_count}</p>
                                        </div>
                                    </div>
                                </article>
                            ))}
                        </div>
                    ) : (
                        <div className="mt-8 rounded-[2rem] border border-white/10 bg-white/10 p-8 text-center text-white/75">
                            Belum ada sesi praktik yang sedang berjalan.
                        </div>
                    )}

                    <div className="mt-10 text-center">
                        <LiveClock />
                    </div>
                </div>
            </div>
        </>
    );
}

function LiveClock() {
    const [now, setNow] = useState(new Date());

    useEffect(() => {
        const timer = window.setInterval(() => setNow(new Date()), 1000);
        return () => window.clearInterval(timer);
    }, []);

    const time = new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    }).format(now);

    const date = new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(now);

    return (
        <div>
            <p className="text-5xl font-black tracking-[0.18em]">{time}</p>
            <p className="mt-3 text-sm uppercase tracking-[0.3em] text-white/60">{date}</p>
        </div>
    );
}
