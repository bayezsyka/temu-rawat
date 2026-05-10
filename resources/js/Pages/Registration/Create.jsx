import PatientRegistrationForm from '@/Components/TemuRawat/PatientRegistrationForm';
import { Head } from '@inertiajs/react';

export default function Create({ session }) {
    return (
        <>
            <Head title="Daftar Pasien" />
            <div className="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
                <PatientRegistrationForm session={session} />
            </div>
        </>
    );
}
