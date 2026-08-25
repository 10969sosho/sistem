'use client';

import Link from 'next/link';
import { ArrowRight } from 'lucide-react';

const owners = [
  {
    key: 'cecil',
    name: 'CECIL',
    description: 'Daftar task khusus milik Cecil',
    accent: 'from-rose-500 to-orange-400',
  },
  {
    key: 'tian',
    name: 'TIAN',
    description: 'Daftar task khusus milik Tian',
    accent: 'from-blue-600 to-cyan-400',
  },
] as const;

export default function TasksPage() {
  return (
    <div className="space-y-6">
      <div>
        <p className="text-sm font-medium uppercase tracking-wider text-blue-600">Workflow Tim</p>
        <h1 className="mt-1 text-3xl font-bold tracking-tight text-slate-900">Task Board</h1>
        <p className="mt-2 text-sm text-slate-500">
          Pilih pemilik task untuk membuka daftar task-nya.
        </p>
      </div>

      <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
        {owners.map((owner) => (
          <Link
            key={owner.key}
            href={`/tasks/${owner.key}`}
            className={`group relative flex min-h-56 flex-col justify-between overflow-hidden rounded-2xl bg-gradient-to-br ${owner.accent} p-8 text-white shadow-lg transition hover:-translate-y-1 hover:shadow-xl`}
          >
            <div>
              <p className="text-xs font-semibold uppercase tracking-[0.3em] text-white/70">Owner</p>
              <h2 className="mt-2 text-4xl font-extrabold tracking-tight">{owner.name}</h2>
              <p className="mt-3 text-sm text-white/85">{owner.description}</p>
            </div>
            <span className="inline-flex items-center gap-2 text-sm font-semibold">
              Buka daftar task
              <ArrowRight size={17} className="transition group-hover:translate-x-1" />
            </span>
          </Link>
        ))}
      </div>

      <div className="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
        Butuh melihat semua task termasuk yang sudah selesai di-check?{' '}
        <Link href="/tasks/all" className="font-semibold underline">Buka halaman All Tasks</Link>.
      </div>
    </div>
  );
}
