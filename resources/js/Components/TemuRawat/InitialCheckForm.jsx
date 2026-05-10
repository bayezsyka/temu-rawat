import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';

const inputClass =
    'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:ring-0';

export default function InitialCheckForm({ queue }) {
    const form = useForm({
        tekanan_darah: queue?.initial_check?.tekanan_darah || '',
        berat_badan: queue?.initial_check?.berat_badan || '',
        tinggi_badan: queue?.initial_check?.tinggi_badan || '',
        suhu: queue?.initial_check?.suhu || '',
        nadi: queue?.initial_check?.nadi || '',
        catatan_asisten: queue?.initial_check?.catatan_asisten || '',
    });

    useEffect(() => {
        form.setData({
            tekanan_darah: queue?.initial_check?.tekanan_darah || '',
            berat_badan: queue?.initial_check?.berat_badan || '',
            tinggi_badan: queue?.initial_check?.tinggi_badan || '',
            suhu: queue?.initial_check?.suhu || '',
            nadi: queue?.initial_check?.nadi || '',
            catatan_asisten: queue?.initial_check?.catatan_asisten || '',
        });
    }, [queue?.id]);

    if (!queue) {
        return (
            <div className="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                Pilih antrian dari daftar untuk mengisi pemeriksaan awal.
            </div>
        );
    }

    const submit = (event) => {
        event.preventDefault();

        form.patch(route('panel.queues.initial-check', queue.id), {
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="space-y-4 rounded-[1.75rem] border border-slate-200 bg-slate-50/70 p-5">
            <div className="grid gap-4 md:grid-cols-2">
                <Input label="Tekanan darah" value={form.data.tekanan_darah} onChange={(value) => form.setData('tekanan_darah', value)} />
                <Input label="Berat badan" value={form.data.berat_badan} onChange={(value) => form.setData('berat_badan', value)} />
                <Input label="Tinggi badan" value={form.data.tinggi_badan} onChange={(value) => form.setData('tinggi_badan', value)} />
                <Input label="Suhu" value={form.data.suhu} onChange={(value) => form.setData('suhu', value)} />
                <Input label="Nadi" value={form.data.nadi} onChange={(value) => form.setData('nadi', value)} />
            </div>

            <label className="block space-y-2">
                <span className="text-sm font-semibold text-slate-700">Catatan asisten</span>
                <textarea rows="4" value={form.data.catatan_asisten} onChange={(event) => form.setData('catatan_asisten', event.target.value)} className={inputClass} />
            </label>

            <button
                type="submit"
                disabled={form.processing}
                className="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:opacity-70"
            >
                {form.processing ? 'Menyimpan...' : 'Simpan pemeriksaan awal'}
            </button>
        </form>
    );
}

function Input({ label, value, onChange }) {
    return (
        <label className="block space-y-2">
            <span className="text-sm font-semibold text-slate-700">{label}</span>
            <input value={value} onChange={(event) => onChange(event.target.value)} className={inputClass} />
        </label>
    );
}
