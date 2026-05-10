import FlashMessage from '@/Components/TemuRawat/FlashMessage';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

const inputClass =
    'w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white focus:ring-0';

export default function OtpLogin({ nomorWhatsapp = '' }) {
    const { flash } = usePage().props;
    const form = useForm({
        nomor_whatsapp: nomorWhatsapp,
    });

    const submit = (event) => {
        event.preventDefault();
        form.post(route('patient.otp.send'));
    };

    return (
        <>
            <Head title="Masuk Pasien" />
            <div className="flex min-h-screen items-center justify-center px-4 py-10">
                <div className="grid w-full max-w-5xl gap-6 lg:grid-cols-[1.05fr_0.95fr]">
                    <section className="rounded-[2rem] border border-white/80 bg-white/95 p-8 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
                        <p className="text-sm font-semibold uppercase tracking-[0.24em] text-teal-600">Temu Rawat</p>
                        <h1 className="mt-3 text-4xl font-black tracking-tight text-slate-900">Masuk pasien dengan OTP WhatsApp</h1>
                        <p className="mt-3 max-w-xl text-sm text-slate-600">
                            Nomor WhatsApp dipakai sebagai akses sederhana pasien. Setelah verifikasi, satu nomor bisa menyimpan beberapa profil keluarga.
                        </p>
                        <div className="mt-6">
                            <FlashMessage flash={flash} />
                        </div>
                        <form onSubmit={submit} className="mt-6 space-y-5">
                            <label className="block space-y-2">
                                <span className="text-sm font-semibold text-slate-700">Nomor WhatsApp</span>
                                <input
                                    value={form.data.nomor_whatsapp}
                                    onChange={(event) => form.setData('nomor_whatsapp', event.target.value)}
                                    className={inputClass}
                                    placeholder="08xxxxxxxxxx"
                                />
                                {form.errors.nomor_whatsapp ? <span className="text-xs text-rose-600">{form.errors.nomor_whatsapp}</span> : null}
                            </label>
                            <button
                                type="submit"
                                disabled={form.processing}
                                className="rounded-3xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:opacity-70"
                            >
                                {form.processing ? 'Mengirim OTP...' : 'Kirim OTP'}
                            </button>
                        </form>
                    </section>
                    <aside className="rounded-[2rem] bg-[linear-gradient(135deg,#0f766e_0%,#155e75_55%,#082f49_100%)] p-8 text-white shadow-[0_28px_90px_rgba(14,116,144,0.22)]">
                        <p className="text-sm font-semibold uppercase tracking-[0.24em] text-white/70">Akses Pasien</p>
                        <div className="mt-6 space-y-4">
                            <Feature title="OTP 6 digit" desc="Berlaku 5 menit dan disimpan dalam bentuk hash." />
                            <Feature title="Cooldown 60 detik" desc="Kirim ulang OTP dibatasi agar alur development tetap rapi." />
                            <Feature title="Banyak profil" desc="Satu nomor bisa dipakai untuk diri sendiri, anak, orang tua, atau keluarga." />
                        </div>
                        <div className="mt-8 rounded-[1.5rem] border border-white/15 bg-white/10 p-5 text-sm text-white/80">
                            Sudah mengirim OTP?
                            <Link href={route('patient.login', { step: 'verifikasi' })} className="ml-2 font-semibold text-white underline decoration-white/40 underline-offset-4">
                                Masuk ke verifikasi
                            </Link>
                        </div>
                    </aside>
                </div>
            </div>
        </>
    );
}

function Feature({ title, desc }) {
    return (
        <div className="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
            <p className="text-lg font-bold">{title}</p>
            <p className="mt-2 text-sm text-white/80">{desc}</p>
        </div>
    );
}
