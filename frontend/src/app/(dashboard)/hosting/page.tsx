'use client';

import { FormEvent, useEffect, useState } from 'react';
import { api, type PaginatedResponse } from '@/lib/api';
import type { Hosting } from '@/lib/types';
import Link from 'next/link';
import { Eye, Globe2, Pencil, Plus, Search, Server, Trash2 } from 'lucide-react';
import type { Project } from '@/lib/types';
import { Drawer, FormField, fieldClass } from '@/components/ui/Drawer';
import { DrawerButtons } from '@/components/ui/DrawerButtons';
import { useDrawer } from '@/lib/use-drawer';

export default function HostingPage() {
  const [hostings, setHostings] = useState<Hosting[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [error, setError] = useState('');
  const [projects, setProjects] = useState<Project[]>([]);
  const [selected, setSelected] = useState<Hosting | null>(null);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({ project_id: '', provider: '', package: '', expired_date: '', domain: '', registrar: '', domain_expired_date: '', ssl_status: 'active', ssl_expired_date: '', server_ip: '', panel: '', username: '', notes: '' });
  const drawer = useDrawer();

  const fetchHostings = async () => {
    setLoading(true);
    setError('');
    try {
      const params: Record<string, string | number> = { page };
      if (search) params.search = search;
      
      const res = await api.get<PaginatedResponse<Hosting>>('/hosting', params as Record<string, string>);
      setHostings(res.data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Data hosting gagal dimuat.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchHostings();
  }, [search, page]);

  useEffect(() => { void api.get<PaginatedResponse<Project>>('/projects', { per_page: 100 }).then((res) => setProjects(res.data)); }, []);
  useEffect(() => {
    if (!drawer.id) { setSelected(null); return; }
    void api.get<{ data: Hosting }>(`/hosting/${drawer.id}`).then((res) => { setSelected(res.data); setForm({ project_id: String(res.data.project_id), provider: res.data.provider || '', package: res.data.package || '', expired_date: res.data.expired_date || '', domain: res.data.domain || '', registrar: res.data.registrar || '', domain_expired_date: res.data.domain_expired_date || '', ssl_status: res.data.ssl_status || 'active', ssl_expired_date: res.data.ssl_expired_date || '', server_ip: res.data.server_ip || '', panel: res.data.panel || '', username: res.data.username || '', notes: res.data.notes || '' }); }).catch((err) => setError(err instanceof Error ? err.message : 'Detail hosting gagal dimuat.'));
  }, [drawer.id]);
  const openCreate = () => { setSelected(null); setForm({ project_id: '', provider: '', package: '', expired_date: '', domain: '', registrar: '', domain_expired_date: '', ssl_status: 'active', ssl_expired_date: '', server_ip: '', panel: '', username: '', notes: '' }); drawer.open('create'); };
  const submit = async (event: FormEvent) => { event.preventDefault(); setSaving(true); setError(''); try { await api.post('/hosting', { ...form, project_id: Number(form.project_id) }); await fetchHostings(); drawer.close(); } catch (err) { setError(err instanceof Error ? err.message : 'Hosting gagal disimpan.'); } finally { setSaving(false); } };
  const remove = async (item: Hosting) => { if (!window.confirm(`Hapus data hosting untuk "${item.project?.name || '-'}"?`)) return; setError(''); try { await api.delete(`/hosting/project/${item.project_id}`); await fetchHostings(); } catch (err) { setError(err instanceof Error ? err.message : 'Hosting gagal dihapus.'); } };

  const statusColor = (status: string | null) => {
    switch (status) {
      case 'active': return 'bg-green-100 text-green-800';
      case 'expiring': return 'bg-yellow-100 text-yellow-800';
      case 'expired': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div>
      <div className="mb-6 flex items-end justify-between gap-4">
        <div>
          <p className="text-sm font-semibold uppercase tracking-wider text-blue-600">Infrastructure</p>
          <h1 className="mt-1 text-3xl font-bold tracking-tight text-slate-900">Hosting & Domain</h1>
          <p className="mt-2 text-sm text-slate-500">Pantau server, domain, SSL, dan tanggal kedaluwarsa dalam satu tempat.</p>
        </div>
        <button onClick={openCreate} className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"><Plus size={17} /> Tambah Data</button>
      </div>

      <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div className="relative">
          <Search size={18} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="search"
            placeholder="Cari domain, provider, atau project..."
            value={search}
            onChange={(e) => { setSearch(e.target.value); setPage(1); }}
            className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
          />
        </div>
      </div>

      {error && <div className="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}

      <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        {loading ? (
          <div className="p-14 text-center text-sm text-slate-500">Memuat data infrastructure...</div>
        ) : hostings.length === 0 ? (
          <div className="flex flex-col items-center px-6 py-16 text-center">
            <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><Globe2 size={30} /></div>
            <h2 className="mt-5 text-base font-bold text-slate-900">Belum ada data hosting</h2>
            <p className="mt-2 max-w-sm text-sm leading-6 text-slate-500">Data hosting akan muncul setelah ditambahkan dari detail project.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-slate-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Project</th>
                  <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Domain</th>
                  <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Provider</th>
                  <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Hosting Status</th>
                  <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Domain Status</th>
                  <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Expired Date</th>
                  <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {hostings.map((hosting) => (
                  <tr key={hosting.id}>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {hosting.project?.name || '-'}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {hosting.domain || '-'}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {hosting.provider || '-'}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusColor(hosting.hosting_status)}`}>
                        {hosting.hosting_status || '-'}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusColor(hosting.domain_status)}`}>
                        {hosting.domain_status || '-'}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {hosting.expired_date || '-'}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm"><div className="flex gap-2"><button onClick={() => drawer.open('show', hosting.id)} className="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-blue-600"><Eye size={16} /></button><button onClick={() => drawer.open('edit', hosting.id)} className="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-blue-600"><Pencil size={16} /></button><button onClick={() => void remove(hosting)} className="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-red-600" title="Hapus"><Trash2 size={16} /></button></div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      <Drawer open={drawer.mode !== null} onClose={drawer.close} size="lg" eyebrow="Infrastructure" title={drawer.mode === 'create' ? 'Tambah Hosting & Domain' : drawer.mode === 'edit' ? 'Edit Hosting & Domain' : 'Detail Hosting & Domain'} footer={drawer.mode === 'create' || drawer.mode === 'edit' ? <DrawerButtons formId="hosting-drawer-form" saving={saving} onCancel={drawer.close} submitLabel="Simpan Data" /> : undefined}>
        {drawer.mode === 'show' && selected ? <div className="space-y-6"><div className="rounded-xl bg-slate-900 p-5 text-white"><p className="text-xs uppercase tracking-wider text-blue-300">Infrastructure record</p><h3 className="mt-2 text-2xl font-bold">{selected.domain || 'Tanpa domain'}</h3><p className="mt-1 text-sm text-slate-300">{selected.project?.name || '-'}</p></div><div className="grid grid-cols-2 gap-4 text-sm"><div><p className="text-xs font-bold uppercase text-slate-400">Provider</p><p className="mt-1 text-slate-800">{selected.provider || '-'}</p></div><div><p className="text-xs font-bold uppercase text-slate-400">Paket</p><p className="mt-1 text-slate-800">{selected.package || '-'}</p></div><div><p className="text-xs font-bold uppercase text-slate-400">Hosting Expired</p><p className="mt-1 text-slate-800">{selected.expired_date || '-'}</p></div><div><p className="text-xs font-bold uppercase text-slate-400">Domain Expired</p><p className="mt-1 text-slate-800">{selected.domain_expired_date || '-'}</p></div><div><p className="text-xs font-bold uppercase text-slate-400">Server IP</p><p className="mt-1 text-slate-800">{selected.server_ip || '-'}</p></div><div><p className="text-xs font-bold uppercase text-slate-400">Panel</p><p className="mt-1 text-slate-800">{selected.panel || '-'}</p></div></div><button onClick={() => drawer.open('edit', selected.id)} className="w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700">Edit data hosting</button></div> : <form id="hosting-drawer-form" onSubmit={submit} className="grid grid-cols-1 gap-5 sm:grid-cols-2"><div className="sm:col-span-2"><FormField label="Project"><select required value={form.project_id} onChange={(e) => setForm({ ...form, project_id: e.target.value })} className={fieldClass}><option value="">Pilih project</option>{projects.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}</select></FormField></div><FormField label="Provider"><input value={form.provider} onChange={(e) => setForm({ ...form, provider: e.target.value })} className={fieldClass} /></FormField><FormField label="Paket"><input value={form.package} onChange={(e) => setForm({ ...form, package: e.target.value })} className={fieldClass} /></FormField><FormField label="Hosting Expired"><input type="date" value={form.expired_date} onChange={(e) => setForm({ ...form, expired_date: e.target.value })} className={fieldClass} /></FormField><FormField label="Domain"><input value={form.domain} onChange={(e) => setForm({ ...form, domain: e.target.value })} className={fieldClass} /></FormField><FormField label="Registrar"><input value={form.registrar} onChange={(e) => setForm({ ...form, registrar: e.target.value })} className={fieldClass} /></FormField><FormField label="Domain Expired"><input type="date" value={form.domain_expired_date} onChange={(e) => setForm({ ...form, domain_expired_date: e.target.value })} className={fieldClass} /></FormField><FormField label="SSL Status"><select value={form.ssl_status} onChange={(e) => setForm({ ...form, ssl_status: e.target.value })} className={fieldClass}><option value="active">Active</option><option value="non_active">Non Active</option></select></FormField><FormField label="SSL Expired"><input type="date" value={form.ssl_expired_date} onChange={(e) => setForm({ ...form, ssl_expired_date: e.target.value })} className={fieldClass} /></FormField><FormField label="Server IP"><input value={form.server_ip} onChange={(e) => setForm({ ...form, server_ip: e.target.value })} className={fieldClass} /></FormField><FormField label="Panel"><input value={form.panel} onChange={(e) => setForm({ ...form, panel: e.target.value })} className={fieldClass} /></FormField><FormField label="Username"><input value={form.username} onChange={(e) => setForm({ ...form, username: e.target.value })} className={fieldClass} /></FormField><div className="sm:col-span-2"><FormField label="Catatan"><textarea rows={4} value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} className={fieldClass} /></FormField></div></form>}
      </Drawer>
    </div>
  );
}
