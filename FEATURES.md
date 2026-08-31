# Dokumentasi Fitur - Task Manager

Dokumentasi lengkap untuk setiap fitur aplikasi Task Manager.

---

## 1. Dashboard

### Deskripsi
Dashboard adalah halaman utama yang menampilkan ringkasan informasi penting secara real-time. Tujuannya agar tim bisa langsung melihat apa yang perlu diperhatikan tanpa harus membuka menu satu per satu.

### Widget yang Ditampilkan

| Widget | Deskripsi | Warna |
|--------|-----------|-------|
| Task Hari Ini | Task dengan deadline hari ini dan belum selesai | Biru |
| Task Terlambat | Task yang deadline-nya sudah lewat dan belum selesai | Merah |
| Task Minggu Ini | Task dengan deadline 7 hari ke depan | Hijau |
| Project Progress | Project dengan status progress, testing, maintenance | Ungu |
| Project Revisi | Project dengan status revisi | Kuning |
| Hosting Expired | Hosting yang akan expired dalam 30 hari | Oranye |
| Domain Expired | Domain yang akan expired dalam 30 hari | Pink |
| Tagihan Belum Lunas | Finance dengan total bayar < total project | Indigo |

### Cara Kerja
- Data di-fetch dari endpoint `GET /api/dashboard`
- Setiap widget menampilkan jumlah (count) dan preview 3-8 item teratas
- Klik "Lihat semua" untuk membuka halaman terkait dengan filter yang sesuai

### Indikator Warna
- **Merah**: Perlu perhatian segera (overdue, expired)
- **Kuning/Oranye**: Peringatan (akan expired, revisi)
- **Hijau/Biru**: Normal (task aktif, project berjalan)

---

## 2. Customer Management

### Deskripsi
Mengelola data customer/perusahaan yang menjadi klien software house. Satu customer bisa memiliki banyak project.

### Field Customer

| Field | Tipe | Wajib | Deskripsi |
|-------|------|-------|-----------|
| Nama Customer | Text | Ya | Nama perusahaan atau individu |
| Nama PIC | Text | Tidak | Person in charge di sisi customer |
| Nomor WhatsApp | Text | Tidak | Nomor WA untuk komunikasi |
| Email | Email | Tidak | Alamat email customer |
| Alamat | Textarea | Tidak | Alamat lengkap |
| Status | Select | Ya | Active / Non Active |
| Catatan | Textarea | Tidak | Catatan tambahan |

### Fitur
- **List Customer**: Menampilkan semua customer dengan pagination (15 per halaman)
- **Search**: Cari berdasarkan nama, PIC, WhatsApp, atau email
- **Filter Status**: Filter customer active atau non-active
- **Sorting**: Urutkan berdasarkan nama, PIC, status, atau tanggal dibuat
- **Project Count**: Menampilkan jumlah project per customer
- **Soft Delete**: Customer yang dihapus bisa dikembalikan

### Status Customer
- **Active**: Customer aktif, bisa ditambahkan project baru
- **Non Active**: Customer tidak aktif, tetap bisa dilihat tapi tidak disarankan untuk project baru

### Relationship
```
Customer → Has Many → Project
Customer → Has Many → Task
```

---

## 3. Project Management

### Deskripsi
Mengelola project yang sedang berjalan. Setiap project milik satu customer dan bisa memiliki banyak task, data hosting, dan data finance.

### Field Project

| Field | Tipe | Wajib | Deskripsi |
|-------|------|-------|-----------|
| Customer | Select | Ya | Customer pemilik project |
| Nama Project | Text | Ya | Nama project |
| Jenis Project | Text | Tidak | Website, Mobile App, Web App, dll |
| Deskripsi | Textarea | Tidak | Deskripsi project |
| Status | Select | Ya | Status project |
| Deadline | Date | Tidak | Tanggal batas selesai |
| PIC | Text | Tidak | Person in charge internal |
| Tanggal Mulai | Date | Tidak | Tanggal mulai project |
| Tanggal Selesai | Date | Tidak | Tanggal selesai aktual |
| Catatan Internal | Textarea | Tidak | Catatan untuk tim internal |

