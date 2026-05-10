import FlashMessage from '@/Components/TemuRawat/FlashMessage';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

const inputClass =
    'w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white focus:ring-0';

export default function QueueRegistration({ account, profiles, selectedPatientId, sessions }) {
    const { flash } = usePage().props;
    const form = useForm({
        patient_id: selectedPatientId || profiles[0]?.id || '',
        practice_session_id: sessions[0]?.id || '',
        keluhan: '',
        status_kunjungan: 'baru',
        metode_daftar: 'online',
    });

    const submit = (event) => {
        event.preventDefault();
        form.post(route('registration.store'));
    };

    return (
        <>
            <Head title="Daftar Antrian" />
            <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                <div className="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
                    <form onSubmit={submit} className="rounded-[2rem] border border-white/80 bg-white/95 p-8 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
                        <p className="text-sm font-semibold uppercase tracking-[0.24em] text-teal-600">Daftar Multi Dokter</p>
                        <h1 className="mt-3 text-3xl font-black tracking-tight text-slate-900">Pilih profil, dokter, dan sesi praktik aktif</h1>
                        <p className="mt-3 text-sm text-slate-600">
                            Nomor WhatsApp aktif: <span className="font-semibold text-slate-800">{account.nomor_whatsapp}</span>
                        </p>
                        <div className="mt-5">
                            <FlashMessage flash={flash} />
                        </div>

                        <section className="mt-6">
                            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Profil pasien</p>
                            <div className="mt-3 grid gap-3">
                                {profiles.map((profile) => (
                                    <label key={profile.id} className={`rounded-[1.5rem] border p-4 transition ${String(form.data.patient_id) === String(profile.id) ? 'border-teal-500 bg-teal-50' : 'border-slate-200 bg-slate-50/70'}`}>
                                        <div className="flex items-start gap-3">
                                            <input
                                                type="radio"
                                                name="patient_id"
                                                value={profile.id}
                                                checked={String(form.data.patient_id) === String(profile.id)}
                                                onChange={() => form.setData('patient_id', profile.id)}
                                                className="mt-1"
                                            />
                                            <div>
                                                <p className="font-bold text-slate-900">{profile.nama}</p>
                                                <p className="text-sm text-slate-600">{profile.hubungan} • {profile.jenis_kelamin || '-'} • {profile.usia || '-'} tahun</p>
                                                <p className="text-xs uppercase tracking-[0.2em] text-slate-500">NIK: {profile.masked_nik || 'Belum diisi'}</p>
                                            </div>
                                        </div>
                                    </label>
                                ))}
                            </div>
                            <div className="mt-3">
                                <Link href={route('patient.profile.index')} className="text-sm font-semibold text-teal-700 underline underline-offset-4">
                                    Kelola profil pasien
                                </Link>
                            </div>
                        </section>

                        <section className="mt-8">
                            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Sesi praktik aktif</p>
                            <div className="mt-3 grid gap-3">
                                {sessions.length ? sessions.map((session) => (
                                    <label key={session.id} className={`rounded-[1.5rem] border p-4 transition ${String(form.data.practice_session_id) === String(session.id) ? 'border-teal-500 bg-teal-50' : 'border-slate-200 bg-slate-50/70'}`}>
                                        <div className="flex items-start gap-3">
                                            <input
                                                type="radio"
                                                name="practice_session_id"
                                                value={session.id}
                                                checked={String(form.data.practice_session_id) === String(session.id)}
                                                onChange={() => form.setData('practice_session_id', session.id)}
                                                className="mt-1"
                                            />
                                            <div className="flex-1">
                                                <div className="flex flex-wrap items-center justify-between gap-3">
                                                    <div>
                                                        <p className="font-bold text-slate-900">{session.doctor?.nama}</p>
                                                        <p className="text-sm text-slate-600">{session.doctor?.spesialisasi || 'Dokter umum'} • status {session.status}</p>
                                                    </div>
                                                    <div className="rounded-2xl bg-white px-3 py-2 text-right">
                                                        <p className="text-xs uppercase tracking-[0.2em] text-slate-500">Menunggu</p>
                                                        <p className="text-lg font-black text-slate-900">{session.waiting_count}</p>
                                                    </div>
                                                </div>
                                                <p className="mt-3 text-sm text-slate-600">
                                                    Sedang dilayani: <span className="font-semibold text-slate-800">{session.current_queue?.kode_antrian || 'Belum ada'}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </label>
                                )) : (
                                    <div className="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                                        Belum ada sesi praktik aktif. Minta admin membuka sesi lebih dulu.
                                    </div>
                                )}
                            </div>
                        </section>

                        <section className="mt-8 grid gap-4 md:grid-cols-2">
                            <Field label="Status kunjungan" error={form.errors.status_kunjungan}>
                                <select value={form.data.status_kunjungan} onChange={(event) => form.setData('status_kunjungan', event.target.value)} className={inputClass}>
                                    <option value="baru">Baru</option>
                                    <option value="lama">Lama</option>
                                </select>
                            </Field>
                            <Field label="Metode daftar" error={form.errors.metode_daftar}>
                                <select value={form.data.metode_daftar} onChange={(event) => form.setData('metode_daftar', event.target.value)} className={inputClass}>
                                    <option value="online">Online</option>
                                    <option value="langsung">Langsung</option>
                                </select>
                            </Field>
                            <div className="md:col-span-2">
                                <Field label="Keluhan singkat" error={form.errors.keluhan}>
                                    <textarea rows="4" value={form.data.keluhan} onChange={(event) => form.setData('keluhan', event.target.value)} className={`${inputClass} resize-none`} />
                                </Field>
                            </div>
                        </section>

                        <div className="mt-8">
                            <button type="submit" disabled={form.processing || !sessions.length} className="rounded-3xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:opacity-70">
                                {form.processing ? 'Mendaftarkan...' : 'Daftarkan pasien'}
                            </button>
                        </div>
                    </form>

                    <aside className="space-y-4 rounded-[2rem] bg-[linear-gradient(135deg,#0f172a_0%,#155e75_55%,#14b8a6_100%)] p-8 text-white shadow-[0_30px_90px_rgba(8,47,73,0.28)]">
                        <Info title="Nomor antrian per dokter" value="A-001, B-001, C-001" />
                        <Info title="Akses tetap sederhana" value="Pasien cukup pakai OTP WhatsApp dan profil keluarga." />
                        <Info title="Ringkasan hasil" value="Resep dan hasil singkat bisa dibuka pasien selama 7 hari setelah selesai." />
                    </aside>
                </div>
            </div>
        </>
    );
}

function Field({ label, error, children }) {
    return (
        <label className="block space-y-2">
            <span className="text-sm font-semibold text-slate-700">{label}</span>
            {children}
            {error ? <span className="text-xs text-rose-600">{error}</span> : null}
        </label>
    );
}

function Info({ title, value }) {
    return (
        <div className="rounded-[1.75rem] border border-white/10 bg-white/10 p-5">
            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-white/70">{title}</p>
            <p className="mt-3 text-xl font-bold">{value}</p>
        </div>
    );
}
