'use client';

import { useCallback, useEffect, useMemo, useState } from 'react';
import Link from 'next/link';
import { api } from '@/lib/api';
import type { CrmDashboard } from '@/lib/types';
import { CRM_SOURCE, CRM_STATUS } from '@/lib/types';
import { ArrowUpRight, CircleDollarSign, TrendingUp, UserPlus, Users as UsersIcon, Phone } from 'lucide-react';

export default function CrmDashboardPage() {
  const [data, setData] = useState<CrmDashboard | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const load = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const res = await api.get<{ data: CrmDashboard }>('/crm/dashboard');
      setData(res.data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Dashboard CRM gagal dimuat.');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { void load(); }, [load]);

  const pipelineValue = data?.pipeline_value ?? 0;
  const revenue = data?.revenue ?? 0;
  const totalLeads = Object.values(data?.status_counts ?? {}).reduce((a, b) => a + b, 0);
  const deals = data?.status_counts?.deal ?? 0;

  if (loading) return <div className="py-20 text-center text-sm text-slate-500">Memuat dashboard CRM...</div>;
  if (error) return <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>;
  if (!data) return null;

  return (
    <div className="space-y-8">
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {[
          { label: 'Total Leads', value: totalLeads, icon: UsersIcon, color: 'text-blue-600' },
          { label: 'Pipeline Value', value: `Rp ${Math.round(pipelineValue).toLocaleString('id-ID')}`, icon: TrendingUp, color: 'text-amber-600' },
          { label: 'Revenue', value: `Rp ${Math.round(revenue).toLocaleString('id-ID')}`, icon: CircleDollarSign, color: 'text-emerald-600' },
          { label: 'Deals', value: deals, icon: UserPlus, color: 'text-indigo-600' },
        ].map((stat) => (
          <div key={stat.label} className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div className="flex items-center gap-3">
              <stat.icon className={`h-8 w-8 ${stat.color}`} />
              <div>
                <div className="text-2xl font-bold text-slate-900">{stat.value}</div>
                <div className="text-xs text-slate-500">{stat.label}</div>
              </div>
            </div>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div className="rounded-xl border border-slate-200 bg-white p-5">
          <div className="flex items-center justify-between mb-4">
            <h3 className="font-semibold text-slate-800">Pipeline Status</h3>
            <Link href="/crm/leads" className="text-sm text-blue-600 hover:underline inline-flex items-center gap-1">Semua Leads <ArrowUpRight className="h-3 w-3" /></Link>
          </div>
          <div className="space-y-2">
            {Object.entries(CRM_STATUS).map(([key, label]) => {
              const count = data.status_counts[key as keyof typeof data.status_counts] ?? 0;
              if (count === 0) return null;
              return (
                <div key={key} className="flex items-center justify-between text-sm">
                  <span className="text-slate-600">{label}</span>
                  <span className="font-semibold text-slate-800">{count}</span>
                </div>
              );
            })}
          </div>
        </div>

        <div className="rounded-xl border border-slate-200 bg-white p-5">
          <h3 className="font-semibold text-slate-800 mb-4">Source Performance</h3>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="text-left text-xs uppercase text-slate-400">
                  <th className="pb-2 pr-3">Source</th>
                  <th className="pb-2 pr-3 text-right">Leads</th>
                  <th className="pb-2 pr-3 text-right">Interested</th>
                  <th className="pb-2 text-right">Deals</th>
                </tr>
              </thead>
              <tbody>
                {Object.entries(data.source_stats).map(([source, stats]) => (
                  <tr key={source} className="border-t border-slate-100">
                    <td className="py-2 pr-3 font-medium text-slate-700">{CRM_SOURCE[source as keyof typeof CRM_SOURCE] ?? source}</td>
                    <td className="py-2 pr-3 text-right">{stats.leads}</td>
                    <td className="py-2 pr-3 text-right">{stats.interested}</td>
                    <td className="py-2 text-right">{stats.deals}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {data.recent_leads.length > 0 && (
        <div className="rounded-xl border border-slate-200 bg-white p-5">
          <div className="flex items-center justify-between mb-4">
            <h3 className="font-semibold text-slate-800">Recent Leads</h3>
            <Link href="/crm/leads" className="text-sm text-blue-600 hover:underline inline-flex items-center gap-1">Semua <ArrowUpRight className="h-3 w-3" /></Link>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {data.recent_leads.map((lead) => (
              <Link key={lead.id} href={`/crm/leads/${lead.id}`} className="block rounded-lg border p-4 hover:border-blue-300 transition-colors">
                <div className="font-semibold text-slate-800">{lead.name}</div>
                <div className="text-xs text-slate-500 mt-1">
                  <span className="inline-flex items-center gap-1"><Phone className="h-3 w-3" />{lead.phone}</span>
                </div>
                <span className={`mt-2 inline-block rounded-full px-2 py-0.5 text-xs font-semibold ${
                  lead.status === 'deal' ? 'bg-emerald-100 text-emerald-700' : lead.status === 'lost' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'
                }`}>{lead.status_label}</span>
              </Link>
            ))}
          </div>
        </div>
      )}

      {data.recent_activities.length > 0 && (
        <div className="rounded-xl border border-slate-200 bg-white p-5">
          <div className="flex items-center justify-between mb-4">
            <h3 className="font-semibold text-slate-800">Recent Activities</h3>
            <Link href="/crm/activities" className="text-sm text-blue-600 hover:underline inline-flex items-center gap-1">Semua <ArrowUpRight className="h-3 w-3" /></Link>
          </div>
          <div className="space-y-3">
            {data.recent_activities.map((act) => (
              <div key={act.id} className="flex items-start gap-3 text-sm border-b border-slate-100 pb-3 last:border-b-0 last:pb-0">
                <span className="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{act.type}</span>
                <div>
                  <div className="text-slate-700">{act.description}</div>
                  <div className="text-xs text-slate-400 mt-0.5">
                    {act.lead?.name ? `Lead: ${act.lead.name}` : ''} &middot; {new Date(act.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })}
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