### Status Project

| Status | Deskripsi | Warna Badge |
|--------|-----------|-------------|
| Pending | Project baru, belum dimulai | Abu-abu |
| Progress | Sedang dikerjakan | Biru |
| Testing | Sedang diuji coba | Kuning |
| Revisi | Menunggu revisi dari customer | Oranye |
| Maintenance | Dalam masa maintenance | Ungu |
| Selesai | Project selesai | Hijau |

### Fitur
- **List Project**: Menampilkan semua project dengan pagination
- **Search**: Cari berdasarkan nama, jenis, deskripsi, atau nama customer
- **Filter Status**: Filter berdasarkan status project
- **Filter Customer**: Filter berdasarkan customer tertentu
- **Task Count**: Menampilkan jumlah task (open/total)
- **Deadline Tracking**: Menampilkan deadline dengan indikator overdue

### Relationship
```
Project → Belongs To → Customer
Project → Has Many → Task
Project → Has One → Hosting
Project → Has One → Finance
```

---

## 4. Task Management (Kanban Board)

### Deskripsi
Menu yang paling sering digunakan. Task ditampilkan dalam bentuk Kanban Board 3 kolom untuk memudahkan tracking workflow tim.

### Field Task

| Field | Tipe | Wajib | Deskripsi |
|-------|------|-------|-----------|
| Judul Task | Text | Ya | Judul/nama task |
| Customer | Select | Tidak | Customer terkait (otomatis dari project) |
| Project | Select | Tidak | Project terkait |
| Cabang | Select | Tidak | Cabang task: Tian / Cecil |
| PIC | Text | Tidak | Person in charge |
| Priority | Select | Ya | Tingkat prioritas |
| Status | Select | Ya | Status task |
| Deadline | Date | Tidak | Tanggal batas selesai |
| Estimasi | Text | Tidak | Estimasi waktu pengerjaan |
| Catatan | Textarea | Tidak | Catatan tambahan |

### Kanban Board - 3 Kolom

```
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│  Belum          │  │  Sudah          │  │  Sudah          │
│  Dikerjakan     │→ │  Dikerjakan     │→ │  Check          │
│                 │  │                 │  │                 │
│  • Task baru    │  │  • Menunggu     │  │  • Selesai      │
│  • Perlu mulai  │  │    check tim    │  │  • Dikonfirmasi │
│                 │  │                 │  │                 │
│  Status:        │  │  Status:        │  │  Status:        │
│  • Todo         │  │  • Progress     │  │  • Done         │
│  • Waiting      │  │                 │  │                 │
└─────────────────┘  └─────────────────┘  └─────────────────┘
```

### Cara Menggunakan Kanban
1. **Drag task** dari kolom kiri ke tengah setelah selesai dikerjakan
2. **Drag task** dari kolom tengah ke kanan setelah tim lain melakukan check
3. **Status otomatis tersimpan** saat task dipindahkan
4. **Undo otomatis** jika gagal menyimpan (kembali ke kolom sebelumnya)

### Jenis Task

| Jenis | Deskripsi | Contoh |
|-------|-----------|--------|
| Development | Pengembangan fitur baru | "Buat halaman login" |
| Revisi | Perubahan berdasarkan feedback customer | "Ubah warna header" |
| Bug Fix | Perbaikan error/bug | "Fix error 500 di checkout" |
| Maintenance | Pemeliharaan rutin | "Update plugin WordPress" |

### Prioritas Task

| Prioritas | Warna | Deskripsi |
|-----------|-------|-----------|
| Low | Abu-abu | Tidak mendesak, bisa dikerjakan nanti |
| Medium | Kuning | Prioritas normal, perlu dikerjakan |
| High | Oranye | Penting, perlu segera dikerjakan |
| Urgent | Merah | Sangat mendesak, harus dikerjakan sekarang |

### Status Task

