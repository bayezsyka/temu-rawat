export default function FlashMessage({ flash }) {
    if (!flash?.success && !flash?.error) {
        return null;
    }

    const isError = Boolean(flash.error);

    return (
        <div
            className={`rounded-2xl border px-4 py-3 text-sm ${
                isError
                    ? 'border-rose-200 bg-rose-50 text-rose-700'
                    : 'border-emerald-200 bg-emerald-50 text-emerald-700'
            }`}
        >
            {flash.error || flash.success}
        </div>
    );
}
