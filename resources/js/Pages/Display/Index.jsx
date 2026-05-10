import PublicQueueDisplay from '@/Components/TemuRawat/PublicQueueDisplay';
import { Head } from '@inertiajs/react';

export default function Index({ session }) {
    return (
        <>
            <Head title="Display Antrian" />
            <PublicQueueDisplay session={session} />
        </>
    );
}
