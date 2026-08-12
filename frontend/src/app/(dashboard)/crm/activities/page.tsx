'use client';

import { useCallback, useEffect, useState } from 'react';
import Link from 'next/link';
import { Search } from 'lucide-react';
import { api, type PaginatedResponse } from '@/lib/api';
import type { CrmActivity } from '@/lib/types';

export default function ActivitiesPage() {
  const [activities, setActivities] = useState<CrmActivity[]>([]);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const load = useCallback(async () => {
    setLoading(true); setError('');
    try { setActivities((await api.get<PaginatedResponse<CrmActivity>>('/crm/activities', { per_page: 100 })).data); }
    catch (err) { setError(err instanceof Error ? err.message : 'Aktivitas gagal dimuat.'); }
    finally { setLoading(false); }
  }, []);

  useEffect(() => { void load(); }, [load]);

  const filtered = activities.filter((a) => {
    if (!search) return true;
    const q = search.toLowerCase();
    return a.description.toLowerCase().includes(q) || a.type.toLowerCase().includes(q) || a.lead?.name?.toLowerCase().includes(q);
  });

  if (loading) return <div className="py-20 text-center text-sm text-slate-500">Memuat timeline aktivitas...</div>;
  if (error) return <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>;

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-2 rounded-lg border bg-white px-3 py-2 max-w-md"><Search className="h-4 w-4 text-slate-400" /><input placeholder="Cari aktivitas..." className="w-full text-sm outline-none" value={search} onChange={(e) => setSearch(e.target.value)} /></div>
      <div className="rounded-xl border border-slate-200 bg-white p-5">
        <h3 className="font-semibold text-slate-800 mb-4">Timeline Aktivitas ({filtered.length})</h3>
        {filtered.length === 0 ? <div className="py-8 text-center text-sm text-slate-400">Belum ada aktivitas CRM yang tercatat.</div> : (
          <div className="space-y-3">
            {filtered.map((act) => (
              <div key={act.id} className="flex items-start gap-3 text-sm border-b border-slate-100 pb-3 last:border-b-0 last:pb-0">
                <span className="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 shrink-0">{act.type}</span>
                <div>
                  <div className="text-slate-700">{act.description}</div>
                  <div className="text-xs text-slate-400 mt-0.5">
                    {act.lead ? <Link href={`/crm/leads/${act.lead.id}`} className="text-blue-600 hover:underline">{act.lead.name}</Link> : '-'}
                    &nbsp;&middot;&nbsp;
                    {new Date(act.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })}
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
