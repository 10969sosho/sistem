# Project Brief - Simple Project & Task Management System

## Objective

Buat sebuah aplikasi web **Simple Project & Task Management** yang fokus untuk membantu perusahaan software house mengelola customer, project, task harian, revisi, hosting/domain, dan keuangan project.

**Prioritas utama adalah kesederhanaan, kecepatan input, dan kemudahan monitoring.**

Jangan membuat sistem seperti ERP. Semua fitur harus ringan dan mudah digunakan.

---

# Tech Stack

* Laravel (Latest)
* MySQL
* next.js
* Vanilla JavaScript
* Responsive
* Clean UI
* Rilltimechange

---

# Menu

## 1. Dashboard

Tampilkan informasi penting:

* Task hari ini
* Task overdue
* Task minggu ini
* Project yang sedang berjalan
* Project revisi
* Hosting akan expired
* Domain akan expired
* Invoice/tagihan belum lunas

---

## 2. Customer

Satu customer dapat memiliki banyak project.

Field:

* Nama Customer
* Nama PIC
* Nomor WhatsApp
* Email
* Alamat
* Status (Active / Non Active)
* Catatan

Relationship

Customer
→ Has Many Project

---

## 3. Project

Setiap project milik satu customer.

Field:

* Customer
* Nama Project
* Jenis Project
* Deskripsi
* Status

Status:

* Pending
* Progress
* Testing
* Revisi
* Maintenance
* Selesai

Field tambahan:

* Deadline
* PIC
* Tanggal Mulai
* Tanggal Selesai
* Catatan Internal

Relationship

Project
→ Has Many Task

Project
→ Has One Hosting Information

Project
→ Has One Finance Information

---

## 4. To Do / Task

Ini adalah menu yang paling sering digunakan.

Task harus cepat dibuat.

Field:

* Judul Task
* Customer
* Project
* PIC
* Priority

Priority:

* Low
* Medium
* High
* Urgent

Status

* Todo
* Progress
* Waiting
* Done

Field tambahan

* Deadline
* Estimasi
* Catatan

Task dapat dibuat dari:

* Project baru
* Revisi customer
* Maintenance
* Task manual

---

## 5. Revisi Customer

Revisi sebenarnya adalah Task.

Tambahkan kategori:

Jenis Task

* Development
* Revisi
* Bug Fix
* Maintenance

Sehingga revisi otomatis masuk ke Task.

---

## 6. Hosting & Domain

Satu project memiliki data hosting.

Field

Hosting

* Provider
* Paket
* Expired Date

Domain

* Domain
* Registrar
* Expired Date

SSL

* Status
* Expired Date

Server

* IP
* Panel
* Username
* Catatan

Dashboard harus dapat menampilkan hosting/domain yang akan expired.

---

## 7. Finance Project

Jangan membuat accounting.

Cukup monitoring pembayaran project.

Field

* Total Project
* DP
* Termin 1
* Termin 2
* Termin 3
* Pelunasan
* Total Dibayar
* Sisa Tagihan

Status

* Belum Bayar
* Sebagian
* Lunas

---

# Dashboard Requirement

Widget

* Task Hari Ini
* Task Terlambat
* Project Progress
* Project Revisi
* Hosting Expired
* Domain Expired
* Tagihan Belum Lunas

---

# Search

Search global harus dapat mencari:

* Customer
* Project
* Task
* Domain

---

# Future Integration

Persiapkan struktur agar mudah diintegrasikan dengan:

* WAHA WhatsApp API
* AI Assistant
* Email
* Telegram

Belum perlu diimplementasikan.

---

# Coding Rules

* Gunakan Service Layer
* Gunakan Repository Pattern bila diperlukan
* Clean Controller
* Reusable Component
* Validasi Form
* Soft Delete
* Pagination
* Search
* Filter
* Sorting

---

# UI Principles

* Sangat sederhana
* Cepat dipahami
* Sedikit klik
* Fokus produktivitas
* Tidak banyak popup
* Mobile friendly

---

# Target

Aplikasi harus menjadi pusat informasi perusahaan software house untuk mengetahui:

* Customer yang dimiliki
* Project yang sedang berjalan
* Task yang harus dikerjakan hari ini
* Revisi yang belum selesai
* Hosting/domain yang akan habis
* Pembayaran project yang belum lunas

Semua informasi harus dapat diakses dengan cepat melalui dashboard dan pencarian global, dengan desain yang sederhana, cepat, dan mudah dikembangkan di masa depan.
