'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { Activity, BarChart3, Briefcase, Layers } from 'lucide-react';

const tabs = [
  { name: 'Dashboard', href: '/crm', icon: BarChart3 },
  { name: 'Leads', href: '/crm/leads', icon: Layers },
  { name: 'Pipeline', href: '/crm/opportunities', icon: Briefcase },
  { name: 'Activities', href: '/crm/activities', icon: Activity },
];

export default function CrmLayout({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-1 border-b border-slate-200 pb-0">
        {tabs.map((tab) => {
          const active = pathname === tab.href || (tab.href !== '/crm' && pathname.startsWith(tab.href));
          return (
            <Link
              key={tab.href}
              href={tab.href}
              className={`inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold border-b-2 -mb-[1px] transition-colors ${
                active
                  ? 'border-blue-600 text-blue-700'
                  : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
              }`}
            >
              <tab.icon className="h-4 w-4" />
              {tab.name}
            </Link>
          );
        })}
      </div>
      {children}
    </div>
  );
}
