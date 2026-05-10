import { Head } from '@inertiajs/react';

export default function VisitSummary({ available, visit }) {
    return (
        <>
            <Head title="Ringkasan Kunjungan" />
            <div className="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
                <div className="rounded-[2rem] border border-white/80 bg-white/95 p-8 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
                    <p className="text-sm font-semibold uppercase tracking-[0.24em] text-teal-600">Ringkasan Pasien</p>
                    {available && visit ? (
                        <>
                            <h1 className="mt-3 text-3xl font-black tracking-tight text-slate-900">Hasil singkat kunjungan</h1>
                            <div className="mt-6 grid gap-4 md:grid-cols-2">
                                <Detail label="Tanggal kunjungan" value={visit.tanggal} />
                                <Detail label="Dokter" value={visit.doctor || '-'} />
                                <Detail label="Kontrol ulang" value={visit.kontrol_ulang_pada || '-'} />
                                <Detail label="Tautan aktif sampai" value={visit.patient_visible_until || '-'} />
                            </div>
                            <Section title="Diagnosis" value={visit.diagnosis} />
                            <Section title="Anjuran" value={visit.anjuran} />
                            <div className="mt-6 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5">
                                <p className="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Resep</p>
                                {visit.prescription ? (
                                    <div className="mt-4 space-y-3">
                                        {visit.prescription.catatan_resep ? <p className="text-sm text-slate-600">{visit.prescription.catatan_resep}</p> : null}
                                        {visit.prescription.items.length ? visit.prescription.items.map((item, index) => (
                                            <div key={`${item.nama_obat}-${index}`} className="rounded-2xl border border-slate-200 bg-white p-4">
                                                <p className="font-bold text-slate-900">{item.nama_obat}</p>
                                                <p className="mt-2 text-sm text-slate-600">
                                                    {item.dosis || '-'} • {item.aturan_pakai || '-'} • {item.jumlah || '-'} {item.satuan || ''}
                                                </p>
                                                {item.catatan ? <p className="mt-2 text-sm text-slate-500">{item.catatan}</p> : null}
                                            </div>
                                        )) : <p className="text-sm text-slate-500">Belum ada item resep.</p>}
                                    </div>
                                ) : <p className="mt-4 text-sm text-slate-500">Belum ada resep tercatat.</p>}
                            </div>
                        </>
                    ) : (
                        <>
                            <h1 className="mt-3 text-3xl font-black tracking-tight text-slate-900">Ringkasan sudah tidak tersedia</h1>
                            <p className="mt-4 text-sm leading-7 text-slate-600">
                                Ringkasan kunjungan sudah tidak tersedia melalui tautan pasien. Silakan hubungi klinik bila membutuhkan informasi lanjutan.
                            </p>
                        </>
                    )}
                </div>
            </div>
        </>
    );
}

function Detail({ label, value }) {
    return (
        <div className="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{label}</p>
            <p className="mt-2 font-semibold text-slate-900">{value}</p>
        </div>
    );
}

function Section({ title, value }) {
    return (
        <div className="mt-6 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5">
            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">{title}</p>
            <p className="mt-3 whitespace-pre-wrap text-sm leading-7 text-slate-700">{value || '-'}</p>
        </div>
    );
}