| Status | Deskripsi |
|--------|-----------|
| Todo | Belum dikerjakan |
| Progress | Sedang dikerjakan |
| Waiting | Menunggu sesuatu (approval, data, dll) |
| Done | Selesai |

### Cabang Task

Task dapat dikelompokkan berdasarkan cabang (branch):

| Cabang | Keterangan |
|--------|-----------|
| Tian | Cabang Tian |
| Cecil | Cabang Cecil |

- Saat membuat/mengedit task, pilih cabang (Tian / Cecil)
- Board task menyediakan filter "Semua Cabang / Tian / Cecil"
- Kartu task menampilkan badge cabang jika terisi

### Fitur Tambahan
- **Search**: Cari task berdasarkan judul, project, customer, PIC
- **Filter Priority**: Filter berdasarkan prioritas
- **Filter Type**: Filter berdasarkan jenis task
- **Filter Cabang**: Filter berdasarkan cabang task
- **Delete Task**: Hapus task langsung dari kartu kanban (dengan konfirmasi)
- **Indikator Overdue**: Task yang deadline-nya sudah lewat ditandai merah
- **Quick Status Change**: Ubah status langsung dari detail task
- **Detail Task**: Halaman detail dengan informasi lengkap

### Hubungan dengan Revisi Customer
Revisi customer sebenarnya adalah Task dengan jenis "Revisi". Jadi tidak perlu menu terpisah, cukup filter task berdasarkan type = "revisi".

---

## 5. Hosting & Domain

### Deskripsi
Mengelola informasi hosting, domain, SSL, dan server untuk setiap project. Penting untuk memantau tanggal expired agar tidak terjadi downtime.

### Field Hosting

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| **Hosting** | | |
| Provider | Text | Penyedia hosting (Niagahoster, IDCloudHost, dll) |
| Paket | Text | Paket hosting (Business, Premium, VPS, dll) |
| Expired Date | Date | Tanggal expired hosting |
| **Domain** | | |
| Domain | Text | Nama domain (example.com) |
| Registrar | Text | Registrar domain (Niagahoster, Namecheap, dll) |
| Domain Expired Date | Date | Tanggal expired domain |
| **SSL** | | |
| SSL Status | Select | Active / Non Active |
| SSL Expired Date | Date | Tanggal expired SSL |
| **Server** | | |
| Server IP | Text | IP address server |
| Panel | Text | Control panel (cPanel, Plesk, dll) |
| Username | Text | Username akses panel |
| Catatan | Textarea | Catatan tambahan |

### Status Monitoring

| Status | Deskripsi | Warna |
|--------|-----------|-------|
| Active | Masih aktif, belum mendekati expired | Hijau |
| Expiring | Akan expired dalam 30 hari | Kuning |
| Expired | Sudah expired | Merah |

### Fitur
- **List Hosting**: Menampilkan semua data hosting dengan pagination
- **Search**: Cari berdasarkan domain, provider, registrar, atau nama project
- **Status Indicator**: Badge warna untuk status hosting dan domain
- **Expired Alert**: Dashboard menampilkan hosting/domain yang akan expired
- **Per-Project**: Data hosting terikat ke satu project (one-to-one)

### Cara Kerja
- Data hosting di-input dari endpoint `POST /api/hosting` dengan project_id
- Jika sudah ada data hosting untuk project tersebut, akan di-update (upsert)
- Dashboard otomatis menampilkan hosting yang akan expired dalam 30 hari

---

## 6. Finance Project

### Deskripsi
Monitoring pembayaran project tanpa kompleksitas accounting. Cukup tracking total, DP, termin, dan pelunasan.

### Field Finance

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| Project | Select | Project terkait |
| Total Project | Number | Total harga project |
| DP | Number | Down payment / uang muka |
| Termin 1 | Number | Pembayaran tahap 1 |
| Termin 2 | Number | Pembayaran tahap 2 |
| Termin 3 | Number | Pembayaran tahap 3 |
| Pelunasan | Number | Pembayaran pelunasan |

