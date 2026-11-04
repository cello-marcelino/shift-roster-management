# Operational Manpower Roster

Sistem penyusunan jadwal kerja mingguan operator berbasis digital dengan integrasi stasiun interaktif (*zero-typing*), deteksi konflik real-time, ekspor dokumen PDF A4 Landscape, dan dukungan aplikasi desktop native menggunakan Electron.

---

## 🛠️ Ringkasan Teknis Sistem

| Komponen | Teknologi | Deskripsi |
|---|---|---|
| **Backend** | Laravel 13 (PHP 8.5) | Arsitektur service layer (`RosterService`, `MasterDataService`, `RosterValidationService`). |
| **Reaktif & UI** | Livewire 4 + Alpine.js | State reaktif instan tanpa reload, modal interaktif, floating toast notification. |
| **Styling** | Tailwind CSS v4 + daisyUI 5 | Dense-mode UI, token warna Teal, tipografi Inter + JetBrains Mono untuk angka/NIDN/NIM. |
| **Database** | SQLite (`database/database.sqlite`) | File-based database lokal, zero-configuration, portabel tanpa dependensi DBMS eksternal. |
| **Desktop Wrapper** | Electron (v45) | Menjalankan aplikasi sebagai jendela desktop native dengan auto-spawn/kill server PHP. |
| **Dokumen Cetak** | `barryvdh/laravel-dompdf` | Cetak A4 Landscape monoline tajam per Line atau Merged PDF seluruh Line. |

### Fitur Kunci:
- **Zero-Typing Roster**: Penugasan stasiun via dropdown inline, multi-station per operator, dan tandem (2+ operator per stasiun).
- **Auto-Fill & Rotasi**: Isi cepat manpower otomatis berbasis matriks keahlian, serta duplikasi jadwal dari minggu sebelumnya dengan opsi rotasi shift.
- **Validasi Non-Blocking**: Notifikasi real-time untuk duplikasi operator, kekurangan MP standard, dan penugasan di luar stasiun keahlian tanpa memblokir penyimpanan atau cetak PDF.

---

## ⚙️ Setup Awal (Instalasi)

Pastikan telah terpasang **PHP >= 8.3**, **Composer**, dan **Node.js >= 20**.

```powershell
# 1. Install dependensi
composer install
npm install

# 2. Konfigurasi Environment & Database
copy .env.example .env
php artisan key:generate
php artisan migrate --seed

# 3. Build asset frontend
npm run build
```

---

## 🚀 Panduan Menjalankan

### A. Mode Desktop (Electron)
Menjalankan sistem langsung sebagai aplikasi desktop native (otomatis mengompilasi asset dan menjalankan server lokal di latar belakang):

```powershell
npm run electron:dev
# atau via composer:
composer run desktop
```

> [!TIP]
> Saat jendela aplikasi desktop ditutup, proses background PHP otomatis dimatikan secara bersih tanpa menyisakan proses di Windows Task Manager.

### B. Mode Web Browser
Jika ingin dijalankan via peramban standar:

```powershell
php artisan serve
```
Akses aplikasi melalui peramban: `http://127.0.0.1:8000`

---

## 📦 Electron Standalone Distribution (.exe)

Untuk mengemas aplikasi menjadi berkas `.exe` mandiri yang dapat dijalankan di komputer lain **tanpa perlu instalasi PHP / Node.js**:

1. **Unduh Portable PHP**: Unduh PHP Windows x64 ZIP dari [windows.php.net](https://windows.php.net/download/) dan ekstrak ke `electron/bin/php/` (pastikan berkas `php.exe` ada di sana dan ekstensi `pdo_sqlite` aktif di `php.ini`).
2. **Build Installer**:
   ```powershell
   npm install --save-dev electron-builder
   npx electron-builder --win
   ```
   File installer/portable `.exe` akan terbentuk di direktori `dist/`.

---

## 🧪 Pengujian (Tests)

Jalankan rangkaian test otomatis:

```powershell
php artisan test
```
