'use client';

import { FormEvent, useCallback, useEffect, useState } from 'react';
import Link from 'next/link';
import { Plus, Search, Trash2 } from 'lucide-react';
import { api, type PaginatedResponse } from '@/lib/api';
import type { CrmLead, CrmOpportunity } from '@/lib/types';
import { CRM_STATUS } from '@/lib/types';
import { Drawer, FormField, fieldClass } from '@/components/ui/Drawer';
import { DrawerButtons } from '@/components/ui/DrawerButtons';
import { useDrawer } from '@/lib/use-drawer';

const blankForm = { lead_id: '', title: '', value: '', offer_date: new Date().toISOString().slice(0, 10), notes: '' };

export default function OpportunitiesPage() {
  const [opportunities, setOpportunities] = useState<CrmOpportunity[]>([]);
  const [leads, setLeads] = useState<CrmLead[]>([]);
  const [form, setForm] = useState(blankForm);
  const [search, setSearch] = useState('');
  const [filterStage, setFilterStage] = useState('');
  const [loading, setLoading] = useState(true);
  const [leadsLoading, setLeadsLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const drawer = useDrawer();

  const load = useCallback(async () => {
    setLoading(true); setError('');
    try {
      const params: Record<string, string | number> = { per_page: 100 };
      if (search) params.search = search;
      if (filterStage) params.stage = filterStage;
      setOpportunities((await api.get<PaginatedResponse<CrmOpportunity>>('/crm/opportunities', params)).data);
    } catch (err) { setError(err instanceof Error ? err.message : 'Pipeline gagal dimuat.'); }
    finally { setLoading(false); }
  }, [search, filterStage]);

  useEffect(() => { void load(); }, [load]);

  const loadLeads = useCallback(async () => {
    setLeadsLoading(true);
    try {
      setLeads((await api.get<PaginatedResponse<CrmLead>>('/crm/leads', { per_page: 100 })).data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Daftar leads gagal dimuat.');
    } finally {
      setLeadsLoading(false);
    }
  }, []);

  useEffect(() => { void loadLeads(); }, [loadLeads]);

  const remove = async (item: CrmOpportunity) => {
    if (!window.confirm(`Hapus penawaran "${item.title}"?`)) return;
    setError('');
    try { await api.delete(`/crm/opportunities/${item.id}`); await load(); }
    catch (err) { setError(err instanceof Error ? err.message : 'Penawaran gagal dihapus.'); }
  };

  const submit = async (e: FormEvent) => {
    e.preventDefault(); setSaving(true); setError('');
    try { await api.post('/crm/opportunities', { ...form, lead_id: Number(form.lead_id), value: Number(form.value) }); drawer.close(); await load(); }
    catch (err) {
      const msg = err instanceof Error ? err.message : 'Gagal menyimpan.';
      const details = (err as { data?: { errors?: Record<string, string[]> } })?.data?.errors;
      setError(details ? Object.values(details).flat().join('. ') : msg);
    } finally { setSaving(false); }
  };

  const pipelineValue = opportunities.filter((o) => o.stage !== 'deal' && o.stage !== 'lost').reduce((s, o) => s + (o.value ?? 0), 0);
  const revenue = opportunities.filter((o) => o.stage === 'deal').reduce((s, o) => s + (o.value ?? 0), 0);

  return (<div className="space-y-6">
    {error && <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}
    <div className="grid grid-cols-2 gap-4">
      <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3"><div className="text-xs text-amber-600 font-semibold uppercase">Pipeline Value</div><div className="text-xl font-bold text-amber-900 mt-1">Rp {Math.round(pipelineValue).toLocaleString('id-ID')}</div></div>
      <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3"><div className="text-xs text-emerald-600 font-semibold uppercase">Revenue</div><div className="text-xl font-bold text-emerald-900 mt-1">Rp {Math.round(revenue).toLocaleString('id-ID')}</div></div>
    </div>
    <div className="flex flex-wrap items-center gap-4">
      <div className="flex items-center gap-2 rounded-lg border bg-white px-3 py-2 flex-1 max-w-md"><Search className="h-4 w-4 text-slate-400" /><input placeholder="Cari penawaran atau lead..." className="w-full text-sm outline-none" value={search} onChange={(e) => setSearch(e.target.value)} /></div>
      <select value={filterStage} onChange={(e) => setFilterStage(e.target.value)} className="rounded-lg border bg-white px-3 py-2 text-sm"><option value="">Semua Stage</option>{Object.entries(CRM_STATUS).map(([k, v]) => <option key={k} value={k}>{v}</option>)}</select>
      <button onClick={() => { setForm(blankForm); drawer.open('create'); }} className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"><Plus className="h-4 w-4" /> Penawaran Baru</button>
    </div>
    {loading ? <div className="py-12 text-center text-sm text-slate-500">Memuat pipeline...</div> : <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white"><table className="min-w-full text-sm"><thead><tr className="text-left text-xs uppercase text-slate-400 border-b"><th className="px-4 py-3">Judul</th><th className="px-4 py-3">Lead</th><th className="px-4 py-3">Value</th><th className="px-4 py-3">Stage</th><th className="px-4 py-3">Prob</th><th className="px-4 py-3">Tanggal</th><th className="px-4 py-3"></th></tr></thead><tbody>{opportunities.map((item) => (<tr key={item.id} className="border-b border-slate-50 hover:bg-slate-50/50"><td className="px-4 py-3 font-medium text-slate-800">{item.title}</td><td className="px-4 py-3 text-slate-600">{item.lead ? <Link href={`/crm/leads/${item.lead.id}`} className="text-blue-600 hover:underline">{item.lead.name}</Link> : '-'}</td><td className="px-4 py-3 text-slate-700">Rp {Math.round(item.value).toLocaleString('id-ID')}</td><td className="px-4 py-3"><span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${item.stage==='deal'?'bg-emerald-100 text-emerald-700':item.stage==='lost'?'bg-red-100 text-red-700':'bg-amber-100 text-amber-700'}`}>{CRM_STATUS[item.stage as keyof typeof CRM_STATUS]??item.stage}</span></td><td className="px-4 py-3 text-slate-600">{item.probability}%</td><td className="px-4 py-3 text-slate-600">{item.offer_date??'-'}</td><td className="px-4 py-3 text-right"><button onClick={() => void remove(item)} className="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-600" title="Hapus"><Trash2 className="h-4 w-4" /></button></td></tr>))}{opportunities.length===0&&<tr><td colSpan={7} className="px-4 py-12 text-center text-slate-400">Belum ada penawaran.</td></tr>}</tbody></table></div>}
    <Drawer open={drawer.mode !== null} title="Penawaran Baru" onClose={drawer.close}>
      <form onSubmit={(e) => { void submit(e); }} className="space-y-4">
        <FormField label="Lead"><select className={fieldClass} value={form.lead_id} onChange={(e) => setForm({ ...form, lead_id: e.target.value })} required disabled={leadsLoading}><option value="">{leadsLoading ? 'Memuat leads...' : 'Pilih lead'}</option>{leads.map((lead) => <option key={lead.id} value={lead.id}>{lead.name} (ID: {lead.id}){lead.company ? ` - ${lead.company}` : ''}</option>)}</select></FormField>
        <FormField label="Judul Penawaran"><input className={fieldClass} value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} required /></FormField>
        <FormField label="Nilai (Rp)"><input type="number" className={fieldClass} value={form.value} onChange={(e) => setForm({ ...form, value: e.target.value })} required /></FormField>
        <FormField label="Tanggal Kirim"><input type="date" className={fieldClass} value={form.offer_date} onChange={(e) => setForm({ ...form, offer_date: e.target.value })} required /></FormField>
        <FormField label="Catatan"><textarea className={fieldClass} rows={2} value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} /></FormField>
        <DrawerButtons onCancel={drawer.close} saving={saving} submitLabel="Simpan" />
      </form>
    </Drawer>
  </div>);
}
