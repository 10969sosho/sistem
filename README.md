# Task Manager - Simple Project & Task Management System

Aplikasi web untuk membantu perusahaan software house mengelola customer, project, task harian, revisi, hosting/domain, dan keuangan project.

## 📋 Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Arsitektur](#arsitektur)
- [Instalasi & Setup](#instalasi--setup)
- [Database Schema](#database-schema)
- [API Endpoints](#api-endpoints)
- [Testing](#testing)
- [Screenshots](#screenshots)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)

---

## 🎯 Fitur Utama

### 1. Dashboard
- Task hari ini
- Task overdue
- Task minggu ini
- Project yang sedang berjalan
- Project revisi
- Hosting akan expired
- Domain akan expired
- Invoice/tagihan belum lunas

### 2. Customer Management
- CRUD Customer
- Search & Filter
- Status tracking (Active/Non Active)
- Relationship: Has Many Projects

### 3. Project Management
- CRUD Project
- Status: Pending, Progress, Testing, Revisi, Maintenance, Selesai
- Deadline tracking
- PIC assignment
- Relationship: Has Many Tasks, Has One Hosting, Has One Finance

### 4. Task Management (Kanban Board)
- **3 Kolom Kanban:**
  - Belum Dikerjakan (todo, waiting)
  - Sudah Dikerjakan (progress) - menunggu check
  - Sudah Check (done)
- **Cabang task (Tian / Cecil):** pilih cabang saat buat/edit task, filter & badge cabang di board
- Drag & drop antar kolom
- Status otomatis tersimpan
- Filter: search, priority, type, cabang
- Detail task dengan workflow status
- Delete task langsung dari kartu kanban

### 5. Hosting & Domain
- Provider & Package tracking
- Domain & Registrar
- SSL Status
- Expired date monitoring
- Server information (IP, Panel, Username)

### 6. Finance
- Total project cost
- DP, Termin 1-3, Pelunasan
- Auto-calculate total paid & remaining
- Payment status: Belum Bayar, Sebagian, Lunas

### 7. Global Search
- Search across Customer, Project, Task, Domain

### 8. CRM (v1)
- Lead pipeline: New → Contacted → Interested → Discussion → Offer Sent → Negotiation → Deal / Lost
- Auto follow-up task saat lead baru masuk
- Activity timeline per lead (WhatsApp, Call, Meeting, Note)
- Opportunity / penawaran dengan pipeline value & revenue tracking
- Konversi Deal otomatis menjadi Customer
- Source performance dashboard
- Semua data CRM di-scope per user
- Delete action pada Leads & Opportunities

---

## 🛠 Tech Stack

### Backend
- **Laravel 13** - PHP Framework
- **MySQL 9** - Database
- **Laravel Sanctum** - API Authentication
- **PHPUnit 12** - Testing (65 tests)

### Frontend
- **Next.js 16** - React Framework
- **TypeScript** - Type Safety
- **Tailwind CSS** - Styling
- **Zustand** - State Management
- **Lucide React** - Icons

---

## 🏗 Arsitektur

### Backend Structure
```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/     # API Controllers
│   │   ├── Requests/            # Form Validation
│   │   └── Resources/           # API Resources
│   ├── Models/                  # Eloquent Models
│   ├── Repositories/            # Repository Pattern
│   └── Services/                # Business Logic
├── database/
│   ├── migrations/              # Database Schema
│   ├── seeders/                 # Demo Data
│   └── factories/               # Test Factories
├── routes/
│   └── api.php                  # API Routes
└── tests/
    └── Feature/                 # Feature Tests
```

### Frontend Structure
```
frontend/
├── src/
│   ├── app/
│   │   ├── (dashboard)/         # Authenticated Pages
│   │   │   ├── customers/
│   │   │   ├── projects/
│   │   │   ├── tasks/
│   │   │   │   ├── page.tsx     # Kanban Board
│   │   │   │   └── [id]/        # Task Detail
│   │   │   ├── hosting/
│   │   │   └── finance/
│   │   ├── login/
│   │   └── layout.tsx           # Root Layout
│   └── lib/
│       ├── api.ts               # API Client
│       ├── auth-store.ts        # Auth State
│       └── types.ts             # TypeScript Types
└── .env.local                   # Environment Config
```

---

## 📦 Instalasi & Setup

### Prerequisites
- PHP 8.3+
- Composer
- Node.js 18+
- MySQL 8+

### Backend Setup

```bash
cd backend

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=root
DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# Seed demo data
php artisan db:seed

# Start development server
php artisan serve
```

Backend akan berjalan di `http://localhost:8000`

### Frontend Setup

```bash
cd frontend

# Install dependencies
npm install

# Configure API URL in .env.local
NEXT_PUBLIC_API_URL=http://localhost:8000/api

# Start development server
npm run dev
```

Frontend akan berjalan di `http://localhost:3000`

### Default Credentials

Setelah menjalankan `php artisan db:seed`:
- **Email:** admin@example.com
- **Password:** password

---

## 🗄 Database Schema

### Users
- id, name, email, password, remember_token, timestamps

### Customers
- id, name, pic_name, whatsapp, email, address, status (active/non_active), notes, timestamps, soft_deletes

### Projects
- id, customer_id, name, type, description, status (pending/progress/testing/revisi/maintenance/selesai), deadline, pic, start_date, finish_date, internal_notes, timestamps, soft_deletes

### Tasks
- id, customer_id, project_id, lead_id, title, type (development/revisi/bug_fix/maintenance), priority (low/medium/high/urgent), status (todo/progress/waiting/done), cabang (tian/cecil), pic, deadline, estimate, notes, timestamps, soft_deletes

### Hostings
- id, project_id (unique), provider, package, expired_date, domain, registrar, domain_expired_date, ssl_status, ssl_expired_date, server_ip, panel, username, notes, timestamps, soft_deletes

### Finances
- id, project_id (unique), total, dp, termin1, termin2, termin3, pelunasan, timestamps, soft_deletes

### Leads (CRM)
- id, name, company, email, phone, source, requirement, notes, entered_at, status, estimated_value, deadline, deal_date, lost_reason, replied_at, user_id, customer_id (nullable), timestamps

### Activities (CRM)
- id, user_id, lead_id (nullable), type, description, timestamps

### Opportunities (CRM)
- id, title, lead_id, customer_id (nullable), value, stage, probability, proposal_sent_at, offer_date, deal_date, expected_close_date, notes, user_id, soft_deletes, timestamps

---

## 🔌 API Endpoints

### Authentication
```
POST   /api/login              # Login
POST   /api/logout             # Logout (auth required)
GET    /api/me                 # Current user (auth required)
```

### Dashboard
```
GET    /api/dashboard          # Dashboard summary (auth required)
```

### Customers
```
GET    /api/customers          # List customers (auth required)
GET    /api/customers/{id}     # Get customer (auth required)
POST   /api/customers          # Create customer (auth required)
PUT    /api/customers/{id}     # Update customer (auth required)
DELETE /api/customers/{id}     # Delete customer (auth required)
```

### Projects
```
GET    /api/projects           # List projects (auth required)
GET    /api/projects/{id}      # Get project (auth required)
POST   /api/projects           # Create project (auth required)
PUT    /api/projects/{id}      # Update project (auth required)
DELETE /api/projects/{id}      # Delete project (auth required)
```

### Tasks
```
GET    /api/tasks              # List tasks (auth required)
GET    /api/tasks/{id}         # Get task (auth required)
POST   /api/tasks              # Create task (auth required)
PUT    /api/tasks/{id}         # Update task (auth required)
PATCH  /api/tasks/{id}/status  # Change status (auth required)
DELETE /api/tasks/{id}         # Delete task (auth required)
```

### Hosting
```
GET    /api/hosting                    # List hosting (auth required)
GET    /api/hosting/project/{id}       # Get by project (auth required)
POST   /api/hosting                    # Create/Update (auth required)
DELETE /api/hosting/project/{id}       # Delete (auth required)
```

### Finance
```
GET    /api/finance                    # List finance (auth required)
GET    /api/finance/project/{id}       # Get by project (auth required)
POST   /api/finance                    # Create/Update (auth required)
DELETE /api/finance/project/{id}       # Delete (auth required)
```

### Search
```
GET    /api/search?q={query}           # Global search (auth required)
```

### Meta
```
GET    /api/meta/options               # Dropdown options (auth required)
GET    /api/meta/enums                 # Enum values (auth required)
```

### CRM
```
GET    /api/crm/dashboard              # CRM summary (auth required)
GET    /api/crm/leads                  # List leads (auth required)
POST   /api/crm/leads                  # Create lead (auth required)
GET    /api/crm/leads/{id}             # Get lead detail (auth required)
PUT    /api/crm/leads/{id}             # Update lead (auth required)
DELETE /api/crm/leads/{id}             # Delete lead (auth required)
PATCH  /api/crm/leads/{id}/status      # Change lead status (auth required)
POST   /api/crm/leads/{id}/activities  # Add activity to lead (auth required)
GET    /api/crm/activities             # List all activities (auth required)
GET    /api/crm/opportunities          # List opportunities (auth required)
POST   /api/crm/opportunities          # Create opportunity (auth required)
GET    /api/crm/opportunities/{id}     # Get opportunity (auth required)
PUT    /api/crm/opportunities/{id}     # Update opportunity (auth required)
DELETE /api/crm/opportunities/{id}     # Delete opportunity (auth required)
```

---

## 🧪 Testing

### Backend Tests

```bash
cd backend

# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=AuthTest

# Run with coverage
php artisan test --coverage
```

**Total: 65 tests** covering:
- Authentication (5 tests)
- Customer CRUD (9 tests)
- Project CRUD (9 tests)
- Task CRUD (13 tests)
- Hosting CRUD (6 tests)
- Finance CRUD (7 tests)
- Dashboard (6 tests)
- Global Search (7 tests)

### Frontend Build

```bash
cd frontend

# Type check
npm run type-check

# Build production
npm run build

# Start production server
npm start
```

---

## 📸 Screenshots

### Dashboard
- 8 widget summary dengan data real-time
- Task hari ini, overdue, minggu ini
- Project progress dan revisi
- Hosting/domain expired
- Tagihan belum lunas

### Task Board (Kanban)
- 3 kolom: Belum Dikerjakan → Sudah Dikerjakan → Sudah Check
- Drag & drop task antar kolom
- Status otomatis tersimpan
- Filter dan search task
- Priority badges dengan warna

### Customer Management
- List dengan search dan filter
- Status active/non-active
- Project count per customer

### Project Management
- List dengan status badges
- Customer relationship
- Task count (open/total)

### Hosting & Domain
- Monitoring expired dates
- Status: active/expiring/expired
- Search by domain/provider

### Finance
- Payment tracking
- Total, paid, remaining calculation
- Status: belum_bayar/sebagian/lunas

---

## 🚀 Deployment

### Backend Deployment

1. **Environment Setup**
```bash
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
```

2. **Optimize**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. **Web Server**
- Nginx/Apache dengan PHP-FPM
- Point document root ke `backend/public`

### Frontend Deployment

1. **Build**
```bash
npm run build
```

2. **Deploy**
- Vercel (recommended)
- Netlify
- Self-hosted with Node.js

3. **Environment Variables**
```
NEXT_PUBLIC_API_URL=https://api.yourdomain.com/api
```

---

## 🔧 Troubleshooting

### CORS Error
Jika ada error CORS, pastikan `backend/config/cors.php`:
```php
'allowed_origins' => ['http://localhost:3000'],
'supports_credentials' => true,
```

### Database Connection
- Cek MySQL service running
- Pastikan database `task_manager` sudah dibuat
- Cek credentials di `.env`

### Frontend Not Loading
- Restart dev server: `npm run dev`
- Clear cache: `rm -rf .next`
- Reinstall: `rm -rf node_modules && npm install`

### Login Failed
- Cek backend running di port 8000
- Cek API token tersimpan di localStorage
- Pastikan user sudah di-seed: `php artisan db:seed`

---

## 📝 Coding Standards

### Backend
- ✅ Service Layer Pattern
- ✅ Repository Pattern
- ✅ Form Request Validation
- ✅ API Resources
- ✅ Soft Deletes
- ✅ Pagination
- ✅ Search & Filter
- ✅ Sorting

### Frontend
- ✅ TypeScript for type safety
- ✅ Component-based architecture
- ✅ Tailwind CSS for styling
- ✅ Responsive design
- ✅ Clean UI principles
- ✅ Drag & drop kanban
- ✅ Modern icons (Lucide)

---

## 🔮 Future Integrations

Struktur sudah disiapkan untuk integrasi dengan:
- **WAHA WhatsApp API** - Notifikasi otomatis
- **AI Assistant** - Smart task suggestions
- **Email** - Reminder & notifications
- **Telegram** - Team notifications

---

## 📄 License

MIT

---

## 👥 Support

Untuk pertanyaan atau issue, silakan buat issue di repository ini.

---

**Built with ❤️ for software house productivity**
