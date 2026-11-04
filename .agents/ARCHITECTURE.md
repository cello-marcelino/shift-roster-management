---
tags: [rules, engineering, architecture, organization, layered]
aliases: [Architecture Philosophy, Code Organization, Layered Architecture]
related: ["[[PRINCIPLES]]", "[[CODE_STYLE]]", "[[API_RULES]]", "[[AGENTS]]"]
---

# Architecture & Code Organization

Panduan arsitektur sistem, pembagian layer, dan struktur organisasi folder. Berlaku saat membuat atau mengubah modul, class, folder, dan layer kode.

---

## 1. Default Architecture Stack
Gunakan arsitektur layered ini sebagai titik awal standar:

```
HTTP Request ──> Controller ──> Service ──> Repository ──> Database
```

- **Controller**: Menerima HTTP request, memvalidasi input, memanggil Service, dan mengembalikan response terstandarisasi. **Dilarang memuat business logic**.
- **Service**: Rumah utama untuk seluruh business logic murni sistem.
- **Repository**: Satu-satunya layer yang diizinkan mengakses database atau persistence layer secara langsung.

---

## 2. Dependency Direction (Ketat)

Dependency hanya boleh mengarah **satu arah ke dalam/bawah**:

```
Controller ──> Service ──> Repository ──> Model / Database
```

- Controller **tidak boleh** langsung mengakses Model atau Database.
- Service **tidak boleh** bergantung pada HTTP request/response object (agar mudah di-unit test).
- Repository **tidak boleh** menentukan aturan bisnis (*business rules*).

---

## 3. Boundary Antar Layer

Layer tidak boleh ditembus langsung. Contoh pelanggaran fatal:
```
❌ UI component langsung memanggil query database
❌ Controller langsung memanggil Repository (melompati Service)
❌ Service langsung mengembalikan HTTP response object (misal: Response::json)
```

---

## 4. Kontrak Antar Layer (DTO)

Antar layer dan antar domain wajib menggunakan **Data Transfer Object (DTO)** atau schema terstruktur, bukan melemparkan array/object mentah tak berstruktur.

---

## 5. Filosofi Struktur & Domain Grouping

Gunakan **Layered + Domain Grouping** secara konsisten di seluruh project:
- Layer di level atas, domain grouping di dalamnya.
- Penamaan folder domain harus **sama persis** di semua layer:

```
server/src/Controllers/Auth/   server/src/Services/Auth/   server/src/Repositories/Auth/
server/src/Controllers/User/   server/src/Services/User/   server/src/Repositories/User/
```

Hal ini memudahkan navigasi serta ekstraksi domain menjadi microservice/modul terpisah di masa depan.

---

## 6. Folder Structure Standar (Client-Server Monorepo)

```
project-root/
├── client/                     # Frontend Application (Vite + Tailwind v4)
│   ├── src/
│   │   ├── components/         # Komponen UI bersama (reusable 2+ halaman)
│   │   ├── views/ / pages/     # Halaman utama aplikasi (single-page inlined)
│   │   ├── services/           # HTTP API client services
│   │   ├── hooks/ / stores/    # State management & client logic
│   │   └── main.css            # Entry CSS (@import "tailwindcss"; + @theme)
│   ├── vite.config.ts
│   └── package.json
│
└── server/                     # Backend Application
    ├── src/ / app/
    │   ├── Controllers/        # HTTP Handlers
    │   ├── Services/           # Business Logic Layer
    │   ├── Repositories/       # Database Access Layer
    │   ├── Models/             # Entity & Schema
    │   ├── Middleware/         # Auth, Rate Limit, CORS
    │   └── Config/
    ├── database/
    │   ├── migrations/         # Skema berversi
    │   ├── seeders/            # Data statis awal
    │   └── factories/          # Dummy data testing
    ├── tests/
    │   ├── Unit/
    │   └── Integration/
    ├── .env.example
    └── package.json / composer.json / requirements.txt
```

---

## 7. Hierarki Keputusan Penempatan File Baru

Saat menentukan lokasi penempatan berkas baru, ikuti urutan prioritas:
1. **Konvensi modul yang sudah ada** — Jika ada folder serupa, tempatkan di situ.
2. **Konvensi repository** — Ikuti pattern penamaan yang sudah konsisten.
3. **Domain ownership** — Tentukan domain fungsional kepemilikan file.
4. **Separasi tanggung jawab** — Tentukan layer teknisnya (Controller, Service, Repository, dsb).
5. **Tambah struktur seminimal mungkin** — Jangan membuat folder baru kecuali ada alasan kuat.

---

## 8. Module Boundary & Import Rules
- Satu berkas memiliki **satu tanggung jawab utama**.
- Import/export harus **terarah** — cegah terjadinya *circular dependency*.
- Pisahkan kode **shared/common** dari kode yang spesifik untuk satu fitur.

---

## 9. Evolusi Kompleksitas (Earned, Not Assumed)

Tambahkan kompleksitas arsitektur hanya ketika ada pemicu (*trigger*) nyata:

```
Simple App ──> Modular App ──> Modular Monolith ──> Horizontal Scaling ──> Service Extraction
```

| Trigger Nyata | Kompleksitas yang Ditambahkan |
|---|---|
| Butuh multiple implementation | Interface / Strategy Pattern |
| Butuh proses background berat | Queue / Background Worker |
| Butuh independent deployment | Service boundary terpisah |
| Butuh throughput baca tinggi | In-Memory Cache (Redis) |

> **Prinsip**: Kompleksitas harus **diperoleh karena kebutuhan nyata (*earned*)**, bukan diasumsikan (*assumed*).
