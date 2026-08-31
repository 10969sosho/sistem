'use client';

import { FormEvent, useEffect, useState, useCallback } from 'react';
import { api, type PaginatedResponse } from '@/lib/api';
import type { Transaction, Debt, TransactionSummary, DebtSummary } from '@/lib/types';
import {
  KATEGORI_PEMASUKAN,
  KATEGORI_PENGELUARAN,
  METODE_PEMBAYARAN,
  TRANSACTION_TYPE,
  TRANSACTION_STATUS,
  DEBT_TYPE,
  DEBT_STATUS,
  PERSON_OPTIONS,
} from '@/lib/types';
import { Drawer, FormField, fieldClass } from '@/components/ui/Drawer';
import { DrawerButtons } from '@/components/ui/DrawerButtons';
import { useDrawer } from '@/lib/use-drawer';
import {
  ArrowDownCircle,
  ArrowUpCircle,
  Banknote,
  CircleDollarSign,
  Eye,
  Pencil,
  Plus,
  Search,
  Trash2,
  TrendingDown,
  TrendingUp,
  Wallet,
  AlertTriangle,
  LayoutDashboard,
  List,
} from 'lucide-react';

const MONTHS = [
  { value: '', label: 'Semua Bulan' },
  { value: '1', label: 'Januari' },
  { value: '2', label: 'Februari' },
  { value: '3', label: 'Maret' },
  { value: '4', label: 'April' },
  { value: '5', label: 'Mei' },
  { value: '6', label: 'Juni' },
  { value: '7', label: 'Juli' },
  { value: '8', label: 'Agustus' },
  { value: '9', label: 'September' },
  { value: '10', label: 'Oktober' },
  { value: '11', label: 'November' },
  { value: '12', label: 'Desember' },
];

type Tab = 'transactions' | 'summary' | 'debts';

const formatCurrency = (amount: number) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);

export default function KeuanganPage() {
  const [activeTab, setActiveTab] = useState<Tab>('transactions');

  return (
    <div>
      <div className="mb-6">
        <p className="text-sm font-semibold uppercase tracking-wider text-emerald-600">Keuangan</p>
        <h1 className="mt-1 text-3xl font-bold tracking-tight text-slate-900">Keuangan</h1>
        <p className="mt-2 text-sm text-slate-500">Catat transaksi harian, pantau laba/rugi, dan kelola hutang ke owner.</p>
      </div>

      {/* Tab Navigation */}
      <div className="mb-6 flex gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
        <button
          onClick={() => setActiveTab('transactions')}
          className={`flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition ${
            activeTab === 'transactions'
              ? 'bg-emerald-600 text-white shadow-sm'
              : 'text-slate-600 hover:bg-slate-100'
          }`}
        >
          <List size={16} /> Transaksi
        </button>
        <button
          onClick={() => setActiveTab('summary')}
          className={`flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition ${
            activeTab === 'summary'
              ? 'bg-emerald-600 text-white shadow-sm'
              : 'text-slate-600 hover:bg-slate-100'
          }`}
        >
          <LayoutDashboard size={16} /> Dashboard
        </button>
        <button
          onClick={() => setActiveTab('debts')}
          className={`flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition ${
            activeTab === 'debts'
              ? 'bg-emerald-600 text-white shadow-sm'
              : 'text-slate-600 hover:bg-slate-100'
          }`}
        >
          <AlertTriangle size={16} /> Hutang ke Owner
        </button>
      </div>

      {activeTab === 'transactions' && <TransactionsTab />}
      {activeTab === 'summary' && <SummaryTab />}
      {activeTab === 'debts' && <DebtsTab />}
    </div>
  );
}

