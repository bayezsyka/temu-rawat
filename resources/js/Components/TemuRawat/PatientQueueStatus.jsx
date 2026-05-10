import FlashMessage from '@/Components/TemuRawat/FlashMessage';
import StatusBadge from '@/Components/TemuRawat/StatusBadge';
import useRealtimeReload from '@/Hooks/useRealtimeReload';
import { usePage } from '@inertiajs/react';

export default function PatientQueueStatus({
    queue,
    session,
    remainingBefore,
    statusMessage,
}) {
    const { flash } = usePage().props;

    useRealtimeReload({
        publicChannels: ['practice-overview', session && `practice-session.${session.id}`],
        only: ['queue', 'session', 'remainingBefore', 'statusMessage'],
    });

    return (
        <div className="mx-auto max-w-4xl space-y-6">
            <div className="rounded-[2rem] border border-white/80 bg-white/95 p-8 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
                <p className="text-sm font-semibold uppercase tracking-[0.2em] text-teal-600">
                    Status Antrian
                </p>
                <div className="mt-4 flex flex-wrap items-center gap-4">
                    <h1 className="text-5xl font-black tracking-tight text-slate-900">
                        {queue.kode_antrian}
                    </h1>
                    <StatusBadge status={queue.status} className="text-sm" />
                </div>
                <p className="mt-4 max-w-2xl text-sm text-slate-600">{statusMessage}</p>
                <div className="mt-6">
                    <FlashMessage flash={flash} />
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-3">
                <MetricCard label="Nomor sedang dilayani" value={session?.current_queue?.kode_antrian || 'Belum ada'} />
                <MetricCard label="Sisa sebelum Anda" value={String(remainingBefore)} />
                <MetricCard label="Status praktik" value={session?.status || 'Belum dibuka'} />
            </div>
        </div>
    );
}

function MetricCard({ label, value }) {
    return (
        <div className="rounded-[1.75rem] border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{label}</p>
            <p className="mt-3 text-3xl font-extrabold text-slate-900">{value}</p>
        </div>
    );
}
