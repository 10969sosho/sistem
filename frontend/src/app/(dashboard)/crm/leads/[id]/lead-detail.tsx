'use client';

import { FormEvent, useCallback, useEffect, useState } from 'react';
import { api } from '@/lib/api';
import type { CrmActivity, CrmLead } from '@/lib/types';
import { CRM_SOURCE, CRM_STATUS } from '@/lib/types';
import { fieldClass } from '@/components/ui/Drawer';
import { useParams } from 'next/navigation';
import Link from 'next/link';
import { ArrowLeft, Phone } from 'lucide-react';

const activityForm = { type: 'whatsapp', description: '' };

export default function LeadDetail() {
  const params = useParams<{ id: string }>();
  const [lead, setLead] = useState<CrmLead | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [actForm, setActForm] = useState(activityForm);
  const [savingAct, setSavingAct] = useState(false);

  const load = useCallback(async () => {
    setLoading(true); setError('');
    try { setLead((await api.get<{ data: CrmLead }>(`/crm/leads/${params.id}`)).data); }
    catch (err) { setError(err instanceof Error ? err.message : 'Detail lead gagal dimuat.'); }
    finally { setLoading(false); }
  }, [params.id]);

  useEffect(() => { if (params.id && params.id !== 'placeholder') void load(); }, [load, params.id]);

  const addActivity = async (e: FormEvent) => {
    e.preventDefault(); setSavingAct(true); setError('');
    try { await api.post(`/crm/leads/${params.id}/activities`, actForm); setActForm(activityForm); await load(); }
    catch (err) { setError(err instanceof Error ? err.message : 'Gagal mencatat aktivitas.'); }
    finally { setSavingAct(false); }
  };

  const changeStatusQuick = async (status: string) => {
    setError('');
    try { await api.patch(`/crm/leads/${params.id}/status`, { status }); await load(); }
    catch (err) { setError(err instanceof Error ? err.message : 'Gagal mengubah status.'); }
  };

  if (loading) return <div className="py-20 text-center text-sm text-slate-500">Memuat detail lead...</div>;
  if (error && !lead) return <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>;
  if (!lead) return null;

  return (
    <div className="space-y-6">
      {error && <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}
      <div className="flex flex-wrap items-center gap-4">
        <Link href="/crm/leads" className="text-sm font-semibold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1"><ArrowLeft className="h-4 w-4" /> Kembali ke Leads</Link>
      </div>
      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div className="lg:col-span-2 space-y-6">
          <div className="rounded-xl border border-slate-200 bg-white p-5">
            <div className="flex items-start justify-between">
              <div><h1 className="text-xl font-bold text-slate-900">{lead.name}</h1>
                <div className="text-sm text-slate-500 mt-1"><span className="inline-flex items-center gap-1"><Phone className="h-3 w-3" />{lead.phone}</span>{lead.company && <span className="ml-3">{lead.company}</span>}</div>
              </div>
              <span className={`rounded-full px-3 py-1 text-xs font-semibold uppercase ${lead.status==='deal'?'bg-emerald-100 text-emerald-700':lead.status==='lost'?'bg-red-100 text-red-700':'bg-blue-100 text-blue-700'}`}>{lead.status_label}</span>
            </div>
            <div className="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
              <div><span className="text-slate-400">Source</span><div className="font-medium">{CRM_SOURCE[lead.source as keyof typeof CRM_SOURCE] ?? lead.source}</div></div>
              <div><span className="text-slate-400">Masuk</span><div className="font-medium">{lead.entered_at}</div></div>
              <div><span className="text-slate-400">Est. Value</span><div className="font-medium">{lead.estimated_value!=null?`Rp ${Math.round(lead.estimated_value).toLocaleString('id-ID')}`:'-'}</div></div>
              <div><span className="text-slate-400">Deadline</span><div className="font-medium">{lead.deadline??'-'}</div></div>
            </div>
            <div className="mt-4"><div className="text-sm font-medium text-slate-700">Kebutuhan</div><div className="text-sm text-slate-600 mt-1">{lead.requirement}</div></div>
            {lead.notes && <div className="mt-3"><div className="text-sm font-medium text-slate-700">Catatan</div><div className="text-sm text-slate-600 mt-1">{lead.notes}</div></div>}
          </div>
          <div className="rounded-xl border border-slate-200 bg-white p-5"><h3 className="font-semibold text-slate-800 mb-4">Quick Status</h3>
            <div className="flex flex-wrap gap-2">
              {Object.entries(CRM_STATUS).map(([key, label]) => (
                <button key={key} disabled={lead.status===key} onClick={()=>changeStatusQuick(key)} className={`rounded-full px-3 py-1.5 text-xs font-semibold border transition-colors ${lead.status===key?'bg-slate-100 text-slate-400 border-slate-200 cursor-default':'bg-white text-slate-600 border-slate-300 hover:border-blue-400 hover:text-blue-700'}`}>{label}</button>
              ))}
            </div>
          </div>
          {lead.opportunities && lead.opportunities.length > 0 && (
            <div className="rounded-xl border border-slate-200 bg-white p-5">
              <div className="flex items-center justify-between mb-4"><h3 className="font-semibold text-slate-800">Penawaran ({lead.opportunities.length})</h3><Link href="/crm/opportunities" className="text-sm text-blue-600 hover:underline">Lihat Pipeline</Link></div>
              <div className="space-y-3">{lead.opportunities.map((opp) => (<div key={opp.id} className="flex items-center justify-between border-b border-slate-100 pb-3 last:border-b-0 last:pb-0"><div><div className="font-medium text-slate-800">{opp.title}</div><div className="text-xs text-slate-500 mt-0.5">Stage: {CRM_STATUS[opp.stage as keyof typeof CRM_STATUS] ?? opp.stage}</div></div><div className="text-sm font-semibold text-slate-700">Rp {Math.round(opp.value).toLocaleString('id-ID')}</div></div>))}</div>
            </div>
          )}
        </div>
        <div className="space-y-6">
          <div className="rounded-xl border border-slate-200 bg-white p-5"><h3 className="font-semibold text-slate-800 mb-4">Tambah Aktivitas</h3>
            <form onSubmit={(e) => { void addActivity(e); }} className="space-y-3">
              <select className={fieldClass} value={actForm.type} onChange={(e) => setActForm({ ...actForm, type: e.target.value })}>{['whatsapp','call','meeting','note'].map((t) => (<option key={t} value={t}>{t.charAt(0).toUpperCase()+t.slice(1)}</option>))}</select>
              <textarea className={fieldClass} rows={3} placeholder="Deskripsi aktivitas..." value={actForm.description} onChange={(e) => setActForm({ ...actForm, description: e.target.value })} required />
              <button type="submit" disabled={savingAct} className="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">{savingAct?'Menyimpan...':'Catat Aktivitas'}</button>
            </form>
          </div>
          {lead.activities && lead.activities.length > 0 && (
            <div className="rounded-xl border border-slate-200 bg-white p-5"><h3 className="font-semibold text-slate-800 mb-4">Timeline ({lead.activities.length})</h3>
              <div className="space-y-3">{lead.activities.map((act: CrmActivity) => (<div key={act.id} className="flex items-start gap-3 text-sm border-b border-slate-100 pb-3 last:border-b-0 last:pb-0"><span className="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 shrink-0">{act.type}</span><div><div className="text-slate-700">{act.description}</div><div className="text-xs text-slate-400 mt-0.5">{new Date(act.created_at).toLocaleDateString('id-ID',{day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'})}</div></div></div>))}</div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
