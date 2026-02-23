# Docker & CI/CD Setup Guide

Dokumen ini menjelaskan cara menggunakan konfigurasi Docker dan CI/CD yang telah dibuat untuk otomatisasi deployment ke VPS.

## 1. Persiapan di VPS
Pastikan VPS Anda sudah terinstall:
- **Docker** & **Docker Compose** (v2+)
- Folder project sudah ada (misal di `/var/www/filament-pm`)
- File `.env` sudah disiapkan secara manual di folder tersebut.

---

## 2. Persiapan di GitHub (Secrets)
Agar GitHub Actions bisa melakukan deployment, Anda perlu menambahkan **Repository Secrets**:
1. Buka repo Anda di GitHub.
2. Ke **Settings** > **Secrets and variables** > **Actions**.
3. Tambahkan secret berikut:
   - `VPS_HOST`: IP Address VPS Anda.
   - `VPS_USERNAME`: Username SSH (misal: `root` atau `ubuntu`).
   - `VPS_SSH_KEY`: Isi dari Private SSH Key Anda (`id_rsa`).

---

## 3. Cara Kerja CI/CD
1. Setiap kali Anda melakukan `git push origin main`, GitHub Actions akan otomatis berjalan (lihat tab **Actions**).
2. GitHub akan me-build Docker Image dan menyimpannya di **GitHub Container Registry (GHCR)**.
3. Setelah build selesai, GitHub akan SSH ke VPS Anda dan menjalankan perintah update container secara otomatis.

---

## 4. Perintah Docker yang Berguna di VPS

Jika Anda ingin menjalankan atau melihat status secara manual di VPS:

### Menjalankan Container
```bash
docker compose -f docker-compose.prod.yaml up -d
```

### Melihat Log Aplikasi
```bash
docker logs -f filament_app
```

### Masuk ke dalam Container (Tinker/Artisan)
```bash
docker exec -it filament_app php artisan tinker
```

---

## 5. Keamanan
- Port internal MySQL (3306) dan Redis (6379) tidak dibuka ke publik, hanya bisa diakses antar container.
- Port publik yang dibuka adalah `8080` (bisa diubah di `docker-compose.prod.yaml`).
- Disarankan menggunakan Nginx Reverse Proxy (seperti Nginx Proxy Manager atau Traefik) di depan port `8080` untuk menangani SSL (HTTPS).
