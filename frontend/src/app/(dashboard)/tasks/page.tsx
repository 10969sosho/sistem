'use client';

import { FormEvent, useEffect, useState } from 'react';
import Link from 'next/link';
import { Eye, Pencil, Plus } from 'lucide-react';
import { api, type ApiResponse, type PaginatedResponse } from '@/lib/api';
import type { Customer, Project, Task } from '@/lib/types';
import { TASK_PRIORITY, TASK_STATUS, TASK_TYPE } from '@/lib/types';
import { Drawer, FormField, fieldClass } from '@/components/ui/Drawer';
import { DrawerButtons } from '@/components/ui/DrawerButtons';
import { useDrawer } from '@/lib/use-drawer';

type ColumnKey = 'todo' | 'progress' | 'done';

const columns: Array<{
  key: ColumnKey;
  title: string;
  description: string;
  accent: string;
  emptyText: string;
}> = [
  {
    key: 'todo',
    title: 'Belum Dikerjakan',
    description: 'Task baru yang perlu mulai dikerjakan',
    accent: 'border-slate-300 bg-slate-50',
    emptyText: 'Tarik task ke sini untuk dikerjakan',
  },
  {
    key: 'progress',
    title: 'Sudah Dikerjakan',
    description: 'Menunggu tim lain melakukan check',
    accent: 'border-amber-300 bg-amber-50/60',
    emptyText: 'Tarik task ke sini setelah selesai dikerjakan',
  },
  {
    key: 'done',
    title: 'Sudah Check',
    description: 'Task selesai dan sudah dikonfirmasi',
    accent: 'border-emerald-300 bg-emerald-50/60',
    emptyText: 'Task yang sudah di-check muncul di sini',
  },
];

