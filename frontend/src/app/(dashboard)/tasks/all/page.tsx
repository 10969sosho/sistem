'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { api, type PaginatedResponse } from '@/lib/api';
import type { Task } from '@/lib/types';
import { TASK_CABANG, TASK_PRIORITY, TASK_STATUS } from '@/lib/types';

export default function AllTasksPage() {
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const load = async () => {
      try {
        // include_finished=1 -> semua task termasuk yang sudah di-check/finish.
        const response = await api.get<PaginatedResponse<Task>>('/tasks', {
          per_page: 100,
          include_finished: 1,
        });
        setTasks(response.data);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Task gagal dimuat.');
      } finally {
        setLoading(false);
      }
    };

    void load();
  }, []);

  return (
    <div className="space-y-6">
      <div>
        <p className="text-sm font-medium uppercase tracking-wider text-blue-600">Workflow Tim</p>
        <h1 className="mt-1 text-3xl font-bold tracking-tight text-slate-900">All Tasks</h1>
        <p className="mt-2 text-sm text-slate-500">
          Semua task dari kedua owner, termasuk yang sudah selesai di-check (finish).{' '}
          <Link href="/tasks" className="font-semibold text-blue-600 underline">Kembali ke pilihan owner</Link>
        </p>
      </div>

      {error && (
        <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>
      )}

      {loading ? (
        <div className="rounded-xl border border-slate-200 bg-white p-12 text-center text-sm text-slate-500">Memuat task...</div>
      ) : (
        <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
          <table className="min-w-full divide-y divide-slate-200 text-sm">
            <thead className="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
              <tr>
                <th className="px-5 py-3">Judul</th>
                <th className="px-5 py-3">Owner</th>
                <th className="px-5 py-3">Status</th>
                <th className="px-5 py-3">Prioritas</th>
                <th className="px-5 py-3">Deadline</th>
                <th className="px-5 py-3">Finish</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {tasks.map((task) => (
                <tr key={task.id} className={task.is_finished ? 'bg-emerald-50/40' : ''}>
                  <td className="px-5 py-3 font-semibold text-slate-800">{task.title}</td>
                  <td className="px-5 py-3 text-slate-600">{task.cabang ? TASK_CABANG[task.cabang].toUpperCase() : '-'}</td>
                  <td className="px-5 py-3 text-slate-600">
                    {TASK_STATUS[task.status as keyof typeof TASK_STATUS]}
                    {task.is_finished && (
                      <span className="ml-2 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700">Finished</span>
                    )}
                  </td>
                  <td className="px-5 py-3 text-slate-600">{TASK_PRIORITY[task.priority as keyof typeof TASK_PRIORITY]}</td>
                  <td className={`px-5 py-3 ${task.is_overdue ? 'font-semibold text-red-600' : 'text-slate-600'}`}>{task.deadline || '-'}</td>
                  <td className="px-5 py-3 text-slate-600">{task.finished_at ? new Date(task.finished_at).toLocaleString('id-ID') : '-'}</td>
                </tr>
              ))}
              {tasks.length === 0 && (
                <tr><td colSpan={6} className="px-5 py-10 text-center text-slate-400">Belum ada task.</td></tr>
              )}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
