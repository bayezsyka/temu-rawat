import PatientQueueStatus from '@/Components/TemuRawat/PatientQueueStatus';
import { Head } from '@inertiajs/react';

export default function Show({ queue, session, remainingBefore, statusMessage }) {
    return (
        <>
            <Head title={`Antrian ${queue.kode_antrian}`} />
            <div className="px-4 py-10 sm:px-6 lg:px-8">
                <PatientQueueStatus
                    queue={queue}
                    session={session}
                    remainingBefore={remainingBefore}
                    statusMessage={statusMessage}
                />
            </div>
        </>
    );
}