export default function TasksPage() {
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);
  const [savingTaskId, setSavingTaskId] = useState<number | null>(null);
  const [draggedTaskId, setDraggedTaskId] = useState<number | null>(null);
  const [search, setSearch] = useState('');
  const [priorityFilter, setPriorityFilter] = useState('');
  const [typeFilter, setTypeFilter] = useState('');
  const [error, setError] = useState('');
  const [task, setTask] = useState<Task | null>(null);
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [projects, setProjects] = useState<Project[]>([]);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({ customer_id: '', project_id: '', title: '', type: 'development', priority: 'medium', status: 'todo', pic: '', deadline: '', estimate: '', notes: '' });
  const drawer = useDrawer();

  const fetchTasks = async () => {
    setLoading(true);
    setError('');

    try {
      const params: Record<string, string | number> = { per_page: 100 };
      if (search) params.search = search;
      if (priorityFilter) params.priority = priorityFilter;
      if (typeFilter) params.type = typeFilter;

      const response = await api.get<PaginatedResponse<Task>>('/tasks', params);
      setTasks(response.data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Task gagal dimuat.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchTasks();
  }, [search, priorityFilter, typeFilter]);

  useEffect(() => {
    void Promise.all([
      api.get<PaginatedResponse<Customer>>('/customers', { per_page: 100 }),
      api.get<PaginatedResponse<Project>>('/projects', { per_page: 100 }),
    ]).then(([customerResponse, projectResponse]) => {
      setCustomers(customerResponse.data);
      setProjects(projectResponse.data);
    });
  }, []);

  useEffect(() => {
    if (!drawer.id) { setTask(null); return; }
    void api.get<ApiResponse<Task>>(`/tasks/${drawer.id}`).then((response) => {
      const value = response.data;
      setTask(value);
      setForm({ customer_id: value.customer_id ? String(value.customer_id) : '', project_id: value.project_id ? String(value.project_id) : '', title: value.title, type: value.type, priority: value.priority, status: value.status, pic: value.pic || '', deadline: value.deadline || '', estimate: value.estimate || '', notes: value.notes || '' });
    }).catch((err) => setError(err instanceof Error ? err.message : 'Detail task gagal dimuat.'));
  }, [drawer.id]);

  const openCreate = () => {
    setTask(null);
    setForm({ customer_id: '', project_id: '', title: '', type: 'development', priority: 'medium', status: 'todo', pic: '', deadline: '', estimate: '', notes: '' });
    drawer.open('create');
  };

  const submit = async (event: FormEvent) => {
    event.preventDefault(); setSaving(true); setError('');
    const payload = { ...form, customer_id: form.customer_id ? Number(form.customer_id) : null, project_id: form.project_id ? Number(form.project_id) : null };
    try {
      if (drawer.mode === 'edit' && drawer.id) await api.put(`/tasks/${drawer.id}`, payload);
      else await api.post('/tasks', payload);
      await fetchTasks(); drawer.close();
    } catch (err) { setError(err instanceof Error ? err.message : 'Task gagal disimpan.'); }
    finally { setSaving(false); }
  };

  const moveTask = async (taskId: number, nextStatus: ColumnKey) => {
    const task = tasks.find((item) => item.id === taskId);
    if (!task || task.status === nextStatus) return;

    const previousStatus = task.status;
    setSavingTaskId(taskId);
    setError('');
    setTasks((current) => current.map((item) => (
      item.id === taskId ? { ...item, status: nextStatus } : item
    )));

    try {
      await api.patch(`/tasks/${taskId}/status`, { status: nextStatus });
    } catch (err) {
      setTasks((current) => current.map((item) => (
        item.id === taskId ? { ...item, status: previousStatus } : item
      )));
      setError(err instanceof Error ? err.message : 'Status task gagal disimpan.');
    } finally {
      setSavingTaskId(null);
    }
  };

  const handleDrop = (column: ColumnKey) => {
    if (draggedTaskId !== null) {
      void moveTask(draggedTaskId, column);
    }
    setDraggedTaskId(null);
  };

  const priorityColor = (priority: string) => {
    switch (priority) {
      case 'urgent': return 'bg-red-100 text-red-800';
      case 'high': return 'bg-orange-100 text-orange-800';
      case 'medium': return 'bg-yellow-100 text-yellow-800';
      default: return 'bg-slate-100 text-slate-700';
    }
  };

  const tasksForColumn = (column: ColumnKey) => tasks.filter((task) => {
    if (column === 'todo') return task.status === 'todo' || task.status === 'waiting';
    return task.status === column;
  });

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p className="text-sm font-medium uppercase tracking-wider text-blue-600">Workflow Tim</p>
          <h1 className="mt-1 text-3xl font-bold tracking-tight text-slate-900">Task Board</h1>
          <p className="mt-2 text-sm text-slate-500">
            Seret task dari kiri ke tengah setelah selesai dikerjakan, lalu ke kanan setelah di-check.
          </p>
        </div>
        <button onClick={openCreate} className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"><Plus size={17} /> Tambah Task</button>
      </div>

      <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div className="grid grid-cols-1 gap-3 md:grid-cols-3">
          <input
            type="text"
            placeholder="Cari judul, project, customer..."
            value={search}
            onChange={(event) => setSearch(event.target.value)}
            className="rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
          />
          <select
            value={priorityFilter}
            onChange={(event) => setPriorityFilter(event.target.value)}
            className="rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
          >
            <option value="">Semua Prioritas</option>
            {Object.entries(TASK_PRIORITY).map(([value, label]) => (
              <option key={value} value={value}>{label}</option>
            ))}
          </select>
          <select
            value={typeFilter}
            onChange={(event) => setTypeFilter(event.target.value)}
            className="rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
          >
            <option value="">Semua Jenis Task</option>
            {Object.entries(TASK_TYPE).map(([value, label]) => (
              <option key={value} value={value}>{label}</option>
            ))}
          </select>
        </div>
      </div>

      {error && (
        <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>
      )}

      {loading ? (
        <div className="rounded-xl border border-slate-200 bg-white p-12 text-center text-sm text-slate-500">Memuat task...</div>
      ) : (
        <div className="grid grid-cols-1 gap-5 xl:grid-cols-3">
          {columns.map((column) => {
            const columnTasks = tasksForColumn(column.key);

            return (
              <section
                key={column.key}
                onDragOver={(event) => event.preventDefault()}
                onDrop={() => handleDrop(column.key)}
                className={`min-h-[28rem] rounded-xl border-2 p-4 transition ${column.accent} ${draggedTaskId !== null ? 'ring-2 ring-blue-200 ring-offset-2' : ''}`}
              >
                <div className="mb-4 flex items-start justify-between gap-3">
                  <div>
                    <h2 className="font-bold text-slate-900">{column.title}</h2>
                    <p className="mt-1 text-xs leading-5 text-slate-500">{column.description}</p>
                  </div>
                  <span className="rounded-full bg-white px-2.5 py-1 text-sm font-bold text-slate-700 shadow-sm">
                    {columnTasks.length}
                  </span>
                </div>

                <div className="space-y-3">
                  {columnTasks.map((task) => (
                    <article
                      key={task.id}
                      draggable
                      onDragStart={() => setDraggedTaskId(task.id)}
                      onDragEnd={() => setDraggedTaskId(null)}
                      className={`cursor-grab rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md active:cursor-grabbing ${draggedTaskId === task.id ? 'opacity-50' : ''}`}
                    >
                      <div className="flex items-start justify-between gap-3">
                        <h3 className="text-sm font-semibold leading-5 text-slate-900">{task.title}</h3>
                        <span className={`shrink-0 rounded-full px-2 py-1 text-[10px] font-bold uppercase ${priorityColor(task.priority)}`}>
                          {TASK_PRIORITY[task.priority as keyof typeof TASK_PRIORITY]}
                        </span>
                      </div>

                      <div className="mt-3 space-y-1.5 text-xs text-slate-500">
                        <p>{task.project?.name || task.customer?.name || 'Task manual'}</p>
                        <p>{TASK_TYPE[task.type as keyof typeof TASK_TYPE]}{task.pic ? ` · ${task.pic}` : ''}</p>
                        {task.deadline && (
                          <p className={task.is_overdue ? 'font-semibold text-red-600' : ''}>
                            Deadline: {task.deadline}{task.is_overdue ? ' · Terlambat' : ''}
                          </p>
                        )}
                      </div>

                      <div className="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                        <div className="flex items-center gap-1"><button type="button" onClick={() => drawer.open('show', task.id)} className="rounded-lg p-1.5 text-slate-500 hover:bg-blue-50 hover:text-blue-600" title="Detail task"><Eye size={15} /></button><button type="button" onClick={() => drawer.open('edit', task.id)} className="rounded-lg p-1.5 text-slate-500 hover:bg-blue-50 hover:text-blue-600" title="Edit task"><Pencil size={15} /></button></div>
                        {savingTaskId === task.id && <span className="text-[11px] text-blue-600">Menyimpan...</span>}
                      </div>
                    </article>
                  ))}

                  {columnTasks.length === 0 && (
                    <div className="flex min-h-32 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white/50 p-5 text-center text-xs text-slate-400">
                      {column.emptyText}
                    </div>
                  )}
                </div>
              </section>
            );
          })}
        </div>
      )}

      <div className="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
        <strong>Alur kerja:</strong> Todo masuk kolom kiri, task yang sudah dikerjakan masuk kolom tengah untuk dicek tim lain, dan task yang sudah dikonfirmasi masuk kolom kanan.
      </div>

      <Drawer open={drawer.mode !== null} onClose={drawer.close} eyebrow="Task workflow" title={drawer.mode === 'create' ? 'Tambah Task' : drawer.mode === 'edit' ? 'Edit Task' : 'Detail Task'} footer={drawer.mode === 'create' || drawer.mode === 'edit' ? <DrawerButtons formId="task-drawer-form" saving={saving} onCancel={drawer.close} submitLabel={drawer.mode === 'create' ? 'Tambah Task' : 'Simpan Perubahan'} /> : undefined}>
        {drawer.mode === 'show' && task ? <div className="space-y-6"><div className="rounded-xl bg-slate-900 p-5 text-white"><p className="text-xs uppercase tracking-wider text-blue-300">Task detail</p><h3 className="mt-2 text-xl font-bold">{task.title}</h3><p className="mt-2 text-sm text-slate-300">{task.project?.name || task.customer?.name || 'Task manual'}</p></div><div className="grid grid-cols-2 gap-4 text-sm"><div><p className="text-xs font-bold uppercase text-slate-400">Status</p><p className="mt-1 font-semibold text-slate-800">{TASK_STATUS[task.status as keyof typeof TASK_STATUS]}</p></div><div><p className="text-xs font-bold uppercase text-slate-400">Prioritas</p><p className="mt-1 text-slate-800">{TASK_PRIORITY[task.priority as keyof typeof TASK_PRIORITY]}</p></div><div><p className="text-xs font-bold uppercase text-slate-400">Jenis</p><p className="mt-1 text-slate-800">{TASK_TYPE[task.type as keyof typeof TASK_TYPE]}</p></div><div><p className="text-xs font-bold uppercase text-slate-400">Deadline</p><p className="mt-1 text-slate-800">{task.deadline || '-'}</p></div></div><div><p className="text-xs font-bold uppercase text-slate-400">Catatan</p><p className="mt-2 min-h-24 whitespace-pre-wrap rounded-lg bg-slate-50 p-4 text-sm leading-6 text-slate-600">{task.notes || 'Belum ada catatan.'}</p></div><button onClick={() => drawer.open('edit', task.id)} className="w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700">Edit task</button></div> : <form id="task-drawer-form" onSubmit={submit} className="space-y-5"><FormField label="Judul Task"><input required value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} className={fieldClass} /></FormField><div className="grid grid-cols-2 gap-4"><FormField label="Customer"><select value={form.customer_id} onChange={(e) => setForm({ ...form, customer_id: e.target.value })} className={fieldClass}><option value="">Tanpa customer</option>{customers.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}</select></FormField><FormField label="Project"><select value={form.project_id} onChange={(e) => setForm({ ...form, project_id: e.target.value })} className={fieldClass}><option value="">Task manual</option>{projects.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}</select></FormField></div><div className="grid grid-cols-2 gap-4"><FormField label="Jenis"><select value={form.type} onChange={(e) => setForm({ ...form, type: e.target.value })} className={fieldClass}>{Object.entries(TASK_TYPE).map(([value, label]) => <option key={value} value={value}>{label}</option>)}</select></FormField><FormField label="Prioritas"><select value={form.priority} onChange={(e) => setForm({ ...form, priority: e.target.value })} className={fieldClass}>{Object.entries(TASK_PRIORITY).map(([value, label]) => <option key={value} value={value}>{label}</option>)}</select></FormField></div><div className="grid grid-cols-2 gap-4"><FormField label="Status"><select value={form.status} onChange={(e) => setForm({ ...form, status: e.target.value })} className={fieldClass}>{Object.entries(TASK_STATUS).map(([value, label]) => <option key={value} value={value}>{label}</option>)}</select></FormField><FormField label="PIC"><input value={form.pic} onChange={(e) => setForm({ ...form, pic: e.target.value })} className={fieldClass} /></FormField></div><div className="grid grid-cols-2 gap-4"><FormField label="Deadline"><input type="date" value={form.deadline} onChange={(e) => setForm({ ...form, deadline: e.target.value })} className={fieldClass} /></FormField><FormField label="Estimasi"><input value={form.estimate} onChange={(e) => setForm({ ...form, estimate: e.target.value })} className={fieldClass} placeholder="Contoh: 2 hari" /></FormField></div><FormField label="Catatan"><textarea rows={5} value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} className={fieldClass} /></FormField></form>}
      </Drawer>
    </div>
  );
}
