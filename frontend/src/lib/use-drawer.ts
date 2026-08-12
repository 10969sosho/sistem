'use client';

import { useEffect, useState } from 'react';

export type DrawerMode = 'create' | 'show' | 'edit' | null;

export function useDrawer() {
  const [mode, setMode] = useState<DrawerMode>(null);
  const [id, setId] = useState<number | null>(null);

  useEffect(() => {
    const sync = () => {
      const query = new URLSearchParams(window.location.search);
      const nextMode = query.get('drawer') as DrawerMode;
      setMode(nextMode && ['create', 'show', 'edit'].includes(nextMode) ? nextMode : null);
      setId(query.get('id') ? Number(query.get('id')) : null);
    };

    sync();
    window.addEventListener('popstate', sync);
    const escape = (event: KeyboardEvent) => {
      if (event.key === 'Escape') close();
    };
    window.addEventListener('keydown', escape);
    return () => {
      window.removeEventListener('popstate', sync);
      window.removeEventListener('keydown', escape);
    };
  }, []);

  const open = (nextMode: Exclude<DrawerMode, null>, nextId?: number) => {
    const query = new URLSearchParams(window.location.search);
    query.set('drawer', nextMode);
    if (nextId) query.set('id', String(nextId));
    else query.delete('id');
    window.history.pushState({}, '', `${window.location.pathname}?${query.toString()}`);
    setMode(nextMode);
    setId(nextId ?? null);
  };

  const close = () => {
    const query = new URLSearchParams(window.location.search);
    query.delete('drawer');
    query.delete('id');
    const next = query.toString() ? `${window.location.pathname}?${query}` : window.location.pathname;
    window.history.pushState({}, '', next);
    setMode(null);
    setId(null);
  };

  return { mode, id, open, close };
}
