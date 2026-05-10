import FlashMessage from '@/Components/TemuRawat/FlashMessage';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

export default function ProfileIndex({ account, profiles }) {
    const { flash } = usePage().props;
    const form = useForm({ patient_id: null });

    const chooseProfile = (patientId) => {
        form.setData('patient_id', patientId);
        form.post(route('patient.profile.store'));
    };

    return (
        <>
            <Head title="Profil Pasien" />
            <div className="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
                <div className="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
                    <section className="rounded-[2rem] border border-white/80 bg-white/95 p-8 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
                        <p className="text-sm font-semibold uppercase tracking-[0.24em] text-teal-600">Profil Pasien</p>
                        <h1 className="mt-3 text-3xl font-black tracking-tight text-slate-900">Pilih profil sebelum daftar antrian</h1>
                        <p className="mt-3 text-sm text-slate-600">
                            Nomor WhatsApp aktif: <span className="font-semibold text-slate-800">{account.nomor_whatsapp}</span>
                        </p>
                        <div className="mt-5">
                            <FlashMessage flash={flash} />
                        </div>
                        <div className="mt-6 space-y-4">
                            {profiles.length ? profiles.map((profile) => (
                                <div key={profile.id} className="rounded-[1.75rem] border border-slate-200 bg-slate-50/80 p-5">
                                    <div className="flex flex-wrap items-start justify-between gap-4">
                                        <div>
                                            <p className="text-xl font-bold text-slate-900">{profile.nama}</p>
                                            <p className="mt-1 text-sm text-slate-600">{profile.hubungan || 'diri_sendiri'} • {profile.jenis_kelamin || '-'} • {profile.usia || '-'} tahun</p>
                                            <p className="mt-2 text-xs uppercase tracking-[0.2em] text-slate-500">NIK: {profile.masked_nik || 'Belum diisi'}</p>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => chooseProfile(profile.id)}
                                            disabled={form.processing}
                                            className={`rounded-3xl px-4 py-3 text-sm font-semibold transition ${profile.selected ? 'bg-teal-600 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:border-teal-300 hover:text-teal-700'}`}
                                        >
                                            {profile.selected ? 'Profil aktif' : 'Pakai profil ini'}
                                        </button>
                                    </div>
                                    <div className="mt-4 grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                                        <div>Tanggal lahir: {profile.tanggal_lahir || '-'}</div>
                                        <div>Alamat: {profile.alamat || '-'}</div>
                                    </div>
                                </div>
                            )) : (
                                <div className="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                                    Belum ada profil pasien untuk nomor ini.
                                </div>
                            )}
                        </div>
                    </section>
                    <aside className="rounded-[2rem] bg-[linear-gradient(145deg,#12303b_0%,#0f766e_55%,#164e63_100%)] p-8 text-white shadow-[0_28px_90px_rgba(18,48,59,0.24)]">
                        <p className="text-sm font-semibold uppercase tracking-[0.24em] text-white/70">Langkah Lanjut</p>
                        <div className="mt-6 space-y-4">
                            <ActionCard title="Tambah profil baru" desc="Simpan profil untuk anak, orang tua, atau anggota keluarga lainnya.">
                                <Link href={route('patient.profile.index', { create: 1 })} className="inline-flex rounded-3xl bg-white px-4 py-3 text-sm font-semibold text-slate-900">
                                    Buka form profil
                                </Link>
                            </ActionCard>
                            <ActionCard title="Lanjut daftar" desc="Pilih profil aktif, lalu masuk ke pendaftaran multi dokter.">
                                <Link href={route('registration.create')} className="inline-flex rounded-3xl border border-white/20 px-4 py-3 text-sm font-semibold text-white">
                                    Ke halaman daftar
                                </Link>
                            </ActionCard>
                        </div>
                    </aside>
                </div>
            </div>
        </>
    );
}

function ActionCard({ title, desc, children }) {
    return (
        <div className="rounded-[1.75rem] border border-white/10 bg-white/10 p-5">
            <p className="text-xl font-bold">{title}</p>
            <p className="mt-2 text-sm text-white/80">{desc}</p>
            <div className="mt-4">{children}</div>
        </div>
    );
}
