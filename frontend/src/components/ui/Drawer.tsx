'use client';

import { useEffect, useState } from 'react';
import { X } from 'lucide-react';

interface DrawerProps {
  open: boolean;
  title: string;
  eyebrow?: string;
  onClose: () => void;
  children: React.ReactNode;
  footer?: React.ReactNode;
  size?: 'md' | 'lg';
}

export function Drawer({ open, title, eyebrow, onClose, children, footer, size = 'md' }: DrawerProps) {
  const [mounted, setMounted] = useState(open);

  useEffect(() => {
    if (open) {
      setMounted(true);
      return;
    }

    const timer = window.setTimeout(() => setMounted(false), 340);
    return () => window.clearTimeout(timer);
  }, [open]);

  useEffect(() => {
    if (!open) return;

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    const handleEscape = (event: KeyboardEvent) => {
      if (event.key === 'Escape') onClose();
    };
    window.addEventListener('keydown', handleEscape);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener('keydown', handleEscape);
    };
  }, [open, onClose]);

  if (!mounted) return null;

  return (
    <div className="fixed inset-0 z-[100]" aria-hidden={!open}>
      <button
        type="button"
        aria-label="Tutup drawer"
        onClick={onClose}
        className={`absolute inset-0 z-0 cursor-default border-0 bg-slate-950/40 backdrop-blur-[2px] transition-opacity duration-300 ease-out ${open ? 'opacity-100' : 'opacity-0'}`}
      />
      <aside
        role="dialog"
        aria-modal="true"
        aria-label={title}
        onClick={(event) => event.stopPropagation()}
        className={`absolute right-0 top-0 z-10 flex h-full w-full flex-col bg-white shadow-2xl will-change-transform ${size === 'lg' ? 'sm:w-[min(720px,100vw)]' : 'sm:w-[min(520px,100vw)]'} ${open ? 'animate-drawer-in' : 'animate-drawer-out'}`}
      >
        <header className="flex items-start justify-between border-b border-slate-200 px-6 py-5">
          <div>
            {eyebrow && <p className="text-[11px] font-bold uppercase tracking-[0.18em] text-blue-600">{eyebrow}</p>}
            <h2 className="mt-1 text-xl font-bold tracking-tight text-slate-900">{title}</h2>
          </div>
          <button type="button" onClick={onClose} className="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Tutup">
            <X size={20} />
          </button>
        </header>
        <div className="min-h-0 flex-1 overflow-y-auto px-6 py-6">{children}</div>
        {footer && <footer className="border-t border-slate-200 bg-slate-50 px-6 py-4">{footer}</footer>}
      </aside>
    </div>
  );
}

export function FormField({ label, children, hint }: { label: string; children: React.ReactNode; hint?: string }) {
  return (
    <label className="block">
      <span className="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">{label}</span>
      {children}
      {hint && <span className="mt-1 block text-xs text-slate-400">{hint}</span>}
    </label>
  );
}

export const fieldClass = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100';
