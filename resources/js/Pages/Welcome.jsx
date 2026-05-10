import { Head, Link, usePage } from '@inertiajs/react';

const primaryButton =
    'inline-flex items-center rounded-2xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-700';

const secondaryButton =
    'inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-teal-300 hover:text-teal-700';

export default function Welcome() {
    const auth = usePage().props.auth;

    return (
        <>
            <Head title="Temu Rawat" />
            <div className="relative overflow-hidden">
                <div className="absolute inset-x-0 top-0 -z-10 h-[32rem] bg-[radial-gradient(circle_at_top_right,rgba(15,141,131,0.25),transparent_35%),radial-gradient(circle_at_top_left,rgba(243,181,90,0.25),transparent_28%)]" />
                <div className="mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-10 lg:px-8">
                    <header className="flex items-center justify-between">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-teal-600">
                                Temu Rawat
                            </p>
                            <h1 className="mt-2 text-xl font-extrabold text-slate-900">
                                Sistem pendaftaran dan antrian praktik dokter
                            </h1>
                        </div>
                        <div className="flex gap-3">
                            {auth.user ? (
                                <Link href={route('panel.index')} className={secondaryButton}>
                                    Panel
                                </Link>
                            ) : (
                                <Link href={route('login')} className={secondaryButton}>
                                    Login staf
                                </Link>
                            )}
                            <Link href={route('registration.create')} className={primaryButton}>
                                Daftar pasien
                            </Link>
                        </div>
                    </header>

                    <main className="grid flex-1 items-center gap-10 py-16 lg:grid-cols-[1.08fr_0.92fr]">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">
                                MVP ringan, realtime, siap dikembangkan
                            </p>
                            <h2 className="mt-6 max-w-3xl text-5xl font-black leading-tight text-slate-950">
                                Satu alur sederhana untuk daftar, tunggu, panggil, dan selesai.
                            </h2>
                            <p className="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
                                Pasien daftar lewat web, sistem memberi nomor otomatis, lalu halaman pasien, panel staf, dan display umum bergerak realtime tanpa refresh manual.
                            </p>
                            <div className="mt-8 flex flex-wrap gap-4">
                                <Link href={route('registration.create')} className={primaryButton}>
                                    Buka pendaftaran
                                </Link>
                                <Link href={route('display.index')} className={secondaryButton}>
                                    Lihat display
                                </Link>
                            </div>
                        </div>

                        <div className="grid gap-4">
                            <FeatureCard title="Pendaftaran pasien" body="Form singkat untuk pasien online maupun datang langsung." />
                            <FeatureCard title="Status antrian realtime" body="Pasien melihat nomor aktif, sisa antrian, dan status tanpa refresh." />
                            <FeatureCard title="Panel staf" body="Asisten dan dokter dapat memanggil, mulai periksa, lewati, batalkan, dan menyelesaikan antrian." />
                        </div>
                    </main>
                </div>
            </div>
        </>
    );
}

function FeatureCard({ title, body }) {
    return (
        <div className="rounded-[2rem] border border-white/70 bg-white/90 p-6 shadow-[0_20px_60px_rgba(15,45,59,0.08)]">
            <p className="text-lg font-bold text-slate-900">{title}</p>
            <p className="mt-2 text-sm leading-7 text-slate-600">{body}</p>
        </div>
    );
}
