'use client';

import { FormEvent, useCallback, useEffect, useState } from 'react';
import Link from 'next/link';
import { Eye, Pencil, Search, UserPlus } from 'lucide-react';
import { api, type PaginatedResponse } from '@/lib/api';
import type { CrmLead } from '@/lib/types';
import { CRM_SOURCE, CRM_STATUS } from '@/lib/types';
import { Drawer, FormField, fieldClass } from '@/components/ui/Drawer';
import { DrawerButtons } from '@/components/ui/DrawerButtons';
import { useDrawer } from '@/lib/use-drawer';

const blankForm = { name: '', phone: '', company: '', email: '', source: 'meta_ads', requirement: '', notes: '', entered_at: new Date().toISOString().slice(0, 10) };

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
      setLead(res.data); setForm({ name: res.data.name, phone: res.data.phone, company: res.data.company ?? '', email: res.data.email ?? '', source: res.data.source, requirement: res.data.requirement, notes: res.data.notes ?? '', entered_at: res.data.entered_at });
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

  return (<div className="space-y-6">
    {error && <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}
    <div className="flex flex-wrap items-center gap-4">
      <div className="flex items-center gap-2 rounded-lg border bg-white px-3 py-2 flex-1 max-w-md"><Search className="h-4 w-4 text-slate-400" /><input placeholder="Cari nama, perusahaan, atau telepon..." className="w-full text-sm outline-none" value={search} onChange={(e) => setSearch(e.target.value)} /></div>
      <select value={filterStatus} onChange={(e) => setFilterStatus(e.target.value)} className="rounded-lg border bg-white px-3 py-2 text-sm"><option value="">Semua Status</option>{Object.entries(CRM_STATUS).map(([k, v]) => <option key={k} value={k}>{v}</option>)}</select>
      <button onClick={openAdd} className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"><UserPlus className="h-4 w-4" /> Lead Baru</button>
    </div>
    {loading ? <div className="py-12 text-center text-sm text-slate-500">Memuat leads...</div> : <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white"><table className="min-w-full text-sm"><thead><tr className="text-left text-xs uppercase text-slate-400 border-b"><th className="px-4 py-3">Nama</th><th className="px-4 py-3">Perusahaan</th><th className="px-4 py-3">Source</th><th className="px-4 py-3">Status</th><th className="px-4 py-3">Masuk</th><th className="px-4 py-3">Value</th><th className="px-4 py-3"></th></tr></thead><tbody>{leads.map((item) => (<tr key={item.id} className="border-b border-slate-50 hover:bg-slate-50/50"><td className="px-4 py-3 font-medium text-slate-800">{item.name}</td><td className="px-4 py-3 text-slate-600">{item.company ?? '-'}</td><td className="px-4 py-3 text-slate-600">{CRM_SOURCE[item.source as keyof typeof CRM_SOURCE] ?? item.source}</td><td className="px-4 py-3"><span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${item.status==='deal'?'bg-emerald-100 text-emerald-700':item.status==='lost'?'bg-red-100 text-red-700':'bg-blue-100 text-blue-700'}`}>{item.status_label}</span></td><td className="px-4 py-3 text-slate-600">{item.entered_at}</td><td className="px-4 py-3 text-slate-700">{item.estimated_value!=null?`Rp ${Math.round(item.estimated_value).toLocaleString('id-ID')}`:'-'}</td><td className="px-4 py-3 text-right"><div className="flex items-center gap-2 justify-end"><Link href={`/crm/leads/${item.id}`} className="text-slate-400 hover:text-blue-600"><Eye className="h-4 w-4" /></Link><button onClick={() => drawer.open('edit', item.id)} className="text-slate-400 hover:text-blue-600"><Pencil className="h-4 w-4" /></button></div></td></tr>))}{leads.length===0&&<tr><td colSpan={7} className="px-4 py-12 text-center text-slate-400">Belum ada lead. Klik &ldquo;Lead Baru&rdquo; untuk menambahkan.</td></tr>}</tbody></table></div>}
    <Drawer open={drawer.mode !== null} title={drawer.mode === 'create' ? 'Lead Baru' : 'Edit Lead'} onClose={drawer.close}>
      <form onSubmit={(e) => { void submit(e); }} className="space-y-4">
        <FormField label="Nama"><input className={fieldClass} value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required /></FormField>
        <FormField label="Telepon / WhatsApp"><input className={fieldClass} value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} required /></FormField>
        <FormField label="Perusahaan"><input className={fieldClass} value={form.company} onChange={(e) => setForm({ ...form, company: e.target.value })} /></FormField>
        <FormField label="Email"><input type="email" className={fieldClass} value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} /></FormField>
        <FormField label="Source"><select className={fieldClass} value={form.source} onChange={(e) => setForm({ ...form, source: e.target.value })} required>{Object.entries(CRM_SOURCE).map(([k, v]) => <option key={k} value={k}>{v}</option>)}</select></FormField>
        <FormField label="Kebutuhan"><textarea className={fieldClass} rows={3} value={form.requirement} onChange={(e) => setForm({ ...form, requirement: e.target.value })} required /></FormField>
        <FormField label="Catatan"><textarea className={fieldClass} rows={2} value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} /></FormField>
        <FormField label="Tanggal Masuk"><input type="date" className={fieldClass} value={form.entered_at} onChange={(e) => setForm({ ...form, entered_at: e.target.value })} required /></FormField>
        <DrawerButtons onCancel={drawer.close} saving={saving} submitLabel="Simpan" />
      </form>
    </Drawer>
  </div>);
}