### Auto-Calculate
- **Total Dibayar** = DP + Termin 1 + Termin 2 + Termin 3 + Pelunasan
- **Sisa Tagihan** = Total Project - Total Dibayar
- **Status Pembayaran** = Otomatis berdasarkan total bayar

### Status Pembayaran

| Status | Kondisi | Warna |
|--------|---------|-------|
| Belum Bayar | Total bayar = 0 | Merah |
| Sebagian | 0 < Total bayar < Total project | Kuning |
| Lunas | Total bayar >= Total project | Hijau |

### Fitur
- **List Finance**: Menampilkan semua data finance dengan pagination
- **Search**: Cari berdasarkan nama project
- **Filter Status**: Filter berdasarkan status pembayaran
- **Currency Format**: Otomatis format Rupiah (IDR)
- **Progress Indicator**: Menampilkan total, paid, remaining
- **Per-Project**: Data finance terikat ke satu project (one-to-one)

### Cara Kerja
- Data finance di-input dari endpoint `POST /api/finance` dengan project_id
- Jika sudah ada data finance untuk project tersebut, akan di-update (upsert)
- Total paid dan remaining dihitung otomatis di backend
- Status pembayaran ditentukan otomatis berdasarkan total bayar

### Contoh Perhitungan
```
Total Project: Rp 10.000.000
DP:            Rp  3.000.000
Termin 1:      Rp  2.000.000
Termin 2:      Rp  2.000.000
Termin 3:      Rp  1.000.000
Pelunasan:     Rp  2.000.000
─────────────────────────────
Total Dibayar: Rp 10.000.000
Sisa Tagihan:  Rp      0
Status:        Lunas ✓
```

---

## 7. Global Search

### Deskripsi
Pencarian global yang bisa mencari di semua modul: Customer, Project, Task, dan Domain.

### Cara Menggunakan
- Akses dari endpoint `GET /api/search?q={query}`
- Minimal 1 karakter untuk melakukan pencarian
- Hasil dibatasi 10 item per kategori

### Kategori Pencarian

| Kategori | Field yang Dicari |
|----------|-------------------|
| Customer | Nama, PIC, WhatsApp, Email |
| Project | Nama, jenis, deskripsi, nama customer |
| Task | Judul, catatan, PIC, nama project, nama customer |
| Domain | Domain, provider, registrar, nama project |

### Format Response
```json
{
  "data": {
    "customers": [...],
    "projects": [...],
    "tasks": [...],
    "domains": [...]
  }
}
```

### Contoh Penggunaan
- Cari "Acme" → Menampilkan customer, project, task, dan domain yang mengandung "Acme"
- Cari "login" → Menampilkan task dengan judul "Fix login bug", project dengan deskripsi "login system", dll
- Cari "example.com" → Menampilkan domain "example.com" dan project terkait

---

## 8. Authentication

### Deskripsi
Sistem autentikasi menggunakan Laravel Sanctum dengan token-based authentication.

### Flow Login
1. User memasukkan email dan password di halaman login
2. Frontend mengirim request ke `POST /api/login`
3. Backend memverifikasi credentials
4. Jika valid, backend generate token dan return ke frontend
5. Frontend menyimpan token di localStorage
6. Semua request selanjutnya menyertakan token di header `Authorization: Bearer {token}`

### Flow Logout
1. User klik tombol Logout
2. Frontend mengirim request ke `POST /api/logout` dengan token
3. Backend menghapus token dari database
4. Frontend menghapus token dari localStorage
5. Redirect ke halaman login

### Security
- Password di-hash dengan bcrypt
- Token di-generate secara random
- Token disimpan di localStorage (bisa diganti dengan httpOnly cookie untuk production)
- Semua endpoint kecuali `/api/login` memerlukan authentication
- CORS dikonfigurasi untuk mengizinkan hanya origin yang di-whitelist

### Default User
Setelah menjalankan seeder:
- Email: `admin@example.com`
- Password: `password`

---

## 9. Workflow Tim

### Deskripsi
Workflow tim dirancang untuk memudahkan kolaborasi dan tracking progress task.

