'use client';

import { useEffect, useState } from 'react';
import { api } from '@/lib/api';
import type { DashboardSummary } from '@/lib/types';
import Link from 'next/link';

export default function DashboardPage() {
  const [summary, setSummary] = useState<DashboardSummary | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get<{ data: DashboardSummary }>('/dashboard')
      .then((res) => setSummary(res.data))
      .catch(console.error)
      .finally(() => setLoading(false));
  }, []);

  if (loading) {
    return <div className="flex items-center justify-center h-64">Loading...</div>;
  }

  if (!summary) {
    return <div className="text-red-500">Failed to load dashboard</div>;
  }

  const widgets = [
    { title: 'Task Hari Ini', count: summary.today_tasks.count, items: summary.today_tasks.items, color: 'blue', href: '/tasks?filter=today' },
    { title: 'Task Terlambat', count: summary.overdue_tasks.count, items: summary.overdue_tasks.items, color: 'red', href: '/tasks?filter=overdue' },
    { title: 'Task Minggu Ini', count: summary.week_tasks.count, items: summary.week_tasks.items, color: 'green', href: '/tasks?filter=week' },
    { title: 'Project Progress', count: summary.active_projects.count, items: summary.active_projects.items, color: 'purple', href: '/projects?status=progress' },
    { title: 'Project Revisi', count: summary.revisi_projects.count, items: summary.revisi_projects.items, color: 'yellow', href: '/projects?status=revisi' },
    { title: 'Hosting Expired', count: summary.hosting_expiring.count, items: summary.hosting_expiring.items, color: 'orange', href: '/hosting?filter=expiring' },
    { title: 'Domain Expired', count: summary.domain_expiring.count, items: summary.domain_expiring.items, color: 'pink', href: '/hosting?filter=domain_expiring' },
    { title: 'Tagihan Belum Lunas', count: summary.unpaid_invoices.count, items: summary.unpaid_invoices.items, color: 'indigo', href: '/finance?filter=unpaid' },
  ];

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {widgets.map((widget) => (
          <div key={widget.title} className="bg-white rounded-lg shadow p-6">
            <div className="flex items-center justify-between">
              <h3 className="text-sm font-medium text-gray-500">{widget.title}</h3>
              <span className={`text-2xl font-bold text-${widget.color}-600`}>{widget.count}</span>
            </div>
            <div className="mt-4">
              {widget.items.length > 0 ? (
                <ul className="space-y-2">
                  {widget.items.slice(0, 3).map((item: any) => (
                    <li key={item.id} className="text-sm text-gray-700 truncate">
                      {item.title || item.name}
                    </li>
                  ))}
                </ul>
              ) : (
                <p className="text-sm text-gray-400">Tidak ada data</p>
              )}
            </div>
            <Link href={widget.href} className="mt-4 text-sm text-blue-600 hover:text-blue-800">
              Lihat semua →
            </Link>
          </div>
        ))}
      </div>
    </div>
  );
}
