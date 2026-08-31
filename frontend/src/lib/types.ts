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
  cabang: 'tian' | 'cecil' | null;
  finished_at: string | null;
  is_finished: boolean;
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

export interface Transaction {
  id: number;
  date: string;
  type: 'pemasukan' | 'pengeluaran';
  category: string;
  description: string | null;
  vendor: string | null;
  amount: number;
  status: 'paid' | 'pending' | 'cancelled';
  payment_method: string | null;
  created_at: string;
  updated_at: string;
}

export interface Debt {
  id: number;
  date: string | null;
  type: 'talangan' | 'pinjaman' | 'reimburse';
  person: 'CECIL' | 'TIAN';
  description: string;
  amount: number;
  status: 'belum_dibayar' | 'dibayar_sebagian' | 'lunas';
  paid_date: string | null;
  created_at: string;
  updated_at: string;
}

export interface TransactionSummary {
  year: number;
  month: number | null;
  total_pemasukan: number;
  total_pengeluaran: number;
  laba_rugi: number;
  pemasukan_by_category: Record<string, number>;
  pengeluaran_by_category: Record<string, number>;
  kas_masuk: number;
  kas_keluar: number;
  saldo_kas: number;
}

export interface DebtSummary {
  total_all: number;
  total_belum_dibayar: number;
  total_dibayar_sebagian: number;
  total_lunas: number;
  by_person: Record<string, { total: number; belum_dibayar: number; lunas: number; count: number }>;
}

export const TRANSACTION_TYPE = {
  pemasukan: 'Pemasukan',
  pengeluaran: 'Pengeluaran',
} as const;

export const TRANSACTION_STATUS = {
  paid: 'Paid',
  pending: 'Pending',
  cancelled: 'Cancelled',
} as const;

export const DEBT_TYPE = {
  talangan: 'Talangan',
  pinjaman: 'Pinjaman',
  reimburse: 'Reimburse',
} as const;

export const DEBT_STATUS = {
  belum_dibayar: 'Belum Dibayar',
  dibayar_sebagian: 'Dibayar Sebagian',
  lunas: 'Lunas',
} as const;

export const KATEGORI_PEMASUKAN = [
  'Pendapatan Penjualan',
  'Pendapatan Project',
  'Pendapatan Maintenance',
  'Pendapatan Lainnya',
] as const;

export const KATEGORI_PENGELUARAN = [
  'Beban Gaji',
  'Beban Marketing',
  'Beban AI',
  'Beban Software',
  'Beban Transportasi',
  'Beban Operasional Kantor',
  'Beban Pajak',
  'Beban Peralatan',
  'Beban Maintanance',
  'Beban Lainnya',
] as const;

export const METODE_PEMBAYARAN = [
  'Transfer',
  'Cash',
  'QRIS',
  'Debit',
  'Credit Card',
] as const;

export const PERSON_OPTIONS = ['CECIL', 'TIAN'] as const;

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

export interface CrmLead {
  id: number;
  name: string;
  company: string | null;
  email: string | null;
  phone: string;
  source: string;
  source_label: string;
  requirement: string;
  notes: string | null;
  entered_at: string;
  status: CrmStatus;
  status_label: string;
  estimated_value: number | null;
  deadline: string | null;
  deal_date: string | null;
  lost_reason: string | null;
  opportunities?: CrmOpportunity[];
  activities?: CrmActivity[];
}

export interface CrmOpportunity {
  id: number;
  title: string;
  lead_id: number | null;
  customer_id: number | null;
  value: number;
  stage: CrmStatus;
  probability: number;
  offer_date: string | null;
  deal_date: string | null;
  notes: string | null;
  lead?: Pick<CrmLead, 'id' | 'name' | 'phone' | 'status'>;
  customer?: Pick<Customer, 'id' | 'name'>;
}

export interface CrmActivity {
  id: number;
  lead_id: number | null;
  type: string;
  description: string;
  created_at: string;
  lead?: Pick<CrmLead, 'id' | 'name' | 'status'>;
}

export type CrmStatus = 'new' | 'contacted' | 'interested' | 'discussion' | 'offer_sent' | 'negotiation' | 'deal' | 'lost';

export interface CrmDashboard {
  status_counts: Record<CrmStatus, number>;
  pipeline_value: number;
  revenue: number;
  source_stats: Record<string, { leads: number; interested: number; offers: number; deals: number; revenue: number }>;
  recent_activities: CrmActivity[];
  recent_leads: CrmLead[];
  customers_count: number;
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

export const TASK_CABANG = {
  tian: 'Tian',
  cecil: 'Cecil',
} as const;

export const CRM_STATUS = {
  new: 'New',
  contacted: 'Contacted',
  interested: 'Interested',
  discussion: 'Discussion',
  offer_sent: 'Offer Sent',
  negotiation: 'Negotiation',
  deal: 'Deal',
  lost: 'Lost',
} as const;

export const CRM_SOURCE = {
  meta_ads: 'Meta Ads',
  whatsapp: 'WhatsApp',
  instagram: 'Instagram',
  referral: 'Referral',
  website: 'Website',
  other: 'Other',
} as const;
