import FlashMessage from '@/Components/TemuRawat/FlashMessage';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

const inputClass =
    'w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white focus:ring-0';

export default function OtpVerify({ nomorWhatsapp }) {
    const { flash } = usePage().props;
    const form = useForm({
        nomor_whatsapp: nomorWhatsapp || '',
        otp: '',
    });

    const submit = (event) => {
        event.preventDefault();
        form.post(route('patient.otp.verify'));
    };

    return (
        <>
            <Head title="Verifikasi OTP" />
            <div className="flex min-h-screen items-center justify-center px-4 py-10">
                <div className="w-full max-w-3xl rounded-[2rem] border border-white/80 bg-white/95 p-8 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
                    <p className="text-sm font-semibold uppercase tracking-[0.24em] text-teal-600">Verifikasi Pasien</p>
                    <h1 className="mt-3 text-3xl font-black tracking-tight text-slate-900">Masukkan OTP 6 digit</h1>
                    <p className="mt-3 text-sm text-slate-600">
                        OTP dikirim ke WhatsApp <span className="font-semibold text-slate-800">{nomorWhatsapp}</span>.
                    </p>
                    <div className="mt-5">
                        <FlashMessage flash={flash} />
                    </div>
                    <form onSubmit={submit} className="mt-6 space-y-5">
                        <label className="block space-y-2">
                            <span className="text-sm font-semibold text-slate-700">Kode OTP</span>
                            <input
                                value={form.data.otp}
                                onChange={(event) => form.setData('otp', event.target.value)}
                                className={inputClass}
                                inputMode="numeric"
                                maxLength={6}
                                placeholder="123456"
                            />
                            {form.errors.otp ? <span className="text-xs text-rose-600">{form.errors.otp}</span> : null}
                        </label>
                        <div className="flex flex-wrap gap-3">
                            <button
                                type="submit"
                                disabled={form.processing}
                                className="rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:opacity-70"
                            >
                                {form.processing ? 'Memverifikasi...' : 'Verifikasi'}
                            </button>
                            <Link href={route('patient.login')} className="rounded-3xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-teal-300 hover:text-teal-700">
                                Ubah nomor
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
