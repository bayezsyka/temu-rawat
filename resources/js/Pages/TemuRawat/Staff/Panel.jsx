import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useRealtimeReload from '@/hooks/useRealtimeReload';
import { Head, Link, router, useForm } from '@inertiajs/react';
import Swal from 'sweetalert2';
import { useEffect, useState } from 'react';

const inputClass =
    'w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white focus:ring-0';

export default function StaffPanel({ sessions, activeSessionId, queues, viewer }) {
    const [selectedQueueId, setSelectedQueueId] = useState(queues[0]?.id || null);
    const [expandedHistory, setExpandedHistory] = useState(null);

    const activeSession = sessions.find((session) => session.id === activeSessionId) || sessions[0] || null;
    const selectedQueue = queues.find((queue) => queue.id === selectedQueueId) || queues[0] || null;

    useEffect(() => {
        if (!queues.some((queue) => queue.id === selectedQueueId)) {
            setSelectedQueueId(queues[0]?.id || null);
        }
    }, [queues, selectedQueueId]);

    useRealtimeReload({
        publicChannels: ['practice-overview', ...sessions.map((session) => `practice-session.${session.id}`)],
        privateChannels: ['staff-panel', viewer.doctor_id ? `doctor.${viewer.doctor_id}` : null],
        only: ['sessions', 'activeSessionId', 'queues', 'flash'],
        pollInterval: window.Echo ? null : 10000,
    });

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-sm font-semibold uppercase tracking-[0.24em] text-teal-600">Temu Rawat</p>
                    <h2 className="mt-2 text-2xl font-black text-slate-900">Panel dokter dan asisten</h2>
                </div>
            }
        >
            <Head title="Panel Klinik" />
            <div className="grid gap-6 2xl:grid-cols-[320px_340px_minmax(0,1fr)]">
                <section className="space-y-4">
                    <Card title="Sesi aktif">
                        <div className="space-y-3">
                            {sessions.length ? sessions.map((session) => (
                                <Link
                                    key={session.id}
                                    href={route('panel.sessions.show', session.id)}
                                    className={`block rounded-[1.5rem] border p-4 transition ${session.id === activeSessionId ? 'border-teal-500 bg-teal-50' : 'border-slate-200 bg-slate-50 hover:border-teal-200'}`}
                                >
                                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-teal-700">{session.status}</p>
                                    <p className="mt-2 text-lg font-black text-slate-900">{session.doctor?.nama}</p>
                                    <p className="mt-1 text-sm text-slate-600">{session.current_queue?.kode_antrian || 'Belum ada pasien aktif'}</p>
                                    <p className="mt-3 text-xs uppercase tracking-[0.2em] text-slate-500">Menunggu {session.waiting_count}</p>
                                </Link>
                            )) : (
                                <div className="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">
                                    Belum ada sesi untuk hari ini.
                                </div>
                            )}
                        </div>
                    </Card>
                </section>

                <section className="space-y-4">
                    <Card title={activeSession ? `Antrian ${activeSession.doctor?.nama}` : 'Daftar antrian'}>
                        <div className="space-y-3">
                            {queues.length ? queues.map((queue) => (
                                <button
                                    key={queue.id}
                                    type="button"
                                    onClick={() => setSelectedQueueId(queue.id)}
                                    className={`w-full rounded-[1.5rem] border p-4 text-left transition ${selectedQueue?.id === queue.id ? 'border-teal-500 bg-teal-50' : 'border-slate-200 bg-slate-50 hover:border-teal-200'}`}
                                >
                                    <div className="flex items-center justify-between gap-3">
                                        <div>
                                            <p className="text-xl font-black text-slate-900">{queue.kode_antrian}</p>
                                            <p className="text-sm text-slate-600">{queue.patient.nama}</p>
                                        </div>
                                        <StatusChip value={queue.status} />
                                    </div>
                                    <p className="mt-3 text-sm text-slate-500">{queue.keluhan || 'Keluhan belum diisi.'}</p>
                                </button>
                            )) : (
                                <div className="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">
                                    Belum ada antrian pada sesi ini.
                                </div>
                            )}
                        </div>
                    </Card>
                </section>

                <section className="space-y-4">
                    <Card title={selectedQueue ? `Pasien aktif: ${selectedQueue.patient.nama}` : 'Detail pasien'}>
                        {selectedQueue ? (
                            <>
                                <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                    <Info label="Antrian" value={selectedQueue.kode_antrian} />
                                    <Info label="Status" value={selectedQueue.status} />
                                    <Info label="Dokter" value={selectedQueue.doctor?.nama || '-'} />
                                    <Info label="NIK" value={selectedQueue.patient.nik || '-'} />
                                    <Info label="Usia" value={selectedQueue.patient.usia || '-'} />
                                    <Info label="Jenis kelamin" value={selectedQueue.patient.jenis_kelamin || '-'} />
                                    <Info label="WhatsApp" value={selectedQueue.patient.nomor_whatsapp || '-'} />
                                    <Info label="Alamat" value={selectedQueue.patient.alamat || '-'} />
                                    <Info label="Keluhan" value={selectedQueue.keluhan || '-'} />
                                </div>
                                <QueueActions queue={selectedQueue} />
                            </>
                        ) : (
                            <p className="text-sm text-slate-500">Pilih antrian untuk melihat detail pasien.</p>
                        )}
                    </Card>

                    <InitialCheckSection queue={selectedQueue} />
                    <MedicalVisitSection queue={selectedQueue} />
                    <PrescriptionSection queue={selectedQueue} />

                    <Card title="Riwayat kunjungan pasien">
                        {selectedQueue?.patient_history?.length ? selectedQueue.patient_history.map((item) => (
                            <div key={item.id} className="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                                <div className="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p className="font-bold text-slate-900">{item.tanggal}</p>
                                        <p className="text-sm text-slate-600">{item.doctor || '-'}</p>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={() => setExpandedHistory(expandedHistory === item.id ? null : item.id)}
                                        className="rounded-3xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700"
                                    >
                                        Lihat detail
                                    </button>
                                </div>
                                <p className="mt-3 text-sm text-slate-600">Diagnosis: {item.diagnosis || '-'}</p>
                                <p className="mt-1 text-sm text-slate-600">Resep ringkas: {item.prescription || '-'}</p>
                                {expandedHistory === item.id ? (
                                    <p className="mt-3 rounded-2xl bg-white p-3 text-sm text-slate-600">
                                        {item.anjuran || 'Tidak ada catatan tambahan.'}
                                    </p>
                                ) : null}
                            </div>
                        )) : (
                            <p className="text-sm text-slate-500">Belum ada riwayat kunjungan sebelumnya.</p>
                        )}
                    </Card>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}

