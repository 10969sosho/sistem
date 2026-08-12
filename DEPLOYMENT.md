# Deployment

Task Manager (Laravel API + Next.js frontend) berjalan di server `alurelab`.

## Target

- SSH alias: `alurelab`
- Repository server: `~/repositories/sistem`
- Domain: `qwe.solusisurabaya.com`
- Database: `alurelab_sistem` (MariaDB di hosting)
- Branch production: `main`
- GitHub remote: `https://github.com/10969sosho/sistem.git`

## Arsitektur

Satu domain menangani dua bagian:

| Bagian | Lokasi | Cara disajikan |
| --- | --- | --- |
| Frontend (Next.js static export) | `~/qwe.solusisurabaya.com/*` | File statis (`index.html`, `customers.html`, `_next/`, dll) |
| Backend (Laravel API) | `~/repositories/sistem/backend` | `index.php` front controller di docroot yang mem-bootstrap Laravel; semua request `/api/*` di-rewrite ke `index.php` |

- `frontend/next.config.ts` memakai `output: "export"` → hasil build di `frontend/out/`.
- Frontend adalah SPA client-side; data diambil lewat `NEXT_PUBLIC_API_URL` yang di-build dengan nilai `https://qwe.solusisurabaya.com/api`.
- Route client (mis. `/tasks/:id`) memakai SPA fallback ke `index.html`.
- Route dynamic `/tasks/[id]` di-export sebagai `/tasks/placeholder` lalu dirender client-side.

## Deploy Dari Lokal

```bash
cd frontend && npm run lint && npm run build   # verifikasi build statis
cd ../backend && php artisan test              # 63 tests wajib PASS
git add .
git commit -m "..." 
git push origin main
```

## Update Server

```bash
ssh alurelab

cd ~/repositories/sistem
git pull origin main

# Backend
cd backend
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Frontend (static export)
cd ../frontend
npm install
NEXT_PUBLIC_API_URL=https://qwe.solusisurabaya.com/api npm run build

# Publikasikan hasil build ke docroot
cp -r out/* ~/qwe.solusisurabaya.com/
```

File `.env` production di `~/repositories/sistem/backend/.env` tidak boleh di-commit dan berisi:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://qwe.solusisurabaya.com
DB_CONNECTION=mysql
DB_DATABASE=alurelab_sistem
DB_USERNAME=alurelab_sistem
DB_PASSWORD=...
```

## Database

Database `alurelab_sistem` dibuat lewat cPanel (uapi Mysql) lalu diisi dari dump
MySQL lokal `task_manager` (tabel + data: 1 admin, 17 customer, 25 project,
25 finance). User `alurelab_sistem` memiliki `ALL PRIVILEGES` pada database-nya.
Migrations sudah sinkron (`php artisan migrate:status` menampilkan 15 migration `Ran`).

## Verifikasi

```bash
curl -sI https://qwe.solusisurabaya.com/                        # 200, text/html
curl -s -X POST https://qwe.solusisurabaya.com/api/login \
  -H 'Content-Type: application/json' \
  --data '{"email":"admin@example.com","password":"password"}'   # 200 + token
curl -s -o /dev/null -w '%{http_code}\n' https://qwe.solusisurabaya.com/api/meta/enums   # 401 (unauthenticated)
curl -s https://qwe.solusisurabaya.com/customers                 # 200 (customers.html)
curl -s -o /dev/null -w '%{http_code}\n' https://qwe.solusisurabaya.com/tasks/5   # 200 (SPA fallback)
```

## Rollback

`git revert` commit yang bermasalah lalu ulangi langkah Update Server. Jangan
menghapus `.env` production. File `index.php` dan `.htaccess` di docroot adalah
front controller statis (tidak ikut versi git) — copy dari `backend/public`
pattern `photobox.alureflow.com` bila perlu dipulihkan.
