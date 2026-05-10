import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

const inputClass =
    'w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white focus:ring-0';

export default function Doctors({ doctors, doctorUsers }) {
    const form = useForm({
        user_id: '',
        nama: '',
        spesialisasi: '',
        nomor_sip: '',
        status: 'aktif',
    });

    const submit = (event) => {
        event.preventDefault();
        form.post(route('doctors.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-sm font-semibold uppercase tracking-[0.24em] text-teal-600">Admin Klinik</p>
                    <h2 className="mt-2 text-2xl font-black text-slate-900">Data dokter</h2>
                </div>
            }
        >
            <Head title="Dokter" />
            <div className="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                <form onSubmit={submit} className="rounded-[2rem] border border-white/80 bg-white/95 p-6 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
                    <h3 className="text-xl font-black text-slate-900">Tambah dokter</h3>
                    <div className="mt-5 grid gap-4">
                        <label className="space-y-2">
                            <span className="text-sm font-semibold text-slate-700">Akun user dokter</span>
                            <select value={form.data.user_id} onChange={(event) => form.setData('user_id', event.target.value)} className={inputClass}>
                                <option value="">Belum ditautkan</option>
                                {doctorUsers.map((user) => (
                                    <option key={user.id} value={user.id}>
                                        {user.name} - {user.email}
                                    </option>
                                ))}
                            </select>
                        </label>
                        <Field label="Nama dokter"><input value={form.data.nama} onChange={(event) => form.setData('nama', event.target.value)} className={inputClass} /></Field>
                        <Field label="Spesialisasi"><input value={form.data.spesialisasi} onChange={(event) => form.setData('spesialisasi', event.target.value)} className={inputClass} /></Field>
                        <Field label="Nomor SIP"><input value={form.data.nomor_sip} onChange={(event) => form.setData('nomor_sip', event.target.value)} className={inputClass} /></Field>
                        <Field label="Status">
                            <select value={form.data.status} onChange={(event) => form.setData('status', event.target.value)} className={inputClass}>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </Field>
                        <button type="submit" disabled={form.processing} className="rounded-3xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:opacity-70">
                            {form.processing ? 'Menyimpan...' : 'Simpan dokter'}
                        </button>
                    </div>
                </form>

                <div className="space-y-4">
                    {doctors.map((doctor) => (
                        <div key={doctor.id} className="rounded-[2rem] border border-white/80 bg-white/95 p-6 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-teal-600">{doctor.status}</p>
                            <h3 className="mt-2 text-xl font-black text-slate-900">{doctor.nama}</h3>
                            <p className="mt-2 text-sm text-slate-600">{doctor.spesialisasi || 'Dokter umum'}</p>
                            <p className="mt-2 text-sm text-slate-500">SIP: {doctor.nomor_sip || '-'}</p>
                            <p className="mt-2 text-sm text-slate-500">Email login: {doctor.email || '-'}</p>
                        </div>
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function Field({ label, children }) {
    return (
        <label className="block space-y-2">
            <span className="text-sm font-semibold text-slate-700">{label}</span>
            {children}
        </label>
    );
}