function QueueActions({ queue }) {
    const postAction = async ({ routeName, confirm, successTitle }) => {
        if (confirm) {
            const result = await Swal.fire({
                title: confirm.title,
                text: confirm.text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Lanjutkan',
                cancelButtonText: 'Batal',
            });

            if (!result.isConfirmed) return;
        }

        router.post(route(routeName, queue.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                Swal.fire({
                    icon: 'success',
                    title: successTitle,
                    timer: 1400,
                    showConfirmButton: false,
                });
            },
        });
    };

    return (
        <div className="mt-6 flex flex-wrap gap-3">
            <ActionButton label="Panggil" onClick={() => postAction({ routeName: 'panel.queues.call', successTitle: 'Pasien dipanggil' })} />
            <ActionButton label="Mulai Pemeriksaan Awal" onClick={() => postAction({ routeName: 'panel.queues.initial', successTitle: 'Pemeriksaan awal dimulai' })} />
            <ActionButton label="Mulai Periksa Dokter" onClick={() => postAction({ routeName: 'panel.queues.start-doctor', successTitle: 'Pemeriksaan dokter dimulai' })} />
            <ActionButton label="Lewati" onClick={() => postAction({ routeName: 'panel.queues.skip', successTitle: 'Antrian dilewati', confirm: { title: 'Lewati pasien ini?', text: `Antrian ${queue.kode_antrian} akan dilewati.` } })} />
            <ActionButton label="Batal" onClick={() => postAction({ routeName: 'panel.queues.cancel', successTitle: 'Antrian dibatalkan', confirm: { title: 'Batalkan antrian?', text: `Antrian ${queue.kode_antrian} akan dibatalkan.` } })} danger />
            <ActionButton label="Selesai" onClick={() => postAction({ routeName: 'panel.queues.finish', successTitle: 'Pemeriksaan selesai', confirm: { title: 'Selesaikan pemeriksaan?', text: 'Ringkasan pasien akan aktif selama 7 hari.' } })} success />
        </div>
    );
}

