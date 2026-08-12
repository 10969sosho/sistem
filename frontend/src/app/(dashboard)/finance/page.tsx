'use client';

import { FormEvent, useEffect, useState } from 'react';
import { api, type PaginatedResponse } from '@/lib/api';
import type { Finance } from '@/lib/types';
import Link from 'next/link';
import { CircleDollarSign, Eye, Pencil, Plus, Search, WalletCards } from 'lucide-react';
import type { Project } from '@/lib/types';
import { Drawer, FormField, fieldClass } from '@/components/ui/Drawer';
import { DrawerButtons } from '@/components/ui/DrawerButtons';
import { useDrawer } from '@/lib/use-drawer';

export default function FinancePage() {
  const [finances, setFinances] = useState<Finance[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);
  const [error, setError] = useState('');
  const [projects, setProjects] = useState<Project[]>([]);
  const [selected, setSelected] = useState<Finance | null>(null);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({ project_id: '', total: '', dp: '0', termin1: '0', termin2: '0', termin3: '0', pelunasan: '0' });
  const drawer = useDrawer();

  const fetchFinances = async () => {
    setLoading(true);
    setError('');
    try {
      const params: Record<string, string | number> = { page };
      if (search) params.search = search;
      if (statusFilter) params.payment_status = statusFilter;
      
      const res = await api.get<PaginatedResponse<Finance>>('/finance', params as Record<string, string>);
      setFinances(res.data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Data finance gagal dimuat.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchFinances();
  }, [search, statusFilter, page]);
  useEffect(() => { void api.get<PaginatedResponse<Project>>('/projects', { per_page: 100 }).then((res) => setProjects(res.data)); }, []);
  useEffect(() => {
    if (!drawer.id) { setSelected(null); return; }
    void api.get<{ data: Finance }>(`/finance/${drawer.id}`).then((res) => { setSelected(res.data); setForm({ project_id: String(res.data.project_id), total: String(res.data.total), dp: String(res.data.dp), termin1: String(res.data.termin1), termin2: String(res.data.termin2), termin3: String(res.data.termin3), pelunasan: String(res.data.pelunasan) }); }).catch((err) => setError(err instanceof Error ? err.message : 'Detail finance gagal dimuat.'));
  }, [drawer.id]);
  const openCreate = () => { setSelected(null); setForm({ project_id: '', total: '', dp: '0', termin1: '0', termin2: '0', termin3: '0', pelunasan: '0' }); drawer.open('create'); };
  const submit = async (event: FormEvent) => { event.preventDefault(); setSaving(true); setError(''); try { await api.post('/finance', { project_id: Number(form.project_id), total: Number(form.total), dp: Number(form.dp), termin1: Number(form.termin1), termin2: Number(form.termin2), termin3: Number(form.termin3), pelunasan: Number(form.pelunasan) }); await fetchFinances(); drawer.close(); } catch (err) { setError(err instanceof Error ? err.message : 'Finance gagal disimpan.'); } finally { setSaving(false); } };

  const statusColor = (status: string) => {
    switch (status) {
      case 'lunas': return 'bg-green-100 text-green-800';
      case 'sebagian': return 'bg-yellow-100 text-yellow-800';
      default: return 'bg-red-100 text-red-800';
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  return (
    <div>
      <div className="mb-6 flex items-end justify-between gap-4">
        <div>
          <p className="text-sm font-semibold uppercase tracking-wider text-emerald-600">Project Finance</p>
          <h1 className="mt-1 text-3xl font-bold tracking-tight text-slate-900">Finance</h1>
          <p className="mt-2 text-sm text-slate-500">Monitoring pembayaran project, tanpa kompleksitas accounting.</p>
        </div>
        <button onClick={openCreate} className="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700"><Plus size={17} /> Tambah Finance</button>
      </div>

      <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
          <div className="relative">
            <Search size={18} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="search"
            placeholder="Cari nama project..."
            value={search}
            onChange={(e) => { setSearch(e.target.value); setPage(1); }}
            className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
          />
          </div>
          <select
            value={statusFilter}
            onChange={(e) => { setStatusFilter(e.target.value); setPage(1); }}
            className="rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
          >
            <option value="">All Status</option>
            <option value="belum_bayar">Belum Bayar</option>
            <option value="sebagian">Sebagian</option>
            <option value="lunas">Lunas</option>
          </select>
        </div>
      </div>

      {error && <div className="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}

      <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        {loading ? (
          <div className="p-14 text-center text-sm text-slate-500">Memuat data pembayaran...</div>
        ) : finances.length === 0 ? (
          <div className="flex flex-col items-center px-6 py-16 text-center">
            <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-500"><WalletCards size={30} /></div>
            <h2 className="mt-5 text-base font-bold text-slate-900">Belum ada data pembayaran</h2>
            <p className="mt-2 max-w-sm text-sm leading-6 text-slate-500">Tambahkan finance dari detail project untuk memantau DP, termin, dan sisa tagihan.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-slate-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remaining</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {finances.map((finance) => (
                  <tr key={finance.id}>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {finance.project?.name || '-'}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {formatCurrency(finance.total)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {formatCurrency(finance.total_paid)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {formatCurrency(finance.remaining)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusColor(finance.payment_status)}`}>
                        {finance.payment_status}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm"><div className="flex gap-2"><button onClick={() => drawer.open('show', finance.id)} className="rounded-lg p-2 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600"><Eye size={16} /></button><button onClick={() => drawer.open('edit', finance.id)} className="rounded-lg p-2 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600"><Pencil size={16} /></button></div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      <Drawer open={drawer.mode !== null} onClose={drawer.close} eyebrow="Project Finance" title={drawer.mode === 'create' ? 'Tambah Finance' : drawer.mode === 'edit' ? 'Edit Finance' : 'Detail Finance'} footer={drawer.mode === 'create' || drawer.mode === 'edit' ? <DrawerButtons formId="finance-drawer-form" saving={saving} onCancel={drawer.close} submitLabel="Simpan Finance" /> : undefined}>
        {drawer.mode === 'show' && selected ? <div className="space-y-6"><div className="rounded-xl bg-slate-900 p-5 text-white"><p className="text-xs uppercase tracking-wider text-emerald-300">Payment overview</p><h3 className="mt-2 text-2xl font-bold">{selected.project?.name || 'Project'}</h3><p className="mt-1 text-sm text-slate-300">{selected.project?.customer?.name || '-'}</p></div><div className="grid grid-cols-2 gap-4 text-sm"><div><p className="text-xs font-bold uppercase text-slate-400">Total Project</p><p className="mt-1 font-semibold text-slate-800">{formatCurrency(selected.total)}</p></div><div><p className="text-xs font-bold uppercase text-slate-400">Status</p><p className="mt-1 font-semibold text-slate-800">{selected.payment_status}</p></div><div><p className="text-xs font-bold uppercase text-slate-400">Total Dibayar</p><p className="mt-1 text-emerald-700">{formatCurrency(selected.total_paid)}</p></div><div><p className="text-xs font-bold uppercase text-slate-400">Sisa Tagihan</p><p className="mt-1 text-red-600">{formatCurrency(selected.remaining)}</p></div></div><div className="space-y-3 rounded-lg bg-slate-50 p-4 text-sm"><p className="flex justify-between"><span>DP</span><strong>{formatCurrency(selected.dp)}</strong></p><p className="flex justify-between"><span>Termin 1</span><strong>{formatCurrency(selected.termin1)}</strong></p><p className="flex justify-between"><span>Termin 2</span><strong>{formatCurrency(selected.termin2)}</strong></p><p className="flex justify-between"><span>Termin 3</span><strong>{formatCurrency(selected.termin3)}</strong></p><p className="flex justify-between"><span>Pelunasan</span><strong>{formatCurrency(selected.pelunasan)}</strong></p></div><button onClick={() => drawer.open('edit', selected.id)} className="w-full rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Edit data finance</button></div> : <form id="finance-drawer-form" onSubmit={submit} className="space-y-5"><FormField label="Project"><select required value={form.project_id} onChange={(e) => setForm({ ...form, project_id: e.target.value })} className={fieldClass}><option value="">Pilih project</option>{projects.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}</select></FormField><FormField label="Total Project"><input required type="number" min="0" value={form.total} onChange={(e) => setForm({ ...form, total: e.target.value })} className={fieldClass} /></FormField><div className="grid grid-cols-2 gap-4">{(['dp', 'termin1', 'termin2', 'termin3', 'pelunasan'] as const).map((field) => <FormField key={field} label={field === 'dp' ? 'DP' : field === 'pelunasan' ? 'Pelunasan' : field.replace('termin', 'Termin ')}><input type="number" min="0" value={form[field]} onChange={(e) => setForm({ ...form, [field]: e.target.value })} className={fieldClass} /></FormField>)}</div></form>}
      </Drawer>
    </div>
  );
}
