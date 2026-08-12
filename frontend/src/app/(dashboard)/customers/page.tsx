'use client';

import { FormEvent, useEffect, useState } from 'react';
import { Eye, Pencil, Plus, Search, Users } from 'lucide-react';
import { api, type ApiResponse, type PaginatedResponse } from '@/lib/api';
import type { Customer } from '@/lib/types';
import { Drawer, FormField, fieldClass } from '@/components/ui/Drawer';
import { DrawerButtons } from '@/components/ui/DrawerButtons';
import { useDrawer } from '@/lib/use-drawer';

const blank = { name: '', pic_name: '', whatsapp: '', email: '', address: '', status: 'active', notes: '' };

export default function CustomersPage() {
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [customer, setCustomer] = useState<Customer | null>(null);
  const [form, setForm] = useState(blank);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const drawer = useDrawer();

  const load = async () => {
    setLoading(true);
    try { setCustomers((await api.get<PaginatedResponse<Customer>>('/customers', { search, per_page: 100 })).data); }
    catch (err) { setError(err instanceof Error ? err.message : 'Customer gagal dimuat.'); }
    finally { setLoading(false); }
  };

  useEffect(() => { void load(); }, [search]);

  useEffect(() => {
    if (!drawer.id) { setCustomer(null); return; }
    void api.get<ApiResponse<Customer>>(`/customers/${drawer.id}`).then((res) => {
      setCustomer(res.data);
      setForm({ name: res.data.name, pic_name: res.data.pic_name || '', whatsapp: res.data.whatsapp || '', email: res.data.email || '', address: res.data.address || '', status: res.data.status, notes: res.data.notes || '' });
    }).catch((err) => setError(err instanceof Error ? err.message : 'Detail customer gagal dimuat.'));
  }, [drawer.id]);

  const openCreate = () => { setCustomer(null); setForm(blank); drawer.open('create'); };
  const submit = async (event: FormEvent) => {
    event.preventDefault(); setSaving(true); setError('');
    try {
      if (drawer.mode === 'edit' && drawer.id) await api.put(`/customers/${drawer.id}`, form);
      else await api.post('/customers', form);
      await load(); drawer.close();
    } catch (err) { setError(err instanceof Error ? err.message : 'Customer gagal disimpan.'); }
    finally { setSaving(false); }
  };

  return <div className="space-y-6">
    <div className="flex items-end justify-between gap-4"><div><p className="text-sm font-semibold uppercase tracking-wider text-blue-600">Relationship</p><h1 className="mt-1 text-3xl font-bold tracking-tight text-slate-900">Customers</h1><p className="mt-2 text-sm text-slate-500">Kelola customer dan seluruh hubungan project mereka.</p></div><button onClick={openCreate} className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"><Plus size={17} /> Customer Baru</button></div>
    <div className="relative rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><Search size={18} className="absolute left-7 top-1/2 -translate-y-1/2 text-slate-400" /><input type="search" placeholder="Cari nama, PIC, WhatsApp, atau email..." value={search} onChange={(e) => setSearch(e.target.value)} className={`${fieldClass} pl-10`} /></div>
    {error && <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}
    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">{loading ? <div className="p-14 text-center text-sm text-slate-500">Memuat customer...</div> : customers.length === 0 ? <div className="flex flex-col items-center px-6 py-16 text-center"><Users size={36} className="text-slate-300" /><h2 className="mt-4 font-bold text-slate-900">Belum ada customer</h2><p className="mt-2 text-sm text-slate-500">Tambahkan customer pertama untuk mulai mengelola project.</p></div> : <div className="overflow-x-auto"><table className="min-w-full divide-y divide-slate-200"><thead className="bg-slate-50"><tr>{['Name','PIC','WhatsApp','Email','Status','Projects','Actions'].map((head) => <th key={head} className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">{head}</th>)}</tr></thead><tbody className="divide-y divide-slate-100">{customers.map((item) => <tr key={item.id} className="transition hover:bg-slate-50"><td className="px-6 py-4 text-sm font-semibold text-slate-900">{item.name}</td><td className="px-6 py-4 text-sm text-slate-500">{item.pic_name || '-'}</td><td className="px-6 py-4 text-sm text-slate-500">{item.whatsapp || '-'}</td><td className="px-6 py-4 text-sm text-slate-500">{item.email || '-'}</td><td className="px-6 py-4"><span className={`rounded-full px-2.5 py-1 text-xs font-semibold ${item.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'}`}>{item.status === 'active' ? 'Active' : 'Non Active'}</span></td><td className="px-6 py-4 text-sm text-slate-500">{item.projects_count || 0}</td><td className="px-6 py-4"><div className="flex gap-2"><button onClick={() => drawer.open('show', item.id)} className="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-blue-600" title="Lihat"><Eye size={16} /></button><button onClick={() => drawer.open('edit', item.id)} className="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-blue-600" title="Edit"><Pencil size={16} /></button></div></td></tr>)}</tbody></table></div>}</div>
    <Drawer open={drawer.mode !== null} onClose={drawer.close} eyebrow="Customer" title={drawer.mode === 'create' ? 'Tambah Customer' : drawer.mode === 'edit' ? 'Edit Customer' : 'Detail Customer'} footer={drawer.mode === 'create' || drawer.mode === 'edit' ? <DrawerButtons formId="customer-drawer-form" saving={saving} onCancel={drawer.close} submitLabel={drawer.mode === 'create' ? 'Tambah Customer' : 'Simpan Perubahan'} /> : undefined}>
      {drawer.mode === 'show' && customer ? <div className="space-y-6"><div className="rounded-xl bg-slate-900 p-5 text-white"><p className="text-xs uppercase tracking-wider text-blue-300">Customer profile</p><h3 className="mt-2 text-2xl font-bold">{customer.name}</h3><p className="mt-1 text-sm text-slate-300">{customer.pic_name || 'PIC belum diisi'}</p></div><div className="grid grid-cols-2 gap-4 text-sm"><div><p className="text-xs font-bold uppercase text-slate-400">WhatsApp</p><p className="mt-1 text-slate-800">{customer.whatsapp || '-'}</p></div><div><p className="text-xs font-bold uppercase text-slate-400">Email</p><p className="mt-1 break-words text-slate-800">{customer.email || '-'}</p></div></div><div><p className="text-xs font-bold uppercase text-slate-400">Alamat</p><p className="mt-2 rounded-lg bg-slate-50 p-4 text-sm leading-6 text-slate-600">{customer.address || 'Belum ada alamat.'}</p></div><button onClick={() => drawer.open('edit', customer.id)} className="w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700">Edit customer</button></div> : <form id="customer-drawer-form" onSubmit={submit} className="space-y-5"><FormField label="Nama Customer"><input required value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} className={fieldClass} /></FormField><FormField label="Nama PIC"><input value={form.pic_name} onChange={(e) => setForm({ ...form, pic_name: e.target.value })} className={fieldClass} /></FormField><div className="grid grid-cols-2 gap-4"><FormField label="WhatsApp"><input value={form.whatsapp} onChange={(e) => setForm({ ...form, whatsapp: e.target.value })} className={fieldClass} /></FormField><FormField label="Email"><input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} className={fieldClass} /></FormField></div><FormField label="Status"><select value={form.status} onChange={(e) => setForm({ ...form, status: e.target.value })} className={fieldClass}><option value="active">Active</option><option value="non_active">Non Active</option></select></FormField><FormField label="Alamat"><textarea rows={3} value={form.address} onChange={(e) => setForm({ ...form, address: e.target.value })} className={fieldClass} /></FormField><FormField label="Catatan"><textarea rows={4} value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} className={fieldClass} /></FormField></form>}
    </Drawer>
  </div>;
}