function InitialCheckSection({ queue }) {
    const form = useForm({
        tekanan_darah: '',
        berat_badan: '',
        tinggi_badan: '',
        suhu: '',
        nadi: '',
        saturasi_oksigen: '',
        catatan_asisten: '',
    });

    useEffect(() => {
        form.setData({
            tekanan_darah: queue?.initial_check?.tekanan_darah || '',
            berat_badan: queue?.initial_check?.berat_badan || '',
            tinggi_badan: queue?.initial_check?.tinggi_badan || '',
            suhu: queue?.initial_check?.suhu || '',
            nadi: queue?.initial_check?.nadi || '',
            saturasi_oksigen: queue?.initial_check?.saturasi_oksigen || '',
            catatan_asisten: queue?.initial_check?.catatan_asisten || '',
        });
    }, [queue?.id]);

    const submit = (event) => {
        event.preventDefault();
        if (!queue) return;

        form.post(route('panel.queues.initial', queue.id), {
            preserveScroll: true,
            onSuccess: () => Swal.fire({ icon: 'success', title: 'Pemeriksaan awal tersimpan', timer: 1400, showConfirmButton: false }),
        });
    };

    return (
        <Card title="Pemeriksaan awal">
            {queue ? (
                <form onSubmit={submit} className="grid gap-4 md:grid-cols-2">
                    <Field label="Tekanan darah"><input value={form.data.tekanan_darah} onChange={(event) => form.setData('tekanan_darah', event.target.value)} className={inputClass} /></Field>
                    <Field label="Berat badan"><input value={form.data.berat_badan} onChange={(event) => form.setData('berat_badan', event.target.value)} className={inputClass} /></Field>
                    <Field label="Tinggi badan"><input value={form.data.tinggi_badan} onChange={(event) => form.setData('tinggi_badan', event.target.value)} className={inputClass} /></Field>
                    <Field label="Suhu"><input value={form.data.suhu} onChange={(event) => form.setData('suhu', event.target.value)} className={inputClass} /></Field>
                    <Field label="Nadi"><input value={form.data.nadi} onChange={(event) => form.setData('nadi', event.target.value)} className={inputClass} /></Field>
                    <Field label="Saturasi oksigen"><input value={form.data.saturasi_oksigen} onChange={(event) => form.setData('saturasi_oksigen', event.target.value)} className={inputClass} /></Field>
                    <div className="md:col-span-2">
                        <Field label="Catatan asisten">
                            <textarea rows="4" value={form.data.catatan_asisten} onChange={(event) => form.setData('catatan_asisten', event.target.value)} className={`${inputClass} resize-none`} />
                        </Field>
                    </div>
                    <div className="md:col-span-2">
                        <button type="submit" disabled={form.processing} className="rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:opacity-70">
                            {form.processing ? 'Menyimpan...' : 'Simpan Pemeriksaan Awal'}
                        </button>
                    </div>
                </form>
            ) : (
                <p className="text-sm text-slate-500">Pilih antrian untuk mengisi pemeriksaan awal.</p>
            )}
        </Card>
    );
}

