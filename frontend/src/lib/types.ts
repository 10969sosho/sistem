export interface Customer {
  id: number;
  name: string;
  pic_name: string | null;
  whatsapp: string | null;
  email: string | null;
  address: string | null;
  status: 'active' | 'non_active';
  notes: string | null;
  projects_count?: number;
  created_at: string;
  updated_at: string;
  projects?: Project[];
}

export interface Project {
  id: number;
  customer_id: number;
  name: string;
  type: string | null;
  description: string | null;
  status: 'pending' | 'progress' | 'testing' | 'revisi' | 'maintenance' | 'selesai';
  deadline: string | null;
  pic: string | null;
  start_date: string | null;
  finish_date: string | null;
  internal_notes: string | null;
  tasks_count?: number;
  open_tasks_count?: number;
  created_at: string;
  updated_at: string;
  customer?: Customer;
  tasks?: Task[];
  hosting?: Hosting;
  finance?: Finance;
}

export interface Task {
  id: number;
  customer_id: number | null;
  project_id: number | null;
  title: string;
  type: 'development' | 'revisi' | 'bug_fix' | 'maintenance';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  status: 'todo' | 'progress' | 'waiting' | 'done';
  pic: string | null;
  deadline: string | null;
  estimate: string | null;
  notes: string | null;
  is_overdue: boolean;
  created_at: string;
  updated_at: string;
  customer?: Customer;
  project?: Project;
}

export interface Hosting {
  id: number;
  project_id: number;
  provider: string | null;
  package: string | null;
  expired_date: string | null;
  domain: string | null;
  registrar: string | null;
  domain_expired_date: string | null;
  ssl_status: 'active' | 'non_active' | null;
  ssl_expired_date: string | null;
  server_ip: string | null;
  panel: string | null;
  username: string | null;
  notes: string | null;
  hosting_status: 'active' | 'expiring' | 'expired' | null;
  domain_status: 'active' | 'expiring' | 'expired' | null;
  created_at: string;
  updated_at: string;
  project?: Project;
}

export interface Finance {
  id: number;
  project_id: number;
  total: number;
  dp: number;
  termin1: number;
  termin2: number;
  termin3: number;
  pelunasan: number;
  total_paid: number;
  remaining: number;
  payment_status: 'belum_bayar' | 'sebagian' | 'lunas';
  created_at: string;
  updated_at: string;
  project?: Project;
}

export interface DashboardSummary {
  date: string;
  today_tasks: { count: number; items: Task[] };
  overdue_tasks: { count: number; items: Task[] };
  week_tasks: { count: number; items: Task[] };
  active_projects: { count: number; items: Project[] };
  revisi_projects: { count: number; items: Project[] };
  hosting_expiring: { count: number; items: Hosting[] };
  domain_expiring: { count: number; items: Hosting[] };
  unpaid_invoices: { count: number; items: Finance[] };
}

export interface SearchResult {
  customers: Customer[];
  projects: Project[];
  tasks: Task[];
  domains: Hosting[];
}

export const CUSTOMER_STATUS = {
  active: 'Active',
  non_active: 'Non Active',
} as const;

export const PROJECT_STATUS = {
  pending: 'Pending',
  progress: 'Progress',
  testing: 'Testing',
  revisi: 'Revisi',
  maintenance: 'Maintenance',
  selesai: 'Selesai',
} as const;

export const TASK_STATUS = {
  todo: 'Todo',
  progress: 'Progress',
  waiting: 'Waiting',
  done: 'Done',
} as const;

export const TASK_PRIORITY = {
  low: 'Low',
  medium: 'Medium',
  high: 'High',
  urgent: 'Urgent',
} as const;

export const TASK_TYPE = {
  development: 'Development',
  revisi: 'Revisi',
  bug_fix: 'Bug Fix',
  maintenance: 'Maintenance',
} as const;
