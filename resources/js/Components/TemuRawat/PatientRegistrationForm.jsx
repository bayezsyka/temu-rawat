import FlashMessage from '@/Components/TemuRawat/FlashMessage';
import { useForm, usePage } from '@inertiajs/react';

const visitOptions = ['baru', 'lama'];
const methodOptions = ['online', 'langsung'];
const genderOptions = ['laki-laki', 'perempuan'];

const inputClass =
    'w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white focus:ring-0';

export default function PatientRegistrationForm({ session }) {
    const { flash } = usePage().props;
    const form = useForm({
        nama: '',
        nomor_whatsapp: '',
        tanggal_lahir: '',
        usia: '',
        jenis_kelamin: '',
        alamat: '',
        keluhan: '',
        status_kunjungan: 'baru',
        metode_daftar: 'online',
    });

    const submit = (event) => {
        event.preventDefault();
        form.post(route('registration.store'));
    };

    return (
        <div className="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
            <form
                onSubmit={submit}
                className="space-y-5 rounded-[2rem] border border-white/80 bg-white/95 p-6 shadow-[0_24px_80px_rgba(15,45,59,0.08)] backdrop-blur"
            >
                <div className="space-y-1">
                    <p className="text-sm font-semibold uppercase tracking-[0.2em] text-teal-600">
                        Pendaftaran Pasien
                    </p>
                    <h1 className="text-3xl font-extrabold text-slate-900">
                        Daftar antrian tanpa antre di meja depan
                    </h1>
                    <p className="text-sm text-slate-600">
                        Isi data singkat. Setelah berhasil, pasien langsung mendapat nomor antrian dan halaman status realtime.
                    </p>
                </div>

                <FlashMessage flash={flash} />

                <div className="grid gap-4 md:grid-cols-2">
                    <Field label="Nama pasien" error={form.errors.nama}>
                        <input value={form.data.nama} onChange={(e) => form.setData('nama', e.target.value)} className={inputClass} />
                    </Field>
                    <Field label="Nomor WhatsApp" error={form.errors.nomor_whatsapp}>
                        <input value={form.data.nomor_whatsapp} onChange={(e) => form.setData('nomor_whatsapp', e.target.value)} className={inputClass} />
                    </Field>
                    <Field label="Tanggal lahir" error={form.errors.tanggal_lahir}>
                        <input type="date" value={form.data.tanggal_lahir} onChange={(e) => form.setData('tanggal_lahir', e.target.value)} className={inputClass} />
                    </Field>
                    <Field label="Usia" error={form.errors.usia}>
                        <input type="number" min="0" value={form.data.usia} onChange={(e) => form.setData('usia', e.target.value)} className={inputClass} />
                    </Field>
                    <Field label="Jenis kelamin" error={form.errors.jenis_kelamin}>
                        <select value={form.data.jenis_kelamin} onChange={(e) => form.setData('jenis_kelamin', e.target.value)} className={inputClass}>
                            <option value="">Pilih</option>
                            {genderOptions.map((option) => (
                                <option key={option} value={option}>
                                    {option}
                                </option>
                            ))}
                        </select>
                    </Field>
                    <Field label="Metode daftar" error={form.errors.metode_daftar}>
                        <select value={form.data.metode_daftar} onChange={(e) => form.setData('metode_daftar', e.target.value)} className={inputClass}>
                            {methodOptions.map((option) => (
                                <option key={option} value={option}>
                                    {option}
                                </option>
                            ))}
                        </select>
                    </Field>
                </div>

                <Field label="Alamat singkat" error={form.errors.alamat}>
                    <input value={form.data.alamat} onChange={(e) => form.setData('alamat', e.target.value)} className={inputClass} />
                </Field>

                <div className="grid gap-4 md:grid-cols-[0.8fr_1.2fr]">
                    <Field label="Status kunjungan" error={form.errors.status_kunjungan}>
                        <select value={form.data.status_kunjungan} onChange={(e) => form.setData('status_kunjungan', e.target.value)} className={inputClass}>
                            {visitOptions.map((option) => (
                                <option key={option} value={option}>
                                    {option}
                                </option>
                            ))}
                        </select>
                    </Field>
                    <Field label="Keluhan" error={form.errors.keluhan}>
                        <textarea rows="4" value={form.data.keluhan} onChange={(e) => form.setData('keluhan', e.target.value)} className={`${inputClass} resize-none`} />
                    </Field>
                </div>

                <button
                    type="submit"
                    disabled={form.processing}
                    className="inline-flex items-center rounded-2xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-70"
                >
                    {form.processing ? 'Memproses...' : 'Ambil nomor antrian'}
                </button>
            </form>

            <aside className="space-y-4 rounded-[2rem] border border-teal-100 bg-gradient-to-br from-teal-900 via-teal-800 to-cyan-800 p-6 text-white shadow-[0_24px_80px_rgba(12,68,84,0.24)]">
                <p className="text-sm font-semibold uppercase tracking-[0.24em] text-teal-100/90">
                    Praktik Hari Ini
                </p>

                {session ? (
                    <>
                        <div className="rounded-3xl bg-white/10 p-5">
                            <p className="text-sm text-teal-100">Status praktik</p>
                            <p className="mt-2 text-3xl font-extrabold capitalize">{session.status}</p>
                            <p className="mt-3 text-sm text-teal-50/90">
                                Nomor terakhir: {session.nomor_terakhir ? `A-${String(session.nomor_terakhir).padStart(3, '0')}` : 'Belum ada'}
                            </p>
                        </div>
                        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                            <InfoTile label="Sedang dilayani" value={session.current_queue?.kode_antrian || 'Belum ada'} />
                            <InfoTile label="Pasien menunggu" value={String(session.waiting_count)} />
                        </div>
                    </>
                ) : (
                    <div className="rounded-3xl bg-white/10 p-5 text-sm text-teal-50">
                        Sesi praktik hari ini belum dibuka. Pendaftaran baru bisa diproses setelah admin membuka sesi.
                    </div>
                )}

                <div className="rounded-3xl border border-white/10 bg-white/5 p-5 text-sm text-teal-50/90">
                    Untuk pasien datang langsung, QR pendaftaran bisa diarahkan ke halaman ini agar petugas tidak perlu input manual berulang.
                </div>
            </aside>
        </div>
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

function InfoTile({ label, value }) {
    return (
        <div className="rounded-3xl bg-white/10 p-4">
            <p className="text-xs uppercase tracking-[0.2em] text-teal-100/80">{label}</p>
            <p className="mt-2 text-2xl font-bold text-white">{value}</p>
        </div>
    );
}