function MedicalVisitSection({ queue }) {
    const form = useForm({
        keluhan_utama: '',
        ringkasan_pemeriksaan: '',
        diagnosis: '',
        tindakan: '',
        catatan_dokter: '',
        anjuran: '',
        kontrol_ulang_pada: '',
    });

    useEffect(() => {
        form.setData({
            keluhan_utama: queue?.medical_visit?.keluhan_utama || '',
            ringkasan_pemeriksaan: queue?.medical_visit?.ringkasan_pemeriksaan || '',
            diagnosis: queue?.medical_visit?.diagnosis || '',
            tindakan: queue?.medical_visit?.tindakan || '',
            catatan_dokter: queue?.medical_visit?.catatan_dokter || '',
            anjuran: queue?.medical_visit?.anjuran || '',
            kontrol_ulang_pada: queue?.medical_visit?.kontrol_ulang_pada || '',
        });
    }, [queue?.id]);

    const submit = (event) => {
        event.preventDefault();
        if (!queue) return;

        form.post(route('panel.visits.store', queue.medical_visit.reference), {
            preserveScroll: true,
            onSuccess: () => Swal.fire({ icon: 'success', title: 'Pemeriksaan dokter tersimpan', timer: 1400, showConfirmButton: false }),
        });
    };

    return (
        <Card title="Pemeriksaan dokter">
            {queue ? (
                <form onSubmit={submit} className="grid gap-4 md:grid-cols-2">
                    <div className="md:col-span-2"><Field label="Keluhan utama"><textarea rows="3" value={form.data.keluhan_utama} onChange={(event) => form.setData('keluhan_utama', event.target.value)} className={`${inputClass} resize-none`} /></Field></div>
                    <div className="md:col-span-2"><Field label="Ringkasan pemeriksaan"><textarea rows="4" value={form.data.ringkasan_pemeriksaan} onChange={(event) => form.setData('ringkasan_pemeriksaan', event.target.value)} className={`${inputClass} resize-none`} /></Field></div>
                    <Field label="Diagnosis"><textarea rows="4" value={form.data.diagnosis} onChange={(event) => form.setData('diagnosis', event.target.value)} className={`${inputClass} resize-none`} /></Field>
                    <Field label="Tindakan"><textarea rows="4" value={form.data.tindakan} onChange={(event) => form.setData('tindakan', event.target.value)} className={`${inputClass} resize-none`} /></Field>
                    <Field label="Catatan dokter"><textarea rows="4" value={form.data.catatan_dokter} onChange={(event) => form.setData('catatan_dokter', event.target.value)} className={`${inputClass} resize-none`} /></Field>
                    <Field label="Anjuran"><textarea rows="4" value={form.data.anjuran} onChange={(event) => form.setData('anjuran', event.target.value)} className={`${inputClass} resize-none`} /></Field>
                    <Field label="Kontrol ulang pada"><input type="date" value={form.data.kontrol_ulang_pada} onChange={(event) => form.setData('kontrol_ulang_pada', event.target.value)} className={inputClass} /></Field>
                    <div className="md:col-span-2">
                        <button type="submit" disabled={form.processing} className="rounded-3xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:opacity-70">
                            {form.processing ? 'Menyimpan...' : 'Simpan Pemeriksaan Dokter'}
                        </button>
                    </div>
                </form>
            ) : (
                <p className="text-sm text-slate-500">Pilih antrian untuk mengisi pemeriksaan dokter.</p>
            )}
        </Card>
    );
}

