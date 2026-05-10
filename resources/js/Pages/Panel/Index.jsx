import StaffQueuePanel from '@/Components/TemuRawat/StaffQueuePanel';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Index({ session, queues }) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-sm font-semibold uppercase tracking-[0.2em] text-teal-600">
                        Temu Rawat
                    </p>
                    <h2 className="mt-2 text-2xl font-extrabold text-slate-900">
                        Panel dokter dan asisten
                    </h2>
                </div>
            }
        >
            <Head title="Panel" />
            <StaffQueuePanel session={session} queues={queues} />
        </AuthenticatedLayout>
    );
}