/* ─── TRANSACTIONS TAB ─── */
function TransactionsTab() {
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [typeFilter, setTypeFilter] = useState('');
  const [categoryFilter, setCategoryFilter] = useState('');
  const [monthFilter, setMonthFilter] = useState('');
  const [yearFilter, setYearFilter] = useState(String(new Date().getFullYear()));
  const [page, setPage] = useState(1);
  const [error, setError] = useState('');
  const [selected, setSelected] = useState<Transaction | null>(null);
  const [saving, setSaving] = useState(false);
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1, total: 0 });
  const drawer = useDrawer();

  const [form, setForm] = useState({
    date: '',
    type: 'pemasukan',
    category: '',
    description: '',
    vendor: '',
    amount: '',
    status: 'paid',
    payment_method: 'Transfer',
  });

  const fetchTransactions = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const params: Record<string, string | number> = { page, per_page: 15 };
      if (search) params.search = search;
      if (typeFilter) params.type = typeFilter;
      if (categoryFilter) params.category = categoryFilter;
      if (monthFilter) params.month = monthFilter;
      if (yearFilter) params.year = yearFilter;

      const res = await api.get<PaginatedResponse<Transaction>>('/transactions', params as Record<string, string>);
      setTransactions(res.data);
      setMeta(res.meta);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Gagal memuat transaksi.');
    } finally {
      setLoading(false);
    }
  }, [page, search, typeFilter, categoryFilter, monthFilter, yearFilter]);

  useEffect(() => { fetchTransactions(); }, [fetchTransactions]);

  useEffect(() => {
    if (!drawer.id) {
      setSelected(null);
      return;
    }
    void api.get<{ data: Transaction }>(`/transactions/${drawer.id}`).then((res) => {
      setSelected(res.data);
      setForm({
        date: res.data.date,
        type: res.data.type,
        category: res.data.category,
        description: res.data.description || '',
        vendor: res.data.vendor || '',
        amount: String(res.data.amount),
        status: res.data.status,
        payment_method: res.data.payment_method || 'Transfer',
      });
    }).catch((err) => setError(err instanceof Error ? err.message : 'Gagal memuat detail.'));
  }, [drawer.id]);

  const openCreate = () => {
    setSelected(null);
    setForm({
      date: new Date().toISOString().split('T')[0],
      type: 'pemasukan',
      category: '',
      description: '',
      vendor: '',
      amount: '',
      status: 'paid',
      payment_method: 'Transfer',
    });
    drawer.open('create');
  };

  const submit = async (event: FormEvent) => {
    event.preventDefault();
    setSaving(true);
    setError('');
    try {
      const payload = {
        date: form.date,
        type: form.type,
        category: form.category,
        description: form.description || null,
        vendor: form.vendor || null,
        amount: Number(form.amount),
        status: form.status,
        payment_method: form.payment_method || null,
      };
      if (drawer.mode === 'edit' && drawer.id) {
        await api.put(`/transactions/${drawer.id}`, payload);
      } else {
        await api.post('/transactions', payload);
      }
      await fetchTransactions();
      drawer.close();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Gagal menyimpan transaksi.');
    } finally {
      setSaving(false);
    }
  };

  const remove = async (item: Transaction) => {
    if (!window.confirm(`Hapus transaksi "${item.description || item.category}"?`)) return;
    setError('');
    try {
      await api.delete(`/transactions/${item.id}`);
      await fetchTransactions();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Gagal menghapus transaksi.');
    }
  };

  const categories = form.type === 'pemasukan' ? [...KATEGORI_PEMASUKAN] : [...KATEGORI_PENGELUARAN];

  const statusColor = (status: string) => {
    switch (status) {
      case 'paid': return 'bg-green-100 text-green-800';
      case 'pending': return 'bg-yellow-100 text-yellow-800';
      case 'cancelled': return 'bg-red-100 text-red-800';
      default: return 'bg-slate-100 text-slate-800';
    }
  };

  const typeColor = (type: string) =>
    type === 'pemasukan' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800';

  return (
    <div>
      <div className="mb-6 flex items-end justify-between gap-4">
        <div>
          <p className="text-sm font-semibold uppercase tracking-wider text-emerald-600">Transaksi</p>
          <h2 className="mt-1 text-xl font-bold tracking-tight text-slate-900">Daftar Transaksi</h2>
        </div>
        <button onClick={openCreate} className="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
          <Plus size={17} /> Tambah Transaksi
        </button>
      </div>

      {/* Filters */}
      <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div className="grid grid-cols-1 gap-3 md:grid-cols-5">
          <div className="relative md:col-span-2">
            <Search size={18} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              type="search"
              placeholder="Cari deskripsi, vendor..."
              value={search}
              onChange={(e) => { setSearch(e.target.value); setPage(1); }}
              className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
            />
          </div>
          <select value={typeFilter} onChange={(e) => { setTypeFilter(e.target.value); setPage(1); }} className="rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
            <option value="">Semua Jenis</option>
            <option value="pemasukan">Pemasukan</option>
            <option value="pengeluaran">Pengeluaran</option>
          </select>
          <select value={monthFilter} onChange={(e) => { setMonthFilter(e.target.value); setPage(1); }} className="rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
            {MONTHS.map((m) => <option key={m.value} value={m.value}>{m.label}</option>)}
          </select>
          <select value={yearFilter} onChange={(e) => { setYearFilter(e.target.value); setPage(1); }} className="rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
            <option value="2026">2026</option>
            <option value="2025">2025</option>
          </select>
        </div>
      </div>

      {error && <div className="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}

      {/* Table */}
      <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        {loading ? (
          <div className="p-14 text-center text-sm text-slate-500">Memuat transaksi...</div>
        ) : transactions.length === 0 ? (
          <div className="flex flex-col items-center px-6 py-16 text-center">
            <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-500"><Wallet size={30} /></div>
            <h2 className="mt-5 text-base font-bold text-slate-900">Belum ada transaksi</h2>
            <p className="mt-2 max-w-sm text-sm leading-6 text-slate-500">Klik &quot;Tambah Transaksi&quot; untuk mulai mencatat pemasukan atau pengeluaran.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-slate-50">
                <tr>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                  <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nominal</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {transactions.map((txn) => (
                  <tr key={txn.id} className="hover:bg-slate-50">
                    <td className="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                      {new Date(txn.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}
                    </td>
                    <td className="whitespace-nowrap px-4 py-3">
                      <span className={`inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ${typeColor(txn.type)}`}>
                        {txn.type === 'pemasukan' ? <ArrowUpCircle size={12} /> : <ArrowDownCircle size={12} />}
                        {TRANSACTION_TYPE[txn.type]}
                      </span>
                    </td>
                    <td className="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{txn.category}</td>
                    <td className="px-4 py-3 text-sm text-gray-600 max-w-[200px] truncate">{txn.description || '-'}</td>
                    <td className="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{txn.vendor || '-'}</td>
                    <td className={`whitespace-nowrap px-4 py-3 text-sm font-semibold text-right ${txn.type === 'pemasukan' ? 'text-emerald-600' : 'text-red-600'}`}>
                      {txn.type === 'pemasukan' ? '+' : '-'} {formatCurrency(txn.amount)}
                    </td>
                    <td className="whitespace-nowrap px-4 py-3">
                      <span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${statusColor(txn.status)}`}>
                        {txn.status}
                      </span>
                    </td>
                    <td className="whitespace-nowrap px-4 py-3 text-sm">
                      <div className="flex gap-1">
                        <button onClick={() => drawer.open('show', txn.id)} className="rounded-lg p-2 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600"><Eye size={15} /></button>
                        <button onClick={() => drawer.open('edit', txn.id)} className="rounded-lg p-2 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600"><Pencil size={15} /></button>
                        <button onClick={() => void remove(txn)} className="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-red-600"><Trash2 size={15} /></button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {/* Pagination */}
        {meta.last_page > 1 && (
          <div className="flex items-center justify-between border-t border-slate-200 bg-white px-4 py-3 sm:px-6">
            <p className="text-sm text-slate-500">
              Halaman {meta.current_page} dari {meta.last_page} ({meta.total} transaksi)
            </p>
            <div className="flex gap-2">
              <button disabled={page <= 1} onClick={() => setPage(page - 1)} className="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50">Sebelumnya</button>
              <button disabled={page >= meta.last_page} onClick={() => setPage(page + 1)} className="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50">Selanjutnya</button>
            </div>
          </div>
        )}
      </div>

      {/* Drawer: Show / Create / Edit */}
      <Drawer
        open={drawer.mode !== null}
        onClose={drawer.close}
        eyebrow="Transaksi"
        title={drawer.mode === 'create' ? 'Tambah Transaksi' : drawer.mode === 'edit' ? 'Edit Transaksi' : 'Detail Transaksi'}
        footer={drawer.mode === 'create' || drawer.mode === 'edit' ? (
          <DrawerButtons formId="txn-form" saving={saving} onCancel={drawer.close} submitLabel="Simpan" />
        ) : undefined}
      >
        {drawer.mode === 'show' && selected ? (
          <div className="space-y-5">
            <div className={`rounded-xl p-5 text-white ${selected.type === 'pemasukan' ? 'bg-emerald-600' : 'bg-orange-600'}`}>
              <p className="text-xs uppercase tracking-wider opacity-80">{TRANSACTION_TYPE[selected.type]}</p>
              <h3 className="mt-2 text-2xl font-bold">{formatCurrency(selected.amount)}</h3>
              <p className="mt-1 text-sm opacity-90">{selected.description || selected.category}</p>
            </div>
            <div className="grid grid-cols-2 gap-4 text-sm">
              <div><p className="text-xs font-bold uppercase text-slate-400">Tanggal</p><p className="mt-1 font-semibold text-slate-800">{new Date(selected.date).toLocaleDateString('id-ID')}</p></div>
              <div><p className="text-xs font-bold uppercase text-slate-400">Kategori</p><p className="mt-1 font-semibold text-slate-800">{selected.category}</p></div>
              <div><p className="text-xs font-bold uppercase text-slate-400">Vendor</p><p className="mt-1 font-semibold text-slate-800">{selected.vendor || '-'}</p></div>
              <div><p className="text-xs font-bold uppercase text-slate-400">Status</p><p className="mt-1 font-semibold text-slate-800">{selected.status}</p></div>
              <div><p className="text-xs font-bold uppercase text-slate-400">Metode Bayar</p><p className="mt-1 font-semibold text-slate-800">{selected.payment_method || '-'}</p></div>
            </div>
            <button onClick={() => drawer.open('edit', selected.id)} className="w-full rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Edit Transaksi</button>
          </div>
        ) : (
          <form id="txn-form" onSubmit={submit} className="space-y-5">
            <FormField label="Tanggal">
              <input type="date" required value={form.date} onChange={(e) => setForm({ ...form, date: e.target.value })} className={fieldClass} />
            </FormField>
            <FormField label="Jenis">
              <select required value={form.type} onChange={(e) => setForm({ ...form, type: e.target.value, category: '' })} className={fieldClass}>
                <option value="pemasukan">Pemasukan</option>
                <option value="pengeluaran">Pengeluaran</option>
              </select>
            </FormField>
            <FormField label="Kategori">
              <select required value={form.category} onChange={(e) => setForm({ ...form, category: e.target.value })} className={fieldClass}>
                <option value="">Pilih kategori...</option>
                {categories.map((c) => <option key={c} value={c}>{c}</option>)}
              </select>
            </FormField>
            <FormField label="Keterangan">
              <input type="text" value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} className={fieldClass} placeholder="Deskripsi transaksi..." />
            </FormField>
            <FormField label="Customer/Vendor">
              <input type="text" value={form.vendor} onChange={(e) => setForm({ ...form, vendor: e.target.value })} className={fieldClass} placeholder="Nama vendor/customer..." />
            </FormField>
            <FormField label="Nominal">
              <input type="number" required min="0" value={form.amount} onChange={(e) => setForm({ ...form, amount: e.target.value })} className={fieldClass} placeholder="0" />
            </FormField>
            <div className="grid grid-cols-2 gap-4">
              <FormField label="Status">
                <select value={form.status} onChange={(e) => setForm({ ...form, status: e.target.value })} className={fieldClass}>
                  <option value="paid">Paid</option>
                  <option value="pending">Pending</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </FormField>
              <FormField label="Metode Bayar">
                <select value={form.payment_method} onChange={(e) => setForm({ ...form, payment_method: e.target.value })} className={fieldClass}>
                  <option value="">Pilih...</option>
                  {METODE_PEMBAYARAN.map((m) => <option key={m} value={m}>{m}</option>)}
                </select>
              </FormField>
            </div>
          </form>
        )}
      </Drawer>
    </div>
  );
}

/* ─── SUMMARY TAB ─── */
function SummaryTab() {
  const [summary, setSummary] = useState<TransactionSummary | null>(null);
  const [loading, setLoading] = useState(true);
  const [year, setYear] = useState(String(new Date().getFullYear()));
  const [month, setMonth] = useState('');

  const fetchSummary = useCallback(async () => {
    setLoading(true);
    try {
      const params: Record<string, string> = { year };
      if (month) params.month = month;
      const res = await api.get<{ data: TransactionSummary }>('/transactions/summary', params);
      setSummary(res.data);
    } catch {
      // ignore
    } finally {
      setLoading(false);
    }
  }, [year, month]);

  useEffect(() => { fetchSummary(); }, [fetchSummary]);

  if (loading) return <div className="p-14 text-center text-sm text-slate-500">Memuat ringkasan...</div>;
  if (!summary) return <div className="p-14 text-center text-sm text-slate-500">Gagal memuat data.</div>;

  return (
    <div>
      <div className="mb-6 flex items-end justify-between gap-4">
        <div>
          <p className="text-sm font-semibold uppercase tracking-wider text-emerald-600">Dashboard Keuangan</p>
          <h2 className="mt-1 text-xl font-bold tracking-tight text-slate-900">Ringkasan Keuangan</h2>
        </div>
        <div className="flex gap-2">
          <select value={month} onChange={(e) => setMonth(e.target.value)} className="rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-emerald-500">
            <option value="">Semua Bulan</option>
            {MONTHS.filter((m) => m.value).map((m) => <option key={m.value} value={m.value}>{m.label}</option>)}
          </select>
          <select value={year} onChange={(e) => setYear(e.target.value)} className="rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-emerald-500">
            <option value="2026">2026</option>
            <option value="2025">2025</option>
          </select>
        </div>
      </div>

      {/* Summary Cards */}
      <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><TrendingUp size={20} /></div>
            <p className="text-xs font-bold uppercase text-slate-400">Total Pemasukan</p>
          </div>
          <p className="mt-3 text-2xl font-bold text-emerald-600">{formatCurrency(summary.total_pemasukan)}</p>
        </div>
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50 text-red-600"><TrendingDown size={20} /></div>
            <p className="text-xs font-bold uppercase text-slate-400">Total Pengeluaran</p>
          </div>
          <p className="mt-3 text-2xl font-bold text-red-600">{formatCurrency(summary.total_pengeluaran)}</p>
        </div>
        <div className={`rounded-xl border bg-white p-5 shadow-sm ${summary.laba_rugi >= 0 ? 'border-emerald-200' : 'border-red-200'}`}>
          <div className="flex items-center gap-3">
            <div className={`flex h-10 w-10 items-center justify-center rounded-lg ${summary.laba_rugi >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600'}`}>
              <CircleDollarSign size={20} />
            </div>
            <p className="text-xs font-bold uppercase text-slate-400">Laba / Rugi</p>
          </div>
          <p className={`mt-3 text-2xl font-bold ${summary.laba_rugi >= 0 ? 'text-emerald-600' : 'text-red-600'}`}>{formatCurrency(summary.laba_rugi)}</p>
        </div>
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600"><Banknote size={20} /></div>
            <p className="text-xs font-bold uppercase text-slate-400">Saldo Kas</p>
          </div>
          <p className={`mt-3 text-2xl font-bold ${summary.saldo_kas >= 0 ? 'text-blue-600' : 'text-red-600'}`}>{formatCurrency(summary.saldo_kas)}</p>
        </div>
      </div>

      {/* Arus Kas */}
      <div className="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 className="mb-4 text-sm font-bold uppercase text-slate-500">Arus Kas</h3>
        <div className="grid grid-cols-3 gap-4">
          <div className="text-center">
            <p className="text-xs text-slate-400">Kas Masuk</p>
            <p className="mt-1 text-lg font-bold text-emerald-600">{formatCurrency(summary.kas_masuk)}</p>
          </div>
          <div className="text-center">
            <p className="text-xs text-slate-400">Kas Keluar</p>
            <p className="mt-1 text-lg font-bold text-red-600">{formatCurrency(summary.kas_keluar)}</p>
          </div>
          <div className="text-center">
            <p className="text-xs text-slate-400">Saldo</p>
            <p className={`mt-1 text-lg font-bold ${summary.saldo_kas >= 0 ? 'text-emerald-600' : 'text-red-600'}`}>{formatCurrency(summary.saldo_kas)}</p>
          </div>
        </div>
      </div>

      {/* Category Breakdown */}
      <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <h3 className="mb-4 text-sm font-bold uppercase text-emerald-600">Pemasukan per Kategori</h3>
          {Object.keys(summary.pemasukan_by_category).length === 0 ? (
            <p className="text-sm text-slate-400">Belum ada data.</p>
          ) : (
            <div className="space-y-3">
              {Object.entries(summary.pemasukan_by_category).sort(([, a], [, b]) => b - a).map(([cat, amount]) => (
                <div key={cat} className="flex items-center justify-between">
                  <span className="text-sm text-slate-700">{cat}</span>
                  <span className="text-sm font-semibold text-emerald-600">{formatCurrency(amount)}</span>
                </div>
              ))}
            </div>
          )}
        </div>
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <h3 className="mb-4 text-sm font-bold uppercase text-red-600">Pengeluaran per Kategori</h3>
          {Object.keys(summary.pengeluaran_by_category).length === 0 ? (
            <p className="text-sm text-slate-400">Belum ada data.</p>
          ) : (
            <div className="space-y-3">
              {Object.entries(summary.pengeluaran_by_category).sort(([, a], [, b]) => b - a).map(([cat, amount]) => (
                <div key={cat} className="flex items-center justify-between">
                  <span className="text-sm text-slate-700">{cat}</span>
                  <span className="text-sm font-semibold text-red-600">{formatCurrency(amount)}</span>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

/* ─── DEBTS TAB ─── */
function DebtsTab() {
  const [debts, setDebts] = useState<Debt[]>([]);
  const [summary, setSummary] = useState<DebtSummary | null>(null);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [typeFilter, setTypeFilter] = useState('');
  const [personFilter, setPersonFilter] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);
  const [error, setError] = useState('');
  const [selected, setSelected] = useState<Debt | null>(null);
  const [saving, setSaving] = useState(false);
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1, total: 0 });
  const drawer = useDrawer();

  const [form, setForm] = useState({
    date: '',
    type: 'talangan',
    person: 'TIAN',
    description: '',
    amount: '',
    status: 'belum_dibayar',
    paid_date: '',
  });

  const fetchDebts = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const params: Record<string, string | number> = { page, per_page: 15 };
      if (search) params.search = search;
      if (typeFilter) params.type = typeFilter;
      if (personFilter) params.person = personFilter;
      if (statusFilter) params.status = statusFilter;

      const [debtRes, summaryRes] = await Promise.all([
        api.get<PaginatedResponse<Debt>>('/debts', params as Record<string, string>),
        api.get<{ data: DebtSummary }>('/debts/summary'),
      ]);
      setDebts(debtRes.data);
      setMeta(debtRes.meta);
      setSummary(summaryRes.data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Gagal memuat data hutang.');
    } finally {
      setLoading(false);
    }
  }, [page, search, typeFilter, personFilter, statusFilter]);

  useEffect(() => { fetchDebts(); }, [fetchDebts]);

  useEffect(() => {
    if (!drawer.id) { setSelected(null); return; }
    void api.get<{ data: Debt }>(`/debts/${drawer.id}`).then((res) => {
      setSelected(res.data);
      setForm({
        date: res.data.date || '',
        type: res.data.type,
        person: res.data.person,
        description: res.data.description,
        amount: String(res.data.amount),
        status: res.data.status,
        paid_date: res.data.paid_date || '',
      });
    }).catch((err) => setError(err instanceof Error ? err.message : 'Gagal memuat detail.'));
  }, [drawer.id]);

  const openCreate = () => {
    setSelected(null);
    setForm({ date: '', type: 'talangan', person: 'TIAN', description: '', amount: '', status: 'belum_dibayar', paid_date: '' });
    drawer.open('create');
  };

  const submit = async (event: FormEvent) => {
    event.preventDefault();
    setSaving(true);
    setError('');
    try {
      const payload = {
        date: form.date || null,
        type: form.type,
        person: form.person,
        description: form.description,
        amount: Number(form.amount),
        status: form.status,
        paid_date: form.paid_date || null,
      };
      if (drawer.mode === 'edit' && drawer.id) {
        await api.put(`/debts/${drawer.id}`, payload);
      } else {
        await api.post('/debts', payload);
      }
      await fetchDebts();
      drawer.close();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Gagal menyimpan data hutang.');
    } finally {
      setSaving(false);
    }
  };

  const remove = async (item: Debt) => {
    if (!window.confirm(`Hapus hutang "${item.description}"?`)) return;
    setError('');
    try {
      await api.delete(`/debts/${item.id}`);
      await fetchDebts();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Gagal menghapus hutang.');
    }
  };

  const statusColor = (status: string) => {
    switch (status) {
      case 'lunas': return 'bg-green-100 text-green-800';
      case 'dibayar_sebagian': return 'bg-yellow-100 text-yellow-800';
      case 'belum_dibayar': return 'bg-red-100 text-red-800';
      default: return 'bg-slate-100 text-slate-800';
    }
  };

  return (
    <div>
      <div className="mb-6 flex items-end justify-between gap-4">
        <div>
          <p className="text-sm font-semibold uppercase tracking-wider text-emerald-600">Hutang ke Owner</p>
          <h2 className="mt-1 text-xl font-bold tracking-tight text-slate-900">Daftar Hutang</h2>
        </div>
        <button onClick={openCreate} className="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
          <Plus size={17} /> Tambah Hutang
        </button>
      </div>

      {/* Summary Cards */}
      {summary && (
        <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p className="text-xs font-bold uppercase text-slate-400">Total Hutang</p>
            <p className="mt-2 text-xl font-bold text-slate-800">{formatCurrency(summary.total_all)}</p>
          </div>
          <div className="rounded-xl border border-red-200 bg-white p-5 shadow-sm">
            <p className="text-xs font-bold uppercase text-red-400">Belum Dibayar</p>
            <p className="mt-2 text-xl font-bold text-red-600">{formatCurrency(summary.total_belum_dibayar)}</p>
          </div>
          {Object.entries(summary.by_person).map(([person, data]) => (
            <div key={person} className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
              <p className="text-xs font-bold uppercase text-slate-400">Hutang {person}</p>
              <p className="mt-2 text-xl font-bold text-slate-800">{formatCurrency(data.total)}</p>
              <p className="mt-1 text-xs text-slate-500">{data.count} item &middot; {formatCurrency(data.belum_dibayar)} belum lunas</p>
            </div>
          ))}
        </div>
      )}

      {/* Filters */}
      <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div className="grid grid-cols-1 gap-3 md:grid-cols-4">
          <div className="relative md:col-span-1">
            <Search size={18} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              type="search"
              placeholder="Cari..."
              value={search}
              onChange={(e) => { setSearch(e.target.value); setPage(1); }}
              className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
            />
          </div>
          <select value={typeFilter} onChange={(e) => { setTypeFilter(e.target.value); setPage(1); }} className="rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-emerald-500">
            <option value="">Semua Jenis</option>
            <option value="talangan">Talangan</option>
            <option value="pinjaman">Pinjaman</option>
            <option value="reimburse">Reimburse</option>
          </select>
          <select value={personFilter} onChange={(e) => { setPersonFilter(e.target.value); setPage(1); }} className="rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-emerald-500">
            <option value="">Semua Orang</option>
            <option value="CECIL">CECIL</option>
            <option value="TIAN">TIAN</option>
          </select>
          <select value={statusFilter} onChange={(e) => { setStatusFilter(e.target.value); setPage(1); }} className="rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-emerald-500">
            <option value="">Semua Status</option>
            <option value="belum_dibayar">Belum Dibayar</option>
            <option value="dibayar_sebagian">Dibayar Sebagian</option>
            <option value="lunas">Lunas</option>
          </select>
        </div>
      </div>

      {error && <div className="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}

      {/* Table */}
      <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        {loading ? (
          <div className="p-14 text-center text-sm text-slate-500">Memuat data hutang...</div>
        ) : debts.length === 0 ? (
          <div className="flex flex-col items-center px-6 py-16 text-center">
            <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-500"><Wallet size={30} /></div>
            <h2 className="mt-5 text-base font-bold text-slate-900">Belum ada hutang</h2>
            <p className="mt-2 max-w-sm text-sm leading-6 text-slate-500">Klik &quot;Tambah Hutang&quot; untuk mencatat talangan atau pinjaman ke owner.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-slate-50">
                <tr>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kepada</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                  <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nominal</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {debts.map((debt) => (
                  <tr key={debt.id} className="hover:bg-slate-50">
                    <td className="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                      {debt.date ? new Date(debt.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'}
                    </td>
                    <td className="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{DEBT_TYPE[debt.type]}</td>
                    <td className="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">{debt.person}</td>
                    <td className="px-4 py-3 text-sm text-gray-600 max-w-[200px] truncate">{debt.description}</td>
                    <td className="whitespace-nowrap px-4 py-3 text-sm font-semibold text-right text-red-600">{formatCurrency(debt.amount)}</td>
                    <td className="whitespace-nowrap px-4 py-3">
                      <span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${statusColor(debt.status)}`}>
                        {DEBT_STATUS[debt.status]}
                      </span>
                    </td>
                    <td className="whitespace-nowrap px-4 py-3 text-sm">
                      <div className="flex gap-1">
                        <button onClick={() => drawer.open('show', debt.id)} className="rounded-lg p-2 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600"><Eye size={15} /></button>
                        <button onClick={() => drawer.open('edit', debt.id)} className="rounded-lg p-2 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600"><Pencil size={15} /></button>
                        <button onClick={() => void remove(debt)} className="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-red-600"><Trash2 size={15} /></button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {meta.last_page > 1 && (
          <div className="flex items-center justify-between border-t border-slate-200 bg-white px-4 py-3 sm:px-6">
            <p className="text-sm text-slate-500">Halaman {meta.current_page} dari {meta.last_page}</p>
            <div className="flex gap-2">
              <button disabled={page <= 1} onClick={() => setPage(page - 1)} className="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50">Sebelumnya</button>
              <button disabled={page >= meta.last_page} onClick={() => setPage(page + 1)} className="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50">Selanjutnya</button>
            </div>
          </div>
        )}
      </div>

      {/* Drawer */}
      <Drawer
        open={drawer.mode !== null}
        onClose={drawer.close}
        eyebrow="Hutang ke Owner"
        title={drawer.mode === 'create' ? 'Tambah Hutang' : drawer.mode === 'edit' ? 'Edit Hutang' : 'Detail Hutang'}
        footer={drawer.mode === 'create' || drawer.mode === 'edit' ? (
          <DrawerButtons formId="debt-form" saving={saving} onCancel={drawer.close} submitLabel="Simpan" />
        ) : undefined}
      >
        {drawer.mode === 'show' && selected ? (
          <div className="space-y-5">
            <div className="rounded-xl bg-slate-900 p-5 text-white">
              <p className="text-xs uppercase tracking-wider text-emerald-300">{DEBT_TYPE[selected.type]}</p>
              <h3 className="mt-2 text-2xl font-bold">{formatCurrency(selected.amount)}</h3>
              <p className="mt-1 text-sm text-slate-300">{selected.description}</p>
            </div>
            <div className="grid grid-cols-2 gap-4 text-sm">
              <div><p className="text-xs font-bold uppercase text-slate-400">Tanggal</p><p className="mt-1 font-semibold text-slate-800">{selected.date ? new Date(selected.date).toLocaleDateString('id-ID') : '-'}</p></div>
              <div><p className="text-xs font-bold uppercase text-slate-400">Kepada</p><p className="mt-1 font-semibold text-slate-800">{selected.person}</p></div>
              <div><p className="text-xs font-bold uppercase text-slate-400">Status</p><p className="mt-1 font-semibold text-slate-800">{DEBT_STATUS[selected.status]}</p></div>
              <div><p className="text-xs font-bold uppercase text-slate-400">Tanggal Bayar</p><p className="mt-1 font-semibold text-slate-800">{selected.paid_date ? new Date(selected.paid_date).toLocaleDateString('id-ID') : '-'}</p></div>
            </div>
            <button onClick={() => drawer.open('edit', selected.id)} className="w-full rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Edit Hutang</button>
          </div>
        ) : (
          <form id="debt-form" onSubmit={submit} className="space-y-5">
            <FormField label="Tanggal">
              <input type="date" value={form.date} onChange={(e) => setForm({ ...form, date: e.target.value })} className={fieldClass} />
            </FormField>
            <FormField label="Jenis">
              <select required value={form.type} onChange={(e) => setForm({ ...form, type: e.target.value })} className={fieldClass}>
                <option value="talangan">Talangan</option>
                <option value="pinjaman">Pinjaman</option>
                <option value="reimburse">Reimburse</option>
              </select>
            </FormField>
            <FormField label="Kepada">
              <select required value={form.person} onChange={(e) => setForm({ ...form, person: e.target.value })} className={fieldClass}>
                <option value="TIAN">TIAN</option>
                <option value="CECIL">CECIL</option>
              </select>
            </FormField>
            <FormField label="Keterangan">
              <input type="text" required value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} className={fieldClass} placeholder="Deskripsi hutang..." />
            </FormField>
            <FormField label="Nominal">
              <input type="number" required min="0" value={form.amount} onChange={(e) => setForm({ ...form, amount: e.target.value })} className={fieldClass} placeholder="0" />
            </FormField>
            <div className="grid grid-cols-2 gap-4">
              <FormField label="Status">
                <select value={form.status} onChange={(e) => setForm({ ...form, status: e.target.value })} className={fieldClass}>
                  <option value="belum_dibayar">Belum Dibayar</option>
                  <option value="dibayar_sebagian">Dibayar Sebagian</option>
                  <option value="lunas">Lunas</option>
                </select>
              </FormField>
              <FormField label="Tanggal Dibayar">
                <input type="date" value={form.paid_date} onChange={(e) => setForm({ ...form, paid_date: e.target.value })} className={fieldClass} />
              </FormField>
            </div>
          </form>
        )}
      </Drawer>
    </div>
  );
}