### Alur Kerja Task

```
1. Task Baru Dibuat
   ↓
2. Masuk Kolom "Belum Dikerjakan" (Todo)
   ↓
3. Tim Mulai Mengerjakan
   ↓
4. Drag ke Kolom "Sudah Dikerjakan" (Progress)
   ↓
5. Tim Lain Melakukan Check
   ↓
6. Drag ke Kolom "Sudah Check" (Done)
   ↓
7. Task Selesai ✓
```

### Peran dalam Workflow
- **Developer**: Mengerjakan task, drag ke kolom tengah setelah selesai
- **Tester/QA**: Check task di kolom tengah, drag ke kolom kanan jika OK
- **Project Manager**: Monitor progress melalui dashboard dan kanban board
- **Customer**: Bisa melihat progress melalui laporan (future feature)

### Best Practices
1. **Buat task se-spesifik mungkin**: "Fix login bug di halaman checkout" lebih baik dari "Fix bug"
2. **Set priority dengan bijak**: Jangan semua task di-set "Urgent"
3. **Update status secara real-time**: Drag task segera setelah selesai
4. **Tambahkan deadline**: Agar tim tahu kapan task harus selesai
5. **Gunakan catatan**: Tambahkan detail yang tidak muat di judul

---

## 10. Future Integrations

### Deskripsi
Struktur aplikasi sudah disiapkan untuk integrasi dengan layanan eksternal.

### WAHA WhatsApp API
- **Tujuan**: Kirim notifikasi otomatis ke customer via WhatsApp
- **Use Case**: 
  - Notifikasi task selesai
  - Reminder deadline
  - Update status project
- **Status**: Struktur siap, belum diimplementasi

### AI Assistant
- **Tujuan**: Membantu tim dengan suggestions dan automasi
- **Use Case**:
  - Auto-generate task description
  - Suggest priority berdasarkan deadline
  - Detect potential delays
- **Status**: Struktur siap, belum diimplementasi

### Email
- **Tujuan**: Kirim notifikasi dan reminder via email
- **Use Case**:
  - Daily task summary
  - Overdue task reminder
  - Project status update
- **Status**: Struktur siap, belum diimplementasi

### Telegram
- **Tujuan**: Notifikasi real-time ke tim via Telegram
- **Use Case**:
  - Alert task overdue
  - Notification hosting expired
  - Team collaboration
- **Status**: Struktur siap, belum diimplementasi

---

## Ringkasan Fitur

| Fitur | Status | Deskripsi Singkat |
|-------|--------|-------------------|
| Dashboard | ✅ Aktif | Ringkasan informasi penting |
| Customer | ✅ Aktif | Kelola data customer |
| Project | ✅ Aktif | Kelola project dengan status tracking |
| Task (Kanban) | ✅ Aktif | Kelola task dengan drag & drop |
| Hosting & Domain | ✅ Aktif | Monitor hosting, domain, SSL |
| Finance | ✅ Aktif | Track pembayaran project |
| Global Search | ✅ Aktif | Cari di semua modul |
| Authentication | ✅ Aktif | Login/logout dengan Sanctum |
| WAHA WhatsApp | 🔜 Future | Notifikasi WhatsApp |
| AI Assistant | 🔜 Future | Smart suggestions |
| Email | 🔜 Future | Notifikasi email |
| Telegram | 🔜 Future | Notifikasi Telegram |

---

**Dokumentasi ini akan di-update seiring dengan penambahan fitur baru.**

## 11. CRM Pipeline - Pemilihan Lead

Form **Penawaran Baru** pada menu Pipeline menggunakan field `LEAD ID` berbentuk dropdown.
Daftar option diambil dari `GET /api/crm/leads` dan hanya menampilkan leads milik user yang sedang login.
Dropdown menampilkan nama lead, ID, dan perusahaan jika tersedia, serta dinonaktifkan saat daftar sedang dimuat atau belum memiliki lead.
API juga memvalidasi kepemilikan `lead_id` sebelum membuat opportunity.
