import PracticeSessionControl from '@/Components/TemuRawat/PracticeSessionControl';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useRealtimeReload from '@/Hooks/useRealtimeReload';
import { Head } from '@inertiajs/react';

export default function Sessions({ session }) {
    useRealtimeReload({
        publicChannels: ['practice-overview', session && `practice-session.${session.id}`],
        privateChannels: ['panel'],
        only: ['session', 'flash'],
    });

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-sm font-semibold uppercase tracking-[0.2em] text-teal-600">
                        Admin
                    </p>
                    <h2 className="mt-2 text-2xl font-extrabold text-slate-900">
                        Pengaturan sesi praktik
                    </h2>
                </div>
            }
        >
            <Head title="Sesi Praktik" />
            <PracticeSessionControl session={session} />
        </AuthenticatedLayout>
    );
}
