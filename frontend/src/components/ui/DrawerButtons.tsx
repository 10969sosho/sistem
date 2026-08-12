export function DrawerButtons({ saving, onCancel, submitLabel = 'Simpan', formId }: { saving?: boolean; onCancel: () => void; submitLabel?: string; formId?: string }) {
  return (
    <div className="flex items-center justify-end gap-3">
      <button type="button" onClick={onCancel} className="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
      <button type="submit" form={formId} disabled={saving} className="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">{saving ? 'Menyimpan...' : submitLabel}</button>
    </div>
  );
}
