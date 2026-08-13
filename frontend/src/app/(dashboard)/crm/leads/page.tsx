'use client';

import { FormEvent, useCallback, useEffect, useState } from 'react';
import { Eye, Pencil, Phone, Search, Trash2, UserPlus } from 'lucide-react';
import { api, type PaginatedResponse } from '@/lib/api';
import type { CrmActivity, CrmLead } from '@/lib/types';
import { CRM_SOURCE, CRM_STATUS } from '@/lib/types';
import { Drawer, FormField, fieldClass } from '@/components/ui/Drawer';
import { DrawerButtons } from '@/components/ui/DrawerButtons';
import { useDrawer } from '@/lib/use-drawer';

const blankForm = { name: '', phone: '', company: '', source: 'meta_ads', requirement: '', notes: '', entered_at: new Date().toISOString().slice(0, 10) };

export default function LeadsPage() {
  const [leads, setLeads] = useState<CrmLead[]>([]);
  const [lead, setLead] = useState<CrmLead | null>(null);
  const [form, setForm] = useState(blankForm);
  const [search, setSearch] = useState('');
  const [filterStatus, setFilterStatus] = useState('');
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const drawer = useDrawer();

  const load = useCallback(async () => {
    setLoading(true); setError('');
    try {
      const params: Record<string, string | number> = { per_page: 100 };
      if (search) params.search = search;
      if (filterStatus) params.status = filterStatus;
      setLeads((await api.get<PaginatedResponse<CrmLead>>('/crm/leads', params)).data);
    } catch (err) { setError(err instanceof Error ? err.message : 'Leads gagal dimuat.'); }
    finally { setLoading(false); }
  }, [search, filterStatus]);

  useEffect(() => { void load(); }, [load]);

  useEffect(() => {
    if (!drawer.id || drawer.mode === 'create') { setLead(null); return; }
    void api.get<{ data: CrmLead }>(`/crm/leads/${drawer.id}`).then((res) => {
      setLead(res.data);
      setForm({ name: res.data.name, phone: res.data.phone, company: res.data.company ?? '', source: res.data.source, requirement: res.data.requirement, notes: res.data.notes ?? '', entered_at: res.data.entered_at });
    }).catch(() => setError('Gagal memuat detail lead.'));
  }, [drawer.id, drawer.mode]);

  const openAdd = () => { setLead(null); setForm(blankForm); drawer.open('create'); };

  const submit = async (e: FormEvent) => {
    e.preventDefault(); setSaving(true); setError('');
    try {
      if (drawer.mode === 'create') await api.post<{ data: CrmLead }>('/crm/leads', form);
      else if (drawer.id) await api.put(`/crm/leads/${drawer.id}`, form);
      drawer.close(); await load();
    } catch (err) {
      const msg = err instanceof Error ? err.message : 'Gagal menyimpan.';
      const details = (err as { data?: { errors?: Record<string, string[]> } })?.data?.errors;
      setError(details ? Object.values(details).flat().join('. ') : msg);
    } finally { setSaving(false); }
  };

  const remove = async (item: CrmLead) => {
    if (!window.confirm(`Hapus lead "${item.name}"?`)) return;
    setError('');
    try { await api.delete(`/crm/leads/${item.id}`); await load(); }
    catch (err) { setError(err instanceof Error ? err.message : 'Lead gagal dihapus.'); }
  };

  return (<div className="space-y-6">
    {error && <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}
    <div className="flex flex-wrap items-center gap-4">
      <div className="flex items-center gap-2 rounded-lg border bg-white px-3 py-2 flex-1 max-w-md"><Search className="h-4 w-4 text-slate-400" /><input placeholder="Cari nama, perusahaan, atau telepon..." className="w-full text-sm outline-none" value={search} onChange={(e) => setSearch(e.target.value)} /></div>
      <select value={filterStatus} onChange={(e) => setFilterStatus(e.target.value)} className="rounded-lg border bg-white px-3 py-2 text-sm"><option value="">Semua Status</option>{Object.entries(CRM_STATUS).map(([k, v]) => <option key={k} value={k}>{v}</option>)}</select>
      <button onClick={openAdd} className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"><UserPlus className="h-4 w-4" /> Lead Baru</button>
    </div>
    {loading ? <div className="py-12 text-center text-sm text-slate-500">Memuat leads...</div> : <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white"><table className="min-w-full text-sm"><thead><tr className="text-left text-xs uppercase text-slate-400 border-b"><th className="px-4 py-3">Nama</th><th className="px-4 py-3">Perusahaan</th><th className="px-4 py-3">Source</th><th className="px-4 py-3">Status</th><th className="px-4 py-3">Masuk</th><th className="px-4 py-3">Value</th><th className="px-4 py-3"></th></tr></thead><tbody>{leads.map((item) => (<tr key={item.id} className="border-b border-slate-50 hover:bg-slate-50/50"><td className="px-4 py-3 font-medium text-slate-800">{item.name}</td><td className="px-4 py-3 text-slate-600">{item.company ?? '-'}</td><td className="px-4 py-3 text-slate-600">{CRM_SOURCE[item.source as keyof typeof CRM_SOURCE] ?? item.source}</td><td className="px-4 py-3"><span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${item.status==='deal'?'bg-emerald-100 text-emerald-700':item.status==='lost'?'bg-red-100 text-red-700':'bg-blue-100 text-blue-700'}`}>{item.status_label}</span></td><td className="px-4 py-3 text-slate-600">{item.entered_at}</td><td className="px-4 py-3 text-slate-700">{item.estimated_value!=null?`Rp ${Math.round(item.estimated_value).toLocaleString('id-ID')}`:'-'}</td><td className="px-4 py-3 text-right"><div className="flex items-center gap-2 justify-end"><button onClick={() => drawer.open('show', item.id)} className="text-slate-400 hover:text-blue-600" title="Open Detail"><Eye className="h-4 w-4" /></button><button onClick={() => drawer.open('edit', item.id)} className="text-slate-400 hover:text-blue-600" title="Edit Lead"><Pencil className="h-4 w-4" /></button><button onClick={() => void remove(item)} className="text-slate-400 hover:text-red-600" title="Hapus Lead"><Trash2 className="h-4 w-4" /></button></div></td></tr>))}{leads.length===0&&<tr><td colSpan={7} className="px-4 py-12 text-center text-slate-400">Belum ada lead. Klik &ldquo;Lead Baru&rdquo; untuk menambahkan.</td></tr>}</tbody></table></div>}
    <Drawer open={drawer.mode !== null} title={drawer.mode === 'create' ? 'Lead Baru' : drawer.mode === 'edit' ? 'Edit Lead' : 'Detail Lead'} onClose={drawer.close}>
      {drawer.mode === 'show' && lead ? <div className="space-y-6"><div className="rounded-xl bg-slate-900 p-5 text-white"><p className="text-xs uppercase tracking-wider text-blue-300">Lead profile</p><h3 className="mt-2 text-2xl font-bold">{lead.name}</h3><p className="mt-1 text-sm text-slate-300">{lead.company || 'Perusahaan belum diisi'}</p></div><div className="flex items-center gap-4 text-sm"><span className="inline-flex items-center gap-1 text-slate-700"><Phone className="h-4 w-4" />{lead.phone}</span><span className={`rounded-full px-3 py-1 text-xs font-semibold uppercase ${lead.status==='deal'?'bg-emerald-100 text-emerald-700':lead.status==='lost'?'bg-red-100 text-red-700':'bg-blue-100 text-blue-700'}`}>{lead.status_label}</span></div><div className="grid grid-cols-2 gap-4 text-sm"><div><p className="text-xs font-bold uppercase text-slate-400">Source</p><p className="mt-1 text-slate-800">{CRM_SOURCE[lead.source as keyof typeof CRM_SOURCE] ?? lead.source}</p></div><div><p className="text-xs font-bold uppercase text-slate-400">Masuk</p><p className="mt-1 text-slate-800">{lead.entered_at}</p></div><div><p className="text-xs font-bold uppercase text-slate-400">Est. Value</p><p className="mt-1 text-slate-800">{lead.estimated_value!=null?`Rp ${Math.round(lead.estimated_value).toLocaleString('id-ID')}`:'-'}</p></div><div><p className="text-xs font-bold uppercase text-slate-400">Deadline</p><p className="mt-1 text-slate-800">{lead.deadline??'-'}</p></div></div><div><p className="text-xs font-bold uppercase text-slate-400">Kebutuhan</p><p className="mt-2 rounded-lg bg-slate-50 p-4 text-sm leading-6 text-slate-600">{lead.requirement}</p></div>{lead.notes && <div><p className="text-xs font-bold uppercase text-slate-400">Catatan</p><p className="mt-2 rounded-lg bg-slate-50 p-4 text-sm leading-6 text-slate-600">{lead.notes}</p></div>}{lead.activities && lead.activities.length > 0 && <div><p className="text-xs font-bold uppercase text-slate-400">Timeline</p><div className="mt-2 space-y-3">{lead.activities.map((act: CrmActivity) => (<div key={act.id} className="flex items-start gap-3 text-sm border-b border-slate-100 pb-3 last:border-b-0 last:pb-0"><span className="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 shrink-0">{act.type}</span><div><div className="text-slate-700">{act.description}</div><div className="text-xs text-slate-400 mt-0.5">{new Date(act.created_at).toLocaleDateString('id-ID',{day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'})}</div></div></div>))}</div></div>}<button onClick={() => drawer.open('edit', lead.id)} className="w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700">Edit lead</button></div> : <form onSubmit={(e) => { void submit(e); }} className="space-y-4">
        <FormField label="Nama"><input className={fieldClass} value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required /></FormField>
        <FormField label="Telepon / WhatsApp"><input className={fieldClass} value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} required /></FormField>
        <FormField label="Perusahaan"><input className={fieldClass} value={form.company} onChange={(e) => setForm({ ...form, company: e.target.value })} /></FormField>
        <FormField label="Source"><select className={fieldClass} value={form.source} onChange={(e) => setForm({ ...form, source: e.target.value })} required>{Object.entries(CRM_SOURCE).map(([k, v]) => <option key={k} value={k}>{v}</option>)}</select></FormField>
        <FormField label="Kebutuhan"><textarea className={fieldClass} rows={3} value={form.requirement} onChange={(e) => setForm({ ...form, requirement: e.target.value })} required /></FormField>
        <FormField label="Catatan"><textarea className={fieldClass} rows={2} value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} /></FormField>
        <FormField label="Tanggal Masuk"><input type="date" className={fieldClass} value={form.entered_at} onChange={(e) => setForm({ ...form, entered_at: e.target.value })} required /></FormField>
        <DrawerButtons onCancel={drawer.close} saving={saving} submitLabel="Simpan" />
      </form>}
    </Drawer>
  </div>);
}
