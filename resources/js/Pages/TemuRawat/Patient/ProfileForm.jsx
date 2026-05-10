import FlashMessage from '@/Components/TemuRawat/FlashMessage';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

const inputClass =
    'w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white focus:ring-0';

const hubunganOptions = ['diri_sendiri', 'anak', 'orang_tua', 'keluarga', 'lainnya'];

export default function ProfileForm() {
    const { flash } = usePage().props;
    const form = useForm({
        nama: '',
        nik: '',
        tanggal_lahir: '',
        usia: '',
        jenis_kelamin: '',
        alamat: '',
        hubungan: 'diri_sendiri',
    });

    const submit = (event) => {
        event.preventDefault();
        form.post(route('patient.profile.store'));
    };

    return (
        <>
            <Head title="Tambah Profil Pasien" />
            <div className="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
                <div className="rounded-[2rem] border border-white/80 bg-white/95 p-8 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-teal-600">Profil Baru</p>
                            <h1 className="mt-3 text-3xl font-black tracking-tight text-slate-900">Tambah profil pasien dalam satu nomor</h1>
                        </div>
                        <Link href={route('patient.profile.index')} className="rounded-3xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700">
                            Kembali
                        </Link>
                    </div>
                    <div className="mt-5">
                        <FlashMessage flash={flash} />
                    </div>
                    <form onSubmit={submit} className="mt-6 grid gap-5 md:grid-cols-2">
                        <Field label="Nama" error={form.errors.nama}>
                            <input value={form.data.nama} onChange={(event) => form.setData('nama', event.target.value)} className={inputClass} />
                        </Field>
                        <Field label="NIK" error={form.errors.nik}>
                            <input value={form.data.nik} onChange={(event) => form.setData('nik', event.target.value)} className={inputClass} />
                        </Field>
                        <Field label="Tanggal lahir" error={form.errors.tanggal_lahir}>
                            <input type="date" value={form.data.tanggal_lahir} onChange={(event) => form.setData('tanggal_lahir', event.target.value)} className={inputClass} />
                        </Field>
                        <Field label="Usia" error={form.errors.usia}>
                            <input type="number" min="0" value={form.data.usia} onChange={(event) => form.setData('usia', event.target.value)} className={inputClass} />
                        </Field>
                        <Field label="Jenis kelamin" error={form.errors.jenis_kelamin}>
                            <select value={form.data.jenis_kelamin} onChange={(event) => form.setData('jenis_kelamin', event.target.value)} className={inputClass}>
                                <option value="">Pilih</option>
                                <option value="laki-laki">Laki-laki</option>
                                <option value="perempuan">Perempuan</option>
                            </select>
                        </Field>
                        <Field label="Hubungan" error={form.errors.hubungan}>
                            <select value={form.data.hubungan} onChange={(event) => form.setData('hubungan', event.target.value)} className={inputClass}>
                                {hubunganOptions.map((option) => <option key={option} value={option}>{option}</option>)}
                            </select>
                        </Field>
                        <div className="md:col-span-2">
                            <Field label="Alamat" error={form.errors.alamat}>
                                <textarea rows="4" value={form.data.alamat} onChange={(event) => form.setData('alamat', event.target.value)} className={`${inputClass} resize-none`} />
                            </Field>
                        </div>
                        <div className="md:col-span-2">
                            <button type="submit" disabled={form.processing} className="rounded-3xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:opacity-70">
                                {form.processing ? 'Menyimpan...' : 'Simpan profil pasien'}
                            </button>
                        </div>
                    </form>
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
