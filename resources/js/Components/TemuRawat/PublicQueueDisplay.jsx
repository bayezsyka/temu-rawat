import StatusBadge from '@/Components/TemuRawat/StatusBadge';
import useRealtimeReload from '@/Hooks/useRealtimeReload';
import { useEffect, useState } from 'react';

export default function PublicQueueDisplay({ session }) {
    useRealtimeReload({
        publicChannels: ['practice-overview', session && `practice-session.${session.id}`],
        only: ['session'],
    });

    if (!session) {
        return (
            <div className="flex min-h-screen items-center justify-center bg-slate-950 p-6 text-white">
                <div className="w-full max-w-3xl rounded-[2.5rem] border border-white/40 bg-slate-950/90 p-10 text-center shadow-[0_40px_120px_rgba(2,6,23,0.35)]">
                    <p className="text-sm font-semibold uppercase tracking-[0.24em] text-teal-300">
                        Temu Rawat
                    </p>
                    <h1 className="mt-4 text-4xl font-black">
                        Sesi praktik belum dibuka
                    </h1>
                    <p className="mt-4 text-slate-300">
                        Silakan tunggu admin membuka sesi hari ini.
                    </p>

                    <LiveClock />
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-slate-950 px-6 py-10 text-white">
            <div className="mx-auto flex min-h-[calc(100vh-5rem)] max-w-6xl flex-col space-y-8">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.24em] text-teal-300">
                            Display Antrian
                        </p>
                        <h1 className="mt-3 text-2xl font-bold text-white/95">
                            Praktik hari ini
                        </h1>
                    </div>

                    <StatusBadge status={session.status} className="text-sm" />
                </div>

                <div className="rounded-[2.5rem] border border-white/10 bg-gradient-to-br from-teal-500 via-cyan-500 to-sky-500 p-10 text-slate-950 shadow-[0_40px_120px_rgba(14,165,233,0.18)]">
                    <p className="text-sm font-semibold uppercase tracking-[0.24em] text-slate-800">
                        Sedang dilayani
                    </p>

                    <div className="mt-6 text-8xl font-black tracking-tight">
                        {session.current_queue?.kode_antrian || '--'}
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <div className="rounded-[2rem] border border-white/10 bg-white/5 p-6">
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-slate-300">
                            Antrian berikutnya
                        </p>

                        <div className="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {session.upcoming_queues.length ? (
                                session.upcoming_queues.map((queueItem) => (
                                    <div
                                        key={queueItem.id}
                                        className="rounded-[1.75rem] bg-white/10 p-5 text-center"
                                    >
                                        <p className="text-4xl font-black">
                                            {queueItem.kode_antrian}
                                        </p>

                                        {queueItem.nama_samaran ? (
                                            <p className="mt-2 text-sm text-slate-300">
                                                {queueItem.nama_samaran}
                                            </p>
                                        ) : null}
                                    </div>
                                ))
                            ) : (
                                <div className="rounded-[1.75rem] bg-white/10 p-5 text-slate-300">
                                    Belum ada antrian berikutnya.
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="space-y-4">
                        <StatCard
                            label="Pasien menunggu"
                            value={String(session.waiting_count)}
                        />

                        <StatCard
                            label="Nomor terakhir"
                            value={`A-${String(session.nomor_terakhir).padStart(3, '0')}`}
                        />
                    </div>
                </div>

                <div className="mt-auto">
                    <LiveClock />
                </div>
            </div>
        </div>
    );
}

function StatCard({ label, value }) {
    return (
        <div className="rounded-[2rem] border border-white/10 bg-white/5 p-6">
            <p className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">
                {label}
            </p>
            <p className="mt-3 text-4xl font-black text-white">
                {value}
            </p>
        </div>
    );
}

function LiveClock() {
    const [now, setNow] = useState(new Date());

    useEffect(() => {
        const timer = window.setInterval(() => {
            setNow(new Date());
        }, 1000);

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

    const registrationUrl = `${window.location.origin}/daftar`;
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(registrationUrl)}&bgcolor=020617&color=ffffff&margin=10`;

    return (
        <div className="flex items-center justify-center space-x-10 border-t border-white/10 pt-8">
            <div className="text-left text-white/70">
                <p className="text-4xl font-black tracking-widest text-white">{time}</p>
                <p className="mt-1 text-sm capitalize tracking-[0.2em]">
                    {date}
                </p>
            </div>

            <div className="flex flex-col items-center space-y-2">
                <div className="rounded-2xl border border-white/20 bg-white/5 p-2 shadow-2xl">
                    <img
                        src={qrUrl}
                        alt="QR Daftar"
                        className="h-24 w-24 rounded-lg opacity-90 transition-opacity hover:opacity-100"
                    />
                </div>
                <p className="text-[10px] font-bold uppercase tracking-[0.3em] text-teal-400">
                    Daftar Online
                </p>
            </div>
        </div>
    );
}
