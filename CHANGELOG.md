# Changelog

## 2026-08-18

- Memperbaiki endpoint detail CRM Leads agar lead yang belum terhubung ke customer tetap dapat dimuat.
- Menambahkan regresi test untuk detail lead tanpa customer.
- Memperbaiki perubahan status CRM Leads dari form edit dan menambahkan regresi test endpoint status.
- Mengubah field Lead ID pada form CRM Pipeline menjadi dropdown yang mengambil daftar leads milik user.
- Menambahkan integration test untuk alur login hingga akses dashboard.
- Menambahkan Playwright UI test untuk redirect autentikasi, login, dashboard, dan logout.
