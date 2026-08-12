'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { useParams } from 'next/navigation';
import { api, type ApiResponse } from '@/lib/api';
import type { Task } from '@/lib/types';
import { TASK_PRIORITY, TASK_STATUS, TASK_TYPE } from '@/lib/types';

export function generateStaticParams() {
  return [];
}

export default function TaskDetailPage() {
  const params = useParams<{ id: string }>();
  const [task, setTask] = useState<Task | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    const loadTask = async () => {
      try {
        const response = await api.get<ApiResponse<Task>>(`/tasks/${params.id}`);
        setTask(response.data);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Detail task gagal dimuat.');
      } finally {
        setLoading(false);
      }
    };

    if (params.id) {
      void loadTask();
    }
  }, [params.id]);

  const changeStatus = async (status: string) => {
    if (!task || task.status === status) return;

    const previousStatus = task.status;
    setSaving(true);
    setError('');
    setTask({ ...task, status: status as Task['status'] });

    try {
      const response = await api.patch<ApiResponse<Task>>(`/tasks/${task.id}/status`, { status });
      setTask(response.data);
    } catch (err) {
      setTask({ ...task, status: previousStatus });
      setError(err instanceof Error ? err.message : 'Status task gagal disimpan.');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <div className="rounded-xl border border-slate-200 bg-white p-12 text-center text-sm text-slate-500">Memuat detail task...</div>;
  }

  if (error && !task) {
    return (
      <div className="space-y-4">
        <Link href="/tasks" className="text-sm font-semibold text-blue-600 hover:text-blue-800">← Kembali ke Task Board</Link>
        <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>
      </div>
    );
  }

  if (!task) return null;

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div>
          <Link href="/tasks" className="text-sm font-semibold text-blue-600 hover:text-blue-800">← Kembali ke Task Board</Link>
          <p className="mt-4 text-sm font-medium uppercase tracking-wider text-blue-600">Detail Task</p>
          <h1 className="mt-1 text-3xl font-bold tracking-tight text-slate-900">{task.title}</h1>
        </div>
        <Link
          href={`/tasks/${task.id}/edit`}
          className="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
        >
          Edit Task
        </Link>
      </div>

      {error && <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
          <div className="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-5">
            <span className="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
              {TASK_TYPE[task.type as keyof typeof TASK_TYPE]}
            </span>
            <span className="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-800">
              Prioritas {TASK_PRIORITY[task.priority as keyof typeof TASK_PRIORITY]}
            </span>
            {task.is_overdue && <span className="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">Terlambat</span>}
          </div>

          <dl className="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
              <dt className="text-xs font-semibold uppercase tracking-wide text-slate-400">Customer</dt>
              <dd className="mt-1 text-sm font-medium text-slate-900">{task.customer?.name || '-'}</dd>
            </div>
            <div>
              <dt className="text-xs font-semibold uppercase tracking-wide text-slate-400">Project</dt>
              <dd className="mt-1 text-sm font-medium text-slate-900">{task.project?.name || 'Task manual'}</dd>
            </div>
            <div>
              <dt className="text-xs font-semibold uppercase tracking-wide text-slate-400">PIC</dt>
              <dd className="mt-1 text-sm font-medium text-slate-900">{task.pic || '-'}</dd>
            </div>
            <div>
              <dt className="text-xs font-semibold uppercase tracking-wide text-slate-400">Estimasi</dt>
              <dd className="mt-1 text-sm font-medium text-slate-900">{task.estimate || '-'}</dd>
            </div>
            <div>
              <dt className="text-xs font-semibold uppercase tracking-wide text-slate-400">Deadline</dt>
              <dd className={`mt-1 text-sm font-medium ${task.is_overdue ? 'text-red-600' : 'text-slate-900'}`}>{task.deadline || '-'}</dd>
            </div>
            <div>
              <dt className="text-xs font-semibold uppercase tracking-wide text-slate-400">Dibuat</dt>
              <dd className="mt-1 text-sm font-medium text-slate-900">{new Date(task.created_at).toLocaleString('id-ID')}</dd>
            </div>
          </dl>

          <div className="mt-8">
            <h2 className="text-sm font-bold text-slate-900">Catatan</h2>
            <p className="mt-2 min-h-24 whitespace-pre-wrap rounded-lg bg-slate-50 p-4 text-sm leading-6 text-slate-600">
              {task.notes || 'Belum ada catatan untuk task ini.'}
            </p>
          </div>
        </section>

        <aside className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
          <h2 className="text-sm font-bold text-slate-900">Status Workflow</h2>
          <p className="mt-2 text-sm leading-6 text-slate-500">Ubah status dari halaman detail atau gunakan drag-and-drop di Task Board.</p>
          <label htmlFor="task-status" className="mt-6 block text-xs font-semibold uppercase tracking-wide text-slate-400">Status saat ini</label>
          <select
            id="task-status"
            value={task.status}
            disabled={saving}
            onChange={(event) => void changeStatus(event.target.value)}
            className="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm font-medium text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:opacity-60"
          >
            {Object.entries(TASK_STATUS).map(([value, label]) => (
              <option key={value} value={value}>{label}</option>
            ))}
          </select>
          {saving && <p className="mt-2 text-xs text-blue-600">Menyimpan perubahan...</p>}

          <div className="mt-6 space-y-3 border-t border-slate-100 pt-5 text-xs text-slate-500">
            <p><strong className="text-slate-700">Todo:</strong> belum dikerjakan.</p>
            <p><strong className="text-slate-700">Progress:</strong> sudah dikerjakan, menunggu check.</p>
            <p><strong className="text-slate-700">Done:</strong> sudah di-check dan selesai.</p>
          </div>
        </aside>
      </div>
    </div>
  );
}