function PrescriptionSection({ queue }) {
    const form = useForm({
        catatan_resep: '',
        items: [{ nama_obat: '', dosis: '', aturan_pakai: '', jumlah: '', satuan: '', catatan: '' }],
    });

    useEffect(() => {
        form.setData({
            catatan_resep: queue?.prescription?.catatan_resep || '',
            items: queue?.prescription?.items?.length
                ? queue.prescription.items.map((item) => ({
                    nama_obat: item.nama_obat || '',
                    dosis: item.dosis || '',
                    aturan_pakai: item.aturan_pakai || '',
                    jumlah: item.jumlah || '',
                    satuan: item.satuan || '',
                    catatan: item.catatan || '',
                }))
                : [{ nama_obat: '', dosis: '', aturan_pakai: '', jumlah: '', satuan: '', catatan: '' }],
        });
    }, [queue?.id]);

    const submit = (event) => {
        event.preventDefault();
        if (!queue) return;

        form.post(route('panel.visits.prescription', queue.medical_visit.reference), {
            preserveScroll: true,
            onSuccess: () => Swal.fire({ icon: 'success', title: 'Resep tersimpan', timer: 1400, showConfirmButton: false }),
        });
    };

    const setItem = (index, key, value) => {
        const items = [...form.data.items];
        items[index] = { ...items[index], [key]: value };
        form.setData('items', items);
    };

    return (
        <Card title="Resep sederhana">
            {queue ? (
                <form onSubmit={submit} className="space-y-4">
                    <Field label="Catatan resep">
                        <textarea rows="3" value={form.data.catatan_resep} onChange={(event) => form.setData('catatan_resep', event.target.value)} className={`${inputClass} resize-none`} />
                    </Field>
                    {form.data.items.map((item, index) => (
                        <div key={index} className="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <Field label="Nama obat"><input value={item.nama_obat} onChange={(event) => setItem(index, 'nama_obat', event.target.value)} className={inputClass} /></Field>
                                <Field label="Dosis"><input value={item.dosis} onChange={(event) => setItem(index, 'dosis', event.target.value)} className={inputClass} /></Field>
                                <Field label="Aturan pakai"><input value={item.aturan_pakai} onChange={(event) => setItem(index, 'aturan_pakai', event.target.value)} className={inputClass} /></Field>
                                <Field label="Jumlah"><input value={item.jumlah} onChange={(event) => setItem(index, 'jumlah', event.target.value)} className={inputClass} /></Field>
                                <Field label="Satuan"><input value={item.satuan} onChange={(event) => setItem(index, 'satuan', event.target.value)} className={inputClass} /></Field>
                                <Field label="Catatan"><input value={item.catatan} onChange={(event) => setItem(index, 'catatan', event.target.value)} className={inputClass} /></Field>
                            </div>
                        </div>
                    ))}
                    <div className="flex flex-wrap gap-3">
                        <button
                            type="button"
                            onClick={() => form.setData('items', [...form.data.items, { nama_obat: '', dosis: '', aturan_pakai: '', jumlah: '', satuan: '', catatan: '' }])}
                            className="rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700"
                        >
                            Tambah Obat
                        </button>
                        <button type="submit" disabled={form.processing} className="rounded-3xl bg-cyan-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-800 disabled:opacity-70">
                            {form.processing ? 'Menyimpan...' : 'Simpan Resep'}
                        </button>
                    </div>
                </form>
            ) : (
                <p className="text-sm text-slate-500">Pilih antrian untuk mengisi resep.</p>
            )}
        </Card>
    );
}

function Card({ title, children }) {
    return (
        <section className="rounded-[2rem] border border-white/80 bg-white/95 p-6 shadow-[0_24px_80px_rgba(15,45,59,0.08)]">
            <h3 className="text-xl font-black text-slate-900">{title}</h3>
            <div className="mt-5">{children}</div>
        </section>
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

function Info({ label, value }) {
    return (
        <div className="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{label}</p>
            <p className="mt-2 text-sm font-semibold text-slate-900">{value}</p>
        </div>
    );
}

function StatusChip({ value }) {
    return (
        <span className="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-white">
            {value}
        </span>
    );
}

function ActionButton({ label, onClick, danger = false, success = false }) {
    const classes = success
        ? 'bg-emerald-600 text-white hover:bg-emerald-700'
        : danger
            ? 'bg-rose-50 text-rose-700 hover:bg-rose-100'
            : 'border border-slate-200 bg-white text-slate-700 hover:border-teal-300 hover:text-teal-700';

    return (
        <button type="button" onClick={onClick} className={`rounded-3xl px-4 py-3 text-sm font-semibold transition ${classes}`}>
            {label}
        </button>
    );
}
