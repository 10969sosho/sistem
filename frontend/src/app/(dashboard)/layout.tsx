'use client';

import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { useAuthStore } from '@/lib/auth-store';
import { useEffect } from 'react';
import { BarChart3, CheckSquare, ChevronRight, CircleDollarSign, Handshake, LayoutDashboard, ListChecks, LogOut, Server, Users, BriefcaseBusiness, Wallet } from 'lucide-react';

const navigation = [
  { name: 'Dashboard', href: '/', icon: LayoutDashboard },
  { name: 'Customers', href: '/customers', icon: Users },
  { name: 'Projects', href: '/projects', icon: BriefcaseBusiness },
  { name: 'Tasks', href: '/tasks', icon: CheckSquare },
  { name: 'All Tasks', href: '/tasks/all', icon: ListChecks },
  { name: 'Hosting', href: '/hosting', icon: Server },
  { name: 'Finance', href: '/finance', icon: CircleDollarSign },
  { name: 'Keuangan', href: '/keuangan', icon: Wallet },
  { name: 'CRM', href: '/crm', icon: Handshake },
];

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const router = useRouter();
  const { user, logout, isLoading, init } = useAuthStore();

  useEffect(() => {
    init();
  }, [init]);

  useEffect(() => {
    if (!isLoading && !user) {
      router.push('/login');
    }
  }, [isLoading, user, router]);

  const handleLogout = async () => {
    await logout();
    router.push('/login');
  };

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  if (!user) {
    return null;
  }

  return (
      <div className="min-h-screen bg-[#f5f7fb]">
      <div className="flex h-screen">
        {/* Sidebar */}
        <div className="hidden md:flex md:flex-shrink-0">
          <div className="flex flex-col w-64">
            <div className="flex flex-col flex-grow overflow-y-auto bg-[#111827] py-6">
              <div className="flex items-center flex-shrink-0 px-6">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500 text-white shadow-lg shadow-blue-950/30">
                  <BarChart3 size={21} strokeWidth={2.5} />
                </div>
                <div className="ml-3">
                  <h1 className="text-base font-bold tracking-tight text-white">Task Manager</h1>
                  <p className="text-[11px] text-slate-400">Software house workspace</p>
                </div>
              </div>
              <div className="mt-5 flex-1 flex flex-col">
                <nav className="flex-1 space-y-1 px-3 pt-8">
                  <p className="px-3 pb-3 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Workspace</p>
                  {navigation.map((item) => {
                    const isActive = pathname === item.href || (item.href !== '/' && pathname.startsWith(item.href));
                    const Icon = item.icon;
                    return (
                      <Link
                        key={item.name}
                        href={item.href}
                        className={`group flex items-center rounded-xl px-3 py-3 text-sm font-medium transition ${
                          isActive
                            ? 'bg-blue-500 text-white shadow-lg shadow-blue-950/20'
                            : 'text-slate-400 hover:bg-slate-800 hover:text-white'
                        }`}
                      >
                        <Icon size={18} className="mr-3" />
                        {item.name}
                        {isActive && <ChevronRight size={16} className="ml-auto" />}
                      </Link>
                    );
                  })}
                </nav>
              </div>
              <div className="flex flex-shrink-0 border-t border-slate-700/70 p-5">
                <div className="flex items-center w-full">
                  <div className="flex-shrink-0">
                    <div className="flex h-9 w-9 items-center justify-center rounded-full bg-blue-500 text-sm font-bold text-white">
                      {user.name.charAt(0).toUpperCase()}
                    </div>
                  </div>
                  <div className="ml-3">
                    <p className="text-sm font-semibold text-white">{user.name}</p>
                    <button
                      onClick={handleLogout}
                      className="mt-1 flex items-center text-xs text-slate-400 transition hover:text-white"
                    >
                      <LogOut size={12} className="mr-1" />
                      Logout
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Main content */}
        <div className="flex flex-col flex-1 overflow-hidden">
          <main className="flex-1 relative overflow-y-auto focus:outline-none">
            <div className="py-6">
              <div className="mx-auto max-w-7xl px-4 sm:px-6 md:px-8">
                {children}
              </div>
            </div>
          </main>
        </div>
      </div>
    </div>
  );
}
